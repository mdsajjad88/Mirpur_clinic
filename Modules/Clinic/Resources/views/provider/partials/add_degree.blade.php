<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
            $form_id = 'doctor_degree_add_form';
            $url = action([\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'storeDegrees']);
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}
        {!! Form::hidden('doctor_profile_id', $id) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add New Degree</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>

                <!-- Degree Name -->
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::checkbox('show_in_pad[]', 'degree_name', false) !!} {!! Form::label('degree_name', __('clinic::doctor.degree_name') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-signature"></i></span>
                            {!! Form::text('degree_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('clinic::doctor.degree_name')]) !!}
                        </div>
                    </div>
                </div>

                <!-- Degree Short Name -->
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::checkbox('show_in_pad[]', 'degree_short_name', false) !!} {!! Form::label('degree_short_name', __('clinic::doctor.degree_short_name') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-monument"></i></span>
                            {!! Form::text('degree_short_name', null, ['class' => 'form-control', 'required', 'placeholder' => __('clinic::doctor.degree_short_name')]) !!}
                        </div>
                    </div>
                </div>

                <!-- Certification Place -->
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::checkbox('show_in_pad[]', 'certification_place', false) !!} {!! Form::label('certification_place', __('clinic::doctor.certification_place') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-location-arrow"></i></span>
                            {!! Form::text('certification_place', null, ['class' => 'form-control', 'required', 'placeholder' => __('clinic::doctor.certification_place')]) !!}
                        </div>
                    </div>
                </div>

                <!-- Certification Date -->
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('certification_date', __('clinic::doctor.certification_date') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                            {!! Form::text('certification_date', null, ['class' => 'form-control dob-date-picker', 'placeholder' => __('clinic::doctor.certification_date'), 'readonly']) !!}
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
    </div>
</div>

<script>
    $(document).ready(function() {
        var id = @json($id);

        $('#doctor_degree_add_form').validate({
            rules: {
                degree_name: {
                    required: true,
                    remote: {
                        url: "{{ route('degree.checkUniqueName') }}",
                        type: 'POST',
                        data: {
                            name: function() {
                                return $('#degree_name').val();
                            },
                            degree_id: null,
                            doctor_id: id,
                        }
                    }
                }
            },
            messages: {
                degree_name: {
                    required: 'Please enter a degree name.',
                    remote: 'This Degree is already taken.'
                }
            }
        });

        $('#doctor_degree_add_form').submit(function(e) {
            e.preventDefault();
            var data = $(this).serialize();

            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div.add_degree_form').modal('hide');
                        toastr.success(result.msg);
                        $('#degree_table').DataTable().ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(xhr) {
                    toastr.error("An error occurred: " + xhr.responseText);
                }
            });
        });
    });
</script>
