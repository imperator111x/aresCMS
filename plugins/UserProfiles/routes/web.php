<?php

require_once __DIR__.'/../src/bootstrap.php';

use Illuminate\Support\Facades\Route;
use Plugins\UserProfiles\Http\Controllers\ProfileController;

Route::middleware(['web', 'auth'])
    ->group(function (): void {
        Route::get('/members', [ProfileController::class, 'index'])->name('profiles.index');
        Route::get('/messages', [ProfileController::class, 'inbox'])->name('profiles.inbox');
        Route::get('/messages/unread-summary', [ProfileController::class, 'unreadSummary'])
            ->middleware('throttle:60,1')
            ->name('profiles.unread-summary');
        Route::get('/members/{user}', [ProfileController::class, 'show'])->whereNumber('user')->name('profiles.show');
        Route::post('/members/{user}/friend-request', [ProfileController::class, 'sendFriendRequest'])
            ->whereNumber('user')
            ->middleware('throttle:20,1')
            ->name('profiles.friend-request');

        Route::post('/friendships/{friendship}/accept', [ProfileController::class, 'accept'])
            ->whereNumber('friendship')
            ->name('profiles.friendships.accept');
        Route::post('/friendships/{friendship}/decline', [ProfileController::class, 'decline'])
            ->whereNumber('friendship')
            ->name('profiles.friendships.decline');

        Route::get('/friendships/{friendship}/chat', [ProfileController::class, 'chat'])
            ->whereNumber('friendship')
            ->name('profiles.chat');
        Route::get('/friendships/{friendship}/messages', [ProfileController::class, 'fetchMessages'])
            ->whereNumber('friendship')
            ->name('profiles.messages.fetch');
        Route::post('/friendships/{friendship}/messages/read', [ProfileController::class, 'markRead'])
            ->whereNumber('friendship')
            ->name('profiles.messages.mark-read');
        Route::post('/friendships/{friendship}/messages/clear', [ProfileController::class, 'clearMessages'])
            ->whereNumber('friendship')
            ->name('profiles.messages.clear');
        Route::post('/friendships/{friendship}/messages', [ProfileController::class, 'sendMessage'])
            ->whereNumber('friendship')
            ->middleware('throttle:120,1')
            ->name('profiles.messages.store');

        Route::post('/profiles/e2e/public-key', [ProfileController::class, 'storePublicKey'])
            ->middleware('throttle:30,1')
            ->name('profiles.e2e.key');
        Route::get('/friendships/{friendship}/e2e-status', [ProfileController::class, 'e2eStatus'])
            ->whereNumber('friendship')
            ->name('profiles.e2e.status');
    });
