<?php

namespace App\Utils;

use App\CashRegister;
use App\CashRegisterTransaction;
use App\Transaction;
use DB;

class CashRegisterUtil extends Util
{
    /**
     * Returns number of opened Cash Registers for the
     * current logged in user
     *
     * @return int
     */
    public function countOpenedRegister()
    {
        $user_id = auth()->user()->id;
        $count = CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->count();

        return $count;
    }

    /**
     * Adds sell payments to currently opened cash register
     *
     * @param object/int $transaction
     * @param  array  $payments
     * @return bool
     */
    public function addSellPayments($transaction, $payments)
    {
        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->first();
        $payments_formatted = [];
        foreach ($payments as $payment) {
            $payment_amount = (isset($payment['is_return']) && $payment['is_return'] == 1) ? (-1 * $this->num_uf($payment['amount'])) : $this->num_uf($payment['amount']);
            if ($payment_amount != 0) {
                $type = 'credit';
                if ($transaction->type == 'expense') {
                    $type = 'debit';
                }

                $payments_formatted[] = new CashRegisterTransaction([
                    'amount' => $payment_amount,
                    'pay_method' => $payment['method'],
                    'type' => $type,
                    'transaction_type' => $transaction->type,
                    'transaction_id' => $transaction->id,
                ]);
            }
        }

        if (! empty($payments_formatted)) {
            $register->cash_register_transactions()->saveMany($payments_formatted);
        }

        return true;
    }

    /**
     * Adds sell payments to currently opened cash register
     *
     * @param object/int $transaction
     * @param  array  $payments
     * @return bool
     */
    public function updateSellPayments($status_before, $transaction, $payments)
    {
        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->first();
        //If draft -> final then add all
        //If final -> draft then refund all
        //If final -> final then update payments
        if ($status_before == 'draft' && $transaction->status == 'final') {
            $this->addSellPayments($transaction, $payments);
        } elseif ($status_before == 'final' && $transaction->status == 'draft') {
            $this->refundSell($transaction);
        } elseif ($status_before == 'final' && $transaction->status == 'final') {
            $prev_payments = CashRegisterTransaction::where('transaction_id', $transaction->id)
                            ->select(
                                DB::raw("SUM(IF(pay_method='cash', IF(type='credit', amount, -1 * amount), 0)) as total_cash"),
                                DB::raw("SUM(IF(pay_method='card', IF(type='credit', amount, -1 * amount), 0)) as total_card"),
                                DB::raw("SUM(IF(pay_method='cheque', IF(type='credit', amount, -1 * amount), 0)) as total_cheque"),
                                DB::raw("SUM(IF(pay_method='bank_transfer', IF(type='credit', amount, -1 * amount), 0)) as total_bank_transfer"),
                                DB::raw("SUM(IF(pay_method='other', IF(type='credit', amount, -1 * amount), 0)) as total_other"),
                                DB::raw("SUM(IF(pay_method='custom_pay_1', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_1"),
                                DB::raw("SUM(IF(pay_method='custom_pay_2', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_2"),
                                DB::raw("SUM(IF(pay_method='custom_pay_3', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_3"),
                                DB::raw("SUM(IF(pay_method='custom_pay_4', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_4"),
                                DB::raw("SUM(IF(pay_method='custom_pay_5', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_5"),
                                DB::raw("SUM(IF(pay_method='custom_pay_6', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_6"),
                                DB::raw("SUM(IF(pay_method='custom_pay_7', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_7"),
                                DB::raw("SUM(IF(pay_method='advance', IF(type='credit', amount, -1 * amount), 0)) as total_advance")
                            )->first();
            if (! empty($prev_payments)) {
                $payment_diffs = [
                    'cash' => $prev_payments->total_cash,
                    'card' => $prev_payments->total_card,
                    'cheque' => $prev_payments->total_cheque,
                    'bank_transfer' => $prev_payments->total_bank_transfer,
                    'other' => $prev_payments->total_other,
                    'custom_pay_1' => $prev_payments->total_custom_pay_1,
                    'custom_pay_2' => $prev_payments->total_custom_pay_2,
                    'custom_pay_3' => $prev_payments->total_custom_pay_3,
                    'custom_pay_4' => $prev_payments->total_custom_pay_4,
                    'custom_pay_5' => $prev_payments->total_custom_pay_5,
                    'custom_pay_6' => $prev_payments->total_custom_pay_6,
                    'custom_pay_7' => $prev_payments->total_custom_pay_7,
                    'advance' => $prev_payments->total_advance,
                ];

                foreach ($payments as $payment) {
                    if (isset($payment['is_return']) && $payment['is_return'] == 1) {
                        $payment_diffs[$payment['method']] += $this->num_uf($payment['amount']);
                    } else {
                        $payment_diffs[$payment['method']] -= $this->num_uf($payment['amount']);
                    }
                }
                $payments_formatted = [];
                foreach ($payment_diffs as $key => $value) {
                    if ($value > 0) {
                        $payments_formatted[] = new CashRegisterTransaction([
                            'amount' => $value,
                            'pay_method' => $key,
                            'type' => 'debit',
                            'transaction_type' => 'refund',
                            'transaction_id' => $transaction->id,
                        ]);
                    } elseif ($value < 0) {
                        $payments_formatted[] = new CashRegisterTransaction([
                            'amount' => -1 * $value,
                            'pay_method' => $key,
                            'type' => 'credit',
                            'transaction_type' => 'sell',
                            'transaction_id' => $transaction->id,
                        ]);
                    }
                }
                if (! empty($payments_formatted)) {
                    $register->cash_register_transactions()->saveMany($payments_formatted);
                }
            }
        }

        return true;
    }

    /**
     * Refunds all payments of a sell
     *
     * @param object/int $transaction
     * @return bool
     */
    public function refundSell($transaction)
    {
        $user_id = auth()->user()->id;
        $register = CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->first();

        $total_payment = CashRegisterTransaction::where('transaction_id', $transaction->id)
                            ->select(
                                DB::raw("SUM(IF(pay_method='cash', IF(type='credit', amount, -1 * amount), 0)) as total_cash"),
                                DB::raw("SUM(IF(pay_method='card', IF(type='credit', amount, -1 * amount), 0)) as total_card"),
                                DB::raw("SUM(IF(pay_method='cheque', IF(type='credit', amount, -1 * amount), 0)) as total_cheque"),
                                DB::raw("SUM(IF(pay_method='bank_transfer', IF(type='credit', amount, -1 * amount), 0)) as total_bank_transfer"),
                                DB::raw("SUM(IF(pay_method='other', IF(type='credit', amount, -1 * amount), 0)) as total_other"),
                                DB::raw("SUM(IF(pay_method='custom_pay_1', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_1"),
                                DB::raw("SUM(IF(pay_method='custom_pay_2', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_2"),
                                DB::raw("SUM(IF(pay_method='custom_pay_3', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_3"),
                                DB::raw("SUM(IF(pay_method='custom_pay_4', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_4"),
                                DB::raw("SUM(IF(pay_method='custom_pay_5', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_5"),
                                DB::raw("SUM(IF(pay_method='custom_pay_6', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_6"),
                                DB::raw("SUM(IF(pay_method='custom_pay_7', IF(type='credit', amount, -1 * amount), 0)) as total_custom_pay_7")
                            )->first();
        $refunds = [
            'cash' => $total_payment->total_cash,
            'card' => $total_payment->total_card,
            'cheque' => $total_payment->total_cheque,
            'bank_transfer' => $total_payment->total_bank_transfer,
            'other' => $total_payment->total_other,
            'custom_pay_1' => $total_payment->total_custom_pay_1,
            'custom_pay_2' => $total_payment->total_custom_pay_2,
            'custom_pay_3' => $total_payment->total_custom_pay_3,
            'custom_pay_4' => $total_payment->total_custom_pay_4,
            'custom_pay_5' => $total_payment->total_custom_pay_5,
            'custom_pay_6' => $total_payment->total_custom_pay_6,
            'custom_pay_7' => $total_payment->total_custom_pay_7,
        ];
        $refund_formatted = [];
        foreach ($refunds as $key => $val) {
            if ($val > 0) {
                $refund_formatted[] = new CashRegisterTransaction([
                    'amount' => $val,
                    'pay_method' => $key,
                    'type' => 'debit',
                    'transaction_type' => 'refund',
                    'transaction_id' => $transaction->id,
                ]);
            }
        }

        if (! empty($refund_formatted)) {
            $register->cash_register_transactions()->saveMany($refund_formatted);
        }

        return true;
    }

    /**
     * Retrieves details of given rigister id else currently opened register
     *
     * @param $register_id default null
     * @return object
     */
    public function getRegisterDetails($register_id = null)
    {
        $query = CashRegister::leftjoin(
            'cash_register_transactions as ct',
            'ct.cash_register_id',
            '=',
            'cash_registers.id'
        )
        ->join(
            'users as u',
            'u.id',
            '=',
            'cash_registers.user_id'
        )
        ->leftJoin(
            'business_locations as bl',
            'bl.id',
            '=',
            'cash_registers.location_id'
        );
        if (empty($register_id)) {
            $user_id = auth()->user()->id;
            $query->where('user_id', $user_id)
                ->where('cash_registers.status', 'open');
        } else {
            $query->where('cash_registers.id', $register_id);
        }

        $register_details = $query->select(
            'cash_registers.created_at as open_time',
            'cash_registers.closed_at as closed_at',
            'cash_registers.user_id',
            'cash_registers.closing_note',
            'cash_registers.location_id',
            'cash_registers.denominations',
            DB::raw("SUM(IF(transaction_type='initial', amount, 0)) as cash_in_hand"),
            DB::raw("SUM(IF(transaction_type='sell', amount, IF(transaction_type='refund', -1 * amount, 0))) as total_sale"),
            DB::raw("SUM(IF(transaction_type='expense', IF(transaction_type='refund', -1 * amount, amount), 0)) as total_expense"),
            DB::raw("SUM(IF(pay_method='cash', IF(transaction_type='sell', amount, 0), 0)) as total_cash"),
            DB::raw("SUM(IF(pay_method='cash', IF(transaction_type='expense', amount, 0), 0)) as total_cash_expense"),
            DB::raw("SUM(IF(pay_method='cheque', IF(transaction_type='sell', amount, 0), 0)) as total_cheque"),
            DB::raw("SUM(IF(pay_method='cheque', IF(transaction_type='expense', amount, 0), 0)) as total_cheque_expense"),
            DB::raw("SUM(IF(pay_method='card', IF(transaction_type='sell', amount, 0), 0)) as total_card"),
            DB::raw("SUM(IF(pay_method='card', IF(transaction_type='expense', amount, 0), 0)) as total_card_expense"),
            DB::raw("SUM(IF(pay_method='bank_transfer', IF(transaction_type='sell', amount, 0), 0)) as total_bank_transfer"),
            DB::raw("SUM(IF(pay_method='bank_transfer', IF(transaction_type='expense', amount, 0), 0)) as total_bank_transfer_expense"),
            DB::raw("SUM(IF(pay_method='other', IF(transaction_type='sell', amount, 0), 0)) as total_other"),
            DB::raw("SUM(IF(pay_method='other', IF(transaction_type='expense', amount, 0), 0)) as total_other_expense"),
            DB::raw("SUM(IF(pay_method='advance', IF(transaction_type='sell', amount, 0), 0)) as total_advance"),
            DB::raw("SUM(IF(pay_method='advance', IF(transaction_type='expense', amount, 0), 0)) as total_advance_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_1', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_1"),
            DB::raw("SUM(IF(pay_method='custom_pay_2', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_2"),
            DB::raw("SUM(IF(pay_method='custom_pay_3', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_3"),
            DB::raw("SUM(IF(pay_method='custom_pay_4', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_4"),
            DB::raw("SUM(IF(pay_method='custom_pay_5', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_5"),
            DB::raw("SUM(IF(pay_method='custom_pay_6', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_6"),
            DB::raw("SUM(IF(pay_method='custom_pay_7', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_7"),
            DB::raw("SUM(IF(pay_method='custom_pay_1', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_1_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_2', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_2_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_3', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_3_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_4', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_4_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_5', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_5_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_6', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_6_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_7', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_7_expense"),
            DB::raw("SUM(IF(transaction_type='refund', amount, 0)) as total_refund"),
            DB::raw("SUM(IF(transaction_type='sell_return', amount, 0)) as total_return"),
            DB::raw("SUM(IF(transaction_type='sell_return', IF(pay_method='cash', amount, 0), 0)) as total_cash_sell_return"),
            DB::raw("SUM(IF(transaction_type='sell_return', IF(pay_method='cheque', amount, 0), 0)) as total_cheque_sell_return"),
            DB::raw("SUM(IF(transaction_type='sell_return', IF(pay_method='card', amount, 0), 0)) as total_card_sell_return"),
            DB::raw("SUM(IF(transaction_type='sell_return', IF(pay_method='custom_pay_1', amount, 0), 0)) as total_custom_pay_1_sell_return"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='cash', amount, 0), 0)) as total_cash_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='cheque', amount, 0), 0)) as total_cheque_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='card', amount, 0), 0)) as total_card_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='bank_transfer', amount, 0), 0)) as total_bank_transfer_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='other', amount, 0), 0)) as total_other_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='advance', amount, 0), 0)) as total_advance_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_1', amount, 0), 0)) as total_custom_pay_1_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_2', amount, 0), 0)) as total_custom_pay_2_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_3', amount, 0), 0)) as total_custom_pay_3_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_4', amount, 0), 0)) as total_custom_pay_4_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_5', amount, 0), 0)) as total_custom_pay_5_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_6', amount, 0), 0)) as total_custom_pay_6_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_7', amount, 0), 0)) as total_custom_pay_7_refund"),
            DB::raw("SUM(IF(pay_method='cheque', 1, 0)) as total_cheques"),
            DB::raw("SUM(IF(pay_method='card', 1, 0)) as total_card_slips"),
            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as user_name"),
            'u.email',
            'bl.name as location_name'
        )->first();

        return $register_details;
    }


    public function getRegisterAccountDetails($register_id = null)
    {
        $query = CashRegister::leftjoin(
            'cash_register_transactions as ct',
            'ct.cash_register_id',
            '=',
            'cash_registers.id'
        )
        ->join(
            'users as u',
            'u.id',
            '=',
            'cash_registers.user_id'
        )
        ->leftJoin(
            'business_locations as bl',
            'bl.id',
            '=',
            'cash_registers.location_id'
        );
        if (empty($register_id)) {
            $user_id = auth()->user()->id;
            $query->where('user_id', $user_id)
                ->where('cash_registers.status', 'open');
        } else {
            $query->where('cash_registers.id', $register_id);
        }

        $register_details = $query->select(
            'cash_registers.user_id',
            'cash_registers.closing_note',
            'cash_registers.location_id',
            'cash_registers.denominations',
            DB::raw("SUM(IF(transaction_type='initial', amount, 0)) as cash_in_hand"),
            DB::raw("SUM(IF(transaction_type='sell', amount, IF(transaction_type='refund', -1 * amount, 0))) as total_sale"),
            DB::raw("SUM(IF(transaction_type='expense', IF(transaction_type='refund', -1 * amount, amount), 0)) as total_expense"),
            DB::raw("SUM(IF(pay_method='cash', IF(transaction_type='sell', amount, 0), 0)) as total_cash"),
            DB::raw("SUM(IF(pay_method='cash', IF(transaction_type='expense', amount, 0), 0)) as total_cash_expense"),
            DB::raw("SUM(IF(pay_method='cheque', IF(transaction_type='sell', amount, 0), 0)) as total_cheque"),
            DB::raw("SUM(IF(pay_method='cheque', IF(transaction_type='expense', amount, 0), 0)) as total_cheque_expense"),
            DB::raw("SUM(IF(pay_method='card', IF(transaction_type='sell', amount, 0), 0)) as total_card"),
            DB::raw("SUM(IF(pay_method='card', IF(transaction_type='expense', amount, 0), 0)) as total_card_expense"),
            DB::raw("SUM(IF(pay_method='bank_transfer', IF(transaction_type='sell', amount, 0), 0)) as total_bank_transfer"),
            DB::raw("SUM(IF(pay_method='bank_transfer', IF(transaction_type='expense', amount, 0), 0)) as total_bank_transfer_expense"),
            DB::raw("SUM(IF(pay_method='other', IF(transaction_type='sell', amount, 0), 0)) as total_other"),
            DB::raw("SUM(IF(pay_method='other', IF(transaction_type='expense', amount, 0), 0)) as total_other_expense"),
            DB::raw("SUM(IF(pay_method='advance', IF(transaction_type='sell', amount, 0), 0)) as total_advance"),
            DB::raw("SUM(IF(pay_method='advance', IF(transaction_type='expense', amount, 0), 0)) as total_advance_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_1', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_1"),
            DB::raw("SUM(IF(pay_method='custom_pay_2', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_2"),
            DB::raw("SUM(IF(pay_method='custom_pay_3', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_3"),
            DB::raw("SUM(IF(pay_method='custom_pay_4', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_4"),
            DB::raw("SUM(IF(pay_method='custom_pay_5', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_5"),
            DB::raw("SUM(IF(pay_method='custom_pay_6', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_6"),
            DB::raw("SUM(IF(pay_method='custom_pay_7', IF(transaction_type='sell', amount, 0), 0)) as total_custom_pay_7"),
            DB::raw("SUM(IF(pay_method='custom_pay_1', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_1_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_2', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_2_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_3', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_3_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_4', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_4_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_5', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_5_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_6', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_6_expense"),
            DB::raw("SUM(IF(pay_method='custom_pay_7', IF(transaction_type='expense', amount, 0), 0)) as total_custom_pay_7_expense"),
            DB::raw("SUM(IF(transaction_type='refund', amount, 0)) as total_refund"),
            DB::raw("SUM(IF(transaction_type='sell_return', amount, 0)) as total_return"),
            DB::raw("SUM(IF(transaction_type='sell_return', IF(pay_method='cash', amount, 0), 0)) as total_cash_sell_return"),
            DB::raw("SUM(IF(transaction_type='sell_return', IF(pay_method='cheque', amount, 0), 0)) as total_cheque_sell_return"),
            DB::raw("SUM(IF(transaction_type='sell_return', IF(pay_method='card', amount, 0), 0)) as total_card_sell_return"),
            DB::raw("SUM(IF(transaction_type='sell_return', IF(pay_method='custom_pay_1', amount, 0), 0)) as total_custom_pay_1_sell_return"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='cash', amount, 0), 0)) as total_cash_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='cheque', amount, 0), 0)) as total_cheque_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='card', amount, 0), 0)) as total_card_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='bank_transfer', amount, 0), 0)) as total_bank_transfer_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='other', amount, 0), 0)) as total_other_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='advance', amount, 0), 0)) as total_advance_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_1', amount, 0), 0)) as total_custom_pay_1_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_2', amount, 0), 0)) as total_custom_pay_2_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_3', amount, 0), 0)) as total_custom_pay_3_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_4', amount, 0), 0)) as total_custom_pay_4_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_5', amount, 0), 0)) as total_custom_pay_5_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_6', amount, 0), 0)) as total_custom_pay_6_refund"),
            DB::raw("SUM(IF(transaction_type='refund', IF(pay_method='custom_pay_7', amount, 0), 0)) as total_custom_pay_7_refund"),
            DB::raw("SUM(IF(pay_method='cheque', 1, 0)) as total_cheques"),
            DB::raw("SUM(IF(pay_method='card', 1, 0)) as total_card_slips"),
            DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as user_name"),
            'u.email',
            'bl.name as location_name'
        )->first();

        return $register_details;
    }

    /**
     * Get the transaction details for a particular register
     *
     * @param $user_id int
     * @param $open_time datetime
     * @param $close_time datetime
     * @return array
     */
    public function getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled = false)
    {
        $product_details_by_brand = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('products AS P', 'TSL.product_id', '=', 'P.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->leftjoin('brands AS B', 'P.brand_id', '=', 'B.id')
                ->groupBy('B.id')
                ->select(
                    'B.name as brand_name',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->orderByRaw('CASE WHEN brand_name IS NULL THEN 2 ELSE 1 END, brand_name')
                ->get();


        $product_details = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('variations AS v', 'TSL.variation_id', '=', 'v.id')
                ->join('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products AS p', 'v.product_id', '=', 'p.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->groupBy('v.id')
                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'v.name as variation_name',
                    'pv.name as product_variation_name',
                    'v.sub_sku as sku',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->get();
        
        $product_details_by_customer = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('variations AS v', 'TSL.variation_id', '=', 'v.id')
                ->join('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products AS p', 'v.product_id', '=', 'p.id')
                ->join('contacts AS c', 'c.id', '=', 'transactions.contact_id')
                ->where('TSL.children_type', '!=', 'combo')
                ->groupBy('c.id')
                ->select(
                    'p.name as product_name',
                    'c.name as contact_name',
                    'p.type as product_type',
                    'v.name as variation_name',
                    'pv.name as product_variation_name',
                    'v.sub_sku as sku',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->get();
        $product_details_by_customer_group = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('variations AS v', 'TSL.variation_id', '=', 'v.id')
                ->join('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products AS p', 'v.product_id', '=', 'p.id')
                ->join('contacts AS c', 'c.id', '=', 'transactions.contact_id')
                ->leftJoin('customer_groups AS cg', 'cg.id', '=', 'c.customer_group_id')
                ->leftJoin('selling_price_groups AS spg', 'spg.id', '=', 'cg.selling_price_group_id')
                ->where('TSL.children_type', '!=', 'combo')
                ->groupBy('cg.id') // group by customer group and contact id
                ->select(
                    'p.name as product_name',
                    'c.name as contact_name',
                    'cg.name as group_name',
                    'cg.amount as amount',
                    'cg.price_calculation_type',
                    'cg.selling_price_group_id',
                    'spg.name as spg_name',
                    'p.type as product_type',
                    'v.name as variation_name',
                    'pv.name as product_variation_name',
                    'v.sub_sku as sku',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax * TSL.quantity) as total_amount')
                )
                ->get();
            

                // Query to fetch product details by category
                $product_details_by_category = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('products AS P', 'TSL.product_id', '=', 'P.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->leftJoin('categories AS c', 'P.category_id', '=', 'c.id')
                ->groupBy('c.id')
                ->select(
                    'c.name as category_name',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax * TSL.quantity) as total_amount'),
                    DB::raw('SUM(
                        CASE 
                            WHEN TSL.line_discount_type = "fixed" THEN ROUND(TSL.line_discount_amount, 2)
                            WHEN TSL.line_discount_type = "percentage" THEN ROUND((TSL.line_discount_amount / 100) * (TSL.unit_price * TSL.quantity), 2)
                            ELSE 0
                        END
                    ) as total_line_discount')
                )
                ->get();

            // Query to fetch sell details
            $sell_details = Transaction::with(['payments' => function ($query) use ($user_id) {
                $query->where('created_by', $user_id);
                }])
                ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                    $join->on('transactions.id', '=', 'tsl.transaction_id')
                        ->whereNull('tsl.parent_sell_line_id');
                })
                ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                ->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')
                ->leftJoin('users as dp', 'transactions.delivery_person', '=', 'dp.id')
                ->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
                ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
                ->leftJoin('types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id')
                ->where(function ($query) use ($user_id, $open_time, $close_time) {
                    $query->where(function ($query) use ($user_id, $open_time, $close_time) {
                        // Transactions created by $user_id
                        $query->where('transactions.created_by', $user_id)
                              ->whereBetween('transactions.created_at', [$open_time, $close_time]);
                    })->orWhere(function ($query) use ($user_id, $open_time, $close_time) {
                        // Filter based on tp.created_by
                        $query->where('transactions.created_by', '!=', $user_id)
                              ->where('tp.created_by', $user_id)
                              ->whereBetween('tp.created_at', [$open_time, $close_time]);
                    });
                })
                ->where('transactions.type', 'sell')          
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->select(
                    DB::raw('DISTINCT transactions.id'),
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.type',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'transactions.invoice_no as invoice_no_text',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.contact_id',
                    'contacts.supplier_business_name',
                    'contacts.customer_group_id',
                    'transactions.status',
                    'transactions.payment_status',
                    'transactions.final_total',
                    'transactions.tax_amount',
                    'transactions.due_key',
                    'tsl.line_discount_type',
                    'tsl.line_discount_amount',
                    'tsl.unit_price',
                    'tsl.quantity',
                    'tsl.quantity_returned',
                    'tp.method',
                    'tp.created_by',
                    'transactions.discount_amount',
                    'transactions.discount_type',
                    'transactions.total_before_tax',
                    'transactions.rp_redeemed',
                    'transactions.rp_redeemed_amount',
                    'transactions.rp_earned',
                    'transactions.types_of_service_id',
                    'transactions.shipping_status',
                    'transactions.shipping_charges',
                    'transactions.pay_term_number',
                    'transactions.pay_term_type',
                    'transactions.additional_notes',
                    'transactions.staff_note',
                    'transactions.shipping_details',
                    'transactions.document',
                    'transactions.created_at',
                    DB::raw('SUM(
                        CASE 
                            WHEN tsl.line_discount_type = "fixed" THEN ROUND(tsl.line_discount_amount, 2)
                            WHEN tsl.line_discount_type = "percentage" THEN ROUND((tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity), 2)
                            ELSE 0
                        END
                    ) as total_line_discount'),
                    DB::raw('SUM(
                        CASE 
                            WHEN transactions.discount_type = "fixed" THEN ROUND(transactions.discount_amount, 2)
                            WHEN transactions.discount_type = "percentage" THEN ROUND((transactions.discount_amount / 100) * transactions.final_total, 2)
                            ELSE 0
                        END
                    ) as total_discount')
                )
            ->groupBy('transactions.id')
            ->orderBy('transactions.id', 'desc')
            ->get();

                // Calculate total discount amount for transactions
                $Total_discount_amount = $sell_details->sum(function ($detail) {
                if ($detail->discount_type == 'fixed') {
                    return round($detail->discount_amount, 2);
                } elseif ($detail->discount_type == 'percentage') {
                    return round(($detail->discount_amount / 100) * $detail->total_before_tax, 2);
                }
                return 0;
                });

                // Array of bills where transaction-level discount > 0
                $bills_with_discount = $sell_details->filter(function ($detail) {
                return $detail->discount_amount > 0;
                });

                // Array of bills where line-level discount > 0
                $bills_with_line_discount = $sell_details->filter(function ($detail) {
                return $detail->line_discount_amount > 0;
                });

                // Calculate the summary values
                $summary = [
                    'total_bills' => $sell_details->count(),
                    'total_shipping_bills' => $sell_details->where('shipping_charges', '>', 0)->count(),
                    'total_due_bills' => $sell_details->where('due_key', 'due')->count(),
                    // Calculate line-level discount with consistency in logic
                    'total_line_discount_amount' => $sell_details->sum(function ($detail) {
                        return $detail->total_line_discount ?? 0;  // Ensure there's a fallback for null values
                    }),
                    // Calculate transaction-level discount
                    'total_discount_amount' => $Total_discount_amount, // Already calculated above
                    'total_shipping_charge' => $sell_details->sum('shipping_charges'),
                    'bills_with_discount' => $bills_with_discount->count(),
                    'bills_with_line_discount' => $bills_with_line_discount->count()
                ];


        // Shipping details
        $shipping_details = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->where('transactions.shipping_charges', '>', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->select(
                    'transactions.delivered_to',
                    'transactions.shipping_address',
                    'transactions.shipping_status',
                    'transactions.delivery_person',
                    'transactions.shipping_charges',
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->groupBy('transactions.delivered_to', 'transactions.shipping_charges', 'transactions.shipping_address', 'transactions.shipping_status', 'transactions.delivery_person')
                ->get();

        //If types of service
        $types_of_service_details = null;
        if ($is_types_of_service_enabled) {
            $types_of_service_details = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transaction_date', [$open_time, $close_time])
                ->where('transactions.is_direct_sale', 0)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->leftjoin('types_of_services AS tos', 'tos.id', '=', 'transactions.types_of_service_id')
                ->groupBy('tos.id')
                ->select(
                    'tos.name as types_of_service_name',
                    DB::raw('SUM(final_total) as total_sales')
                )
                ->orderBy('total_sales', 'desc')
                ->get();
        }

        $transaction_details = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.is_direct_sale', 0)
                ->where('transactions.status', 'final')
                ->select(
                    DB::raw('SUM(tax_amount) as total_tax'),
                    DB::raw('SUM(IF(discount_type = "percentage", total_before_tax*discount_amount/100, discount_amount)) as total_discount'),
                    DB::raw('SUM(final_total) as total_sales'),
                    DB::raw('SUM(shipping_charges) as total_shipping_charges')
                )
                ->first();

        return ['product_details_by_brand' => $product_details_by_brand,
            'transaction_details' => $transaction_details,
            'types_of_service_details' => $types_of_service_details,
            'product_details_by_customer' => $product_details_by_customer,
            'product_details_by_category' => $product_details_by_category,
            'product_details' => $product_details,
            'shipping_details' => $shipping_details,
            'sell_details' => $sell_details,
            'summary' => $summary,
            'product_details_by_customer_group' => $product_details_by_customer_group,
        ];
    }

    public function getClinicRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled = false)
    {
        $product_details_by_brand = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('products AS P', 'TSL.product_id', '=', 'P.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->leftjoin('brands AS B', 'P.brand_id', '=', 'B.id')
                ->groupBy('B.id')
                ->select(
                    'B.name as brand_name',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->orderByRaw('CASE WHEN brand_name IS NULL THEN 2 ELSE 1 END, brand_name')
                ->get();


        $product_details = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('variations AS v', 'TSL.variation_id', '=', 'v.id')
                ->join('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products AS p', 'v.product_id', '=', 'p.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->groupBy('v.id')
                ->select(
                    'p.name as product_name',
                    'p.type as product_type',
                    'v.name as variation_name',
                    'pv.name as product_variation_name',
                    'v.sub_sku as sku',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->get();
        
        $product_details_by_customer = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('variations AS v', 'TSL.variation_id', '=', 'v.id')
                ->join('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products AS p', 'v.product_id', '=', 'p.id')
                ->join('contacts AS c', 'c.id', '=', 'transactions.contact_id')
                ->where('TSL.children_type', '!=', 'combo')
                ->groupBy('c.id')
                ->select(
                    'p.name as product_name',
                    'c.name as contact_name',
                    'p.type as product_type',
                    'v.name as variation_name',
                    'pv.name as product_variation_name',
                    'v.sub_sku as sku',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->get();
        $product_details_by_customer_group = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('variations AS v', 'TSL.variation_id', '=', 'v.id')
                ->join('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products AS p', 'v.product_id', '=', 'p.id')
                ->join('contacts AS c', 'c.id', '=', 'transactions.contact_id')
                ->leftJoin('customer_groups AS cg', 'cg.id', '=', 'c.customer_group_id')
                ->leftJoin('selling_price_groups AS spg', 'spg.id', '=', 'cg.selling_price_group_id')
                ->where('TSL.children_type', '!=', 'combo')
                ->groupBy('cg.id') // group by customer group and contact id
                ->select(
                    'p.name as product_name',
                    'c.name as contact_name',
                    'cg.name as group_name',
                    'cg.amount as amount',
                    'cg.price_calculation_type',
                    'cg.selling_price_group_id',
                    'spg.name as spg_name',
                    'p.type as product_type',
                    'v.name as variation_name',
                    'pv.name as product_variation_name',
                    'v.sub_sku as sku',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax * TSL.quantity) as total_amount')
                )
                ->get();
            

            // Query to fetch therapy details by category
            $therapy_details_by_category = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('products AS P', 'TSL.product_id', '=', 'P.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->where('transactions.sub_type', 'therapy')
                ->leftJoin('categories AS c', 'P.category_id', '=', 'c.id')
                ->groupBy('c.id')
                ->select(
                    'c.name as category_name',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax * TSL.quantity) as total_amount'),
                    DB::raw('SUM(
                        CASE 
                            WHEN TSL.line_discount_type = "fixed" THEN ROUND(TSL.line_discount_amount, 2)
                            WHEN TSL.line_discount_type = "percentage" THEN ROUND((TSL.line_discount_amount / 100) * (TSL.unit_price * TSL.quantity), 2)
                            ELSE 0
                        END
                    ) as total_line_discount')
                )
                ->get();

            // Query to fetch test details by category
            $test_details_by_category = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('products AS P', 'TSL.product_id', '=', 'P.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->where('transactions.sub_type', 'test')
                ->leftJoin('categories AS c', 'P.category_id', '=', 'c.id')
                ->groupBy('c.id')
                ->select(
                    'c.name as category_name',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax * TSL.quantity) as total_amount'),
                    DB::raw('SUM(
                        CASE 
                            WHEN TSL.line_discount_type = "fixed" THEN ROUND(TSL.line_discount_amount, 2)
                            WHEN TSL.line_discount_type = "percentage" THEN ROUND((TSL.line_discount_amount / 100) * (TSL.unit_price * TSL.quantity), 2)
                            ELSE 0
                        END
                    ) as total_line_discount')
                )
                ->get();
            // Query to fetch ipd details by category
            $ipd_details_by_category = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('products AS P', 'TSL.product_id', '=', 'P.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->where('transactions.sub_type', 'ipd')
                ->leftJoin('categories AS c', 'P.category_id', '=', 'c.id')
                ->groupBy('c.id')
                ->select(
                    'c.name as category_name',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax * TSL.quantity) as total_amount'),
                    DB::raw('SUM(
                        CASE 
                            WHEN TSL.line_discount_type = "fixed" THEN ROUND(TSL.line_discount_amount, 2)
                            WHEN TSL.line_discount_type = "percentage" THEN ROUND((TSL.line_discount_amount / 100) * (TSL.unit_price * TSL.quantity), 2)
                            ELSE 0
                        END
                    ) as total_line_discount')
                )
                ->get();

            // Query to fetch consultation details by category
            $consultation_details_by_category = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('products AS P', 'TSL.product_id', '=', 'P.id')
                ->where('TSL.children_type', '!=', 'combo')
                ->where('transactions.sub_type', 'consultation')
                ->leftJoin('categories AS c', 'P.category_id', '=', 'c.id')
                ->groupBy('c.id')
                ->select(
                    'c.name as category_name',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax * TSL.quantity) as total_amount'),
                    DB::raw('SUM(
                        CASE 
                            WHEN TSL.line_discount_type = "fixed" THEN ROUND(TSL.line_discount_amount, 2)
                            WHEN TSL.line_discount_type = "percentage" THEN ROUND((TSL.line_discount_amount / 100) * (TSL.unit_price * TSL.quantity), 2)
                            ELSE 0
                        END
                    ) as total_line_discount')
                )
                ->get();

            // Query to fetch sell details
            $sell_details = Transaction::with(['payments' => function ($query) use ($user_id) {
                    $query->where('created_by', $user_id);
                }])
                ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                    $join->on('transactions.id', '=', 'tsl.transaction_id')
                        ->whereNull('tsl.parent_sell_line_id');
                })
                ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                ->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')
                ->leftJoin('users as dp', 'transactions.delivery_person', '=', 'dp.id')
                ->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
                ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
                ->leftJoin('types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id')
                ->where(function ($query) use ($user_id, $open_time, $close_time) {
                    $query->where(function ($query) use ($user_id, $open_time, $close_time) {
                        // Transactions created by $user_id
                        $query->where('transactions.created_by', $user_id)
                              ->whereBetween('transactions.created_at', [$open_time, $close_time]);
                    })->orWhere(function ($query) use ($user_id, $open_time, $close_time) {
                        // Filter based on tp.created_by
                        $query->where('transactions.created_by', '!=', $user_id)
                              ->where('tp.created_by', $user_id)
                              ->whereBetween('tp.created_at', [$open_time, $close_time]);
                    });
                })
                ->where('transactions.type', 'sell')          
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->select(
                    DB::raw('DISTINCT transactions.id'),
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.type',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'transactions.invoice_no as invoice_no_text',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.contact_id',
                    'contacts.supplier_business_name',
                    'contacts.customer_group_id',
                    'transactions.status',
                    'transactions.payment_status',
                    'transactions.final_total',
                    'transactions.tax_amount',
                    'transactions.due_key',
                    'tsl.line_discount_type',
                    'tsl.line_discount_amount',
                    'tsl.unit_price',
                    'tsl.quantity',
                    'tsl.quantity_returned',
                    'tp.method',
                    'tp.created_by',
                    'transactions.discount_amount',
                    'transactions.discount_type',
                    'transactions.total_before_tax',
                    'transactions.rp_redeemed',
                    'transactions.rp_redeemed_amount',
                    'transactions.rp_earned',
                    'transactions.types_of_service_id',
                    'transactions.shipping_status',
                    'transactions.shipping_charges',
                    'transactions.pay_term_number',
                    'transactions.pay_term_type',
                    'transactions.additional_notes',
                    'transactions.staff_note',
                    'transactions.shipping_details',
                    'transactions.document',
                    'transactions.created_at',
                    DB::raw('SUM(
                        CASE 
                            WHEN tsl.line_discount_type = "fixed" THEN ROUND(tsl.line_discount_amount, 2)
                            WHEN tsl.line_discount_type = "percentage" THEN ROUND((tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity), 2)
                            ELSE 0
                        END
                    ) as total_line_discount'),
                    DB::raw('SUM(
                        CASE 
                            WHEN transactions.discount_type = "fixed" THEN ROUND(transactions.discount_amount, 2)
                            WHEN transactions.discount_type = "percentage" THEN ROUND((transactions.discount_amount / 100) * transactions.final_total, 2)
                            ELSE 0
                        END
                    ) as total_discount')
                )
            ->groupBy('transactions.id')
            ->orderBy('transactions.id', 'desc')
            ->get();

            $sell_details_by_sub_type = Transaction::leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->where(function ($query) use ($user_id, $open_time, $close_time) {
                    $query->where(function ($query) use ($user_id, $open_time, $close_time) {
                        $query->where('transactions.created_by', $user_id)
                            ->whereBetween('transactions.created_at', [$open_time, $close_time]);
                    })->orWhere(function ($query) use ($user_id, $open_time, $close_time) {
                        $query->where('transactions.created_by', '!=', $user_id)
                            ->where('tp.created_by', $user_id)
                            ->whereBetween('tp.created_at', [$open_time, $close_time]);
                    });
                })
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->select(
                    'transactions.sub_type',
                    DB::raw('COUNT(DISTINCT transactions.id) as bill_count'),
                    DB::raw('SUM(DISTINCT transactions.total_before_tax) as total_amount'),
                    DB::raw('SUM(DISTINCT transactions.final_total) as total_payable'),

                    // Corrected payment summation
                    DB::raw('SUM(CASE WHEN tp.is_return = 1 THEN -1 * tp.amount ELSE tp.amount END) as total_paid'),

                    // Corrected due amount calculation
                    DB::raw('SUM(DISTINCT transactions.final_total) - SUM(CASE WHEN tp.is_return = 1 THEN -1 * tp.amount ELSE tp.amount END) as due_amount'),

                    // Change return should sum only returned payments
                    DB::raw('SUM(CASE WHEN tp.is_return = 1 THEN tp.amount ELSE 0 END) as change_return_amount'),

                    // **Fixed Total Discount Calculation (Prevents Double Counting)**
                    DB::raw('SUM( 
                        CASE 
                            WHEN transactions.discount_type = "fixed" THEN transactions.discount_amount
                            WHEN transactions.discount_type = "percentage" THEN (transactions.discount_amount / 100) * transactions.total_before_tax
                            ELSE 0
                        END
                    ) as total_discount'),

                    // Payments by method
                    DB::raw('SUM(CASE WHEN tp.method = "cash" AND tp.is_return = 0 THEN tp.amount ELSE 0 END) - SUM(CASE WHEN tp.method = "cash" AND tp.is_return = 1 THEN tp.amount ELSE 0 END) as cash_payment'),
                    DB::raw('SUM(CASE WHEN tp.method = "card" AND tp.is_return = 0 THEN tp.amount ELSE 0 END) - SUM(CASE WHEN tp.method = "card" AND tp.is_return = 1 THEN tp.amount ELSE 0 END) as card_payment'),
                    DB::raw('SUM(CASE WHEN tp.method = "custom_pay_1" AND tp.is_return = 0 THEN tp.amount ELSE 0 END) - SUM(CASE WHEN tp.method = "custom_pay_1" AND tp.is_return = 1 THEN tp.amount ELSE 0 END) as custom_pay_1'),


                    // **Fixed Line Discount Calculation (Prevents Multiple Summation per Payment Row)**
                    DB::raw('(SELECT SUM(
                        CASE 
                            WHEN tsl.line_discount_type = "fixed" THEN tsl.line_discount_amount
                            WHEN tsl.line_discount_type = "percentage" THEN (tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity)
                            ELSE 0
                        END
                    ) FROM transaction_sell_lines AS tsl WHERE tsl.transaction_id = transactions.id) as total_line_discount')
                )
                ->groupBy('transactions.sub_type')
                ->get();


            $sell_return_details_by_sub_type = Transaction::leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->where(function ($query) use ($user_id, $open_time, $close_time) {
                    $query->where(function ($query) use ($user_id, $open_time, $close_time) {
                        $query->where('transactions.created_by', $user_id)
                            ->whereBetween('transactions.created_at', [$open_time, $close_time]);
                    })->orWhere(function ($query) use ($user_id, $open_time, $close_time) {
                        $query->where('transactions.created_by', '!=', $user_id)
                            ->where('tp.created_by', $user_id)
                            ->whereBetween('tp.created_at', [$open_time, $close_time]);
                    });
                })
                ->where('transactions.type', 'sell_return')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->select(
                    'transactions.sub_type',
                    DB::raw('COUNT(DISTINCT transactions.id) as bill_count'),
                    DB::raw('SUM(DISTINCT transactions.total_before_tax) as total_amount'),
                    DB::raw('SUM(DISTINCT transactions.final_total) as total_payable'),

                    // Corrected payment summation
                    DB::raw('SUM(CASE WHEN tp.is_return = 1 THEN -1 * tp.amount ELSE tp.amount END) as total_paid'),

                    // Corrected due amount calculation
                    DB::raw('SUM(DISTINCT transactions.final_total) - SUM(CASE WHEN tp.is_return = 1 THEN -1 * tp.amount ELSE tp.amount END) as due_amount'),

                    // Change return should sum only returned payments
                    DB::raw('SUM(CASE WHEN tp.is_return = 1 THEN tp.amount ELSE 0 END) as change_return_amount'),

                    // **Fixed Total Discount Calculation (Prevents Double Counting)**
                    DB::raw('SUM( 
                        CASE 
                            WHEN transactions.discount_type = "fixed" THEN transactions.discount_amount
                            WHEN transactions.discount_type = "percentage" THEN (transactions.discount_amount / 100) * transactions.total_before_tax
                            ELSE 0
                        END
                    ) as total_discount'),

                    // Payments by method
                    DB::raw('SUM(CASE WHEN tp.method = "cash" AND tp.is_return = 0 THEN tp.amount ELSE 0 END) - SUM(CASE WHEN tp.method = "cash" AND tp.is_return = 1 THEN tp.amount ELSE 0 END) as cash_payment'),
                    DB::raw('SUM(CASE WHEN tp.method = "card" AND tp.is_return = 0 THEN tp.amount ELSE 0 END) - SUM(CASE WHEN tp.method = "card" AND tp.is_return = 1 THEN tp.amount ELSE 0 END) as card_payment'),
                    DB::raw('SUM(CASE WHEN tp.method = "custom_pay_1" AND tp.is_return = 0 THEN tp.amount ELSE 0 END) - SUM(CASE WHEN tp.method = "custom_pay_1" AND tp.is_return = 1 THEN tp.amount ELSE 0 END) as custom_pay_1'),


                    // **Fixed Line Discount Calculation (Prevents Multiple Summation per Payment Row)**
                    DB::raw('(SELECT SUM(
                        CASE 
                            WHEN tsl.line_discount_type = "fixed" THEN tsl.line_discount_amount
                            WHEN tsl.line_discount_type = "percentage" THEN (tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity)
                            ELSE 0
                        END
                    ) FROM transaction_sell_lines AS tsl WHERE tsl.transaction_id = transactions.return_parent_id) as total_line_discount')
                )
                ->groupBy('transactions.sub_type')
                ->get();
            
                $sell_and_return_details_by_sub_type = Transaction::leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->where(function ($query) use ($user_id, $open_time, $close_time) {
                    $query->where(function ($query) use ($user_id, $open_time, $close_time) {
                        $query->where('transactions.created_by', $user_id)
                            ->whereBetween('transactions.created_at', [$open_time, $close_time]);
                    })->orWhere(function ($query) use ($user_id, $open_time, $close_time) {
                        $query->where('transactions.created_by', '!=', $user_id)
                            ->where('tp.created_by', $user_id)
                            ->whereBetween('tp.created_at', [$open_time, $close_time]);
                    });
                })
                ->whereIn('transactions.type', ['sell', 'sell_return'])
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->select(
                    'transactions.sub_type',
                    DB::raw('COUNT(DISTINCT CASE WHEN transactions.type = "sell" THEN transactions.id END) as bill_count'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell" THEN transactions.total_before_tax ELSE 0 END) as total_amount'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell" THEN transactions.final_total ELSE 0 END) as total_payable'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell" AND tp.is_return = 1 THEN -1 * tp.amount ELSE tp.amount END) as total_paid'),
                    DB::raw('SUM(DISTINCT transactions.final_total) - SUM(CASE WHEN tp.is_return = 1 THEN -1 * tp.amount ELSE tp.amount END) as due_amount'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell_return" THEN tp.amount ELSE 0 END) as return_amount'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell_return" AND tp.method = "cash" THEN tp.amount ELSE 0 END) as return_cash_amount'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell_return" AND tp.method = "card" THEN tp.amount ELSE 0 END) as return_card_amount'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell_return" AND tp.method = "custom_pay_1" THEN tp.amount ELSE 0 END) as return_custom_pay_1_amount'),
                    DB::raw('SUM(DISTINCT
                        CASE 
                            WHEN transactions.discount_type = "fixed" THEN transactions.discount_amount
                            WHEN transactions.discount_type = "percentage" THEN (transactions.discount_amount / 100) * transactions.total_before_tax
                            ELSE 0
                        END
                    ) as total_discount'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell" AND tp.method = "cash" AND tp.is_return = 0 THEN tp.amount ELSE 0 END) - SUM(CASE WHEN transactions.type = "sell" AND tp.method = "cash" AND tp.is_return = 1 THEN tp.amount ELSE 0 END) as cash_payment'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell" AND tp.method = "card" AND tp.is_return = 0 THEN tp.amount ELSE 0 END) - SUM(CASE WHEN transactions.type = "sell" AND tp.method = "card" AND tp.is_return = 1 THEN tp.amount ELSE 0 END) as card_payment'),
                    DB::raw('SUM(DISTINCT CASE WHEN transactions.type = "sell" AND tp.method = "custom_pay_1" AND tp.is_return = 0 THEN tp.amount ELSE 0 END) - SUM(CASE WHEN transactions.type = "sell" AND tp.method = "custom_pay_1" AND tp.is_return = 1 THEN tp.amount ELSE 0 END) as custom_pay_1'),
                    DB::raw('(SELECT SUM(DISTINCT
                        CASE 
                            WHEN tsl.line_discount_type = "fixed" THEN tsl.line_discount_amount
                            WHEN tsl.line_discount_type = "percentage" THEN (tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity)
                            ELSE 0
                        END
                    ) FROM transaction_sell_lines AS tsl WHERE tsl.transaction_id = transactions.id) as total_line_discount')
                )
                ->groupBy('transactions.sub_type') // Group only by sub_type
                ->get();

                // Calculate total discount amount for transactions
                $Total_discount_amount = $sell_details->sum(function ($detail) {
                if ($detail->discount_type == 'fixed') {
                    return round($detail->discount_amount, 2);
                } elseif ($detail->discount_type == 'percentage') {
                    return round(($detail->discount_amount / 100) * $detail->total_before_tax, 2);
                }
                return 0;
                });

                // Array of bills where transaction-level discount > 0
                $bills_with_discount = $sell_details->filter(function ($detail) {
                return $detail->discount_amount > 0;
                });

                // Array of bills where line-level discount > 0
                $bills_with_line_discount = $sell_details->filter(function ($detail) {
                return $detail->line_discount_amount > 0;
                });

                // Calculate the summary values
                $summary = [
                    'total_bills' => $sell_details->count(),
                    'total_shipping_bills' => $sell_details->where('shipping_charges', '>', 0)->count(),
                    'total_due_bills' => $sell_details->whereIn('payment_status', ['due', 'partial'])->count(),
                    // Calculate line-level discount with consistency in logic
                    'total_line_discount_amount' => $sell_details->sum(function ($detail) {
                        return $detail->total_line_discount ?? 0;  // Ensure there's a fallback for null values
                    }),
                    // Calculate transaction-level discount
                    'total_discount_amount' => $Total_discount_amount, // Already calculated above
                    'total_shipping_charge' => $sell_details->sum('shipping_charges'),
                    'bills_with_discount' => $bills_with_discount->count(),
                    'bills_with_line_discount' => $bills_with_line_discount->count()
                ];

                // Query to fetch credit sale payment details
                $creditSalepayment = Transaction::with(['payments' => function ($query) use ($user_id) {
                    $query->where('created_by', $user_id);
                    }])
                    ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                        $join->on('transactions.id', '=', 'tsl.transaction_id')
                            ->whereNull('tsl.parent_sell_line_id');
                    })
                    ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                    ->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')
                    ->leftJoin('users as dp', 'transactions.delivery_person', '=', 'dp.id')
                    ->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')
                    ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                    ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
                    ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
                    ->leftJoin('types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id')
                    ->where('tp.created_by', $user_id)
                    ->whereBetween('tp.created_at', [$open_time, $close_time])
                    ->whereNotBetween('transactions.created_at', [$open_time, $close_time])
                    ->where('transactions.type', 'sell')          
                    ->where('transactions.status', 'final')
                    ->where('transactions.is_direct_sale', 0)
                    ->select(
                        DB::raw('DISTINCT transactions.id'),
                        'transactions.id',
                        'transactions.transaction_date',
                        'transactions.type',
                        'transactions.is_direct_sale',
                        'transactions.invoice_no',
                        'transactions.invoice_no as invoice_no_text',
                        'contacts.name',
                        'contacts.mobile',
                        'contacts.contact_id',
                        'contacts.supplier_business_name',
                        'contacts.customer_group_id',
                        'transactions.status',
                        'transactions.payment_status',
                        'transactions.final_total',
                        'transactions.tax_amount',
                        'transactions.due_key',
                        'tsl.line_discount_type',
                        'tsl.line_discount_amount',
                        'tsl.unit_price',
                        'tsl.quantity',
                        'tsl.quantity_returned',
                        'tp.method',
                        'tp.created_by',
                        'tp.created_at as payment_date',
                        'transactions.discount_amount',
                        'transactions.discount_type',
                        'transactions.total_before_tax',
                        'transactions.rp_redeemed',
                        'transactions.rp_redeemed_amount',
                        'transactions.rp_earned',
                        'transactions.types_of_service_id',
                        'transactions.shipping_status',
                        'transactions.shipping_charges',
                        'transactions.pay_term_number',
                        'transactions.pay_term_type',
                        'transactions.additional_notes',
                        'transactions.staff_note',
                        'transactions.shipping_details',
                        'transactions.document',
                        'transactions.created_at',
                        DB::raw('SUM(
                            CASE 
                                WHEN tsl.line_discount_type = "fixed" THEN ROUND(tsl.line_discount_amount, 2)
                                WHEN tsl.line_discount_type = "percentage" THEN ROUND((tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity), 2)
                                ELSE 0
                            END
                        ) as total_line_discount'),
                        DB::raw('SUM(
                            CASE 
                                WHEN transactions.discount_type = "fixed" THEN ROUND(transactions.discount_amount, 2)
                                WHEN transactions.discount_type = "percentage" THEN ROUND((transactions.discount_amount / 100) * transactions.final_total, 2)
                                ELSE 0
                            END
                        ) as total_discount')
                    )
                ->groupBy('transactions.id')
                ->orderBy('transactions.id', 'desc')
                ->get();

                $consultation_sell_details = Transaction::with(['payments' => function ($query) use ($user_id) {
                    $query->where('created_by', $user_id);
                    }])
                    ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                        $join->on('transactions.id', '=', 'tsl.transaction_id')
                            ->whereNull('tsl.parent_sell_line_id');
                    })
                    ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                    ->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')
                    ->leftJoin('users as dp', 'transactions.delivery_person', '=', 'dp.id')
                    ->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')
                    ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                    ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
                    ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
                    ->leftJoin('types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id')
                    ->where(function ($query) use ($user_id) {
                        $query->where('transactions.created_by', $user_id)
                            ->orWhere(function ($query) use ($user_id) {
                                $query->where('transactions.created_by', '!=', $user_id)
                                      ->where('tp.created_by', $user_id);
                            });
                    })
                    ->whereBetween('tp.created_at', [$open_time, $close_time])
                    ->where('transactions.type', 'sell')          
                    ->where('transactions.status', 'final')
                    ->where('transactions.is_direct_sale', 0)
                    ->where('transactions.sub_type', 'consultation')
                    ->select(
                        DB::raw('DISTINCT transactions.id'),
                        'transactions.id',
                        'transactions.transaction_date',
                        'transactions.type',
                        'transactions.is_direct_sale',
                        'transactions.invoice_no',
                        'transactions.invoice_no as invoice_no_text',
                        'contacts.name',
                        'contacts.mobile',
                        'contacts.contact_id',
                        'contacts.supplier_business_name',
                        'contacts.customer_group_id',
                        'transactions.status',
                        'transactions.payment_status',
                        'transactions.final_total',
                        'transactions.tax_amount',
                        'transactions.due_key',
                        'tsl.line_discount_type',
                        'tsl.line_discount_amount',
                        'tsl.unit_price',
                        'tsl.quantity',
                        'tsl.quantity_returned',
                        'tp.method',
                        'tp.created_by',
                        'transactions.discount_amount',
                        'transactions.discount_type',
                        'transactions.total_before_tax',
                        'transactions.rp_redeemed',
                        'transactions.rp_redeemed_amount',
                        'transactions.rp_earned',
                        'transactions.types_of_service_id',
                        'transactions.shipping_status',
                        'transactions.shipping_charges',
                        'transactions.pay_term_number',
                        'transactions.pay_term_type',
                        'transactions.additional_notes',
                        'transactions.staff_note',
                        'transactions.shipping_details',
                        'transactions.document',
                        'transactions.created_at',
                        DB::raw('SUM(
                            CASE 
                                WHEN tsl.line_discount_type = "fixed" THEN ROUND(tsl.line_discount_amount, 2)
                                WHEN tsl.line_discount_type = "percentage" THEN ROUND((tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity), 2)
                                ELSE 0
                            END
                        ) as total_line_discount'),
                        DB::raw('SUM(
                            CASE 
                                WHEN transactions.discount_type = "fixed" THEN ROUND(transactions.discount_amount, 2)
                                WHEN transactions.discount_type = "percentage" THEN ROUND((transactions.discount_amount / 100) * transactions.final_total, 2)
                                ELSE 0
                            END
                        ) as total_discount')
                    )
                ->groupBy('transactions.id')
                ->orderBy('transactions.id', 'desc')
                ->get();

                $test_sell_details = Transaction::with(['payments' => function ($query) use ($user_id) {
                    $query->where('created_by', $user_id);
                    }])
                    ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                        $join->on('transactions.id', '=', 'tsl.transaction_id')
                            ->whereNull('tsl.parent_sell_line_id');
                    })
                    ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                    ->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')
                    ->leftJoin('users as dp', 'transactions.delivery_person', '=', 'dp.id')
                    ->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')
                    ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                    ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
                    ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
                    ->leftJoin('types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id')
                    ->where(function ($query) use ($user_id, $open_time, $close_time) {
                        $query->where(function ($query) use ($user_id, $open_time, $close_time) {
                            // Transactions created by $user_id
                            $query->where('transactions.created_by', $user_id)
                                  ->whereBetween('transactions.created_at', [$open_time, $close_time]);
                        })->orWhere(function ($query) use ($user_id, $open_time, $close_time) {
                            // Filter based on tp.created_by
                            $query->where('transactions.created_by', '!=', $user_id)
                                  ->where('tp.created_by', $user_id)
                                  ->whereBetween('tp.created_at', [$open_time, $close_time]);
                        });
                    })
                    ->where('transactions.type', 'sell')          
                    ->where('transactions.status', 'final')
                    ->where('transactions.is_direct_sale', 0)
                    ->where('transactions.sub_type', 'test')
                    ->select(
                        DB::raw('DISTINCT transactions.id'),
                        'transactions.id',
                        'transactions.transaction_date',
                        'transactions.type',
                        'transactions.is_direct_sale',
                        'transactions.invoice_no',
                        'transactions.invoice_no as invoice_no_text',
                        'contacts.name',
                        'contacts.mobile',
                        'contacts.contact_id',
                        'contacts.supplier_business_name',
                        'contacts.customer_group_id',
                        'transactions.status',
                        'transactions.payment_status',
                        'transactions.final_total',
                        'transactions.tax_amount',
                        'transactions.due_key',
                        'tsl.line_discount_type',
                        'tsl.line_discount_amount',
                        'tsl.unit_price',
                        'tsl.quantity',
                        'tsl.quantity_returned',
                        'tp.method',
                        'tp.created_by',
                        'transactions.discount_amount',
                        'transactions.discount_type',
                        'transactions.total_before_tax',
                        'transactions.rp_redeemed',
                        'transactions.rp_redeemed_amount',
                        'transactions.rp_earned',
                        'transactions.types_of_service_id',
                        'transactions.shipping_status',
                        'transactions.shipping_charges',
                        'transactions.pay_term_number',
                        'transactions.pay_term_type',
                        'transactions.additional_notes',
                        'transactions.staff_note',
                        'transactions.shipping_details',
                        'transactions.document',
                        'transactions.created_at',
                        DB::raw('SUM(
                            CASE 
                                WHEN tsl.line_discount_type = "fixed" THEN ROUND(tsl.line_discount_amount, 2)
                                WHEN tsl.line_discount_type = "percentage" THEN ROUND((tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity), 2)
                                ELSE 0
                            END
                        ) as total_line_discount'),
                        DB::raw('SUM(
                            CASE 
                                WHEN transactions.discount_type = "fixed" THEN ROUND(transactions.discount_amount, 2)
                                WHEN transactions.discount_type = "percentage" THEN ROUND((transactions.discount_amount / 100) * transactions.final_total, 2)
                                ELSE 0
                            END
                        ) as total_discount')
                    )
                ->groupBy('transactions.id')
                ->orderBy('transactions.id', 'desc')
                ->get();

                $ipd_sell_details = Transaction::with(['payments' => function ($query) use ($user_id) {
                    $query->where('created_by', $user_id);
                    }])
                    ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                        $join->on('transactions.id', '=', 'tsl.transaction_id')
                            ->whereNull('tsl.parent_sell_line_id');
                    })
                    ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                    ->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')
                    ->leftJoin('users as dp', 'transactions.delivery_person', '=', 'dp.id')
                    ->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')
                    ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                    ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
                    ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
                    ->leftJoin('types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id')
                    ->where(function ($query) use ($user_id, $open_time, $close_time) {
                        $query->where(function ($query) use ($user_id, $open_time, $close_time) {
                            // Transactions created by $user_id
                            $query->where('transactions.created_by', $user_id)
                                  ->whereBetween('transactions.created_at', [$open_time, $close_time]);
                        })->orWhere(function ($query) use ($user_id, $open_time, $close_time) {
                            // Filter based on tp.created_by
                            $query->where('transactions.created_by', '!=', $user_id)
                                  ->where('tp.created_by', $user_id)
                                  ->whereBetween('tp.created_at', [$open_time, $close_time]);
                        });
                    })
                    ->where('transactions.type', 'sell')          
                    ->where('transactions.status', 'final')
                    ->where('transactions.is_direct_sale', 0)
                    ->where('transactions.sub_type', 'ipd')
                    ->select(
                        DB::raw('DISTINCT transactions.id'),
                        'transactions.id',
                        'transactions.transaction_date',
                        'transactions.type',
                        'transactions.is_direct_sale',
                        'transactions.invoice_no',
                        'transactions.invoice_no as invoice_no_text',
                        'contacts.name',
                        'contacts.mobile',
                        'contacts.contact_id',
                        'contacts.supplier_business_name',
                        'contacts.customer_group_id',
                        'transactions.status',
                        'transactions.payment_status',
                        'transactions.final_total',
                        'transactions.tax_amount',
                        'transactions.due_key',
                        'tsl.line_discount_type',
                        'tsl.line_discount_amount',
                        'tsl.unit_price',
                        'tsl.quantity',
                        'tsl.quantity_returned',
                        'tp.method',
                        'tp.created_by',
                        'transactions.discount_amount',
                        'transactions.discount_type',
                        'transactions.total_before_tax',
                        'transactions.rp_redeemed',
                        'transactions.rp_redeemed_amount',
                        'transactions.rp_earned',
                        'transactions.types_of_service_id',
                        'transactions.shipping_status',
                        'transactions.shipping_charges',
                        'transactions.pay_term_number',
                        'transactions.pay_term_type',
                        'transactions.additional_notes',
                        'transactions.staff_note',
                        'transactions.shipping_details',
                        'transactions.document',
                        'transactions.created_at',
                        DB::raw('SUM(
                            CASE 
                                WHEN tsl.line_discount_type = "fixed" THEN ROUND(tsl.line_discount_amount, 2)
                                WHEN tsl.line_discount_type = "percentage" THEN ROUND((tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity), 2)
                                ELSE 0
                            END
                        ) as total_line_discount'),
                        DB::raw('SUM(
                            CASE 
                                WHEN transactions.discount_type = "fixed" THEN ROUND(transactions.discount_amount, 2)
                                WHEN transactions.discount_type = "percentage" THEN ROUND((transactions.discount_amount / 100) * transactions.final_total, 2)
                                ELSE 0
                            END
                        ) as total_discount')
                    )
                ->groupBy('transactions.id')
                ->orderBy('transactions.id', 'desc')
                ->get();

                $therapy_sell_details = Transaction::with(['payments' => function ($query) use ($user_id) {
                    $query->where('created_by', $user_id);
                    }])
                    ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                    ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                        $join->on('transactions.id', '=', 'tsl.transaction_id')
                            ->whereNull('tsl.parent_sell_line_id');
                    })
                    ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                    ->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')
                    ->leftJoin('users as dp', 'transactions.delivery_person', '=', 'dp.id')
                    ->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')
                    ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                    ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
                    ->leftJoin('transactions AS SR', 'transactions.id', '=', 'SR.return_parent_id')
                    ->leftJoin('types_of_services AS tos', 'transactions.types_of_service_id', '=', 'tos.id')
                    ->leftJoin('session_details as sd', function ($join) {
                        $join->on('transactions.id', '=', 'sd.transaction_id')
                             ->on('tsl.product_id', '=', 'sd.product_id') 
                             ->on('tsl.variation_id', '=', 'sd.variation_id');
                    })
                    ->where(function ($query) use ($user_id, $open_time, $close_time) {
                        $query->where(function ($query) use ($user_id, $open_time, $close_time) {
                            // Transactions created by $user_id
                            $query->where('transactions.created_by', $user_id)
                                  ->whereBetween('transactions.created_at', [$open_time, $close_time]);
                        })->orWhere(function ($query) use ($user_id, $open_time, $close_time) {
                            // Filter based on tp.created_by
                            $query->where('transactions.created_by', '!=', $user_id)
                                  ->where('tp.created_by', $user_id)
                                  ->whereBetween('tp.created_at', [$open_time, $close_time]);
                        });
                    })
                    ->where('transactions.type', 'sell')          
                    ->where('transactions.status', 'final')
                    ->where('transactions.is_direct_sale', 0)
                    ->where('transactions.sub_type', 'therapy')
                    ->select(
                        DB::raw('DISTINCT transactions.id'),
                        'transactions.id',
                        'transactions.transaction_date',
                        'transactions.type',
                        'transactions.is_direct_sale',
                        'transactions.invoice_no',
                        'transactions.invoice_no as invoice_no_text',
                        'contacts.name',
                        'contacts.mobile',
                        'contacts.contact_id',
                        'contacts.supplier_business_name',
                        'contacts.customer_group_id',
                        'transactions.status',
                        'transactions.payment_status',
                        'transactions.final_total',
                        'transactions.tax_amount',
                        'transactions.due_key',
                        'tsl.line_discount_type',
                        'tsl.line_discount_amount',
                        'tsl.unit_price',
                        'tsl.quantity',
                        'tsl.quantity_returned',
                        'tp.method',
                        'tp.created_by',
                        'transactions.discount_amount',
                        'transactions.discount_type',
                        'transactions.total_before_tax',
                        'transactions.rp_redeemed',
                        'transactions.rp_redeemed_amount',
                        'transactions.rp_earned',
                        'transactions.types_of_service_id',
                        'transactions.shipping_status',
                        'transactions.shipping_charges',
                        'transactions.pay_term_number',
                        'transactions.pay_term_type',
                        'transactions.additional_notes',
                        'transactions.staff_note',
                        'transactions.shipping_details',
                        'transactions.document',
                        'transactions.created_at',
                        'sd.session_no',
                        DB::raw('SUM(
                            CASE 
                                WHEN tsl.line_discount_type = "fixed" THEN ROUND(tsl.line_discount_amount, 2)
                                WHEN tsl.line_discount_type = "percentage" THEN ROUND((tsl.line_discount_amount / 100) * (tsl.unit_price * tsl.quantity), 2)
                                ELSE 0
                            END
                        ) as total_line_discount'),
                        DB::raw('SUM(
                            CASE 
                                WHEN transactions.discount_type = "fixed" THEN ROUND(transactions.discount_amount, 2)
                                WHEN transactions.discount_type = "percentage" THEN ROUND((transactions.discount_amount / 100) * transactions.final_total, 2)
                                ELSE 0
                            END
                        ) as total_discount')
                    )
                ->groupBy('transactions.id')
                ->orderBy('transactions.id', 'desc')
                ->get();

            $Therapy_product_details = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->where('transactions.sub_type', 'therapy')
                ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->leftJoin('variations AS v', 'TSL.variation_id', '=', 'v.id')
                ->leftJoin('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->leftJoin('products AS p', 'v.product_id', '=', 'p.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
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
                    'transactions.invoice_no as invoice_no_text',
                    'transactions.additional_notes',
                    'transactions.payment_status',
                    'transactions.discount_amount',
                    'transactions.discount_type',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.contact_id',
                    'contacts.supplier_business_name',
                    'contacts.customer_group_id',
                    'p.name as product_name',
                    'p.type as product_type',
                    'v.name as variation_name',
                    'pv.name as product_variation_name',
                    'v.sub_sku as sku',
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
                    ) as total_discount')
                )
            ->get();
            


            
        // Shipping details
        $shipping_details = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->where('transactions.shipping_charges', '>', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->select(
                    'transactions.delivered_to',
                    'transactions.shipping_address',
                    'transactions.shipping_status',
                    'transactions.delivery_person',
                    'transactions.shipping_charges',
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->groupBy('transactions.delivered_to', 'transactions.shipping_charges', 'transactions.shipping_address', 'transactions.shipping_status', 'transactions.delivery_person')
                ->get();

        //If types of service
        $types_of_service_details = null;
        if ($is_types_of_service_enabled) {
            $types_of_service_details = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transaction_date', [$open_time, $close_time])
                ->where('transactions.is_direct_sale', 0)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->leftjoin('types_of_services AS tos', 'tos.id', '=', 'transactions.types_of_service_id')
                ->groupBy('tos.id')
                ->select(
                    'tos.name as types_of_service_name',
                    DB::raw('SUM(final_total) as total_sales')
                )
                ->orderBy('total_sales', 'desc')
                ->get();
        }

        $transaction_details = Transaction::where('transactions.created_by', $user_id)
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.is_direct_sale', 0)
                ->where('transactions.status', 'final')
                ->select(
                    DB::raw('SUM(tax_amount) as total_tax'),
                    DB::raw('SUM(IF(discount_type = "percentage", total_before_tax*discount_amount/100, discount_amount)) as total_discount'),
                    DB::raw('SUM(final_total) as total_sales'),
                    DB::raw('SUM(shipping_charges) as total_shipping_charges')
                )
                ->first();

        return ['product_details_by_brand' => $product_details_by_brand,
            'transaction_details' => $transaction_details,
            'types_of_service_details' => $types_of_service_details,
            'product_details_by_customer' => $product_details_by_customer,
            'therapy_details_by_category' => $therapy_details_by_category,
            'product_details' => $product_details,
            'shipping_details' => $shipping_details,
            'sell_details' => $sell_details,
            'summary' => $summary,
            'product_details_by_customer_group' => $product_details_by_customer_group,
            'test_details_by_category' => $test_details_by_category,
            'consultation_details_by_category' => $consultation_details_by_category,
            'ipd_details_by_category' => $ipd_details_by_category,
            'consultation_sell_details' => $consultation_sell_details,
            'test_sell_details' => $test_sell_details,
            'ipd_sell_details' => $ipd_sell_details,
            'therapy_sell_details' => $therapy_sell_details,
            'Therapy_product_details' => $Therapy_product_details,
            'sell_details_by_sub_type' => $sell_details_by_sub_type,
            'sell_return_details_by_sub_type' => $sell_return_details_by_sub_type,
            'sell_and_return_details_by_sub_type' => $sell_and_return_details_by_sub_type,
            'credit_sell_payment_details' => $creditSalepayment,
        ];
    }

    public function getRegisterTransactionAcountDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled = false)
    {

            $product_details_by_customer = Transaction::whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_direct_sale', 0)
                ->join('transaction_sell_lines AS TSL', 'transactions.id', '=', 'TSL.transaction_id')
                ->join('variations AS v', 'TSL.variation_id', '=', 'v.id')
                ->join('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products AS p', 'v.product_id', '=', 'p.id')
                ->join('contacts AS c', 'c.id', '=', 'transactions.contact_id')
                ->where('TSL.children_type', '!=', 'combo')
                ->groupBy('c.id')
                ->select(
                    'p.name as product_name',
                    'c.name as contact_name',
                    'p.type as product_type',
                    'v.name as variation_name',
                    'pv.name as product_variation_name',
                    'v.sub_sku as sku',
                    DB::raw('SUM(TSL.quantity) as total_quantity'),
                    DB::raw('SUM(TSL.unit_price_inc_tax*TSL.quantity) as total_amount')
                )
                ->get();

            $sell_details = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                    $join->on('transactions.id', '=', 'tsl.transaction_id')
                        ->whereNull('tsl.parent_sell_line_id');
                })
                ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
                ->leftJoin('users as ss', 'transactions.res_waiter_id', '=', 'ss.id')
                ->leftJoin('users as dp', 'transactions.delivery_person', '=', 'dp.id')
                ->leftJoin('res_tables as tables', 'transactions.res_table_id', '=', 'tables.id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->leftJoin(
                    'transactions AS SR',
                    'transactions.id',
                    '=',
                    'SR.return_parent_id'
                )
                ->leftJoin(
                    'types_of_services AS tos',
                    'transactions.types_of_service_id',
                    '=',
                    'tos.id'
                )
                ->where('transactions.type', 'sell')
                ->whereBetween('transactions.created_at', [$open_time, $close_time])
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.type',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'transactions.invoice_no as invoice_no_text',
                    'contacts.name',
                    'contacts.mobile',
                    'contacts.contact_id',
                    'contacts.supplier_business_name',
                    'transactions.status',
                    'transactions.payment_status',
                    'transactions.final_total',
                    'transactions.tax_amount',
                    'transactions.due_key',
                    'tsl.line_discount_type',
                    'tsl.line_discount_amount',
                    'tsl.unit_price',
                    'tsl.quantity',
                    'tp.method',
                    'transactions.discount_amount',
                    'transactions.discount_type',
                    'transactions.total_before_tax',
                    'transactions.rp_redeemed',
                    'transactions.rp_redeemed_amount',
                    'transactions.rp_earned',
                    'transactions.types_of_service_id',
                    'transactions.shipping_status',
                    'transactions.shipping_charges',
                    'transactions.pay_term_number',
                    'transactions.pay_term_type',
                    'transactions.additional_notes',
                    'transactions.staff_note',
                    'transactions.shipping_details',
                    'transactions.document'
                )
                ->groupBy('transactions.id')
                ->get();

        $discount_amount = $sell_details->sum(function ($detail) {
                if ($detail->discount_type == 'fixed') {
                    return $detail->discount_amount;
                } elseif ($detail->discount_type == 'percentage') {
                    // Calculate percentage discount on the total_before_tax
                    return ($detail->discount_amount / 100) * $detail->total_before_tax;
                }
                return 0;
            });
        
          // Calculate the summary values
        $summary = [
            'total_bills' => $sell_details->count(),
            'total_shipping_bills' => $sell_details->where('shipping_charges', '>', 0)->count(),
            'total_due_bills' => $sell_details->where('due_key', 'due')->count(),
            // Calculate line-level discount
            'line_discount_amount' => $sell_details->sum(function ($detail) {
                if ($detail->line_discount_type == 'fixed') {
                    return $detail->line_discount_amount;
                } elseif ($detail->line_discount_type == 'percentage') {
                    // Calculate percentage discount on the line total (unit_price * quantity)
                    return ($detail->line_discount_amount / 100) * ($detail->unit_price * $detail->quantity);
                }
                return 0;
            }),
            // Calculate transaction-level discount
            'discount_amount' => $discount_amount, // Already calculated above
            'total_shipping_charge' => $sell_details->sum('shipping_charges')
        ];

            $transaction_details = Transaction::whereBetween('transactions.created_at', [$open_time, $close_time])
                ->where('transactions.type', 'sell')
                ->where('transactions.is_direct_sale', 0)
                ->where('transactions.status', 'final')
                ->select(
                    DB::raw('SUM(tax_amount) as total_tax'),
                    DB::raw('SUM(IF(discount_type = "percentage", total_before_tax*discount_amount/100, discount_amount)) as total_discount'),
                    DB::raw('SUM(final_total) as total_sales'),
                    DB::raw('SUM(shipping_charges) as total_shipping_charges')
                )
                ->first();
        
            return ['transaction_details' => $transaction_details,
            'product_details_by_customer' => $product_details_by_customer,
            'sell_details' => $sell_details,
            'summary' => $summary,
        ];
    }

    /**
     * Retrieves the currently opened cash register for the user
     *
     * @param $int user_id
     * @return obj
     */
    public function getCurrentCashRegister($user_id)
    {
        $register = CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->first();

        return $register;
    }
}
