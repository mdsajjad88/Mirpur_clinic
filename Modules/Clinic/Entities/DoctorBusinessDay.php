<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\User;
class DoctorBusinessDay extends Model
{
    use HasFactory;
    protected $table = 'doctor_appointment_days';
    protected $fillable = [
        'doctor_profile_id', 'doctor_user_id', 'doctor_chamber_id', 'business_day_number', 'business_day_type', 'business_operating_hours', 'remarks', 'active_status', 'created_by', 'modified_by', 'business_id', 'location_id'
    ];
    public function doctor(){
        return $this->belongsTo(DoctorProfile::class, 'doctor_profile_id');
    }
    public function user(){
        return $this->belongsTo(User::class, 'doctor_user_id');
    }
}
