<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportController;


Route::post('/upload/{patient_id}', [ReportController::class, 'uploadReport']);