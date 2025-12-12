<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App\CheckoutController;
use App\Http\Controllers\Admin\ChatController;
use Illuminate\Support\Facades\Auth;


Route::post('/checkout/process', [CheckoutController::class, 'process'])
    ->name('checkout.process');

Route::post('/checkout/webhook', [CheckoutController::class, 'webhook'])
    ->name('checkout.webhook');

// Webhook Evolution API (sem autenticação)
Route::post('/webhook/evolution', [ChatController::class, 'webhook'])
    ->name('webhook.evolution');

// Chat API routes (com autenticação)
Route::middleware(['web', 'jwt.cookie', 'auth', 'admin'])->prefix('chat')->name('api.chat.')->group(function () {
    Route::get('/conversations', [ChatController::class, 'getConversations'])->name('conversations');
    Route::get('/messages', [ChatController::class, 'getMessages'])->name('messages');
    Route::post('/send', [ChatController::class, 'sendMessage'])->name('send');
    Route::post('/mark-read', [ChatController::class, 'markAsRead'])->name('markRead');
});

Route::middleware('web')->get('/auth/check', function () {
    return response()->json([
        'authenticated' => auth()->check(),
        'user' => auth()->user(),
    ]);
});

