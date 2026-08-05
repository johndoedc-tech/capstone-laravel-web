<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineOperationReceipt extends Model
{
    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = [
        'idempotency_key',
        'client_operation_uuid',
        'user_id',
        'operation_type',
        'status',
        'response_status',
        'response_body',
        'resource_type',
        'resource_id',
        'completed_at',
    ];

    protected $casts = [
        'response_body' => 'array',
        'response_status' => 'integer',
        'completed_at' => 'datetime',
    ];
}
