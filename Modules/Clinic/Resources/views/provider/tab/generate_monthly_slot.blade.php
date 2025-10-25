<div class="modal-dialog" role="document">
    <div class="modal-content">
        @php
            $form_id = 'monthly_sloot_generate_form';
            $url = action([\Modules\Clinic\Http\Controllers\doctor\DoctorSlotController::class, 'store']);

        @endphp
        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

            <h4 class="modal-title d-flex align-items-center">
                Generate Monthly Sloot
                @if (!empty($breakTimes))
                    <span class="ml-3" style="font-size: 13px; font-weight: normal;">
                        (
                        @foreach ($breakTimes as $index => $break)
                            {{ $break['break_type'] ?? 'Break' }}:
                            {{ \Carbon\Carbon::createFromFormat('H:i', $break['start_time'])->format('g:i A') }}
                            to
                            {{ \Carbon\Carbon::createFromFormat('H:i', $break['end_time'])->format('g:i A') }}
                            @if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                        )
                    </span>
                @endif
            </h4>
        </div>

        <div class="modal-body">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::hidden('doctor_profile_id', $doctor->id) !!}
                        {!! Form::hidden('month_id', $month) !!}
                        {!! Form::label('slot_capacity', 'Capacity'. ':*') !!}
                        {!! Form::number('slot_capacity', 2, [
                            'class' => 'form-control',
                            'required',
                            'id' => 'slot_capacity',
                            'placeholder' => 'Capacity',
                            'value'=>2
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('slot_duration', 'Duration'. ':* (Minute)') !!}
                        {!! Form::number('slot_duration', null, [
                            'class' => 'form-control',
                            'required',
                            'id' => 'slot_duration',
                           'placeholder' => 'duration',
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>
<script>
    $(document).ready(function(){
        $('#monthly_sloot_generate_form').validate({
            rules: {
                slot_capacity: {
                    required: true,
                },
                slot_duration: {
                    required: true,
                }
            },
            messages: {
                slot_capacity: {
                    required: 'Please enter a slot capacity',
                },
                slot_duration: {
                    required: 'Please enter a slot duration time',
                },
            },
        });

        // Handle form submission
        $('#monthly_sloot_generate_form').submit(function(e) {
            e.preventDefault();
            var $form = $(this);
            if (!$form.valid()) return; // Validate the form first
            
            var data = $form.serialize();
            var submitButton = $form.find('button[type="submit"]');
            submitButton.prop('disabled', true); // Disable the button

            $.ajax({
                method: 'POST',
                url: $form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success) {
                        $('div.monthly_sloot_generate').modal('hide');
                        toastr.success(result.msg);
                        $('#monthly_slot_table').DataTable().ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('An error occurred: ' + error);
                },
                complete: function() {
                    submitButton.prop('disabled', false); // Re-enable the button
                }
            });
        });
    });
</script>
