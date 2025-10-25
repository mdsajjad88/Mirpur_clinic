<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use App\BusinessLocation;
use App\CashRegister;
use App\Contact;
use App\Events\TransactionPaymentAdded;
use App\Events\TransactionPaymentUpdated;
use App\Exceptions\AdvanceBalanceNotAvailable;
use App\Transaction;
use App\TransactionPayment;
use App\Utils\ModuleUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use Datatables;
use Illuminate\Support\Facades\DB;
class ClinicTransactionController extends Controller
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
        if (!(auth()->user()->can('sell.payments') || auth()->user()->can('purchase.payments') || auth()->user()->can('hms.add_booking_payment'))) {
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

            return view('clinic::payments.edit_payment_row')
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
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('delete_purchase_payment') && !auth()->user()->can('delete_sell_payment') && !auth()->user()->can('all_expense.access') && !auth()->user()->can('view_own_expense') && !auth()->user()->can('hms.delete_booking_payment')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $payment = TransactionPayment::findOrFail($id);

                DB::beginTransaction();

                if (!empty($payment->transaction_id)) {
                    $transaction = Transaction::find($payment->transaction_id);
                // Check if the payment is related to a sell or sell_return
                if ($transaction && ($transaction->type == 'sell')) {
                    $user_id = auth()->user()->id;
                    $register = CashRegister::where('user_id', $user_id)
                                            ->where('status', 'open')
                                            ->first();
                    Log::info(['register:' => $register]);

                    if ($register) {
                            // Add a sell_return entry to the cash register transaction
                            $register->cash_register_transactions()->create([
                                'amount' => $payment->amount,
                                'pay_method' => $payment->method,
                                'type' => 'debit', // Refund, so it's a debit
                                'transaction_type' => 'sell_return',
                                'transaction_id' => $payment->transaction_id,
                            ]);
                        } else {
                            $output = [
                                'success' => false,
                                'msg' => __('No payment can be delete as the register is closed.'),
                            ];
                            return $output;
                        }
    
                    }
                    TransactionPayment::deletePayment($payment);
                } else { //advance payment
                    $adjusted_payments = TransactionPayment::where(
                        'parent_id',
                        $payment->id
                    )
                        ->get();

                    $total_adjusted_amount = $adjusted_payments->sum('amount');

                    //Get customer advance share from payment and deduct from advance balance
                    $total_customer_advance = $payment->amount - $total_adjusted_amount;
                    if ($total_customer_advance > 0) {
                        $this->transactionUtil->updateContactBalance($payment->payment_for, $total_customer_advance, 'deduct');
                    }

                    //Delete all child payments
                    foreach ($adjusted_payments as $adjusted_payment) {
                        //Make parent payment null as it will get deleted
                        $adjusted_payment->parent_id = null;
                        TransactionPayment::deletePayment($adjusted_payment);
                    }

                    //Delete advance payment
                    TransactionPayment::deletePayment($payment);
                }

                DB::commit();

                $output = [
                    'success' => true,
                    'msg' => __('purchase.payment_deleted_success'),
                ];
            } catch (\Exception $e) {
                DB::rollBack();

                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
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

    public function viewPayment($payment_id)
    {
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

            return view('clinic::payments.single_payment_view')
                ->with(compact('single_payment_line', 'transaction', 'payment_types'));
        }
    }

}
