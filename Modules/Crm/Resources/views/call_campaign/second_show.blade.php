@extends('clinic::layouts.app2')

@section('title', $campaign->name)
@section('content')
    @include('crm::layouts.nav')
    <section class="content">
        <div class="row">
            <div class="col-md-5">
                @component('components.widget', ['class' => 'box-primary', 'title' => $campaign->name])
                    <p><strong>@lang('Status'):</strong> <span
                            class="label label-{{ $campaign->status == 'active' ? 'success' : ($campaign->status == 'completed' ? 'primary' : 'default') }}">{{ ucfirst($campaign->status) }}</span>
                    </p>
                    <p><strong>@lang('Survey Type'):</strong> {{ $campaign->surveyType->name ?? '' }}</p>
                    <p><strong>@lang('Description'):</strong> {{ $campaign->description ?? 'N/A' }}</p>
                    <p><strong>@lang('Period'):</strong>
                        {{ $campaign->start_date }}
                        @if ($campaign->end_date)
                            - {{ $campaign->end_date }}
                        @endif
                    </p>
                    <p><strong>@lang('Progress'):</strong>
                        {{ $campaign->completed_count }} / {{ $campaign->target_count }}
                        ({{ round(($campaign->completed_count / max(1, $campaign->target_count)) * 100) }}%)
                    </p>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar"
                            style="width: {{ round(($campaign->completed_count / max(1, $campaign->target_count)) * 100) }}%;"
                            aria-valuenow="{{ round(($campaign->completed_count / max(1, $campaign->target_count)) * 100) }}"
                            aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-7">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Campaign Contacts')])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="campaign_contacts_table">
                            <thead>
                                <tr>
                                    <th>@lang('Contact Name')</th>
                                    <th>Updated At</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Assigned To')</th>
                                    <th>@lang('Called At')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
        <div class="row" id="ratings_and_call_logs_area">
            <div class="col-md-5">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Call Logs')])
                    <div class="hidden">
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
                    </div>
                @endcomponent
            </div>
            <div class="col-md-7">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Survey Feedback')])
                    <div id="call_campaign_feedback">
                        <!-- Content will be loaded via AJAX -->
                    </div>
                @endcomponent
            </div>
        </div>
        <div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade schedule" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var campaign_id = "{{ $campaign->id }}";
            var campaignContactsTable = $('#campaign_contacts_table').DataTable({
                processing: true,
                serverSide: true,
                // dom: 'Bfrtip',
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                ajax: {
                    url: "{{ action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'show'], [$campaign->id]) }}",
                    data: function(d) {
                        // Add any additional filters here
                    }
                },
                aaSorting: ['1', 'desc'],
                columns: [{
                        data: 'contact_name',
                        name: 'contacts.name'
                    },
                    {
                        data: 'updated_at',
                        name: 'campaign_contacts.updated_at',
                        visible: false
                    },
                    {
                        data: 'contact_type',
                        name: 'contacts.type'
                    },
                    {
                        data: 'status',
                        name: 'campaign_contacts.status'
                    },
                    {
                        data: 'assigned_to_name',
                        name: 'users.first_name'
                    },
                    {
                        data: 'called_at',
                        name: 'campaign_contacts.called_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Start call
            $(document).on('click', '.start-call', function(e) {
                e.preventDefault();

                var contactId = $(this).data('contact-id');
                var campaignId = $(this).data('campaign-id');

                $.ajax({
                    url: "{{ action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'startCall']) }}",
                    method: 'POST',
                    data: {
                        contact_id: contactId,
                        campaign_id: campaignId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (typeof response === 'object' && response.success === false) {
                            toastr.error(response.msg);
                        } else {
                            $('#call_campaign_feedback').html(response);
                            $('html, body').animate({
                                scrollTop: $("#feedback_form_content").offset().top
                            }, 500);
                        }
                        campaignContactsTable.ajax.reload(null, false);
                        $('#call_logs_table').closest('.hidden').removeClass('hidden');
                        showCallLogs(contactId);
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.msg || __(
                            'messages.something_went_wrong'));
                    }
                });
            });


            $(document).on('click', '.create_new_appointment_btn', function(e) {
                e.preventDefault();
                const contact_id = $('#customer_contact_id').val() || '';
                const baseUrl = $(this).attr('href');
                const appointmentUrl = baseUrl + '?contact_id=' + encodeURIComponent(contact_id);
                window.open(appointmentUrl, '_blank');
            });

            // Add this new event handler for the follow-up button
            $(document).on('click', '.create_follow_up_btn', function() {
                const contact_id = $('#customer_contact_id').val() || '';
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

                        // Initialize datetimepicker if needed
                        $('.datetimepicker').datetimepicker({
                            format: moment_date_format + ' ' + moment_time_format,
                            ignoreReadonly: true,
                        });

                        // Initialize select2 if needed
                        $('.select2').select2();
                    },
                    error: function() {
                        toastr.error('Something went wrong');
                    }
                });
            });


            function showCallLogs(contact_id) {
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
                                const campaign = data.campaign_name || '-';
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
            }

            $(document).on('submit', '#feedback_store_form_in_call_center_2', function(e) {
                e.preventDefault();

                var form = $(this);
                var submitButton = form.find('button[type="submit"]');
                var isValid = true;
                var callStatus = $('select[name="call_status"]').val();

                // Clear previous errors
                $('.validation-error').removeClass('validation-error');

                if (callStatus === 'Received') {
                    // Validate star ratings
                    $('.star-rating').each(function() {
                        var $container = $(this).closest('.form-group');
                        var hasChecked = $(this).find('.star-rating-input:checked').length > 0;
                        var isNA = $(this).find('.na-checkbox:checked').length > 0;

                        if (!hasChecked && !isNA) {
                            isValid = false;
                            $container.find('.question-label').addClass('validation-error');
                        }
                    });

                    // Validate checkboxes
                    $('input[type="checkbox"][name*="question_"]:not(.na-checkbox)').each(function() {
                        var name = $(this).attr('name');
                        if (name && name.includes('[]')) {
                            var baseName = name.replace(/\[\]$/, '');
                            var $formGroup = $(this).closest('.form-group');
                            var isAnyChecked = $('input[name="' + name + '"]:checked').length > 0;
                            var isNA = $formGroup.find('.na-checkbox:checked').length > 0;

                            if (!isAnyChecked && !isNA && !$formGroup.find('.validation-error')
                                .length) {
                                isValid = false;
                                $formGroup.find('.question-label').addClass('validation-error');
                            }
                        }
                    });

                    // Validate radio buttons
                    $('input[type="radio"][name^="question_"]').each(function() {
                        var name = $(this).attr('name');
                        var $formGroup = $(this).closest('.form-group');
                        var isAnyChecked = $('input[name="' + name + '"]:checked').length > 0;
                        var isNA = $formGroup.find('.na-checkbox:checked').length > 0;

                        if (!isAnyChecked && !isNA && !$formGroup.find('.validation-error')
                            .length) {
                            isValid = false;
                            $formGroup.find('.question-label').addClass('validation-error');
                        }
                    });

                    // Validate textareas
                    $('textarea[name^="question_"]').each(function() {
                        var $formGroup = $(this).closest('.form-group');
                        var isNA = $formGroup.find('.na-checkbox:checked').length > 0;

                        if ($(this).val().trim() === '' && !isNA && !$formGroup.find(
                                '.validation-error').length) {
                            isValid = false;
                            $formGroup.find('.question-label').addClass('validation-error');
                        }
                    });
                }

                if (!isValid) {
                    toastr.error('Please answer all required questions or mark N/A where applicable');
                    submitButton.prop('disabled', false).text('Submit');
                    return false;
                }

                var data = $(this).serializeArray();
                data.push({
                    name: 'campaign_id',
                    value: "{{ $campaign->id }}"
                });
                data.push({
                    name: 'contact_id',
                    value: $('#contact_id').val()
                });
                data.push({
                    name: 'status',
                    value: $('#call_status').val() == 'Received' ? 'completed' : $('#call_status')
                        .val()
                });
                $.ajax({
                    method: 'POST',
                    url: form.attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button(submitButton);
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.msg);
                            campaignContactsTable.ajax.reload();
                            $('#call_campaign_feedback').empty();
                            $('#call_logs_table tbody').empty();

                        } else {
                            toastr.error(response.msg);
                        }
                        submitButton.prop('disabled', false).text('Submit');
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.responseJSON?.message || status;
                        toastr.error(errorMessage);
                        submitButton.prop('disabled', false).text('Submit');
                        form.reset[0]
                    },
                    complete: function() {
                        submitButton.prop('disabled', false).text('Submit');
                    }
                });
            });
            var dummyName = '';
            $(document).on('click', '.select_patient', function() {
                dummyName = '';
                var mobile = $(this).data('mobile');
                var name = $(this).data('name');
                dummyName = name;
                assignPatient(mobile);
            });

        });
    </script>
@endsection
