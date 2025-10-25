@extends('clinic::layouts.app2')
@section('title', 'Intake Form Details')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('All Intake Form Details')])
            @if(auth()->user()->can('intake_form.create'))
            @slot('tool')
                <div class="box-tools">
                    <a href="{{ url('survey/intake-form/create') }}" class="btn btn-block btn-primary">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </a>
                </div>
            @endslot
            @endif
            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="intake_form_table" style="width:100%">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            {{-- <th>Contact</th> --}}
                            <th>Disease</th>
                            <th>Added By</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            {{-- Modal --}}
    
            <div class="modal fade" id="printModal" tabindex="-1" role="dialog" aria-labelledby="printModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="printModalLabel">Print Preview</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body" id="printContent">
                            <!-- Print content will be loaded here -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" onclick="printModalContent()">Print</button>
                        </div>
                    </div>
                </div>
            </div>
            {{--  --}}
        @endcomponent
        <div class="modal fade view_modal_intake" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_report_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade edit_report_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        </div>
    </div>
</div>
    
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('#intake_form_table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                ajax: "/survey/intake-form",
                columns: [{
                        data: 'created_date',
                        name: 'created_date',                        
                    },
                    {
                        data: 'patient_name',
                        name: 'patient_name'
                    },

                    // {
                    //     data: 'mobile',
                    //     name: 'patient_profiles.mobile'
                    // },

                    {
                        data: 'problems',
                        name: 'problems'
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
                order: [
                    [0, 'desc']
                ]
            });
            $(document).on('click', '.print_button_intake', function(e) {
                e.preventDefault();
                const id = $(this).data('id');

                const printUrl = "{{ route('print.intake.form', ':id') }}".replace(':id', id);

                // window.open(printUrl);
                window.location.href = printUrl;
            });

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
                            url: `/survey/intake-form/${id}`, // Correct URL for deletion
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                                    'content') // CSRF token for security
                            },
                            success: function(response) {
                                $('#intake_form_table').DataTable().ajax.reload();
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


            $(document).on('click', '.editReport', function(e) {
                e.preventDefault();
                var href = $(this).data('href');
                $('.edit_report_modal').load(href, function() {
                    $(this).modal('show');
                    $('#patient_profile_id, #doctor_user_id, #problem, #reference_id').select2();
                });
            });
        });
    </script>
@endsection
