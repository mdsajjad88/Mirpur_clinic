<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\FeedbackAnswerController::class, 'update'], [$id]),
            'method' => 'put',
            'id' => 'feedback_answer_update_form',
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Edit Answer(s) for Question</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('question_text', __('clinic::lang.feedback_question') . ':*') !!}
                {!! Form::select('feedback_question_id', $questions, $id, [
                    'class' => 'form-control',
                    'required',
                    'disabled',
                    'id' => 'question_id',
                    'style' => 'width:100%;',
                ]) !!}
            </div>

            {{-- Existing options --}}
            @foreach($answers as $index => $answer)
            <div class="form-group option-row">
                <div class="label-holder">
                    @if ($index == 0)
                        {!! Form::label('question_text', __('clinic::lang.feedback_answer') . ':*') !!}
                    @endif
                </div>
                <div class="row">
                    <div class="col-md-6">
                        {!! Form::text('option_text[]', $answer->option_text, ['placeholder' => 'Answer', 'required', 'class' => 'form-control']) !!}
                        {!! Form::hidden('answer_ids[]', $answer->id) !!}
                    </div>
                    <div class="col-md-6">
                        <div class="input-group">
                            {!! Form::number('rating_value[]', $answer->rating_value, ['placeholder' => 'Rating Value', 'required', 'class' => 'form-control']) !!}
                            @if ($index == 0)
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat add_new_option_row">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </button>
                            </span>
                            @else
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat remove_option_this_row">
                                    <i class="fa fa-minus-circle text-danger fa-lg"></i>
                                </button>
                            </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach

            <div class="form-group">
                {!! Form::label('is_display_rating_value', 'Display Rating Value: ') !!}
                {!! Form::checkbox('is_display_rating_value', 1, $answers->first()->is_display_rating_value == 1, ['class' => 'input-icheck']) !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>

<script>
    $("#question_id").select2();
    $('input[type="checkbox"].input-icheck, input[type="radio"].input-icheck').iCheck({
        checkboxClass: 'icheckbox_square-blue',
        radioClass: 'iradio_square-blue'
    });
</script>
