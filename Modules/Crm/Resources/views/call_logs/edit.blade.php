<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        @php
            $form_id = 'call_log_update_form';
            $url = action([\Modules\Crm\Http\Controllers\CallLogController::class, 'update'], [$call_log->id]);
        @endphp

        {!! Form::open(['url' => $url, 'method' => 'put', 'id' => $form_id]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('crm::lang.edit_call_log_info')</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    {!! Form::label('call_subject_id', __('crm::lang.call_subject') . ':*') !!}
                    {!! Form::select('call_subject_id[]', $call_subjects->toArray(), $selected_subjects, [
                        'class' => 'form-control select2',
                        'id' => 'call_subject_id',
                        'required',
                        'multiple',
                        'style' => 'width: 100% !important;',
                    ]) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    {!! Form::label('call_tag_id', __('crm::lang.call_tag') . ':*') !!}
                    {!! Form::select('call_tag_id[]', $tags->toArray(), $selected_tags, [
                        'class' => 'form-control select2',
                        'id' => 'call_tag_id',
                        'required',
                        'multiple',
                        'style' => 'width: 100% !important;',
                    ]) !!}
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    {!! Form::label('note', __('crm::lang.note')) !!}
                    {!! Form::textarea('note', $call_log->note, [
                        'class' => 'form-control',
                        'rows' => 4,
                        'placeholder' => __('crm::lang.note'),
                        'style' => 'font-size: 22px;',
                    ]) !!}
                </div>
            </div>
        </div>

        <div class="modal-footer">
            {!! Form::submit(__('messages.save'), ['class' => 'btn btn-primary']) !!}
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
<script>
    $('.select2').select2({});
</script>