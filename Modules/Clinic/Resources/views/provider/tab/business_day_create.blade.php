<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
            $form_id = 'doctor_business_day_add_form';
            $url = action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'businessDayStore']);

        @endphp
        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add Doctor Business Day</h4>
        </div>

        <div class="modal-body">
            <div class="row">

                {!! Form::hidden('doctor_profile_id', $id) !!}
                {!! Form::hidden('business_day_type', 'monthly_sloot') !!}
                {{-- {!! Form::label('business_day_number', 'Day Name', ':*') !!} --}}
                {!! Form::hidden('business_day_number', null, [
                    'class' => 'form-control',
                    'id' => 'business_day_number',
                    'placeholder' => 'Business Day Name',
                ]) !!}

                <div class="col-md-6">
                    <div class="form-group" id="create-operating-hours">
                        {!! Form::label('business_operating_hours', 'Day Start and End Time', ':*') !!}
                        <div class="row">
                            <div class="col-md-6">
                                {!! Form::time('start_time[]', '10:00', [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => 'Ex: 10:00',
                                ]) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::time('close_time[]', '18:00', [
                                    'class' => 'form-control',
                                    'required',
                                    'placeholder' => 'Ex: 10.00 - 6.00',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                {{-- <div class="col-md-1">
                    {!! Form::label('') !!}
                    <button type="button" class="btn btn-success mt-1" id="create-add-button">Add</button>
                </div> --}}


                <div class="col-md-5">
                    <div class="form-group">
                        {!! Form::label('remarks', 'Remarks', ':') !!} <br>
                        {!! Form::textarea('remarks', null, [
                            'class' => 'form-control',
                            'rows' => 2,
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        {!! Form::hidden('is_off_day', 1, false, [
                            'class' => 'input-icheck is_off_day',
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

    </div><!-- /.modal-content -->
</div>
<script>
    $(document).ready(function() {
        var doctor_id = '{{ $id }}';
        const $operatingHoursSection = $('#create-operating-hours');
        const $checkbox = $('#is_off_day');
        const $addButton = $('#create-add-button');

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

        // Validate the form
        $('#doctor_business_day_add_form').validate({
            rules: {
                business_day_number: {
                    remote: {
                        url: '{{ route('doctors.checkBusinessDay') }}',
                        type: 'post',
                        data: {
                            business_day_number: function() {
                                return $('#business_day_number').val();
                            },
                            doctor_id: doctor_id,
                        },
                    },
                },
            },
            messages: {
                business_day_number: {
                    remote: 'This business day already exists',
                    required: 'Please enter a business day name',
                },
            },
        });

        // Handle form submission
        $('#doctor_business_day_add_form').submit(function(e) {
            e.preventDefault();
            const data = $(this).serializeArray();
            const action = $(this).attr('action');

            $.post(action, data)
                .then((result) => {
                    if (result.success) {
                        $('div.create_business_day_modal').modal('hide');
                        toastr.success(result.msg);
                        const newBusinessDayHtml = result.data
                            .map(day => `
                                <div class="row mt-1">
                                    <div class="col-md-6">
                                        <i class="fas fa-circle"></i> <b>${day.business_day_number} ${day.business_operating_hours}</b>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="/business-day/edit/${day.id}" class="btn make_app edit_business_day">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="/business-day/delete/${day.id}" class="btn btn-danger delete_business_day">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            `).join('');
                        $('.openHoursContainer').append(newBusinessDayHtml);
                    } else {
                        toastr.error(result.msg);
                    }
                })
                .fail((xhr) => {
                    toastr.error("An error occurred: " + xhr.responseJSON?.message || xhr
                        .statusText);
                });
        });

        // Add new operating hour row
        $('#create-add-button').on('click', function() {
            const newRow = `
                <div class="row mt-1">
                    <div class="col-md-5">
                        <input type="time" name="start_time[]" class="form-control" required>
                    </div>
                    <div class="col-md-5">
                        <input type="time" name="close_time[]" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger remove-button">Remove</button>
                    </div>
                </div>`;
            $('#create-operating-hours').append(newRow);
        });

        // Delegate click event for remove buttons
        $('#create-operating-hours').on('click', '.remove-button', function() {
            $(this).closest('.row').remove();
        });
    });
</script>
