<?php

namespace Modules\Clinic\Utils;

use App\Utils\Util;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use Modules\Clinic\Entities\PatientSessionInfo;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Transaction;
use Modules\Clinic\Entities\Prescription;
use Carbon\Carbon;

class AppointmentUtil extends Util
{

    public function getAppointmentQuery($patient_profile_id = null, $type)
    {
        $today = Carbon::today()->toDateString();
        $query = PatientAppointmentRequ::select(
            'patient_appointment_requests.id',
            'patient_appointment_requests.created_at',
            'patient_appointment_requests.updated_at',
            'patient_appointment_requests.request_date',
            'patient_appointment_requests.bill_no',
            'patient_appointment_requests.appointment_media',
            'patient_appointment_requests.type',
            'patient_appointment_requests.request_slot',
            'patient_appointment_requests.remarks',
            'patient_appointment_requests.comments',
            'patient_appointment_requests.call_status',
            'patient_appointment_requests.confirm_time',
            'patient_appointment_requests.created_by',
            'patient_appointment_requests.confirmed_by',
            'patient_appointment_requests.appointment_type',
            'patient_appointment_requests.patient_session_info_id',
            'patient_appointment_requests.confirm_status',
            'patient_appointment_requests.cancel_status',
            'doctor_profiles.prefix_color as prefixColor',
            'doctor_profiles.first_name as doctor_first_name',
            'patient_profiles.first_name as patient_first_name',
            'patient_profiles.last_name as patient_last_name',
            'patient_profiles.mobile as patient_mobile',
            'doctor_s_ls.sl_no as sl_no',
            'doctor_s_ls.id as sl_id',
            'doctor_s_ls.sl_without_prefix as sl_without_prefix',
            'doctor_profiles.room as doctor_room',
            'doctor_profiles.is_available as is_available',
            'doctor_s_ls.status as sl_status',
            'dasl.date as break_date',
            'dasl.break_start_time as break_start_time',
            'dasl.end_time as break_end_time',
            'contacts.contact_id as customer_id',
            'session_information.sub_type as subscription_type',
            DB::raw("CASE WHEN patient_appointment_requests.bill_no IS NULL THEN 'Not Final' 
                    WHEN patient_appointment_requests.remarks = 'cancelled' THEN 'Cancelled' 
                    ELSE transactions.payment_status 
                END as transaction_payment_status"),
            DB::raw("CONCAT(doctor_profiles.first_name, ' ', COALESCE(doctor_profiles.last_name, '')) as doctor_name")
        )
            ->join('doctor_profiles', 'patient_appointment_requests.doctor_profile_id', '=', 'doctor_profiles.id')
            ->join('patient_profiles', 'patient_appointment_requests.patient_profile_id', '=', 'patient_profiles.id')
            ->leftJoin('transactions', 'patient_appointment_requests.bill_no', '=', 'transactions.id')
            ->leftJoin('doctor_s_ls', 'doctor_s_ls.appointment_id', '=', 'patient_appointment_requests.id')
            ->leftJoin('contacts', 'contacts.id', '=', 'patient_profiles.patient_contact_id')
            ->leftJoin('patient_session_info', 'patient_session_info.id', '=', 'patient_appointment_requests.patient_session_info_id')
            ->leftJoin('session_information', 'session_information.id', '=', 'patient_session_info.session_id')
            ->leftJoin(DB::raw("(SELECT id, doctor_profile_id, date, break_start_time, end_time
            FROM doctor_available_status_logs AS t1
            WHERE (t1.end_time IS NULL OR t1.end_time = '00:00:00')
            AND t1.date = '$today'
            AND NOT EXISTS (
                SELECT 1 FROM doctor_available_status_logs t2
                WHERE t2.doctor_profile_id = t1.doctor_profile_id
                    AND t2.id > t1.id
                    AND (t2.end_time IS NULL OR t2.end_time = '00:00:00')
                    AND t2.date = '$today'
            )
        ) as dasl"), 'dasl.doctor_profile_id', '=', 'doctor_profiles.id')   
            ->where('patient_appointment_requests.appointment_type', $type);

        if (!empty($patient_profile_id)) {
            $query->where('patient_appointment_requests.patient_profile_id', $patient_profile_id);
        }


        $todayAppointments = PatientAppointmentRequ::select('id', 'bill_no', 'remarks')
            ->whereDate('request_date', Carbon::today())
            ->get();

        foreach ($todayAppointments as $data) {
            if ($data->bill_no && !in_array($data->remarks, ['refunded', 'confirmed'])) {
                $this->updateRemarksForRefundedTransaction($data->bill_no);
            }
        }

        return $query;
    }

    public function updateRemarksForRefundedTransaction($transaction_id)
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::where('return_parent_id', $transaction_id)->first();
            if ($transaction && $transaction->type == 'sell_return') {
                $appointment = PatientAppointmentRequ::where('bill_no', $transaction_id)->first();
                if ($appointment) {
                    if ($appointment->remarks !== 'refunded') {
                        $appointment->remarks = 'refunded';
                        $appointment->save();
                        $sessionInfo = PatientSessionInfo::where('patient_contact_id', $appointment->patient_contact_id)->latest()->first();
                        if ($sessionInfo) {
                            if ($sessionInfo->visited_count < 1) {
                                Log::info('Deleting session info for patient_contact_id ' . $appointment->patient_contact_id);
                                $sessionInfo->delete();
                            }
                        }
                    }
                }
            }
            $payment = Transaction::where('id', $transaction_id)->first();
            if ($payment && $payment->status == 'final' && $payment->payment_status != 'due') {
                $appointments = PatientAppointmentRequ::where('bill_no', $transaction_id)->get();
                foreach ($appointments as $appointment) {
                    if ($appointment->remarks == 'booked' && $appointment->confirm_status === 1) {
                        $appointment->remarks = 'confirmed';
                        $appointment->confirm_time = now();
                        $appointment->save();
                    }
                }
            }


            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in updateRemarksForRefundedTransaction: ' . $e->getMessage());
            return false;
        }
    }



    public function getConfirmAppointmentQuery($patient_profile_id = null)
    {
        $user = Auth::user();
        $query = PatientAppointmentRequ::from('patient_appointment_requests as appointment')
            ->join('doctor_profiles as dp', 'appointment.doctor_profile_id', '=', 'dp.id')
            ->join('patient_profiles as pp', 'appointment.patient_profile_id', '=', 'pp.id')
            ->join('intake_form_info as intake_form', 'appointment.patient_contact_id', '=', 'intake_form.patient_contact_id')
            ->join('districts as dist', 'intake_form.district_id', '=', 'dist.id')
            ->leftJoin('report_and_problems as disease', 'disease.intake_form_id', '=', 'intake_form.id')
            ->leftJoin('doctor_s_ls as doctor_sl', 'doctor_sl.appointment_id', '=', 'appointment.id')
            ->leftJoin('problems AS problems', 'problems.id', '=', 'disease.problem_id')
            ->leftJoin('prescriptions as pres', 'pres.appointment_id', '=', 'appointment.id')
            ->whereIn('appointment.remarks', ['confirmed', 'refunded', 'prescribed', 'expired'])
            ->where('appointment.confirm_status', 1)
            ->select(
                DB::raw("CONCAT(dp.first_name, ' ', COALESCE(dp.last_name, '')) as doctor_name"),
                DB::raw("CONCAT(pp.first_name, ' ', COALESCE(pp.last_name, '')) as patient_name"),
                DB::raw("GROUP_CONCAT(DISTINCT problems.name SEPARATOR ', ') as diseases"),
                DB::raw("CASE 
                WHEN appointment.remarks = 'prescribed' 
                     AND pres.created_at IS NOT NULL 
                     AND pres.created_at = pres.updated_at 
                THEN TRUE
                WHEN appointment.remarks = 'prescribed' 
                     AND pres.created_at IS NOT NULL 
                     AND pres.created_at <> pres.updated_at 
                THEN FALSE
                ELSE NULL
                END as created_updated_same"),
                DB::raw("(SELECT COUNT(*) 
         FROM patient_appointment_requests a2
         JOIN patient_profiles p2 ON a2.patient_profile_id = p2.id
         WHERE p2.mobile = pp.mobile
           AND a2.id <> appointment.id
           AND a2.remarks = 'prescribed'
           AND a2.request_date = appointment.request_date
        ) as family_count"),
                'pp.mobile as mobile',
                'pp.age as age',
                'pp.gender as gender',
                'dp.first_name as doctor_first_name',
                'dp.last_name as doctor_last_name',
                'dp.is_available as is_doctor_available',
                'dist.name as patient_district',
                'appointment.patient_contact_id as customerId',
                'appointment.id as appId',
                'appointment.remarks as status',
                'appointment.request_date as appointment_date',
                'appointment.appointment_number as appointment_no',
                'appointment.confirm_time as waiting_time_start',
                'appointment.created_at',
                'appointment.request_slot',
                'appointment.can_visit',
                'appointment.is_visited',
                'appointment.updated_at',
                'appointment.appointment_type',
                'appointment.appointment_media',
                'appointment.type as patient_type',
                'doctor_sl.sl_no as serial_number',
                'dp.serial_prefix as serial_prefix',
                'dp.prefix_color as prefixColor',
            )
            ->groupBy(
                'appointment.id',
                'dp.first_name',
                'dp.last_name',
                'pp.first_name',
                'pp.last_name',
                'pp.mobile',
                'pp.age',
                'pp.gender',
                'dist.name',
                'appointment.patient_contact_id',
                'appointment.id',
                'appointment.request_date',
                'appointment.appointment_number',
                'appointment.request_slot',
                'patient_type'
            );

        if ($this->is_doctor($user)) {
            $query->where('appointment.doctor_user_id', $user->id);
        }

        if (!empty($patient_profile_id)) {
            $query->where('patient_appointment_requests.patient_profile_id', $patient_profile_id);
        }

        return $query;
    }
    public function getTherapyPatientData()
    {

        $user = Auth::user();
        $query = PatientAppointmentRequ::from('patient_appointment_requests as appointment')
            ->join('doctor_profiles as dp', 'appointment.doctor_profile_id', '=', 'dp.id')
            ->join('patient_profiles as pp', 'appointment.patient_profile_id', '=', 'pp.id')
            ->join('intake_form_info as intake_form', 'appointment.patient_contact_id', '=', 'intake_form.patient_contact_id')
            ->join('districts as dist', 'intake_form.district_id', '=', 'dist.id')
            ->leftJoin('report_and_problems as disease', 'disease.intake_form_id', '=', 'intake_form.id')
            ->leftJoin('doctor_s_ls as doctor_sl', 'doctor_sl.appointment_id', '=', 'appointment.id')
            ->leftJoin('problems AS problems', 'problems.id', '=', 'disease.problem_id')
            ->leftJoin('prescriptions as pres', 'pres.appointment_id', '=', 'appointment.id')
            ->whereIn('appointment.remarks', ['confirmed', 'refunded', 'prescribed', 'expired'])
            ->where('appointment.confirm_status', 1)
            ->select(
                DB::raw("CONCAT(dp.first_name, ' ', COALESCE(dp.last_name, '')) as doctor_name"),
                DB::raw("CONCAT(pp.first_name, ' ', COALESCE(pp.last_name, '')) as patient_name"),
                DB::raw("GROUP_CONCAT(DISTINCT problems.name SEPARATOR ', ') as diseases"),
                DB::raw("CASE 
                WHEN appointment.remarks = 'prescribed' 
                     AND pres.created_at IS NOT NULL 
                     AND pres.created_at = pres.updated_at 
                THEN TRUE
                WHEN appointment.remarks = 'prescribed' 
                     AND pres.created_at IS NOT NULL 
                     AND pres.created_at <> pres.updated_at 
                THEN FALSE
                ELSE NULL
                END as created_updated_same"),
                'pp.mobile as mobile',
                'pp.age as age',
                'pp.gender as gender',
                'dp.first_name as doctor_first_name',
                'dp.last_name as doctor_last_name',
                'dp.is_available as is_doctor_available',
                'dist.name as patient_district',
                'appointment.patient_contact_id as customerId',
                'appointment.id as appId',
                'appointment.remarks as status',
                'appointment.request_date as appointment_date',
                'appointment.appointment_number as appointment_no',
                'appointment.confirm_time as waiting_time_start',
                'appointment.created_at',
                'appointment.request_slot',
                'appointment.can_visit',
                'appointment.is_visited',
                'appointment.updated_at',
                'appointment.type as patient_type',
                'doctor_sl.sl_no as serial_number',
                'dp.serial_prefix as serial_prefix',
                'dp.prefix_color as prefixColor',
            )
            ->groupBy(
                'appointment.id',
                'dp.first_name',
                'dp.last_name',
                'pp.first_name',
                'pp.last_name',
                'pp.mobile',
                'pp.age',
                'pp.gender',
                'dist.name',
                'appointment.patient_contact_id',
                'appointment.id',
                'appointment.request_date',
                'appointment.appointment_number',
                'appointment.request_slot',
                'patient_type'
            );

        if ($this->is_doctor($user)) {
            $query->where('appointment.doctor_user_id', $user->id);
        }

        return $query;
    }

    // AppointmentUtil (বা যেখানেই আছে)
    public function getPrescribedData()
    {
        return Prescription::from('prescriptions as pres')
            ->join('patient_appointment_requests as appointment', 'pres.appointment_id', '=', 'appointment.id')
            ->join('doctor_profiles as dp', 'appointment.doctor_profile_id', '=', 'dp.id')
            ->join('patient_profiles as pp', 'appointment.patient_profile_id', '=', 'pp.id')
            ->join('contacts', 'contacts.id', '=', 'pp.patient_contact_id')
            ->join('intake_form_info as intake_form', 'appointment.patient_contact_id', '=', 'intake_form.patient_contact_id')
            ->join('districts as dist', 'intake_form.district_id', '=', 'dist.id')
            ->leftJoin('report_and_problems as disease', 'disease.intake_form_id', '=', 'intake_form.id')
            ->leftJoin('doctor_s_ls as doctor_sl', 'doctor_sl.appointment_id', '=', 'appointment.id')
            ->leftJoin('problems AS problems', 'problems.id', '=', 'disease.problem_id')

            // ← NutritionistPrescription attach করলাম (to detect created/edit status)
            ->leftJoin('nutritionist_prescriptions as np', 'np.prescription_id', '=', 'pres.id')
            ->leftJoin('users as creator', 'np.created_by', '=', 'creator.id')
            ->leftJoin('users as editor', 'np.updated_by', '=', 'editor.id')

            ->select(
                DB::raw("CONCAT(dp.first_name, ' ', COALESCE(dp.last_name, '')) as doctor_name"),
                DB::raw("CONCAT(pp.first_name, ' ', COALESCE(pp.last_name, '')) as patient_name"),
                DB::raw("GROUP_CONCAT(DISTINCT problems.name SEPARATOR ', ') as diseases"),

                // patient/doctor/proxy fields
                'contacts.contact_id as contact_id',
                'pp.mobile',
                'pp.age',
                'pp.gender',
                'dp.first_name as doctor_first_name',
                'dp.last_name as doctor_last_name',
                'doctor_sl.sl_no as serial_number',

                // prescription keys
                'pres.id as prescription_id',
                'pres.prescription_date',
                'pres.created_at as pres_created_at',
                'appointment.id as appId',
                'appointment.request_slot',

                // nutritionist flags
                'np.id as nu_pres_id',
                'np.created_at as nu_created_at',
                'np.updated_at as nu_updated_at',
                'np.type as pres_type',
                'creator.username as completed_by',
                'editor.username as last_updated_by',
                'editor.id as last_updated_by_id'
            )
            ->where('appointment.remarks', 'prescribed')
            ->groupBy(
                'appointment.id',
                'dp.first_name',
                'dp.last_name',
                'pp.first_name',
                'pp.last_name',
                'pp.mobile',
                'pp.age',
                'pp.gender',
                'dist.name',
                'pres.id',
                'np.id',
                'np.created_at',
                'np.updated_at',
                'creator.username',
                'editor.username',
                'doctor_sl.sl_no',
                'pres.prescription_date',
                'appointment.request_slot'
            );
    }
}
