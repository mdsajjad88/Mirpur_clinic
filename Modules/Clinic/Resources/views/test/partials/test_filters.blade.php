<div class="row">
    <div class="col-md-12">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('type[]', __('clinic::test.product_type') . ':') !!}
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
                {!! Form::label('active_state', __('Test Status') . ':') !!}
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
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('brand_id[]', __('clinic::lang.brand') . ':') !!}
                {!! Form::select('brand_id[]', $brands, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'product_list_filter_brand_id', 'multiple' => 'multiple'])!!}
            </div>
        </div>            
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        @if (!empty($pos_module_data))
            @foreach ($pos_module_data as $key => $value)
                @if (!empty($value['view_path']))
                    @includeIf($value['view_path'], ['view_data' => $value['view_data']])
                @endif
            @endforeach
        @endif
    </div>
</div>
@if(auth()->user()->can('today_sell.apply.date.range.filter'))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
        {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']) !!}
    </div>
</div>
@endif
