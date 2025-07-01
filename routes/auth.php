<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post(uri: '/register', action: [AuthController::class, 'register']);
Route::post(uri: '/login', action: [AuthController::class, 'login']);
