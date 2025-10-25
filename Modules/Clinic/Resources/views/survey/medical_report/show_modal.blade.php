<!-- Modal -->
<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <!-- Modal Header -->
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h5 class="modal-title" id="exampleModalLabel">Feedback </h5>
        </div>
        @php
            $form_id = 'feedback-form';
            $url = action([\Modules\Clinic\Http\Controllers\Survey\ReviewReportController::class, 'store']);
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'POST', 'id' => $form_id]) !!}
        <!-- Modal Body -->
        <div class="modal-body">
            <div class="row">
                <!-- Left Column -->
                <div class="col-md-12">
                    <!-- Patient Name -->
                    {!! Form::hidden('appointment_id', $id) !!}
                    {!! Form::hidden('patient_profile_id', $patient->id) !!}
                    <div class="form-group">
                        <label><strong>Patient Name:</strong></label>
                        <p>{{ $patient->first_name ?? 'N/A' }} {{ $patient->last_name ?? '' }}</p>
                    </div>

                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('feedback', 'Feedback:', ['class' => 'control-label']) !!}
                        {!! Form::select('comment_id[]', $comments, array_keys($selectedComments), [
                            'class' => 'form-control select2',
                            'multiple' => 'multiple',
                            'style' => 'width: 100%;'
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('comment', 'Comment:') !!}
                        {!! Form::textarea('comment', $comment, ['rows'=>'2', 'class'=>'form-control', 'placeholder'=>'Enter Custom Comment']) !!}
                    </div>
                </div>
                
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select Comments",
            allowClear: true
        });

        // Ajax form submission
        $('#feedback-form').on('submit', function(e) {
            e.preventDefault();
            
            $.ajax({
                url: $(this).attr('action'),
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if(response.success) {
                        toastr.success('Feedback saved successfully');
                        $('.modal').modal('hide');
                        $('#reportTable').DataTable().ajax.reload();
                    } else {
                        toastr.error(response.msg || 'Error saving feedback');
                    }
                },
                error: function(xhr) {
                    if(xhr.status === 422) {
                        var errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, value) {
                            toastr.error(value[0]);
                        });
                    } else {
                        toastr.error('Error occurred while saving feedback');
                    }
                }
            });
        });
    });
</script>
