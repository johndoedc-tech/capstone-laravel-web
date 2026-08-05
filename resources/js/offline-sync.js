import {
    buildRequestBody,
    createQueuedOperation,
    discardOperation,
    getOperation,
    getQueueSummary,
    getUserMeta,
    listOperations,
    resolveOperation,
    saveIdMapping,
    serializeFormData,
    setUserMeta,
    updateOperation,
} from './offline-queue.js';

const MAX_AUTOMATIC_ATTEMPTS = 5;
const BACKOFF_MS = [1000, 2500, 5000, 10000, 30000];
const AUTOMATIC_STATUSES = new Set(['queued', 'syncing', 'authentication_required']);

const toast = (title, message, type = 'success') => {
    window.dispatchEvent(new CustomEvent('harviana-toast', { detail: { title, message, type } }));
};

const formatBytes = (bytes) => {
    if (!bytes) return '0 MB';
    return `${(bytes / (1024 * 1024)).toFixed(bytes >= 1024 * 1024 ? 1 : 2)} MB`;
};

export class HarvianaSyncManager {
    constructor(root) {
        this.root = root;
        this.userId = Number(root.dataset.userId);
        this.userRole = root.dataset.userRole;
        this.contextUrl = root.dataset.contextUrl;
        this.syncing = false;
        this.dialog = root.querySelector('[data-sync-dialog]');
        this.statusLabel = root.querySelector('[data-sync-status]');
        this.countLabel = root.querySelector('[data-sync-count]');
        this.queueList = root.querySelector('[data-sync-list]');
        this.attachmentLabel = root.querySelector('[data-sync-attachments]');
        this.lastSyncLabel = root.querySelector('[data-last-sync]');
        this.pendingCount = 0;
    }

    async init() {
        this.root.querySelectorAll('[data-sync-now]').forEach((button) => button.addEventListener('click', () => this.syncAll({ manual: true })));
        this.root.querySelector('[data-open-sync]')?.addEventListener('click', () => {
            if (typeof this.dialog?.showModal === 'function') this.dialog.showModal();
            else this.dialog?.setAttribute('open', 'open');
            this.refreshUi();
        });
        this.root.querySelectorAll('[data-close-sync]').forEach((button) => button.addEventListener('click', () => {
            if (typeof this.dialog?.close === 'function') this.dialog.close();
            else this.dialog?.removeAttribute('open');
        }));
        this.queueList?.addEventListener('click', (event) => this.handleQueueAction(event));

        window.addEventListener('online', () => {
            this.refreshUi();
            this.syncAll();
        });
        window.addEventListener('offline', () => this.refreshUi());
        navigator.serviceWorker?.addEventListener('message', (event) => {
            if (event.data?.type === 'HARVIANA_SYNC_REQUESTED') this.syncAll();
            if (event.data?.type === 'HARVIANA_SYNC_COMPLETED') {
                this.refreshUi();
                window.dispatchEvent(new CustomEvent('harviana-offline-sync-complete', { detail: { background: true } }));
            }
        });
        window.addEventListener('harviana-offline-queue-updated', () => this.refreshUi());

        this.bindLguForms();
        this.bindLogoutWarning();
        await this.refreshUi();

        if (navigator.onLine) setTimeout(() => this.syncAll(), 500);
    }

    async enqueue(input) {
        const serialized = input.formData ? serializeFormData(input.formData) : {
            fields: input.fields || {},
            attachments: input.attachments || [],
        };
        const operation = await createQueuedOperation({
            ...input,
            ...serialized,
            userId: this.userId,
            userRole: this.userRole,
            sourceUrl: input.sourceUrl || window.location.pathname,
        });

        window.dispatchEvent(new CustomEvent('harviana-offline-queue-updated', { detail: { operation } }));
        await this.registerBackgroundSync();

        return operation;
    }

    async queueAndSync(input) {
        const operation = await this.enqueue(input);

        if (!navigator.onLine) {
            return { operation, queued: true, synchronized: false };
        }

        await this.syncAll({ onlyUuid: operation.uuid });
        const updated = await getOperation(operation.uuid);
        return {
            operation: updated || operation,
            queued: updated?.status !== 'synchronized',
            synchronized: updated?.status === 'synchronized',
            response: updated?.serverResult || null,
        };
    }

    async replaceAndSync(uuid, input) {
        const current = await getOperation(uuid);
        if (!current || Number(current.userId) !== this.userId) {
            throw new Error('This saved record does not belong to the current account.');
        }
        if (input.operationType && input.operationType !== current.operationType) {
            throw new Error('The saved record type cannot be changed.');
        }

        const serialized = input.formData ? serializeFormData(input.formData) : {
            fields: input.fields || {},
            attachments: input.attachments || [],
        };
        const operation = await updateOperation(uuid, {
            ...serialized,
            method: String(input.method || current.method).toUpperCase(),
            endpoint: input.endpoint ?? current.endpoint,
            routeTemplate: input.routeTemplate ?? current.routeTemplate,
            routeParameters: input.routeParameters ?? current.routeParameters,
            payloadType: input.payloadType || (serialized.attachments.length ? 'formdata' : 'json'),
            localRecordId: input.localRecordId || current.localRecordId,
            relatedServerRecordId: input.relatedServerRecordId ?? current.relatedServerRecordId,
            dependsOn: input.dependsOn ?? current.dependsOn,
            expectedRevision: input.expectedRevision ?? current.expectedRevision,
            display: input.display || current.display,
            status: 'queued',
            attemptCount: 0,
            lastAttemptAt: null,
            nextAttemptAt: null,
            lastError: null,
            serverResult: null,
        });

        window.dispatchEvent(new CustomEvent('harviana-offline-queue-updated', { detail: { operation } }));
        await this.registerBackgroundSync();

        if (navigator.onLine) await this.syncAll({ onlyUuid: operation.uuid });
        const updated = await getOperation(operation.uuid);
        return {
            operation: updated || operation,
            queued: updated?.status !== 'synchronized',
            synchronized: updated?.status === 'synchronized',
            response: updated?.serverResult || null,
        };
    }

    async getFreshContext() {
        const response = await fetch(this.contextUrl, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json' },
        });

        if (response.status === 401 || response.status === 419 || response.redirected) {
            const error = new Error('Sign in to continue syncing.');
            error.code = 'authentication_required';
            throw error;
        }

        if (!response.ok) throw new Error('Harviana could not refresh the secure session.');

        const context = await response.json();
        if (Number(context.user?.id) !== this.userId) {
            const error = new Error('Sign in with the account that saved these records.');
            error.code = 'authentication_required';
            throw error;
        }

        return context;
    }

    async syncAll({ manual = false, onlyUuid = null } = {}) {
        if (this.syncing || !navigator.onLine) {
            await this.refreshUi();
            return;
        }

        this.syncing = true;
        await this.refreshUi();

        try {
            const context = await this.getFreshContext();
            const operations = (await listOperations(this.userId))
                .filter((operation) => !onlyUuid || operation.uuid === onlyUuid)
                .filter((operation) => manual
                    ? operation.status !== 'synchronized' && operation.status !== 'conflict' && operation.status !== 'needs_attention'
                    : AUTOMATIC_STATUSES.has(operation.status));

            for (const operation of operations) {
                if (!navigator.onLine) break;
                if (operation.nextAttemptAt && !manual && Date.parse(operation.nextAttemptAt) > Date.now()) continue;

                const resolved = await resolveOperation(operation);
                if (!resolved.ready) continue;

                await this.syncOne(resolved.operation, context.csrf_token);
            }

        } catch (error) {
            if (error.code === 'authentication_required') {
                const operations = await listOperations(this.userId);
                await Promise.all(operations
                    .filter((operation) => AUTOMATIC_STATUSES.has(operation.status) || operation.status === 'failed')
                    .map((operation) => updateOperation(operation.uuid, {
                        status: 'authentication_required',
                        lastError: error.message,
                    })));
                toast('Sync paused', error.message, 'warning');
            }
        } finally {
            this.syncing = false;
            await this.refreshUi();
        }
    }

    async syncOne(operation, csrfToken) {
        const baseOrigin = globalThis.location?.origin || 'http://localhost';
        let destination;

        try {
            destination = new URL(operation.endpoint, baseOrigin);
        } catch (_) {
            await updateOperation(operation.uuid, { status: 'needs_attention', lastError: 'This record has an invalid Harviana destination.' });
            return;
        }

        if (destination.origin !== baseOrigin) {
            await updateOperation(operation.uuid, { status: 'needs_attention', lastError: 'This record is not addressed to Harviana.' });
            return;
        }

        const attemptCount = Number(operation.attemptCount || 0) + 1;
        await updateOperation(operation.uuid, {
            status: 'syncing',
            attemptCount,
            lastAttemptAt: new Date().toISOString(),
            lastError: null,
        });
        await this.refreshUi();

        const body = buildRequestBody(operation);
        const headers = {
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Idempotency-Key': operation.idempotencyKey,
            'X-Harviana-Offline-Sync': '1',
        };
        if (!(body instanceof FormData)) headers['Content-Type'] = 'application/json';

        try {
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
                const error = new Error('Open Harviana and sign in to continue syncing.');
                error.code = 'authentication_required';
                throw error;
            }

            const payload = await response.json().catch(() => ({ message: `Harviana returned HTTP ${response.status}.` }));

            if (response.ok) {
                const serverRecord = payload.event || payload.harvest_event || null;
                if (operation.localRecordId && serverRecord?.id) {
                    await saveIdMapping(this.userId, operation.localRecordId, serverRecord.id);
                }
                await updateOperation(operation.uuid, {
                    status: 'synchronized',
                    attachments: [],
                    serverResult: payload,
                    relatedServerRecordId: serverRecord?.id || operation.relatedServerRecordId,
                    lastError: null,
                    nextAttemptAt: null,
                });
                const synchronizedAt = new Date().toISOString();
                await setUserMeta(this.userId, 'lastSuccessfulSync', synchronizedAt);
                window.dispatchEvent(new CustomEvent('harviana-offline-sync-complete', {
                    detail: { operation, response: payload },
                }));
                return;
            }

            if (response.status === 401 || response.status === 419) {
                await updateOperation(operation.uuid, { status: 'authentication_required', lastError: 'Sign in to continue syncing.' });
                const error = new Error('Sign in to continue syncing.');
                error.code = 'authentication_required';
                throw error;
            }

            if (response.status === 409) {
                await updateOperation(operation.uuid, { status: 'conflict', lastError: payload.message || 'The server record has changed.', serverResult: payload });
                toast('Conflict requires review', payload.message || 'The server record has changed.', 'warning');
                return;
            }

            if (response.status === 422) {
                await updateOperation(operation.uuid, { status: 'needs_attention', lastError: payload.message || 'Review the submitted fields.', serverResult: payload });
                toast('Could not sync', payload.message || 'Review the submitted fields.', 'warning');
                return;
            }

            if (response.status >= 400 && response.status < 500 && ![408, 425, 429].includes(response.status)) {
                await updateOperation(operation.uuid, { status: 'needs_attention', lastError: payload.message || 'This record requires review.', serverResult: payload });
                toast('Could not sync', payload.message || 'This record requires review.', 'warning');
                return;
            }

            await this.markTemporaryFailure(operation, attemptCount, payload.message || `Harviana returned HTTP ${response.status}.`);
        } catch (error) {
            if (error.code === 'authentication_required') throw error;
            await this.markTemporaryFailure(operation, attemptCount, error.message || 'The connection was interrupted.');
        }
    }

    async markTemporaryFailure(operation, attemptCount, message) {
        const exhausted = attemptCount >= MAX_AUTOMATIC_ATTEMPTS;
        const backoff = BACKOFF_MS[Math.min(attemptCount - 1, BACKOFF_MS.length - 1)];
        await updateOperation(operation.uuid, {
            status: exhausted ? 'failed' : 'queued',
            lastError: message,
            nextAttemptAt: exhausted ? null : new Date(Date.now() + backoff).toISOString(),
        });

        if (!exhausted) {
            setTimeout(() => this.syncAll(), backoff + 50);
        }
    }

    async registerBackgroundSync() {
        try {
            const registration = await navigator.serviceWorker?.ready;
            if (registration?.sync) await registration.sync.register('harviana-offline-sync');
        } catch (_) {
            // The online and app-open fallbacks remain active.
        }
    }

    async refreshUi() {
        const summary = await getQueueSummary(this.userId).catch(() => ({ operations: [], total: 0, statusCounts: {}, attachmentCount: 0, attachmentBytes: 0 }));
        this.pendingCount = summary.total;
        const lastSync = await getUserMeta(this.userId, 'lastSuccessfulSync').catch(() => null);
        const needsAttention = (summary.statusCounts.needs_attention || 0) + (summary.statusCounts.conflict || 0) + (summary.statusCounts.failed || 0);
        const authRequired = summary.statusCounts.authentication_required || 0;
        let label = navigator.onLine ? 'Online' : 'Offline';

        if (this.syncing || summary.statusCounts.syncing) label = 'Syncing';
        else if (authRequired) label = 'Sign in to sync';
        else if (needsAttention) label = 'Review sync';
        else if (summary.total) label = navigator.onLine ? 'Pending sync' : 'Saved on device';

        if (this.statusLabel) this.statusLabel.textContent = label;
        if (this.countLabel) {
            this.countLabel.textContent = summary.total;
            this.countLabel.hidden = summary.total === 0;
        }
        if (this.attachmentLabel) this.attachmentLabel.textContent = `${summary.attachmentCount} photos, ${formatBytes(summary.attachmentBytes)}`;
        if (this.lastSyncLabel) this.lastSyncLabel.textContent = lastSync ? new Date(lastSync).toLocaleString() : 'Not yet synced';
        this.root.dataset.state = this.syncing ? 'syncing' : (needsAttention ? 'attention' : (navigator.onLine ? 'online' : 'offline'));
        this.renderQueue(summary.operations);
    }

    renderQueue(operations) {
        if (!this.queueList) return;
        if (!operations.length) {
            this.queueList.innerHTML = '<p class="rounded-lg bg-gray-50 px-3 py-4 text-center text-sm text-gray-500">No records are waiting to sync.</p>';
            return;
        }

        this.queueList.innerHTML = operations.map((operation) => {
            const title = operation.display?.title || operation.operationType.replaceAll('.', ' ');
            const status = operation.status.replaceAll('_', ' ');
            const error = operation.lastError ? `<p class="mt-1 text-xs text-red-700">${this.escape(operation.lastError)}</p>` : '';
            const canRetry = ['failed', 'authentication_required'].includes(operation.status);
            const canEdit = operation.status === 'needs_attention' && operation.operationType.startsWith('farmer.');
            const review = ['needs_attention', 'conflict'].includes(operation.status) && !canEdit
                ? `<a href="${this.escape(operation.sourceUrl || '/')}" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700">Review</a>` : '';
            const edit = canEdit ? '<button type="button" data-queue-action="edit" class="rounded-md border border-gray-300 px-3 py-2 text-xs font-semibold text-gray-700">Edit record</button>' : '';
            const savedFields = Object.entries(operation.fields || {})
                .filter(([, value]) => value !== null && value !== undefined && String(value).trim() !== '')
                .slice(0, 10)
                .map(([name, value]) => `<div class="flex justify-between gap-3"><dt class="capitalize text-gray-500">${this.escape(name.replaceAll('_', ' '))}</dt><dd class="max-w-[60%] break-words text-right text-gray-700">${this.escape(value)}</dd></div>`)
                .join('');
            const currentServer = operation.serverResult?.current_server;
            const queuedDecision = operation.serverResult?.queued_decision;
            const conflictDetails = operation.status === 'conflict' && currentServer
                ? `<div class="mt-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                    <p><strong>Server now:</strong> ${this.escape(currentServer.status || 'changed')} (revision ${this.escape(currentServer.revision ?? 'unknown')})</p>
                    <p class="mt-1"><strong>Your saved decision:</strong> ${this.escape(queuedDecision?.status || 'not sent')} (expected revision ${this.escape(queuedDecision?.expected_revision ?? 'unknown')})</p>
                </div>` : '';
            return `<article class="rounded-xl border border-gray-200 bg-white p-3" data-operation="${operation.uuid}">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0"><p class="truncate text-sm font-semibold text-gray-900">${this.escape(title)}</p><p class="mt-0.5 text-xs capitalize text-gray-500">${this.escape(status)}</p>${error}</div>
                    <span class="shrink-0 text-xs text-gray-400">${operation.attemptCount || 0} tries</span>
                </div>
                ${conflictDetails}
                ${savedFields ? `<details class="mt-3 rounded-lg bg-gray-50 px-3 py-2 text-xs"><summary class="cursor-pointer font-semibold text-gray-700">View saved details</summary><dl class="mt-2 space-y-1.5">${savedFields}</dl></details>` : ''}
                <div class="mt-3 flex flex-wrap justify-end gap-2">${review}${edit}${canRetry ? '<button type="button" data-queue-action="retry" class="rounded-md bg-emerald-600 px-3 py-2 text-xs font-semibold text-white">Retry</button>' : ''}<button type="button" data-queue-action="discard" class="rounded-md border border-red-200 px-3 py-2 text-xs font-semibold text-red-700">Discard</button></div>
            </article>`;
        }).join('');
    }

    async handleQueueAction(event) {
        const button = event.target.closest('[data-queue-action]');
        const article = event.target.closest('[data-operation]');
        if (!button || !article) return;

        if (button.dataset.queueAction === 'discard') {
            if (!window.confirm('Discard this saved-on-device record? This cannot be undone.')) return;
            await discardOperation(article.dataset.operation, this.userId);
        } else if (button.dataset.queueAction === 'edit') {
            const operation = await getOperation(article.dataset.operation);
            if (!operation || Number(operation.userId) !== this.userId) return;
            if (typeof this.dialog?.close === 'function') this.dialog.close();
            else this.dialog?.removeAttribute('open');
            window.dispatchEvent(new CustomEvent('harviana-offline-edit-requested', { detail: { operation } }));
            return;
        } else {
            await updateOperation(article.dataset.operation, { status: 'queued', attemptCount: 0, nextAttemptAt: null, lastError: null });
            await this.syncAll({ manual: true, onlyUuid: article.dataset.operation });
        }

        window.dispatchEvent(new CustomEvent('harviana-offline-queue-updated'));
    }

    bindLguForms() {
        document.querySelectorAll('form[data-offline-operation^="lgu.validation."]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (!form.reportValidity()) return;
                event.preventDefault();
                const submitButton = form.querySelector('[type="submit"]');
                submitButton?.setAttribute('disabled', 'disabled');

                try {
                    const formData = new FormData(form);
                    const decision = form.dataset.offlineOperation.endsWith('approve') ? 'Approve report' : 'Request correction';
                    const result = await this.queueAndSync({
                        operationType: form.dataset.offlineOperation,
                        method: 'POST',
                        endpoint: form.action,
                        formData,
                        expectedRevision: Number(formData.get('expected_revision')),
                        relatedServerRecordId: Number(form.dataset.recordId),
                        recordSnapshot: {
                            id: Number(form.dataset.recordId),
                            status: form.dataset.recordStatus,
                            revision: Number(formData.get('expected_revision')),
                            title: form.dataset.recordTitle,
                        },
                        display: { title: `${decision}: ${form.dataset.recordTitle}` },
                    });

                    if (result.synchronized) {
                        toast('Decision saved', result.response?.message || 'The LGU decision is synced with Harviana.');
                        setTimeout(() => window.location.reload(), 500);
                    } else if (['needs_attention', 'conflict'].includes(result.operation?.status)) {
                        toast('Could not sync - review required', result.operation?.lastError || 'Review this LGU decision before retrying.', 'warning');
                    } else if (result.operation?.status === 'authentication_required') {
                        toast('Sign in to continue syncing', 'This decision is safe on this device and remains tied to this account.', 'warning');
                    } else {
                        toast('Saved on this device', 'The LGU decision will sync when Harviana is online.', 'warning');
                    }
                } catch (error) {
                    toast('Could not save offline', error.message, 'error');
                } finally {
                    submitButton?.removeAttribute('disabled');
                }
            });
        });
    }

    bindLogoutWarning() {
        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement) || !form.action.includes('/logout')) return;
            if (this.pendingCount && !window.confirm(`${this.pendingCount} record(s) are still saved on this device. They will remain tied to this account. Log out anyway?`)) {
                event.preventDefault();
            }
        }, true);
    }

    escape(value) {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    }
}

export const initializeOfflineSync = async () => {
    const root = document.getElementById('harviana-offline-sync');
    if (!root || !globalThis.indexedDB) return null;

    const manager = new HarvianaSyncManager(root);
    window.HarvianaOffline = {
        enqueue: (input) => manager.enqueue(input),
        queueAndSync: (input) => manager.queueAndSync(input),
        replaceAndSync: (uuid, input) => manager.replaceAndSync(uuid, input),
        syncNow: () => manager.syncAll({ manual: true }),
        list: () => listOperations(manager.userId),
        summary: () => getQueueSummary(manager.userId),
        userId: manager.userId,
        userRole: manager.userRole,
    };
    await manager.init();
    return manager;
};
