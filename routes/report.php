<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\ValidateUserToken;

Route::middleware(ValidateUserToken::class)
    ->controller(ReportController::class)
    ->group(function () {
        Route::post('/upload/{patient_id}', 'uploadReport');
        Route::get('/retrive/{patient_id}/{report_type?}', 'retrieveReport');
    });