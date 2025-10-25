@extends('clinic::layouts.app2')
@section('title', 'Hospital Survey')
@section('css')
    <style>
        .mt-5 {
            margin-top: 40px !important;
        }

        .btn-check {
            display: none;
        }

        .btn {
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 5px;
        }

        .btn.active {
            color: white;
        }

        .btn-outline-success {
            border: 1px solid #28a745;
            background-color: transparent;
        }

        /* .btn-outline-success.active {
                                background-color: #28a745;
                            } */

        .btn-outline-info {
            border: 1px solid #17a2b8;
            background-color: transparent;
        }

        .radio-label.active {
            background-color: #28a745;
            /* Success color */
            color: white;
        }

        .radio-label-info.active {
            background-color: #17a2b8;
            /* Success color */
            color: white;
        }

        .btn-outline-info.active {
            background-color: #17a2b8;
            /* Info color */
            color: white;
        }
    </style>
@endsection
@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('clinic::lang.create_patient_report')
            {{-- <small>@lang('clinic::lang.manage_you_patients')</small> --}}
        </h1>
    </section>

    <!-- Main content -->

    @php
        $url = action([\Modules\Clinic\Http\Controllers\Survey\ReviewReportController::class, 'store']);
    @endphp
    <section class="content">

        @component('components.widget', ['class' => 'box-primary', 'title' => 'Create New Report'])
            <div class="row">
                <div class="col-md-12" id="reportCreteDesign">
                    {{-- Open Form --}}
                    {!! Form::open([
                        'url' => $url,
                        'method' => 'POST',
                        'enctype' => 'multipart/form-data',
                        'id' => 'addReport',
                    ]) !!}
                    @csrf


                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('patient_profile_id', 'Patient :*') !!}
                                <div class="input-group">
                                    {!! Form::select('patient_profile_id', $patients, null, [
                                        'class' => 'form-control select2 patient_profile_id',
                                        'id' => 'patient_profile_id',
                                        'autofocus',
                                        'placeholder' => 'Select a Patient ',
                                    ]) !!}
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default bg-white btn-flat add_new_patients"
                                            data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                      
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('comment_id', 'Comments :*') !!}
                                <div class="input-group">
                                    {!! Form::select('comment_id[]', $comments, null, [
                                        'class' => 'form-control select2 new-comment',
                                        'id' => 'comments_id',
                                        'multiple' => 'multiple',
                                        'required',
                                    ]) !!}
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-default bg-white btn-flat add_new_comment"
                                            data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>

                     
                    </div>
                </div>
            </div>
        @endcomponent
        <div class="row mt-5">
            <div class="col-md-12 text-center">
                {!! Form::reset('Reset', ['class' => 'btn btn-lg btn-secondary mx-2']) !!}
                {!! Form::submit('Save Report', ['class' => 'btn btn-lg btn-primary mx-2']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </section>


    <div class="modal fade patient_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('clinic::patient.patients.partials.add_patient')
    </div>
    <div class="modal fade comment_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('clinic::survey.comment.create')
    </div>
    <div class="modal fade problem_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('clinic::survey.problem.create')
    </div>

@endsection

@section('javascript')
    <script type="text/javascript">
        
            $(document).on('click', '.add_new_patients', function() {
                $('#customer_id').select2('close');
                var name = $(this).data('name');
                $('.patient_add_modal').find('input#name').val(name);
                $('.patient_add_modal')
                    .find('select#contact_type')
                    .val('customer')
                    .closest('div.contact_type_div')
                    .addClass('hide');
                $('.patient_add_modal').modal('show');
            });

            $(document).on('click', '.add_new_comment', function() {
                $('#customer_id').select2('close');
                var name = $(this).data('name');
                $('.comment_add_modal').find('input#name').val(name);
                $('.comment_add_modal')
                    .find('select#contact_type')
                    .val('customer')
                    .closest('div.contact_type_div')
                    .addClass('hide');
                $('.comment_add_modal').modal('show');
            });
       
            $(document).on('shown.bs.modal', '.patient_add_modal', function(e) {
                $('.dob-date-picker').datepicker({
                    autoclose: true,
                    endDate: 'today',
                });
            });
       
        $(document).ready(function() {
            $('form#addReport').validate({
                rules: {
                    patient_profile_id: {
                        required: true,
                    },
                    comment_id: {
                        required: true,
                    },
                    
                },
                messages: {
                    patient_profile_id: {
                        required: 'Please select a patient',
                    },
                   
                    comment_id: {
                        required: 'Please select comment',
                    },
                },

                submitHandler: function(form) {
                    var data = $(form).serialize();
                    
                    var submitButton = $(form).find('button[type="submit"]');
                    submitButton.prop('disabled', true).text('Processing...');

                    $.ajax({
                        method: 'POST',
                        url: $(form).attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                window.location.href = '{{ url('survey/medical-report') }}';
                                toastr.success(result.msg ||
                                    'Report Added Successfully');
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                var errors = xhr.responseJSON.errors;
                                $.each(errors, function(field, messages) {
                                    toastr.error(messages[0]);
                                });
                            } else {
                                toastr.error('An error occurred: ' + xhr
                                    .statusText);
                            }
                        },
                        complete: function() {
                            submitButton.prop('disabled', false).text('Submit');
                            $('#comments_id').val('');
                           
                        }
                    });
                },
                invalidHandler: function() {
                    toastr.error(LANG.some_error_in_input_field);
                },
            });
        });
    </script>
@endsection
