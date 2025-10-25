<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prescription extends Model
{
    use HasFactory;
    protected $table = 'prescriptions';
    protected $fillable = [
        'appointment_id',
        'doctor_user_id',
        'prescription_number',
        'visit_date',
        'prescription_date',
        'patient_contact_id',
        'patient_profile_id',
        'transaction_id',
        'membership_number',
        'name',
        'age',
        'gender',
        'current_weight',
        'current_height',
        'current_height_feet',
        'current_height_inches',
        'body_temp',
        'blood_pressure',
        'diastolic_pressure',
        'systolic_pressure',
        'diabetic_info',
        'pulse_rate',
        'status',
        'publish_date',
        'share_seen',
        'comments',
        'diagnoses_others_info',
        'natural_advise_others_info',
        'allopathic_advise_others_info',
        'next_visit_date',
        'template',
        'respiratory',
        'bmi',
        'body_fat_percent',
        'fat_mass_percent',
        'lean_mass_percent',
        'write_oe',
        'note',
        'follow_up',
        'follow_up_number',
        'follow_up_type',
        'created_by',
        'modified_by',
        'created_at',
        'updated_at',
        'start_time',
        'end_time',
        'template_id',
        'template_similarity_percentage'
    ];
    public function prescribedMedicines()
    {
        return $this->hasMany(PrescribeMedicine::class, 'prescription_id');
    }

    // Add this relationship for tests
    public function prescribedTests()
    {
        return $this->hasMany(PrescribeTest::class, 'prescription_id');
    }

    // Add this relationship for therapies
    public function prescribedTherapies()
    {
        return $this->hasMany(PrescribeTherapie::class, 'prescription_id');
    }

    public function dosage()
    {
        return $this->belongsTo(Dosage::class, 'taken_instruction');
    }

    public function medicineMeal()
    {
        return $this->belongsTo(MedicineMeal::class, 'dosage_form');
    }

    public function duration()
    {
        return $this->belongsTo(Duration::class, 'medication_duration');
    }

    public function template()
    {
        return $this->belongsTo(PrescriptionTemplate::class, 'template_id');
    }

    public function appointment() {
        return $this->belongsTo(PatientAppointmentRequ::class, 'appointment_id');
    }

    public function ipdAdmission()
    {
        return $this->hasOne(PrescribeIpdAdmission::class, 'prescription_id');
    }
}
