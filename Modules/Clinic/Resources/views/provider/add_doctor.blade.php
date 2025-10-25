<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php

            $form_id = 'doctor_add_form';
            if (isset($quick_add)) {
                $form_id = 'quick_add_doctor';
            }

            if (isset($store_action)) {
                $url = $store_action;
                $type = 'lead';
                $customer_groups = [];
            } else {
                $url = action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'store']);
                $type = isset($selected_type) ? $selected_type : '';
                $sources = [];
                $life_stages = [];
            }
        @endphp
        {!! Form::open(['url' => action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'store']), 'method' => 'post', 'id' => 'doctor_add_form']) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">
                @lang('clinic::doctor.add_doctor')
                
            </h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                        {!! Form::text('first_name', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('business.first_name'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3 individual">
                    <div class="form-group">
                        {!! Form::label('last_name', __('business.last_name') . ':') !!}
                        {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('business.last_name')]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-mobile"></i>
                            </span>
                            {!! Form::number('mobile', null, ['class' => 'form-control', 'required', 'placeholder' => __('contact.mobile')]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('email', __('business.email') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-envelope"></i>
                            </span>
                            {!! Form::email('email', null, ['class' => 'form-control', 'placeholder' => __('business.email')]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('dob', __('lang_v1.dob') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>

                            {!! Form::text('dob', null, [
                                'class' => 'form-control dob-date-picker',
                                'placeholder' => __('lang_v1.dob'),
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <br>
                        <label>
                            {!! Form::checkbox('is_doctor', 1, true, [
                                'class' => 'is_doctor',
                                'id' => 'is_doctor',
                                'data-target' => '.doctor_designation',
                                'style' => 'height:20px; width:20px;',
                            ]) !!} <strong>Is Doctor</strong>
                        </label>
                    </div>
                </div>
                <div class="doctor_designation">
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('designation', __('Doctor Designation') . ':') !!}
                            <div class="input-group">

                                {!! Form::text('designation', null, ['class' => 'form-control', 'placeholder' => __('Doctor Designation')]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <br>
                        <label>
                            {!! Form::checkbox('is_show_invoice', 1, true, [
                                'class' => 'is_show_invoice',
                                'id' => 'is_show_invoice',
                                'style' => 'height:20px; width:20px;',
                            ]) !!} <strong>Is show Invoice?</strong>
                        </label>
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('address', __('clinic::doctor.address') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-location-pin"></i>
                            </span>
                            {!! Form::textarea('address', null, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.address_placeholder'),
                                'rows' => 2,
                            ]) !!}
                        </div>
                    </div>
                </div>


            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}

        </div>
    </div>
