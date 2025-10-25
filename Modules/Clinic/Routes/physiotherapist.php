<?php 
use Illuminate\Support\Facades\Route;
use Modules\Clinic\Http\Controllers\Physiotherapist\{
    PrescriptionController,
    TherapySessionNoteController

};

    Route::resource('prescription', PrescriptionController::class);
    Route::resource('session-note', TherapySessionNoteController::class);

    Route::controller(PrescriptionController::class)->group(function (){
        Route::get('/create-new-prescription/{id}', 'createNewPrescription'); 
    });
