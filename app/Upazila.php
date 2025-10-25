<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upazila extends Model
{
    use HasFactory;

    protected $table = 'upazilas';

    public function district()
    {
        return $this->belongsTo(District::class);
    }
}
