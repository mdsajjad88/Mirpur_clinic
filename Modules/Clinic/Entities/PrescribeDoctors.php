<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrescribeDoctors extends Model
{
    use HasFactory;

    protected $table = 'prescription_doctors';
    protected $fillable = ['organization_profile_id', 'doctor_chamber_id', 'doctor_profile_id', 'prescription_id', 'comments', 'received_from_doctor', 'prescription_received_date', 'send_to_doctor', 'prescription_sending_date', 'is_current', 'created', 'modified', 'created_by', 'modified_by'];
   
}
