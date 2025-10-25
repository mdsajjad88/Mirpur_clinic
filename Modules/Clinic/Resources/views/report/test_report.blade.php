@extends('clinic::layouts.app2')
@section('title', __('Test Report'))
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('sale.sells')
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
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
        @endcomponent
        <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#all_sell_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                    aria-hidden="true"></i> All Test Sells</a>
                        </li>
                        <li>
                            <a href="#today_sell_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                    aria-hidden="true"></i> Today Sells</a>
                        </li>
                        
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="all_sell_list_tab">
                            @include('clinic::report.test_sell')
                        </div>
                        <div class="tab-pane" id="today_sell_list_tab">
                            @include('clinic::report.today_test_sell_invoice_wise');
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade" id="view_product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <input type="hidden" id="stock_expiry_alert_days"
        value="{{ \Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d') }}">
@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).attr("href"); // Get the target tab
                if (target === '#all_sell_list_tab') {
                    $('#clinic_test_sell_report').DataTable().ajax.reload();
                }
            });
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).attr("href"); // Get the target tab
                if (target === '#today_sell_list_tab') {
                    $('#clinic_test_sell_report_today').DataTable().ajax.reload();
                }
            });
        });
        $(document).ready(function() {
            //Date range as a button
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    clinic_test_sell_report.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                clinic_test_sell_report.ajax.reload();
            });

            clinic_test_sell_report = $('#clinic_test_sell_report').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": "/test-report",
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
                        data: 'sub_type',
                        name: 'sub_type'
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
                    __currency_convert_recursively($('#clinic_test_sell_report'));
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
                    clinic_test_sell_report.ajax.reload();
                });

            $('#only_subscriptions').on('ifChanged', function(event) {
                clinic_test_sell_report.ajax.reload();
            });
            $('#delivery_sales').on('ifChanged', function(event) {
                clinic_test_sell_report.ajax.reload();
            });

            // Event handler for the due filter
            $('#due_filter').on('click', function() {
                // Set payment status filter to 'due'
                $('#sell_list_filter_payment_status').val('due').trigger('change');

                // Reload the DataTable
                clinic_test_sell_report.ajax.reload();
            });
    });
    
        $(document).ready(function () {
        // Initialize DataTable with AJAX source and footer callback for totals
        var sellTable = $('#sell_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ url("/sells") }}',
                data: function (d) {
                    d.start_date = $('#daterange_start').val();
                    d.end_date = $('#daterange_end').val();
                    d.customer_id = $('#customer_id').val();
                    d.payment_status = $('#payment_status').val();
                    d.location_id = $('#location_id').val();
                    d.sale_type = $('#sale_type').val();
                }
            },
            columns: [
                { data: 'date', name: 'date' },
                { data: 'invoice_no', name: 'invoice_no' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'payment_status', name: 'payment_status' },
                { data: 'final_total', name: 'final_total' },
                { data: 'total_paid', name: 'total_paid' },
                { data: 'total_remaining', name: 'total_remaining' },
                @if(auth()->user()->can('view_sell_return'))
                    { data: 'sell_return_due', name: 'sell_return_due' },
                @endif
                @if(auth()->user()->can('view_special_discount'))
                    { data: 'special_discount', name: 'special_discount' },
                @endif
                @if(auth()->user()->can('view_campaign_discount'))
                    { data: 'campaign_discount', name: 'campaign_discount' },
                @endif
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();
                
                // Initialize total variables
                var footer_sale_total = 0;
                var footer_total_paid = 0;
                var footer_total_remaining = 0;
                var footer_total_sell_return_due = 0;
                var footer_total_special_discount = 0;
                var footer_total_campaign_discount = 0;

                // Loop through data to calculate totals
                data.forEach(function (rowData) {
                    footer_sale_total += parseFloat(rowData.final_total) || 0;
                    footer_total_paid += parseFloat(rowData.total_paid) || 0;
                    footer_total_remaining += parseFloat(rowData.total_remaining) || 0;
                    @if(auth()->user()->can('view_sell_return'))
                        footer_total_sell_return_due += parseFloat(rowData.sell_return_due) || 0;
                    @endif
                    @if(auth()->user()->can('view_special_discount'))
                        footer_total_special_discount += parseFloat(rowData.special_discount) || 0;
                    @endif
                    @if(auth()->user()->can('view_campaign_discount'))
                        footer_total_campaign_discount += parseFloat(rowData.campaign_discount) || 0;
                    @endif
                });

                // Update footer totals
                $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total, true));
                $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid, true));
                $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining, true));
                @if(auth()->user()->can('view_sell_return'))
                    $('.footer_total_sell_return_due').html(__currency_trans_from_en(footer_total_sell_return_due, true));
                @endif
                @if(auth()->user()->can('view_special_discount'))
                    $('.footer_total_special_discount').html(__currency_trans_from_en(footer_total_special_discount, true));
                @endif
                @if(auth()->user()->can('view_campaign_discount'))
                    $('.footer_total_campaign_discount').html(__currency_trans_from_en(footer_total_campaign_discount, true));
                @endif
            }
        });

    // Apply filters and reload DataTable
    $('#filter_button').on('click', function () {
        sellTable.ajax.reload();
    });
});


        $(document).ready(function() {
           
            var today_sell_table = $('#today_sell_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [4, 'desc']
                ], // Sort by date
                ajax: {
                    url: '/clinic/test/today/sell/report',
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
           

            // Trigger DataTable reload on filter change
            $('#product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_type, #product_list_filter_unit_id, #product_list_filter_tax_id, #active_state, #location_id, #product_list_filter_stock_status, #selling_state')
                .change(function() {
                    today_sell_table.draw();
                    test_wise_sell_report.draw();
                   
                });
        });
        $(document).ready(function() {
            var clinic_test_wise_sell_report = $('#clinic_test_wise_sell_report').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [3, 'asc']
                ],
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                "ajax": {
                    "url": "/clinic/test/wise/sell/report",
                    "data": function(d) {
                        d.type = $('#product_list_filter_type').val();
                        d.category_id = $('#product_list_filter_category_id').val();
                        d.active_state = $('#active_state').val();
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.selling_state = $('#selling_state').val();
                        d.location_id = $('#location_id').val();
                        if ($('#repair_model_id').length == 1) {
                            d.repair_model_id = $('#repair_model_id').val();
                        }

                        if ($('#woocommerce_enabled').length == 1 && $('#woocommerce_enabled').is(
                                ':checked')) {
                            d.woocommerce_enabled = 1;
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },
                columnDefs: [{
                    "targets": [0, 1, 2],
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'mass_delete'
                    },
                    {
                        data: 'image',
                        name: 'products.image'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'product',
                        name: 'products.name'
                    },
                    // {
                    //     data: 'product_locations',
                    //     name: 'product_locations'
                    // },
                    @can('view_purchase_price')
                        {
                            data: 'purchase_price',
                            name: 'max_purchase_price',
                            searchable: false
                        },
                    @endcan
                    @can('access_default_selling_price')
                        {
                            data: 'selling_price',
                            name: 'max_price',
                            searchable: false
                        },
                    @endcan
                    @can('view_purchase_price')
                        {
                            data: 'profit_margin',
                            name: 'profit_margin',
                            searchable: false
                        },
                    @endcan {
                        data: 'current_stock',
                        searchable: false
                    },
                    {
                        data: 'type',
                        name: 'products.type'
                    },
                    {
                        data: 'category',
                        name: 'c1.name'
                    },
                    {
                        data: 'tax',
                        name: 'tax_rates.name',
                        searchable: false
                    },
                    {
                        data: 'sku',
                        name: 'products.sku'
                    },
                    {
                        data: 'product_custom_field1',
                        name: 'products.product_custom_field1',
                        visible: $('#cf_1').text().length > 0
                    },
                    {
                        data: 'product_custom_field2',
                        name: 'products.product_custom_field2',
                        visible: $('#cf_2').text().length > 0
                    },
                    {
                        data: 'product_custom_field3',
                        name: 'products.product_custom_field3',
                        visible: $('#cf_3').text().length > 0
                    },
                    {
                        data: 'product_custom_field4',
                        name: 'products.product_custom_field4',
                        visible: $('#cf_4').text().length > 0
                    },
                    {
                        data: 'product_custom_field5',
                        name: 'products.product_custom_field5',
                        visible: $('#cf_5').text().length > 0
                    },
                    {
                        data: 'product_custom_field6',
                        name: 'products.product_custom_field6',
                        visible: $('#cf_6').text().length > 0
                    },
                    {
                        data: 'product_custom_field7',
                        name: 'products.product_custom_field7',
                        visible: $('#cf_7').text().length > 0
                    },
                ],
                createdRow: function(row, data, dataIndex) {
                    if ($('input#is_rack_enabled').val() == 1) {
                        var target_col = 0;
                        @can('product.delete')
                            target_col = 1;
                        @endcan
                        $(row).find('td:eq(' + target_col + ') div').prepend(
                            '<i style="margin:auto;" class="fa fa-plus-circle text-success cursor-pointer no-print rack-details" title="' +
                            LANG.details + '"></i>&nbsp;&nbsp;');
                    }
                    $(row).find('td:eq(0)').attr('class', 'selectable_td');
                },
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#product_table'));
                },
            });
           
            $('#product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_type, #product_list_filter_unit_id, #product_list_filter_tax_id, #active_state, #location_id, #product_list_filter_stock_status, #selling_state')
                .change(function() {
                    clinic_test_wise_sell_report.draw();
                   
                });
        });
        
    </script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
