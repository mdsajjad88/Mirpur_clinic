@extends('clinic::layouts.app2')
@section('title', __('All Bills'))
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>Bills
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @if ($RemaininInLast > 0)
            <div class="alert alert-info">
                <span>Your total due {{ $RemaininInLast }} in last 30 days </span> <span
                    style="text-decoration: underline; color:blue; cursor: pointer;" id="due_filter">Please click here for
                    view
                    all due sell</span>
            </div>
        @endif

        @component('components.filters', ['title' => __('report.filters')])
            @include('clinic::sell.partials.sell_list_filters')
            @if (!empty($sources))
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sell_list_filter_source', __('lang_v1.sources') . ':') !!}

                        {!! Form::select('sell_list_filter_source', $sources, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
            @endif
            <div class="col-md-3">
                <div class="form-group">
                    @php

                        $sub_type = [
                            'test' => 'Test',
                            'therapy' => 'Therapy',
                            'ipd' => 'IPD',
                            'consultation' => 'Consultation',
                        ];
                    @endphp
                    {!! Form::label('sub_type', __('clinic::lang.billing_type') . ':') !!}

                    {!! Form::select('sub_type', $sub_type, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'id' => 'sub_type',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('categories', __('clinic::lang.category') . ':') !!}
                    {!! Form::select('category_id', $categories, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'multiple' => 'multiple',
                        'id' => 'product_list_filter_category_id',
                    ]) !!}
                </div>
            </div>
        @endcomponent
        <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#all_sell_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                    aria-hidden="true"></i> All Bills</a>
                        </li>
                        <li>
                            <a href="#product_sell_tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fas fa-calendar-check"></i> Today Bill Details</a>
                        </li>
                        <li>
                            <a href="#sell_payment_tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fas fa-calendar-check"></i> Today Payment Details</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="all_sell_list_tab">
                            @can('clinic.sell.create')
                                <a class="btn btn-primary pull-right"
                                    href="{{ action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'create']) }}">
                                    <i class="fa fa-plus"></i> @lang('messages.add')</a> <br><br>
                            @endcan
                            @include('clinic::sell.partials.all_sell_table')
                        </div>
                        <div class="tab-pane" id="product_sell_tab">
                            @include('clinic::sell.today_sell')
                        </div>
                        <div class="tab-pane" id="sell_payment_tab">
                            @include('clinic::sell.today_payment')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <input type="hidden" id="stock_expiry_alert_days"
        value="{{ \Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d') }}">
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <input type="hidden" id="register_report" value="{{ $register }}" data-register="{{ $register }}">
    <!-- This will be printed -->
    <!-- <section class="invoice print_section" id="receipt_section">
                                                                                                                    </section> -->

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var today_payment_table = $('#today_payment_table').DataTable({
                processing: true,
                serverSide: true,
                
                aaSorting: [
                    [1, 'desc']
                ], // Sort by date
                ajax: {
                    url: "/clinic-sell-payment-report",
                    data: function(d) {
                        var start = moment().startOf('day').format('YYYY-MM-DD');
                        var end = moment().endOf('day').format('YYYY-MM-DD');
                        d.start_date = start;
                        d.end_date = end;
                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.customer_id = $('#sell_list_filter_customer_id').val();
                        d.payment_status = $('#sell_list_filter_payment_status').val();
                        d.created_by = $('#created_by').val();
                        d.payment_method = $('#sell_list_filter_payment_method').val();
                        d.sub_type = $('#sub_type').val();
                        d.status = $('#status').val();
                    }
                },
               
                columns: [{
                        data: 'invoice_no',
                        name: 'transactions.invoice_no'
                    },
                    {
                        data: 'pay_date',
                        name: 'transaction_payments.paid_on'
                    },
                    {
                        data: 'sale_date',
                        name: 'transactions.transaction_date'
                    },
                    {
                        data: 'name',
                        name: 'contacts.name'
                    },
                    {
                        data: 'sub_type',
                        name: 'transactions.sub_type'
                    },
                    {
                        data: 'method',
                        name: 'transaction_payments.method'
                    },
                    {
                        data: 'total_items',
                        name: 'total_items'
                    },
                    {
                        data: 'final_total',
                        name: 'transactions.final_total'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid'
                    },
                    {
                        data: 'total_payment',
                        name: 'total_payment'
                    },
                    {
                        data: 'payment_status',
                        name: 'transactions.payment_status'
                    },
                    {
                        data: 'added_by',
                        name: 'added_by'
                    }
                ],
                fnDrawCallback: function(oSettings) {
                    let api = this.api();
                    // Calculate the total payment using the correct column index (9 for total_payment)
                    let totalPayment = api.column(9, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                        if (!isNaN(numericValueB)) {
                            return parseFloat(a) + numericValueB;
                        }
                        return parseFloat(a);
                    }, 0);

                    // Update the footer with the totals
                    $('#footer_total_today_pay').text(totalPayment.toFixed(2));
                    __currency_convert_recursively($('#today_payment_table'));
                },
            });
            //Date range as a button
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    clinic_sell_table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                clinic_sell_table.ajax.reload();
            });

            clinic_sell_table = $('#clinic_sell_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": "/clinic-sell",
                    "data": function(d) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                        d.is_direct_sale = 1;

                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.customer_id = $('#sell_list_filter_customer_id').val();
                        d.payment_status = $('#sell_list_filter_payment_status').val();
                        d.created_by = $('#created_by').val();
                        d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                        d.service_staffs = $('#service_staffs').val();
                        d.discount_type = $('#sell_list_filter_discount_type').val();
                        d.payment_method = $('#sell_list_filter_payment_method').val();
                        d.sub_type = $('#sub_type').val();
                        if ($('#shipping_status').length) {
                            d.shipping_status = $('#shipping_status').val();
                        }


                        if ($('#sell_list_filter_source').length) {
                            d.source = $('#sell_list_filter_source').val();
                        }

                        if ($('#only_subscriptions').is(':checked')) {
                            d.only_subscriptions = 1;
                        }
                        if ($('#delivery_sales').is(':checked')) {
                            d.delivery_sales = 1;
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'sub_type',
                        name: 'sub_type',
                        render: function(data, type, row) {
                            if (data) {
                                return data.charAt(0).toUpperCase() + data.slice(1);
                            }
                            return data;
                        },

                    },
                    {
                        data: 'payment_methods',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid',
                        "searchable": false
                    },
                    {
                        data: 'total_remaining',
                        name: 'total_remaining'
                    },
                    {
                        data: 'line_discount_amount',
                        name: 'line_discount_amount',
                        "searchable": false
                    },
                    {
                        data: 'discount_amount',
                        name: 'discount_amount'
                    },
                    {
                        data: 'return_due',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    {
                        data: 'total_items',
                        name: 'total_items',
                        "searchable": false
                    },
                    {
                        data: 'types_of_service_name',
                        name: 'tos.name',
                        @if (empty($is_types_service_enabled))
                            visible: false
                        @endif
                    },
                    {
                        data: 'service_custom_field_1',
                        name: 'service_custom_field_1',
                        @if (empty($is_types_service_enabled))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_1',
                        name: 'transactions.custom_field_1',
                        @if (empty($custom_labels['sell']['custom_field_1']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_2',
                        name: 'transactions.custom_field_2',
                        @if (empty($custom_labels['sell']['custom_field_2']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_3',
                        name: 'transactions.custom_field_3',
                        @if (empty($custom_labels['sell']['custom_field_3']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_4',
                        name: 'transactions.custom_field_4',
                        @if (empty($custom_labels['sell']['custom_field_4']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                    {
                        data: 'additional_notes',
                        name: 'additional_notes'
                    },
                    {
                        data: 'staff_note',
                        name: 'staff_note'
                    },
                    {
                        data: 'shipping_details',
                        name: 'shipping_details'
                    },
                    {
                        data: 'table_name',
                        name: 'tables.name',
                        @if (empty($is_tables_enabled))
                            visible: false
                        @endif
                    },
                    {
                        data: 'waiter',
                        name: 'ss.first_name',
                        @if (empty($is_service_staff_enabled))
                            visible: false
                        @endif
                    },
                    {
                        data: 'reference',
                        name: 'dpro.first_name',
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#clinic_sell_table'));
                },
                "footerCallback": function(row, data, start, end, display) {
                    var footer_sale_total = 0;
                    var footer_total_paid = 0;
                    var footer_total_remaining = 0;
                    var footer_total_sell_return_due = 0;
                    var footer_total_special_discount = 0;
                    var footer_total_campaign_discount = 0;

                    // Initialize an object to store payment method counts and subtotals
                    var paymentMethodTotals = {};

                    // Loop through each data row
                    for (var r in data) {
                        // Accumulate totals for sale total, total paid, total remaining, and sell return due
                        footer_sale_total += $(data[r].final_total).data('orig-value') ? parseFloat($(
                            data[r].final_total).data('orig-value')) : 0;
                        footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(
                            data[r].total_paid).data('orig-value')) : 0;
                        footer_total_remaining += $(data[r].total_remaining).data('orig-value') ?
                            parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                        footer_total_sell_return_due += $(data[r].return_due).find('.sell_return_due')
                            .data('orig-value') ? parseFloat($(data[r].return_due).find(
                                '.sell_return_due').data('orig-value')) : 0;

                        // Special Discount: Ensure proper parsing and data retrieval
                        footer_total_special_discount += $(data[r].discount_amount).data('orig-value') ?
                            parseFloat($(data[r].discount_amount).data('orig-value')) : 0;
                        footer_total_campaign_discount += $(data[r].line_discount_amount).data(
                            'orig-value') ? parseFloat($(data[r].line_discount_amount).data(
                            'orig-value')) : 0;

                        // Count payment methods and calculate subtotal based on payment methods
                        var paymentMethods = $(data[r].payment_methods).text().split(',');
                        for (var i = 0; i < paymentMethods.length; i++) {
                            var paymentMethod = paymentMethods[i].trim();
                            if (!paymentMethodTotals.hasOwnProperty(paymentMethod)) {
                                paymentMethodTotals[paymentMethod] = {
                                    count: 0,
                                    subtotal: 0
                                };
                            }
                            paymentMethodTotals[paymentMethod].count++;
                            paymentMethodTotals[paymentMethod].subtotal += parseFloat($(data[r]
                                .final_total).data('orig-value'));
                        }
                    }

                    // Render payment method counts and subtotals
                    var paymentMethodHtml = '';
                    for (var method in paymentMethodTotals) {
                        paymentMethodHtml += method + '(' + paymentMethodTotals[method].count + ')-' +
                            __currency_trans_from_en(paymentMethodTotals[method].subtotal) + '<br>';
                    }

                    // Update footer elements with totals and payment method information
                    $('.footer_total_campaign_discount').html(__currency_trans_from_en(
                        footer_total_campaign_discount));
                    $('.footer_total_special_discount').html(__currency_trans_from_en(
                        footer_total_special_discount));
                    $('.footer_total_sell_return_due').html(__currency_trans_from_en(
                        footer_total_sell_return_due));
                    $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
                    $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
                    $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total));
                    $('.footer_payment_status_count').html(__count_status(data, 'payment_status'));
                    $('.service_type_count').html(__count_status(data, 'types_of_service_name'));
                    $('.payment_method_count').html(paymentMethodHtml);
                },

                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(6)').attr('class', 'clickable_td');
                }
            });

            $(document).on('change',
                '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status, #sell_list_filter_source, #sell_list_filter_payment_method, #sell_list_filter_discount_type, #shipping_status, #sub_type',
                function() {
                    clinic_sell_table.ajax.reload();
                    today_payment_table.ajax.reload();
                });

            $('#only_subscriptions').on('ifChanged', function(event) {
                clinic_sell_table.ajax.reload();
            });
            $('#delivery_sales').on('ifChanged', function(event) {
                clinic_sell_table.ajax.reload();
            });

            // Event handler for the due filter
            $('#due_filter').on('click', function() {
                // Set payment status filter to 'due'
                $('#sell_list_filter_payment_status').val('due').trigger('change');

                // Reload the DataTable
                clinic_sell_table.ajax.reload();
                today_payment_table.draw();
            });
        });

        $(document).ready(function() {
            var today_sell_table = $('#today_sell_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [0, 'desc']
                ], // Sort by date
                ajax: {
                    url: '/clinic/reports/product-sell-grouped-report',
                    data: function(d) {
                        var today = new Date();
                        var day = String(today.getDate()).padStart(2, '0');
                        var month = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
                        var year = today.getFullYear();
                        var todayDate = year + '-' + month + '-' + day;
                        d.variation_id = $('#variation_id').val();
                        d.customer_id = $('select#customer_id').val();
                        d.customer_group_id = $('#psr_customer_group_id').val();
                        d.type = $('#product_list_filter_type').val();
                        d.category_id = $('#product_list_filter_category_id').val();
                        d.unit_id = $('#product_list_filter_unit_id').val();
                        d.tax_id = $('#product_list_filter_tax_id').val();
                        d.active_state = $('#active_state').val();
                        d.selling_state = $('#selling_state').val();
                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.transaction_date = todayDate; // Add transaction_date filter
                        d.stock_status = $('#product_list_filter_stock_status').val();
                        d.product_sub_type = $('#sub_type').val();
                    },
                },

                columns: [{
                        data: 'product_name',
                        name: 'p.name'
                    },
                    {
                        data: 'product_variation',
                        name: 'pv.name',
                        render: function(data, type, row) {
                            if (data === 'DUMMY') {
                                return '';
                            }
                            return data;
                        }
                    },

                    {
                        data: 'sub_sku',
                        name: 'v.sub_sku'
                    },
                    {
                        data: 'product_type',
                        name: 'p.product_type',
                        render: function(data, type, row) {
                            if (data) {
                                return data.charAt(0).toUpperCase() + data.slice(1);
                            }
                            return data;
                        },
                        orderable: true,
                    },
                    {
                        data: 'category_name',
                        name: 'cat.name'
                    },
                    {
                        data: 'brand_name',
                        name: 'b.name'
                    },
                    {
                        data: 'total_qty_sold',
                        render: function(data, type, row) {
                            if (data === null || data == 0.00) {
                                return ''; // Return empty string if null or 0.00
                            }
                            return parseFloat(data).toFixed(
                                2); // Format to 2 decimal places if not null or 0.00
                        },
                        searchable: false,
                    },
                    {
                        data: 'total_qty_sold_modifier',
                        render: function(data, type, row) {
                            if (data === null || data == 0.00) {
                                return ''; // Return empty string if null or 0.00
                            }
                            return parseFloat(data).toFixed(
                                2); // Format to 2 decimal places if not null or 0.00
                        },
                        searchable: false,
                    },
                    {
                        data: 'subtotal',
                        name: 'subtotal',
                        searchable: false
                    },
                ],
                fnDrawCallback: function(oSettings) {
                    let api = this.api();

                    // Calculate the total quantity sold
                    let totalQtySold = api.column(6, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                        if (!isNaN(numericValueB)) {
                            return parseFloat(a) + numericValueB;
                        }
                        return parseFloat(
                            a);
                    }, 0);
                    let totalQtySoldModifier = api.column(7, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                        if (!isNaN(numericValueB)) {
                            return parseFloat(a) + numericValueB;
                        }
                        return parseFloat(
                            a);
                    }, 0);

                    // Calculate the total sold subtotal
                    let totalSubtotal = api.column(8, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                        return parseFloat(a) + numericValueB;
                    }, 0);

                    // Update the footer with the totals
                    $('#footer_today_subtotal').text(totalSubtotal.toFixed(2));
                    $('.footer_today_total_qty_sold').text(totalQtySold.toFixed(2));
                    $('.footer_today_total_qty_sold_modifier').text(totalQtySoldModifier.toFixed(2));

                    __currency_convert_recursively($('#today_sell_table'));
                },
                buttons: [{
                        extend: 'csv',
                        text: '<i class="fa fa-file-csv" aria-hidden="true"></i> ' + LANG.export_to_csv,
                        className: 'btn-sm',
                        exportOptions: {
                            columns: ':visible',
                        },
                        footer: true,
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fa fa-file-excel" aria-hidden="true"></i> ' + LANG
                            .export_to_excel,
                        className: 'btn-sm',
                        exportOptions: {
                            columns: ':visible',
                        },
                        footer: true,
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print" aria-hidden="true"></i> ' + LANG.print,
                        className: 'btn-sm',
                        exportOptions: {
                            columns: ':visible',
                            stripHtml: true,
                        },
                        footer: true,
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fa fa-columns" aria-hidden="true"></i> ' + LANG.col_vis,
                        className: 'btn-sm',
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fa fa-file-pdf" aria-hidden="true"></i> ' + LANG.export_to_pdf,
                        className: 'btn-sm',
                        exportOptions: {
                            columns: ':visible',
                        },
                        footer: true,
                    },
                    {
                        extend: 'print',
                        text: '<i class="fa fa-print" aria-hidden="true"></i> ' + 'Custom Print',
                        className: 'btn-sm',
                        action: function(e, dt, button, config) {
                            var oldStart = dt.settings()[0]
                                ._iDisplayStart; // Get the current start position
                            dt.page.len(-1).draw().one('draw', function() {
                                $.fn.dataTable.ext.buttons.print.action.call(this, e, dt,
                                    button, config);
                                dt.page.len(25).draw().one('draw', function() {
                                    dt.settings()[0]._iDisplayStart =
                                        oldStart; // Restore the start position
                                    dt.draw(false);
                                });
                            });
                        },
                        exportOptions: {
                            columns: [0, 2, 3,
                                4
                            ], // Columns: product name, category, brand, total quantity sold, subtotal
                            format: {
                                body: function(data, row, column, node) {
                                    return data;
                                }
                            },
                            customize: function(win) {
                                if ($('.print_table_part').length > 0) {
                                    $($('.print_table_part').html()).insertBefore(
                                        $(win.document.body).find('table')
                                    );
                                }
                                if ($(win.document.body).find('table.hide-footer').length) {
                                    $(win.document.body).find('table.hide-footer tfoot').remove();
                                }
                                __currency_convert_recursively($(win.document.body).find('table'));
                            },
                        },
                        customize: function(win) {
                            var data = today_sell_table.rows({
                                search: 'applied'
                            }).data().toArray();
                            data.sort(function(a, b) {
                                if (a.category_name < b.category_name) return -1;
                                if (a.category_name > b.category_name) return 1;
                                if (a.brand_name < b.brand_name) return -1;
                                if (a.brand_name > b.brand_name) return 1;
                                return 0;
                            });

                            var body = $(win.document.body).find('table tbody');
                            body.empty();
                            data.forEach(function(row) {
                                var tr = $('<tr></tr>');
                                var categoryName = row.category_name.length > 8 ?
                                    row.category_name.substring(0, 6) + '..' + row
                                    .category_name.slice(-2) :
                                    row.category_name;
                                var brandName = row.brand_name.length > 6 ? row.brand_name
                                    .substring(0, 6) + '..' : row.brand_name;
                                tr.append('<td style="padding: 2px; margin: 2px;">' + row
                                    .product_name + '</td>');
                                tr.append('<td style="padding: 2px; margin: 2px;">' +
                                    categoryName + '</td>');
                                tr.append('<td style="padding: 2px; margin: 2px;">' +
                                    brandName + '</td>');
                                tr.append('<td style="padding: 2px; margin: 2px;">' + row
                                    .current_stock + '</td>');
                                body.append(tr);
                            });
                            $(win.document.body).find('table')
                                .addClass('compact')
                                .css({
                                    'font-size': '10px',
                                    'margin-left': '0.5px',
                                    'margin-right': '3px',
                                    'padding-left': '0px',
                                    'padding-right': '2px'
                                });
                            $(win.document.body).css({
                                'margin-left': '0.5px',
                                'margin-right': '3px',
                                'padding-left': '0px',
                                'padding-right': '2px'
                            });

                            $(win.document.body).find('table').parent()
                                .css({
                                    'margin-left': '2px',
                                    'margin-right': '3px',
                                    'padding-left': '2px',
                                    'padding-right': '3px'
                                });

                            // Hide default title if present
                            $(win.document.body).find('h1').first().hide();

                            // Insert custom title
                            var customTitle =
                                '<h1 style="font-size: 16px; text-align: center;margin-top:0px;padding-top:0px;">AWC Today sell details</h1>';
                            $(win.document.body).prepend(customTitle);
                        }
                    }
                ]
            });





            $('#product_list_filter_category_id, #product_list_filter_type, #product_list_filter_unit_id, #product_list_filter_tax_id, #active_state, #location_id, #product_list_filter_stock_status, #selling_state, #sub_type')
                .change(function() {
                    today_sell_table.draw();

                });

        });

        $(document).on('change', '#sub_type', function() {
            var sub_type = $(this).val();
            if (!sub_type) {
                sub_type = 'all';
            }
            $.ajax({
                url: '/get/billing/sub_type/category/' + sub_type,
                type: 'GET',
                success: function(response) {
                    var categoryDropdown = $(
                        '#product_list_filter_category_id');
                    categoryDropdown.empty();

                    $.each(response.data, function(index, category) {
                        categoryDropdown.append('<option value="' + category.id + '">' +
                            category.name + '</option>');
                    });
                }
            })
        })
        $(document).on('click', '.sell-return-btn', function(e) {
            e.preventDefault(); // Prevent the default action of the link

            const isDirectSale = $(this).data('is_direct_sale'); // Get the is_direct_sale value
            const register = $('#register_report').data('register'); // Get the register value

            if (isDirectSale == 0 && !register) {
                swal({
                    title: "No Bill Return can be processed as the register is closed.",
                    icon: "warning",
                    dangerMode: true,
                });
            } else {
                // Proceed with the normal link action if conditions are not met
                window.location.href = $(this).attr('href');
            }
        });
    </script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
