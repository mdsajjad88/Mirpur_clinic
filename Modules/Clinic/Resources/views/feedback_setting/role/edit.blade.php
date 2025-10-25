<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\FeedbackRoleController::class, 'update'],[$role->id]),
            'method' => 'PUT',
            'id' => 'feedback_role_update_form',
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"> Edit Feedback Role Info</h4>
        </div>

        <div class="modal-body">
            
            <div class="form-group">
                {!! Form::label('role_name', __('clinic::lang.feedback_role_name') . ':*') !!}
                {!! Form::text('role_name', $role->role_name, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('clinic::lang.feedback_role_name'),
                ]) !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
{{--  --}}
<script>
    $(document).ready(function() {
        $(document).on('submit', '#feedback_role_update_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();

            $.ajax({
                method: 'POST',
                url: $(this).attr('action'),
                dataType: 'json',
                data: data,
                beforeSend: function(xhr) {
                    __disable_submit_button(form.find('button[type="submit"]'));
                },
                success: function(result) {
                    if (result.success == true) {
                        $('div.role_create_modal').modal('hide');
                        toastr.success(result.msg);
                        
                        window.dispatchEvent(evt);

                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        });
    })
</script>