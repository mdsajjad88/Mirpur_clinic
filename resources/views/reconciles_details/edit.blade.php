<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        @php
            $form_id = 'reconcile_details_update_form';
            $url = action([\App\Http\Controllers\ReconcileDetailsController::class, 'update'], [$reconcileDetails->id]);

        @endphp
        {!! Form::open(['url' => $url, 'method' => 'put', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add Reconcile Details</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('reconcile_id', __('business.recon_name') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-signature"></i>
                            </span>
                            {!! Form::select('reconcile_id', ['Select Reconcile'] + $reconciles->toArray(), $reconcileDetails->reconcile_id, [
                'class' => 'form-control',
                'required',
            ]) !!}
                        </div>
                    </div>
                </div>


                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('name',"Name" . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-file-signature"></i>
                            </span>

                            {!! Form::text('name', $reconcileDetails->name, [
                                'class' => 'form-control',
                                'placeholder' => "Name",
                                'required'
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('sku',"SKU" . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-medal"></i>
                            </span>

                            {!! Form::text('sku', $reconcileDetails->sku, [
                                'class' => 'form-control',
                                'placeholder' => "Enter SKU",
                                'required'
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('physical_qty',"physical Quantity" . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-star"></i>
                            </span>

                            {!! Form::number('physical_qty', $reconcileDetails->physical_qty, [
                                'class' => 'form-control',
                                'placeholder' => "Enter Physical Quantity",
                                'required'
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('software_qty',"Software Quantity" . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-compass"></i>
                            </span>

                            {!! Form::number('software_qty', $reconcileDetails->software_qty, [
                                'class' => 'form-control',
                                'placeholder' => "Enter Software Quantity",
                                'required'
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('difference',"Difference" . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-align-center"></i>
                            </span>

                            {!! Form::number('difference', $reconcileDetails->difference, [
                                'class' => 'form-control',
                                'placeholder' => "Enter Difference",
                                'required'
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('difference_percentage',"Difference Percentage(%)" . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-bacon"></i>
                            </span>

                            {!! Form::number('difference_percentage', $reconcileDetails->difference_percentage, [
                                'class' => 'form-control',
                                'placeholder' => "Difference Percentage",
                                'required'
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
            $('form#reconcile_details_update_form').submit(function(event) {
                event.preventDefault(); // Prevent default form submission
        
                // Validate form inputs here if needed
                if ($(this).valid()) {
                    // If validation is successful
                    var data = $(this).serialize(); // Serialize form data
        
                    $.ajax({
                        method: 'PUT',
                        url: $(this).attr('action'), // Get the action URL from the form
                        dataType: 'json',
                        data: data,
                        beforeSend: function(xhr) {
                            // Disable the submit button to prevent multiple clicks
                            __disable_submit_button($(this).find('button[type="submit"]'));
                        },
                        success: function(result) {
                            if (result.success) {
                                // Hide the modal on success
                                $('div.modal').modal('hide');
                                toastr.success(result.msg); // Display success message
                                // Optionally, refresh a table or perform other actions
                                $('#reconcile_details_table').DataTable().ajax.reload();
                            } else {
                                toastr.error(result.msg); // Display error message
                            }
                        },
                        error: function(xhr) {
                            // Handle any errors
                            toastr.error('An error occurred. Please try again.'); 
                        },
                        complete: function() {
                            // Re-enable the submit button
                            __enable_submit_button($(this).find('button[type="submit"]'));
                        }
                    });
                }
            });
        
            // Additional validation rules
            $('#reconcile_details_update_form').validate({
        rules: {
            name: { required: true },
            sku: { required: true },
            physical_qty: { required: true },
            software_qty: { required: true },
            difference: { required: true },
            difference_percentage: { required: true },
        },
        messages: {
            name: {
                required: 'Please enter data this field.',
            },
            sku: {
                required: 'Please enter data this field.',
            },
            physical_qty: {
                required: 'Please enter data this field.',
            },
            software_qty: {
                required: 'Please enter data this field.',
            },
            difference: {
                required: 'Please enter data this field.',
            },
            difference_percentage: {
                required: 'Please enter data this field.',
            },
            
        },
    });
});
        </script>