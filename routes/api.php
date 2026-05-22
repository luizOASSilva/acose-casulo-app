<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\DashboardController;

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentCategoryController;
use App\Http\Controllers\KeywordController;
use App\Http\Controllers\DonationController;
use App\Http\Controllers\TransparencyController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\Settings\SettingController;

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
*/

Route::get('/settings/public', [SettingController::class, 'public']);

Route::get('/articles/recent', [ArticleController::class, 'recent']);
Route::get('/activities/recent', [ActivityController::class, 'recent']);

Route::get('/transparency', [TransparencyController::class, 'index']);
Route::get('/partners', [PartnerController::class, 'index']);

/*
|--------------------------------------------------------------------------
| Curtida Pública de Atividade
|--------------------------------------------------------------------------
| IMPORTANTE:
| Fica antes do apiResource('activities'), porque senão o Laravel pode tentar
| interpretar "like" como parte da rota show.
*/
Route::post('/activities/{activity}/like', [ActivityController::class, 'toggleLike'])
    ->middleware('throttle:30,1');

/*
|--------------------------------------------------------------------------
| Agenda de Atividades - Administrativo
|--------------------------------------------------------------------------
| IMPORTANTE:
| Fica antes do apiResource('activities'), porque senão "schedules" pode ser
| interpretado como parâmetro da rota show.
*/
Route::get('/activities/schedules', [ActivityController::class, 'schedules'])
    ->middleware('auth:admin');

/*
|--------------------------------------------------------------------------
| Listagens Públicas
|--------------------------------------------------------------------------
*/
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

/*
|--------------------------------------------------------------------------
| Fluxo de Doações Público
|--------------------------------------------------------------------------
*/
Route::post('/donations', [DonationController::class, 'store'])
    ->middleware('throttle:10,1');

Route::get('/donations/{id}/status', [DonationController::class, 'status'])
    ->middleware('throttle:120,1');

Route::put('/donations/{id}/pix', [DonationController::class, 'updatePix'])
    ->middleware('throttle:10,1');

/*
|--------------------------------------------------------------------------
| Webhook Mercado Pago
|--------------------------------------------------------------------------
*/
Route::post('/webhook/mercadopago', [DonationController::class, 'webhook']);

/*
|--------------------------------------------------------------------------
| Rotas Privadas (Dashboard e Gestão)
|--------------------------------------------------------------------------
| Protegidas por Cookies HttpOnly via Sanctum.
*/
Route::middleware('auth:admin')->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */
    Route::get('/dashboard', [DashboardController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Perfil e Logout
    |--------------------------------------------------------------------------
    */
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    /*
    |--------------------------------------------------------------------------
    | Gestão de Conteúdo
    |--------------------------------------------------------------------------
    */
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

    Route::apiResource('partners', PartnerController::class)
        ->only(['store', 'update', 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Doações - Administrativo
    |--------------------------------------------------------------------------
    */
    Route::get('/donations', [DonationController::class, 'index']);

    /*
    |--------------------------------------------------------------------------
    | Rotas Exclusivas de Usuário Master
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin.master')->group(function () {
        Route::apiResource('admins', AdminController::class);

        Route::get('/settings', [SettingController::class, 'index']);
        Route::put('/settings', [SettingController::class, 'update']);
        Route::post('/settings/clear-cache', [SettingController::class, 'clearCache']);
    });
});
