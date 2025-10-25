<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>A4 Print</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"
        media="print">
    <style>
        @media print {

            /* Hide the button and other non-printable elements */
            .no-print {
                display: none;
            }

            body {
                font-family: Arial, sans-serif;
                width: 210mm;
                height: 297mm;
                padding: 0;
                box-sizing: border-box;
                margin: 0;
            }

            .header {
                text-align: center;
                /* Center the content */
                line-height: 0.5;
                /* Adjust line height for the header */
            }

            .header h1 {
                font-weight: normal;
                /* Remove bold from the title */
                font-size: 32px;
                /* Adjust font size for the title */
                line-height: 0.5;
                /* Adjust line height for title */
            }

            .header h1 b {
                font-weight: bold;
                /* Keep the user name bold */
            }

            .header h3 {
                font-weight: normal;
                /* Remove bold from the date range */
                font-size: 20px;
                /* Adjust font size for the date range */
                line-height: 0.5;
                /* Decrease line height for date range */
            }

            .header h3 b {
                font-weight: bold;
                /* Keep the time part bold */
            }

            .header h5 {
                font-weight: normal;
                line-height: 0.5;
                /* Adjust line height for business location */
            }

            .header h5 b {
                font-weight: bold;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
            }

            .table th,
            .table td {
                padding: 5px;
                text-align: left;
                font-size: 12px;
                border: 1px solid black;
            }

            .table-container {
                display: flex;
                justify-content: space-between;
            }

            .table-wrapper {
                width: 49%;
                /* Adjust width for both tables to fit side by side */
            }

            /* Custom Striped Rows */
            .table tr:nth-child(even) {
                background-color: #f2f2f2;
                /* Light grey background for even rows */
            }

            .table tr:nth-child(odd) {
                background-color: #fff;
                /* White background for odd rows */
            }

            /* Table Borders */
            .table th,
            .table td {
                border: 1px solid #ddd;
                /* Subtle border for all cells */
            }

            /* Optional: Styling for header */
            .table th {
                background-color: #2dce89;
                /* Darker background for headers */
                color: white;
                /* White text for contrast */
                font-weight: bold;
            }

            /* Adjust header and content fonts for better printing */
            h3 {
                font-size: 18px;
            }

            .content {
                font-size: 14px;
            }

            /* FontAwesome icons Unicode mapping for print */
            .fa-print-icon:before {
                content: attr(data-icon);
                font-family: 'Font Awesome 5 Free';
                font-weight: 700;
                /* Make sure it's bold enough */
                font-size: 10px;
                /* Adjust size if necessary */
                margin-left: 3px;
                /* Spacing for better alignment */
            }
        }
    </style>
</head>

<body onload="handlePrint()">
    <div class="header">
        <h1>@lang('cash_register.sales_register_report'): <b>{{ $register_details->user_name }}</b></h1>
        <h3>
            {!! preg_replace(
                '/(\d+)/',
                '<b>$1</b>',
                \Carbon::createFromFormat('Y-m-d H:i:s', $register_details->open_time)->format('jS M, Y h:i A'),
            ) !!}
            -
            {!! preg_replace(
                '/(\d+)/',
                '<b>$1</b>',
                \Carbon::createFromFormat('Y-m-d H:i:s', $close_time)->format('jS M, Y h:i A'),
            ) !!}
        </h3>
        <h5>@lang('business.business_location'): <b> {{ $register_details->location_name }}</b></h5>
    </div>
    <div class="table-container">
        <div class="table-wrapper">
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
                                data-currency_symbol="true">{{ number_format($register_details->total_cash, 2) }}</span>
                        </td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_cash_expense, 2) }}</span>
                        </td>
                    </tr>
                @endif

                @if ($register_details->total_cheque != 0 || $register_details->total_cheque_expense != 0)
                    <tr>
                        <td>@lang('cash_register.checque_payment'):</td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_cheque, 2) }}</span>
                        </td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_cheque_expense, 2) }}</span>
                        </td>
                    </tr>
                @endif

                @if ($register_details->total_card != 0 || $register_details->total_card_expense != 0)
                    <tr>
                        <td>@lang('cash_register.card_payment'):</td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_card, 2) }}</span>
                        </td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_card_expense, 2) }}</span>
                        </td>
                    </tr>
                @endif

                @if ($register_details->total_bank_transfer != 0 || $register_details->total_bank_transfer_expense != 0)
                    <tr>
                        <td>@lang('cash_register.bank_transfer'):</td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_bank_transfer, 2) }}</span>
                        </td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_bank_transfer_expense, 2) }}</span>
                        </td>
                    </tr>
                @endif

                @if ($register_details->total_advance != 0 || $register_details->total_advance_expense != 0)
                    <tr>
                        <td>@lang('lang_v1.advance_payment'):</td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_advance, 2) }}</span>
                        </td>
                        <td><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_advance_expense, 2) }}</span>
                        </td>
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
                                data-currency_symbol="true">{{ number_format($register_details->total_custom_pay_1, 2) }}</span>
                        </td>
                        <td>
                            <span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->total_custom_pay_1_expense, 2) }}</span>
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
        </div>
        <div class="table-wrapper">
            <table class="table table-condensed">
                <tr>
                    <td>
                        @lang('cash_register.total_sales'):
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ number_format($register_details->total_sale, 2) }}</span>
                    </td>
                </tr>
                @if ($register_details->total_refund != 0)
                    <tr class="danger">

                        <td>
                            @lang('cash_register.total_refund')
                        </td>
                        <td>

                            <b><span class="display_currency"
                                    data-currency_symbol="true">{{ number_format($register_details->total_refund, 2) }}</span></b><br>

                            <small>
                                @if ($register_details->total_cash_refund != 0)
                                    Cash: <span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($register_details->total_cash_refund, 2) }}</span><br>
                                @endif
                                @if ($register_details->total_cheque_refund != 0)
                                    Cheque: <span class="display_currency"
                                        data-currency_symbol="true">{{ $register_details->total_cheque_refund }}</span><br>
                                @endif
                                @if ($register_details->total_card_refund != 0)
                                    Card: <span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($register_details->total_card_refund, 2) }}</span><br>
                                @endif
                                @if ($register_details->total_bank_transfer_refund != 0)
                                    Bank Transfer: <span class="display_currency"
                                        data-currency_symbol="true">{{ $register_details->total_bank_transfer_refund }}</span><br>
                                @endif
                                @if (array_key_exists('custom_pay_1', $payment_types) && $register_details->total_custom_pay_1_refund != 0)
                                    {{ $payment_types['custom_pay_1'] }}: <span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($register_details->total_custom_pay_1_refund, 2) }}</span>
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
                    <td>
                        @lang('lang_v1.credit_sales'):
                    </td>
                    <td>
                        <b><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($details['transaction_details']->total_sales - $register_details->total_sale, 2) }}</span></b>
                    </td>
                </tr>
                <tr class="success">
                    <td>
                        @lang('cash_register.net_sales'):
                    </td>
                    <td>
                        <b><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($details['transaction_details']->total_sales, 2) }}</span></b>
                    </td>
                </tr>
                <tr class="success">
                    <td>
                        @lang('lang_v1.total_payment')
                    </td>
                    <td>
                        <b><span class="display_currency"
                                data-currency_symbol="true">{{ number_format($register_details->cash_in_hand + $register_details->total_cash - $register_details->total_cash_refund, 2) }}</span></b>
                    </td>
                </tr>
                @if ($register_details->total_expense != 0)
                    <tr class="danger">
                        <td>
                            @lang('report.total_expense'):
                        </td>
                        <td>
                            <b><span class="display_currency"
                                    data-currency_symbol="true">{{ number_format($register_details->total_expense, 2) }}</span></b>
                        </td>
                    </tr>
                @endif
                <tr class="info">
                    <td>Total Campaign Discount:</td>
                    <td>{{ number_format($details['summary']['total_line_discount_amount'], 2) }}</td>
                </tr>
                <tr class="info">
                    <td>Total Special Discount:</td>
                    <td>{{ number_format($details['summary']['total_discount_amount'], 2) }}</td>
                </tr>
                <tr class="info">
                    <td>Total Shipping Charge:</td>
                    <td>{{ number_format($details['summary']['total_shipping_charge'], 2) }}</td>
                </tr>
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
        $return_total = 0;
        $return_count = 0;
        foreach ($sell_return as $value) {
            if ($value->payment_status == 'paid') {
                $return_total += $value->final_total;
            }
            $return_count += 1;
        }
    @endphp

    @if (isset($common_settings['enable_details_by_bill']))
        <div class="row">
            <div class="col-md-12">
                <h3>Details by bill</h3>
                <!-- Sell Details Table -->
                <table class="table table-condensed">
                    <thead>
                        <tr>
                            <th>Invoice No.</th>
                            <th>Customer</th>
                            <th>Total Amount</th>
                            <th>@lang('lang_v1.payment_method')</th>
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
                                        <span class="fa-print-icon" data-icon="&#xf02b;"
                                            title="Special Discunt"></span>
                                    @endif
                                    @if ($detail->line_discount_amount > 0)
                                        <!-- Campaign Discunt Icon -->
                                        <span style="color: #2dce89" class="fa-print-icon" data-icon="&#xf02b;"
                                            title="Campaign Discunt"></span>
                                    @endif
                                    @if ($detail->quantity_returned > 0)
                                        <!-- Quantity Returned Icon -->
                                        <span style="color: #1572E8" class="fa-print-icon" data-icon="&#xf0e2;"
                                            title="Quantity Returned"></span>
                                    @endif
                                    @if ($detail->customer_group_id)
                                        <!-- Group Customer Icon -->
                                        <span class="fa-print-icon" data-icon="&#xf0c0;"
                                            title="Group Customer"></span>
                                    @endif
                                </td>
                                <td>{{ $detail->mobile }} ({{ $detail->contact_id }}) {{ $detail->name }}</td>
                                <td><span class="display_currency"
                                        data-currency_symbol="true">{{ number_format($detail->total_before_tax, 2) }}</span>
                                </td>
                                <td>
                                    {!! $paymentMethods !!}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
    @if ($return_count > 0)
        <div class="row">
            <div class="col-md-12">
                <h3>Bill Return </h3>
                <!-- Sell Details Table -->
                <table class="table table-condensed table-sm table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Invoice No.</th>
                            <th>Parent Sale</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sell_return as $detail)
                            <tr>
                                <td>
                                    {{ $loop->iteration }}.
                                </td>
                                <td>
                                    {{ $detail->invoice_no }}
                                </td>
                                <td><button type="button" class="btn btn-link btn-modal"
                                        data-container=".view_modal"
                                        data-href="{{ action([\App\Http\Controllers\SellController::class, 'show'], [$detail->parent_sale_id]) }}">{{ $detail->parent_sale }}
                                    </button></td>
                                <td>{{ Carbon::parse($detail->transaction_date)->format('d-m-Y h:i A') }}</td>
                                <td><span class="display_currency"
                                        data-currency_symbol="true">{{ $detail->final_total }}</span></td>
                                <td>
                                    {{ $detail->payment_status }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <br>
                Return Total: {{ $return_total }}TK
            </div>
        </div>
    @endif
    <div class="table-container">
        @if (isset($common_settings['enable_summary']))
            <div style="width: 30%">
                <div>
                    <!-- Top Block: Summary -->
                    <div style="width: 100%; margin-bottom: 20px;">
                        <h4 style="margin-bottom: 10px;">Summary</h4>
                        <p style="font-size: 14px; margin: 4px 0;">Total bills:
                            <strong>{{ $details['summary']['total_bills'] }}</strong>
                        </p>
                        <p style="font-size: 14px; margin: 4px 0;">Total shipping bills:
                            <strong>{{ $details['summary']['total_shipping_bills'] }}</strong>
                        </p>
                        <p style="font-size: 14px; margin: 4px 0;">Total due bills:
                            <strong>{{ $details['summary']['total_due_bills'] }}</strong>
                        </p>
                        <p style="font-size: 14px; margin: 4px 0;">Total Campaign Discount bills:
                            <strong>{{ $details['summary']['bills_with_line_discount'] }}</strong>
                        </p>
                        <p style="font-size: 14px; margin: 4px 0;">Total Special Discount bills:
                            <strong>{{ $details['summary']['bills_with_discount'] }}</strong>
                        </p>
                    </div>

                </div>
            </div>
        @endif
        @if (isset($common_settings['enable_details_by_category']))
            <div style="width: 68%">
                <h3>@lang('lang_v1.product_sold_details_register') (@lang('lang_v1.by_category'))</h3>
                <table class="table table-condensed">
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>@lang('sale.qty')</th>
                        <th>@lang('sale.line_discount')</th>
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
                                {{ @format_quantity($detail->total_quantity) }}
                                @php
                                    $total_quantity += $detail->total_quantity;
                                @endphp
                            </td>
                            <td>
                                <span class="display_currency" data-currency_symbol="true">
                                    {{ number_format($detail->total_line_discount) }}
                                </span>
                            </td>
                            <td>
                                <span class="display_currency" data-currency_symbol="true">
                                    {{ number_format($detail->total_amount, 2) }}
                                </span>
                                @php
                                    $total_amount += $detail->total_amount;
                                @endphp
                            </td>
                        </tr>
                    @endforeach


                    @php
                        $total_amount +=
                            $details['transaction_details']->total_tax -
                            $details['transaction_details']->total_discount;

                        $total_amount += $details['transaction_details']->total_shipping_charges;
                    @endphp

                    <!-- Final details -->
                    <tr class="success">
                        <th>#</th>
                        <th></th>
                        <th>{{ $total_quantity }}</th>
                        <th>{{ number_format($details['summary']['total_line_discount_amount'], 2) }}</th>
                        <th>

                            @if ($details['transaction_details']->total_tax != 0)
                                @lang('sale.order_tax'): (+)
                                <span class="display_currency" data-currency_symbol="true">
                                    {{ number_format($details['transaction_details']->total_tax, 2) }}
                                </span>
                                <br />
                            @endif

                            @if ($details['transaction_details']->total_discount != 0)
                                @lang('sale.discount'): (-)
                                <span class="display_currency" data-currency_symbol="true">
                                    {{ number_format($details['transaction_details']->total_discount, 2) }}
                                </span>
                                <br />
                            @endif
                            @if ($details['transaction_details']->total_shipping_charges != 0)
                                @lang('lang_v1.total_shipping_charges'): (+)
                                <span class="display_currency" data-currency_symbol="true">
                                    {{ number_format($details['transaction_details']->total_shipping_charges, 2) }}
                                </span>
                                <br />
                            @endif

                            @lang('lang_v1.grand_total'):
                            <span class="display_currency" data-currency_symbol="true">
                                {{ $total_amount }}
                            </span>
                        </th>
                    </tr>
                </table>
            </div>
        @endif
    </div>
    @if (isset($common_settings['enable_details_by_customer']))
        <div class="row">
            <div class="col-md-12">
                <h3>@lang('lang_v1.product_sold_details_register') (By Customer)</h3>
                <table class="table table-condensed">
                    <tr>
                        <th>#</th>
                        <th>Customer name</th>
                        <th>@lang('sale.qty')</th>
                        <th>@lang('sale.total_amount')</th>
                    </tr>
                    @php
                        $total_amount = 0;
                        $total_quantity = 0;
                    @endphp
                    @foreach ($details['product_details_by_customer'] as $detail)
                        <tr>
                            <td>
                                {{ $loop->iteration }}.
                            </td>
                            <td>
                                {{ $detail->contact_name }}
                            </td>
                            <td>
                                {{ @format_quantity($detail->total_quantity) }}
                                @php
                                    $total_quantity += $detail->total_quantity;
                                @endphp
                            </td>
                            <td>
                                <span class="display_currency" data-currency_symbol="true">
                                    {{ number_format($detail->total_amount, 2) }}
                                </span>
                                @php
                                    $total_amount += $detail->total_amount;
                                @endphp
                            </td>
                        </tr>
                    @endforeach


                    @php
                        $total_amount +=
                            $details['transaction_details']->total_tax -
                            $details['transaction_details']->total_discount;

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
                                {{ $total_amount }}
                            </span>
                        </th>
                    </tr>

                </table>
            </div>
        </div>
    @endif

    @if (isset($common_settings['enable_details_by_customer_group']))
        <div class="row">
            <div class="col-md-12">
                <h3>@lang('lang_v1.product_sold_details_register') (by Customer Group)</h3>
                <table class="table table-condensed">
                    <tr>
                        <th>#</th>
                        <th>Group Name</th>
                        <th>Discount Policy</th>
                        <th>SPG Name</th>
                        <th>@lang('sale.qty')</th>
                        <th>@lang('sale.total_amount')</th>
                    </tr>
                    @php
                        $total_amount = 0;
                        $total_quantity = 0;
                    @endphp
                    @foreach ($details['product_details_by_customer_group'] as $detail)
                        <tr>
                            <td>{{ $loop->iteration }}.</td>
                            <td>{{ $detail->group_name ?? 'All Others' }}</td>
                            <td><span>{{ abs($detail->amount) }}%</span></td>
                            <td>{{ $detail->spg_name }}</td>
                            <td>
                                {{ @format_quantity($detail->total_quantity) }}
                                @php
                                    $total_quantity += $detail->total_quantity;
                                @endphp
                            </td>
                            <td>
                                <span class="display_currency"
                                    data-currency_symbol="true">{{ number_format($detail->total_amount, 2) }}</span>
                                @php
                                    $total_amount += $detail->total_amount;
                                @endphp
                            </td>
                        </tr>
                    @endforeach

                    @php
                        $total_amount +=
                            $details['transaction_details']->total_tax -
                            $details['transaction_details']->total_discount;

                        $total_amount += $details['transaction_details']->total_shipping_charges;
                    @endphp

                    <!-- Final details -->
                    <tr class="success">
                        <th>#</th>
                        <th colspan="3"></th>
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
                                {{ $total_amount }}
                            </span>
                        </th>
                    </tr>

                </table>
            </div>
        </div>
    @endif

    @if (isset($common_settings['enable_details_of_products_sold']))
        <div class="row">
            <div class="col-md-12">
                <h3>@lang('lang_v1.product_sold_details_register')</h3>
                <table class="table table-condensed">
                    <tr>
                        <th>#</th>
                        <th>@lang('product.sku')</th>
                        <th>@lang('sale.product')</th>
                        <th>@lang('sale.qty')</th>
                        <th>@lang('sale.total_amount')</th>
                    </tr>
                    @php
                        $total_amount = 0;
                        $total_quantity = 0;
                    @endphp
                    @foreach ($details['product_details'] as $detail)
                        <tr>
                            <td>
                                {{ $loop->iteration }}.
                            </td>
                            <td>
                                {{ $detail->sku }}
                            </td>
                            <td>
                                {{ $detail->product_name }}
                                @if ($detail->type == 'variable')
                                    {{ $detail->product_variation_name }} - {{ $detail->variation_name }}
                                @endif
                            </td>
                            <td>
                                {{ @format_quantity($detail->total_quantity) }}
                                @php
                                    $total_quantity += $detail->total_quantity;
                                @endphp
                            </td>
                            <td>
                                <span class="display_currency" data-currency_symbol="true">
                                    {{ number_format($detail->total_amount, 2) }}
                                </span>
                                @php
                                    $total_amount += $detail->total_amount;
                                @endphp
                            </td>
                        </tr>
                    @endforeach


                    @php
                        $total_amount +=
                            $details['transaction_details']->total_tax -
                            $details['transaction_details']->total_discount;

                        $total_amount += $details['transaction_details']->total_shipping_charges;
                    @endphp

                    <!-- Final details -->
                    <tr class="success">
                        <th>#</th>
                        <th></th>
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
                                {{ $total_amount }}
                            </span>
                        </th>
                    </tr>

                </table>
            </div>
        </div>
    @endif

    @if (isset($common_settings['enable_details_by_brand']))
        <div class="row">
            <div class="col-md-12">
                <h3>@lang('lang_v1.product_sold_details_register') (@lang('lang_v1.by_brand'))</h3>
                <table class="table table-condensed">
                    <tr>
                        <th>#</th>
                        <th>@lang('brand.brands')</th>
                        <th>@lang('sale.qty')</th>
                        <th>@lang('sale.total_amount')</th>
                    </tr>
                    @php
                        $total_amount = 0;
                        $total_quantity = 0;
                    @endphp
                    @foreach ($details['product_details_by_brand'] as $detail)
                        <tr>
                            <td>
                                {{ $loop->iteration }}.
                            </td>
                            <td>
                                {{ $detail->brand_name }}
                            </td>
                            <td>
                                {{ @format_quantity($detail->total_quantity) }}
                                @php
                                    $total_quantity += $detail->total_quantity;
                                @endphp
                            </td>
                            <td>
                                <span class="display_currency" data-currency_symbol="true">
                                    {{ number_format($detail->total_amount, 2) }}
                                </span>
                                @php
                                    $total_amount += $detail->total_amount;
                                @endphp
                            </td>
                        </tr>
                    @endforeach


                    @php
                        $total_amount +=
                            $details['transaction_details']->total_tax -
                            $details['transaction_details']->total_discount;

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
                                {{ $total_amount }}
                            </span>
                        </th>
                    </tr>

                </table>
            </div>
        </div>
    @endif

    @if (isset($common_settings['enable_service_details']))
        @if ($details['types_of_service_details'])
            <div class="row">
                <div class="col-md-12">
                    <h3>@lang('lang_v1.types_of_service_details')</h3>
                    <table class="table">
                        <tr>
                            <th>#</th>
                            <th>@lang('lang_v1.types_of_service')</th>
                            <th>@lang('sale.total_amount')</th>
                        </tr>
                        @php
                            $total_sales = 0;
                        @endphp
                        @foreach ($details['types_of_service_details'] as $detail)
                            <tr>
                                <td>
                                    {{ $loop->iteration }}
                                </td>
                                <td>
                                    {{ $detail->types_of_service_name ?? '--' }}
                                </td>
                                <td>
                                    <span class="display_currency" data-currency_symbol="true">
                                        {{ $detail->total_sales }}
                                    </span>
                                    @php
                                        $total_sales += $detail->total_sales;
                                    @endphp
                                </td>
                            </tr>
                            @php
                                $total_sales += $detail->total_sales;
                            @endphp
                        @endforeach
                        <!-- Final details -->
                        <tr class="success">
                            <th>#</th>
                            <th></th>
                            <th>
                                @lang('lang_v1.grand_total'):
                                <span class="display_currency" data-currency_symbol="true">
                                    {{ $total_amount }}
                                </span>
                            </th>
                        </tr>

                    </table>
                </div>
            </div>
        @endif
    @endif
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