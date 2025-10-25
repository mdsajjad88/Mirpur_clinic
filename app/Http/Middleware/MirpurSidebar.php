<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;

use App\Utils\ModuleUtil;
use Closure;
use Menu;
use Modules\Clinic\Entities\DoctorProfile;

class MirpurSidebar
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
        Menu::create('mirpur-sidebar-menu', function ($menu) {
            $enabled_modules = !empty(session('business.enabled_modules')) ? session('business.enabled_modules') : [];

            $common_settings = !empty(session('business.common_settings')) ? session('business.common_settings') : [];
            $pos_settings = !empty(session('business.pos_settings')) ? json_decode(session('business.pos_settings'), true) : [];

            $is_admin = auth()->user()->hasRole('Admin#' . session('business.id')) ? true : false;
            $is_doctor = auth()->user()->hasRole('Doctor#' . session('business.id')) ? true : false;
            //Home
            // if ($is_admin) {
                $menu->url(action([\Modules\Clinic\Http\Controllers\ClinicController::class, 'index']), __('clinic::lang.dashboard'), ['icon' => 'fas fa-chart-line', 'active' => request()->segment(1) == 'clinic'])->order(5);
                
            // }
            if(auth()->user()->can('patient-manage-token')){
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
                        if (auth()->user()->can('dosage.view') || auth()->user()->can('dosage.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\DosageController::class, 'index']), __('clinic::lang.dosage'), ['icon' => 'fa fas fa-square', 'active' => request()->segment(1) == 'medicine-dosage'])->order(6);
                        }
                        if (auth()->user()->can('medicine_durations.view') || auth()->user()->can('medicine_durations.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\DurationController::class, 'index']), __('clinic::lang.duration'), ['icon' => 'fa fas fa-microscope', 'active' => request()->segment(1) == 'medicine-durations'])->order(7);
                        }
                        if (auth()->user()->can('therapy.frequency.view') || auth()->user()->can('therapy.frequency.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\FrequencyController::class, 'index']), __('clinic::lang.frequency'), ['icon' => 'fa fas fa-wave-square', 'active' => request()->segment(1) == 'therapy-frequency'])->order(8);
                        }
                        
                        if (auth()->user()->can('medicine_meal.view') || auth()->user()->can('medicine_meal.create')) {
                            $menu->url(action([\Modules\Clinic\Http\Controllers\doctor\MedicineMealController::class, 'index']).'?type=nutritionist', __('clinic::lang.instructions'), ['icon' => 'fa fas fa-wine-glass', 'active' => request()->segment(1) == 'medicine-meal' && request()->get('type') == 'nutritionist'])->order(10);
                        }
                        
                    },

                    ['icon' => 'fa fas fa-cog', 'id' => 'cog']
                )->order(10);
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
                        if (auth()->user()->can('intake.form.list')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\Survey\IntakeFormController::class, 'index']),
                                __('clinic::lang.intake_form'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(2) == 'intake-form' && request()->segment(3) == null]
                            );
                        }                    
                        $sub->url(
                                action([\App\Http\Controllers\CustomerGroupController::class, 'index']),
                                __('lang_v1.customer_groups'),
                                ['icon' => 'fa fas fa-users', 'active' => request()->segment(1) == 'customer-group']
                            );
                    },
                    ['icon' => 'fa fas fa-bed', 'id' => 'tour_step5']
                )->order(20);
            }
            if (auth()->user()->can('clinic.all_appointment_show')) {
                $menu->dropdown(
                    __('clinic::lang.appointment'),
                    function ($sub){
                        $sub->url(
                            action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'index']),
                            __('clinic::lang.all_appointment'),
                            ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'all-appointment']
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
                        }
                        if (auth()->user()->can('appointment_report.show')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\AppReportController::class, 'index']),
                                __('clinic::lang.appointment_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'appointment-report']
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
                        
            if ((auth()->user()->can('clinic.register_report.view') || auth()->user()->can('sell_payment_report.view'))) {

                $menu->dropdown(
                    __('clinic::lang.report'),

                    function ($sub) use($is_admin) {
                       
                        if (auth()->user()->can('sell_payment_report.view')) {
                            $sub->url(
                                action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'paymentReport']),
                                __('clinic::lang.sell_payment_report'),
                                ['icon' => 'fa fas fa-list', 'active' => request()->segment(1) == 'clinic-sell-payment-report']
                            );
                        }                        

                        if (auth()->user()->can('clinic.register_report.view')) {
                            $sub->url(
                                action([\App\Http\Controllers\ReportController::class, 'getClinicRegisterReport']),
                                __('report.register_report'),
                                ['icon' => 'fa fas fa-circle', 'active' => request()->segment(2) == 'clinic-register-report']
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
            
            // if (auth()->user()->can('manage_modules')) {
            //     $menu->url(action([\App\Http\Controllers\Install\ModulesController::class, 'index']), __('lang_v1.modules'), ['icon' => 'fa fas fa-plug', 'active' => request()->segment(1) == 'manage-modules'])->order(60);
            // }
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

                        // if (in_array('modifiers', $enabled_modules) && (auth()->user()->can('product.view') || auth()->user()->can('product.create'))) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\Restaurant\ModifierSetsController::class, 'index']),
                        //         __('restaurant.modifiers'),
                        //         ['icon' => 'fa fas fa-pizza-slice', 'active' => request()->segment(1) == 'modules' && request()->segment(2) == 'modifiers']
                        //     );
                        // }

                        // if (in_array('types_of_service', $enabled_modules) && auth()->user()->can('access_types_of_service')) {
                        //     $sub->url(
                        //         action([\App\Http\Controllers\TypesOfServiceController::class, 'index']),
                        //         __('lang_v1.types_of_service'),
                        //         ['icon' => 'fa fas fa-user-circle', 'active' => request()->segment(1) == 'types-of-service']
                        //     );
                        // }
                    },
                    ['icon' => 'fa fas fa-cog', 'id' => 'tour_step3']
                )->order(85);
            }
        });

        //Add menus from modules
        $moduleUtil = new ModuleUtil;
        $moduleData = $moduleUtil->getModuleData('modifyAdminMenu');
        return $next($request);
    }
}
