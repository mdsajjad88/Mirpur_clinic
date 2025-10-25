<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrescribeDetails extends Model
{
    use HasFactory;
    protected $table = 'prescription_details';
    protected $fillable = ['prescription_id', 'patient_problem', 'doctor_investigation', 'doctor_findings', 'doctor_lifestyle_advice', 'doctor_diet_advice', 'doctor_food_advice', 'created', 'modifed', 'created_by', 'modified_by'];
    
}
