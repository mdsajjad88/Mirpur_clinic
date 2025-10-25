<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorAdvice extends Model
{
    use HasFactory;
    protected $table = 'doctor_advise';

    protected $fillable = ['organization_profile_id', 'value', 'type', 'status', 'created_by', 'modified_by', 'helped_by', 'created', 'modified', 'created_at', ''];
    public function scopeActive($query)
    {
        return $query->where('status', true); // Assuming 'is_active' is the column that indicates if the advice is active
    }
}
