<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
            $form_id = 'doctor_profile_update_form';
            $url = action(
                [\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'update'],
                ['clinic_doctor' => $provider->id],
            );
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'PUT', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('clinic::doctor.doctor_edit')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>
                <div class="col-md-6 individual">
                    {!! Form::hidden('user_id', $provider->user_id) !!}

                    <div class="form-group">
                        {!! Form::label('first_name', __('business.first_name') . ':') !!} <span style="color: red;">*</span>
                        {!! Form::text('first_name', $provider->first_name, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('business.first_name'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6 individual">
                    <div class="form-group">
                        {!! Form::label('last_name', __('business.last_name') . ':') !!} <span style="color: red;">*</span>
                        {!! Form::text('last_name', $provider->last_name, [
                            'class' => 'form-control',
                            'placeholder' => __('business.last_name'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('mobile', __('contact.mobile') . ':') !!} <span style="color: red;">*</span>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-mobile"></i>
                            </span>
                            {!! Form::number('mobile', $provider->mobile, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('contact.mobile'),
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('email', __('clinic::doctor.email') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-envelope"></i>
                            </span>
                            {!! Form::email('email', $provider->email, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.email'),
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('dob', __('clinic::doctor.dob') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>

                            {!! Form::text('dob', !empty($provider->date_of_birth) ? @format_date($provider->date_of_birth) : null, [
                                'class' => 'form-control dob-date-picker',
                                'placeholder' => __('clinic::doctor.dob'),
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('gender', __('clinic::doctor.gender') . ':') !!} <span style="color: red;">*</span>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-venus"></i>
                            </span>
                            {!! Form::select('gender', ['' => __('Select Gender')] + $gender, $provider->gender, [
                                'class' => 'form-control select2',
                                'placeholder' => __('clinic::doctor.gender'),
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('bmdc_number', __('clinic::doctor.bmdc_number') . ':') !!} <span style="color: red;">*</span>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-hashtag"></i>
                            </span>
                            {!! Form::text('bmdc_number', $provider->bmdc_number, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.bmdc_number'),
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('blood_group', __('clinic::doctor.blood_group') . ':') !!} <span style="color: red;">*</span>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-id-badge"></i>
                            </span>
                            {!! Form::select('blood_group', ['' => __('Select Blood Group')] + $bloods, $provider->blood_group, [
                                'class' => 'form-control select2',
                                'placeholder' => __('clinic::doctor.blood_group'),
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('nid', __('clinic::doctor.nid') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-id-card"></i>
                            </span>
                            {!! Form::number('nid', $provider->nid, ['class' => 'form-control', 'placeholder' => __('clinic::doctor.nid')]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('specialist', __('clinic::doctor.specialist') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-leaf"></i>
                            </span>
                            {!! Form::text('specialist', $provider->specialist, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.specialist'),
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('serial_prefix', __('clinic::doctor.serial_prefix') . ':') !!} <span style="color: red;">*</span>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-list-ol"></i>
                            </span>
                            {!! Form::text('serial_prefix', $provider->serial_prefix, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.serial_prefix'),
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('prefix_color', __('clinic::doctor.prefix_color') . ':') !!} <span style="color: red;">*</span>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-list-ol"></i>
                            </span>
                            {!! Form::color('prefix_color', $provider->prefix_color, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.prefix_color'),
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('room', __('clinic::doctor.room') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-hotel"></i>
                            </span>
                            {!! Form::text('room', $provider->room, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.room'),
                            ]) !!}
                        </div>
                    </div>
                </div>
                
                @can('admin')
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('fee', __('clinic::doctor.fee') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fas fa-money-bill-alt"></i>
                                </span>
                                {!! Form::Number('fee', $provider->fee, ['class' => 'form-control', 'placeholder' => __('clinic::doctor.fee')]) !!}
                            </div>
                        </div>
                    </div>
                @endcan


                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('address', __('clinic::doctor.address') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-map-marker-alt"></i>
                            </span>
                            {!! Form::textarea('address', $provider->address, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.address_placeholder'),
                                'rows' => 2,
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('is_full_time', __('Employment Type') . ':') !!} <span style="color: red;">*</span>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-user-clock"></i>
                            </span>
                            {!! Form::select(
                                'is_full_time',
                                [1 => 'Full-time', 0 => 'Part-time'],
                                $provider->is_full_time,
                                ['class' => 'form-control select2', 'required']
                            ) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('is_consultant', __('Consultant Status') . ':') !!} <span style="color: red;">*</span>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-user-md"></i>
                            </span>
                            {!! Form::select(
                                'is_consultant',
                                [1 => 'Consultant', 0 => 'Not Consultant'],
                                $provider->is_consultant,
                                ['class' => 'form-control select2', 'required']
                            ) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('rf_id', __('RFID') . ':') !!} @show_tooltip('RFID for attendance system')
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-id-card"></i>
                            </span>
                            {!! Form::number('rf_id', $provider->rf_id, [
                                'class' => 'form-control',
                                'placeholder' => __('RFID'),
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('doctor_type', __('Doctor Type') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-font"></i>
                            </span>
                           {!! Form::select('type', ['doctor'=>'Doctor', 'therapist'=>'Therapist'], $provider->type, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('description', __('clinic::doctor.description') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-at"></i>
                            </span>
                            {!! Form::textarea('description', $provider->description, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.description'),
                                'rows' => 2,
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-12">
                    {!! Form::label('show_in_pad', __('clinic::doctor.show_in_pad') . ':') !!}
                    <div style="width: 100%">
                        {!! Form::textarea('show_in_pad', $provider->show_in_pad, [
                            'class' => 'form-control',
                        ]) !!}
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
        $(document).ready(function() {
            $('#show_in_pad').summernote({
                placeholder: 'Enter the degree description...',
                tabsize: 2,
                height: 150,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontsize', ['fontsize']],
                    ['color', ['color']],
                    ['para', ['paragraph']],
                    ['height', ['height']],
                    ['view', ['codeview', 'help']]
                ],
                lineHeights: ['0.5', '1.0', '1.5', '2.0', '2.5', '3.0']
            });

            $('form#doctor_profile_update_form').submit(function(event) {
                event.preventDefault(); // Prevent default form submission

                var provider = @json($provider);
                var oldEmail = provider['email'];
                var newEmail = $('#email').val();

                // Validate the form
            }).validate({
                rules: {
                    email: {
                        remote: {
                            url: "{{ route('doctors.checkEmailId') }}",
                            type: 'post',
                            data: {
                                email: function() {
                                    var provider = @json($provider);
                                    var oldEmail = provider['email'];
                                    return oldEmail !== $('#email').val() ? $('#email').val() : null;
                                },
                            },
                        },
                    },
                },
                messages: {
                    email: {
                        remote: LANG.email_id_already_exists,
                    },
                },
                submitHandler: function(form) {
                    __disable_submit_button($(form).find('button[type="submit"]'));
                    submitDoctorForm(form);
                },
            });

            $('#doctor_profile_update_form').trigger('contactFormvalidationAdded');

            function submitDoctorForm(form) {
                var data = $(form).serialize();

                $.ajax({
                    method: 'PUT',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('div.edit_profile_form').modal('hide');
                            toastr.success(result.msg);
                            location.reload(); // Reload the page to reflect changes
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(xhr) {
                        // Handle errors
                        toastr.error('An error occurred. Please try again.');
                    },
                });
            }
        });
    </script>
