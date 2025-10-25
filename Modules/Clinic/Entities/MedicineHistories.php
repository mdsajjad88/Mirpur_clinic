<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicineHistories extends Model
{
    use HasFactory;
    protected $table = 'prescribed_medicine_histories';
    protected $fillable = ['prescription_id', 'medicines', 'comments', 'created_by', 'created', 'modified'];
    
}
