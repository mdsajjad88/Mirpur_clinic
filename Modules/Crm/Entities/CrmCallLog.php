<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;

class CrmCallLog extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];
    public function callSubjects()
    {
        return $this->belongsToMany(CrmCallSubject::class, 'crm_call_log_call_subject');
    }

    public function callTags()
    {
        return $this->belongsToMany(CallTag::class, 'crm_call_log_call_tag');
    }
}
