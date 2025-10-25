<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\Modules\Crm\Http\Controllers\ScheduleController::class, 'updateStatus'], ['follow_up' => $schedule->id]), 'method' => 'post', 'id' => 'edit_schedule_status' ]) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    @lang('crm::lang.edit_schedule_status')
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                       <div class="form-group">
                            {!! Form::label('status', __('sale.status') .':') !!}
                            {!! Form::select('status', $statuses, $schedule->status, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;', 'id' => 'follow_up_edit_status']) !!}
                       </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">
                    @lang('messages.update')
                </button>
                <button type="button" class="btn btn-default" data-dismiss="modal">
                @lang('messages.close')
                </button>
            </div>
        {!! Form::close() !!}
    </div>
</div>
