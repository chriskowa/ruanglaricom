<?php

use App\Http\Controllers\Api\V1\AnalysisApiController;
use App\Http\Controllers\Api\V1\ArticleApiController;
use App\Http\Controllers\Api\V1\AuthApiController;
use App\Http\Controllers\Api\V1\CommunityApiController;
use App\Http\Controllers\Api\V1\EventCalendarApiController;
use App\Http\Controllers\Api\V1\MarketplaceApiController;
use App\Http\Controllers\Api\V1\NotificationApiController;
use App\Http\Controllers\Api\V1\ProfileApiController;
use App\Http\Controllers\Api\V1\RunConnectApiController;
use App\Http\Controllers\Api\V1\RunnerScheduleApiController;
use App\Http\Controllers\Api\V1\StravaApiController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RuangLari Mobile REST API Routes (V1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Public Auth Routes
    Route::post('/auth/register', [AuthApiController::class, 'register'])->name('api.v1.auth.register');
    Route::post('/auth/login', [AuthApiController::class, 'login'])->name('api.v1.auth.login');
    Route::post('/auth/social-login', [AuthApiController::class, 'socialLogin'])->name('api.v1.auth.social-login');
    Route::post('/auth/forgot-password', [AuthApiController::class, 'forgotPassword'])->name('api.v1.auth.forgot-password');

    // Public Blog & Article Routes
    Route::get('/articles', [ArticleApiController::class, 'index'])->name('api.v1.articles.index');
    Route::get('/articles/latest', [ArticleApiController::class, 'latest'])->name('api.v1.articles.latest');
    Route::get('/articles/featured', [ArticleApiController::class, 'featured'])->name('api.v1.articles.featured');
    Route::get('/articles/categories', [ArticleApiController::class, 'categories'])->name('api.v1.articles.categories');
    Route::get('/articles/{slug}', [ArticleApiController::class, 'show'])->name('api.v1.articles.show');

    // Public Race Events Calendar Routes
    Route::get('/events', [EventCalendarApiController::class, 'index'])->name('api.v1.events.index');
    Route::get('/events/{slug}', [EventCalendarApiController::class, 'show'])->name('api.v1.events.show');

    // Public Running Connect Routes
    Route::get('/run-connect/threads', [RunConnectApiController::class, 'index'])->name('api.v1.run-connect.threads.index');
    Route::get('/run-connect/threads/{id}', [RunConnectApiController::class, 'show'])->name('api.v1.run-connect.threads.show');
    Route::get('/run-connect/leaderboard', [RunConnectApiController::class, 'leaderboard'])->name('api.v1.run-connect.leaderboard');

    // Public Marketplace Routes
    Route::get('/marketplace/products', [MarketplaceApiController::class, 'products'])->name('api.v1.marketplace.products');
    Route::get('/marketplace/products/{slug}', [MarketplaceApiController::class, 'showProduct'])->name('api.v1.marketplace.product.show');
    Route::get('/marketplace/categories', [MarketplaceApiController::class, 'categories'])->name('api.v1.marketplace.categories');

    // Public Running Communities Routes
    Route::get('/communities', [CommunityApiController::class, 'index'])->name('api.v1.communities.index');
    Route::get('/communities/{slug}', [CommunityApiController::class, 'show'])->name('api.v1.communities.show');

    // Protected Routes (Requires Bearer Token via Sanctum / API Token)
    Route::middleware(['auth:sanctum'])->group(function () {

        // Auth & Runner Profile
        Route::post('/auth/logout', [AuthApiController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/me', [ProfileApiController::class, 'me'])->name('api.v1.me');
        Route::put('/me/profile', [ProfileApiController::class, 'updateProfile'])->name('api.v1.me.profile.update');
        Route::put('/me/paces', [ProfileApiController::class, 'updatePaces'])->name('api.v1.me.paces.update');

        // AI Running Form Biomechanics Analysis (Camera/Gallery Video Upload)
        Route::post('/analysis/upload', [AnalysisApiController::class, 'upload'])->name('api.v1.analysis.upload');
        Route::get('/analysis/my-reports', [AnalysisApiController::class, 'myReports'])->name('api.v1.analysis.my-reports');
        Route::get('/analysis/reports/{id}', [AnalysisApiController::class, 'showReport'])->name('api.v1.analysis.show-report');

        // Mobile Push Notifications & Device Token
        Route::post('/device-token', [NotificationApiController::class, 'registerDeviceToken'])->name('api.v1.device-token.register');
        Route::get('/notifications', [NotificationApiController::class, 'index'])->name('api.v1.notifications.index');
        Route::post('/notifications/read-all', [NotificationApiController::class, 'markAllRead'])->name('api.v1.notifications.read-all');
        Route::post('/notifications/{id}/read', [NotificationApiController::class, 'markRead'])->name('api.v1.notifications.read');

        // Strava Integration
        Route::get('/strava/status', [StravaApiController::class, 'status'])->name('api.v1.strava.status');
        Route::post('/strava/sync', [StravaApiController::class, 'sync'])->name('api.v1.strava.sync');
        Route::post('/strava/disconnect', [StravaApiController::class, 'disconnect'])->name('api.v1.strava.disconnect');

        // Race Event Bookmarks
        Route::post('/events/{id}/bookmark', [EventCalendarApiController::class, 'toggleBookmark'])->name('api.v1.events.bookmark');
        Route::get('/me/saved-events', [EventCalendarApiController::class, 'savedEvents'])->name('api.v1.me.saved-events');

        // Personal Runner Schedule & Training Calendar
        Route::get('/calendar/month', [RunnerScheduleApiController::class, 'month'])->name('api.v1.calendar.month');
        Route::get('/calendar/day/{date}', [RunnerScheduleApiController::class, 'day'])->name('api.v1.calendar.day');
        Route::post('/calendar/sessions/{id}/complete', [RunnerScheduleApiController::class, 'completeSession'])->name('api.v1.calendar.sessions.complete');
        Route::post('/calendar/sessions/{id}/reschedule', [RunnerScheduleApiController::class, 'rescheduleSession'])->name('api.v1.calendar.sessions.reschedule');
        Route::post('/calendar/custom-workouts', [RunnerScheduleApiController::class, 'storeCustomWorkout'])->name('api.v1.calendar.custom-workouts.store');

        // Running Connect / Cari Teman Lari
        Route::post('/run-connect/threads', [RunConnectApiController::class, 'store'])->name('api.v1.run-connect.threads.store');
        Route::post('/run-connect/generate-description', [RunConnectApiController::class, 'generateDescription'])->name('api.v1.run-connect.generate-description');
        Route::post('/run-connect/threads/{id}/join', [RunConnectApiController::class, 'join'])->name('api.v1.run-connect.threads.join');
        Route::post('/run-connect/threads/{id}/leave', [RunConnectApiController::class, 'leave'])->name('api.v1.run-connect.threads.leave');
        Route::post('/run-connect/threads/{id}/approve/{participantId}', [RunConnectApiController::class, 'approveParticipant'])->name('api.v1.run-connect.approve');
        Route::post('/run-connect/threads/{id}/reject/{participantId}', [RunConnectApiController::class, 'rejectParticipant'])->name('api.v1.run-connect.reject');
        Route::get('/run-connect/threads/{id}/messages', [RunConnectApiController::class, 'getMessages'])->name('api.v1.run-connect.messages.index');
        Route::post('/run-connect/threads/{id}/messages', [RunConnectApiController::class, 'sendMessage'])->name('api.v1.run-connect.messages.store');
        Route::post('/run-connect/threads/{id}/rate', [RunConnectApiController::class, 'rateBuddy'])->name('api.v1.run-connect.rate');
        Route::get('/run-connect/random-match', [RunConnectApiController::class, 'randomMatch'])->name('api.v1.run-connect.random-match');
        Route::get('/run-connect/history', [RunConnectApiController::class, 'history'])->name('api.v1.run-connect.history');
        Route::get('/run-connect/approvals', [RunConnectApiController::class, 'approvals'])->name('api.v1.run-connect.approvals');
    });

});
