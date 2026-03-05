<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\App\CheckoutController;
use App\Http\Controllers\Admin\ChatController;
<<<<<<< HEAD
use App\Http\Controllers\Api\AuthController;
=======
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\SubdomainProvisionController;

Route::post('/checkout/process', [CheckoutController::class, 'process'])
    ->name('checkout.process');

Route::post('/checkout/webhook', [CheckoutController::class, 'webhook'])
    ->name('checkout.webhook');

// Webhook Evolution API (sem autenticação)
Route::post('/webhook/evolution', [ChatController::class, 'webhook'])
    ->name('webhook.evolution');

// Chat API routes (com autenticação)
<<<<<<< HEAD
Route::middleware(['api', 'jwt.cookie'])->prefix('chat')->name('api.chat.')->group(function () {
=======
Route::middleware(['web', 'jwt.cookie', 'auth', 'admin'])->prefix('chat')->name('api.chat.')->group(function () {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    Route::get('/conversations', [ChatController::class, 'getConversations'])->name('conversations');
    Route::get('/messages', [ChatController::class, 'getMessages'])->name('messages');
    Route::post('/send', [ChatController::class, 'sendMessage'])->name('send');
    Route::post('/mark-read', [ChatController::class, 'markAsRead'])->name('markRead');
});

<<<<<<< HEAD
Route::middleware('jwt.cookie')->get('/auth/check', function () {
=======
Route::middleware('web')->get('/auth/check', function () {
>>>>>>> c7087f6c00cabafc1ea6f94cc62cb7d79852372f
    return response()->json([
        'authenticated' => auth('api')->check(),
        'user' => auth('api')->user(),
    ]);
});

// Login API para obter JWT (documentado no Scramble com @unauthenticated)
Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

Route::post('create-user', [RegisteredUserController::class, 'apiCreateUser'])->middleware('api.token')->name('api.createUser');

Route::post('provision-subdomain', [SubdomainProvisionController::class, 'create'])->middleware('api.token')->name('api.provisionSubdomain');
Route::get('get-create-user', [RegisteredUserController::class, 'apiGetCreateUser'])->name('api.getCreateUser');
