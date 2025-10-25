<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\BusinessUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Media;
use App\{Product, Variation, Category, Brands, TransactionPayment};
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionSellLine;
use App\TypesOfService;
use App\User;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\{DB, Log};
use Illuminate\Support\Carbon;
use Modules\Clinic\Entities\{PatientProfile, Prescription};
use Modules\Clinic\Utils\{ClinicSellUtil, AppointmentUtil};
use Yajra\DataTables\DataTables;
class PatientPayForController extends Controller
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
    protected $appointmentUtil;
    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil, ClinicSellUtil $clinicSellUtil, AppointmentUtil $appointmentUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->clinicSellUtil = $clinicSellUtil;
        $this->appointmentUtil = $appointmentUtil;

        $this->dummyPaymentLine = ['method' => '', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
            'is_return' => 0, 'transaction_no' => '', ];

        $this->shipping_status_colors = [
            'ordered' => 'bg-yellow',
            'packed' => 'bg-info',
            'shipped' => 'bg-navy',
            'delivered' => 'bg-green',
            'cancelled' => 'bg-red',
        ];
    }

    public function index()
    {
        return view('clinic::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function PatientSell($id){
        // if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access') && !auth()->user()->can('view_own_sell_only')) {
        //     abort(403, 'Unauthorized action.');
        // }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
                            ->pluck('name', 'id');
        $query = Transaction::where('business_id', $business_id)
                    ->where('id', $id)
                    ->with(['contact', 'delivery_person_user', 'sell_lines' => function ($q) {
                        $q->whereNull('parent_sell_line_id');
                    }, 'sell_lines.product', 'sell_lines.product.unit', 'sell_lines.product.second_unit', 'sell_lines.variations', 'sell_lines.variations.product_variation', 'payment_lines', 'sell_lines.modifiers', 'sell_lines.lot_details', 'tax', 'sell_lines.sub_unit', 'table', 'service_staff', 'sell_lines.service_staff', 'types_of_service', 'sell_lines.warranties', 'media']);

        if (! auth()->user()->can('sell.view') && ! auth()->user()->can('direct_sell.access') && auth()->user()->can('view_own_sell_only')) {
            $query->where('transactions.created_by', request()->session()->get('user.id'));
        }

        $sell = $query->firstOrFail();

        $activities = Activity::forSubject($sell)
           ->with(['causer', 'subject'])
           ->latest()
           ->get();

        $line_taxes = [];
        foreach ($sell->sell_lines as $key => $value) {
            if (! empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }

            if (! empty($taxes[$value->tax_id])) {
                if (isset($line_taxes[$taxes[$value->tax_id]])) {
                    $line_taxes[$taxes[$value->tax_id]] += ($value->item_tax * $value->quantity);
                } else {
                    $line_taxes[$taxes[$value->tax_id]] = ($value->item_tax * $value->quantity);
                }
            }
        }

        $payment_types = $this->transactionUtil->payment_types($sell->location_id, true);
        $order_taxes = [];
        if (! empty($sell->tax)) {
            if ($sell->tax->is_tax_group) {
                $order_taxes = $this->transactionUtil->sumGroupTaxDetails($this->transactionUtil->groupTaxDetails($sell->tax, $sell->tax_amount));
            } else {
                $order_taxes[$sell->tax->name] = $sell->tax_amount;
            }
        }

        $business_details = $this->businessUtil->getDetails($business_id);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
        $shipping_statuses = $this->transactionUtil->shipping_statuses();
        $shipping_status_colors = $this->shipping_status_colors;
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = ! empty($common_settings['enable_product_warranty']) ? true : false;

        $statuses = Transaction::sell_statuses();

        if ($sell->type == 'sales_order') {
            $sales_order_statuses = Transaction::sales_order_statuses(true);
            $statuses = array_merge($statuses, $sales_order_statuses);
        }
        $status_color_in_activity = Transaction::sales_order_statuses();
        $sales_orders = $sell->salesOrders();

        return view('clinic::patient.patients.payfor.sell')
            ->with(compact(
                'taxes',
                'sell',
                'payment_types',
                'order_taxes',
                'pos_settings',
                'shipping_statuses',
                'shipping_status_colors',
                'is_warranty_enabled',
                'activities',
                'statuses',
                'status_color_in_activity',
                'sales_orders',
                'line_taxes'
            ));
    }
    public function patientTransactions($id){
         $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $sub_type = ['therapy', 'test', 'ipd', 'consultation'];
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $sells = $this->clinicSellUtil->getListSells($business_id, 'sell', $sub_type);
            if(!empty(request()->input('service_type'))){
                $sells->where('transactions.sub_type', request()->input('service_type'));
            }
            $sells->where('transactions.contact_id', $id);
            $sells->groupBy('transactions.id');
            $sales_order_statuses = Transaction::sales_order_statuses();
            $datatable = Datatables::of($sells)
                ->removeColumn('id')
                ->editColumn(
                    'final_total',
                    '<span class="final-total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn(
                    'tax_amount',
                    '<span class="total-tax" data-orig-value="{{$tax_amount}}">@format_currency($tax_amount)</span>'
                )
                ->editColumn(
                    'total_paid',
                    '<span class="total-paid" data-orig-value="{{$total_paid}}">@format_currency($total_paid)</span>'
                )
                ->editColumn(
                    'total_before_tax',
                    '<span class="total_before_tax" data-orig-value="{{$total_before_tax}}">@format_currency($total_before_tax)</span>'
                )
                ->editColumn(
                    'discount_amount',
                    function ($row) {
                        $discount = !empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (!empty($discount) && $row->discount_type == 'percentage') {
                            $discount = $row->total_before_tax * ($discount / 100);
                        }

                        return '<span class="total-discount" data-orig-value="' . $discount . '">' . $this->transactionUtil->num_f($discount, true) . '</span>';
                    }
                )->editColumn(
                    'line_discount_amount',
                    function ($row) {
                        return '<span class="total-discount" data-orig-value="' . $row->total_line_discount . '">' . $this->transactionUtil->num_f($row->total_line_discount, true) . '</span>';
                    }

                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return ucfirst($payment_status);
                    }     
                )
                ->editColumn(
                    'types_of_service_name',
                    '<span class="service-type-label" data-orig-value="{{$types_of_service_name}}" data-status-name="{{$types_of_service_name}}">{{$types_of_service_name}}</span>'
                )
                ->addColumn('total_remaining', function ($row) {
                    $total_remaining = $row->final_total - $row->total_paid;
                    $total_remaining_html = '<span class="payment_due" data-orig-value="' . $total_remaining . '">' . $this->transactionUtil->num_f($total_remaining, true) . '</span>';

                    return $total_remaining_html;
                })
                
                ->editColumn('invoice_no', function ($row){
                    $Edited = Transaction::where('id', $row->id)->first();
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (!empty($row->return_exists)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.some_qty_returned_from_sell') . '"><i class="fas fa-undo"></i></small>';
                    }
                    // Check for 'edited' activities related to the transaction and append the edit icon if found
                    $activities = Activity::forSubject($Edited)
                        ->where('description', '=', 'edited')
                        ->get();
                    // Check if the activities collection is not empty
                    if ($activities->isNotEmpty()) {
                        $invoice_no .= ' &nbsp;<small class="label bg-blue label-round no-print" title="' . __('Edited') . '"><i class="fas fa-edit"></i></small>';
                    }
                    if (!empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (!empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }                            
                    return $invoice_no;
                })
                
                ->addColumn('conatct_name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br> @endif {{$name}}')
                ->editColumn('total_items', '{{@format_quantity($total_items)}}')
                ->filterColumn('conatct_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('contacts.name', 'like', "%{$keyword}%")
                            ->orWhere('contacts.supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('payment_methods', function ($row) use ($payment_types) {
                    $methods = array_unique($row->payment_lines->pluck('method')->toArray());
                    $count = count($methods);
                    $payment_method = '';
                    if ($count == 1) {
                        $payment_method = $payment_types[$methods[0]] ?? '';
                    } elseif ($count > 1) {
                        $payment_method = __('lang_v1.checkout_multi_pay');
                    }

                    $html = !empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

                    return $html;
                })
                ->editColumn('status', function ($row) use ($sales_order_statuses) {
                    $status = '';

                    if ($row->type == 'sales_order') {
                        
                        $status = '<span class="label ' . $sales_order_statuses[$row->status]['class'] . '" >' . $sales_order_statuses[$row->status]['label'] . '</span>';
                        
                    }

                    return $status;
                })
                ->editColumn(
                    'sub_type',
                    function ($row) {
                        return $row->sub_type ? ucfirst($row->sub_type) : '';
                    }
                )
                ->setRowAttr([
                    'class' => function ($row) {
                        return 'patient-payment-row';
                    },
                    'data-href' => function ($row) {
                        return action([\Modules\Clinic\Http\Controllers\PatientPayForController::class, 'show'], [$row->id]);
                    }
                ])
                ->editColumn('so_qty_remaining', '{{@format_quantity($so_qty_remaining)}}');

            $rawColumns = ['line_discount_amount', 'final_total',  'total_paid', 'total_remaining', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'types_of_service_name', 'payment_methods', 'conatct_name', 'status', 'sub_type'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }
    }
    public function PatientPurchase(){

    }
    public function PatientPurchaseReturn(){

    }
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
        $transaction = Transaction::findOrFail($id);
        $payments = TransactionPayment::where('transaction_id', $transaction->id)->get();

        return response()->json([
                'success' => true,
                'html' => view('clinic::patient.patients.payfor.payment_details', compact('transaction', 'payments'))->render()
            ]);
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
    public function deleteMedia($media_id)
    {
        if (!auth()->user()->can('product.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                Media::deleteMedia($business_id, $media_id);

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.file_deleted_successfully'),
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }
    public function patientAppointmentDetails($id)
    {
            $appointments = $this->appointmentUtil->getConfirmAppointmentQuery();
            $appointments->where('appointment.patient_contact_id', $id);
            return datatables()->of($appointments)
                ->addColumn('action', function ($row) {
                    $html = '';

                    if (auth()->user()->can('intake.form.show.patient.profile')) {
                        $intakeform = action([\Modules\Clinic\Http\Controllers\Survey\IntakeFormController::class, 'show'], [$row->customerId]);

                        $html .= '<a href="#" data-href="' . $intakeform . '" class="btn btn-modal btn-primary btn-xs" data-container=".view_modal_intake">
                                    <i class="fa fa-file-alt" aria-hidden="true"></i> 
                                </a>';
                    }
                    
                    $prescription = Prescription::where('appointment_id', $row->appId)->latest()->first();

                    if ($prescription && auth()->user()->can('prescription.show.patient.profile')) {
                        
                        $prescriptionView = action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'show'], [$prescription->id]);

                        $html .= '<a href="#" data-href="' . $prescriptionView . '" class="btn btn-success btn-xs view_prescription" style="margin-left: 5px;">
                                 <i class="fa fa-eye" aria-hidden="true"></i>
                               </a>';

                    } else {
                        $html .= '';
                    }

                    return $html;
                })
                ->filterColumn('patient_name', function ($query, $keyword) {
                    $query->whereRaw("LOWER(CONCAT(pp.first_name)) LIKE ?", ["%" . strtolower($keyword) . "%"]);
                })
                
                ->filterColumn('doctor_name', function ($query, $keyword) {
                    $query->whereRaw("LOWER(CONCAT(dp.first_name)) LIKE ?", ["%" . strtolower($keyword) . "%"]);
                })
                ->addColumn('doctor_name', function ($row) {
                    return '<p style=""><b style="background-color: ' . $row->prefixColor . '14; padding: 5px; border-radius: 5px; color: ' . $row->prefixColor . ';">
                                <i class="fas fa-stethoscope"></i> ' . $row->doctor_first_name . ' ' . $row->doctor_last_name . '</b>
                            </p>';
                })


                ->addColumn('serial_number', function ($row) {
                    return '<p style="padding: 3px; border-radius: 5px;">
                    <span style="background-color: ' . $row->prefixColor . '14; padding: 3px; border-radius: 5px;">
                    <b style="color: ' . $row->prefixColor . ';">' . ($row->serial_number ?? 'N/A') . '</b>
                    </span>
                    </p>';
                })
                ->addColumn('status', function ($row) {
                    $status = ucfirst(trim($row->status));
                    $buttonColor = '';
                    switch (strtolower($status)) {
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
                    $editBtn = '';
                    return '<button class="' . $buttonColor . ' btn-xs">' . $status . '</button>'.$editBtn;
                })
                ->filterColumn('appointment_date', function ($query, $keyword) {
                    $query->where('appointment.request_date', 'like', "%{$keyword}%");
                })
                ->addColumn('waiting_time', function ($row) {
                    $currentTime = '';
                    $buttonClass = '';
                    $prescription = Prescription::where('appointment_id', $row->appId)->latest()->first();
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

                    $confirmTime = Carbon::parse($row->waiting_time_start);
                    $interval = $currentTime->diff($confirmTime);
                    $isLate = $interval->invert == 1;
                    $totalMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;

                    if ($isLate) {
                        if ($totalMinutes >= 0 && $totalMinutes <= 20) {
                            $buttonClass = 'btn-yellow';
                        } elseif ($totalMinutes >= 21 && $totalMinutes <= 60) {
                            $buttonClass = 'btn-orange';
                        } elseif ($totalMinutes > 60 && $totalMinutes < 720) {
                            $buttonClass = 'btn-danger';
                        } else if ($totalMinutes >= 720) {
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

                        if ($totalMinutes <= 720) {
                            if ($totalMinutes < 60) {
                                $waitingTimeMessage .= sprintf("%02d mins", $minutes);
                            } else if ($totalMinutes >= 60) {
                                $decimalHours = $hours + ($minutes / 60);
                                $waitingTimeMessage .= sprintf("%.2f hrs", $decimalHours);
                            }
                        } else if ($totalMinutes >= 720) {
                            $is_interval = false;
                            $waitingTimeMessage = '';
                        }
                    } else {
                        $waitingTimeMessage = "On Time";
                        $buttonClass = 'btn-success';
                    }
                    if ($row->status == 'expired') {
                        $waitingTimeMessage = '';
                        $buttonClass = 'btn btn-red';
                    }
                    return '<div class="waiting-time" data-start-time="' . $confirmTime->toDateTimeString() . '" data-is-interval="' . ($is_interval ? 'true' : 'false') . '">
                                <i class="fas fa-clock"></i> ' . $start . ' - ' . $end . '
                                </br> <button class="btn btn-xs ' . $buttonClass . '" style="margin: 1px;">' . $waitingTimeMessage . '</button>
                            </div>';
                })
                ->addColumn('created_updated_same', function ($row) {
                    return $row->created_updated_same;
                })                
                ->rawColumns(['action', 'waiting_time', 'doctor_name', 'status', 'serial_number'])
                ->make(true);
        }
    
}
