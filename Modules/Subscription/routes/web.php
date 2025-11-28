<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Modules\Subscription\src\Http\Controllers\SubscriptionController;

Route::middleware(['auth', 'web'])->group(function () {

    Route::get('/test-auth', function () {
        if (Auth::check()) {
            return 'Logged in as: ' . Auth::user()->email;
        } else {
            return 'Not logged in';
        }
    });

    Route::get('/subscriptions', [SubscriptionController::class, 'index'])
        ->name('subscription.index');

    Route::get('/subscriptions/{subscription}', [SubscriptionController::class, 'show'])
        ->name('subscription.show');

    Route::post('/subscriptions', [SubscriptionController::class, 'store'])
        ->name('subscription.store');

    Route::post('/subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])
        ->name('subscription.cancel');
});
