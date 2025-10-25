@extends('clinic::layouts.app2')

@section('title', 'Patient Feedback')

@section('content')
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters'), 'class'=>'box-primary'])
            <div class="row">

                <div class="col-md-3">
                    {!! Form::label('Date', 'Appointment Date') !!}
                    {!! Form::date('appointment_date', \Carbon\Carbon::today()->toDateString(), [
                        'class' => 'form-control',
                        'id' => 'appointment_date',
                    ]) !!}
                </div>

            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Patient Feedback List')])
            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="reportTable" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Patient Name</th>
                            <th>Contact</th>
                            <th>Created At</th>
                            <th>Doctor</th>
                            <th>Diseases</th>
                            <th>Waiting Time</th>
                            <th>Feedback</th>
                            <th>Added By</th>
                            <th>Last Updated</th>
                            <th style="width: 75px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        @endcomponent

        <div class="modal fade view_modal_feedback" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade view_modal_intake" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    </section>

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#reportTable').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                "ajax": {
                    "url": "/survey/medical-report",
                    "data": function(d) {
                        d.appointment_date = $('#appointment_date').val();
                    }
                },
                order: [
                    [2, 'desc']
                ],
                columns: [{
                        data: 'patient_name',
                        name: 'patient_name'
                    },

                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at',
                        orderable: true,
                        searchable: false,
                        visible: false,

                    },

                    {
                        data: 'doctor_name',
                        name: 'doctor_name'
                    },
                    {
                        data: 'diseases',
                        name: 'diseases'
                    },
                    {
                        data: 'waiting_time',
                        name: 'waiting_time'
                    },
                    {
                        data: 'all_comments',
                        name: 'all_comments',
                        visible:false,
                    },

                    {
                        data: 'user_name',
                        name: 'user_name'
                    },
                    {
                        data: 'editor_name',
                        name: 'editor_name',
                        render: data => data && data.trim() ? data : ''
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],

            });
            $(document).on('change', '#appointment_date', function() {
                $('#reportTable').DataTable().ajax.reload();
            })

            // Edit Report Modal
            $(document).on('click', '.editReport', function() {
                const id = $(this).data('id');
                $.ajax({
                    url: `/survey/medical-report/${id}/edit`,
                    method: 'GET',
                    success: function(response) {
                        $('body').append(response);
                        $('#reportEditModal').modal('show');
                    }
                });
            });

            // Delete Report with Confirmation
            $(document).on('click', '.deleteReport', function() {
                const id = $(this).data('id');

                // Show the confirmation dialog
                swal({
                    title: LANG.sure, // Use your language variable for the confirmation message
                    text: "Are you sure you want to delete this report?", // Optional text
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        // If the user confirms deletion
                        $.ajax({
                            url: `/survey/medical-report/${id}`, // Correct URL for deletion
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content') // CSRF token for security
                            },
                            success: function(response) {
                                // Reload the DataTable to reflect the changes
                                $('#reportTable').DataTable().ajax.reload();

                                // Show success message
                                swal({
                                    icon: 'success',
                                    text: 'Report deleted successfully!',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                            },
                            error: function(xhr, status, error) {
                                // Show error message if deletion fails
                                swal({
                                    icon: 'error',
                                    text: 'Failed to delete the report. Please try again.',
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 2000
                                });
                            }
                        });
                    } else {
                        // If the user cancels deletion
                        swal({
                            icon: 'info',
                            text: 'Deletion cancelled.',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 2000
                        });
                    }
                });
            });

            $(document).on('click', '.view_report', function(e) {
                e.preventDefault();
                var href = $(this).data('href');
                $('.view_report_modal').load(href, function() {
                    $(this).modal('show');
                });
            });
            $(document).on('click', '.editReport', function(e) {
                e.preventDefault();
                var href = $(this).data('href');
                $('.edit_report_modal').load(href, function() {
                    $(this).modal('show');
                    $('#patient_user_id, #doctor_user_id, #problem, #reference_id').select2();
                });
            });
        });
    </script>
@endsection
