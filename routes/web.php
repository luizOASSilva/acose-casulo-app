<?php

use Illuminate\Support\Facades\Route;

Route::get('/{any}', function () {
    return response()->json(['message' => 'Not Found'], 404);
})->where('any', '.*');
