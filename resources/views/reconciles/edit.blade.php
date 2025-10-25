<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        @php
            $form_id = 'reconcile_update_form';
            $url = action([\App\Http\Controllers\ReconcileController::class, 'update'],[$reconcile->id]);

        @endphp
        {!! Form::open(['url' => $url, 'method' => 'put', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit Reconcile Info</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('name', __('business.recon_name') . ':*') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>
                            {!! Form::text('name', $reconcile->name, [
                                'class' => 'form-control',
                                'required',
                                'placeholder' => __('business.recon_name'),
                            ]) !!}
                        </div>
                    </div>
                </div>


                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('date', __('business.date') . ':') !!}
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-calendar"></i>
                            </span>

                            {!! Form::text('date', $reconcile->date, [
                                'class' => 'form-control dob-date-picker',
                                'placeholder' => __('business.date'),
                                'readonly', 'required'
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

        </div>
    </div>
    <script>
    $(document).ready(function() {
        $(document).on('shown.bs.modal', '.edit_reconcile_modal', function(e) {
    $('.dob-date-picker').datepicker({
      autoclose: true,
      endDate: 'today',
      format: 'yyyy-mm-dd',
    });
});
        $('form#reconcile_update_form').submit(function(event) {
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
                            $('#reconcile_table').DataTable().ajax.reload();
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
        $('#reconcile_update_form').validate({
            rules: {
                name: {
                    required: true,
                    remote: {
                        url: '{{ route("reconciles.checkUniqueName") }}', // Adjust the route as necessary
                        type: 'POST',
                        data: {
                            name: function() {
                                var reconcile = @json($reconcile);
                                var oldName = reconcile['name'];
                                return oldName !== $('#name').val() ? $('#name').val() : null;
                            }
                        }
                    }
                },
                date: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: 'Please enter a reconcile name.',
                    remote: 'This name is already taken.'
                },
                date: {
                    required: 'Please select a date.',
                },
            },
        });
    });
    </script>
        