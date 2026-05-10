<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\TransparencyController;
use Illuminate\Support\Facades\Route;

Route::get('/articles/recent',    [ArticleController::class,  'recent']);
Route::get('/activities/recent',  [ActivityController::class, 'recent']);
Route::get('/transparency',       [TransparencyController::class, 'index']);


Route::post('/donations',               [DonationController::class, 'store'])
    ->middleware('throttle:10,1');

Route::put('/donations/{id}',           [DonationController::class, 'update'])
    ->middleware('throttle:30,1');

Route::put('/donations/{id}/pix',       [DonationController::class, 'updatePix'])
    ->middleware('throttle:10,1');

Route::get('/donations/{id}/status',    [DonationController::class, 'status'])
    ->middleware('throttle:120,1');

Route::post('/webhook/mercadopago',     [DonationController::class, 'webhook']);

$resources = [
    'articles'   => ArticleController::class,
    'activities' => ActivityController::class,
    'documents'  => DocumentController::class,
    'keywords'   => KeywordController::class,
];

foreach ($resources as $uri => $controller) {
    Route::apiResource($uri, $controller)->only(['index', 'show']);
}

Route::apiResource('document-categories', DocumentCategoryController::class)
    ->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () use ($resources) {
    foreach ($resources as $uri => $controller) {
        Route::apiResource($uri, $controller)->only(['store', 'update', 'destroy']);
    }

    Route::apiResource('document-categories', DocumentCategoryController::class)
        ->only(['store', 'update', 'destroy']);

    Route::apiResource('admins', AdminController::class);
});

Route::get('/donations/latest/debug', function () {
    return \App\Models\Donation::latest()->first()->only(['id', 'status', 'payment_id', 'pix_expires_at']);
});
