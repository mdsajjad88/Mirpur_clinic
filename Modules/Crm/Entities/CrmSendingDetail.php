<?php

namespace Modules\Crm\Entities;

use App\Contact;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmSendingDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'crm_campaign_id',
        'customer_id',
        'customer_name',
        'mobile',
        'send_by',
        'notification_date',
        'status'
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'crm_campaign_id'); // Adjust accordingly
    }


    public function sendBy()
    {
        return $this->belongsTo(User::class, 'send_by');
    }
    public function customer()
    {
        return $this->belongsTo(Contact::class, 'customer_id');
    }

    // protected static function newFactory()
    // {
    //     return \Modules\Crm\Database\factories\CrmSendingDetailFactory::new();
    // }php artisan migrate --path=\Modules\Crm\Database\Migrations\2024_09_26_131439_create_crm_sending_details_table.php
}
