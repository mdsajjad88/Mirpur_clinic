<?php

namespace Modules\Crm\Http\Controllers;

use App\Contact;
use App\Business;
use App\User;
use Modules\Crm\Entities\SmsLog;
use Modules\Crm\Entities\BulkSmsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\ContactFilter;
use Yajra\DataTables\Facades\DataTables;

class SmsController extends Controller
{
    /**
     * Show bulk SMS history index
     */
    public function index(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');

        if ($request->ajax()) {
            $query = BulkSmsHistory::with('user')
                ->where('business_id', $business_id)
                ->select('id', 'sms_body', 'total_contacts', 'success_count', 'fail_count', 'total_sms_count', 'created_at', 'created_by');

            if (! empty(request()->get('user_id'))) {
                $query->where('created_by', request()->get('user_id'));
            }

            if (! empty(request()->input('start_time')) && ! empty(request()->input('end_time'))) {
                $start_time = request()->input('start_time');
                $end_time = request()->input('end_time');
                $query->whereDate('created_at', '>=', $start_time)
                    ->whereDate('created_at', '<=', $end_time);
            }

            return DataTables::of($query)
                ->editColumn('created_at', '{{@format_datetime($created_at)}}')
                ->addColumn('sent_by', function ($row) {
                    return optional($row->user)->first_name . ' ' . optional($row->user)->last_name;
                })
                ->addColumn('action', function ($row) {
                    $html = '<a href="#" data-href="' . action([\Modules\Crm\Http\Controllers\SmsController::class, 'show'], [$row->id]) . '" data-container=".view_modal" class="btn-modal btn-info btn btn-sm">
                                <i class="fa fa-eye"></i> ' . __('messages.view') . '</a>';
                    return $html;
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $users = User::forDropdown($business_id, false);

        $contact_filters = ContactFilter::pluck('name', 'id');

        return view('crm::sms.index', compact('contact_filters', 'users'));
    }

    // Add this method to your SmsController
    public function create()
    {
        if (! auth()->user()->can('crm.send_sms')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact_filters = ContactFilter::pluck('name', 'id');
        
        return view('crm::sms.create', compact('contact_filters'));
    }

    /**
     * Show modal with details of individual SMS logs
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            $bulk = BulkSmsHistory::where('business_id', $business_id)->findOrFail($id);

            $logs = SmsLog::with('contact')
                ->where('bulk_sms_id', $bulk->id)
                ->get();

            return view('crm::sms.show', compact('bulk', 'logs'));
        }
    }

    /**
     * Process CSV upload and create contacts
     */
    public function processCsv(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');

        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
                'errors' => $validator->errors()
            ]);
        }

        try {
            $file = $request->file('csv_file');
            $csvData = array_map('str_getcsv', file($file->getRealPath()));

            // ✅ Extract and validate header
            $header = array_map('strtolower', array_map('trim', array_shift($csvData)));

            if (count($header) !== 2 || $header[0] !== 'name' || $header[1] !== 'phone') {
                return response()->json([
                    'success' => false,
                    'msg' => 'Invalid CSV format. The file must contain exactly 2 columns with headers: "name" and "phone". Example:
                            name,phone
                            John Doe,1234567890'
                ]);
            }

            $contacts = [];
            $errors = [];

            foreach ($csvData as $index => $row) {
                // Ensure exactly 2 columns per row
                if (count($row) !== 2) {
                    $errors[] = "Row " . ($index + 2) . ": Must have exactly 2 columns (name, phone)";
                    continue;
                }

                $name = trim($row[0]);
                $mobile = trim($row[1]);

                if (empty($mobile)) {
                    $errors[] = "Row " . ($index + 2) . ": Phone number is required";
                    continue;
                }

                // Check duplicate
                $existingContact = Contact::where('business_id', $business_id)
                    ->where('first_name', $name)
                    ->where('mobile', $mobile)
                    ->first();

                if ($existingContact) {
                    $contacts[] = [
                        'id' => $existingContact->id,
                        'name' => $existingContact->first_name,
                        'mobile' => $existingContact->mobile,
                        'is_new' => false
                    ];
                    continue;
                }

                // Create new
                $contact = Contact::create([
                    'business_id' => $business_id,
                    'first_name' => $name,
                    'mobile' => $mobile,
                    'type' => 'customer',
                    'created_by' => Auth::id()
                ]);

                $contacts[] = [
                    'id' => $contact->id,
                    'name' => $contact->first_name,
                    'mobile' => $contact->mobile,
                    'is_new' => true
                ];
            }

            return response()->json([
                'success' => true,
                'contacts' => $contacts,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }

    /**
     * Send SMS to selected contacts
     */
    public function sendSms(Request $request)
    {
        if (! auth()->user()->can('crm.send_sms')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        // ✅ Validation
        $validator = Validator::make($request->all(), [
            'sms_body' => 'required|string',
            'contact_ids' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
                'errors' => $validator->errors()
            ]);
        }

        try {
            $contactIds = $request->contact_ids;

            $contacts = Contact::whereIn('id', $contactIds)->get();

            if ($contacts->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'msg' => __('crm::lang.no_contacts_found')
                ]);
            }

            $smsBody = $request->sms_body;

            $results = [];
            $successCount = 0;
            $failCount = 0;
            $totalSmsCount = 0;

            // ✅ Create Bulk SMS History
            $bulk = BulkSmsHistory::create([
                'business_id' => $business_id,
                'sms_body' => $smsBody,
                'total_contacts' => $contacts->count(),
                'success_count' => 0,
                'fail_count' => 0,
                'total_sms_count' => 0,
                'created_by' => Auth::id()
            ]);

            foreach ($contacts as $contact) {
                $name = $contact->first_name . ' ' . $contact->last_name;
                $personalizedMessage = str_replace('{name}', $name, $smsBody);

                // ✅ Send SMS
                $response = $this->sendSMSConfig($contact->mobile, $personalizedMessage);
                $smsCount = $this->calculateSmsCount($personalizedMessage);

                // ✅ Log SMS
                SmsLog::create([
                    'bulk_sms_id' => $bulk->id,
                    'contact_id' => $contact->id,
                    'mobile_number' => $contact->mobile,
                    'sms_body' => $personalizedMessage,
                    'sms_length' => strlen($personalizedMessage),
                    'sms_count' => $smsCount,
                    'status' => $response['success'] ? 'success' : 'failed',
                    'response' => json_encode($response),
                    'created_by' => Auth::id()
                ]);

                $totalSmsCount += $smsCount;

                if ($response['success']) {
                    $successCount++;
                } else {
                    $failCount++;
                }

                $results[] = [
                    'contact' => $contact->first_name,
                    'mobile' => $contact->mobile,
                    'status' => $response['success'] ? 'success' : 'failed',
                    'message' => $response['success'] ? 'SMS sent successfully' : ($response['error'] ?? 'Unknown error')
                ];
            }

            // ✅ Update Bulk SMS History
            $bulk->update([
                'success_count' => $successCount,
                'fail_count' => $failCount,
                'total_sms_count' => $totalSmsCount
            ]);

            return response()->json([
                'success' => true,
                'msg' => "SMS sent to $successCount contacts, $failCount failed",
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }


    /**
     * Calculate SMS count based on message length
     */
    private function calculateSmsCount($message)
    {
        // Check if the message contains non-GSM characters (Unicode)
        $is_unicode = mb_detect_encoding($message, 'ASCII', true) === false;

        if ($is_unicode) {
            // Unicode SMS: 70 chars per SMS, 67 for concatenated
            $limit = 70;
            $concat_limit = 67;
        } else {
            // GSM SMS: 160 chars per SMS, 153 for concatenated
            $limit = 160;
            $concat_limit = 153;
        }

        $length = mb_strlen($message, 'UTF-8');

        if ($length <= $limit) {
            return 1;
        } else {
            return (int) ceil($length / $concat_limit);
        }
    }

    /**
     * Send SMS using your existing method
     */
    private function sendSMSConfig($number, $message)
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
            return ['success' => 1, 'response' => $response->json()];
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return ['success' => 0, 'error' => $e->getMessage()];
        }
    }


    public function getSmsLogInfo($contact_id)
    {
        try {
            $logs = SmsLog::with('createdBy')
                ->where('contact_id', $contact_id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($log) {
                    return [
                        'sent_time' => $log->created_at->format('Y-m-d H:i:s'),
                        'agent' => optional($log->createdBy)->first_name . ' ' . optional($log->createdBy)->last_name ?? '-',
                        'sms_body' => $log->sms_body,
                        'sms_length' => $log->sms_length,
                        'sms_count' => $log->sms_count,
                        'status' => ucfirst($log->status),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $logs
            ]);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }

}