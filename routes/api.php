<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\Admin\AdminActionLogController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminCreationRequestController;
use App\Http\Controllers\Admin\AdminEmailChangeController;
use App\Http\Controllers\Admin\AdminPasswordResetController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\Media\MediaLibraryController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\Settings\SettingController;
use App\Http\Controllers\TransparencyController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');

Route::post('/auth/forgot-password', [AdminPasswordResetController::class, 'forgot'])
    ->middleware('throttle:5,1');

Route::post('/auth/reset-password', [AdminPasswordResetController::class, 'reset'])
    ->middleware('throttle:10,1');

Route::post(
    '/admins/email-change/confirm',
    [AdminEmailChangeController::class, 'confirm']
)->middleware('throttle:10,1');

Route::get(
    '/admins/creation-request',
    [AdminCreationRequestController::class, 'show']
)->middleware('throttle:20,1');

Route::post(
    '/admins/creation-request/confirm',
    [AdminCreationRequestController::class, 'confirm']
)->middleware('throttle:10,1');

Route::get('/settings/public', [SettingController::class, 'public']);

Route::post('/contact', [ContactController::class, 'store'])
    ->middleware('throttle:5,1');

Route::get('/articles/recent', [ArticleController::class, 'recent']);
Route::get('/activities/recent', [ActivityController::class, 'recent']);

Route::get('/transparency', [TransparencyController::class, 'index']);
Route::get('/partners', [PartnerController::class, 'index']);

Route::post(
    '/activities/{activity}/like',
    [ActivityController::class, 'toggleLike']
)->middleware('throttle:30,1');

Route::get(
    '/activities/schedules',
    [ActivityController::class, 'schedules']
)->middleware('auth:admin');

Route::apiResource('articles', ArticleController::class)
    ->only(['index', 'show']);

Route::apiResource('activities', ActivityController::class)
    ->only(['index', 'show']);

Route::apiResource('documents', DocumentController::class)
    ->only(['index', 'show']);

Route::apiResource('keywords', KeywordController::class)
    ->only(['index', 'show']);

Route::apiResource('document-categories', DocumentCategoryController::class)
    ->only(['index', 'show']);

Route::post('/donations', [DonationController::class, 'store'])
    ->middleware('throttle:10,1');

Route::get('/donations/{id}/status', [DonationController::class, 'status'])
    ->middleware('throttle:120,1');

Route::put('/donations/{id}/pix', [DonationController::class, 'updatePix'])
    ->middleware('throttle:10,1');

Route::post(
    '/webhook/mercadopago',
    [DonationController::class, 'webhook']
);

Route::middleware('auth:admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get(
        '/admin/analytics/summary',
        [AdminAnalyticsController::class, 'summary']
    );

    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get(
        '/admin/action-logs',
        [AdminActionLogController::class, 'index']
    );

    Route::get(
        '/admin/action-logs/filters',
        [AdminActionLogController::class, 'filters']
    );

    Route::get(
        '/admin/action-logs/{actionLog}',
        [AdminActionLogController::class, 'show']
    );

    Route::get(
        '/media/{collection}',
        [MediaLibraryController::class, 'index']
    );

    Route::post(
        '/media/{collection}',
        [MediaLibraryController::class, 'store']
    );

    Route::delete(
        '/media/{collection}/{mediaFile}',
        [MediaLibraryController::class, 'destroy']
    );

    Route::apiResource('articles', ArticleController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('activities', ActivityController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('documents', DocumentController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('keywords', KeywordController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource(
        'document-categories',
        DocumentCategoryController::class
    )->only(['store', 'update', 'destroy']);

    Route::get(
        '/admin/partners',
        [PartnerController::class, 'index']
    );

    Route::get(
        '/admin/partners/{partner}',
        [PartnerController::class, 'show']
    );

    Route::apiResource('partners', PartnerController::class)
        ->only(['store', 'update', 'destroy']);

    Route::get('/donations', [DonationController::class, 'index']);

    Route::middleware('admin.master')->group(function () {
        Route::apiResource('admins', AdminController::class);

        Route::post(
            '/admins/create-request',
            [AdminCreationRequestController::class, 'request']
        );

        Route::post(
            '/admins/{admin}/email-change-request',
            [AdminEmailChangeController::class, 'request']
        );

        Route::get('/settings', [SettingController::class, 'index']);
        Route::put('/settings', [SettingController::class, 'update']);

        Route::post(
            '/settings/clear-cache',
            [SettingController::class, 'clearCache']
        );
    });
});
