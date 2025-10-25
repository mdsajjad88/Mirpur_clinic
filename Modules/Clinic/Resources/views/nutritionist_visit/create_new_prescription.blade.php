@extends('clinic::layouts.app2')

@section('title', __('clinic::lang.nutritionist_visit'))

@section('content')
    <div class="container-fluid">
        {!! Form::open([
            'url' => action([
                \Modules\Clinic\Http\Controllers\nutritionist\NutritionistVisitController::class,
                'storeNewPrescription',
            ]),
            'method' => 'post',
            'id' => 'nutritionist_new_prescription_form',
        ]) !!}
        <div class="row">
            <div class="col-md-9">
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
                            <tr>
                                <th>Health Concerns: </th>
                                <td colspan="3">
                                    {{ !empty($disease) ? implode(', ', $disease) : 'N/A' }}
                                </td>
                                
                            </tr>
                        </tbody>
                    </table>
                @endcomponent
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
            </div>
        </div>

        @php
            $filtered_products = collect($filtered_products); // convert array to collection
        @endphp
        
        {!! Form::hidden('appointment_id', $appointment_id) !!}
        {!! Form::hidden('prescription_id', $prescription->id) !!}
        {!! Form::hidden('prescription_date', $prescription_date) !!}
        {!! Form::hidden('patient_contact_id', $patient_contact_id) !!}
        {!! Form::hidden('doctor_user_id', $doctor_user_id) !!}
        <div class="row">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Select Nutritionist Food Products'])
                <div class="col">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Select</th>
                                <th>Product Name</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filtered_products->chunk(2) as $chunk)
                                <tr>
                                    @foreach ($chunk as $id => $name)
                                        <td>{{ $name }}</td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input input-icheck" type="checkbox"
                                                    name="nutritionist_products[{{ $id }}]"
                                                    value="{{ $name }}" id="product_{{ $id }}"
                                                    {{ isset($selected_products[$id]) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="product_{{ $id }}"></label>
                                            </div>
                                        </td>
                                    @endforeach

                                    @if ($chunk->count() < 2)
                                        <td></td>
                                        <td></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <input type="hidden" name="action" id="clicked_action">
                </div>
                <div class="col">
                    <h4>Lifestyle Products</h4>
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Select</th>
                                <th>Product Name</th>
                                <th>Select</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($filtered_lifestyles->chunk(2) as $chunk)
                                <tr>
                                    @foreach ($chunk as $id => $name)
                                        <td>{{ $name }}</td>
                                        <td>
                                            <div class="form-check">
                                                <input class="form-check-input input-icheck" type="checkbox"
                                                    name="nutritionist_lifestyle[{{ $id }}]"
                                                    value="{{ $name }}" id="product_{{ $id }}"
                                                    {{ isset($selected_lifestyles[$id]) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="product_{{ $id }}"></label>
                                            </div>
                                        </td>
                                    @endforeach

                                    @if ($chunk->count() < 2)
                                        <td></td>
                                        <td></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="col hide" style="margin-left: -10px;">
                    <a href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'create']) }}"
                        id="add_new_advice">
                    </a>

                </div>
                <div class="col">
                    <div class="search-container">
                        <input type="text" id="search_advice_input" class="search-input" placeholder="Search advice here" style="width: 95%;">
                        <i class="fa fa-search search-icon"></i>
                    </div>
                    <div id="adviceSearchnoResults"></div>
                </div>
                <div id="adviceFieldsContainer">
                    @if (!empty($nu->advices) && count($nu->advices) > 0)
                        @foreach ($nu->advices as $advice)
                            <div class="adviceField row mt-1">
                                <div class="col-md-11">
                                    {!! Form::text('advice_name[]', $advice->advise_name, ['class' => 'form-control custom-input']) !!}
                                    {!! Form::hidden('advice_id[]', $advice->advice_id) !!}
                                </div>
                                <div class="col-md-1">
                                    <button type="button" class="btn btn-danger btn-xs removeAdvice">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="text-center my-3" style="margin-top: 20px;">
                    <button type="submit" name="action" value="save" class="btn btn-primary submit_btn me-2">Save</button>
                    <button type="submit" name="action" value="save_and_print" class="btn btn-success submit_btn">Save and
                        Print</button>
                </div>
            @endcomponent
        </div>
        {!! Form::close() !!}
    </div>
    <div class="modal fade add_dosage_view" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            $(document).on('click', '.submit_btn', function() {
                $('#clicked_action').val($(this).val()); // hidden input e set hobe
            });

            $(document).on('submit', '#nutritionist_new_prescription_form', function(e) {
                e.preventDefault();

                if ($('input[name^="nutritionist_products"]:checked').length === 0) {
                    toastr.error("Please select at least one product.");
                    $('button[type="submit"]').attr('disabled', false);
                    return false;
                }


                var form = $(this);
                var url = form.attr('action');
                var data = form.serialize();

                $.ajax({
                    method: 'POST',
                    url: url,
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button(form.find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            window.location.href = result.redirect_url;
                        } else {
                            toastr.error(result.msg);
                            $('button[type="submit"]').attr('disabled', false);
                        }
                    },
                });
            });


            var advice_name = ''; // Declare global variable for advice

            $('#search_advice_input').autocomplete({
                source: function(request, response) {
                    $.getJSON('/get/doctor/advice/', {
                        term: request.term
                    }, function(data) {
                        if (data.results.length === 0) {
                            $('.ui-menu-item').remove();
                            advice_name = $('#search_advice_input').val();
                            $('.advice_create_our_system').remove();
                            $('#adviceSearchnoResults').after(`
                        <button type="button" class="btn btn-link advice_create_our_system" data-name="${advice_name}">
                            <i class="fa fa-plus-circle fa-lg"></i> Add "${advice_name}" as New Advice
                        </button>`);
                        } else {
                            $('.advice_create_our_system').remove();
                            response(data.results);
                        }
                    }).fail(function() {
                        toastr.error('Failed to fetch advice. Please try again.', 'Error');
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    addAdviceToPrescription(ui.item);
                    clearPlaceholder();
                    // Clear the input field explicitly
                    setTimeout(function() {
                        $('#search_advice_input').val('').focus();
                        $('.advice_create_our_system').remove();
                    }, 0);
                }
            }).autocomplete('instance')._renderItem = function(ul, item) {
                return $('<li>').append(`<div>${item.text}</div>`).appendTo(ul);
            };

            function clearPlaceholder() {
                $('#search_advice_input').val('').focus();
            }
            $(document).on('click', '.advice_create_our_system', function() {
                advice_name = $(this).data('name'); // Retrieve from data attribute
                $('#add_new_advice').data('name', advice_name).trigger('click');
            });

            // Handle "Add New Advice" modal opening
            $(document).on('click', '#add_new_advice', function(e) {
                e.preventDefault();

                let newAdvice = $(this).data('name'); // Retrieve stored advice name
                $('div.add_dosage_view').load($(this).attr('href'), function() {
                    $(this).modal('show'); // Show modal
                    $('.advice_value').val(newAdvice); // Set input field value
                });
            });

            function addAdviceToPrescription(advice) {
                var isAdviceAdded = false;
                $('#adviceFieldsContainer .adviceField').each(function() {
                    var existingName = $(this).find('input[name="advice_name[]"]').val().trim();
                    if (existingName === advice.text) {
                        isAdviceAdded = true;
                        return false;
                    }
                });

                setTimeout(function() {
                    $('#search_advice_input').val('').focus();
                    $('.advice_create_our_system').remove();
                }, 0);

                if (isAdviceAdded) {
                    swal({
                        icon: 'warning',
                        title: 'Advice already added!',
                        text: 'The selected advice is already in the advice list.',
                    });
                } else {
                    var newField = `<div class="adviceField row mt-1">
                                    <div class="col-md-11">
                                        <input type='text' name='advice_name[]' value='${advice.text}' class='form-control custom-input' required>
                                    </div>
                                    <div class="col-md-1">
                                        <input type="hidden" name="advice_id[]" value="${advice.id}">
                                        <button type="button" class="btn btn-danger btn-xs removeAdvice"><i class="fas fa-minus"></i></button>
                                    </div>
                                </div>`;

                    $('#adviceFieldsContainer').append(newField);
                }
            }

            $(document).on('click', '.removeAdvice', function() {
                $(this).closest('.adviceField').remove();
            });



            $(document).on('submit', '#doctor_advice_store_form', function(e) {
                e.preventDefault(); // Prevent default form submission

                var form = $(this);
                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            // Close the modal
                            $('.modal').modal('hide');

                            // Add the newly created advice to the list
                            addAdviceToPrescription({
                                id: response.data.id,
                                text: response.data.value
                            });

                            toastr.success(response.msg, 'Success');
                        } else {
                            toastr.error(response.msg, 'Error');
                        }
                    },
                    error: function() {
                        toastr.error('Something went wrong. Please try again.', 'Error');
                    }
                });
            });
        });
        $(document).ready(function() {
        function calculateMetrics() {
            var age = @json($patient->age);
            var sex = @json($patient->gender);
            if (sex === 'male') {
                sex = 1;
            } else if (sex === 'female') {
                sex = 0;
            }

            var height_feet = parseFloat($('input[name="current_height_feet"]').val());
            var height_inches = parseFloat($('input[name="current_height_inches"]').val());
            // Convert height to centimeters
            var height_cm = (height_feet * 30.48) + (height_inches * 2.54);

            var weight = parseFloat($('input[name="current_weight"]').val());

            if (height_cm > 0 && weight > 0) {
                var bmi = weight / ((height_cm / 100) * (height_cm / 100));
                $('input[name="bmi"]').val(bmi.toFixed(2));
                var bodyFat = (1.20 * bmi) + (0.23 * age) - (10.8 * sex) - (5.4);
                var fatMass = (bodyFat / 100) * weight;
                var leanMass = weight - fatMass;

                $('input[name="body_fat_percent"]').val(bodyFat.toFixed(2));
                $('input[name="fat_mass_percent"]').val(fatMass.toFixed(2));
                $('input[name="lean_mass_percent"]').val(leanMass.toFixed(2));
            } else {
                $('input[name="bmi"]').val('');
                $('input[name="body_fat_percent"]').val('');
                $('input[name="fat_mass_percent"]').val('');
                $('input[name="lean_mass_percent"]').val('');
            }
        }
        $('input[name="current_height_feet"], input[name="current_height_inches"], input[name="current_weight"]')
            .on('keyup change', function() {
                calculateMetrics();
            });
    });
    </script>
@endsection
