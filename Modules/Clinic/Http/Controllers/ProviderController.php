<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\CommonUtil;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use App\NotificationTemplate;
use Illuminate\Support\Facades\DB;
use Modules\Clinic\Utils\DoctorUtil;
use Modules\Clinic\Events\DoctorModifiedEvent;
use Illuminate\Support\Facades\Log;
use App\User;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Entities\{DoctorBusinessDay, DoctorAvailableStatusLog};
use Modules\Clinic\Entities\DoctorAppointmentSloot;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    protected $moduleUtil;
    protected $commonUtil;
    protected $doctorUtil;
    public function __construct(
        ModuleUtil $moduleUtil,
        DoctorUtil $doctorUtil,
        Util $commonUtil,
    ) {
        $this->doctorUtil = $doctorUtil;
        $this->commonUtil = $commonUtil;
        $this->moduleUtil = $moduleUtil;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('clinic.provider.view') && !auth()->user()->can('clinic.provider.create')) {
            abort(403, 'Unauthorized action.');
        }
        if ($request->ajax()) {
            $query = DoctorProfile::with('user')->where('is_doctor', 1)->latest()->get();
            $doctors = Datatables::of($query)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    if (auth()->user()->can('clinic.provider.view')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'show'], [$row->id]) . '" class="view_doctor_button">
                                <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '</a></li>';
                    }
                    if (auth()->user()->can('clinic.provider.profile.show')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'profile'], [$row->id]) . '">
                                <i class="fas fa-user" aria-hidden="true"></i>' . __('clinic::doctor.profile') . '</a></li>';
                    }

                    if (auth()->user()->can('clinic.provider.edit')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'edit'], [$row->id]) . '" class="edit_doctor_button">
                                <i class="glyphicon glyphicon-edit"></i>' . __('messages.edit') . '</a></li>';
                    }

                    if (auth()->user()->can('clinic.provider.delete')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'destroy'], [$row->id]) . '" class="delete_doctor_button">
                                <i class="glyphicon glyphicon-trash"></i>' . __('messages.delete') . '</a></li>';
                    }

                    if (auth()->user()->can('clinic.provider.deactive')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'updateStatus'], [$row->id]) . '" class="update_doctor_status">
                                <i class="fas fa-power-off"></i>';

                        $html .= $row->is_active == 1 ? __('messages.deactivate') : __('messages.activate');

                        $html .= '</a></li>';
                    }

                    $html .= '<li class="divider"></li>';
                    $html .= '</ul></div>';

                    return $html;
                })
                ->editColumn('name', function ($row) {
                    $name = $row->first_name . " " . $row->last_name;

                    if ($row->is_active == 0) {
                        $name .= ' <small class="label pull-right bg-red no-print">' . __('lang_v1.inactive') . '</small>';
                    }
                    return $name;
                })
                ->editColumn('userName', function ($row) {
                    return $row->user->username ?? "";
                })
                ->editColumn('is_consultant', function ($row) {
                    return $row->is_consultant ? 'Consultant' : 'General';
                })
                ->editColumn('is_full_time', function ($row) {
                    return $row->is_full_time ? 'Full Time' : 'Part Time';
                })
                ->addColumn('gender', function ($row) {
                    return ucfirst($row->gender);
                })
                ->rawColumns(['action', 'name', 'userName'])
                ->make(true);
            return $doctors;
        }

        return view('clinic::provider.index');
    }
    public function profile($id)
    {
        if (!auth()->user()->can('clinic.provider.profile.show')) {
            abort(403, 'Unauthorized action.');
        }
        $openHours = DoctorBusinessDay::where('doctor_profile_id', $id)->get();
        $doctor = DoctorProfile::with(['user', 'degrees'])->find($id);
        $checkBreak = DoctorAvailableStatusLog::where('doctor_profile_id', $id)->whereNull('end_time')->latest('break_start_time')->first();
        $breakStart = $checkBreak ? $checkBreak->break_start_time : null;
        $expect_duration = $checkBreak ? $checkBreak->expect_duration : 0;
        return view('clinic::provider.profile', compact('doctor', 'openHours', 'breakStart', 'expect_duration'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (!auth()->user()->can('clinic.provider.create')) {
            abort(403, 'Unauthorized action.');
        }
        session(['reference_type' => '']);
        session(['reference_type' => 'doctor']);
        return view('clinic::provider.add_doctor'); // passing to view

    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('clinic.provider.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            // Step 1: Validate and prepare input for doctor
            $input = $request->only([
                'first_name',
                'middle_name',
                'last_name',
                'mobile',
                'landline',
                'alternate_number',
                'city',
                'state',
                'country',
                'address_line_1',
                'address_line_2',
                'customer_group_id',
                'zip_code',
                'contact_id',
                'custom_field1',
                'custom_field2',
                'custom_field3',
                'custom_field4',
                'custom_field5',
                'custom_field6',
                'custom_field7',
                'custom_field8',
                'custom_field9',
                'custom_field10',
                'email',
                'shipping_address',
                'position',
                'dob',
                'address',
                'designation',
                'is_show_invoice',
            ]);

            $input['is_doctor'] = $request->has('is_doctor') ? 1 : 0;
            $input['is_show_invoice'] = $request->has('is_show_invoice') ? 1 : 0;
            $input['created_by'] = $request->session()->get('user.id');

            if (!empty($input['first_name'])) {
                $name_array[] = $input['first_name'];
            }

            if (!empty($input['last_name'])) {
                $name_array[] = $input['last_name'];
            }
            if (!empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            DB::beginTransaction();

            // Step 2: Create the doctor record
            $output = $this->doctorUtil->createNewDoctor($input);

            // Step 3: Handle the business day logic
            $this->storeBusinessDay($output['data']);

            event(new DoctorModifiedEvent($input, 'added'));
            $this->moduleUtil->getModuleData('after_contact_saved', ['contact' => $output['data'], 'input' => $request->input()]);
            $this->doctorUtil->activityLog($output['data'], 'added');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function storeBusinessDay($doctor)
    {
        // Step 1: Prepare operating hours

        $operatingHours[] = [
            'start' => '10:00',
            'end' => '18:00'
        ];

        // Step 2: Create business days for the doctor
        $businessDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        foreach ($businessDays as $businessDay) {

            // Step 3: Create the doctor business day record
            $doctorBusinessDay = new DoctorBusinessDay();
            $doctorBusinessDay->doctor_profile_id = $doctor->id;
            $doctorBusinessDay->doctor_user_id = $doctor->user_id;
            $doctorBusinessDay->business_day_number = $businessDay;
            $doctorBusinessDay->business_operating_hours = json_encode($operatingHours);
            $doctorBusinessDay->active_status = 1;
            $doctorBusinessDay->is_off_day = 0;
            $doctorBusinessDay->business_day_type = 'monthly_sloot';
            $doctorBusinessDay->created_by = auth()->user()->id;
            $doctorBusinessDay->save();
        }
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        Log::info('calling show method');
        if (!auth()->user()->can('clinic.provider.view')) {
            abort(403, 'Unauthorized action.');
        }
        $provider = DoctorProfile::with('user')->findOrFail($id);

        return view('clinic::provider.view', compact('provider'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (!auth()->user()->can('clinic.provider.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $provider = DoctorProfile::findOrFail($id);


        return view('clinic::provider.edit', compact('provider'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('clinic.provider.edit')) {
            abort(403, 'Unauthorized action.');
        }
        try {

            $input = $request->only([
                'first_name',
                'middle_name',
                'last_name',
                'mobile',
                'city',
                'state',
                'country',
                'customer_group_id',
                'zip_code',
                'contact_id',
                'email',
                'shipping_address',
                'position',
                'dob',
                'address',
                'user_id',
                'designation',
                'is_show_invoice',
                'is_doctor',
            ]);

            $name_array = [];
            $input['is_doctor'] = $request->has('is_doctor') ? 1 : 0;

            $input['is_show_invoice'] = $request->has('is_show_invoice') ? 1 : 0;

            if (!empty($input['first_name'])) {
                $name_array[] = $input['first_name'];
            }

            if (!empty($input['last_name'])) {
                $name_array[] = $input['last_name'];
            }
            if (!empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            $input['updated_by'] = $request->session()->get('user.id');

            DB::beginTransaction();
            $output = $this->doctorUtil->updateDoctor($input, $id);

            event(new DoctorModifiedEvent($input, 'updated'));

            // $this->moduleUtil->getModuleData('after_contact_saved', ['contact' => $output['data'], 'input' => $request->input()]);

            $this->doctorUtil->activityLog($output['data'], 'updated');

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        // Check user permissions
        if (
            !auth()->user()->can('clinic.provider.delete')
        ) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                // Begin the transaction
                DB::beginTransaction();

                // Find the doctor profile
                $doctor = DoctorProfile::find($id);
                Log::info('Doctor id: ' . $id . " name: " . ($doctor ? $doctor->first_name : 'not found'));

                if (!$doctor) {
                    return response()->json([
                        'success' => false,
                        'msg' => __('clinic::doctor.doctor_not_found'),
                    ]);
                }

                // Delete associated DoctorBusinessDay records
                $doctorBusinessDays = DoctorBusinessDay::where('doctor_profile_id', $id)->get();
                foreach ($doctorBusinessDays as $doctorBusinessDay) {
                    $doctorBusinessDay->delete();
                    Log::info("DoctorBusinessDay with ID {$doctorBusinessDay->id} deleted.");
                }

                // Get the associated user
                $user = User::find($doctor->user_id);

                // Delete the user and doctor profile
                if ($user) {
                    $user->delete();
                    Log::info("User with ID {$user->id} deleted.");
                }

                $doctor->delete();
                Log::info("Doctor with ID {$doctor->id} deleted.");

                // Commit the transaction
                DB::commit();

                return response()->json([
                    'success' => true,
                    'msg' => __('clinic::doctor.deleted_success'),
                ]);
            } catch (\Exception $e) {
                // Rollback the transaction on error
                DB::rollBack();

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

    public function checkEmailId(Request $request)
    {
        $email = $request->input('email'); // Degree name from the request
        $doctorId = $request->input('doctor_id'); // Degree ID (for update), if provided

        // Default to valid
        $valid = 'true';

        // Check if degree name is provided
        if (!empty($email)) {
            $query = DoctorProfile::where('email', $email);
            if ($doctorId) {
                $query->where('id', '!=', $doctorId); // Exclude the current degree ID
            }
            $count = $query->count();
            if ($count > 0) {
                $valid = 'false'; // Name already exists
            }
        }

        echo $valid; // Return as JSON
        exit;
    }

    public function updateStatus($id)
    {
        if (!auth()->user()->can('clinic.provider.deactive')) {
            Log::warning('Unauthorized action attempted by user ID: ' . auth()->id());
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                DB::beginTransaction();
                Log::info('Updating status for doctor ID: ' . $id);

                $doctor = DoctorProfile::find($id);
                if (!$doctor) {
                    Log::error('Doctor not found for ID: ' . $id);
                    return response()->json(['success' => false, 'msg' => __('messages.not_found')]);
                }

                // Toggle the active status
                $doctor->is_active = $doctor->is_active == 1 ? 0 : 1;
                $doctor->save();

                // Update the corresponding user
                $user = User::where('id', $doctor->user_id)->first();
                if ($user) {
                    $user->allow_login = $user->allow_login == 1 ? 0 : 1;
                    $user->save();
                } else {
                    Log::error('User not found for doctor ID: ' . $id);
                }

                DB::commit();

                Log::info('Doctor status updated successfully for ID: ' . $id, ['is_active' => $doctor->is_active]);

                $output = [
                    'success' => true,
                    'msg' => __('clinic::doctor.updated_success'),
                ];

                return response()->json($output);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error updating status for doctor ID: ' . $id, [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'message' => $e->getMessage(),
                ]);

                return response()->json(['success' => false, 'msg' => __('messages.something_went_wrong')]);
            }
        }
    }
    public function businessDayEdit($id)
    {
        $days = DoctorBusinessDay::find($id);
        $allDays = DoctorBusinessDay::where('doctor_profile_id', $days->doctor_profile_id)
            ->where('id', '!=', $id) // Exclude the current day
            ->get();

        return view('clinic::provider.tab.business_day_edit', compact('days', 'allDays'));
    }
    public function businessDayCreate($id)
    {
        return view('clinic::provider.tab.business_day_create', compact('id'));
    }
    public function checkBusinessDay(Request $request)
    {

        $day = $request->input('business_day_number'); // Degree name from the request
        $doctorId = $request->input('doctor_id'); // Degree ID (for update), if provided
        $day_id = $request->input('day_id'); // Day ID
        // Default to valid
        $valid = 'true';

        if (!empty($day)) {
            $query = DoctorBusinessDay::where('business_day_number', $day)->where('doctor_profile_id', $doctorId);
            if (!empty($day_id)) {
                $query->where('id', '!=', $day_id);
            }
            $count = $query->count();

            if ($count > 0) {
                $valid = 'false';
            }
        }

        echo $valid;
        exit;
    }

    public function businessDayUpdate(Request $request, $id)
    {
        Log::info('Starting business day update process', [
            'id' => $id,
            'request_data' => $request->all(),
        ]);

        // Start logging the update process

        try {
            $startTimes = $request->input('start_time');
            $closeTimes = $request->input('close_time');

            $operatingHours = [];

            foreach ($startTimes as $index => $startTime) {
                if (isset($closeTimes[$index])) {
                    $operatingHours[] = [
                        'start' => $startTime,
                        'end' => $closeTimes[$index],
                    ];
                }
            }

            // Find the existing business day or fail if not found
            $doctorBusinessDay = DoctorBusinessDay::findOrFail($id);

            // Log the current state before update
            Log::info('Current business day before update', [
                'current_data' => $doctorBusinessDay,
            ]);

            // Update the fields
            $doctorBusinessDay->business_day_number = $request->input('business_day_number');
            $doctorBusinessDay->business_operating_hours = json_encode($operatingHours);
            $doctorBusinessDay->is_off_day = $request->has('is_off_day') ? 1 : 0;
            $doctorBusinessDay->remarks = $request->input('remarks');
            $doctorBusinessDay->modified_by = auth()->user()->id;
            $doctorBusinessDay->save();

            // Check if any day is selected for copying settings
            if ($request->has('copy_from_day')) {
                $copyFromDays = $request->input('copy_from_day');

                // Loop through the selected days to copy settings from
                foreach ($copyFromDays as $dayId) {
                    $copyDay = DoctorBusinessDay::find($dayId);

                    if ($copyDay) {
                        // Copy operating hours and remarks
                        $copyDay->business_operating_hours = $doctorBusinessDay->business_operating_hours;
                        $copyDay->remarks = $doctorBusinessDay->remarks;
                        $copyDay->is_off_day = $doctorBusinessDay->is_off_day;
                        $copyDay->modified_by = $doctorBusinessDay->modified_by;
                        $copyDay->save();
                    }
                }
            }

            // Log the updated business day data
            Log::info('Business day updated successfully', [
                'updated_data' => $doctorBusinessDay,
            ]);

            $businessOperatingHours = json_decode($doctorBusinessDay->business_operating_hours, true) ?? [];
            $formattedHours = '';

            if (!empty($businessOperatingHours)) {
                foreach ($businessOperatingHours as $time) {
                    $formattedHours .= "{$time['start']} to {$time['end']}, ";
                }
                $formattedHours = rtrim($formattedHours, ', '); // Remove trailing comma
            } else {
                $formattedHours = 'No operating hours available';
            }

            // Prepare output data
            $output = [
                'success' => true,
                'msg' => 'Business Day Updated Successfully',
                'data' => [
                    'id' => $doctorBusinessDay->id,
                    'business_day_number' => $doctorBusinessDay->business_day_number,
                    'business_operating_hours' => $formattedHours,
                    'is_off_day' => $doctorBusinessDay->is_off_day,
                ],
            ];
        } catch (\Exception $e) {
            // Log the exception for debugging
            Log::error('Error updating business day: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'id' => $id,
            ]);

            // Handle exceptions and return a response
            $output = [
                'success' => false,
                'msg' => 'Something went wrong! Please try again',
            ];
        }

        return response()->json($output);
    }


    public function businessDayDelete($id)
    {
        try {
            Log::info("Attempting to delete monthly slots for doctor ID: $id");

            $deletedCount = DoctorBusinessDay::find($id)
                ->delete();

            // Check if any rows were deleted
            if ($deletedCount > 0) {
                Log::info("Successfully deleted $deletedCount monthly slots for doctor ID: $id");
                $output = [
                    'success' => true,
                    'msg' => 'Business Day Deleted Successfully',
                ];
            } else {
                Log::warning("No data found to delete for $id");
                $output = [
                    'success' => false,
                    'msg' => 'No Data Found to Delete',
                ];
            }
        } catch (\Exception $e) {
            // Handle the exception
            Log::error("Error deleting Business Day for doctor ID: $id " . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => 'An error occurred: ' . $e->getMessage(),
            ];
        }

        return $output;
    }


    public function getBusinessDaysData($id)
    {
        if (!auth()->user()->can('clinic.provider.profile.show')) {
            abort(403, 'Unauthorized action.');
        }

        $businessDays = DoctorBusinessDay::where('doctor_profile_id', $id)->get();

        return DataTables::of($businessDays)
            ->addColumn('action', function ($row) {
                $editUrl = action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'businessDayEdit'], [$row->id]);

                $html = '<a href="' . $editUrl . '" class="btn btn-sm btn-primary make_app edit_business_day"><i class="fas fa-edit"></i> Edit</a>';

                return $html;
            })
            ->editColumn('business_operating_hours', function ($row) {
                $business_day_number = $row['business_day_number'];
                $is_off_day = $row->is_off_day;
                $hours = json_decode($row->business_operating_hours, true) ?? [];

                // Start building the content string with the icon and bold text
                $content = '<i class="fas fa-circle"></i> <b>' . $business_day_number . ' ';

                if (!empty($hours) && empty($is_off_day)) {
                    // If there are operating hours, append them to the string
                    $content .= implode(', ', array_map(function ($time) {
                        return $time['start'] . ' to ' . $time['end'];
                    }, $hours));
                } else if ($is_off_day) {
                    $content .= ' <small class="bg-red p-1">Holiday</small>';
                } else {
                    // If no operating hours are available, show 'No operating hours available'
                    $content .= 'No operating hours available';
                }

                // Close the bold tag
                $content .= '</b>';

                return $content;
            })


            ->rawColumns(['action', 'business_operating_hours'])
            ->make(true);
    }


    public function updateDoctorStatus(Request $request)
    {
        try {
            $doctor = DoctorProfile::findOrFail($request->doctor_id);
            $doctor->is_available = $request->is_active === 'true' ? 1 : 0;
            $doctor->save();
            if (!$doctor->is_available) {
                // Doctor becomes unavailable: insert new break log
                DoctorAvailableStatusLog::create([
                    'doctor_profile_id' => $doctor->id,
                    'break_start_time' => now(),
                    'date' => Carbon::now()->toDateString(),
                    'expect_duration' => $request->expect_duration,
                    'reason' => $request->reason
                ]);
            } else {
                // Doctor becomes available again: update last break log
                $log = DoctorAvailableStatusLog::where('doctor_profile_id', $doctor->id)
                    ->whereNull('end_time')
                    ->latest('break_start_time')
                    ->first();

                if ($log) {
                    $start = Carbon::parse($log->break_start_time);
                    $end = now();
                    $duration = $start->diffInMinutes($end);
                    if($duration > 360){
                        $log->delete();
                    }else{
                        $log->update([
                            'end_time' => $end,
                            'duration' => $duration,
                        ]);
                    }
                }
            }
            return response()->json([
                'success' => true,
                'msg' => 'Status updated successfully.',
                'is_available' => $doctor->is_available
            ]);
        } catch (\Exception $e) {
            Log::error('Doctor status update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Failed to update status.',
            ]);
        }
    }
}
