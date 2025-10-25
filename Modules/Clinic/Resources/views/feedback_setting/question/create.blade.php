<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\FeedbackQuestionController::class, 'store']),
            'method' => 'post',
            'id' => 'feedback_question_store_form',
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Add New Feedback Question</h4>
        </div>

        <div class="modal-body">
            <div class="form-group hide">
                {!! Form::label('Survey Type', __('clinic::lang.survey_type') . ':*') !!} 
                {!! Form::select('survey_type_id', $suveyTypes, null, ['class' => 'form-control select2', 'required', 'placeholder' => 'Select a Survey Type', 'id' => 'survey_type_id', 'style' => 'width:100%;']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('role_id', 'Feedback Role:*') !!}
                {!! Form::select('feedback_role_id', $roles, null, [
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
                                {!! Form::checkbox('display_bn', 1, false, ['class' => 'input-icheck', 'id' => 'display_bn']) !!}
                            </div>
                        </div>
                    </div>
                </div>
                {!! Form::textarea('question_text', null, [
                    'class' => 'form-control',
                    'required',
                    'rows' => 2,
                    'placeholder' => 'Please fill out this field',
                    'id' => 'question_text',
                ]) !!}
            </div>

            <div class="form-group d-flex justify-content-between">
                <!-- Label + Textarea on the left -->
                <div style="flex: 1;">
                    
                    {!! Form::label('question_text_bn', __('clinic::lang.feedback_question_bn'), ['class' => 'form-label']) !!}

                    {!! Form::textarea('question_text_bn', null, [
                        'class' => 'form-control',
                        'rows' => 2,
                        'id' => 'question_text_bn',
                        'placeholder' => __('clinic::lang.feedback_question'),
                    ]) !!}
                </div>
            
               
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
                    null,
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
                    <div id="question-options" class="flex-grow-1"></div>

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

    .gap-2>*+* {
        margin-left: 0.5rem;
    }
</style>

<script>
    $(document).ready(function() {
        $(".select2").select2();
       var survey = $('#survey_types_filter').val();
       $('#survey_type_id').val(survey).trigger('change');

        $('input[type="checkbox"].input-icheck, input[type="radio"].input-icheck').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue'
        });

        function renderOptions(type) {
            const container = $('#question-options');
            container.empty();
            $('#add_option').hide();

            if (['multiple_choice', 'checkbox'].includes(type)) {
                $('#question-options-container').show();
                $('#add_option').show();
                container.append(`<div class="input-group option-row">
                            <input type="text" name="options[]" class="form-control mb-2" required placeholder="Option" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat remove_option_this_row"
                                    data-name=""><i class="fa fa-minus-circle text-danger fa-lg"></i></button>
                                </button>
                            </span>
                        </div>
                
                
                `);
            } else if (type === 'star_rating') {
                $('#question-options-container').show();
                container.append(`
                    <label>Star Preview:</label>
                    <div id="star_preview_wrapper" class="mb-2">
                        <div id="star_preview_label" class="mb-1 text-center">Rating Preview</div>
                        <div id="star_preview"></div>
                    </div>
                `);

                const starCount = 5;
                const stars = [...Array(starCount)].map(() => `<div class="star-item">★</div>`).join('');
                $('#star_preview').html(stars);
            } else {
                $('#question-options-container').hide();
            }
        }

        $('#question_type').change(function() {
            renderOptions($(this).val());
        });

        $('#add_option').click(function() {
            $('#question-options').append(
                `<div class="input-group option-row">
                            <input type="text" name="options[]" class="form-control mb-2" required placeholder="Option" />
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-default bg-white btn-flat remove_option_this_row"
                                    data-name=""><i class="fa fa-minus-circle text-danger fa-lg"></i></button>
                                </button>
                            </span>
                        </div>`
            );
        });
        $(document).on('ifChecked', '#display_bn', function() {
            const questionText = $('#question_text').val().trim();
            const questionTextBn = $('#question_text_bn').val().trim();

            if (questionText !== '' && questionTextBn === '') {
                // Translate English to Bangla using fetch
                fetch(
                        `https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=bn&dt=t&q=${encodeURIComponent(questionText)}`
                    )
                    .then(response => response.json())
                    .then(data => {
                        if (data && data[0]) {
                            const translated = data[0][0][0];
                            $('#question_text_bn').val(translated);
                        }
                    })
                    .catch(error => {
                        console.error('Translation error:', error);
                        alert('Could not translate question.');
                    });
            }
        });
    });
</script>
