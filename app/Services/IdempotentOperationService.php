<?php

namespace App\Services;

use App\Models\FarmerCalendarEvent;
use App\Models\OfflineOperationReceipt;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class IdempotentOperationService
{
    public function execute(Request $request, string $operationType, Closure $callback): Response
    {
        $key = trim((string) ($request->header('Idempotency-Key') ?: $request->input('client_operation_uuid')));

        if ($key === '') {
            return $callback();
        }

        if (! Str::isUuid($key)) {
            return response()->json([
                'success' => false,
                'message' => 'The client operation ID must be a valid UUID.',
                'errors' => ['client_operation_uuid' => ['Use a valid UUID for this operation.']],
            ], 422);
        }

        if (! Schema::hasTable('offline_operation_receipts')) {
            return response()->json([
                'success' => false,
                'message' => 'Offline synchronization is waiting for the latest Harviana database migration.',
                'code' => 'offline_sync_migration_required',
            ], 503);
        }

        $userId = (int) $request->user()->getAuthIdentifier();

        return DB::transaction(function () use ($callback, $key, $operationType, $userId) {
            DB::table('offline_operation_receipts')->insertOrIgnore([
                'idempotency_key' => $key,
                'client_operation_uuid' => $key,
                'user_id' => $userId,
                'operation_type' => $operationType,
                'status' => OfflineOperationReceipt::STATUS_PROCESSING,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $receipt = OfflineOperationReceipt::query()
                ->where('idempotency_key', $key)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $receipt->user_id !== $userId || $receipt->operation_type !== $operationType) {
                return response()->json([
                    'success' => false,
                    'message' => 'This operation ID is already assigned to a different request.',
                    'code' => 'idempotency_key_conflict',
                ], 409);
            }

            if ($receipt->status === OfflineOperationReceipt::STATUS_COMPLETED) {
                return response()
                    ->json(array_merge($receipt->response_body ?? [], ['idempotent_replay' => true]), $receipt->response_status ?: 200)
                    ->header('Idempotent-Replay', 'true');
            }

            $response = $callback();
            $status = $response->getStatusCode();

            if ($status < 200 || $status >= 300) {
                $receipt->delete();

                return $response;
            }

            $body = $response instanceof JsonResponse
                ? $response->getData(true)
                : ['success' => true];
            $resource = $body['event'] ?? $body['harvest_event'] ?? null;

            $receipt->update([
                'status' => OfflineOperationReceipt::STATUS_COMPLETED,
                'response_status' => $status,
                'response_body' => $body,
                'resource_type' => is_array($resource) ? FarmerCalendarEvent::class : null,
                'resource_id' => is_array($resource) && isset($resource['id']) ? (int) $resource['id'] : null,
                'completed_at' => now(),
            ]);

            $response->headers->set('Idempotent-Replay', 'false');

            return $response;
        }, 3);
    }
}
