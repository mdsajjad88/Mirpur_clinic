<div class="row">
    <div class="col-md-4">
        <div class="form-group">
        {!! Form::label('clinic_location', __('clinic::lang.location') . ':*') !!}
        {!! Form::select('common_settings[clinic_location]', 
            ['' => __('Select Clinic Location')] + $business_locations->toArray(), 
            $common_settings['clinic_location'] ?? null, 
            ['class' => 'form-control select2', 'required' => 'required']) !!}
    </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
        {!! Form::label('clinic_location', __('clinic::lang.default_location') . ':*') !!}
        {!! Form::select('common_settings[clinic_location]', 
            ['' => __('Select Clinic Location')] + $business_locations->toArray(), 
            $common_settings['clinic_location'] ?? null, 
            ['class' => 'form-control select2', 'required' => 'required']) !!}
    </div>
    </div>
    <div class="col-md-4"></div>
</div>

