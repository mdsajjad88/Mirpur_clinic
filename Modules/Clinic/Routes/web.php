<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;
use Modules\Clinic\Http\Controllers\{
    ActivityLogController,
    AgeController,
    AgentCommissionController,
    AgentDetailsController,
    AgentMappingController,
    AgentProfileController,
    AllAppointmentController,
    AppReportController,
    AppRescheduleController,
    DoctorScheduleController,
    NewDoctorController,
    NewTestController,
    NewTherapyController,
    NextVisitController,
    PastDoctorController,
    PastTherapyController,
    PatientController,
    ProviderController,
    SubsPatientController,
    TherapyScheduleController,
    AbandonReportController,
    AppTestListController,
    ChartController,
    ClinicBrandController,
    ClinicStoreController,
    ConsultantPaymentController,
    CrmCallHistoriesController,
    DiscountController,
    DiseasesController,
    FollowUpCallListController,
    IncomeController,
    MedicalTestController,
    MemosController,
    PayReportController,
    PrescriptionsController,
    RefundController,
    ServiceReportController,
    SubscribeReportController,
    SubsPaymentController,
    TodayPayController,
    InstallController,
    PatientPayForController,
    PatientPaymentController,
    PatientProfileController,
    ClinicSellController,
    TestController,
    ClinicProductController,
    TherapyController,
    ClinicCategoryController,
    ClinicTransactionController,
    ClinicPosController,
    ClinicNotificationController,
    ClinicReferenceController,
    DoctorConsultationController,
    IPDController,
    BillReturnController,
    ClinicController,
    OldMedicineController,
    SessionController,
    DiseaseCategoryController,
    DosageController,
    CallCenterFeedbackController,
    FeedbackRoleController,
    FeedbackQuestionController,
    FeedbackAnswerController,
    SurveyTypeController,
    InvestigationReportController,
    ContactMargeController,
    DemandReportController,
    DoctoreKPIController,
    FoodGuidlineController,
    MedicineFulfillmentReportController,
    PrescriptionReportController,
    SeminarPatientInfoController,
    ServiceComparisonReportController,
    StockOutAnalysisReportController,
    TherapyDemandReportController
};
use Modules\Clinic\Http\Controllers\doctor\{
    DoctorController,
    DoctorSlotController,
    DashboardController,
    DoctorAdviceController,
    MedicineUseController,
    MedicineMealController,
    InvestigationController,
    ChiefComplainController,
    DurationController,
    FrequencyController
};
use Modules\Clinic\Http\Controllers\setting\SettingController;
use Modules\Clinic\Http\Controllers\Survey\IntakeFormController;
use Modules\Clinic\Http\Controllers\report\{
    TestReportController,
    TestSellReportController,
};
use Modules\Clinic\Http\Controllers\nutritionist\{NutritionistVisitController, MealTimeController};
use Ramsey\Uuid\Guid\Guid;

// Routes that do NOT require login
Route::middleware(['setData'])->prefix('clinic')->group(function () {
    Route::get('/token', [ClinicController::class, 'token'])->name('clinic.token');
    Route::post('/sl/update-call-status', [AllAppointmentController::class, 'updateCallStatus'])->name('update.call.status');
    Route::get('/sl/get-calling-status', [AllAppointmentController::class, 'getCallingStatus']);
});

Route::middleware('AdminSidebarMenu', 'ClinicSidebarMenu', 'CheckUserLogin', 'auth', 'MirpurSidebar')->group(function () {

    Route::prefix('clinic')->group(function () {
        Route::get('/', 'ClinicController@index')->name('clinic.dashboard');
        Route::get('/get-totals', [ClinicController::class, 'getTotals'])->name('clinic.gettotals');
        Route::get('/get-sale-report', [ClinicController::class, 'getDueSaleReport'])->name('clinic.getDueSaleReport');
        Route::get('/token', [ClinicController::class, 'token'])->name('clinic.token');
        Route::get('install', [InstallController::class, 'index']);
        Route::post('install', [InstallController::class, 'install']);
        Route::get('install/uninstall', [\Modules\Clinic\Http\Controllers\InstallController::class, 'uninstall']);
        Route::get('install/update', [\Modules\Clinic\Http\Controllers\InstallController::class, 'update']);
    });
    Route::prefix('survey')->group(base_path('Modules/Clinic/Routes/survey.php'));
    Route::prefix('physiotherapist')->group(base_path('Modules/Clinic/Routes/physiotherapist.php'));

    Route::resources([
        'clinic-sell' => ClinicSellController::class,
        'bill-return' => BillReturnController::class,
        'doctor-schedule' => DoctorScheduleController::class,
        'therapy-schedule' => TherapyScheduleController::class,
        'provider' => ProviderController::class,
        'patients' => PatientController::class,
        'subs-patients' => SubsPatientController::class,
        'all-appointment' => AllAppointmentController::class,
        'new-doctor' => NewDoctorController::class,
        'past-doctor' => PastDoctorController::class,
        'new-test' => NewTestController::class,
        'new-therapy' => NewTherapyController::class,
        'next-visit' => NextVisitController::class,
        'past-therapy' => PastTherapyController::class,
        'appointment-report' => AppReportController::class,
        'abandon-report' => AbandonReportController::class,
        'app-reschedule' => AppRescheduleController::class,
        'test-list' => AppTestListController::class,
        'call-histories' => CrmCallHistoriesController::class,
        'follow-up-call-list' => FollowUpCallListController::class,
        'prescriptions' => PrescriptionsController::class,
        'clinic-store' => ClinicStoreController::class,
        'memos' => MemosController::class,
        'subs-payment' => SubsPaymentController::class,
        'agent-profile' => AgentProfileController::class,
        'agent-mapping' => AgentMappingController::class,
        'agent-details' => AgentDetailsController::class,
        'subs-report' => SubscribeReportController::class,
        'agent-commission' => AgentCommissionController::class,
        'service-report' => ServiceReportController::class,
        'consultant' => ConsultantPaymentController::class,
        'pay-report' => PayReportController::class,
        'today-pay' => TodayPayController::class,
        'refund' => RefundController::class,
        'activity-log' => ActivityLogController::class,
        'graph-chart' => ChartController::class,
        'income' => IncomeController::class,
        'medical-report' => MedicalTestController::class,
        'clinic-diseases' => DiseasesController::class,
        'age-report' => AgeController::class,
        'patient-payment' => PatientPaymentController::class,
        'clinic-doctor' => DoctorController::class,
        'doctor-sloot' => DoctorSlotController::class,
        'patient-profiles' => PatientProfileController::class,
        'clinic-test' => TestController::class,
        'clinic-therapy' => TherapyController::class,
        'clinic-category' => ClinicCategoryController::class,
        'clinic-transaction' => ClinicTransactionController::class,
        'clinic-pos' => ClinicPosController::class,
        'clinic-notification' => ClinicNotificationController::class,
        'clinic-settings' => SettingController::class,
        'test-report' => TestReportController::class,
        'test-sell-report' => TestSellReportController::class,
        'clinic-reference' => ClinicReferenceController::class,
        'doctor-consultation' => DoctorConsultationController::class,
        'clinic-ipd' => IPDController::class,
        'clinic-brand' => ClinicBrandController::class,
        'patient-old-medicine' => OldMedicineController::class,
        'doctor-dashboard' => DashboardController::class,
        'doctor-advice' => DoctorAdviceController::class,
        'medicine-use' => MedicineUseController::class,
        'medicine-meal' => MedicineMealController::class,
        'doctor-investigation' => InvestigationController::class,
        'therapy-frequency' => FrequencyController::class,
        'chief-complaint' => ChiefComplainController::class,
        'medicine-durations' => DurationController::class,
        'session-info' => SessionController::class,
        'medicine-dosage' => DosageController::class,
        'survey-types' => SurveyTypeController::class,
        'call-center-feedback' => CallCenterFeedbackController::class,
        'feedback-role' => FeedbackRoleController::class,
        'feedback-question' => FeedbackQuestionController::class,
        'feedback-answer' => FeedbackAnswerController::class,
        'investigation-report' => InvestigationReportController::class,
        'patient-pay-for' => PatientPayForController::class,
        'nutritionist-visit' => NutritionistVisitController::class,
        'food-guidline' => FoodGuidlineController::class,
        'seminar-patient-info' => SeminarPatientInfoController::class,

    ]);

    Route::get('medical-test-sell', [MedicalTestController::class, 'testSellList']);

    Route::get('age-graph', [AgeController::class, 'index2']);
    Route::get('follow-up-report', [FollowUpCallListController::class, 'index2']);
    Route::get('doctor-profile/{id}', [ProviderController::class, 'profile'])->name('doctor.profile.info');

    Route::get('peyment-report-get', [PayReportController::class, 'getPayReport'])->name('pay.report.get');

    Route::controller(NewDoctorController::class)->group(function () {
        Route::get('appointment/details', 'appointmentDetails')->name('appointment.details');
        Route::get('appointment/confirmation', 'appointmentConfirm')->name('appointment.confirm');
        Route::get('appointment/number', 'appointmentNumber')->name('appointment.number');
        Route::get('appointment/doctor/{id}', 'doctorAppointment')->name('appointment.doctor');
        Route::get('appointment/change-call-status/{id}', 'changeCallStatus')->name('appointment.change.call.status');
        Route::put('appointment/update-call-status/{id}', 'updateCallStatus')->name('appointment.update.call.status');
    });

    Route::controller(PatientPaymentController::class)->group(function () {
        Route::get('/patient/view-payment/{payment_id}', 'viewPayment');
        Route::get('patient/pay-due/{contact_id}', 'getPayContactDue')->name('patient.payment.due');

    });
    Route::controller(SessionController::class)->group(function () {
        Route::get('/show/session/details/{id}', 'getSessionDetails')->name('patient.session.details');
    });
    Route::controller(PatientController::class)->group(function () {
        Route::get('patient-profile/{id}', 'profile')->name('patient.profile');
        Route::get('patient/clinic_led', 'getPatientClinic')->name('patient.patient_clinic_led');
        Route::get('patient/prescriptions', 'getPatientPrescription')->name('patient.prescriptions');
        Route::get('patient-call-detials', 'callHistories')->name('patient.call_details');
        Route::get('patient/subscription', 'AddSubscription')->name('patient.subscription');
        Route::get('patient/payment/{contact_id}', 'getContactPayments')->name('patient.payment');
        Route::get('ledger/pdf', 'getLedger')->name('patient.ledger.pdf');
        Route::get('/patient/update-status/{id}', 'updateStatus')->name('patient.update.status');
        Route::get('/patient/reference/{id}', 'getReference')->name('patient.reference');
        Route::get('/get/clinic/customer', 'getClinicCustomer')->name('get.clinic.customer');
        Route::get('/patient/only-name/edit/{id}', 'patientOnlyNameEdit')->name('patient.only.name.edit');
        Route::put('/patient/only-name/update/{id}', 'updatePatientName')->name('patient.only.name.update');
        Route::get('/patient/mobile/update/modal/{id}', 'getMobileUpdateModal')->name('patient.only.name.update');
    });
    Route::controller(SubsPatientController::class)->group(function () {
       Route::get('/subs/end/date/edit/{id}', 'endDateEdit')->name('subs.end.date.edit'); 
       Route::post('/subs/end/date/update', 'endDateUpdate')->name('subs.end.date.update'); 

    });
    Route::controller(ServiceReportController::class)->group(function () {
       Route::get('/test/sell/report/by/category', 'testSellReportByCategory')->name('test.sell.report.by.category'); 

    });
    Route::controller(PatientPayForController::class)->group(function () {
        Route::get('patient/sell/{id}', 'PatientSell')->name('patient.sell');
        Route::get('patient/sell/return', 'PatientSellReturn')->name('patient.sell.return');
        Route::get('patient/purchase', 'PatientPurchase')->name('patient.purchase');
        Route::get('patient/purchase/return', 'PatientPurchaseReturn')->name('patient.purchase.return');
        Route::get('patient/deleteMedia/{id}', 'deleteMedia')->name('patient.deleteMedia');

        // here to new process
        Route::get('patient/transaction/interface/{id}', 'patientTransactions')->name('patient.transaction.interface');
        Route::get('patient/appointment/info/{id}', 'patientAppointmentDetails')->name('patient.appointment.details');
    });
    Route::controller(SubsPaymentController::class)->group(function () {
        Route::get('prima/transaction/details/{patient_id}/{subscription_id}', 'primaTransactionDetails')->name('prima.transaction.details');
    });
    Route::controller(ProviderController::class)->group(function () {
        Route::get('/provider/{id}/business-days', 'getBusinessDaysData')->name('provider.businessDays');
        Route::post('/doctors/check-email-id', 'checkEmailId')->name('doctors.checkEmailId');
        Route::get('/doctors/updateStatus/{id}', 'updateStatus')->name('doctors.updateStatus');
        Route::get('/business-day/edit/{id}', 'businessDayEdit')->name('doctors.business.day.edit');
        Route::get('/business-day/create/{id}', 'businessDayCreate')->name('doctors.business.day.create');
        Route::post('/business-day/store', 'businessDayStore')->name('doctors.business.day.store');
        Route::post('/business-day/check', 'checkBusinessDay')->name('doctors.checkBusinessDay');
        Route::delete('/business-day/delete/{id}', 'businessDayDelete')->name('doctors.businessDayDelete');
        Route::post('/doctor/availability/status/update','updateDoctorStatus')->name('doctor.updateStatus');
    });
    Route::put('business-day/update/{id}', [ProviderController::class, 'businessDayUpdate'])->name('businessDayUpdate');

    Route::controller(DoctorController::class)->group(function () {
        Route::get('add-degrees/{id}', 'addDegrees')->name('doctors.add.degrees');
        Route::post('degree/name/check', 'checkUniqueName')->name('degree.checkUniqueName');
        Route::get('specilities/check-term', 'checkSpecialitiesName')->name('specialities.checkSpecialitiesName');
        Route::post('store-degrees', 'storeDegrees')->name('doctors.store.degrees');
        Route::delete('delete/degree/{id}', 'deleteDegrees')->name('doctors.degrees.delete');
        Route::delete('delete/specilities/{id}', 'deleteSpecilities')->name('doctors.specilities.delete');
        Route::get('edit/degree/{id}', 'degreeEdit')->name('doctors.degrees.edit');
        Route::get('edit/specilities/{id}', 'specilitiesEdit')->name('doctors.specilities.edit');
        Route::put('update/degree/{id}', 'updateDegrees')->name('doctors.degrees.update');
        Route::put('update/specilities/{id}', 'updateSpecialities')->name('doctors.specilities.update');
        Route::get('get/doctor/degrees/{id}', 'getDegrees')->name('get.doctor.degrees');
        Route::get('get/doctor/specilities/{id}', 'getSpecilities')->name('get.doctor.specilities');
        Route::get('add/specilities/{id}', 'addSpecilities')->name('add.specilities');
        Route::post('store/specilities', 'storeSpecialities')->name('store.specilities');
    });
    Route::controller(DoctorSlotController::class)->group(function () {
        Route::get('/get/daily/sloot/data/{id}', 'getDailySlootData')->name('doctors.getDailySlootData');
        Route::get('doctor/daily/sloot/{id}', 'dailySlotGenerate')->name('dailySlotGenerate.view');
        Route::delete('delte/monthly/sloot/{month}/{id}', 'deleteMonthlySloot')->name('delete.monthly.sloot');
        Route::post('store/daily/sloot', 'storeDailySloot')->name('store.daily.loot');
        Route::get('doctor/monthly/sloot/{id}/{month}', 'monthlySlotGenerate')->name('monthlySlotGenerate.view');
        Route::get('get/monthly/sloot/{id}', 'getMonthlySlootData')->name('doctors.getMonthlySlootData');
        Route::get('doctor/ViewSlot/{id}/{month}', 'ViewSlot')->name('ViewSlot.view');
        Route::get('doctor/slotInfo/{id}/{date}/{serial?}', 'slotInfo')->name('doctor.slotInfo');
        Route::delete('/provider.slot.delete/{id}', 'individualSlotDelete')->name('provider.slot.delete');
        Route::put('/doctor/break/time/update/{id}', 'updateBreakTimeSetting')->name('provider.break.time.update');
        Route::get('/date-wise-get-doctor/{date}', 'getDateWiseDoctor')->name('date.wise.get.doctor');
    });
    Route::controller(PatientProfileController::class)->group(function () {
        Route::get('/patients/profile/info/{id}/{date?}', 'profileInfo')->name('patient.profile.info');
        Route::get('/generate/membership/card/{id}', 'generateMemberShipCard')->name('generate.patient.membership.card');
        Route::get('/print/membership/card/{id}', 'printMemberShipCard')->name('print.membership.card');
        Route::post('/membership/card/transaction/{id}', 'memebershipCardTransaction')->name('membership.card.transaction');
        Route::get('/check-patient-prescription/{id}', 'checkPrescription');
    });
    Route::controller(ContactMargeController::class)->group(function () {
        Route::post('/contacts/mark-inactive', 'contactMarkInactive')->name('contacts.markInactive');
        Route::get('/logs/{date?}', 'logShow')->name('logs.show');
    });
    Route::controller(PrescriptionsController::class)->group(function () {
        Route::get('store/dosage/view', 'storeDosageView')->name('patient.storeDosageView');
        Route::post('store/dosage/prescription', 'storeDosage')->name('patient.storeDosage');
        Route::get('add/to/doctor/prescription/{id}', 'showInDoctor')->name('add.to.doctor.prescription');
        Route::get('/prescription/print/view/{id}', 'printView')->name('prescription.print.view');
        Route::post('/prescription/temp/store', 'storeTemplate')->name('prescription.temp.store');
        Route::get('/check/template/exists/{id}', 'templateExistOrNot')->name('check.template.exist');
        Route::get('/prescription/template/data/{appointment}/{prescription}', 'getTemplateData')->name('prescription.template.data.get');
        Route::get('/fit/convert/to/cm', 'fitConvertCM')->name('fit.convert.to.cm');
        Route::get('/doctor-profile-summary', 'DoctorProfileSummary')->name('doctor.profile.summary');
        Route::get('/doctor-doctors-comparativeKPIReport', 'DoctorsComparativeKPIReport')->name('doctor_comparative_kpi');
        Route::get('/next-visit-data', 'NextVisitData')->name('next.visit.data');
        Route::get('/next-visit-data/edit-call-status/{id}', 'editCallStatus')->name('edit.call.status');
        Route::POST('/next-visit-data/update-call-status', 'updateCallStatus')->name('update.call.status');
        Route::get('/prescriptions/missing-test-details/{testName}', 'missingTestDetails');
        Route::get('/prescriptions/missing-medicine-details/{medicineName}', 'missingMedicineDetails');
        Route::get('/prescriptions/missing-therapy-details/{therapyName}', 'missingTherapyDetails');
    });

    Route::get('doctor-kpi-performance', [DoctoreKPIController::class, 'DoctorKPIPerformanceReport'])
    ->name('doctor_kpi_performance');

    Route::get('/medicine-fulfillment-report', [MedicineFulfillmentReportController::class, 'medicineFulfillmentReport'])
    ->name('clinic.medicine.fulfillment.report');

    Route::get('/stock-out-analysis-report', [StockOutAnalysisReportController::class, 'stockOutAnalysisReport'])
        ->name('clinic.stock.out.analysis.report');

    Route::get('/demand-report', [DemandReportController::class, 'demandReport'])
        ->name('clinic.demand.report');

    Route::get('/prescription-fulfillment-report', [PrescriptionReportController::class,'prescriptionFulfillmentReport'])
            ->name('clinic.prescription.fulfillment.report');
        
    Route::get('/prescription-details/{id}', [PrescriptionReportController::class,'getPrescriptionDetails'])
            ->name('clinic.prescription.details');

    Route::get('/therapy-demand-report', [TherapyDemandReportController::class, 'demandReport'])
        ->name('clinic.therapy.demand.report');

    Route::get('/service-comparison-report', [ServiceComparisonReportController::class, 'serviceComparisonReport'])
        ->name('clinic.service.comparison.report');

    Route::controller(TestController::class)->group(function () {
        Route::get('add/selling/prices/{id}', 'addSellingPrices')->name('add.selling.prices');
        Route::get('/clinic/test/view/{id}', 'view')->name('clinic.test.view');
        Route::get('/clinic/test/activate/{id}', 'activate')->name('clinic.test.activate');
        Route::get('/test/category/wise/report/details/{id}/{start_date}/{end_date}', 'testCategoryReportDetails')->name('test.category.report.details.individual');
    });
    Route::controller(TherapyController::class)->group(function () {
        Route::get('/therapy/add/selling/prices/{id}', 'addSellingPrices')->name('add.therapy.selling.prices');
        Route::get('/clinic/therapy/view/{id}', 'view')->name('clinic.therapy.view');
        Route::get('/clinic/therapy/activate/{id}', 'activate')->name('clinic.therapy.activate');
        Route::get('/clinic/therapy/sell-grouped-report', 'todayTherapySellGroupedReport')->name('get.today.therapy.sell.report');
        Route::get('/therapy-selection-report-details', 'therapySelectionReport')->name('therapy.selection.report.details');
        Route::get('/therapy-selection-report-details-individual/{variation_id}/{start_date}/{end_date}', 'therapySelectionReportDetails')
     ->name('therapy.selection.report.details.individual');

    
    });
    Route::controller(ClinicProductController::class)->group(function () {
        Route::get('/get/search/product/', 'getProducts')->name('get.search.product');
        Route::get('/clinic/quick/add/product/', 'quickAdd')->name('quick.add.product.clinic');
        Route::get('/clinic/product/stock/history/{id}', 'productStockHistory')->name('clinic.product.stock.history');
        Route::get('/show/billing/options', 'showBillingOptions')->name('clinic.show.billing.options');
        Route::get('/product/show/in/doctor', 'productListInApi')->name('doctor.show.api.product.list');
        Route::get('/drug/show/in/doctor', 'bdDrugListInApi')->name('drug.list.show.using.api');
        Route::get('/drug/info/show/using/api/{id}', 'drugInfoShow')->name('drug.info.show.using.api');
        Route::get('/pharmacy-product-stock-expire-report', 'stockExpireReport')->name('product.stock.expire.report');
    });
    Route::controller(ClinicSellController::class)->group(function () {
        Route::get('/get-variations-by-product', 'getVariationsByProduct')->name('get.variations.by.product');
        Route::get('/clinic/session-details-report', 'sessionDetailsReport')->name('clinic.session.details.report');
        Route::get('/clinic/register/', 'register')->name('clinic.register');
        Route::post('/clinic/register_store/', 'registerStore')->name('clinic.register_store');
        Route::get('/clinic/sells/pos/get_product_row/{variation_id}/{location_id}/{status}', 'getProductRow')->name('get.sell.pos.search.product');
        Route::get('/clinic/reports/product-sell-grouped-report', 'todayProductSellGroupedReport')->name('get.today.sell.product');
        Route::get('/clinic-sell-payment-report', 'paymentReport')->name('payment_report');
        Route::get('/clinic/draft/sell/list', 'getDraftDatables')->name('clinicDraftSell');
        Route::get('/clinic/sells/{sell}/draftEdit', 'draftEdit')->name('clinic.sells.draftEdit');
        Route::get('/clinic/view-media/{model_id}', 'viewMedia');
        Route::get('/sell-by-type-with-date/{id}', 'sellByDate');
    });

    Route::controller(TestReportController::class)->group(function () {
        Route::get('/test/today/sell/invoice', 'todayInvoiceReport')->name('today.invoice.report');
        Route::get('/clinic/test/register/report', 'testRegisterReport')->name('get.test.register.report');
    });

    Route::controller(TestSellReportController::class)->group(function () {
        Route::get('/clinic/test/today/sell/report', 'todayTestSellReport')->name('get.today.sell.report.test');
        Route::get('/clinic/test/wise/sell/report', 'totalTestReport')->name('get.test.all.report');
    });

    Route::controller(SettingController::class)->group(function () {
        Route::get('/setting1', 'setting1')->name('setting1');
        Route::get('/setting2', 'setting2')->name('setting2');
    });
    Route::controller(ClinicCategoryController::class)->group(function () {
        Route::get('/clinic-category-ajax-index-page', 'getCategoryIndexPage')->name('clinic.category.index');
        Route::get('/clinic-category-rate', 'getRate')->name('clinic.category.rate');
        Route::get('/get/billing/sub_type/category/{type}', 'billingTypeCategory')->name('get.billing.sub_type.category');
    });
    Route::controller(ClinicTransactionController::class)->group(function () {
        Route::get('/clinic/payments/add_payment/{transaction_id}', 'addPayment')->name('clinic.add.payment');
        Route::get('/clinic/payments/view-payment/{payment_id}', 'viewPayment')->name('clinic.view.payment.transaction');
    });
    Route::controller(ClinicPosController::class)->group(function () {
        Route::get('/clinic/sells/invoice-url/{id}', 'showInvoiceUrl');
    });
    Route::controller(ClinicNotificationController::class)->group(function () {
        Route::get('/clinic/notification/get-template/{transaction_id}/{template_for}', 'getTemplate');

    });
    Route::controller(AllAppointmentController::class)->group(function () {
        Route::get('/get/request/to/final/{id}', 'getRequestToFinal');
        Route::post('/update/request/to/final/appointment', 'updateRequestToFinal')->name('updateRequestToFinal');
        Route::post('appointments/{id}/cancel',  'cancelAppointment')->name('appointments.cancel');
        Route::get('/get/sl-status/{id}', 'getSlStatus');
        Route::get('/final/therapy/appointment/{id}', 'therapyAppointmentFinal');
        Route::post('/request/to/final/therapy/appointment', 'requestToFinalTherapyAppointment');
        Route::post('/change/sl-status/', 'changeSlStatus')->name('change.sl.status');
        Route::get('/therapy-appointment', 'therapyAppointment')->name('therapy.appointment');
        Route::post('/{id}/send-meeting-link-sms', 'sendMeetingLinkSms')->name('sendMeetingLinkSms');
    });
    Route::controller(ChiefComplainController::class)->group(function () {
        Route::get('/get/search/complain/', 'getSearchComplain');
        Route::get('/get/instruction/info', 'getInstructionInfo');
        Route::get('/get/meal_time/info', 'getMealTimeInfo');

    });
    Route::controller(DoctorAdviceController::class)->group(function () {
        Route::get('/get/doctor/advice/', 'getAdvices');

    });
    Route::controller(FeedbackQuestionController::class)->group(function () {
        Route::post('/is/show/question/in/form/{id}', 'isShowQuestionInForm')->name('is.show.question.in.form');
        Route::post('/update/feedback/question/position', 'updateQuestionPosition')->name('update.feedback.question.position');
        Route::get('/survey-type-date-range/{id}','getSurveyTypeDateRange')->name('survey.type.date.range');



    });

    Route::controller(InvestigationReportController::class)->group(function () {
        Route::post('/invoices/upload-pdf', 'uploadPdf')->name('invoices.upload-pdf');
        Route::delete('/pdf_report_delete/{id}', 'deletePdf')->name('pdf_report_delete');
    });

    //Sell return
    Route::get('/validate-invoice-to-return/{invoice_no}', [BillReturnController::class, 'validateInvoiceToReturn']);
    Route::get('/bill-return/get-product-row', [BillReturnController::class, 'getProductRow']);
    Route::get('/bill-return/print/{id}', [BillReturnController::class, 'printInvoice']);
    Route::get('/bill-return/add/{id}', [BillReturnController::class, 'add']);
    Route::post('/bill-return/store-with-payment', [BillReturnController::class, 'storeWithPayment']);
    Route::get('/today-bill-return', [\Modules\Clinic\Http\Controllers\BillReturnController::class, 'todaySellReturn']);

    Route::get('/reports/clinic-register-report', [ReportController::class, 'getClinicRegisterReport']);
    Route::get('/get/intake/form/data/{id?}', [IntakeFormController::class, 'getFormData'])->name('get.intake.form.data');
    Route::resource('duplicate-contact-marge', ContactMargeController::class);


    Route::controller(NutritionistVisitController::class)->group(function () {
        Route::get('/create-nu-prescription/{id}','createNuPrescription')->name('create.nu.prescription');
        Route::get('/nutritionist-visit/print/{id}','print')->name('nutritionist-visit.print');
        Route::get('/load/old/prescription/{presId}/{nupresId}','loadOldPrescription')->name('load.old.prescription');
        Route::get('/nutritionist-second-index','secondIndex');
        Route::get('/create-nu-new-prescription/{id}','createNewPrescription');
        Route::post('/store-new-nu-prescription','storeNewPrescription');
        Route::get('/print/new/prescription/{id}','newPrintView')->name('new.prescription.print.view');
    });
    Route::controller(FoodGuidlineController::class)->group(function () {
        Route::get('/get/guidelines-info/{id}', 'getGuidelinesInfo');
    });
    Route::controller(SeminarPatientInfoController::class)->group(function () {
        Route::post('/import/csv/seminar/data', 'importCsv');
    });
    Route::resource('meal-time', MealTimeController::class);

    Route::post('/{id}/set-kpi-role', [FeedbackRoleController::class, 'setKpiRole'])->name('setKpiRole');

});