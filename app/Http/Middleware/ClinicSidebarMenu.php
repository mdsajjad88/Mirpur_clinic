<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

use App\Utils\ModuleUtil;
use Closure;
use Menu;
use Modules\Clinic\Entities\DoctorProfile;

class ClinicSidebarMenu
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next)
    {
        if ($request->ajax()) {
            return $next($request);
        }
        \App::setLocale(auth()->user()->language);
        Menu::create('clinic-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];

            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];

            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $is_doctor = auth()->user()->hasRole('Doctor#' . session('business.id')) ? true : false;
            //Home
            if ($is_admin) {
                $menu->url(action([\App\Http\Controllers\HomeController::class, 'index']), __('home.home'), ['icon' => 'fa fas fa-home', 'active' => request()->segment(1) == 'home'])->order(5);
                $menu->url(action([\Modules\Clinic\Http\Controllers\ClinicController::class, 'index']), __('clinic::lang.dashboard'), ['icon' => 'fas fa-chart-line', 'active' => request()->segment(1) == 'clinic'])->order(5);
                $menu->url(action([\Modules\Clinic\Http\Controllers\ClinicController::class, 'token']), __('clinic::lang.token'), ['icon' => 'fas fa-bullhorn', 'active' => request()->segment(1) == 'clinic' && request()->segment(2) == 'token'])->order(5);
            }

            $doctor_user_id = DoctorProfile::where('user_id', auth()->user()->id)->first();
            if (!empty($doctor_user_id)) {
                $menu->url(action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'profile'], [$doctor_user_id->id]), __('clinic::lang.profile'), ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'doctor-profile'])->order(5);
            }
            if(auth()->user()->can('prescription_dashboard_show') || $is_doctor){
                $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\DashboardController::class, 'index']), __('clinic::lang.appointment_list'), ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'doctor-dashboard'])->order(5);
            }
            // if wee enable it this time nutritionsit with guidline full process will working correctly some we hidden it 
            // if($is_admin){
            //     $menu->url(action([\Modules\Clinic\Http\Controllers\nutritionist\NutritionistVisitController::class, 'index']), 'NutritionistOrg', ['icon' => 'fa fas fa-stethoscope', 'active' => request()->segment(1) == 'nutritionist-visit'])->order(5);           
            //  }
            if(auth()->user()->can('nutritionist_prescription_create') || auth()->user()->can('nutritionist_prescription_show')){
                $menu->url(action([\Modules\Clinic\Http\Controllers\nutritionist\NutritionistVisitController::class, 'secondIndex']), 'Nutritionist', ['icon' => 'fa fas fa-stethoscope', 'active' => request()->segment(1) == 'nutritionist-second-index' || request()->segment(1) == 'create-nu-new-prescription'])->order(5);
            }
            
            if ($is_admin || $is_doctor || auth()->user()->can('prescription.setting.show')) {
                $menu->dropdown(
                    __('clinic::lang.prescription_settings'),
                    function ($menu) use ($is_admin) {
                        if (auth()->user()->can('doctor.advice.view') || auth()->user()->can('doctor.advice.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'index']), __('clinic::lang.advice'), ['icon' => 'fa fas fa-comment', 'active' => request()->segment(1) == 'doctor-advice'])->order(1);
                        }
                        if (auth()->user()->can('medicine_use.view') || auth()->user()->can('medicine_use.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\MedicineUseController::class, 'index']), __('clinic::lang.medicine_use'), ['icon' => 'fa fas fa-tablets', 'active' => request()->segment(1) == 'medicine-use'])->order(2);
                        }
                        if (auth()->user()->can('medicine_meal.view') || auth()->user()->can('medicine_meal.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\MedicineMealController::class, 'index']).'?type=doctor', __('clinic::lang.medicine_meal'), ['icon' => 'fa fas fa-wine-glass', 'active' => request()->segment(1) == 'medicine-meal' && request()->get('type') == 'doctor'])->order(3);
                        }
                        if (auth()->user()->can('disease.show') || auth()->user()->can('disease.store')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\DiseasesController::class, 'index']) . '?type=doctor_dashboard', __('clinic::lang.chief_complaint'), ['icon' => 'fa fas fa-question', 'active' => request()->segment(1) == 'clinic-diseases' && request()->get('type') == 'doctor_dashboard'])->order(4);
                        }
                        if (auth()->user()->can('investigation.view') || auth()->user()->can('investigation.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\InvestigationController::class, 'index']), __('clinic::lang.investigation'), ['icon' => 'fa fas fa-star', 'active' => request()->segment(1) == 'doctor-investigation'])->order(5);
                        }
                        if (auth()->user()->can('dosage.view') || auth()->user()->can('dosage.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\DosageController::class, 'index']), __('clinic::lang.dosage'), ['icon' => 'fa fas fa-square', 'active' => request()->segment(1) == 'medicine-dosage'])->order(6);
                        }
                        if (auth()->user()->can('medicine_durations.view') || auth()->user()->can('medicine_durations.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\DurationController::class, 'index']), __('clinic::lang.duration'), ['icon' => 'fa fas fa-microscope', 'active' => request()->segment(1) == 'medicine-durations'])->order(7);
                        }
                        if (auth()->user()->can('therapy.frequency.view') || auth()->user()->can('therapy.frequency.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\FrequencyController::class, 'index']), __('clinic::lang.frequency'), ['icon' => 'fa fas fa-wave-square', 'active' => request()->segment(1) == 'therapy-frequency'])->order(8);
                        }
                        if(auth()->user()->can('nu.meal_time.view') || auth()->user()->can('nu.meal_time.create')){
                             $menu->url(action([\Modules\Clinic\Http\Controllers\nutritionist\MealTimeController::class, 'index']), __('clinic::lang.meal_time'), ['active' => request()->segment(1) == 'meal-time'])->order(9);
                        }

                        if (auth()->user()->can('medicine_meal.view') || auth()->user()->can('medicine_meal.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\MedicineMealController::class, 'index']).'?type=nutritionist', __('clinic::lang.instructions'), ['icon' => 'fa fas fa-wine-glass', 'active' => request()->segment(1) == 'medicine-meal' && request()->get('type') == 'nutritionist'])->order(10);
                        }
                        if(auth()->user()->can('food_guidline.view') || auth()->user()->can('food_guidline.create')){
                           $menu->url(action([\Modules\Clinic\Http\Controllers\FoodGuidlineController::class, 'index']), __('clinic::lang.food_guidline'), ['icon' => 'fa fas fa-wine-glass', 'active' => request()->segment(1) == 'food-guidline'])->order(11);
                        }
                    },

                    ['icon' => 'fa fas fa-cog', 'id' => 'cog']
                )->order(10);
                $menu->dropdown(
                    __('clinic::lang.drug_database'),
                    function ($menu) {
                        if (auth()->user()->can('bd_drug.view')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\ClinicProductController::class, 'bdDrugListInApi']), __('clinic::lang.bd_drug_data'), ['icon' => 'fa fas fa-wave-square', 'active' => request()->segment(1) == 'drug' && request()->segment(2) == 'show' && request()->segment(3) == 'in' && request()->segment(4) == 'doctor'])->order(9);
                        }
                        if (auth()->user()->can('pharmacy_drug.view')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\ClinicProductController::class, 'productListInApi']), __('clinic::lang.pharmacy_drug_data'), ['icon' => 'fa fas fa-wave-square', 'active' => request()->segment(1) == 'product' && request()->segment(2) == 'show' && request()->segment(3) == 'in' && request()->segment(4) == 'doctor'])->order(10);
                        }
                        $menu->url(action([\Modules\Clinic\Http\Controllers\ClinicProductController::class, 'stockExpireReport']), __('clinic::lang.stock_expire_report'), ['active' => request()->segment(1) == 'pharmacy-product-stock-expire-report'])->order(11);
                    },
                    ['icon' => 'fa fas fa-tablets', 'id' => 'drugInfo']
                )->order(15);
            }
            if (auth()->user()->can('user.view') || auth()->user()->can('user.create') || auth()->user()->can('roles.view')) {
                $menu->dropdown(
                    __('user.user_management'),
                    function ($sub) {
                        if (auth()->user()->can('user.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ManageUserController::class, 'index']),
                                __('user.users'),
                                ['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'users']
                            );
                        }
                        if (auth()->user()->can('roles.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\RoleController::class, 'index']),
                                __('user.roles'),
                                ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(1) == 'roles']
                            );
                        }
                        if (auth()->user()->can('user.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\SalesCommissionAgentController::class, 'index']),
                                __('lang_v1.sales_commission_agents'),
                                ['icon' => 'fa fas fa-handshake', 'active' => request()->segment(1) == 'sales-commission-agents']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-users']
                )->order(10);
            }
            //Contacts dropdown
            if (auth()->user()->can('clinic.provider.view')) {
                $menu->dropdown(
                    __('clinic::lang.provider'),
                    function ($sub) {
                        if (auth()->user()->can('clinic.provider.view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'index']),
                                __('clinic::lang.doctor_therapist'),
                                ['icon' => 'fa fas fa-star', 'active' => request()->segment(1) == 'provider']
                            );
                        }
                        if (auth()->user()->can('clinic.reference.list')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicReferenceController::class, 'index']),
                                __('clinic::lang.reference_list'),
                                ['icon' => 'fa fas fa-star', 'active' => request()->segment(1) == 'clinic-reference']
                            );
                        }
                        if (auth()->user()->can('reference_doctor.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\Survey\ReferenceDoctorController::class, 'index']),
                                __('clinic::lang.reference_doctor'),
                                ['icon' => 'fa fas fa-star', 'active' => request()->segment(1) == 'survey' && request()->segment(2) == 'reference-doctor']
                            );
                        }
                    },

                    ['icon' => 'fa fas fa-user', 'id' => 'tour_step4']
                )->order(15);
            }

            if (
                auth()->user()->can('clinic.patient.view')
            ) {
                $menu->dropdown(
                    __('clinic::lang.patient'),
                    function ($sub) {
                        if (auth()->user()->can('clinic.patient.view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\PatientController::class, 'index']),
                                __('clinic::lang.patients'),
                                ['icon' => 'fas fa-bed', 'active' => request()->segment(1) == 'patients' && request()->segment(2) == '']
                            );
                        }

                        if (auth()->user()->can('clinic.subs.patient.view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\SubsPatientController::class, 'index']),
                                __('clinic::lang.subscribe_patient'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'subs-patients' && request()->segment(2) == '']
                            );
                        }
                        if (auth()->user()->can('clinic.category.view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicCategoryController::class, 'index']) . '?type=disease',
                                __('clinic::lang.disease_category'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'clinic-category' && request()->get('type') == 'disease']
                            );
                        }
                        if (auth()->user()->can('disease.show')) {

                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\DiseasesController::class, 'index']) . '?type=disease',
                                __('clinic::lang.disease'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'clinic-diseases' && request()->get('type') == 'disease']
                            );
                        }
                        if (auth()->user()->can('patient_old_medicine.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\OldMedicineController::class, 'index']),
                                __('clinic::lang.patient_old_medicine'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'patient-old-medicine']
                            );
                        }
                        if (auth()->user()->can('subscription.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\SessionController::class, 'index']),
                                __('clinic::lang.subscription'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'session-info']
                            );
                        }
                        if (auth()->user()->can('show.prima.subscription')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\SubsPaymentController::class, 'index']),
                                __('clinic::lang.prima_subscription'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'subs-payment']
                            );
                        }
                        $sub->url(
                                action([\App\Http\Controllers\CustomerGroupController::class, 'index']),
                                __('lang_v1.customer_groups'),
                                ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'customer-group']
                            );
                        $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ContactMargeController::class, 'index']),
                                __('clinic::lang.marge_contacts'),
                                ['icon' => 'fa fas fa-code-merge', 'active' => request()->segment(1) == 'duplicate-contact-marge']
                            );
                        $sub->url(
                                action([\Modules\Clinic\Http\Controllers\SeminarPatientInfoController::class, 'index']),
                                __('clinic::lang.seminar_patient_info'),
                                ['icon' => 'fa fas fa-code-merge', 'active' => request()->segment(1) == 'seminar-patient-info']
                            );
                        
                    },
                    ['icon' => 'fa fas fa-bed', 'id' => 'tour_step5']
                )->order(20);
            }
            if (auth()->user()->can('clinic.all_appointment_show')) {
                $menu->dropdown(
                    __('clinic::lang.appointment'),
                    function ($sub) use ($common_settings) {
                        $sub->url(
                            action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'index']),
                            __('clinic::lang.all_appointment'),
                            ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'all-appointment']
                        );
                        $sub->url(
                            action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'therapyAppointment']),
                            __('clinic::lang.therapy_appointment'),
                            ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'therapy-appointment']
                        );
                        if (auth()->user()->can('new.appointment.create')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'index']),
                                __('clinic::lang.new_doc_app'),
                                [
                                    'icon' => 'fa fas fa-list', 
                                    'active' => (request()->segment(1) == 'new-doctor' && request()->get('type') != 'therapist') || 
                                            (request()->segment(1) == 'appointment' && request()->segment(2) == 'doctor' && request()->get('type') != 'therapist')
                                ]     
                            );

                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'index']).'?type=therapist',
                                __('clinic::lang.new_therapy_app'),
                                [
                                    'icon' => 'fa fas fa-list', 
                                    'active' => (request()->segment(1) == 'new-doctor' && request()->get('type') == 'therapist') || 
                                            (request()->segment(1) == 'appointment' && request()->segment(2) == 'doctor' && request()->get('type') == 'therapist')
                                ]
                            );
                        }
                        if (auth()->user()->can('appointment_report.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\AppReportController::class, 'index']),
                                __('clinic::lang.appointment_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'appointment-report']
                            );
                        }
                        if (auth()->user()->can('abandon_report_list')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\AbandonReportController::class, 'index']),
                                __('clinic::lang.abandon_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'abandon-report']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-file-medical', 'id' => 'tour_step6']
                )->order(25);
            }

            //Sell dropdown

            if ((auth()->user()->can('doctor.consultation.show'))) {
                $menu->dropdown(
                    __('clinic::lang.consultation'),
                    function ($sub) {
                        if (auth()->user()->can('doctor.consultation.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\DoctorConsultationController::class, 'index']),
                                __('clinic::lang.all_consultation'),
                                ['icon' => 'fas fa-arrow-up', 'active' => request()->segment(1) == 'doctor-consultation' && request()->segment(2) == null]
                            );
                        }
                        if (auth()->user()->can('doctor.consultation.create')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\DoctorConsultationController::class, 'create']),
                                __('clinic::lang.add_consultation'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'doctor-consultation' && request()->segment(2) == 'create']
                            );
                        }
                        if (auth()->user()->can('consultation.category_view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicCategoryController::class, 'index']) . '?type=consultation',
                                __('clinic::lang.category'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-category' && request()->get('type') == 'consultation']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-syringe']
                )->order(35);
            }
            if ((auth()->user()->can('ipd.show'))) {
                $menu->dropdown(
                    __('clinic::lang.ipd'),
                    function ($sub) {

                        $sub->url(
                            action([\Modules\Clinic\Http\Controllers\IPDController::class, 'index']),
                            __('clinic::lang.all_ipd'),
                            ['icon' => 'fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-ipd' && request()->segment(2) == null]
                        );

                        if (auth()->user()->can('ipd.store')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\IPDController::class, 'create']),
                                __('clinic::lang.ipd_create'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-ipd' && request()->segment(2) == 'create']
                            );
                        }
                        if (auth()->user()->can('ipd.category_view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicCategoryController::class, 'index']) . '?type=ipd',
                                __('clinic::lang.category'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-category' && request()->get('type') == 'ipd']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-bed']
                )->order(35);
            }
            if ((auth()->user()->can('clinic.test.view'))) {
                $menu->dropdown(
                    __('clinic::lang.test'),
                    function ($sub) {
                        $sub->url(
                            action([\Modules\Clinic\Http\Controllers\TestController::class, 'index']),
                            __('clinic::lang.alltest'),
                            ['icon' => 'fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-test' && request()->segment(2) == null]
                        );
                        if (auth()->user()->can('clinic.test.create')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\TestController::class, 'create']),
                                __('clinic::lang.addtest'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-test' && request()->segment(2) == 'create']
                            );
                        }
                        if (auth()->user()->can('test.category_view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicCategoryController::class, 'index']) . '?type=test',
                                __('clinic::lang.category'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-category' && request()->get('type') == 'test']
                            );
                        }
                        if (auth()->user()->can('clinic.brand.view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicBrandController::class, 'index']) . '?sub_type=test',
                                __('clinic::lang.brand'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-brand' && request()->get('sub_type') == 'test']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-vial']
                )->order(35);
            }
            if ((auth()->user()->can('clinic.therapy.view'))) {
                $menu->dropdown(
                    __('clinic::lang.therapy'),
                    function ($sub) {
                        if (auth()->user()->can('clinic.therapy.view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\TherapyController::class, 'index']),
                                __('clinic::lang.alltherapy'),
                                ['icon' => 'fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-therapy' && request()->segment(2) == null]
                            );
                        }
                        if (auth()->user()->can('clinic.therapy.create')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\TherapyController::class, 'create']),
                                __('clinic::lang.addtherapy'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-therapy' && request()->segment(2) == 'create']
                            );
                        }

                        if (auth()->user()->can('therapy.category_view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicCategoryController::class, 'index']) . '?type=therapy',
                                __('clinic::lang.category'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-category' && request()->get('type') == 'therapy']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-pump-medical']
                )->order(35);
            }
            if ((auth()->user()->can('clinic.sell.view'))) {
                $menu->dropdown(
                    __('clinic::lang.bill'),
                    function ($sub) {
                        if (auth()->user()->can('clinic.sell.view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'index']),
                                __('clinic::lang.all_bill'),
                                ['icon' => 'fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-sell' && request()->segment(2) == null]
                            );
                        }
                        if (auth()->user()->can('clinic.sell.create')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'create']),
                                __('clinic::lang.add_bill'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-sell' && request()->segment(2) == 'create' && empty(request()->get('status'))]
                            );
                        }
                        if (auth()->user()->can('clinic.draft.sell.create')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'create'], ['status' => 'draft']),
                                __('clinic::lang.add_draft_bill'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic-sell' && request()->segment(2) == 'create' && request()->get('status')]
                            );
                        }
                        if (auth()->user()->can('clinic.sell.draft_view_all')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'getDraftDatables']),
                                __('clinic::lang.list_draft_bill'),
                                ['icon' => 'fa fas fa-arrow-up', 'active' => request()->segment(1) == 'clinic' && request()->segment(2) == 'draft' && request()->segment(3) == 'sell' && request()->segment(4) == 'list']
                            );
                        }
                        if (auth()->user()->can('access_sell_return') || auth()->user()->can('access_own_sell_return')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\BillReturnController::class, 'index']),
                                __('clinic::lang.list_bill_return'),
                                ['icon' => 'fa fas fa-undo', 'active' => request()->segment(1) == 'bill-return' && request()->segment(2) == null]
                            );
                        }
                        if (auth()->user()->can('discount.access')) {
                            $sub->url(
                                action([\App\Http\Controllers\DiscountController::class, 'index']),
                                __('lang_v1.discounts'),
                                ['icon' => 'fa fas fa-percent', 'active' => request()->segment(1) == 'discount']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-arrow-up']
                )->order(35);
            }

            // $menu->dropdown(
            //     __('clinic::lang.lab_report'),
            //     function ($sub) {
            //         if (auth()->user()->can('patient.visit.view')) {
            //             $sub->url(
            //                 action([\Modules\Clinic\Http\Controllers\InvestigationReportController::class, 'index']),
            //                 __('clinic::lang.all_lab_report'),
            //                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'investigation-report' && request()->segment(2) == null]
            //             );
            //         }
            //     },
            //     ['icon' => 'fas fa-flask']
            // )->order(36);

            if (auth()->user()->can('patient.visit.view')) {
                $menu->dropdown(
                    __('clinic::lang.patient_visit'),
                    function ($sub) {
                        if (auth()->user()->can('patient.visit.view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\Survey\ReviewReportController::class, 'index']),
                                __('clinic::lang.feedback_list'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(2) == 'medical-report' && request()->segment(3) == null]
                            );
                        }
                        if (auth()->user()->can('call.center.feedback.list')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\CallCenterFeedbackController::class, 'index']),
                                __('clinic::lang.call_center_feedback_list'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'call-center-feedback']
                            );
                        }
                        if (auth()->user()->can('intake.form.list')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\Survey\IntakeFormController::class, 'index']),
                                __('clinic::lang.intake_form'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(2) == 'intake-form' && request()->segment(3) == null]
                            );
                        }
                        $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;

                        if ($is_admin) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\Survey\ProblemController::class, 'problemWisePatient']),
                                __('clinic::lang.disease_wise_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'survey' && request()->segment(2) == 'problem' && request()->segment(3) == 'wise' && request()->segment(4) == 'patient' || request()->segment(1) == 'survey' && request()->segment(2) == 'problem-wise-patient-chart-filter']
                            );
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\Survey\CommentController::class, 'commentWisePatient']),
                                __('clinic::lang.feedback_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'survey' && request()->segment(2) == 'comment' && request()->segment(3) == 'wise' && request()->segment(4) == 'patient' || request()->segment(1) == 'survey' && request()->segment(2) == 'comment-wise-patient-chart-filter']
                            );
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\Survey\ReferenceController::class, 'index']),
                                __('clinic::lang.reference_wise_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'survey' && request()->segment(2) == 'survey-references' || request()->segment(1) == 'survey' && request()->segment(2) == 'reference-wise-patient-chart-filter']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-pen']
                )->order(50);
            }


            if (auth()->user()->can('patient.feedback.setting')) {
                $menu->dropdown(
                    __('clinic::lang.feedback_setting'),
                    function ($sub) {
                        if (auth()->user()->can('survey.type.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\SurveyTypeController::class, 'index']),
                                __('clinic::lang.survey_setup'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'survey-types' && empty(request()->get('type'))]
                            );
                        }
                        $sub->url(
                                action([\Modules\Clinic\Http\Controllers\SurveyTypeController::class, 'index']).'?type=seminar',
                                __('clinic::lang.seminar_setup'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'survey-types' && request()->get('type') == 'seminar']
                            );
                        if (auth()->user()->can('feedback.role.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\FeedbackRoleController::class, 'index']),
                                __('clinic::lang.feedback_role_show'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'feedback-role']
                            );
                        }
                        if (auth()->user()->can('feedback.question.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\FeedbackQuestionController::class, 'index']),
                                __('clinic::lang.feedback_question_list'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'feedback-question']
                            );
                        }
                        if (auth()->user()->can('feedback.kpi.report.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\Survey\ReviewReportController::class, 'feedbackKPIReport']),
                                __('clinic::lang.feedback_kpi_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'survey' && request()->segment(2) == 'feedback' && request()->segment(3) == 'kpi' && request()->segment(4) == 'report' && request()->get('survey_type') == '']
                            );
                        }
                        $sub->url(
                                action([\Modules\Clinic\Http\Controllers\Survey\ReviewReportController::class, 'feedbackKPIReport']).'?survey_type=seminar',
                                __('clinic::lang.seminar_feedback_kpi_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'survey' && request()->segment(2) == 'feedback' && request()->segment(3) == 'kpi' && request()->segment(4) == 'report' && request()->get('survey_type') == 'seminar']
                            );
                    },
                    ['icon' => 'fa fas fa-hammer']
                )->order(55);
            }


            //stock adjustment dropdown
            // if ((auth()->user()->can('clinic.store'))) {
            //     $menu->dropdown(
            //         __('clinic::lang.store'),
            //         function ($sub) {
            //             if (auth()->user()->can('purchase.view')) {
            //                 $sub->url(
            //                     action([\Modules\Clinic\Http\Controllers\ClinicStoreController::class, 'index']),
            //                     __('clinic::lang.all_stock'),
            //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'clinic-store' && request()->segment(2) == null]
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-database']
            //     )->order(40);
            // }
            //Purchase dropdown


            // if (auth()->user()->can('clinic.crm')) {
            //     $menu->dropdown(
            //         __('clinic::lang.crm'),
            //         function ($sub) use ($enabled_modules, $is_admin, $pos_settings) {
            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\Modules\Clinic\Http\Controllers\CrmCallHistoriesController::class, 'index']),
            //                     __('clinic::lang.call_histories'),
            //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'call-histories' && request()->segment(2) == null]
            //                 );
            //             }

            //             if (auth()->user()->can('product.view')) {
            //                 $sub->url(
            //                     action([\Modules\Clinic\Http\Controllers\FollowUpCallListController::class, 'index2']),
            //                     __('clinic::lang.follow_up_call'),
            //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'follow-up-call-list' && request()->segment(2) == null]
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-bookmark', 'id' => 'tour_step7']
            //     )->order(30);
            // }
            // if (auth()->user()->can('clinic.schedule')) {
            //     $menu->dropdown(
            //         __('clinic::lang.schedule'),
            //         function ($sub) {
            //             if (auth()->user()->can('user.view')) {
            //                 $sub->url(
            //                     action([\Modules\Clinic\Http\Controllers\DoctorScheduleController::class, 'index']),
            //                     __('clinic::lang.doctor_schedule'),
            //                     ['icon' => 'fa fas fa-user', 'active' => request()->segment(1) == 'doctor-schedule']
            //                 );
            //             }
            //             if (auth()->user()->can('roles.view')) {
            //                 $sub->url(
            //                     action([\Modules\Clinic\Http\Controllers\TherapyScheduleController::class, 'index']),
            //                     __('clinic::lang.therapy_schedule'),
            //                     ['icon' => 'fa fas fa-briefcase', 'active' => request()->segment(1) == 'therapy-schedule']
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-clipboard-list',]
            //     )->order(10);
            // }
            //Stock transfer dropdown
            // if (auth()->user()->can('clinic.prescriptions')) {
            //     $menu->dropdown(
            //         __('clinic::lang.prescriptions'),
            //         function ($sub) {
            //             if (auth()->user()->can('clinic.prescriptions')) {
            //                 $sub->url(
            //                     action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'index']),
            //                     __('clinic::lang.prescriptions'),
            //                     ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'prescriptions' && request()->segment(2) == null]
            //                 );
            //             }
            //         },
            //         ['icon' => 'fa fas fa-book-medical']
            //     )->order(35);
            // }
            //Expense dropdown
            if ((auth()->user()->can('clinic.report'))) {

                $menu->dropdown(
                    __('clinic::lang.report'),

                    function ($sub) use($is_admin) {
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\SubscribeReportController::class, 'index']),
                        //         __('clinic::lang.subscribe_report'),
                        //         ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'subs-report']
                        //     );
                        // }
                        if (auth()->user()->can('admin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'paymentReport']),
                                __('clinic::lang.sell_payment_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'clinic-sell-payment-report']
                            );
                        }


                        if (auth()->user()->can('admin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ServiceReportController::class, 'index']),
                                __('clinic::lang.service_report'),
                                ['icon' => 'fa fas fa-plus-circle', 'active' => request()->segment(1) == 'service-report']
                            );
                        }

                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\ConsultantPaymentController::class, 'index']),
                        //         __('clinic::lang.consultation_report'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'consultant']
                        //     );
                        // }
                        if (auth()->user()->can('admin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\PayReportController::class, 'index']),
                                __('clinic::lang.payment_report'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'pay-report']
                            );
                        }

                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\TodayPayController::class, 'index']),
                        //         __('clinic::lang.today_report'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'today-pay']
                        //     );
                        // }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\RefundController::class, 'index']),
                        //         __('clinic::lang.refund_list'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'refund']
                        //     );
                        // }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\ActivityLogController::class, 'index']),
                        //         __('clinic::lang.activity_log'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'activity-log']
                        //     );
                        // }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\ChartController::class, 'index']),
                        //         __('clinic::lang.graph_report'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'graph-chart']
                        //     );
                        // }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\IncomeController::class, 'index']),
                        //         __('clinic::lang.income_report'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'income']
                        //     );
                        // }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\MedicalTestController::class, 'index']),
                        //         __('clinic::lang.pm_test_report'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'medical-report']
                        //     );
                        // }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\MedicalTestController::class, 'testSellList']),
                        //         __('clinic::lang.total_pm_sell_report'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'medical-test-sell']
                        //     );
                        // }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\FollowUpCallListController::class, 'index2']),
                        //         __('clinic::lang.follow_up_call_list'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'follow-up-report']
                        //     );
                        // }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\DiseasesController::class, 'index']),
                        //         __('clinic::lang.patient_diseases'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'diseases']
                        //     );
                        // }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\AgeController::class, 'index']),
                        //         __('clinic::lang.age_graph'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'age-report']
                        //     );
                        // }
                        if (auth()->user()->can('admin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\report\TestReportController::class, 'index']),
                                __('clinic::lang.test_report'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'test-report']
                            );
                        }
                        if (auth()->user()->can('admin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\report\TestSellReportController::class, 'index']),
                                __('clinic::lang.test_sell_report'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'test-sell-report']
                            );
                        }


                        if (auth()->user()->can('register_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getClinicRegisterReport']),
                                __('report.register_report'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(2) == 'clinic-register-report']
                            );
                        }
                        if (auth()->user()->can('admin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'sessionDetailsReport']),
                                __('clinic::lang.individual_therapy_report'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(2) == 'session-details-report']
                            );
                        }
                        // if (auth()->user()->can('admin')) {
                        //     $sub->url(
                        //         action([\Modules\Clinic\Http\Controllers\AgeController::class, 'index2']),
                        //         __('clinic::lang.age_report'),
                        //         ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'age-graph']
                        //     );
                        // }
                        if (auth()->user()->can('admin') || auth()->user()->can('superadmin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'DoctorProfileSummary']),
                                __('clinic::lang.doctor_profile_summary'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'doctor-profile-summary']
                            );
                        }
                        if (auth()->user()->can('admin') || auth()->user()->can('superadmin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'DoctorsComparativeKPIReport']),
                                __('clinic::lang.doctor_comparative_kpireport'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'doctor-doctors-comparativeKPIReport']
                            );
                        }
                        if (auth()->user()->can('admin') || auth()->user()->can('superadmin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\DoctoreKPIController::class, 'DoctorKPIPerformanceReport']),
                                __('clinic::lang.doctor_kpi_performance'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'doctor-kpi-performance']
                            ); 
                        }
                        if (auth()->user()->can('admin') || auth()->user()->can('superadmin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\MedicineFulfillmentReportController::class, 'medicineFulfillmentReport']),
                                __('clinic::lang.medicine_fulfillment'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'medicine-fulfillment-report']
                            );
                        }
                        if (auth()->user()->can('admin') || auth()->user()->can('superadmin')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'NextVisitData']),
                                __('clinic::lang.next_visit_data'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(1) == 'next-visit-data']
                            );
                        }

                        if ($is_admin) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'activityLog']),
                                __('lang_v1.activity_log'),
                                ['icon' => 'fa fas fa-user-secret', 'active' => request()->segment(2) == 'activity-log']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-minus-circle']
                )->order(45);
            }
            //Accounts dropdown
            // if (auth()->user()->can('clinic.memo')) {
            //     $menu->dropdown(
            //         __('clinic::lang.memo'),
            //         function ($sub) {
            //             $sub->url(
            //                 action([\Modules\Clinic\Http\Controllers\MemosController::class, 'index']),
            //                 __('clinic::lang.memo'),
            //                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'memos']
            //             );
            //         },
            //         ['icon' => 'fa fas fa-money-check-alt']
            //     )->order(50);
            // }
            //     $menu->dropdown(
            //         __('clinic::lang.payment'),
            //         function ($sub) {
            //             $sub->url(
            //                 action([\Modules\Clinic\Http\Controllers\SubsPaymentController::class, 'index']),
            //                 __('clinic::lang.subs_payment'),
            //                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'subs-payment']
            //             );
            //         },
            //         ['icon' => 'fa fas fa-file-invoice-dollar']
            //     )->order(45);
            // if (auth()->user()->can('clinic.agent')) {
            //     $menu->dropdown(
            //         __('clinic::lang.agent'),
            //         function ($sub) {
            //             $sub->url(
            //                 action([\Modules\Clinic\Http\Controllers\AgentProfileController::class, 'index']),
            //                 __('clinic::lang.agent_profile'),
            //                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'agent-profile']
            //             );
            //             $sub->url(
            //                 action([\Modules\Clinic\Http\Controllers\AgentMappingController::class, 'index']),
            //                 __('clinic::lang.mapping'),
            //                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'agent-mapping']
            //             );
            //             $sub->url(
            //                 action([\Modules\Clinic\Http\Controllers\AgentDetailsController::class, 'index']),
            //                 __('clinic::lang.agent_details'),
            //                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'agent-details']
            //             );
            //             $sub->url(
            //                 action([\Modules\Clinic\Http\Controllers\AgentCommissionController::class, 'index']),
            //                 __('clinic::lang.agent_com'),
            //                 ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'agent-commission']
            //             );
            //         },
            //         ['icon' => 'fa fas fa-users']
            //     )->order(50);
            // }
            if (auth()->user()->can('manage_modules')) {
                $menu->url(action([\App\Http\Controllers\Install\ModulesController::class, 'index']), __('lang_v1.modules'), ['icon' => 'fa fas fa-plug', 'active' => request()->segment(1) == 'manage-modules'])->order(60);
            }
            if (
                auth()->user()->can('business_settings.access') ||
                auth()->user()->can('barcode_settings.access') ||
                auth()->user()->can('invoice_settings.access') ||
                auth()->user()->can('tax_rate.view') ||
                auth()->user()->can('tax_rate.create') ||
                auth()->user()->can('access_package_subscriptions')
            ) {
                $menu->dropdown(
                    __('business.settings'),
                    function ($sub) use ($enabled_modules) {
                        if (auth()->user()->can('business_settings.access')) {
                            $sub->url(
                                action([\App\Http\Controllers\BusinessController::class, 'getBusinessSettings']),
                                __('business.business_settings'),
                                ['icon' => 'fa fas fa-cogs', 'active' => request()->segment(1) == 'business', 'id' => 'tour_step2']
                            );
                            $sub->url(
                                action([\App\Http\Controllers\BusinessLocationController::class, 'index']),
                                __('business.business_locations'),
                                ['icon' => 'fa fas fa-map-marker', 'active' => request()->segment(1) == 'business-location']
                            );
                        }
                        if (auth()->user()->can('invoice_settings.access')) {
                            $sub->url(
                                action([\App\Http\Controllers\InvoiceSchemeController::class, 'index']),
                                __('invoice.invoice_settings'),
                                ['icon' => 'fa fas fa-file', 'active' => in_array(request()->segment(1), ['invoice-schemes', 'invoice-layouts'])]
                            );
                        }
                        if (auth()->user()->can('barcode_settings.access')) {
                            $sub->url(
                                action([\App\Http\Controllers\BarcodeController::class, 'index']),
                                __('barcode.barcode_settings'),
                                ['icon' => 'fa fas fa-barcode', 'active' => request()->segment(1) == 'barcodes']
                            );
                        }
                        if (auth()->user()->can('access_printers')) {
                            $sub->url(
                                action([\App\Http\Controllers\PrinterController::class, 'index']),
                                __('printer.receipt_printers'),
                                ['icon' => 'fa fas fa-share-alt', 'active' => request()->segment(1) == 'printers']
                            );
                        }

                        if (auth()->user()->can('tax_rate.view') || auth()->user()->can('tax_rate.create')) {
                            $sub->url(
                                action([\App\Http\Controllers\TaxRateController::class, 'index']),
                                __('tax_rate.tax_rates'),
                                ['icon' => 'fa fas fa-bolt', 'active' => request()->segment(1) == 'tax-rates']
                            );
                        }

                        if (in_array('tables', $enabled_modules) && auth()->user()->can('access_tables')) {
                            $sub->url(
                                action([\App\Http\Controllers\Restaurant\TableController::class, 'index']),
                                __('restaurant.tables'),
                                ['icon' => 'fa fas fa-table', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'tables']
                            );
                        }

                        if (in_array('modifiers', $enabled_modules) && (auth()->user()->can('product.view') || auth()->user()->can('product.create'))) {
                            $sub->url(
                                action([\App\Http\Controllers\Restaurant\ModifierSetsController::class, 'index']),
                                __('restaurant.modifiers'),
                                ['icon' => 'fa fas fa-pizza-slice', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'modifiers']
                            );
                        }

                        if (in_array('types_of_service', $enabled_modules) && auth()->user()->can('access_types_of_service')) {
                            $sub->url(
                                action([\App\Http\Controllers\TypesOfServiceController::class, 'index']),
                                __('lang_v1.types_of_service'),
                                ['icon' => 'fa fas fa-user-circle', 'active' => request()->segment(1) == 'types-of-service']
                            );
                        }
                    },
                    ['icon' => 'fa fas fa-cog', 'id' => 'tour_step3']
                )->order(85);
            }
        });

        //Add menus from modules
        $moduleUtil = new ModuleUtil;
        $moduleData = $moduleUtil->getModuleData('modifyAdminMenu');
        // Check if module data is not null
        // if ($moduleData !== null) {
        //     // Loop through module data
        //     foreach ($moduleData as $moduleName => $modulePermissions) {
        //         // Check if modulePermissions is an array and has 'adminMenuPermission' key
        //         if (is_array($modulePermissions) && isset($modulePermissions['adminMenuPermission'])) {
        //             // Example permission logic
        //             if ($modulePermissions['adminMenuPermission'] === true) {
        //                 // Grant permission
        //                 return $next($request);
        //             } else {
        //                 // Deny permission
        //                 return redirect()->route('unauthorized');
        //             }
        //         }
        //     }
        // }

        return $next($request);
    }
}
