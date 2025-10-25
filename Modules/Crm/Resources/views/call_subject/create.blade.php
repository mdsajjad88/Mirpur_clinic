<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        @php
            $form_id = 'call_subject_add_form';
            $url = action([\Modules\Crm\Http\Controllers\CrmCallSubjectController::class, 'store']);
        @endphp

        {!! Form::open(['url' => $url, 'method' => 'POST', 'id' => $form_id]) !!}
        
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('crm::lang.add_call_subject')</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('crm::lang.name') . ':*') !!}
                {!! Form::text('name', null, ['class' => 'form-control', 'required', 'id' => 'name']) !!}
            </div>
        </div>

        <div class="modal-footer">
            {!! Form::submit(__('messages.save'), ['class' => 'btn btn-primary']) !!}
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>
