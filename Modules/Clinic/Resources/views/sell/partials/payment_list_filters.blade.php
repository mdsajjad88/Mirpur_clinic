
@if(empty($only) || in_array('sell_list_filter_date_range', $only))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
        {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly'])  !!}
    </div>
</div>
@endif
@if((empty($only) || in_array('created_by', $only)) && !empty($sales_representative))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('created_by',  __('report.user') . ':') !!}
        {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%'])  !!}
    </div>
</div>
@endif
@if(empty($only) || in_array('sell_list_filter_payment_method', $only))
<div class="col-md-3">
    <div class="form-group">
        {!! Form::label('sell_list_filter_payment_method', __('lang_v1.payment_method') . ':') !!}
        {!! Form::select('sell_list_filter_payment_method', $payment_types, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')])  !!}
    </div>
</div>
@endif
