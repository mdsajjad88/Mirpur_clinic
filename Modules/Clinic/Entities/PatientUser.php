<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientUser extends Model
{
    use HasFactory;

    protected $table = 'patient_users';
    protected $fillable = [
        'username', 'password', 'login_alias', 'change_password', 'active', 'user_body', 'created', 'modified', 'created_by', 'modified_by'
    ];
    public function profile()
    {
        return $this->hasOne(PatientProfile::class, 'patient_user_id');
    }
    
}
