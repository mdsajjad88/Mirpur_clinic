@extends('clinic::layouts.app2')
@section('title', __('Test Sell Report'))
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
            @include('clinic::test.partials.test_filters')
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                    {!! Form::text('sell_list_filter_date_range', null, [
                        'placeholder' => __('lang_v1.select_a_date_range'),
                        'class' => 'form-control',
                        'readonly',
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
                            <a href="#test_wise_report" data-toggle="tab" aria-expanded="true"><i
                                    class="fas fa-calendar-check"></i>Test Sell Details</a>
                        </li>
                        <li>
                            <a href="#today_sell_tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fas fa-calendar-check"></i> Today Sell Details</a>
                        </li>

                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane active" id="test_wise_report">
                            @include('clinic::report.test_wise_sell_report')
                        </div>
                        <div class="tab-pane" id="today_sell_tab">
                            @include('clinic::sell.today_sell')
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
                if (target === '#today_sell_tab') {
                    $('#today_sell_table').DataTable().ajax.reload();
                }
            });
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var target = $(e.target).attr("href"); // Get the target tab
                if (target === '#test_wise_report') {
                    $('#clinic_test_wise_sell_report').DataTable().ajax.reload();
                }
            });
        // });

        // $(document).ready(function() {
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
                        data: 'product_sub_type',
                        name: 'product_sub_type',
                        render: function(data, type, row) {
                            if (data) {
                                return data.charAt(0).toUpperCase() + data.slice(1);
                            }
                            return data; 
                        }
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
            $('#product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_type, #product_list_filter_unit_id, #product_list_filter_tax_id, #active_state, #location_id, #product_list_filter_stock_status, #selling_state, #sell_list_filter_date_range')
                .change(function() {
                    today_sell_table.draw();
                    test_wise_sell_report.draw();

                });
        // });
        // $(document).ready(function() {
            var test_wise_sell_report = $('#clinic_test_wise_sell_report').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [4, 'desc']
                ], // Sort by date
                ajax: {
                    url: '/test-sell-report',
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
            $('#product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_type, #product_list_filter_unit_id, #product_list_filter_tax_id, #active_state, #location_id, #product_list_filter_stock_status, #selling_state, #sell_list_filter_date_range')
                .change(function() {
                    today_sell_table.draw();
                    test_wise_sell_report.draw();

                });
        // });
        $('#sell_list_filter_date_range').daterangepicker(
            dateRangeSettings,
            function(start, end) {
                $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                    moment_date_format));
                test_wise_sell_report.ajax.reload();
                today_sell_table.ajax.reload();
            }
        );
        $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#sell_list_filter_date_range').val('');
            test_wise_sell_report.ajax.reload();
            today_sell_table.ajax.reload();
        });
        });
    </script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
