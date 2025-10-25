<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Menu;
class DataController extends Controller
{
    public function superadmin_package()
    {
        return [
            [
                'name' => 'clinic_module',
                'label' => __('clinic::lang.clinic_module'),
                'default' => false,
            ],
        ];
    }
    public function user_permissions()
    {
        return [
            [
                'value' => 'clinic.view',
                'label' =>__('clinic::lang.clinic_view'),
                'default' => false,
            ],
            [
                'value' => 'clinic.dashboard.view',
                'label' => __('clinic::lang.clinic_dashboard_show'),
                'default' => false,
            ],
        // Prescription Setting Start
            // doctor advice Start
            [
                'value' => 'prescription_dashboard_show',
                'label' => __('clinic::lang.prescription_dashboard_show'),
                'default' => false,
            ],
            [
                'value' => 'all_days_prescription_list_show',
                'label' => __('clinic::lang.all_days_prescription_list_show'),
                'default' => false,
            ],
            [
                'value' => 'seven_days_prescription_list_show',
                'label' => __('clinic::lang.show_seven_days_prescription_list'),
                'default' => false,
            ],
            [
                'value' => 'prescription_store_or_update',
                'label' => __('clinic::lang.prescription_store_or_update'),
                'default' => false,
            ],
            [
                'value' => 'prescription.setting.show',
                'label' => __('clinic::lang.prescription_setting_show'),
                'default' => false,
            ],
            [
                'value' => 'doctor.advice.view',
                'label' => __('clinic::lang.doctor_advice_show'),
                'default' => false,
            ],
            [
                'value' => 'doctor.advice.create',
                'label' => __('clinic::lang.doctor_advice_store'),
                'default' => false,
            ],
            [
                'value' => 'doctor.advice.update',
                'label' => __('clinic::lang.doctor_advice_update'),
                'default' => false,
            ],
            [
                'value' => 'doctor.advice.delete',
                'label' => __('clinic::lang.doctor_advice_delete'),
                'default' => false,
            ],
            // doctor advice End

            // medicine use Start
            [
                'value' => 'medicine_use.view',
                'label' => __('clinic::lang.medicine_use_show'),
                'default' => false,
            ],
            [
                'value' => 'medicine_use.create',
                'label' => __('clinic::lang.medicine_use_store'),
                'default' => false,
            ],
            [
                'value' => 'medicine_use.update',
                'label' => __('clinic::lang.medicine_use_update'),
                'default' => false,
            ],
            [
                'value' => 'medicine_use.delete',
                'label' => __('clinic::lang.medicine_use_delete'),
                'default' => false,
            ],
            // medicine use End

            // medicine meal Start
            [
                'value' => 'medicine_meal.view',
                'label' => __('clinic::lang.medicine_meal_show'),
                'default' => false,
            ],
            [
                'value' => 'medicine_meal.create',
                'label' => __('clinic::lang.medicine_meal_store'),
                'default' => false,
            ],
            [
                'value' => 'medicine_meal.update',
                'label' => __('clinic::lang.medicine_meal_update'),
                'default' => false,
            ],
            [
                'value' => 'medicine_meal.delete',
                'label' => __('clinic::lang.medicine_meal_delete'),
                'default' => false,
            ],
            // medicine meal End

           

            // dosage Start
            [
                'value' => 'dosage.view',
                'label' => __('clinic::lang.dosage_show'),
                'default' => false,
            ],
            [
                'value' => 'dosage.create',
                'label' => __('clinic::lang.dosage_store'),
                'default' => false,
            ],
            [
                'value' => 'dosage.update',
                'label' => __('clinic::lang.dosage_update'),
                'default' => false,
            ],
            [
                'value' => 'dosage.delete',
                'label' => __('clinic::lang.dosage_delete'),
                'default' => false,
            ],
            // dosage End

            // medicine durations Start
            [
                'value' => 'medicine_durations.view',
                'label' => __('clinic::lang.medicine_durations_show'),
                'default' => false,
            ],
            [
                'value' => 'medicine_durations.create',
                'label' => __('clinic::lang.medicine_durations_store'),
                'default' => false,
            ],
            [
                'value' => 'medicine_durations.update',
                'label' => __('clinic::lang.medicine_durations_update'),
                'default' => false,
            ],
            [
                'value' => 'medicine_durations.delete',
                'label' => __('clinic::lang.medicine_durations_delete'),
                'default' => false,
            ],
            // medicine durations End

            // therapy frequency Start
            [
                'value' => 'therapy.frequency.view',
                'label' => __('clinic::lang.therapy_frequency_show'),
                'default' => false,
            ],
            [
                'value' => 'therapy.frequency.create',
                'label' => __('clinic::lang.therapy_frequency_store'),
                'default' => false,
            ],
            [
                'value' => 'therapy.frequency.update',
                'label' => __('clinic::lang.therapy_frequency_update'),
                'default' => false,
            ],
            [
                'value' => 'therapy.frequency.delete',
                'label' => __('clinic::lang.therapy_frequency_delete'),
                'default' => false,
            ],
           
        // Prescription End

        // Drug Database Start
            //BD drug Start
            [
                'value' => 'bd_drug.view',
                'label' => __('clinic::lang.bd_drug_show'),
                'default' => false,
            ],
            [
                'value' => 'pharmacy_drug.view',
                'label' => __('clinic::lang.pharmacy_drug_show'),
                'default' => false,
            ],
         // Drug Database End   
            // Provider Start  
            [
                'value' => 'clinic.provider.create',
                'label' => __('clinic::lang.provider_create'),
                'default' => false,
            ],
            [
                'value' => 'clinic.provider.view',
                'label' => __('clinic::lang.provider_view'),
                'default' => false,
            ],
            [
                'value' => 'clinic.reference.list',
                'label' => __('clinic::lang.reference_list'),
                'default' => false,
            ],
            [
                'value' => 'clinic.provider.profile.show',
                'label' => __('clinic::lang.provider_profile_show'),
                'default' => false,
            ],
            [
                'value' => 'clinic.provider.edit',
                'label' => __('clinic::lang.provider_edit'),
                'default' => false,
            ],
            [
                'value' => 'clinic.provider.delete',
                'label' => __('clinic::lang.provider_delete'),
                'default' => false,
            ],
            [
                'value' => 'clinic.provider.deactive',
                'label' => __('clinic::lang.provider_deactive'),
                'default' => false,
            ],
             [
                'value' => 'reference_doctor.show',
                'label' => __('clinic::lang.reference_doctor_show'),
                'default' => false,
            ],
            [
                'value' => 'delete.daily.slots',
                'label' => __('clinic::lang.delete_daily_slots'),
                'default' => false,
            ],
        // Provider End

        // Patient Start
            [
                'value' => 'clinic.subs.patient.view',
                'label' => __('clinic::lang.subs_patient'),
                'default' => false,
            ],
            [
                'value' => 'clinic.patient.create',
                'label' => __('clinic::lang.patient_create'),
                'default' => false,
            ],
            [
                'value' => 'clinic.patient.pay',
                'label' => __('clinic::lang.patient_pay'),
                'default' => false,
            ],
            [
                'value' => 'clinic.patient.view',
                'label' => __('clinic::lang.patient_view'),
                'default' => false,
            ],
            [
                'value' => 'clinic.patient.edit',
                'label' => __('clinic::lang.patient_edit'),
                'default' => false,
            ],
            [
                'value' => 'clinic.patient.delete',
                'label' => __('clinic::lang.patient_delete'),
                'default' => false,
            ],
            [
                'value' => 'clinic.patient.deactive',
                'label' => __('clinic::lang.patient_deactive'),
                'default' => false,
            ],
            [
                'value' => 'clinic.patient.ledger',
                'label' => __('clinic::lang.patient_ledger'),
                'default' => false,
            ],
            [
                'value' => 'clinic.patient.sales',
                'label' => __('clinic::lang.patient_sales'),
                'default' => false,
            ],
            [
                'value' => 'clinic.patient.document_and_note',
                'label' => __('clinic::lang.patient_document_and_note'),
                'default' => false,
            ],
            [
                'value' => 'patient_old_medicine.show',
                'label' => __('clinic::lang.patient_old_medicine_show'),
                'default' => false,
            ],
            [
                'value' => 'patient_old_medicine.store',
                'label' => __('clinic::lang.patient_old_medicine_store'),
                'default' => false,
            ],
            [
                'value' => 'patient_old_medicine.update',
                'label' => __('clinic::lang.patient_old_medicine_update'),
                'default' => false,
            ],
            [
                'value' => 'patient_old_medicine.delete',
                'label' => __('clinic::lang.patient_old_medicine_delete'),
                'default' => false,
            ],
            [
                'value' => 'subscription.show',
                'label' => __('clinic::lang.subscription_show'),
                'default' => false,
            ],
            [
                'value' => 'subscription.store',
                'label' => __('clinic::lang.subscription_store'),
                'default' => false,
            ],
            [
                'value' => 'subscription.update',
                'label' => __('clinic::lang.subscription_update'),
                'default' => false,
            ],
            [
                'value' => 'subscription.delete',
                'label' => __('clinic::lang.subscription_delete'),
                'default' => false,
            ],
            [
                "value"=>'show.prima.subscription',
                "label"=>__('clinic::lang.show_prima_subscription'),
                "default"=>false,
            ],
            //patinet phone number defualt visible
            [
                'value' => 'patient.phone_number',
                'label' => __('clinic::lang.patient_phone_number'),
                'default' => false,
            ],
        // Patient End
            // Appointement permission start here
            [
                'value' => 'clinic.all_appointment_show',
                'label' => __('clinic::lang.all_appointment_show'),
                'default' => false,
            ],
            [
                'value' => 'new.appointment.create',
                'label' => __('clinic::lang.new_appointment_create'),
                'default' => false,
            ],
            [
                'value' => 'appointment_report.show',
                'label' => __('clinic::lang.appointment_report_show'),
                'default' => false,
            ],
            [
                'value' => 'appointment.final',
                'label' => __('clinic::lang.appointment_final'),
                'default' => false,
            ],
            [
                'value' => 'appointment.cancel',
                'label' => __('clinic::lang.appointment_cancel'),
                'default' => false,
            ],
            [
                'value' => 'appointment.delete',
                'label' => __('clinic::lang.appointment_delete'),
                'default' => false,
            ],
            [
                'value' => 'appointment.add_payment',
                'label' => __('clinic::lang.appointment_add_payment'),
                'default' => false,
            ],
            [
                'value' => 'appointment.update',
                'label' => __('clinic::lang.appointment_update'),
                'default' => false,
            ],
            [
                'value' => 'appointment.change_call_status',
                'label' => __('clinic::lang.appointment_call_status_update'),
                'default' => false,
            ],
            [
                'value' => 'appointment_updated_at_sort',
                'label' => __('clinic::lang.appointment_updated_at_sort'),
                'default' => false,
            ],
            [
                'value' => 'all_appointment_per_minute_reload_stop',
                'label' => __('clinic::lang.all_appointment_reload_stop'),
                'default' => false,
            ],
            [
                'value' => 'patient-manage-token',
                'label' => __('clinic::lang.patient_manage_token'),
                'default' => false,
            ],
            // Appointement permission end here
            // Doctor Consultation Permission Section
            [
                'value' => 'doctor.consultation.show',
                'label' => __('clinic::lang.consultation_show'),
                'default' => false,
            ],
            [
                'value' => 'doctor.consultation.create',
                'label' => __('clinic::lang.consultation_create'),
                'default' => false,
            ],
            [
                'value' => 'doctor.consultation.update',
                'label' => __('clinic::lang.consultation_update'),
                'default' => false,
            ],
            
            [
                'value' => 'doctor.consultation.delete',
                'label' => __('clinic::lang.consultation_delete'),
                'default' => false,
            ],
            [
                'value' => 'consultation.stock_history',
                'label' => __('clinic::lang.consultation_stock_history'),
                'default' => false,
            ],
            [
                'value' => 'consultation.add_group_price',
                'label' => __('clinic::lang.consultation_add_group_price'),
                'default' => false,
            ],
            [
                'value' => 'consultation.category_view',
                'label' => __('clinic::lang.consultation_category_view'),
                'default' => false,
            ],
            // here to end Consultation Permission section
           
           
            // clinic sell permissions Start
            [
                'value' => 'clinic.sell.create',
                'label' => __('clinic::lang.sell_create'),
                'default' => false,
            ],
            [
                'value' => 'clinic.sell.view',
                'label' => __('clinic::lang.sell_view'),
                'default' => false,
            ],
            
            [
                'value' => 'clinic.sell.delete',
                'label' => __('clinic::lang.sell_delete'),
                'default' => false,
            ],
            [
                'value' => 'clinic.draft.sell.create',
                'label' => __('clinic::lang.draft_sell_create'),
                'default' => false,
            ],
            [
                'value' => 'clinic.sell.print_invoice',
                'label' => __('clinic::lang.print_invoice'),
                'default' => false,
            ],
            [
                'value' => 'clinic.sell.add_payment',
                'label' => __('clinic::lang.add_payment'),
                'default' => false,
            ],
            [
                'value' => 'clinic.sell.payment.show',
                'label' => __('clinic::lang.sell_payment_show'),
                'default' => false,
            ],
            
            // clinic sell permissions end
             [
                'value' => 'clinic.report',
                'label' => __('clinic::lang.report'),
                'default' => false,
            ],
             // Patient Visit Permission Section            
          
            // Patient Visit Permission section end
            
            [
                'value' => 'intake.form.list',
                'label' => __('clinic::lang.intake_form_list'),
                'default' => false,
            ],
            [
                'value' => 'intake.form.show.patient.profile',
                'label' => __('clinic::lang.intake_form_show_patient_profile'),
                'default' => false,
            ],
            [
                'value' => 'prescription.show.patient.profile',
                'label' => __('clinic::lang.prescription_show_patient_profile'),
                'default' => false,
            ],
            [
                'value' => 'intake_form.create',
                'label' => __('clinic::lang.intake_form_create'),
                'default' => false,
            ],
            [
                'value' => 'intake.form.delete',
                'label' => __('clinic::lang.intake_form_delete'),
                'default' => false,
            ],
            // call center feedback permission end
            [
                'value' => 'patient.feedback.setting',
                'label' => __('clinic::lang.patient_feedback_setting'),
                'default' => false,
            ],         
            
            [
                'value' => 'clinic.category.create',
                'label' => __('clinic::lang.category_create'),
                'default' => false,
            ],
            [
                'value' => 'clinic.category.view',
                'label' => __('clinic::lang.category_view'),
                'default' => false,
            ],
            [
                'value' => 'clinic.category.edit',
                'label' => __('clinic::lang.category_edit'),
                'default' => false,
            ],
            [
                'value' => 'clinic.category.delete',
                'label' => __('clinic::lang.category_delete'),
                'default' => false,
            ],
           
            // test permission end            
            
            // Brand Permission Section
            [
                'value' => 'clinic.brand.create',
                'label' => __('clinic::lang.clinic_brand_create'),
                'default' => false,
            ],
            [
                'value' => 'clinic.brand.view',
                'label' => __('clinic::lang.clinic_brand_view'),
                'default' => false,
            ],
            [
                'value' => 'clinic.brand.update',
                'label' => __('clinic::lang.clinic_brand_update'),
                'default' => false,
            ],
            [
                'value' => 'clinic.brand.delete',
                'label' => __('clinic::lang.clinic_brand_delete'),
                'default' => false,
            ],
            // End Brand Permission Section
            // Disease Permission section
            [
                'value' => 'disease.show',
                'label' => __('clinic::lang.disease_list_show'),
                'default' => false,
            ], 
            [
                'value' => 'disease.store',
                'label' => __('clinic::lang.disease_store'),
                'default' => false,
            ], 
           
            [
                'value' => 'disease.update',
                'label' => __('clinic::lang.disease_update'),
                'default' => false,
            ], 
            [
                'value' => 'disease.delete',
                'label' => __('clinic::lang.disease_delete'),
                'default' => false,
            ], 
            // Disease Permission section end
            //Start Intake form Permission
           
            [
                'value' => 'business.day.delete',
                'label' => __('clinic::lang.business_day_delete'),
                'default' => false,
            ],
            [
                'value' => 'appointment.add_customer',
                'label' => __('clinic::lang.add_customer_in_appointment'),
                'default' => false,
            ],
            [
                'value' => 'clinic.register_report.view',
                'label' => __('clinic::lang.register_report_view'),
                'default' => false,
            ],
            [
                'value' => 'sell_payment_report.view',
                'label' => __('clinic::lang.sell_payment_report_view'),
                'default' => false,
            ]
            
            
        ];
    }

    /**
     * clinic Report options
     *
     * @return array
     */
    public function InboxReportOptions()
    {
        return [
            [
                'name' => 'clinic_report',
                'label' => __('clinic::lang.clinic_report'),
                'default' => false,
            ],
        ];
    }
    public function index()
    {
        return view('clinic::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function modifyAdminMenu()
    {
        $module_util = new ModuleUtil();

        $business_id = session()->get('user.business_id');
        $is_clinic_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'clinic_module');

        if ($is_clinic_enabled && auth()->user()->can('clinic.view')) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                    action([\Modules\Clinic\Http\Controllers\ClinicController::class, 'index']),
                    '&nbsp;&nbsp;'.__('clinic::lang.clinic'),
                    ['icon' => 'fas fa-clinic-medical', 'style' => config('app.env') == 'demo' ? 'background-color: yellow !important;' : '', 'active' => request()->segment(1) == 'clinic']
                )->order(50);
            });
        }
          
    }
    public function create()
    {
        return view('clinic::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('clinic::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('clinic::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
}
