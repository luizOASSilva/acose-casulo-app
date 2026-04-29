<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

$resources = [
    'articles' => ArticleController::class,
    'posts' => PostController::class,
    'documents' => DocumentController::class,
    'keywords' => KeywordController::class,
];

foreach($resources as $uri => $controller) {
    Route::apiResource($uri, $controller)->only(['index', 'show']);
}

Route::middleware('auth:sanctum')->group(function () use ($resources) {
    foreach($resources as $uri => $controller) {
        Route::apiResource($uri, $controller)->only(['store', 'update', 'destroy']);
    }

    Route::apiResource('admins', AdminController::class);
});
