<?php

use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
use Modules\Clinic\Http\Controllers\PatientController;
use Modules\Clinic\Http\Controllers\SurveyTypeController;
use Modules\Crm\Http\Controllers\{CallCampaignController, FacebookLeadController};
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
Route::middleware('api-session')->post('contacts', [ContactController::class, 'storeApi']);
Route::middleware('api-session')->get('getCustomers', [ContactController::class, 'getCustomersApi']);
Route::middleware('api-session')->put('update/customer/information/{id}', [PatientController::class, 'updateCustomerInfo']);
Route::middleware('api-session')->get('get/seminar/information/{id?}', [SurveyTypeController::class, 'getSeminarInfo']);
Route::middleware('api-session')->get('get/seminar/disease/', [SurveyTypeController::class, 'getDisease']);
Route::middleware('api-session')->get('get/division/', [SurveyTypeController::class, 'getDivision']);
Route::middleware('api-session')->get('get/district/{id?}', [SurveyTypeController::class, 'getDistrict']);
Route::middleware('api-session')->get('get/upazila/{id?}', [SurveyTypeController::class, 'getUpazila']);
Route::middleware('api-session')->post('/store/seminar/information', [CallCampaignController::class, 'storeSeminarLead']);
Route::middleware('api-session')->get('/update/seminar/payment/status/{invoice}/{tnx}', [CallCampaignController::class, 'updateSeminarPaymentStatus']);
Route::middleware('api-session')->post('/facebook/webhook', [FacebookLeadController::class, 'webhook']);