export const OFFLINE_DB_NAME = 'harviana-offline-queue';
const DB_VERSION = 1;
const OPERATIONS = 'operations';
const MAPPINGS = 'mappings';
const META = 'meta';

export const OFFLINE_SCHEMA_VERSION = 1;
export const ACTIVE_STATUSES = [
    'queued',
    'syncing',
    'needs_attention',
    'authentication_required',
    'conflict',
    'failed',
];

const requestResult = (request) => new Promise((resolve, reject) => {
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
});

const transactionDone = (transaction) => new Promise((resolve, reject) => {
    transaction.oncomplete = () => resolve();
    transaction.onerror = () => reject(transaction.error);
    transaction.onabort = () => reject(transaction.error || new Error('IndexedDB transaction aborted.'));
});

const storageError = (error) => {
    if (error?.name === 'QuotaExceededError' || error?.name === 'NS_ERROR_DOM_QUOTA_REACHED') {
        const quotaError = new Error('This device does not have enough browser storage for the queued photos. Remove another queued item or submit without leaving this page.');
        quotaError.name = 'OfflineStorageQuotaError';
        return quotaError;
    }

    return error;
};

export const normalizeOfflineStorageError = storageError;

export const deleteOfflineDatabase = () => new Promise((resolve, reject) => {
    const request = indexedDB.deleteDatabase(OFFLINE_DB_NAME);
    request.onsuccess = () => resolve();
    request.onerror = () => reject(request.error);
    request.onblocked = () => reject(new Error('Offline database deletion was blocked.'));
});

export const openOfflineDatabase = () => new Promise((resolve, reject) => {
    if (!globalThis.indexedDB) {
        reject(new Error('Offline storage is not supported in this browser.'));
        return;
    }

    const request = indexedDB.open(OFFLINE_DB_NAME, DB_VERSION);

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

        if (!database.objectStoreNames.contains(META)) {
            database.createObjectStore(META, { keyPath: 'key' });
        }
    };

    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
});

export const makeUuid = () => globalThis.crypto?.randomUUID?.()
    || 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (character) => {
        const random = Math.random() * 16 | 0;
        const value = character === 'x' ? random : (random & 0x3 | 0x8);
        return value.toString(16);
    });

export const serializeFormData = (formData) => {
    const fields = {};
    const attachments = [];

    for (const [name, value] of formData.entries()) {
        if (value instanceof Blob) {
            attachments.push({
                field: name,
                name: value.name || `${name}.bin`,
                type: value.type || 'application/octet-stream',
                size: value.size,
                lastModified: value.lastModified || Date.now(),
                blob: value,
            });
            continue;
        }

        if (Object.prototype.hasOwnProperty.call(fields, name)) {
            fields[name] = Array.isArray(fields[name]) ? [...fields[name], value] : [fields[name], value];
        } else {
            fields[name] = value;
        }
    }

    delete fields._token;

    return { fields, attachments };
};

export const createQueuedOperation = async (input) => {
    const uuid = input.uuid || makeUuid();
    const now = new Date().toISOString();
    const operation = {
        uuid,
        idempotencyKey: input.idempotencyKey || uuid,
        userId: Number(input.userId),
        userRole: String(input.userRole || ''),
        operationType: String(input.operationType),
        method: String(input.method || 'POST').toUpperCase(),
        endpoint: input.endpoint || null,
        routeTemplate: input.routeTemplate || null,
        routeParameters: input.routeParameters || {},
        payloadType: input.payloadType || (input.attachments?.length ? 'formdata' : 'json'),
        fields: input.fields || {},
        attachments: input.attachments || [],
        localRecordId: input.localRecordId || null,
        relatedServerRecordId: input.relatedServerRecordId || null,
        dependsOn: input.dependsOn || [],
        expectedRevision: input.expectedRevision ?? null,
        recordSnapshot: input.recordSnapshot || null,
        sourceUrl: input.sourceUrl || globalThis.location?.pathname || '/',
        display: input.display || {},
        createdAt: input.createdAt || now,
        lastAttemptAt: null,
        attemptCount: 0,
        nextAttemptAt: null,
        status: 'queued',
        lastError: null,
        serverResult: null,
        schemaVersion: OFFLINE_SCHEMA_VERSION,
    };

    if (!operation.userId || !operation.operationType || (!operation.endpoint && !operation.routeTemplate)) {
        throw new Error('The offline operation is missing its user, type, or destination.');
    }

    const database = await openOfflineDatabase();
    const transaction = database.transaction(OPERATIONS, 'readwrite');

    try {
        transaction.objectStore(OPERATIONS).add(operation);
        await transactionDone(transaction);
        return operation;
    } catch (error) {
        throw storageError(error);
    } finally {
        database.close();
    }
};

export const getOperation = async (uuid) => {
    const database = await openOfflineDatabase();
    const transaction = database.transaction(OPERATIONS, 'readonly');
    const result = await requestResult(transaction.objectStore(OPERATIONS).get(uuid));
    database.close();
    return result || null;
};

export const listOperations = async (userId, { includeSynchronized = false } = {}) => {
    const database = await openOfflineDatabase();
    const transaction = database.transaction(OPERATIONS, 'readonly');
    const request = transaction.objectStore(OPERATIONS).index('userId').getAll(Number(userId));
    const records = await requestResult(request);
    database.close();

    return records
        .filter((record) => includeSynchronized || record.status !== 'synchronized')
        .sort((left, right) => left.createdAt.localeCompare(right.createdAt));
};

export const updateOperation = async (uuid, changes) => {
    const database = await openOfflineDatabase();
    const transaction = database.transaction(OPERATIONS, 'readwrite');
    const store = transaction.objectStore(OPERATIONS);
    const current = await requestResult(store.get(uuid));

    if (!current) {
        database.close();
        return null;
    }

    const updated = { ...current, ...changes };
    store.put(updated);
    await transactionDone(transaction);
    database.close();
    return updated;
};

export const discardOperation = async (uuid, userId) => {
    const operation = await getOperation(uuid);
    if (!operation || Number(operation.userId) !== Number(userId)) return false;

    const database = await openOfflineDatabase();
    const transaction = database.transaction(OPERATIONS, 'readwrite');
    transaction.objectStore(OPERATIONS).delete(uuid);
    await transactionDone(transaction);
    database.close();
    return true;
};

export const saveIdMapping = async (userId, localId, serverId) => {
    const database = await openOfflineDatabase();
    const transaction = database.transaction(MAPPINGS, 'readwrite');
    transaction.objectStore(MAPPINGS).put({
        key: `${Number(userId)}:${localId}`,
        userId: Number(userId),
        localId,
        serverId: Number(serverId),
        synchronizedAt: new Date().toISOString(),
    });
    await transactionDone(transaction);
    database.close();
};

export const getIdMapping = async (userId, localId) => {
    const database = await openOfflineDatabase();
    const transaction = database.transaction(MAPPINGS, 'readonly');
    const mapping = await requestResult(transaction.objectStore(MAPPINGS).get(`${Number(userId)}:${localId}`));
    database.close();
    return mapping?.serverId ?? null;
};

const replaceLocalValues = async (value, userId) => {
    if (typeof value === 'string' && value.startsWith('local:')) {
        return await getIdMapping(userId, value) ?? value;
    }

    if (Array.isArray(value)) {
        return Promise.all(value.map((item) => replaceLocalValues(item, userId)));
    }

    if (value && typeof value === 'object') {
        const entries = await Promise.all(Object.entries(value).map(async ([key, item]) => [key, await replaceLocalValues(item, userId)]));
        return Object.fromEntries(entries);
    }

    return value;
};

export const resolveOperation = async (operation) => {
    for (const dependency of operation.dependsOn || []) {
        if (await getIdMapping(operation.userId, dependency) === null) {
            return { ready: false, operation };
        }
    }

    const fields = await replaceLocalValues(operation.fields || {}, operation.userId);
    const routeParameters = await replaceLocalValues(operation.routeParameters || {}, operation.userId);
    let endpoint = operation.endpoint || operation.routeTemplate;

    for (const [name, value] of Object.entries(routeParameters)) {
        if (typeof value === 'string' && value.startsWith('local:')) {
            return { ready: false, operation };
        }
        endpoint = endpoint.replace(`{${name}}`, encodeURIComponent(value));
    }

    return { ready: true, operation: { ...operation, endpoint, fields, routeParameters } };
};

export const buildRequestBody = (operation) => {
    if (operation.payloadType === 'formdata' || (operation.attachments || []).length > 0) {
        const formData = new FormData();
        Object.entries(operation.fields || {}).forEach(([name, value]) => {
            (Array.isArray(value) ? value : [value]).forEach((item) => {
                if (item !== null && item !== undefined) formData.append(name, String(item));
            });
        });
        (operation.attachments || []).forEach((attachment) => {
            formData.append(attachment.field, attachment.blob, attachment.name);
        });
        return formData;
    }

    return JSON.stringify(operation.fields || {});
};

export const getQueueSummary = async (userId) => {
    const operations = await listOperations(userId);
    const statusCounts = operations.reduce((counts, operation) => {
        counts[operation.status] = (counts[operation.status] || 0) + 1;
        return counts;
    }, {});
    const attachments = operations.flatMap((operation) => operation.attachments || []);

    return {
        operations,
        total: operations.length,
        statusCounts,
        attachmentCount: attachments.length,
        attachmentBytes: attachments.reduce((total, attachment) => total + Number(attachment.size || 0), 0),
    };
};

export const setUserMeta = async (userId, name, value) => {
    const database = await openOfflineDatabase();
    const transaction = database.transaction(META, 'readwrite');
    transaction.objectStore(META).put({ key: `${Number(userId)}:${name}`, userId: Number(userId), name, value });
    await transactionDone(transaction);
    database.close();
};

export const getUserMeta = async (userId, name) => {
    const database = await openOfflineDatabase();
    const transaction = database.transaction(META, 'readonly');
    const meta = await requestResult(transaction.objectStore(META).get(`${Number(userId)}:${name}`));
    database.close();
    return meta?.value ?? null;
};
