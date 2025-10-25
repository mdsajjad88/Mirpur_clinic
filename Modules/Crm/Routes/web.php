<?php

// use App\Http\Controllers\Modules;
// use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Route;
use Modules\Crm\Http\Controllers\{CallCampaignController, CallLogController, FacebookLeadController, SmsController};

Route::middleware('web', 'authh', 'SetSessionData', 'auth', 'language', 'timezone', 'ContactSidebarMenu', 'CheckContactLogin', 'ClinicSidebarMenu', 'MirpurSidebar')->prefix('contact')->group(function () {
    Route::resource('contact-dashboard', 'Modules\Crm\Http\Controllers\DashboardController');
    Route::get('contact-profile', [Modules\Crm\Http\Controllers\ManageProfileController::class, 'getProfile']);
    Route::post('contact-password-update', [Modules\Crm\Http\Controllers\ManageProfileController::class, 'updatePassword']);
    Route::post('contact-profile-update', [Modules\Crm\Http\Controllers\ManageProfileController::class, 'updateProfile']);
    Route::get('contact-purchases', [Modules\Crm\Http\Controllers\PurchaseController::class, 'getPurchaseList']);
    Route::get('contact-sells', [Modules\Crm\Http\Controllers\SellController::class, 'getSellList']);
    Route::get('contact-ledger', [Modules\Crm\Http\Controllers\LedgerController::class, 'index']);
    Route::get('contact-get-ledger', [Modules\Crm\Http\Controllers\LedgerController::class, 'getLedger']);
    Route::resource('bookings', 'Modules\Crm\Http\Controllers\ContactBookingController');
    Route::resource('order-request', 'Modules\Crm\Http\Controllers\OrderRequestController');
    Route::get('products/list', [\App\Http\Controllers\ProductController::class, 'getProducts']);
    Route::get('order-request/get_product_row/{variation_id}/{location_id}', [Modules\Crm\Http\Controllers\OrderRequestController::class, 'getProductRow']);
});

Route::middleware('web', 'authh', 'auth', 'SetSessionData', 'language', 'timezone', 'AdminSidebarMenu', 'CheckUserLogin', 'ClinicSidebarMenu', 'MirpurSidebar')->prefix('crm')->group(function () {
    Route::get('commissions', [Modules\Crm\Http\Controllers\ContactLoginController::class, 'commissions']);
    Route::get('all-contacts-login', [Modules\Crm\Http\Controllers\ContactLoginController::class, 'allContactsLoginList']);
    Route::resource('contact-login', 'Modules\Crm\Http\Controllers\ContactLoginController')->except(['show']);
    Route::resource('follow-ups', 'Modules\Crm\Http\Controllers\ScheduleController');
    Route::get('todays-follow-ups', [Modules\Crm\Http\Controllers\ScheduleController::class, 'getTodaysSchedule']);
    Route::get('lead-follow-ups', [Modules\Crm\Http\Controllers\ScheduleController::class, 'getLeadSchedule']);
    Route::get('get-invoices', [Modules\Crm\Http\Controllers\ScheduleController::class, 'getInvoicesForFollowUp']);
    Route::get('get-followup-groups', [Modules\Crm\Http\Controllers\ScheduleController::class, 'getFollowUpGroups']);
    Route::get('follow-ups/{follow_up}/edit-status', [Modules\Crm\Http\Controllers\ScheduleController::class, 'editStatus'])->name('follow-ups.edit_status');
    Route::post('follow-ups/{follow_up}/update-status', [Modules\Crm\Http\Controllers\ScheduleController::class, 'updateStatus'])->name('follow-ups.update_status');
    Route::get('all-users-call-logs', [Modules\Crm\Http\Controllers\CallLogController::class, 'allUsersCallLog']);
    Route::get('call-log/{id}/feedback', [Modules\Crm\Http\Controllers\CallLogController::class, 'showFeedback'])->name('call-log.feedback');

    Route::resource('follow-up-log', 'Modules\Crm\Http\Controllers\ScheduleLogController');
    Route::resource('call-subjects', 'Modules\Crm\Http\Controllers\CrmCallSubjectController');
    Route::resource('call-tags', 'Modules\Crm\Http\Controllers\CallTagController');
    Route::get('install', [Modules\Crm\Http\Controllers\InstallController::class, 'index']);
    Route::post('install', [Modules\Crm\Http\Controllers\InstallController::class, 'install']);
    Route::get('install/uninstall', [Modules\Crm\Http\Controllers\InstallController::class, 'uninstall']);
    Route::get('install/update', [Modules\Crm\Http\Controllers\InstallController::class, 'update']);

    Route::resource('leads', 'Modules\Crm\Http\Controllers\LeadController');
    Route::get('lead/{id}/convert', [Modules\Crm\Http\Controllers\LeadController::class, 'convertToCustomer']);
    Route::get('lead/{id}/post-life-stage', [Modules\Crm\Http\Controllers\LeadController::class, 'postLifeStage']);

    Route::get('camp-wise-data/{id}', [Modules\Crm\Http\Controllers\CampaignController::class, 'showCampWiseModal']);

    Route::post('get-product-group-row', [Modules\Crm\Http\Controllers\CampaignController::class, 'getProductRow']);
    Route::get('{id}/send-campaign-notification', [Modules\Crm\Http\Controllers\CampaignController::class, 'sendNotification']);
    Route::resource('campaigns', 'Modules\Crm\Http\Controllers\CampaignController');
    Route::get('dashboard', [Modules\Crm\Http\Controllers\CrmDashboardController::class, 'index']);
    Route::get('call-subject-summary', [Modules\Crm\Http\Controllers\CrmDashboardController::class, 'getCallSubjectSummary']);
    Route::get('/dashboard/conversion-chart-data', [Modules\Crm\Http\Controllers\CrmDashboardController::class, 'conversionChartData'])->name('dashboard.conversion_chart');
    Route::get('/dashboard/call-subject-chart', [Modules\Crm\Http\Controllers\CrmDashboardController::class, 'getCallSubjectChartData'])->name('dashboard.call_subject_chart');
    Route::get('/calls-per-month-chart', [Modules\Crm\Http\Controllers\CrmDashboardController::class, 'getCallsPerMonthChartData'])->name('dashboard.calls_per_month_chart');
    Route::get('/calls-this-month-daily-chart', [Modules\Crm\Http\Controllers\CrmDashboardController::class, 'getCallsThisMonthDailyChartData'])->name('dashboard.calls_this_month_daily_chart');
    Route::get('/users-call-logs', [\Modules\Crm\Http\Controllers\CrmDashboardController::class, 'getAllUsersCallLogs'])
        ->name('crm.getAllUsersCallLogs');


    Route::get('reports', [Modules\Crm\Http\Controllers\ReportController::class, 'index']);
    Route::get('follow-ups-by-user', [Modules\Crm\Http\Controllers\ReportController::class, 'followUpsByUser']);
    Route::get('follow-ups-by-contact', [Modules\Crm\Http\Controllers\ReportController::class, 'followUpsContact']);
    Route::get('lead-to-customer-report', [Modules\Crm\Http\Controllers\ReportController::class, 'leadToCustomerConversion']);
    Route::get('lead-to-customer-details/{user_id}', [Modules\Crm\Http\Controllers\ReportController::class, 'showLeadToCustomerConversionDetails']);
    Route::get('daily-call-report', [Modules\Crm\Http\Controllers\ReportController::class, 'getDailyCallReport'])->name('crm.daily_call_report');
    Route::resource('call-log', Modules\Crm\Http\Controllers\CallLogController::class);
    Route::post('mass-delete-call-log', [Modules\Crm\Http\Controllers\CallLogController::class, 'massDestroy']);
    Route::get('get/call/log/info/{id}', [Modules\Crm\Http\Controllers\CallLogController::class, 'getCallLogInfo'])->name('get.call.log.info');


    Route::get('/sms', [SmsController::class, 'index'])->name('crm.sms.index');
    Route::get('/sms/create', [SmsController::class, 'create'])->name('crm.sms.create');
    Route::post('/sms/process-csv', [SmsController::class, 'processCsv'])->name('crm.sms.process_csv');
    Route::post('/sms/send', [SmsController::class, 'sendSms'])->name('crm.sms.send');
    Route::get('/sms/{id}', [SmsController::class, 'show'])->name('crm.sms.show');
    Route::get('sms-log-info/{contact_id}', [SmsController::class, 'getSmsLogInfo'])->name('crm.sms_log_info');


    Route::get('edit-proposal-template', [Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'getEdit']);
    Route::post('update-proposal-template', [Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'postEdit']);
    Route::get('view-proposal-template', [Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'getView']);
    Route::get('send-proposal', [Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'send']);
    Route::delete('delete-proposal-media/{id}', [Modules\Crm\Http\Controllers\ProposalTemplateController::class, 'deleteProposalMedia']);
    Route::resource('proposal-template', 'Modules\Crm\Http\Controllers\ProposalTemplateController')->except(['show', 'edit', 'update', 'destroy']);
    Route::resource('proposals', 'Modules\Crm\Http\Controllers\ProposalController')->except(['create', 'edit', 'update', 'destroy']);
    Route::get('settings', [Modules\Crm\Http\Controllers\CrmSettingsController::class, 'index']);
    Route::post('update-settings', [Modules\Crm\Http\Controllers\CrmSettingsController::class, 'updateSettings']);
    Route::get('order-request', [Modules\Crm\Http\Controllers\OrderRequestController::class, 'listOrderRequests']);
    Route::get('b2b-marketplace', [Modules\Crm\Http\Controllers\CrmMarketplaceController::class, 'index']);
    Route::post('save-marketplace', [Modules\Crm\Http\Controllers\CrmMarketplaceController::class, 'save']);
    Route::get('import-leads', [Modules\Crm\Http\Controllers\CrmMarketplaceController::class, 'importLeads']);

    Route::resource('call-campaigns', Modules\Crm\Http\Controllers\CallCampaignController::class);
    Route::controller(CallCampaignController::class)->group(function () {
        Route::get('get-dummy-patient', 'getDummyPatient')->name('get-dummy-patient');
        Route::get('add-campaign-contact', 'addCampaignContact')->name('add.campaign.contact');
        Route::get('marge-contact-campaign/{id}', 'margeContactCampaign')->name('marge.contact.campaign');
        Route::post('/call-campaign/{id}/merge/process', 'mergeProcess')
            ->name('call-campaign.merge.process');
    });
    Route::controller(CallLogController::class)->group(function () {
        Route::get('/get/call/log/info/patient/profile/{id}', 'getCallLogInfoDatatable')->name('get.call.log.info.patient.profile');
    });
    // Import contacts
    Route::post('call-campaigns/{id}/import-contacts', [Modules\Crm\Http\Controllers\CallCampaignController::class, 'importContacts'])->name('call-campaigns.import-contacts');

    // Call handling
    Route::post('call-campaigns/start-call', [Modules\Crm\Http\Controllers\CallCampaignController::class, 'startCall'])->name('call-campaigns.start-call');
    Route::post('call-campaigns/save-call-result', [Modules\Crm\Http\Controllers\CallCampaignController::class, 'saveCallResult'])->name('call-campaigns.save-call-result');
    //ContactFilterController
    Route::resource('contact-filters', Modules\Crm\Http\Controllers\ContactFilterController::class);
    Route::get('/get-filtered-field-data/{id}', [Modules\Crm\Http\Controllers\ContactFilterController::class, 'getFilteredFieldData'])->name('get-filtered-field-data');
});
