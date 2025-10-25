<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Account;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\InvoiceLayout;
use App\InvoiceScheme;
use App\Media;
use App\Product;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Transaction;
use App\TransactionPayment;
use App\TransactionSellLine;
use App\TypesOfService;
use App\User;
use App\Division;
use App\Utils\Util;
use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use App\Variation;
use App\Warranty;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Stripe\Charge;
use Stripe\Stripe;
use Yajra\DataTables\Facades\DataTables;
use App\Events\SellCreatedOrModified;
use App\ProductWaitlist;
use Illuminate\Support\Facades\Log;
use Modules\Manufacturing\Entities\MfgRecipe;
use Modules\Manufacturing\Entities\MfgRecipeIngredient;
use Modules\Manufacturing\Utils\ManufacturingUtil;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Carbon;
class ClinicPosController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $contactUtil;

    protected $productUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $cashRegisterUtil;

    protected $moduleUtil;

    protected $notificationUtil;

    protected $mfgUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        ManufacturingUtil $mfgUtil,
        NotificationUtil $notificationUtil
    ) {
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->mfgUtil = $mfgUtil;
        $this->notificationUtil = $notificationUtil;
        $this->middleware(StartSession::class);
        $this->dummyPaymentLine = [
            'method' => 'cash',
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
    }
    public function index()
    {
        return view('clinic::index');
    }
    public function showInvoiceUrl($id)
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                ->findorfail($id);
            $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);

            return view('clinic::sell.partials.invoice_url_modal')
                ->with(compact('transaction', 'url'));
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
       
        $business_id = $request->input('business_id');

        $business = Business::findOrFail($business_id);

        $pos_settings = json_decode($business->pos_settings, true);

        $disable_is_direct_sale = $pos_settings['disable_direct_sell'] ?? '';

        Log::info(['disable_is_direct_sale'=> $disable_is_direct_sale]);

        $is_direct_sale = false;
        if (!empty($request->input('is_direct_sale'))) {
            $is_direct_sale = true;
        }


        //Check if there is a open register, if no then redirect to Create Register screen.
        if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
        }

        try {
            $input = $request->except('_token');
            $input['is_quotation'] = 0;
            //status is send as quotation from Add sales screen.
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
                $input['sub_status'] = 'quotation';
            } elseif ($input['status'] == 'proforma') {
                $input['status'] = 'draft';
                $input['sub_status'] = 'proforma';
            }
            $pos_settings = ! empty(request()->session()->get('business.pos_settings')) ? json_decode(request()->session()->get('business.pos_settings'), true) : [];

            $auto_mfg = !empty($pos_settings['auto_mfg']) ? $pos_settings['auto_mfg'] : 0;
            if($auto_mfg==$input['location_id']){
                foreach ($input['products'] as $product) {
                    $auto = Product::findOrFail($product['product_id']);
                    // dd($auto->is_auto_mfg);
                    $mfg = MfgRecipe::where('variation_id', $product['variation_id'])->first();
                    if (!empty($mfg) && $auto->is_auto_mfg ==1) {
                    $production = $this->storeProduction($product['variation_id'], $product['quantity'], $input['location_id']);
                    }
                }
            }
            // dd($production);
            //Add change return
            $change_return = $this->dummyPaymentLine;
            if (!empty($input['payment']['change_return'])) {
                $change_return = $input['payment']['change_return'];
                unset($input['payment']['change_return']);
            }
            $payamount = 0;
            if ($input['status'] == 'final') {
                foreach ($input['payment'] as $amount) {
                    $payamount += $this->transactionUtil->num_uf($amount['amount']);
                }
            } elseif ($input['status'] == 'draft') {
            }
            //Check Customer credit limit
            $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input);

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
                        ->action([\App\Http\Controllers\SellController::class, 'index'])
                        ->with('status', $output);
                }
            }

            if (!empty($input['products'])) {
                $business_id = $request->input('business_id');

                //Check if subscribed or not, then check for users quota
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
                    return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action([\App\Http\Controllers\SellPosController::class, 'index']));
                }

                $user_id = $request->input('user_id');

                $discount = [
                    'discount_type' => $input['discount_type'],
                    'discount_amount' => $input['discount_amount'],
                ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);
                DB::beginTransaction();

                if (empty($request->input('transaction_date'))) {
                    $input['transaction_date'] = Carbon::now();
                } else {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }
                if ($is_direct_sale) {
                    $input['is_direct_sale'] = 1;
                }

                //Set commission agent
                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                $commsn_agnt_setting = $request->input('sales_cmsn_agnt');
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

                //If default price group for the location exists
                $price_group_id = $price_group_id == 0 && $request->has('default_price_group') ? $request->input('default_price_group') : $price_group_id;
                $input['sale_note'] = $input['sale_note'] ?? '';

                if (isset($input['notes_content'])) {
                    $input['sale_note'] .= ', ' . $input['notes_content'];
                }

                if (isset($input['special_discount_note'])) {
                    $input['sale_note'] .= ', ' . $input['special_discount_note'];
                }
                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend'] ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                //Generate reference number
                if (!empty($input['is_recurring'])) {
                    //Update reference count
                    $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
                    $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);
                }

                if (!empty($request->input('invoice_scheme_id'))) {
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

                if ($request->input('additional_expense_value_1') != '') {
                    $input['additional_expense_key_1'] = $request->input('additional_expense_key_1');
                    $input['additional_expense_value_1'] = $request->input('additional_expense_value_1');
                }
                $due_amount = $this->transactionUtil->num_uf($input['final_total']) - $payamount;
                if ($due_amount > 0) {
                    $input['due_amount'] = $due_amount;
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
                $input['document'] = $this->transactionUtil->uploadFile($request, 'sell_document', 'documents');

                $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);

                //Upload Shipping documents
                Media::uploadMedia($business_id, $transaction, $request, 'shipping_documents', false, 'shipping_document');

                $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);

                $change_return['amount'] = $input['change_return'] ?? 0;
                $change_return['is_return'] = 1;

                $input['payment'][] = $change_return;

                $is_credit_sale = isset($input['is_credit_sale']) && $input['is_credit_sale'] == 1 ? true : false;

                if (! $transaction->is_suspend && ! $is_credit_sale) {
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                }

                //Check for final and do some processing.
                if ($input['status'] == 'final') {
                    if (!$is_direct_sale) {
                        //set service staff timer
                        foreach ($input['products'] as $product_line) {
                            if (!empty($product_line['res_service_staff_id'])) {
                                $product = Product::find($product_line['product_id']);

                                if (!empty($product->preparation_time_in_minutes)) {
                                    $service_staff = User::find($product_line['res_service_staff_id']);

                                    $base_time = Carbon::parse($transaction->transaction_date);

                                    //if already assigned set base time as available_at
                                    if (!empty($service_staff->available_at) && Carbon::parse($service_staff->available_at)->gt(Carbon::now())) {
                                        $base_time = Carbon::parse($service_staff->available_at);
                                    }

                                    $total_minutes = $product->preparation_time_in_minutes * $this->transactionUtil->num_uf($product_line['quantity']);

                                    $service_staff->available_at = $base_time->addMinutes($total_minutes);
                                    $service_staff->save();
                                }
                            }
                        }
                    }
                    //update product stock
                    foreach ($input['products'] as $product) {
                        $decrease_qty = $this->productUtil
                            ->num_uf($product['quantity']);
                        if (!empty($product['base_unit_multiplier'])) {
                            $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                        }

                        if ($product['enable_stock']) {
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $input['location_id'],
                                $decrease_qty
                            );
                        }

                        if ($product['product_type'] == 'combo') {
                            //Decrease quantity of combo as well.
                            $this->productUtil
                                ->decreaseProductQuantityCombo(
                                    $product['combo'],
                                    $input['location_id']
                                );
                        }
                    }

                    //Add payments to Cash Register
                    if (!$is_direct_sale && !$transaction->is_suspend && !empty($input['payment']) && !$is_credit_sale) {
                        $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
                    }

                    //Update payment status
                    $payment_status = $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

                    $transaction->payment_status = $payment_status;

                    if ($request->session()->get('business.enable_rp') == 1) {
                        $redeemed = !empty($input['rp_redeemed']) ? $input['rp_redeemed'] : 0;
                        $this->transactionUtil->updateCustomerRewardPoints($contact_id, $transaction->rp_earned, 0, $redeemed);
                    }

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
                    $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

                    //Auto send notification
                    $whatsapp_link = $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
                }

                if (!empty($transaction->sales_order_ids)) {
                    $this->transactionUtil->updateSalesOrderStatus($transaction->sales_order_ids);
                }


                $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);

                Media::uploadMedia($business_id, $transaction, $request, 'documents');

                $this->transactionUtil->activityLog($transaction, 'added');

                DB::commit();

                SellCreatedOrModified::dispatch($transaction);

                if ($request->input('is_save_and_print') == 11) {
                    $output = [
                     'success' => true,
                     'msg' => 'Consultation confirm time call',
                     'transaction_id' =>$transaction->id,
                    ];
                    return $output;
                 }
                if ($request->input('is_save_and_print') == 1) {
                    $url = $this->transactionUtil->getInvoiceUrl($transaction->id, $business_id);
                    Log::info('return to line 482');

                    return redirect()->to($url . '?print_on_load=true');
                }
                


                $msg = trans('sale.pos_sale_added');
                $receipt = '';
                $invoice_layout_id = $request->input('invoice_layout_id');
                $print_invoice = false;
                if (!$is_direct_sale) {
                    if ($input['status'] == 'draft') {
                        $msg = trans('sale.draft_added');

                        if ($input['is_quotation'] == 1) {
                            $msg = trans('lang_v1.quotation_added');
                            $print_invoice = true;
                        }
                    } elseif ($input['status'] == 'final') {
                        $print_invoice = true;
                    } 
                }

                if ($transaction->is_suspend == 1 && empty($pos_settings['print_on_suspend'])) {
                    $print_invoice = false;
                }

                if (!auth()->user()->can('print_invoice')) {
                    $print_invoice = false;
                }

                if ($print_invoice) {
                    $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id, null, false, true, $invoice_layout_id);
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

            // Handle the 'demand' status and waitlist logic
            if ($input['status'] == 'demand') {
                $this->storeInWaitlist($input['products'], $request->get('contact_id'), $input['location_id'], $transaction->id, $input['sale_note']);

                $output = [
                    'success' => 1,
                    'msg' => __('Product has been stored in the waitlist due to demand status.')
                ];

                return redirect()
                    ->action([\App\Http\Controllers\ProductWaitlistController::class, 'index'])
                    ->with('status', $output);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage(), [' Input' => $input]);
            $msg = trans('messages.something_went_wrong');
            Log::error('Transaction failed: ' . $e->getMessage(), [
                'error_code' => $e->getCode(),
                'stack_trace' => $e->getTraceAsString(),
            ]);
            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }
            if (get_class($e) == \App\Exceptions\AdvanceBalanceNotAvailable::class) {
                $msg = $e->getMessage();
            }

            $output = [
                'success' => 0,
                'msg' => $msg,
            ];
        }

        if (!$is_direct_sale) {
            Log::info('return to line 563');

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
            } elseif ($input['status'] == 'quotation') {
                return redirect()
                    ->action([\App\Http\Controllers\SellController::class, 'getQuotations'])
                    ->with('status', $output);
            } elseif (isset($input['type']) && $input['type'] == 'sales_order') {
                return redirect()
                    ->action([\App\Http\Controllers\SalesOrderController::class, 'index'])
                    ->with('status', $output);
            } else {
                if (!empty($input['sub_type']) && $input['sub_type'] == 'repair') {
                    $redirect_url = $input['print_label'] == 1 ? action([\Modules\Repair\Http\Controllers\RepairController::class, 'printLabel'], [$transaction->id]) : action([\Modules\Repair\Http\Controllers\RepairController::class, 'index']);
                    Log::info('return to line 588');

                    return redirect($redirect_url)
                        ->with('status', $output);
                }
                Log::info('return to line 593');
                return redirect()
                    ->action([\App\Http\Controllers\SellController::class, 'index'])
                    ->with('status', $output);

            }
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

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');

        if (!(auth()->user()->can('superadmin') || auth()->user()->can('sell.update')
            || auth()->user()->can('edit_pos_payment')
            || ($this->moduleUtil->hasThePermissionInSubscription($business_id, 'repair_module') &&
                auth()->user()->can('repair.update')))) {
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

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action([\App\Http\Controllers\CashRegisterController::class, 'create']);
        }

        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', [
                'success' => 0,
                'msg' => __('lang_v1.return_exist'),
            ]);
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);

        $business_details = $this->businessUtil->getDetails($business_id);

        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);

        $transaction = Transaction::where('business_id', $business_id)
            ->where('type', 'sell')
            ->with(['price_group', 'types_of_service'])
            ->findorfail($id);

        $location_id = $transaction->location_id;
        $business_location = BusinessLocation::find($location_id);
        $payment_types = $this->productUtil->payment_types($business_location, true);
        $location_printer_type = $business_location->receipt_printer_type;
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
            ->with(['warranties'])
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
                'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
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

                //qty_available not added when negative to avoid max quanity getting decreased in edit and showing error in max quantity validation
                DB::raw('IF(vld.qty_available > 0, vld.qty_available + transaction_sell_lines.quantity, transaction_sell_lines.quantity) AS qty_available')
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

                    //Add available lot numbers for dropdown to sell lines
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;

                    if (!empty($value->sub_unit_id)) {
                        $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                        $sell_details[$key] = $value;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

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

        $featured_products = $business_location->getFeaturedProducts();

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        //If brands, category are enabled then send else false.
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::forDropdown($business_id)
            ->prepend(__('lang_v1.all_brands'), 'all') : false;

        $change_return = $this->dummyPaymentLine;

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

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false, true);
        }

        $waiters = [];
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters_enabled = true;
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }
        $redeem_details = [];
        if (request()->session()->get('business.enable_rp') == 1) {
            $redeem_details = $this->transactionUtil->getRewardRedeemDetails($business_id, $transaction->contact_id);

            $redeem_details['points'] += $transaction->rp_redeemed;
            $redeem_details['points'] -= $transaction->rp_earned;
        }

        $edit_discount = auth()->user()->can('edit_product_discount_from_pos_screen');
        $edit_price = auth()->user()->can('edit_product_price_from_pos_screen');
        $shipping_statuses = $this->transactionUtil->shipping_statuses();

        $warranties = $this->__getwarranties();
        $sub_type = request()->get('sub_type');

        //pos screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_pos_screen_view', ['sub_type' => $sub_type]);

        $invoice_schemes = [];
        $default_invoice_schemes = null;

        if ($transaction->status == 'draft') {
            $invoice_schemes = InvoiceScheme::forDropdown($business_id);
            $default_invoice_schemes = InvoiceScheme::getDefault($business_id);
        }

        $invoice_layouts = InvoiceLayout::forDropdown($business_id);

        $customer_due = $this->transactionUtil->getContactDue($transaction->contact_id, $transaction->business_id);

        $customer_due = $customer_due != 0 ? $this->transactionUtil->num_f($customer_due, true) : '';

        //Added check because $users is of no use if enable_contact_assign if false
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];
        $only_payment = request()->segment(2) == 'payment';
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

        $divisions = Division::all();

        return view('sale_pos.edit')
            ->with(compact(
                'business_details',
                'taxes',
                'payment_types',
                'walk_in_customer',
                'sell_details',
                'transaction',
                'payment_lines',
                'location_printer_type',
                'shortcuts',
                'commission_agent',
                'categories',
                'pos_settings',
                'change_return',
                'types',
                'customer_groups',
                'brands',
                'accounts',
                'waiters',
                'redeem_details',
                'edit_price',
                'edit_discount',
                'shipping_statuses',
                'warranties',
                'sub_type',
                'pos_module_data',
                'invoice_schemes',
                'default_invoice_schemes',
                'invoice_layouts',
                'featured_products',
                'customer_due',
                'users',
                'only_payment',
                'discount_rules',
                'enable_discount_rules',
                'divisions'
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
}
