<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlockController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\DeviceTokenController;
use App\Http\Controllers\Api\V1\FeatureRequestController;
use App\Http\Controllers\Api\V1\FeedController;
use App\Http\Controllers\Api\V1\GoodController;
use App\Http\Controllers\Api\V1\IdVerificationController;
use App\Http\Controllers\Api\V1\MessageController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\NotificationPreferenceController;
use App\Http\Controllers\Api\V1\PasswordResetController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RatingController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RouteAlertController;
use App\Http\Controllers\Api\V1\TripController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:6,1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [PasswordResetController::class, 'forgot']);
    Route::post('/auth/reset-password', [PasswordResetController::class, 'reset']);
});

Route::get('/users/{user}', [ProfileController::class, 'show']);
Route::get('/users/{user}/ratings', [ProfileController::class, 'ratings']);

Route::get('/trips', [TripController::class, 'index']);
Route::get('/trips/{trip}', [TripController::class, 'show']);

Route::get('/goods', [GoodController::class, 'index']);
Route::get('/goods/{good}', [GoodController::class, 'show']);

Route::get('/feed', [FeedController::class, 'index']);

Route::middleware(['auth:sanctum', 'not-banned', 'touch-last-seen'])->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/auth/resend-verification', [AuthController::class, 'resendVerificationEmail']);
    Route::post('/auth/verify-email', [AuthController::class, 'verifyEmail']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::patch('/me', [ProfileController::class, 'update']);
    Route::delete('/me', [ProfileController::class, 'destroy']);

    Route::post('/trips', [TripController::class, 'store']);
    Route::patch('/trips/{trip}', [TripController::class, 'update']);
    Route::delete('/trips/{trip}', [TripController::class, 'destroy']);

    Route::post('/goods', [GoodController::class, 'store']);
    Route::patch('/goods/{good}', [GoodController::class, 'update']);
    Route::delete('/goods/{good}', [GoodController::class, 'destroy']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{booking}', [BookingController::class, 'show']);
    Route::post('/bookings/{booking}/accept', [BookingController::class, 'accept']);
    Route::post('/bookings/{booking}/reject', [BookingController::class, 'reject']);
    Route::post('/bookings/{booking}/mark-in-transit', [BookingController::class, 'markInTransit']);
    Route::post('/bookings/{booking}/mark-arrived', [BookingController::class, 'markArrived']);
    Route::post('/bookings/{booking}/send-delivery-otp', [BookingController::class, 'sendDeliveryOtp']);
    Route::post('/bookings/{booking}/complete', [BookingController::class, 'complete']);
    Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);

    Route::post('/bookings/{booking}/ratings', [RatingController::class, 'store']);

    Route::get('/conversations', [MessageController::class, 'conversations']);
    Route::get('/messages/unread-count', [MessageController::class, 'unreadCount']);
    Route::get('/users/{user}/messages', [MessageController::class, 'index']);
    Route::post('/users/{user}/messages', [MessageController::class, 'store']);

    Route::post('/users/{user}/reports', [ReportController::class, 'store']);

    Route::get('/blocks', [BlockController::class, 'index']);
    Route::post('/users/{user}/block', [BlockController::class, 'store']);
    Route::delete('/users/{user}/block', [BlockController::class, 'destroy']);

    Route::get('/route-alerts', [RouteAlertController::class, 'index']);
    Route::post('/route-alerts', [RouteAlertController::class, 'store']);
    Route::delete('/route-alerts/{routeAlert}', [RouteAlertController::class, 'destroy']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);

    Route::get('/notification-preferences', [NotificationPreferenceController::class, 'show']);
    Route::patch('/notification-preferences', [NotificationPreferenceController::class, 'update']);

    Route::post('/device-tokens', [DeviceTokenController::class, 'store']);
    Route::delete('/device-tokens', [DeviceTokenController::class, 'destroy']);

    Route::get('/id-verification', [IdVerificationController::class, 'show']);
    Route::post('/id-verification', [IdVerificationController::class, 'store']);

    Route::get('/feature-requests', [FeatureRequestController::class, 'index']);
    Route::post('/feature-requests', [FeatureRequestController::class, 'store']);
    Route::post('/feature-requests/{featureRequest}/vote', [FeatureRequestController::class, 'vote']);
});
