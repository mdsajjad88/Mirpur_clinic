@extends('clinic::layouts.app2')

@section('title', __('clinic::lang.stock_expire_report'))

@section('content')
    <div class="container-fluid">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('view_stock_filter', __('report.view_stocks') . ':') !!}
                        {!! Form::select('view_stock_filter', $view_stock_filter, null, [
                            'placeholder' => __('messages.all'),
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'id' => 'view_stock_filter',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('category_id', __('product.category') . ':') !!}
                        <select id="category_id" class="form-control select2" style="width:100%">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('brand_id', __('product.brand') . ':') !!}
                        <select id="brand_id" class="form-control select2" style="width:100%">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('unit_id', __('product.unit') . ':') !!}
                        <select id="unit_id" class="form-control select2" style="width:100%">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>

            </div>
        @endcomponent

        <div class="row">
            <div class="col">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="ph_stock_expiry_report_table">
                            <thead>
                                <tr>
                                    <th>@lang('business.product')</th>
                                    <th>SKU</th>
                                    <!-- <th>@lang('purchase.ref_no')</th> -->
                                    <th>@lang('report.stock_left')</th>
                                    <th>@lang('lang_v1.lot_number')</th>
                                    <th>@lang('product.exp_date')</th>
                                    <th>@lang('product.mfg_date')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>

    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var firstLoad = true;

            var stock_expiry_report_table = $('#ph_stock_expiry_report_table').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: 'https://awc.careneterp.com:82/api/stock-expiry-report-in-doctor',
                    data: function(d) {
                        d.exp_date_filter = $('#view_stock_filter').val();
                        d.category_id = $('#category_id').val();
                        d.brand_id = $('#brand_id').val();
                        d.unit_id = $('#unit_id').val();

                        if (!firstLoad) {
                            d.skip_dropdowns = true; // দ্বিতীয়বার থেকে dropdown না আনবে
                        }
                    },
                    dataSrc: function(json) {
                        if (firstLoad) {
                            // শুধু প্রথমবার dropdown populate হবে
                            if (json.categories) {
                                $.each(json.categories, function(key, value) {
                                    $('#category_id').append('<option value="' + key + '">' +
                                        value + '</option>');
                                });
                            }
                            $('#category_id').val(41).trigger('change');
                            if (json.brands) {
                                $.each(json.brands, function(key, value) {
                                    $('#brand_id').append('<option value="' + key + '">' +
                                        value + '</option>');
                                });
                            }
                            if (json.units) {
                                $.each(json.units, function(key, value) {
                                    $('#unit_id').append('<option value="' + key + '">' +
                                        value + '</option>');
                                });
                            }
                            firstLoad = false;
                        }

                        return json.data;
                    }
                },
                columns: [{
                        data: 'product'
                    },
                    {
                        data: 'ref_no'
                    },
                    {
                        data: 'stock_left',
                        render: function(data, type, row) {
                            return Math.floor(data);
                        }
                    },
                    {
                        data: 'lot_number'
                    },
                    {
                        data: 'exp_date'
                    },
                    {
                        data: 'mfg_date'
                    }
                ]
            });

            $('#view_stock_filter, #category_id, #brand_id, #unit_id').change(function() {
                stock_expiry_report_table.ajax.reload();
            });
            $('#category_id').val(41).trigger('change');
        });
    </script>
@endsection
