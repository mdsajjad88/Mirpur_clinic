<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open([
            'url' => action([\Modules\Crm\Http\Controllers\ScheduleController::class, 'store']),
            'method' => 'post',
            'id' => 'add_schedule',
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" id="myModalLabel">
                @lang('crm::lang.add_schedule')
            </h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <input type="hidden" name="schedule_for" value="{{ $schedule_for }}" id="schedule_for">
                <div class="col-md-8">
                    <div class="form-group">
                        {!! Form::label('title', __('crm::lang.title') . ':') !!} <span class="text-danger">*</span>
                        {!! Form::text('title', null, ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('contact_id', __('contact.customer') . '/' . __('crm::lang.lead') . ':') !!} <span class="text-danger">*</span>
                        @if (!empty($contact_id))
                            {!! Form::select('contact_id', $oneCustomer, $contact_id, [
                                'class' => 'form-control select2',
                                'style' => 'width: 100%;',
                                'required',
                                'disabled',
                            ]) !!}
                            <input type="hidden" name="contact_id" value="{{ $contact_id }}">
                        @else
                            {!! Form::select('contact_id', $customers, null, [
                                'class' => 'form-control select2',
                                'placeholder' => __('messages.please_select'),
                                'required',
                                'style' => 'width: 100%;',
                            ]) !!}
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('status', __('sale.status') . ':') !!} <span class="text-danger">*</span>
                        {!! Form::select('status', $statuses, null, [
                            'class' => 'form-control select2',
                            'placeholder' => __('messages.please_select'),
                            'style' => 'width: 100%;',
                            'id' => 'follow_up_create_status',
                            'required',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('start_datetime', __('crm::lang.start_datetime') . ':') !!} <span class="text-danger">*</span>
                        {!! Form::text('start_datetime', null, ['class' => 'form-control datetimepicker', 'required', 'readonly']) !!}
                    </div>
                </div>
            </div>
            <div class="row hide">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('schedule_type', __('crm::lang.schedule_type') . ':') !!}
                        {!! Form::select('schedule_type', $follow_up_types, null, [
                            'class' => 'form-control select2',
                            'placeholder' => __('messages.please_select'),
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('followup_category_id', __('crm::lang.followup_category') . ':') !!}
                        {!! Form::select('followup_category_id', $followup_category, null, [
                            'class' => 'form-control select2',
                            'style' => 'width: 100%;',
                            'placeholder' => __('messages.please_select'),
                        ]) !!}
                    </div>
                </div>
            </div>
            {{-- <div class="row hide">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>
                                {!! Form::checkbox('allow_notification', 1, false, ['class' => 'input-icheck', 'id' => 'allow_notification']) !!}
                                @lang('crm::lang.send_notification')
                            </label>
                            @show_tooltip(__('crm::lang.send_schedule_notificatoion'))
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="allow_notification_elements hide">
                        <div class="col-md-6">
                            {!! Form::label('notify_via', __('crm::lang.notify_via') .':*') !!} 
                            <div class="form-group checkbox-inline">
                                <label>
                                    {!! Form::checkbox('notify_via[sms]', 1, false, ['class' => 'input-icheck']) !!}
                                    @lang('crm::lang.sms')
                                </label>
                            </div>
                            <div class="form-group checkbox-inline">
                                <label>
                                    {!! Form::checkbox('notify_via[mail]', 1, true, ['class' => 'input-icheck']) !!}
                                    @lang('business.email')
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="multi-input">
                                    {!! Form::label('notify_before', __('crm::lang.notify_before') . ':*') !!}
                                    <br/>
                                    {!! Form::number('notify_before', null, ['class' => 'form-control width-40 pull-left', 'placeholder' => __('crm::lang.notify_before'), 'required']) !!}

                                    {!! Form::select('notify_type', $notify_type, '', ['class' => 'form-control width-60 pull-left']) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div> --}}
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('user_id', __('crm::lang.assgined') . ':*') !!}
                        {!! Form::select('user_id[]', $users, auth()->user()->id, [
                            'class' => 'form-control select2',
                            'multiple',
                            'required',
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
                @lang('messages.close')
            </button>
            <button type="submit" class="btn btn-primary">
                @lang('messages.save')
            </button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
