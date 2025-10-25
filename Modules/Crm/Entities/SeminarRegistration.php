<?php

namespace Modules\Crm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeminarRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_type_id', 'patient_contact_id', 'primary_diseases_id', 'secondary_diseases_id', 'invoice_number', 'comment', 'trx_id', 'status', 'c_status', 'c_comment', 'c_diseases', 'seminar_date', 'seminar_time', 'modified_by',
    ];
}
