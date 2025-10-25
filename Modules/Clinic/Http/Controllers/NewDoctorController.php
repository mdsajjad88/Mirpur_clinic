<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Modules\Clinic\Entities\Disease;
use Modules\Clinic\Entities\DoctorAppointment;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use Modules\Clinic\Entities\PatientUser;
use Illuminate\Support\Facades\Log;
use Modules\Clinic\Entities\Chamber;
use Modules\Clinic\Entities\PatientProfile;
use Modules\Clinic\Entities\DoctorAppointmentSloot;
use Modules\Clinic\Entities\PatientDisease;
use Illuminate\Support\Facades\DB;
use App\Product;
use App\Http\Controllers\SellPosController;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;
use Illuminate\Session\SessionManager;
use Modules\Clinic\Http\Controllers\ClinicPosController;
use App\CustomerGroup;
use App\Business;
use App\{User, Transaction, Contact};
use Modules\Clinic\Entities\Problem;
use Modules\Clinic\Entities\DoctorSL;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;

class NewDoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $session;
    protected $commonUtil;
    public function __construct(SessionManager $session, Util $commonUtil)
    {
        $this->session = $session;
        $this->commonUtil = $commonUtil;
    }

    public function index($id = null)
    {
        if (!auth()->user()->can('new.appointment.create')) {
            abort(403, 'Unauthorized action.');
        }
        $contact_id = request()->input('contact_id');

        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $app_doctor = null;
        if ($id) {
            $app_doctor = DoctorProfile::find($id);
        }

        $doctors = DoctorProfile::where('is_doctor', 1)->get()->mapWithKeys(function ($doctor) {
            return [$doctor->id => $doctor->first_name . ' ' . $doctor->last_name];
        });
        $diseases = Problem::all();
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $selected_type = request()->type;
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];
        $contact = null;
        if ($contact_id) {
            $contact = Contact::find($contact_id);
            $patient_profiles = PatientProfile::where('patient_contact_id', $contact_id)->get();
        } else {
            $patient_profiles = PatientProfile::take(10)->get();
        }
        $doctorType = request()->get('type', 'doctor');
        return view('clinic::appointment.new_doctor', compact('doctors', 'patient_profiles', 'diseases', 'app_doctor', 'selected_type', 'users', 'customer_groups', 'business_id', 'contact', 'doctorType'));
    }
    public function doctorAppointment($id)
    {
        $app_doctor = DoctorProfile::find($id);
        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $clinic_location = $common_settings['clinic_location'] ?? null;
        $doctors = DoctorProfile::all()->mapWithKeys(function ($doctor) {
            return [$doctor->id => $doctor->first_name . ' ' . $doctor->last_name];
        });
        $patient_profiles = PatientProfile::all();
        $diseases = Problem::all();
        $customer_groups = CustomerGroup::forDropdown($business_id);

        // Return the view with the necessary data
        return view('clinic::appointment.new_doctor', compact('doctors', 'patient_profiles', 'customer_groups', 'diseases', 'app_doctor'));
    }

    public function appointmentDetails()
    {
        return view('clinic::appointment.details');
    }
    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('clinic::create');
    }
    public function appointmentConfirm()
    {
        return view('clinic::appointment.confirmation');
    }
    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function appointmentNumber(Request $request)
    {
        $data = [
            [
                'date_time' => '2024-09-22 10:00 AM',
                'tnx' => 'TXN001',
                'user' => 'User1',
                'pay_form' => 'Credit Card',
                'debits' => 50.00,
                'credits' => 100.00,
                'balance' => 50.00,
            ],
            [
                'date_time' => '2024-09-21 02:30 PM',
                'tnx' => 'TXN002',
                'user' => 'User2',
                'pay_form' => 'Cash',
                'debits' => 30.00,
                'credits' => 70.00,
                'balance' => 40.00,
            ],
            [
                'date_time' => '2024-09-20 09:15 AM',
                'tnx' => 'TXN003',
                'user' => 'User3',
                'pay_form' => 'Debit Card',
                'debits' => 20.00,
                'credits' => 60.00,
                'balance' => 40.00,
            ],
        ];

        return datatables()->of($data)->make(true);
    }


    public function store(Request $request)
    {
        if (!auth()->user()->can('new.appointment.create')) {
            abort(403, 'Unauthorized action.');
        }
        // Validate incoming request data
        $validatedData = $request->validate([
            'request_date' => 'required',
            'doctor_profile_id' => 'required',
            'appointment_media' => 'required',
            'patient_contact_id' => 'required',
            'slot' => 'required',
            'comments' => 'nullable',
        ]);      // Retrieve     input values


        $doctor_profile = $request->input('doctor_profile_id');
        $contact_id = $request->input('patient_contact_id');
        $date = $request->input('request_date');
        $requestedSlotIndex = $request->input('slot');
        $appointment_media = $request->input('appointment_media');
        $comments = $request->input('comments');



        $requestedSlotValue = $request->input('slot'); // This will be in "index-subslot" format

        if ($requestedSlotValue) {
            list($requestedSlotIndex, $subSlotIndex) = explode('-', $requestedSlotValue);

            $serialNumbers = $request->input('serialNumber'); // Fetch all serial numbers

            if (isset($serialNumbers[$requestedSlotIndex][$subSlotIndex])) {
                $selectedSerialNumber = $serialNumbers[$requestedSlotIndex][$subSlotIndex];
            } else {
                $selectedSerialNumber = null;
            }
        } else {
            $selectedSerialNumber = null;
        }
        $doctor = DoctorProfile::where('id', $doctor_profile)->first();
        $patient = PatientProfile::with('contact')->where('patient_contact_id', $contact_id)->first();
        if (empty($patient)) {
            $output = ['success' => false, 'msg' => 'Patient not found. Please Update Intake Form'];
            return $output;
        }
        // Retrieve the available slots for the selected date
        $contact = Contact::find($contact_id);
        if (empty($contact)) {
            $output = ['success' => false, 'msg' => 'Patient not found. Please Update Intake Form'];
            return $output;
        }
        $appointment = PatientAppointmentRequ::where('patient_contact_id', $contact_id)
            ->where('request_date', $date)
            ->where('appointment_type', $doctor->type)
            ->where(function ($query) {
                $query->where('remarks', '!=', 'prescribed')
                    ->where('remarks', '!=', 'cancelled')
                    ->where('remarks', '!=', 'refunded');
            })
            ->first();

        if ($appointment) {
            return response()->json([
                'success' => false,
                'msg' => 'Appointment already exists.'
            ]);
        }

        $slots = DoctorAppointmentSloot::where('doctor_profile_id', $doctor_profile)
            ->where('calendar_date', $date)
            ->first();
        $availableSlots = json_decode($slots->slots, true);
        $todaySlots = $availableSlots[$date] ?? [];

        // Check if the requested slot exists
        if (!isset($todaySlots[$requestedSlotIndex])) {
            Log::error('Requested slot index does not exist.', [
                'requestedSlotIndex' => $requestedSlotIndex,
                'availableSlots' => $todaySlots,
            ]);
            return response()->json(['success' => false, 'msg' => 'The selected slot is not available.'], 400);
        }

        $selectedSlot = $todaySlots[$requestedSlotIndex];

        // Prepare the request slot data
        $requestSlotData = [
            'slot_details' => $selectedSlot,
            'appointment_time' => $selectedSlot['start'],
        ];

        // Begin database transaction
        $patientType = strtolower($request->input('hidden_patient_type'));
        if (empty($patientType) || is_null($patientType)) {
            $prevAppointments = DB::table('patient_appointment_requests')
                ->where('patient_contact_id', $contact_id)
                ->get();

            if ($prevAppointments->count() == 0) {
                $patientType = 'new';
            } elseif ($prevAppointments->where('remarks', 'prescribed')->count() == 0) {
                $patientType = 'new';
            } else {
                $lastPrescribedDate = $prevAppointments->where('remarks', 'prescribed')->max('request_date');
                if ($lastPrescribedDate >= now()->subMonths(4)) {
                    $patientType = 'followup';
                } else {
                    $patientType = 'old';
                }
            }
        }
        DB::beginTransaction();
        $contact->patient_type = $patientType;
        $contact->save();
        $slResponse = $this->checkSLno($doctor_profile, $selectedSerialNumber, $date);
        if ($slResponse['success'] == true) {
            $output = [
                'success' => false,
                'msg' => 'Serial number already Booked. Choose another serial number',
            ];
            return $output;
        } else {
            try {

                // Create and save the patient appointment request
                $patientApp = new PatientAppointmentRequ();
                $patientApp->doctor_profile_id = $doctor_profile;
                $patientApp->doctor_user_id = $doctor->user_id;
                $patientApp->patient_contact_id = $contact_id;
                $patientApp->patient_profile_id = $patient->id;
                $patientApp->appointment_media = $appointment_media;
                $patientApp->request_date = $date;
                $patientApp->request_slot = json_encode($requestSlotData); // Store as JSON
                $patientApp->doctor_appointment_slot_id = $slots->id;
                $patientApp->doctor_appointment_day_id = $slots->doctor_appointment_day_id;
                $patientApp->remarks = 'booked';
                $patientApp->comments = $comments;
                $patientApp->created_by = auth()->user()->id; // User ID
                $patientApp->created_name = auth()->user()->username; // Username
                $patientApp->appointment_number = date('YmdHis');
                $patientApp->type = $patientType;
                $patientApp->appointment_type = $doctor->type;
                $patientApp->save();

                $formattedSerial = $doctor->serial_prefix . '-' . $selectedSerialNumber;

                $todaySlots[$requestedSlotIndex]['reserved']++;
                $availableSlots[$date] = $todaySlots; // Update the slots array
                $slots->slots = json_encode($availableSlots); // Encode it back to JSON
                $slots->save(); // Save the updated slots back to the database

                if (!empty($validatedData['disease'])) {
                    foreach ($validatedData['disease'] as $diseaseId) {
                        $patient_disease = new PatientDisease();
                        $patient_disease->patient_profile_id = $patient->id;
                        $patient_disease->disease_id = $diseaseId;
                        $patient_disease->patient_appointment_request_id = $patientApp->id;
                        $patient_disease->created_by = auth()->user()->id;
                        $patient_disease->save();
                    }
                }


                // Create doctor SL record
                $doctorSL = new DoctorSL();
                $doctorSL->doctor_profile_id = $doctor_profile;
                $doctorSL->patient_contact_id = $contact_id;
                $doctorSL->appointment_id = $patientApp->id;
                $doctorSL->appointment_date = $date;
                $doctorSL->sl_no = $formattedSerial;
                $doctorSL->sl_without_prefix = $selectedSerialNumber;
                $doctorSL->created_by = auth()->user()->id;
                $doctorSL->save();
                $this->commonUtil->activityLog($doctorSL, 'created');
                DB::commit();

                    // try {
                    //     $patient_phone = $contact->mobile; // Assuming contact has "mobile" field
                    //     $is_fasting_required = $request->input('is_fasting_required');

                    //     if (!empty($patient_phone)) {
                    //         $sms_message = 'Dear ' . $contact->name . ', ' .
                    //             'Appointment booked on ' . Carbon::parse($date)->format('d M') . 
                    //             ' with ' . $doctor->first_name . ' ' . $doctor->last_name . 
                    //             ', SL: ' . $selectedSerialNumber . ' (' . $doctor->serial_prefix . '). ' .
                    //             'Address: Islam Tower, 2nd Floor, 102 Sukrabad, Dhanmondi 32.';

                    //         // Add fasting instruction if required
                    //         if ($is_fasting_required == 1) {
                    //             $sms_message .= ' Please stay fasting 8 hours before Dr. visit.';
                    //         }
                    //         $sms_response = '';
                    //         if($doctor->type == 'doctor'){
                    //               $sms_response = $this->sendAppointmentSMS($patient_phone, $sms_message);
                    //         }
                    //         Log::info('Appointment SMS sent response: ', [$sms_response]);
                    //     }
                    // } catch (\Exception $e) {
                    //     Log::error('Error sending appointment SMS: ' . $e->getMessage());
                    // }

                $output = [
                    'success' => true,
                    'msg' => 'Appointment booked successfully.',
                    'appointment_number' => $patientApp->appointment_number,
                    'redirect_url' => route('all-appointment.index'),
                ];
                return $output;
            } catch (\Exception $e) {
                Log::error('Exception caught in store method:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                DB::rollBack();
                $errorDetails = [
                    'doctor_profile_id' => $doctor_profile,
                    'patient_profile_id' => $patient->id,
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                ];

                if (strpos($e->getMessage(), 'Session store not set on request') !== false) {
                    Log::error('Session error while booking appointment:', $errorDetails);
                    $output = ['success' => false, 'msg' => 'Session is not initialized. Please try again later.'];
                } elseif ($e instanceof QueryException) {
                    Log::error('Database error while booking appointment:', $errorDetails);
                    $output = ['success' => false, 'msg' => 'Database error occurred. Please try again later.'];
                } elseif ($e instanceof ValidationException) {
                    Log::error('Validation error while booking appointment:', $errorDetails);
                    $output = ['success' => false, 'msg' => 'Validation error. Please check your input and try again.'];
                } else {
                    Log::error('Unexpected error while booking appointment:', $errorDetails);
                    $output = ['success' => false, 'msg' => 'Failed to book appointment. Please try again later.'];
                }
                if ($e->getCode() == 23000) {
                    $output = ['success' => false, 'msg' => 'Serial number already exists. Please Choose another serial number.'];
                }
            }
        }



        return $output;
    }


    private function sendAppointmentSMS($number, $message)
    {
        $business_id = request()->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();
        $sms_settings = $business->sms_settings;

        $username = $sms_settings['send_to_param_name'];
        $password = $sms_settings['msg_param_name'];
        $url = $sms_settings['url'];

        $params = [
            'user' => $username,
            'password' => $password,
            'from' => 'AWC DHAKA',
            'to' => $number,
            'text' => $message
        ];

        try {
            $response = Http::get($url, $params);
            return $response->json();
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return ['success' => 0, 'error' => $e->getMessage()];
        }
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

    // NewDoctorController.php

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (!auth()->user()->can('appointment.update')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);

        // Fetch the appointment details
        $appointment = PatientAppointmentRequ::with('diseases')->findOrFail($id);

        // Fetch the doctor profile
        $app_doctor = DoctorProfile::find($appointment->doctor_profile_id);

        // Fetch all doctors, patient profiles, diseases, etc.
        $doctors = DoctorProfile::all()->mapWithKeys(function ($doctor) {
            return [$doctor->id => $doctor->first_name . ' ' . $doctor->last_name];
        });
        $diseases = Problem::all();
        $customer_groups = CustomerGroup::forDropdown($business_id);
        $selected_type = request()->type;
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        // Fetch the selected slot details
        $requestSlotData = json_decode($appointment->request_slot, true);
        $selectedSlotIndex = $requestSlotData['slot_details']['index'] ?? null;
        $serialNo = DoctorSL::where('appointment_id', $appointment->id)
            ->first();
        $contact = Contact::find($appointment->patient_contact_id);
        return view('clinic::appointment.edit_doctor', compact(
            'appointment',
            'doctors',
            'diseases',
            'app_doctor',
            'selected_type',
            'users',
            'customer_groups',
            'selectedSlotIndex',
            'serialNo',
            'contact'
        ));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('appointment.update')) {
            abort(403, 'Unauthorized action.');
        }
        // Validate incoming request data
        $validatedData = $request->validate([
            'request_date' => 'required',
            'doctor_profile_id' => 'required',
            'appointment_media' => 'required',
            'patient_contact_id' => 'required',
            'slot' => 'required',
            'comments' => 'nullable',
        ]);

        // Retrieve input values
        $doctor_profile = $request->input('doctor_profile_id');
        $contact_id = $request->input('patient_contact_id');
        $date = $request->input('request_date');
        $requestedSlotIndex = $request->input('slot');
        $appointment_media = $request->input('appointment_media');
        $comments = $request->input('comments');

        $requestedSlotValue = $request->input('slot'); // This will be in "index-subslot" format

        if ($requestedSlotValue) {
            list($requestedSlotIndex, $subSlotIndex) = explode('-', $requestedSlotValue);

            $serialNumbers = $request->input('serialNumber'); // Fetch all serial numbers

            if (isset($serialNumbers[$requestedSlotIndex][$subSlotIndex])) {
                $selectedSerialNumber = $serialNumbers[$requestedSlotIndex][$subSlotIndex];
            } else {
                $selectedSerialNumber = null;
            }
        } else {
            $selectedSerialNumber = null;
        }
        $doctor = DoctorProfile::with('user')->where('id', $doctor_profile)->first();
        $patient = PatientProfile::with('contact')->where('patient_contact_id', $contact_id)->first();

        // Retrieve the existing appointment to check for changes
        $existingAppointment = PatientAppointmentRequ::findOrFail($id);
        $oldDoctorId = $existingAppointment->doctor_profile_id;
        $oldDate = $existingAppointment->request_date;
        $oldRemarks = $existingAppointment->remarks;
        
        // Check if doctor or date has changed
        $doctorChanged = ($oldDoctorId != $doctor_profile);
        $dateChanged = ($oldDate != $date);
        $statusChangedFromCancelled = ($oldRemarks == 'cancelled');
        $shouldSendSMS = $doctorChanged || $dateChanged || $statusChangedFromCancelled;

        // Retrieve the available slots for the selected date
        $slots = DoctorAppointmentSloot::where('doctor_profile_id', $doctor_profile)
            ->where('calendar_date', $date)
            ->first();

        $availableSlots = json_decode($slots->slots, true);
        $todaySlots = $availableSlots[$date] ?? [];

        // Check if the requested slot exists
        if (!isset($todaySlots[$requestedSlotIndex])) {
            Log::error('Requested slot index does not exist.', [
                'requestedSlotIndex' => $requestedSlotIndex,
                'availableSlots' => $todaySlots,
            ]);
            return response()->json(['success' => false, 'msg' => 'The selected slot is not available.'], 400);
        }

        $selectedSlot = $todaySlots[$requestedSlotIndex];

        // Prepare the request slot data
        $requestSlotData = [
            'slot_details' => $selectedSlot,
            'appointment_time' => $selectedSlot['start'],
        ];
        $contact = Contact::find($contact_id);
        $patientType = strtolower($request->input('hidden_patient_type'));
        if (empty($patientType) || is_null($patientType)) {
            $prevAppointments = DB::table('patient_appointment_requests')
                ->where('patient_contact_id', $contact_id)
                ->get();

            if ($prevAppointments->count() == 0) {
                $patientType = 'new';
            } elseif ($prevAppointments->where('remarks', 'prescribed')->count() == 0) {
                $patientType = 'new';
            } else {
                $lastPrescribedDate = $prevAppointments->where('remarks', 'prescribed')->max('request_date');
                if ($lastPrescribedDate >= now()->subMonths(4)) {
                    $patientType = 'followup';
                } else {
                    $patientType = 'old';
                }
            }
        }
        // Begin database transaction
        DB::beginTransaction();
        $contact->patient_type = $patientType;
        $contact->save();
        $slResponse = $this->checkSLno($doctor_profile, $selectedSerialNumber, $date, $id);
        if ($slResponse['success'] == true) {
            $output = [
                'success' => false,
                'msg' => 'Serial number already Booked. Choose another serial number',
            ];
            return response()->json($output);
        } else {
            try {
                // Before updating new slot, free old slot
                $oldApp = PatientAppointmentRequ::findOrFail($id);
                $oldSlotData = json_decode($oldApp->request_slot, true);

                if ($oldApp->doctor_appointment_slot_id && $oldApp->request_date) {
                    $oldSlots = DoctorAppointmentSloot::where('id', $oldApp->doctor_appointment_slot_id)->first();
                    $oldAvailableSlots = json_decode($oldSlots->slots, true);
                    $oldTodaySlots = $oldAvailableSlots[$oldApp->request_date] ?? [];

                    if (!empty($oldSlotData) && isset($oldSlotData['slot_details']['index']) && isset($oldTodaySlots[$oldSlotData['slot_details']['index']])) {
                        $oldTodaySlots[$oldSlotData['slot_details']['index']]['reserved']--;
                        if ($oldTodaySlots[$oldSlotData['slot_details']['index']]['reserved'] < 0) {
                            $oldTodaySlots[$oldSlotData['slot_details']['index']]['reserved'] = 0;
                        }
                        $oldAvailableSlots[$oldApp->request_date] = $oldTodaySlots;
                        $oldSlots->slots = json_encode($oldAvailableSlots);
                        $oldSlots->save();
                    }
                }




                // Find the existing appointment
                $patientApp = PatientAppointmentRequ::findOrFail($id);

                // Check if status is changing from cancelled to booked
                $wasCancelled = ($patientApp->remarks == 'cancelled');
                
                // Update the appointment details
                $patientApp->doctor_profile_id = $doctor_profile;
                $patientApp->doctor_user_id = $doctor->user->id;
                $patientApp->patient_contact_id = $contact_id;
                $patientApp->patient_profile_id = $patient->id;
                $patientApp->appointment_media = $appointment_media;
                $patientApp->request_date = $date;
                $patientApp->request_slot = json_encode($requestSlotData); // Store as JSON
                $patientApp->doctor_appointment_slot_id = $slots->id;
                $patientApp->doctor_appointment_day_id = $slots->doctor_appointment_day_id;
                $patientApp->modified_by = auth()->user()->id; // User ID
                $patientApp->comments = $comments;
                $patientApp->type = $patientType;
                if ($patientApp->remarks == 'cancelled') {
                    $patientApp->remarks = 'booked';
                    $patientApp->cancel_status = 0;
                }
                $patientApp->save();
                $formattedSerial = $doctor->serial_prefix . '-' . $selectedSerialNumber;

                DoctorSL::updateOrCreate(
                    ['appointment_id' => $patientApp->id],
                    [
                        'doctor_profile_id' => $doctor_profile,
                        'sl_no' => $formattedSerial,
                        'sl_without_prefix' => $selectedSerialNumber,
                        'appointment_date' => $date
                    ]
                );

                $sls = DoctorSL::where('appointment_id', $id)->first();
                $sls->doctor_profile_id = $doctor_profile;
                $sls->patient_contact_id = $contact_id;
                $sls->appointment_date = $date;
                $sls->sl_no = $formattedSerial;
                $sls->sl_without_prefix = $selectedSerialNumber;
                $sls->save();
                // Update the slots
                $todaySlots[$requestedSlotIndex]['reserved']++;
                $availableSlots[$date] = $todaySlots; // Update the slots array
                $slots->slots = json_encode($availableSlots); // Encode it back to JSON
                $slots->save(); // Save the updated slots back to the database
                if (!empty($patientApp->bill_no)) {
                    $transaction = Transaction::find($patientApp->bill_no);
                    $transaction->reference_id = $patientApp->doctor_user_id;
                    $transaction->save();
                }
                // Update patient diseases
                PatientDisease::where('patient_appointment_request_id', $patientApp->id)->delete();
                if (!empty($validatedData['disease'])) {
                    foreach ($validatedData['disease'] as $diseaseId) {
                        $patient_disease = new PatientDisease();
                        $patient_disease->patient_profile_id = $patient->id;
                        $patient_disease->disease_id = $diseaseId;
                        $patient_disease->patient_appointment_request_id = $patientApp->id;
                        $patient_disease->created_by = auth()->user()->id;
                        $patient_disease->save();
                    }
                }

                DB::commit();
                
                // Send SMS if doctor or date has changed OR if status changed from cancelled to booked
                // if ($shouldSendSMS) {
                //     try {
                        
                //         $patient_phone = $contact->mobile;
                //         $is_fasting_required = $request->input('is_fasting_required');
                        
                //         if (!empty($patient_phone)) {
                //             $oldDoctor = DoctorProfile::find($oldDoctorId);
                //             $oldDoctorName = $oldDoctor ? $oldDoctor->first_name . ' ' . $oldDoctor->last_name : 'Unknown Doctor';
                            
                //             // Build SMS message using the new format
                //             $sms_message = 'Dear ' . $contact->name . ', ';
                            
                //             if ($statusChangedFromCancelled && !$doctorChanged && !$dateChanged) {
                //                 // Only status changed from cancelled to booked (rebooking)
                //                 $sms_message .= 'Appointment rebooked on ' . Carbon::parse($date)->format('d M') . 
                //                     ' with Dr. ' . $doctor->first_name . ' ' . $doctor->last_name . 
                //                     ', SL: ' . $selectedSerialNumber . ' (' . $doctor->serial_prefix . '). ' .
                //                     'Address: Islam Tower, 2nd Floor, 102 Sukrabad, Dhanmondi 32.';
                //             } 
                //             elseif ($statusChangedFromCancelled && ($doctorChanged || $dateChanged)) {
                //                 // Status changed from cancelled AND doctor/date also changed
                //                 $sms_message .= 'Appointment rebooked with changes on ' . Carbon::parse($date)->format('d M') . 
                //                     ' with Dr. ' . $doctor->first_name . ' ' . $doctor->last_name . 
                //                     ', SL: ' . $selectedSerialNumber . ' (' . $doctor->serial_prefix . '). ' .
                //                     'Address: Islam Tower, 2nd Floor, 102 Sukrabad, Dhanmondi 32.';
                //             }
                //             elseif ($doctorChanged && $dateChanged) {
                //                 // Both doctor and date changed
                //                 $sms_message .= 'Appointment updated to ' . Carbon::parse($date)->format('d M') . 
                //                     ' with Dr. ' . $doctor->first_name . ' ' . $doctor->last_name . 
                //                     ', SL: ' . $selectedSerialNumber . ' (' . $doctor->serial_prefix . '). ' .
                //                     'Address: Islam Tower, 2nd Floor, 102 Sukrabad, Dhanmondi 32.';
                //             } 
                //             elseif ($doctorChanged) {
                //                 // Only doctor changed
                //                 $sms_message .= 'Appointment updated on ' . Carbon::parse($date)->format('d M') . 
                //                     ' with Dr. ' . $doctor->first_name . ' ' . $doctor->last_name . 
                //                     ', SL: ' . $selectedSerialNumber . ' (' . $doctor->serial_prefix . '). ' .
                //                     'Address: Islam Tower, 2nd Floor, 102 Sukrabad, Dhanmondi 32.';
                //             } 
                //             elseif ($dateChanged) {
                //                 // Only date changed
                //                 $sms_message .= 'Appointment updated to ' . Carbon::parse($date)->format('d M') . 
                //                     ' with Dr. ' . $doctor->first_name . ' ' . $doctor->last_name . 
                //                     ', SL: ' . $selectedSerialNumber . ' (' . $doctor->serial_prefix . '). ' .
                //                     'Address: Islam Tower, 2nd Floor, 102 Sukrabad, Dhanmondi 32.';
                //             }

                //             // Add fasting instruction if required
                //             if ($is_fasting_required == 1) {
                //                 $sms_message .= ' Please stay fasting 8 hours before Dr. visit.';
                //             }

                //             $sms_response = '';
                //             if($doctor->type == 'doctor') {
                //                $sms_response = $this->sendAppointmentSMS($patient_phone, $sms_message);
                //             }
                            
                //             Log::info('Appointment update SMS sent response: ', [$sms_response]);
                //         }
                //     } catch (\Exception $e) {
                //         Log::error('Error sending appointment update SMS: ' . $e->getMessage());
                //         // Don't fail the whole request if SMS fails
                //     }
                // }

                $output = [
                    'success' => true,
                    'msg' => 'Appointment updated successfully.',
                    'appointment_number' => $patientApp->appointment_number,
                    'redirect_url' => route('all-appointment.index'),
                ];
            } catch (\Exception $e) {
                Log::error('Exception caught in update method:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);

                DB::rollBack();
                $errorDetails = [
                    'doctor_profile_id' => $doctor_profile,
                    'patient_profile_id' => $patient->id,
                    'error_message' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                ];

                if (strpos($e->getMessage(), 'Session store not set on request') !== false) {
                    Log::error('Session error while updating appointment:', $errorDetails);
                    $output = ['success' => false, 'msg' => 'Session is not initialized. Please try again later.'];
                } elseif ($e instanceof QueryException) {
                    Log::error('Database error while updating appointment:', $errorDetails);
                    $output = ['success' => false, 'msg' => 'Database error occurred. Please try again later.'];
                } elseif ($e instanceof ValidationException) {
                    Log::error('Validation error while updating appointment:', $errorDetails);
                    $output = ['success' => false, 'msg' => 'Validation error. Please check your input and try again.'];
                } else {
                    Log::error('Unexpected error while updating appointment:', $errorDetails);
                    $output = ['success' => false, 'msg' => 'Failed to update appointment. Please try again later.'];
                }
                if ($e->getCode() == 23000) {
                    $output = ['success' => false, 'msg' => 'Serial already Booked. Choose another serial number.'];
                }
            }
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
        if (! auth()->user()->can('appointment.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                DB::beginTransaction();
                $appointment = PatientAppointmentRequ::findOrFail($id);
                $appointment->cancel_status = 1;
                $appointment->remarks = 'cancelled';
                $appointment->save();
                $doctorProfileId = $appointment->doctor_profile_id;
                $date = $appointment->request_date;
                $requestedSlotIndex = json_decode($appointment->request_slot, true)['slot_details']['start'];
                $slots = DoctorAppointmentSloot::where('doctor_profile_id', $doctorProfileId)
                    ->where('calendar_date', $date)
                    ->first();
                $availableSlots = json_decode($slots->slots, true);
                $todaySlots = $availableSlots[$date] ?? [];
                $slotFound = false;
                foreach ($todaySlots as $key => $slot) {
                    if ($slot['start'] === $requestedSlotIndex) {
                        $todaySlots[$key]['reserved'] = max(0, $todaySlots[$key]['reserved'] - 1);
                        $slotFound = true;
                        break;
                    }
                }

                if ($slotFound) {
                    $availableSlots[$date] = $todaySlots;
                    $slots->slots = json_encode($availableSlots);
                    $slots->save();
                } else {
                    Log::error('No matching slot found to cancel for appointment.', [
                        'appointment_id' => $id,
                        'requestedSlot' => $requestedSlotIndex,
                        'availableSlots' => $todaySlots,
                    ]);
                }
                $appointment->delete();

                $this->commonUtil->activityLog($appointment, 'deleted', auth()->user(), [
                    'remarks' => 'Appointment cancelled and deleted.',
                ]);

                DB::commit();
                $output = [
                    'success' => true,
                    'msg' => 'Appointment cancelled successfully.',
                ];
            } catch (\Exception $e) {
                Log::error('Error while cancelling appointment:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]);
                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return response()->json($output);
        }
    }
    private function checkSLno($doctor_id, $selectedSerialNumber, $date, $id = null)
    {
        $query = DoctorSL::where('doctor_profile_id', $doctor_id)->where('sl_without_prefix', $selectedSerialNumber)->where('appointment_date', $date);
        if ($id) {
            $query->where('appointment_id', '!=', $id);
        }
        $doctorSL = $query->first();
        if ($doctorSL) {
            $output = [
                'success' => true,
                'msg' => 'Serial number already exists',
            ];
        } else {
            $output = [
                'success' => false,
                'msg' => 'Serial number Available',
            ];
        }
        return $output;
    }
    public function changeCallStatus($id)
    {
        if (!auth()->user()->can('appointment.change_call_status')) {
            abort(403, 'Unauthorized action.');
        }
        $common_settings = session('business.common_settings');
        $call_statuses = $common_settings['call_status'] ?? [];
        $callDropdown = collect($call_statuses)->pluck('call_status', 'call_status')->toArray();
        $appointment = PatientAppointmentRequ::find($id);
        $helped_by = '';
        if ($appointment->helped_by) {
            $helped_by = User::find($appointment->helped_by)->username;
        }
        return view('clinic::appointment.call_status_modal', compact('id', 'callDropdown', 'appointment', 'helped_by'));
    }
    public function updateCallStatus(Request $request, $id)
    {
        if (!auth()->user()->can('appointment.change_call_status')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $appointment = PatientAppointmentRequ::find($id);
            $appointment->call_status = $request->status;
            $appointment->comments = $request->comments;
            $appointment->helped_by = auth()->user()->id;
            $appointment->save();
            $output = [
                'success' => true,
                'msg' => 'Call status updated successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Error while updating call status:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        return $output;
    }
}
