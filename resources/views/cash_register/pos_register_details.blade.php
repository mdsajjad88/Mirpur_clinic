<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>POS Print</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet" media="print">
    <style>
        @media print {
            /* Hide the button and other non-printable elements */
            .no-print {
                display: none;
            }
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 3px;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            .table th, .table td {
                border: 1px solid #000;
                padding: 3px;
                text-align: left;
            }
            h3 {
                margin: 0 0 10px 0;
            }
             /* FontAwesome icons Unicode mapping for print */
             .fa-print-icon:before {
                content: attr(data-icon);
                font-family: 'Font Awesome 5 Free';
                font-weight: 700; /* Make sure it's bold enough */
                font-size: 10px;  /* Adjust size if necessary */
                margin-left: 3px; /* Spacing for better alignment */
            }
        }
    </style>
</head>

<body onload="handlePrint()">
    <h3>@lang('cash_register.register_details') (
        {{ \Carbon::createFromFormat('Y-m-d H:i:s', $register_details->open_time)->format('jS M, Y h:i A') }} -
        {{ \Carbon::createFromFormat('Y-m-d H:i:s', $close_time)->format('jS M, Y h:i A') }} )</h3>
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
    <br>
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
                                data-currency_symbol="true">{{ number_format($register_details->total_cash,2) }}</span></td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_cash_expense,2) }}</span></td>
                    </tr>
                @endif

                @if ($register_details->total_cheque != 0 || $register_details->total_cheque_expense != 0)
                    <tr>
                        <td>@lang('cash_register.checque_payment'):</td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_cheque,2) }}</span></td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_cheque_expense,2) }}</span></td>
                    </tr>
                @endif

                @if ($register_details->total_card != 0 || $register_details->total_card_expense != 0)
                    <tr>
                        <td>@lang('cash_register.card_payment'):</td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_card,2) }}</span></td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_card_expense,2) }}</span></td>
                    </tr>
                @endif

                @if ($register_details->total_bank_transfer != 0 || $register_details->total_bank_transfer_expense != 0)
                    <tr>
                        <td>@lang('cash_register.bank_transfer'):</td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_bank_transfer,2) }}</span></td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_bank_transfer_expense,2) }}</span>
                        </td>
                    </tr>
                @endif

                @if ($register_details->total_advance != 0 || $register_details->total_advance_expense != 0)
                    <tr>
                        <td>@lang('lang_v1.advance_payment'):</td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_advance,2) }}</span></td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_advance_expense,2) }}</span></td>
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
                                data-currency_symbol="true">{{ number_format($register_details->total_custom_pay_1,2) }}</span>
                        </td>
                        <td>
                            <span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_custom_pay_1_expense,2) }}</span>
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
                @if (array_key_exists('custom_pay_3', $payment_types) &&
                        ($register_details->total_custom_pay_3 != 0 || $register_details->total_custom_pay_3_expense != 0))
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
                @if (array_key_exists('custom_pay_4', $payment_types) &&
                        ($register_details->total_custom_pay_4 != 0 || $register_details->total_custom_pay_4_expense != 0))
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
                @if (array_key_exists('custom_pay_5', $payment_types) &&
                        ($register_details->total_custom_pay_5 != 0 || $register_details->total_custom_pay_5_expense != 0))
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
                @if (array_key_exists('custom_pay_6', $payment_types) &&
                        ($register_details->total_custom_pay_6 != 0 || $register_details->total_custom_pay_6_expense != 0))
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
                @if (array_key_exists('custom_pay_7', $payment_types) &&
                        ($register_details->total_custom_pay_7 != 0 || $register_details->total_custom_pay_7_expense != 0))
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
                            data-currency_symbol="true">{{ number_format($register_details->total_sale,2) }}</span>
                    </td>
                </tr>
                @if ($register_details->total_refund != 0)
                    <tr class="danger">

                        <th>
                            @lang('cash_register.total_refund')
                        </th>
                        <td>

                            <b><span class="display_currency"
                                    data-currency_symbol="true">{{ number_format($register_details->total_refund,2) }}</span></b><br>

                            <small>
                                @if ($register_details->total_cash_refund != 0)
                                    Cash: <span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($register_details->total_cash_refund,2) }}</span><br>
                                @endif
                                @if ($register_details->total_cheque_refund != 0)
                                    Cheque: <span class="display_currency"
                                        data-currency_symbol="true">{{ $register_details->total_cheque_refund }}</span><br>
                                @endif
                                @if ($register_details->total_card_refund != 0)
                                    Card: <span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($register_details->total_card_refund,2) }}</span><br>
                                @endif
                                @if ($register_details->total_bank_transfer_refund != 0)
                                    Bank Transfer: <span class="display_currency"
                                        data-currency_symbol="true">{{ $register_details->total_bank_transfer_refund }}</span><br>
                                @endif
                                @if (array_key_exists('custom_pay_1', $payment_types) && $register_details->total_custom_pay_1_refund != 0)
                                    {{ $payment_types['custom_pay_1'] }}: <span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($register_details->total_custom_pay_1_refund,2) }}</span>
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
                                data-currency_symbol="true">{{ number_format($details['transaction_details']->total_sales,2) }}</span></b>
                    </td>
                </tr>
                @if ($register_details->total_expense != 0)
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
    
    <div class="row">
        <div class="col-md-12">
            <hr>
            <h3>Details by bill</h3>
            <!-- Sell Details Table -->
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Customer</th>
                        <th>Total Amount</th>
                        <th>Pmt Method</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($details['sell_details'] as $detail)
                    @php
                        $paymentMethods = $detail->payments->groupBy('method')->map(function ($payments, $method) use ($payment_types) {
                            $refundAmount = $payments->where('is_return', 1)->sum('amount');
                            $regularAmount = $payments->where('is_return', 0)->sum('amount');
                            $displayMethod = $payment_types[$method] ?? ucfirst($method);
                            $output = '';
                            if ($regularAmount > 0) {
                                $output .= $displayMethod . ' (৳' . number_format($regularAmount, 2) . ')';
                            }
                            if ($refundAmount > 0) {
                                $output .= ' <small>Refund: ' . $displayMethod . ' (৳' . number_format($refundAmount, 2) . ')</small>';
                            }
                            return $output;
                        })->implode(', ');
                    @endphp
                        <tr>
                            <td>
                                {{ $detail->invoice_no }}
                                @if ($detail->due_key == 'due')
                                    <!-- Due Bill Icon -->
                                    <span class="fa-print-icon" data-icon="&#xf06a;"></span>
                                @endif
                                @if ($detail->shipping_charges > 0)
                                    <!-- Shipping Bill Icon -->
                                    <span class="fa-print-icon" data-icon="&#xf48b;"></span>
                                @endif
                                @if ($detail->discount_amount > 0)
                                <!-- Special Discunt Icon -->
                                <span class="fa-print-icon" data-icon="&#xf02b;" title="Special Discunt"></span>
                                @endif
                                @if ($detail->line_discount_amount > 0)
                                    <!-- Campaign Discunt Icon -->
                                    <span class="fa-print-icon" data-icon="&#xf02b;" title="Campaign Discunt"></span>
                                @endif
                                @if ($detail->quantity_returned > 0)
                                    <!-- Quantity Returned Icon -->
                                    <span class="fa-print-icon" data-icon="&#xf0e2;" title="Quantity Returned"></span>
                                @endif
                                @if ($detail->customer_group_id)
                                    <!-- Group Customer Icon -->
                                    <span class="fa-print-icon" data-icon="&#xf0c0;" title="Group Customer"></span>
                                @endif
                            </td>                                                                                   
                            <td>{{ $detail->mobile }} <br> ({{ $detail->contact_id}})</td>
                            <td><span class="display_currency"
                                    data-currency_symbol="true">{{ number_format($detail->total_before_tax,2) }}</span></td>
                            <td>
                                {!! $paymentMethods !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <hr>
    <div class="row">
        <div class="col-md-6">
            <h4>Summary</h4>
            <p>Total bills: <strong>{{ $details['summary']['total_bills'] }}</strong></p>
            <p>Total shipping bills: <strong>{{ $details['summary']['total_shipping_bills'] }}</strong></p>
            <p>Total due bills: <strong>{{ $details['summary']['total_due_bills'] }}</strong></p>
            <p>Total Campaign Discount bills:<strong>{{ $details['summary']['bills_with_line_discount'] }}</strong></p>
            <p>Total Special Discount bills:<strong>{{ $details['summary']['bills_with_discount'] }}</strong></p>
        </div>
        <div class="col-md-6">
            <h4>Financial Summary</h4>
            <p>Total Campaign Discount: <strong><span class="display_currency" data-currency_symbol="true">{{ $details['summary']['total_line_discount_amount'] }}</span></strong></p>
            <p>Total Special Discount: <strong><span class="display_currency" data-currency_symbol="true">{{ $details['summary']['total_discount_amount'] }}</span></strong></p>
            <p>Total Shipping Charge: <strong><span class="display_currency" data-currency_symbol="true">{{ $details['summary']['total_shipping_charge'] }}</span></strong></p>
            <p>Grand Total: <strong><span class="display_currency" data-currency_symbol="true">{{ $total_amount }}</span></strong></p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <hr>
            <h3>@lang('lang_v1.product_sold_details_register') (@lang('lang_v1.by_category'))</h3>
            <table class="table table-condensed">
                <tr>
                    <th>#</th>
                    <th>Category</th>
                    <th>@lang('sale.qty')</th>
                    <th>@lang('sale.total_amount')</th>
                </tr>
                @php
                    $total_amount = 0;
                    $total_quantity = 0;
                @endphp
                @foreach ($details['product_details_by_category'] as $detail)
                    <tr>
                        <td>
                            {{ $loop->iteration }}.
                        </td>
                        <td>
                            {{ $detail->category_name }}
                        </td>
                        <td>
                            {{ number_format($detail->total_quantity) }}
                            @php
                                $total_quantity += $detail->total_quantity;
                            @endphp
                        </td>
                        <td>
                            <span class="display_currency" data-currency_symbol="true">
                                {{ number_format($detail->total_amount,2) }}
                            </span>
                            @php
                                $total_amount += $detail->total_amount;
                            @endphp
                        </td>
                    </tr>
                @endforeach


                @php
                    $total_amount +=
                        $details['transaction_details']->total_tax - $details['transaction_details']->total_discount;

                    $total_amount += $details['transaction_details']->total_shipping_charges;
                @endphp

                <!-- Final details -->
                <tr class="success">
                    <th>#</th>
                    <th></th>
                    <th>{{ $total_quantity }}</th>
                    <th>

                        @if ($details['transaction_details']->total_tax != 0)
                            @lang('sale.order_tax'): (+)
                            <span class="display_currency" data-currency_symbol="true">
                                {{ $details['transaction_details']->total_tax }}
                            </span>
                            <br />
                        @endif

                        @if ($details['transaction_details']->total_discount != 0)
                            @lang('sale.discount'): (-)
                            <span class="display_currency" data-currency_symbol="true">
                                {{ $details['transaction_details']->total_discount }}
                            </span>
                            <br />
                        @endif
                        @if ($details['transaction_details']->total_shipping_charges != 0)
                            @lang('lang_v1.total_shipping_charges'): (+)
                            <span class="display_currency" data-currency_symbol="true">
                                {{ $details['transaction_details']->total_shipping_charges }}
                            </span>
                            <br />
                        @endif

                        @lang('lang_v1.grand_total'):
                        <span class="display_currency" data-currency_symbol="true">
                            {{ number_format($total_amount,2) }}
                        </span>
                    </th>
                </tr>

            </table>
        </div>
    </div>

<script>
    function handlePrint() {
            window.print();
            window.onafterprint = function() {
                window.history.back();
            };
        }
</script>    
</body>

</html>
