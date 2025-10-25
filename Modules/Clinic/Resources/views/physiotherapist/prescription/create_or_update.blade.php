    @extends('clinic::layouts.app2')
    @section('title', __('Create new Prescriptions'))
    @section('content')
        <style>
            .is-invalid {
                border-color: red;
            }

            .is-invalid:focus {
                outline: none;
                border-color: darkred;
            }

            #medicine_name_th {
                width: 26% !important;
            }

            #dosage_th {
                width: 12% !important;
                border-right: 1px solid rgb(137, 124, 124) !important;
            }

            #duration_th {
                width: 15% !important;
                border-right: 1px solid rgb(137, 124, 124) !important;
            }

            #dosage_time_th {
                width: 27% !important;
                border-right: 1px solid rgb(137, 124, 124) !important;
            }

            .comment_th {
                width: 15% !important;
                border-right: 1px solid rgb(137, 124, 124) !important;
            }

            .trash_th {
                width: 5% !important;
            }

            #prescription_form input,
            select,
            textarea {
                font-size: 13px !important;
            }

            textarea {
                resize: vertical;
            }


            /* Base input styles */
            .custom-input {
                width: 100%;
                padding: 3px 5px;
                border: 1px solid #cae1f5;
                border-radius: 6px;
                font-size: 14px;
                height: 25px;
                color: #333;
                background-color: #fff;
                transition: border-color 0.2s ease-in-out;
            }

            /* Focus state */
            .custom-input:focus {
                outline: none;
                border-color: #90c2ef;
                box-shadow: 0 0 0 2px rgba(144, 194, 239, 0.1);
            }

            /* Placeholder style */
            .custom-input::placeholder {
                color: #a0a0a0;
            }

            /* Hover state */
            .custom-input:hover {
                border-color: #90c2ef;
            }

            /* Disabled state */
            .custom-input:disabled {
                background-color: #f5f5f5;
                border-color: #e0e0e0;
                cursor: not-allowed;
            }

            .search-container {
                position: relative;
                width: 100%;
            }

            .search-input {
                width: 100%;
                padding: 7px 7px 7px 7px;
                border: 1px solid #ccc;
                font-size: 16px;
            }

            .search-icon {
                position: absolute;
                right: 10px;
                top: 50%;
                transform: translateY(-50%);
                color: #888;
                font-size: 18px;
            }

            .icon_row {
                margin-top: -15px;
            }
        </style>
        <div class="container-fluid" id="prescription_form">
            <div class="row">
                <div class="col custom-row mt-2 doctor-heading">
                    <div class="text-left">
                        <a
                            href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DashboardController::class, 'index']) }}">
                            <i class="fas fa-backward"></i>&nbsp;
                        </a>
                        <strong>@lang('clinic::lang.acrh') ({{ $doctor_name ?? '' }})</strong>
                    </div>
                </div>
            </div>
            @php
                $url = action([\Modules\Clinic\Http\Controllers\physiotherapist\PrescriptionController::class, 'store']);
                $form_id = 'create_therapist_prescriptions';
            @endphp

            {!! Form::open(['url' => $url, 'id' => $form_id]) !!}
            {!! Form::hidden('appointment_id', $appointment->id) !!}
            {!! Form::hidden('start_time', $now) !!}
            <div class="row">
                <div class="col-md-3">
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="prescription-left-widget">
                            <div class="row">
                                <div class="col-md-6"><b>@lang('clinic::lang.total_visits'): 5</b></div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <select class="form-control select2" id="other_prescription_app_id">
                                        <option value="">Select Visit</option>

                                    </select>

                                </div>
                                <div class="col-md-4">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-info btn-sm " id="view_prescription_btn"
                                                data-href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'show'], ['APPOINTMENT_ID']) }}"
                                                data-container=".view_modal_visit_form">
                                                @lang('clinic::lang.view')
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button data-appointment-id="{{ $appointment->id ?? '' }}"
                                                data-href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'getTemplateData'], ['APPOINTMENT_ID', 'PRESCRIPTION_ID']) }}"
                                                class="btn btn-primary btn-sm" id="load_prescription">@lang('clinic::lang.load')</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endcomponent

                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="prescription-left-widget2">
                            <div class="row">
                                <div class="col-md-11">
                                    {!! Form::label('chief_complain_id', __('clinic::lang.chief_complain')) !!}
                                </div>
                                <div class="col-md-1 hide" style="margin-left: -10px;">
                                    <a href="#"
                                        data-href="{{ action([\Modules\Clinic\Http\Controllers\DiseasesController::class, 'create']) . '?type=doctor_dashboard' }}"
                                        id="add_new_complain" class="btn-modal" data-container=".add_new_complain_modal">
                                    </a>
                                    <button type="button" class="btn btn-default bg-white btn-flat add_new_template"
                                        data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>

                                </div>

                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="search-container">
                                        <input type="text" id="search_complain_input" class="search-input"
                                            placeholder="Search complain Here">
                                        <i class="fa fa-search search-icon"></i>
                                    </div>
                                    <div id="complainSearchnoResults">

                                    </div>

                                </div>
                                <div id="complainFieldsContainer">
                                    @if (!empty($prescribedComplain))
                                        @foreach ($prescribedComplain as $complain)
                                            <div class="complainField row mt-1">

                                                <div class="col-md-10">
                                                    {!! Form::hidden('complain_name[]', $complain->complain_name, [
                                                        'class' => 'form-control custom-input',
                                                        'readonly',
                                                    ]) !!}
                                                    {!! Form::hidden('chief_complain_id[]', $complain->complain_id) !!}
                                                    {!! Form::text('complain_comment[]', $complain->comment, [
                                                        'class' => 'form-control custom-input',
                                                        'placeholder' => 'Comment',
                                                    ]) !!}
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-xs removeComplain"> <i
                                                            class="fas fa-minus"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endcomponent
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="oe_section_in_therapist">
                            {!! Form::label('on_examination', __('clinic::therapist.on_examination')) !!}
                            <div class="row mt-1">
                                <div class="col-md-3">
                                    {!! Form::label('current_height', __('clinic::lang.height')) !!}
                                </div>
                                <div class="col-md-3">
                                    <div class="row">
                                        <div style="padding-right: 0; padding-left: 0" class="col-md-6">
                                            {!! Form::number('current_height_feet', null, [
                                                'class' => 'form-control custom-input',
                                                'placeholder' => 'Feet',
                                                'max' => 6,
                                            ]) !!}
                                        </div>
                                        <div style="padding-right: 0; padding-left: 0" class="col-md-6">
                                            {!! Form::number('current_height_inches', null, [
                                                'class' => 'form-control custom-input',
                                                'placeholder' => 'Inches',
                                                'max' => 12,
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    {!! Form::label('current_weight', __('clinic::lang.weight')) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::number('current_weight', null, [
                                        'class' => 'form-control custom-input',
                                        'placeholder' => 'kg',
                                    ]) !!}
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div class="col-md-3">
                                    {!! Form::label('pulse_rate', __('clinic::lang.pulse_min')) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::number('pulse_rate', $prescription->pulse_rate ?? '', [
                                        'class' => 'form-control custom-input',
                                        'placeholder' => 'bpm',
                                    ]) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::label('blood_pressure', __('B.P')) !!}
                                </div>
                                <div class="col-md-3">
                                    <div class="row">
                                        <div style="padding-right: 0; padding-left: 0" class="col-md-5">
                                            {!! Form::number('systolic_pressure', $prescription->systolic_pressure ?? '', [
                                                'class' => 'form-control custom-input',
                                                'placeholder' => 'sbp',
                                            ]) !!}
                                        </div>
                                        <div style="padding-right: 2px; padding-left: 5px" class="col-md-2">
                                            <span>/</span>
                                        </div>
                                        <div style="padding-right: 0; padding-left: 0" class="col-md-5">
                                            {!! Form::number('diastolic_pressure', $prescription->diastolic_pressure ?? '', [
                                                'class' => 'form-control custom-input',
                                                'placeholder' => 'dbp',
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                {!! Form::text('on_examination_search_box', null, [
                                    'class' => 'form-control',
                                    'id' => 'on_examination_search_box',
                                    'style' => 'margin-top: 3px',
                                    'placeholder' => 'Search on Examination',
                                ]) !!}
                            </div>
                            <div class="row" id="onExaminationFieldsContainer">
                                @if (!empty($on_examinations))
                                        @foreach ($on_examinations as $advice)
                                            <div class="adviceField row mt-1">
                                                <div class="col-md-11">
                                                    {!! Form::text('on_examinations[]', $advice->advise_name, ['class' => 'form-control custom-input']) !!}
                                                   
                                                    {!! Form::hidden('on_examination_id[]', $advice->advice_id) !!}
                                                </div>
                                                <div class="col-md-1" style="margin:0px; padding:0px">
                                                    <button type="button" class="btn btn-danger btn-xs removeAdvice"><i class="fas fa-minus"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                            </div>
                        </div>
                    @endcomponent
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="row">
                            <div style="padding-right: 5px; padding-left: 5px" class="col-md-12">
                                {!! Form::label('ddx', __('clinic::therapist.ddx')) !!}
                            </div>
                            <div>
                                {!! Form::textarea('differential_diagonosis_ddx', null, [
                                    'class' => 'form-control',
                                    'id' => 'differential_diagnosis',
                                    'rows' => 2,
                                    'placeholder' => 'Enter Differential Diagnosis Here',
                                ]) !!}
                            </div>
                        </div>
                    @endcomponent

                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="row">
                            <div class="col-md-11">
                                {!! Form::label('history_of', __('clinic::therapist.history_of')) !!}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                {!! Form::select(
                                    'disease_history[]',
                                    $complains,
                                    $diseaseHistories ?? [],
                                    ['class' => 'form-control select2', 'multiple', 'data-placeholder' => __('Select disease history')],
                                ) !!}
                            </div>
                        </div>
                    @endcomponent

                </div>

                <div class="col-md-6">
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="prescription-middle-widget">
                            <div class="row">
                                <div class="col-md-5">
                                    <h4>{{ $patient->first_name }} {{ $patient->last_name ?? ''}}</h4>

                                </div>
                                <div class="col-md-7 text-right">
                                    <div class="form-group row">
                                        <!-- Age -->
                                        <label for="age" style="padding: 2px;" class="col-sm-1">@lang('clinic::lang.age')</label>
                                        <div style="padding: 0;" class="col-sm-3">
                                            {!! Form::number('age', $patient->age ?? '', [
                                                'class' => 'form-control custom-input',
                                                'placeholder' => 'Age',
                                            ]) !!}
                                        </div>

                                        <!-- Gender -->
                                        <label for="gender" style="padding: 2px;"
                                            class="col-sm-2">@lang('clinic::lang.gender')</label>
                                        <div style="padding: 0;" class="col-sm-3">
                                            {!! Form::select('gender', ['male' => 'Male', 'female' => 'Female', 'others' => 'Others'], $patient->gender ?? '', [
                                                'class' => 'form-control custom-input',
                                                'placeholder' => 'Select Gender',
                                            ]) !!}
                                        </div>
                                        <div style="padding: 0;" class="col-sm-2">
                                            <p><span>@lang('clinic::lang.blood'):{{ $patient->blood_group ?? 'N/A' }}</span>
                                            </p>
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-9">
                                    <p><span>@lang('clinic::lang.marital_status'):
                                            Married</span>
                                        <span>@lang('clinic::lang.profession'):
                                            Banker</span>
                                    </p>

                                </div>
                                <div class="col-md-3 text-right">
                                    <button type="button" class="btn  btn-primary btn-xs btn-modal"
                                        data-href="{{ action([\Modules\Clinic\Http\Controllers\Survey\IntakeFormController::class, 'show'], [$patient->patient_contact_id]) }}"
                                        data-container=".view_modal_intake_form">
                                        View Intake Form
                                    </button>


                                </div>
                            </div>
                        </div>
                    @endcomponent
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="therapist-treatment-plan-widget">
                            <div class="row">
                                <div class="col-md-11">
                                    {!! Form::label('treatment_plan', __('clinic::therapist.treatment_plan')) !!}
                                </div>
                                <div class="col-md-1" style="margin-left: -10px;">
                                    <a href="#" id="add_new_advice" class="btn btn-modal"
                                        data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'create']) . '?type=treatment_plan' }}"
                                        data-container=".add_new_treatment_plan_modal">
                                        <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                    </a>
                                    <a href="#" id="add_new_on_examination" class="btn btn-modal hide"
                                        data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'create']) . '?type=on_examination' }}"
                                        data-container=".add_new_treatment_plan_modal">
                                        <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                    </a>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="search-container">
                                        <input type="text" id="search_advice_input" class="search-input"
                                            placeholder="Search treatment plan here">
                                        <i class="fa fa-search search-icon"></i>
                                    </div>
                                    <div id="adviceSearchnoResults"></div>
                                </div>
                                <div id="adviceFieldsContainer">
                                    @if (!empty($treatmentPlans))
                                        @foreach ($treatmentPlans as $advice)
                                            <div class="adviceField row mt-1">
                                                <div class="col-md-11">
                                                    {!! Form::text('treatment_plan[]', $advice->advise_name, ['class' => 'form-control custom-input']) !!}
                                                   
                                                    {!! Form::hidden('treatment_plan_id[]', $advice->advice_id) !!}
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger btn-xs removeAdvice"><i class="fas fa-minus"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endcomponent
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="prescription-middle-widget_therapy">
                            <div class="row icon_row">
                                <div class="col">
                                    <i class="fas fa-prescription"></i>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="search-container">
                                        <input type="text" id="search_prescription_medicine" class="search-input"
                                            placeholder="Search Medicine Here">
                                        <i class="fa fa-search search-icon"></i>
                                    </div>

                                </div>

                            </div>

                            <div class="row mt-1">
                                <div class="col">
                                    <table class="table table-bordered table-th-green table-striped"
                                        id="prescription_medicine_product_table">
                                        <thead>
                                            <tr>
                                                <th id="medicine_name_th" style="border-right: 1px solid rgb(103, 93, 93);">
                                                    @lang('clinic::lang.drug_name')</th>
                                                <th id="dosage_th">
                                                    @lang('clinic::lang.dosage')
                                                    <a href="#"
                                                        data-href="{{ action([\Modules\Clinic\Http\Controllers\DosageController::class, 'create']) }}"
                                                        id="add_new_dosage" class="text-right btn-modal"
                                                        data-container=".add_dosage_view">
                                                        <i class="fas fa-plus add_new_dosage_btn"></i>
                                                    </a>
                                                </th>


                                                <th id="dosage_time_th">
                                                    @lang('clinic::lang.dosage_time')
                                                    <span style="float: right;">
                                                        <a href="#"
                                                            data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\MedicineMealController::class, 'create']).'?type=therapist' }}"
                                                            id="add_new_dosage_time" class="text-right btn-modal"
                                                            data-container=".medicine_meal_modal">
                                                            <i class="fas fa-plus add_new_dosage_btn"></i>
                                                        </a>
                                                    </span>
                                                </th>

                                                <th id="duration_th">@lang('clinic::lang.duration') <span style="float: right;"><a
                                                            href="#"
                                                            data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DurationController::class, 'create']) }}"
                                                            id="add_new_duration" class="text-right btn-modal"
                                                            data-container=".view_modal_duration_form">
                                                            <i class="fas fa-plus add_new_dosage_btn"></i>
                                                        </a></span></th>
                                                <th class="comment_th">Comment</th>
                                                <th class="trash_th"><i class="fa fa-trash"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody id="prescribe_medicine_content">
                                            @if ($prescribedMedicines && $prescribedMedicines->isNotEmpty())
                                                @foreach ($prescribedMedicines as $medicine)
                                                    <tr>
                                                        <td>
                                                            @if ($common_settings['show_medicine_name_as'] == 'generic')
                                                                {!! Form::text('generic_name[]', $medicine->generic_name, [
                                                                    'class' => 'form-control custom-input',
                                                                    'readonly',
                                                                ]) !!}
                                                                {!! Form::hidden('medicine_name[]', $medicine->x_medicine_name, [
                                                                    'class' => 'form-control custom-input',
                                                                    'readonly',
                                                                ]) !!}
                                                            @else
                                                                {!! Form::text('medicine_name[]', $medicine->x_medicine_name, [
                                                                    'class' => 'form-control custom-input',
                                                                    'readonly',
                                                                ]) !!}
                                                                {!! Form::hidden('generic_name[]', $medicine->generic_name, [
                                                                    'class' => 'form-control custom-input',
                                                                    'readonly',
                                                                ]) !!}
                                                            @endif
                                                        </td>
                                                        {!! Form::hidden('product_id[]', $medicine->x_medicine_id) !!}
                                                        {!! Form::hidden('generic_id[]', $medicine->generic_id) !!}
                                                        <td> {!! Form::select('taken_instruction[]', $dosages, $medicine->taken_instruction, [
                                                            'class' => 'form-control custom-input dosage_class',
                                                        ]) !!} </td>
                                                        <td> {!! Form::select('dosage_form[]', $meals, $medicine->dosage_form, ['class' => 'form-control custom-input dosage_form']) !!}</td>
                                                        <td> {!! Form::select('medication_duration[]', $durations, $medicine->medication_duration, [
                                                            'class' => 'form-control custom-input medication_duration',
                                                        ]) !!}</td>
                                                        <td>
                                                            {!! Form::textarea('medicine_comment[]', $medicine->comment, [
                                                                'class' => 'form-control custom-input',
                                                                'placeholder' => 'Comment',
                                                                'rows' => 1,
                                                            ]) !!}
                                                        </td>
                                                        <td>
                                                            <button type="button"
                                                                class="btn btn-danger btn-remove-row btn-xs">
                                                                <i class="fa fa-times"
                                                                    style="font-size: 12px; cursor: pointer;"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                            @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endcomponent


                </div>

                <div class="col-md-3">
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="prescription-right-widget">
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::label('template', 'Load Template:') !!}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    {!! Form::select('template_id', [], null, [
                                        'class' => 'form-control select2',
                                        'placeholder' => 'Load a Template',
                                        'id' => 'template_id',
                                    ]) !!}
                                </div>
                                <div class="col-md-4">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="button" class="btn btn-info btn-sm" id="view_template_btn"
                                                data-href="{{ isset($prescription->id) ? action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'show'], [$prescription->id]) : '#' }}"
                                                data-container=".view_modal_intake_form">
                                                @lang('clinic::lang.view')
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'getTemplateData'], [$prescription->appointment_id ?? '', $prescription->id ?? '']) }}"
                                                class="btn btn-primary btn-sm" id="load_template">
                                                @lang('clinic::lang.load')
                                            </a>

                                        </div>
                                    </div>

                                </div>

                            </div>
                        </div>
                    @endcomponent

                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="row" id="testSection" style="min-height: 169px !important;">
                            <div class="col">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="">Investigation</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="search-container">
                                            <input type="text" id="search_prescription_test" class="search-input"
                                                placeholder="Search Test Here">
                                            <i class="fa fa-search search-icon"></i>
                                        </div>
                                    </div>
                                </div>
                                <div id="testFieldsContainer">
                                    @if (!empty($prescribedTest))
                                        @foreach ($prescribedTest as $test)
                                            <div class="testField row mt-1">
                                                <div class="col-md-7">
                                                    {!! Form::text('test_name[]', $test->test_name, ['class' => 'form-control custom-input', 'readonly']) !!}
                                                    {!! Form::hidden('test_product_id[]', $test->product_id) !!}
                                                </div>

                                                <div class="col-md-5">
                                                    <div class="row">
                                                        <div style="padding-left: 0; padding-right: 0" class="col-md-9">
                                                            {!! Form::textarea('test_comment[]', $test->comment, [
                                                                'class' => 'form-control custom-input',
                                                                'placeholder' => 'Comment',
                                                                'rows' => '1',
                                                            ]) !!}
                                                        </div>
                                                        <div class="col-md-3">
                                                            <button type="button" class="btn btn-danger btn-xs removeField">
                                                                <i class="fas fa-minus"></i></button>
                                                        </div>
                                                    </div>

                                                </div>

                                            </div>
                                        @endforeach
                                    @endif

                                </div>
                            </div>
                        </div>
                    @endcomponent
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="row">
                            <div class="col-md-10">
                                {!! Form::label('home_advice', 'Home Advice:') !!}
                            </div>
                            <div class="col-md-2">
                                <a href="#" id="add_new_home_advice" class="btn btn-modal"
                                    data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'create']) . '?type=home_advice' }}"
                                    data-container=".add_new_treatment_plan_modal">
                                    <i class="fa fa-plus-circle text-primary fa-lg"></i>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            {!! Form::text('home_advice_search_box', null, [
                                'class' => 'form-control',
                                'id' => 'home_advice_search_box',
                                'placeholder' => 'Search Home Advice Here',
                            ]) !!}
                        </div>
                        <div id="homeAdviceFieldsContainer" class="row">
                            @if (!empty($home_advices))
                                        @foreach ($home_advices as $advice)
                                            <div class="adviceField row mt-1">
                                                <div class="col-md-11">
                                                    {!! Form::text('home_advice[]', $advice->advise_name, ['class' => 'form-control custom-input']) !!}
                                                   
                                                    {!! Form::hidden('home_advice_id[]', $advice->advice_id) !!}
                                                </div>
                                                <div class="col-md-1" style="margin:0px; padding:0px">
                                                    <button type="button" class="btn btn-danger btn-xs removeAdvice"><i class="fas fa-minus"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                        </div>
                    @endcomponent
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::label('follow_up', __('clinic::therapist.next_follow_up')) !!}
                                <hr>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                {!! Form::select(
                                    'follow_up_number',
                                    array_combine(range(1, 30), range(1, 30)),
                                    5,
                                    ['class' => 'form-control select2', 'placeholder' => 'Select Number'],
                                ) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::select(
                                    'follow_up_type',
                                    ['days' => 'Days', 'months' => 'Months', 'years' => 'Years'],
                                    'days',
                                    ['class' => 'form-control select2', 'placeholder' => 'Select Option'],
                                ) !!}
                            </div>
                        </div>
                        <div class="row" style="margin-top: 2px">
                            <div class="col-md-6">
                                {!! Form::label('duration', __('clinic::therapist.treatment_duration')) !!}
                            </div>
                            <div class="col-md-4">
                                {!! Form::number('treatment_duration_per_week', null, ['class' => 'form-control', 'placeholder' => 'Weeks']) !!}
                            </div>
                            <div class="col-md-2">
                                Weeks
                            </div>
                        </div>
                        <div class="row" style="margin-top: 2px">
                            <div class="col-md-6">
                                {!! Form::label('Visit Per Week', __('clinic::therapist.visit_per_week')) !!}
                            </div>
                            <div class="col-md-4">
                                {!! Form::number('visit_per_week', null, ['class' => 'form-control', 'placeholder' => 'Visit Per Week']) !!}
                            </div>
                            <div class="col-md-2">
                                Times
                            </div>
                        </div>
                    @endcomponent
                </div>
            </div>

            <div class="row">
                <div class="col-md-3"></div>
                <div class="col-md-6 text-center">
                    <button type="submit" name="action" value="save"
                        class="btn btn-success">@lang('clinic::lang.save')</button>
                    <button type="submit" name="action" value="save_and_print"
                        class="btn btn-primary">@lang('clinic::lang.save_and_print')</button>
                    <button type="submit" name="action" value="save_as_template"
                        class="btn make_app saveAsTempBtn">Save as
                        Template</button>
                </div>
                <div class="col-md-3"></div>
            </div>

            {!! Form::close() !!}

        </div>
        <div class="modal fade add_dosage_view" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade medicine_meal_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_modal_duration_form" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade add_new_treatment_plan_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade add_new_complain_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_modal_intake_form" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
    @endsection
    @section('javascript')
        @include('clinic::physiotherapist.prescription.physiotherapist_js')
        <script type="text/javascript"></script>
    @endsection
