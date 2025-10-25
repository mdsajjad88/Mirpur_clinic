<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;

class BulkSmsHistory extends Model
{
    protected $table = 'bulk_sms_history';
    
    protected $fillable = [
        'business_id',
        'sms_body',
        'total_contacts',
        'success_count',
        'fail_count',
        'total_sms_count',
        'created_by'
    ];
    
    public function user()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}