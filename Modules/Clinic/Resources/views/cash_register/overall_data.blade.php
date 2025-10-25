<h3>
    @lang('cash_register.register_details')
    ({{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $open_time)->format('jS M, Y h:i A') }} -
    {{ \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $close_time)->format('jS M, Y h:i A') }})
</h3>

<div class="row">
    <div class="col-sm-12">
        <table class="table table-condensed">
            <tr>
                <th>@lang('lang_v1.payment_method')</th>
                <th>@lang('sale.sale')</th>
                <th>@lang('lang_v1.expense')</th>
            </tr>
            @if ($register_details->cash_in_hand != 0)
                <tr>
                    <td>@lang('cash_register.cash_in_hand'):</td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->cash_in_hand }}</span></td>
                    <td>--</td>
                </tr>
            @endif

            @if ($register_details->total_cash != 0 || $register_details->total_cash_expense != 0)
                <tr>
                    <td>@lang('cash_register.cash_payment'):</td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_cash }}</span></td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_cash_expense }}</span></td>
                </tr>
            @endif

            @if ($register_details->total_cheque != 0 || $register_details->total_cheque_expense != 0)
                <tr>
                    <td>@lang('cash_register.checque_payment'):</td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_cheque }}</span></td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_cheque_expense }}</span></td>
                </tr>
            @endif

            @if ($register_details->total_card != 0 || $register_details->total_card_expense != 0)
                <tr>
                    <td>@lang('cash_register.card_payment'):</td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_card }}</span></td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_card_expense }}</span></td>
                </tr>
            @endif

            @if ($register_details->total_bank_transfer != 0 || $register_details->total_bank_transfer_expense != 0)
                <tr>
                    <td>@lang('cash_register.bank_transfer'):</td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_bank_transfer }}</span></td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_bank_transfer_expense }}</span></td>
                </tr>
            @endif

            @if ($register_details->total_advance != 0 || $register_details->total_advance_expense != 0)
                <tr>
                    <td>@lang('lang_v1.advance_payment'):</td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_advance }}</span></td>
                    <td><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_advance_expense }}</span></td>
                </tr>
            @endif
            @if (array_key_exists('custom_pay_1', $payment_types) &&
                    ($register_details->total_custom_pay_1 != 0 || $register_details->total_custom_pay_1_expense != 0))
                <tr>
                    <td>
                        {{ $payment_types['custom_pay_1'] }}:
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_1 }}</span>
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_1_expense }}</span>
                    </td>
                </tr>
            @endif
            @if (array_key_exists('custom_pay_2', $payment_types) &&
                    ($register_details->total_custom_pay_2 != 0 || $register_details->total_custom_pay_2_expense != 0))
                <tr>
                    <td>
                        {{ $payment_types['custom_pay_2'] }}:
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_2 }}</span>
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_2_expense }}</span>
                    </td>
                </tr>
            @endif
            @if (array_key_exists('custom_pay_3', $payment_types))
                <tr>
                    <td>
                        {{ $payment_types['custom_pay_3'] }}:
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_3 }}</span>
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_3_expense }}</span>
                    </td>
                </tr>
            @endif
            @if (array_key_exists('custom_pay_4', $payment_types))
                <tr>
                    <td>
                        {{ $payment_types['custom_pay_4'] }}:
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_4 }}</span>
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_4_expense }}</span>
                    </td>
                </tr>
            @endif
            @if (array_key_exists('custom_pay_5', $payment_types))
                <tr>
                    <td>
                        {{ $payment_types['custom_pay_5'] }}:
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_5 }}</span>
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_5_expense }}</span>
                    </td>
                </tr>
            @endif
            @if (array_key_exists('custom_pay_6', $payment_types))
                <tr>
                    <td>
                        {{ $payment_types['custom_pay_6'] }}:
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_6 }}</span>
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_6_expense }}</span>
                    </td>
                </tr>
            @endif
            @if (array_key_exists('custom_pay_7', $payment_types))
                <tr>
                    <td>
                        {{ $payment_types['custom_pay_7'] }}:
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_7 }}</span>
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_custom_pay_7_expense }}</span>
                    </td>
                </tr>
            @endif
            @if ($register_details->total_other != 0 || $register_details->total_other_expense != 0)
                <tr>
                    <td>
                        @lang('cash_register.other_payments'):
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_other }}</span>
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_other_expense }}</span>
                    </td>
                </tr>
            @endif
        </table>
        <hr>
        <table class="table table-condensed">
            <tr>
                <td>
                    @lang('cash_register.total_sales'):
                </td>
                <td>
                    <span class="display_currency"
                        data-currency_symbol="true">{{ $register_details->total_sale }}</span>
                </td>
            </tr>
            @if ($register_details->total_refund != 0)
            <tr class="danger">
              
                <th>
                    @lang('cash_register.total_refund')
                </th>
                <td>
                  
                  <b><span class="display_currency"
                    data-currency_symbol="true">{{ $register_details->total_refund }}</span></b><br>
                 
                    <small>
                        @if ($register_details->total_cash_refund != 0)
                            Cash: <span class="display_currency"
                                data-currency_symbol="true">{{ $register_details->total_cash_refund }}</span><br>
                        @endif
                        @if ($register_details->total_cheque_refund != 0)
                            Cheque: <span class="display_currency"
                                data-currency_symbol="true">{{ $register_details->total_cheque_refund }}</span><br>
                        @endif
                        @if ($register_details->total_card_refund != 0)
                            Card: <span class="display_currency"
                                data-currency_symbol="true">{{ $register_details->total_card_refund }}</span><br>
                        @endif
                        @if ($register_details->total_bank_transfer_refund != 0)
                            Bank Transfer: <span class="display_currency"
                                data-currency_symbol="true">{{ $register_details->total_bank_transfer_refund }}</span><br>
                        @endif
                        @if (array_key_exists('custom_pay_1', $payment_types) && $register_details->total_custom_pay_1_refund != 0)
                            {{ $payment_types['custom_pay_1'] }}: <span class="display_currency"
                                data-currency_symbol="true">{{ $register_details->total_custom_pay_1_refund }}</span>
                        @endif
                        @if (array_key_exists('custom_pay_2', $payment_types) && $register_details->total_custom_pay_2_refund != 0)
                            {{ $payment_types['custom_pay_2'] }}: <span class="display_currency"
                                data-currency_symbol="true">{{ $register_details->total_custom_pay_2_refund }}</span>
                        @endif
                        @if (array_key_exists('custom_pay_3', $payment_types) && $register_details->total_custom_pay_3_refund != 0)
                            {{ $payment_types['custom_pay_3'] }}: <span class="display_currency"
                                data-currency_symbol="true">{{ $register_details->total_custom_pay_3_refund }}</span>
                        @endif
                        @if ($register_details->total_other_refund != 0)
                            Other: <span class="display_currency"
                                data-currency_symbol="true">{{ $register_details->total_other_refund }}</span>
                        @endif
                    </small>
                </td>
            </tr>
            @endif
            <tr class="success">
                <th>
                    @lang('lang_v1.total_payment')
                </th>
                <td>
                    <b><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund }}</span></b>
                </td>
            </tr>
            <tr class="success">
                <th>
                    @lang('lang_v1.credit_sales'):
                </th>
                <td>
                    <b><span class="display_currency"
                            data-currency_symbol="true">{{ $details['transaction_details']->total_sales - $register_details->total_sale }}</span></b>
                </td>
            </tr>
            <tr class="success">
                <th>
                    @lang('cash_register.total_sales'):
                </th>
                <td>
                    <b><span class="display_currency"
                            data-currency_symbol="true">{{ $details['transaction_details']->total_sales }}</span></b>
                </td>
            </tr>
            @if ($register_details->total_expense!= 0)
            <tr class="danger">
                <th>
                    @lang('report.total_expense'):
                </th>
                <td>
                    <b><span class="display_currency"
                            data-currency_symbol="true">{{ $register_details->total_expense }}</span></b>
                </td>
            </tr>
            @endif
        </table>
    </div>
</div>



@php
$total_amount = 0;
$total_quantity = 0;
foreach ($details['product_details_by_customer'] as $detail) {
    $total_quantity += $detail->total_quantity;
    $total_amount += $detail->total_amount;
}
$total_amount += $details['transaction_details']->total_tax;
$total_amount -= $details['transaction_details']->total_discount; // Apply discount
$total_amount += $details['transaction_details']->total_shipping_charges; // Add shipping
@endphp


<hr>
<div class="row">
    <div class="col-md-6">
        <h4>Summary</h4>
        <p>Total bills: <strong>{{ $details['summary']['total_bills'] }}</strong></p>
        <p>Total shipping bills: <strong>{{ $details['summary']['total_shipping_bills'] }}</strong></p>
        <p>Total due bills: <strong>{{ $details['summary']['total_due_bills'] }}</strong></p>
    </div>
    <div class="col-md-6">
        <h4>Financial Summary</h4>
        <p>Total Campaign Discount: <strong><span class="display_currency" data-currency_symbol="true">{{ $details['summary']['line_discount_amount'] }}</span></strong></p>
        <p>Total Special Discount: <strong><span class="display_currency" data-currency_symbol="true">{{ $details['summary']['discount_amount'] }}</span></strong></p>
        <p>Total Shipping Charge: <strong><span class="display_currency" data-currency_symbol="true">{{ $details['summary']['total_shipping_charge'] }}</span></strong></p>
        <p>Grand Total: <strong><span class="display_currency" data-currency_symbol="true">{{ $total_amount }}</span></strong></p>
    </div>
</div>

<hr>

@if (!empty($register_details->denominations))
    @php $total = 0; @endphp
    <div class="row">
        <div class="col-md-8 col-sm-12">
            <h3>@lang('lang_v1.cash_denominations')</h3>
            <table class="table table-slim">
                <thead>
                    <tr>
                        <th width="20%" class="text-right">@lang('lang_v1.denomination')</th>
                        <th width="20%">&nbsp;</th>
                        <th width="20%" class="text-center">@lang('lang_v1.count')</th>
                        <th width="20%">&nbsp;</th>
                        <th width="20%" class="text-left">@lang('sale.subtotal')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($register_details->denominations as $key => $value)
                        <tr>
                            <td class="text-right">{{ $key }}</td>
                            <td class="text-center">X</td>
                            <td class="text-center">{{ $value ?? 0 }}</td>
                            <td class="text-center">=</td>
                            <td class="text-left">@format_currency($key * $value)</td>
                        </tr>
                        @php $total += ($key * $value); @endphp
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-center">@lang('sale.total')</th>
                        <td>@format_currency($total)</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-xs-6">
        <b>@lang('report.user'):</b> {{ $register_details->user_name }}<br>
        <b>@lang('business.email'):</b> {{ $register_details->email }}<br>
        <b>@lang('business.business_location'):</b> {{ $register_details->location_name }}<br>
    </div>
    @if (!empty($register_details->closing_note))
        <div class="col-xs-6">
            <strong>@lang('cash_register.closing_note'):</strong><br>
            {{ $register_details->closing_note }}
        </div>
    @endif
</div>

<div class="mt-4">
    <button type="button" class="btn btn-primary no-print" aria-label="Print" onclick="window.print();">
        <i class="fa fa-print"></i> @lang('messages.print')
    </button>

    <a href="#" class="btn btn-default no-print">@lang('messages.cancel')</a>
</div>