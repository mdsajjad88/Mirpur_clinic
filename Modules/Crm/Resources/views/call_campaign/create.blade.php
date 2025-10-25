@extends('clinic::layouts.app2')

@section('title', __('Add New Campaign'))

@section('content')
    @include('crm::layouts.nav')
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open([
                'url' => action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'store']),
                'method' => 'POST',
                'id' => 'campaign_form',
            ]) !!}

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('name', __('Name') . ':*') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __('Campaign Name')]) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('survey_type_id', __('Survey Type') . ':*') !!}
                        {!! Form::select('survey_type_id', $surveyTypes, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'required',
                            'placeholder' => 'Select a Survey Type',
                        ]) !!}
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('start_date', __('Start Date') . ':*') !!}
                        {!! Form::date('start_date', null, ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('end_date', __('End Date')) !!}
                        {!! Form::date('end_date', null, ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>

            <div class="form-group">
                {!! Form::label('description', __('Description')) !!}
                {!! Form::textarea('description', null, [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => __('Optional description about the campaign'),
                ]) !!}
            </div>

            <h4>@lang('Contact Filters')</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('filters', __('Filters')) !!}
                        {!! Form::select('contact_filter_id', $filters, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('All'),
                            'required',
                            'id' => 'contact_filter_id',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-8" id="filters_information">

                </div>


            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">@lang('Create Campaign')</button>
                <a href="{{ action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'index']) }}"
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

            // Form validation
            $('#campaign_form').validate({
                rules: {
                    name: {
                        required: true,
                        minlength: 3
                    },
                    survey_type_id: {
                        required: true
                    },
                    start_date: {
                        required: true,
                        date: true
                    }
                },
                messages: {
                    name: {
                        required: "Please enter campaign name",
                        minlength: "Campaign name must be at least 3 characters"
                    }
                },
                submitHandler: function(form) {
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
                            }
                        }
                    });
                }
            });

            $(document).on('change', '#contact_filter_id', function() {
                var contact_filter_id = $(this).val();
                if (contact_filter_id != '') {
                    $.ajax({
                        method: 'GET',
                        url: '/crm/get-filtered-field-data/' + contact_filter_id,
                        dataType: 'json',
                        success: function(response) {
                            if (response.success == false) {
                                toastr.error(response.msg);
                                return;
                            }

                            let filters = response.data;
                            let html = '';
                            html += `<div class="col-md-3">
                                        <div class="form-group">
                                            <strong>Total Customer:</strong> ${response.target_contact}
                                        </div>
                                    </div>`;
                            $.each(filters, function(key, value) {
                                // Capitalize key
                                let label = key.replace(/_/g, ' ').replace(/\b\w/g, l =>
                                    l.toUpperCase());

                                let displayValue = '';
                                console.log('Months from response:', value);

                                if (Array.isArray(value)) {
                                    if (key === 'months') {
                                        displayValue = value
                                            .map(m => {
                                                // Ensure we have a number
                                                let monthIndex = parseInt(m, 10) -
                                                1;
                                                return new Date(0, monthIndex)
                                                    .toLocaleString('default', {
                                                        month: 'long'
                                                    });
                                            })
                                            .join(', ');
                                    } else {
                                        displayValue = value.join(', ');
                                    }
                                } else {
                                    displayValue = value ? value.charAt(0)
                                        .toUpperCase() + value.slice(1) : 'N/A';
                                }

                                html += `
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>${label}:</strong> ${displayValue}
                                        </div>
                                    </div>
                                `;
                            });



                            $('#filters_information').html(html);
                        },

                        error: function(xhr, status, error) {
                            var errorMessage = xhr.responseJSON?.message || status;
                            toastr.error(errorMessage);
                        }
                    })
                }
            })
        });
    </script>
@endsection
