<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
            $form_id = 'daily_sloot_generate_form';
            $url = action([\Modules\Clinic\Http\Controllers\doctor\DoctorSlotController::class, 'storeDailySloot']);
        @endphp

        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

            <h4 class="modal-title d-flex align-items-center">
                Generate Daily Sloot
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
                        {!! Form::label('calendar_date', 'Date: *') !!}
                        @php
                            $formattedDates = collect($availableDatesWithoutSlot)->mapWithKeys(function ($date) {
                                return [$date => \Carbon\Carbon::parse($date)->format('l, F j, Y')];
                            });
                        @endphp

                        {!! Form::select('calendar_date', $formattedDates, null, [
                            'class' => 'form-control select2',
                            'required' => true,
                            'placeholder' => 'Select a date',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group" id="operating-hours-container">
                        {!! Form::label('business_operating_hours', 'Business Operating Hours: *') !!}

                        <div class="row">
                            <div class="col-md-6">
                                {!! Form::time('start_time[]', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => 'Ex: 10:00',
                                ]) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::time('close_time[]', null, [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => 'Ex: 10:00 - 18:00',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- <div class="col-md-1">
                    <div class="form-group">
                        {!! Form::label('') !!}
                        {!! Form::label('') !!}
                        <button type="button" class="btn btn-success mt-1 btn-sm" id="add-button-daily">Add</button>
                    </div>
                </div> --}}
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('slot_capacity', 'Capacity: *') !!}
                        {!! Form::number('slot_capacity', 2, [
                            'class' => 'form-control',
                            'required',
                            'id' => 'slot_capacity',
                            'placeholder' => 'Capacity',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('slot_duration', 'Duration: * (Minutes)') !!}
                        {!! Form::number('slot_duration', null, [
                            'class' => 'form-control',
                            'required',
                            'id' => 'slot_duration',
                            'placeholder' => 'Duration',
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
    $(document).ready(function() {
        $('#daily_sloot_generate_form').validate({
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
        $('#daily_sloot_generate_form').submit(function(e) {
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
                    if (result.success == true) {
                        $('div.view_daily_slot_data').modal('hide');
                        toastr.success(result.msg);
                        $('#daily_slot_table').DataTable().ajax.reload();
                    } else if (result.success == false) {
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

        // Add new operating hours row
        $('#add-button-daily').on('click', function() {
            const newRow = `
                <div class="row mt-1">
                    <div class="col-md-5">
                        {!! Form::time('start_time[]', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => 'Ex: 10:00',
                        ]) !!}
                    </div>
                    <div class="col-md-5">
                        {!! Form::time('close_time[]', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => 'Ex: 10:00 - 18:00',
                        ]) !!}
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-button">Remove</button>
                    </div>
                </div>`;
            $('#operating-hours-container').append(newRow);
        });

        // Delegate click event for remove buttons
        $('#operating-hours-container').on('click', '.remove-button', function() {
            $(this).closest('.row').remove();
        });
    });
</script>
