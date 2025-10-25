<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorAppointmentSloot extends Model
{
    use HasFactory;
    
    // Explicitly set the table name to avoid Laravel's auto-pluralization
    protected $table = 'doctor_appointment_slots';
    
    protected $fillable = [
        'doctor_chamber_id', 'doctor_profile_id', 'doctor_user_id', 
        'doctor_assistant_id', 'profile_id', 'calendar_year', 'calendar_month', 
        'calendar_day', 'calendar_date', 'slots', 'slot_capacity', 'slot_count', 
        'slot_reserved', 'slot_booked', 'slot_active', 'remarks', 'business_id', 
        'location_id', 'created_by', 'modified_by', 'slot_duration', 
        'doctor_appointment_day_id'
    ];
    

    public function appointments()
    {
        return $this->hasMany(PatientAppointmentRequ::class, 'doctor_appointment_slot_id');
    }

    public function confirmedAppointments()
    {
        return $this->hasMany(PatientAppointmentRequ::class, 'doctor_appointment_slot_id')
                    ->where('confirm_status', 'confirmed');
    }

    public function attendedAppointments()
    {
        return $this->hasMany(PatientAppointmentRequ::class, 'doctor_appointment_slot_id')
                    ->where('remarks', 'prescribed');
    }
}