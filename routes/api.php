<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

$resources = [
    'articles' => ArticleController::class,
    'activities' => ActivityController::class,
    'documents' => DocumentController::class,
    'keywords' => KeywordController::class,
];

Route::get('/articles/recent', [ArticleController::class, 'recent']);
Route::get('/activities/recent', [ActivityController::class, 'recent']);

foreach($resources as $uri => $controller) {
    Route::apiResource($uri, $controller)->only(['index', 'show']);
}

Route::middleware('auth:sanctum')->group(function () use ($resources) {
    foreach($resources as $uri => $controller) {
        Route::apiResource($uri, $controller)->only(['store', 'update', 'destroy']);
    }

    Route::apiResource('admins', AdminController::class);
});
