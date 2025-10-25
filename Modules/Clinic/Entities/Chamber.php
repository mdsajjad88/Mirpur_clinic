<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chamber extends Model
{
    use HasFactory;

    protected $fillable = [];
    protected $table = 'doctor_chambers';

    protected static function newFactory()
    {
        return \Modules\Clinic\Database\factories\ChamberFactory::new();
    }
}
