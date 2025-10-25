<?php

namespace Modules\Clinic\Utils;

use App\Utils\Util;
use Modules\Clinic\Entities\DoctorProfile;
use App\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class DoctorUtil extends Util
{
    public function createNewDoctor($input)
{
    $count = 0;

    if (!empty($input['email'])) {
        $count = User::where('email', $input['email'])->count();

        if ($count > 0) {
            Log::info('Email is duplicate: ' . $input['email']);
        } else {
            Log::info('Email is new: ' . $input['email']);
        }
    }

    if ($count == 0) {
        DB::beginTransaction(); // Start the transaction
        try {
            // Prepare user data
            $data = [
                // 'surname' => $input['first_name'],
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'contact_no' => $input['mobile'],
                'username' => 'awc' . strtolower(str_replace(' ', '', $input['first_name'])) . rand(1000, 9999),
                'email' => $input['email'],
                'password' => Hash::make('12345678'), // Hash the password
                'dob' => $input['dob'],
                'address' => $input['address'],
                'business_id'=>1,
            ];

            // Create the user
            $user = User::create($data);

            $doctorData = [
                'user_id' => $user->id,
                'first_name' => $input['first_name'],
                'last_name' => $input['last_name'],
                'mobile' => $input['mobile'],
                'email' => $input['email'], 
                'address' => $input['address'], 
                'date_of_birth' => $input['dob'],
                'created_by' => Auth::user()->id,
                'is_doctor' => $input['is_doctor'],
                'designation' => $input['designation'],
                'is_show_invoice' => $input['is_show_invoice'],
            ];
            // Log::info('is doctor'.$doctorData);
            $doctor = DoctorProfile::create($doctorData);

            // Log the successful creation
            Log::info('New doctor created:', [
                'user_id' => $user->id,
                'email' => $input['email'],
                'doctor_id' => $doctor->id,
            ]);

            DB::commit(); 
            
            return [
                'success' => true,
                'data' => $doctor,
                'msg' => __('clinic::doctor.added_success_ref'),
            ];
        } catch (\Exception $e) {
            DB::rollBack(); 
            Log::error('Error creating doctor:', [
                'message' => $e->getMessage(),
                'input' => $input,
            ]);
            throw new \Exception('Error Processing Request: ' . $e->getMessage(), 1);
        }
    } else {
        Log::warning('Attempt to create a doctor with existing email:', [
            'email' => $input['email'],
        ]);
        throw new \Exception('Error Processing Request: Email already exists.', 1);
    }
}

public function updateDoctor($input, $id)
{
    // Start a transaction
    DB::beginTransaction();

    try {
        $count = 0;

        if (!empty($input['email'])) {
            $count = User::where('email', $input['email'])
                ->where('id', '!=', $input['user_id']) // Exclude the current user's ID
                ->count();
        
            if ($count > 0) {
                Log::info('Email is duplicate: ' . $input['email']);
                throw new \Exception('Error Processing Request: Email already exists.', 1);
            } else {
                Log::info('Email is new: ' . $input['email']);
            }
        }
        // Prepare user data
        $data = [
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'contact_no' => $input['mobile'],
            'email' => $input['email'],
            'dob' => $input['dob'],
            'address' => $input['address'],
        ];

        // Update the user
        $user = User::findOrFail($input['user_id']);
        $user->update($data);

        // Prepare doctor profile data
        $doctorData = [
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'mobile' => $input['mobile'],
            'email' => $input['email'],
            'address' => $input['address'],
            'date_of_birth' => $input['dob'],
            'designation' => $input['designation'],
            'is_show_invoice' => $input['is_show_invoice'],
            'is_doctor' => $input['is_doctor'],
            'modified_by' => Auth::user()->id,
        ];

        // Update the doctor profile
        $doctor = DoctorProfile::findOrFail($id);
        $doctor->update($doctorData);

        // Commit the transaction
        DB::commit();

        return [
            'success' => true,
            'data' => $doctor,
            'msg' => __('clinic::doctor.update_success'),
        ];
    } catch (\Exception $e) {
        // Rollback the transaction if something failed
        DB::rollback();

        Log::error('Error updating doctor:', ['error' => $e->getMessage()]);

        throw new \Exception('Error Processing Request: ' . $e->getMessage(), 1);
    }
}
}
