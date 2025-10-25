<div class="modal-dialog" role="document">
    <div class="modal-content">
        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\FeedbackQuestionController::class, 'update'], [$question->id]),
            'method' => 'PUT',
            'id' => 'feedback_question_update_form',
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Edit Feedback Question</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('Survey Type', __('clinic::lang.survey_type') . ':*') !!} 
                {!! Form::select('survey_type_id', $suveyTypes, $question->survey_type_id??'', ['class' => 'form-control select2', 'required', 'placeholder' => 'Select a Survey Type', 'id' => 'survey_type_id', 'style' => 'width:100%;']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('role_id', 'Feedback Role:*') !!}
                {!! Form::select('feedback_role_id', $roles, $question->feedback_role_id, [
                    'class' => 'form-control select2',
                    'placeholder' => 'Select Feedback Role',
                    'id' => 'roles_id',
                    'style' => 'width:100%',
                    'required',
                ]) !!}
            </div>

            <div class="form-group">
                <div class="row">
                    <div class="col-md-8">
                        {!! Form::label('question_text', __('clinic::lang.feedback_question') . ':*') !!}
                    </div>
                    <div class="col-md-4">
                        <div class="row">
                            <div class="col-md-8">
                                {!! Form::label('display_bn', 'Display BN:', ['class' => 'form-check-label']) !!}
                            </div>
                            <div class="col-md-4">
                                {!! Form::checkbox('display_bn', 1, $question->display_bn == 1, ['class' => 'input-icheck']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                {!! Form::textarea('question_text', $question->question_text, [
                    'class' => 'form-control',
                    'required',
                    'rows' => 2,
                    'placeholder' => 'Please fill out this field',
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('question_text_bn', __('clinic::lang.feedback_question_bn')) !!}
                {!! Form::textarea('question_text_bn', $question->question_text_bn ?? '', [
                    'class' => 'form-control',
                    'rows' => 2,
                    'placeholder' => __('clinic::lang.feedback_question'),
                ]) !!}
            </div>

            <div class="form-group">
                {!! Form::label('question_type', 'Question Type:*') !!}
                {!! Form::select(
                    'question_type',
                    [
                        'short_text' => 'Short Answer',
                        'multiple_choice' => 'Multiple Choice',
                        'checkbox' => 'Checkboxes',
                        'star_rating' => 'Star Rating (1–5)',
                    ],
                    $question->question_type,
                    [
                        'class' => 'form-control',
                        'required',
                        'id' => 'question_type',
                        'placeholder' => 'Select Question Type',
                    ],
                ) !!}
            </div>

            <div id="question-options-container" style="display: none;">
                <label class="d-flex justify-content-between align-items-center">
                    <span>Options:</span>
                    <button type="button" class="btn btn-sm btn-success mt-1" id="add_option" style="display: none;">
                        <i class="fa fa-plus-circle text-light fa-lg"></i>
                    </button>
                </label>                
                <div class="d-flex align-items-center gap-2 option-row">
                    <div id="question-options" class="flex-grow-1">
                    </div>
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

<style>
    #star_preview {
        display: flex;
        justify-content: space-between;
        padding: 0 1rem;
    }
    .star-item {
        flex: 1;
        text-align: center;
        font-size: 2rem;
        color: gold;
    }
    #star_preview_wrapper {
        max-width: 500px;
        margin: auto;
    }
    .gap-2 > * + * {
        margin-left: 0.5rem;
    }
</style>

<script>
    $(document).ready(function() {
        $(".select2").select2();

        $('input[type="checkbox"].input-icheck, input[type="radio"].input-icheck').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue'
        });

        function renderOptions(type) {
            const container = $('#question-options');
            container.empty();
            $('#add_option').hide();
            $('#question-options-container').hide();

            if (['multiple_choice', 'checkbox'].includes(type)) {
                $('#question-options-container').show();
                $('#add_option').show();

                const existingOptions = @json($answers ?? []);
                if (existingOptions.length > 0 && type === '{{ $question->question_type }}') {
                    console.log('Existing Options:', existingOptions);
                    existingOptions.forEach(option => {
                        container.append(`
                            <div class="input-group option-row">
                                <input type="hidden" name="options[][id]" required value="${option.id}" />
                                <input type="text" name="options[][text]" class="form-control mb-2" value="${option.option_text}" placeholder="Option" />
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat remove_option_this_row">
                                        <i class="fa fa-minus-circle text-danger fa-lg"></i>
                                    </button>
                                </span>
                            </div>
                        `);
                    });
                } else {
                    container.append(`
                        <div class="input-group option-row">
                            <input type="text" name="options[][text]" required class="form-control mb-2" placeholder="Option" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat remove_option_this_row">
                                    <i class="fa fa-minus-circle text-danger fa-lg"></i>
                                </button>
                            </span>
                        </div>
                    `);
                }

            } else if (type === 'star_rating') {
                $('#question-options-container').show();
                container.append(`
                    <label>Star Preview:</label>
                    <div id="star_preview_wrapper" class="mb-2">
                        <div id="star_preview_label" class="mb-1 text-center">Rating Preview</div>
                        <div id="star_preview"></div>
                    </div>
                `);

                const stars = Array.from({ length: 5 }, () => '<div class="star-item">★</div>').join('');
                $('#star_preview').html(stars);
            }
        }

        $('#question_type').change(function() {
            renderOptions($(this).val());
        });

        $('#add_option').click(function() {
            $('#question-options').append(`
                <div class="input-group option-row">
                    <input type="text" name="options[][text]" class="form-control mb-2" required placeholder="Option" />
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat remove_option_this_row">
                            <i class="fa fa-minus-circle text-danger fa-lg"></i>
                        </button>
                    </span>
                </div>
            `);
        });

        $(document).on('click', '.remove_option_this_row', function() {
            $(this).closest('.option-row').remove();
        });

        renderOptions('{{ $question->question_type }}');
    });
</script>
