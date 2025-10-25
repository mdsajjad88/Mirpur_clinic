<?php

namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Product;
class PrescribeMedicine extends Model
{
    use HasFactory;
    protected $table = 'prescribed_medicines';
    protected $fillable = [ 'patient_profile_id', 'comment','doctor_profile_id', 'prescription_id', 'x_medicine_id', 'x_medicine_name', 'generic_id', 'generic_name', 'dosage_form', 'medicine_unit', 'medicine_quantity', 'taken_instruction', 'taken_instruction_detail', 'stomach_status', 'medication_duration', 'other_instruction', 'is_natural', 'created', 'modified', 'created_by', 'modified_by', 'created_at', 'updated_at'];  
    public function product(){
        return $this->belongsTo(Product::class, 'x_medicine_id', 'id');
    }

    public function prescription()
    {
        return $this->belongsTo(Prescription::class, 'prescription_id');
    }
    public function dosage()
    {
        return $this->belongsTo(Dosage::class, 'dosage_form', 'id');
    }

    public function medicineMeal()
    {
        return $this->belongsTo(MedicineMeal::class, 'taken_instruction', 'id');
    }

    public function duration()
    {
        return $this->belongsTo(Duration::class, 'medication_duration', 'id');
    }

}
