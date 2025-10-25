<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\User;
class DoctorSpecilities extends Model
{
    use HasFactory;

    protected $table = 'doctor_specialities';
    protected $fillable = [
        'doctor_profile_id', 'term_name', 'term_short_name', 'year_of_experience', 'certifications', 'business_id', 'location_id', 'created_by', 'modified_by', 'doctor_user_id',
    ];
    public function doctorProfile()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_profile_id');
    }

    // Relationship to Business

    // Relationship to User (for created_by and modified_by)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifiedBy()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    // Relationship to User (if applicable for doctor_user_id)
    public function doctorUser()
    {
        return $this->belongsTo(User::class, 'doctor_user_id');
    }
    
}
