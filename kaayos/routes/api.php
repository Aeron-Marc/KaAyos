<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Register API routes here. These routes are automatically prefixed with
| "/api" and assigned the "api" middleware group by Laravel.
|
*/

Route::middleware('auth:sanctum')->get('/user', function ($request) {
    return $request->user();
});
