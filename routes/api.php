<?php

use Illuminate\Support\Facades\Route;

// Controllers Públicos e de Admin
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\TransparencyController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\Admin\DashboardController;

/*
|--------------------------------------------------------------------------
| Autenticação (Login)
|--------------------------------------------------------------------------
*/
Route::post('/auth/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Rotas Públicas (Site Institucional)
|--------------------------------------------------------------------------
| Estas rotas não exigem login.
*/

// Listagens e Detalhes
Route::get('/articles/recent', [ArticleController::class, 'recent']);
Route::get('/activities/recent', [ActivityController::class, 'recent']);
Route::get('/transparency', [TransparencyController::class, 'index']);
Route::get('/partners', [PartnerController::class, 'index']);

Route::apiResource('articles', ArticleController::class)->only(['index', 'show']);
Route::apiResource('activities', ActivityController::class)->only(['index', 'show']);
Route::apiResource('documents', DocumentController::class)->only(['index', 'show']);
Route::apiResource('keywords', KeywordController::class)->only(['index', 'show']);
Route::apiResource('document-categories', DocumentCategoryController::class)->only(['index', 'show']);

// Fluxo de Doações (Público)
Route::post('/donations', [DonationController::class, 'store'])->middleware('throttle:10,1');
Route::get('/donations/{id}/status', [DonationController::class, 'status'])->middleware('throttle:120,1');
Route::put('/donations/{id}/pix', [DonationController::class, 'updatePix'])->middleware('throttle:10,1');

// Webhook (Importante: Fora do middleware de auth)
Route::post('/webhook/mercadopago', [DonationController::class, 'webhook']);

/*
|--------------------------------------------------------------------------
| Rotas Privadas (Dashboard e Gestão)
|--------------------------------------------------------------------------
| Protegidas por Cookies HttpOnly via Sanctum.
*/
Route::middleware('auth:sanctum')->group(function () {

    // Dashboard - Central de estatísticas
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Perfil e Logout
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Gestão de Administradores
    Route::apiResource('admins', AdminController::class);

    // Gestão de Conteúdo (Escrita)
    Route::apiResource('articles', ArticleController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('activities', ActivityController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('documents', DocumentController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('keywords', KeywordController::class)->only(['store', 'update', 'destroy']);
    Route::apiResource('document-categories', DocumentCategoryController::class)->only(['store', 'update', 'destroy']);

    // Gestão de Parceiros (Dashboard)
    Route::apiResource('partners', PartnerController::class)->except(['index']);

    // Gestão de Doações (Edição/Atualização)
    Route::put('/donations/{id}', [DonationController::class, 'update'])->middleware('throttle:30,1');
});
