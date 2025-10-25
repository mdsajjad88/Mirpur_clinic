<?php

namespace Modules\Clinic\Http\Controllers;

use App\CashRegister;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Yajra\DataTables\DataTables;
use Modules\Clinic\Utils\AppointmentUtil;
use App\Utils\ContactUtil;
use App\Utils\TransactionUtil;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use Modules\Clinic\Entities\PatientProfile;
use Modules\Clinic\Entities\PatientUser;
use App\Business;
use App\Product;
use App\{Transaction, TransactionPayment, Contact};
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\{DB, Http, Log};
use App\Utils\BusinessUtil;
use App\Utils\ProductUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\NotificationUtil;
use Carbon\Carbon;
use Modules\Clinic\Entities\Intakeform;
use Modules\Clinic\Entities\PatientSessionInfo;
use Modules\Clinic\Entities\SessionInfo;
use Modules\Clinic\Entities\PatientSessionDetails;
use Modules\Clinic\Entities\DoctorAppointmentSloot;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Entities\DoctorSL;
use Modules\Clinic\Entities\Prescription;

class AllAppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $contactUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $productUtil;
    protected $moduleUtil;
    protected $clinicSellUtil;
    protected $cashRegisterUtil;
    protected $notificationUtil;
    protected $appointmentUtil;

    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil, CashRegisterUtil $cashRegisterUtil, NotificationUtil $notificationUtil, AppointmentUtil $appointmentUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->appointmentUtil = $appointmentUtil;
        $this->dummyPaymentLine = [
            'method' => '',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
        ];

        $this->shipping_status_colors = [
            'ordered' => 'bg-yellow',
            'packed' => 'bg-info',
            'shipped' => 'bg-navy',
            'delivered' => 'bg-green',
            'cancelled' => 'bg-red',
        ];
    }


    public function index(Request $request)
    {
        if (!auth()->user()->can('clinic.all_appointment_show')) {
            abort(403, 'Unauthorized action.');
        }
        if ($request->ajax()) {
            $type = 'doctor';
            return $this->getAllAppointment($type);
        }
        $patients = PatientProfile::all();
        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->first();
        $statuses = ['booked' => 'Booked', 'confirmed' => 'Confirmed', 'prescribed' => 'Prescribed', 'expired' => 'Expired', 'refunded' => 'Refunded', 'cancelled' => 'Cancelled'];

        $doctors = DoctorProfile::where('is_doctor', 1)->get()->mapWithKeys(function ($doctor) {
            return [$doctor->id => $doctor->first_name . ' ' . $doctor->last_name];
        })->toArray();

        $common_settings = session('business.common_settings');
        $call_statuses = $common_settings['call_status'] ?? [];
        $callDropdown = collect($call_statuses)->pluck('call_status', 'call_status')->toArray();

        return view('clinic::appointment.all_appointment', compact('patients', 'statuses', 'register', 'doctors', 'callDropdown'));
    }
    private function getAllAppointment($type)
    {
        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings ?? [];
        $meeting_link = $common_settings['meeting_link'] ?? null;
        $is_admin = $this->contactUtil->is_admin(auth()->user());
        $query = $this->appointmentUtil->getAppointmentQuery($patient_profile_id = null, $type);
        if (!empty(request()->input('appointment_date'))) {
            $query->where('patient_appointment_requests.request_date', request()->input('appointment_date'));
        }
        if (!empty(request()->input('patient_profile_id'))) {
            $query->where('patient_appointment_requests.patient_profile_id', request()->input('patient_profile_id'));
        }
        if (!empty(request()->input('mobile'))) {
            $mobile = request()->input('mobile');
            $query->where('patient_profiles.mobile', 'LIKE', '%' . $mobile . '%');
        }
        if (!empty(request()->input('status'))) {
            $query->where('patient_appointment_requests.remarks', request()->input('status'));
        }


        if (!empty(request()->input('doctor_id'))) {
            $query->where('doctor_profiles.id', request()->input('doctor_id'));
        }

        if (!empty(request()->input('call_status'))) {
            $query->where('patient_appointment_requests.call_status', request()->input('call_status'));
        }

        if (!empty(request()->input('appointment_media'))) {
            $query->where('patient_appointment_requests.appointment_media', request()->input('appointment_media'));
        }

        if (!empty(request()->input('patient_type'))) {
            $query->where('patient_appointment_requests.type', request()->input('patient_type'));
        }
        $appointment = Datatables::of($query)
            ->addColumn('action', function ($row) use ($is_admin, $meeting_link) {
                $html = '<div class="btn-group">
                        <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                            data-toggle="dropdown" aria-expanded="false">' . __('messages.actions') . '
                        <span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                // Action links as permission
                if (auth()->user()->can('clinic.all_appointment_show')) {
                    if ($row->confirm_status != 1) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'show'], [$row->id]) . '" class="show_appointment_info" data-row-id="' . $row->id . '"><i class="fas fa-eye"></i> ' . __('messages.view') . '</a></li>';
                    }
                }

                if ($row->confirm_status != 1 && $row->cancel_status != 1) {
                    if (auth()->user()->can('appointment.final')) {
                        if ($row->appointment_type == 'therapist') {
                            $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'therapyAppointmentFinal'], [$row->id]) . '" class="getRequestToFinalBtn" data-row-id="' . $row->id . '"><i class="fas fa-clipboard-check"></i>  Final</a></li>';
                        } else {
                            $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'getRequestToFinal'], [$row->id]) . '" class="getRequestToFinalBtn" data-row-id="' . $row->id . '"><i class="fas fa-clipboard-check"></i>  Final</a></li>';
                        }
                    }
                    if (auth()->user()->can('appointment.cancel')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'cancelAppointment'], [$row->id]) . '" class="cancelAppointmentBtn" data-row-id="' . $row->id . '"><i class="fas fa-ban"></i>Cancel</a></li>';
                    }
                }
                if (($row->remarks != 'prescribed' || $row->remarks == 'cancelled') && $row->remarks != 'refunded' || $row->remarks != 'expired') {
                    if (auth()->user()->can('appointment.update')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'edit'], [$row->id]) . '" class="" data-row-id="' . $row->id . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    }
                }
                if ($row->confirm_status == 1 && $row->cancel_status != 1) {
                    if ($row->appointment_type != 'therapist') {
                        $html .= '<li><a href="#" data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->bill_no]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';
                    }
                    
                        $html .= '<li><a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->bill_no]) . '?app_id=' . $row->id . '" data-row-id="' . $row->id . '"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.print_invoice') . '</a></li>';
                }
                if (($row->confirm_status == 1 && $row->remarks == 'booked' && $row->cancel_status != 1) || $row->remarks == 'confirmed') {
                    if ($is_admin && $row->appointment_type != 'therapist') {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'edit'], [$row->bill_no]) . '"><i class="fas fa-pen-nib"></i> Bill Edit</a></li>';
                    }
                    if (auth()->user()->can('appointment.add_payment')) {
                        if ($row->appointment_type = 'therapist'  && $row->subscription_type == 'regular') {
                            $url = action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'create'])
                                . '?only_therapy_bill=true&session_id=' . $row->patient_session_info_id . '&appointment_id=' . $row->id;
                            $html .= '<li><a href="' . $url . '" class=""><i class="fas fa-edit"></i> Payment</a></li>';
                        } else {
                            $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\PatientPaymentController::class, 'show'], [$row->bill_no]) . '" class="view_payment_modal" data-row-id="' . $row->id . '"><i class="fas fa-edit"></i> Payment</a></li>';
                        }
                    }

                    // $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\BillReturnController::class, 'add'], [$row->bill_no]) . '"><i class="fas fa-undo"></i> ' . __('clinic::lang.bill_refund') . '</a></li>';
                }
                if ($row->confirm_status != 1) {
                    if (auth()->user()->can('appointment.delete')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'destroy'], [$row->id]) . '" class="delete_appointment_btn" data-row-id="' . $row->id . '"><i class="fas fa-trash"></i> Delete</a></li>';
                    }
                    if (auth()->user()->can('appointment.change_call_status')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'changeCallStatus'], [$row->id]) . '" class="change_call_status_btn" data-row-id="' . $row->id . '"><i class="fas fa-comment"></i>Call Status</a></li>';
                    }
                }

                // Add Send Meeting Link SMS button for Online appointments with confirmed status and non-empty meeting link
                if ($row->confirm_status == 1 && $row->appointment_media == 2 && !empty($meeting_link) && auth()->user()->can('appointment.send_sms')) {
                    $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'sendMeetingLinkSms'], [$row->id]) . '" class="send_meeting_link_sms" data-row-id="' . $row->id . '"><i class="fas fa-sms"></i> Send Meeting Link SMS</a></li>';
                }

                return $html;
            })
            ->addColumn('doctor_name', function ($row) {
                $name = $row->doctor_name ?? $row->doctor_first_name;

                if ($row->is_available == 1) {
                    $name .= ' <span style="color: green; font-size: 20px; vertical-align: middle;">●</span>';
                } elseif (
                    $row->is_available == 0 &&
                    \Carbon\Carbon::parse($row->break_date)->isToday() &&
                    (
                        is_null($row->break_end_time) || $row->break_end_time === '00:00:00'
                    )
                ) {
                    $start = \Carbon\Carbon::parse($row->break_start_time);
                    $end = \Carbon\Carbon::now();
                    $duration = $start->diffInMinutes($end);
                    $name .= ' <span style="color: red; vertical-align: middle;">(' . $duration . ' min)</span>';
                }
                return $name;
            })

            ->addColumn('patient_name', function ($row) {
                $fullName = $row->patient_first_name . ' ' . $row->patient_last_name;
                $phoneId = 'phone_' . $row->id;
                $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';

                if (auth()->user()->can('patient.phone_number')) {
                    return $fullName . ' ' . $row->patient_mobile;
                } else {
                    return $fullName . ' ' . $phoneIcon .
                        ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->patient_mobile . '</span>';
                }
            })
            ->addColumn('sl_no', function ($row) {
                if ($row->remarks == 'prescribed') {
                    return '<b style="color: ' . $row->prefixColor . ';">' . ($row->sl_no ?? 'N/A') . '</b>';
                } elseif ($row->remarks == 'confirmed') {
                    return '<a title="Announce SL" href="#" style="background-color: ' . $row->prefixColor . '; color: #fff" class="btn btn-xs announce_btn" 
                    data-row-id="' . $row->sl_id . '" 
                    data-row-sl_no="' . $row->sl_no . '" 
                    data-row-patient_name="' . $row->patient_first_name . ' ' . $row->patient_last_name . '" 
                    data-row-room_no="' . $row->doctor_room . '"><i class="fas fa-bullhorn"></i> <b>' . ($row->sl_no ?? 'N/A') . '</b></a><br> 
                    <a title="SL Status" href="' . action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'getSlStatus'], [$row->id]) . '" class="btn btn-xs sl_statu_btn mt-5" data-row-id="' . $row->id . '" style="background-color: ' . $row->prefixColor . '; color: #fff";>' . $row->sl_status . '</a>';
                } else {
                    return '<b style="color: ' . $row->prefixColor . ';">' . ($row->sl_no ?? 'N/A') . '</b>';
                }
            })
            ->addColumn(
                'transaction_payment_status',
                function ($row) {
                    $pay_status = ucfirst(trim($row->transaction_payment_status)) ?? 'Not Final';
                    $buttonColor = '';
                    switch (strtolower($pay_status)) {
                        case 'due':
                            $buttonColor = 'btn btn-orange';
                            break;
                        case 'partial':
                            $buttonColor = 'btn btn-yellow';
                            break;
                        case 'cancelled':
                            $buttonColor = 'btn btn-red';
                            break;
                        case 'paid':
                            $buttonColor = 'btn btn-success';
                            break;
                        default:
                            $buttonColor = 'btn btn-secondary';
                            break;
                    }

                    if ($pay_status == 'Not Final') {
                        return '<button class="' . $buttonColor . ' btn-xs">' . $pay_status . '</button>';
                    }
                    if (auth()->user()->can('appointment.add_payment')) {
                        return '<a href="' . action([\Modules\Clinic\Http\Controllers\PatientPaymentController::class, 'show'], [$row->bill_no]) . '" class="view_payment_modal ' . $buttonColor . ' btn-xs">' . $pay_status . '</a>';
                    } else {
                        return '<a href="#" class=" ' . $buttonColor . ' btn-xs">' . $pay_status . '</a>';
                    }
                }
            )
            ->addColumn('request_slot', function ($row) {
                if (is_null($row->request_slot)) {
                    return 'N/A';
                }

                $slotDetails = json_decode($row->request_slot, true);
                $start = $slotDetails['slot_details']['start'] ?? 'N/A';
                $end = $slotDetails['slot_details']['end'] ?? 'N/A';
                $slotTime = $start . ' - ' . $end;
                return $slotTime;
            })
            ->addColumn('appointment_media', function ($row) {
                if (!$row->appointment_media) {
                    return ' ';
                }

                switch ($row->appointment_media) {
                    case 1:
                        return '<span>In-Person VISIT</span>';
                    case 2:
                        // Online → red color
                        return '<span style="color: red;">Online</span>';
                    case 3:
                        // Report Follow-up → green color
                        return '<span style="color: green;">Report Follow-up</span>';
                    default:
                        return ' ';
                }
            })
            ->addColumn('contributor', function ($row) {
                return $row->contributor->first_name ?? ' ';
            })
            ->addColumn('creator', function ($row) {
                return $row->creator->first_name ?? ' ';
            })
            ->addColumn('patient_type', function ($row) {
                $type = $row->type ?? 'N/A';
                $buttonColor = '';
                switch (strtolower($type)) {
                    case 'new':
                        $buttonColor = 'btn-red';
                        break;
                    case 'followup':
                        $buttonColor = 'btn-success';
                        break;
                    case 'old':
                        $buttonColor = 'btn-purple';
                        break;
                    default:
                        $buttonColor = 'btn-secondary';
                        break;
                }
                return '<button title="' . $type . '" class="btn ' . $buttonColor . ' btn-xs">' . ucfirst($type) . '</button>';
            })
            ->addColumn('status', function ($row) {
                $status = ucfirst(trim($row->remarks));
                $buttonColor = '';
                switch (strtolower($status)) {
                    case 'booked':
                        $buttonColor = 'btn btn-info';
                        break;
                    case 'cancelled':
                        $buttonColor = 'btn btn-danger';
                        break;
                    case 'confirmed':
                        $buttonColor = 'btn btn-success';
                        break;
                    case 'prescribed':
                        $buttonColor = 'btn btn-prescribed';
                        break;
                    case 'expired':
                        $buttonColor = 'btn btn-red';
                        break;
                    case 'refunded':
                        $buttonColor = 'btn btn-orange';
                        break;
                    default:
                        $buttonColor = 'btn btn-secondary';
                        break;
                }
                return '<button class="' . $buttonColor . ' btn-xs">' . $status . '</button>';
            })
            ->addColumn('waiting_time', function ($row) {
                if (is_null($row->request_slot)) {
                    return 'N/A';
                }
                $currentTime = '';
                $buttonClass = '';
                $prescription = Prescription::where('appointment_id', $row->id)->latest()->first();
                $is_interval = false; // Initialize the flag
                $slotDetails = json_decode($row->request_slot, true);
                $start = $slotDetails['slot_details']['start'];
                $end = $slotDetails['slot_details']['end'];
                if ($prescription) {
                    $currentTime = $prescription->start_time ? Carbon::parse($prescription->start_time) : null;
                    if ($currentTime) {
                        $buttonClass = 'btn-info';
                    }
                }

                if (!$currentTime) {
                    $currentTime = Carbon::now();
                }

                $confirmTime = Carbon::parse($row->confirm_time);
                $interval = $currentTime->diff($confirmTime);
                $isLate = $interval->invert == 1;
                $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                if ($isLate) {
                    if ($totalMinutes >= 0 && $totalMinutes <= 20) {
                        $buttonClass = 'btn-yellow';
                    } elseif ($totalMinutes >= 21 && $totalMinutes <= 60) {
                        $buttonClass = 'btn-orange';
                    } elseif ($totalMinutes > 60 && $totalMinutes < 1440) {
                        $buttonClass = 'btn-danger';
                    } else if ($totalMinutes >= 1440) {
                        $buttonClass = '';
                    }

                    if (!$prescription) {
                        $waitingTimeMessage = 'Waiting for ';
                        $is_interval = true;
                    } else {
                        $waitingTimeMessage = 'Waited for ';
                        $buttonClass = 'btn-secondary';
                    }

                    // Calculate hours and minutes
                    $days = $interval->days;
                    $hours = $interval->h;
                    $minutes = $interval->i;
                    $seconds = $interval->s;

                    // If total time is under 1 hour, show in minutes

                    if ($totalMinutes <= 1440) {
                        if ($totalMinutes < 60) {
                            $waitingTimeMessage .= sprintf("%02d mins", $minutes);
                        } else if ($totalMinutes >= 60) {
                            $decimalHours = $hours + ($minutes / 60);
                            $waitingTimeMessage .= sprintf("%.2f hrs", $decimalHours);
                        }
                    } else if ($totalMinutes >= 1441) {
                        $is_interval = false;
                        $waitingTimeMessage = '';
                    }
                } else {
                    $waitingTimeMessage = "";
                    $buttonClass = 'btn-success';
                }
                if ($row->remarks == 'expired') {
                    $waitingTimeMessage = '';
                    $buttonClass = 'btn btn-red';
                }
                return '<div class="waiting-time" data-start-time="' . $confirmTime->toDateTimeString() . '" data-is-interval="' . ($is_interval ? 'true' : 'false') . '">
                            <i class="fas fa-clock"></i> ' . $start . ' - ' . $end . '
                            </br> <button class="btn btn-xs ' . $buttonClass . '" style="margin: 1px;">' . $waitingTimeMessage . '</button>
                        </div>';
            })
            ->addColumn('notes', function ($row) {
                return $row->comments;
            })
            ->addColumn('call_status', function ($row) {
                if (auth()->user()->can('appointment.change_call_status')) {
                    if (empty($row->call_status)) {
                        return '<a href="' . action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'changeCallStatus'], [$row->id]) . '" class="change_call_status_btn btn btn-xs btn-default" data-row-id="' . $row->id . '"><i class="fas fa-phone-slash"></i></a>';
                    }
                    return '<a href="' . action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'changeCallStatus'], [$row->id]) . '" class="change_call_status_btn btn btn-xs btn-default" data-row-id="' . $row->id . '">' . $row->call_status . '</a>';
                }
            })
            ->rawColumns(['doctor_name', 'patient_name', 'mobile', 'action', 'status', 'contributor', 'transaction_payment_status', 'updated_at', 'request_slot', 'waiting_time', 'sl_no', 'appointment_media', 'patient_type', 'notes', 'call_status'])
            ->make(true);
        return $appointment;
    }

    public function sendMeetingLinkSms($appointment_id)
    {
        if (!auth()->user()->can('appointment.send_sms')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Fetch the appointment with patient profile using JOIN
            $appointment = DB::table('patient_appointment_requests as par')
                ->join('patient_profiles as pp', 'par.patient_profile_id', '=', 'pp.id')
                ->where('par.id', $appointment_id)
                ->where('par.confirm_status', 1)
                ->where('par.appointment_media', 2)
                ->select(
                    'par.id',
                    'pp.first_name',
                    'pp.last_name',
                    'pp.mobile'
                )
                ->first();

            if (!$appointment) {
                return [
                    'success' => false,
                    'msg' => __('Appointment not found or does not meet criteria.')
                ];
            }

            // Fetch the meeting link from common settings
            $business_id = request()->session()->get('user.business_id');
            $business = Business::findOrFail($business_id);
            $common_settings = $business->common_settings ?? [];
            $meeting_link = $common_settings['meeting_link'] ?? null;

            if (empty($meeting_link)) {
                return [
                    'success' => false,
                    'msg' => __('Meeting link is not configured in settings.')
                ];
            }

            // Prepare the SMS message
            $patient_name = $appointment->first_name . ' ' . $appointment->last_name;
            $message = "Dear {$patient_name}, your online appointment is confirmed. Join the meeting here: {$meeting_link}";

            // Send SMS using the existing method
            $response = $this->sendAppointmentSMS($appointment->mobile, $message);

            if (isset($response['success']) && $response['success'] == 0) {
                return [
                    'success' => false,
                    'msg' => $response['error']
                ];
            }

            return [
                'success' => true,
                'msg' => __('SMS sent successfully.')
            ];
        } catch (\Exception $e) {
            Log::error('Error sending meeting link SMS: ' . $e->getMessage());
            return [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }
    }


    public function getSlStatus($id)
    {
        $sl = DoctorSL::where('appointment_id', $id)->first();
        return view('clinic::appointment.sl_status', compact('sl'));
    }

    public function changeSlStatus(Request $request)
    {
        $sl = DoctorSL::where('appointment_id', $request->id)->first();

        if ($sl) {
            $sl->status = $request->status;
            $sl->save();
            return response()->json(['success' => true, 'msg' => 'SL Status updated']);
        }

        return response()->json(['success' => false, 'msg' => 'Record not found'], 404);
    }

    // Method to get current calling status
    public function getCallingStatus()
    {
        try {
            $calling = DoctorSL::where('call_status', 'calling')
                ->join('contacts', 'doctor_s_ls.patient_contact_id', '=', 'contacts.id')
                ->join('doctor_profiles', 'doctor_s_ls.doctor_profile_id', '=', 'doctor_profiles.id')
                ->select(
                    'doctor_s_ls.*',
                    'contacts.name as patient_name',
                    'doctor_profiles.room as doctor_room',
                    DB::raw("CONCAT(doctor_profiles.first_name, ' ', COALESCE(doctor_profiles.last_name, '')) as doctor_name")
                )
                ->first();

            return response()->json([
                'success' => true,
                'calling' => $calling
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getCallingStatus: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch calling status.'
            ], 500);
        }
    }


    // Modified update method (simplified)
    public function updateCallStatus(Request $request)
    {
        $request->validate([
            'sl_id' => 'required|exists:doctor_s_ls,id',
            'status' => 'required|in:pending,calling,called'
        ]);

        try {
            DB::beginTransaction();

            $sl = DoctorSL::findOrFail($request->sl_id);

            // If changing to 'calling', update any existing 'calling' status to 'called'
            if ($request->status === 'calling' && auth()->check()) {
                DoctorSL::where('call_status', 'calling')
                    ->update([
                        'call_status' => 'called',
                        'called_by' => auth()->user()->id,
                    ]);
            }

            if (auth()->check()) {
                $sl->update([
                    'call_status' => $request->status,
                    'called_by' => auth()->user()->id,
                    'called_at' => now(),
                ]);
            } else {
                $sl->update([
                    'call_status' => $request->status,
                    'called_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
                'data' => $sl->fresh()
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
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
        $appointment = PatientAppointmentRequ::with('patient', 'doctor', 'contributor')->find($id);
        return view('clinic::appointment.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('clinic::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
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
    public function getRequestToFinal($id)
    {
        if (!auth()->user()->can('appointment.final')) {
            abort(403, 'Unauthorized action.');
        }

        $appointment = PatientAppointmentRequ::with('patient', 'doctor', 'contact')->find($id);
        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $clinic_location = $common_settings['clinic_location'] ?? null;
        $consultationSessions = $common_settings['consultation_sessions'] ?? [];

        $validSessions = array_filter($consultationSessions, function ($session) {
            return !empty($session['product_id']);
        });
        $productIds = array_column($validSessions, 'product_id');
        $products = Product::whereIn('id', $productIds)
            ->with('variations')
            ->get()
            ->keyBy('id');
        $productOptions = SessionInfo::where('status', 1)
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->session_name . ' - (৳' . $item->session_amount . ')'];
            });
        $payment_types = $this->transactionUtil->payment_types($clinic_location, true, $business_id);
        $session = PatientSessionInfo::where('patient_contact_id', $appointment->patient_contact_id)
            ->where(function ($query) {
                $query->where('is_closed', null)
                    ->orWhere('is_closed', 0);
            })
            ->where('remaining_visit', '>', 0)
            ->where('end_date', '>=', now())
            ->orderBy('start_date', 'desc')
            ->latest()->first();
        $is_intake_form = false;
        $intakePatient = Intakeform::where('patient_contact_id', $appointment->patient_contact_id)->first();
        if ($intakePatient) {
            $is_intake_form = true;
        } else {
            $is_intake_form = false;
        }
        $transaction = null;
        $payments = collect();

        if ($session) {
            $transaction = Transaction::find($session->transaction_id);

            if ($transaction) {
                $payments = TransactionPayment::where('transaction_id', $transaction->id)->get();
            }
        }
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if ($appointment) {
            return view('clinic::appointment.partials.request_to_final', compact('appointment', 'productOptions', 'payment_types', 'session', 'is_intake_form', 'payments', 'is_admin'));
        } else {
            return response()->json(['error' => 'Appointment not found.'], 404);
        }
    }

    public function updateRequestToFinal(Request $request)
    {
        if (!auth()->user()->can('appointment.final')) {
            abort(403, 'Unauthorized action.');
        }
        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->first();

        // Check if direct sale is 0 and register is closed
        if (!$register) {
            $output = [
                'success' => false,
                'message' => __('Please Open Cashregister First'),
            ];

            return response()->json($output);
        }
        try {
            $business_id = $request->session()->get('user.business_id');
            $appointment = PatientAppointmentRequ::find($request->input('appointment_id'));
            $business = Business::find($business_id);
            $common_settings = $business->common_settings;
            $clinic_location = $common_settings['clinic_location'] ?? null;
            $consultationSessions = $common_settings['consultation_sessions'] ?? [];
            $contact_id = $request->input('contact_id');
            $intakeForm = Intakeform::where('patient_contact_id', $contact_id)->first();
            if (!$intakeForm) {
                $output = [
                    'success' => false,
                    'message' => 'Please Update Intake form in this patient! ',
                ];
                return $output;
            }
            if (is_array($consultationSessions)) {
                $validSessions = array_filter($consultationSessions, function ($session) {
                    return !empty($session['product_id']);
                });
            }
            $productIds = array_column($validSessions, 'product_id');
            $sessionInfo = SessionInfo::where('id', $request->input('product_id'))->first();
            $product = Product::whereIn('id', $productIds)->get()->keyBy('id');
            $product = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->select('products.*', 'variations.id as variation_id', 'variations.default_sell_price', 'variations.sell_price_inc_tax')
                ->where('products.id', $sessionInfo->product_id)
                ->first();
            if ($sessionInfo->type == 'therapy' && $sessionInfo->sub_type == 'regular') {
                try {
                    DB::beginTransaction();

                    $newSession = new PatientSessionInfo();
                    $newSession->session_id = $request->input('product_id');
                    $newSession->patient_contact_id = $request->input('contact_id');
                    $newSession->created_by = auth()->user()->id;
                    $newSession->save();

                    $appointment = PatientAppointmentRequ::find($request->appointment_id);
                    $appointment->confirmed_by = auth()->user()->id;
                    $appointment->confirm_time = now();
                    $appointment->confirm_status = 1;
                    $appointment->remarks = 'confirmed';
                    $appointment->modified_by = auth()->user()->id;
                    $appointment->patient_session_info_id = $newSession->id;
                    $appointment->save();

                    DB::commit();

                    return response()->json(['success' => true, 'msg' => 'Appointment confirmed successfully.'], 200);
                } catch (\Throwable $th) {
                    DB::rollBack();
                    throw $th;
                }
            }

            // Start database transaction
            DB::beginTransaction();
            $slots = DoctorAppointmentSloot::where('id', $appointment->doctor_appointment_slot_id)->firstOrFail();
            $availableSlots = json_decode($slots->slots, true);
            $requestSlotData = json_decode($appointment->request_slot, true);
            $appointmentTime = $requestSlotData['appointment_time'] ?? null;

            if (!$appointmentTime) {
                DB::rollBack();
                return response()->json(['success' => false, 'msg' => 'Invalid appointment time.'], 400);
            }

            // Find the slot and increment "booked" count
            $updated = false;
            foreach ($availableSlots[$appointment->request_date] ?? [] as $key => $slot) {
                if ($slot['start'] === $appointmentTime) {
                    $availableSlots[$appointment->request_date][$key]['booked'] = isset($slot['booked']) ? $slot['booked'] + 1 : 1;
                    $updated = true;
                    break;
                }
            }


            if (!$updated) {
                Log::warning('No matching slot found to update booked count.', [
                    'appointment_id' => $appointment->id,
                    'appointment_time' => $appointmentTime,
                    'availableSlots' => $availableSlots
                ]);
            }

            $slots->slots = json_encode($availableSlots);
            $slots->save();

            $session = PatientSessionInfo::where('session_id', $request->input('product_id'))
                ->where('patient_contact_id', $contact_id)
                ->where(function ($query) {
                    $query->where('is_closed', null)
                        ->orWhere('is_closed', 0);
                })
                ->where('remaining_visit', '>', 0)
                ->where('end_date', '>=', now())
                ->first();
            if (!empty($session)) {
                $appointment->confirmed_by = auth()->user()->id;
                $appointment->confirm_time = now();
                $appointment->confirm_status = 1;
                $appointment->patient_session_info_id = $session->id;
                $appointment->bill_no = $session->transaction_id;
                $appointment->modified_by = auth()->user()->id;
                $appointment->save();
                $transactionId = $appointment->bill_no;
                $transaction = Transaction::where('id', $transactionId)->first();
                $transaction->reference_id = $appointment->doctor_user_id;
                $transaction->additional_notes = 'You Have Remaining ' . $session->remaining_visit - 1 . ' Consultancy';
                $transaction->save();
            } else {
                $modifiedData = [
                    '_token' => Session::token(),
                    'location_id' => $clinic_location,
                    'sub_type' => $sessionInfo->type,
                    'contact_id' => $request->input('contact_id'),
                    'status' => 'final',
                    'invoice_scheme_id' => '1',
                    'products' => [
                        1 => [
                            'product_type' => 'single',
                            'product_id' => $product->id,
                            'variation_id' => $product->variation_id,
                            'enable_stock' => 0,
                            'quantity' => 1.00,
                            'product_unit_id' => 1,
                            'unit_price' => $product->default_sell_price,
                            'unit_price_inc_tax' => $product->sell_price_inc_tax,
                            'final_total' => $product->sell_price_inc_tax,
                            'item_tax' => 0.00,
                            'tax_id' => null,
                        ],
                    ],
                    'hidden_price_group' => '0',
                    'default_price_group' => '0',
                    'types_of_service_id' => null,
                    'types_of_service_price_group' => null,
                    'pay_term_number' => null,
                    'pay_term_type' => null,
                    'invoice_no' => null,
                    'search_product' => null,
                    'tax_id' => null,
                    'line_discount_amount' => '0.00',
                    'line_discount_type' => 'fixed',
                    'discount_type' => 'fixed',
                    'item_tax' => '0.00',
                    'discount_amount' => '0.00',
                    'tax_rate_id' => null,
                    'tax_calculation_amount' => '0.00',
                    'sale_note' => null,
                    'is_direct_sale' => 0,
                    'shipping_details' => null,
                    'shipping_address' => null,
                    'shipping_charges' => '0.00',
                    'shipping_status' => null,
                    'delivered_to' => null,
                    'delivery_person' => null,
                    'additional_expense_key_1' => null,
                    'additional_expense_value_1' => '0',
                    'additional_expense_key_2' => null,
                    'additional_expense_value_2' => '0',
                    'additional_expense_key_3' => null,
                    'additional_expense_value_3' => '0',
                    'additional_expense_key_4' => null,
                    'additional_expense_value_4' => '0',
                    'round_off_amount' => '0',
                    'final_total' => $product->sell_price_inc_tax,
                    'advance_balance' => '0.0000',
                    'payment' => [
                        // '0' => [
                        //     'amount' => 0,
                        //     'paid_on' => now()->format('d-m-Y h:i A'),
                        //     'method' => $request->input('prefer_payment_method'),
                        //     'account_id' => null,
                        //     'card_number' => null,
                        //     'card_holder_name' => null,
                        //     'card_transaction_number' => null,
                        //     'card_type' => 'credit',
                        //     'card_month' => null,
                        //     'card_year' => null,
                        //     'card_security' => null,
                        //     'cheque_number' => null,
                        //     'bank_account_number' => null,
                        //     'transaction_no_1' => null,
                        //     'transaction_no_2' => null,
                        //     'transaction_no_3' => null,
                        //     'transaction_no_4' => null,
                        //     'transaction_no_5' => null,
                        //     'transaction_no_6' => null,
                        //     'transaction_no_7' => null,
                        //     'note' => null,
                        //     'item_tax' => '0.00',
                        //     'tax_id' => null,
                        // ],
                        'change_return' => [
                            'method' => 'cash',
                            'item_tax' => '0.00',
                            'tax_id' => null,
                            'account_id' => null,
                            'card_number' => null,
                            'card_holder_name' => null,
                            'card_transaction_number' => null,
                            'card_type' => 'credit',
                            'card_month' => null,
                            'card_year' => null,
                            'card_security' => null,
                            'cheque_number' => null,
                            'bank_account_number' => null,
                            'transaction_no_1' => null,
                            'transaction_no_2' => null,
                            'transaction_no_3' => null,
                            'transaction_no_4' => null,
                            'transaction_no_5' => null,
                            'transaction_no_6' => null,
                            'transaction_no_7' => null,
                        ]
                    ],
                    'change_return' => '0.00',
                    'is_save_and_print' => 11,
                    'recur_interval' => null,
                    'recur_interval_type' => 'days',
                    'recur_repetitions' => null,
                    'subscription_repeat_on' => null,
                    'business_id' => $request->session()->get('user.business_id'),
                    'location_id' => $clinic_location,
                    'user_id' => $request->session()->get('user.id'),
                    'sales_cmsn_agnt' => $request->session()->get('business.sales_cmsn_agnt'),
                    'reference_id' => $appointment->doctor_user_id,
                    'transaction_date' => now()->format('d-m-Y h:i A'),
                ];

                $newRequest = clone $request;
                $newRequest->merge($modifiedData);
                $newRequest->setLaravelSession($request->session());
                $response = app(ClinicPosController::class)->store($newRequest);
                if (is_array($response) && isset($response['success'])) {
                    if ($response['success']) {
                        $transaction = $response['transaction_id'];
                        $appointment = PatientAppointmentRequ::find($request->input('appointment_id'));
                        $appointment->confirmed_by = auth()->user()->id;
                        $appointment->confirm_time = now();
                        $appointment->confirm_status = 1;
                        $appointment->bill_no = $transaction;
                        $appointment->modified_by = auth()->user()->id;
                        $appointment->save();
                        $transactionId = $transaction;
                        $newSession = new PatientSessionInfo();
                        $newSession->session_id = $request->input('product_id');
                        $newSession->patient_contact_id = $contact_id;
                        $newSession->transaction_id = $transaction;
                        $newSession->start_date = now();
                        $newSession->end_date = now()->addMonths($sessionInfo->duration_month);
                        $newSession->total_visit = $sessionInfo->total_visit;
                        $newSession->visited_count = 0;
                        $newSession->remaining_visit = $sessionInfo->total_visit;
                        $newSession->created_by = auth()->user()->id;
                        $newSession->save();
                        $appointment->patient_session_info_id = $newSession->id;
                        $appointment->save();
                        if ($transaction) {
                            $transactions = Transaction::where('id', $transactionId)->first();
                            $transactions->additional_notes = 'You have remaining ' . ($newSession->remaining_visit - 1) . ' consultancy';
                            $transactions->save();
                        }
                        $contact = Contact::find($contact_id);
                        if ($contact) {
                            $customerId = $contact->contact_id;
                            if ($contact->type === 'lead') {
                                $contact->type = 'customer';
                                $contact->converted_by = $appointment->created_by;
                                $contact->converted_on = Carbon::now();
                                $contact->save();
                                    
                            }
                        }
                    } else {
                        Log::error("Failed to create transaction", $response);
                    }
                } elseif ($response instanceof \Illuminate\Http\RedirectResponse) {
                    Log::info('Redirecting to URL: ' . $response->getTargetUrl());
                    preg_match('/invoice\/([a-z0-9]+)\?/', $response->getTargetUrl(), $matches);
                    if (isset($matches[1])) {
                        $transactionIdFromUrl = $matches[1];
                        Log::info("Transaction ID from redirect URL: " . $transactionIdFromUrl);
                    }
                } else {
                    Log::error("Unexpected response from store method", ['response' => $response]);
                    $output = [
                        'success' => false,
                        'message' => 'Unexpected response from store method',
                    ];
                    return $output;
                }
            }

            $this->transactionUtil->activityLog($appointment, 'updated', auth()->user(), [
                'confirmed_by' => $appointment->confirmed_by,
                'confirm_time' => $appointment->confirm_time,
                'confirm_status' => $appointment->confirm_status,
                'bill_no' => $appointment->bill_no,
                'modified_by' => $appointment->modified_by,
                'patient_session_info_id' => $appointment->patient_session_info_id
            ]);


            DB::commit();
            // Return success response
            $output = ['success' => true, 'message' => 'Appointment updated successfully!', 'row_id' => $request->input('row_id'), 'transaction_id' => $transactionId];
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Error updating request: ' . $e->getMessage());

            // Return error response
            $output = ['success' => false, 'message' => 'Error updating request: ' . $e->getMessage()];
        }
        return $output;
    }
    public function cancelAppointment($id)
    {
        if (! auth()->user()->can('appointment.cancel')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                DB::beginTransaction();
                $slNo = DoctorSL::where('appointment_id', $id)->first();
                if ($slNo) {
                    $slNo->delete();
                }
                $appointment = PatientAppointmentRequ::findOrFail($id);

                // Store appointment details for SMS before cancellation
                $doctorProfileId = $appointment->doctor_profile_id;
                $date = $appointment->request_date;
                $serialNumber = $slNo ? $slNo->sl_no : 'N/A';
                $patientContactId = $appointment->patient_contact_id;

                $appointment->cancel_status = 1;
                $appointment->remarks = 'cancelled';
                $appointment->save();

                $requestedSlotIndex = json_decode($appointment->request_slot, true)['slot_details']['start'];
                $slots = DoctorAppointmentSloot::where('doctor_profile_id', $doctorProfileId)
                    ->where('calendar_date', $date)
                    ->first();
                $appointment->request_slot = null;
                $appointment->save();
                $availableSlots = json_decode($slots->slots, true);
                $todaySlots = $availableSlots[$date] ?? [];
                $slotFound = false;
                foreach ($todaySlots as $key => $slot) {
                    if ($slot['start'] === $requestedSlotIndex) {
                        $todaySlots[$key]['reserved']--;
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
                $this->transactionUtil->activityLog($appointment, 'cancelled', auth()->user(), [
                    'remarks' => $appointment->remarks,
                    'cancel_status' => $appointment->cancel_status,
                ]);

                DB::commit();

                // Send cancellation SMS
                try {
                    $contact = Contact::find($patientContactId);
                    if ($contact && !empty($contact->mobile)) {
                        $doctor = DoctorProfile::find($doctorProfileId);
                        $doctorName = $doctor ? $doctor->first_name . ' ' . $doctor->last_name : 'Unknown Doctor';

                        // New SMS format
                        $sms_message = 'Dear ' . $contact->name .
                            ', your appointment with Dr. ' . $doctorName .
                            ' on ' . Carbon::parse($date)->format('d M') .
                            ' (SL: ' . $serialNumber . ') has been cancelled.';

                        $sms_response = $this->sendAppointmentSMS($contact->mobile, $sms_message);
                        Log::info('Appointment cancellation SMS sent response: ', [$sms_response]);
                    }
                } catch (\Exception $e) {
                    Log::error('Error sending appointment cancellation SMS: ' . $e->getMessage());
                    // Don't fail the whole cancellation process if SMS fails
                }

                $output = [
                    'success' => true,
                    'msg' => 'Appointment cancelled successfully.',
                ];
            } catch (\Exception $e) {
                DB::rollBack();
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


    public function therapyAppointmentFinal($id)
    {

        $subscriptions = SessionInfo::where('status', 1)
            ->where('type', 'therapy')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->session_name . ' - (৳' . $item->session_amount . ')'];
            });
        // $subscriptions = SessionInfo::where('status', 1)->where('type', 'therapy')->pluck('session_name', 'id')->toArray();


        $appointment = PatientAppointmentRequ::with('patient', 'doctor', 'contact')->find($id);
        $payments = collect();
        return view('clinic::physiotherapist.appointment.request_to_final', compact('subscriptions', 'id', 'appointment', 'payments'));
    }
    public function requestToFinalTherapyAppointment(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $clinic_location = $common_settings['clinic_location'] ?? null;

        try {
            $subscription = SessionInfo::find($request->input('subscription_id'));
            if ($subscription->type == 'therapy' && $subscription->sub_type == 'regular') {
                Log::info("requestToFinalTherapyAppointment: " . json_encode($request->all()));
                $newSession = new PatientSessionInfo();
                $newSession->session_id = $request->input('subscription_id');
                $newSession->patient_contact_id = $request->input('contact_id');
                $newSession->created_by = auth()->user()->id;
                $newSession->save();

                $appointment = PatientAppointmentRequ::find($request->appointment_id);
                $appointment->confirmed_by = auth()->user()->id;
                $appointment->confirm_time = now();
                $appointment->confirm_status = 1;
                $appointment->remarks = 'confirmed';
                $appointment->modified_by = auth()->user()->id;
                $appointment->patient_session_info_id = $newSession->id;
                $appointment->save();
            } else if ($subscription->type == 'therapy' && $subscription->sub_type == 'subscription') {
                $checkSession = PatientSessionInfo::where('patient_contact_id', $request->input('contact_id'))->where('session_id', $request->input('subscription_id'))
                    ->where(function ($query) {
                        $query->where('is_closed', null)
                            ->orWhere('is_closed', 0);
                    })->where('remaining_visit', '>', 0)
                    ->where('end_date', '>=', now())->first();
                if (!empty($checkSession)) {
                    $appointment = PatientAppointmentRequ::find($request->appointment_id);
                    $appointment->confirmed_by = auth()->user()->id;
                    $appointment->confirm_time = now();
                    $appointment->confirm_status = 1;
                    $appointment->remarks = 'confirmed';
                    $appointment->patient_session_info_id = $checkSession->id;
                    $appointment->modified_by = auth()->user()->id;
                    $appointment->bill_no = $checkSession->transaction_id;
                    $appointment->save();

                    $transactionId = $appointment->bill_no;
                    $transaction = Transaction::where('id', $transactionId)->first();
                    $transaction->reference_id = $appointment->doctor_user_id;
                    $transaction->save();
                }
            } else {
            }

            return response()->json(['success' => true, 'message' => 'Appointment confirmed successfully']);
        } catch (\Exception $e) {
            Log::error('Appointment confirming failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        }
    }

    public function therapyAppointment(Request $request)
    {
        if (!auth()->user()->can('clinic.all_appointment_show')) {
            abort(403, 'Unauthorized action.');
        }
        if ($request->ajax()) {
            $type = 'therapist';
            return $this->getAllAppointment($type);
        }
        $patients = PatientProfile::all();
        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->first();
        $statuses = ['booked' => 'Booked', 'confirmed' => 'Confirmed', 'prescribed' => 'Prescribed', 'expired' => 'Expired', 'refunded' => 'Refunded', 'cancelled' => 'Cancelled'];

        $doctors = DoctorProfile::where('is_doctor', 1)->get()->mapWithKeys(function ($doctor) {
            return [$doctor->id => $doctor->first_name . ' ' . $doctor->last_name];
        })->toArray();

        $common_settings = session('business.common_settings');
        $call_statuses = $common_settings['call_status'] ?? [];
        $callDropdown = collect($call_statuses)->pluck('call_status', 'call_status')->toArray();

        return view('clinic::appointment.therapy_appointment', compact('patients', 'statuses', 'register', 'doctors', 'callDropdown'));
    }
}
