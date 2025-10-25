<div class="row">
    <div class="col-md-6">
        <h4>{{ $contact->name }}</h4>
        <input type="hidden" id="customer_contact_id" value="{{ $contact->id }}">
        <p><strong>@lang('Mobile'):</strong> {{ $contact->mobile }}</p>
    </div>
    <div class="col-md-6 text-right">
        <div class="box-tools">
            @if($patientProfile)
                <a target="_blank" class="btn btn-primary show_patient_profile"
                    href="{{ action([\Modules\Clinic\Http\Controllers\PatientController::class, 'profile'], ['id' => $patientProfile->id]) }}">
                    @lang('crm::lang.profile')
                </a>

                <a target="_blank" class="btn btn-primary create_new_appointment_btn"
                    href="{{ action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'index']) }}">
                    @lang('crm::lang.appointment')
                </a>
            @endif
            <!-- Add this new button for follow-up -->
            <button type="button" class="btn btn-primary create_follow_up_btn"
                data-href="{{ action([\Modules\Crm\Http\Controllers\ScheduleController::class, 'create']) }}">
                @lang('crm::lang.create_follow_up')
            </button>
        </div>
    </div>
</div>
<hr>
<div id="feedback_form_container">
    <form action="{{ action([\Modules\Clinic\Http\Controllers\CallCenterFeedbackController::class, 'store']) }}" method="POST" id="feedback_store_form_in_call_center">
        @csrf
        <input type="hidden" name="survey_type_id" value="{{ $survey_type_id }}">
        <input type="hidden" name="start_time" value="{{ $start_time }}">
        <input type="hidden" name="contact_id" value="{{ $contact->id }}">
        <input type="hidden" name="campaign_id" value="{{ $campaignId }}">
        
        <div class="patient-feedback-header">
            <div class="patient-info">
                <span class="patient-name">
                    <i class="fa fa-user"></i> {{ $patientProfile->first_name ?? '' }}
                    {{ $patientProfile->last_name ?? '' }}
                </span>

                @if (!empty($patientProfile->mobile))
                    <span class="patient-contact">
                        <i class="fa fa-phone"></i> {{ $patientProfile->mobile }}
                    </span>
                @endif

                @if (!empty($district->name))
                    <span class="patient-location">
                        <i class="fa fa-map-marker"></i> {{ $district->name }}
                    </span>
                @endif
                @if (!empty($surveyName))
                    <span class="survey-name">
                        <i class="fa fa-clipboard-list"></i> {{ $surveyName }}
                    </span>
                @endif
            </div>

            @if($patientProfile)
                <div class="health-concerns">
                    <div class="concerns-label">
                        <i class="fa fa-heartbeat"></i> @lang('clinic::lang.health_concern'):
                    </div>
                    <div class="concerns-tags">
                        @foreach ($diseases->pluck('problem_name') as $disease)
                            <span class="tag">{{ $disease }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="form-group">
                <label>Call Status:*</label>
                <select name="call_status" class="form-control" required>
                    @foreach($callDropdown as $key => $value)
                        <option value="{{ $key }}" {{ $key === $calling_status ? 'selected' : '' }}>{{ $value }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="modal-body" style="padding: 30px;">
            @php $counting = 0; @endphp

            @foreach ($questions as $questionGroup)
                @php
                    $counting++;
                    $question = $questionGroup->first();
                    $answersList = $questionGroup;
                    $answerCount = $answersList->count();
                    $questionAnswers = $answers->get($question->id, collect());
                @endphp

                <div class="form-group">
                    <label for="question_{{ $question->id }}" class="question-label">
                        {{ $question->display_bn ? $counting . '. ' . $question->question_text_bn : $counting . '. ' . $question->question_text }}
                    </label>

                    @if ($question->question_type == 'multiple_choice')
                        <div style="display: flex; flex-wrap: wrap; width: 100%;">
                            @php
                                if ($question->is_show_form != 1) {
                                    $answerCount = $answerCount + 1;
                                }
                            @endphp
                            @foreach ($answersList as $answer)
                                <div style="width: {{ 100 / $answerCount }}%; padding: 5px;">
                                    <label style="display: block;">
                                        <input type="radio" name="question_{{ $question->id }}" value="{{ $answer->answer_id }}"
                                            class="input-icheck" data-is-show-form="{{ $question->is_show_form }}"
                                            {{ $questionAnswers->isNotEmpty() && $questionAnswers->first()->feedback_answer_id == $answer->answer_id ? 'checked' : '' }}>
                                        {{ $answer->option_text }}
                                    </label>
                                </div>
                            @endforeach
                            @if ($question->is_show_form != 1)
                                <label style="display: block; padding: 5px;">
                                    <input type="radio" name="question_{{ $question->id }}" value="__NA__"
                                        class="input-icheck na-checkbox"
                                        data-question-id="{{ $question->id }}"
                                        data-is-show-form="{{ $question->is_show_form }}"
                                        {{ $questionAnswers->isNotEmpty() && $questionAnswers->first()->is_n_a ? 'checked' : '' }}>
                                    N/A
                                </label>
                            @endif
                        </div>
                    @elseif ($question->question_type == 'checkbox')
                        <div style="display: flex; flex-wrap: wrap; width: 100%;">
                            @php
                                if ($question->is_show_form != 1) {
                                    $answerCount = $answerCount + 1;
                                }
                            @endphp
                            @foreach ($answersList as $answer)
                                <div style="width: {{ 100 / $answerCount }}%; padding: 5px;">
                                    <label style="display: block;">
                                        <input type="checkbox" name="question_{{ $question->id }}[]" value="{{ $answer->answer_id }}"
                                            class="input-icheck" data-is-show-form="{{ $question->is_show_form }}"
                                            {{ $questionAnswers->isNotEmpty() && $questionAnswers->pluck('feedback_answer_id')->contains($answer->answer_id) ? 'checked' : '' }}>
                                        {{ $answer->option_text }}
                                    </label>
                                </div>
                            @endforeach
                            @if ($question->is_show_form != 1)
                                <label style="display: block; padding: 5px;">
                                    <input type="checkbox" name="question_{{ $question->id }}[]" value="__NA__"
                                        class="input-icheck na-checkbox"
                                        data-question-id="{{ $question->id }}"
                                        data-is-show-form="{{ $question->is_show_form }}"
                                        {{ $questionAnswers->isNotEmpty() && $questionAnswers->first()->is_n_a ? 'checked' : '' }}>
                                    N/A
                                </label>
                            @endif
                        </div>
                    @elseif ($question->question_type == 'short_text')
                        <textarea name="question_{{ $question->id }}" class="form-control" placeholder="Your Answer"
                            data-is-show-form="{{ $question->is_show_form }}" rows="2">{{ $questionAnswers->isNotEmpty() ? $questionAnswers->first()->answer_text : '' }}</textarea>
                        @if ($question->is_show_form != 1)
                            <label style="display: block; padding: 5px;">
                                <input type="checkbox" name="question_{{ $question->id }}_na" value="1"
                                    class="input-icheck na-checkbox"
                                    data-question-id="{{ $question->id }}"
                                    data-is-show-form="{{ $question->is_show_form }}"
                                    {{ $questionAnswers->isNotEmpty() && $questionAnswers->first()->is_n_a ? 'checked' : '' }}>
                                N/A
                            </label>
                        @endif
                    @elseif ($question->question_type == 'star_rating')
                        <div class="star-rating" data-question-id="{{ $question->id }}"
                            data-required="{{ $question->is_show_form }}">
                            @for ($i = 1; $i <= 5; $i++)
                                <input type="radio" name="question_{{ $question->id }}" value="{{ $i }}"
                                    id="star{{ $i }}_{{ $question->id }}" class="star-rating-input"
                                    style="display: none;"
                                    {{ $questionAnswers->isNotEmpty() && $questionAnswers->first()->rating_value == $i ? 'checked' : '' }}>
                                <label for="star{{ $i }}_{{ $question->id }}"
                                    title="Rating {{ $i }}" class="star-rating-label">&#9733;</label>
                            @endfor
                            @if ($question->is_show_form != 1)
                                <input type="hidden" name="question_{{ $question->id }}" class="na-hidden-field"
                                    value="__NA__"
                                    {{ $questionAnswers->isNotEmpty() && $questionAnswers->first()->is_n_a ? '' : 'disabled' }}>

                                <label style="display: block; padding: 5px;">
                                    <input type="checkbox" class="input-icheck na-checkbox"
                                        data-question-id="{{ $question->id }}"
                                        {{ $questionAnswers->isNotEmpty() && $questionAnswers->first()->is_n_a ? 'checked' : '' }}>
                                    N/A
                                </label>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="form-group">
                <label>@lang('clinic::lang.comment'):</label>
                <input type="text" name="comment" class="form-control" id="comment" 
                    value="{{ $comment }}" placeholder="Enter comments">
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
        </div>
    </form>
</div>

<style>
    .star-rating {
        display: flex;
        flex-direction: row;
        justify-content: flex-start;
        align-items: center;
        width: 100%;
    }

    .star-rating-label {
        color: #ddd;
        font-size: 30px;
        cursor: pointer;
        width: 20%;
        text-align: center;
        display: inline-block;
        transition: color 0.2s ease-in-out;
    }

    .star-rating-label.filled {
        color: #f39c12;
    }

    .patient-feedback-header {
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 15px;
    }

    .patient-info {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
        margin-bottom: 10px;
    }

    .patient-name,
    .patient-contact,
    .patient-location {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .health-concerns {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-top: 8px;
    }

    .concerns-label {
        font-weight: bold;
    }

    .concerns-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .tag {
        background-color: #e3f2fd;
        color: #1976d2;
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 0.85em;
    }

    .survey-name {
        font-size: 24px;
        font-weight: bold
    }

    .question-label.validation-error {
        color: red !important;
    }
</style>

<script>
    $(document).ready(function() {
        $('input[type="checkbox"].input-icheck, input[type="radio"].input-icheck').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue'
        });

        function updateStars($container, rating) {
            $container.find('.star-rating-label').each(function() {
                var starValue = parseInt($(this).attr('for').match(/star(\d+)_/)[1]);
                $(this).toggleClass('filled', starValue <= rating);
            });
        }

        $('.star-rating-label').on('mouseenter', function() {
            var $container = $(this).closest('.star-rating');
            var starValue = parseInt($(this).attr('for').match(/star(\d+)_/)[1]);
            updateStars($container, starValue);
        });

        $('.star-rating').on('mouseleave', function() {
            var $container = $(this);
            var selectedValue = $container.find('.star-rating-input:checked').val();
            updateStars($container, selectedValue);
        });

        $('.star-rating-input').on('change', function() {
            var $container = $(this).closest('.star-rating');
            var selectedValue = $(this).val();
            updateStars($container, selectedValue);
            var $naCheckbox = $container.find('.na-checkbox');
            var $hiddenInput = $container.find('.na-hidden-field');
            $naCheckbox.iCheck('uncheck');
            $hiddenInput.removeAttr('name');
        });

        $('.star-rating').each(function() {
            var $container = $(this);
            var selectedValue = $container.find('.star-rating-input:checked').val();
            updateStars($container, selectedValue);
        });

        function toggleModalBody() {
            var callStatus = $('select[name="call_status"]').val();
            if (callStatus === 'Received') {
                $('.modal-body').show();
            } else {
                $('.modal-body').hide();
            }
        }
        toggleModalBody();
        $('select[name="call_status"]').on('change', function() {
            toggleModalBody();
        });

        $(document).on('ifChanged', '.na-checkbox', function() {
            var questionId = $(this).data('question-id');
            const $starContainer = $(`.star-rating[data-question-id="${questionId}"]`);
            var $hiddenInput = $starContainer.find('.na-hidden-field');
            var hiddenField = $('input.na-hidden-field[name="question_' + questionId + '"]');
            var radioInputs = $('input[name="question_' + questionId + '"][type="radio"]');

            if ($(this).is(':checked')) {
                var $formGroup = $(this).closest('.form-group');
                $formGroup.find('input[type="radio"]').not(this).iCheck('uncheck');
                $formGroup.find('input[type="checkbox"]').not(this).iCheck('uncheck');
                $formGroup.find('textarea').val('');

                updateStars($starContainer, 0);
                radioInputs.prop('checked', false).prop('disabled', true);
                hiddenField.prop('disabled', false);
            } else {
                $hiddenInput.removeAttr('name');
                radioInputs.prop('disabled', false);
                hiddenField.prop('disabled', true);
            }
        });

        $(document).on('input change', '.form-group input:not(.na-checkbox), .form-group textarea', function() {
            var $formGroup = $(this).closest('.form-group');
            $formGroup.find('.na-checkbox').iCheck('uncheck').trigger('change');
        });
    });
</script>