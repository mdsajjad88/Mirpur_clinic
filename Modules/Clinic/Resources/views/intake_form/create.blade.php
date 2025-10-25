@extends('clinic::layouts.app2')

@section('title', 'Intake Form')

@section('content')
    <style>
        /* General Styles for Screen View */
        .form-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .form-control {
            font-size: 14px;
            width: 100%;
            padding: 5px;
        }

        .input-dcheck {
            height: 18px;
            width: 18px;
            margin-right: 5px;
        }

        .p-1 {
            padding: 5px !important;
        }

        .p-2 {
            padding: 10px !important;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-8">
                <div class="row">
                    @component('components.widget', ['class' => 'box-primary', 'title' => 'Search a Patient'])
                        <div class="col-md-8">
                            {!! Form::label('patient_contact_id', 'Patient Mobile No:*') !!}
                            {!! Form::select('patient_contact_id', [], null, [
                                'class' => 'form-control',
                                'placeholder' => 'Enter Patient Contact No',
                                'id' => 'patient_contact_id',
                            ]) !!}
                        </div>
                        <div class="col-md-4 mt-1">
                            {!! Form::label('', '') !!} <br>
                            {{-- <button type="submit" class="btn btn-primary" id="search_patient">Search</button> --}}
                            <button type="reset" class="btn btn-danger" id="reset_button">Reset</button>
                        </div>
                    @endcomponent
                </div>
            </div>
            <div class="col-md-2"></div>
        </div>
        <div class="row">
            <div class="col-md-1"></div>

            <div class="col-md-10">
                <div id="dynamic-section">

                </div>
            </div>
            <div class="col-md-1"></div>

        </div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('select#patient_contact_id').select2({
                ajax: {
                    url: '/get/clinic/customer',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term, // search term
                            page: params.page,
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data,
                        };
                    },
                },
                placeholder: 'Enter Patient name / phone',
                allowClear: true,
                templateResult: function(data) {
                    let template = '';
                    if (data.supplier_business_name) {
                        template += data.supplier_business_name + '<br>';
                    }
                    template += data.text + '<br>' + LANG.mobile + ': ' + data.mobile;

                    if (typeof data.total_rp !== 'undefined') {
                        const rp = data.total_rp ? data.total_rp : 0;
                        template += "<br><i class='fa fa-gift text-success'></i> " + rp;
                    }

                    return template;
                },
                templateSelection: function(data) {
                    if (!data.id) {
                        return data.text; // Placeholder or default text
                    }
                },
                minimumInputLength: 1,
                language: {
                    inputTooShort: function(args) {
                        return LANG.please_enter + args.minimum + LANG.or_more_characters;
                    },
                    noResults: function() {
                        const name = $('#patient_contact_id').data('select2').dropdown.$search.val();
                        return (
                            '<button type="button" data-name="' +
                            name +
                            '" class="btn btn-link patient_create_our_system">' +
                            '<i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp;' +
                            __translate('add_name_as_new_patient', {
                                name
                            }) +
                            '</button>'
                        );
                    },
                },
                escapeMarkup: function(markup) {
                    return markup;
                },
            });

            $(document).on('click', '.patient_create_our_system', function() {
                searchPatient();
            });
            $('#patient_contact_id').on('change', function() {
                searchPatient();
            })

            function searchPatient() {
                var patient_contact_id = $('#patient_contact_id').val();
                $.ajax({
                    type: "GET",
                    url: '/get/intake/form/data/' + patient_contact_id,
                    success: function(response) {
                        if (response.success == true) {
                            swal({
                                title: 'Patient Intake Form Found',
                                text: "Would you like to update the patient's intake form?",
                                icon: 'success',
                                buttons: {
                                    cancel: {
                                        text: 'Cancel',
                                        value: null,
                                        visible: true,
                                        className: 'btn btn-danger',
                                        closeModal: true
                                    },
                                    confirm: {
                                        text: 'Update',
                                        value: true,
                                        visible: true,
                                        className: 'btn btn-success',
                                        closeModal: true
                                    }
                                },
                            }).then((result) => {
                                if (result) {
                                    $('#reset_button').click();
                                    $('#dynamic-section').append(response
                                        .new_section_html);
                                } else {
                                    $('#reset_button').click();
                                }
                            });


                        }
                        if (response.success == false) {
                            swal({
                                title: 'Patient not found !',
                                text: 'Would you like to create a new intake form for this patient?',
                                icon: 'warning',
                                buttons: {
                                    cancel: {
                                        text: 'Cancel',
                                        value: null,
                                        visible: true,
                                        className: 'btn btn-danger',
                                        closeModal: true
                                    },
                                    confirm: {
                                        text: 'Yes, add it!',
                                        value: true,
                                        visible: true,
                                        className: 'btn btn-success',
                                        closeModal: true
                                    }
                                },
                            }).then((result) => {
                                if (result) {
                                    $('#reset_button').click();
                                    $('#dynamic-section').append(response
                                        .empty_section_html);
                                } else {
                                    $('#reset_button').click();
                                }
                            });
                        }

                    },
                    error: function(xhr, status, error) {
                        let errorMessage = "An error occurred while fetching data. ";
                        errorMessage += "Status: " + xhr.status + ", ";
                        errorMessage += "Error: " + error + ". ";
                        if (xhr.responseJSON) {
                            errorMessage += "Details: " + (xhr.responseJSON.message || JSON
                                .stringify(xhr.responseJSON));
                        } else if (xhr.responseText) {
                            errorMessage += "Details: " + xhr.responseText;
                        }
                        toastr.error(errorMessage);
                    }
                })
            }

            $('#reset_button').on('click', function(e) {
                $('#dynamic-section').empty(); // This will remove all the appended content

                $('#patient_contact_id').empty();
            });
            $(document).on('submit', 'form#reference_doctor_add_form', function(e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button(form.find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('div.add_new_doctor_reference_modal').modal('hide');
                            toastr.success(result.msg);
                            var newDoctorOption =
                                `<option value="${result.data.id}" selected>${result.data.dr_name}</option>`;
                            $('.reference_doctor_id').val();

                            $('.reference_doctor_id').append(newDoctorOption);

                            var evt = new CustomEvent("referenceAdded", {
                                detail: result.data
                            });

                            window.dispatchEvent(evt);
                            //event can be listened as
                            //window.addEventListener("brandAdded", function(evt) {}

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    complete: function() {
                        form.find('button[type="submit"]').prop('disabled', false).text(
                            'Submit');
                        form.trigger("reset");

                    }
                });
            });
            $(document).on('submit', 'form#medicine_store_form', function(e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();
                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button(form.find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('div.add_new_old_medicine_modal').modal('hide');
                            toastr.success(result.msg);
                            var newDoctorOption =
                                `<option value="${result.data.id}" selected>${result.data.medicine_name}</option>`;
                            $('.old_prescribed_medicine').append(newDoctorOption);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    complete: function() {
                        form.find('button[type="submit"]').prop('disabled', false).text(
                            'Submit');
                        form.trigger("reset");

                    }
                });
            });


            $(document).on('submit', 'form#patient_mobile_no_update_form', function(e) {
                e.preventDefault();
                var form = $(this);
                var mobile = form.find("input[name='mobile']").val().trim();

                // Client-side validation
                var mobileRegex = /^01[0-9]{9}$/; // Must start with 01 and be 11 digits
                if (!mobileRegex.test(mobile)) {
                    toastr.error('Mobile number must be 11 digits and start with 01');
                    form.find('button[type="submit"]').prop('disabled', false).text(
                        'Submit');
                    return false;
                }
                var data = form.serialize();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button(form.find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('div.mobile_update_modal').modal('hide');
                            var data = result.data;
                            if (data.mobile) {
                                var masked_mobile = '*'.repeat(data.mobile.length - 4) + data
                                    .mobile.slice(-4);
                                $("input[name='just_show_mobile']").val(masked_mobile);
                            }
                            $("input[name='mobile']").val(data.mobile);
                            toastr.success(result.msg);
                        } else {
                            form.find('button[type="submit"]').prop('disabled', false);
                            toastr.error(result.msg);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = "An error occurred while fetching data. ";
                        errorMessage += "Status: " + xhr.status + ", ";
                        errorMessage += "Error: " + xhr.statusText +
                        ". "; // Fix: Use xhr.statusText or another relevant property
                        if (xhr.responseJSON) {
                            errorMessage += "Details: " + (xhr.responseJSON.message || JSON
                                .stringify(xhr.responseJSON));
                        } else if (xhr.responseText) {
                            errorMessage += "Details: " + xhr.responseText;
                        }
                        toastr.error(errorMessage);

                        form.find('button[type="submit"]').prop('disabled', false);
                    },
                    complete: function() {
                        form.find('button[type="submit"]').prop('disabled', false);
                        form.trigger("reset");

                    }
                });
            });
        });
    </script>
@endsection
