<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DummyPatient extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'mobile', 'is_done'];
    
    
}
