<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\Crm\Database\Factories\CrmCallSubjectFactory;

class CrmCallSubject extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name'];

    // protected static function newFactory(): CrmCallSubjectFactory
    // {
    //     // return CrmCallSubjectFactory::new();
    // }
    public function callLogs()
    {
        return $this->belongsToMany(CrmCallLog::class, 'crm_call_log_call_subject');
    }
}
