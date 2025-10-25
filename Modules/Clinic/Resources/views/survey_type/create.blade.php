<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\SurveyTypeController::class, 'store']),
            'method' => 'post',
            'id' => 'survey_type_store_form',
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add New Survey Type</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('clinic::lang.survey_name') . ':*') !!}
                {!! Form::text('name', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('clinic::lang.survey_name'),
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('survey_wise_date', 'How many days will it take to collect patient feedback? *') !!} 
                {!! Form::number('date_counting', null, ['class' => 'form-control', 'min' => '1', 'placeholder' => 'Days', 'required']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('survey_wise_date', 'How many prior days of extra data can I view? *') !!} 
                {!! Form::number('date_counting_with_pre_date', 7, ['class' => 'form-control', 'min' => '1', 'placeholder' => 'Days', 'required']) !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
