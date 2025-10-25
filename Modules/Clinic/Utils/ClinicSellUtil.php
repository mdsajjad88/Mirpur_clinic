<?php

namespace Modules\Clinic\Utils;

use App\TransactionPayment;
use App\Utils\Util;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class ClinicSellUtil extends Util
{

    public function getListPayments($business_id, $sale_type = 'sell', $sub_type = null)
    {

        $payments = TransactionPayment::leftJoin('transactions', 'transaction_payments.transaction_id', '=', 'transactions.id')
            ->leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftJoin('users as u', 'transactions.created_by', '=', 'u.id')
            ->leftJoin('transaction_sell_lines as tsl', function ($join) {
                $join->on('transactions.id', '=', 'tsl.transaction_id')
                    ->whereNull('tsl.parent_sell_line_id');
            })
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', $sale_type);

        if ($sub_type !== null) {
            $payments->where(function ($query) use ($sub_type) {
                $query->where('transactions.sub_type', 'test')
                    ->orWhere('transactions.sub_type', 'therapy')
                    ->orWhere('transactions.sub_type', 'ipd')
                    ->orWhere('transactions.sub_type', 'consultation');
            });
        }

        $payments->select(
            'transaction_payments.id as payment_id',
            'transactions.id as transaction_id',
            'transactions.invoice_no',
            DB::raw('DATE_FORMAT(transactions.transaction_date, "%Y/%m/%d") as sale_date'),
            DB::raw('DATE_FORMAT(transaction_payments.paid_on, "%Y/%m/%d") as pay_date'),
            'contacts.name',
            'transactions.sub_type',
            'transaction_payments.method',
            DB::raw('COUNT(DISTINCT tsl.id) as total_items'),
            'transactions.final_total',
            DB::raw('IF(transaction_payments.is_return = 1, -1 * transaction_payments.amount, transaction_payments.amount) as total_paid'),
            DB::raw('(SELECT SUM(IF(TP2.is_return = 1, -1 * TP2.amount, TP2.amount)) FROM transaction_payments AS TP2 WHERE TP2.transaction_id = transactions.id) as transaction_total_paid'),
            'transactions.payment_status',
            DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as added_by")
        );

        if ($sale_type == 'sell') {
            $payments->where('transactions.status', 'final');
        }

        return $payments;
    }

    public function getListSells($business_id, $sale_type = 'sell', $sub_type = null)
    {
        $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
            ->leftJoin('users as dpro', 'transactions.reference_id', '=', 'dpro.id')
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
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', $sale_type);
        if ($sub_type !== null) {
            $sells->where(function ($query) use ($sub_type) {
                $query->where('transactions.sub_type', 'test')
                    ->orWhere('transactions.sub_type', 'therapy')
                    ->orWhere('transactions.sub_type', 'ipd')
                    ->orWhere('transactions.sub_type', 'consultation');
            });
        }
        $sells->select(
            'transactions.id',
            'transactions.transaction_date',
            'transactions.type',
            'transactions.is_direct_sale',
            'transactions.invoice_no',
            'transactions.invoice_no as invoice_no_text',
            'contacts.id as contact_main_id',
            'contacts.name',
            'contacts.mobile',
            'contacts.contact_id',
            'contacts.supplier_business_name',
            'transactions.status',
            'transactions.payment_status',
            'transactions.final_total',
            'transactions.tax_amount',
            'tsl.line_discount_type',
            'tsl.line_discount_amount',
            'tp.method',
            'transactions.discount_amount',
            'transactions.discount_type',
            'transactions.total_before_tax',
            'transactions.rp_redeemed',
            'transactions.rp_redeemed_amount',
            'transactions.rp_earned',
            'transactions.types_of_service_id',
            'transactions.shipping_status',
            'transactions.pay_term_number',
            'transactions.pay_term_type',
            'transactions.additional_notes',
            'transactions.staff_note',
            'transactions.shipping_details',
            'transactions.document',
            'transactions.shipping_custom_field_1',
            'transactions.shipping_custom_field_2',
            'transactions.shipping_custom_field_3',
            'transactions.shipping_custom_field_4',
            'transactions.shipping_custom_field_5',
            'transactions.custom_field_1',
            'transactions.custom_field_2',
            'transactions.custom_field_3',
            'transactions.custom_field_4',
            'transactions.sub_type',
            DB::raw('DATE_FORMAT(transactions.transaction_date, "%Y/%m/%d") as sale_date'),
            DB::raw('DATE_FORMAT(tp.paid_on, "%Y/%m/%d") as pay_date'),
            DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
            DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as total_paid'),
            'bl.name as business_location',
            DB::raw('COUNT(SR.id) as return_exists'),
            DB::raw('(SELECT SUM(TP2.amount) FROM transaction_payments AS TP2 WHERE
                        TP2.transaction_id=SR.id ) as return_paid'),
            DB::raw('COALESCE(SR.final_total, 0) as amount_return'),
            'SR.id as return_transaction_id',
            'tos.name as types_of_service_name',
            'transactions.service_custom_field_1',
            DB::raw('COUNT( DISTINCT tsl.id) as total_items'),
            DB::raw("CONCAT(COALESCE(ss.surname, ''),' ',COALESCE(ss.first_name, ''),' ',COALESCE(ss.last_name,'')) as waiter"),
            'tables.name as table_name',
            DB::raw("CONCAT(COALESCE(dpro.first_name, ''),' ',COALESCE(dpro.last_name,'')) as reference"),
            DB::raw('SUM(tsl.quantity - tsl.so_quantity_invoiced) as so_qty_remaining'),
            'transactions.is_export',
            DB::raw("CONCAT(COALESCE(dp.surname, ''),' ',COALESCE(dp.first_name, ''),' ',COALESCE(dp.last_name,'')) as delivery_person"),
            DB::raw('SUM(CASE WHEN tsl.line_discount_type = "percentage" THEN tsl.line_discount_amount * tsl.quantity * tsl.unit_price / 100 ELSE tsl.line_discount_amount END) as total_line_discount')
        );

        if ($sale_type == 'sell') {
            $sells->where('transactions.status', 'final');
        }
        return $sells;
    }
    public function getSellsListToday($business_id, $sale_type = 'sell', $sub_type)
    {

        $today = Carbon::today()->toDateString();
        $startOfDay = Carbon::parse($today)->startOfDay();  // '2024-12-12 00:00:00'
        $endOfDay = Carbon::parse($today)->endOfDay();
        $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
            ->leftJoin('users as dpro', 'transactions.reference_id', '=', 'dpro.id')
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
            ->whereBetween('transactions.transaction_date', [$startOfDay, $endOfDay])
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', $sale_type)
            ->whereIn('transactions.sub_type', $sub_type)
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
                'tsl.line_discount_type',
                'tsl.line_discount_amount',
                'tp.method',
                'transactions.discount_amount',
                'transactions.discount_type',
                'transactions.total_before_tax',
                'transactions.rp_redeemed',
                'transactions.rp_redeemed_amount',
                'transactions.rp_earned',
                'transactions.types_of_service_id',
                'transactions.shipping_status',
                'transactions.pay_term_number',
                'transactions.pay_term_type',
                'transactions.additional_notes',
                'transactions.staff_note',
                'transactions.shipping_details',
                'transactions.document',
                'transactions.shipping_custom_field_1',
                'transactions.shipping_custom_field_2',
                'transactions.shipping_custom_field_3',
                'transactions.shipping_custom_field_4',
                'transactions.shipping_custom_field_5',
                'transactions.custom_field_1',
                'transactions.custom_field_2',
                'transactions.custom_field_3',
                'transactions.custom_field_4',
                'transactions.sub_type',
                DB::raw('DATE_FORMAT(transactions.transaction_date, "%Y/%m/%d") as sale_date'),
                DB::raw("CONCAT(COALESCE(u.surname, ''),' ',COALESCE(u.first_name, ''),' ',COALESCE(u.last_name,'')) as added_by"),
                DB::raw('(SELECT SUM(IF(TP.is_return = 1,-1*TP.amount,TP.amount)) FROM transaction_payments AS TP WHERE
                        TP.transaction_id=transactions.id) as total_paid'),
                'bl.name as business_location',
                DB::raw('COUNT(SR.id) as return_exists'),
                DB::raw('(SELECT SUM(TP2.amount) FROM transaction_payments AS TP2 WHERE
                        TP2.transaction_id=SR.id ) as return_paid'),
                DB::raw('COALESCE(SR.final_total, 0) as amount_return'),
                'SR.id as return_transaction_id',
                'tos.name as types_of_service_name',
                'transactions.service_custom_field_1',
                DB::raw('COUNT( DISTINCT tsl.id) as total_items'),
                DB::raw("CONCAT(COALESCE(ss.surname, ''),' ',COALESCE(ss.first_name, ''),' ',COALESCE(ss.last_name,'')) as waiter"),
                'tables.name as table_name',
                DB::raw("CONCAT(COALESCE(dpro.first_name, ''),' ',COALESCE(dpro.last_name,'')) as reference"),
                DB::raw('SUM(tsl.quantity - tsl.so_quantity_invoiced) as so_qty_remaining'),
                'transactions.is_export',
                DB::raw("CONCAT(COALESCE(dp.surname, ''),' ',COALESCE(dp.first_name, ''),' ',COALESCE(dp.last_name,'')) as delivery_person"),
                DB::raw('SUM(CASE WHEN tsl.line_discount_type = "percentage" THEN tsl.line_discount_amount * tsl.quantity * tsl.unit_price / 100 ELSE tsl.line_discount_amount END) as total_line_discount')
            );

        if ($sale_type == 'sell') {
            $sells->where('transactions.status', 'final');
        }

        return $sells;
    }
}
