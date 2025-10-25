<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CampaignContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'contact_id',
        'status',
        'assigned_to',
        'called_at',
        'notes',
        'feedback_form_id',
    ];

}
