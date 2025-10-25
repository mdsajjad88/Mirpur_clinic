@extends('clinic::layouts.app2')
@section('title', __('Therapy List'))
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('clinic::lang.therapy_list')
            <small>@lang('clinic::lang.manage_therapy')</small>
        </h1>
        <!-- <ol class="breadcrumb">
                            <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                            <li class="active">Here</li>
                        </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
                <div class="col-md-12">
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('type[]', __('product.product_type') . ':') !!}
                            {!! Form::select(
                                'type[]',
                                ['single' => __('lang_v1.single'), 'variable' => __('lang_v1.variable'), 'combo' => __('lang_v1.combo')],
                                null,
                                [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'id' => 'product_list_filter_type',
                                    'multiple' => 'multiple',
                                ],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('category_id[]', __('product.category') . ':') !!}
                            {!! Form::select('category_id[]', $categories, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'product_list_filter_category_id',
                                'multiple' => 'multiple',
                            ]) !!}
                        </div>
                    </div>
                    <div hidden class="col-md-3" id="location_filter">
                        <div class="form-group">
                            {!! Form::label('location_id', __('purchase.business_location') . ':') !!}
                            {!! Form::select('location_id', $business_locations, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('active_state', __('Product Status') . ':') !!}
                            {!! Form::select(
                                'active_state',
                                ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')],
                                request('active_state', 'active'),
                                [
                                    'class' => 'form-control select2',
                                    'style' => 'width:100%',
                                    'id' => 'active_state',
                                    'placeholder' => __('lang_v1.all'),
                                ],
                            ) !!}
                        </div>
                    </div>
                    {{-- <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('brand_id[]', __('clinic::lang.brand') . ':') !!}
                            {!! Form::select('brand_id[]', $brands, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'product_list_filter_brand_id',
                                'multiple' => 'multiple',
                            ]) !!}
                        </div>
                    </div> --}}
                    @if(auth()->user()->can('today_sell.apply.date.range.filter'))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
        {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']) !!}
    </div>
</div>
@endif


                </div>
            </div>
            <div class="row">
                <div class="col-md-12">


                    <!-- include module filter -->
                    @if (!empty($pos_module_data))
                        @foreach ($pos_module_data as $key => $value)
                            @if (!empty($value['view_path']))
                                @includeIf($value['view_path'], ['view_data' => $value['view_data']])
                            @endif
                        @endforeach
                    @endif

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('selling_state', __('Selling Status') . ':') !!}
                            {!! Form::select(
                                'selling_state',
                                ['all' => __('lang_v1.all'), '0' => __('For selling'), '1' => __('lang_v1.not_for_selling')],
                                'all',
                                ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'selling_state'],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                    <br>
                                  {!! Form::checkbox('show_with_modifier', 1, false, 
                                  [ 'class' => 'input-icheck', 'id' => 'show_with_modifier'])  !!} {{ __('Show With Moodifier') }}
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent
        @can('clinic.therapy.view')
            <div class="row">
                <div class="col-md-12">
                    <!-- Custom Tabs -->
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#product_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                        aria-hidden="true"></i> All Therapy</a>
                            </li>
                            <li>
                                <a href="#product_sell_tab" data-toggle="tab" aria-expanded="true"><i
                                        class="fas fa-calendar-check"></i> Today Sell Details</a>
                            </li>
                            <li>
                                <a href="#refund_sell_tab" data-toggle="tab" aria-expanded="true"><i
                                        class="fas fa-calendar-check"></i> Today Refund Therapy</a>
                            </li>

                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="product_list_tab">
                                @if ($is_admin)
                                    <a class="btn btn-success pull-right margin-left-10"
                                        href="{{ action([\App\Http\Controllers\ProductController::class, 'downloadExcel']) }}"><i
                                            class="fa fa-download"></i> @lang('lang_v1.download_excel')</a>
                                @endif
                                @can('clinic.therapy.create')
                                    <a class="btn btn-primary pull-right"
                                        href="{{ action([\Modules\Clinic\Http\Controllers\TherapyController::class, 'create']) }}">
                                        <i class="fa fa-plus"></i> @lang('messages.add')</a>
                                    <br><br>
                                @endcan
                                @include('clinic::therapy.partials.product_list')
                            </div>
                            <div class="tab-pane" id="product_sell_tab">
                                @include('clinic::therapy.therapy_sell_report')
                            </div>
                            <div class="tab-pane" id="refund_sell_tab">
                                @include('clinic::therapy.bill-return-table')
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
                    "url": "/clinic-therapy",
                    "data": function(d) {
                        d.type = $('#product_list_filter_type').val();
                        d.category_id = $('#product_list_filter_category_id').val();
                        d.brand_id = $('#product_list_filter_brand_id').val();
                        d.active_state = $('#active_state').val();
                        d.selling_state = $('#selling_state').val();
                        d.location_id = $('#location_id').val();
                        if ($('#repair_model_id').length == 1) {
                            d.repair_model_id = $('#repair_model_id').val();
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
                    "searchable": false,
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
                    // { data: 'brand', name: 'brands.name'},
                    {
                        data: 'sku',
                        name: 'products.sku'
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

            $(document).ready(function() {
                var url = "{{ route('get.today.therapy.sell.report') }}?sub_type=therapy";

                var therapy_sell_table = $('#therapy_sell_table').DataTable({
                    processing: true,
                    serverSide: true,
                    aaSorting: [
                        [4, 'desc']
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
                            $('.service_t_or_c').text('Therapy Name');

                        },
                    },
                    columnDefs: [{
                        "targets": [0, ],
                        "orderable": false,
                        "searchable": false,
                    }],
                    columns: [{
                            data: 'product_name',
                            name: 'p.name',
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
                                    return ''; 
                                }
                                return parseFloat(data).toFixed(
                                    2);
                            },
                            searchable: false,
                        },
                        {
                            data: 'total_qty_sold_modifier',
                            render: function(data, type, row) {
                                if (data === null || data == 0.00) {
                                    return '';
                                }
                                return parseFloat(data).toFixed(
                                    2);
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

                        __currency_convert_recursively($('#therapy_sell_table'));
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
                                var data = therapy_sell_table.rows({
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
                        therapy_sell_table.draw();
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
                therapy_sell_table.ajax.reload();
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
                                    therapy_sell_table.ajax.reload();
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
                                        therapy_sell_table.ajax.reload();
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
                            therapy_sell_table.ajax.reload();
                            sell_return_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            });

            $(document).on('change',
                '#product_list_filter_type, #product_list_filter_category_id, #location_id, #active_state, #repair_model_id, #selling_state, #product_list_filter_brand_id',
                function() {
                    if ($("#product_list_tab").hasClass('active')) {
                        product_table.ajax.reload();
                    }

                    if ($("#product_sell_tab").hasClass('active')) {
                        therapy_sell_table.ajax.reload();
                    }

                });

            $(document).on('ifChanged', '#not_for_selling', function() {
                if ($("#product_list_tab").hasClass('active')) {
                    product_table.ajax.reload();
                }



                if ($("#product_sell_tab").hasClass('active')) {
                    therapy_sell_table.ajax.reload();
                }
            });
            $(document).on('ifChanged', '#show_with_modifier', function(){
                product_table.ajax.reload();
            })

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
        var data_table_initailized = false;


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
                        therapy_sell_table.ajax.reload();
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
                        d.product_type = 'therapy';
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
                    $('#therapy_sell_table').DataTable().ajax.reload();
                }
            );

            // Set default input value = today's date
            $('#sell_list_filter_date_range').val(
                today.format(moment_date_format) + ' ~ ' + today.format(moment_date_format)
            );

            // Handle cancel (clear date)
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                $('#therapy_sell_table').DataTable().ajax.reload();
            });
        });
    </script>
@endsection
