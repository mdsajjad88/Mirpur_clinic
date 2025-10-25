<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\FeedbackAnswerController::class, 'store']),
            'method' => 'post',
            'id' => 'feedback_answer_store_form',
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add New Answer against Question</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('question_text', __('clinic::lang.feedback_question') . ':*') !!}
                {!! Form::select('feedback_question_id', $questions, null, [
                    'class' => 'form-control',
                    'required',
                    'id' => 'question_id',
                    'placeholder' => 'Select a question',
                    'style' => 'width:100%',
                ]) !!}
            </div>
            <div class="form-group option-row">
                <div class="label-holder">
                    {!! Form::label('question_text', __('clinic::lang.feedback_answer') . ':*') !!}
                </div>                <div class="row">
                    <div class="col-md-6">
                        {!! Form::text('option_text[]', null, ['placeholder' => 'Answer','required', 'class' => 'form-control']) !!}
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            {!! Form::number('rating_value[]', null, ['placeholder' => 'Rating Value','required', 'class' => 'form-control']) !!}
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat add_new_option_row"
                                    data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>

                                </button>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('is_display_rating_value', 'Display Rating Value: ') !!}
                {!! Form::checkbox('is_display_rating_value', 1, false, ['class' => 'input-icheck']) !!}
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
    $("#question_id").select2();
    $('input[type="checkbox"].input-icheck, input[type="radio"].input-icheck').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue'
        });
</script>
