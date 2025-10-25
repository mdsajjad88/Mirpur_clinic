<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrescribeDiagonosis extends Model
{
    use HasFactory;
    protected $table = 'prescription_diagnoses';
    protected $fillable = ['prescription_id', 'diagnosis_id', 'diagnosis_name', 'comments', 'created', 'modified', 'created_by', 'modified_by'];
    
   
}
