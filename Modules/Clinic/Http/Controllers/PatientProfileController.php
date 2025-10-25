<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Clinic\Entities\{ReportAndProblem, PatientSessionInfo, Problem, MembershipTransaction, Intakeform, PatientAppointmentRequ, PatientDisease, PatientProfile, Prescription};
use App\{Product, TransactionPayment, Transaction, Category, Business, CashRegister, Contact};
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Modules\Crm\Entities\Schedule;

class PatientProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
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
    public function profileInfo($id, $date = null)
    {
        $business_id = request()->session()->get('user.business_id');
        try {
            // Check for existing appointment
            $requestType = request()->get('requestType');
            $is_edit = request()->get('is_edit');
            if ($requestType != 'call_log') {
                $appointment = PatientAppointmentRequ::where('patient_contact_id', $id)
                    ->where('request_date', $date)
                    ->where(function ($query) {
                        $query->where('remarks', '!=', 'prescribed')
                            ->where('remarks', '!=', 'cancelled')
                            ->where('remarks', '!=', 'refunded');
                    })
                    ->first();

                if ($appointment && !$is_edit) {
                    return response()->json([
                        'success' => false,
                        'msg' => 'Appointment already exists.'
                    ]);
                }
            }

            // Get patient and intake form data
            // Get patient profile or create one if not exists
            $patient = PatientProfile::where('patient_contact_id', $id)->first();

            $contact = Contact::find($id);

            if (!$patient) {
                if (!$contact) {
                    return response()->json(['success' => false, 'msg' => 'Contact not found.']);
                }

                $patient = PatientProfile::create([
                    'patient_contact_id' => $id,
                    'first_name' => $contact->first_name,
                    'last_name' => $contact->last_name,
                    'mobile' => $contact->mobile,
                    'gender' => $contact->gender ?? null, // if exists
                    'email' => $contact->email ?? null, // if exists
                ]);
            }
            $intakeForm = Intakeform::where('patient_contact_id', $id)->first();
            $appointment = PatientAppointmentRequ::where('patient_contact_id', $id)->whereNotNull('bill_no')->latest()->first();
            if ($appointment) {
                $bill_no = is_numeric($appointment->bill_no) ? $appointment->bill_no : (int)$appointment->bill_no;
                $session = PatientSessionInfo::where('transaction_id', $bill_no)
                    ->latest()->first();
                $payment = TransactionPayment::where('transaction_id', $session->transaction_id)->get();
                $transaction = Transaction::where('id', $session->transaction_id)->first();
                $total = $transaction->final_total;
                $paid = $payment->sum('amount');
                $due = $total - $paid;
                $is_closed = $session->is_closed == 1 ? 'Closed' : 'Running';

                $all_sessions = PatientSessionInfo::where('patient_contact_id', $id)->get();
            }

            // ✅ Optimized patient type logic with single DB query
            $previousAppointments = PatientAppointmentRequ::where('patient_profile_id', $patient->id)
                ->where('request_date', '<', $date)
                ->get();

            $hasAnyPrevious = $previousAppointments->isNotEmpty();
            $hasPrescribed = $previousAppointments->contains('remarks', 'prescribed');

            $lastPrescribedDate = $previousAppointments
                ->where('remarks', 'prescribed')
                ->max('request_date');

            if (!$hasAnyPrevious || !$hasPrescribed) {
                $patientType = 'New';
            } elseif ($lastPrescribedDate && Carbon::parse($lastPrescribedDate)->greaterThanOrEqualTo(Carbon::parse($date)->subMonths(4))) {
                $patientType = 'Followup';
            } else {
                $patientType = 'Old';
            }

            $healthConcerns = [];

            if ($intakeForm) {
                $concerns = ReportAndProblem::where('intake_form_id', $intakeForm->id)->pluck('problem_id')->toArray();
                if (is_array($concerns) && count($concerns)) {
                    $healthConcerns = Problem::whereIn('id', $concerns)->pluck('name')->toArray();
                }
            }
            if (empty($healthConcerns) && !empty($patient)) {
                $disease = PatientDisease::where('patient_profile_id', $patient->id)->pluck('disease_id')->toArray();
                if (is_array($disease) && count($disease)) {
                    $healthConcerns = Problem::whereIn('id', $disease)->pluck('name')->toArray();
                }
            }

            $life_stages = Category::forDropdown($business_id, 'life_stage');

            $cus_type = ucfirst($contact->type);
            if ($cus_type == 'Customer') {
                $cus_type = 'Patient';
            }
            $totalSchedule = Schedule::where('contact_id', $id)->count();
            $pendingScheduleCount = Schedule::where('contact_id', $id)->where('status', 'scheduled')->count();

            $response = [
                'success' => true,
                'age' => $patient->age ?? null,
                'mobile' => $patient->mobile ?? null,
                'email' => $patient->email ?? null,
                'gender' => $patient->gender ?? null,
                'diseases' => collect([]),
                'msg' => 'Data retrieved successfully',
                'payment' => $payment ?? [],
                'total' => $total ?? '',
                'paid' => $paid ?? '',
                'due' => $due ?? '',
                'is_closed' => $is_closed ?? '',
                'session' => $session ?? [],
                'all_sessions' => $all_sessions ?? [],
                'patientType' => $patientType,
                'healthConcerns' => $healthConcerns,
                'contact_life_stage_id' => $contact->crm_life_stage ?? null,
                'crm_source_id' => $contact->crm_source ?? null,
                'sub_source_id' => $contact->sub_source_id ?? null,
                'life_stages' => $life_stages,
                'cus_type' => $cus_type,
                'totalSchedule' => $totalSchedule,
                'pendingScheduleCount' => $pendingScheduleCount,
                'contact_id' => $contact->id
            ];

            // Add problems/diseases if intake form exists
            if ($intakeForm) {
                $problems = ReportAndProblem::where('intake_form_id', $intakeForm->id)->get();
                $response['diseases'] = collect($problems)->map(function ($problem) {
                    return [
                        'id' => $problem->problem_id,
                        'name' => $problem->problem_name
                    ];
                });

                // Remove null values for existing patients
                $response['age'] = $patient->age;
                $response['mobile'] = $patient->mobile;
                $response['email'] = $patient->email;
                $response['gender'] = $patient->gender;
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'msg' => 'Patient not found.']);
        } catch (\Exception $e) {
            Log::info('Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'msg' => 'An error occurred: ' . $e->getMessage()]);
        }
        return response()->json($response);
    }
    public function generateMemberShipCard($id)
    {
        $patient = PatientProfile::where('patient_contact_id', $id)->first();
        $contact = Contact::find($id);
        return view('clinic::patient.patients.card', compact('patient', 'contact'));
    }
    public function printMemberShipCard($id)
    {
        $patient = PatientProfile::where('patient_contact_id', $id)->first();
        $contact = Contact::find($id);
        return view('clinic::patient.patients.membership_card', compact('patient', 'contact'));
    }

    public function memebershipCardTransaction($id)
    {
        $contactId = $id;
        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->first();

        // Check if direct sale is 0 and register is closed

        try {
            $business_id = request()->session()->get('user.business_id');
            $business = Business::find($business_id);
            $common_settings = $business->common_settings;
            $clinic_location = $common_settings['clinic_location'] ?? null;
            $membershipProduct = $common_settings['membership_product'] ?? null;
            $product = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->select('products.*', 'variations.id as variation_id', 'variations.default_sell_price', 'variations.sell_price_inc_tax')
                ->where('products.id', $membershipProduct)
                ->first();
            $membershipTransaction = MembershipTransaction::where('contact_id', $contactId)->where('product_id', $membershipProduct)->first();
            // Start database transaction
            if (!empty($membershipTransaction)) {
                $transaction = Transaction::find($membershipTransaction->transaction_id);
                if ($transaction->payment_status == 'paid') {
                    $output = [
                        'success' => true,
                        'message' => 'This patient has already paid for the membership.',
                        'url' => route('print.membership.card', ['id' => $contactId]),
                    ];
                    return response()->json($output);
                } else if ($transaction->payment_status == 'partial') {
                    $output = [
                        'success' => false,
                        'message' => 'This patient not paid membership amount.',
                    ];
                } else if ($transaction->payment_status == 'due') {
                    $output = [
                        'success' => false,
                        'message' => 'This patient membership amount are due.',
                    ];
                }
                return response()->json($output);
            } else {
                if (!$register) {
                    $output = [
                        'success' => false,
                        'message' => __('Please Open Cashregister First'),
                    ];

                    return response()->json($output);
                }
                DB::beginTransaction();


                $modifiedData = [
                    '_token' => Session::token(),
                    'location_id' => $clinic_location,
                    'sub_type' => 'consultation',
                    'contact_id' => $contactId,
                    'status' => 'final',
                    'invoice_scheme_id' => '1',
                    'products' => [
                        1 => [
                            'product_type' => 'single',
                            'product_id' => $membershipProduct,
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
                    'business_id' => request()->session()->get('user.business_id'),
                    'location_id' => $clinic_location,
                    'user_id' => request()->session()->get('user.id'),
                    'sales_cmsn_agnt' => request()->session()->get('business.sales_cmsn_agnt'),
                    'transaction_date' => now()->format('d-m-Y h:i A'),
                ];

                $newRequest = clone request();
                $newRequest->merge($modifiedData);
                $newRequest->setLaravelSession(request()->session());
                $response = app(ClinicPosController::class)->store($newRequest);
                Log::info('Response from store method: ' . json_encode($response));
                if (is_array($response) && isset($response['success'])) {
                    if ($response['success']) {
                        $transactionId = $response['transaction_id'];
                        $mTransaction = new MembershipTransaction();
                        $mTransaction->contact_id = $contactId;
                        $mTransaction->transaction_id = $transactionId;
                        $mTransaction->product_id = $membershipProduct;
                        $mTransaction->created_by = auth()->user()->id;
                        $mTransaction->save();

                        $output = ['success' => false, 'message' => 'This patient membership amount are due.'];
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


                DB::commit();

                $output = ['success' => false, 'message' => 'This patient membership amount are due.'];
            }
        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollBack();
            Log::error('Error updating request: ' . $e->getMessage());

            // Return error response
            $output = ['success' => false, 'message' => 'Error updating request: ' . $e->getMessage()];
        }
        return $output;
    }
    public function checkPrescription($id)
    {
        try {
            $prescription = Prescription::where('patient_contact_id', $id)->latest()->first();
            $patient = PatientProfile::where('patient_contact_id', $id)->first();
            $output = [
                'success' => true,
                'doctor_id' => $prescription ? $prescription->doctor_user_id : null,
                'age' => $patient->age,
                'gender' => $patient->gender
            ];
            return $output;
        } catch (\Exception $e) {
            Log::error('Error checking prescription: ' . $e->getMessage());
            $output = ['success' => false, 'message' => 'Error checking prescription: ' . $e->getMessage()];
            return $output;
        }
    }
}
