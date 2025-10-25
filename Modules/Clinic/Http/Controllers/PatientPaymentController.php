<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Transaction;
use App\TransactionPayment;
use App\Utils\ModuleUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Events\TransactionPaymentUpdated;
use Illuminate\Support\Facades\DB;
use App\Contact;
class PatientPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    
     protected $transactionUtil;

     protected $moduleUtil;
     protected $businessUtil;
     /**
      * Constructor
      *
      * @param  TransactionUtil  $transactionUtil
      * @return void
      */
     public function __construct(TransactionUtil $transactionUtil, BusinessUtil $businessUtil, ModuleUtil $moduleUtil)
     {
         $this->businessUtil = $businessUtil;
         $this->transactionUtil = $transactionUtil;
         $this->moduleUtil = $moduleUtil;
     }
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
        if (!auth()->user()->can('clinic.sell.payment.show')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {           
            $transaction = Transaction::where('id', $id)
                ->with(['contact', 'business', 'transaction_for'])
                ->first();
            $payments_query = TransactionPayment::where('transaction_id', $id);

            $accounts_enabled = false;
            if ($this->moduleUtil->isModuleEnabled('account')) {
                $accounts_enabled = true;
                $payments_query->with(['payment_account']);
            }

            $payments = $payments_query->get();
            $location_id = !empty($transaction->location_id) ? $transaction->location_id : null;
            $payment_types = $this->transactionUtil->payment_types($location_id, true);
            return view('clinic::payments.show_payments')
                ->with(compact('transaction', 'payments', 'payment_types', 'accounts_enabled'));
        }
    }

    public function addPayment($transaction_id)
    {
        if (!auth()->user()->can('purchase.payments') && !auth()->user()->can('sell.payments') && !auth()->user()->can('all_expense.access') && !auth()->user()->can('view_own_expense') && !auth()->user()->can('hms.add_booking_payment')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                ->with(['contact', 'location'])
                ->findOrFail($transaction_id);
            if ($transaction->payment_status != 'paid') {
                $show_advance = in_array($transaction->type, ['sell', 'purchase']) ? true : false;
                $payment_types = $this->transactionUtil->payment_types($transaction->location, $show_advance);

                $paid_amount = $this->transactionUtil->getTotalPaid($transaction_id);
                $amount = $transaction->final_total - $paid_amount;
                if ($amount < 0) {
                    $amount = 0;
                }

                $amount_formated = $this->transactionUtil->num_f($amount);

                $payment_line = new TransactionPayment();
                $payment_line->amount = $amount;
                $payment_line->method = 'cash';
                $payment_line->paid_on = \Carbon::now()->toDateTimeString();

                //Accounts
                $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);

                $view = view('clinic::payments.payment_row')
                    ->with(compact('transaction', 'payment_types', 'payment_line', 'amount_formated', 'accounts'))->render();

                $output = [
                    'status' => 'due',
                    'view' => $view,
                ];
            } else {
                $output = [
                    'status' => 'paid',
                    'view' => '',
                    'msg' => 'Updated and '.__('purchase.amount_already_paid'),
                ];
            }

            return json_encode($output);
        }
    }


    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        if (!auth()->user()->can('edit_purchase_payment') && !auth()->user()->can('edit_sell_payment') && !auth()->user()->can('hms.edit_booking_payment')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $payment_line = TransactionPayment::with(['denominations'])->where('method', '!=', 'advance')->findOrFail($id);

            $transaction = Transaction::where('id', $payment_line->transaction_id)
                ->where('business_id', $business_id)
                ->with(['contact', 'location'])
                ->first();

            $payment_types = $this->transactionUtil->payment_types($transaction->location);

            //Accounts
            $accounts = $this->moduleUtil->accountsDropdown($business_id, true, false, true);

            return view('clinic::patient.patients.payment.edit_payment_row')
                ->with(compact('transaction', 'payment_types', 'payment_line', 'accounts'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('edit_purchase_payment') && !auth()->user()->can('edit_sell_payment') && !auth()->user()->can('all_expense.access') && !auth()->user()->can('view_own_expense') && !auth()->user()->can('hms.edit_booking_payment')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = request()->session()->get('user.business_id');

            $inputs = $request->only([
                'amount', 'method', 'note', 'card_number', 'card_holder_name',
                'card_transaction_number', 'card_type', 'card_month', 'card_year', 'card_security',
                'cheque_number', 'bank_account_number',
            ]);
            $inputs['paid_on'] = $this->transactionUtil->uf_date($request->input('paid_on'), true);
            $inputs['amount'] = $this->transactionUtil->num_uf($inputs['amount']);

            if ($inputs['method'] == 'custom_pay_1') {
                $inputs['transaction_no'] = $request->input('transaction_no_1');
            } elseif ($inputs['method'] == 'custom_pay_2') {
                $inputs['transaction_no'] = $request->input('transaction_no_2');
            } elseif ($inputs['method'] == 'custom_pay_3') {
                $inputs['transaction_no'] = $request->input('transaction_no_3');
            }

            if (!empty($request->input('account_id'))) {
                $inputs['account_id'] = $request->input('account_id');
            }

            $payment = TransactionPayment::where('method', '!=', 'advance')->findOrFail($id);

            if (!empty($request->input('denominations'))) {
                $this->transactionUtil->updateCashDenominations($payment, $request->input('denominations'));
            }

            //Update parent payment if exists
            if (!empty($payment->parent_id)) {
                $parent_payment = TransactionPayment::find($payment->parent_id);
                $parent_payment->amount = $parent_payment->amount - ($payment->amount - $inputs['amount']);

                $parent_payment->save();
            }

            $business_id = $request->session()->get('user.business_id');

            $transaction = Transaction::where('business_id', $business_id)
                ->find($payment->transaction_id);

            $transaction_before = $transaction->replicate();
            $document_name = $this->transactionUtil->uploadFile($request, 'document', 'documents');
            if (!empty($document_name)) {
                $inputs['document'] = $document_name;
            }

            DB::beginTransaction();

            $payment->update($inputs);

            //update payment status
            $payment_status = $this->transactionUtil->updatePaymentStatus($payment->transaction_id);
            $transaction->payment_status = $payment_status;

            $this->transactionUtil->activityLog($transaction, 'payment_edited', $transaction_before);

            DB::commit();

            //event
            event(new TransactionPaymentUpdated($payment, $transaction->type));

            $output = [
                'success' => true,
                'msg' => __('purchase.payment_updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('delete_purchase_payment') && !auth()->user()->can('delete_sell_payment') && !auth()->user()->can('all_expense.access') && !auth()->user()->can('view_own_expense') && !auth()->user()->can('hms.delete_booking_payment')) {
            \Log::warning('Unauthorized deletion attempt by user ID: ' . auth()->id());
            abort(403, 'Unauthorized action.');
        }
    
        if (request()->ajax()) {
            try {
                \Log::info('Attempting to delete payment with ID: ' . $id);
                $payment = TransactionPayment::findOrFail($id);
    
                DB::beginTransaction();
    
                if (!empty($payment->transaction_id)) {
                    \Log::info('Deleting payment with transaction ID: ' . $payment->transaction_id);
                    TransactionPayment::deletePayment($payment);
                } else { // advance payment
                    $adjusted_payments = TransactionPayment::where('parent_id', $payment->id)->get();
                    $total_adjusted_amount = $adjusted_payments->sum('amount');
    
                    // Get customer advance share from payment and deduct from advance balance
                    $total_customer_advance = $payment->amount - $total_adjusted_amount;
                    if ($total_customer_advance > 0) {
                        \Log::info('Deducting customer advance for payment ID: ' . $payment->id . ', Amount: ' . $total_customer_advance);
                        $this->transactionUtil->updateContactBalance($payment->payment_for, $total_customer_advance, 'deduct');
                    }
    
                    // Delete all child payments
                    foreach ($adjusted_payments as $adjusted_payment) {
                        \Log::info('Deleting adjusted payment ID: ' . $adjusted_payment->id);
                        // Make parent payment null as it will get deleted
                        $adjusted_payment->parent_id = null;
                        TransactionPayment::deletePayment($adjusted_payment);
                    }
    
                    // Delete advance payment
                    \Log::info('Deleting advance payment ID: ' . $payment->id);
                    TransactionPayment::deletePayment($payment);
                }
    
                DB::commit();
    
                \Log::info('Payment deletion successful for ID: ' . $id);
                $output = [
                    'success' => true,
                    'msg' => __('purchase.payment_deleted_success'),
                ];
            } catch (\Exception $e) {
                DB::rollBack();
    
                \Log::emergency('Error during payment deletion. File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage());
                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }
    
            return $output;
        }
    }
    public function viewPayment($payment_id)
    {
        if (!(auth()->user()->can('sell.payments') ||
            auth()->user()->can('purchase.payments') ||
            auth()->user()->can('edit_sell_payment') ||
            auth()->user()->can('delete_sell_payment') ||
            auth()->user()->can('edit_purchase_payment') ||
            auth()->user()->can('delete_purchase_payment') ||
            auth()->user()->can('hms.add_booking_payment')
        )) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('business.id');
            $single_payment_line = TransactionPayment::findOrFail($payment_id);

            $transaction = null;
            if (!empty($single_payment_line->transaction_id)) {
                $transaction = Transaction::where('id', $single_payment_line->transaction_id)
                    ->with(['contact', 'location', 'transaction_for'])
                    ->first();
            } else {
                $child_payment = TransactionPayment::where('business_id', $business_id)
                    ->where('parent_id', $payment_id)
                    ->with(['transaction', 'transaction.contact', 'transaction.location', 'transaction.transaction_for'])
                    ->first();
                $transaction = !empty($child_payment) ? $child_payment->transaction : null;
            }

            $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

            return view('clinic::patient.patients.payment.single_payment_view')
                ->with(compact('single_payment_line', 'transaction', 'payment_types'));
        }
    }
    public function getPayContactDue($contact_id)
    {
        if (!(auth()->user()->can('sell.payments') || auth()->user()->can('purchase.payments'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $due_payment_type = request()->input('type');
            $query = Contact::where('contacts.id', $contact_id)
                ->leftjoin('transactions AS t', 'contacts.id', '=', 't.contact_id');
            if ($due_payment_type == 'purchase') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'purchase', final_total, 0)) as total_purchase"),
                    DB::raw("SUM(IF(t.type = 'purchase', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id'
                );
            } elseif ($due_payment_type == 'purchase_return') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'purchase_return', final_total, 0)) as total_purchase_return"),
                    DB::raw("SUM(IF(t.type = 'purchase_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_return_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id'
                );
            } elseif ($due_payment_type == 'sell') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', final_total, 0)) as total_invoice"),
                    DB::raw("SUM(IF(t.type = 'sell' AND t.status = 'final', (SELECT SUM(IF(is_return = 1,-1*amount,amount)) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id'
                );
            } elseif ($due_payment_type == 'sell_return') {
                $query->select(
                    DB::raw("SUM(IF(t.type = 'sell_return', final_total, 0)) as total_sell_return"),
                    DB::raw("SUM(IF(t.type = 'sell_return', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as total_return_paid"),
                    'contacts.name',
                    'contacts.supplier_business_name',
                    'contacts.id as contact_id'
                );
            }

            //Query for opening balance details
            $query->addSelect(
                DB::raw("SUM(IF(t.type = 'opening_balance', final_total, 0)) as opening_balance"),
                DB::raw("SUM(IF(t.type = 'opening_balance', (SELECT SUM(amount) FROM transaction_payments WHERE transaction_payments.transaction_id=t.id), 0)) as opening_balance_paid")
            );
            $contact_details = $query->first();

            $payment_line = new TransactionPayment();
            if ($due_payment_type == 'purchase') {
                $contact_details->total_purchase = empty($contact_details->total_purchase) ? 0 : $contact_details->total_purchase;
                $payment_line->amount = $contact_details->total_purchase -
                    $contact_details->total_paid;
            } elseif ($due_payment_type == 'purchase_return') {
                $payment_line->amount = $contact_details->total_purchase_return -
                    $contact_details->total_return_paid;
            } elseif ($due_payment_type == 'sell') {
                $contact_details->total_invoice = empty($contact_details->total_invoice) ? 0 : $contact_details->total_invoice;

                $payment_line->amount = $contact_details->total_invoice -
                    $contact_details->total_paid;
            } elseif ($due_payment_type == 'sell_return') {
                $payment_line->amount = $contact_details->total_sell_return -
                    $contact_details->total_return_paid;
            }

            //If opening balance due exists add to payment amount
            $contact_details->opening_balance = !empty($contact_details->opening_balance) ? $contact_details->opening_balance : 0;
            $contact_details->opening_balance_paid = !empty($contact_details->opening_balance_paid) ? $contact_details->opening_balance_paid : 0;
            $ob_due = $contact_details->opening_balance - $contact_details->opening_balance_paid;
            if ($ob_due > 0) {
                $payment_line->amount += $ob_due;
            }

            $amount_formated = $this->transactionUtil->num_f($payment_line->amount);

            $contact_details->total_paid = empty($contact_details->total_paid) ? 0 : $contact_details->total_paid;

            $payment_line->method = 'cash';
            $payment_line->paid_on = \Carbon::now()->toDateTimeString();

            $payment_types = $this->transactionUtil->payment_types(null, false, $business_id);

            //Accounts
            $accounts = $this->moduleUtil->accountsDropdown($business_id, true);

            return view('clinic::patient.patients.payment.pay_customer_due_modal')
                ->with(compact('contact_details', 'payment_types', 'payment_line', 'due_payment_type', 'ob_due', 'amount_formated', 'accounts'));
        }
    }
}
