<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\Modules\Crm\Http\Controllers\ScheduleLogController::class, 'store']), 'method' => 'post', 'id' => 'schedule_log_form' ]) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    @lang('crm::lang.add_schedule_log')
                </h4>
            </div>
            <div class="modal-body">
                <!-- schedule id -->
                <input type="hidden" name="schedule_id" value="{{$schedule->id}}">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('subject', __('crm::lang.subject') . ':*') !!}
                            {!! Form::text('subject', null, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('log_type', __('crm::lang.log_type') .':*') !!}
                            {!! Form::select('log_type', ['call' => __('crm::lang.call'), 'sms' => __('crm::lang.sms'), 'meeting' => __('crm::lang.meeting'), 'email' => __('business.email')], $schedule->schedule_type, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                       <div class="form-group">
                            {!! Form::label('start_datetime', __('crm::lang.start_datetime') . ':*' )!!}
                            {!! Form::text('start_datetime', null, ['class' => 'form-control datetimepicker', 'required', 'readonly']) !!}
                       </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('end_datetime', __('crm::lang.end_datetime') . ':*' )!!}
                            <select name="end_datetime" class="form-control select2" style="width: 100%;" required>
                                <option value="">{{ __('messages.please_select') }}</option>
                                <option value="5 minutes">@lang('lang_v1.5_minutes')</option>
                                <option value="10 minutes">@lang('lang_v1.10_minutes')</option>
                                <option value="20 minutes">@lang('lang_v1.20_minutes')</option>
                                <option value="30 minutes">@lang('lang_v1.30_minutes')</option>
                                <option value="45 minutes">@lang('lang_v1.45_minutes')</option>
                                <option value="1 hour">@lang('lang_v1.1_hour')</option>
                                <option value="2 hours">@lang('lang_v1.2_hours')</option>
                                <option value="3 hours">@lang('lang_v1.3_hours')</option>
                                <option value="4 hours">@lang('lang_v1.4_hours')</option>
                                <option value="5 hours">@lang('lang_v1.5_hours')</option>
                                <option value="6 hours">@lang('lang_v1.6_hours')</option>
                                <option value="7 hours">@lang('lang_v1.7_hours')</option>
                                <option value="1 day">@lang('lang_v1.1_day')</option>
                                <option value="2 days">@lang('lang_v1.2_days')</option>
                                <option value="3 days">@lang('lang_v1.3_days')</option>
                                <option value="4 days">@lang('lang_v1.4_days')</option>
                                <option value="5 days">@lang('lang_v1.5_days')</option>
                                <option value="6 days">@lang('lang_v1.6_days')</option>
                                <option value="7 days">@lang('lang_v1.7_days')</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('description', __('lang_v1.description') . ':') !!}
                            {!! Form::textarea('description', null, ['class' => 'form-control ', 'id' => 'description']) !!}
                        </div>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-4">
                       <div class="form-group">
                            {!! Form::label('status', __('crm::lang.schedule_status') .':') !!}
                            {!! Form::select('status', $statuses, $schedule->status, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']) !!}
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