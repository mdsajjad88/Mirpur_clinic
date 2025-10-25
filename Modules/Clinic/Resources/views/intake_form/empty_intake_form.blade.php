<style>
    .star {
        color: red;
        font-size: 22px;
    }

    .line-container {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        margin-top: 10px;
        margin-bottom: 10px;
    }

    .line {
        flex-grow: 1;
        border-top: 1px solid black;
    }

    .text {
        margin: 0 15px;
        font-size: 18px;
        /* Adjusted to match normal text size */
        font-weight: bold;
    }
</style>

@component('components.widget', ['class' => 'box-primary'])
    <div class="form-header">
        <h2>Health Information</h2>
        <p>Please provide medical and laboratory test reports from the past 8 years with this form.</p>
    </div>

    {!! Form::open([
        'url' => action([\Modules\Clinic\Http\Controllers\Survey\IntakeFormController::class, 'store']),
        'method' => 'post',
        'id' => 'intakeFormSubmit',
    ]) !!}

    <div class="row">
        <div class="col-md-4">
            {!! Form::label('last_visited_date', 'Date:') !!}<span class="star">*</span>
            {!! Form::date('last_visited_date', \Carbon\Carbon::now()->format('Y-m-d'), [
                'class' => 'form-control',
                'required',
            ]) !!}
        </div>

        <div class="col-md-4">
            {!! Form::label('first_name', 'Name:') !!}<span class="star">*</span>
            {!! Form::text('first_name', null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Enter patient name',
            ]) !!}

        </div>
        <div class="col-md-4">
            {!! Form::label('age', 'Age:') !!}<span class="star">*</span>
            {!! Form::number('age', null, ['class' => 'form-control', 'required', 'placeholder' => 'Enter patient age']) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            {!! Form::label('gender', 'Gender:') !!}<span class="star">*</span>
            {!! Form::select(
                'gender',
                ['' => 'Select Gender', 'male' => 'Male', 'female' => 'Female', 'others' => 'Others'],
                null,
                [
                    'class' => 'form-control',
                    'required',
                ],
            ) !!}
        </div>
        <div class="col-md-4">
            {!! Form::label('profession', 'Profession:') !!}<span class="star">*</span>
            {!! Form::text('profession', null, [
                'class' => 'form-control',
                'required',
                'placeholder' => 'Enter patient profession',
            ]) !!}
        </div>
        <div class="col-md-4">
            {!! Form::label('blood_group', 'Blood Group:') !!}<span class="star"></span>
            {!! Form::select(
                'blood_group',
                [
                    'A+' => 'A+',
                    'A-' => 'A-',
                    'B+' => 'B+',
                    'B-' => 'B-',
                    'AB+' => 'AB+',
                    'AB-' => 'AB-',
                    'O+' => 'O+',
                    'O-' => 'O-',
                ],
                null,
                ['class' => 'form-control select2', 'placeholder' => 'Select Blood Group', 'id' => 'blood_group'],
            ) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            {!! Form::label('marital_status', 'Marital Status:') !!}<span class="star">*</span>
            {!! Form::select(
                'marital_status',
                ['' => 'Select Marital Status', 'married' => 'Married', 'unmarried' => 'Unmarried', 'other' => 'Other'],
                null,
                ['class' => 'form-control', 'required'],
            ) !!}
        </div>
        <div class="col-md-4">
            {!! Form::label('mobile', 'Mobile:') !!}<span class="star">*</span>
            {!! Form::number('mobile', null, [
                'class' => 'form-control',
                'id' => 'mobile_no',
                'required',
                'placeholder' => 'Enter Patient mobile no',
            ]) !!}
        </div>
        <div class="col-md-4">
            {!! Form::label('emergency_phone', 'Emergency Phone:') !!}<span class="star"></span>
            {!! Form::text('emergency_phone', null, [
                'class' => 'form-control',
                'placeholder' => 'Patient Emergency Contact No',
            ]) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            {!! Form::label('email', 'Email:') !!}<span class="star"></span>
            {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Enter patient email']) !!}
        </div>
        <div class="col-md-4">
            {!! Form::label('district_id', 'District') !!}<span class="star">*</span>
            {!! Form::select('district_id', $districts, null, [
                'class' => 'form-control',
                'placeholder' => 'Select City',
                'id' => 'district',
                'required',
            ]) !!}
            <div id="division_error" class="text-danger" style="display: none;"></div>
        </div>
        <div class="col-md-4">
            {!! Form::label('address', 'Address') !!} <span class="star"></span>
            {!! Form::text('address', null, [
                'class' => 'form-control',
                'placeholder' => 'Enter patient address',
            ]) !!}
        </div>
        <div class="col-md-4">
            {!! Form::label('reference_id', 'How did you hear about us?:') !!}<span class="star">*</span>
            <select name="reference_id" id="reference_id" class="form-control select2 reference_id" required>
                <option value="">Select Reference</option>
                @foreach ($references as $reference)
                    <option value="{{ $reference->id }}" data-details="{{ $reference->details }}">
                        {{ $reference->name }}
                    </option>
                @endforeach
            </select>

        </div>
        <div class="col-md-4" id="reference_details_wrapper" style="display: none">
            {!! Form::label('reference_details', 'Reference Details:') !!}<span class="star">*</span>
            {!! Form::text('reference_details', null, [
                'class' => 'form-control',
                'placeholder' => 'Details',
                'id' => 'reference_details',
                'required',
            ]) !!}
        </div>
    </div>

    <!-- Health History Section -->

    <div class="row">
        <div class="col-lg-12">
            <div class="line-container">
                <div class="line"></div>
                <h4 class="text">Health Concerns</h4>
                <div class="line"></div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mt-1" style="display: flex; align-items: center;">
        <div style="flex: 0 0 34%; display: inline-block;">
            {!! Form::label('problem_id', 'What is your primary health concern(s)?') !!}<span class="star" style="display: inline;">*</span>
        </div>
        <div class="filding" style="flex: 0 0 66%;">
            <div class="form-group">
                <div class="input-group">
                    {!! Form::select('problem_id[]', $problems, null, [
                        'class' => 'form-control select2 multipleProblem',
                        'id' => 'problem_id',
                        'multiple' => 'multiple',
                        'required',
                    ]) !!}
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-default bg-white btn-flat add_new_problem" data-name="">
                            <i class="fa fa-plus-circle text-primary fa-md"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    </div>



    <div class="col-md-4 mt-1" style="display: flex; align-items: center;">
        <div style="flex: 0 0 65%;">
            {!! Form::label('main_disease_duration_day', 'How long have you been experiencing this issue(s)?') !!}
        </div>
        <div style="flex: 0 0 25%;">
            {!! Form::text('main_disease_duration_day', null, [
                'class' => 'form-control',
                'placeholder' => 'Duration',
            ]) !!}
        </div>
        <div style="flex: 0 0 10%;">
            &nbsp; Days
        </div>
    </div>

    <div class="col-md-12 mt-1">
        {!! Form::label('main_disease', 'Describe your main health problems(Optional):') !!}
        {!! Form::textarea('main_disease', null, [
            'class' => 'form-control',
            'rows' => '3',
            'placeholder' => 'Describe your main health problems',
        ]) !!}
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="line-container">
                <div class="line"></div>
                <h4 class="text">Medical History </h4>
                <div class="line"></div>
            </div>
        </div>
    </div>


    <!-- Childhood Illness Section -->
    <div class="col-md-12 mt-1">
        <div class="row">
            <div class="col-md-4">
                <b>Did you have any chronic illness from childhood?</b>
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div>
                                {!! Form::radio('childhood_fitness_good', 0, false, ['class' => 'input-dcheck']) !!}
                                Yes
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div>
                                {!! Form::radio('childhood_fitness_good', 1, false, ['class' => 'input-dcheck']) !!}
                                No
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-1 hide chironic_diseases_wrapper"
                style="text-align: right !important; padding-right: 12px; font-size:16px; white-space: nowrap;">
                <b>Select <span class="star">*</span></b>
            </div>
            <div class="col-md-4 hide chironic_diseases_wrapper">
                {!! Form::select('chironic_illness[]', $chironic_diseases->pluck('name', 'id')->toArray(), null, [
                    'class' => 'form-control select2 chironic_diseases',
                    'multiple' => 'multiple',
                    'style' => 'width: 100% !important;',
                ]) !!}
            </div>
        </div>
    </div>

    <!-- Family History Section -->
    <div class="col-md-12 mt-1">
        <div class="row">
            <div class="col-md-4">
                {!! Form::label('family_history_disease', 'Does your family have any history of health conditions or disease?:') !!}
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div>
                                {!! Form::radio('is_family_disease', 1, false, ['class' => 'input-dcheck']) !!} Yes
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div>
                                {!! Form::radio('is_family_disease', 0, false, ['class' => 'input-dcheck']) !!} No
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-1 family_disease_wrapper hide"
                style="text-align: right !important; padding-right: 12px; font-size:16px; white-space: nowrap;">
                <b>Select <span class="star">*</span></b>
            </div>

            <div class="col-md-4 hide family_disease_wrapper">
                {!! Form::select('family_history_disease[]', $family_problems->pluck('name', 'id')->toArray(), null, [
                    'class' => 'form-control select2 family_history_disease',
                    'multiple' => 'multiple',
                    'style' => 'width: 100% !important;',
                ]) !!}
            </div>
        </div>
    </div>

    <!-- Other Doctor Reference Section -->
    <div class="col-md-12 mt-1">
        <div class="row">
            <div class="col-md-4" style="word-wrap: break-word; display: flex; align-items: center;">
                {!! Form::label('reference_doctor_id', 'Are you taking treatment with any other doctor / hospital?:', [
                    'style' => 'display: inline;',
                ]) !!}
                <span class="star" style="display: inline;">*</span>
            </div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div>
                                {!! Form::radio('is_other_doctor_reference', 1, false, ['class' => 'input-dcheck', 'required']) !!} Yes
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div>
                                {!! Form::radio('is_other_doctor_reference', 0, false, ['class' => 'input-dcheck', 'required']) !!} No
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-1 reference_doctor_wrapper hide"
                style="text-align: right !important; padding-right: 12px; font-size:16px; white-space: nowrap;">
                <b>Select <span class="star">*</span></b>
            </div>

            <div class="col-md-4 hide reference_doctor_wrapper">
                <div class="form-group">
                    <div class="input-group">
                        {!! Form::select('reference_doctor_id', $doctor_references->pluck('dr_name', 'id')->toArray(), null, [
                            'class' => 'form-control select2 reference_doctor_id',
                            'placeholder' => 'Select Reference Doctor',
                            'style' => 'width: 100% !important;',
                        ]) !!}
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default bg-white btn-flat add_new_doctor_reference"
                                data-name="">
                                <i class="fa fa-plus-circle text-primary fa-md"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-12 mt-1">
        <div class="row">
            <div class="col-md-4">
                <div style="word-wrap: break-word; display: flex; align-items: center;">
                    {!! Form::label('medicine', 'Are you taking any medication / supplement?:') !!}<span class="star" style="display: inline;">*</span>
                </div>
                <small> (for more than 1 month)</small>
            </div>

            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-6">
                        <div>
                            {!! Form::radio('is_old_prescribed_medicine', 1, false, ['class' => 'input-dcheck', 'required']) !!} Yes
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div>
                            {!! Form::radio('is_old_prescribed_medicine', 0, false, ['class' => 'input-dcheck', 'required']) !!} No
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-1 old_prescribed_medicine_wrapper hide"
                style="text-align: right !important; padding-right: 12px; font-size:16px; white-space: nowrap;">
                <b>Select <span class="star">*</span></b>
            </div>


            <div class="col-md-4 hide old_prescribed_medicine_wrapper">
                <div class="form-group">
                    <div class="input-group">
                        {!! Form::select('old_prescribed_medicine[]', $medicines, null, [
                            'class' => 'form-control select2 old_prescribed_medicine',
                            'multiple' => 'multiple',
                            'style' => 'width: 100%',
                        ]) !!}
                        <span class="input-group-btn">
                            <button type="button" class="btn btn-default bg-white btn-flat add_new_old_medicine"
                                data-name="">
                                <i class="fa fa-plus-circle text-primary fa-md"></i>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!! Form::hidden('patient_contact_id', null) !!}
    <div class="row">
        <div class="col-lg-12">
            <div class="line-container">
                <div class="line"></div>
                <h4 class="text">Lifestyle & Habits</h4>
                <div class="line"></div>
            </div>
        </div>
    </div>
    <!-- Sleep and Exercise Section -->
    <div class="col-md-12" style="margin-top: 5px !important;">
        <div class="row">
            <div class="col-md-3">{!! Form::label('daily_sleeping_hourse', 'How many hours do you sleep per night?') !!}</div>
            <div class="col-md-3">{!! Form::text('daily_sleeping_hourse', null, [
                'class' => 'form-control',
                'placeholder' => 'Patient Sleeping hours',
            ]) !!}</div>
            <div class="col-md-3">{!! Form::label('daily_exercize_minute', 'How many minutes do you exercise daily?') !!}</div>
            <div class="col-md-3">{!! Form::text('daily_exercize_minute', null, [
                'class' => 'form-control',
                'placeholder' => 'Patient exercise minute',
            ]) !!}</div>
        </div>
    </div>

    <div class="col-md-12 mt-1">
        <div class="row">
            <div class="col-md-3">{!! Form::label('is_sleeping_problem', 'Do you have trouble sleeping at night?') !!}</div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-4">
                        {!! Form::radio('is_sleeping_problem', 1, null, [
                            'class' => 'input-dcheck',
                        ]) !!} Yes
                    </div>
                    <div class="col-md-4">
                        {!! Form::radio('is_sleeping_problem', 0, null, [
                            'class' => 'input-dcheck',
                        ]) !!} No
                    </div>
                </div>
            </div>

            <div class="hide" id="reason_for_less_sleeping_wrapper">
                <div class="col-md-3">
                    {!! Form::label('reason_for_less_sleeping', 'If yes, why do you sleep less at night?') !!}<span class="star">*</span>
                </div>
                <div class="col-md-3">
                    {!! Form::textarea('reason_for_less_sleeping', null, [
                        'class' => 'form-control reason_for_less_sleeping',
                        'rows' => 2,
                        'placeholder' => 'Patient Less Sleeping reason',
                    ]) !!}
                </div>
            </div>

        </div>
    </div>
    <div class="col-md-12 mt-1">
        <div class="row">
            <div class="col-md-3">{!! Form::label('is_mentally_stress', 'Do you have chronic stress or anxiety?') !!}</div>
            <div class="col-md-3">
                <div class="row">
                    <div class="col-md-4">
                        {!! Form::radio('is_mentally_stress', 1, false, [
                            'class' => 'input-dcheck',
                        ]) !!} Yes
                    </div>
                    <div class="col-md-4">
                        {!! Form::radio('is_mentally_stress', 0, false, [
                            'class' => 'input-dcheck',
                        ]) !!} No
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Diet Section -->
    <div class="col-md-12 mt-1">
        <table style="width: 100%">
            <thead style="border: 1px solid rgb(235, 232, 232)">
                <tr>
                    <th colspan="2" class="text-center">
                        <b>Diet Discussion</b> <br> List all the food and drinks consumed in the past day
                    </th>
                </tr>
            </thead>
            <tbody class="text-center">
                <tr>
                    <td>{{ Form::label('breakfast', 'Breakfast') }}</td>
                    <td>{{ Form::text('breakfast', null, ['class' => 'form-control']) }}</td>
                </tr>
                <tr>
                    <td>{{ Form::label('lunch', 'Lunch') }}</td>
                    <td>{{ Form::text('lunch', null, ['class' => 'form-control']) }}</td>
                </tr>
                <tr>
                    <td>{{ Form::label('afternoon_snaks', 'Afternoon Snacks') }}</td>
                    <td>{{ Form::text('afternoon_snaks', null, ['class' => 'form-control']) }}
                    </td>
                </tr>
                <tr>
                    <td>{{ Form::label('dinner', 'Dinner') }}</td>
                    <td>{{ Form::text('dinner', null, ['class' => 'form-control']) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-md-12 mt-1">
        <div class="form-group text-center">
            <button class="btn btn-success" id="submit_intake_form">Update</button>
        </div>
    </div>

    {!! Form::close() !!}
    <div class="modal fade problem_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('clinic::survey.problem.create')
    </div>
    <div class="modal fade add_new_doctor_reference_modal" tabindex="-1" role="dialog"
        aria-labelledby="gridSystemModalLabel">
        @include('clinic::reference_doctor.create')
    </div>
    <div class="modal fade add_new_old_medicine_modal" tabindex="-1" role="dialog"
        aria-labelledby="gridSystemModalLabel">
        @include('clinic::old_medicine.create')
    </div>
@endcomponent

<script>
    $('#problem_id').select2({
        allowClear: true,
        placeholder: " Select patient primary health concern",

    });
    $('.family_history_disease').select2({
        allowClear: true,
        placeholder: " Select patient family disease",

    });
    $('.chironic_diseases').select2({
        allowClear: true,
        placeholder: " Select patient chronic disease",

    });

    $('#division').select2({
        allowClear: false,
    });
    $('#district').select2({
        allowClear: false,
    });
    $('.reference_id').select2({
        allowClear: false,
    });
    $('.reference_doctor_id').select2({
        allowClear: false,
    });
    $('.old_prescribed_medicine').select2({
        allowClear: false,
        placeholder: 'Select your old Prescribed Medicine',
    });
    $(document).ready(function() {
        $('#reference_id').change(function() {
            var selectedOption = $(this).find('option:selected'); // Get the selected option
            var selectedDetails = selectedOption.data('details'); // Get the data-details attribute

            // Update the placeholder of the reference_details input field
            $('#reference_details').attr('placeholder', selectedDetails || 'Details');

            // Show the reference_details_wrapper if a reference is selected
            if ($(this).val()) {
                $('#reference_details_wrapper').show();
            } else {
                $('#reference_details_wrapper').hide();
            }
        });

        // Trigger the change event on page load if a reference is already selected
        if ($('#reference_id').val()) {
            $('#reference_id').trigger('change');
        }
        $(document).on('change', '#division', function() {
            let divisionId = $(this).val();

            if (divisionId) {
                $.get('/districts/' + divisionId, function(data) {
                    // Clear the dropdown
                    $('#district').empty();

                    // Add the placeholder option
                    $('#district').append(
                        '<option value="" selected disabled>Select City</option>');
                    $.each(data, function(key, district) {
                        $('#district').append('<option value="' + district.id + '">' +
                            district.name + '</option>');
                    });
                });
            } else {
                // If no division is selected, reset the district dropdown
                $('#district').empty().append(
                    '<option value="" selected disabled>Select City</option>');
            }
        });


        $(document).on('click', '.add_new_problem', function() {
            $('#customer_id').select2('close');
            var name = $(this).data('name');
            $('.problem_add_modal').find('input#name').val(name);
            $('.problem_add_modal')
                .find('select#contact_type')
                .val('customer')
                .closest('div.contact_type_div')
                .addClass('hide');
            $('.problem_add_modal').modal('show');
        });
        $(document).on('click', '.add_new_doctor_reference', function() {
            $('.add_new_doctor_reference_modal')
                .find('select#contact_type')
                .val('customer')
                .closest('div.contact_type_div')
                .addClass('hide');
            $('.add_new_doctor_reference_modal').modal('show');
        });
        $(document).on('click', '.add_new_old_medicine', function() {
            $('.add_new_old_medicine_modal')
                .find('select#contact_type')
                .val('customer')
                .closest('div.contact_type_div')
                .addClass('hide');
            $('.add_new_old_medicine_modal').modal('show');
        });
       


        function toggleVisibilityAndRequired({
            triggerSelector,
            targetSelector,
            showCondition,
            requiredSelector = null,
        }) {
            $(triggerSelector).on('change', function() {
                var selectedValue = $(this).val();

                if (showCondition(selectedValue)) {
                    $(targetSelector).removeClass('hide');
                    if (requiredSelector) {
                        $(requiredSelector).attr('required', 'required');
                    }
                } else {
                    $(targetSelector).addClass('hide');
                    if (requiredSelector) {
                        $(requiredSelector).removeAttr('required');
                    }
                }
            });

            // Trigger change on page load to set initial state
            $(triggerSelector + ':checked').trigger('change');
        }

        // Call the function for each condition
        toggleVisibilityAndRequired({
            triggerSelector: 'input[name="childhood_fitness_good"]',
            targetSelector: '.chironic_diseases_wrapper',
            showCondition: (value) => value == 0, // Show if value is 0
            requiredSelector: '.chironic_diseases',
        });

        toggleVisibilityAndRequired({
            triggerSelector: 'input[name="is_family_disease"]',
            targetSelector: '.family_disease_wrapper',
            showCondition: (value) => value == 1, // Show if value is 1
            requiredSelector: '.family_history_disease',
        });

        toggleVisibilityAndRequired({
            triggerSelector: 'input[name="is_other_doctor_reference"]',
            targetSelector: '.reference_doctor_wrapper',
            showCondition: (value) => value == 1, // Show if value is 1
            requiredSelector: '.reference_doctor_id'
        });

        toggleVisibilityAndRequired({
            triggerSelector: 'input[name="is_sleeping_problem"]',
            targetSelector: '#reason_for_less_sleeping_wrapper',
            showCondition: (value) => value == 1, // Show if value is 1
            requiredSelector: '.reason_for_less_sleeping',

        });
        toggleVisibilityAndRequired({
            triggerSelector: 'input[name="is_old_prescribed_medicine"]',
            targetSelector: '.old_prescribed_medicine_wrapper',
            showCondition: (value) => value == 1, // Show if value is 1
            requiredSelector: '.old_prescribed_medicine',
        });

        // Validate mobile number on form submission
        $('#intakeFormSubmit').on('submit', function(event) {
            var mobile = $('#mobile_no').val();
            if (!/^01\d{9}$/.test(mobile)) {
                toastr.error('Mobile number must start with 01 and have 11 digits.',
                    'Invalid Mobile Number');
                return false;
            }
        });
        $('.reference_id').on('change', function() {
            if ($(this).val()) {
                $('#reference_details_wrapper').show();
            } else {
                $('#reference_details_wrapper').hide();
            }
        });
        $('.reference_id').trigger('change');
    });
</script>
