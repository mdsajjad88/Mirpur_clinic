<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrescribeAdvice extends Model
{
    use HasFactory;
    protected $table = 'prescription_advises';
    protected $fillable = [
        'prescription_id', 'nu_prescription_id', 'advice_id', 'advise_name', 'is_natural', 'type', 'created_by', 'modified_by', 'created_at', 'updated_at'
    ];
    
}
