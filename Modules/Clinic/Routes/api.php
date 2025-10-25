<?php

use Illuminate\Http\Request;
use Modules\Clinic\Http\Controllers\Api\CustomerReportApiController;
use Modules\Clinic\Http\Controllers\Api\PrescriptionsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/clinic', function (Request $request) {
    return $request->user();
});

Route::get('customer/reports', [CustomerReportApiController::class, 'getReports']);
Route::get('prescriptions/by-details', [PrescriptionsController::class, 'getByDetails'])->name('prescriptions.by-details');