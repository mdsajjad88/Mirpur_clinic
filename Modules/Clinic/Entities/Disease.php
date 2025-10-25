<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Disease extends Model
{
    use HasFactory;
  
    protected $fillable = ['id', 'name', 'description', 'value', 'status', 'business_id', 'location_id', 'created_by', 'updated_by', 'helped_by', 'created_at', 'updated_at'];
    public function patientDiseases()
    {
        return $this->hasMany(PatientDisease::class, 'disease_id', 'id');
    }
    
}

