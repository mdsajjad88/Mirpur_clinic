<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorAppointment extends Model
{
    use HasFactory;
 
    protected $fillable = ['type_name'];
    
    protected static function newFactory()
    {
        return \Modules\Clinic\Database\factories\DoctorAppointmentFactory::new();
    }
}
