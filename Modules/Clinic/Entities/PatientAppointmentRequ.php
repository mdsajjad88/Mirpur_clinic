<?php

namespace Modules\Clinic\Entities;

use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientAppointmentRequ extends Model
{
    use HasFactory;

    protected $table = 'patient_appointment_requests';

    protected $fillable = ['doctor_chamber_id', 'reference_doctor_id', 'doctor_profile_id', 'doctor_user_id', 'patient_profile_id', 'request_date', 'request_slot', 'doctor_appointment_slot_id', 'doctor_appointment_day_id', 'appointment_media', 'can_visit', 'confirmed_by', 'confirm_time', 'confirm_status', 'payment_status', 'remarks', 'is_visited', 'visited_date', 'created_by', 'created_name', 'modified_by', 'helped_by', 'appointment_number', 'change_history', 'reference_link', 'appointment_type_id', 'service_type_id', 'sub_service_type_ids', 'patient_referred_by', 'arrival_time', 'consultation_start_time', 'end_of_consultation_time', 'appointment_consultation_time_difference', 'consultation_duration', 'comments', 'opinions', 'business_id', 'location_id', 'color_code_hex', 'patient_subscription_id', 'is_subscribe', 'special_discount', 'subscription_discount', 'bill_no', 'service_appointment_slot_id', 'service_appointment_day_id', 'patient_contact_id', 'patient_session_info_id', 'type', 'cancel_status', 'appointment_type'];


    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_profile_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function contributor()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
    public function patient()
    {
        return $this->belongsTo(PatientProfile::class, 'patient_profile_id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'patient_contact_id');
    }
    
    public static function getAppointmentStatus($appointment)
    {
        $status = $appointment->payment_status;        
            return $status == 1 ?'Paid':'Due'; 
        return $status;
    }

    public function diseases()
    {
        return $this->belongsToMany(Problem::class, 'patient_diseases', 'patient_appointment_request_id', 'disease_id');
    }

    public function slot()
    {
        return $this->belongsTo(DoctorAppointmentSloot::class, 'slot_id');
    }

}
