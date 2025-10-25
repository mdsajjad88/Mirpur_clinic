<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Crm\Database\Factories\CallTagFactory;

class CallTag extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'key',
        'value',
    ];

    // protected static function newFactory(): CallTagFactory
    // {
    //     // return CallTagFactory::new();
    // }
    public function callLogs()
    {
        return $this->belongsToMany(CrmCallLog::class, 'crm_call_log_call_tag');
    }
}
