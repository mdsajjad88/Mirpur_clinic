<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $table = 'sms_logs';
    
    protected $fillable = [
        'bulk_sms_id',
        'contact_id',
        'mobile_number',
        'sms_body',
        'sms_length',
        'sms_count',
        'status',
        'response',
        'created_by'
    ];
    
    public function contact()
    {
        return $this->belongsTo(\App\Contact::class);
    }
    
    public function createdBy()
    {
        return $this->belongsTo(\App\User::class, 'created_by');
    }
}