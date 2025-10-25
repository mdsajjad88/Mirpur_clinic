<div class="pos-tab-content">
    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('clinic_location', __('clinic::lang.default_location') . ':*') !!} <br>
                {!! Form::select(
                    'common_settings[clinic_location]',
                    ['' => __('Select Clinic Location')] + $business_locations->toArray(),
                    $common_settings['clinic_location'] ?? null,
                    ['class' => 'form-control select2', 'style' => 'width: 100%;'],
                ) !!}
            </div>
        </div>
        <div class="col-md-4">
            {!! Form::label('clinic_home_collection_charge', __('clinic::lang.clinic_home_collection_charge') . ':') !!} <br>
            {!! Form::number(
                'common_settings[clinic_home_collection_charge]',
                $common_settings['clinic_home_collection_charge'] ?? null,
                ['class' => 'form-control'],
            ) !!}
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('intake_form_disease', __('clinic::lang.chironic_illness') . ':') !!} <br>
                {!! Form::select(
                    'common_settings[intake_form_disease][]',
                    $diseases,
                    $common_settings['intake_form_disease'] ?? null,
                    ['class' => 'form-control select2', 'multiple' => 'multiple', 'style' => 'width: 100%;'],
                ) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('family_history_disease', __('clinic::lang.family_history_disease') . ':') !!} <br>
                {!! Form::select(
                    'common_settings[family_history_disease][]',
                    $diseases,
                    $common_settings['family_history_disease'] ?? null,
                    ['class' => 'form-control select2', 'multiple' => 'multiple', 'style' => 'width: 100%;'],
                ) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('clinic_service_charge_key', __('clinic::lang.service_charge_key') . ':') !!} <br>
                {!! Form::text(
                    'common_settings[clinic_service_charge_key]',
                    $common_settings['clinic_service_charge_key'] ?? 'Accessories',
                    ['class' => 'form-control', 'style' => 'width: 100%;'],
                ) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('clinic_service_charge', __('clinic::lang.service_charge') . ':') !!} <br>
                {!! Form::select(
                    'common_settings[clinic_service_charge]',
                    [0 => 'No', 1 => 'Yes'],
                    $common_settings['clinic_service_charge'] ?? null,
                    ['class' => 'form-control select2', 'style' => 'width: 100%;'],
                ) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('membership_product', 'Membership Product:') !!} <br>
                {!! Form::select(
                    'common_settings[membership_product]',
                    ['' => __('Select Product')] + $consultations->toArray(),
                    $common_settings['membership_product'] ?? null,
                    ['class' => 'form-control'],
                ) !!}

            </div>
            
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('call_status', 'Call Status:') !!} <br>
                <table class="table table-bordered table-sm table-striped" id="call_status_table">
                    <thead>
                        <tr>
                            <th>Call Status</th>
                            <th>Color</th>
                            <th>
                                <button type="button" class="btn btn-success btn-sm" id="add_call_status">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($common_settings['call_status']))
                            @foreach ($common_settings['call_status'] as $index => $status)
                                <tr>
                                    <td>
                                        {!! Form::text("common_settings[call_status][$index][call_status]", $status['call_status'] ?? '', [
                                            'class' => 'form-control',
                                            'placeholder' => 'Status',
                                        ]) !!}
                                    </td>
                                    <td>
                                        {!! Form::color(
                                            "common_settings[call_status][$index][call_status_color]",
                                            $status['call_status_color'] ?? '#000000',
                                            ['class' => 'form-control'],
                                        ) !!}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-danger remove-status-row">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>

                </table>

            </div>
        </div>

        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('show_medicine_name_as', __('clinic::lang.show_medicine_name_as') . ':') !!}
                <br>
                <div class="icheck-primary d-inline">
                    {!! Form::radio(
                        'common_settings[show_medicine_name_as]',
                        'medicine',
                        isset($common_settings['show_medicine_name_as']) && $common_settings['show_medicine_name_as'] == 'medicine'
                            ? true
                            : false,
                        ['id' => 'show_medicine_name_as_medicine'],
                    ) !!}
                    {!! Form::label('show_medicine_name_as_medicine', __('clinic::lang.medicine_name')) !!}
                </div>
                <div class="icheck-primary d-inline">
                    {!! Form::radio(
                        'common_settings[show_medicine_name_as]',
                        'generic',
                        isset($common_settings['show_medicine_name_as']) && $common_settings['show_medicine_name_as'] == 'generic'
                            ? true
                            : false,
                        ['id' => 'show_medicine_name_as_generic'],
                    ) !!}
                    {!! Form::label('show_medicine_name_as_generic', __('clinic::lang.generic_name')) !!}
                </div>
            </div>
        </div>
    </div>
</div>
