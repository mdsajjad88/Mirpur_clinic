<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
            $form_id = 'doctor_update_form';

                $url = action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'update'],
                        ['provider' => $provider->id]);
                    
            
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'PUT', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">  
                    @lang('clinic::doctor.edit_reference_info')
                </h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>
                <div class="col-md-3 individual">
                    {!! Form::hidden('user_id', $provider->user_id) !!}

                    <div class="form-group">
                        {!! Form::label('first_name', __('business.first_name') . ':*') !!}
                        {!! Form::text('first_name', $provider->first_name, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('business.first_name'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3 individual">
                    <div class="form-group">
                        {!! Form::label('last_name', __('business.last_name') . ':') !!}
                        {!! Form::text('last_name', $provider->last_name, [
                            'class' => 'form-control',
                            'placeholder' => __('business.last_name'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('mobile', __('contact.mobile') . ':*') !!}
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

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('email', __('business.email') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-envelope"></i>
                            </span>
                            {!! Form::email('email', $provider->email, ['class' => 'form-control', 'id' => 'email']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('dob', __('lang_v1.dob') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>

                            {!! Form::text('dob', !empty($provider->date_of_birth) ? @format_date($provider->date_of_birth) : null, [
                                'class' => 'form-control dob-date-picker',
                                'placeholder' => __('lang_v1.dob'),
                                'readonly',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <br>
                        <label>
                            {!! Form::checkbox('is_doctor', 1, $provider->is_doctor == 1 ? true : false, [
                                'class' => 'is_doctor',
                                'id' => 'is_doctor',
                                'data-target' => '.doctor_designation',
                                'style' => 'height:20px; width:20px;',
                            ]) !!} <strong>@lang('clinic::lang.is_doctor')</strong>
                        </label>
                    </div>
                </div>
                <div class="doctor_designation {{ $provider->is_doctor == 1 && $provider->designation != null ? 'show' : 'hide' }}">
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('designation', __('clinic::lang.doctor_designation') . ':') !!}
                            <div class="input-group">

                                {!! Form::text('designation', $provider->designation, ['class' => 'form-control', 'placeholder' => __('clinic::lang.doctor_designation')]) !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <br>
                        <label>
                            {!! Form::checkbox('is_show_invoice', 1, $provider->is_show_invoice == 1 ? true : false, [
                                'class' => 'is_show_invoice',
                                'id' => 'is_show_invoice',
                                'style' => 'height:20px; width:20px;',
                            ]) !!} <strong>@lang('clinic::lang.show_invoice')</strong>
                        </label>
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('address', __('clinic::doctor.address') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fas fa-location-pin"></i>
                            </span>
                            {!! Form::textarea('address', $provider->address, [
                                'class' => 'form-control',
                                'placeholder' => __('clinic::doctor.address_placeholder'),
                                'rows' => 2,
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
        $(document).ready(function() {
            var doctor_id = {{$provider->id}}; // Define doctor_id here

            $('form#doctor_update_form').submit(function(event) {
                    event.preventDefault();
                    return false;
                })
                .validate({

                    rules: {
                        email: {
                            remote: {
                                url: "{{ route('doctors.checkEmailId') }}",
                                type: 'post',
                                data: {
                                    email: function() {
                                        return $('#email').val();
                                    },
                                    doctor_id:doctor_id
                                },
                            },
                        },
                    },
                    messages: {
                        email: {
                            remote: LANG.email_id_already_exists, // Adjust your error message
                        },
                    },
                    submitHandler: function(form) {
                        event.preventDefault();
                        $.ajax({
                            method: 'POST',
                            url: '{{ route('doctors.checkEmailId') }}',
                            dataType: 'json',
                            data: {
                                email: function() {
                                    return $('#email').val();
                                },
                            },
                            beforeSend: function(xhr) {
                                __disable_submit_button($(form).find('button[type="submit"]'));
                            },
                            success: function(result) {
                                if (result.is_email_exists == true) {
                                    swal({
                                        title: LANG.sure,
                                        text: result.msg,
                                        icon: 'warning',
                                        buttons: true,
                                        dangerMode: true
                                    }).then(willContinue => {
                                        if (willContinue) {
                                            submitDoctorForm(form);
                                        } else {
                                            $('#mobile').select();
                                        }
                                    });

                                } else {
                                    submitDoctorForm(form);
                                }
                            },
                        });
                    },
                });

            $('#doctor_update_form').trigger('contactFormvalidationAdded');

            function submitDoctorForm(form) {
                var data = $(form).serialize();

                $.ajax({
                    method: 'PUT',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: data,
                    success: function(result) {
                        if (result.success == true) {
                            $('div.edit_doctor_modal').modal('hide');
                            toastr.success(result.msg);

                            $('#doctors_table').DataTable().ajax.reload();

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            }
        })
    </script>
