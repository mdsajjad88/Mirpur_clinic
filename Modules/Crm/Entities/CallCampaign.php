<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CallCampaign extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_filter_id', 'name', 'description', 'survey_type_id', 'start_date', 'end_date', 'status', 'target_count', 'completed_count', 'filters', 'created_by',
    ];

    public function surveyType()
    {
        return $this->belongsTo(\Modules\Clinic\Entities\SurveyType::class, 'survey_type_id');
    }
    
}
