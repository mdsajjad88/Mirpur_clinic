<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dosage extends Model
{
    use HasFactory;

    protected $table = 'dosage';
    protected $fillable = [
        'value'
    ];
}
