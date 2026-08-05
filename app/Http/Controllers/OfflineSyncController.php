<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfflineSyncController extends Controller
{
    public function context(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'csrf_token' => csrf_token(),
            'user' => [
                'id' => (int) $request->user()->getAuthIdentifier(),
                'role' => (string) $request->user()->role,
            ],
        ])->header('Cache-Control', 'no-store, private');
    }
}
