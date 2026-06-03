<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider or bootstrap/app.php
| within a group which is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Version 1 Routes
Route::prefix('v1')
    ->group(base_path('routes/api_v1.php'));
