@extends('layouts.app')
@section('title', __('Modifiers Sales Report'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('Modifiers Sales')
        <small>@lang('Reports')</small>
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
        {{-- <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('type[]', __('product.product_type') . ':') !!}
                {!! Form::select('type[]', ['single' => __('lang_v1.single'), 'variable' => __('lang_v1.variable'), 'combo' => __('lang_v1.combo')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_type','multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('category_id[]', __('product.category') . ':') !!}
                {!! Form::select('category_id[]', $categories, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_category_id', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('unit_id[]', __('product.unit') . ':') !!}
                {!! Form::select('unit_id[]', $units, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_unit_id', 'multiple' => 'multiple']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('tax_id[]', __('product.tax') . ':') !!}
                {!! Form::select('tax_id[]', $taxes, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_tax_id', 'multiple' => 'multiple']) !!}
            </div>
        </div> --}}
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        {{-- <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('brand_id[]', __('product.brand') . ':') !!}
                {!! Form::select('brand_id[]', $brands, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_brand_id', 'multiple' => 'multiple']) !!}
            </div>
        </div> --}}
        <div class="col-md-3" id="location_filter">
            <div class="form-group">
                {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        {{-- <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('stock_status', __('Stock Status') . ':') !!}
                {!! Form::select('stock_status', ['in_stock' => __('In stock'), 'out_of_stock' => __('Out of stock')], null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_stock_status', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div> --}}
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('product_sr_date_filter', __('report.date_range') . ':') !!}
                {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'product_sr_date_filter', 'readonly']) !!}
            </div>
        </div>
    </div>
</div>
@endcomponent
@can('product.view')
    <div class="row">
        <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary'])
                <table style="width: 100%" class="table table-bordered table-striped ajax_view hide-footer" id="product_sell_table">
                    <thead>
                        <tr>
                            <th>@lang('sale.product')</th>
                            <th>@lang('product.sku')</th>
                            {{-- <th>Category</th>
                            <th>Brand</th> --}}
                            <th>Date</th>
                            {{-- <th>@lang('report.current_stock')</th> --}}
                            <th>@lang('report.total_unit_sold')</th>
                            <th>@lang('sale.total')</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td colspan="3"><strong>@lang('sale.total'):</strong></td>
                            <td id="footer_total_today_sold"></td>
                            <td><span class="display_currency" id="footer_today_subtotal" data-currency_symbol="true"></span></td>
                        </tr>
                    </tfoot>
                </table>
            @endcomponent
        </div>
    </div>
@endcan
</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
$(document).ready(function() {
    // Initialize daterangepicker
    $('#product_sr_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
        $('#product_sr_date_filter').val(
                start.format('YYYY-MM-DD') + ' ~ ' + end.format('YYYY-MM-DD')
            );
            product_sell_table.ajax.reload();
        });

        $('#product_sr_date_filter').on('cancel.daterangepicker', function(ev, picker) {
            $('#product_sr_date_filter').val('');
            product_sell_table.ajax.reload();
        });

    // DataTable initialization for product sell
    var product_sell_table = $('#product_sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'asc']],
        ajax: {
            url: '/reports/modifiers-sell-report',
            data: function(d) {
                var start = '';
                var end = '';
                var start_time = $('#product_sr_start_time').val();
                var end_time = $('#product_sr_end_time').val();
                if ($('#product_sr_date_filter').val()) {
                    start = $('input#product_sr_date_filter')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD');
                    end = $('input#product_sr_date_filter')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD');

                    start = moment(start + " " + start_time, "YYYY-MM-DD" + " " + moment_time_format).format('YYYY-MM-DD HH:mm');
                    end = moment(end + " " + end_time, "YYYY-MM-DD" + " " + moment_time_format).format('YYYY-MM-DD HH:mm');
                }
                d.start_date = start;
                d.end_date = end;

                d.variation_id = $('#variation_id').val();
                d.customer_id = $('select#customer_id').val();
                d.customer_group_id = $('#psr_customer_group_id').val();
                d.type = $('#product_list_filter_type').val();
                d.category_id = $('#product_list_filter_category_id').val();
                d.brand_id = $('#product_list_filter_brand_id').val();
                d.unit_id = $('#product_list_filter_unit_id').val();
                d.tax_id = $('#product_list_filter_tax_id').val();
                d.active_state = $('#active_state').val();
                d.location_id = $('#location_id').val();
                d.stock_status = $('#product_list_filter_stock_status').val();
            },
        },
        columns: [
            { data: 'product_name', name: 'p.name' },
            { data: 'sub_sku', name: 'v.sub_sku' },
            // { data: 'category_name', name: 'cat.name' },
            // { data: 'brand_name', name: 'b.name' },
            { data: 'transaction_date', name: 'transaction_date', searchable: false, },
            // { data: 'current_stock', name: 'current_stock', searchable: false, orderable: false },
            { data: 'total_qty_sold', name: 'total_qty_sold', searchable: false },
            { data: 'subtotal', name: 'subtotal', searchable: false },
        ],
        fnDrawCallback: function(oSettings) {
            let api = this.api();

            // Calculate the total quantity sold
            let totalQtySold = api.column(3, { page: 'current' }).data().reduce(function(a, b) {
                let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                return parseFloat(a) + numericValueB;
            }, 0);

            // Calculate the total sold subtotal
            let totalSubtotal = api.column(4, { page: 'current' }).data().reduce(function(a, b) {
                let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                return parseFloat(a) + numericValueB;
            }, 0);

            // Update the footer with the totals
            $('#footer_today_subtotal').text(totalSubtotal.toFixed(2));

            __currency_convert_recursively($('#product_sell_table'));
        },
    });

    // Trigger DataTable reload on filter change
    $('#product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_type, #product_list_filter_unit_id, #product_list_filter_tax_id, #active_state, #location_id, #product_list_filter_stock_status').change(function() {
        product_sell_table.draw();
        
    });

    $(document).on('change', '#product_list_filter_type, #product_list_filter_category_id, #product_list_filter_brand_id, #product_list_filter_unit_id, #product_list_filter_tax_id, #product_list_filter_stock_status, #location_id, #active_state, #repair_model_id',
        function() {
            product_sell_table.ajax.reload();
        });

});

</script>
@endsection