<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php

            $form_id = 'patient_add_form';
            if (isset($quick_add)) {
                $form_id = 'quick_add_contact';
            }

            if (isset($store_action)) {
                $url = $store_action;
                $type = 'lead';
                $customer_groups = [];
            } else {
                $url = action([\Modules\Clinic\Http\Controllers\PatientController::class, 'store']);
                $type = isset($selected_type) ? $selected_type : '';
                $sources = [];
                $life_stages = [];
            }
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('clinic::patient.add_patient')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-3 individual">
                    <div class="form-group">
                        {!! Form::label('first_name', __('clinic::lang.name') . ':') !!}<span class="star">*</span>
                        {!! Form::text('first_name', null, [
                            'class' => 'form-control first_name',
                            'required',
                            'placeholder' => __('clinic::lang.name'),
                        ]) !!}
                    </div>
                </div>
                {{-- <div class="col-md-3 individual">
                    <div class="form-group">
                        {!! Form::label('middle_name', __('lang_v1.middle_name') . ':') !!}
                        {!! Form::text('middle_name', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.middle_name')]) !!}
                    </div>
                </div> --}}
                <div class="col-md-3 individual hide">
                    <div class="form-group">
                        {!! Form::label('last_name', __('business.last_name') . ':') !!}
                        {!! Form::text('last_name', null, ['class' => 'form-control', 'placeholder' => __('business.last_name')]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('mobile', __('contact.mobile') . ':') !!} <span class="star">*</span>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-mobile"></i>
                            </span>
                            {!! Form::text('mobile', null, [
                                'class' => 'form-control mobile',
                                'required',
                                'placeholder' => __('contact.mobile'),
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3 hide">
                    <div class="form-group">
                        {!! Form::label('landline', __('contact.landline') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-phone"></i>
                            </span>
                            {!! Form::text('landline', null, ['class' => 'form-control', 'placeholder' => __('contact.landline')]) !!}
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
                        {!! Form::label('age', __('clinic::lang.age').':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::number('age', null, ['class' => 'form-control', 'placeholder' => 'Enter your age']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('gender',  __('clinic::lang.gender') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-users"></i>
                            </span>
                            {!! Form::select('gender', ['male' => __('clinic::lang.male'), 'female' => __('clinic:lang.female'), 'others' => __('clinic::lang.others')], null, [
                                'class' => 'form-control',
                                'placeholder' => 'Select your gender',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    {!! Form::label('disease', __('clinic::lang.health_concern') . ':') !!} <br>
                    {!! Form::select('disease[]', $diseases->pluck('name', 'id'), null, [
                        'class' => 'form-control select2',
                        'multiple' => 'multiple',
                        'id' => 'disease',
                        'style' => 'width: 100%;',
                        'data-placeholder' => __('clinic::lang.select_health_concern'),
                    ]) !!}
                </div>
                <div class="col-md-3 customer_fields">
                    <div class="form-group">
                        {!! Form::label('send_sms', __('clinic::patient.sms_notifications') . ':') !!}
                        <div class="input-group">
                            {!! Form::checkbox('send_sms', 1, true) !!}
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-4 contact_type_div hide">
                    <div class="form-group">
                        {!! Form::label('type', __('contact.contact_type') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::text('type', 'customer', ['class' => 'form-control', 'required', 'readonly' => 'readonly']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4 hide">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('clinic::lang.user_id') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                            {!! Form::text('contact_id', null, ['class' => 'form-control', 'placeholder' => __('clinic::lang.user_id')]) !!}
                        </div>
                        <p class="help-block">
                            @lang('lang_v1.leave_empty_to_autogenerate')
                        </p>
                    </div>
                </div>


                <div class="col-md-4 customer_fields">
                    <div class="form-group">
                        {!! Form::label('customer_group_id', __('clinic::lang.patient_group') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-users"></i>
                            </span>
                            {!! Form::select('customer_group_id', $customer_groups, '', ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>

               

                <div class="col-md-2 mt-15 hide">
                    <label class="radio-inline">
                        <input type="radio" name="contact_type_radio" id="inlineRadio1" value="individual" checked>
                        @lang('lang_v1.individual')
                    </label>
                </div>



                <div class="clearfix"></div>

            </div>
            <div class="row mt-1">
                <div class="col-md-12">
                    <button type="button" class="btn btn-primary center-block more_btn"
                        data-target="#more_div">@lang('clinic::lang.more_info') <i class="fa fa-chevron-down"></i>
                    </button>
                </div>

                <div id="more_div" class="add_more_info_customer hide">
                    {!! Form::hidden('position', null, ['id' => 'position']) !!}
                    <div class="col-md-12">
                        <hr />
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-4 individual">
                        <div class="form-group">
                            {!! Form::label('prefix', __('business.prefix') . ':') !!}
                            {!! Form::text('prefix', null, ['class' => 'form-control', 'placeholder' => __('business.prefix_placeholder')]) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
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
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('alternate_number', __('contact.alternate_contact_number') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-phone"></i>
                                </span>
                                {!! Form::text('alternate_number', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('contact.alternate_contact_number'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('address_line_1', __('lang_v1.address_line_1') . ':') !!}
                            {!! Form::text('address_line_1', null, [
                                'class' => 'form-control',
                                'placeholder' => __('lang_v1.address_line_1'),
                                'rows' => 3,
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('address_line_2', __('lang_v1.address_line_2') . ':') !!}
                            {!! Form::text('address_line_2', null, [
                                'class' => 'form-control',
                                'placeholder' => __('lang_v1.address_line_2'),
                                'rows' => 3,
                            ]) !!}
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('city', __('business.city') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::text('city', null, ['class' => 'form-control', 'placeholder' => __('business.city')]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('state', __('business.state') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::text('state', null, ['class' => 'form-control', 'placeholder' => __('business.state')]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('country', __('business.country') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-globe"></i>
                                </span>
                                {!! Form::text('country', null, ['class' => 'form-control', 'placeholder' => __('business.country')]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('zip_code', __('business.zip_code') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-map-marker"></i>
                                </span>
                                {!! Form::text('zip_code', null, [
                                    'class' => 'form-control',
                                    'placeholder' => __('business.zip_code_placeholder'),
                                ]) !!}
                            </div>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="col-md-12">
                        <hr />
                    </div>
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
