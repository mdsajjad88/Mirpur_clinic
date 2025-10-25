<?php 
namespace Modules\Clinic\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; // Import SoftDeletes
use App\User;

class DoctorDegree extends Model
{
    use HasFactory, SoftDeletes; // Use SoftDeletes trait

    protected $table = 'doctor_degrees'; // Should be a string

    protected $fillable = [
        'doctor_profile_id', 
        'degree_name', 
        'degree_short_name', 
        'certification_place', 
        'certification_date', 
        'business_id', 
        'location_id', 
        'created_by', 
        'modified_by', 
        'doctor_user_id',
    ];

    protected $casts = [
        'certification_date' => 'date', // Ensure correct data type
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'doctor_user_id');
    }

    public function doctor()
    {
        return $this->belongsTo(DoctorProfile::class, 'doctor_profile_id');
    }
}
