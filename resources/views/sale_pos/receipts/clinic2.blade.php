<style>
    * {
        margin: 0%;
        padding-right: 5px;
    }

    .table-container {
        position: relative;
    }

    .watermark_due {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 80px;
        color: rgba(19, 16, 16, 0.75) !important;
        white-space: nowrap;
        z-index: 10;
    }

    .row-divider {
        border-bottom: 1px solid #ccc !important;
        margin: 0;
        padding: 0;
    }

    .page-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        width: 100%;
        background-color: white;
        /* Adjust as needed */
        padding: 10px;
        /* Adjust as needed */
        border-top: 1px solid #000;
        /* Optional: Add a border */
    }

    .right-align-header {
        text-align: right !important;
        width: 20% !important;
    }

    .t_bottom_border {
        border-bottom: 1px solid #000000 !important;
    }

    @media print {
        @page {
            size: A5;
            margin-top: 25mm !important;
        }

        body {
            margin-bottom: 150px;
            /* Adjust based on footer height */
        }

        * {
            border: none !important;
            outline: none !important;
        }

        @media print {
            .received_upper_border {
                border-top: 1px solid black !important;
                margin-top: 1px solid black !important;
            }

            .t_bottom_border {
                border-bottom: 1px solid #000000 !important;
            }
        }
    }
</style>
@php
    $show_discount_column = collect($receipt_details->lines)->contains(function ($line) {
        return !empty($line['total_line_discount']) && $line['total_line_discount'] >= 1;
    });
@endphp

<table style="width:100%; color: #000000 !important;">
    {{-- <thead>
        <tr>
            <td colspan="3">
                <p class="text-right">
                    <small class="text-muted-imp">

                    </small>
                </p>
            </td>
        </tr>
    </thead> --}}
    <thead>
        <tr>
            <td>
                <table style="width:100%;">
                    <thead>
                        <th>
                            <p class="text-left">
                                <small class="text-muted-imp">
                                    @if (!empty($receipt_details->client_id))
                                        {{ $receipt_details->client_id_label }}
                                        {!! $receipt_details->client_id !!}
                                    @endif
                                </small>
                            </p>
                        </th>
                        <th colspan="2">
                            <p class="text-right">
                                <small class="text-muted-imp">
                                    @if (!empty($receipt_details->invoice_no_prefix))
                                        {!! $receipt_details->invoice_no_prefix !!}
                                    @endif

                                    {{ $receipt_details->invoice_no }}
                                </small>
                            </p>
                        </th>
                    </thead>
                    <tbody>
                        <br>
                        <tr>
                            <td style="width:33.33%">

                                @if ($receipt_details->show_barcode)
                                    <img class="right-block"
                                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->client_id, 'C39', 1.0, 30, [39, 48, 54], false) }}">
                                @endif
                            </td>
                            <td class="text-center" style="width:33.33%">
                                @if (empty($receipt_details->letter_head))
                                    @if (!empty($receipt_details->header_text))
                                        {!! $receipt_details->header_text !!}
                                    @endif

                                    @php
                                        $sub_headings = implode(
                                            '<br/>',
                                            array_filter([
                                                $receipt_details->sub_heading_line1,
                                                $receipt_details->sub_heading_line2,
                                                $receipt_details->sub_heading_line3,
                                                $receipt_details->sub_heading_line4,
                                                $receipt_details->sub_heading_line5,
                                            ]),
                                        );
                                    @endphp

                                    @if (!empty($sub_headings))
                                        <span>{!! $sub_headings !!}</span>
                                    @endif
                                @endif
                                @if (!empty($receipt_details->invoice_heading))
                                    <div style="margin-top: -70px;">
                                        <h2>{!! $receipt_details->invoice_heading !!}</h2>
                                    </div>
                                @endif
                            </td>
                            <td class="text-right" style="width:33.33%">
                                @if ($receipt_details->show_barcode || $receipt_details->show_qr_code)
                                    {{-- Barcode --}}
                                    @if ($receipt_details->show_barcode)
                                        <img class="right-block"
                                            src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], false) }}">
                                    @endif


                                    @if (!empty($receipt_details->show_qr_code) && !empty($receipt_details->qr_code_text))
                                        <img class="center-block mt-5"
                                            src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54]) }}">
                                    @endif
                                @endif
                            </td>
                        </tr>

                    </tbody>
                </table>
            </td>
        </tr>
    </thead>
    <tbody>


        <tr>
            <td>
                <div class="row" style="font-size:10px;margin-top:6px;">
                    <div class="col-md-6 invoice-col width-50">
                        @if (!empty($receipt_details->all_due))
                            <div class="text-right font-10 padding-5">
                                <span class="pull-left">
                                    {!! $receipt_details->all_bal_label !!}
                                </span>

                                {{ $receipt_details->all_due }}
                            </div>
                        @endif

                        <!-- Total Paid-->
                        {{-- @if (!empty($receipt_details->total_paid))
                            <div class="text-right font-10 ">
                                <span class="pull-left">{!! $receipt_details->total_paid_label !!}</span>
                                {{number_format($receipt_details->total_paid,2)}}
                            </div>
                        @endif --}}


                        @if (!empty($receipt_details->due_date_label))
                            <div class="font-10">
                                <span class="pull-left">
                                    {{ $receipt_details->due_date_label }}
                                </span>

                                {{ $receipt_details->due_date ?? '' }}
                            </div>
                        @endif

                        @if (!empty($receipt_details->sell_custom_field_1_value))
                            <div class="font-10 ">
                                <span class="pull-left">
                                    {{ $receipt_details->sell_custom_field_1_label }}
                                </span>

                                {{ $receipt_details->sell_custom_field_1_value }}
                            </div>
                        @endif
                        @if (!empty($receipt_details->sell_custom_field_2_value))
                            <div class="font-10 ">
                                <span class="pull-left">
                                    {{ $receipt_details->sell_custom_field_2_label }}
                                </span>

                                {{ $receipt_details->sell_custom_field_2_value }}
                            </div>
                        @endif
                        @if (!empty($receipt_details->sell_custom_field_3_value))
                            <div class="font-10 ">
                                <span class="pull-left">
                                    {{ $receipt_details->sell_custom_field_3_label }}
                                </span>

                                {{ $receipt_details->sell_custom_field_3_value }}
                            </div>
                        @endif
                        @if (!empty($receipt_details->sell_custom_field_4_value))
                            <div class="font-10 ">
                                <span class="pull-left">
                                    {{ $receipt_details->sell_custom_field_4_label }}
                                </span>

                                {{ $receipt_details->sell_custom_field_4_value }}
                            </div>
                        @endif
                        {{-- <div class="row text-right">
                            @if ($receipt_details->show_barcode || $receipt_details->show_qr_code)
                                @if ($receipt_details->show_barcode)
                                    <img class="right-block"
                                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2, 30, [39, 48, 54], true) }}">
                                @endif

                                @if (!empty($receipt_details->show_qr_code) && !empty($receipt_details->qr_code_text))
                                    <img class="center-block mt-5"
                                        src="data:image/png;base64,{{ DNS2D::getBarcodePNG($receipt_details->qr_code_text, 'QRCODE', 3, 3, [39, 48, 54]) }}">
                                @endif
                            @endif
                        </div>
                        <br> --}}
                        <div class="word-wrap">
                            <div class="text-left">
                                @if (!empty($receipt_details->customer_label))
                                    <b>{{ $receipt_details->customer_label }}</b><br />
                                @endif

                                <!-- customer info -->
                                @if (!empty($receipt_details->customer_info_clinic))
                                    {!! $receipt_details->customer_info_clinic !!}
                                @endif

                            </div>
                            @if (!empty($receipt_details->client_id_label))
                                {{-- <br /> --}}
                                <strong>{{ $receipt_details->client_id_label }}</strong>
                                {{ $receipt_details->client_id }}
                            @endif
                            @if (!empty($receipt_details->customer_tax_label))
                                <br />
                                <strong>{{ $receipt_details->customer_tax_label }}</strong>
                                {{ $receipt_details->customer_tax_number }}
                            @endif
                            @if (!empty($receipt_details->customer_custom_fields))
                                <br />{!! $receipt_details->customer_custom_fields !!}
                            @endif


                            {{-- @if (!empty($receipt_details->commission_agent_label))
                                <br />
                                <strong>{{ $receipt_details->commission_agent_label }}</strong>
                                {{ $receipt_details->commission_agent }}
                            @endif --}}

                            @if (!empty($receipt_details->customer_rp_label))
                                <br />
                                <strong>{{ $receipt_details->customer_rp_label }}</strong>
                                {{ $receipt_details->customer_total_rp }}
                            @endif

                            <!-- Display type of service details -->
                            @if (!empty($receipt_details->types_of_service))
                                <span class="pull-left text-left">
                                    <strong>{!! $receipt_details->types_of_service_label !!}:</strong>
                                    {{ $receipt_details->types_of_service }}
                                    <!-- Waiter info -->
                                    @if (!empty($receipt_details->types_of_service_custom_fields))
                                        <br>
                                        @foreach ($receipt_details->types_of_service_custom_fields as $key => $value)
                                            <strong>{{ $key }}: </strong> {{ $value }}@if (!$loop->last)
                                                ,
                                            @endif
                                        @endforeach
                                    @endif
                                </span>
                            @endif
                        </div>


                    </div>
                    <div class="col-md-6 invoice-col width-50 text-right">
                        @if (empty($receipt_details->letter_head))
                            <!-- Logo -->
                            @if (!empty($receipt_details->logo))
                                <img style="max-height: 130px; width: auto;" src="{{ $receipt_details->logo }}"
                                    class="img center-block">
                                <br />
                            @endif

                            <!-- Shop & Location Name  -->

                            {{-- <span>
                                @if (!empty($receipt_details->display_name))
                                    {{ $receipt_details->display_name }}
                                    <br />
                                @endif

                                @if (!empty($receipt_details->address))
                                    {!! $receipt_details->address !!}
                                @endif

                                @if (!empty($receipt_details->contact))
                                    <br />{!! $receipt_details->contact !!}
                                @endif

                                @if (!empty($receipt_details->website))
                                    <br />{{ $receipt_details->website }}
                                @endif

                                @if (!empty($receipt_details->tax_info1))
                                    <br />{{ $receipt_details->tax_label1 }} {{ $receipt_details->tax_info1 }}
                                @endif

                                @if (!empty($receipt_details->tax_info2))
                                    <br />{{ $receipt_details->tax_label2 }} {{ $receipt_details->tax_info2 }}
                                @endif

                                @if (!empty($receipt_details->location_custom_fields))
                                    <br />{{ $receipt_details->location_custom_fields }}
                                @endif
                            </span> --}}
                        @endif
                        <!-- Date-->
                        @if (!empty($receipt_details->date_label))
                            <div class="font-10 ">
                                <span class="pull-left">
                                </span>
                                <b>{{ $receipt_details->date_label }}:</b>

                                {{ $receipt_details->invoice_date }}
                            </div>
                        @endif
                        @if (!empty($receipt_details->sales_person_label))
                            <strong>{{ $receipt_details->sales_person_label }}</strong>
                            {{ $receipt_details->sales_person }}
                            <br />
                        @endif
                        @if (!empty($receipt_details->reference))
                            <b>Referred by : </b>{{ $receipt_details->reference }}
                            <br>{{ $receipt_details->designation }}
                        @endif
                        <!-- Table information-->
                        @if (!empty($receipt_details->table_label) || !empty($receipt_details->table))
                            <p>
                                @if (!empty($receipt_details->table_label))
                                    {!! $receipt_details->table_label !!}
                                @endif
                                {{ $receipt_details->table }}
                            </p>
                        @endif

                        <!-- Waiter info -->
                        @if (!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
                            <p>
                                @if (!empty($receipt_details->service_staff_label))
                                    {!! $receipt_details->service_staff_label !!}
                                @endif
                                {{ $receipt_details->service_staff }}
                            </p>
                        @endif



                        <div class="word-wrap">

                            <p class="text-right ">

                                @if (!empty($receipt_details->brand_label) || !empty($receipt_details->repair_brand))
                                    @if (!empty($receipt_details->brand_label))
                                        <span class="pull-left">
                                            <strong>{!! $receipt_details->brand_label !!}</strong>
                                        </span>
                                    @endif
                                    {{ $receipt_details->repair_brand }}<br>
                                @endif


                                @if (!empty($receipt_details->device_label) || !empty($receipt_details->repair_device))
                                    @if (!empty($receipt_details->device_label))
                                        <span class="pull-left">
                                            <strong>{!! $receipt_details->device_label !!}</strong>
                                        </span>
                                    @endif
                                    {{ $receipt_details->repair_device }}<br>
                                @endif

                                @if (!empty($receipt_details->model_no_label) || !empty($receipt_details->repair_model_no))
                                    @if (!empty($receipt_details->model_no_label))
                                        <span class="pull-left">
                                            <strong>{!! $receipt_details->model_no_label !!}</strong>
                                        </span>
                                    @endif
                                    {{ $receipt_details->repair_model_no }} <br>
                                @endif

                                @if (!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
                                    @if (!empty($receipt_details->serial_no_label))
                                        <span class="pull-left">
                                            <strong>{!! $receipt_details->serial_no_label !!}</strong>
                                        </span>
                                    @endif
                                    {{ $receipt_details->repair_serial_no }}<br>
                                @endif
                                @if (!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
                                    @if (!empty($receipt_details->repair_status_label))
                                        <span class="pull-left">
                                            <strong>{!! $receipt_details->repair_status_label !!}</strong>
                                        </span>
                                    @endif
                                    {{ $receipt_details->repair_status }}<br>
                                @endif

                                @if (!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
                                    @if (!empty($receipt_details->repair_warranty_label))
                                        <span class="pull-left">
                                            <strong>{!! $receipt_details->repair_warranty_label !!}</strong>
                                        </span>
                                    @endif
                                    {{ $receipt_details->repair_warranty }}
                                    <br>
                                @endif
                            </p>
                        </div>
                    </div>


                </div>
                @if (!empty($receipt_details->shipping_custom_field_1_label) || !empty($receipt_details->shipping_custom_field_2_label))
                    <div class="row">
                        <div class="col-xs-6">
                            @if (!empty($receipt_details->shipping_custom_field_1_label))
                                <strong>{!! $receipt_details->shipping_custom_field_1_label !!} :</strong> {!! $receipt_details->shipping_custom_field_1_value ?? '' !!}
                            @endif
                        </div>
                        <div class="col-xs-6">
                            @if (!empty($receipt_details->shipping_custom_field_2_label))
                                <strong>{!! $receipt_details->shipping_custom_field_2_label !!}:</strong> {!! $receipt_details->shipping_custom_field_2_value ?? '' !!}
                            @endif
                        </div>
                    </div>
                @endif
                @if (!empty($receipt_details->shipping_custom_field_3_label) || !empty($receipt_details->shipping_custom_field_4_label))
                    <div class="row">
                        <div class="col-xs-6">
                            @if (!empty($receipt_details->shipping_custom_field_3_label))
                                <strong>{!! $receipt_details->shipping_custom_field_3_label !!} :</strong> {!! $receipt_details->shipping_custom_field_3_value ?? '' !!}
                            @endif
                        </div>
                        <div class="col-xs-6">
                            @if (!empty($receipt_details->shipping_custom_field_4_label))
                                <strong>{!! $receipt_details->shipping_custom_field_4_label !!}:</strong> {!! $receipt_details->shipping_custom_field_4_value ?? '' !!}
                            @endif
                        </div>
                    </div>
                @endif
                @if (!empty($receipt_details->shipping_custom_field_5_label))
                    <div class="row">
                        <div class="col-xs-6">
                            @if (!empty($receipt_details->shipping_custom_field_5_label))
                                <strong>{!! $receipt_details->shipping_custom_field_5_label !!} :</strong> {!! $receipt_details->shipping_custom_field_5_value ?? '' !!}
                            @endif
                        </div>
                    </div>
                @endif
                @if (!empty($receipt_details->sale_orders_invoice_no) || !empty($receipt_details->sale_orders_invoice_date))
                    <div class="row">
                        <div class="col-xs-6">
                            <strong>@lang('restaurant.order_no'):</strong> {!! $receipt_details->sale_orders_invoice_no ?? '' !!}
                        </div>
                        <div class="col-xs-6">
                            <strong>@lang('lang_v1.order_dates'):</strong> {!! $receipt_details->sale_orders_invoice_date ?? '' !!}
                        </div>
                    </div>
                @endif
                <div class="row">
                    @includeIf('sale_pos.receipts.partial.common_repair_invoice')
                </div>
                <div class="row  mt-5">
                    <div class="col-xs-12 table-container"
                        style="height: 300px; overflow: hidden;break-inside: avoid;  page-break-inside: avoid;">
                        <table class="table table-slim" style="font-size:10px;  max-height:300px;">
                            <thead style="border-top: 1px solid black !important;" class="t_bottom_border">
                                <tr>
                                    <th style="text-align: center; vertical-align: middle; width: 5% !important;">#</th>
                                    <th style="text-align: center; vertical-align: middle; width: 35% !important;">
                                        {{ $receipt_details->table_product_label }}</th>
                                    @if ($receipt_details->show_cat_code == 1)
                                        <th style="text-align: right; vertical-align: middle; width: 10% !important;">
                                            {{ $receipt_details->cat_code_label }}</th>
                                    @endif
                                    <th style="text-align: center; vertical-align: middle; width: 20% !important;">
                                        {{ $receipt_details->table_qty_label }}</th>
                                    @if (!empty($receipt_details->item_discount_label) && $show_discount_column)
                                        <th style="text-align: center; vertical-align: middle; width: 20% !important;">
                                            {{ $receipt_details->item_discount_label }}</th>
                                    @endif
                                    <th style="text-align: right; vertical-align: middle; width: 20% !important;">
                                        {{ $receipt_details->table_unit_price_label }}</th>

                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $subtotal = 0;
                                @endphp

                                @foreach ($receipt_details->lines as $line)
                                    <tr>
                                        <td class="text-center" style="padding-top:3px;">{{ $loop->iteration }}</td>
                                        <td style="padding-top:3px;">
                                            @if (!empty($line['image']))
                                                <img src="{{ $line['image'] }}" alt="Image" width="50"
                                                    style="float: left; margin-right: 8px;">
                                            @endif
                                            {{ $line['name'] }} {{ $line['product_variation'] }}
                                            {{ $line['variation'] }}
                                            @if (!empty($line['sub_sku']))
                                                , {{ $line['sub_sku'] }}
                                            @endif
                                            @if (!empty($line['brand']))
                                                @if (!empty($receipt_details->sub_type) && $receipt_details->sub_type == 'test' && $line['brand'] != 'Non Govt. Rate')
                                                    , {{ $line['brand'] }}
                                                @endif
                                            @endif
                                            @if (!empty($line['product_custom_fields']))
                                                , {{ $line['product_custom_fields'] }}
                                            @endif
                                            @if (!empty($line['product_description']))
                                                <small>{!! $line['product_description'] !!}</small>
                                            @endif
                                            @if (!empty($line['sell_line_note']))
                                                <br><small>{!! $line['sell_line_note'] !!}</small>
                                            @endif
                                            @if (!empty($line['lot_number']))
                                                <br>{{ $line['lot_number_label'] }}: {{ $line['lot_number'] }}
                                            @endif
                                            @if (!empty($line['product_expiry']))
                                                , {{ $line['product_expiry_label'] }}: {{ $line['product_expiry'] }}
                                            @endif
                                            @if (!empty($line['warranty_name']))
                                                <br><small>{{ $line['warranty_name'] }}</small>
                                            @endif
                                            @if (!empty($line['warranty_exp_date']))
                                                <small>- {{ @format_date($line['warranty_exp_date']) }}</small>
                                            @endif
                                            @if (!empty($line['warranty_description']))
                                                <small>{{ $line['warranty_description'] ?? '' }}</small>
                                            @endif
                                            @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                                <br><small>
                                                    1 {{ $line['units'] }} = {{ $line['base_unit_multiplier'] }}
                                                    {{ $line['base_unit_name'] }}<br>
                                                    {{ $line['base_unit_price'] }} x {{ $line['orig_quantity'] }} =
                                                    {{ $line['line_total'] }}
                                                </small>
                                            @endif
                                        </td>
                                        @if ($receipt_details->show_cat_code == 1)
                                            <td style="padding-top:3px; text-align:right">
                                                @if (!empty($line['cat_code']))
                                                    {{ $line['cat_code'] }}
                                                @endif
                                            </td>
                                        @endif
                                        <td class="text-center"
                                            style="font-size:10px; padding-top:3px; text-align:center">
                                            {{ number_format($line['quantity'], 0) }}
                                            {{-- {{ $line['units'] }} --}}
                                            @if ($receipt_details->show_base_unit_details && $line['quantity'] && $line['base_unit_multiplier'] !== 1)
                                                <br><small>
                                                    {{ $line['quantity'] }} x {{ $line['base_unit_multiplier'] }} =
                                                    {{ $line['orig_quantity'] }} {{ $line['base_unit_name'] }}
                                                </small>
                                            @endif
                                        </td>
                                        @if (!empty($receipt_details->item_discount_label) && $show_discount_column)
                                            <td class="text-center" style="padding-top:3px; text-align:center">
                                                @if (!empty($line['line_discount_percent']))
                                                    {{ $line['line_discount_percent'] }}%
                                                @else
                                                    @if (!empty($line['line_discount_uf']))
                                                        {{ number_format($line['line_discount_uf'], 2) }}
                                                    @endif
                                                @endif

                                            </td>
                                        @endif
                                        <td class="text-center" style="padding-top:3px; text-align:right">
                                            @if ($line['unit_price_before_discount'] > 0)
                                                {{ number_format($line['unit_price_before_discount'], 2) }}
                                            @endif
                                        </td>

                                    </tr>
                                    @if (!empty($line['modifiers']))
                                        @foreach ($line['modifiers'] as $modifier)
                                            <tr>
                                                <td>&nbsp;</td>
                                                <td style="font-style:italic; padding-left:10px;">
                                                    {{ $modifier['variation'] }}
                                                    @if (!empty($modifier['sub_sku']))
                                                        {{-- , {{ $modifier['sub_sku'] }} --}}
                                                    @endif
                                                    @if (!empty($modifier['sell_line_note']))
                                                        ({!! $modifier['sell_line_note'] !!})
                                                    @endif
                                                </td>
                                                @if ($receipt_details->show_cat_code == 1)
                                                    <td style="text-align: right;">
                                                        @if (!empty($modifier['cat_code']))
                                                            {{ $modifier['cat_code'] }}
                                                        @endif
                                                    </td>
                                                @endif
                                                <td></td>
                                                @if ($show_discount_column)
                                                    <td></td>
                                                @endif
                                                <td class="text-right">
                                                    {{ number_format($modifier['unit_price_exc_tax'], 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach

                                @php
                                    $lines = count($receipt_details->lines);
                                @endphp

                                @for ($i = $lines; $i < 7; $i++)
                                    <tr>
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        @if ($receipt_details->show_cat_code == 1)
                                            <td>&nbsp;</td>
                                        @endif
                                        <td>&nbsp;</td>
                                        <td>&nbsp;</td>
                                        @if (!empty($receipt_details->item_discount_label) && $show_discount_column)
                                            <td>&nbsp;</td>
                                        @endif
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                        @if (!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
                            <div class="watermark_due">
                                {!! $receipt_details->total_due_label !!}
                            </div>
                        @endif
                        @if (!empty($receipt_details->status))
                            <div class="watermark_due"
                                style="font-family: Verdana, Geneva, Tahoma, sans-serif !important;">
                                {{ $receipt_details->status }}
                            </div>
                        @endif
                    </div>
                    <div style="page-break-after: always;"></div>
                </div>
                {{-- <div class="row-divider"></div> --}}



            </td>
        </tr>

    </tbody>

    <tfoot>
        <tr>
            <td>
                <div class="page-footer">


                    <div class="row" style="page-break-inside: avoid !important">
                        <div class="col-md-8 invoice-col" style="width:64.5%; font-size: 9px;">
                            @if (!empty($receipt_details->payments))

                                <p><b>Payment Details</b></p>
                                <table class="table table-slim">
                                    @foreach ($receipt_details->payments as $payment)
                                        <tr>
                                            <td>
                                                <b>{{ $payment['method_name'] }}</b>
                                                <small>{{ $payment['method_id'] }}</small>
                                            </td>
                                            <td>{{ number_format($payment['amount'], 2) }}</td>
                                            <td>{{ $payment['date'] }}</td>
                                            <td>{{ $payment['created_by'] }}</td>
                                        </tr>
                                        <tr>
                                            <td colspan="{{ count($payment) }}">
                                                <hr class="row-divider">
                                            </td>
                                        </tr>
                                    @endforeach

                                </table>
                            @endif
                            @if (!empty($receipt_details->additional_notes))
                                <div class="row">
                                    <div class="col-xs-12">
                                        <br>
                                        <i> <b>Note: </b> {!! nl2br($receipt_details->additional_notes) !!}</i>
                                    </div>
                                </div>
                            @endif
                            <b class="pull-left" style="margin-top: 80px;">Authorize Signature</b>
                        </div>

                        <div class="col-md-4 invoice-col" style="font-size: 10px;">
                            <table class="table table-slim">
                                <tbody>
                                    @if (!empty($receipt_details->total_quantity_label))
                                        <tr>
                                            <td style="width:50%">
                                                {!! $receipt_details->total_quantity_label !!}
                                            </td>
                                            <td class="text-right">
                                                {{ $receipt_details->total_quantity }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if (!empty($receipt_details->total_items_label))
                                        <tr>
                                            <td style="width:50%">
                                                {!! $receipt_details->total_items_label !!}
                                            </td>
                                            <td class="text-right">
                                                {{ $receipt_details->total_items }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td style="width:50%">
                                            {!! $receipt_details->subtotal_label !!}
                                        </td>
                                        <td class="text-right">
                                            ৳
                                            {{ number_format($receipt_details->subtotal + $receipt_details->total_line_discount) }}
                                        </td>
                                    </tr>

                                    <!-- Shipping Charges -->
                                    @if (!empty($receipt_details->shipping_charges))
                                        <tr>
                                            <td style="width:50%">
                                                {!! $receipt_details->shipping_charges_label !!}
                                            </td>
                                            <td class="text-right">
                                                {{ $receipt_details->shipping_charges }}
                                            </td>
                                        </tr>
                                    @endif

                                    @if (!empty($receipt_details->packing_charge))
                                        <tr>
                                            <td style="width:50%">
                                                {!! $receipt_details->packing_charge_label !!}
                                            </td>
                                            <td class="text-right">
                                                {{ $receipt_details->packing_charge }}
                                            </td>
                                        </tr>
                                    @endif

                                    <!-- Tax -->
                                    @if (!empty($receipt_details->taxes))
                                        @foreach ($receipt_details->taxes as $k => $v)
                                            <tr>
                                                <td>{{ $k }}</td>
                                                <td class="text-right">(+) {{ $v }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (!empty($receipt_details->total_line_discount))
                                        <tr>
                                            <td>
                                                {{-- {!! $receipt_details->line_discount_label !!} --}} Campaign Discount:
                                            </td>

                                            <td class="text-right">
                                                (-) {{ $receipt_details->total_line_discount }}
                                            </td>
                                        </tr>
                                    @endif
                                    <!-- Discount -->
                                    @if (!empty($receipt_details->discount))
                                        <tr>
                                            <td>
                                                Special {!! $receipt_details->discount_label !!}
                                            </td>

                                            <td class="text-right">
                                                (-) {{ $receipt_details->discount }}
                                            </td>
                                        </tr>
                                    @endif



                                    @if (!empty($receipt_details->additional_expenses))
                                        @foreach ($receipt_details->additional_expenses as $key => $val)
                                            <tr>
                                                <td>
                                                    {{ $key }}:
                                                </td>

                                                <td class="text-right">
                                                    (+)
                                                    {{ $val }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif

                                    @if (!empty($receipt_details->reward_point_label))
                                        <tr>
                                            <td>
                                                {!! $receipt_details->reward_point_label !!}
                                            </td>

                                            <td class="text-right">
                                                (-) {{ $receipt_details->reward_point_amount }}
                                            </td>
                                        </tr>
                                    @endif

                                    @if (!empty($receipt_details->group_tax_details))
                                        @foreach ($receipt_details->group_tax_details as $key => $value)
                                            <tr>
                                                <td>
                                                    {!! $key !!}
                                                </td>
                                                <td class="text-right">
                                                    (+)
                                                    {{ $value }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @if (!empty($receipt_details->tax))
                                            <tr>
                                                <td>
                                                    {!! $receipt_details->tax_label !!}
                                                </td>
                                                <td class="text-right">
                                                    (+) {{ $receipt_details->tax }}
                                                </td>
                                            </tr>
                                        @endif
                                    @endif

                                    {{-- @if ($receipt_details->round_off_amount > 0)
                                    <tr>
                                        <td>
                                            {!! $receipt_details->round_off_label !!}
                                        </td>
                                        <td class="text-right">
                                            {{ number_format($receipt_details->round_off, 2) }}
                                        </td>
                                    </tr>
                                @endif --}}

                                    <!-- Total -->
                                    @if (!empty($receipt_details->total_due) && !empty($receipt_details->total_due_label))
                                        <tr>
                                            <th style="" class="">
                                                {!! $receipt_details->total_due_label !!}
                                            </th>
                                            <td class="text-right">
                                                {{ $receipt_details->total_due }}
                                            </td>
                                        </tr>

                                        {{-- <div class="text-right font-10 padding-5">
                                        <span class="pull-left">
                                            {!! $receipt_details->total_due_label !!}
                                        </span>

                                        {{ $receipt_details->total_due }}
                                    </div> --}}
                                    @else
                                        <tr>
                                            <th style="" class="font-10">
                                                {{-- {!! $receipt_details->total_label !!} --}}Payable:
                                            </th>
                                            <td class="text-right font-10 " style="">
                                                ৳ {{ number_format($receipt_details->total) }}
                                            </td>
                                        </tr>
                                        @if (!empty($receipt_details->total_in_words))
                                            <tr>
                                                <td colspan="2" class="text-right">
                                                    <small>({{ $receipt_details->total_in_words }})</small>
                                                </td>
                                            </tr>
                                        @endif
                                    @endif

                                    @if (!empty($receipt_details->total_paid))
                                        <tr style="border:solid #413939 2px;">


                                        </tr>
                                        <tr class="received_upper_border">

                                            <th class="font-10 ">
                                                {{-- {!! $receipt_details->total_paid_label !!} --}} Received:
                                            </th>
                                            <th class="text-right font-10" style="">
                                                ৳ {{ number_format($receipt_details->total_paid) }}
                                            </th>
                                        </tr>
                                    @endif


                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="border-bottom col-md-12">
                        @if (empty($receipt_details->hide_price) && !empty($receipt_details->tax_summary_label))
                            <!-- tax -->
                            @if (!empty($receipt_details->taxes))
                                <table class="table table-slim">
                                    <tr>
                                        <th colspan="2" class="text-center">
                                            {{ $receipt_details->tax_summary_label }}
                                        </th>
                                    </tr>
                                    @foreach ($receipt_details->taxes as $key => $val)
                                        <tr>
                                            <td class="text-center"><b>{{ $key }}</b></td>
                                            <td class="text-center">{{ $val }}</td>
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
                        @endif

                        <div style="display: flex; justify-content: space-between; font-size: 10px;">
                            <span></span>
                            <span>Printed by {{ auth()->user()->username }} on
                                {{ now()->format('Y-m-d H:i:s') }}</span>
                        </div>
                    </div>
                </div>
            </td>
        </tr>
    </tfoot>
</table>
