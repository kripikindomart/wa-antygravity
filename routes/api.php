<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\WebhookController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| WhatsApp Gateway REST API
| Documentation: /docs
|
*/

// WhatsApp Webhook (from Node.js service - no auth required)
Route::post('/webhook/whatsapp', [WebhookController::class, 'handle']);

// Authenticated API Routes (requires Sanctum token)
Route::middleware('auth:sanctum')->group(function () {

    /**
     * @group Authentication
     */
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()->only(['id', 'name', 'email', 'created_at']),
        ]);
    });

    // Device Management
    Route::apiResource('devices', DeviceController::class);
    Route::get('/devices/{id}/qr', [DeviceController::class, 'getQr']);

    // Contact Management
    Route::apiResource('contacts', ContactController::class);

    // Message Management
    Route::get('/messages', [MessageController::class, 'index']);
    Route::post('/messages/send', [MessageController::class, 'store']);
    Route::post('/messages/send-bulk', [MessageController::class, 'sendBulk']);
    Route::get('/messages/{id}', [MessageController::class, 'show']);
});
