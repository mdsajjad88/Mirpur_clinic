@extends('clinic::layouts.app2')

@section('title', __('Add New Filter'))

@section('content')
    @include('crm::layouts.nav')
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open([
                'url' => action([\Modules\Crm\Http\Controllers\ContactFilterController::class, 'store']),
                'method' => 'POST',
                'id' => 'contact_filter_form',
            ]) !!}

            <div class="row">
                <h4>@lang('Contact Filters')</h4>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('name', __('Name') . ':*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Campaign Name')]) !!}
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('contact_type', __('Contact Type')) !!}
                        {!! Form::select('contact_type', ['customer' => __('Customer'), 'lead' => __('Lead')], null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('All'),
                            'id' => 'contact_type',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-3" id="crm_life_stage_div">
                    <div class="form-group">
                        {!! Form::label('crm_life_stage', __('lang_v1.life_stage')) !!}
                        {!! Form::select('crm_life_stage', $life_stages, null, [
                            'class' => 'form-control select2',
                            'id' => 'crm_life_stage',
                            'placeholder' => __('messages.please_select'),
                        ]) !!}
                    </div>
                </div>
                <div id="others">
                    <div class="col-md-3">
                        {!! Form::label('patient_type', __('Patient Type')) !!}
                        {!! Form::select(
                            'patient_type',
                            ['' => __('All'), 'new' => __('New'), 'followup' => __('Followup'), 'old' => __('Old')],
                            null,
                            ['class' => 'form-control select2'],
                        ) !!}
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('customer_group_id', __('Customer Group')) !!}
                            {!! Form::select('customer_group_ids[]', $customer_groups, null, [
                                'class' => 'form-control select2',
                                'id' => 'customer_group_id',
                                'multiple',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('minimum_visit_frequency', __('Minimum Visit Frequency') . ':') !!}
                            <div class="row">
                                <div class="col-xs-6">
                                    <div class="input-group">
                                        {!! Form::number('minimum_visit_frequency', null, [
                                            'class' => 'form-control',
                                            'min' => 1,
                                            'placeholder' => __('x'),
                                        ]) !!}
                                        <span class="input-group-addon">
                                            Times in
                                        </span>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="input-group">
                                        {!! Form::number('minimum_visit_frequency_in', null, [
                                            'class' => 'form-control',
                                            'min' => 1,
                                            'placeholder' => __('y'),
                                        ]) !!}
                                        <span class="input-group-addon">months</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        @php
                            $months = [];
                            foreach (range(1, 12) as $m) {
                                $months[sprintf('%02d', $m)] = date('F', mktime(0, 0, 0, $m, 1));
                            }
                            $currentMonth = date('m');
                        @endphp
                        {!! Form::label('Month', __('Month')) !!}
                        {!! Form::select('months[]', $months, $currentMonth, ['class' => 'form-control select2', 'multiple']) !!}

                    </div>
                    <div class="col-md-2">
                        @php
                            $years = [];
                            $currentYear = date('Y');

                            foreach (range($currentYear, $currentYear - 4) as $y) {
                                $years[$y] = $y;
                            }
                        @endphp
                        {!! Form::label('year', __('Year')) !!}
                        {!! Form::select('year', $years, $currentYear, [
                            'class' => 'form-control',
                            'placeholder' => '-- Select Year --',
                        ]) !!}

                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('average_spend_per_visit_range', __('Average Spend Per Visit (TK)') . ':') !!}

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">From</span>
                                        {!! Form::number('average_spend_per_visit_range[0]', request('average_spend_per_visit_range')[0] ?? null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('Min'),
                                            'min' => 0,
                                            'step' => 'any',
                                        ]) !!}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-addon">To</span>
                                        {!! Form::number('average_spend_per_visit_range[1]', request('average_spend_per_visit_range')[1] ?? null, [
                                            'class' => 'form-control',
                                            'placeholder' => __('Max'),
                                            'min' => 0,
                                            'step' => 'any',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doctor_id', __('Doctors')) !!}
                            {!! Form::select('doctor_user_id', $doctors, null, [
                                'class' => 'form-control select2',
                                'id' => 'doctor_user_id',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('district_id', __('District')) !!}
                            {!! Form::select('district_id', ['' => __('All')] + $districts->toArray(), null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'district_id',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('gender', __('Gender')) !!}
                            {!! Form::select('gender', ['' => __('All'), 'male' => __('Male'), 'female' => __('Female')], null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'gender',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('age_range', __('Age Range')) !!}
                            <div class="row">
                                <div class="col-md-6">
                                    {!! Form::number('age_range_min', null, [
                                        'class' => 'form-control',
                                        'min' => 1,
                                        'placeholder' => __('min'),
                                    ]) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::number('age_range_max', null, [
                                        'class' => 'form-control',
                                        'min' => 1,
                                        'placeholder' => __('max'),
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('has_transaction', __('Has Transaction')) !!}
                            {!! Form::select('has_transaction', ['1' => __('Yes'), '0' => __('No')], null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'placeholder' => __('All'),
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('last_transaction_days', __('Last Transaction (Days)')) !!}
                            {!! Form::number('last_transaction_days', null, [
                                'class' => 'form-control',
                                'min' => 1,
                                'placeholder' => __('Days'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('problem_id', __('Diseases')) !!}
                            {!! Form::select('problem_ids[]', $problems, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'multiple' => 'multiple',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('description', __('Description')) !!}
                        {!! Form::textarea('description', null, [
                            'class' => 'form-control',
                            'rows' => 3,
                            'placeholder' => __('Optional description about the campaign'),
                        ]) !!}
                    </div>
                </div>

            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">@lang('Create Contact Filter')</button>
                <a href="{{ action([\Modules\Crm\Http\Controllers\ContactFilterController::class, 'index']) }}"
                    class="btn btn-default">@lang('Cancel')</a>
            </div>

            {!! Form::close() !!}
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            // Initialize select2
            $('.select2').select2();

            function toggleCrmLifeStageDiv() {
                if ($('#contact_type').val() === 'lead') {
                    $('#crm_life_stage_div').show(); // show life_stage
                    $('#others').hide(); // hide others
                } else if ($('#contact_type').val() === 'customer') {
                    $('#crm_life_stage_div').hide(); // hide life_stage
                    $('#others').show(); // show others
                } else {
                    // for placeholder 'All' or empty selection
                    $('#crm_life_stage_div').hide();
                    $('#others').hide();
                }
            }


            $('#contact_type').on('change', toggleCrmLifeStageDiv);
            toggleCrmLifeStageDiv();


            // Form validation
            $('#contact_filter_form').validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3
                    }
                },
                messages: {
                    name: {
                        required: "Please enter filter name",
                        minlength: "Filter name must be at least 3 characters"
                    }
                },
                submitHandler: function(form) {
                    var submitButton = $(form).find('button[type="submit"]');
                    submitButton.prop('disabled', true);

                    $.ajax({
                        url: $(form).attr('action'),
                        type: 'POST',
                        data: $(form).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.msg);
                                setTimeout(function() {
                                    window.location.href = response.redirect;
                                }, 1000);
                            } else {
                                toastr.error(response.msg);
                                submitButton.prop('disabled', false).text('Submit');

                            }
                        },
                        error: function() {
                            submitButton.prop('disabled', false).text('Submit');
                        },
                        complete: function() {
                            submitButton.prop('disabled', false).text('Submit');
                        }
                    });
                }
            });
        });
    </script>
@endsection
