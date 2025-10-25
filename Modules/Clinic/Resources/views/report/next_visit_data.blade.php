@extends('clinic::layouts.app2')
@section('title', __('Next Visit Date'))
@section('content')
    <section class="content-header">
        <h1>Next Visit Date Report</h1>
    </section>

    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
                {{-- Date Range Filter --}}
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, [
                            'class' => 'form-control',
                            'id' => 'date_range',
                            'readonly',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('call_by', __('Call by') . ':') !!}
                        {!! Form::select('call_by', $users, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
                    </div>
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-primary', 'title' => 'next visit data'])
            <table class="table table-bordered data-table" id="nextVisitDataTable">
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Mobile</th>
                        <th>PID</th>
                        <th>Patient Type</th>
                        <th>Doctor</th>
                        <th>Next Visit Date</th>
                        <th>Status</th>
                        <th>Call Status</th>
                        <th>Call Note</th>
                        <th>Call by</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        @endcomponent

        <div class="modal fade edit_call_status_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var dateRangeSettings = {
                ranges: {
                    [LANG.today]: [moment(), moment()],
                    [LANG.next_7_days]: [moment(), moment().add(7, 'days')],
                    [LANG.next_14_days]: [moment(), moment().add(14, 'days')],
                    [LANG.next_30_days]: [moment(), moment().add(30, 'days')],
                    [LANG.this_month]: [moment().startOf('month'), moment().endOf('month')]
                },
                startDate: moment(),
                endDate: moment().add(7, 'days'),
                maxDate: moment().add(1, 'years')
            };

            // ✅ Daterange picker initialization
            $('#date_range').daterangepicker(dateRangeSettings, function(start, end) {
                $('#date_range').val(start.format(moment_date_format) + ' - ' + end.format(
                    moment_date_format));
                table.ajax.reload(); // ✅ Ensure reload happens after date change
            });

            var table = $('#nextVisitDataTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/next-visit-data',
                    data: function(d) {
                        d.start_date = $('#date_range').data('daterangepicker').startDate.format(
                            'YYYY-MM-DD');
                        d.end_date = $('#date_range').data('daterangepicker').endDate.format(
                            'YYYY-MM-DD');
                        d.call_by = $('#call_by').val();
                    }
                },
                columns: [
                    {data: 'patient_name', name: 'patient_profiles.first_name'},
                    {data: 'mobile', name: 'patient_profiles.mobile', visible: false},
                    {data: 'patient_contact_id', name: 'patient_profiles.patient_contact_id', visible: false},
                    {data: 'patient_type', name: 'patient_type'},
                    {data: 'doctor_name', name: 'doctor_profiles.first_name'},
                    {data: 'next_visit_date',name: 'prescriptions.next_visit_date'},
                    {data: 'status',name: 'patient_appointment_requests.remarks'},
                    {data: 'call_status',name: 'prescriptions.call_status'},
                    {data: 'call_note',name: 'prescriptions.call_note'},
                    {data: 'call_by',name: 'prescriptions.call_by'},
                    {data: 'action',name: 'action', orderable: false, searchable: false}
                ]
            });

            // ✅ Ensure table reloads on date selection
            $('#date_range').on('apply.daterangepicker', function() {
                table.ajax.reload();
            });
            $('#call_by').change(function() {
                table.ajax.reload();
            });

            $(document).on('click', '.edit-call-status', function(e) {
                e.preventDefault();
                var href = $(this).data('href');
                $('.edit_call_status_modal').load(href, function() {
                    $(this).modal('show');
                });
            });

            $(document).on('submit', '#callStatusForm', function(e) {
                e.preventDefault();

                var form = $(this);
                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('.edit_call_status_modal').modal('hide');
                        toastr.success(response.success);
                        table.ajax.reload();
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            var errors = xhr.responseJSON.errors;
                            for (var key in errors) {
                                toastr.error(errors[key][0]);
                            }
                        } else {
                            toastr.error('Something went wrong.');
                        }
                    }
                });
            });

        });
    </script>

@endsection
