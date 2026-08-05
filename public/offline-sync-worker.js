(function attachHarvianaWorkerSync(scope) {
    const DB_NAME = 'harviana-offline-queue';
    const DB_VERSION = 1;
    const OPERATIONS = 'operations';
    const MAPPINGS = 'mappings';
    const META = 'meta';
    const MAX_ATTEMPTS = 5;
    const BACKOFF_MS = [1000, 2500, 5000, 10000, 30000];

    const requestResult = (request) => new Promise((resolve, reject) => {
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });

    const transactionDone = (transaction) => new Promise((resolve, reject) => {
        transaction.oncomplete = () => resolve();
        transaction.onerror = () => reject(transaction.error);
        transaction.onabort = () => reject(transaction.error || new Error('Offline queue transaction aborted.'));
    });

    const openDatabase = () => new Promise((resolve, reject) => {
        const request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = () => {
            const database = request.result;
            if (!database.objectStoreNames.contains(OPERATIONS)) {
                const store = database.createObjectStore(OPERATIONS, { keyPath: 'uuid' });
                store.createIndex('userId', 'userId', { unique: false });
                store.createIndex('status', 'status', { unique: false });
                store.createIndex('idempotencyKey', 'idempotencyKey', { unique: true });
                store.createIndex('localRecordId', 'localRecordId', { unique: false });
                store.createIndex('createdAt', 'createdAt', { unique: false });
            }
            if (!database.objectStoreNames.contains(MAPPINGS)) {
                const store = database.createObjectStore(MAPPINGS, { keyPath: 'key' });
                store.createIndex('userId', 'userId', { unique: false });
                store.createIndex('localId', 'localId', { unique: false });
            }
            if (!database.objectStoreNames.contains(META)) database.createObjectStore(META, { keyPath: 'key' });
        };
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
    });

    const getOperations = async (userId) => {
        const database = await openDatabase();
        const transaction = database.transaction(OPERATIONS, 'readonly');
        const operations = await requestResult(transaction.objectStore(OPERATIONS).index('userId').getAll(Number(userId)));
        database.close();
        return operations
            .filter((operation) => ['queued', 'syncing'].includes(operation.status))
            .sort((left, right) => left.createdAt.localeCompare(right.createdAt));
    };

    const updateOperation = async (uuid, changes) => {
        const database = await openDatabase();
        const transaction = database.transaction(OPERATIONS, 'readwrite');
        const store = transaction.objectStore(OPERATIONS);
        const current = await requestResult(store.get(uuid));
        if (current) store.put({ ...current, ...changes });
        await transactionDone(transaction);
        database.close();
    };

    const mappingKey = (userId, localId) => `${Number(userId)}:${localId}`;

    const getMapping = async (userId, localId) => {
        const database = await openDatabase();
        const transaction = database.transaction(MAPPINGS, 'readonly');
        const mapping = await requestResult(transaction.objectStore(MAPPINGS).get(mappingKey(userId, localId)));
        database.close();
        return mapping?.serverId ?? null;
    };

    const saveMapping = async (userId, localId, serverId) => {
        const database = await openDatabase();
        const transaction = database.transaction(MAPPINGS, 'readwrite');
        transaction.objectStore(MAPPINGS).put({
            key: mappingKey(userId, localId),
            userId: Number(userId),
            localId,
            serverId: Number(serverId),
            synchronizedAt: new Date().toISOString(),
        });
        await transactionDone(transaction);
        database.close();
    };

    const setLastSync = async (userId) => {
        const database = await openDatabase();
        const transaction = database.transaction(META, 'readwrite');
        transaction.objectStore(META).put({
            key: `${Number(userId)}:lastSuccessfulSync`,
            userId: Number(userId),
            name: 'lastSuccessfulSync',
            value: new Date().toISOString(),
        });
        await transactionDone(transaction);
        database.close();
    };

    const replaceLocalValues = async (value, userId) => {
        if (typeof value === 'string' && value.startsWith('local:')) return await getMapping(userId, value) ?? value;
        if (Array.isArray(value)) return Promise.all(value.map((item) => replaceLocalValues(item, userId)));
        if (value && typeof value === 'object') {
            const entries = await Promise.all(Object.entries(value).map(async ([key, item]) => [key, await replaceLocalValues(item, userId)]));
            return Object.fromEntries(entries);
        }
        return value;
    };

    const resolveOperation = async (operation) => {
        for (const dependency of operation.dependsOn || []) {
            if (await getMapping(operation.userId, dependency) === null) return null;
        }

        const fields = await replaceLocalValues(operation.fields || {}, operation.userId);
        const parameters = await replaceLocalValues(operation.routeParameters || {}, operation.userId);
        let endpoint = operation.endpoint || operation.routeTemplate;
        for (const [name, value] of Object.entries(parameters)) {
            if (typeof value === 'string' && value.startsWith('local:')) return null;
            endpoint = endpoint.replace(`{${name}}`, encodeURIComponent(value));
        }
        return { ...operation, endpoint, fields, routeParameters: parameters };
    };

    const requestBody = (operation) => {
        if (operation.payloadType === 'formdata' || (operation.attachments || []).length) {
            const formData = new FormData();
            Object.entries(operation.fields || {}).forEach(([name, value]) => {
                (Array.isArray(value) ? value : [value]).forEach((item) => {
                    if (item !== null && item !== undefined) formData.append(name, String(item));
                });
            });
            (operation.attachments || []).forEach((attachment) => formData.append(attachment.field, attachment.blob, attachment.name));
            return formData;
        }
        return JSON.stringify(operation.fields || {});
    };

    const notifyClients = async () => {
        const clients = await scope.clients.matchAll({ type: 'window', includeUncontrolled: true });
        clients.forEach((client) => client.postMessage({ type: 'HARVIANA_SYNC_COMPLETED' }));
    };

    scope.harvianaSyncQueuedOperations = async () => {
        const contextResponse = await fetch('/offline-sync/context', {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json' },
        });

        if (!contextResponse.ok || contextResponse.redirected) return;
        const context = await contextResponse.json();
        const userId = Number(context.user?.id);
        if (!userId || !context.csrf_token) return;

        const operations = await getOperations(userId);
        let temporaryFailure = false;

        for (const queued of operations) {
            if (queued.nextAttemptAt && Date.parse(queued.nextAttemptAt) > Date.now()) {
                temporaryFailure = true;
                continue;
            }
            const operation = await resolveOperation(queued);
            if (!operation) continue;

            let destination;
            try {
                destination = new URL(operation.endpoint, scope.location.origin);
            } catch (_) {
                await updateOperation(operation.uuid, { status: 'needs_attention', lastError: 'This record has an invalid Harviana destination.' });
                continue;
            }

            if (destination.origin !== scope.location.origin) {
                await updateOperation(operation.uuid, { status: 'needs_attention', lastError: 'This record is not addressed to Harviana.' });
                continue;
            }

            const attemptCount = Number(operation.attemptCount || 0) + 1;
            await updateOperation(operation.uuid, {
                status: 'syncing',
                attemptCount,
                lastAttemptAt: new Date().toISOString(),
                lastError: null,
            });

            try {
                const body = requestBody(operation);
                const headers = {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': context.csrf_token,
                    'Idempotency-Key': operation.idempotencyKey,
                    'X-Harviana-Offline-Sync': '1',
                };
                if (!(body instanceof FormData)) headers['Content-Type'] = 'application/json';
                const response = await fetch(destination.href, {
                    method: operation.method,
                    credentials: 'same-origin',
                    headers,
                    body,
                });

                if (response.redirected) {
                    await updateOperation(operation.uuid, {
                        status: 'authentication_required',
                        lastError: 'Open Harviana and sign in to continue syncing.',
                    });
                    break;
                }

                const payload = await response.json().catch(() => ({ message: `Harviana returned HTTP ${response.status}.` }));

                if (response.ok) {
                    const serverRecord = payload.event || payload.harvest_event || null;
                    if (operation.localRecordId && serverRecord?.id) await saveMapping(userId, operation.localRecordId, serverRecord.id);
                    await updateOperation(operation.uuid, {
                        status: 'synchronized',
                        attachments: [],
                        serverResult: payload,
                        relatedServerRecordId: serverRecord?.id || operation.relatedServerRecordId,
                        lastError: null,
                        nextAttemptAt: null,
                    });
                    await setLastSync(userId);
                } else if ([401, 419].includes(response.status)) {
                    await updateOperation(operation.uuid, { status: 'authentication_required', lastError: 'Sign in to continue syncing.' });
                    break;
                } else if (response.status === 409) {
                    await updateOperation(operation.uuid, { status: 'conflict', lastError: payload.message, serverResult: payload });
                } else if (response.status === 422) {
                    await updateOperation(operation.uuid, { status: 'needs_attention', lastError: payload.message, serverResult: payload });
                } else if (response.status >= 400 && response.status < 500 && ![408, 425, 429].includes(response.status)) {
                    await updateOperation(operation.uuid, { status: 'needs_attention', lastError: payload.message || 'This record requires review.', serverResult: payload });
                } else {
                    const exhausted = attemptCount >= MAX_ATTEMPTS;
                    temporaryFailure = temporaryFailure || !exhausted;
                    const backoff = BACKOFF_MS[Math.min(attemptCount - 1, BACKOFF_MS.length - 1)];
                    await updateOperation(operation.uuid, {
                        status: exhausted ? 'failed' : 'queued',
                        lastError: payload.message || `Harviana returned HTTP ${response.status}.`,
                        nextAttemptAt: exhausted ? null : new Date(Date.now() + backoff).toISOString(),
                    });
                }
            } catch (error) {
                const exhausted = attemptCount >= MAX_ATTEMPTS;
                temporaryFailure = temporaryFailure || !exhausted;
                const backoff = BACKOFF_MS[Math.min(attemptCount - 1, BACKOFF_MS.length - 1)];
                await updateOperation(operation.uuid, {
                    status: exhausted ? 'failed' : 'queued',
                    lastError: error.message || 'The connection was interrupted.',
                    nextAttemptAt: exhausted ? null : new Date(Date.now() + backoff).toISOString(),
                });
            }
        }

        await notifyClients();
        if (temporaryFailure) throw new Error('Harviana has queued records that still need a network retry.');
    };
})(self);
