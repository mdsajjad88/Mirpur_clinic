@extends('clinic::layouts.app2')
@section('title', __('clinic::lang.consultation_list'))
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('clinic::lang.consultation_list')
            <small>@lang('clinic::lang.manage_consultation')</small>
        </h1>
        <!-- <ol class="breadcrumb">
                <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                <li class="active">Here</li>
            </ol> -->
    </section>
    @if (session('status'))
        <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
            data-msg="{{ session('status.msg') }}">
    @endif
    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            @include('clinic::test.partials.test_filters')
            <div class="col-md-3">
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <br>
                          {!! Form::checkbox('show_with_modifier', 1, false, 
                          [ 'class' => 'input-icheck', 'id' => 'show_with_modifier'])  !!} {{ __('clinic::lang.show_with_modifier') }}
                        </label>
                    </div>
                </div>
            </div>
        @endcomponent
        @can('clinic.test.view')
            <div class="row">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#product_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                        aria-hidden="true"></i> @lang('clinic::lang.all_consultation')</a>
                            </li>
                            <li>
                                <a href="#product_sell_tab" data-toggle="tab" aria-expanded="true"><i
                                        class="fas fa-calendar-check"></i> @lang('clinic::lang.today_consultation_details')</a>
                            </li>

                            <li>
                                <a href="#refund_sell_tab" data-toggle="tab" aria-expanded="true"><i
                                        class="fas fa-calendar-check"></i> @lang('clinic::lang.today_consultation_return')</a>
                            </li>

                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="product_list_tab">
                                @if ($is_admin)
                                    <a class="btn btn-success pull-right margin-left-10"
                                        href="{{ action([\App\Http\Controllers\ProductController::class, 'downloadExcel']) }}"><i
                                            class="fa fa-download"></i> @lang('lang_v1.download_excel')</a>
                                @endif
                                @can('doctor.consultation.create')
                                    <a class="btn btn-primary pull-right"
                                        href="{{ action([\Modules\Clinic\Http\Controllers\DoctorConsultationController::class, 'create']) }}">
                                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                                    <br><br>
                                @endcan
                                @include('clinic::consultation.consultation-table')
                            </div>
                            <div class="tab-pane" id="product_sell_tab">
                                @include('clinic::product.product_sell_report')
                            </div>

                            <div class="tab-pane" id="refund_sell_tab">
                                @include('clinic::consultation.bill-return-table')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
        <input type="hidden" id="is_rack_enabled" value="{{ $rack_enabled }}">

        <div class="modal fade product_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="view_product_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>

        <div class="modal fade" id="opening_stock_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        @if ($is_woocommerce)
            @include('product.partials.toggle_woocommerce_sync_modal')
        @endif
        @include('product.partials.edit_product_location_modal')

        <input type="hidden" id="stock_expiry_alert_days"
            value="{{ \Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d') }}">
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#active_state').val($('#active_state').val() || 'active');
            product_table = $('#product_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [3, 'asc']
                ],
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                "ajax": {
                    "url": "/doctor-consultation",
                    "data": function(d) {
                        d.type = $('#product_list_filter_type').val();
                        d.category_id = $('#product_list_filter_category_id').val();
                        d.active_state = $('#active_state').val();
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.location_id = $('#location_id').val();
                        if ($('#repair_model_id').length == 1) {
                            d.repair_model_id = $('#repair_model_id').val();
                        }

                        if ($('#woocommerce_enabled').length == 1 && $('#woocommerce_enabled').is(
                                ':checked')) {
                            d.woocommerce_enabled = 1;
                            
                        }
                        if ($('#show_with_modifier').is(':checked')) {
                            d.show_with_modifier = 1;
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },
                columnDefs: [{
                    "targets": [0, 1],
                    "orderable": false,
                    "searchable": false
                }],
                columns: [{
                        data: 'mass_delete'
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
                    
                    // {
                    //     data: 'type',
                    //     name: 'products.type'
                    // },
                    {
                        data: 'category',
                        name: 'c1.name'
                    },
                    // {
                    //     data: 'brand',
                    //     name: 'brands.name',
                    // },
                   
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
            $(document).on('ifChanged', '#show_with_modifier', function(){
                product_table.ajax.reload();
            })
            $(document).ready(function() {
                var url = "{{ route('get.today.therapy.sell.report') }}?sub_type=consultation";

                var consultation_sell_table = $('#product_sell_table').DataTable({
                    processing: true,
                    serverSide: true,
                    aaSorting: [
                        [0, 'desc']
                    ], // Sort by date

                    ajax: {
                        url: url,
                        data: function(d) {
                            var dateRange = $('#sell_list_filter_date_range').val();
                            var hasPermission =
                                {{ auth()->user()->can('today_test_sell.apply.date.range.filter') ? 'true' : 'false' }};
                            var today = new Date();
                            var day = String(today.getDate()).padStart(2, '0');
                            var month = String(today.getMonth() + 1).padStart(2, '0');
                            var year = today.getFullYear();
                            var todayDate = year + '-' + month + '-' + day;

                            if (hasPermission && dateRange) {
                                var dates = dateRange.split(' ~ ');
                                d.start_date = dates[0];
                                d.end_date = dates[1];
                            } else {
                                // No permission or no date selected → only today
                                d.start_date = todayDate;
                                d.end_date = todayDate;
                            }

                            d.variation_id = $('#variation_id').val();
                            d.customer_id = $('select#customer_id').val();
                            d.customer_group_id = $('#psr_customer_group_id').val();
                            d.type = $('#product_list_filter_type').val();
                            d.category_id = $('#product_list_filter_category_id').val();
                            d.brand_id = $('#product_list_filter_brand_id').val();
                            d.active_state = $('#active_state').val();
                            d.selling_state = $('#selling_state').val();
                            d.location_id = $('#location_id').val();
                            $('.service_t_or_c').text('Consultation Name');

                        },
                    },
                    columns: [{
                            data: 'product_name',
                            name: 'p.name'
                        },
                        {
                            data: 'variation_name',
                            name: 'pv.name',
                            orderable: true,
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

                        let totalQtySold = api.column(5, {
                            page: 'current'
                        }).data().reduce(function(a, b) {
                            let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                            if (!isNaN(numericValueB)) {
                                return parseFloat(a) + numericValueB;
                            }
                            return parseFloat(
                                a);
                        }, 0);
                        let totalQtySoldModifier = api.column(6, {
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
                        let totalSubtotal = api.column(7, {
                            page: 'current'
                        }).data().reduce(function(a, b) {
                            let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                            return parseFloat(a) + numericValueB;
                        }, 0);

                        // Update the footer with the totals
                        $('#footer_today_subtotal').text(totalSubtotal.toFixed(2));
                        $('#footer_today_total_qty').text(totalQtySold.toFixed(2));
                        $('#footer_today_total_modifier_qty').text(totalQtySoldModifier.toFixed(2));

                        __currency_convert_recursively($('#product_sell_table'));
                    },
                    buttons: [{
                            extend: 'csv',
                            text: '<i class="fa fa-file-csv" aria-hidden="true"></i> ' + LANG
                                .export_to_csv,
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
                            text: '<i class="fa fa-print" aria-hidden="true"></i> ' + LANG
                                .print,
                            className: 'btn-sm',
                            exportOptions: {
                                columns: ':visible',
                                stripHtml: true,
                            },
                            footer: true,
                        },
                        {
                            extend: 'colvis',
                            text: '<i class="fa fa-columns" aria-hidden="true"></i> ' + LANG
                                .col_vis,
                            className: 'btn-sm',
                        },
                        {
                            extend: 'pdf',
                            text: '<i class="fa fa-file-pdf" aria-hidden="true"></i> ' + LANG
                                .export_to_pdf,
                            className: 'btn-sm',
                            exportOptions: {
                                columns: ':visible',
                            },
                            footer: true,
                        },
                        {
                            extend: 'print',
                            text: '<i class="fa fa-print" aria-hidden="true"></i> ' +
                                'Custom Print',
                            className: 'btn-sm',
                            action: function(e, dt, button, config) {
                                var oldStart = dt.settings()[0]
                                    ._iDisplayStart; // Get the current start position
                                dt.page.len(-1).draw().one('draw', function() {
                                    $.fn.dataTable.ext.buttons.print.action.call(
                                        this, e, dt, button, config);
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
                                    if ($(win.document.body).find('table.hide-footer')
                                        .length) {
                                        $(win.document.body).find('table.hide-footer tfoot')
                                            .remove();
                                    }
                                    __currency_convert_recursively($(win.document.body)
                                        .find('table'));
                                },
                            },
                            customize: function(win) {
                                var data = consultation_sell_table.rows({
                                    search: 'applied'
                                }).data().toArray();
                                data.sort(function(a, b) {
                                    if (a.category_name < b.category_name) return -
                                        1;
                                    if (a.category_name > b.category_name) return 1;
                                    if (a.brand_name < b.brand_name) return -1;
                                    if (a.brand_name > b.brand_name) return 1;
                                    return 0;
                                });

                                var body = $(win.document.body).find('table tbody');
                                body.empty();
                                data.forEach(function(row) {
                                    var tr = $('<tr></tr>');
                                    var categoryName = row.category_name.length >
                                        8 ?
                                        row.category_name.substring(0, 6) + '..' +
                                        row.category_name.slice(-2) :
                                        row.category_name;
                                    var brandName = row.brand_name.length > 6 ? row
                                        .brand_name.substring(0, 6) + '..' : row
                                        .brand_name;
                                    tr.append(
                                        '<td style="padding: 2px; margin: 2px;">' +
                                        row.product_name + '</td>');
                                    tr.append(
                                        '<td style="padding: 2px; margin: 2px;">' +
                                        categoryName + '</td>');
                                    tr.append(
                                        '<td style="padding: 2px; margin: 2px;">' +
                                        brandName + '</td>');
                                    tr.append(
                                        '<td style="padding: 2px; margin: 2px;">' +
                                        row.current_stock + '</td>');
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
                $('#product_list_filter_category_id, #product_list_filter_type, #active_state, #location_id, #selling_state, #product_list_filter_brand_id')
                    .change(function() {
                        consultation_sell_table.draw();
                    });
            });


            $(document).on('click', 'a.delete_sell_return', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        var data = $(this).serialize();

                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    sell_return_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });

            var detailRows = [];

            $('#product_table tbody').on('click', 'tr i.rack-details', function() {
                var i = $(this);
                var tr = $(this).closest('tr');
                var row = product_table.row(tr);
                var idx = $.inArray(tr.attr('id'), detailRows);

                if (row.child.isShown()) {
                    i.addClass('fa-plus-circle text-success');
                    i.removeClass('fa-minus-circle text-danger');

                    row.child.hide();

                    // Remove from the 'open' array
                    detailRows.splice(idx, 1);
                } else {
                    i.removeClass('fa-plus-circle text-success');
                    i.addClass('fa-minus-circle text-danger');

                    row.child(get_product_details(row.data())).show();

                    // Add to the 'open' array
                    if (idx === -1) {
                        detailRows.push(tr.attr('id'));
                    }
                }
            });

            $('#opening_stock_modal').on('hidden.bs.modal', function(e) {
                product_table.ajax.reload();
                product_sell_table.ajax.reload();
                sell_return_table.ajax.reload();
            });

            $('table#product_table tbody').on('click', 'a.delete-product', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        $.ajax({
                            method: "DELETE",
                            url: href,
                            dataType: "json",
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    product_table.ajax.reload();
                                    product_sell_table.ajax.reload();
                                    sell_return_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            }
                        });
                    }
                });
            });

            $(document).on('click', '#delete-selected', function(e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_rows').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('form#mass_delete_form').submit();
                        }
                    });
                } else {
                    $('input#selected_rows').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            });

            $(document).on('click', '#deactivate-selected', function(e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_products').val(selected_rows);
                    swal({
                        title: LANG.sure,
                        icon: "warning",
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            var form = $('form#mass_deactivate_form')

                            var data = form.serialize();
                            $.ajax({
                                method: form.attr('method'),
                                url: form.attr('action'),
                                dataType: 'json',
                                data: data,
                                success: function(result) {
                                    if (result.success == true) {
                                        toastr.success(result.msg);
                                        product_table.ajax.reload();
                                        product_sell_table.ajax.reload();
                                        sell_return_table.ajax.reload();
                                        form
                                            .find('#selected_products')
                                            .val('');
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                },
                            });
                        }
                    });
                } else {
                    $('input#selected_products').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            })

            $(document).on('click', '#edit-selected', function(e) {
                e.preventDefault();
                var selected_rows = getSelectedRows();

                if (selected_rows.length > 0) {
                    $('input#selected_products_for_edit').val(selected_rows);
                    $('form#bulk_edit_form').submit();
                } else {
                    $('input#selected_products').val('');
                    swal('@lang('lang_v1.no_row_selected')');
                }
            })

            $('table#product_table tbody').on('click', 'a.activate-product', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                $.ajax({
                    method: "get",
                    url: href,
                    dataType: "json",
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            product_table.ajax.reload();
                            product_sell_table.ajax.reload();
                            sell_return_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $(document).on('change',
                '#product_list_filter_type, #product_list_filter_category_id, #location_id, #active_state, #repair_model_id, #product_list_filter_brand_id',
                function() {
                    if ($("#product_list_tab").hasClass('active')) {
                        product_table.ajax.reload();
                    }

                    if ($("#product_sell_tab").hasClass('active')) {
                        product_sell_table.ajax.reload();
                    }

                });

            $(document).on('ifChanged', '#not_for_selling', function() {
                if ($("#product_list_tab").hasClass('active')) {
                    product_table.ajax.reload();
                }


                if ($("#product_sell_tab").hasClass('active')) {
                    product_sell_table.ajax.reload();
                }

            });

            $('#product_location').select2({
                dropdownParent: $('#product_location').closest('.modal')
            });

        });

        $(document).on('shown.bs.modal', 'div.view_product_modal, div.view_modal, #view_product_modal',
            function() {
                var div = $(this).find('#view_product_stock_details');
                if (div.length) {
                    $.ajax({
                        url: "{{ action([\App\Http\Controllers\ReportController::class, 'getStockReport']) }}" +
                            '?for=view_product&product_id=' + div.data('product_id'),
                        dataType: 'html',
                        success: function(result) {
                            div.html(result);
                            __currency_convert_recursively(div);
                        },
                    });
                }
                __currency_convert_recursively($(this));
            });

        $(document).on('click', '.update_product_location', function(e) {
            e.preventDefault();
            var selected_rows = getSelectedRows();

            if (selected_rows.length > 0) {
                $('input#selected_products').val(selected_rows);
                var type = $(this).data('type');
                var modal = $('#edit_product_location_modal');
                if (type == 'add') {
                    modal.find('.remove_from_location_title').addClass('hide');
                    modal.find('.add_to_location_title').removeClass('hide');
                } else if (type == 'remove') {
                    modal.find('.add_to_location_title').addClass('hide');
                    modal.find('.remove_from_location_title').removeClass('hide');
                }

                modal.modal('show');
                modal.find('#product_location').select2({
                    dropdownParent: modal
                });
                modal.find('#product_location').val('').change();
                modal.find('#update_type').val(type);
                modal.find('#products_to_update_location').val(selected_rows);
            } else {
                $('input#selected_products').val('');
                swal('@lang('lang_v1.no_row_selected')');
            }
        });

        $(document).on('submit', 'form#edit_product_location_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.ajax({
                method: $(this).attr('method'),
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                beforeSend: function(xhr) {
                    __disable_submit_button(form.find('button[type="submit"]'));
                },
                success: function(result) {
                    if (result.success == true) {
                        $('div#edit_product_location_modal').modal('hide');
                        toastr.success(result.msg);
                        product_table.ajax.reload();
                        product_sell_table.ajax.reload();
                        $('form#edit_product_location_form')
                            .find('button[type="submit"]')
                            .attr('disabled', false);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });

        $(document).ready(function() {
            var sell_return_table = $('#sell_return_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [[0, 'desc']],
                "ajax": {
                    "url": "/today-bill-return",
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
                        d.product_type = 'consultation';
                        d.category_id = $('#product_list_filter_category_id').val();
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.unit_id = $('#product_list_filter_unit_id').val();
                        d.tax_id = $('#product_list_filter_tax_id').val();
                        d.active_state = $('#active_state').val();
                        d.selling_state = $('#selling_state').val();
                        d.location_id = $('#location_id').val();
                        d.transaction_date = todayDate; // Add transaction_date filter
                        d.stock_status = $('#product_list_filter_stock_status').val();
                    },
                },
                columnDefs: [{
                    "targets": [5, 6],
                    "orderable": false,
                    "searchable": false
                }],
                columns: [
                    { data: 'product', name: 'product' },
                    { data: 'sku', name: 'sku' },
                    { data: 'category', name: 'category' },
                    { data: 'parent_sale', name: 'T1.invoice_no' },
                    { data: 'payment_status', name: 'payment_status' },
                    { data: 'total_return_qty', name: 'total_return_qty' },
                    { data: 'final_total', name: 'final_total' }
                ],
                "fnDrawCallback": function(oSettings) {
                    var total_sell = sum_table_col($('#sell_return_table'), 'final_total');
                    $('#footer_sell_return_total').text(total_sell);

                    $('#footer_payment_status_count_sr').html(__sum_status_html($('#sell_return_table'), 'payment-status-label'));

                    var total_due = sum_table_col($('#sell_return_table'), 'payment_due');
                    $('#footer_total_due_sr').text(total_due);

                    __currency_convert_recursively($('#sell_return_table'));
                },
                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(2)').attr('class', 'clickable_td');
                }
            });
            // Trigger DataTable reload on filter change
            $('#product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_type, #product_list_filter_unit_id, #product_list_filter_tax_id, #active_state, #location_id, #product_list_filter_stock_status, #selling_state').change(function() {
                sell_return_table.draw();
            });


            var today = moment();

            // Initialize daterangepicker
            $('#sell_list_filter_date_range').daterangepicker(
                $.extend({}, dateRangeSettings, {
                    startDate: today,
                    endDate: today
                }),
                function(start, end) {
                    $('#sell_list_filter_date_range').val(
                        start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                    );
                    $('#product_sell_table').DataTable().ajax.reload();
                }
            );

            // Set default input value = today's date
            $('#sell_list_filter_date_range').val(
                today.format(moment_date_format) + ' ~ ' + today.format(moment_date_format)
            );

            // Handle cancel (clear date)
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                $('#product_sell_table').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
