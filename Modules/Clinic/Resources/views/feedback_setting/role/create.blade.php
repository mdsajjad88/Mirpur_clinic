<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\FeedbackRoleController::class, 'store']),
            'method' => 'post',
            'id' => 'feedback_role_store_form',
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add New Feedback Role</h4>
        </div>

        <div class="modal-body">
            
            <div class="form-group">
                {!! Form::label('role_name', __('clinic::lang.feedback_role_name') . ':*') !!}
                {!! Form::text('role_name', null, [
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
<script>
    $('#survey_type_id').select2();
</script>