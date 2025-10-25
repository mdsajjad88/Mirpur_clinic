<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CashRegisterTransaction extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    // In App\CashRegisterTransaction model
    public function cash_register()
    {
        return $this->belongsTo(\App\CashRegister::class, 'cash_register_id');
    }
}
