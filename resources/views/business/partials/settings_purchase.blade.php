<!--Purchase related settings -->
<div class="pos-tab-content">
    <div class="row">
        @if (!config('constants.disable_purchase_in_other_currency', true))
            <div class="col-sm-4">
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            {!! Form::checkbox('purchase_in_diff_currency', 1, $business->purchase_in_diff_currency, [
                                'class' => 'input-icheck',
                                'id' => 'purchase_in_diff_currency',
                            ]) !!} {{ __('purchase.allow_purchase_different_currency') }}
                        </label>
                        @show_tooltip(__('tooltip.purchase_different_currency'))
                    </div>
                </div>
            </div>
            <div class="col-sm-4 @if ($business->purchase_in_diff_currency != 1) hide @endif" id="settings_purchase_currency_div">
                <div class="form-group">
                    {!! Form::label('purchase_currency_id', __('purchase.purchase_currency') . ':') !!}
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fas fa-money-bill-alt"></i>
                        </span>
                        {!! Form::select('purchase_currency_id', $currencies, $business->purchase_currency_id, [
                            'class' => 'form-control select2',
                            'placeholder' => __('business.currency'),
                            'required',
                            'style' => 'width:100% !important',
                        ]) !!}
                    </div>
                </div>
            </div>
            <div class="col-sm-4 @if ($business->purchase_in_diff_currency != 1) hide @endif" id="settings_currency_exchange_div">
                <div class="form-group">
                    {!! Form::label('p_exchange_rate', __('purchase.p_exchange_rate') . ':') !!}
                    @show_tooltip(__('tooltip.currency_exchange_factor'))
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-info"></i>
                        </span>
                        {!! Form::number('p_exchange_rate', $business->p_exchange_rate, [
                            'class' => 'form-control',
                            'placeholder' => __('business.p_exchange_rate'),
                            'required',
                            'step' => '0.001',
                        ]) !!}
                    </div>
                </div>
            </div>
        @endif
        <div class="clearfix"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('enable_editing_product_from_purchase', 1, $business->enable_editing_product_from_purchase, [
                            'class' => 'input-icheck',
                        ]) !!} {{ __('lang_v1.enable_editing_product_from_purchase') }}
                    </label>
                    @show_tooltip(__('lang_v1.enable_updating_product_price_tooltip'))
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('enable_purchase_status', 1, $business->enable_purchase_status, [
                            'class' => 'input-icheck',
                            'id' => 'enable_purchase_status',
                        ]) !!} {{ __('lang_v1.enable_purchase_status') }}
                    </label>
                    @show_tooltip(__('lang_v1.tooltip_enable_purchase_status'))
                </div>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('enable_lot_number', 1, $business->enable_lot_number, [
                            'class' => 'input-icheck',
                            'id' => 'enable_lot_number',
                        ]) !!} {{ __('lang_v1.enable_lot_number') }}
                    </label>
                    @show_tooltip(__('lang_v1.tooltip_enable_lot_number'))
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox(
                            'common_settings[enable_purchase_order]',
                            1,
                            !empty($common_settings['enable_purchase_order']),
                            ['class' => 'input-icheck', 'id' => 'enable_purchase_order'],
                        ) !!} {{ __('lang_v1.enable_purchase_order') }}
                    </label>
                    @show_tooltip(__('lang_v1.purchase_order_help_text'))
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-6">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox(
                            'common_settings[enable_purchase_requisition]',
                            1,
                            !empty($common_settings['enable_purchase_requisition']),
                            ['class' => 'input-icheck', 'id' => 'enable_purchase_requisition'],
                        ) !!} {{ __('lang_v1.enable_purchase_requisition') }}
                    </label>
                    @show_tooltip(__('lang_v1.purchase_requisition_help_text'))
                </div>
            </div>
        </div>
        <div class="col-sm-6">

            <div class="form-group">
                {!! Form::label(
                    'supplier_id',$supplier
                ) !!}
                {{-- <span id="show_purchase_contact"></span> --}}
                {!! Form::hidden('common_settings[contact_id]', !empty($common_settings['contact_id']) ? $common_settings['contact_id'] : null, [
                            'class' => 'form-control',
                            'id' => 'show_purchase_contact',
                            'required',
                        ]) !!}
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-user"></i>
                    </span>
                    
                    {!! Form::select('supplier_id', [],null, ['class' => 'form-control', 'placeholder' => __('messages.please_select'), 'id' => 'supplier_id']) !!}
                </div>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox(
                            'common_settings[mandatory_expiry_date]',
                            1,
                            !empty($common_settings['mandatory_expiry_date']),
                            ['class' => 'input-icheck', 'id' => 'mandatory_expiry_date'],
                        ) !!} Enable Mandatory Expiry Date
                    </label>
                    @show_tooltip(__('A Mandatory Expiry Date for purchases refers to a policy or system requirement that enforces the input of an expiration date for products during the procurement process.'))
                </div>
            </div>
        </div>
    </div>
    {{-- <div class="row">
        <div class="col-sm-12">
            <h4>Auto EXP Category List</h4>
        </div>
        <div class="col-xs-12">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>@lang('Category Name')</th>
                            <th>@lang('Description')</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                    </tbody>
                </table>
            </div>
        </div>
    </div> --}}

    @php
    $index = !empty($common_settings['purchases_exp']) ? count($common_settings['purchases_exp']) : 0;
@endphp
<div class="row">
    <div class="col-sm-12">
        <h4>Set Category and Month</h4>
    </div>
    <div class="col-xs-12" id="purchases_exp_container">
        @for ($i = 0; $i < $index; $i++)
            <div class="purchases_exp_item row mb-3">
                <div class="form-group col-md-3">
                    <label for="purchases_exp[{{ $i }}][category_id]">@lang('Category Name')</label>
                    {!! Form::select("common_settings[purchases_exp][$i][category_id]", $category_dropdown, !empty($common_settings['purchases_exp'][$i]['category_id']) ? $common_settings['purchases_exp'][$i]['category_id'] : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']) !!}
                </div>
                <div class="form-group col-md-3">
                    <label for="purchases_exp[{{ $i }}][month]">Month:</label>
                    {!! Form::number("common_settings[purchases_exp][$i][month]", !empty($common_settings['purchases_exp'][$i]['month']) ? $common_settings['purchases_exp'][$i]['month'] : null, ['class' => 'form-control', 'placeholder' => 'Add EXP Month']) !!}
                </div>
                <div class="form-group col-md-2">
                    <button type="button" class="btn btn-danger remove-purchase-exp" style="margin-top: 25px;">Remove</button>
                </div>
            </div>
        @endfor
    </div>
    <div class="col-md-12">
        <button type="button" class="btn btn-primary" id="add_purchase_exp_item">Add Another</button>
    </div>
</div>

</div>