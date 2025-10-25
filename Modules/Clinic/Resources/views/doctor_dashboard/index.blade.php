@extends('clinic::layouts.app2')
@section('title', Auth::user()->username)
@section('content')
    <style>
        #doctor_appointment_table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 10px;
            background: #FFFFFF;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            text-shadow: 20px 20px 20px #dfd4d4;
            padding: 20px;
        }

        #doctor_appointment_table td {
            text-align: left;
            vertical-align: middle;
            font-size: 14px;
            border: none !important;
        }

        #doctor_appointment_table tr {
            background-color: #FFFFFF;
            color: black;
            transition: background-color 0.3s ease, transform 0.3s ease;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            border-radius: 20px;
        }

        #doctor_appointment_table tr:nth-child(odd) {
            background-color: #FFFFFF;
        }

        #doctor_appointment_table td:first-child {
            border-top-left-radius: 20px !important;
            border-bottom-left-radius: 20px !important;
        }

        #doctor_appointment_table td:last-child {
            border-top-right-radius: 20px !important;
            border-bottom-right-radius: 20px !important;
        }

        #doctor_appointment_table tr:hover {
            background-color: #f9f9f9;
            transform: scaleX(1.01);
        }

        #doctor_appointment_table td:hover {
            background-color: #f9f9f9;
        }

        .patient_profile {
            width: 45% !important;
        }

        .disease_section {
            border-left: 1px solid rgb(50, 157, 219);
            padding-left: 10px;
            align-items: center;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 3;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 55px;

        }

        .btn-create {
            background: #4a2b94;
            color: white;
        }

        .disease_btn_primary {
            background: #e6f4ea;
        }

        .btn-orange {
            background: #f87502;
            color: white;
        }



        .btn-yellow {
            background: #f1c40f;
            color: white;
        }

        .action_th {
            width: 12% !important;
        }

        .cion {
            font-size: 13px;
        }

        #doctor_appointment_table td.text-center {
            text-align: center;
        }

        .btn-prescribed {
            background: #00661a;
            color: white;
        }

        .btn-red {
            background: red;
            color: white;
        }

        .btn-danger {
            background: red;
            color: white;
        }

        .btn-purple {
            background: purple;
            color: white;
        }

        /* .dataTables_scrollHeadInner {
                background-color: #f1f1f1;
                font-size: 16px;
                color: #333;
                border-bottom: 2px solid #ddd;
                padding: 10px;
            } */
    </style>

    @if (session('printUrl'))
        <script>
            const printUrl = "{{ session('printUrl') }}";
            window.location.href = printUrl;
        </script>
    @endif

    <div class="container-fluid">
        @component('components.filters', ['title' => 'Filters'])
            <div class="row">
                <div class="col-md-3">
                    {!! Form::label('Status', 'Status:') !!}
                    {!! Form::select('status', ['' => 'All'] + $statuses, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_status',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('Date', 'Appointment Date') !!}
                    {!! Form::date('appointment_date', \Carbon\Carbon::today()->toDateString(), [
                        'class' => 'form-control',
                        'id' => 'appointment_date_filter',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('health_concern', 'Health Concern:') !!}
                    {!! Form::select('filter_with_disease_id', ['' => 'All'] + $diseases, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_disease_id',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('waiting_status', 'Waiting Status') !!}
                    {!! Form::select('waiting_status', ['' => 'All'] + $waiting_statuses, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_waiting_status',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('doctor', 'Doctor:') !!}
                    {!! Form::select('doctor', ['' => 'All'] + $doctors, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_doctor',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('appointment_media', __('clinic::lang.appointment_media')) !!}
                    {!! Form::select('appointment_media', ['' => 'All'] + $medias, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'appointment_media_filter',
                    ]) !!}
                </div>
            </div>
        @endcomponent

        <div class="row">
            <div class="col">
                <section class="content-header">
                    <h1>Appointment List
                        <small>Manage Your Appointment - <b>Date:</b><span id="appointment_date_val">
                                {{ \Carbon\Carbon::today()->format('d-m-Y') }} </span></small>
                    </h1>
                </section>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col">
                @php
                    $title = auth()->user()->can('admin') ? 'View all Appointments' : 'My Appointments';
                @endphp
                @component('components.widget', [
                    'title' => $title,
                    'class' => 'box-primary',
                    'style' => 'background-color:#FFFFFF',
                ])
                    <table class="table table-striped" id="doctor_appointment_table" style="width:100%">
                        <thead>
                            <tr>
                                <th>SL No</th>
                                <th class="patient_profile">Patient Profile</th>
                                <th>Created time</th>
                                <th style="min-width: 75px !important;">Status</th>
                                <th>Appointment Media</th>
                                @can('admin')
                                    <th>Doctor</th>
                                @endcan
                                <th>Appointment Time</th>
                                <th class="action_th">Action</th>
                            </tr>
                        </thead>
                        <tbody style="text-align: center !important;">

                        </tbody>
                    </table>
                @endcomponent
            </div>
        </div>
    </div>
    <div class="modal fade view_modal_intake" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade view_prescription_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var doctorType = '{{ $doctorType }}';
            var url = "/doctor-dashboard";
            if (doctorType == "therapist") {
                url = "/physiotherapist/prescription";
            }
            var doctor_appointment_table = $('#doctor_appointment_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": url,
                    "data": function(d) {
                        d.status = $('#filter_with_status').val();
                        d.appointment_date = $('#appointment_date_filter').val();
                        d.health_concern_id = $('#filter_with_disease_id').val();
                        d.waiting_status = $('#filter_with_waiting_status').val();
                        d.doctor_id = $('#filter_with_doctor').val();
                        d.appointment_media = $('#appointment_media_filter').val();
                    }
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [{
                        data: 'serial_number',
                        name: 'doctor_sl.sl_no',
                        className: 'text-center',
                    },
                    {
                        data: 'patient_info',
                        name: 'pp.first_name'
                    },

                    {
                        data: 'updated_at',
                        name: 'updated_at',
                        orderable: true,
                        searchable: false,
                        visible: false,

                    },
                    {
                        data: 'status',
                        name: 'appointment.remarks',
                        className: 'text-center',
                    },
                    {
                        data: 'appointment_media',
                        name: 'appointment.appointment_media',
                        className: 'text-center',

                    },
                    @can('admin')
                        {
                            data: 'doctor_name',
                            name: 'dp.first_name',
                            className: 'text-center',
                            orderable: false,

                        },
                    @endcan {
                        data: 'waiting_time',
                        name: 'waiting_time',
                        className: 'text-center',
                        orderable: false, // Disable sorting for this column
                        render: function(data, type, row) {
                            return data;
                        }
                    },
                    {
                        data: 'action',
                        name: 'action',
                        className: 'text-center',

                    },
                ],
            });

            function calculateWaitingTime(startTime) {
                var start = moment(startTime);
                var now = moment();
                var duration = moment.duration(now.diff(start));

                var days = Math.floor(duration.asDays());
                var hours = duration.hours();
                var minutes = duration.minutes();
                var seconds = duration.seconds();

                if (days > 0) {
                    return days + ":" + hours + ":" + minutes + " dys";
                } else if (hours > 0) {
                    return hours + ":" + minutes + " hrs";
                } else if (minutes > 0) {
                    return minutes + " min";
                } else {
                    return seconds + " sec";
                }
            }

            function updateWaitingTime() {
                $('#doctor_appointment_table tbody tr').each(function() {
                    var waitingTimeCell = $(this).find('.waiting-time');
                    var isInterval = waitingTimeCell.data('is-interval') === true;
                    var startTime = waitingTimeCell.data('start-time');
                    var currentTime = moment().format('YYYY-MM-DD HH:mm:ss');
                    var differeceTime = moment(currentTime).diff(moment(startTime), 'hours');

                    if (isInterval && startTime) {
                        var newWaitingTime = calculateWaitingTime(startTime);
                        var $button = waitingTimeCell.find('button');
                        var buttonText = $button.text().split(' ')[0];
                        $button.text(buttonText + ' ' + newWaitingTime);

                        // Update the button class based on the new waiting time
                        var totalMinutes = moment.duration(moment().diff(moment(startTime))).asMinutes();
                        if (totalMinutes >= 0 && totalMinutes <= 20) {
                            $button.removeClass('btn-info btn-orange btn-danger btn-success').addClass(
                                'btn-yellow');
                        } else if (totalMinutes >= 21 && totalMinutes <= 60) {
                            $button.removeClass('btn-info btn-yellow btn-danger btn-success').addClass(
                                'btn-orange');
                        } else if (totalMinutes >= 60) {
                            $button.removeClass('btn-info btn-yellow btn-orange btn-success').addClass(
                                'btn-danger');
                        } else {
                            $button.removeClass('btn-info btn-yellow btn-orange btn-danger').addClass(
                                'btn-success');
                        }
                    }
                });
            }
            @if (!auth()->user()->can('admin'))
                setInterval(function() {
                    $('#doctor_appointment_table').DataTable().ajax.reload();
                }, 60000);
            @endif
            // setInterval(updateWaitingTime, 1000);


            $(document).on('change',
                '#filter_with_status, #appointment_date_filter, #filter_with_disease_id, #filter_with_waiting_status, #filter_with_doctor, #appointment_media_filter',
                function() {
                    doctor_appointment_table.ajax.reload();
                })
            $(document).on('click', '.view_prescription', function(e) {
                e.preventDefault();
                $('div.view_prescription_modal').load($(this).attr('data-href'), function() {
                    $(this).modal('show');
                });
            });
            $(document).on('change', '#appointment_date_filter', function(e) {
                let selected = $(this).val();
                let parts = selected.split('-');
                if (parts.length === 3) {
                    let formatted = parts[2] + '-' + parts[1] + '-' + parts[0];
                    $('#appointment_date_val').text(formatted);
                } else {
                    $('#appointment_date_val').text(selected);
                }
            });


            $(document).on('click', '.direct_print_prescription', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                const printUrl = "{{ route('prescription.print.view', ':id') }}".replace(':id', id);
                window.location.href = printUrl;
            });

            $(document).on('click', '.create_edit_common_btn', function(e) {
                e.preventDefault();
                var is_available = $(this).data('is_doctor_available');
                if (is_available == 1) {
                    window.location.href = $(this).attr('href');
                } else {
                    toastr.error('Your status is unavailable! Please turn on your availability status.');
                }
            });

        });
    </script>

@endsection
