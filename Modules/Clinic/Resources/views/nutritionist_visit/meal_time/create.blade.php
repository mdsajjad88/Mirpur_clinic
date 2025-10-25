<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        @php
            $form_id = 'meal_time_add_form';           
            $url = action([\Modules\Clinic\Http\Controllers\nutritionist\MealTimeController::class, 'store']); 
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add Meal Time</h4>
        </div>
    
        <div class="modal-body">
            <div class="row">
                <!-- Name Field -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="name">Meal Name *</label>
                        {!! Form::text('name', null, ['class' => 'form-control', 'required' => true, 'placeholder' => 'e.g., Breakfast']) !!}
                    </div>
                </div>

                <!-- Start Time Field -->
                {{-- <div class="col-md-12">
                    <div class="form-group">
                        <label for="start_time">Start Time *</label>
                        {!! Form::time('start_time', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                </div>

                <!-- End Time Field -->
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="end_time">End Time *</label>
                        {!! Form::time('end_time', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                </div> --}}
            </div>
        </div>
        <div class="modal-footer">
            {!! Form::submit(__('messages.save'), ['class' => 'btn btn-primary']) !!}
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
<script>
    $(document).ready(function() {
        
        $('form#meal_time_add_form').submit(function(event) {
                event.preventDefault(); 
                return false;
            })
            .validate({
                rules: {
                    'name':{required:true},
                    // 'start_time':{required:true},
                    // 'end_time':{required:true},
                },
                messages: {
                    'name': {
                        required: 'This field is required',
                    },
                    // 'start_time': {
                    //     required: 'This field is required',
                    // },
                    // 'end_time': {
                    //     required: 'This field is required',
                    // },
                },
                submitHandler: function(form) {
                    event.preventDefault();
                    submitDosageForm(form);
                },
            });

        function submitDosageForm(form) {
            var data = $(form).serialize();
            
            $.ajax({
                method: 'POST',
                url: $(form).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div.add_new_meal_time').modal('hide');
                        $('#meal_time_table').DataTable().ajax.reload();
                        var newDosage = $('<option>', {
                                value: result.data.text,
                                text: result.data.text,
                            });
                            $('.meal_time_select').append(newDosage).trigger('change').val(result.data.text);
                            toastr.success(result.msg);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        }
    })
</script>     
    

