<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Contact;
use App\District;

class PatientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_user_id', 'patient_contact_id', 'mobile', 'first_name', 'last_name', 'email', 'gender', 'date_of_birth', 'nid', 'disease_id', 'age', 'created_by', 'created', 'modified_by', 'modified', 'photo', 'photo_dir', 'blood_group', 'address', 'marital_status', 'height_cm', 'weight_kg', 'body_fat_percentage', 'home_phone', 'work_phone', 'city', 'state', 'post_code', 'country', 'emergency_contact_person', 'emergency_phone', 'emergency_relation', 'is_regular', 'is_subscriptions_3_months', 'is_subscriptions_6_months', 'has_text_consent', 'text_reminder', 'email_reminder', 'insurance_payer_name', 'insurance_payer_id', 'insurance_plan', 'insurance_group', 'discount', 'referral', 'is_active', 'is_deceased', 'remarks', 'address2', 'nick_name', 'address_alt', 'address2_alt', 'city_alt', 'state_alt', 'post_code_alt', 'patient_type_id', 'internal_comments', 'profession', 'marketing_source_by', 'marketing_source', 'agent_code_number', 'business_id', 'location_id', 'division_id', 'district_id', 'upazila_id'
    ];
    protected $table = 'patient_profiles';
    public function patientDiseases()
    {
        return $this->hasMany(PatientDisease::class, 'patient_profile_id', 'id');
    }
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'patient_contact_id');
    }
    public function patient_user(){
        return $this->belongsTo(PatientUser::class, 'patient_user_id');

    }
    public function district(){
        return $this->belongsTo(District::class, 'district_id');

    }
    public function problems()
    {
        return $this->belongsToMany(Problem::class, 'patient_diseases', 'patient_profile_id', 'disease_id');
    }
   
    
}
