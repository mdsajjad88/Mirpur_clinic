<!-- Modal -->
<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="modal-title" id="reportEditModalLabel">Edit Patient Report</h5>
        </div>
        @php
            $url = action(
                [\Modules\Clinic\Http\Controllers\Survey\ReviewReportController::class, 'update'],
                [$report->id],
            );
            $id = 'updateReport';
        @endphp
        <div class="modal-body">
            {!! Form::open(['id' => $id, 'url' => $url, 'method' => 'PUT', 'enctype' => 'multipart/form-data']) !!}
            <div class="row g-3">
                <!-- Left Column -->
                <div class="col-md-6">
                    <!-- Patient Name -->
                    <div class="form-group">
                        {!! Form::label('patient_profile_id', __('Patient Name') . ':*', ['class' => 'form-label']) !!}
                        {!! Form::select('patient_profile_id', $patients, $report->patient_profile_id, [
                            'class' => 'form-control select2',
                            'id' => 'patient_profile_id',
                            'required' => true,
                            'placeholder' => 'Select Patient',
                            'style' => 'width: 100%;',
                        ]) !!}
                        {!! Form::hidden('report_id', $report->id, ['id' => 'id']) !!}
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-md-6">
                    <!-- Problems -->
                    <div class="form-group">
                        {!! Form::label('comment_id', __('Comments') . ':*', ['class' => 'form-label']) !!}
                        {!! Form::select('comment_id[]', $comments, $selectedComment, [
                            'class' => 'form-control select2',
                            'id' => 'problem',
                            'multiple' => true,
                            'required' => true,
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer modalFooter">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    @lang('messages.close')
                </button>
                {!! Form::submit('Update Report', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {


        // Calculate Total Medicine
        function calculateMedicineTotal() {
            var bd = parseInt($('#bd_medicine').val()) || 0;
            var us = parseInt($('#us_medicine').val()) || 0;
            $('#no_of_medicine').val(bd + us);
        }

        $('#bd_medicine, #us_medicine').on('keyup', calculateMedicineTotal);


        $(document).ready(function() {
            $('form#updateReport').validate({
                rules: {
                    patient_profile_id: {
                        required: true,
                    },
                   
                    comment_id: {
                        required: true,
                    },
                },
                messages: {
                    patient_profile_id: {
                        required: 'Please a Patient',
                    },
                    
                    comment_id: {
                        required: 'Please select comments',
                    },
                },

                submitHandler: function(form) {
                    var data = $(form).serialize();
                    
                    var submitButton = $(form).find('button[type="submit"]');
                    submitButton.prop('disabled', true).text('Processing...');

                    $.ajax({
                        method: 'POST',
                        url: $(form).attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                $('.edit_report_modal').modal('hide')
                                toastr.success(result.msg ||
                                    'Report Updated Successfully');
                                $('#reportTable').DataTable().ajax.reload();
                            } else {
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
    });
</script>
