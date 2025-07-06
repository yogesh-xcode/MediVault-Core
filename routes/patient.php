<?php

use Illuminate\Support\Facades\Route;
<<<<<<< HEAD

use App\Http\Controllers\PatientController;
use App\Http\Middleware\ValidateUserToken;

Route::middleware(ValidateUserToken::class)->controller(PatientController::class)->group(function () {
    Route::post('/', 'create');
    Route::get('/', 'all');
    Route::get('/{patient_id}', 'retrieve');
    Route::patch('/{patient_id}', 'update');
    Route::delete('/{patient_id}', 'remove');
});
=======
use App\Http\Controllers\PatientController;


Route::post(uri:'/create', action: [PatientController::class,'create']);
Route::post(uri:'/update', action: [PatientController::class,'update']);
Route::DELETE(uri:'/delete/{patient_id}', action: [PatientController::class,'delete']);
Route::GET(uri:'/get/{patient_id}', action: [PatientController::class,'get']);
Route::GET(uri:'/getAll', action: [PatientController::class,'getAll']);


>>>>>>> dev
