<!--Purchase related settings -->
<div class="pos-tab-content">
    <div class="row">
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('default_credit_limit',__('lang_v1.default_credit_limit') . ':') !!}
                {!! Form::text('common_settings[default_credit_limit]', $common_settings['default_credit_limit'] ?? '', ['class' => 'form-control input_number',
                'placeholder' => __('lang_v1.default_credit_limit'), 'id' => 'default_credit_limit']) !!}
            </div>
        </div>

        <!-- Add Customer Group for Doctor Appointment Patients -->
        <div class="col-sm-4">
            <div class="form-group">
                {!! Form::label('doctor_visit_customer_group', __('Doctor Appointment Customer Group') . ':') !!}
                {!! Form::select('common_settings[doctor_visit_customer_group_id]', [],
                    $common_settings['doctor_visit_customer_group_id'] ?? '', 
                    ['class' => 'form-control select2', 'id' => 'doctor_visit_customer_group', 'style' => 'width:100%', 'placeholder' => __('messages.please_select')]) !!}
                <small class="help-block">{{ __('Select customer group for doctor visited patients to apply pharmacy products discount') }}</small>
            </div>
        </div>
    </div>
</div>