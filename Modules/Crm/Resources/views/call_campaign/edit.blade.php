@extends('clinic::layouts.app2')

@section('title', __('Edit Campaign') . ': ' . $campaign->name)

@section('content')
@include('crm::layouts.nav')
    <section class="content">
        @component('components.widget', ['class' => 'box-primary'])
            {!! Form::open(['url' => action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'update'], [$campaign->id]), 
                'method' => 'POST', 'id' => 'campaign_form' ]) !!}
            {!! Form::hidden('_method', 'PUT') !!}
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('name', __('Name') . ':*') !!}
                        {!! Form::text('name', $campaign->name, 
                            ['class' => 'form-control', 'required', 'placeholder' => __('Campaign Name')]) !!}
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('survey_type_id', __('Survey Type') . ':*') !!}
                        {!! Form::select('survey_type_id', $surveyTypes, $campaign->survey_type_id, 
                            ['class' => 'form-control select2', 'style' => 'width:100%', 'required', 'placeholder' => 'Select a Survey Type']) !!}
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('start_date', __('Start Date') . ':*') !!}
                        {!! Form::date('start_date', $campaign->start_date, 
                            ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('end_date', __('End Date')) !!}
                        {!! Form::date('end_date', $campaign->end_date, 
                            ['class' => 'form-control']) !!}
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('status', __('Status') . ':*') !!}
                        {!! Form::select('status', 
                            ['draft' => __('Draft'), 'active' => __('Active'), 'paused' => __('Paused'), 'completed' => __('Completed')], 
                            $campaign->status, 
                            ['class' => 'form-control select2', 'style' => 'width:100%', 'required']) !!}
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                {!! Form::label('description', __('Description')) !!}
                {!! Form::textarea('description', $campaign->description, 
                    ['class' => 'form-control', 'rows' => 3, 'placeholder' => __('Optional description about the campaign')]) !!}
            </div>
            
            <h4>@lang('Contact Filters')</h4>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('filters', __('Filters')) !!}
                        {!! Form::select('contact_filter_id', 
                            $filters, 
                            $campaign->contact_filter_id??'', 
                            ['class' => 'form-control select2', 'required', 'id'=>'contact_filter_id', 'style' => 'width:100%', 'placeholder' => __('All')]) !!}
                    </div>
                </div>
                <div class="col-md-8" id="filters_information">
                    
                </div>
                
            </div>
            
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('regenerate_contacts', 1, false) !!}
                        @lang('Regenerate contacts based on updated filters')
                    </label>
                    <p class="help-block">@lang('Warning: This will delete all existing campaign contacts and create new ones based on current filters')</p>
                </div>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">@lang('Update Campaign')</button>
                <a href="{{ action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'show'], [$campaign->id]) }}" 
                   class="btn btn-info">@lang('View Campaign')</a>
                <a href="{{ action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'index']) }}" 
                   class="btn btn-default">@lang('Cancel')</a>
            </div>
            
            {!! Form::close() !!}
        @endcomponent
        
        @component('components.widget', ['class' => 'box-primary'])
            <h4>@lang('Import Additional Contacts')</h4>
            {!! Form::open(['url' => action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'importContacts'], [$campaign->id]), 
                'method' => 'POST', 'files' => true, 'id' => 'import_form' ]) !!}
            
            <div class="form-group">
                {!! Form::label('contacts_file', __('CSV File') . ':*') !!}
                {!! Form::file('contacts_file', ['class' => 'form-control', 'required', 'accept' => '.csv']) !!}
                <p class="help-block">@lang('CSV format: name,mobile,type (customer/lead/supplier)')</p>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn btn-primary">@lang('Import Contacts')</button>
            </div>
            
            {!! Form::close() !!}
        @endcomponent
        
        @component('components.widget', ['class' => 'box-primary'])
            <h4>@lang('Campaign Statistics')</h4>
            <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">@lang('Total Contacts')</span>
                            <span class="info-box-number">{{ $campaign->target_count }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-green"><i class="fa fa-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">@lang('Completed')</span>
                            <span class="info-box-number">{{ $campaign->completed_count }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-yellow"><i class="fa fa-clock-o"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">@lang('In Progress')</span>
                            <span class="info-box-number">{{ $campaign->in_progress_count ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-red"><i class="fa fa-times"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">@lang('Failed')</span>
                            <span class="info-box-number">{{ $campaign->failed_count ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>
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
                    },
                    status: {
                        required: true
                    }
                },
                messages: {
                    name: {
                        required: "Please enter campaign name",
                        minlength: "Campaign name must be at least 3 characters"
                    }
                }
            });
            
            // Confirm before regenerating contacts
            $('input[name="regenerate_contacts"]').change(function() {
                if ($(this).is(':checked')) {
                    if (!confirm('Are you sure you want to regenerate contacts? This will delete all existing campaign contacts.')) {
                        $(this).prop('checked', false);
                    }
                }
            });

            $('#campaign_form').submit(function(e) {
                e.preventDefault();
                var form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.msg);
                            setTimeout(function() {
                                window.location.href = response.redirect;
                            }, 2000);
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        toastr.error(error);
                    }
                });
            });
            function getFilterData(contact_filter_id) {
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

                            $.each(filters, function(key, value) {
                                // Capitalize key
                                let label = key.replace(/_/g, ' ').replace(/\b\w/g, l =>
                                    l.toUpperCase());

                                html += `
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <strong>${label}:</strong> ${value}
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
            }
            var filter_id = '{{ $campaign->contact_filter_id }}';
            if (filter_id != '') {
                getFilterData(filter_id);
            }
            $(document).on('change', '#contact_filter_id', function() {
                getFilterData($(this).val());
            });
        });
    </script>
@endsection