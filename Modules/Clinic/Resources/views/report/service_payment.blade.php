@extends('clinic::layouts.app2')
@section('title', __('Service Payment Report'))
@section('content')
    <div class="container-fluid">
        <div class="row">

            @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-primary'])
                <div class="col-md-3">
                    {!! Form::label('product_type', 'Service Type') !!}
                    {!! Form::select('sub_type', $types, null, [
                        'class' => 'form-control select2',
                        'style' => 'width: 100%;',
                        'id' => 'sub_type',
                        'multiple',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('service_list_filter_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('service_list_filter_date_range', null, [
                            'placeholder' => __('lang_v1.select_a_date_range'),
                            'class' => 'form-control',
                            'readonly',
                        ]) !!}
                    </div>
                </div>
            @endcomponent
        </div>


        <div class="row">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="col">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active">
                                <a href="#all_sell_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                        aria-hidden="true"></i> All Pay Report</a>
                            </li>
                            <li>
                                <a href="#test_by_category_report_tab" data-toggle="tab" aria-expanded="true"><i
                                        class="fas fa-calendar-check"></i> Test By Category</a>
                            </li>
                           
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane active" id="all_sell_list_tab">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="service_payment_report_table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Price</th>
                                                <th>Sale Amount</th>
                                                <th>Discount</th>
                                                <th>Total Sell Quantity</th>
                                                <th>Service Type</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="test_by_category_report_tab">
                                <table class="table table-bordered table-striped" id="test_sell_report_by_category"
                                    style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>@lang('category.category')</th>
                                            <th>@lang('report.current_stock')</th>
                                            <th>@lang('report.total_unit_sold')</th>
                                            <th>@lang('sale.total')</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr class="bg-gray font-17 footer-total text-center">
                                            <td><strong>@lang('sale.total'):</strong></td>
                                            <td id="footer_psr_by_cat_total_stock"></td>
                                            <td id="footer_psr_by_cat_total_sold"></td>
                                            <td><span class="display_currency" id="footer_psr_by_cat_total_sell"
                                                    data-currency_symbol ="true"></span></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            
                        </div>
                    </div>

                </div>
            @endcomponent

        </div>
        <div class="modal fade therapy_selection_report_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
    </div>

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var dateRangeSettings = {
                ranges: {
                    'Today': [moment().startOf('day'), moment().endOf('day')], // Today's date range
                    'Last 7 Days': [moment().subtract(6, 'days'), moment()], // Last 7 days
                    'Last 14 Days': [moment().subtract(13, 'days'), moment()], // Last 14 days
                    'Last 30 Days': [moment().subtract(29, 'days'), moment()], // Last 30 days
                    'This Month': [moment().startOf('month'), moment().endOf('month')], // Current month
                    'Last Month': [moment().subtract(1, 'months').startOf('month'), moment().subtract(1,
                        'months').endOf('month')], // Last month
                },
                startDate: moment().startOf('day'), // Default start date (today)
                endDate: moment().endOf('day'), // Default end date (today)
                locale: {
                    format: 'YYYY-MM-DD' // Date format (customize if needed)
                }
            };

            $('#service_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#service_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end
                        .format(moment_date_format));
                    $("#service_payment_report_table").DataTable().ajax.reload();
                    $('#test_sell_report_by_category').DataTable().ajax.reload();
                    $('#therapy_selection_report_table').DataTable().ajax.reload();
                }
            );

            $('#service_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#service_list_filter_date_range').val('');
                $("#service_payment_report_table").DataTable().ajax.reload();
            });
            var service_payment_report_table = $('#service_payment_report_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('service-report') }}',
                    data: function(d) {
                        d.sub_type = $('#sub_type').val();
                        if ($('#service_list_filter_date_range').val()) {
                            var start = $('#service_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#service_list_filter_date_range').data('daterangepicker')
                                .endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'product_name',
                        name: 'p.name'
                    },
                    {
                        data: 'price',
                        name: 'v.sell_price_inc_tax',
                        searchable: false
                    },
                    {
                        data: 'sale_amount',
                        name: 'tp.total_payment', // Match the subquery column
                        searchable: false // Disable search for aggregated values
                    },
                    {
                        data: 'total_discount',
                        searchable: false // Disable search for calculated fields
                    },
                    {
                        data: 'total_qty_sold',
                        searchable: false // Disable search for calculated fields
                    },
                    {
                        data: 'transaction_sub_type',
                        name: 't.sub_type' // Match the database column name
                    }
                ],
                order: [
                    [5, 'desc']
                ],
                createdRow: function(row, data, dataIndex) {
                    if (data.row_class) {
                        $(row).addClass(data.row_class);
                    }
                    if (data.data_href) {
                        $(row).attr('data-href', data.data_href);
                        $(row).attr('data-container', '.therapy_selection_report_modal');
                    }
                }

            });
            var test_sell_report_by_category_datatable = $('#test_sell_report_by_category').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ url('test/sell/report/by/category') }}',
                    data: function(d) {
                        if ($('#service_list_filter_date_range').val()) {
                            var start = $('#service_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#service_list_filter_date_range').data('daterangepicker')
                                .endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                    },
                },
                columns: [{
                        data: 'category_name',
                        name: 'cat.name'
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
                    $('#footer_psr_by_cat_total_sell').text(
                        sum_table_col($('#test_sell_report_by_category'), 'row_subtotal')
                    );
                    $('#footer_psr_by_cat_total_sold').text(
                        sum_table_col($('#test_sell_report_by_category'), 'row_qty_sold')
                    );


                    __currency_convert_recursively($('#test_sell_report_by_category'));
                },
            });
          
            $('#sub_type').on('change', function() {
                service_payment_report_table.ajax.reload();

            });

        });
    </script>
@endsection
