<?php

namespace Modules\Clinic\Http\Controllers\report;

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

use App\Contact;

use Spatie\Activitylog\Models\Activity;
use App\TransactionSellLine;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Modules\Clinic\Utils\ClinicSellUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\NotificationUtil;


class TestReportController extends Controller
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


    public function index()
    {
        Log::info('test report index calling to clinic');
        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (! $is_admin && ! auth()->user()->hasAnyPermission(['clinic.sell.view', 'clinic.sell.create'])) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        if (request()->ajax()) {

            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $shipping_statuses = $this->transactionUtil->shipping_statuses();

            $sale_type = ! empty(request()->input('sale_type')) ? request()->input('sale_type') : 'sell';
            $sub_type = ['test'];
            $sells = $this->clinicSellUtil->getListSells($business_id, $sale_type, $sub_type);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (! empty($created_by)) {
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
            if (! auth()->user()->can('direct_sell.view')) {
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

            if (! $is_admin && ! $only_shipments && $sale_type != 'sales_order') {
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

            if (! empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
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
                if (! empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (! empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.rp_earned')
                        ->orWhere('transactions.rp_redeemed', '>', 0);
                });
            }

            if (! empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
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
                if (! empty($commission_agent)) {
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if (! empty(request()->input('source'))) {
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

            if (! empty(request()->list_for) && request()->list_for == 'service_staff_report') {
                $sells->whereNotNull('transactions.res_waiter_id');
            }

            if (! empty(request()->res_waiter_id)) {
                $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
            }

            if (! empty(request()->input('sub_type'))) {
                $sells->where('transactions.sub_type', request()->input('sub_type'));
            }

            if (! empty(request()->input('created_by'))) {
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (! empty(request()->input('status'))) {
                $sells->where('transactions.status', request()->input('status'));
            }

            if (! empty(request()->input('sales_cmsn_agnt'))) {
                $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
            }

            if (! empty(request()->input('service_staffs'))) {
                $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
            }

            $only_pending_shipments = request()->only_pending_shipments == 'true' ? true : false;
            if ($only_pending_shipments) {
                $sells->where('transactions.shipping_status', '!=', 'delivered')
                    ->whereNotNull('transactions.shipping_status');
                $only_shipments = true;
            }

            if (! empty(request()->input('shipping_status'))) {
                $sells->where('transactions.shipping_status', request()->input('shipping_status'));
            }

            if (! empty(request()->input('for_dashboard_sales_order'))) {
                $sells->whereIn('transactions.status', ['partial', 'ordered'])
                    ->orHavingRaw('so_qty_remaining > 0');
            }

            if ($sale_type == 'sales_order') {
                if (! auth()->user()->can('so.view_all') && auth()->user()->can('so.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            }

            if (! empty(request()->input('delivery_person'))) {
                $sells->where('transactions.delivery_person', request()->input('delivery_person'));
            }
            $sells->groupBy('transactions.id');

            if (! empty(request()->suspended)) {
                $transaction_sub_type = request()->get('transaction_sub_type');
                if (! empty($transaction_sub_type)) {
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
            if (! empty($with)) {
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
                        if (! $only_shipments) {
                            if ($row->is_direct_sale == 0) {
                                if (auth()->user()->can('clinic.sell.edit')) {
                                    $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                                }
                            } elseif ($row->type == 'sales_order') {
                                if (auth()->user()->can('clinic.sell.edit')) {
                                    $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                                }
                            } else {
                                if (auth()->user()->can('clinic.sell.edit')) {
                                    $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                                }
                            }

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

                            if (! empty($row->shipping_status)) {
                                $html .= '<li><a href="' . route('packing.downloadPdf', [$row->id]) . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.download_paking_pdf') . '</a></li>';
                            }
                        }

                        if (auth()->user()->can('clinic.sell.view')) {
                            if (! empty($row->document)) {
                                $document_name = ! empty(explode('_', $row->document, 2)[1]) ? explode('_', $row->document, 2)[1] : $row->document;
                                $html .= '<li><a href="' . url('uploads/documents/' . $row->document) . '" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __('purchase.download_document') . '</a></li>';
                                if (isFileImage($document_name)) {
                                    $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) . '" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>' . __('lang_v1.view_document') . '</a></li>';
                                }
                            }
                        }

                        if ($row->type == 'sell' || $row->sub_type == 'test' || $row->sub_type == 'therapy') {
                            if (auth()->user()->can('clinic.sell.print_invoice')) {
                                $html .= '<li><a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.print_invoice') . '</a></li>';
                            }
                            $html .= '<li class="divider"></li>';
                            if (! $only_shipments) {
                                if (auth()->user()->can('clinic.sell.add_payment')) {
                                    if ($row->payment_status != 'paid') {
                                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicTransactionController::class, 'addPayment'], [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.add_payment') . '</a></li>';
                                    }

                                    $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicTransactionController::class, 'show'], [$row->id]) . '" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.view_payments') . '</a></li>';
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
                        $discount = ! empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (! empty($discount) && $row->discount_type == 'percentage') {
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
                        return $row->sub_type;
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
                    if (! empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $return_due_html .= '<a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="sell_return_due" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }

                    return $return_due_html;
                })
                ->editColumn('invoice_no', function ($row) use ($is_crm) {
                    $Edited = Transaction::where('id', $row->id)->first();
                    $invoice_no = $row->invoice_no;
                    if (! empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (! empty($row->return_exists)) {
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
                    if (! empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (! empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (! empty($row->is_export)) {
                        $invoice_no .= '</br><small class="label label-default no-print" title="' . __('lang_v1.export') . '">' . __('lang_v1.export') . '</small>';
                    }

                    if ($is_crm && ! empty($row->crm_is_order_request)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-yellow label-round no-print" title="' . __('crm::lang.order_request') . '"><i class="fas fa-tasks"></i></small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
                    $status_color = ! empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                    $status = ! empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action([\App\Http\Controllers\SellController::class, 'editShipping'], [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color . '">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

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
                ->addColumn('mobile', function ($row) {
                    $phoneId = 'phone_' . $row->id;
                    $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';
                    
                    if (auth()->user()->can('patient.phone_number')) {
                        return $row->mobile;
                    } else {
                        return $phoneIcon . 
                            '<span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->mobile . '</span>';
                    }
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

                    $html = ! empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

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
                            return  action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->id]);
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
        if (! empty($is_cmsn_agent_enabled)) {
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
            // Add more payment methods as needed
        ];

       

        return view('clinic::report.test_report')
            ->with(compact('business_locations', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses', 'sources', 'payment_types'));
    }
    public function todayInvoiceReport()
    {
        Log::info('test report todayInvoiceReport calling to clinic');
        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (! $is_admin && ! auth()->user()->hasAnyPermission(['clinic.sell.view', 'clinic.sell.create'])) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        if (request()->ajax()) {
            Log::info('test report todayInvoiceReport calling to Ajax');

            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $shipping_statuses = $this->transactionUtil->shipping_statuses();

            $sale_type = ! empty(request()->input('sale_type')) ? request()->input('sale_type') : 'sell';
            $sub_type = ['test'];
            $sells = $this->clinicSellUtil->getSellsListToday($business_id, $sale_type, $sub_type);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (! empty($created_by)) {
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
            if (! auth()->user()->can('direct_sell.view')) {
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

            if (! $is_admin && ! $only_shipments && $sale_type != 'sales_order') {
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

            if (! empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
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
                if (! empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (! empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.rp_earned')
                        ->orWhere('transactions.rp_redeemed', '>', 0);
                });
            }

            if (! empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
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
                if (! empty($commission_agent)) {
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if (! empty(request()->input('source'))) {
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

            if (! empty(request()->list_for) && request()->list_for == 'service_staff_report') {
                $sells->whereNotNull('transactions.res_waiter_id');
            }

            if (! empty(request()->res_waiter_id)) {
                $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
            }

            if (! empty(request()->input('sub_type'))) {
                $sells->where('transactions.sub_type', request()->input('sub_type'));
            }

            if (! empty(request()->input('created_by'))) {
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (! empty(request()->input('status'))) {
                $sells->where('transactions.status', request()->input('status'));
            }

            if (! empty(request()->input('sales_cmsn_agnt'))) {
                $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
            }

            if (! empty(request()->input('service_staffs'))) {
                $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
            }

            $only_pending_shipments = request()->only_pending_shipments == 'true' ? true : false;
            if ($only_pending_shipments) {
                $sells->where('transactions.shipping_status', '!=', 'delivered')
                    ->whereNotNull('transactions.shipping_status');
                $only_shipments = true;
            }

            if (! empty(request()->input('shipping_status'))) {
                $sells->where('transactions.shipping_status', request()->input('shipping_status'));
            }

            if (! empty(request()->input('for_dashboard_sales_order'))) {
                $sells->whereIn('transactions.status', ['partial', 'ordered'])
                    ->orHavingRaw('so_qty_remaining > 0');
            }

            if ($sale_type == 'sales_order') {
                if (! auth()->user()->can('so.view_all') && auth()->user()->can('so.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            }

            if (! empty(request()->input('delivery_person'))) {
                $sells->where('transactions.delivery_person', request()->input('delivery_person'));
            }
            $sells->groupBy('transactions.id');

            if (! empty(request()->suspended)) {
                $transaction_sub_type = request()->get('transaction_sub_type');
                if (! empty($transaction_sub_type)) {
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
            if (! empty($with)) {
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
                        if (! $only_shipments) {
                            if ($row->is_direct_sale == 0) {
                                if (auth()->user()->can('clinic.sell.edit')) {
                                    $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                                }
                            } elseif ($row->type == 'sales_order') {
                                if (auth()->user()->can('clinic.sell.edit')) {
                                    $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                                }
                            } else {
                                if (auth()->user()->can('clinic.sell.edit')) {
                                    $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                                }
                            }

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

                            if (! empty($row->shipping_status)) {
                                $html .= '<li><a href="' . route('packing.downloadPdf', [$row->id]) . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.download_paking_pdf') . '</a></li>';
                            }
                        }

                        if (auth()->user()->can('clinic.sell.view')) {
                            if (! empty($row->document)) {
                                $document_name = ! empty(explode('_', $row->document, 2)[1]) ? explode('_', $row->document, 2)[1] : $row->document;
                                $html .= '<li><a href="' . url('uploads/documents/' . $row->document) . '" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __('purchase.download_document') . '</a></li>';
                                if (isFileImage($document_name)) {
                                    $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) . '" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>' . __('lang_v1.view_document') . '</a></li>';
                                }
                            }
                        }

                        if ($row->type == 'sell' || $row->sub_type == 'test' || $row->sub_type == 'therapy') {
                            if (auth()->user()->can('clinic.sell.print_invoice')) {
                                $html .= '<li><a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.print_invoice') . '</a></li>';
                            }
                            $html .= '<li class="divider"></li>';
                            if (! $only_shipments) {
                                if (auth()->user()->can('clinic.sell.add_payment')) {
                                    if ($row->payment_status != 'paid') {
                                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicTransactionController::class, 'addPayment'], [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.add_payment') . '</a></li>';
                                    }

                                    $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicTransactionController::class, 'show'], [$row->id]) . '" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.view_payments') . '</a></li>';
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
                        $discount = ! empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (! empty($discount) && $row->discount_type == 'percentage') {
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
                        return $row->sub_type;
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
                    if (! empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $return_due_html .= '<a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="sell_return_due" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }

                    return $return_due_html;
                })
                ->editColumn('invoice_no', function ($row) use ($is_crm) {
                    $Edited = Transaction::where('id', $row->id)->first();
                    $invoice_no = $row->invoice_no;
                    if (! empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (! empty($row->return_exists)) {
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
                    if (! empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (! empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (! empty($row->is_export)) {
                        $invoice_no .= '</br><small class="label label-default no-print" title="' . __('lang_v1.export') . '">' . __('lang_v1.export') . '</small>';
                    }

                    if ($is_crm && ! empty($row->crm_is_order_request)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-yellow label-round no-print" title="' . __('crm::lang.order_request') . '"><i class="fas fa-tasks"></i></small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
                    $status_color = ! empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                    $status = ! empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action([\App\Http\Controllers\SellController::class, 'editShipping'], [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color . '">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

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

                    $html = ! empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

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
                            return  action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->id]);
                        } else {
                            return '';
                        }
                    },
                ]);

            $rawColumns = ['line_discount_amount', 'final_total', 'action', 'total_paid', 'total_remaining', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status', 'types_of_service_name', 'payment_methods', 'return_due', 'conatct_name', 'status'];
            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }

        // $business_locations = BusinessLocation::forDropdown($business_id, false);
        // $customers = Contact::customersDropdown($business_id, false);
        // $sales_representative = User::forDropdown($business_id, false, false, true);

        // //Commission agent filter
        // $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        // $commission_agents = [];
        // if (! empty($is_cmsn_agent_enabled)) {
        //     $commission_agents = User::forDropdown($business_id, false, true, true);
        // }

        // //Service staff filter
        // $service_staffs = null;
        // if ($this->productUtil->isModuleEnabled('service_staff')) {
        //     $service_staffs = $this->productUtil->serviceStaffDropdown($business_id);
        // }

        // $shipping_statuses = $this->transactionUtil->shipping_statuses();

        // $sources = $this->transactionUtil->getSources($business_id);
        // if ($is_woocommerce) {
        //     $sources['woocommerce'] = 'Woocommerce';
        // }
        // $payment_types = [
        //     'cash' => __('lang_v1.cash'),
        //     'card' => __('lang_v1.card'),
        //     'custom_pay_1' => __('bKash'),
        //     // Add more payment methods as needed
        // ];

        // $RemaininInLast = Transaction::leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
        //     ->where('transactions.business_id', $business_id)
        //     ->where('transactions.type', 'sell')
        //     ->whereDate('transactions.transaction_date', '>=', now()->subDays(30))
        //     ->select(
        //         'transactions.id', 
        //         DB::raw('transactions.final_total - COALESCE((SELECT SUM(tp.amount) FROM transaction_payments AS tp WHERE tp.transaction_id = transactions.id), 0) as remaining_amount')
        //     )
        //     ->groupBy('transactions.id')  
        //     ->having('remaining_amount', '>', 0) 
        //     ->count(); 


        // return view('clinic::report.test_report')
        // ->with(compact('business_locations', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses', 'sources', 'payment_types', 'RemaininInLast'));
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
    public function testRegisterReport()
    {
        Log::info('test report index calling to clinic');
        $is_admin = $this->businessUtil->is_admin(auth()->user());

        if (! $is_admin && ! auth()->user()->hasAnyPermission(['clinic.sell.view', 'clinic.sell.create'])) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $is_crm = $this->moduleUtil->isModuleInstalled('Crm');
        $is_tables_enabled = $this->transactionUtil->isModuleEnabled('tables');
        $is_service_staff_enabled = $this->transactionUtil->isModuleEnabled('service_staff');
        $is_types_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        if (request()->ajax()) {

            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);
            $with = [];
            $shipping_statuses = $this->transactionUtil->shipping_statuses();

            $sale_type = ! empty(request()->input('sale_type')) ? request()->input('sale_type') : 'sell';
            $sub_type = ['test'];
            $sells = $this->clinicSellUtil->getListSells($business_id, $sale_type, $sub_type);

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (request()->has('created_by')) {
                $created_by = request()->get('created_by');
                if (! empty($created_by)) {
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
            if (! auth()->user()->can('direct_sell.view')) {
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

            if (! $is_admin && ! $only_shipments && $sale_type != 'sales_order') {
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

            if (! empty(request()->input('payment_status')) && request()->input('payment_status') != 'overdue') {
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
                if (! empty($location_id)) {
                    $sells->where('transactions.location_id', $location_id);
                }
            }

            if (! empty(request()->input('rewards_only')) && request()->input('rewards_only') == true) {
                $sells->where(function ($q) {
                    $q->whereNotNull('transactions.rp_earned')
                        ->orWhere('transactions.rp_redeemed', '>', 0);
                });
            }

            if (! empty(request()->customer_id)) {
                $customer_id = request()->customer_id;
                $sells->where('contacts.id', $customer_id);
            }
            if (! empty(request()->start_date) && ! empty(request()->end_date)) {
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
                if (! empty($commission_agent)) {
                    $sells->where('transactions.commission_agent', $commission_agent);
                }
            }

            if (! empty(request()->input('source'))) {
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

            if (! empty(request()->list_for) && request()->list_for == 'service_staff_report') {
                $sells->whereNotNull('transactions.res_waiter_id');
            }

            if (! empty(request()->res_waiter_id)) {
                $sells->where('transactions.res_waiter_id', request()->res_waiter_id);
            }

            if (! empty(request()->input('sub_type'))) {
                $sells->where('transactions.sub_type', request()->input('sub_type'));
            }

            if (! empty(request()->input('created_by'))) {
                $sells->where('transactions.created_by', request()->input('created_by'));
            }

            if (! empty(request()->input('status'))) {
                $sells->where('transactions.status', request()->input('status'));
            }

            if (! empty(request()->input('sales_cmsn_agnt'))) {
                $sells->where('transactions.commission_agent', request()->input('sales_cmsn_agnt'));
            }

            if (! empty(request()->input('service_staffs'))) {
                $sells->where('transactions.res_waiter_id', request()->input('service_staffs'));
            }

            $only_pending_shipments = request()->only_pending_shipments == 'true' ? true : false;
            if ($only_pending_shipments) {
                $sells->where('transactions.shipping_status', '!=', 'delivered')
                    ->whereNotNull('transactions.shipping_status');
                $only_shipments = true;
            }

            if (! empty(request()->input('shipping_status'))) {
                $sells->where('transactions.shipping_status', request()->input('shipping_status'));
            }

            if (! empty(request()->input('for_dashboard_sales_order'))) {
                $sells->whereIn('transactions.status', ['partial', 'ordered'])
                    ->orHavingRaw('so_qty_remaining > 0');
            }

            if ($sale_type == 'sales_order') {
                if (! auth()->user()->can('so.view_all') && auth()->user()->can('so.view_own')) {
                    $sells->where('transactions.created_by', request()->session()->get('user.id'));
                }
            }

            if (! empty(request()->input('delivery_person'))) {
                $sells->where('transactions.delivery_person', request()->input('delivery_person'));
            }
            $sells->where('transactions.sub_type', 'test');
            $sells->groupBy('transactions.id');

            if (! empty(request()->suspended)) {
                $transaction_sub_type = request()->get('transaction_sub_type');
                if (! empty($transaction_sub_type)) {
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
            if (! empty($with)) {
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
                        if (! $only_shipments) {
                            if ($row->is_direct_sale == 0) {
                                if (auth()->user()->can('clinic.sell.edit')) {
                                    $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                                }
                            } elseif ($row->type == 'sales_order') {
                                if (auth()->user()->can('clinic.sell.edit')) {
                                    $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                                }
                            } else {
                                if (auth()->user()->can('clinic.sell.edit')) {
                                    $html .= '<li><a target="_blank" href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'draftEdit'], [$row->id]) . '"><i class="fas fa-edit"></i> ' . __('messages.edit') . '</a></li>';
                                }
                            }

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

                            if (! empty($row->shipping_status)) {
                                $html .= '<li><a href="' . route('packing.downloadPdf', [$row->id]) . '" target="_blank"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.download_paking_pdf') . '</a></li>';
                            }
                        }

                        if (auth()->user()->can('clinic.sell.view')) {
                            if (! empty($row->document)) {
                                $document_name = ! empty(explode('_', $row->document, 2)[1]) ? explode('_', $row->document, 2)[1] : $row->document;
                                $html .= '<li><a href="' . url('uploads/documents/' . $row->document) . '" download="' . $document_name . '"><i class="fas fa-download" aria-hidden="true"></i>' . __('purchase.download_document') . '</a></li>';
                                if (isFileImage($document_name)) {
                                    $html .= '<li><a href="#" data-href="' . url('uploads/documents/' . $row->document) . '" class="view_uploaded_document"><i class="fas fa-image" aria-hidden="true"></i>' . __('lang_v1.view_document') . '</a></li>';
                                }
                            }
                        }

                        if ($row->type == 'sell' || $row->sub_type == 'test' || $row->sub_type == 'therapy') {
                            if (auth()->user()->can('clinic.sell.print_invoice')) {
                                $html .= '<li><a href="#" class="print-invoice" data-href="' . route('sell.printInvoice', [$row->id]) . '"><i class="fas fa-print" aria-hidden="true"></i> ' . __('lang_v1.print_invoice') . '</a></li>';
                            }
                            $html .= '<li class="divider"></li>';
                            if (! $only_shipments) {
                                if (auth()->user()->can('clinic.sell.add_payment')) {
                                    if ($row->payment_status != 'paid') {
                                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicTransactionController::class, 'addPayment'], [$row->id]) . '" class="add_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.add_payment') . '</a></li>';
                                    }

                                    $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicTransactionController::class, 'show'], [$row->id]) . '" class="view_payment_modal"><i class="fas fa-money-bill-alt"></i> ' . __('purchase.view_payments') . '</a></li>';
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
                        $discount = ! empty($row->discount_amount) ? $row->discount_amount : 0;

                        if (! empty($discount) && $row->discount_type == 'percentage') {
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
                        return $row->sub_type;
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
                    if (! empty($row->return_exists)) {
                        $return_due = $row->amount_return - $row->return_paid;
                        $return_due_html .= '<a href="' . action([\App\Http\Controllers\TransactionPaymentController::class, 'show'], [$row->return_transaction_id]) . '" class="view_purchase_return_payment_modal"><span class="sell_return_due" data-orig-value="' . $return_due . '">' . $this->transactionUtil->num_f($return_due, true) . '</span></a>';
                    }

                    return $return_due_html;
                })
                ->editColumn('invoice_no', function ($row) use ($is_crm) {
                    $Edited = Transaction::where('id', $row->id)->first();
                    $invoice_no = $row->invoice_no;
                    if (! empty($row->woocommerce_order_id)) {
                        $invoice_no .= ' <i class="fab fa-wordpress text-primary no-print" title="' . __('lang_v1.synced_from_woocommerce') . '"></i>';
                    }
                    if (! empty($row->return_exists)) {
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
                    if (! empty($row->is_recurring)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-red label-round no-print" title="' . __('lang_v1.subscribed_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (! empty($row->recur_parent_id)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-info label-round no-print" title="' . __('lang_v1.subscription_invoice') . '"><i class="fas fa-recycle"></i></small>';
                    }

                    if (! empty($row->is_export)) {
                        $invoice_no .= '</br><small class="label label-default no-print" title="' . __('lang_v1.export') . '">' . __('lang_v1.export') . '</small>';
                    }

                    if ($is_crm && ! empty($row->crm_is_order_request)) {
                        $invoice_no .= ' &nbsp;<small class="label bg-yellow label-round no-print" title="' . __('crm::lang.order_request') . '"><i class="fas fa-tasks"></i></small>';
                    }

                    return $invoice_no;
                })
                ->editColumn('shipping_status', function ($row) use ($shipping_statuses) {
                    $status_color = ! empty($this->shipping_status_colors[$row->shipping_status]) ? $this->shipping_status_colors[$row->shipping_status] : 'bg-gray';
                    $status = ! empty($row->shipping_status) ? '<a href="#" class="btn-modal" data-href="' . action([\App\Http\Controllers\SellController::class, 'editShipping'], [$row->id]) . '" data-container=".view_modal"><span class="label ' . $status_color . '">' . $shipping_statuses[$row->shipping_status] . '</span></a>' : '';

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

                    $html = ! empty($payment_method) ? '<span class="payment-method" data-orig-value="' . $payment_method . '" data-status-name="' . $payment_method . '">' . $payment_method . '</span>' : '';

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
                            return  action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->id]);
                        } else {
                            return '';
                        }
                    },
                ]);

            $rawColumns = ['line_discount_amount', 'final_total', 'action', 'total_paid', 'total_remaining', 'payment_status', 'invoice_no', 'discount_amount', 'tax_amount', 'total_before_tax', 'shipping_status', 'types_of_service_name', 'payment_methods', 'return_due', 'conatct_name', 'status'];

            return $datatable->rawColumns($rawColumns)
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $sales_representative = User::forDropdown($business_id, false, false, true);

        //Commission agent filter
        $is_cmsn_agent_enabled = request()->session()->get('business.sales_cmsn_agnt');
        $commission_agents = [];
        if (! empty($is_cmsn_agent_enabled)) {
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
            // Add more payment methods as needed
        ];

       

        return view('clinic::report.test_report')
            ->with(compact('business_locations', 'customers', 'is_woocommerce', 'sales_representative', 'is_cmsn_agent_enabled', 'commission_agents', 'service_staffs', 'is_tables_enabled', 'is_service_staff_enabled', 'is_types_service_enabled', 'shipping_statuses', 'sources', 'payment_types'));
    }

}
