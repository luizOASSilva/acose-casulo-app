<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\TransparencyController;
use Illuminate\Support\Facades\Route;

Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');

Route::post('/auth/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::get('/auth/me', [AuthController::class, 'me'])
    ->middleware('auth:sanctum');

Route::get('/articles/recent', [ArticleController::class, 'recent']);
Route::get('/activities/recent', [ActivityController::class, 'recent']);
Route::get('/transparency', [TransparencyController::class, 'index']);

Route::post('/donations', [DonationController::class, 'store'])
    ->middleware('throttle:10,1');

Route::put('/donations/{id}', [DonationController::class, 'update'])
    ->middleware('throttle:30,1');

Route::put('/donations/{id}/pix', [DonationController::class, 'updatePix'])
    ->middleware('throttle:10,1');

Route::get('/donations/{id}/status', [DonationController::class, 'status'])
    ->middleware('throttle:120,1');

Route::post('/webhook/mercadopago', [DonationController::class, 'webhook']);


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

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource('admins', AdminController::class);

    Route::apiResource('articles', ArticleController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('activities', ActivityController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('documents', DocumentController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('keywords', KeywordController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('document-categories', DocumentCategoryController::class)
        ->only(['store', 'update', 'destroy']);
});

