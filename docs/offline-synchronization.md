# Harviana Offline Synchronization

## Scope

Offline submission is intentionally limited to:

- Farmer crop-plan creation
- Farmer damage-report creation
- Farmer actual-harvest recording
- LGU approval decisions
- LGU correction or rejection decisions

All synchronized writes use authenticated same-origin Laravel routes. The browser never connects directly to Railway PostgreSQL and never stores database credentials, passwords, permanent access tokens, or old CSRF tokens.

## Architecture

1. A supported form is validated by the browser.
2. Its fields and optional photo Blobs are written to IndexedDB before replay.
3. The interface shows `Saved on this device` or `Pending sync`.
4. When online, the window sync manager or service worker obtains a fresh CSRF token and current authenticated user from `/offline-sync/context`.
5. The queue is filtered to that user and processed sequentially in dependency order.
6. Laravel repeats all validation and authorization checks.
7. `IdempotentOperationService` stores the accepted response with the user, operation type, and UUID in the same database transaction as the agricultural write.
8. A replay returns the original response and does not create another record or attachment.
9. Successful temporary IDs are mapped to server IDs before child operations are sent.

Harviana remains deployed on Railway and connected to its machine-learning service and Railway PostgreSQL. Crop plans created offline do not fabricate an ML result; production estimates are shown only after Laravel accepts the plan and runs the existing online prediction flow.

## IndexedDB Schema

Database: `harviana-offline-queue`, version `1`.

### `operations`

Key: `uuid`. Indexes: `userId`, `status`, unique `idempotencyKey`, `localRecordId`, and `createdAt`.

Each record contains:

- `uuid`, `idempotencyKey`, and `schemaVersion`
- `userId` and `userRole`
- `operationType`, `method`, `endpoint` or `routeTemplate`
- `routeParameters`, `fields`, `payloadType`, and optional Blob `attachments`
- `localRecordId`, `relatedServerRecordId`, and `dependsOn`
- `expectedRevision` and an LGU `recordSnapshot` when relevant
- `createdAt`, `lastAttemptAt`, `attemptCount`, and `nextAttemptAt`
- `status`, `lastError`, and the confirmed `serverResult`

Statuses are `queued`, `syncing`, `synchronized`, `needs_attention`, `authentication_required`, `conflict`, and `failed`.

### `mappings`

Maps an account-bound local ID such as `local:<uuid>` to the server-generated event ID.

### `meta`

Stores non-secret per-user synchronization metadata, including the last successful sync time.

## Synchronization Rules

- One signed-in user's queue is processed sequentially.
- A child waits until every local parent ID has a server mapping.
- Same-origin cookies and a newly fetched CSRF token are used for every replay session.
- HTTP `401` and `419` pause the record as `authentication_required`.
- HTTP `409` marks an LGU decision as `conflict` and retains both server and queued decision data.
- HTTP `422` marks the record as `needs_attention` for correction.
- Network and temporary server failures use limited exponential backoff and stop automatic retry after five attempts.
- Successful records become `synchronized`; their Blob data is removed after confirmed success.
- Background Sync is used when supported. Window `online`, app-open, and manual `Sync now` paths provide required fallbacks.
- The service worker does not cache Farmer, LGU, Admin, profile, evidence, or synchronization responses.

## LGU Conflict Protection

An LGU decision includes the revision that was loaded online. Laravel locks the report row, rechecks the Validator's municipal authorization, verifies that the report remains pending, and compares the expected revision. A stale decision receives HTTP `409`; it never overwrites the newer server decision and never increments the revision twice.

The current Farmer profile does not store a barangay field. Existing LGU authorization therefore remains municipality-based even when an LGU account has an optional barangay assignment. Barangay-level enforcement requires a separately reviewed Farmer-location schema change.

## Photos and Storage

Damage and harvest photos retain the existing image and 5 MB server limits. IndexedDB stores the original Blob, filename, MIME type, size, and modification time, then reconstructs multipart `FormData` during replay. Quota errors keep the form open and explain that the photo could not be stored. The sync panel shows queued photo count and approximate size.

Evidence responses use private no-store headers and are excluded from service-worker runtime caching.

## Account and Logout Policy

Pending records stay on the device when a user logs out. Harviana warns before logout, keeps records bound to their creator's user ID, and does not display or synchronize them under another account. The original account may later sign in to retry or discard them. This avoids silent data loss while preserving account isolation.

IndexedDB is origin-isolated browser storage, not application-level encrypted storage. Photos may remain accessible to someone with control of an unlocked device or browser profile until they synchronize or are discarded. Shared-device deployments should use device lock policies and should consider encrypted-at-rest outbox storage as a future hardening phase.

## Browser Limitations

- Background Sync support is inconsistent on iOS. Reopening Harviana while online or pressing `Sync now` is the supported fallback.
- Browsers may evict site storage under severe device pressure. Persistent-storage permission can reduce but not eliminate that risk.
- Protected page HTML is not cached to prevent another account from seeing prior authenticated content. Queued operations survive refresh and browser restart in IndexedDB and reappear after the correct user opens an authenticated Farmer or LGU page.

## Later Railway Deployment

No production migration or deployment is performed by this implementation.

After review and approval:

1. Take or confirm a Railway PostgreSQL backup.
2. Deploy the reviewed application build to the Harviana Railway service.
3. Run `php artisan migrate --force` in the Harviana Railway service so `offline_operation_receipts` exists before accepting queued writes.
4. Run `php artisan optimize:clear` if the deployment does not already clear Laravel caches.
5. Open Farmer and LGU pages once online so the new service worker activates.
6. Test one crop plan, one photo report, one harvest, and one LGU decision with a non-production test account.
7. Confirm one receipt per operation in Railway PostgreSQL and confirm that no duplicate agricultural records were created.

Do not roll back the receipt migration after offline submissions begin unless queued clients have been disabled and the idempotency implications have been reviewed.
