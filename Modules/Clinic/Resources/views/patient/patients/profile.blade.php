@extends('clinic::layouts.app2')

@section('title', __('Patient Profile'))

@section('content')
    <style>
        .patient-heading {
            background: #D7D7FB;
            margin: 2px;
            padding: 10px;
        }

        .fa-user-tie {
            font-size: 100px;
        }

        .fa-user-plus {
            font-size: 50px;
        }

        .patient_details {
            margin: 3px;
        }

        .subscribe,
        .deactive {
            background: #FA2364;
            color: white;
        }

        .deactive:hover {
            background: #FA2364;
            color: white;
        }

        .subscribe:hover {
            background: #FA2364;
            color: white;
        }

        .appointment,
        .f-password {
            background: #626EE2;
            color: white;
        }

        .f-password:hover {
            color: white;
        }

        .appointment:hover {
            color: white;
        }

        .nav-tabs .nav-link {
            font-size: 16px;
            /* Adjust this value as needed */
        }

        .dateTime {
            min-width: 88px;
        }

        .mt-2 {
            margin-top: 20px;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col custom-row patient-heading">
                <div class="text-left">
                    <a href="{{ action([\Modules\Clinic\Http\Controllers\PatientController::class, 'index']) }}">
                        <i class="fas fa-backward"></i>
                    </a>
                    <strong>@lang('clinic::lang.patient_info')</strong>
                </div>
            </div>
        </div>

        <div class="row patient_details">
            <div class="col-12">
                @component('components.widget', ['class' => 'box-success'])
                    @slot('tool')
                        <div class="row">
                            <div class="col-md-5">
                                <div class="row">
                                    <div class="col-md-2">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="col-md-10">
                                        <strong>Name: {{ $patient->first_name ?? '' }} {{ $patient->last_name ?? '' }}</strong><br>
                                        <strong>Patient ID: ########</strong><br>
                                        <strong>Gender:{{ $patient->gender ?? '' }}</strong><br>
                                        <strong>Age:{{ $patient->age ?? '' }}</strong><br>
                                        <strong>E-mail:{{ $patient->email ?? '' }}</strong><br>
                                        <strong>Phone:{{ $patient->mobile ?? '' }}</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-7 text-right">
                                <div class="row">
                                    <div class="col">
                                        <a href="" class="btn subscribe" id="patient_subscription">Subscribe</a>
                                        <a href="" class="btn appointment"><i class="fa fa-plus"></i> Make an Appointment</a>
                                        <a href="" class="btn deactive"><i class="fas fa-exclamation"></i> Deactive</a>
                                        <a href="" class="btn f-password">Forgotten Password?</a>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col">
                                        <button type="button" class="btn btn-flat btn-primary btn-modal"
                                            data-href="{{ action([\Modules\Clinic\Http\Controllers\PatientProfileController::class, 'generateMemberShipCard'],[$patient->patient_contact_id]) }}"
                                            data-container=".membership_card_modal">
                                            <i class="fa fa-plus"></i> Card
                                        </button>

                                        <a href="#" class="btn btn-info" id="patient_details">View Details</a>

                                        <button type="button" class="btn btn-success btn-flat btn-modal"
                                            data-href="{{ action([\Modules\Clinic\Http\Controllers\PatientController::class, 'edit'], [$patient->patient_contact_id ?? 0], ['quick_add' => true]) }}"
                                            data-container=".view_modal">
                                            Update Profile Info
                                        </button>
                                    </div>
                                </div>


                            </div>
                        </div>

                        <div class="row">
                            <div class="col">
                                <ul class="nav nav-tabs" id="patientTab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link" id="patient_dash-tab" data-toggle="tab" href="#patient_dash"
                                            role="tab" aria-controls="patient_dash" aria-selected="true">
                                            <i class="fas fa-user"></i> @lang('clinic::lang.patient_dash')
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="patient_profile-tab" data-toggle="tab" href="#patient_profile"
                                            role="tab" aria-controls="patient_profile" aria-selected="false">
                                            <i class="fas fa-user-clock"></i> @lang('clinic::lang.patient_profile')
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="file_form-tab" data-toggle="tab" href="#file_form" role="tab"
                                            aria-controls="file_form" aria-selected="false">
                                            <i class="fas fa-file-import"></i> @lang('clinic::lang.file_form')
                                        </a>
                                    </li>
                                    {{-- <li class="nav-item">
                                        <a class="nav-link" id="appointment-tab" data-toggle="tab" href="#appointment"
                                            role="tab" aria-controls="appointment" aria-selected="false">
                                            <i class="fas fa-calendar-check"></i> @lang('clinic::lang.appointment')
                                        </a>
                                    </li> --}}
                                    <li class="nav-item">
                                        <a class="nav-link" id="prescriptions-tab" data-toggle="tab" href="#prescriptions"
                                            role="tab" aria-controls="prescriptions" aria-selected="false">
                                            <i class="fas fa-envelope-open-text"></i> @lang('clinic::lang.prescriptions')
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="patient_clinic_led-tab" data-toggle="tab" href="#patient_clinic_led"
                                            role="tab" aria-controls="patient_clinic_led"
                                            data-url="{{ route('patient.patient_clinic_led') }}" aria-selected="false">
                                            <i class="fas fa-folder-open"></i> @lang('clinic::lang.patient_clinic_led')
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="patient_store_led-tab" data-toggle="tab" href="#patient_store_led"
                                            role="tab" aria-controls="patient_store_led" aria-selected="false">
                                            <i class="fas fa-folder"></i> @lang('clinic::lang.patient_store_led')
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="subscriptions-tab" data-toggle="tab" href="#subscriptions"
                                            role="tab" aria-controls="subscriptions" aria-selected="false">
                                            <i class="fas fa-thumbs-up"></i> @lang('clinic::lang.subscriptions')
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="add_discount-tab" data-toggle="tab" href="#add_discount"
                                            role="tab" aria-controls="add_discount" aria-selected="false">
                                            <i class="fas fa-percent"></i> @lang('clinic::lang.add_discount')
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="call_histories-tab" data-toggle="tab" href="#call_histories"
                                            role="tab" aria-controls="call_histories" aria-selected="false">
                                            <i class="fas fa-book-medical"></i> @lang('clinic::lang.call_histories')
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endslot
                @endcomponent

                @component('components.widget', ['class' => 'box-primary'])
                    @slot('tool')
                        <div class="tab-content" id="patientTabContent">
                            <div class="tab-pane fade" id="patient_dash" role="tabpanel" aria-labelledby="patient_dash-tab">
                                <h4 hidden>Patient Dashboard Content</h4>
                                @include('clinic::patient.patients.partials.dashboard')
                            </div>
                            <div class="tab-pane fade" id="patient_profile" role="tabpanel"
                                aria-labelledby="patient_profile-tab">
                                <h4 hidden>Patient Profile Content</h4>
                                {{-- @include('clinic::patient.patients.partials.profile') --}}
                            </div>
                            <div class="tab-pane fade" id="file_form" role="tabpanel" aria-labelledby="file_form-tab">
                                <h4 hidden>File and Form Content</h4>
                                @include('clinic::patient.patients.partials.file_form')
                            </div>
                            {{-- <div class="tab-pane fade" id="appointment" role="tabpanel" aria-labelledby="appointment-tab">
                                <h4 hidden>Appointment List</h4>
                                @include('clinic::patient.patients.partials.appointment')
                            </div> --}}
                            <div class="tab-pane fade" id="prescriptions" role="tabpanel" aria-labelledby="prescriptions-tab">
                                <h4 hidden>Prescriptions Content</h4>
                                @include('clinic::patient.patients.partials.prescription')
                            </div>
                            <div class="tab-pane fade" id="patient_clinic_led" role="tabpanel"
                                aria-labelledby="patient_clinic_led-tab">
                                <h4 hidden>Patient Clinic Ledger</h4>
                                @include('clinic::patient.patients.partials.patient_clinic')
                            </div>
                            <div class="tab-pane fade" id="patient_store_led" role="tabpanel"
                                aria-labelledby="patient_store_led-tab">
                                <h4 hidden>Patient Store Ledger Content</h4>
                                @include('clinic::patient.patients.partials.patient_store')
                            </div>
                            <div class="tab-pane fade" id="subscriptions" role="tabpanel" aria-labelledby="subscriptions-tab">
                                <h4 hidden>Subscriptions Content</h4>
                                @include('clinic::patient.patients.partials.subscription')
                            </div>
                            <div class="tab-pane fade" id="add_discount" role="tabpanel" aria-labelledby="add_discount-tab">
                                <h4 hidden>Additional Discount Content</h4>
                                @include('clinic::patient.patients.partials.add_discount')
                            </div>
                            <div class="tab-pane fade" id="call_histories" role="tabpanel" aria-labelledby="call_histories-tab">
                                <h4 hidden>Call Histories Content</h4>
                                @include('clinic::patient.patients.partials.call_histories')
                            </div>
                        </div>
                    @endslot
                @endcomponent
            </div>
        </div>
    </div>
    <div class="modal fade profile_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('clinic::patient.patients.partials.view_profile_details')
    </div>
    <div class="modal fade membership_card_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection

@section('javascript')
    <script>
        $(document).on('click', '#patient_details', function() {
            $('#customer_id').select2('close');
            var name = $(this).data('name');
            $('.profile_details_modal').find('input#name').val(name);
            $('.profile_details_modal')
                .find('select#contact_type')
                .val('customer')
                .closest('div.contact_type_div')
                .addClass('hide');
            $('.profile_details_modal').modal('show');
        });

        $(document).ready(function() {
            $('#patientTab a:first').tab('show');
        });

        function updateWidgetTitle() {
            var activeTabContent = $('#patientTabContent .tab-pane.active');
            var newTitle = activeTabContent.find('h4').first().text();
            if (newTitle) {
                $('.box-primary .box-title').text(newTitle);
            }
        }

        updateWidgetTitle();


        // Update title on tab switch
        $('#patientTab a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            updateWidgetTitle();
        });

        $(document).ready(function() {

            var CallHistories = $('#callHistories').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('patient.call_details') }}",
                columns: [{
                        data: 'call_id',
                        name: 'call_id'
                    },
                    {
                        data: 'caller_name',
                        name: 'caller_name'
                    },
                    {
                        data: 'receiver_name',
                        name: 'receiver_name'
                    },
                    {
                        data: 'call_start_time',
                        name: 'call_start_time'
                    },

                    {
                        data: 'call_end_time',
                        name: 'call_end_time'
                    },
                    {
                        data: 'duration',
                        name: 'duration'
                    },
                    {
                        data: 'call_type',
                        name: 'call_type'
                    },

                    {
                        data: 'notes',
                        name: 'notes'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });
        });


        $(document).ready(function() {
            $('#patient_subscription').off('click').on('click', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '{{ route('patient.subscription') }}',
                    type: 'GET',
                    success: function(response) {
                        $('body').append(response);
                        $('#subscriptionModal').modal('show');
                    }
                });
            })
        })
    </script>
@endsection
