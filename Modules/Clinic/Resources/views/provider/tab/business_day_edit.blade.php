<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
            $form_id = 'doctor_business_day_update_form';
            $url = action(
                [\Modules\Clinic\Http\Controllers\ProviderController::class, 'businessDayUpdate'],
                [$days->id],
            );
        @endphp

        {!! Form::open(['url' => $url, 'method' => 'put', 'id' => $form_id, 'novalidate']) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Edit Doctor Business <small>{{ $days->business_day_number }}</small></h4>
        </div>

        <div class="modal-body">
            <div class="row">
                {{-- <div class="col-md-5">
                    <div class="form-group"> --}}
                {!! Form::hidden('days_id', $days->id) !!}
                {{-- {!! Form::label('business_day_number', 'Day Name', ':*') !!} --}}
                {!! Form::hidden('business_day_number', $days->business_day_number, [
                    'class' => 'form-control',
                    'required',
                    'id' => 'business_day_number',
                    'placeholder' => 'Business Day Name',
                ]) !!}
                {{-- <span>{{ $days->business_day_number }}</span> --}}
                {{-- </div>
                </div> --}}

                <div class="col-md-6">
                    <div class="form-group" id="operating-hours-container">
                        {!! Form::label('business_operating_hours', 'Day Start and End Time', ':*') !!}
                        @php
                            $businessOperatingHours = json_decode($days->business_operating_hours, true) ?? [];
                        @endphp

                        @forelse($businessOperatingHours as $time)
                            <div class="row mt-1">
                                <div class="col-md-6">
                                    {!! Form::time('start_time[]', $time['start'] ?? '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Ex: 10.00',
                                    ]) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::time('close_time[]', $time['end'] ?? '', [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Ex: 20.00',
                                    ]) !!}
                                </div>
                                {{-- <div class="col-md-2">
                                    <button type="button" class="btn btn-danger btn-sm remove-button">Remove</button>
                                </div> --}}
                            </div>
                        @empty
                            <div class="row">
                                <div class="col-md-6">
                                    {!! Form::time('start_time[]', null, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Ex: 10.00',
                                    ]) !!}
                                </div>
                                <div class="col-md-6">
                                    {!! Form::time('close_time[]', null, [
                                        'class' => 'form-control',
                                        'required',
                                        'placeholder' => 'Ex: 10.00 - 6.00',
                                    ]) !!}
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- <div class="col-md-1">
                    <button type="button" class="btn btn-success btn-sm mt-1" style="margin-top: 27px;" id="add-button">Add</button>
                </div> --}}

                {{-- <div class="col-md-5">
                    <div class="form-group">
                        {!! Form::label('remarks', 'Remarks', ':') !!} <br>
                        {!! Form::textarea('remarks', $days->remarks, [
                            'class' => 'form-control',
                            'rows' => 2,
                        ]) !!}
                    </div>
                </div> --}}

                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::label('is_off_day', 'Is Holiday', ':') !!} <br>
                        {!! Form::checkbox('is_off_day', 1, $days->is_off_day ? true : false, [
                            'class' => 'input-icheck',
                        ]) !!}
                    </div>
                </div>
            </div>
            <!-- Copy Day Settings Section -->
            <div class="row">
                <div style="margin-left: 15px" class="col-12">
                    <h4 class="mb-3">Copy Schedule:</h4>
                </div>

                @foreach ($allDays as $day)
                    <div class="col-md-6 d-flex justify-content-between align-items-center mb-2">
                        <!-- Checkbox with inline CSS for alignment -->
                        <input class="form-check-input" id="copy_day_{{ $day->id }}" type="checkbox"
                            name="copy_from_day[]" value="{{ $day->id }}">
                        <!-- Label with inline CSS for styling -->
                        <label for="copy_day_{{ $day->id }}" class="fw-bold"
                            style="margin-right: 10px;">{{ $day->business_day_number }}</label>


                    </div>
                @endforeach
            </div>

        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}
    </div><!-- /.modal-content -->
</div>

<script>
    $(document).ready(function() {
        var doctor_id = {{ $days->doctor_profile_id }};
        var day_id = {{ $days->id }};

        const $operatingHoursSection = $('#operating-hours-container');
        const $checkbox = $('#is_off_day');
        const $addButton = $('#add-button');

        function toggleOperatingHours() {
            if ($checkbox.is(':checked')) {
                $operatingHoursSection.hide();
                $addButton.hide();
            } else {
                $operatingHoursSection.show();
                $addButton.show();
            }
        }
        toggleOperatingHours();
        $checkbox.change(toggleOperatingHours);

        $('#doctor_business_day_update_form').validate({
            rules: {
                business_day_number: {
                    required: true,
                    remote: {
                        url: "{{ route('doctors.checkBusinessDay') }}",
                        type: 'post',
                        data: {
                            business_day_number: function() {
                                return $('#business_day_number').val();
                            },
                            doctor_id: doctor_id,
                            day_id: day_id,
                        },
                    },
                },
                start_time: {
                    required: true,
                },
                close_time: {
                    required: true,
                },
            },
            messages: {
                business_day_number: {
                    remote: 'This business day already exists',
                    required: 'Please enter a business day name',
                },
                start_time: {
                    required: 'Enter Start Time',
                },
                close_time: {
                    required: 'Enter Close Time',
                },
            },
        });

        $('#add-button').on('click', function() {
            const newRow = `
                <div class="row mt-1">
                    <div class="col-md-5">
                        {!! Form::time('start_time[]', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => 'Ex: 10.00',
                        ]) !!}
                    </div>
                    <div class="col-md-5">
                        {!! Form::time('close_time[]', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => 'Ex: 10.00 - 6.00',
                        ]) !!}
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm remove-button">Remove</button>
                    </div>
                </div>`;
            $('#operating-hours-container').append(newRow);
        });
        $('#operating-hours-container').on('click', '.remove-button', function() {
            $(this).closest('.row').remove();
        });
    });
</script>
