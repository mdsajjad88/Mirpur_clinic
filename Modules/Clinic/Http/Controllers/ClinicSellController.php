<?php

namespace Modules\Clinic\Http\Controllers;

use App\TransactionPayment;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\BusinessLocation;
use App\Transaction;
use App\InvoiceScheme;
use App\User;
use App\Category;
use App\Brands;
use App\Product;
use App\Variation;
use App\SellingPriceGroup;
use App\TypesOfService;
use App\Account;
use App\Business;
use App\CashRegister;
use App\CustomerGroup;
use App\Contact;
use App\Media;
use App\Division;
use App\TaxRate;
use App\InvoiceLayout;
use Illuminate\Support\Facades\Session;
use Spatie\Activitylog\Models\Activity;
use App\TransactionSellLine;
use App\Warranty;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Modules\Clinic\Utils\ClinicSellUtil;
use Modules\Clinic\Entities\DoctorProfile;
use App\Utils\CashRegisterUtil;
use App\Utils\NotificationUtil;
use App\ProductWaitlist;
use App\Events\SellCreatedOrModified;
use App\SessionDetail;
use Modules\Clinic\Entities\Disease;
use Modules\Clinic\Entities\Problem;
use Illuminate\Support\Carbon;
use Modules\Clinic\Entities\PatientAppointmentRequ;

class ClinicSellController extends Controller
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

    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil, ClinicSellUtil $clinicSellUtil, CashRegisterUtil $cashRegisterUtil, NotificationUtil $notificationUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->clinicSellUtil = $clinicSellUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->notificationUtil = $notificationUtil;
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
        $is_admin = $this->businessUtil->is_admin(auth()->user());
        if (!$is_admin && !auth()->user()->hasAnyPermission(['clinic.sell.view', 'clinic.sell.create'])) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $clinic_location = $common_settings['clinic_location'] ?? null;

        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        if (request()->ajax()) {
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $shipping_statuses = $this->transactionUtil->shipping_statuses();

            $sale_type = !empty(request()->input('sale_type')) ? request()->input('sale_type') : 'sell';
            $all_sub = ['therapy', 'test', 'ipd', 'consultation'];
            $sub_type = !empty(request()->input('sub_type')) ? request()->input('sub_type') : $all_sub;


            $sells = $this->clinicSellUtil->getListSells($business_id, $sale_type, $sub_type);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }
            $sells->where('transactions.location_id', $clinic_location);

            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }


            if (request()->has('payment_method') && !empty(request()->payment_method)) {
                $sells->where('tp.method', 'like', '%' . request()->payment_method . '%');
            }

            // Apply discount type filter
            if (!empty(request()->input('discount_type'))) {
                $discount_type = request()->input('discount_type');
                if ($discount_type == 'campaign') {
                    $sells->where('tsl.line_discount_amount', '>', 0);
                } elseif ($discount_type == 'special') {
                    $sells->where('transactions.discount_amount', '>', 0);
                } elseif ($discount_type == 'group') {
                }
            }
            $partial_permissions = ['view_own_sell_only', 'view_commission_agent_sell', 'access_own_shipping', 'access_commission_agent_shipping'];
            if (!auth()->user()->can('direct_sell.view')) {
                $sells->where(function ($q) {
                    if (auth()->user()->hasAnyPermission(['view_own_sell_only', 'access_own_shipping'])) {
                        $q->where('transactions.created_by', request()->session()->get('user.id'));
                    }

                    if (auth()->user()->hasAnyPermission(['view_commission_agent_sell', 'access_commission_agent_shipping'])) {
                        $q->orWhere('transactions.commission_agent', request()->session()->get('user.id'));
                    }
                });
            }

            $only_shipments = request()->only_shipments == 'true' ? true : false;
            if ($only_shipments) {
                $sells->whereNotNull('transactions.shipping_status');

                if (auth()->user()->hasAnyPermission(['access_pending_shipments_only'])) {
                    $sells->where('transactions.shipping_status', '!=', 'delivered');
                }
            }

            if (!$is_admin && !$only_shipments && $sale_type != 'sales_order') {
                $payment_status_arr = [];
                if (auth()->user()->can('view_paid_sells_only')) {
                    $payment_status_arr[] = 'paid';
                }

                if (auth()->user()->can('view_due_sells_only')) {
                    $payment_status_arr[] = 'due';
                }

                if (auth()->user()->can('view_partial_sells_only')) {
                    $payment_status_arr[] = 'partial';
                }

                if (empty($payment_status_arr)) {
                    if (auth()->user()->can('view_overdue_sells_only')) {
                        $sells->OverDue();
                    }
                } else {
                    if (auth()->user()->can('view_overdue_sells_only')) {
                        $sells->where(function ($q) use ($payment_status_arr) {
                            $q->whereIn('transactions.payment_status', $payment_status_arr)
                                ->orWhere(function ($qr) {
                                    $qr->OverDue();
                                });
                        });
                    } else {
                        $sells->whereIn('transactions.payment_status', $payment_status_arr);
                    }
                }
            }
            if (!empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
                $sells->where('transactions.payment_status', request()->input('payment_status'));
            } elseif (request()->input('payment_status') == 'overdue') {
                $sells->whereIn('transactions.payment_status', ['due', 'partial'])
                    ->whereNotNull('transactions.pay_term_number')
                    ->whereNotNull('transactions.pay_term_type')
                    ->whereRaw("IF(transactions.pay_term_type='days', DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number DAY) < CURDATE(), DATE_ADD(transactions.transaction_date, INTERVAL transactions.pay_term_number MONTH) < CURDATE())");
            }

            //Add condition for location,used in sales representative expense report
            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (!empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.rp_earned')
                        ->orWhere('transactions.rp_redeemed', '>', 0);
                });
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                    ->whereDate('transactions.transaction_date', '<=', $end);
            }

            //Check is_direct sell
            if (request()->has('is_direct_sale')) {
                $is_direct_sale = request()->is_direct_sale;
                if ($is_direct_sale == 0) {
                    $sells->where('transactions.is_direct_sale', 0);
                    $sells->whereNull('transactions.sub_type');
                }
            }

            //Add condition for commission_agent,used in sales representative sales with commission report
            if (request()->has('commission_agent')) {
                $commission_agent = request()->get('commission_agent');
                if (!empty($commission_agent)) {
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if (!empty(request()->input('source'))) {
                //only exception for woocommerce
                if (request()->input('source') == 'woocommerce') {
                    $sells->whereNotNull('transactions.woocommerce_order_id');
                } else {
                    $sells->where('transactions.source', request()->input('source'));
                }
            }

            if ($is_crm) {
                $sells->addSelect('transactions.crm_is_order_request');

                if (request()->has('crm_is_order_request')) {
                    $sells->where('transactions.crm_is_order_request', 1);
                }
            }

            if (request()->only_subscriptions) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.recur_parent_id')
                        ->orWhere('transactions.is_recurring', 1);
                });
            }
            if (request()->delivery_sales) {
                $sells->whereNotNull('transactions.shipping_status');
            }

            if (!empty(request()->list_for) && request()->list_for == 'service_staff_report') {
                $sells->whereNotNull('transactions.res_waiter_id');
            }

            if (!empty(request()->res_waiter_id)) {
                $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
            }

            if (!empty(request()->input('sub_type'))) {
                $sub_type = ['sub_type' => request()->input('sub_type')];

                $sells->where('transactions.sub_type', $sub_type['sub_type']);
            }

            if (!empty(request()->input('created_by'))) {
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (!empty(request()->input('status'))) {
                $sells->where('transactions.status', request()->input('status'));
            }

            if (!empty(request()->input('sales_cmsn_agnt'))) {
                $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
            }

            if (!empty(request()->input('service_staffs'))) {
                $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
            }

            $only_pending_shipments = request()->only_pending_shipments == 'true' ? true : false;
            if ($only_pending_shipments) {
                $sells->where('transactions.shipping_status', '!=', 'delivered')
                    ->whereNotNull('transactions.shipping_status');
                $only_shipments = true;
            }

            if (!empty(request()->input('shipping_status'))) {
                $sells->where('transactions.shipping_status', request()->input('shipping_status'));
            }

            if (!empty(request()->input('for_dashboard_sales_order'))) {
                $sells->whereIn('transactions.status', ['partial', 'ordered'])
                    ->orHavingRaw('so_qty_remaining > 0');
            }

            if ($sale_type == 'sales_order') {
                if (!auth()->user()->can('so.view_all') && auth()->user()->can('so.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            }

            if (!empty(request()->input('delivery_person'))) {
                $sells->where('transactions.delivery_person', request()->input('delivery_person'));
            }
            $sells->groupBy('transactions.id');

            if (!empty(request()->suspended)) {
                $transaction_sub_type = request()->get('transaction_sub_type');
                if (!empty($transaction_sub_type)) {
                    $sells->where('transactions.sub_type', $transaction_sub_type);
                } else {
                    $sells->where('transactions.sub_type', null);
                }

                $with = ['sell_lines'];

                if ($is_tables_enabled) {
                    $with[] = 'table';
                }

                if ($is_service_staff_enabled) {
                    $with[] = 'service_staff';
                }

                $sales = $sells->where('transactions.is_suspend', 1)
                    ->with($with)
                    ->addSelect('transactions.is_suspend', 'transactions.res_table_id', 'transactions.res_waiter_id', 'transactions.additional_notes')
                    ->get();

                return view('clinic::sell.partials.suspended_sales_modal')->with(compact('sales', 'is_tables_enabled', 'is_service_staff_enabled', 'transaction_sub_type'));
            }

            $with[] = 'payment_lines';
            if (!empty($with)) {
                $sells->with($with);
            }

            //$business_details = $this->businessUtil->getDetails($business_id);
            if ($this->businessUtil->isModuleEnabled('subscription')) {
                $sells->addSelect('transactions.is_recurring', 'transactions.recur_parent_id');
            }
            $sales_order_statuses = Transaction::sales_order_statuses();
            $datatable = Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) use ($only_shipments, $is_admin, $sale_type) {
                        $html = '<div class="btn-group">
                                    <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                        data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                        </span>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                        if (auth()->user()->can('clinic.sell.view')) {
                            $html .= '<li><a href="#" data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('messages.view') . '</a></li>';
                        }
                        if (!$only_shipments) {
                            // if ($row->is_direct_sale == 0) {
                            //     if (auth()->user()->can('clinic.sell.edit')) {
                            //         $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'edit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                            //     }
                            // } elseif ($row->type == 'sales_order') {
                            //     if (auth()->user()->can('clinic.sell.edit')) {
                            //         $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'edit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                            //     }
                            // } else {
                            if (auth()->user()->can('clinic.sell.edit')) {
                                $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'edit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                            }
                            // }

                            $delete_link = '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'destroy'], [$row->id]) . '" class="delete-sale"><i class="fas fa-trash"></i> ' . __('messages.delete') . '</a></li>';
                            if ($row->is_direct_sale == 0) {
                                if (auth()->user()->can('clinic.sell.delete')) {
                                    $html .= $delete_link;
                                }
                            } elseif ($row->type == 'sales_order') {
                                if (auth()->user()->can('clinic.sell.delete')) {
                                    $html .= $delete_link;
                                }
                            } else {
                                if (auth()->user()->can('clinic.sell.delete')) {
                                    $html .= $delete_link;
                                }
                            }
                        }

                        if (config('constants.enable_download_pdf') && auth()->user()->can('clinic.sell.print_invoice') && $sale_type != 'sales_order') {
                            $html .= '<li><a href="' . route('sell.downloadPdf', [$row->id]) . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.download_pdf') . '</a></li>';

                            if (!empty($row->shipping_status)) {
                                $html .= '<li><a href="' . route('packing.downloadPdf', [$row->id]) . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.download_paking_pdf') . '</a></li>';
                            }
                        }

                        if (auth()->user()->can('clinic.sell.view')) {
                            if (!empty($row->document)) {
                                $document_name = !empty(explode('_', $row->document, 2)[1]) ? explode('_', $row->document, 2)[1] : $row->document;
                                $html .= '<li><a href="' . url('uploads/documents/' . $row->document) . '" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __('purchase.download_document') . '</a></li>';
                                if (isFileImage($document_name)) {
                                    $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) . '" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>' . __('lang_v1.view_document') . '</a></li>';
                                }
                            }
                        }

                        if ($row->type == 'sell' || $row->sub_type == 'test' || $row->sub_type == 'therapy' || $row->sub_type == 'ipd' || $row->sub_type == 'consultation') {
                            if (auth()->user()->can('clinic.sell.print_invoice')) {
                                $html .= '<li><a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.print_invoice') . '</a></li>';
                            }
                            $html .= '<li class="divider"></li>';
                            if (!$only_shipments) {
                                if (auth()->user()->can('clinic.sell.add_payment')) {
                                    if ($row->payment_status != 'paid') {
                                        $html .= '<li><a data-is_direct_sale="' . $row->is_direct_sale . '" href="' . action([\Modules\Clinic\Http\Controllers\ClinicTransactionController::class, 'addPayment'], [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.add_payment') . '</a></li>';
                                    }

                                    $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicTransactionController::class, 'show'], [$row->id]) . '" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.view_payments') . '</a></li>';
                                }
                                if (auth()->user()->can('access_sell_return') || auth()->user()->can('access_own_sell_return')) {
                                    $html .= '<li><a class="sell-return-btn" data-is_direct_sale="' . $row->is_direct_sale . '" href="' . action([\Modules\Clinic\Http\Controllers\BillReturnController::class, 'add'], [$row->id]) . '"><i class="fas fa-undo"></i> ' . __('clinic::lang.bill_refund') . '</a></li>';
                                }
                                if (auth()->user()->can('clinic.sell.create')) {
                                    $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicPosController::class, 'showInvoiceUrl'], [$row->id]) . '" class="view_invoice_url"><i class="fas fa-eye"></i> ' . __('lang_v1.view_invoice_url') . '</a></li>';
                                }
                            }
                            if (auth()->user()->can('new_sale_notification')) {
                                $html .= '<li><a href="#" data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicNotificationController::class, 'getTemplate'], ['transaction_id' => $row->id, 'template_for' => 'new_sale']) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __('lang_v1.new_sale_notification') . '</a></li>';
                            }
                        } else {
                            $html .= '<li><a href="#" data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'viewMedia'], ['model_id' => $row->id, 'model_type' => \App\Transaction::class, 'model_media_type' => 'shipping_document']) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-paperclip" aria-hidden="true"></i>' . __('lang_v1.shipping_documents') . '</a></li>';
                        }
                        $html .= '</ul></div>';
                        return $html;
                    }
                )
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

                )->editColumn(
                    'sub_type',
                    function ($row) {
                        return $row->sub_type ?? '';
                    }
                )
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('clinic::appointment.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id]);
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
                ->addColumn('return_due', function ($row) {
                    $return_due_html = '';
                    if (!empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $return_due_html .= '<a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="sell_return_due" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }

                    return $return_due_html;
                })
                ->editColumn('invoice_no', function ($row) use ($is_crm) {
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

                    if (!empty($row->is_export)) {
                        $invoice_no .= '</br><small class="label label-default no-print" title="' . __('lang_v1.export') . '">' . __('lang_v1.export') . '</small>';
                    }

                    if ($is_crm && !empty($row->crm_is_order_request)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-yellow label-round no-print" title="' . __('crm::lang.order_request') . '"><i class="fas fa-tasks"></i></small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('mobile', function ($row) {
                    $phoneId = 'phone_' . $row->id;
                    $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';

                    if (auth()->user()->can('patient.phone_number')) {
                        return $row->mobile;
                    } else {
                        return $phoneIcon .
                            ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->mobile . '</span>';
                    }
                })
                ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
                    $status_color = !empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                    $status = !empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action([\App\Http\Controllers\SellController::class, 'editShipping'], [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color . '">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

                    return $status;
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
                ->editColumn('status', function ($row) use ($sales_order_statuses, $is_admin) {
                    $status = '';

                    if ($row->type == 'sales_order') {
                        if ($is_admin && $row->status != 'completed') {
                            $status = '<span class="edit-so-status label ' . $sales_order_statuses[$row->status]['class'] . '" data-href="' . action([\App\Http\Controllers\SalesOrderController::class, 'getEditSalesOrderStatus'], ['id' => $row->id]) . '">' . $sales_order_statuses[$row->status]['label'] . '</span>';
                        } else {
                            $status = '<span class="label ' . $sales_order_statuses[$row->status]['class'] . '" >' . $sales_order_statuses[$row->status]['label'] . '</span>';
                        }
                    }

                    return $status;
                })
                ->editColumn('so_qty_remaining', '{{@format_quantity($so_qty_remaining)}}')
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('sell.view') || auth()->user()->can('view_own_sell_only')) {
                            return action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->id]);
                        } else {
                            return '';
                        }
                    },
                ]);

            $rawColumns = ['line_discount_amount', 'final_total', 'action', 'total_paid', 'total_remaining', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status', 'types_of_service_name', 'payment_methods', 'return_due', 'conatct_name', 'status', 'mobile'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        //Commission agent filter
        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        //Service staff filter
        $service_staffs = null;
        if ($this->productUtil->isModuleEnabled('service_staff')) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $sources = $this->transactionUtil->getSources($business_id);
        if ($is_woocommerce) {
            $sources['woocommerce'] = 'Woocommerce';
        }
        $payment_types = [
            'cash' => __('lang_v1.cash'),
            'card' => __('lang_v1.card'),
            'custom_pay_1' => __('bKash'),
        ];
        $RemaininInLast = Transaction::where('payment_status', 'due')->whereIn('sub_type', ['test', 'therapy', 'ipd', 'consultation'])
            ->whereDate('transactions.transaction_date', '>=', now()->subDays(30))
            ->count();

        $sub_type = ['therapy', 'test', 'ipd', 'consultation'];

        $brands = Brands::forDropdownWithSubType($business_id, false, false, false, false, $sub_type);
        $categories = Category::forDropdownClinic($business_id, $sub_type);

        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->first();

        return view('clinic::sell.index')
            ->with(compact('business_locations', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses', 'sources', 'payment_types', 'RemaininInLast', 'brands', 'categories', 'register'));
    }


    public function sessionDetailsReport(Request $request)
    {
        // Check permissions
        if (!auth()->user()->can('clinic.sell.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $session_details = Transaction::where('transactions.type', 'sell')
            ->where('transactions.status', 'final')
            ->where('transactions.is_direct_sale', 0)
            ->where('transactions.sub_type', 'therapy')
            ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
            ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftJoin('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
            ->leftJoin('variations AS v', 'TSL.variation_id', '=', 'v.id')
            ->leftJoin('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
            ->leftJoin('products AS p', 'v.product_id', '=', 'p.id')
            ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
            ->leftjoin('categories as cat', 'p.category_id', '=', 'cat.id')
            ->leftJoin('session_details as sd', function ($join) {
                $join->on('transactions.id', '=', 'sd.transaction_id')
                    ->on('TSL.product_id', '=', 'sd.product_id')
                    ->on('TSL.variation_id', '=', 'sd.variation_id');
            })
            ->leftJoin('transaction_sell_lines AS child_TSL', function ($join) {
                $join->on('TSL.id', '=', 'child_TSL.parent_sell_line_id')
                    ->where('child_TSL.children_type', 'modifier'); // Ensuring only modifiers are joined
            })
            ->leftJoin('variations AS v_child', 'child_TSL.variation_id', '=', 'v_child.id') // **Get variation name for modifiers**
            ->where('TSL.children_type', '!=', 'combo')
            ->whereNull('TSL.parent_sell_line_id') // **Only parent TSL products**
            ->groupBy('TSL.id') // **Group by parent TSL ID**
            ->select(
                'transactions.id',
                'transactions.transaction_date',
                'transactions.type',
                'transactions.is_direct_sale',
                'transactions.invoice_no',
                'transactions.additional_notes',
                'transactions.payment_status',
                'transactions.discount_amount',
                'transactions.discount_type',
                'transactions.final_total',
                'u.id as user_id',
                'contacts.name as customer_name',
                'contacts.mobile',
                'contacts.contact_id',
                'contacts.supplier_business_name',
                'contacts.customer_group_id',
                'p.name as product_name',
                'p.id as product_id',
                'p.type as product_type',
                'v.name as variation_name',
                'v.id as variation_id',
                'pv.name as product_variation_name',
                'v.sub_sku as sku',
                'v_child.id as child_variation_id',
                'cat.id as cat_id',
                'sd.session_no',
                'TSL.sell_line_note',
                'TSL.line_discount_type',
                'TSL.line_discount_amount',
                'TSL.unit_price',
                'TSL.quantity',
                'TSL.quantity_returned',
                'tp.method',
                'tp.amount',
                'tp.created_by',
                DB::raw('SUM(child_TSL.unit_price_inc_tax * child_TSL.quantity) as child_total_amount'),
                DB::raw('TSL.unit_price_inc_tax * TSL.quantity as total_amount'),
                DB::raw('SUM(
                        CASE 
                            WHEN TSL.line_discount_type = "fixed" THEN ROUND(TSL.line_discount_amount, 2)
                            WHEN TSL.line_discount_type = "percentage" THEN ROUND((TSL.line_discount_amount / 100) * (TSL.unit_price * TSL.quantity), 2)
                            ELSE 0
                        END
                    ) as total_line_discount'),
                DB::raw('GROUP_CONCAT(DISTINCT 
                        CASE 
                            WHEN child_TSL.children_type = "modifier" THEN v_child.name 
                            ELSE NULL 
                        END 
                        SEPARATOR ", "
                    ) as modifier_names'),
                DB::raw('SUM(
                        CASE 
                            WHEN transactions.discount_type = "fixed" THEN ROUND(transactions.discount_amount, 2)
                            WHEN transactions.discount_type = "percentage" THEN ROUND((transactions.discount_amount / 100) * transactions.final_total, 2)
                            ELSE 0
                        END
                    ) as total_discount'),
                DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
            )
            ->orderBy('transactions.transaction_date', 'desc')
            ->groupBy('transactions.id');

        // Apply filters
        // if (!empty($request->input('category_id[]'))) {
        //     $session_details->whereIn('session_details.category_id', $request->input('category_id[]'));
        // }

        if (!empty($request->input('start_date')) && !empty($request->input('end_date'))) {
            $start_date = $request->input('start_date');
            $end_date = $request->input('end_date');
            $session_details->whereBetween('transactions.transaction_date', [$start_date, $end_date]);
        }

        if (!empty($request->input('product_id'))) {
            $session_details->where('p.id', $request->input('product_id'));
        }

        if (!empty($request->input('variation_id'))) {
            $session_details->where('v_child.id', $request->input('variation_id'));
        }
        if (!empty($request->input('created_by'))) {
            $session_details->where('u.id', $request->input('created_by'));
        }
        if (!empty($request->input('category_id'))) {
            $session_details->where('cat.id', $request->input('category_id'));
        }

        // Return DataTables response for AJAX requests
        if ($request->ajax()) {
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            return Datatables::of($session_details)
                ->editColumn('invoice_no', function ($row) {
                    $url = action('Modules\Clinic\Http\Controllers\ClinicSellController@show', [$row->id]);
                    return '<a class="btn-link cursor-pointer btn-modal" data-container=".view_modal" data-href="' . $url . '">
                                ' . e($row->invoice_no) . '
                            </a>';
                })
                ->addColumn('customer_name', function ($row) {
                    $fullName = $row->customer_name;
                    $phoneId = 'phone_' . $row->id;
                    $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';

                    if (auth()->user()->can('admin')) {
                        return $fullName . ' (' . $row->mobile . ')';
                    } else {
                        return $fullName . ' ' . $phoneIcon .
                            ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->mobile . '</span>';
                    }
                })
                ->editColumn('product_name', function ($row) {
                    return $row->product_name;
                })
                ->editColumn('variation_names', function ($row) {
                    return $row->modifier_names;
                })
                ->editColumn('session_no', function ($row) {
                    return $row->session_no;
                })
                ->editColumn('quantity', function ($row) {
                    return number_format($row->quantity);
                })
                ->editColumn('price', function ($row) {
                    return number_format($row->unit_price, 2);
                })
                ->editColumn('additional_notes', function ($row) {
                    return $row->additional_notes;
                })
                ->editColumn(
                    'final_total',
                    '<span class="final-total" data-orig-value="{{$final_total}}">@format_currency($final_total)</span>'
                )
                ->editColumn(
                    'total_line_discount',
                    '<span class="final-total" data-orig-value="{{$total_line_discount}}">@format_currency($total_line_discount)</span>'
                )
                ->editColumn(
                    'total_discount',
                    '<span class="final-total" data-orig-value="{{$total_discount}}">@format_currency($total_discount)</span>'
                )
                ->editColumn(
                    'payment_status',
                    function ($row) {
                        $payment_status = Transaction::getPaymentStatus($row);
                        return (string) view('clinic::appointment.partials.payment_status', ['payment_status' => $payment_status, 'id' => $row->id]);
                    }
                )
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
                ->editColumn('transaction_date', '{{@format_datetime($transaction_date)}}')
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->rawColumns(['invoice_no', 'customer_name', 'product_name', 'variation_names', 'session_no', 'quantity', 'price', 'total_line_discount', 'total_discount', 'final_total', 'created_at', 'additional_notes', 'payment_status', 'payment_methods'])
                ->make(true);
        }

        // Return the view for non-AJAX requests
        $therapy = ['therapy'];
        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $categories = Category::forDropdownClinic($business_id, $therapy);
        $sales_representative = User::forDropdown($business_id, false, false, true);
        $therapy_product = Product::where('product_type', 'therapy')->orderBy('name', 'asc')->get()->pluck('name', 'id');

        $product_ids = Product::where('product_type', 'therapy')->get()->pluck('id');
        $selections = Variation::whereIn('product_id', $product_ids)->where('name', '!=', 'DUMMY')->orderBy('name', 'asc')->get()->pluck('name', 'id');

        return view('clinic::sell.individual_therapy', compact('business_locations', 'categories', 'sales_representative', 'therapy_product', 'selections'));
    }

    public function getVariationsByProduct(Request $request)
    {
        $productId = $request->input('product_id');
        Log::info('Fetching variations for product ID:', ['product_id' => $productId]); // Log the product ID

        // Fetch variations and exclude DUMMY
        $variations = Variation::where('product_id', $productId)
            ->where('name', '!=', 'DUMMY') // Exclude DUMMY variations
            ->orderBy('name', 'asc')
            ->pluck('name', 'id');

        Log::info('Variations fetched:', ['variations' => $variations]); // Log the variations

        // Debug: Log the raw SQL query
        $query = Variation::where('product_id', $productId)
            ->where('name', '!=', 'DUMMY')
            ->orderBy('name', 'asc')
            ->toSql();
        Log::info('Raw SQL Query:', ['query' => $query]);

        // Debug: Log the raw query results
        $rawResults = DB::table('variations')
            ->where('product_id', $productId)
            ->where('name', '!=', 'DUMMY')
            ->orderBy('name', 'asc')
            ->get();
        Log::info('Raw Query Results:', ['results' => $rawResults]);

        return response()->json($variations);
    }

    public function todayProductSellGroupedReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->get('location_id', null);

        $vld_str = '';
        if (!empty($location_id)) {
            $vld_str = "AND vld.location_id=$location_id";
        }
        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $today = $request->get('transaction_date');
            $single = $request->get('single');

            $query = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->leftjoin('categories as cat', 'p.category_id', '=', 'cat.id')
                ->leftjoin('brands as b', 'p.brand_id', '=', 'b.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->whereIn('t.sub_type', ['test', 'therapy', 'ipd', 'consultation'])
                ->where('t.status', 'final')
                ->select(
                    'p.name as product_name',
                    'p.enable_stock',
                    'cat.name as category_name',
                    'b.name as brand_name',
                    'p.type as product_just_type',
                    'p.product_type as product_type',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    't.id as transaction_id',
                    't.transaction_date as transaction_date',
                    'transaction_sell_lines.parent_sell_line_id',
                    DB::raw('DATE_FORMAT(t.transaction_date, "%Y-%m-%d") as formated_date'),
                    DB::raw("(SELECT SUM(vld.qty_available) FROM variation_location_details as vld WHERE vld.variation_id=v.id $vld_str) as current_stock"),
                    DB::raw('
                        CASE
                            WHEN p.type = "modifier" THEN ""
                            ELSE SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned)
                        END as total_qty_sold
                    '),
                    DB::raw('
                        CASE
                            WHEN p.type = "single" THEN ""
                            ELSE SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned)
                        END as total_qty_sold_modifier
                    '),
                    'u.short_name as unit',
                    DB::raw('SUM((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
                )
                ->groupBy('v.id');

            if ($single == 2) {
                $query->groupBy('formated_date');
            }
            if (!empty($today)) {
                $query->whereDate('t.transaction_date', $today);
            }

            if (!empty($variation_id)) {
                $query->where('transaction_sell_lines.variation_id', $variation_id);
            }
            if (!empty(request()->input('product_sub_type'))) {
                $query->where('p.product_type', request()->input('product_sub_type'));
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereDate('t.transaction_date', '>=', $start_date)
                    ->whereDate('t.transaction_date', '<=', $end_date);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $customer_id = $request->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('t.contact_id', $customer_id);
            }

            $customer_group_id = $request->get('customer_group_id', null);
            if (!empty($customer_group_id)) {
                $query->leftjoin('contacts AS c', 't.contact_id', '=', 'c.id')
                    ->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
                    ->where('CG.id', $customer_group_id);
            }

            $category_id = $request->get('category_id', null);
            if (!empty($category_id)) {
                $query->whereIn('p.category_id', $category_id);
            }

            $brand_id = $request->get('brand_id', null);
            if (!empty($brand_id)) {
                $query->whereIn('p.brand_id', $brand_id);
            }
            $category_id = $request->get('category_id', null);
            if (!empty($category_id)) {
                $query->whereIn('p.category_id', $category_id);
            }
            $unit_ids = request()->get('unit_id', null);
            if (!empty($unit_ids)) {
                $query->whereIn('p.unit_id', $unit_ids);
            }

            $tax_ids = request()->get('tax_id', null);
            if (!empty($tax_ids)) {
                $query->whereIn('p.tax', $tax_ids);
            }

            $types = request()->get('type', null);
            if (!empty($types)) {
                $query->whereIn('p.type', $types);
            }
            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_just_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }
                    $html = '<a href="#" data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->transaction_id]) . '" class="btn-modal" data-container=".view_modal">' . $product_name . '</a>';
                    return $html;
                })
                ->editColumn('transaction_date', '{{@format_date($formated_date)}}')
                ->editColumn('total_qty_sold', function ($row) {
                    return $row->total_qty_sold;
                })
                ->editColumn('total_qty_sold_modifier', function ($row) {
                    return $row->total_qty_sold_modifier;
                })
                ->editColumn('current_stock', function ($row) use ($start_date) {
                    if ($row->enable_stock) {
                        return '<span data-is_quantity="true" class="display_currency current_stock" data-currency_symbol=false data-orig-value="' . (float) $row->current_stock . '" data-unit="' . $row->unit . '" >' . (float) $row->current_stock . '</span> ' . $row->unit;
                    } else {
                        return '';
                    }
                })
                ->editColumn('subtotal', function ($row) {
                    $class = is_null($row->parent_sell_line_id) ? 'row_subtotal' : '';

                    return '<span class="' . $class . '" data-orig-value="' . $row->subtotal . '">' .
                        $this->transactionUtil->num_f($row->subtotal, true) . '</span>';
                })
                ->editColumn('transaction_date', '{{format_datetime($transaction_date)}}')
                ->rawColumns(['product_name', 'current_stock', 'subtotal', 'total_qty_sold', 'total_qty_sold_modifier'])
                ->make(true);
        }
    }
    public function paymentReport(Request $request)
    {
        \Log::info('paymentReport called with request: ' . json_encode($request->all()));
        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->get('location_id', null);

        if ($request->ajax()) {
            \Log::info('AJAX request received');
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

            $sale_type = !empty(request()->input('sale_type')) ? request()->input('sale_type') : 'sell';
            $all_sub = ['therapy', 'test', 'ipd', 'consultation'];
            $sub_type = !empty(request()->input('sub_type')) ? request()->input('sub_type') : $all_sub;

            $sells = $this->clinicSellUtil->getListPayments($business_id, $sale_type, $sub_type);
            \Log::info(' - sells: ' . json_encode($sells->get()));
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->input('created_by'))) {
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (request()->has('payment_method') && !empty(request()->input('payment_method'))) {
                $sells->where('transaction_payments.method', request()->input('payment_method'));
            }

            if (!empty(request()->input('customer_id'))) {
                $sells->where('contacts.id', request()->input('customer_id'));
            }

            if (!empty(request()->input('start_date')) && !empty(request()->input('end_date'))) {
                $start = request()->input('start_date');
                $end = request()->input('end_date');
                $sells->whereDate('transaction_payments.paid_on', '>=', $start)
                    ->whereDate('transaction_payments.paid_on', '<=', $end);
            }

            if (!empty(request()->input('sub_type'))) {
                $sells->where('transactions.sub_type', request()->input('sub_type'));
            }

            if (!empty(request()->input('status'))) {
                $sells->where('transactions.status', request()->input('status'));
            }

            $sells->groupBy('transaction_payments.id');

            \Log::info('Query prepared: ' . $sells->toSql());

            try {
                $datatable = DataTables::of($sells)
                    ->addColumn('invoice_no', function ($row) {
                        return $row->invoice_no;
                    })
                    ->addColumn('sale_date', function ($row) {
                        return $row->sale_date;
                    })
                    ->addColumn('pay_date', function ($row) {
                        return $row->pay_date;
                    })
                    ->addColumn('name', function ($row) {
                        return $row->name;
                    })
                    ->addColumn('sub_type', function ($row) {
                        return $row->sub_type ?? 'N/A';
                    })
                    ->addColumn('method', function ($row) use ($payment_types) {
                        $method = $row->method; // Use the method from the current payment
                        $payment_method = $payment_types[$method] ?? $method;
                        $html = '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>';
                        return $html;
                    })
                    ->addColumn('total_items', function ($row) {
                        return $row->total_items;
                    })
                    ->addColumn('final_total', function ($row) {
                        return number_format($row->final_total, 2);
                    })
                    ->addColumn('total_paid', function ($row) {
                        return number_format($row->transaction_total_paid, 2);
                    })
                    ->addColumn('total_payment', function ($row) {
                        return number_format($row->total_paid, 2); // Use transaction_total_paid
                    })
                    ->addColumn('payment_status', function ($row) {
                        return ucfirst($row->payment_status);
                    })
                    ->addColumn('added_by', function ($row) {
                        return $row->added_by;
                    })
                    ->setRowAttr([
                        'data-href' => function ($row) {
                            if (auth()->user()->can('sell.view') || auth()->user()->can('view_own_sell_only')) {
                                return action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->transaction_id]); // Use transaction_id
                            }
                            return '';
                        },
                    ])
                    ->rawColumns(['method']);

                \Log::info('DataTables response prepared');
                return $datatable->make(true);
            } catch (\Exception $e) {
                \Log::error('DataTables error: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                return response()->json(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
            }
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        //Commission agent filter
        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (!empty($is_cmsn_agent_enabled)) {
            $commission_agents = User::forDropdown($business_id, false, true, true);
        }

        //Service staff filter
        $service_staffs = null;
        if ($this->productUtil->isModuleEnabled('service_staff')) {
            $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $sources = $this->transactionUtil->getSources($business_id);

        $payment_types = [
            'cash' => __('lang_v1.cash'),
            'card' => __('lang_v1.card'),
            'custom_pay_1' => __('bKash'),
        ];
        $RemaininInLast = Transaction::where('payment_status', 'due')->whereIn('sub_type', ['test', 'therapy', 'ipd', 'consultation'])
            ->whereDate('transactions.transaction_date', '>=', now()->subDays(30))
            ->count();

        $sub_type = ['therapy', 'test', 'ipd', 'consultation'];

        $brands = Brands::forDropdownWithSubType($business_id, false, false, false, false, $sub_type);
        $categories = Category::forDropdownClinic($business_id, $sub_type);

        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->first();

        return view('clinic::report.payment_report')
            ->with(compact('business_locations', 'customers', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'shipping_statuses', 'sources', 'payment_types', 'RemaininInLast', 'brands', 'categories', 'register'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function register()
    {
        $sub_type = request()->get('sub_type');
        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $clinic_location = $common_settings['clinic_location'] ?? null;
        $user_id = auth()->user()->id;
        $count = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->where('location_id', $clinic_location)
            ->count();
        //Check if there is a open register, if yes then redirect to POS screen.
        if ($this->cashRegisterUtil->countOpenedRegister() != 0 && $count == 0) {
            return redirect()->action([\App\Http\Controllers\SellPosController::class, 'create'], ['sub_type' => $sub_type]);
        }
        $business_locations = BusinessLocation::forDropdownSell($business_id);

        $user_id = auth()->user()->id;
        // Find the last cash register for the authenticated user
        $cashRegister = Cashregister::where('user_id', $user_id)->latest()->first();
        $cash_register_id = $cashRegister ? $cashRegister->id : null;
        return view('clinic::sell.register')->with(compact('business_locations', 'sub_type', 'cash_register_id', 'count', 'clinic_location'));
    }
    public function registerStore(Request $request)
    {
        //like:repair
        $sub_type = request()->get('sub_type');

        try {
            $initial_amount = 0;
            if (!empty($request->input('amount'))) {
                $initial_amount = $this->cashRegisterUtil->num_uf($request->input('amount'));
            }
            $user_id = $request->session()->get('user.id');
            $business_id = $request->session()->get('user.business_id');

            $register = CashRegister::create([
                'business_id' => $business_id,
                'user_id' => $user_id,
                'status' => 'open',
                'location_id' => $request->input('location_id'),
                'created_at' => Carbon::now()->format('Y-m-d H:i:00'),
            ]);
            if (!empty($initial_amount)) {
                $register->cash_register_transactions()->create([
                    'amount' => $initial_amount,
                    'pay_method' => 'cash',
                    'type' => 'credit',
                    'transaction_type' => 'initial',
                ]);
            }
            Session::put('register_open', 1);
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }

        return redirect()->action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'create']);
    }
    public function create()
    {

        if (!auth()->user()->can('clinic.sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        $user_id = request()->session()->get('user.id');
        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $clinic_location = $common_settings['clinic_location'] ?? null;
        $user_id = auth()->user()->id;
        $count = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->where('location_id', $clinic_location)
            ->count();
        if ($count == 1) {
            true;
            $register_open = null;
        } else {
            $output = [
                'success' => 0,
                'msg' => __('Please Close Another POS Register')
            ];

            return redirect()
                ->action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'register']);
        }
        $sale_type = request()->get('sale_type', '');

        if ($sale_type == 'sales_order') {
            if (!auth()->user()->can('clinic.sell.create')) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            if (!auth()->user()->can('direct_sell.access')) {
                abort(403, 'Unauthorized action.');
            }
        }

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'index']));
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $business_locations = BusinessLocation::forDropdownSell($business_id, false, true);
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];

        $default_location = null;
        foreach ($business_locations as $id => $name) {
            $default_location = BusinessLocation::findOrFail($id);
            break;
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
        }

        $types = [];
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $payment_line = $this->dummyPaymentLine;
        $payment_types = $this->transactionUtil->payment_types($clinic_location, true, $business_id);

        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $default_price_group_id = !empty($default_location->selling_price_group_id) && array_key_exists($default_location->selling_price_group_id, $price_groups) ? $default_location->selling_price_group_id : null;

        $default_datetime = $this->businessUtil->format_date('now', true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $invoice_schemes = InvoiceScheme::forDropdown($business_id);
        $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        if (!empty($default_location) && !empty($default_location->sale_invoice_scheme_id)) {
            $default_invoice_schemes = InvoiceScheme::where('business_id', $business_id)
                ->findorfail($default_location->sale_invoice_scheme_id);
        }
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        //Types of service
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $status = request()->get('status', '');

        $statuses = Transaction::clinic_sell_statuses();

        if ($sale_type == 'sales_order') {
            $status = 'ordered';
        }

        $is_order_request_enabled = false;
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        if ($is_crm) {
            $crm_settings = Business::where('id', auth()->user()->business_id)
                ->value('crm_settings');
            $crm_settings = !empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (!empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $change_return = $this->dummyPaymentLine;

        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $expiring_soon = $common_settings['expiring_soon'] ?? 30;
        $expiring_later = $common_settings['expiring_later'] ?? 90;
        $clinic_location = $common_settings['clinic_location'] ?? null;
        $pos_settings = json_decode($business->pos_settings, true);

        if (auth()->user()->can('discount_no_limit') || auth()->user()->can('superadmin') || auth()->user()->can('admin')) {
            $enable_discount_rules = [];
        } else {
            $enable_discount_rules = $pos_settings['enable_discount_rules'] ?? [];
        }

        $discount_rules = $pos_settings['discount_rules'] ?? [];
        // Set session values
        session([
            'expiring_soon' => $expiring_soon,
            'expiring_later' => $expiring_later,
        ]);
        $sub_type = [
            'test' => 'Test',
            'therapy' => 'Therapy',
            'ipd' => 'IPD'
        ];
        $doctors = DoctorProfile::where('is_doctor', 1)->get()->mapWithKeys(function ($doctor) {
            return [$doctor->user_id => $doctor->first_name . ' ' . $doctor->last_name];
        });
        $diseases = Problem::all();
        $only_therapy_bill = request()->get('only_therapy_bill', false);
        $patient_session_id = request()->get('session_id', null);
        $contact = null;
        $doctor_id = null;
        $appointment_id = request()->get('appointment_id', null);

        if (!empty($patient_session_id)) {
            $appointment = PatientAppointmentRequ::where('patient_session_info_id', $patient_session_id)->first();
            $contact = Contact::find($appointment->patient_contact_id);
            $doctor_id = $appointment->doctor_user_id;
        }
        return view('clinic::sell.create')
            ->with(compact(
                'business_details',
                'taxes',
                'discount_rules',
                'enable_discount_rules',
                'walk_in_customer',
                'business_locations',
                'bl_attributes',
                'default_location',
                'commission_agent',
                'types',
                'clinic_location',
                'customer_groups',
                'payment_line',
                'payment_types',
                'price_groups',
                'default_datetime',
                'pos_settings',
                'invoice_schemes',
                'default_invoice_schemes',
                'types_of_service',
                'accounts',
                'shipping_statuses',
                'status',
                'sale_type',
                'statuses',
                'is_order_request_enabled',
                'users',
                'default_price_group_id',
                'change_return',
                'sub_type',
                'doctors',
                'diseases',
                'register_open',
                'only_therapy_bill',
                'patient_session_id',
                'contact',
                'doctor_id',
                'appointment_id',
            ));
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
        if (!auth()->user()->can('clinic.sell.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $taxes = TaxRate::where('business_id', $business_id)
            ->pluck('name', 'id');
        $query = Transaction::with('reference')->where('business_id', $business_id)
            ->where('id', $id)
            ->with([
                'contact',
                'delivery_person_user',
                'sell_lines' => function ($q) {
                    $q->whereNull('parent_sell_line_id');
                },
                'sell_lines.product',
                'sell_lines.product.unit',
                'sell_lines.product.second_unit',
                'sell_lines.variations',
                'sell_lines.variations.product_variation',
                'payment_lines',
                'sell_lines.modifiers',
                'sell_lines.lot_details',
                'tax',
                'sell_lines.sub_unit',
                'table',
                'service_staff',
                'sell_lines.service_staff',
                'types_of_service',
                'sell_lines.warranties',
                'media'
            ]);

        if (!auth()->user()->can('clinic.sell.view')) {
            $query->where('transactions.created_by', request()->session()->get('user.id'));
        }

        $sell = $query->firstOrFail();

        $activities = Activity::forSubject($sell)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();

        $line_taxes = [];
        foreach ($sell->sell_lines as $key => $value) {
            if (!empty($value->sub_unit_id)) {
                $formated_sell_line = $this->transactionUtil->recalculateSellLineTotals($business_id, $value);
                $sell->sell_lines[$key] = $formated_sell_line;
            }

            if (!empty($taxes[$value->tax_id])) {
                if (isset($line_taxes[$taxes[$value->tax_id]])) {
                    $line_taxes[$taxes[$value->tax_id]] += ($value->item_tax * $value->quantity);
                } else {
                    $line_taxes[$taxes[$value->tax_id]] = ($value->item_tax * $value->quantity);
                }
            }
        }

        $payment_types = $this->transactionUtil->payment_types($sell->location_id, true);
        $order_taxes = [];
        if (!empty($sell->tax)) {
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
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;

        $statuses = Transaction::clinic_sell_statuses();

        if ($sell->type == 'sales_order') {
            $sales_order_statuses = Transaction::sales_order_statuses(true);
            $statuses = array_merge($statuses, $sales_order_statuses);
        }
        $status_color_in_activity = Transaction::sales_order_statuses();
        $sales_orders = $sell->salesOrders();

        return view('clinic::sell.show')
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


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */

    public function edit($id)
    {
        if (!auth()->user()->can('clinic.sell.draft_edit')) {
            abort(403, 'Unauthorized action.');
        }
        $user_id = request()->session()->get('user.id');
        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $clinic_location = $common_settings['clinic_location'] ?? null;
        $user_id = auth()->user()->id;
        $count = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->where('location_id', $clinic_location)
            ->count();
        if ($count == 1) {
            true;
            $register_open = null;
        } else {
            $output = [
                'success' => 0,
                'msg' => __('Please Close Another POS Register')
            ];

            return redirect()
                ->action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'register']);
        }
        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', [
                    'success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]),
                ]);
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('lang_v1.return_exist'),
            ]);
        }

        $business_id = request()->session()->get('user.business_id');

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::with('reference')->where('business_id', $business_id)
            ->with(['price_group', 'types_of_service', 'media', 'media.uploaded_by_user'])
            ->whereIn('type', ['sell', 'sales_order'])
            ->findorfail($id);
        $is_order_request_enabled = false;
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        if ($is_crm) {
            $crm_settings = Business::where('id', auth()->user()->business_id)
                ->value('crm_settings');
            $crm_settings = !empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (!empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }

        $sales_orders = [];
        if (!empty($pos_settings['enable_sales_order']) || $is_order_request_enabled) {
            $sales_orders = Transaction::where('business_id', $business_id)
                ->where('type', 'sales_order')
                ->where('contact_id', $transaction->contact_id)
                ->where(function ($q) use ($transaction) {
                    $q->where('status', '!=', 'completed');

                    if (!empty($transaction->sales_order_ids)) {
                        $q->orWhereIn('id', $transaction->sales_order_ids);
                    }
                })
                ->pluck('invoice_no', 'id');
        }

        $location_id = $transaction->location_id;
        $location_printer_type = BusinessLocation::find($location_id)->receipt_printer_type;

        $sell_details = TransactionSellLine::join(
            'products AS p',
            'transaction_sell_lines.product_id',
            '=',
            'p.id'
        )
            ->join(
                'variations AS variations',
                'transaction_sell_lines.variation_id',
                '=',
                'variations.id'
            )
            ->join(
                'product_variations AS pv',
                'variations.product_variation_id',
                '=',
                'pv.id'
            )
            ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')
                    ->where('vld.location_id', '=', $location_id);
            })
            ->leftjoin('units', 'units.id', '=', 'p.unit_id')
            ->leftjoin('units as u', 'p.secondary_unit_id', '=', 'u.id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->with(['warranties', 'so_line'])
            ->select(
                DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                'p.id as product_id',
                'p.enable_stock',
                'p.name as product_actual_name',
                'p.type as product_type',
                'pv.name as product_variation_name',
                'pv.is_dummy as is_dummy',
                'variations.name as variation_name',
                'variations.sub_sku',
                'p.barcode_type',
                'p.enable_sr_no',
                'variations.id as variation_id',
                'units.short_name as unit',
                'units.allow_decimal as unit_allow_decimal',
                'u.short_name as second_unit',
                'transaction_sell_lines.secondary_unit_quantity',
                'transaction_sell_lines.tax_id as tax_id',
                'transaction_sell_lines.item_tax as item_tax',
                'transaction_sell_lines.unit_price as default_sell_price',
                'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                'transaction_sell_lines.id as transaction_sell_lines_id',
                'transaction_sell_lines.id',
                'transaction_sell_lines.quantity as quantity_ordered',
                'transaction_sell_lines.sell_line_note as sell_line_note',
                'transaction_sell_lines.parent_sell_line_id',
                'transaction_sell_lines.lot_no_line_id',
                'transaction_sell_lines.line_discount_type',
                'transaction_sell_lines.line_discount_amount',
                'transaction_sell_lines.res_service_staff_id',
                'units.id as unit_id',
                'transaction_sell_lines.sub_unit_id',
                'transaction_sell_lines.so_line_id',
                DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
            )
            ->get();

        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                //If modifier or combo sell line then unset
                if (!empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    if ($transaction->status != 'final') {
                        $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                        $sell_details[$key]->qty_available = $actual_qty_avlbl;
                        $value->qty_available = $actual_qty_avlbl;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->transactionUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;

                    if (!empty($value->sub_unit_id)) {
                        $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                        $sell_details[$key] = $value;
                    }

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'modifier')
                            ->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }

                    //Get details of combo items
                    if ($sell_details[$key]->product_type == 'combo') {
                        $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'combo')
                            ->get()
                            ->toArray();
                        if (!empty($sell_line_combos)) {
                            $sell_details[$key]->combo_products = $sell_line_combos;
                        }

                        //calculate quantity available if combo product
                        $combo_variations = [];
                        foreach ($sell_line_combos as $combo_line) {
                            $combo_variations[] = [
                                'variation_id' => $combo_line['variation_id'],
                                'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
                                'unit_id' => null,
                            ];
                        }
                        $sell_details[$key]->qty_available =
                            $this->productUtil->calculateComboQuantity($location_id, $combo_variations);

                        if ($transaction->status == 'final') {
                            $sell_details[$key]->qty_available = $sell_details[$key]->qty_available + $sell_details[$key]->quantity_ordered;
                        }

                        $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
                    }
                }
            }
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $transaction->transaction_date = $this->transactionUtil->format_date($transaction->transaction_date, true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $invoice_schemes = [];
        $default_invoice_schemes = null;

        if ($transaction->status == 'draft') {
            $invoice_schemes = InvoiceScheme::forDropdown($business_id);
            $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        }

        $redeem_details = [];
        if (request()->session()->get('business.enable_rp') == 1) {
            $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }
        $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;
        $warranties = $is_warranty_enabled ? Warranty::forDropdown($business_id) : [];

        $statuses = Transaction::clinic_sell_statuses();

        $is_order_request_enabled = false;
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        if ($is_crm) {
            $crm_settings = Business::where('id', auth()->user()->business_id)
                ->value('crm_settings');
            $crm_settings = !empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (!empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }

        $sales_orders = [];
        if (!empty($pos_settings['enable_sales_order']) || $is_order_request_enabled) {
            $sales_orders = Transaction::where('business_id', $business_id)
                ->where('type', 'sales_order')
                ->where('contact_id', $transaction->contact_id)
                ->where(function ($q) use ($transaction) {
                    $q->where('status', '!=', 'completed');

                    if (!empty($transaction->sales_order_ids)) {
                        $q->orWhereIn('id', $transaction->sales_order_ids);
                    }
                })
                ->pluck('invoice_no', 'id');
        }

        $payment_types = $this->transactionUtil->payment_types($transaction->location_id, false, $business_id);

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $change_return = $this->dummyPaymentLine;

        $customer_due = $this->transactionUtil->getContactDue($transaction->contact_id, $transaction->business_id);

        $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $expiring_soon = $common_settings['expiring_soon'] ?? 30;
        $expiring_later = $common_settings['expiring_later'] ?? 90;
        $pos_settings = json_decode($business->pos_settings, true);

        if (auth()->user()->can('discount_no_limit') || auth()->user()->can('superadmin') || auth()->user()->can('admin')) {
            $enable_discount_rules = [];
        } else {
            $enable_discount_rules = $pos_settings['enable_discount_rules'] ?? [];
        }

        $discount_rules = $pos_settings['discount_rules'] ?? [];
        // Set session values
        session([
            'expiring_soon' => $expiring_soon,
            'expiring_later' => $expiring_later,
        ]);
        $doctors = DoctorProfile::all()->mapWithKeys(function ($doctor) {
            return [$doctor->user_id => $doctor->first_name . ' ' . $doctor->last_name];
        });
        $diseases = Problem::all();

        return view('clinic::sell.partials.draft_edit')
            ->with(compact('business_details', 'doctors', 'taxes', 'sell_details', 'transaction', 'commission_agent', 'types', 'customer_groups', 'pos_settings', 'waiters', 'invoice_schemes', 'default_invoice_schemes', 'redeem_details', 'edit_discount', 'edit_price', 'shipping_statuses', 'warranties', 'statuses', 'sales_orders', 'payment_types', 'accounts', 'payment_lines', 'change_return', 'is_order_request_enabled', 'customer_due', 'users', 'discount_rules', 'enable_discount_rules', 'types_of_service', 'diseases'));
    }


    // public function editORG($id)
    // {
    //     // return redirect("/clinic/sells/{$id}/draftEdit/");



    //     $business_id = request()->session()->get('user.business_id');

    //     if (!(auth()->user()->can('superadmin') || auth()->user()->can('clinic.sell.edit'))) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     //Check if the transaction can be edited or not.
    //     $edit_days = request()->session()->get('business.transaction_edit_days');
    //     if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
    //         return back()
    //             ->with('status', [
    //                 'success' => 0,
    //                 'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]),
    //             ]);
    //     }

    //     //Check if there is a open register, if no then redirect to Create Register screen.
    //     if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
    //         return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
    //     }

    //     //Check if return exist then not allowed
    //     if ($this->transactionUtil->isReturnExist($id)) {
    //         return back()->with('status', [
    //             'success' => 0,
    //             'msg' => __('lang_v1.return_exist'),
    //         ]);
    //     }

    //     $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

    //     $business_details = $this->businessUtil->getDetails($business_id);

    //     $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
    //     $statuses = Transaction::clinic_sell_statuses();

    //     $transaction = Transaction::where('business_id', $business_id)
    //         ->where('type', 'sell')
    //         ->with(['price_group', 'types_of_service'])
    //         ->findorfail($id);
    //         $is_order_request_enabled = false;
    //         $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
    //         if ($is_crm) {
    //             $crm_settings = Business::where('id', auth()->user()->business_id)
    //                                 ->value('crm_settings');
    //             $crm_settings = ! empty($crm_settings) ? json_decode($crm_settings, true) : [];

    //             if (! empty($crm_settings['enable_order_request'])) {
    //                 $is_order_request_enabled = true;
    //             }
    //         }

    //         $sales_orders = [];
    //         if (! empty($pos_settings['enable_sales_order']) || $is_order_request_enabled) {
    //             $sales_orders = Transaction::where('business_id', $business_id)
    //                                 ->where('type', 'sales_order')
    //                                 ->where('contact_id', $transaction->contact_id)
    //                                 ->where(function ($q) use ($transaction) {
    //                                     $q->where('status', '!=', 'completed');

    //                                     if (! empty($transaction->sales_order_ids)) {
    //                                         $q->orWhereIn('id', $transaction->sales_order_ids);
    //                                     }
    //                                 })
    //                                 ->pluck('invoice_no', 'id');
    //         }

    //     $location_id = $transaction->location_id;
    //     $business_location = BusinessLocation::find($location_id);
    //     $payment_types = $this->productUtil->payment_types($business_location, true);
    //     $location_printer_type = $business_location->receipt_printer_type;
    //     $sell_details = TransactionSellLine::join(
    //         'products AS p',
    //         'transaction_sell_lines.product_id',
    //         '=',
    //         'p.id'
    //     )
    //         ->join(
    //             'variations AS variations',
    //             'transaction_sell_lines.variation_id',
    //             '=',
    //             'variations.id'
    //         )
    //         ->join(
    //             'product_variations AS pv',
    //             'variations.product_variation_id',
    //             '=',
    //             'pv.id'
    //         )
    //         ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
    //             $join->on('variations.id', '=', 'vld.variation_id')
    //                 ->where('vld.location_id', '=', $location_id);
    //         })
    //         ->leftjoin('units', 'units.id', '=', 'p.unit_id')
    //         ->leftjoin('units as u', 'p.secondary_unit_id', '=', 'u.id')
    //         ->where('transaction_sell_lines.transaction_id', $id)
    //         ->with(['warranties'])
    //         ->select(
    //             DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
    //             'p.id as product_id',
    //             'p.enable_stock',
    //             'p.name as product_actual_name',
    //             'p.type as product_type',
    //             'pv.name as product_variation_name',
    //             'pv.is_dummy as is_dummy',
    //             'variations.name as variation_name',
    //             'variations.sub_sku',
    //             'p.barcode_type',
    //             'p.enable_sr_no',
    //             'variations.id as variation_id',
    //             'units.short_name as unit',
    //             'units.allow_decimal as unit_allow_decimal',
    //             'u.short_name as second_unit',
    //             'transaction_sell_lines.secondary_unit_quantity',
    //             'transaction_sell_lines.tax_id as tax_id',
    //             'transaction_sell_lines.item_tax as item_tax',
    //             'transaction_sell_lines.unit_price as default_sell_price',
    //             'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
    //             'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
    //             'transaction_sell_lines.id as transaction_sell_lines_id',
    //             'transaction_sell_lines.id',
    //             'transaction_sell_lines.quantity as quantity_ordered',
    //             'transaction_sell_lines.sell_line_note as sell_line_note',
    //             'transaction_sell_lines.parent_sell_line_id',
    //             'transaction_sell_lines.lot_no_line_id',
    //             'transaction_sell_lines.line_discount_type',
    //             'transaction_sell_lines.line_discount_amount',
    //             'transaction_sell_lines.res_service_staff_id',
    //             'units.id as unit_id',
    //             'transaction_sell_lines.sub_unit_id',

    //             //qty_available not added when negative to avoid max quanity getting decreased in edit and showing error in max quantity validation
    //             DB::raw('IF(vld.qty_available > 0, vld.qty_available + transaction_sell_lines.quantity, transaction_sell_lines.quantity) AS qty_available')
    //         )
    //         ->get();
    //     if (!empty($sell_details)) {
    //         foreach ($sell_details as $key => $value) {

    //             //If modifier or combo sell line then unset
    //             if (!empty($sell_details[$key]->parent_sell_line_id)) {
    //                 unset($sell_details[$key]);
    //             } else {
    //                 if ($transaction->status != 'final') {
    //                     $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
    //                     $sell_details[$key]->qty_available = $actual_qty_avlbl;
    //                     $value->qty_available = $actual_qty_avlbl;
    //                 }

    //                 $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

    //                 //Add available lot numbers for dropdown to sell lines
    //                 $lot_numbers = [];
    //                 if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
    //                     $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
    //                     foreach ($lot_number_obj as $lot_number) {
    //                         //If lot number is selected added ordered quantity to lot quantity available
    //                         if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
    //                             $lot_number->qty_available += $value->quantity_ordered;
    //                         }

    //                         $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
    //                         $lot_numbers[] = $lot_number;
    //                     }
    //                 }
    //                 $sell_details[$key]->lot_numbers = $lot_numbers;

    //                 if (!empty($value->sub_unit_id)) {
    //                     $value = $this->productUtil->changeSellLineUnit($business_id, $value);
    //                     $sell_details[$key] = $value;
    //                 }

    //                 $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

    //                 if ($this->transactionUtil->isModuleEnabled('modifiers')) {
    //                     //Add modifier details to sel line details
    //                     $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
    //                         ->where('children_type', 'modifier')
    //                         ->get();
    //                     $modifiers_ids = [];
    //                     if (count($sell_line_modifiers) > 0) {
    //                         $sell_details[$key]->modifiers = $sell_line_modifiers;
    //                         foreach ($sell_line_modifiers as $sell_line_modifier) {
    //                             $modifiers_ids[] = $sell_line_modifier->variation_id;
    //                         }
    //                     }
    //                     $sell_details[$key]->modifiers_ids = $modifiers_ids;

    //                     //add product modifier sets for edit
    //                     $this_product = Product::find($sell_details[$key]->product_id);
    //                     if (count($this_product->modifier_sets) > 0) {
    //                         $sell_details[$key]->product_ms = $this_product->modifier_sets;
    //                     }
    //                 }

    //                 //Get details of combo items
    //                 if ($sell_details[$key]->product_type == 'combo') {
    //                     $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
    //                         ->where('children_type', 'combo')
    //                         ->get()
    //                         ->toArray();
    //                     if (!empty($sell_line_combos)) {
    //                         $sell_details[$key]->combo_products = $sell_line_combos;
    //                     }

    //                     //calculate quantity available if combo product
    //                     $combo_variations = [];
    //                     foreach ($sell_line_combos as $combo_line) {
    //                         $combo_variations[] = [
    //                             'variation_id' => $combo_line['variation_id'],
    //                             'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
    //                             'unit_id' => null,
    //                         ];
    //                     }
    //                     $sell_details[$key]->qty_available =
    //                         $this->productUtil->calculateComboQuantity($location_id, $combo_variations);

    //                     if ($transaction->status == 'final') {
    //                         $sell_details[$key]->qty_available = $sell_details[$key]->qty_available + $sell_details[$key]->quantity_ordered;
    //                     }

    //                     $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
    //                 }
    //             }
    //         }
    //     }

    //     $featured_products = $business_location->getFeaturedProducts();

    //     $payment_lines = $this->transactionUtil->getPaymentDetails($id);
    //     //If no payment lines found then add dummy payment line.
    //     if (empty($payment_lines)) {
    //         $payment_lines[] = $this->dummyPaymentLine;
    //     }

    //     $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
    //     $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

    //     $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
    //     $commission_agent = [];
    //     if ($commsn_agnt_setting == 'user') {
    //         $commission_agent = User::forDropdown($business_id, false);
    //     } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
    //         $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
    //     }

    //     //If brands, category are enabled then send else false.
    //     $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
    //     $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::forDropdown($business_id)
    //         ->prepend(__('lang_v1.all_brands'), 'all') : false;

    //     $change_return = $this->dummyPaymentLine;

    //     $types = [];
    //     if (auth()->user()->can('supplier.create')) {
    //         $types['supplier'] = __('report.supplier');
    //     }
    //     if (auth()->user()->can('customer.create')) {
    //         $types['customer'] = __('report.customer');
    //     }
    //     if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
    //         $types['both'] = __('lang_v1.both_supplier_customer');
    //     }
    //     $customer_groups = CustomerGroup::forDropdown($business_id);

    //     //Accounts
    //     $accounts = [];
    //     if ($this->moduleUtil->isModuleEnabled('account')) {
    //         $accounts = Account::forDropdown($business_id, true, false, true);
    //     }

    //     $waiters = [];
    //     if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
    //         $waiters_enabled = true;
    //         $waiters = $this->productUtil->serviceStaffDropdown($business_id);
    //     }
    //     $redeem_details = [];
    //     if (request()->session()->get('business.enable_rp') == 1) {
    //         $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

    //         $redeem_details['points'] += $transaction->rp_redeemed;
    //         $redeem_details['points'] -= $transaction->rp_earned;
    //     }

    //     $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
    //     $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
    //     $shipping_statuses = $this->transactionUtil->shipping_statuses();

    //     $warranties = $this->__getwarranties();
    //     $sub_type = request()->get('sub_type');

    //     //pos screen view from module
    //     $pos_module_data = $this->moduleUtil->getModuleData('get_pos_screen_view', ['sub_type' => $sub_type]);

    //     $invoice_schemes = [];
    //     $default_invoice_schemes = null;

    //     if ($transaction->status == 'draft') {
    //         $invoice_schemes = InvoiceScheme::forDropdown($business_id);
    //         $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
    //     }

    //     $invoice_layouts = InvoiceLayout::forDropdown($business_id);

    //     $customer_due = $this->transactionUtil->getContactDue($transaction->contact_id, $transaction->business_id);

    //     $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';

    //     //Added check because $users is of no use if enable_contact_assign if false
    //     $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];
    //     $only_payment = request()->segment(2) == 'payment';
    //     $business = Business::findOrFail($business_id);
    //     $common_settings = $business->common_settings;
    //     $expiring_soon = $common_settings['expiring_soon'] ?? 30;
    //     $expiring_later = $common_settings['expiring_later'] ?? 90;
    //     $pos_settings = json_decode($business->pos_settings, true);

    //     if (auth()->user()->can('discount_no_limit') || auth()->user()->can('superadmin') || auth()->user()->can('admin')) {
    //         $enable_discount_rules = [];
    //     } else {
    //         $enable_discount_rules = $pos_settings['enable_discount_rules'] ?? [];
    //     }

    //     $discount_rules = $pos_settings['discount_rules'] ?? [];

    //     // Set session values
    //     session([
    //         'expiring_soon' => $expiring_soon,
    //         'expiring_later' => $expiring_later,
    //     ]);
    //     $doctors = DoctorProfile::all()->mapWithKeys(function ($doctor) {
    //         return [$doctor->id => $doctor->first_name . ' ' . $doctor->last_name];
    //     });
    //     $divisions = Division::all();
    //     $diseases = Disease::all();

    //     return view('clinic::sell.edit')
    //         ->with(compact(
    //             'business_details',
    //             'taxes',
    //             'doctors',
    //             'payment_types',
    //             'walk_in_customer',
    //             'sell_details',
    //             'transaction',
    //             'payment_lines',
    //             'location_printer_type',
    //             'shortcuts',
    //             'commission_agent',
    //             'categories',
    //             'pos_settings',
    //             'change_return',
    //             'types',
    //             'customer_groups',
    //             'brands',
    //             'accounts',
    //             'waiters',
    //             'redeem_details',
    //             'edit_price',
    //             'edit_discount',
    //             'shipping_statuses',
    //             'statuses',
    //             'warranties',
    //             'sub_type',
    //             'pos_module_data',
    //             'invoice_schemes',
    //             'default_invoice_schemes',
    //             'invoice_layouts',
    //             'featured_products',
    //             'customer_due',
    //             'users',
    //             'only_payment',
    //             'discount_rules',
    //             'enable_discount_rules',
    //             'divisions',
    //             'diseases',
    //             'is_order_request_enabled',
    //         ));
    // }


    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (
            !auth()->user()->can('clinic.sell.edit')
        ) {
            abort(403, 'Unauthorized action.');
        }
        $salePoint = $request->input('sale_point', 'default_value');

        try {
            $input = $request->except('_token');

            //status is send as quotation from edit sales screen.
            $input['is_quotation'] = 0;
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
                $input['sub_status'] = 'quotation';
            } elseif ($input['status'] == 'proforma') {
                $input['status'] = 'draft';
                $input['sub_status'] = 'proforma';
                $input['is_quotation'] = 0;
            } else {
                $input['sub_status'] = null;
                $input['is_quotation'] = 0;
            }

            $is_direct_sale = false;
            if (!empty($input['products'])) {
                //Get transaction value before updating.
                $transaction_before = Transaction::find($id);
                $status_before = $transaction_before->status;
                $rp_earned_before = $transaction_before->rp_earned;
                $rp_redeemed_before = $transaction_before->rp_redeemed;

                if ($transaction_before->is_direct_sale == 1) {
                    $is_direct_sale = true;
                }

                $sales_order_ids = $transaction_before->sales_order_ids ?? [];

                //Add change return
                $change_return = $this->dummyPaymentLine;
                if (!empty($input['payment']['change_return'])) {
                    $change_return = $input['payment']['change_return'];
                    unset($input['payment']['change_return']);
                }

                //Check Customer credit limit
                $is_credit_limit_exeeded = $transaction_before->type == 'sell' ? $this->transactionUtil->isCustomerCreditLimitExeeded($input, $id) : false;

                if ($is_credit_limit_exeeded !== false) {
                    $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                    $output = [
                        'success' => 0,
                        'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount]),
                    ];
                    if (!$is_direct_sale) {
                        return $output;
                    } else {
                        return redirect()
                            ->action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'index'])
                            ->with('status', $output);
                    }
                }

                //Check if there is a open register, if no then redirect to Create Register screen.
                if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
                    return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
                }

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');

                $discount = [
                    'discount_type' => $input['discount_type'],
                    'discount_amount' => $input['discount_amount'],
                ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                if (!empty($request->input('transaction_date'))) {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }
                if (!empty($request->input('appointment_date'))) {
                    $input['appointment_date'] = $this->productUtil->uf_date($request->input('appointment_date'), true);
                }

                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                if ($commsn_agnt_setting == 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
                    $input['exchange_rate'] = 1;
                }

                //Customer group details
                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                //set selling price group id
                $price_group_id = $request->has('price_group') ? $request->input('price_group') : null;

                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend'] ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                if ($status_before == 'draft' && !empty($request->input('invoice_scheme_id'))) {
                    $input['invoice_scheme_id'] = $request->input('invoice_scheme_id');
                }

                //Types of service
                if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
                    $input['types_of_service_id'] = $request->input('types_of_service_id');
                    $price_group_id = !empty($request->input('types_of_service_price_group')) ? $request->input('types_of_service_price_group') : $price_group_id;
                    $input['packing_charge'] = !empty($request->input('packing_charge')) ?
                        $this->transactionUtil->num_uf($request->input('packing_charge')) : 0;
                    $input['packing_charge_type'] = $request->input('packing_charge_type');
                    $input['service_custom_field_1'] = !empty($request->input('service_custom_field_1')) ?
                        $request->input('service_custom_field_1') : null;
                    $input['service_custom_field_2'] = !empty($request->input('service_custom_field_2')) ?
                        $request->input('service_custom_field_2') : null;
                    $input['service_custom_field_3'] = !empty($request->input('service_custom_field_3')) ?
                        $request->input('service_custom_field_3') : null;
                    $input['service_custom_field_4'] = !empty($request->input('service_custom_field_4')) ?
                        $request->input('service_custom_field_4') : null;
                    $input['service_custom_field_5'] = !empty($request->input('service_custom_field_5')) ?
                        $request->input('service_custom_field_5') : null;
                    $input['service_custom_field_6'] = !empty($request->input('service_custom_field_6')) ?
                        $request->input('service_custom_field_6') : null;
                }

                $input['selling_price_group_id'] = $price_group_id;

                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $input['res_table_id'] = request()->get('res_table_id');
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $input['res_waiter_id'] = request()->get('res_waiter_id');
                }

                if ($this->transactionUtil->isModuleEnabled('kitchen')) {
                    $input['is_kitchen_order'] = request()->get('is_kitchen_order');
                }

                //upload document
                $document_name = $this->transactionUtil->uploadFile($request, 'sell_document', 'documents');
                if (!empty($document_name)) {
                    $input['document'] = $document_name;
                }

                if ($request->input('additional_expense_value_1') != '') {
                    $input['additional_expense_key_1'] = $request->input('additional_expense_key_1');
                    $input['additional_expense_value_1'] = $request->input('additional_expense_value_1');
                }

                if ($request->input('additional_expense_value_2') != '') {
                    $input['additional_expense_key_2'] = $request->input('additional_expense_key_2');
                    $input['additional_expense_value_2'] = $request->input('additional_expense_value_2');
                }

                if ($request->input('additional_expense_value_3') != '') {
                    $input['additional_expense_key_3'] = $request->input('additional_expense_key_3');
                    $input['additional_expense_value_3'] = $request->input('additional_expense_value_3');
                }

                if ($request->input('additional_expense_value_4') != '') {
                    $input['additional_expense_key_4'] = $request->input('additional_expense_key_4');
                    $input['additional_expense_value_4'] = $request->input('additional_expense_value_4');
                }
                $only_payment = !$is_direct_sale && !auth()->user()->can('sell.update') && auth()->user()->can('edit_pos_payment');

                //if edit pos not allowed and only edit payment allowed
                if ($only_payment) {
                    DB::beginTransaction();
                    $this->onlyUpdatePayment($transaction_before, $input);
                    DB::commit();

                    $can_print_invoice = auth()->user()->can('print_invoice');
                    $invoice_layout_id = $request->input('invoice_layout_id');

                    $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction_before->id, null, false, true, $invoice_layout_id);
                    $msg = trans('purchase.payment_updated_success');

                    $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];

                    return $output;
                }

                //Begin transaction
                DB::beginTransaction();

                $tractionWait = Transaction::findOrFail($id);

                $transaction = $this->transactionUtil->updateSellTransaction($id, $business_id, $input, $invoice_total, $user_id);

                //update service staff timer
                if (!$is_direct_sale && $transaction->status == 'final') {
                    foreach ($input['products'] as $product_line) {
                        if (!empty($product_line['res_service_staff_id'])) {
                            $product = Product::find($product_line['product_id']);

                            if (!empty($product->preparation_time_in_minutes)) {
                                //if quantity not increase skip line
                                $quantity = $this->transactionUtil->num_uf($product_line['quantity']);
                                if (!empty($product_line['transaction_sell_lines_id'])) {
                                    $sl = TransactionSellLine::find($product_line['transaction_sell_lines_id']);

                                    if ($sl->quantity >= $quantity && $sl->res_service_staff_id == $product_line['res_service_staff_id']) {
                                        continue;
                                    }

                                    //if same service staff assigned quantity is only increased quantity
                                    if ($sl->res_service_staff_id == $product_line['res_service_staff_id']) {
                                        $quantity = $quantity - $sl->quantity;
                                    }
                                }

                                $service_staff = User::find($product_line['res_service_staff_id']);

                                $base_time = Carbon::parse($transaction->transaction_date);
                                //is transaction date is past take base time as now
                                if ($base_time->lt(Carbon::now())) {
                                    $base_time = Carbon::now();
                                }

                                //if already assigned set base time as available_at
                                if (!empty($service_staff->available_at) && Carbon::parse($service_staff->available_at)->gt(Carbon::now())) {
                                    $base_time = Carbon::parse($service_staff->available_at);
                                }

                                $total_minutes = $product->preparation_time_in_minutes * $quantity;

                                $service_staff->available_at = $base_time->addMinutes($total_minutes);
                                $service_staff->save();
                            }
                        }
                    }
                }

                //Update Sell lines
                $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], true, $status_before);

                //Update update lines
                $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1 ? true : false;

                $new_sales_order_ids = $transaction->sales_order_ids ?? [];
                $sales_order_ids = array_unique(array_merge($sales_order_ids, $new_sales_order_ids));

                if (!empty($sales_order_ids)) {
                    $this->transactionUtil->updateSalesOrderStatus($sales_order_ids);
                }

                if (!$transaction->is_suspend && !$is_credit_sale) {
                    //Add change return
                    $change_return['amount'] = $input['change_return'] ?? 0;
                    $change_return['is_return'] = 1;
                    if (!empty($input['change_return_id'])) {
                        $change_return['payment_id'] = $input['change_return_id'];
                    }
                    $input['payment'][] = $change_return;

                    if (!$is_direct_sale || auth()->user()->can('sell.payments')) {
                        $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);

                        //Update cash register
                        if (!$is_direct_sale) {
                            $this->cashRegisterUtil->updateSellPayments($status_before, $transaction, $input['payment']);
                        }
                    }
                }

                if ($request->session()->get('business.enable_rp') == 1) {
                    $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, $rp_earned_before, $transaction->rp_redeemed, $rp_redeemed_before);
                }

                Media::uploadMedia($business_id, $transaction, $request, 'shipping_documents', false, 'shipping_document');
                if ($transaction->type == 'sell') {

                    //Update payment status
                    $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);
                    $transaction->payment_status = $payment_status;

                    //Update product stock
                    $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, $input);

                    //Allocate the quantity from purchase and add mapping of
                    //purchase & sell lines in
                    //transaction_sell_lines_purchase_lines table
                    $business_details = $this->businessUtil->getDetails($business_id);
                    $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

                    $business = [
                        'id' => $business_id,
                        'accounting_method' => $request->session()->get('business.accounting_method'),
                        'location_id' => $input['location_id'],
                        'pos_settings' => $pos_settings,
                    ];
                    $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business, $deleted_lines);

                    //Auto send notification
                    $whatsapp_link = $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
                }

                $log_properties = [];
                if (isset($input['repair_completed_on'])) {
                    $completed_on = !empty($input['repair_completed_on']) ? $this->transactionUtil->uf_date($input['repair_completed_on'], true) : null;
                    if ($transaction->repair_completed_on != $completed_on) {
                        $log_properties['completed_on_from'] = $transaction->repair_completed_on;
                        $log_properties['completed_on_to'] = $completed_on;
                    }
                }

                $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);

                Media::uploadMedia($business_id, $transaction, $request, 'documents');

                if ($tractionWait->status == 'draft' && $input['status'] == 'final') {
                    $this->transactionUtil->activityLog($transaction, 'updated_draft', $transaction_before);
                } elseif ($tractionWait->status == 'demand' && $input['status'] == 'final') {
                    $this->transactionUtil->activityLog($transaction, 'updated_demand', $transaction_before);
                } else {
                    $this->transactionUtil->activityLog($transaction, 'edited', $transaction_before);
                }
                SellCreatedOrModified::dispatch($transaction);

                DB::commit();

                if ($request->input('is_save_and_print') == 1) {
                    Log::info('Clinic_sell_controller update with print invoice working');

                    $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);

                    return redirect()->to($url . '?print_on_load=true');
                }

                $msg = __('lang_v1.updated_success');
                $receipt = '';
                $can_print_invoice = auth()->user()->can('print_invoice');
                $invoice_layout_id = $request->input('invoice_layout_id');

                if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                    $msg = trans('sale.draft_added');
                } elseif ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                    $msg = trans('lang_v1.quotation_updated');
                    if (!$is_direct_sale && $can_print_invoice) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
                    } else {
                        $receipt = '';
                    }
                } elseif ($input['status'] == 'final') {
                    $msg = trans('sale.pos_sale_updated');
                    if (!$is_direct_sale && $can_print_invoice) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
                    } else {
                        $receipt = '';
                    }
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt];

                if (!empty($whatsapp_link)) {
                    $output['whatsapp_link'] = $whatsapp_link;
                }
            } else {
                $output = [
                    'success' => 0,
                    'msg' => trans('messages.something_went_wrong'),
                ];
            }
            if ($tractionWait->status == 'demand' && $transaction->status == 'final') {
                $waitlists = ProductWaitlist::where('transaction_id', $id)->get();

                foreach ($waitlists as $waitlist) {
                    $waitlist->status = 'Complete';
                    $waitlist->fulfilled_date = now();
                    $waitlist->save();
                    $waitlist->delete();
                }
            }

            // Handle the 'demand' status and waitlist logic
            if ($input['status'] == 'demand') {
                $this->updateWaitlist($input['products'], $contact_id, $input['location_id'], $transaction->id, $input['sale_note']);

                $output = [
                    'success' => 1,
                    'msg' => __('Product has been updated in the waitlist due to demand status.')
                ];

                return redirect()
                    ->action([\App\Http\Controllers\ProductWaitlistController::class, 'index'])
                    ->with('status', $output);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }
        if ($salePoint === 'clinic') {
            if ($transaction->sub_type == 'consultation') {
                return redirect()->route('all-appointment.index');
            } else {
                return redirect()->route('clinic-sell.index')
                    ->with('status', $output);
            }
        }


        if (!$is_direct_sale) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action([\App\Http\Controllers\SellController::class, 'getQuotations'])
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action([\App\Http\Controllers\SellController::class, 'getDrafts'])
                        ->with('status', $output);
                }
            } else {
                if (!empty($transaction->sub_type) && $transaction->sub_type == 'repair') {
                    return redirect()
                        ->action([\Modules\Repair\Http\Controllers\RepairController::class, 'index'])
                        ->with('status', $output);
                }

                if ($transaction->type == 'sales_order') {
                    return redirect()
                        ->action([\App\Http\Controllers\SalesOrderController::class, 'index'])
                        ->with('status', $output);
                }

                return redirect()
                    ->action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'index'])
                    ->with('status', $output);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('clinic.sell.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                //Begin transaction
                DB::beginTransaction();

                $output = $this->transactionUtil->deleteSale($business_id, $id);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output['success'] = false;
                $output['msg'] = trans('messages.something_went_wrong');
            }

            return $output;
        }
    }
    public function getProductRow($variation_id, $location_id, $status)
    {
        $output = [];

        try {
            $row_count = request()->get('product_row');
            $row_count = $row_count + 1;
            $quantity = request()->get('quantity', 1);
            $weighing_barcode = request()->get('weighing_scale_barcode', null);

            $is_direct_sell = false;
            if (request()->get('is_direct_sell') == 'true') {
                $is_direct_sell = true;
            }

            if ($variation_id == 'null' && !empty($weighing_barcode)) {
                $product_details = $this->__parseWeighingBarcode($weighing_barcode);
                if ($product_details['success']) {
                    $variation_id = $product_details['variation_id'];
                    $quantity = $product_details['qty'];
                } else {
                    $output['success'] = false;
                    $output['msg'] = $product_details['msg'];
                    return $output;
                }
            }

            // Pass the status to getSellLineRow method
            $output = $this->getSellLineRow($variation_id, $location_id, $quantity, $row_count, $is_direct_sell, null, $status);

            if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                $variation = Variation::find($variation_id);
                $business_id = request()->session()->get('user.business_id');
                $this_product = Product::where('business_id', $business_id)
                    ->with([
                        'modifier_sets' => function ($query) {
                            $query->withPivot('modifier_limit');
                        }
                    ])
                    ->find($variation->product_id);
                if (count($this_product->modifier_sets) > 0) {
                    $product_ms = $this_product->modifier_sets;
                    $output['html_modifier'] = view('restaurant.product_modifier_set.modifier_for_product')
                        ->with(compact('product_ms', 'row_count'))->render();
                }
            }
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output['success'] = false;
            $output['msg'] = __('lang_v1.item_out_of_stock');
        }

        return $output;
    }
    private function __parseWeighingBarcode($scale_barcode)
    {
        $business_id = session()->get('user.business_id');

        $scale_setting = session()->get('business.weighing_scale_setting');

        $error_msg = trans('messages.something_went_wrong');

        //Check for prefix.
        if ((strlen($scale_setting['label_prefix']) == 0) || Str::startsWith($scale_barcode, $scale_setting['label_prefix'])) {
            $scale_barcode = substr($scale_barcode, strlen($scale_setting['label_prefix']));

            //Get product sku, trim left side 0
            $sku = ltrim(substr($scale_barcode, 0, $scale_setting['product_sku_length'] + 1), '0');

            //Get quantity integer
            $qty_int = substr($scale_barcode, $scale_setting['product_sku_length'] + 1, $scale_setting['qty_length'] + 1);

            //Get quantity decimal
            $qty_decimal = '0.' . substr($scale_barcode, $scale_setting['product_sku_length'] + $scale_setting['qty_length'] + 2, $scale_setting['qty_length_decimal'] + 1);

            $qty = (float) $qty_int + (float) $qty_decimal;

            //Find the variation id
            $result = $this->productUtil->filterProduct($business_id, $sku, null, false, null, [], ['sub_sku'], false, 'exact')->first();

            if (!empty($result)) {
                return [
                    'variation_id' => $result->variation_id,
                    'qty' => $qty,
                    'success' => true,
                ];
            } else {
                $error_msg = trans('lang_v1.sku_not_match', ['sku' => $sku]);
            }
        } else {
            $error_msg = trans('lang_v1.prefix_did_not_match');
        }

        return [
            'success' => false,
            'msg' => $error_msg,
        ];
    }
    private function getSellLineRow($variation_id, $location_id, $quantity, $row_count, $is_direct_sell, $so_line = null, $status = null)
    {
        $business_id = request()->session()->get('user.business_id');
        $business_details = $this->businessUtil->getDetails($business_id);

        // Check for weighing scale barcode
        $weighing_barcode = request()->get('weighing_scale_barcode');

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        // Determine whether to check quantity based on status and settings
        $check_qty = $status === 'demand' ? false : (!empty($pos_settings['allow_overselling']) ? false : true);

        $is_sales_order = request()->has('is_sales_order') && request()->input('is_sales_order') == 'true';
        $is_draft = request()->has('is_draft') && request()->input('is_draft') == 'true';

        if ($is_sales_order || !empty($so_line) || $is_draft) {
            $check_qty = false;
        }

        if (request()->input('disable_qty_alert') === 'true') {
            $pos_settings['allow_overselling'] = true;
        }

        $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id, $check_qty);

        if (!isset($product->quantity_ordered)) {
            $product->quantity_ordered = $quantity;
        }

        $product->secondary_unit_quantity = !isset($product->secondary_unit_quantity) ? 0 : $product->secondary_unit_quantity;
        $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available, false, null, true);

        $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id, false, $product->product_id);

        // Get customer group and change the price accordingly
        $customer_id = request()->get('customer_id', null);
        $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_id);
        $percent = (empty($cg) || empty($cg->amount) || $cg->price_calculation_type != 'percentage') ? 0 : $cg->amount;
        $product->default_sell_price = round($product->default_sell_price + ($percent * $product->default_sell_price / 100));
        $product->sell_price_inc_tax = round($product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100));

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);

        $enabled_modules = $this->transactionUtil->allModulesEnabled();

        // Get lot number dropdown if enabled
        $lot_numbers = [];
        if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
            $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, true);
            foreach ($lot_number_obj as $lot_number) {
                $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                $lot_numbers[] = $lot_number;
            }
        }
        $product->lot_numbers = $lot_numbers;

        $purchase_line_id = request()->get('purchase_line_id');

        $price_group = request()->input('price_group');
        if (!empty($price_group)) {
            $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $price_group, $product->tax_id);

            if (!empty($variation_group_prices['price_inc_tax'])) {
                $product->sell_price_inc_tax = round(($variation_group_prices['price_inc_tax']));
                $product->default_sell_price = round(($variation_group_prices['price_exc_tax']));
            }
        }

        $warranties = $this->__getwarranties();

        $output['success'] = true;
        $output['enable_sr_no'] = $product->enable_sr_no;

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters_enabled = true;
            $waiters = $this->productUtil->serviceStaffDropdown($business_id, $location_id);
        }

        $last_sell_line = null;
        if ($is_direct_sell) {
            $last_sell_line = $this->getLastSellLineForCustomer($variation_id, $customer_id, $location_id);
        }

        // Fetch the last session number for the given product/variation and contact
        $lastSession = SessionDetail::where('variation_id', $variation_id)
            ->where('contact_id', $customer_id)
            ->orderByDesc('session_no')
            ->value('session_no');

        Log::info(['lastSession' => $lastSession, 'customer_id' => $customer_id, 'variation_id' => $variation_id]);
        // Calculate the next session number
        $current_session = $lastSession ? $lastSession + 1 : 1;

        if (request()->get('type') == 'sell-return') {
            $output['html_content'] = view('clinic::sell_return.partials.product_row')
                ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules', 'sub_units', 'nextSession'))
                ->render();
        } else {
            $is_cg = !empty($cg->id);

            $discount = $this->productUtil->getProductDiscount($product, $business_id, $location_id, $is_cg, $price_group, $variation_id);
            // Calculate discounts for modifiers
            // Log::info($product->modifier_products);
            // $modifier_discounts = [];
            // if (!empty($product->modifier_products)) {
            //     foreach ($product->modifier_products as $modifier) {
            //         $modifier_discount = $this->productUtil->getProductDiscount($modifier, $business_id, $location_id, $is_cg, $price_group, $modifier->variation_id);
            //         Log::info('call this');
            //     }
            // }

            if ($is_direct_sell) {
                $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
                $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');
            } else {
                $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
                $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
            }
            if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                $variation = Variation::find($variation_id);
                $business_id = request()->session()->get('user.business_id');
                $this_product = Product::where('business_id', $business_id)
                    ->with([
                        'modifier_sets' => function ($query) {
                            $query->withPivot('modifier_limit');
                        }
                    ])
                    ->find($variation->product_id);
            }
            $mfg_total = 0;
            foreach ($this_product->modifier_sets as $modifier_set) {
                foreach ($modifier_set->variations as $modifier) {
                    $mfg_total += $modifier->sell_price_inc_tax;
                    Log::info([
                        "product" => $mfg_total,
                    ]);
                }
            }
            $output['html_content'] = view('clinic::sell.product_row')
                ->with(compact('product', 'mfg_total', 'row_count', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'sub_units', 'discount', 'waiters', 'edit_discount', 'edit_price', 'purchase_line_id', 'warranties', 'quantity', 'is_direct_sell', 'so_line', 'is_sales_order', 'last_sell_line', 'current_session'))
                ->render();
        }

        return $output;
    }
    private function __getwarranties()
    {
        $business_id = session()->get('user.business_id');
        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;
        $warranties = $is_warranty_enabled ? Warranty::forDropdown($business_id) : [];

        return $warranties;
    }
    private function getLastSellLineForCustomer($variation_id, $customer_id, $location_id)
    {
        $sell_line = TransactionSellLine::join('transactions as t', 't.id', '=', 'transaction_sell_lines.transaction_id')
            ->where('t.location_id', $location_id)
            ->where('t.contact_id', $customer_id)
            ->where('t.type', 'sell')
            ->where('t.status', 'final')
            ->where('transaction_sell_lines.variation_id', $variation_id)
            ->orderBy('t.transaction_date', 'desc')
            ->select('transaction_sell_lines.*')
            ->first();

        return $sell_line;
    }
    public function getDraftDatables()
    {

        if (!auth()->user()->can('clinic.sell.draft_view_all')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $business = Business::findOrFail($business_id);
            $common_settings = $business->common_settings;
            $clinic_location = $common_settings['clinic_location'] ?? null;
            $is_quotation = request()->input('is_quotation', 0);

            $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                    $join->on('transactions.id', '=', 'tsl.transaction_id')
                        ->whereNull('tsl.parent_sell_line_id');
                })
                ->where('transactions.business_id', $business_id)
                ->where('transactions.location_id', $clinic_location)
                ->where('transactions.type', 'sell')
                ->whereIn('transactions.sub_type', ['test', 'therapy', 'ipd', 'consultation'])
                ->where('transactions.status', 'draft')
                ->select(
                    'transactions.id',
                    'transactions.additional_notes',
                    'transactions.appointment_date',
                    'transactions.sub_type',
                    'transaction_date',
                    'invoice_no',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.supplier_business_name',
                    'bl.name as business_location',
                    'is_direct_sale',
                    'sub_status',
                    DB::raw('COUNT( DISTINCT tsl.id) as total_items'),
                    DB::raw('SUM(tsl.quantity) as total_quantity'),
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by"),
                    'transactions.is_export'
                );

            if ($is_quotation == 1) {
                $sells->where('transactions.sub_status', 'quotation');

                if (!auth()->user()->can('quotation.view_all') && auth()->user()->can('quotation.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            } else {
                if (!auth()->user()->can('draft.view_all') && auth()->user()->can('draft.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $sells->whereDate('transaction_date', '>=', $start)
                    ->whereDate('transaction_date', '<=', $end);
            }

            if (request()->has('location_id')) {
                $location_id = request()->get('location_id');
                if (!empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (!empty($created_by)) {
                    $sells->where('transactions.created_by', $created_by);
                }
            }

            if (!empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }

            if ($is_woocommerce) {
                $sells->addSelect('transactions.woocommerce_order_id');
            }

            if (request()->woocommerce_order == 1) {
                $sells->whereNotNull('transactions.woocommerce_order_id');
            }

            $sells->groupBy('transactions.id');

            return Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                    data-toggle="dropdown" aria-expanded="false">' .
                            __('messages.actions') .
                            '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                                    </span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li>
                                    <a href="#" data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->id]) . '" class="btn-modal" data-container=".view_modal">
                                        <i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '
                                    </a>
                                    </li>';

                        if (auth()->user()->can('clinic.sell.draft_edit')) {
                            if ($row->is_direct_sale == 1) {
                                $html .= '<li>
                                            <a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '">
                                                <i class="fas fa-edit"></i>' . __('messages.edit') . '
                                            </a>
                                        </li>';
                            } else {
                                $html .= '<li>
                                            <a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '">
                                                <i class="fas fa-edit"></i>' . __('messages.edit') . '
                                            </a>
                                        </li>';
                            }
                        }

                        $html .= '<li>
                                    <a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i>' . __('messages.print') . '</a>
                                </li>';

                        if (config('constants.enable_download_pdf')) {
                            $sub_status = $row->sub_status == 'proforma' ? 'proforma' : '';
                            $html .= '<li>
                                        <a href="' . route('quotation.downloadPdf', ['id' => $row->id, 'sub_status' => $sub_status]) . '" target="_blank">
                                            <i class="fas fa-print" aria-hidden="true"></i>' . __('lang_v1.download_pdf') . '
                                        </a>
                                    </li>';
                        }

                        if ((auth()->user()->can('clinic.sell.draft_sell_convert'))) {
                            $html .= '<li>
                                        <a href="' . action([\App\Http\Controllers\SellPosController::class, 'convertToInvoice'], [$row->id]) . '" class="convert-draft"><i class="fas fa-sync-alt"></i>' . __('lang_v1.convert_to_invoice') . '</a>
                                    </li>';
                        }

                        if ($row->sub_status != 'proforma') {
                            $html .= '<li>
                                        <a href="' . action([\App\Http\Controllers\SellPosController::class, 'convertToProforma'], [$row->id]) . '" class="convert-to-proforma"><i class="fas fa-sync-alt"></i>' . __('lang_v1.convert_to_proforma') . '</a>
                                    </li>';
                        }

                        if (auth()->user()->can('clinic.sell.draft_delete')) {
                            $html .= '<li>
                                <a href="' . action([\App\Http\Controllers\SellPosController::class, 'destroy'], [$row->id]) . '" class="delete-sale"><i class="fas fa-trash"></i>' . __('messages.delete') . '</a>
                                </li>';
                        }

                        if ($row->sub_status == 'quotation') {
                            $html .= '<li>
                                        <a href="' . action([\App\Http\Controllers\SellPosController::class, 'copyQuotation'], [$row->id]) . '" 
                                        class="copy_quotation"><i class="fas fa-copy"></i>' .
                                __("lang_v1.copy_quotation") . '</a>
                                    </li>
                                    <li>
                                        <a href="#" data-href="' . action("\App\Http\Controllers\NotificationController@getTemplate", ["transaction_id" => $row->id, "template_for" => "new_quotation"]) . '" class="btn-modal" data-container=".view_modal"><i class="fa fa-envelope" aria-hidden="true"></i>' . __("lang_v1.new_quotation_notification") . '
                                        </a>
                                    </li>';

                            $html .= '<li>
                                        <a href="' . action("\App\Http\Controllers\SellPosController@showInvoiceUrl", [$row->id]) . '" class="view_invoice_url"><i class="fas fa-eye"></i>' . __("lang_v1.view_quote_url") . '</a>
                                    </li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn('invoice_no', function ($row) {
                    $invoice_no = $row->invoice_no;
                    if (!empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }

                    if ($row->sub_status == 'proforma') {
                        $invoice_no .= '<br><span class="label bg-gray">' . __('lang_v1.proforma_invoice') . '</span>';
                    }

                    if (!empty($row->is_export)) {
                        $invoice_no .= '</br><small class="label label-default no-print" title="' . __('lang_v1.export') . '">' . __('lang_v1.export') . '</small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('mobile', function ($row) {
                    $phoneId = 'phone_' . $row->id;
                    $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';

                    if (auth()->user()->can('patient.phone_number')) {
                        return $row->mobile;
                    } else {
                        return $phoneIcon .
                            ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->mobile . '</span>';
                    }
                })
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('additional_notes', '{{$additional_notes}}')
                ->editColumn('appointment_date', '{{@format_date($appointment_date)}}')
                ->editColumn('total_items', '{{@format_quantity($total_items)}}')
                ->editColumn('total_quantity', '{{@format_quantity($total_quantity)}}')
                ->addColumn('conatct_name', '@if(!empty($supplier_business_name)) {{$supplier_business_name}}, <br>@endif {{$name}}')
                ->filterColumn('conatct_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('contacts.name', 'like', "%{$keyword}%")
                            ->orWhere('contacts.supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('added_by', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('sell.view')) {
                            return action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->id]);
                        } else {
                            return '';
                        }
                    },
                ])
                ->rawColumns(['action', 'invoice_no', 'transaction_date', 'conatct_name', 'additional_notes', 'appointment_date', 'mobile'])
                ->make(true);
        }
        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $sales_representative = User::forDropdown($business_id, false, false, true);

        return view('clinic::sell.draft_sell', compact('business_locations', 'customers', 'sales_representative'));
    }
    public function draftEdit($id)
    {

        if (!auth()->user()->can('clinic.sell.draft_edit')) {
            abort(403, 'Unauthorized action.');
        }
        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', [
                    'success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days]),
                ]);
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('lang_v1.return_exist'),
            ]);
        }

        $business_id = request()->session()->get('user.business_id');

        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::with('reference')->where('business_id', $business_id)
            ->with(['price_group', 'types_of_service', 'media', 'media.uploaded_by_user'])
            ->whereIn('type', ['sell', 'sales_order'])
            ->findorfail($id);

        if ($transaction->type == 'sales_order' && !auth()->user()->can('so.update')) {
            abort(403, 'Unauthorized action.');
        }

        $location_id = $transaction->location_id;
        $location_printer_type = BusinessLocation::find($location_id)->receipt_printer_type;

        $sell_details = TransactionSellLine::join(
            'products AS p',
            'transaction_sell_lines.product_id',
            '=',
            'p.id'
        )
            ->join(
                'variations AS variations',
                'transaction_sell_lines.variation_id',
                '=',
                'variations.id'
            )
            ->join(
                'product_variations AS pv',
                'variations.product_variation_id',
                '=',
                'pv.id'
            )
            ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                $join->on('variations.id', '=', 'vld.variation_id')
                    ->where('vld.location_id', '=', $location_id);
            })
            ->leftjoin('units', 'units.id', '=', 'p.unit_id')
            ->leftjoin('units as u', 'p.secondary_unit_id', '=', 'u.id')
            ->where('transaction_sell_lines.transaction_id', $id)
            ->with(['warranties', 'so_line'])
            ->select(
                DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                'p.id as product_id',
                'p.enable_stock',
                'p.name as product_actual_name',
                'p.type as product_type',
                'pv.name as product_variation_name',
                'pv.is_dummy as is_dummy',
                'variations.name as variation_name',
                'variations.sub_sku',
                'p.barcode_type',
                'p.enable_sr_no',
                'variations.id as variation_id',
                'units.short_name as unit',
                'units.allow_decimal as unit_allow_decimal',
                'u.short_name as second_unit',
                'transaction_sell_lines.secondary_unit_quantity',
                'transaction_sell_lines.tax_id as tax_id',
                'transaction_sell_lines.item_tax as item_tax',
                'transaction_sell_lines.unit_price as default_sell_price',
                'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                'transaction_sell_lines.id as transaction_sell_lines_id',
                'transaction_sell_lines.id',
                'transaction_sell_lines.quantity as quantity_ordered',
                'transaction_sell_lines.sell_line_note as sell_line_note',
                'transaction_sell_lines.parent_sell_line_id',
                'transaction_sell_lines.lot_no_line_id',
                'transaction_sell_lines.line_discount_type',
                'transaction_sell_lines.line_discount_amount',
                'transaction_sell_lines.res_service_staff_id',
                'units.id as unit_id',
                'transaction_sell_lines.sub_unit_id',
                'transaction_sell_lines.so_line_id',
                DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
            )
            ->get();

        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                //If modifier or combo sell line then unset
                if (!empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    if ($transaction->status != 'final') {
                        $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                        $sell_details[$key]->qty_available = $actual_qty_avlbl;
                        $value->qty_available = $actual_qty_avlbl;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->transactionUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;

                    if (!empty($value->sub_unit_id)) {
                        $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                        $sell_details[$key] = $value;
                    }

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'modifier')
                            ->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }

                    //Get details of combo items
                    if ($sell_details[$key]->product_type == 'combo') {
                        $sell_line_combos = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)
                            ->where('children_type', 'combo')
                            ->get()
                            ->toArray();
                        if (!empty($sell_line_combos)) {
                            $sell_details[$key]->combo_products = $sell_line_combos;
                        }

                        //calculate quantity available if combo product
                        $combo_variations = [];
                        foreach ($sell_line_combos as $combo_line) {
                            $combo_variations[] = [
                                'variation_id' => $combo_line['variation_id'],
                                'quantity' => $combo_line['quantity'] / $sell_details[$key]->quantity_ordered,
                                'unit_id' => null,
                            ];
                        }
                        $sell_details[$key]->qty_available =
                            $this->productUtil->calculateComboQuantity($location_id, $combo_variations);

                        if ($transaction->status == 'final') {
                            $sell_details[$key]->qty_available = $sell_details[$key]->qty_available + $sell_details[$key]->quantity_ordered;
                        }

                        $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($sell_details[$key]->qty_available, false, null, true);
                    }
                }
            }
        }

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id);
        }

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        $transaction->transaction_date = $this->transactionUtil->format_date($transaction->transaction_date, true);

        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }

        $invoice_schemes = [];
        $default_invoice_schemes = null;

        if ($transaction->status == 'draft') {
            $invoice_schemes = InvoiceScheme::forDropdown($business_id);
            $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        }

        $redeem_details = [];
        if (request()->session()->get('business.enable_rp') == 1) {
            $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }
        $types_of_service = [];
        if ($this->moduleUtil->isModuleEnabled('types_of_service')) {
            $types_of_service = TypesOfService::forDropdown($business_id);
        }
        $edit_discount = auth()->user()->can('edit_product_discount_from_sale_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_sale_screen');

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $common_settings = session()->get('business.common_settings');
        $is_warranty_enabled = !empty($common_settings['enable_product_warranty']) ? true : false;
        $warranties = $is_warranty_enabled ? Warranty::forDropdown($business_id) : [];

        $statuses = Transaction::clinic_sell_statuses();

        $is_order_request_enabled = false;
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        if ($is_crm) {
            $crm_settings = Business::where('id', auth()->user()->business_id)
                ->value('crm_settings');
            $crm_settings = !empty($crm_settings) ? json_decode($crm_settings, true) : [];

            if (!empty($crm_settings['enable_order_request'])) {
                $is_order_request_enabled = true;
            }
        }

        $sales_orders = [];
        if (!empty($pos_settings['enable_sales_order']) || $is_order_request_enabled) {
            $sales_orders = Transaction::where('business_id', $business_id)
                ->where('type', 'sales_order')
                ->where('contact_id', $transaction->contact_id)
                ->where(function ($q) use ($transaction) {
                    $q->where('status', '!=', 'completed');

                    if (!empty($transaction->sales_order_ids)) {
                        $q->orWhereIn('id', $transaction->sales_order_ids);
                    }
                })
                ->pluck('invoice_no', 'id');
        }

        $payment_types = $this->transactionUtil->payment_types($transaction->location_id, false, $business_id);

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $change_return = $this->dummyPaymentLine;

        $customer_due = $this->transactionUtil->getContactDue($transaction->contact_id, $transaction->business_id);

        $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $expiring_soon = $common_settings['expiring_soon'] ?? 30;
        $expiring_later = $common_settings['expiring_later'] ?? 90;
        $pos_settings = json_decode($business->pos_settings, true);

        if (auth()->user()->can('discount_no_limit') || auth()->user()->can('superadmin') || auth()->user()->can('admin')) {
            $enable_discount_rules = [];
        } else {
            $enable_discount_rules = $pos_settings['enable_discount_rules'] ?? [];
        }

        $discount_rules = $pos_settings['discount_rules'] ?? [];
        // Set session values
        session([
            'expiring_soon' => $expiring_soon,
            'expiring_later' => $expiring_later,
        ]);
        $doctors = DoctorProfile::all()->mapWithKeys(function ($doctor) {
            return [$doctor->user_id => $doctor->first_name . ' ' . $doctor->last_name];
        });
        $diseases = Problem::all();
        $today = Carbon::now()->format('d-m-Y H:i:s');
        return view('clinic::sell.partials.draft_edit')
            ->with(compact('business_details', 'doctors', 'taxes', 'sell_details', 'transaction', 'commission_agent', 'types', 'customer_groups', 'pos_settings', 'waiters', 'invoice_schemes', 'default_invoice_schemes', 'redeem_details', 'edit_discount', 'edit_price', 'shipping_statuses', 'warranties', 'statuses', 'sales_orders', 'payment_types', 'accounts', 'payment_lines', 'change_return', 'is_order_request_enabled', 'customer_due', 'users', 'discount_rules', 'enable_discount_rules', 'types_of_service', 'diseases', 'today'));
    }
    public function viewMedia($model_id)
    {
        if (request()->ajax()) {
            $model_type = request()->input('model_type');
            $business_id = request()->session()->get('user.business_id');

            $query = Media::where('business_id', $business_id)
                ->where('model_id', $model_id)
                ->where('model_type', $model_type);

            $title = __('lang_v1.attachments');
            if (!empty(request()->input('model_media_type'))) {
                $query->where('model_media_type', request()->input('model_media_type'));
                $title = __('lang_v1.shipping_documents');
            }

            $medias = $query->get();

            return view('clinic::sell.partials.view_media')->with(compact('medias', 'title'));
        }
    }
    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null,
        $is_package_slip = false,
        $from_pos_screen = true,
        $invoice_layout_id = null,
        $is_delivery_note = false
    ) {
        $output = [
            'is_enabled' => false,
            'print_type' => 'browser',
            'html_content' => null,
            'printer_config' => [],
            'data' => [],
        ];

        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);

        if ($from_pos_screen && $location_details->print_receipt_on_invoice != 1) {
            return $output;
        }
        //Check if printing of invoice is enabled or not.
        //If enabled, get print type.
        $output['is_enabled'] = true;

        $invoice_layout_id = !empty($invoice_layout_id) ? $invoice_layout_id : $location_details->invoice_layout_id;
        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $invoice_layout_id);

        //Check if printer setting is provided.
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

        $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);

        $currency_details = [
            'symbol' => $business_details->currency_symbol,
            'thousand_separator' => $business_details->thousand_separator,
            'decimal_separator' => $business_details->decimal_separator,
        ];
        $receipt_details->currency = $currency_details;

        if ($is_package_slip) {
            $output['html_content'] = view('sale_pos.receipts.packing_slip', compact('receipt_details'))->render();

            return $output;
        }

        if ($is_delivery_note) {
            $output['html_content'] = view('sale_pos.receipts.delivery_note', compact('receipt_details'))->render();

            return $output;
        }

        $output['print_title'] = $receipt_details->invoice_no;
        //If print type browser - return the content, printer - return printer config data, and invoice format config
        if ($receipt_printer_type == 'printer') {
            $output['print_type'] = 'printer';
            $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
            $output['data'] = $receipt_details;
        } else {
            $layout = !empty($receipt_details->design) ? 'sale_pos.receipts.' . $receipt_details->design : 'sale_pos.receipts.classic';

            $output['html_content'] = view($layout, compact('receipt_details'))->render();
        }

        return $output;
    }


 public function sellByDate($id)
{
    $product = Product::find($id);
    if (!$product) abort(404, "Product not found");

    $start_date_raw = request()->get('start_date');
    $end_date_raw = request()->get('end_date');

    // Handle date parsing
    if (!$start_date_raw || !$end_date_raw) {
        $start_date = $end_date = now()->format('Y-m-d');
    } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date_raw)) {
        $start_date = $start_date_raw;
        $end_date = $end_date_raw;
    } else {
        $start_date = \Carbon\Carbon::createFromFormat('d-m-Y', $start_date_raw)->format('Y-m-d');
        $end_date = \Carbon\Carbon::createFromFormat('d-m-Y', $end_date_raw)->format('Y-m-d');
    }

    Log::info('Using dates', ['start_date' => $start_date, 'end_date' => $end_date]);

    $sellDataQuery = TransactionSellLine::query()
        ->leftJoin('transactions', 'transaction_sell_lines.transaction_id', '=', 'transactions.id')
        ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
        ->where('transaction_sell_lines.product_id', $id)
        ->whereDate('transactions.transaction_date', '>=', $start_date)
        ->whereDate('transactions.transaction_date', '<=', $end_date);

    Log::info('SellData SQL:', [
        'sql' => $sellDataQuery->toSql(),
        'bindings' => $sellDataQuery->getBindings()
    ]);

    $sellData = $sellDataQuery->select(
        'transactions.invoice_no',
        DB::raw("CONCAT(contacts.first_name, ' ', IFNULL(contacts.last_name, '')) as customer_name"),
        'transaction_sell_lines.quantity',
        'transaction_sell_lines.unit_price_inc_tax'
    )->get();

    Log::info('Fetched sellData count:', [$sellData->count()]);

    $totalQty = $sellData->sum('quantity');
    $totalAmount = $sellData->sum(fn($row) => $row->quantity * $row->unit_price_inc_tax);

    return view('clinic::sell.sell_by_date', compact(
        'sellData',
        'product',
        'totalQty',
        'totalAmount',
        'start_date',
        'end_date'
    ));
}

}
