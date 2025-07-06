<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PatientController;
use App\Http\Middleware\ValidateUserToken;

Route::middleware(ValidateUserToken::class)->controller(PatientController::class)->group(function () {
    Route::post('/', 'create');
    Route::get('/', 'all');
    Route::get('/{patient_id}', 'retrieve');
    Route::patch('/{patient_id}', 'update');
    Route::delete('/{patient_id}', 'remove');
});