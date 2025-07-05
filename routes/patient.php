<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PatientController;


Route::post(uri:'/create', action: [PatientController::class,'create']);
Route::post(uri:'/update', action: [PatientController::class,'update']);
Route::DELETE(uri:'/delete/{patient_id}', action: [PatientController::class,'delete']);
Route::GET(uri:'/get/{patient_id}', action: [PatientController::class,'get']);
Route::GET(uri:'/getAll', action: [PatientController::class,'getAll']);


