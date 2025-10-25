@extends('clinic::layouts.app2')
@section('title', __('crm::lang.call_logs'))
@section('content')
    @include('crm::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.call_logs')])
            @slot('tool')
                <div class="box-tools pull-right">
                    <div id="patient_follow_up_section" class="hide" style="margin-bottom: 5px;">
                        <a id="total_followups_btn" class="btn btn-success" href="#" target="_blank">
                            Total Followups: <span id="total_followups">0</span>
                        </a>

                        <a id="pending_followups_link" class="btn btn-warning" href="#" target="_blank">
                            Pending Followups: <span id="pending_followups">0</span>
                        </a>
                    </div>


                    <a target="_blank" class="btn btn-primary show_patient_profile"
                        href="{{ action([\Modules\Clinic\Http\Controllers\PatientController::class, 'profile'], ['id' => 0]) }}"
                        style="display: none;">
                        @lang('crm::lang.profile')
                    </a>

                    <a target="_blank" class="btn btn-info create_new_appointment_btn"
                        href="{{ action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'index']) }}"
                        style="display: none;">
                        @lang('crm::lang.appointment')
                    </a>
                    <!-- Add this new button for follow-up -->
                    <button type="button" class="btn btn-primary create_follow_up_btn"
                        data-href="{{ action([\Modules\Crm\Http\Controllers\ScheduleController::class, 'create']) }}"
                        style="display: none;">
                        @lang('crm::lang.create_follow_up')
                    </button>
                </div>
            @endslot

            <div class="row">
                <!-- Left Column - Form -->
                <div class="col-md-8">
                    {!! Form::open([
                        'url' => action([\Modules\Crm\Http\Controllers\CallLogController::class, 'store']),
                        'method' => 'post',
                        'id' => 'call_log_store_form',
                    ]) !!}

                    <div class="row">
                        <div class="col-md-6">
                            {!! Form::label('call_type', __('crm::lang.type') . ':*') !!}
                            {!! Form::select('call_type', ['inbound' => 'In', 'outbound' => 'Out'], null, [
                                'class' => 'form-control',
                                'required',
                            ]) !!}
                        </div>

                        <div class="col-md-6">
                            {!! Form::label('contact_id', __('crm::lang.contact') . ':*') !!}
                            {!! Form::hidden('start_time', $start_time) !!}
                            <div class="input-group">
                                {!! Form::select('contact_id', [], null, [
                                    'class' => 'form-control select2',
                                    'id' => 'customer_id_clinic',
                                    'required',
                                    'data-placeholder' => __('crm::lang.select_lead'),
                                ]) !!}
                                <span class="input-group-btn">
                                    <button type="button"
                                        class="btn btn-default bg-white btn-flat edit_customer_button_call_log" data-id="">
                                        <i class="glyphicon glyphicon-edit text-primary"></i>
                                    </button>
                                </span>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat btn-add-lead pull-right"
                                        data-href="{{ action([\Modules\Crm\Http\Controllers\LeadController::class, 'create']) }}"><i
                                            class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                    </button>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-6">
                            {!! Form::label('call_subject_id', __('crm::lang.call_subject') . ':*') !!}
                            @can('crm.call_subject_store')
                                <div class="input-group">
                                @endcan
                                {!! Form::select('call_subject_id[]', $call_subjects->toArray(), null, [
                                    'class' => 'form-control select2',
                                    'id' => 'call_subject_id',
                                    'required',
                                    'multiple',
                                    'style' => 'width: 100% !important;',
                                ]) !!}
                                @can('crm.call_subject_store')
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal"
                                            data-href="{{ action([\Modules\Crm\Http\Controllers\CrmCallSubjectController::class, 'create']) }}"
                                            data-container=".call_subject_modal"><i
                                                class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                        </button>
                                    </span>
                                </div>
                            @endcan
                        </div>

                        <div class="col-md-6">
                            {!! Form::label('call_tag_id', __('crm::lang.call_tag') . ':*') !!}
                            @can('crm.call_tag_store')
                                <div class="input-group">
                                @endcan
                                {!! Form::select('call_tag_id[]', $tags->toArray(), $tag, [
                                    'class' => 'form-control select2',
                                    'id' => 'call_tag_id',
                                    'required',
                                    'multiple',
                                    'style' => 'width: 100% !important;',
                                ]) !!}
                                @can('crm.call_tag_store')
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default bg-white btn-flat btn-modal"
                                            data-href="{{ action([\Modules\Crm\Http\Controllers\CallTagController::class, 'create']) }}"
                                            data-container=".call_tag_modal"><i
                                                class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                        </button>
                                    </span>
                                </div>
                            @endcan
                        </div>
                    </div>

                    <div class="row" style="margin-top: 15px;">
                        <div class="col-md-8">
                            {!! Form::label('note', __('crm::lang.note')) !!}
                            {!! Form::textarea('note', null, [
                                'class' => 'form-control',
                                'rows' => 4,
                                'placeholder' => __('crm::lang.note'),
                                'style' => 'font-size: 22px;',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <!-- Right Column - Patient Info -->
                <div class="col-md-4">
                    <div class="box box-solid" id="patient_info_section"
                        style="display: none; border: 1px solid #e0e0e0; border-radius: 4px;">
                        <div class="box-header with-border" style="border-bottom: 1px solid #e0e0e0; padding: 10px 15px;">
                            <h3 class="box-title" style="color: #333; font-size: 16px; margin: 0;">
                                <i class="fa fa-user-md" style="color: #555;"></i> <span id="cus_type"></span> Information
                            </h3>
                        </div>
                        <div class="box-body" style="padding: 15px;">
                            <!-- Age & Status Row -->
                            <div
                                style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0;">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong><i class="fa fa-birthday-cake"></i> Age:</strong>
                                    </div>
                                    <div class="col-md-8 age_input hide">
                                        <input type="number" name="age" id="age_input" class="form-control">
                                    </div>
                                    <div class="col-md-8 age_label hide">
                                        <span id="show_age"></span>
                                    </div>

                                </div>

                                <div id="patient_status_div">
                                    <strong style="color: #555;"><i class="fa fa-heartbeat" style="color: #777;"></i>
                                        Status:</strong>
                                    <span id="patient_status" class="label label-warning"
                                        style="margin-left: 5px;">Followup</span>
                                </div>
                            </div>

                            <!-- Health Concerns Row -->
                            <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0;"
                                id="health_concern_lead_div" class="hide">
                                <strong style="color: #555;"><i class="fa fa-stethoscope" style="color: #777;"></i> Health
                                    Concern:</strong>
                                {!! Form::select('disease_id[]', $disease, null, [
                                    'class' => 'form-control select2',
                                    'id' => 'disease_id',
                                    'multiple',
                                    'style' => 'width: 100% !important;',
                                ]) !!}

                            </div>
                            <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0;"
                                id="health_concern_customer_div" class="hide">

                            </div>
                            <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0;"
                                class="source_section hide">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong style="color: #555;"><i class="fa fa-search" style="color: #777;"></i>
                                            Source:</strong>
                                        {!! Form::select('source_id', $sources, null, [
                                            'class' => 'form-control select2',
                                            'id' => 'source_id',
                                            'style' => 'width: 100% !important;',
                                            'placeholder' => 'Select Source',
                                        ]) !!}
                                    </div>
                                    <div class="col-md-6">
                                        <strong style="color: #555;"><i class="fa fa-magnifying-glass"
                                                style="color: #777;"></i>
                                            Sub-Source:</strong>
                                        {!! Form::select('sub_source_id', [], null, [
                                            'class' => 'form-control select2',
                                            'id' => 'sub_source_id',
                                            'style' => 'width: 100% !important;',
                                            'placeholder' => 'Select Sub-Source',
                                        ]) !!}
                                    </div>
                                </div>


                            </div>

                            <!-- Last Appointment Row -->
                            <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0;">
                                <strong style="color: #555;"><i class="fa fa-calendar" style="color: #777;"></i> Last
                                    Appointment:</strong>
                                <span id="patient_last_appointment" style="float: right;">2025-06-11</span>
                            </div>

                            <!-- Loyalty Row -->
                            <div style="margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0;"
                                id="loyality_div">
                                <strong style="color: #555;"><i class="fa fa-star" style="color: #777;"></i>
                                    Loyalty:</strong>
                                <span id="patient_loyalty" class="label label-success" style="float: right;">Loyal</span>
                            </div>

                            <!-- Life Stage Row -->
                            <div>
                                <strong style="color: #555;"><i class="fa fa-user"
                                        style="color: #777; margin-bottom: 10px;"></i> Life Stage:</strong>
                                {!! Form::select('life_stage', $life_stages, null, [
                                    'class' => 'form-control select2',
                                    'id' => 'life_stage',
                                    'style' => 'width: 100% !important;',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="text-center" style="margin-top: 10px;">
                        {!! Form::submit(__('messages.submit'), [
                            'class' => 'btn btn-primary',
                            'style' => 'width: 200px; height: 50px; font-size: 20px;',
                        ]) !!}
                    </div>

                    {!! Form::close() !!}
                </div>
            </div>

            <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            </div>
            <div class="modal fade call_subject_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            </div>
            <div class="modal fade call_tag_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            </div>
            <div class="modal fade schedule" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
            <div class="modal fade edit_customer_button_call_log_modal" tabindex="-1" role="dialog"
                aria-labelledby="gridSystemModalLabel"></div>
        @endcomponent

        <div class="hidden">
            @component('components.widget', ['class' => 'box-solid'])
                <table class="table table-bordered table-striped" id="call_logs_table">
                    <thead>
                        <tr>
                            <th>@lang('crm::lang.start_time')</th>
                            <th>@lang('crm::lang.agent')</th>
                            <th>@lang('crm::lang.call_type')</th>
                            <th>@lang('crm::lang.call_duration')</th>
                            <th>@lang('crm::lang.note')</th>
                            <th>@lang('crm::lang.call_subject')</th>
                            <th>@lang('crm::lang.tag')</th>
                            <th>@lang('crm::lang.campaign_name')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            @endcomponent
        </div>

        <div class="hidden">
            @component('components.widget', ['class' => 'box-solid'])
                <table class="table table-bordered table-striped" id="sms_logs_table">
                    <thead>
                        <tr>
                            <th>@lang('crm::lang.sent_time')</th>
                            <th>@lang('crm::lang.agent')</th>
                            <th>@lang('crm::lang.sms_body')</th>
                            <th>@lang('crm::lang.sms_length')</th>
                            <th>@lang('crm::lang.sms_count')</th>
                            <th>@lang('crm::lang.status')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            @endcomponent
        </div>

    </section>
@endsection

@section('javascript')
    <script src="{{ asset('modules/crm/js/crm.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        var baseUrl = '{{ url('/') }}';
        $(document).ready(function() {
            // Initialize select2 for customer dropdown
            $('#customer_id_clinic').select2({
                ajax: {
                    url: '/get/clinic/customer',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: $.map(data, function(item) {
                                return {
                                    text: item.text,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: true
                },
                minimumInputLength: 1
            });

            toggleProfileButton();
            togglelifeStage();
        });

        function togglelifeStage() {
            const contactId = $('#customer_id_clinic').val();
            if (contactId) {
                $('.life_stage_hide').show();
            } else {
                $('.life_stage_hide').hide();
            }
        }
        let subSourceId = null;
        $(document).on('change', '#customer_id_clinic', function() {
            var contact_id = $(this).val();
            if (!contact_id) {
                $('#patient_info_section').hide();
                return;
            }

            // Show patient info section
            $('#patient_info_section').show();

            // Get current date for the profile info request
            var currentDate = moment().format('YYYY-MM-DD');

            // Get patient information
            $.ajax({
                method: 'GET',
                url: '/patients/profile/info/' + contact_id + '/' + currentDate + '?requestType=call_log',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show patient info section
                        $('#patient_info_section').show();

                        $('#cus_type').text('')
                        $('#cus_type').text(response.cus_type)

                        // Update patient age
                        var patient_age = response.age ?? 0;
                        if (response.cus_type == 'Lead') {
                            $('.age_input').removeClass('hide');
                            $('.age_label').addClass('hide');
                            $('#age_input').val(patient_age); // set input value
                        } else {
                            $('#show_age').text('');
                            $('.age_input').addClass('hide');
                            $('.age_label').removeClass('hide');
                            $('#age_input').val(null); // clear input
                            $('#show_age').text(patient_age); // show value in label
                        }
                        // Reset values
                        // Reset values
                        $('#total_followups').text('0');
                        $('#pending_followups').text('0');
                        $('#total_followups_btn').attr('href', '#');
                        $('#pending_followups_link').attr('href', '#');

                        if (response.totalSchedule > 0) {
                            $('#patient_follow_up_section').removeClass('hide');

                            // Total followups
                            $('#total_followups').text(response.totalSchedule);
                            $('#total_followups_btn').prop(
                                'href',
                                baseUrl + '/crm/follow-ups?patient_value=' + response.contact_id
                            );

                            // Pending followups
                            $('#pending_followups').text(response.pendingScheduleCount ?? 0);
                            $('#pending_followups_link').prop(
                                'href',
                               baseUrl + '/crm/follow-ups?status=scheduled&patient_value=' + response.contact_id
                            );
                        }





                        let healthConcerns = [];
                        if (response.healthConcerns && response.healthConcerns.length > 0) {
                            healthConcerns = response.healthConcerns; // names only
                        } else if (response.diseases && response.diseases.length > 0) {
                            healthConcerns = response.diseases.map(d => d.id); // already IDs
                        }


                        // Convert names → IDs (if needed)
                        if (response.cus_type == 'Lead') {
                            $('#health_concern_lead_div').removeClass('hide');
                            $('#health_concern_customer_div').addClass('hide');
                            let selectedIds = [];

                            healthConcerns.forEach(nameOrId => {
                                // Try matching by value directly
                                if ($("#disease_id option[value='" + nameOrId + "']").length) {
                                    selectedIds.push(nameOrId);
                                } else {
                                    // If name given instead of ID, find option by text
                                    $("#disease_id option").each(function() {
                                        if ($(this).text().trim() === nameOrId) {
                                            selectedIds.push($(this).val());
                                        }
                                    });
                                }
                            });

                            // Clear and set values
                            $('#disease_id').val(null).trigger('change');
                            if (selectedIds.length > 0) {
                                $('#disease_id').val(selectedIds).trigger('change');
                            }
                        } else {
                            $('#health_concern_lead_div').addClass('hide');
                            $('#health_concern_customer_div').removeClass('hide');

                            // Clear old labels
                            $('#health_concern_customer_div').html('');
                            var heading = '<label>Health Concern: </label><br>';
                            $('#health_concern_customer_div').append(heading);
                            // Show labels instead of dropdown
                            healthConcerns.forEach(nameOrId => {
                                let labelText = nameOrId;

                                // If it's an ID, fetch the option text
                                let option = $("#disease_id option[value='" + nameOrId + "']");
                                if (option.length) {
                                    labelText = option.text().trim();
                                }

                                // Append label with color (you can change bg-* classes as you want)
                                $('#health_concern_customer_div').append(
                                    `<span class="badge badge-primary" style="margin:2px; padding:4px 8px;">${labelText}</span>`
                                );
                            });

                        }
                        let lastAppointment = 'N/A';
                        if (response.session) {
                            lastAppointment = response.session.start_date ? moment(response.session
                                .start_date).format('DD MMM, YYYY') : 'N/A';
                            if (response.session.doctor) {
                                lastAppointment += ' (Dr. ' + response.session.doctor + ')';
                            }
                        }
                        $('#patient_last_appointment').text(lastAppointment);

                        // Set patient status with appropriate styling
                        let statusText = response.patientType || 'N/A';
                        let statusClass = 'label-default';
                        if (statusText.toLowerCase().includes('new')) statusClass = 'label-success';
                        else if (statusText.toLowerCase().includes('follow')) statusClass =
                            'label-warning';
                        else if (statusText.toLowerCase().includes('return')) statusClass =
                            'label-primary';

                        let loyaltyStatus = 'N/A';
                        let loyaltyClass = 'label-default';
                        if (response.session) {
                            if (response.session.is_closed == 1) {
                                loyaltyStatus = 'Regular';
                                loyaltyClass = 'label-primary';
                            } else {
                                // Check if this is a follow-up within 4 months
                                if (response.patientType === 'Followup') {
                                    loyaltyStatus = 'Loyal';
                                    loyaltyClass = 'label-success';
                                } else {
                                    loyaltyStatus = 'Regular';
                                    loyaltyClass = 'label-primary';
                                }
                            }
                        }
                        if (response.cus_type != 'Lead') {
                            $('#patient_status_div').removeClass('hide')
                            $('#loyality_div').removeClass('hide')
                            $('#patient_status').text(statusText)
                                .removeClass('label-primary label-success label-warning label-default')
                                .addClass(statusClass);

                            $('#patient_loyalty').text(loyaltyStatus)
                                .removeClass('label-success label-primary label-default')
                                .addClass(loyaltyClass);
                        } else {
                            $('#patient_status_div').addClass('hide')
                            $('#loyality_div').addClass('hide')
                        }


                        // life_stage
                        if (response.contact_life_stage_id) {
                            $('#life_stage').val(response.contact_life_stage_id).trigger('change');
                        } else {
                            $('#life_stage').val(null).trigger('change'); // reset properly
                        }

                        // source_id
                        if (response.cus_type == 'Lead') {
                            $('.source_section').removeClass('hide')
                            if (response.crm_source_id) {
                                $('#source_id').val(response.crm_source_id).trigger('change');
                            } else {
                                $('#source_id').val(null).trigger('change'); // reset properly
                            }
                            if (response.sub_source_id) {
                                subSourceId = response.sub_source_id;
                            } else {
                                subSourceId = null;
                            }
                        } else {
                            $('.source_section').addClass('hide')
                        }

                    } else {
                        // If no data found, hide the info section
                        $('#patient_info_section').hide();
                        if (response.msg) {
                            toastr.error(response.msg);
                        }
                    }
                },
                error: function(xhr) {
                    $('#patient_info_section').hide();
                    toastr.error('Error loading patient information');
                    console.error(xhr.responseText);
                }
            });

            // Existing call logs functionality
            $.ajax({
                method: 'GET',
                url: '/crm/get/call/log/info/' + contact_id,
                dataType: 'json',
                success: function(result) {
                    if (result.success && Array.isArray(result.data)) {
                        const rows = result.data;
                        $('#call_logs_table').closest('.hidden').removeClass('hidden');
                        $('#call_logs_table tbody').empty();

                        rows.forEach((data) => {
                            const start_time = data.start_time || '-';
                            const duration = data.formatted_duration || '-';
                            const call_type = data.call_type || '-';
                            const created_by = data.created_user_name || '-';
                            const subject = data.subject_names || '-';
                            const tag = data.tag_names || '-';
                            const note = data.note || '-';
                            const campaign = data.campaign_name || '_';
                            const newRow = `
                            <tr>
                                <td>${start_time}</td>
                                <td>${created_by}</td>
                                <td>${call_type.charAt(0).toUpperCase() + call_type.slice(1)}</td>
                                <td>${duration}</td>
                                <td>${note}</td>
                                <td>${subject}</td>
                                <td>${tag}</td>
                                <td>${campaign}</td>
                            </tr>
                        `;
                            $('#call_logs_table tbody').append(newRow);
                        });
                    }
                }
            });

            toggleProfileButton();
            togglelifeStage();
        });

        function loadSmsLogs(contact_id) {
            $.ajax({
                method: 'GET',
                url: '/crm/sms-log-info/' + contact_id,
                dataType: 'json',
                success: function(result) {
                    if (result.success && Array.isArray(result.data)) {
                        const rows = result.data;
                        $('#sms_logs_table').closest('.hidden').removeClass('hidden');
                        $('#sms_logs_table tbody').empty();

                        rows.forEach((data) => {
                            const sent_time = data.sent_time || '-';
                            const agent = data.agent || '-';
                            const mobile = data.mobile || '-';
                            const body = data.sms_body || '-';
                            const length = data.sms_length || '-';
                            const count = data.sms_count || '-';
                            const status = data.status || '-';

                            const newRow = `
                                <tr>
                                    <td>${sent_time}</td>
                                    <td>${agent}</td>
                                    <td>${body}</td>
                                    <td>${length}</td>
                                    <td>${count}</td>
                                    <td>
                                        <span class="label label-${status.toLowerCase() === 'success' ? 'success' : 'danger'}">
                                            ${status}
                                        </span>
                                    </td>
                                </tr>
                            `;
                            $('#sms_logs_table tbody').append(newRow);
                        });
                    }
                }
            });
        }

        $('#customer_id_clinic').on('change', function() {
            let contact_id = $(this).val();
            if (contact_id) {
                loadSmsLogs(contact_id);
            }
        });

        // Rest of your existing JavaScript code...
        $(document).on('submit', 'form#call_subject_add_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            var submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> {{ __('messages.saving') }}');

            $.ajax({
                method: 'POST',
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success) {
                        $('div.call_subject_modal').modal('hide');
                        toastr.success(result.msg);
                        form[0].reset();
                        $('#call_subject_id').append($('<option>', {
                            value: result.data.id,
                            text: result.data.name
                        }).prop('selected', true));
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function() {
                    toastr.error('{{ __('messages.something_went_wrong') }}');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('{{ __('messages.save') }}');
                }
            });
        });

        $(document).on('submit', '#call_log_store_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            var submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> {{ __('messages.saving') }}');
            $.ajax({
                method: 'POST',
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success) {
                        toastr.success(result.msg);
                        window.location.href =
                            '{{ action([\Modules\Crm\Http\Controllers\CallLogController::class, 'index']) }}';
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function() {
                    toastr.error('{{ __('messages.something_went_wrong') }}');
                },
            });
        });

        $(document).on('click', '.show_patient_profile', function(e) {
            e.preventDefault();
            const contact_id = $('#customer_id_clinic').val() || '';
            if (!contact_id) {
                toastr.error('Please select a patient.');
                return;
            }

            let baseUrl = $(this).attr('href').replace(/\/0$/, ''); // remove placeholder
            const profileUrl = baseUrl + '/' + encodeURIComponent(contact_id);
            window.open(profileUrl, '_blank');
        });

        $(document).on('click', '.create_new_appointment_btn', function(e) {
            e.preventDefault();
            const contact_id = $('#customer_id_clinic').val() || '';
            const baseUrl = $(this).attr('href');
            const appointmentUrl = baseUrl + '?contact_id=' + encodeURIComponent(contact_id);
            window.open(appointmentUrl, '_blank');
        });

        $(document).on('click', '.add_new_lead_in_back', function() {
            const name = $(this).data('name');
            $('#customer_id_clinic').select2('close');
            $('.btn-add-lead').click();
            setTimeout(function() {
                if (/^\d+$/.test(name)) {
                    $('.mobile').val(name);
                }
            }, 300);
        });

        $(document).on('submit', 'form#call_tag_add_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            var submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> {{ __('messages.saving') }}');

            $.ajax({
                method: 'POST',
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success) {
                        $('div.call_tag_modal').modal('hide');
                        toastr.success(result.msg);
                        form[0].reset();
                        $('#call_tag_id').append($('<option>', {
                            value: result.data.id,
                            text: result.data.value
                        }).prop('selected', true));
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function() {
                    toastr.error('{{ __('messages.something_went_wrong') }}');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('{{ __('messages.save') }}');
                }
            });
        });

        $(document).on('click', '.add_new_patients', function() {
            const name = $(this).data('name');
            $('#customer_id_clinic').select2('close');
            $('.btn-add-lead').click();

            // Wait for modal content to be inserted
            $(document).one('shown.bs.modal', '.contact_modal', function() {
                if (/^\d+$/.test(name)) {
                    $('.contact_modal').find('.mobile').val(name);
                }
            });
        });

        function toggleProfileButton() {
            const contactId = $('#customer_id_clinic').val();
            if (contactId) {
                $('.create_new_appointment_btn').show();
                $('.create_follow_up_btn').show();
            } else {
                $('.create_new_appointment_btn').hide();
                $('.create_follow_up_btn').hide();
            }
            $('.show_patient_profile').hide();
        }

        $(document).on('click', '.create_follow_up_btn', function() {
            const contact_id = $('#customer_id_clinic').val() || '';
            if (!contact_id) {
                toastr.error('Please select a patient.');
                return;
            }

            const url = $(this).data('href') + '?contact_id=' + encodeURIComponent(contact_id);

            $.ajax({
                method: 'GET',
                url: url,
                dataType: 'html',
                success: function(result) {
                    $('.schedule')
                        .html(result)
                        .modal('show');

                    $('.datetimepicker').datetimepicker({
                        format: moment_date_format + ' ' + moment_time_format,
                        ignoreReadonly: true,
                    });

                    $('.select2').select2();
                },
                error: function() {
                    toastr.error('Something went wrong');
                }
            });
        });

        $(document).on('submit', '#patient_name_update_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            var submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> {{ __('messages.saving') }}');

            $.ajax({
                method: 'POST',
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success) {
                        $('.edit_customer_button_call_log_modal').modal('hide');
                        $('#customer_id_clinic').append($('<option>', {
                            value: result.data.id,
                            text: result.data.name
                        }).prop('selected', true));
                        toastr.success(result.msg);
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.msg || '{{ __('messages.something_went_wrong') }}');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('{{ __('messages.save') }}');
                }
            });
        });
        $(document).on('change', '#source_id', function() {
            var source_id = $(this).val();
            if (!source_id) {
                $('#sub_source_id').empty();
                return;
            }
            getSubSource(source_id);
        })

        function getSubSource(val) {
            $.ajax({
                method: 'GET',
                url: '/survey/get-sub-source/' + val,
                dataType: 'json',
                success: function(result) {
                    if (result.success) {
                        $('#sub_source_id').empty();
                        $('#sub_source_id').append($('<option>', {
                            value: '',
                            text: 'Select Sub Source'
                        }));
                        $.each(result.data, function(index, value) {
                            $('#sub_source_id').append($('<option>', {
                                value: value.id,
                                text: value.name
                            }));
                        });
                        $('#sub_source_id').trigger('change');
                        if (subSourceId) {
                            $('#sub_source_id').val(subSourceId).trigger('change');
                        }
                    } else {
                        toastr.error(result.msg || '{{ __('messages.something_went_wrong') }}');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON.msg || '{{ __('messages.something_went_wrong') }}');
                }
            });
        }
    </script>
@endsection
