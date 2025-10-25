@extends('clinic::layouts.app2')

@section('title', __('clinic::lang.nutritionist_visit'))
@section('content')
    <style>
        .instruction_info {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            z-index: 999;
            width: 100%;
            display: none;
            /* hidden until data comes */
        }

        .instruction_info ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .instruction_info li {
            padding: 8px;
            cursor: pointer;
        }

        .instruction_info li:hover {
            background: #f0f0f0;
        }

        .meal_time_info {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            z-index: 999;
            width: 100%;
            display: none;
            /* hidden until data comes */
        }

        .meal_time_info ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .meal_time_info li {
            padding: 8px;
            cursor: pointer;
        }

        .meal_time_info li:hover {
            background: #f0f0f0;
        }
    </style>

    <div class="container-fluid">
        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\nutritionist\NutritionistVisitController::class, 'store']),
            'method' => 'post',
            'id' => 'nutritionist_prescription_form',
        ]) !!}
        <div class="row">

            <div class="patient-info col-md-9">
                @component('components.widget', ['class' => 'box-primary'])
                    <h4 class="mb-3"><b>{{ $patient->first_name }} {{ $patient->last_name }}</b></h4>
                    <table class="table table-sm table-bordered">
                        <tbody>
                            <tr>
                                <th>Age</th>
                                <td>{{ $patient->age }} Y</td>
                                <th>Gender</th>
                                <td>{{ ucfirst($patient->gender) }}</td>
                            </tr>
                            <tr>
                                <th>Blood Group</th>
                                <td>{{ $patient->blood_group ?? 'N/A' }}</td>
                                <th>Marital Status</th>
                                <td>{{ $patient->marital_status ? ucfirst($patient->marital_status) : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Patient Type</th>
                                <td>{{ $contact->patient_type ? ucfirst($contact->patient_type) : 'N/A' }}</td>
                                <th>Contact ID</th>
                                <td>{{ $contact->contact_id }}</td>
                            </tr>
                            <tr>
                                <th>District</th>
                                <td>{{ $patient->district->name ?? 'N/A' }}</td>
                                <th>Profession</th>
                                <td>{{ $patient->profession ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Doctor Name</th>
                                <td>{{ $main_doctor_name ?? 'N/A' }}</td>
                                <th>Date</th>
                                <td>{{ $prescription->prescription_date ?? 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endcomponent

                
                <div class="row @if (!empty($guidline_id)) show @else hide @endif" id="guidlines_info">
                    <div class="form-group">
                        {!! Form::label('guidline_description', __('clinic::lang.guidline_description')) !!}
                        {!! Form::textarea('guidline_description', $guidline_id ? $nu->guidline_description : '', [
                            'class' => 'form-control',
                            'id' => 'guidline_description',
                        ]) !!}
                    </div>
                </div>
            </div>

            <div class="col-md-3">
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
                                    'placeholder' => 'kg',
                                ]) !!}
                            </div>
                        </div>
                        <!-- Vital Signs -->
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
                        <div class="row mt-1">
                            <div class="col-md-3">
                                {!! Form::label('respiratory', __('clinic::lang.respiratory')) !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::text('respiratory', $prescription->respiratory ?? '', [
                                    'class' => 'form-control custom-input',
                                    'placeholder' => 'b/m',
                                ]) !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::label('body_temp', __('clinic::lang.temperature')) !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::text('body_temp', $prescription->body_temp ?? '', [
                                    'class' => 'form-control custom-input',
                                    'placeholder' => '°F',
                                ]) !!}
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
                                    'placeholder' => 'kg/m²',
                                ]) !!}
                            </div>
                            <div style="padding-right: 0;" class="col-md-3">
                                {!! Form::label('body_fat_percent', __('clinic::lang.body_fat')) !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::text('body_fat_percent', $prescription->body_fat_percent ?? '', [
                                    'class' => 'form-control custom-input',
                                    'readonly',
                                    'placeholder' => '%',
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
                                    'placeholder' => '%',
                                ]) !!}
                            </div>
                            <div style="padding-right: 0;" class="col-md-3">
                                {!! Form::label('lean_mass_percent', __('clinic::lang.lean_mass')) !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::text('lean_mass_percent', $prescription->lean_mass_percent ?? '', [
                                    'class' => 'form-control custom-input',
                                    'readonly',
                                    'placeholder' => '%',
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

                @component('components.widget', ['class' => 'box-primary', 'title' => __('clinic::lang.old_prescription')])
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            {!! Form::label('guidlines', __('clinic::lang.guidlines')) !!}
                            {!! Form::select('guidline_id', $guidelines, $guidline_id, [
                                'class' => 'form-control custom-input select2',
                                'placeholder' => 'Select Guidlines',
                                'id' => 'guidlines_select_box',
                            ]) !!}
                        </div>
                    </div>

                </div>    
                <div class="row">
                        <div class="col">
                            <div class="form-group">
                                {!! Form::label('old_prescription', 'Visit  &nbsp;| Date  &nbsp;| Dr.  &nbsp;| Nutritionist') !!}
                                {!! Form::select('old_prescription', $otherPrescriptions, null, [
                                    'class' => 'form-control select2',
                                    'placeholder' => 'Select Prescription',
                                ]) !!}

                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>

        </div>

        <div class="row">

            <div class="col-md-6">
                {!! Form::hidden('appointment_id', $appointment_id) !!}
                {!! Form::hidden('prescription_id', $prescription->id) !!}
                {!! Form::hidden('prescription_date', $prescription_date) !!}
                {!! Form::hidden('patient_contact_id', $patient_contact_id) !!}
                {!! Form::hidden('doctor_user_id', $doctor_user_id) !!}
                @component('components.widget', [
                    'class' => 'box-primary',
                    'title' => __('clinic::lang.food_product'),
                ])
                    <div class="row">
                        {!! Form::text('search_food_products', null, [
                            'class' => 'form-control',
                            'id' => 'search_food_products',
                            'placeholder' => __('clinic::lang.search_food_products'),
                        ]) !!}
                        <table class="table table-bordered table-striped" id="food_products_table" style="margin-top: 3px;">
                            <thead>
                                <th>Food</th>
                                <th>Meal Time <span style="float: right;"><a class="btn btn-block btn-xs btn-primary btn-modal"
                                            data-href="{{ action([\Modules\Clinic\Http\Controllers\nutritionist\MealTimeController::class, 'create']) }}"
                                            data-container=".add_new_meal_time">
                                            <i class="fas fa-plus"></i>
                                        </a></span>
                                </th>
                                <th>Instruction <span style="float: right;"> <a
                                            class="btn btn-block btn-xs btn-success btn-modal"
                                            data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\MedicineMealController::class, 'create']) . '?type=nutritionist' }}"
                                            data-container=".add_new_meal_time">
                                            <i class="fa fa-plus"></i>
                                        </a> </span> </th>
                                <th><i class="fa fa-trash"></i></th>
                            </thead>

                            <tbody>
                                @include('clinic::nutritionist_visit.table_row', [
                                    'items' => $existingFoods,
                                    'nameField' => 'medicine_name',
                                    'productIdField' => 'product_id',
                                    'genericIdField' => 'generic_id',
                                    'mealTime' => $mealTime,
                                    'common_settings' => $common_settings,
                                ])
                            </tbody>
                        </table>
                    </div>
                @endcomponent
            </div>
            <div class="col-md-6">
                @component('components.widget', [
                    'class' => 'box-primary',
                    'title' => __('clinic::lang.life_style_product'),
                ])
                    <div class="row">
                        {!! Form::text('search_life_style_products', null, [
                            'class' => 'form-control',
                            'id' => 'search_life_style_products',
                            'placeholder' => __('clinic::lang.search_life_style_products'),
                        ]) !!}
                        <table class="table table-bordered table-striped" id="life_style_products_table"
                            style="margin-top: 3px;">
                            <thead>
                                <th>Life Style</th>
                                <th>Meal Time <span style="float: right;"><a class="btn btn-block btn-xs btn-primary btn-modal"
                                            data-href="{{ action([\Modules\Clinic\Http\Controllers\nutritionist\MealTimeController::class, 'create']) }}"
                                            data-container=".add_new_meal_time">
                                            <i class="fas fa-plus"></i>
                                        </a></span></th>
                                <th>Instruction <span style="float: right;"> <a
                                            class="btn btn-block btn-xs btn-success btn-modal"
                                            data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\MedicineMealController::class, 'create']) . '?type=nutritionist' }}"
                                            data-container=".add_new_meal_time">
                                            <i class="fa fa-plus"></i>
                                        </a> </span> </th>
                                <th><i class="fa fa-trash"></i></th>
                            </thead>

                            <tbody>
                                @include('clinic::nutritionist_visit.table_row', [
                                    'items' => $existingLifeStyles,
                                    'nameField' => 'life_style_name',
                                    'productIdField' => 'life_style_product_id',
                                    'genericIdField' => 'life_style_generic_id',
                                    'mealTime' => $mealTime,
                                    'common_settings' => $common_settings,
                                ])
                            </tbody>
                        </table>
                    </div>
                @endcomponent
            </div>
        </div>
        <div style="text-align: center; margin-top: 30px;">
            {!! Form::submit('save', ['class' => 'btn btn-primary submit_btn', 'name' => 'action', 'value' => 'save']) !!}
            {!! Form::submit('Save and Print', [
                'class' => 'btn btn-success submit_btn',
                'name' => 'action',
                'value' => 'save_and_print',
            ]) !!}

        </div>

        {!! Form::close() !!}
        <div class="modal fade add_new_meal_time" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
    </div>

@endsection
@section('javascript')
    @include('clinic::nutritionist_visit.nu_prescription_js')
@endsection
