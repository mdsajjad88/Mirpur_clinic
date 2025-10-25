<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FacebookLead extends Model
{
    use HasFactory;

    protected $fillable = [
        'fb_lead_id',
        'page_id',
        'created_time',
        'ad_id',
        'ad_name',
        'adset_id',
        'adset_name',
        'campaign_id',
        'campaign_name',
        'form_id',
        'form_name',
        'is_organic',
        'platform',
        'full_name',
        'email',
        'phone_number',
        'city',
        'inbox_url',
        'lead_status',
        'raw_data',
        'ai_information',
        'raw_payload',
    ];

    protected $casts = [
        'ai_information' => 'array',
        'raw_payload' => 'array',
        'raw_data' => 'array',
        'is_organic' => 'boolean',
        'created_time' => 'datetime',
    ];
}
