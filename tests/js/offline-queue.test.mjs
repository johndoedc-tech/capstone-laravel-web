import 'fake-indexeddb/auto';
import assert from 'node:assert/strict';
import { beforeEach, test } from 'node:test';
import {
    buildRequestBody,
    createQueuedOperation,
    deleteOfflineDatabase,
    getIdMapping,
    getOperation,
    listOperations,
    normalizeOfflineStorageError,
    resolveOperation,
    saveIdMapping,
    serializeFormData,
    updateOperation,
} from '../../resources/js/offline-queue.js';
import { HarvianaSyncManager } from '../../resources/js/offline-sync.js';

if (typeof globalThis.CustomEvent === 'undefined') {
    globalThis.CustomEvent = class CustomEvent extends Event {
        constructor(type, options = {}) {
            super(type);
            this.detail = options.detail;
        }
    };
}

const eventBus = new EventTarget();
globalThis.window = Object.assign(eventBus, {
    location: { pathname: '/farmer/my-calendar' },
    dispatchEvent: eventBus.dispatchEvent.bind(eventBus),
    addEventListener: eventBus.addEventListener.bind(eventBus),
});
globalThis.document = {
    createElement: () => ({
        set textContent(value) { this.innerHTML = String(value); },
        innerHTML: '',
    }),
};
Object.defineProperty(globalThis, 'navigator', {
    configurable: true,
    value: { onLine: true },
});

const fakeRoot = () => ({
    dataset: {
        userId: '10',
        userRole: 'farmer',
        contextUrl: '/offline-sync/context',
        state: 'online',
    },
    querySelector: () => null,
    querySelectorAll: () => [],
});

const queuedCropPlan = (overrides = {}) => createQueuedOperation({
    uuid: crypto.randomUUID(),
    userId: 10,
    userRole: 'farmer',
    operationType: 'farmer.crop_plan.create',
    method: 'POST',
    endpoint: '/farmer/calendar-events',
    fields: { crop: 'Cabbage', event_date: '2026-08-05' },
    localRecordId: `local:${crypto.randomUUID()}`,
    ...overrides,
});

beforeEach(async () => {
    await deleteOfflineDatabase();
    navigator.onLine = true;
});

test('creates durable queue records and isolates them by user', async () => {
    const first = await queuedCropPlan();
    await queuedCropPlan({ userId: 20, localRecordId: `local:${crypto.randomUUID()}` });

    const firstUserRecords = await listOperations(10);
    const secondUserRecords = await listOperations(20);

    assert.equal(firstUserRecords.length, 1);
    assert.equal(secondUserRecords.length, 1);
    assert.equal(firstUserRecords[0].uuid, first.uuid);
    assert.equal(firstUserRecords[0].schemaVersion, 1);
    assert.equal(firstUserRecords[0].status, 'queued');
});

test('holds dependent records until a local parent receives a server ID', async () => {
    const parentLocalId = `local:${crypto.randomUUID()}`;
    const child = await createQueuedOperation({
        uuid: crypto.randomUUID(),
        userId: 10,
        userRole: 'farmer',
        operationType: 'farmer.damage_report.create',
        method: 'POST',
        routeTemplate: '/farmer/calendar-events/{id}/damage',
        routeParameters: { id: parentLocalId },
        fields: { crop_plan_event_id: parentLocalId },
        dependsOn: [parentLocalId],
    });

    assert.equal((await resolveOperation(child)).ready, false);

    await saveIdMapping(10, parentLocalId, 501);
    const resolved = await resolveOperation(child);
    assert.equal(resolved.ready, true);
    assert.equal(resolved.operation.endpoint, '/farmer/calendar-events/501/damage');
    assert.equal(resolved.operation.fields.crop_plan_event_id, 501);
});

test('stores and reconstructs photo blobs as multipart form data', async () => {
    const source = new FormData();
    source.append('crop', 'Cabbage');
    source.append('damage_photo', new Blob(['photo-bytes'], { type: 'image/jpeg' }), 'damage.jpg');
    const serialized = serializeFormData(source);
    const operation = await createQueuedOperation({
        uuid: crypto.randomUUID(),
        userId: 10,
        userRole: 'farmer',
        operationType: 'farmer.damage_report.create',
        endpoint: '/farmer/calendar-events',
        payloadType: 'formdata',
        ...serialized,
    });
    const restored = buildRequestBody(await getOperation(operation.uuid));

    assert.equal(restored.get('crop'), 'Cabbage');
    assert.equal(restored.get('damage_photo').size, 11);
    assert.equal(restored.get('damage_photo').type, 'image/jpeg');
});

test('marks a successful request synchronized and maps its temporary ID', async () => {
    const operation = await queuedCropPlan();
    const manager = new HarvianaSyncManager(fakeRoot());
    const originalFetch = globalThis.fetch;
    globalThis.fetch = async () => new Response(JSON.stringify({
        success: true,
        event: { id: 812, crop: 'Cabbage' },
    }), { status: 200, headers: { 'Content-Type': 'application/json' } });

    try {
        await manager.syncOne(operation, 'csrf-token');
    } finally {
        globalThis.fetch = originalFetch;
    }

    const synchronized = await getOperation(operation.uuid);
    assert.equal(synchronized.status, 'synchronized');
    assert.equal(synchronized.attachments.length, 0);
    assert.equal(await getIdMapping(10, operation.localRecordId), 812);
});

test('network interruption keeps the operation queued with bounded backoff', async () => {
    const operation = await queuedCropPlan();
    const manager = new HarvianaSyncManager(fakeRoot());
    manager.syncAll = async () => {};
    const originalFetch = globalThis.fetch;
    globalThis.fetch = async () => { throw new TypeError('Network request failed'); };

    try {
        await manager.syncOne(operation, 'csrf-token');
    } finally {
        globalThis.fetch = originalFetch;
    }

    const queued = await getOperation(operation.uuid);
    assert.equal(queued.status, 'queued');
    assert.equal(queued.attemptCount, 1);
    assert.match(queued.lastError, /Network request failed/);
    assert.ok(Date.parse(queued.nextAttemptAt) > Date.now());
});

test('authentication, validation, and revision conflicts use reviewable states', async () => {
    const manager = new HarvianaSyncManager(fakeRoot());
    const originalFetch = globalThis.fetch;

    try {
        const authOperation = await queuedCropPlan();
        globalThis.fetch = async () => new Response(JSON.stringify({ message: 'Unauthenticated.' }), { status: 419 });
        await assert.rejects(() => manager.syncOne(authOperation, 'expired'), /Sign in/);
        assert.equal((await getOperation(authOperation.uuid)).status, 'authentication_required');

        const invalidOperation = await queuedCropPlan();
        globalThis.fetch = async () => new Response(JSON.stringify({ message: 'The crop field is required.' }), { status: 422 });
        await manager.syncOne(invalidOperation, 'csrf');
        assert.equal((await getOperation(invalidOperation.uuid)).status, 'needs_attention');

        const conflictOperation = await queuedCropPlan();
        globalThis.fetch = async () => new Response(JSON.stringify({
            message: 'This report changed on the server.',
            current_server: { revision: 2 },
            queued_decision: { expected_revision: 1 },
        }), { status: 409 });
        await manager.syncOne(conflictOperation, 'csrf');
        const conflict = await getOperation(conflictOperation.uuid);
        assert.equal(conflict.status, 'conflict');
        assert.equal(conflict.serverResult.current_server.revision, 2);
    } finally {
        globalThis.fetch = originalFetch;
    }
});

test('a followed authentication or onboarding redirect never clears a queued record', async () => {
    const operation = await queuedCropPlan();
    const manager = new HarvianaSyncManager(fakeRoot());
    const originalFetch = globalThis.fetch;
    globalThis.fetch = async () => ({
        redirected: true,
        ok: true,
        status: 200,
        json: async () => ({}),
    });

    try {
        await assert.rejects(() => manager.syncOne(operation, 'csrf'), /sign in/i);
    } finally {
        globalThis.fetch = originalFetch;
    }

    assert.equal((await getOperation(operation.uuid)).status, 'authentication_required');
});

test('converts storage quota failures into a farmer-readable error', () => {
    const error = normalizeOfflineStorageError({ name: 'QuotaExceededError' });
    assert.equal(error.name, 'OfflineStorageQuotaError');
    assert.match(error.message, /enough browser storage/);
});

test('resumes an authentication-paused operation after the same user signs in', async () => {
    const operation = await queuedCropPlan();
    await updateOperation(operation.uuid, { status: 'authentication_required' });
    const manager = new HarvianaSyncManager(fakeRoot());
    const originalFetch = globalThis.fetch;
    globalThis.fetch = async (url) => {
        if (url === '/offline-sync/context') {
            return new Response(JSON.stringify({
                success: true,
                csrf_token: 'fresh-csrf',
                user: { id: 10, role: 'farmer' },
            }), { status: 200, headers: { 'Content-Type': 'application/json' } });
        }

        return new Response(JSON.stringify({ success: true, event: { id: 901 } }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' },
        });
    };

    try {
        await manager.syncAll();
    } finally {
        globalThis.fetch = originalFetch;
    }

    assert.equal((await getOperation(operation.uuid)).status, 'synchronized');
});

test('manual sync retries failed records without requiring Background Sync support', async () => {
    const operation = await queuedCropPlan();
    await updateOperation(operation.uuid, { status: 'failed', attemptCount: 5 });
    const manager = new HarvianaSyncManager(fakeRoot());
    const originalFetch = globalThis.fetch;
    globalThis.fetch = async (url) => {
        if (url === '/offline-sync/context') {
            return new Response(JSON.stringify({
                success: true,
                csrf_token: 'fresh-csrf',
                user: { id: 10, role: 'farmer' },
            }), { status: 200, headers: { 'Content-Type': 'application/json' } });
        }

        return new Response(JSON.stringify({ success: true, event: { id: 902 } }), {
            status: 200,
            headers: { 'Content-Type': 'application/json' },
        });
    };

    try {
        await manager.registerBackgroundSync();
        await manager.syncAll({ manual: true });
    } finally {
        globalThis.fetch = originalFetch;
    }

    assert.equal((await getOperation(operation.uuid)).status, 'synchronized');
});

test('safe queue editing keeps the original idempotency key and local ID', async () => {
    const operation = await queuedCropPlan();
    await updateOperation(operation.uuid, { status: 'needs_attention', lastError: 'Area needs review.' });
    const manager = new HarvianaSyncManager(fakeRoot());
    navigator.onLine = false;

    await manager.replaceAndSync(operation.uuid, {
        operationType: operation.operationType,
        method: operation.method,
        endpoint: operation.endpoint,
        fields: { crop: 'Cabbage', desired_area_sqm: '750', event_date: '2026-08-05' },
        localRecordId: operation.localRecordId,
    });

    const edited = await getOperation(operation.uuid);
    assert.equal(edited.status, 'queued');
    assert.equal(edited.idempotencyKey, operation.idempotencyKey);
    assert.equal(edited.localRecordId, operation.localRecordId);
    assert.equal(edited.fields.desired_area_sqm, '750');
});

test('refuses to replay a queue record outside the Harviana origin', async () => {
    const operation = await queuedCropPlan({ endpoint: 'https://example.test/collect' });
    const manager = new HarvianaSyncManager(fakeRoot());
    let requested = false;
    const originalFetch = globalThis.fetch;
    globalThis.fetch = async () => {
        requested = true;
        return new Response('{}', { status: 200 });
    };

    try {
        await manager.syncOne(operation, 'csrf');
    } finally {
        globalThis.fetch = originalFetch;
    }

    assert.equal(requested, false);
    assert.equal((await getOperation(operation.uuid)).status, 'needs_attention');
});
