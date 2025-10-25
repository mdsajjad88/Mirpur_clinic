<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => $store_action, 'method' => 'post', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('crm::lang.add_new_lead')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-4 contact_type_div">
                    <div class="form-group">
                        {!! Form::label('type', __('contact.contact_type') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                            {!! Form::select('type', $types, 'lead', [
                                'class' => 'form-control',
                                'id' => 'contact_type',
                                'placeholder' => __('messages.please_select'),
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4 individual">
                    <div class="form-group">
                        {!! Form::label('first_name', __('crm::lang.name') . ':*') !!}
                        {!! Form::text('first_name', 'Unknown', [
                            'class' => 'form-control name',
                            'required',
                            'placeholder' => __('crm::lang.name'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
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
            </div>
            <div class="row">
                <div class="col-md-4 lead_additional_div">
                    <div class="form-group">
                        {!! Form::label('crm_source', __('lang_v1.source') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa fa-search"></i>
                            </span>
                            {!! Form::select('crm_source', $sources, null, [
                                'class' => 'form-control',
                                'id' => 'crm_source',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-4 lead_additional_div">
                    <div class="form-group">
                        {!! Form::label('crm_life_stage', __('lang_v1.life_stage') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa fa-life-ring"></i>
                            </span>
                            {!! Form::select('crm_life_stage', $life_stages, null, [
                                'class' => 'form-control',
                                'id' => 'crm_life_stage',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                            {!! Form::hidden('prefix', null) !!}
                            {!! Form::hidden('email', null) !!}
                            {!! Form::hidden('dob', null) !!}
                            {!! Form::hidden('age', null) !!}
                            {!! Form::hidden('customer_group_id', null) !!}
                            {!! Form::hidden('send_sms', 1) !!}
                            {!! Form::hidden('alternate_number', null) !!}
                            {!! Form::hidden('address_line_1', null) !!}
                            {!! Form::hidden('city', null) !!}
                            {!! Form::hidden('state', null) !!}
                            {!! Form::hidden('country', null) !!}
                            {!! Form::hidden('zip_code', null) !!}
                            {!! Form::hidden('campaign_id',null, ['class' => 'campaign_id']) !!}

                        </div>
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
