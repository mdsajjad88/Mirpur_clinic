<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientDisease extends Model
{
    use HasFactory;
    protected $table = 'patient_diseases';
    protected $fillable = [
        'patient_profile_id', 'patient_appointment_request_id', 'disease_id', 'created_by'
    ];
    public function patientProfile()
{
    return $this->belongsTo(PatientProfile::class, 'patient_profile_id', 'id');
}
public function disease()
{
    return $this->belongsTo(Problem::class, 'disease_id', 'id');
}

}
