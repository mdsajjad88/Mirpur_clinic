<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Charts\CommonChart;
use App\Currency;
use App\Business;
use App\BusinessLocation;
use App\Media;
use App\Transaction;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Entities\Prescription;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use Yajra\DataTables\Facades\DataTables;

class ClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $businessUtil;

    protected $moduleUtil;

    protected $commonUtil;

    protected $transactionUtil;

    public function __construct(ModuleUtil $moduleUtil, TransactionUtil $transactionUtil, BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
        $this->moduleUtil = $moduleUtil;
        $this->transactionUtil = $transactionUtil;
    }
    public function index()
    {
        if (!auth()->user()->can('clinic.dashboard.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'clinic_module') && auth()->user()->can('clinic.view'))) {
            abort(403, 'Unauthorized action.');
        }
        $common_settings = session()->get('business.common_settings');
        $clinic_location = $common_settings['clinic_location'] ?? null;
        if (empty($clinic_location)) {
            $output = [
                'success' => 0,
                'msg' => __('lang_v1.clinic_module_error_location'),
            ];
            return redirect()->route('business.getBusinessSettings')->with('status', $output);
        }
        
        session([
            'clinic_location' => $clinic_location,
        ]);

        $fy = $this->businessUtil->getCurrentFinancialYear($business_id);

        $currency = Currency::where('id', request()->session()->get('business.currency_id'))->first();
        //ensure start date starts from at least 30 days before to get sells last 30 days
        $least_30_days = \Carbon::parse($fy['start'])->subDays(30)->format('Y-m-d');

        //get all sells
        $sells_this_fy = $this->transactionUtil->getSellsCurrentFy($business_id, $least_30_days, $fy['end']);

        $all_locations = BusinessLocation::forDropdown($business_id)->toArray();

        //Chart for sells last 30 days
        $labels = [];
        $all_sell_values = [];
        $dates = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = \Carbon::now()->subDays($i)->format('Y-m-d');
            $dates[] = $date;

            $labels[] = date('j M Y', strtotime($date));

            $total_sell_on_date = $sells_this_fy->where('date', $date)->sum('total_sells');

            if (! empty($total_sell_on_date)) {
                $all_sell_values[] = (float) $total_sell_on_date;
            } else {
                $all_sell_values[] = 0;
            }
        }

        //Group sells by location
        $location_sells = [];
        foreach ($all_locations as $loc_id => $loc_name) {
            $values = [];
            foreach ($dates as $date) {
                $total_sell_on_date_location = $sells_this_fy->where('date', $date)->where('location_id', $loc_id)->sum('total_sells');

                if (! empty($total_sell_on_date_location)) {
                    $values[] = (float) $total_sell_on_date_location;
                } else {
                    $values[] = 0;
                }
            }
            $location_sells[$loc_id]['loc_label'] = $loc_name;
            $location_sells[$loc_id]['values'] = $values;
        }

        $sells_chart_1 = new CommonChart;

        $sells_chart_1->labels($labels)
                        ->options($this->__chartOptions(__(
                            'home.total_sells',
                            ['currency' => $currency->code]
                            )));

        if (! empty($location_sells)) {
            foreach ($location_sells as $location_sell) {
                $sells_chart_1->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
            }
        }

        if (count($all_locations) > 1) {
            $sells_chart_1->dataset(__('report.all_locations'), 'line', $all_sell_values);
        }

        $labels = [];
        $values = [];
        $date = strtotime($fy['start']);
        $last = date('m-Y', strtotime($fy['end']));
        $fy_months = [];
        do {
            $month_year = date('m-Y', $date);
            $fy_months[] = $month_year;

            $labels[] = \Carbon::createFromFormat('m-Y', $month_year)
                            ->format('M-Y');
            $date = strtotime('+1 month', $date);

            $total_sell_in_month_year = $sells_this_fy->where('yearmonth', $month_year)->sum('total_sells');

            if (! empty($total_sell_in_month_year)) {
                $values[] = (float) $total_sell_in_month_year;
            } else {
                $values[] = 0;
            }
        } while ($month_year != $last);

        $fy_sells_by_location_data = [];

        foreach ($all_locations as $loc_id => $loc_name) {
            $values_data = [];
            foreach ($fy_months as $month) {
                $total_sell_in_month_year_location = $sells_this_fy->where('yearmonth', $month)->where('location_id', $loc_id)->sum('total_sells');

                if (! empty($total_sell_in_month_year_location)) {
                    $values_data[] = (float) $total_sell_in_month_year_location;
                } else {
                    $values_data[] = 0;
                }
            }
            $fy_sells_by_location_data[$loc_id]['loc_label'] = $loc_name;
            $fy_sells_by_location_data[$loc_id]['values'] = $values_data;
        }

        $sells_chart_2 = new CommonChart;
        $sells_chart_2->labels($labels)
                    ->options($this->__chartOptions(__(
                        'home.total_sells',
                        ['currency' => $currency->code]
                            )));
        if (! empty($fy_sells_by_location_data)) {
            foreach ($fy_sells_by_location_data as $location_sell) {
                $sells_chart_2->dataset($location_sell['loc_label'], 'line', $location_sell['values']);
            }
        }
        if (count($all_locations) > 1) {
            $sells_chart_2->dataset(__('report.all_locations'), 'line', $values);
        }

        return view('clinic::dashboard.index', compact('clinic_location','sells_chart_1', 'sells_chart_2', 'all_locations'));
    }


    private function __chartOptions($title)
    {
        return [
            'yAxis' => [
                'title' => [
                    'text' => $title,
                ],
            ],
            'legend' => [
                'align' => 'right',
                'verticalAlign' => 'top',
                'floating' => true,
                'layout' => 'vertical',
                'padding' => 20,
            ],
        ];
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

    public function getTotals()
    {
        if (request()->ajax()) {
            $start = request()->start;
            $end = request()->end;
            $location_id = request()->location_id;
            $business_id = request()->session()->get('user.business_id');

            // Fetch patient appointments
            $patient_appointments = $this->transactionUtil->getPatientAppointments($business_id, $start, $end, $location_id);

            // Initialize counts
            $output['new_patients_count'] = 0;
            $output['followup_patients_count'] = 0;
            $output['old_patients_count'] = 0;

            // Calculate counts for each patient type
            foreach ($patient_appointments as $appointment) {
                if ($appointment->patient_type == 'New') {
                    $output['new_patients_count'] = $appointment->count;
                } elseif ($appointment->patient_type == 'Followup') {
                    $output['followup_patients_count'] = $appointment->count;
                } elseif ($appointment->patient_type == 'Old') {
                    $output['old_patients_count'] = $appointment->count;
                }
            }
            // Calculate total appointments (sum of all patient types)
            $output['appointments_count'] = $output['new_patients_count'] + $output['followup_patients_count'] + $output['old_patients_count'];


            $sell_details = $this->transactionUtil->getSellTotalsClinic($business_id, $start, $end, $location_id);

            $total_sell_return = $this->transactionUtil->getSellReturnClinic($business_id, $start, $end, $location_id);
            $output['total_sell_return_paid'] = $this->transactionUtil->getTotalSellReturnPaid($business_id, $start, $end, $location_id);
            // New Additions
            $output['total_bill_income'] = $sell_details['total_bill_income'];
            $output['count_total_bill_income'] = $sell_details['count_total_bill_income'];
            $output['bill_income_per_customer'] = $sell_details['bill_income_per_customer'];
            $due_income = $this->transactionUtil->getDueIncomeClinic($business_id, $start, $end, $location_id);
            $output['due_income'] = $due_income['due_income'];
            $output['count_due_income'] = $due_income['count_due_income'];
            $output['due_income_details'] = $due_income['sell_details'];
            $due_bill = $this->transactionUtil->getDueBillClinic($business_id, $start, $end, $location_id);
            $output['due_bill'] = $due_bill['due_bill'];
            $output['due_bill_details'] = $due_bill['sell_details'];
            $output['count_due_bill'] = $due_bill['count_due_bill'];
            $output['return_refund'] = $total_sell_return['return_refund_amount'] ?? 0;
            $output['count_return_refund'] = $total_sell_return['count_return_refund'] ?? 0;
            $output['return_refund_details'] = $total_sell_return['sell_details'] ?? 0;
            $output['special_discount'] = $sell_details['special_discount'];
            $output['special_discount_details'] = $sell_details['customer_details'];


            // NET INCOME = Total Sales + Due Income - Due Sales - Returns
            $output['net_income'] = $output['total_bill_income'] + $output['due_income'] - $output['due_bill'] - $output['return_refund'];

            // Cash = Only Cash from Net Income
            $cash_amount = $this->transactionUtil->getCashClinic($business_id, $start, $end, $location_id);

            $output['cash_income'] = $cash_amount['cash_amount'];
            $output['current_cash_income_details'] = $cash_amount['current_income'];
            $output['due_cash_income_details'] = $cash_amount['due_income'];

            // service details
            $output['service_details'] = $this->transactionUtil->getServiceClinic($business_id, $start, $end, $location_id);

            $output['session_information'] = $this->transactionUtil->getSessionClinic($business_id, $start, $end, $location_id);
            $output['total_session_count'] = $output['session_information']->sum('count');
            $output['total_session_amount'] = $output['session_information']->sum('amount');

            $output['therapy'] = $this->transactionUtil->getTherapyClinic($business_id, $start, $end, $location_id);
            $output['therapy_item_count'] = $output['therapy']->sum('therapy_item_count');
            $output['therapy_bill_count'] = $output['therapy']->sum('therapy_bill_count');
            $output['total_therapy_amount'] = $output['therapy']->sum('amount');
            $output['tests'] = $this->transactionUtil->getTestClinic($business_id, $start, $end, $location_id);
            $output['test_bill'] = $this->transactionUtil->getTestBillClinic($business_id, $start, $end, $location_id);
            $output['test_item_count'] = $output['tests']->sum('test_item_count');
            $output['test_bill_count'] = $output['test_bill']->sum('test_bill_count');
            $output['total_test_amount'] = $output['test_bill']->sum('amount');
            $output['ipds'] = $this->transactionUtil->getIpdClinic($business_id, $start, $end, $location_id);
            $output['total_ipd_count'] = $output['ipds']->sum('count');
            $output['total_ipd_amount'] = $output['ipds']->sum('amount');

            $output['doctors_patients'] = $this->DoctorsPatientsChart($business_id, $start, $end, $location_id);

            $output['patient_appointment'] = $patient_appointments;

            return $output;
        }
    }


    public function getDueSaleReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

        // Base query for credit sales
        $credit_sales = Transaction::where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->whereIn('transactions.payment_status', ['due', 'partial'])
            ->where('transactions.is_direct_sale', 0)
            ->leftJoin('contacts AS c', 'transactions.contact_id', '=', 'c.id')
            ->leftJoin('transaction_payments AS tp', 'transactions.id', '=', 'tp.transaction_id')
            ->leftJoin('users AS u', 'transactions.created_by', '=', 'u.id')
            ->select(
                'transactions.id',
                'transactions.transaction_date as sale_date',
                'transactions.final_total as total_amount',
                'transactions.invoice_no as invoice_no',
                'c.name as customer_name',
                'c.mobile as customer_mobile',
                DB::raw('COALESCE(SUM(tp.amount), 0) as total_paid'),
                DB::raw('transactions.final_total - COALESCE(SUM(tp.amount), 0) as total_due'),
                DB::raw("MAX(CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, ''))) as billed_by")
            )
            ->groupBy('transactions.id')
            ->orderBy('transactions.transaction_date', 'desc');

        // if (!empty($start_date) && !empty($end_date)) {
        //     $credit_sales->whereBetween('transactions.transaction_date', [$start_date, $end_date]);
        // }

        // Return data for AJAX call
        if ($request->ajax()) {
            return DataTables::of($credit_sales)
                ->addColumn('action', ' <a data-href="{{action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, \'show\'], [$id])}}"   class="btn-modal cursor-pointer" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> @lang("messages.view")</a>')
                ->editColumn('invoice_no', function ($row) {
                    return $row->invoice_no;
                })
                ->addColumn('customer_name', function ($row) {
                    $fullName = $row->customer_name;
                    $phoneId = 'phone_' . $row->id;
                    $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';
                    
                    if (auth()->user()->can('patient.phone_number')) {
                        return $fullName . ' (' . $row->customer_mobile . ')';
                    } else {
                        return $fullName . ' ' . $phoneIcon . 
                            ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->customer_mobile . '</span>';
                    }
                })
                ->editColumn('sale_date', function ($row) {
                    return $this->transactionUtil->format_date($row->sale_date, true);
                })
                ->editColumn('total_amount', function ($row) {
                    return '<span data-orig-value="' . $row->total_amount . '">' . $this->transactionUtil->num_f($row->total_amount, true) . '</span>';
                })
                ->editColumn('total_due', function ($row) {
                    return '<span data-orig-value="' . $row->total_due . '" class="view_payment_modal">' . $this->transactionUtil->num_f($row->total_due, true) . '</span>';
                })
                ->rawColumns(['total_amount', 'total_due', 'invoice_no', 'action', 'customer_name'])
                ->make(true);
        }
    }

    public function DoctorsPatientsChart($business_id, $start_date = null, $end_date = null, $location_id = null, $created_by = null, $permitted_locations = null)
    {
        // Get all doctors
        $doctors = DoctorProfile::where('is_doctor', 1)->where('is_active', 1)->get();

        // Initialize chart data arrays
        $chartData = [
            'labels' => [],
            'patients' => [],
            'backgroundColors' => [],
            'borderColors' => []
        ];

        $colors = [
            ['background' => 'rgba(255, 99, 132, 0.7)', 'border' => 'rgba(255, 99, 132, 1)'],  // Red
            ['background' => 'rgba(54, 162, 235, 0.7)', 'border' => 'rgba(54, 162, 235, 1)'],  // Blue
            ['background' => 'rgba(255, 206, 86, 0.7)', 'border' => 'rgba(255, 206, 86, 1)'],  // Yellow
            ['background' => 'rgba(75, 192, 192, 0.7)', 'border' => 'rgba(75, 192, 192, 1)'],  // Teal
            ['background' => 'rgba(153, 102, 255, 0.7)', 'border' => 'rgba(153, 102, 255, 1)'], // Purple
            ['background' => 'rgba(255, 159, 64, 0.7)', 'border' => 'rgba(255, 159, 64, 1)'],  // Orange
            ['background' => 'rgba(201, 203, 207, 0.7)', 'border' => 'rgba(201, 203, 207, 1)'], // Light Gray
            ['background' => 'rgba(0, 128, 0, 0.7)', 'border' => 'rgba(0, 128, 0, 1)'],        // Green
            ['background' => 'rgba(128, 0, 128, 0.7)', 'border' => 'rgba(128, 0, 128, 1)'],    // Dark Purple
            ['background' => 'rgba(255, 20, 147, 0.7)', 'border' => 'rgba(255, 20, 147, 1)'],  // Deep Pink
            ['background' => 'rgba(30, 144, 255, 0.7)', 'border' => 'rgba(30, 144, 255, 1)'],  // Dodger Blue
            ['background' => 'rgba(255, 140, 0, 0.7)', 'border' => 'rgba(255, 140, 0, 1)'],    // Dark Orange
            ['background' => 'rgba(50, 205, 50, 0.7)', 'border' => 'rgba(50, 205, 50, 1)'],    // Lime Green
            ['background' => 'rgba(128, 128, 0, 0.7)', 'border' => 'rgba(128, 128, 0, 1)'],    // Olive
            ['background' => 'rgba(0, 206, 209, 0.7)', 'border' => 'rgba(0, 206, 209, 1)'],    // Dark Turquoise
        ];
        

        // Process each doctor's data
        foreach ($doctors as $index => $doctor) {
            // Get prescriptions count for this doctor within date range
            $patientsSeen = Prescription::where('doctor_user_id', $doctor->user_id)
                ->whereBetween('prescription_date', [$start_date, $end_date])
                ->count();

            // Add doctor name to labels
            $chartData['labels'][] = $doctor->first_name . ' ' . $doctor->last_name . ' (' . $patientsSeen . ')';
            
            // Add patient count
            $chartData['patients'][] = $patientsSeen;

            // Add colors (cycle through colors if more doctors than predefined colors)
            $colorIndex = $index % count($colors);
            $chartData['backgroundColors'][] = $colors[$colorIndex]['background'];
            $chartData['borderColors'][] = $colors[$colorIndex]['border'];
        }

        return $chartData;
    }

    public function token()
    {
        $tokens = DB::table('doctor_s_ls as dsl')
            ->join('patient_appointment_requests as par', 'dsl.appointment_id', '=', 'par.id')
            ->join('doctor_profiles as dp', 'dsl.doctor_profile_id', '=', 'dp.id')
            ->join('contacts as c', 'c.id', '=', 'dsl.patient_contact_id')
            ->where('par.remarks', 'confirmed') // confirmed but not prescribed
            ->where('par.remarks', '!=', 'prescribed')
            ->select(
                'dsl.id as dsl_id',
                'dp.id as doctor_id',
                'dp.first_name as doctor_first_name',
                'dp.last_name as doctor_last_name',
                'dp.room as room',
                'dsl.sl_no as token_number',
                'dsl.sl_without_prefix as token_without_prefix',
                'dp.serial_prefix as serial_prefix',
                'dp.prefix_color as prefix_color',
                'c.name as patient_name',
                'dsl.status as token_status',
                'dsl.call_status as call_status',
                'dsl.called_at as called_at'
            )
            ->orderBy('dp.first_name', 'asc')
            ->get();

        $groupedTokens = [];
        $lastCall = null;

        foreach ($tokens as $token) {
            $doctorId = $token->doctor_id;
            $doctorName = $token->doctor_first_name . ' ' . $token->doctor_last_name;
            $tokenKey = $token->serial_prefix . '-' . str_pad($token->token_without_prefix, 3, '0', STR_PAD_LEFT);

            if (!isset($groupedTokens[$doctorId])) {
                $groupedTokens[$doctorId] = [
                    'doctor_name' => $doctorName,
                    'room' => $token->room,
                    'serial_prefix' => $token->serial_prefix,
                    'current' => null,
                    'waiting' => [],
                    'skipped' => [],
                ];
            }

            if ($token->token_status === 'served') {
                $groupedTokens[$doctorId]['current'] = $tokenKey;

                // Track the latest by comparing dsl.id
                if (!$lastCall || $token->dsl_id > $lastCall['dsl_id']) {
                    $lastCall = [
                        'token' => $tokenKey,
                        'doctor' => $doctorName,
                        'counter' => $token->room,
                        'dsl_id' => $token->dsl_id,
                    ];
                }
            } elseif ($token->token_status === 'skipped') {
                array_unshift($groupedTokens[$doctorId]['skipped'], $tokenKey);
                $groupedTokens[$doctorId]['skipped'] = array_slice($groupedTokens[$doctorId]['skipped'], 0, 4);
            } else {
                $groupedTokens[$doctorId]['waiting'][] = $tokenKey;
            }
        }

        // Limit waiting tokens to 3
        foreach ($groupedTokens as &$doctor) {
            $doctor['waiting'] = array_slice($doctor['waiting'], 0, 3);
        }

        // Clean up last call
        if ($lastCall) {
            unset($lastCall['dsl_id']);
        }

        if (request()->ajax()) {
            return view('clinic::dashboard._token_list', compact('groupedTokens', 'lastCall'));
        }

        return view('clinic::dashboard.token', compact('groupedTokens', 'lastCall'));
    }

    


}
