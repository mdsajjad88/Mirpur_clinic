<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
            $form_id = 'doctor_specialities_update_form';
            $url = action([\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'updateSpecialities'],[$specilities->id]);
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'put', 'id' => $form_id]) !!}
        {!! Form::hidden('doctor_profile_id', $specilities->doctor_profile_id) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit a Speacilities info</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('term_name', __('Specialities Name') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-signature"></i>
                            </span>
                            {!! Form::text('term_name', $specilities->term_name, [
                                'class' => 'form-control',
                                'required',
                                'id' => 'term_name',
                                'placeholder' => "Enter Specialities name here",
                            ]) !!}
                        </div>

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('term_short_name', __('Specilities Short Name') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-monument"></i>
                            </span>
                            {!! Form::text('term_short_name', $specilities->term_short_name, [
                                'class' => 'form-control',
                                
                                'placeholder' => __('Specilities Short Name'),
                            ]) !!}
                        </div>

                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('year_of_experience', __('Year of exprience this specilities') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-location-arrow"></i>
                            </span>
                            {!! Form::number('year_of_experience', $specilities->year_of_experience, [
                                'class' => 'form-control',
                                'placeholder' => 'Enter Year of Experience this specilities',
                            ]) !!}
                        </div>
                    </div>
                </div>


                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('certifications', __('Archievements') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>

                            {!! Form::text('certifications', $specilities->certifications, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('Archievements'),
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
    </div><!-- /.modal-dialog -->
<script>
    $(document).ready(function(){


        var id = @json($specilities->doctor_profile_id);
            var specialities_id = @json($specilities->id);
            $('#doctor_specialities_update_form').validate({
                rules: {
                    term_name: {
                        required: true,
                        remote: {
                            url: "{{ route('specialities.checkSpecialitiesName') }}", 
                            type: 'GET',
                            data: {
                                name: function() {
                                    return $('#term_name').val(); 
                                },
                                specialities_id: null,
                                doctor_id:id,
                            }
                        }
                    },
                    certifications: {
                        required: true
                    },
                },
                messages: {
                    term_name: {
                        required: 'Please enter a Specialities name.',
                        remote: 'This Specialities is already taken.'
                    },
                    
                },
            });


        $('#doctor_specialities_update_form').submit(function(e) {
                e.preventDefault(); // Prevent the default form submission
                var data = $(this).serialize(); // Serialize the form data
                $.ajax({
                    method: 'PUT',
                    url: $(this).attr('action'), // Get the action URL from the form
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('div.edit_specilities_form').modal('hide'); // Hide the modal
                            // reloadDegreesTable(doctorId);
                            toastr.success(result.msg); // Show success message
                            $("#specilities_table").DataTable().ajax
                            .reload();
                        } else {
                            toastr.error(result.msg); // Show error message
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error("An error occurred: " + xhr
                            .responseText); // Show error if AJAX fails
                    }
                });
            });
    })
</script>