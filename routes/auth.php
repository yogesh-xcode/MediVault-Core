<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Middleware\ValidateUserToken;

use function Pest\Laravel\withMiddleware;

Route::get(uri: '/sec-check', action: function (): array {
    return [
        "response" => "ok"
    ];
})->middleware(middleware: ValidateUserToken::class);

Route::post(uri: '/register', action: [AuthController::class, 'register']);
Route::post(uri: '/login', action: [AuthController::class, 'login']);
