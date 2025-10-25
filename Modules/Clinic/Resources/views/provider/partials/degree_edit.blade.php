<div class="modal-dialog modal-lg" role="document"> 
    <div class="modal-content">
        @php
            $form_id = 'doctor_degree_update_form';
            $url = action(
                [\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'updateDegrees'],
                [$degree->id],
            );
            $showInPad = json_decode($degree->show_in_pad, true); // Assuming show_in_pad is stored as JSON
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'put', 'id' => $form_id]) !!}
        {!! Form::hidden('degree_id', $degree->id) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit Degree Info</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::checkbox('show_in_pad[]', 'degree_name', in_array('degree_name', $showInPad)) !!} 
                        {!! Form::label('degree_name', __('clinic::doctor.degree_name') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-signature"></i>
                            </span>
                            {!! Form::text('degree_name', $degree->degree_name, [
                                'class' => 'form-control',
                                'required',
                                'id' => 'degree_name',
                                'placeholder' => __('clinic::doctor.degree_name'),
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::checkbox('show_in_pad[]', 'degree_short_name', in_array('degree_short_name', $showInPad)) !!} 
                        {!! Form::label('degree_short_name', __('clinic::doctor.degree_short_name') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-monument"></i>
                            </span>
                            {!! Form::text('degree_short_name', $degree->degree_short_name, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('clinic::doctor.degree_short_name'),
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::checkbox('show_in_pad[]', 'certification_place', in_array('certification_place', $showInPad)) !!} 
                        {!! Form::label('certification_place', __('clinic::doctor.certification_place') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-location-arrow"></i>
                            </span>
                            {!! Form::text('certification_place', $degree->certification_place, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('clinic::doctor.certification_place'),
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('certification_date', __('clinic::doctor.certification_date') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text(
                                'certification_date',
                                !empty($degree->certification_date) ? @format_date($degree->certification_date) : null,
                                [
                                    'class' => 'form-control certification-date-picker',
                                    'placeholder' => __('clinic::doctor.certification_date'),
                                    'readonly',
                                ]
                            ) !!}
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
            <i><span style="font-size: 18px" class="pull-left"><strong>Note: </strong> Check Boxes to show fields on prescription pad</span></i>
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
            </div>

            {!! Form::close() !!}
        </div><!-- /.modal-content -->
    </div>

    <script>
        $(document).ready(function() {
            var id = @json($degree->doctor_profile_id);
            var degreeId = @json($degree->id);
            $('#doctor_degree_update_form').validate({
                rules: {
                    degree_name: {
                        required: true,
                        remote: {
                            url: "{{ route('degree.checkUniqueName') }}", // Your validation route
                            type: 'POST',
                            data: {
                                name: function() {
                                    return $('#degree_name').val(); // Degree name input value
                                },
                                degree_id: function() {
                                    return degreeId; // Current degree ID (for update)
                                },
                                doctor_id: id,
                            }
                        }
                    },
                },
                messages: {
                    degree_name: {
                        required: 'Please enter a degree name.',
                        remote: 'This Degree is already taken.' // Adjust as necessary
                    },
                },
            });

            $('#doctor_degree_update_form').submit(function(e) {
                e.preventDefault(); // Prevent the default form submission
                var data = $(this).serialize(); // Serialize the form data
                $.ajax({
                    method: 'PUT',
                    url: $(this).attr('action'), // Get the action URL from the form
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('div.edit_degree_modal').modal('hide'); // Hide the modal
                            toastr.success(result.msg); // Show success message
                            $("#degree_table").DataTable().ajax.reload();
                        } else {
                            toastr.error(result.msg); // Show error message
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error("An error occurred: " + xhr.responseText); // Show error if AJAX fails
                    }
                });
            });
        });
    </script>
