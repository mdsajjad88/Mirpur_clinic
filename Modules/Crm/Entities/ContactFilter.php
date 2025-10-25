<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContactFilter extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'target_count',
        'completed_count',
        'filters',
        'contact_ids',
        'created_by',
    ];

    public function surveyType()
    {
        return $this->belongsTo(\Modules\Clinic\Entities\SurveyType::class, 'survey_type_id');
    }
}
