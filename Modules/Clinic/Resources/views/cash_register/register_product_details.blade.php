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
    $credit_sale_payment_count = 0;
    foreach ($details['credit_sell_payment_details'] as $detail) {
        $credit_sale_payment_count += 1;
    }
@endphp


<div class="row">
    <div class="col-md-12">
        <h3>Details by Services</h3>
        <div style="overflow-x: auto; width: 100%;">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Bills</th>
                        <th>Total Amount</th>
                        <th>Campaign Discount</th>
                        <th>Special Discount</th>
                        <th>Total Payable</th>
                        <th>Total Paid</th>
                        <th>Cash</th>
                        <th>bkash</th>
                        <th>Card</th>
                        <th>Due Amount</th>
                    </tr>
                </thead>
                @php
                    $total_bill = 0;
                    $total_cash_payment = 0;
                    $total_card_payment = 0;
                    $total_custom_pay_1 = 0;
                    $total_amount_payment = 0;
                    $total_due_amount = 0;
                    $total_total_discount = 0;
                    $total_total_line_discount = 0;
                    $total_payable = 0;
                    $total_amount = 0;
                @endphp
                <tbody>
                    @foreach ($details['sell_details_by_sub_type'] as $subType)
                        @php
                            $total_bill += $subType->bill_count;
                            $total_amount += $subType->total_amount + $subType->total_line_discount;
                            $total_total_line_discount += $subType->total_line_discount;
                            $total_total_discount += $subType->total_discount;
                            $total_payable += $subType->total_payable;
                            $total_cash_payment += $subType->cash_payment;
                            $total_card_payment += $subType->card_payment;
                            $total_custom_pay_1 += $subType->custom_pay_1;
                            $total_amount_payment += $subType->total_paid;
                            $total_due_amount += $subType->due_amount;

                        @endphp
                        <tr>
                            <td>{{ $subType->sub_type ?? 'N/A' }}</td>
                            <td>{{ $subType->bill_count }}</td>
                            <td>৳{{ number_format($subType->total_amount + $subType->total_line_discount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_line_discount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_discount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_payable, 2) }}</td>
                            <td>৳{{ number_format($subType->total_paid, 2) }}</td>
                            <td>৳{{ number_format($subType->cash_payment, 2) }}</td>
                            <td>৳{{ number_format($subType->custom_pay_1, 2) }}</td>
                            <td>৳{{ number_format($subType->card_payment, 2) }}</td>
                            <td>৳{{ number_format($subType->due_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th style="text-align: center;">Total:</th>
                        <th>{{ $total_bill }}</th>
                        <th>৳{{ number_format($total_amount, 2) }}</th>
                        <th>৳{{ number_format($total_total_line_discount, 2) }}</th>
                        <th>৳{{ number_format($total_total_discount, 2) }}</th>
                        <th>৳{{ number_format($total_payable, 2) }}</th>
                        <th>৳{{ number_format($total_amount_payment, 2) }}</th>
                        <th>৳{{ number_format($total_cash_payment, 2) }}</th>
                        <th>৳{{ number_format($total_custom_pay_1, 2) }}</th>
                        <th>৳{{ number_format($total_card_payment, 2) }}</th>
                        <th>৳{{ number_format($total_due_amount, 2) }}</th>
                    </tr>
                </tfoot>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h3>Details by Services Refund</h3>
        <div style="overflow-x: auto; width: 100%;">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Bills</th>
                        <th>Total Amount</th>
                        <th>Campaign Discount</th>
                        <th>Special Discount</th>
                        <th>Total Payable</th>
                        <th>Total Paid</th>
                        <th>Cash</th>
                        <th>bkash</th>
                        <th>Card</th>
                        <th>Due Amount</th>
                    </tr>
                </thead>
                @php
                    $total_bill = 0;
                    $total_cash_payment = 0;
                    $total_card_payment = 0;
                    $total_custom_pay_1 = 0;
                    $total_amount_payment = 0;
                    $total_due_amount = 0;
                    $total_total_discount = 0;
                    $total_total_line_discount = 0;
                    $total_payable = 0;
                    $total_amount = 0;
                @endphp
                <tbody>
                    @foreach ($details['sell_return_details_by_sub_type'] as $subType)
                        @php
                            $total_bill += $subType->bill_count;
                            $total_amount += $subType->total_amount;
                            $total_total_line_discount += $subType->total_line_discount;
                            $total_total_discount += $subType->total_discount;
                            $total_payable += $subType->total_payable;
                            $total_cash_payment += $subType->cash_payment;
                            $total_card_payment += $subType->card_payment;
                            $total_custom_pay_1 += $subType->custom_pay_1;
                            $total_amount_payment += $subType->total_paid;
                            $total_due_amount += $subType->due_amount;

                        @endphp
                        <tr>
                            <td>{{ $subType->sub_type ?? 'N/A' }}</td>
                            <td>{{ $subType->bill_count }}</td>
                            <td>৳{{ number_format($subType->total_amount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_line_discount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_discount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_payable, 2) }}</td>
                            <td>৳{{ number_format($subType->total_paid, 2) }}</td>
                            <td>৳{{ number_format($subType->cash_payment, 2) }}</td>
                            <td>৳{{ number_format($subType->custom_pay_1, 2) }}</td>
                            <td>৳{{ number_format($subType->card_payment, 2) }}</td>
                            <td>৳{{ number_format($subType->due_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th style="text-align: center;">Total:</th>
                        <th>{{ $total_bill }}</th>
                        <th>৳{{ number_format($total_amount, 2) }}</th>
                        <th>৳{{ number_format($total_total_line_discount, 2) }}</th>
                        <th>৳{{ number_format($total_total_discount, 2) }}</th>
                        <th>৳{{ number_format($total_payable, 2) }}</th>
                        <th>৳{{ number_format($total_amount_payment, 2) }}</th>
                        <th>৳{{ number_format($total_cash_payment, 2) }}</th>
                        <th>৳{{ number_format($total_custom_pay_1, 2) }}</th>
                        <th>৳{{ number_format($total_card_payment, 2) }}</th>
                        <th>৳{{ number_format($total_due_amount, 2) }}</th>
                    </tr>
                </tfoot>
                </tfoot>
            </table>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <h3>Details by Services with Refund</h3>
        <div style="overflow-x: auto; width: 100%;">
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th>Service</th>
                        <th>Bills</th>
                        <th>Total Amount</th>
                        <th>Campaign Discount</th>
                        <th>Special Discount</th>
                        <th>Return (Cash)</th>
                        <th>Total Payable</th>
                        <th>Total Paid</th>
                        <th>Cash</th>
                        <th>bkash</th>
                        <th>Card</th>
                        <th>Due Amount</th>
                    </tr>
                </thead>
                @php
                    $total_bill = 0;
                    $total_cash_payment = 0;
                    $total_card_payment = 0;
                    $total_custom_pay_1 = 0;
                    $total_paid = 0;
                    $total_due_amount = 0;
                    $total_total_discount = 0;
                    $total_total_line_discount = 0;
                    $total_payable = 0;
                    $total_amount = 0;
                    $total_return_amount = 0;
                @endphp
                <tbody>
                    @foreach ($details['sell_and_return_details_by_sub_type'] as $subType)
                        @php
                            $total_bill += $subType->bill_count;
                            $total_amount += $subType->total_amount + $subType->total_line_discount;
                            $total_total_line_discount += $subType->total_line_discount;
                            $total_total_discount += $subType->total_discount;
                            $total_return_amount += $subType->return_amount;
                            $total_payable += $subType->total_payable - $subType->return_amount;
                            $total_cash_payment += $subType->cash_payment - $subType->return_cash_amount;
                            $total_card_payment += $subType->card_payment - $subType->return_card_amount;
                            $total_custom_pay_1 += $subType->custom_pay_1 - $subType->return_custom_pay_1_amount;
                            $total_paid += $subType->total_paid - $subType->return_amount * 2;
                            $total_due_amount += $subType->due_amount;
                        @endphp
                        <tr>
                            <td>{{ $subType->sub_type ?? 'N/A' }}</td>
                            <td>{{ $subType->bill_count }}</td>
                            <td>৳{{ number_format($subType->total_amount + $subType->total_line_discount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_line_discount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_discount, 2) }}</td>
                            <td>৳{{ number_format($subType->return_amount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_payable - $subType->return_amount, 2) }}</td>
                            <td>৳{{ number_format($subType->total_paid - $subType->return_amount * 2, 2) }}</td>
                            <td>৳{{ number_format($subType->cash_payment - $subType->return_cash_amount, 2) }}</td>
                            <td>৳{{ number_format($subType->custom_pay_1 - $subType->return_custom_pay_1_amount, 2) }}
                            </td>
                            <td>৳{{ number_format($subType->card_payment - $subType->return_card_amount, 2) }}</td>
                            <td>৳{{ number_format($subType->due_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th style="text-align: center;">Total:</th>
                        <th>{{ $total_bill }}</th>
                        <th>৳{{ number_format($total_amount, 2) }}</th>
                        <th>৳{{ number_format($total_total_line_discount, 2) }}</th>
                        <th>৳{{ number_format($total_total_discount, 2) }}</th>
                        <th>৳{{ number_format($total_return_amount, 2) }}</th>
                        <th>৳{{ number_format($total_payable, 2) }}</th>
                        <th>৳{{ number_format($total_paid, 2) }}</th>
                        <th>৳{{ number_format($total_cash_payment, 2) }}</th>
                        <th>৳{{ number_format($total_custom_pay_1, 2) }}</th>
                        <th>৳{{ number_format($total_card_payment, 2) }}</th>
                        <th>৳{{ number_format($total_due_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>


<div class="row">
    <div class="col-md-12">
        <hr>
        <h3>Details by bill</h3>
        <!-- Sell Details Table -->
        <table class="table table-condensed table-sm table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice No.</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>@lang('lang_v1.payment_method')</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($details['sell_details'] as $detail)
                    @php
                        $paymentMethods = $detail->payments
                            ->groupBy('method')
                            ->map(function ($payments, $method) use ($payment_types) {
                                $refundAmount = $payments->where('is_return', 1)->sum('amount');
                                $regularAmount = $payments->where('is_return', 0)->sum('amount');
                                $displayMethod = $payment_types[$method] ?? ucfirst($method);
                                $output = '';
                                if ($regularAmount > 0) {
                                    $output .= $displayMethod . ' (৳' . number_format($regularAmount, 2) . ')';
                                }
                                if ($refundAmount > 0) {
                                    $output .=
                                        ' <small>Change Return: ' .
                                        $displayMethod .
                                        ' (৳' .
                                        number_format($refundAmount, 2) .
                                        ')</small>';
                                }
                                return $output;
                            })
                            ->implode(', ');
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}.</td>
                        <td>
                            <button type="button" class="btn btn-link btn-modal" data-container=".view_modal"
                                data-href="{{ action([\App\Http\Controllers\SellController::class, 'show'], [$detail->id]) }}">{{ $detail->invoice_no }}</button>
                        </td>
                        <td>{{ $detail->mobile }} ({{ $detail->contact_id }}) {{ $detail->name }}</td>
                        <td>{{ Carbon::parse($detail->created_at)->format('d-m-Y h:i A') }}</td>
                        <td>
                            <span class="display_currency"
                                data-currency_symbol="true">{{ $detail->total_before_tax }}</span>
                            <br> {{ $detail->total_discount }}
                            <br> {{ $detail->total_line_discount }}
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

@if ($return_count > 0)

    <hr>
    <div class="row">
        <div class="col-md-12">
            <h3>Bill Refund </h3>
            <!-- Sell Details Table -->
            <table class="table table-condensed table-sm table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Invoice No.</th>
                        <th>Parent Sale</th>
                        <th>Service Name</th>
                        <th>Selections</th>
                        <th>Total Amount</th>
                        <th>Pay Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sell_return as $detail)
                        <tr>
                            <td>
                                {{ $loop->iteration }}.
                            </td>
                            <td>
                                {{ Carbon::parse($detail->transaction_date)->format('d-m-Y') }} <br>
                                {{ Carbon::parse($detail->transaction_date)->format('h:i A') }}
                            </td>
                            <td>
                                <span class="btn-link cursor-pointer btn-modal" data-container=".view_modal"
                                    data-href="{{ action('Modules\Clinic\Http\Controllers\BillReturnController@show', [$detail->parent_sale_id]) }}">
                                    {{ $detail->invoice_no }}
                                </span>
                            </td>
                            <td>
                                <span class="btn-link cursor-pointer btn-modal" data-container=".view_modal"
                                    data-href="{{ action([\App\Http\Controllers\SellController::class, 'show'], [$detail->parent_sale_id]) }}">{{ $detail->parent_sale }}
                                </span> <br>
                                {{ Carbon::parse($detail->parent_transaction_date)->format('d-m-Y') }} <br>
                                {{ Carbon::parse($detail->parent_transaction_date)->format('h:i A') }}
                            </td>
                            <td>{{ $detail->product_names }}</td>
                            <td>{{ $detail->modifier_names ?? 'N/A' }}</td>
                            <td>
                                <span class="display_currency"
                                    data-currency_symbol="true">{{ $detail->final_total }}</span>
                            </td>
                            <td class="text-center">
                                @if ($detail->payment_status == 'due')
                                    <span class="label bg-yellow">{{ $detail->payment_status }}</span>
                                @elseif ($detail->payment_status == 'paid')
                                    <span class="label bg-light-green">{{ $detail->payment_status }}</span>
                                @elseif ($detail->payment_status == 'partial')
                                    <span class="label bg-aqua">{{ $detail->payment_status }}</span>
                                @else
                                    {{ $detail->payment_status }}
                                @endif <br>
                                @if ($detail->payment_status != 'due')
                                    @php
                                        $displayMethod = $payment_types[$detail->method] ?? ucfirst($detail->method);
                                    @endphp
                                    {{ $displayMethod }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-center">Return Total:</th>
                        <th>
                            <span class="display_currency"
                                data-currency_symbol="true">{{ number_format($return_total, 2) }}</span>
                        </th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

@endif
<hr>
<div class="row">
    <div class="col-md-6">
        <h4>Summary</h4>
        <p>Total bills: <strong>{{ $details['summary']['total_bills'] }}</strong></p>
        <p>Total shipping bills: <strong>{{ $details['summary']['total_shipping_bills'] }}</strong></p>
        <p>Total due bills: <strong>{{ $details['summary']['total_due_bills'] }}</strong></p>
        <p>Total Campaign Discount bills: <strong>{{ $details['summary']['bills_with_line_discount'] }}</strong></p>
        <p>Total Special Discount bills: <strong>{{ $details['summary']['bills_with_discount'] }}</strong></p>
    </div>
    <div class="col-md-6">
        <h4>Financial Summary</h4>
        <p>Total Campaign Discount: <strong><span class="display_currency"
                    data-currency_symbol="true">{{ $details['summary']['total_line_discount_amount'] }}</span></strong>
        </p>
        <p>Total Special Discount: <strong><span class="display_currency"
                    data-currency_symbol="true">{{ $details['summary']['total_discount_amount'] }}</span></strong></p>
        <p>Total Shipping Charge: <strong><span class="display_currency"
                    data-currency_symbol="true">{{ $details['summary']['total_shipping_charge'] }}</span></strong></p>
        <p>Grand Total: <strong><span class="display_currency"
                    data-currency_symbol="true">{{ $total_amount }}</span></strong></p>
    </div>
</div>


@if ($credit_sale_payment_count > 0)

    <div class="row">
        <div class="col-md-12">
            <hr>
            <h3>Due Bill Collection</h3>
            <!-- Sell Details Table -->
            <table class="table table-condensed table-sm table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice No.</th>
                        <th>Customer</th>
                        <th>Bill Date</th>
                        <th>Payment Date</th>
                        <th>Total Amount</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($details['credit_sell_payment_details'] as $detail)
                        @php
                            $paymentMethods = $detail->payments
                                ->groupBy('method')
                                ->map(function ($payments, $method) use ($payment_types) {
                                    $changeReturn = $payments->where('is_return', 1)->sum('amount');
                                    $regularAmount = $payments->where('is_return', 0)->sum('amount');
                                    $displayMethod = $payment_types[$method] ?? ucfirst($method);
                                    $output = '';
                                    if ($regularAmount > 0) {
                                        $output .= $displayMethod . ' (৳' . number_format($regularAmount, 2) . ')';
                                    }
                                    if ($changeReturn > 0) {
                                        $output .=
                                            ' <small>Change Return: ' .
                                            $displayMethod .
                                            ' (৳' .
                                            number_format($changeReturn, 2) .
                                            ')</small>';
                                    }
                                    return $output;
                                })
                                ->implode(', ');
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}.</td>
                            <td>
                                <button type="button" class="btn btn-link btn-modal" data-container=".view_modal"
                                    data-href="{{ action([\App\Http\Controllers\SellController::class, 'show'], [$detail->id]) }}">{{ $detail->invoice_no }}</button>
                            </td>
                            <td>{{ $detail->mobile }} ({{ $detail->contact_id }}) {{ $detail->name }}</td>
                            <td>{{ Carbon::parse($detail->created_at)->format('d-m-Y h:i A') }}</td>
                            <td>{{ Carbon::parse($detail->payment_date)->format('d-m-Y h:i A') }}</td>
                            <td>
                                <span class="display_currency"
                                    data-currency_symbol="true">{{ $detail->total_before_tax }}</span>
                            </td>
                            <td>
                                @if ($detail->payment_status == 'due')
                                    <span class="label bg-yellow">{{ $detail->payment_status }}</span>
                                @elseif ($detail->payment_status == 'paid')
                                    <span class="label bg-light-green">{{ $detail->payment_status }}</span>
                                @elseif ($detail->payment_status == 'partial')
                                    <span class="label bg-aqua">{{ $detail->payment_status }}</span>
                                @else
                                    {{ $detail->payment_status }}
                                @endif
                                {!! $paymentMethods !!}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

@endif

<div class="row">
    <div class="col-md-12">
        <hr>
        <h3>Details by Consultation bill</h3>
        <!-- Sell Details Table -->
        <table class="table table-condensed table-sm table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Invoice No.</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                    <th>@lang('lang_v1.payment_method')</th>
                </tr>
            </thead>
            @php
                $grandTotalsByMethod = [];
            @endphp

            <tbody>
                @foreach ($details['consultation_sell_details'] as $detail)
                    @php
                        $paymentMethods = $detail->payments
                            ->groupBy('method')
                            ->map(function ($payments, $method) use ($payment_types) {
                                $refundAmount = $payments->where('is_return', 1)->sum('amount');
                                $regularAmount = $payments->where('is_return', 0)->sum('amount');
                                $displayMethod = $payment_types[$method] ?? ucfirst($method);
                                $output = '';
                                if ($regularAmount > 0) {
                                    $output .= $displayMethod . ' (৳' . number_format($regularAmount, 2) . ')';
                                }
                                if ($refundAmount > 0) {
                                    $output .=
                                        ' <small>Change Return: ' .
                                        $displayMethod .
                                        ' (৳' .
                                        number_format($refundAmount, 2) .
                                        ')</small>';
                                }
                                return $output;
                            })
                            ->implode(', ');
                        foreach ($detail->payments->groupBy('method') as $method => $payments) {
                            $refundAmount = $payments->where('is_return', 1)->sum('amount');
                            $regularAmount = $payments->where('is_return', 0)->sum('amount');
                            $netAmount = $regularAmount - $refundAmount;
                            if (!isset($grandTotalsByMethod[$method])) {
                                $grandTotalsByMethod[$method] = 0;
                            }
                            $grandTotalsByMethod[$method] += $netAmount;
                        }

                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}.</td>
                        <td>
                            <button type="button" class="btn btn-link btn-modal" data-container=".view_modal"
                                data-href="{{ action([\App\Http\Controllers\SellController::class, 'show'], [$detail->id]) }}">{{ $detail->invoice_no }}</button>
                        </td>
                        <td>{{ $detail->mobile }} ({{ $detail->contact_id }}) {{ $detail->name }}</td>
                        <td>{{ Carbon::parse($detail->created_at)->format('d-m-Y h:i A') }}</td>
                        <td>
                            <span class="display_currency"
                                data-currency_symbol="true">{{ $detail->total_before_tax }}</span>
                            <br> {{ $detail->total_discount }}
                            <br> {{ $detail->total_line_discount }}
                        </td>
                        <td>
                            {!! $paymentMethods !!}

                        </td>
                    </tr>
                @endforeach
            </tbody>
            @php
                $grandTotal = array_sum($grandTotalsByMethod);
            @endphp
            <tfoot>
                <tr class="success">
                    <th colspan="4" class="text-right"></th>
                    <th colspan="2" class="text-right">
                        <ul class="list-unstyled">
                            @foreach ($grandTotalsByMethod as $method => $amount)
                                <li>
                                    {{ $payment_types[$method] ?? ucfirst($method) }}:
                                    ৳{{ number_format($amount, 2) }}
                                </li>
                            @endforeach
                        </ul>

                        <strong>@lang('lang_v1.grand_total'): ৳{{ number_format($grandTotal, 2) }}</strong>
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>
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
                        {{ number_format($detail->total_quantity) }}
                        @php
                            $total_quantity += $detail->total_quantity;
                        @endphp
                    </td>
                    <td>
                        <span class="display_currency" data-currency_symbol="true">
                            {{ $detail->total_amount }}
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
                <th>{{ number_format($total_quantity) }}</th>
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

<div class="row">
    <div class="col-md-12">
        <hr>
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
                        {{ number_format($detail->total_quantity) }}
                        @php
                            $total_quantity += $detail->total_quantity;
                        @endphp
                    </td>
                    <td>
                        <span class="display_currency"
                            data-currency_symbol="true">{{ $detail->total_amount }}</span>
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
                <th colspan="3"></th>
                <th>{{ number_format($total_quantity) }}</th>
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


<div class="row">
    <div class="col-md-12">
        <hr>
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
                        {{ number_format($detail->total_quantity) }}
                        @php
                            $total_quantity += $detail->total_quantity;
                        @endphp
                    </td>
                    <td>
                        <span class="display_currency" data-currency_symbol="true">
                            {{ $detail->total_amount }}
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
                <th></th>
                <th>{{ number_format($total_quantity) }}</th>
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


<div class="row">
    <div class="col-md-12">
        <hr>
        <h3>@lang('lang_v1.consultation_sold_details_register') (@lang('lang_v1.by_category'))</h3>
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
            @foreach ($details['consultation_details_by_category'] as $detail)
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
                            {{ $detail->total_line_discount }}
                        </span>
                    </td>
                    <td>
                        <span class="display_currency" data-currency_symbol="true">
                            {{ $detail->total_amount }}
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
                <th>{{ number_format($total_quantity) }}</th>
                <th>
                    <span class="display_currency" data-currency_symbol="true">
                        {{ $details['summary']['total_line_discount_amount'] }}
                    </span>
                </th>
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

@if ($details['types_of_service_details'])
    <div class="row">
        <div class="col-md-12">
            <hr>
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

