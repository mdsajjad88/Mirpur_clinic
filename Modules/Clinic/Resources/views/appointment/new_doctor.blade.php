@extends('clinic::layouts.app2')
@section('title', __('clinic::lang.set_appointment'))
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col custom-row mt-2 doctor-heading">
                <div class="text-left">
                    <a href="#">
                        <i class="fas fa-list"></i>&nbsp;
                    </a>
                    <strong>@lang('clinic::lang.set_appointment')
                        @if ($app_doctor)
                            {{ $app_doctor->first_name ?? '' }} {{ $app_doctor->last_name ?? '' }}
                        @endif
                    </strong>
                </div>
                {{-- <div class="text-right">
                    <a href="{{ route('appointment.confirm') }}" class="btn make_app">Confirmation View</a>
                </div> --}}
            </div>
        </div>
        @php
            $url = action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'store']);
            $form_id = 'create_new_doctor_appointment';
        @endphp
        @component('components.widget')
            <div class="row">
                {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}
                @csrf

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('request_date', __('clinic::lang.month&year') . ':') !!}<span class="star">*</span>
                        @php
                            $today = \Carbon\Carbon::today()->format('Y-m-d');
                            $tomorrow = \Carbon\Carbon::tomorrow()->format('Y-m-d');
                            $user = auth()->user();

                            $dateAttributes = [
                                'class' => 'form-control',
                                'required' => 'required',
                                'id' => 'request_date',
                            ];

                            // Default value today
                            $requestDateValue = $today;

                            if (!$user->can('admin')) {
                                if ($user->hasRole('Cashier#' . $business_id)) {
                                    $dateAttributes['min'] = $today;
                                    $dateAttributes['max'] = '';
                                    $requestDateValue = $today; // cashier gets today
                                } else {
                                    $dateAttributes['min'] = $today;
                                    $requestDateValue = ''; // others get tomorrow
                                }
                            }
                        @endphp

                        {!! Form::date('request_date', $requestDateValue, $dateAttributes) !!}

                    </div>
                    <div class="form-group">
                        {!! Form::label('comments', __('clinic::lang.appointment_notes') . ':') !!}
                        {!! Form::textarea('comments', null, [
                            'class' => 'form-control',
                            'rows' => 2,
                            'placeholder' => __('clinic::lang.appointment_notes'),
                        ]) !!}
                    </div>
                    <div class="form-group hide" id="patient_details">
                        <div class="row">
                            <div class="col-md-3">
                                Age: <span class="patient-age"></span>
                            </div>
                            <div class="col-md-9">
                                Disease: <span id="patient_health_concern"></span>
                            </div>
                        </div>
                    </div>
                        <table style="width:100%" class="table table-bordered table-striped payment-details-table">
                            <thead>
                                <tr>
                                    <th>Amount </th>
                                    <th>Method</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody class="payment-details-body">

                            </tbody>
                            <tfoot class="payment-details-footer">

                            </tfoot>
                        </table>
                        <table style="width:100%" class="table table-bordered table-striped subscription-details-table">
                            <thead>
                                <tr>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Used Consultancy</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody class="subscription-details-body">

                            </tbody>
                        </table>

                </div>

                <div class="col-md-8 hide">
                    <div class="row">
                        @if (empty($only) || in_array('doctor_profile_id', $only))
                            <div class="col-md-4">
                                {!! Form::label('doctor_profile_id', __('clinic::lang.chamber') . ':') !!}<span class="star">*</span>
                                {!! Form::select('doctor_profile_id', [], null, [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('clinic::lang.doctor_name'),
                                    'required' => 'required',
                                    'style' => 'width:100% !important;',
                                ]) !!}
                            </div>
                        @endif

                        @if (empty($only) || in_array('appointment_media', $only))
                            <div class="col-md-4">
                                {!! Form::label('appointment_media', __('clinic::lang.media') . ':') !!}
                                {!! Form::select(
                                    'appointment_media',
                                    [1 => __('clinic::lang.in_person_visit'), 2 => __('clinic::lang.online'), 3 => __('clinic::lang.report_follow_up')],
                                    null,
                                    [
                                        'class' => 'form-control',
                                    ],
                                ) !!}
                            </div>
                        @endif

                        @if (empty($only) || in_array('is_fasting_required', $only))
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label style="margin-top: 25px;">
                                        {!!Form::checkbox('is_fasting_required', 1, false, ['class' => 'input-icheck']) !!}
                                        {{ __( 'clinic::lang.is_fasting_required' )}}
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="row" style="margin-top: 15px;">
                        @if (empty($only) || in_array('patient_contact_id', $only))
                            <div class="col-md-4">
                                <div class="form-group">
                                    {!! Form::label('patient_contact_id', __('clinic::lang.name') . ':') !!}<span class="star">*</span>
                                    <div class="input-group">
                                        {!! Form::select(
                                            'patient_contact_id',
                                            $patient_profiles->mapWithKeys(fn($profile) => [$profile->patient_contact_id => $profile->first_name]),
                                            null,
                                            [
                                                'class' => 'form-control select2 patient_contact_id',
                                                'placeholder' => __('clinic::lang.select_patient'),
                                                'required' => 'required',
                                                'id' => 'customer_id_clinic',
                                                'style' => 'width:100% !important;',
                                            ],
                                        ) !!}
                                        @can('appointment.add_customer')
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default bg-white btn-flat add_new_patients"
                                                data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                            </button>
                                        </span>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if (empty($only) || in_array('mobile', $only))
                            <div class="col-md-4">
                                {!! Form::label('mobile', __('clinic::lang.mobile') . ':') !!}<span class="star">*</span>
                                {!! Form::number('mobile_no', null, ['class' => 'form-control mobile mobile_no_is', 'readonly']) !!}
                            </div>
                        @endif

                        @if (empty($only) || in_array('patient_type', $only))
                            <div class="col-md-4">
                                {!! Form::label('patient_type', __('clinic::lang.type') . ':') !!}
                                <select name="patient_type" id="patient_type" class="form-control">
                                    <option value="">Select Patient Type</option>
                                    <option value="New">New</option>
                                    <option value="Followup">Follow Up</option>
                                    <option value="Old" disabled>Old</option>
                                </select>
                                {!! Form::hidden('hidden_patient_type', null, ['id' => 'hidden_patient_type']) !!}
                            </div>
                        @endif

                    </div>

                    <div class="row mt-1">
                        <div class="col">
                            <table class="table text-center table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th rowspan="2">@lang('clinic::lang.time')</th>
                                        <th colspan="2">@lang('clinic::lang.availability')</th>
                                        <th colspan="3">@lang('clinic::lang.status')</th>
                                    </tr>
                                    <tr>
                                        <th colspan="2"></th>
                                        <th>@lang('clinic::lang.capacity')</th>
                                        <th>@lang('clinic::lang.reserve')</th>
                                        <th>@lang('clinic::lang.confirmed')</th>
                                    </tr>
                                </thead>
                                <tbody id="slotTableBody">

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-1">
                        <div class="col-md-10"></div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-success">@lang('clinic::lang.submit')</button>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            <div class="modal fade patient_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
                @include('clinic::patient.patients.partials.add_patient')
            </div>
            <div class="modal fade session_details_modal_div" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            </div>
        @endcomponent

    </div>

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $("#disease").select2()
            @if(!empty($contact))
                const contactOption = new Option(
                    '{{ addslashes($contact->name) }} ({{ addslashes($contact->mobile) }})',
                    '{{ $contact->id }}',
                    true,
                    true
                );
                $('#customer_id_clinic').append(contactOption).trigger('change');
            @endif

            var date = $('#request_date').val();
            var patient_id = $('#customer_id_clinic').val();
            if (patient_id && date) {
                getPatientData(patient_id, date);
            };
            $('.patient_contact_id').change(function() {
                var patient_id = $(this).val();
                var date = $('#request_date').val();
                getPatientData(patient_id, date);
            });

            $('#patient_type').on('change', function () {
                const selectedVal = $(this).val();
                $('#hidden_patient_type').val(selectedVal);
            });
            function getPatientData(patient_id, date) {
                const url = `/patients/profile/info/${patient_id}/${date}`;
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            $('.mobile_no_is').val(response.mobile);
                            // Clear existing rows
                        $('.payment-details-body').empty();
                        $('.payment-details-footer').empty();
                        $('.subscription-details-body').empty();
                        if (response.patientType) {
                            const patientType = response.patientType;

                            // Temporarily enable Old if needed
                            $('#patient_type option[value="' + patientType + '"]').prop('disabled', false);

                            // Set value in dropdown and hidden field
                            $('#patient_type').val(patientType).trigger('change');
                            $('#hidden_patient_type').val(patientType);

                            // If patientType is not 'New', disable the dropdown
                            if (patientType !== 'New') {
                                $('#patient_type').prop('disabled', true);
                            } else {
                                $('#patient_type').prop('disabled', false);
                            }

                            // Re-disable Old option so it cannot be manually selected
                            $('#patient_type option[value="Old"]').prop('disabled', true);
                        }
                        $('#patient_details').removeClass('hide');
                        $('.patient-age').text(response.age);
                        let healthConcerns = [];
                        if (response.healthConcerns && response.healthConcerns.length > 0) {
                            healthConcerns = response.healthConcerns;
                        } else if (response.diseases && response.diseases.length > 0) {
                            healthConcerns = response.diseases.map(d => d.name);
                        }
                        
                        let healthConcernsHtml = healthConcerns.length > 0 
                            ? healthConcerns.map(concern => `<span class="label label-info" style="margin-right: 3px; display: inline-block; margin-bottom: 3px;">${concern}</span>`).join('')
                            : '<span class="label label-default">N/A</span>';
                        $('#patient_health_concern').html(healthConcernsHtml);
                        // Append new rows if payment exists
                        if (response.payment && response.payment.length > 0) {
                            $.each(response.payment, function(index, payment) {
                                let method = payment.method == 'custom_pay_1' ? 'Bkash' : payment.method;
                                var row = `
                                    <tr>
                                        <td>${payment.amount ? Number(payment.amount).toFixed(2) : 'N/A'}</td>
                                        <td>${method || 'N/A'}</td>
                                        <td>${payment.paid_on || 'N/A'}</td>
                                    </tr>
                                `;
                                $('.payment-details-body').append(row);
                            });
                            if (response.session !== null && response.session !== undefined && response.session !== '') {
                                var rowFooter = `
                                    <tr>
                                        <td colspan="2"><strong>Total Amount:</strong></td>
                                        <td><strong>${response.total ? Number(response.total).toFixed(2) : 'N/A'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Total Paid:</strong></td>
                                        <td><strong>${response.paid ? Number(response.paid).toFixed(2) : 'N/A'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Total Due:</strong></td>
                                        <td><strong>${response.due ? Number(response.due).toFixed(2) : 'N/A'}</strong></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2"><strong>Subscription Status:</strong></td>
                                        <td><strong>${response.is_closed}</strong></td>
                                    </tr>
                                `;
                                $('.payment-details-footer').append(rowFooter);
                            }
                            if(response.all_sessions !== null && response.all_sessions !== undefined && response.all_sessions !== '') {
                                $.each(response.all_sessions, function(index, session) {
                                    var row = `
                                        <tr data-session-id="${session.id}">
                                            <td>${session.start_date ? moment(session.start_date).format('YYYY-MM-DD') : 'N/A'}</td>
                                            <td>${session.end_date ? moment(session.end_date).format('YYYY-MM-DD') : 'N/A'}</td>
                                            <td>${session.visited_count || 0}</td>
                                            <td>${session.remaining_visit || 0}</td>
                                            <td>${session.is_closed == 1 ? 'Closed' : 'Running'}</td>
                                        </tr>
                                    `;
                                    $('.subscription-details-body').append(row);
                                });
                            }                        
                        } else {
                            $('.payment-details-body').append(`
                                <tr>
                                    <td colspan="3">No payments found</td>
                                </tr>
                            `);
                            $('.payment-details-footer').empty();
                        }
                        } else {
                            toastr.error(response.msg);
                        }
                    }
                })
            };

            function fetchAndPopulateSlots() {
                var doctor_id = $('#doctor_profile_id').val();
                var date = $('#request_date').val();
                var serial = {{ $serialNo->id ?? 'null' }}; // Check if a date is selected
                if (!date) {
                    toastr.error('Please select a date.');
                    return;
                }
                if (!doctor_id) {
                    // toastr.error('Please select a doctor.');
                    return;
                }

                $.ajax({
                    url: '/doctor/slotInfo/' + doctor_id + "/" + date + "/" + serial,
                    type: 'GET',
                    success: function(response) {
                        $('#slotTableBody').empty();

                        if (response.success) {
                            var slotData = JSON.parse(response.data.slots);
                            let globalSerialNumber = 1;
                            var serialNo = response.serialNo;
                            if (slotData[date]) {
                                slotData[date].forEach(function(slot, index) {
                                    let radioButtons = '';
                                    // var request_slot = JSON.parse($('#request_slot').val());

                                    for (let i = 1; i <= slot.capacity; i++) {
                                        let isReserved = false;
                                        let isChecked = false;
                                        response.serial.forEach(function(serialRecord) {
                                            if (serialRecord.sl_without_prefix ==
                                                globalSerialNumber) {
                                                isReserved = true;
                                                if (globalSerialNumber == serialNo) {
                                                    isChecked = true;
                                                    isReserved = false;

                                                }
                                            }
                                        });

                                        let disabledAttr = isReserved ? 'disabled' : '';
                                        let checkedAttr = isChecked ? 'checked' : '';
                                        let displayStyle = isReserved ? 'display:none;' : '';

                                        radioButtons += `
                                            <label style="${displayStyle}; margin-right: 10px;">
                                                <div class="btn-group">
                                                    <button type="button" class="btn btn-default slot-btn" data-index="${index}" data-serial="${globalSerialNumber}" style="width: 40px; height: 35px; border-radius: 6px;">
                                                        <input type="radio" name="slot" value="${index}-${i}" class="slot-radio" required ${disabledAttr} ${checkedAttr} style="display:none;">
                                                        ${globalSerialNumber}
                                                    </button>
                                                    <input type="hidden" name="serialNumber[${index}][${i}]" value="${globalSerialNumber}">
                                                </div>
                                            </label>
                                        `;

                                        globalSerialNumber++;
                                    }

                                    let row = `
                                        <tr>
                                            <td>${slot.start} - ${slot.end}</td>
                                            <td colspan="2">${radioButtons}</td>
                                            <td>${slot.capacity}</td>
                                            <td>${slot.reserved}</td>
                                            <td>${slot.booked}</td>
                                        </tr>
                                    `;
                                    $('#slotTableBody').append(row);
                                });

                                $(".slot-btn").each(function() {
                                    if ($(this).attr("data-serial") == serialNo) {
                                        $(this).trigger("click");
                                    }
                                });

                                // Ensure only one radio can be checked at a time
                                $('.slot-radio').on('change', function() {
                                    $('.slot-radio').not(this).prop('checked', false);
                                });

                            } else {
                                toastr.warning('No slots available for this date.');
                            }
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('An error occurred: ' + error);
                    }
                });
            }

            // Trigger slot population when doctor or date changes
            $('#doctor_profile_id, #request_date').on('change', fetchAndPopulateSlots);

            // Trigger slot population on page load if doctor and date are already selected
            if ($('#doctor_profile_id').val() && $('#request_date').val()) {
                fetchAndPopulateSlots();
            }
        });
        $(document).on('click', '.add_new_patients', function() {
            $('#customer_id_clinic').select2('close');
            var name = $(this).data('name');
            $('.patient_add_modal').find('input#name').val(name);
            $('.patient_add_modal')
                .find('select#contact_type')
                .val('customer')
                .closest('div.contact_type_div')
                .addClass('hide');
            $('.patient_add_modal').modal('show');
        });
        $(document).on('click', '.more_btn', function() {
            $("div").find('.add_more_info_customer').toggleClass('hide');
        });
        $(document).ready(function() {
            $(document).on("click", ".slot-btn", function() {
                // Remove 'btn-success' class from all buttons
                $(".slot-btn").removeClass("btn-success").addClass("btn-default");

                // Add 'btn-success' to the clicked button
                $(this).removeClass("btn-default").addClass("btn-success");

                // Check the corresponding hidden radio button
                $(this).find("input").prop("checked", true);
            });
        });



        $(document).ready(function() {
            const formSelector = '#create_new_doctor_appointment';
            // First bind native submit with preventDefault
            $(formSelector).on('submit', function(e) {
                e.preventDefault(); // prevent default form submission
            });
            // Then initialize jQuery Validation
            $(formSelector).validate({
                rules: {
                    request_date: {
                        required: true
                    },
                    doctor_profile_id: {
                        required: true
                    },
                    patient_profile_id: {
                        required: true
                    },

                },
                messages: {
                    request_date: {
                        required: 'Please select a date'
                    },
                    doctor_profile_id: {
                        required: 'Please select a doctor'
                    },
                    patient_profile_id: {
                        required: 'Please select a patient'
                    },
                },
                submitHandler: function(form) {
                    // form is valid, continue with AJAX
                    var data = $(form).serialize();
                    var selectedSlot = $('input[name="slot"]:checked').val();
                    if (!selectedSlot) {
                        toastr.error('Please select a slot first');
                        return false;
                    }

                    var submitButton = $(form).find('button[type="submit"]');
                    submitButton.prop('disabled', true).text('Processing...');

                    $.ajax({
                        method: 'POST',
                        url: $(form).attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg +
                                    ' appointment no: ' + result
                                    .appointment_number);
                                $(form)[0].reset();

                                window.location.href = result.redirect_url;
                            } else if (result.success == false) {
                                toastr.error(result.msg);
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                var errors = xhr.responseJSON.errors;
                                $.each(errors, function(field, messages) {
                                    toastr.error(messages[0]);
                                });
                            } else {
                                toastr.error('An error occurred: ' + xhr
                                    .statusText);
                            }
                        },
                        complete: function() {
                            submitButton.prop('disabled', false).text('Submit');
                        }
                    });
                },
                invalidHandler: function() {
                    toastr.error(LANG.some_error_in_input_field);
                },
            });
        });

        function getDoctorsByDate(date) {
            $.ajax({
                url: '/date-wise-get-doctor/' + date +'?type={{ $doctorType }}',
                type: 'GET',
                success: function(response) {
                    if (response.success == true) {
                        var options =
                            '<option value="">-- Select Doctor --</option>'; // Add label here first
                        $.each(response.data, function(id, name) {
                            options += '<option value="' + id + '">' + name +
                                '</option>';
                        });
                        $('#doctor_profile_id').html(options);
                    }

                },
                error: function(xhr, status, error) {
                    toastr.error('An error occurred: ' + error);
                }
            });
        }

        $(document).ready(function() {
            var date = $('#request_date').val();
            if (date) {
                getDoctorsByDate(date);
            }
            $('#request_date').on('change', function() {
                var date = $(this).val();
                getDoctorsByDate(date);
                hideOrShow();
            });
        });

        function hideOrShow() {
            var date = $('#request_date').val();
            if (date) {
                $('.col-md-8').removeClass('hide');
            } else {
                $('.col-md-8').addClass('hide');
            }

        }
        hideOrShow();


        $(document).on('click', '.subscription-details-body tr', function () {
            var session_id = $(this).data('session-id');
            $.ajax({
                url: '/show/session/details/' + session_id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('.session_details_modal_div').html(response.html);
                        $('.session_details_modal_div').modal('show');
                    } else {
                        toastr.error(response.msg);
                    }
                },
                error: function() {
                    toastr.error('Could not load session details.');
                }
            });
        });

    </script>

@endsection
