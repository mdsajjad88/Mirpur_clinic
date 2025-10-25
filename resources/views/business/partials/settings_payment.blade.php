<!--payment related settings -->
<div class="pos-tab-content">
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('cash_denominations', __('lang_v1.cash_denominations') . ':') !!}
                 {!! Form::text('pos_settings[cash_denominations]', isset($pos_settings['cash_denominations']) ? $pos_settings['cash_denominations'] : null, ['class' => 'form-control', 'id' => 'cash_denominations']) !!}
                 <p class="help-block">{{__('lang_v1.cash_denominations_help')}}</p>
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('enable_cash_denomination_on', __('lang_v1.enable_cash_denomination_on') . ':') !!}
                {!! Form::select('pos_settings[enable_cash_denomination_on]', ['pos_screen' => __('lang_v1.pos_screen'), 'all_screens' => __('lang_v1.all_screen')], isset($pos_settings['enable_cash_denomination_on']) ? $pos_settings['enable_cash_denomination_on'] : 'pos_screen', ['class' => 'form-control', 'style' => 'width: 100%;' ]) !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('enable_cash_denomination_for_payment_methods', __('lang_v1.enable_cash_denomination_for_payment_methods') . ':') !!}
                {!! Form::select('pos_settings[enable_cash_denomination_for_payment_methods][]', $payment_types, isset($pos_settings['enable_cash_denomination_for_payment_methods']) ? $pos_settings['enable_cash_denomination_for_payment_methods'] : null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'multiple' ]) !!}
            </div>
        </div>

        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                <br>
                  <label>
                    {!! Form::checkbox('pos_settings[cash_denomination_strict_check]', 1,  
                        !empty($pos_settings['cash_denomination_strict_check']) , 
                    [ 'class' => 'input-icheck']) !!} {{ __( 'lang_v1.strict_check' ) }}
                  </label>
                  @show_tooltip(__('lang_v1.strict_check_help'))
                </div>
            </div>
        </div>
    </div>
    <!-- Return Process Settings -->
    <div class="form-group">
        {!! Form::label('return_process_settings', __('Return Process') . ':') !!}  @show_tooltip('Choose how to process returns: Automatically mark returned items as paid for quick refunds, or mark them as due for payment to review before finalizing.')
        
        <div class="radio">
            <label>
                {!! Form::radio('pos_settings[return_process]', 'auto_paid', isset($pos_settings['return_process']) && $pos_settings['return_process'] == 'auto_paid', ['class' => 'input-icheck']) !!} 
                {{ __('Process returns as automatically paid') }}
            </label>
        </div>
        <div class="radio">
            <label>
                {!! Form::radio('pos_settings[return_process]', 'due', isset($pos_settings['return_process']) && $pos_settings['return_process'] == 'due', ['class' => 'input-icheck']) !!} 
                {{ __('Process returns as due') }}
            </label>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('sell_return_payment_settings', __('Sell Return Payment') . ':') !!}
        @show_tooltip('Choose how to handle payments for sell returns: Automatically generate payments for due
        bills or process them manually.')

        <div class="radio">
            <label>
                {!! Form::radio(
                    'pos_settings[sell_return_payment]',
                    'auto_paid',
                    isset($pos_settings['sell_return_payment']) && $pos_settings['sell_return_payment'] == 'auto_paid',
                    ['class' => 'input-icheck'],
                ) !!}
                {{ __('Sell Return Auto Ggenerated payment for Sell due billing') }}
            </label>
        </div>
        <div class="radio">
            <label>
                {!! Form::radio(
                    'pos_settings[sell_return_payment]',
                    'manual',
                    isset($pos_settings['sell_return_payment']) && $pos_settings['sell_return_payment'] == 'manual',
                    ['class' => 'input-icheck'],
                ) !!}
                {{ __('Process sell return Payment manually') }}
            </label>
        </div>
    </div>
</div>