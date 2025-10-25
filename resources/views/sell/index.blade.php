@extends('layouts.app')
@section('title', __('lang_v1.all_sales'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('sale.sells')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @isset($RemaininInLast)
            <div class="alert alert-info">
                <span>Your total due {{ $RemaininInLast }} in last 30 days </span> <span
                    style="text-decoration: underline; color:blue; cursor: pointer;" id="due_filter">Please click here for view
                    all due sell</span>
            </div>
        @endisset

        @component('components.filters', ['title' => __('report.filters')])
            @include('sell.partials.sell_list_filters')
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
        @endcomponent
        <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#all_sell_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                    aria-hidden="true"></i> All Sells</a>
                        </li>
                        <li>
                            <a href="#product_sell_tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fas fa-calendar-check"></i> Today Sell Details</a>
                        </li>
                        <li>
                            <a href="#product_return_tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fas fa-undo-alt"></i> Today Return Products</a>
                        </li>
                        @can('stock_report.view')
                            <li>
                                <a href="#product_expiry_tab" data-toggle="tab" aria-expanded="true"><i
                                        class="fa fa-exclamation-triangle text-yellow" aria-hidden="true"></i> Stock Expiry
                                    Alert</a>
                            </li>
                        @endcan
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="all_sell_list_tab">
                            @can('direct_sell.access')
                                <a class="btn btn-primary pull-right"
                                    href="{{ action([\App\Http\Controllers\SellController::class, 'create']) }}">
                                    <i class="fa fa-plus"></i> @lang('messages.add')</a> <br><br>
                            @endcan
                            @include('sell.partials.all_sell_table')
                        </div>
                        <div class="tab-pane" id="product_sell_tab">
                            @include('product.partials.product_sell_report')
                        </div>
                        <div class="tab-pane" id="product_return_tab">
                            @include('product.partials.sell_return')
                        </div>
                        @can('stock_report.view')
                            <div class="tab-pane" id="product_expiry_tab">
                                <a class="btn btn-primary pull-right"
                                    href="{{ action([\App\Http\Controllers\ExpiryDateController::class, 'create']) }}">
                                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                                <br><br>
                                @include('product.partials.product_expiry_alert')
                            </div>
                        @endcan
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

    <!-- This will be printed -->
    <!-- <section class="invoice print_section" id="receipt_section">
    </section> -->

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            //Date range as a button
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    sell_table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                sell_table.ajax.reload();
            });

            sell_table = $('#sell_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": "/sells",
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
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#sell_table'));
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
                '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status, #sell_list_filter_source, #sell_list_filter_payment_method, #sell_list_filter_discount_type',
                function() {
                    sell_table.ajax.reload();
                });

            $('#only_subscriptions').on('ifChanged', function(event) {
                sell_table.ajax.reload();
            });
            $('#delivery_sales').on('ifChanged', function(event) {
                sell_table.ajax.reload();
            });

            // Event handler for the due filter
            $('#due_filter').on('click', function() {
                // Set payment status filter to 'due'
                $('#sell_list_filter_payment_status').val('due').trigger('change');

                // Reload the DataTable
                sell_table.ajax.reload();
            });
        });

        $(document).ready(function() {
            var product_sell_table = $('#product_sell_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [4, 'desc']
                ], // Sort by date
                ajax: {
                    url: '/reports/product-sell-grouped-report',
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
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.unit_id = $('#product_list_filter_unit_id').val();
                        d.tax_id = $('#product_list_filter_tax_id').val();
                        d.active_state = $('#active_state').val();
                        d.selling_state = $('#selling_state').val();
                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.transaction_date = todayDate; // Add transaction_date filter
                        d.stock_status = $('#product_list_filter_stock_status').val();
                    },
                },
                columns: [{
                        data: 'product_name',
                        name: 'p.name'
                    },
                    {
                        data: 'sub_sku',
                        name: 'v.sub_sku'
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
                        data: 'current_stock',
                        name: 'current_stock',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'total_qty_sold',
                        name: 'total_qty_sold',
                        searchable: false
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
                    let totalQtySold = api.column(5, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                        return parseFloat(a) + numericValueB;
                    }, 0);

                    // Calculate the total sold subtotal
                    let totalSubtotal = api.column(6, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                        return parseFloat(a) + numericValueB;
                    }, 0);

                    // Update the footer with the totals
                    $('#footer_today_subtotal').text(totalSubtotal.toFixed(2));

                    __currency_convert_recursively($('#product_sell_table'));
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
                            4], // Columns: product name, category, brand, total quantity sold, subtotal
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
                            var data = product_sell_table.rows({
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

            // Trigger DataTable reload on filter change
            $('#product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_type, #product_list_filter_unit_id, #product_list_filter_tax_id, #active_state, #location_id, #product_list_filter_stock_status, #selling_state')
                .change(function() {
                    product_sell_table.draw();
                });
        });


        $(document).ready(function() {
            var sell_return_table = $('#sell_return_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [0, 'desc']
                ],
                "ajax": {
                    "url": "/today-sell-return",
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
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.unit_id = $('#product_list_filter_unit_id').val();
                        d.tax_id = $('#product_list_filter_tax_id').val();
                        d.active_state = $('#active_state').val();
                        d.selling_state = $('#selling_state').val();
                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.transaction_date = todayDate; // Add transaction_date filter
                        d.stock_status = $('#product_list_filter_stock_status').val();
                    },
                },
                columnDefs: [{
                    "targets": [6, 7],
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'product',
                        name: 'product'
                    },
                    {
                        data: 'sku',
                        name: 'sku'
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'brand',
                        name: 'brand'
                    },
                    {
                        data: 'parent_sale',
                        name: 'T1.invoice_no'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'current_stock',
                        name: 'current_stock'
                    },
                    {
                        data: 'total_return_qty',
                        name: 'total_return_qty'
                    },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    }
                ],
                "fnDrawCallback": function(oSettings) {
                    var total_sell = sum_table_col($('#sell_return_table'), 'final_total');
                    $('#footer_sell_return_total').text(total_sell);

                    $('#footer_payment_status_count_sr').html(__sum_status_html($('#sell_return_table'),
                        'payment-status-label'));

                    var total_due = sum_table_col($('#sell_return_table'), 'payment_due');
                    $('#footer_total_due_sr').text(total_due);

                    __currency_convert_recursively($('#sell_return_table'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(2)').attr('class', 'clickable_td');
                }
            });
            // Trigger DataTable reload on filter change
            $('#product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_type, #product_list_filter_unit_id, #product_list_filter_tax_id, #active_state, #location_id, #product_list_filter_stock_status, #selling_state')
                .change(function() {
                    sell_return_table.draw();
                });
        });

        $(document).ready(function() {
            //Stock expiry report table
            stock_expiry_alert_table = $('#stock_expiry_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/reports/stock-expiry',
                    data: function(d) {
                        d.exp_date_filter = $('#stock_expiry_alert_days').val();
                    },
                },
                order: [
                    [3, 'asc']
                ],
                columns: [{
                        data: 'product',
                        name: 'p.name'
                    },
                    {
                        data: 'location',
                        name: 'l.name'
                    },
                    {
                        data: 'stock_left',
                        name: 'stock_left'
                    },
                    {
                        data: 'lot_number',
                        name: 'lot_number'
                    },
                    {
                        data: 'exp_date',
                        name: 'exp_date'
                    },
                ],
                fnDrawCallback: function(oSettings) {
                    __show_date_diff_for_human($('#stock_expiry_table'));
                    __currency_convert_recursively($('#stock_expiry_table'));
                },
            });
        });
    </script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
