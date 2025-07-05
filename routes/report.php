<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;


Route::post('/upload/{patient_id}', [ReportController::class, 'uploadReport']);
Route::get('/retrive/{patient_id}/{report_type?}', [ReportController::class, 'retriveReport']);