<div class="modal-dialog" role="document">
    <form id="callStatusForm" method="POST" action="{{ route('update.call.status') }}">
        @csrf
        <input type="hidden" name="id" value="{{ $prescription->id }}">

        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">@lang('lang_v1.edit_call_status')</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('call_status', __('lang_v1.call_status') . ':*') !!}
                            {!! Form::select('call_status', $statuses, $prescription->call_status ?? null, [
                                'class' => 'form-control',
                                'placeholder' => __('messages.please_select'),
                                'required',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('call_note', __('lang_v1.notes') . ':') !!}
                            {!! Form::textarea('call_note', $prescription->call_note ?? '', [
                                'class' => 'form-control',
                                'placeholder' => __('lang_v1.notes'),
                                'rows' => '4',
                            ]) !!}
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
            </div>
        </div>
    </form>
</div>
