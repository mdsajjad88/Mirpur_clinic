<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\SessionController::class, 'update'], [$session->id]),
            'method' => 'PUT',
            'id' => 'session_edit_form_clinic',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('clinic::lang.add_session')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('type', __('clinic::lang.type') . ':*') !!}
                {!! Form::select('type', $typeOptions, $session->type??'', [
                    'class' => 'form-control select2',
                    'required',
                    'id' => 'subcription_type',
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('sub_type', __('clinic::lang.sub_type') . ':*') !!}
                {!! Form::select('sub_type', $subTypes, $session->sub_type??'', [
                    'class' => 'form-control select2',
                    'required',
                    'id' => 'subcription_sub_type',
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('session_name', __('clinic::lang.session_name') . ':*') !!}
                {!! Form::text('session_name', $session->session_name ?? '', [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('clinic::lang.session_name'),
                ]) !!}
            </div>
            <div id="only_consultation" style="display: {{ $session->type == 'consultation' ? 'block' : 'none' }};">
                <div class="form-group">
                    {!! Form::label('product_id', __('clinic::lang.session_ammount') . ':*') !!}
                    {!! Form::select('product_id', $productOptions, $session->product_id ?: null, [
                        'class' => 'form-control select2',
                        'required',
                        'placeholder' => __('clinic::lang.session_ammount'),
                    ]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('duration_month', __('clinic::lang.duration_month') . ':') !!}
                    {!! Form::number('duration_month', $session->duration_month, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('clinic::lang.duration_month'),
                    ]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('total_visit', __('clinic::lang.total_visit') . ':') !!}
                    {!! Form::number('total_visit', $session->total_visit, [
                        'class' => 'form-control',
                        'required',
                        'placeholder' => __('clinic::lang.total_visit'),
                    ]) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('status', __('clinic::lang.status') . ':*') !!}
                    {!! Form::select('status', [1 => 'Active', 0 => 'Inactive'], $session->statu ?? '', [
                        'class' => 'form-control',
                        'required',
                    ]) !!}
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
    $(document).ready(function () {
    toggleConsultationFields();
});
</script>