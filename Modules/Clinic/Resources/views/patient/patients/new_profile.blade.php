@extends('clinic::layouts.app2')

@section('title', __('Patient Profile'))

@section('content')
    <style>
        .info-box {
            background: #e9ecef;
            border-radius: 12px;
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: 0.3s;
            min-height: 60px;
        }

        .info-box:hover {
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.08);
            background: #ddd9d9;
        }

        .info-title {
            font-size: 10px;
            color: #6c757d;
            margin-top: 5px;
            font-weight: 400;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 18px;
            font-weight: 600;
            color: #343a40;
        }

        .medical_history {
            padding: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            border: 1px solid #000000;
            border-radius: 12px;
            margin: 5px;
        }

        .badge-info {
            background-color: #17a2b8;
            color: #fff;
        }

        .badge-warning {
            background-color: #f6d87f;
            color: #000000;
        }

        .badge-primary {
            background-color: #007bff;
            color: #fff;
        }

        .btn-prescribed {
            background: #00661a;
            color: white;
        }

        .btn-red {
            background: rgb(252, 17, 17);
            color: white;
        }

        .btn-orange {
            background: rgb(255, 166, 0);
            color: white;
        }
    </style>
    <div class="container-fluid">
        <div class="row patient_details">
            <div class="col-12">
                @component('components.widget')
                    <ul class="nav nav-tabs" id="patientTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link" id="patient-profile-tab" data-toggle="tab" href="#patient_profile_info"
                                role="tab" aria-controls="patient_profile_info" aria-selected="true">
                                <i class="fas fa-user"></i> @lang('clinic::lang.profile')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="patient-transaction-tab" data-toggle="tab" href="#patient_transactions_tab"
                                role="tab" aria-controls="patient_transactions_tab" aria-selected="true">
                                <i class="fas fa-arrow-up"></i> @lang('clinic::lang.transactions')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="patient-appointments-tab" data-toggle="tab" href="#patient_appointments_tab"
                                role="tab" aria-controls="patient_appointments_tab" aria-selected="true">
                                <i class="fas fa-syringe"></i> @lang('clinic::lang.appointment')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="patient_document_and_note" data-toggle="tab"
                                href="#patient_document_and_note_tab" aria-controls="patient_document_and_note_tab"
                                aria-selected="true">
                                <i class="fas fa-file-alt"></i> @lang('clinic::lang.document_and_note')
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="patient_call_log" data-toggle="tab"
                                href="#patient_call_log_tab" aria-controls="patient_call_log_tab"
                                aria-selected="true">
                                <i class="fas fa-file-alt"></i> @lang('crm::lang.call_log')
                            </a>
                        </li>
                    </ul>
                    <div class="tab-content" id="patientTabContent">
                        <div class="tab-pane fade" id="patient_profile_info" role="tabpanel"
                            aria-labelledby="patient-profile-tab">
                            @include('clinic::patient.patients.common_part')
                            @include('clinic::patient.patients.profile_info')
                        </div>
                        <div class="tab-pane fade" id="patient_transactions_tab" role="tabpanel"
                            aria-labelledby="patient-transaction-tab">
                            @include('clinic::patient.patients.common_part')
                            @include('clinic::patient.patients.transactions')
                        </div>
                        <div class="tab-pane fade" id="patient_appointments_tab" role="tabpanel"
                            aria-labelledby="patient-appointments-tab">
                            @include('clinic::patient.patients.common_part')
                            @include('clinic::patient.patients.appointments')
                        </div>
                        <div class="tab-pane fade" id="patient_document_and_note_tab" role="tabpanel"
                            aria-labelledby="patient_document_and_note">
                            @include('clinic::patient.patients.common_part')
                            @component('components.widget', ['class' => 'box-primary'])
                                                            @include('contact.partials.documents_and_notes_tab')

                            @endcomponent
                        </div>
                        <div class="tab-pane fade" id="patient_call_log_tab" role="tabpanel" aria-labelledby="patient_call_log">
                            @include('clinic::patient.patients.common_part')
                            @component('components.widget', ['class' => 'box-primary'])
                                @include('contact.partials.call_log_tab')
                            @endcomponent
                        </div>
                    </div>
                @endcomponent

            </div>
        </div>
        <div class="modal fade profile_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            @include('clinic::patient.patients.partials.view_profile_details')
        </div>
        <div class="modal fade membership_card_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade payment_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_modal_intake" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_prescription_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade callCenterFeedbackModal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
    @endsection

    @section('javascript')
    @include('documents_and_notes.document_and_note_js')

        <script type="text/javascript">
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
            $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
                var currentTab = $(e.target).attr("href");
                if (currentTab === '#patient_transactions_tab') {
                    if (!$.fn.DataTable.isDataTable('#patient_transaction_table')) {
                        var url = '/patient/transaction/interface/{{ $id }}';

                        $('#patient_transaction_table').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: url,
                                data: function(d) {
                                    d.service_type = $('#service_type').val();
                                }
                            },
                            columns: [{
                                    data: 'transaction_date',
                                    name: 'transactions.transaction_date'
                                },
                                {
                                    data: 'invoice_no',
                                    name: 'transactions.invoice_no'
                                },
                                {
                                    data: 'sub_type',
                                    name: 'transactions.sub_type'
                                },
                                {
                                    data: 'payment_methods',
                                    name: 'payment_lines.method',
                                    searchable: false
                                },
                                {
                                    data: 'payment_status',
                                    name: 'transactions.payment_status'
                                },
                                {
                                    data: 'final_total',
                                    name: 'transactions.final_total'
                                },
                                {
                                    data: 'total_paid',
                                    name: 'transactions.total_paid',
                                    searchable: false
                                },
                                {
                                    data: 'total_remaining',
                                    orderable: false,
                                    searchable: false
                                },
                                {
                                    data: 'discount_amount',
                                    name: 'transactions.discount_amount'
                                },
                                {
                                    data: 'line_discount_amount',
                                    orderable: false,
                                    searchable: false
                                },
                                {
                                    data: 'total_items',
                                    name: 'transactions.total_items',
                                    searchable: false
                                },
                                {
                                    data: 'additional_notes',
                                    name: 'transactions.additional_notes'
                                }
                            ]
                        });
                        $('#service_type').on('change', function() {
                            $('#patient_transaction_table').DataTable().ajax.reload();
                        });
                    }
                }
                if (currentTab === '#patient_appointments_tab') {
                    if (!$.fn.DataTable.isDataTable('#patient_appointments_table')) {
                        var appUrl = '/patient/appointment/info/{{ $id }}';

                        $('#patient_appointments_table').DataTable({
                            processing: true,
                            serverSide: true,
                            ajax: {
                                url: appUrl,
                            },
                            columns: [{
                                    data: 'serial_number',
                                    name: 'serial_number'
                                },
                                {
                                    data: 'appointment_date',
                                    name: 'appointment_date'
                                },
                                {
                                    data: 'doctor_name',
                                    name: 'doctor_name'
                                },
                                {
                                    data: 'status',
                                    name: 'status'
                                },
                                {
                                    data: 'waiting_time',
                                    name: 'waiting_time'
                                },
                                {
                                    data: 'action',
                                    name: 'action',
                                    orderable: false,
                                    searchable: false
                                }
                            ]
                        });

                    }
                }
            });
            $(document).on('click', '.patient-payment-row', function() {
                var url = $(this).attr('data-href');
                $.ajax({
                    url: url,
                    success: function(data) {
                        $('.payment_details_modal').html(data.html);
                        $('.payment_details_modal').modal('show');
                    }
                });
            })
            $(document).ready(function() {
                $(document).on('click', '.view_prescription', function(e) {
                    e.preventDefault();
                    $('div.view_prescription_modal').load($(this).attr('data-href'), function() {
                        $(this).modal('show');
                        $('.direct_print_prescription').hide();
                    });
                });
                $(document).on('click', '.direct_print_prescription', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');

                    const printUrl = "{{ route('prescription.print.view', ':id') }}".replace(':id', id) +
                        '?patient_id={{ $id }}';
                    window.location.href = printUrl;
                });
            })
            $(document).ready(function() {
                var callUrl = '/crm/get/call/log/info/patient/profile/{{ $id }}';
               var patient_call_logs_table = $('#patient_call_logs_table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: callUrl,
                    },
                    columns: [
                        { data: 'start_time', name: 'start_time' },
                        { data: 'created_user_name', name: 'created_user_name' },
                        { data: 'call_type', name: 'call_type' },
                        { data: 'formatted_duration', name: 'formatted_duration' },
                        { data: 'note', name: 'note' },
                        { data: 'subject_names', name: 'subject_names' },
                        { data: 'tag_names', name: 'tag_names' },
                        { data: 'campaign_name', name: 'campaign_name' }
                    ]
                });
                patient_call_logs_table.ajax.reload();
            })
            
        </script>
    @endsection
