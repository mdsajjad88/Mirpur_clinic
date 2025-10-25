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

        #dosage_th {
            min-width: 120px !important;
        }

        #dosage_time_th {
            min-width: 135px !important;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col custom-row mt-2 doctor-heading">
                <div class="text-left">
                    <a href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'index']) }}">
                        <i class="fas fa-backward"></i>&nbsp;
                    </a>
                    <strong>@lang('clinic::lang.acrh')</strong>
                </div>
            </div>
        </div>
        @php
            $url = action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'store']);
            $form_id = 'create_new_prescriptions';
        @endphp

        {!! Form::open(['url' => $url, 'id' => $form_id]) !!}
        {!! Form::hidden('appointment_id', $appointment->id) !!}

        <div class="row">
            <div class="col-md-3">
                @component('components.widget')
                    <div class="prescription-left-widget">
                        <div class="row">
                            <div class="col-md-6"><b>@lang('clinic::lang.total_visits')({{ $totalVisit ?: 0 }})</b></div>
                            <div class="col-md-6 text-right">@lang('clinic::lang.visit_date'):</div>
                        </div>
                        <div class="row">
                            <div class="col-md-9">
                                {!! Form::date('visit_date', $appointment->request_date, ['class' => 'form-control']) !!}
                            </div>
                            <div class="col-md-3">
                                {!! Form::button(__('clinic::lang.search'), ['class' => 'btn btn-info btn-sm']) !!}
                            </div>
                        </div>
                    </div>
                @endcomponent

                @component('components.widget')
                    <div class="prescription-left-widget2">
                        <div class="row">
                            <div class="col-md-10"><b>@lang('clinic::lang.chief_complain')</b></div>
                            <div class="col-md-2 text-right"><a href=""><i class="fas fa-pen"></i></a></div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                {!! Form::textarea('chief_complain', null, [
                                    'rows' => 2,
                                    'class' => 'form-control',
                                    'placeholder' => 'Write/Search(C/C)',
                                ]) !!}
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-10">
                                <small>@lang('clinic::lang.on_examination') <i class="fas fa-square-letterboxd"></i>@lang('clinic::lang.follow_up_data')</small>
                            </div>
                            <div class="col-md-2"><a href=""><i class="fas fa-pen"></i></a></div>
                        </div>

                        <!-- Height and Weight Fields -->
                        <div class="row mt-1">
                            <div class="col-md-6">
                                {!! Form::label('current_height', __('clinic::lang.height_cm')) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::number('current_height', $appointment->patientProfile->height_cm ?? '', ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col-md-6">
                                {!! Form::label('current_weight', __('clinic::lang.weight_kg')) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::number('current_weight', $appointment->patientProfile->weight_kg ?? '', ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                BMI: Min: KG | Max: KG | Ideal: KG
                            </div>
                        </div>

                        <!-- Vital Signs -->
                        <div class="row mt-1">
                            <div class="col-md-6">
                                {!! Form::label('pulse_rate', __('clinic::lang.pulse_min')) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::number('pulse_rate', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col-md-6">
                                {!! Form::label('blood_pressure', __('clinic::lang.blood_pressure')) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::number('blood_pressure', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col-md-6">
                                {!! Form::label('respiratory', __('clinic::lang.respiratory')) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::text('respiratory', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="row mt-1">

                            <div class="col-md-6">
                                {!! Form::label('body_temp', __('clinic::lang.temperature')) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::text('body_temp', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col-md-6">
                                {!! Form::label('body_fat', __('clinic::lang.body_fat')) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::text('body_fat', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col-md-6">
                                {!! Form::label('fat_mass', __('clinic::lang.fat_mass')) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::text('fat_mass', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col-md-6">
                                {!! Form::label('lean_mass', __('clinic::lang.lean_mass')) !!}
                            </div>
                            <div class="col-md-6">
                                {!! Form::text('lean_mass', null, ['class' => 'form-control']) !!}
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col-md-12">
                                {!! Form::textarea('oe_note', null, [
                                    'rows' => 2,
                                    'class' => 'form-control',
                                    'placeholder' => 'Write/Search (O/E)',
                                ]) !!}
                            </div>
                        </div>
                    </div>
                @endcomponent
            </div>

            <div class="col-md-6">
                @component('components.widget')
                    <div class="prescription-middle-widget">
                        <div class="row">
                            <div class="col-md-6">
                                <p><b>{{ $appointment->patientProfile->first_name ?? '' }}
                                        {{ $appointment->patientProfile->last_name ?? '' }}({{ $appointment->appointment_number }})
                                    </b> </p>
                            </div>
                            <div class="col-md-6 text-right">
                                <p><span>@lang('clinic::lang.age/sex'): {{ $appointment->patientProfile->age ?? '' }}Y/
                                        {{ $appointment->patientProfile->gender ?? '' }}</span>
                                    <span>@lang('clinic::lang.blood'):{{ $appointment->patientProfile->blood_group ?? '' }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                <p><span>@lang('clinic::lang.religion'): NAI </span><span>@lang('clinic::lang.marital_status'):
                                        {{ $appointment->patientProfile->marital_status ?? '' == 0 ? 'Unmarried' : 'Married' }};</span><span>@lang('clinic::lang.education'):
                                        NAI; </span><span>@lang('clinic::lang.profession'): N/A </span></p>
                                <p><span>@lang('clinic::lang.address'): {{ $appointment->patientProfile->address ?? '' }}
                                        {{ $appointment->patientProfile->address2 ?? '' }}</span></p>
                            </div>
                        </div>
                    </div>
                @endcomponent

                @component('components.widget')
                    <div class="prescription-middle-widget2">

                        <div class="row">
                            <div class="col-md-10">
                                <input type="text" id="search_prescription_medicine" class="form-control"
                                    placeholder="Search Medicine Here">
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="search-button" class="btn btn-success">
                                    <i class="fa fa-search"></i> @lang('Search')
                                </button>
                            </div>
                        </div>

                        <div class="row mt-1">
                            <div class="col">
                                <table class="table table-bordered table-th-green table-striped"
                                    id="prescription_medicine_product_table">
                                    <thead>
                                        <tr>
                                            <th>@lang('clinic::lang.drug_name')</th>
                                            <th id="dosage_th">@lang('clinic::lang.dosage') <span><a
                                                        href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'storeDosageView']) }}"
                                                        id="add_new_dosage">
                                                        <i class="fas fa-plus" id="add_new_dosage_btn"></i>
                                                    </a></span></th>
                                            <th id="dosage_time_th">@lang('clinic::lang.dosage_time')</th>
                                            <th>@lang('clinic::lang.duration')</th>
                                            <th><i class="fa fa-trash"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody id="prescribe_medicine_content">

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endcomponent
                <div class="row">
                    <div class="col-md-6">
                        @component('components.widget', ['class' => 'box-primary'])
                            <div class="row">
                                <div class="col-md-10">@lang('clinic::lang.note')</div>
                                <div class="col-md-2"><a href="#"><i class="fas fa-pen"></i></a></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::textarea('comments', null, ['rows' => 2, 'placeholder' => 'Write Note', 'class' => 'form-control']) !!}
                                </div>
                            </div>
                        @endcomponent
                    </div>

                    <div class="col-md-6">
                        @component('components.widget', ['class' => 'box-primary'])
                            <div class="row">
                                <div class="col-md-10">@lang('clinic::lang.follow_up_data')</div>
                                <div class="col-md-2"><a href=""><i class="fas fa-pen"></i></a></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    {!! Form::textarea('follow_up_data', '', [
                                        'rows' => 2,
                                        'placeholder' => 'Write/Search Follow up',
                                        'class' => 'form-control',
                                    ]) !!}
                                </div>
                            </div>
                        @endcomponent
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                @component('components.widget')
                    <div class="prescription-right-widget">
                        <div class="row">
                            <div class="col-md-6">
                                {!! Form::button(__('clinic::lang.quick_mark'), ['class' => 'btn btn-info btn-sm']) !!}
                            </div>
                            <div class="col-md-6 text-right"><a href=""><i class="fas fa-pen-square"></i></a> <a
                                    href=""><i class="fas fa-list"></i></a></div>
                        </div>
                    </div>
                @endcomponent

                @component('components.widget')
                    <div class="row" id="testSection">
                        <div class="col">
                            <div class="row">
                                <div class="col-md-10">
                                    <input type="text" id="search_prescription_test" class="form-control"
                                        placeholder="Search Test Here">
                                </div>
                                <div class="col-md-2">
                                    <button type="button"class="btn btn-success">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="testFieldsContainer">

                            </div>
                        </div>
                    </div>
                @endcomponent
                @component('components.widget')
                    <div class="row">
                        <div class="col-md-2">
                            {!! Form::label('advice', __('clinic::lang.advice') . ':', ['class' => 'mr-2 mb-0']) !!}
                        </div>
                        <div class="col-md-8">
                            {!! Form::select(
                                'advice_id',
                                ['' => 'Please select an advice'] + $advices->pluck('value', 'id')->toArray(),
                                null,
                                ['class' => 'form-control select2 w-auto'],
                            ) !!}
                        </div>
                        <div class="col-md-2">
                            <a href=""><i class="fas fa-pen"></i></a>
                        </div>
                    </div>

                    <div class="row mt-1">
                        <div class="col-md-12">
                            {!! Form::textarea('natural_advise_others_info', null, [
                                'rows' => 2,
                                'class' => 'form-control',
                                'placeholder' => 'Write/Search Advice',
                            ]) !!}
                        </div>
                    </div>
                @endcomponent

                @component('components.widget', ['class' => 'box-primary'])
                    <div class="row">
                        <div class="col-md-10 d-flex">
                            {!! Form::label('plan', __('clinic::lang.plan') . ':') !!}
                        </div>
                        <div class="col-md-2"><a href=""><i class="fas fa-pen"></i></a></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::textarea('plan', null, ['rows' => 2, 'class' => 'form-control', 'placeholder' => 'Write/Search Plan']) !!}
                        </div>
                    </div>
                @endcomponent

                @component('components.widget', ['class' => 'box-primary'])
                    <div class="row">
                        <div class="col-md-10">
                            @lang('clinic::lang.diagnosis')
                        </div>
                        <div class="col-md-2"><a href=""><i class="fas fa-pen"></i></a></div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            {!! Form::textarea('diagnoses_others_info', null, [
                                'rows' => 2,
                                'class' => 'form-control',
                                'placeholder' => 'Write/Search Diagnosis',
                            ]) !!}
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>





        <div class="row">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <button class="btn btn-success">@lang('clinic::lang.save')</button>
                <button class="btn btn-primary">@lang('clinic::lang.save_and_print')</button>
                <button class="btn btn-info">@lang('clinic::lang.investigation_slip')</button>
                <button class="btn make_app">@lang('clinic::lang.medicine_slip')</button>
            </div>
            <div class="col-md-3"></div>
        </div>

        {!! Form::close() !!}

    </div>
    <div class="modal fade add_dosage_view" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

@endsection

@section('javascript')
    <script type="text/javascript">
        $('#search_prescription_medicine').autocomplete({
            source: function(request, response) {
                $.getJSON('/products/list', {
                    term: request.term
                }, response);
            },
            minLength: 2,
            select: function(event, ui) {
                addProductToPrescription(ui.item);

            }
        }).autocomplete('instance')._renderItem = function(ul, item) {
            var string = '<div>' + item.name + ' (' + item.sku + ') ';
            if (item.brand_id) {
                string += '<br>Brand: ' + item.brand.name + '</div>';
            }
            return $('<li>').append(string).appendTo(ul);
        };

        var dosages = @json($dosages);
        var times = ["before meal", "after meal", "during meal"];

        function addProductToPrescription(product) {
            var isProductAlreadyAdded = false;
            var index = 0;

            $('#prescription_medicine_product_table tbody tr').each(function() {
                var existingSKU = $(this).find('td:nth-child(1) input').val().trim();
                if (existingSKU === product.name) {
                    isProductAlreadyAdded = true;
                    return false;
                }
            });

            if (isProductAlreadyAdded) {
                swal({
                    icon: 'warning',
                    title: 'Medicine already added!',
                    text: 'The selected Medicine is already in the Medicine list.',
                });
            } else {
                var dosageOptions = '';
                var dosageTime = ''
                dosages.forEach(function(dosage) {
                    dosageOptions +=
                        `<option value="${dosage.value}" class='form-control'>${dosage.value}</option>`;
                });
                times.forEach(function(times) {
                    dosageTime +=
                        `<option value="${times}" class='form-control'>${times}</option>`;
                });

                var rowHtml = `<tr>
            <td><input type='text' name='medicine_name[]' value='${product.name}' class='form-control' readonly required></td>
            <td>
                <select name='dosage[]' class='form-control dosage_class' required>
                    ${dosageOptions}
                </select>
            </td>
            <td>
                <select name='dosage_time[]' class='form-control' required>  ${dosageTime}
                </select> 
            </td>
            <td><input type='text' name='dosage_duration[]' class='form-control' placeholder='Ex: Running' required></td>
            <td>
                <input type="hidden" name="product_id[]" value="${product.product_id}">
                <button type="button" class="btn btn-danger btn-remove-row btn-sm">
                    <i class="fa fa-times" style="font-size: 12px; cursor: pointer;"></i>
                </button>
            </td>
        </tr>`;

                $('#prescribe_medicine_content').append(rowHtml);

                validateForm();

                index++;
            }
        }

        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
        });

        $('#create_new_prescriptions').validate({
            rules: {
                'dosage[]': {
                    required: true
                },
                'dosage_time[]': {
                    required: true
                },
                'dosage_duration[]': {
                    required: true
                },
            },
            messages: {
                'dosage[]': {
                    required: 'Field is required'
                },
                'dosage_time[]': {
                    required: 'Field is required'
                },
                'dosage_duration[]': {
                    required: 'Field is required'
                }
            },
            submitHandler: function(form) {
                let isValid = true;

                $('#prescribe_medicine_content tr').each(function() {
                    var dosage = $(this).find('input[name="dosage[]"]').val().trim();
                    var dosageTime = $(this).find('input[name="dosage_time[]"]').val().trim();
                    var dosageDuration = $(this).find('input[name="dosage_duration[]"]').val().trim();

                    if (!dosage || !dosageTime || !dosageDuration) {
                        isValid = false;
                        $(this).find('input').each(function() {
                            if (!$(this).val()) {
                                $(this).addClass('is-invalid');
                            }
                        });
                    } else {
                        $(this).find('input').removeClass('is-invalid');
                    }
                });

                if (isValid) {
                    $.ajax({
                        url: form.action,
                        method: 'POST',
                        data: $(form).serialize(),
                        success: function(response) {
                            if (response.success) {
                                $('#prescribe_medicine_content').empty();

                                swal({
                                    icon: 'success',
                                    title: response.msg,
                                    showCancelButton: true,
                                    confirmButtonText: 'OK',
                                    timer: 2000
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        // Redirect to the response's redirect URL if available
                                        window.location.href = response.redirectUrl ||
                                            window.location.href;
                                    }
                                });
                            } else {
                                swal({
                                    icon: 'error',
                                    title: 'An error occurred',
                                    text: response.msg,
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            swal({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'There was a problem with the request. Please try again later.',
                            });
                        }
                    });
                    return false;
                } else {
                    toastr.error('Doasage all info are required')
                    return false;
                }
            }
        });

        function validateForm() {
            $('#create_new_prescriptions').valid();
        }

        $(document).on('blur', 'input[required]', function() {
            if (!$(this).val()) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        $('#search_prescription_test').autocomplete({
            source: function(request, response) {
                $.getJSON('/products/list', {
                    term: request.term
                }, response);
            },
            minLength: 2,
            select: function(event, ui) {
                addTestToPrescription(ui.item);
            }
        }).autocomplete('instance')._renderItem = function(ul, item) {
            var string = '<div>' + item.name + ' (' + item.sku + ') ';
            if (item.brand_id) {
                string += '<br>Brand: ' + item.brand.name + '</div>';
            }
            return $('<li>').append(string).appendTo(ul);
        };

        function addTestToPrescription(product) {
            var isTestAlreadyAdded = false;
            $('#testFieldsContainer .testField').each(function() {
                var existingName = $(this).find('input[name="test_name[]"]').val().trim();
                if (existingName === product.name) {
                    isTestAlreadyAdded = true;
                    return false;
                }
            });

            if (isTestAlreadyAdded) {
                swal({
                    icon: 'warning',
                    title: 'Test already added!',
                    text: 'The selected Test is already in the Test list.',
                });
            } else {
                var newField = `<div class="testField row mt-1">
                    <div class="col-md-8">
                        <input type='text' name='test_name[]' value='${product.name}' class='form-control' readonly required>
                    </div>
                    <div class="col-md-4">
                        <input type="hidden" name="test_product_id[]" value="${product.product_id}">
                        <button type="button" class="btn btn-danger btn-sm removeField ml-2">Remove</button>
                    </div>
                </div>`;

                $('#testFieldsContainer').append(newField);
            }
        }

        $(document).on('click', '.removeField', function() {
            $(this).closest('.testField').remove();
        });
        $(document).on('click', '#add_new_dosage', function(e) {
            e.preventDefault();
            $('div.add_dosage_view').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
    </script>



@endsection
