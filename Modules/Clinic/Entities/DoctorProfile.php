<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorProfile extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'first_name', 'last_name', 'email', 'mobile', 'gender', 'bmdc_number', 'blood_group', 'token_name', 'address', 'date_of_birth', 'nid', 'description', 'show_in_pad', 'designation', 'is_show_invoice', 'medical_academic_summary', 'specialist', 'is_active', 'is_available', 'is_doctor', 'is_natural_certified', 'is_allopathic_certified', 'created_by', 'created', 'modified_by', 'modified', 'photo', 'photo_dir', 'fee', 'consultant_type', 'business_id', 'location_id', 'created_at', 'updated_at', 'serial_prefix', 'prefix_color', 'room', 'is_full_time', 'is_consultant', 'rf_id', 'type'];

    protected $table = 'doctor_profiles';

    public function user()
    {
        return $this->belongsTo(\App\User::class, 'user_id');
    }
    public function degrees() // Add this method
    {
        return $this->hasMany(DoctorDegree::class, 'doctor_profile_id');
    }

}
