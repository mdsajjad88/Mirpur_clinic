<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        @php
            $form_id = 'dosage_update_form';           
            $url = action([\Modules\Clinic\Http\Controllers\DosageController::class, 'update'], [$dosage->id]); 
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'PUT', 'id' => $form_id]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit Dosage</h4>
        </div>
    
        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="dosage_value">Dosage Value *</label>
                        {!! Form::text('value', $dosage->value, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                </div>
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
        
        $('form#dosage_update_form').submit(function(event) {
                event.preventDefault(); 
                return false;
            })
            .validate({
                rules: {
                    'value':{required:true}
                },
                messages: {
                    'value': {
                        required: 'This field is required',
                    },
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
                        $('div.add_dosage_view').modal('hide');
                        var newDosage = $('<option>', {
                                value: result.dosage.value,
                                text: result.dosage.value,
                            });
                            $('.dosage_class').append(newDosage);
                            toastr.success(result.msg);
                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        }
    })
</script>     
    

