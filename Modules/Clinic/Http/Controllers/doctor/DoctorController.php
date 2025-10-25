<?php

namespace Modules\Clinic\Http\Controllers\doctor;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Modules\Clinic\Entities\DoctorDegree;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Modules\Clinic\Entities\DoctorProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Clinic\Entities\DoctorSpecilities;

class DoctorController extends Controller
{
    /**
     *
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;
    protected $commonUtil;
    public function __construct(
        ModuleUtil $moduleUtil,
        Util $commonUtil,
    ) {
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }

    public function index()
    {
        return view('clinic::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('clinic::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('clinic::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $provider = DoctorProfile::find($id);
        $gender = ['male' => 'Male', 'female' => 'Female', 'other' => 'Other'];
        $bloods = ['A+' => 'A+', 'B+' => 'B+', 'AB+' => 'AB+', 'O+' => 'O+', 'A-' => 'A-', 'B-' => 'B-', 'AB-' => 'AB-', 'O-' => 'O-'];
        return view('clinic::provider.partials.profile_edit', compact('provider', 'gender', 'bloods'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $doctor = DoctorProfile::find($id);

        if (!$doctor) {
            Log::error('Doctor profile not found for user id: ' . $id);
            return response()->json(['success' => false, 'msg' => 'Doctor profile not found']);
        }

        try {
            $validatedData = $request->validate([
                'first_name' => 'required|string|max:255',
                'last_name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255|unique:doctor_profiles,email,' . $id,
                'mobile' => 'required|string|max:15',
                'gender' => 'required|string',
                'bmdc_number' => 'required|string|max:255',
                'blood_group' => 'required|string|max:10',
                'token_name' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'date_of_birth' => 'nullable|date',
                'nid' => 'nullable|string|max:20',
                'description' => 'nullable|string',
                'show_in_pad' => 'nullable|string',
                'serial_prefix' => 'required|string|max:50',
                'prefix_color' => 'required|string|max:50',
                'room' => 'nullable|string|max:50',
                'fee' => 'nullable|numeric',
                'specialist' => 'nullable|string|max:255',
                'created_by' => 'nullable|integer',
                'modified_by' => 'nullable|integer',
                'is_full_time' => 'required|boolean',
                'is_consultant' => 'required|boolean',
                'rf_id' => 'nullable|integer',
                'type'=>'required|string',
            ]);

            // Ensure modified_by is updated
            $validatedData['modified_by'] = Auth::id();

            // Directly update fields
            $doctor->fill($validatedData);

            // Force updating serial_prefix if mass-assignment fails
            if (array_key_exists('serial_prefix', $validatedData)) {
                $doctor->serial_prefix = $validatedData['serial_prefix'];
            }

            $doctor->save();

            Log::info('Doctor profile updated successfully for user id: ' . $id);

            return response()->json([
                'success' => true,
                'msg' => 'Profile updated successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update doctor profile for user id: ' . $id . ' - ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'msg' => 'Failed to update profile',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
    public function addDegrees($id)
    {
        return view('clinic::provider.partials.add_degree', compact('id'));
    }
    public function addSpecilities($id)
    {
        return view('clinic::provider.partials.specialities_add', compact('id'));
    }


    public function storeDegrees(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'degree_name' => 'required|string|max:255',
            'degree_short_name' => 'required|string|max:50',
            'certification_place' => 'required|string|max:255',
            'certification_date' => 'nullable|date', // Use 'date' for optional date
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'msg' => 'Validation errors occurred.',
                'errors' => $validator->errors(),
            ]);
        }
        $showInPad = $request->input('show_in_pad', []);

        try {
            // Create a new DoctorDegree instance
            $degree = new DoctorDegree();
            $degree->doctor_profile_id = $request->input('doctor_profile_id'); // Set the foreign key
            $degree->degree_name = $request->input('degree_name');
            $degree->degree_short_name = $request->input('degree_short_name');
            $degree->certification_place = $request->input('certification_place');
            $degree->certification_date = $request->input('certification_date');
            $degree->show_in_pad = json_encode($showInPad);

            // Save the degree to the database
            if ($degree->save()) {
                return response()->json([
                    'success' => true,
                    'msg' => 'Degree added successfully!',

                ]);
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error saving degree: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'msg' => 'Failed to add the degree. Please try again.',
            ]);
        }

        // If saving fails without an exception, return an error response
        return response()->json([
            'success' => false,
            'msg' => 'Failed to add the degree. Please try again.',
        ]);
    }
    public function checkUniqueName(Request $request)
    {
        $degreeName = $request->input('name'); // Degree name from the request
        $degreeId = $request->input('degree_id'); // Degree ID (for update), if provided
        $doctorId = $request->input('doctor_id'); // Degree ID (for update), if provided

        // Default to valid
        $valid = 'true';

        // Check if degree name is provided
        if (!empty($degreeName)) {
            $query = DoctorDegree::where('degree_name', $degreeName)->where('doctor_profile_id', $doctorId);
            if ($degreeId) {
                $query->where('id', '!=', $degreeId); // Exclude the current degree ID
            }
            $count = $query->count();
            if ($count > 0) {
                $valid = 'false'; // Name already exists
            }
        }

        echo $valid; // Return as JSON
        exit;
    }


    public function checkSpecialitiesName(Request $request)
    {
        $term_name = $request->input('name'); // Degree name from the request
        $specialitiesId = $request->input('specialities_id'); // Degree ID (for update), if provided
        $doctorId = $request->input('doctor_id'); // Degree ID (for update), if provided

        // Default to valid
        $valid = 'true';

        // Check if degree name is provided
        if (!empty($term_name)) {
            $query = DoctorSpecilities::where('term_name', $term_name)->where('doctor_profile_id', $doctorId);
            if ($specialitiesId) {
                $query->where('id', '!=', $specialitiesId); // Exclude the current degree ID
            }
            $count = $query->count();
            if ($count > 0) {
                $valid = 'false'; // Name already exists
            }
        }
        echo $valid;
        exit;
    }
    public function deleteDegrees($id)
    {
        if (request()->ajax()) {
            try {
                // Find the doctor profile
                $degree = DoctorDegree::find($id);

                if (!$degree) {
                    return response()->json([
                        'success' => false,
                        'msg' => 'Data not found',
                    ]);
                }
                $degree->delete();

                return response()->json([
                    'success' => true,
                    'msg' => 'Data Deleted Successfully',
                ]);
            } catch (\Exception $e) {

                // Log the exception details
                Log::emergency('Error deleting doctor: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'id' => $id,
                ]);

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return response()->json($output);
        }
    }
    public function degreeEdit($id)
    {
        $degree = DoctorDegree::find($id);
        return view('clinic::provider.partials.degree_edit', compact('degree'));
    }
    public function specilitiesEdit($id)
    {
        $specilities = DoctorSpecilities::find($id);
        return view('clinic::provider.partials.specialities_edit', compact('specilities'));
    }
    public function updateDegrees(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'degree_name' => 'required|string|max:255',
            'degree_short_name' => 'required|string|max:50',
            'certification_place' => 'required|string|max:255',
            'certification_date' => 'nullable|date', // Use 'date' for optional date
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'msg' => 'Validation errors occurred.',
                'errors' => $validator->errors(),
            ]);
        }

        try {
            // Find the degree by ID
            $degree = DoctorDegree::find($id);

            // Check if the degree exists
            if (!$degree) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Degree not found.',
                ]);
            }
            $showInPad = $request->input('show_in_pad', []);

            // Update degree details
            // $degree->doctor_profile_id = $request->input('doctor_profile_id'); // Set the foreign key
            $degree->degree_name = $request->input('degree_name');
            $degree->degree_short_name = $request->input('degree_short_name');
            $degree->certification_place = $request->input('certification_place');
            $degree->certification_date = $request->input('certification_date');
            $degree->show_in_pad = json_encode($showInPad);

            // Save the degree to the database
            if ($degree->save()) {
                // Log successful update
                Log::info('Degree updated successfully', [
                    'degree_id' => $degree->id,
                    'updated_data' => $degree,
                ]);

                return response()->json([
                    'success' => true,
                    'msg' => 'Degree updated successfully!',
                ]);
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error updating degree: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'msg' => 'Failed to update the degree. Please try again.',
            ]);
        }

        // If saving fails without an exception, return an error response
        return response()->json([
            'success' => false,
            'msg' => 'Failed to update the degree. Please try again.',
        ]);
    }
    public function getDegrees(Request $request, $id)
    {
        if ($request->ajax()) {
            $degrees = DoctorDegree::where('doctor_profile_id', $id)->get(); // Adjust according to your model and relationship
            return datatables()->of($degrees)
                ->addColumn('action', function ($degree) {
                    return '<a href="' . action([\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'degreeEdit'], [$degree->id]) . '" class="edit_degree_button btn btn-primary btn-sm"><i class="glyphicon glyphicon-edit"></i> Edit</a>
                        <a href="' . action([\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'deleteDegrees'], [$degree->id]) . '" class="delete_degree_button btn btn-danger btn-sm"><i class="glyphicon glyphicon-trash"></i> Delete</a>';
                })
                ->make(true);
        }
    }
    public function getSpecilities(Request $request, $id)
    {
        if ($request->ajax()) {
            $specilities = DoctorSpecilities::where('doctor_profile_id', $id)->get(); // Adjust according to your model and relationship
            return datatables()->of($specilities)
                ->addColumn('action', function ($specility) {
                    return '<a href="' . action([\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'specilitiesEdit'], [$specility->id]) . '" class="edit_specilities_button btn btn-primary btn-sm"><i class="glyphicon glyphicon-edit"></i> Edit</a>
                        <a href="' . action([\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'deleteSpecilities'], [$specility->id]) . '" class="delete_specilities_button btn btn-danger btn-sm"><i class="glyphicon glyphicon-trash"></i> Delete</a>';
                })
                ->make(true);
        }
    }
    public function storeSpecialities(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'term_name' => 'required|string|max:255',
            'term_short_name' => 'nullable|string|max:50',
            'year_of_experience' => 'nullable|string|max:255',
            'certifications' => 'required', // Use 'date' for optional date
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'msg' => 'Validation errors occurred.',
                'errors' => $validator->errors(),
            ]);
        }

        try {
            $specilities = new DoctorSpecilities();
            $specilities->doctor_profile_id = $request->input('doctor_profile_id'); // Set the foreign key
            $specilities->term_name = $request->input('term_name'); // Set the foreign key
            $specilities->term_short_name = $request->input('term_short_name');
            $specilities->year_of_experience = $request->input('year_of_experience');
            $specilities->certifications = $request->input('certifications');
            // Save the degree to the database
            if ($specilities->save()) {
                return response()->json([
                    'success' => true,
                    'msg' => 'Degree added successfully!',

                ]);
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error saving degree: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'msg' => 'Failed to add the degree. Please try again.',
            ]);
        }

        // If saving fails without an exception, return an error response
        return response()->json([
            'success' => false,
            'msg' => 'Failed to add the degree. Please try again.',
        ]);
    }
    public function deleteSpecilities($id)
    {
        if (request()->ajax()) {
            try {
                // Find the doctor profile
                $specilities = DoctorSpecilities::find($id);

                if (!$specilities) {
                    return response()->json([
                        'success' => false,
                        'msg' => 'Data not found',
                    ]);
                }
                $specilities->delete();

                return response()->json([
                    'success' => true,
                    'msg' => 'Data Deleted Successfully',
                ]);
            } catch (\Exception $e) {

                // Log the exception details
                Log::emergency('Error deleting doctor: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'id' => $id,
                ]);

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return response()->json($output);
        }
    }

    public function updateSpecialities(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), [
            'term_name' => 'required|string|max:255',
            'term_short_name' => 'nullable|string|max:50',
            'year_of_experience' => 'nullable|string|max:255',
            'certifications' => 'required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'msg' => 'Validation errors occurred.',
                'errors' => $validator->errors(),
            ]);
        }

        try {
            // Find the degree by ID
            $specilities = DoctorSpecilities::find($id);

            // Check if the degree exists
            if (!$specilities) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Data not found.',
                ]);
            }

            $specilities->doctor_profile_id = $request->input('doctor_profile_id'); // Set the foreign key
            $specilities->term_name = $request->input('term_name'); // Set the foreign key
            $specilities->term_short_name = $request->input('term_short_name');
            $specilities->year_of_experience = $request->input('year_of_experience');
            $specilities->certifications = $request->input('certifications');
            // Save the degree to the database
            if ($specilities->save()) {
                // Log successful update
                Log::info('specilities updated successfully', [
                    'degree_id' => $specilities->id,
                    'updated_data' => $specilities,
                ]);

                return response()->json([
                    'success' => true,
                    'msg' => 'specilities updated successfully!',
                ]);
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Error updating specilities: ' . $e->getMessage(), [
                'request' => $request->all(),
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'msg' => 'Failed to update the specilities. Please try again.',
            ]);
        }

        // If saving fails without an exception, return an error response
        return response()->json([
            'success' => false,
            'msg' => 'Failed to update the specilities. Please try again.',
        ]);
    }
}
