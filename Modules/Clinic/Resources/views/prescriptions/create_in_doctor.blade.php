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
                        <a href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DashboardController::class, 'index']) }}">
                            <i class="fas fa-backward"></i>&nbsp;
                        </a>
                        <strong>@lang('clinic::lang.acrh') ({{$doctor_name ?? ''}})</strong>
                    </div>
                </div>
            </div>
            @php
                $url = action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'store']);
                $form_id = 'create_new_prescriptions';
            @endphp

            {!! Form::open(['url' => $url, 'id' => $form_id]) !!}
            {!! Form::hidden('appointment_id', $appointment->id) !!}
            {!! Form::hidden('start_time', $now) !!}

            <div class="row">
                <div class="col-md-3">
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="prescription-left-widget">
                            <div class="row">
                                <div class="col-md-6"><b>@lang('clinic::lang.total_visits'): {{ $visitCount }}</b></div>
                            </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <select class="form-control select2" id="other_prescription_app_id">
                                        <option value="">Select Visit</option>
                                        @foreach ($otherPrescription as $appointmentId => $presc)
                                            <option value="{{ $appointmentId }}"
                                                data-prescription-id="{{ $presc['id'] ?? '' }}">
                                                {{ $presc['label'] }}
                                            </option>
                                        @endforeach
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
                                            <button data-appointment-id="{{ $appointment->id ?? '' }}"  data-href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'getTemplateData'], ['APPOINTMENT_ID','PRESCRIPTION_ID']) }}"
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
                                    <a href="{{ action([\Modules\Clinic\Http\Controllers\DiseasesController::class, 'create'])."?type=doctor_dashboard" }}"
                                        id="add_new_complain">
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
                        <div class="prescription-left-widget3">
                            <div class="row mt-1">
                                <div class="col-md-10">
                                    <b style="font-size: 15px;">@lang('clinic::lang.on_examination') @lang('clinic::lang.follow_up_data')</b>
                                </div>
                            </div>
                            <!-- Height and Weight Fields -->
                            <div class="row mt-1">
                                <div class="col-md-3">
                                    {!! Form::label('current_height', __('clinic::lang.height')) !!}
                                </div>
                                <div class="col-md-3">
                                    <div class="row">
                                        <div style="padding-right: 0; padding-left: 0" class="col-md-6">
                                            {!! Form::number('current_height_feet', $prescription->current_height_feet ?? '', [
                                                'class' => 'form-control custom-input',
                                                'placeholder' => 'Feet',
                                                'max' => 6,
                                            ]) !!}
                                        </div>
                                        <div style="padding-right: 0; padding-left: 0" class="col-md-6">
                                            {!! Form::number('current_height_inches', $prescription->current_height_inches ?? '', [
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
                                    {!! Form::number('current_weight', $prescription->current_weight ?? '', [
                                        'class' => 'form-control custom-input',
                                        'placeholder' => 'kg'
                                    ]) !!}
                                </div>
                            </div>
                            <!-- Vital Signs -->
                            <div class="row mt-1">
                                <div class="col-md-3">
                                    {!! Form::label('pulse_rate', __('clinic::lang.pulse_min')) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::number('pulse_rate', $prescription->pulse_rate ?? '', ['class' => 'form-control custom-input', 'placeholder' => 'bpm']) !!}
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
                            <div class="row mt-1">
                                <div class="col-md-3">
                                    {!! Form::label('respiratory', __('clinic::lang.respiratory')) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::text('respiratory', $prescription->respiratory ?? '', ['class' => 'form-control custom-input', 'placeholder' => 'b/m']) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::label('body_temp', __('clinic::lang.temperature')) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::text('body_temp', $prescription->body_temp ?? '', ['class' => 'form-control custom-input', 'placeholder' => '°F']) !!}
                                </div>
                            </div>
                            <div class="row mt-1">
                                <div style="padding-right: 0;" class="col-md-3">
                                    {!! Form::label('bmi', __('BMI')) !!} <sup style="font-size: 10px">kg/m²</sup>
                                </div>
                                <div class="col-md-3">
                                    {!! Form::text('bmi', $prescription->bmi ?? '', [
                                        'class' => 'form-control custom-input',
                                        'readonly',
                                        'placeholder' => 'kg/m²'
                                    ]) !!}
                                </div>
                                <div style="padding-right: 0;" class="col-md-3">
                                    {!! Form::label('body_fat_percent', __('clinic::lang.body_fat')) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::text('body_fat_percent', $prescription->body_fat_percent ?? '', [
                                        'class' => 'form-control custom-input',
                                        'readonly',
                                        'placeholder' => '%'
                                    ]) !!}
                                </div>

                            </div>
                            <div class="row mt-1">
                                <div style="padding-right: 0;" class="col-md-3">
                                    {!! Form::label('fat_mass_percent', __('clinic::lang.fat_mass')) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::text('fat_mass_percent', $prescription->fat_mass_percent ?? '', [
                                        'class' => 'form-control custom-input',
                                        'readonly',
                                        'placeholder' => '%'
                                    ]) !!}
                                </div>
                                <div style="padding-right: 0;" class="col-md-3">
                                    {!! Form::label('lean_mass_percent', __('clinic::lang.lean_mass')) !!}
                                </div>
                                <div class="col-md-3">
                                    {!! Form::text('lean_mass_percent', $prescription->lean_mass_percent ?? '', [
                                        'class' => 'form-control custom-input',
                                        'readonly',
                                        'placeholder' => '%'
                                    ]) !!}
                                </div>

                            </div>
                            <div class="row mt-1">
                                <div class="col-md-3">
                                    {!! Form::label('comments', 'Comments') !!}
                                </div>
                                <div class="col-md-9">
                                    {!! Form::textarea('comments', $prescription->comments ?? '', [
                                        'class' => 'form-control custom-input',
                                        'placeholder' => 'Comments',
                                        'rows' => '1',
                                    ]) !!}
                                </div>
                            </div>
                        </div>
                    @endcomponent
                    @component('components.widget', ['class' => 'box-primary hide'])
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::label('note', __('clinic::lang.note')) !!} <span><small id="charCount" class="text-muted">110 characters
                                        remaining</small>
                                </span>
                                {!! Form::textarea('note', $prescription->note ?? '', [
                                    'rows' => 1,
                                    'placeholder' => 'Write Note',
                                    'class' => 'form-control',
                                    'id' => 'note_validation',
                                    'maxlength' => 110, // Enforce limit in HTML
                                ]) !!}
                            </div>

                        </div>
                    @endcomponent
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="row">
                            <div style="padding-right: 5px; padding-left: 5px" class="col-md-12">
                                {!! Form::label('investigation_history', __('clinic::lang.investigation_history')) !!}
                                <a class="pull-right" type="button" id="addRowBtn"><i class="fa fa-plus-circle fa-lg black"></i> </a>
                            </div>
                        </div>

                        <div id="investigationRows">
                            <!-- Existing rows populated from the database -->
                            @foreach ($InvestigationHistory as $index => $history)
                                <div class="row mb-2">
                                    <div style="padding-right: 5px; padding-left: 5px" class="col-md-3">
                                        {!! Form::text("investigation_history[{$index}][date]", $history->date, ['class' => 'form-control custom-input date-format', 'readonly']) !!}
                                    </div>
                                    <div style="padding-right: 5px; padding-left: 5px" class="col-md-4">
                                        {!! Form::text("investigation_history[{$index}][test_name]", $history->test_name, ['class' => 'form-control custom-input', 'placeholder' => __('clinic::lang.test_name')]) !!}
                                    </div>
                                    <div style="padding-right: 5px; padding-left: 5px" class="col-md-4">
                                        {!! Form::text("investigation_history[{$index}][result_value]", $history->result_value, ['class' => 'form-control custom-input', 'placeholder' => __('clinic::lang.result_value')]) !!}
                                    </div>
                                    <div style="padding-right: 0; padding-left: 0" class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger removeRow btn-xs"><i class="fas fa-minus"></i></button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endcomponent

                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="row">
                            <div class="col-md-11">
                                {!! Form::label('disease_history', __('clinic::lang.disease_history')) !!}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                {!! Form::select('disease_history[]', $ChiefComplain, $diseaseHistories ?? [], ['class' => 'form-control select2', 'multiple', 'data-placeholder' => __('Select disease history')]) !!}
                            </div>
                        </div>
                    @endcomponent

                </div>

                <div class="col-md-6">
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="prescription-middle-widget">
                            <div class="row">
                                <div class="col-md-5">
                                    <h4>{{ $patient->first_name ? ucfirst($patient->first_name) : '' }}
                                        {{ $patient->last_name ? ucfirst($patient->last_name) : '' }}</h4>

                                </div>
                                <div class="col-md-7 text-right">
                                    <div class="form-group row">
                                        <!-- Age -->
                                        <label for="age" style="padding: 2px;" class="col-sm-1">@lang('clinic::lang.age')</label>
                                        <div style="padding: 0;" class="col-sm-3">
                                            {!! Form::number('age', $patient->age ?? '', [
                                                'class' => 'form-control custom-input',
                                                'placeholder' => 'Age'
                                            ]) !!}
                                        </div>

                                        <!-- Gender -->
                                        <label for="gender" style="padding: 2px;" class="col-sm-2">@lang('clinic::lang.gender')</label>
                                        <div style="padding: 0;" class="col-sm-3">
                                            {!! Form::select('gender', ['male' => 'Male', 'female' => 'Female', 'others' => 'Others'], $patient->gender ?? '', [
                                                'class' => 'form-control custom-input',
                                                'placeholder' => 'Select Gender'
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
                                            {{ $patient->marital_status ? ucfirst($patient->marital_status) : 'N/A' }};</span>
                                        <span>@lang('clinic::lang.profession'):
                                            {{ $patient->profession ? ucfirst($patient->profession) : 'N/A' }} </span>
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

                        <div class="prescription-middle-widget2">
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
                                                <th id="dosage_th">@lang('clinic::lang.dosage') <span style="float: right;"><a
                                                            href="{{ action([\Modules\Clinic\Http\Controllers\DosageController::class, 'create']) }}"
                                                            id="add_new_dosage">
                                                            <i class="fas fa-plus add_new_dosage_btn"></i>
                                                        </a></span></th>
                                                <th id="dosage_time_th">
                                                    @lang('clinic::lang.dosage_time')
                                                    <span style="float: right;">
                                                        <a href="{{ action([\Modules\Clinic\Http\Controllers\doctor\MedicineMealController::class, 'create']) }}"
                                                            id="add_new_dosage_time" class="text-right">
                                                            <i class="fas fa-plus add_new_dosage_btn"></i>
                                                        </a>
                                                    </span>
                                                </th>

                                                <th id="duration_th">@lang('clinic::lang.duration') <span style="float: right;"><a
                                                            href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DurationController::class, 'create']) }}"
                                                            id="add_new_duration">
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
                    @component('components.widget', ['class' => 'box-primary'])
                        <div class="prescription-left-widget2">
                            <div class="row">
                                <div class="col-md-11">
                                    {!! Form::label('advice_id', __('clinic::lang.advice')) !!}
                                </div>
                                <div class="col-md-1 hide" style="margin-left: -10px;">
                                    <a href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'create']) }}"
                                        id="add_new_advice">
                                    </a>
                                    <button type="button" class="btn btn-default bg-white btn-flat add_new_template"
                                        data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="search-container">
                                        <input type="text" id="search_advice_input" class="search-input" placeholder="Search advice here">
                                        <i class="fa fa-search search-icon"></i>
                                    </div>
                                    <div id="adviceSearchnoResults"></div>
                                </div>
                                <div id="adviceFieldsContainer">
                                    @if (!empty($existingAdvice))
                                        @foreach ($existingAdvice as $advice)
                                            <div class="adviceField row mt-1">
                                                <div class="col-md-11">
                                                    {!! Form::text('advice_name[]', $advice->advise_name, ['class' => 'form-control custom-input', 'readonly']) !!}
                                                   
                                                    {!! Form::hidden('advice_id[]', $advice->advice_id) !!}
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button" class="btn btn-danger btn-xs removeAdvice"><i class="fas fa-minus"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>

                            <!-- IPD Admission Checkbox (One Line with input-icheck + jQuery) -->
                            @php
                                $isChecked = $ipdAdmission && $ipdAdmission->is_ipd_admission == 1 ? true : false;
                                $admissionDays = $ipdAdmission ? $ipdAdmission->admission_days : '';
                            @endphp

                            <div class="row mt-5">
                                <div class="col-md-12">
                                    <label style="margin:0; vertical-align:middle; display:inline-block;">
                                        {!! Form::checkbox('ipd_admission', 1, $isChecked, [
                                            'class' => 'input-icheck',
                                            'id' => 'ipd_admission_checkbox',
                                            'style' => 'vertical-align:middle; margin-right:5px;'
                                        ]) !!}
                                        Admission in IPD for
                                    </label>

                                    <select class="form-control custom-input" id="ipd_days_input" name="ipd_days"
                                            style="width:80px; display:inline-block; margin:0 5px; vertical-align:middle;"
                                            {{ $isChecked ? '' : 'disabled' }}>
                                        <option value="" {{ $admissionDays == '' ? 'selected' : '' }}>--</option>
                                        <option value="7"  {{ $admissionDays == 7  ? 'selected' : '' }}>7</option>
                                        <option value="10" {{ $admissionDays == 10 ? 'selected' : '' }}>10</option>
                                        <option value="15" {{ $admissionDays == 15 ? 'selected' : '' }}>15</option>
                                        <option value="20" {{ $admissionDays == 20 ? 'selected' : '' }}>20</option>
                                        <option value="30" {{ $admissionDays == 30 ? 'selected' : '' }}>30</option>
                                    </select>
                                    <span style="vertical-align:middle;">days</span>
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
                                    {!! Form::select('template_id', $templates, null, [
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
                                    {{-- @if (!empty($prescribedTest))
                                        @foreach ($prescribedTest as $test)
                                            <div class="testField row mt-1">

                                                <div class="col-md-10">
                                                    {!! Form::hidden('test_name[]', $test->test_name, [
                                                        'class' => 'form-control custom-input',
                                                        'readonly',
                                                    ]) !!}
                                                    {!! Form::hidden('test_product_id[]', $test->product_id) !!}
                                                    {!! Form::text('test_comment[]', $test->comment, [
                                                        'class' => 'form-control custom-input',
                                                        'placeholder' => 'Comment',
                                                    ]) !!}
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-danger btn-xs removeField"> <i
                                                            class="fas fa-minus"></i></button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif --}}
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
                        <div class="row" id="therapySection" style="min-height: 199px !important;">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="">Therapy:</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="search-container">
                                            <input type="text" id="search_prescription_therapy" class="search-input"
                                                placeholder="Search Therapy Here">
                                            <i class="fa fa-search search-icon"></i>
                                        </div> 
                                    </div>
                                </div>
                                <div id="therapyFieldsContainer">
                                    @if (!empty($prescribedTherapy))
                                        @foreach ($prescribedTherapy as $therapy)
                                            <div class="therapyField row mt-1">
                                                <div class="col-sm-5">
                                                    {!! Form::text('therapy_name[]', $therapy->therapy_name, ['class' => 'form-control custom-input', 'readonly']) !!}
                                                    {!! Form::hidden('therapy_product_id[]', $therapy->product_id) !!}
                                                </div>
                                                <div class="col-sm-7">
                                                    <div class="row">
                                                        <div style="padding: 1px; margin: 0px" class="col-sm-6">
                                                            {!! Form::select('therapy_frequency[]', $frequencies->toArray(), $therapy->frequency, [
                                                                'class' => 'form-control custom-input',
                                                                'placeholder' => 'Select Frequency',
                                                            ]) !!}
                                                        </div>
                                                        <div class="col-sm-4" style="padding: 1px; margin: 0px">
                                                            {!! Form::number('session_count[]', $therapy->session_count, ['class' => 'form-control custom-input', 'placeholder' => 'Session']) !!}
                                                        </div>
                                                        <div class="col-sm-2" style="padding: 1px; margin: 0px">
                                                            <button type="button"
                                                                class="btn btn-danger btn-xs removeTherapyField"><i
                                                                    class="fas fa-minus"></i></button>
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
                            <div class="col-md-12">
                                {!! Form::label('follow_up', __('clinic::lang.follow_up_data')) !!}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">

                                {!! Form::select(
                                    'follow_up_number',
                                    array_combine(range(1, 30), range(1, 30)),
                                    $prescription->follow_up_number ?? 30,
                                    ['class' => 'form-control select2', 'placeholder' => 'Select Number'],
                                ) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::select(
                                    'follow_up_type',
                                    ['days' => 'Days', 'months' => 'Months', 'years' => 'Years'],
                                    $prescription->follow_up_type ?? 'days',
                                    ['class' => 'form-control select2', 'placeholder' => 'Select Option'],
                                ) !!}
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
        <div class="modal fade add_dosage_view" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_modal_intake_form" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_template_modal" tabindex="-1" role="dialog">
        </div>
        <div class="modal fade view_modal_visit_form" tabindex="-1" role="dialog">
        </div>
        <div class="modal fade template_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            @include('clinic::prescriptions.template.create')
        </div>
    @endsection

    @section('javascript')

        @include('clinic::prescriptions.prescription_js')

    @endsection
