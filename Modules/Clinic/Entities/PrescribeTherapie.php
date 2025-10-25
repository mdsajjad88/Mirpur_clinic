<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrescribeTherapie extends Model
{
    use HasFactory;
    protected $table = 'prescription_therapies';
    protected $fillable = [ 'prescription_id', 'product_id', 'therapy_name', 'comment', 'frequency','session_count', 'therapy_detail', 'therapy_instruction', 'created', 'modified', 'created_by', 'modified_by'];
    
    
}
