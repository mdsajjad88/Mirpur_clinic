<script type="text/javascript">
    var dosages = @json($dosages);
    var meals = @json($meals);
    var durations = @json($durations);
    var frequencies = @json($frequencies);
    var show_medicine_name_as = @json($common_settings['show_medicine_name_as']);
    var dosageOptions = '';
    var durationOptions = '';
    var mealsOptions = '';
    var frequencyOptions = '';
    frequencyOptions += `<option value="">Select Frequency</option>`;

    $.each(dosages, function(id, dosage) {
        dosageOptions += `<option value="${id}">${dosage}</option>`;
    });
    $.each(meals, function(id, meal) {
        mealsOptions += `<option value="${id}">${meal}</option>`;
    });
    $.each(durations, function(id, duration) {
        durationOptions += `<option value="${id}">${duration}</option>`;
    });
    $.each(frequencies, function(id, frequency) {
        frequencyOptions += `<option value="${id}">${frequency}</option>`;
    });


    checkAPIConnection();

    function checkAPIConnection() {
        $.ajax({
            url: 'https://awc.careneterp.com:82/api/products',
            type: 'GET',
            data: {
                term: 'test545'
            }, // Just to simulate a request
            dataType: "json",
            success: function(response) {
                console.log("API Connected:", response);
                $('#search_prescription_medicine').prop('disabled', false); // Enable search
                $('#search_prescription_medicine').css('border', '');
            },
            error: function(xhr, status, error) {
                console.log("API Error:", status, error);
                console.log("Response:", xhr.responseText);
                $('#search_prescription_medicine').prop('disabled', true); // Disable search
                $('#search_prescription_medicine').css('border', '1px solid red');
            }
        });
    }




    $('#search_prescription_medicine').autocomplete({
        source: function(request, response) {
            $.getJSON('https://awc.careneterp.com:82/api/products', {
                term: request.term
            }, function(data) {
                if (data.length === 0) {
                    toastr.error('No matching Medicine found.', 'Not Found');

                    swal({
                        title: 'No Medicine found',
                        text: 'Do you want to add the searched medicine as a custom entry?',
                        icon: 'warning',
                        buttons: ['Cancel', 'Yes'],
                        dangerMode: true
                    }).then((willAdd) => {
                        if (willAdd) {
                            var inputValue = $('#search_prescription_medicine')
                                .val();

                            var rowHtml = `<tr>
                    <td><input type='text' name='medicine_name[]' value='${inputValue}' class='form-control custom-input' required></td>
                    <td>
                        <select name='taken_instruction[]' class='form-control custom-input dosage_class' required>
                            ${dosageOptions}
                        </select>
                    </td>
                    <td>
                        <select name='dosage_form[]' class='form-control custom-input dosage_form' required>  ${mealsOptions}
                        </select> 
                    </td>
                    <td><select name='medication_duration[]' class='form-control custom-input medication_duration' required>  ${durationOptions}
                        </select> </td>
                    <td>
                        <textarea name='medicine_comment[]' class='form-control custom-input' placeholder='comment'  rows="1"></textarea>
                    </td>
                    <td>
                        <input type="hidden" name="product_id[]" value="0"> <!-- Set product_id as 0 since it's not found -->
                        <button type="button" class="btn btn-danger btn-remove-row btn-xs">
                            <i class="fa fa-times" style="font-size: 12px; cursor: pointer;"></i>
                        </button>
                    </td>
                </tr>`;

                            $('#prescribe_medicine_content').append(rowHtml);

                            validateForm();
                        }
                    });

                }
                response(data);
            }).fail(function() {
                toastr.error('Failed to fetch products. Please try again.', 'Error');
            });
        },
        minLength: 2,
        select: function(event, ui) {
            addProductToPrescription(ui.item);
        }
    }).autocomplete('instance')._renderItem = function(ul, item) {
        var string = '<div>' + (item.short_name ? item.short_name + '. ' : '');

        if (show_medicine_name_as === 'generic' && item.generic_name) {
            string += item.generic_name;
        } else {
            string += item.name;
        }

        string += ' ' + (item.strength ? '(' + item.strength + ') ' : '');
        if (item.brand_id && show_medicine_name_as != 'generic') {
            string += '<br>Brand: ' + item.brand.name + '</div>';
        } else {
            string += '<br>Name: ' + item.name +  '</div>';
        }
        return $('<li>').append(string).appendTo(ul);
    };

    function addProductToPrescription(product) {
        var isProductAlreadyAdded = false;
        var index = 0;

        var fullProductName = (product.short_name ? product.short_name + '. ' : '') + product.name + ' '+(product.size ?
            '(' + product.size + ') ' : '');

        $('#prescription_medicine_product_table tbody tr').each(function() {
            var existingSKU = $(this).find('td:nth-child(1) input').val().trim();
            if (existingSKU === fullProductName.trim()) {
                isProductAlreadyAdded = true;
                return false; // break the loop
            }
        });

        if (isProductAlreadyAdded) {
            swal({
                icon: 'warning',
                title: 'Medicine already added!',
                text: 'The selected Medicine is already in the Medicine list.',
            });
        } else {

            var rowHtml = `<tr>
        <td>
            <input ${show_medicine_name_as === 'generic' ? 'type="hidden"' : 'readonly required type="text"'} name='medicine_name[]' value='${(product.short_name ? product.short_name + '. ' : '') + product.name + " " + (product.strength ? "(" + product.strength + ") " : "")}' class='form-control custom-input'>

            <input ${show_medicine_name_as === 'generic' ? 'readonly required type="text"' : 'type="hidden"'} name='generic_name[]' value='${(product.short_name ? product.short_name + '. ' : '') + (show_medicine_name_as === "generic" && product.generic_name ? product.generic_name : product.name) + " " + (product.strength ? "(" + product.strength + ") " : "")}' class='form-control custom-input'>
        </td>
        <td>
            <select name='taken_instruction[]' class='form-control custom-input dosage_class' required>
                ${dosageOptions}
            </select>
        </td>
        <td>
            <select name='dosage_form[]' class='form-control custom-input dosage_form' required>  ${mealsOptions}
            </select> 
        </td>
        <td><select name='medication_duration[]' class='form-control custom-input medication_duration' required>  ${durationOptions}
            </select> 
        </td>
        <td>
            <textarea  name='medicine_comment[]'' class='form-control custom-input' placeholder='comment'  rows="1"></textarea>
        </td>
        
        <td>
            <input type="hidden" name="product_id[]" value="${product.product_id}">
            <input type="hidden" name="generic_id[]" value="${product.generic_id}">
            <button type="button" class="btn btn-danger btn-remove-row btn-xs">
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


    $(document).ready(function() {
        $('#create_new_prescriptions').validate({
            rules: {
                'taken_instruction[]': {
                    required: true
                },
                'dosage_form[]': {
                    required: true
                },
                'medication_duration[]': {
                    required: true
                },
            },
            messages: {
                'taken_instruction[]': {
                    required: 'Field is required'
                },
                'dosage_form[]': {
                    required: 'Field is required'
                },
                'medication_duration[]': {
                    required: 'Field is required'
                }
            },
            submitHandler: function(form) {
                let clickedButton = $(document.activeElement);
                let action = clickedButton.val();
                console.log(action);
                if ($('#prescribe_medicine_content').children().length === 0) {
                    toastr.error('Select at least one medicine before submitting.');
                    return false;
                }

                let isValid = true;

                if (action === "save_as_template") {
                    var appointmentId = @json($appointment->id);
                    $.ajax({
                        url: '/check/template/exists/' + appointmentId,
                        method: 'GET',
                        dataType: 'json',
                        async: false, // Ensure this AJAX call is synchronous
                        success: function(response) {
                            if (response.success == true) {
                                isValid = true;
                            } else if (response.success == false) {
                                $('.add_new_template').click();
                                isValid = false;
                            }
                        },
                        error: function() {
                            toastr.error('Error checking template.');
                            isValid = false;
                        }
                    });

                    if (!isValid) {
                        return false;
                    }
                }

                $('#prescribe_medicine_content tr').each(function() {
                    var dosage = $(this).find('input[name="taken_instruction[]"]').val()
                        .trim();
                    var dosageTime = $(this).find('input[name="dosage_form[]"]').val()
                        .trim();
                    var dosageDuration = $(this).find('input[name="medication_duration[]"]')
                        .val().trim();

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
                            if (response.success == true) {
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
                                        window.location.href = response
                                            .redirectUrl || window.location
                                            .href;
                                    }
                                });
                            } else if (response.success == false) {
                                swal({
                                    icon: 'error',
                                    title: 'An error occurred',
                                    text: response.msg,
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            var errorMessage = xhr.responseJSON && xhr.responseJSON
                                .message ? xhr.responseJSON.message :
                                'There was a problem with the request. Please try again later.';
                            swal({
                                icon: 'error',
                                title: 'Oops...',
                                text: errorMessage,
                            });
                        }
                    });
                    return false;
                } else {
                    toastr.error('Dosage info is required');
                    return false;
                }
            }
        });
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
            $.getJSON('/get/search/product/', {
                sub_type: 'test',
                term: request.term
            }, function(data) {
                if (data.length === 0) {
                    toastr.error('No matching test found.', 'Not Found');
                    swal({
                        title: 'No test found',
                        text: 'Do you want to add the searched test as a custom entry?',
                        icon: 'warning',
                        buttons: ['Cancel', 'Yes'],
                        dangerMode: true
                    }).then((willAdd) => {
                        if (willAdd) {
                            var inputValue = $('#search_prescription_test')
                                .val();

                            var testHtml = `<div class="testField row mt-1">
                                    <div class="col-md-7">
                                        <input type='text' name='test_name[]' value='${inputValue}' class='form-control custom-input' required>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="row">
                                            <div style="padding-left: 0; padding-right: 0" class="col-md-9">
                                        <textarea name='test_comment[]' class='form-control custom-input' placeholder='comment'  rows="1"></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="hidden" name="test_product_id[]" value="0">
                                        <button type="button" class="btn btn-danger btn-xs removeField"><i class="fas fa-minus"></i></button>
                                    </div>
                                        </div>
                                    </div>
                                </div>`;
                            $('#testFieldsContainer').append(testHtml);
                            $('#search_prescription_test').val('').focus();
                            validateForm();
                        }
                    });
                }
                response(data);
            }).fail(function() {
                toastr.error('Failed to fetch test. Please try again.', 'Error');
            });
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
                <div class="col-md-7">
                    <input type='text' name='test_name[]' value='${product.name}' class='form-control custom-input' readonly required>
                </div>
                <div class="col-md-5">
                    <div class="row">
                        <div style="padding-left: 0; padding-right: 0" class="col-md-9">
                    <textarea name='test_comment[]' class='form-control custom-input' placeholder='comment'  rows="1"></textarea>
                </div>
                <div class="col-md-3">
                    <input type="hidden" name="test_product_id[]" value="${product.product_id}">
                    <button type="button" class="btn btn-danger btn-xs removeField"><i class="fas fa-minus"></i></button>
                </div>
                    </div>
                </div>
                
            </div>`;

            $('#testFieldsContainer').append(newField);
        }
    }


    $('#search_prescription_therapy').autocomplete({
        source: function(request, response) {
            $.getJSON('/get/search/product/', {
                sub_type: 'therapy',
                term: request.term
            }, function(data) {
                if (data.length === 0) {
                    toastr.error('No matching therapy found.', 'Not Found');
                    swal({
                        title: 'No therapy found',
                        text: 'Do you want to add the searched therapy as a custom entry?',
                        icon: 'warning',
                        buttons: ['Cancel', 'Yes'],
                        dangerMode: true
                    }).then((willAdd) => {
                        if (willAdd) {
                            var inputValue = $('#search_prescription_therapy')
                                .val();

                            var newField = `<div class="therapyField row mt-1">
                                                <div class="col-sm-5">
                                                    <input type='text' name='therapy_name[]' value='${inputValue}' class='form-control custom-input' required>
                                                </div>
                                                <div class="col-sm-7">
                                                    <div class="row">
                                                        <div class="col-sm-6" style="padding: 1px; margin: 0px">
                                                    
                                                            <select name='therapy_frequency[]' class='form-control custom-input'>
                                                                ${frequencyOptions}
                                                            </select>
                                                    
                                                        </div>
                                                        <div class="col-sm-4" style="padding: 1px; margin: 0px">
                                                            <input type="text" name="session_count[]" class="form-control custom-input" placeholder="session">
                                                        </div>
                                                        <div class="col-sm-2" style="padding: 1px; margin: 0px">
                                                            <input type="hidden" name="therapy_product_id[]" value="0">
                                                            <button type="button" class="btn btn-danger btn-xs removeTherapyField"><i class="fas fa-minus"></i></button>
                                                        </div>                                        
                                                    </div>
                                                </div>                                  
                                            </div>`;

                            $('#therapyFieldsContainer').append(newField);
                            $('#search_prescription_therapy').val('').focus();
                            validateForm();
                        }
                    });
                }
                response(data);
            }).fail(function() {
                toastr.error('Failed to fetch therapy. Please try again.', 'Error');
            });
        },
        minLength: 2,
        select: function(event, ui) {
            addTherapyToPrescription(ui.item);
        }
    }).autocomplete('instance')._renderItem = function(ul, item) {
        var string = '<div>' + item.name + ' (' + item.sku + ') ';
        // if (item.brand_id) {
        //     string += '<br>Brand: ' + item.brand.name + '</div>';
        // }
        return $('<li>').append(string).appendTo(ul);
    };

    function addTherapyToPrescription(product) {
        var isTestAlreadyAdded = false;
        $('#therapyFieldsContainer .therapyField').each(function() {
            var existingName = $(this).find('input[name="therapy_name[]"]').val().trim();
            if (existingName === product.name) {
                isTestAlreadyAdded = true;
                return false;
            }
        });

        if (isTestAlreadyAdded) {
            swal({
                icon: 'warning',
                title: 'Therapy already added!',
                text: 'The selected Therapy is already in the Therapy list.',
            });
        } else {
            var newField = `<div class="therapyField row mt-1">
                                <div class="col-md-5">
                                    <input type='text' name='therapy_name[]' value='${product.name}' class='form-control custom-input' readonly required>
                                </div>
                                <div class="col-md-7">
                                    <div class="row">
                                        <div class="col-md-6" style="padding: 1px; margin: 0px">
                                    
                                            <select name='therapy_frequency[]' class='form-control custom-input'>
                                                ${frequencyOptions}
                                            </select>                   
                                        </div>
                                        <div class="col-md-4" style="padding: 1px; margin: 0px">
                                            <input type="text" name="session_count[]" class="form-control custom-input" placeholder="session">
                                        </div>
                                        <div class="col-md-2" style="padding: 1px; margin: 0px">
                                            <input type="hidden" name="therapy_product_id[]" value="${product.product_id}">
                                            <button type="button" class="btn btn-danger btn-xs removeTherapyField ml-2"><i class="fas fa-minus"></i></button>
                                        </div>
                                    </div>
                                </div>                
                            </div>`;

            $('#therapyFieldsContainer').append(newField);
        }
    }


    $(document).on('keyup', '#search_complain_input', function() {
        if ($('#search_complain_input').val().length === 0) {
            $('.complain_create_our_system').remove();
        }
    });
    var complain_name = ''; // Declare global variable

    $('#search_complain_input').autocomplete({
        source: function(request, response) {
            $.getJSON('/get/search/complain/', {
                term: request.term
            }, function(data) {
                if (data.length === 0) {
                    $('.ui-menu-item').remove();
                    complain_name = $('#search_complain_input').val();
                    $('.complain_create_our_system').remove();
                    $('#complainSearchnoResults').after(`
                    <button type="button" class="btn btn-link complain_create_our_system" data-name="${complain_name}">
                        <i class="fa fa-plus-circle fa-lg"></i> Add "${complain_name}" as New Complain
                    </button>
                `);
                } else {
                    $('.complain_create_our_system').remove();
                    response(data);
                }
            }).fail(function() {
                toastr.error('Failed to fetch complain. Please try again.', 'Error');
            });
        },
        minLength: 2,
        select: function(event, ui) {
            addComplainToPrescription(ui.item);
            clearPlaceholderComplain();
            // Clear the input field explicitly
            setTimeout(function() {
                $('#search_complain_input').val('').focus();
                $('.complain_create_our_system').remove();
            }, 0);
        }
    }).autocomplete('instance')._renderItem = function(ul, item) {
        return $('<li>').append(`<div>${item.name}</div>`).appendTo(ul);
    };

    function clearPlaceholderComplain() {
        $('#search_complain_input').val('').focus();
    }
    $(document).on('click', '.complain_create_our_system', function() {
        complain_name = $(this).data('name'); 
        $('#add_new_complain').data('name', complain_name).trigger(
            'click');
    });

    // Handle "Add New Complain" modal opening
    $(document).on('click', '#add_new_complain', function(e) {
        e.preventDefault();

        let newComplain = $(this).data('name'); // Retrieve stored complain name
        $('div.add_dosage_view').load($(this).attr('href'), function() {
            $(this).modal('show'); // Show modal
            $('.complain_value').val(newComplain); // Set input field value
        });
    });
    $(document).on('click', '.add_new_template', function() {
        $('.template_modal').modal('show');
    });


    function addComplainToPrescription(product) {
        var isComplainAdded = false;
        $('#complainFieldsContainer .complainField').each(function() {
            var existingName = $(this).find('input[name="complain_name[]"]').val().trim();
            if (existingName === product.name) {
                isComplainAdded = true;
                return false;
            }
        });

        setTimeout(function() {
            $('#search_complain_input').val('').focus();
            $('.complain_create_our_system').remove();
        }, 0);

        if (isComplainAdded) {
            swal({
                icon: 'warning',
                title: 'Complain already added!',
                text: 'The selected complain is already in the complain list.',
            });
        } else {
            var newField = `<div class="complainField row mt-1">
                
                <div class="col-md-10">
                    <input type='text' name='complain_name[]' value='${product.name}' class='form-control custom-input hide' readonly required>
                   <input type='text' name='complain_comment[]' value='${product.name}' class='form-control custom-input'>
                </div>
                <div class="col-md-2">
                    <input type="hidden" name="chief_complain_id[]" value="${product.id}">
                    <button type="button" class="btn btn-danger btn-xs removeComplain"><i class="fas fa-minus"></i></button>
                </div>
            </div>`;

            $('#complainFieldsContainer').append(newField);

        }
    }




    $(document).on('click', '.removeField', function() {
        $(this).closest('.testField').remove();
    });
    $(document).on('click', '.removeTherapyField', function() {
        $(this).closest('.therapyField').remove();
    });
    $(document).on('click', '.removeComplain', function() {
        $(this).closest('.complainField').remove();
    });
    $(document).on('click', '#add_new_dosage', function(e) {
        e.preventDefault();
        $('div.add_dosage_view').load($(this).attr('href'), function() {
            $(this).modal('show');
        });
    });
    $(document).on('click', '#add_new_dosage_time', function(e) {
        e.preventDefault();
        $('div.add_dosage_view').load($(this).attr('href'), function() {
            $(this).modal('show');
        });
    });

    $(document).on('click', '#add_new_duration', function(e) {
        e.preventDefault();
        $('div.add_dosage_view').load($(this).attr('href'), function() {
            $(this).modal('show');
        });
    });


    $('#note_validation').on('input', function() {
        var maxLength = 110;
        var textLength = $(this).val().length;
        var remaining = maxLength - textLength;

        // Update character count display
        $('#charCount').text(remaining + " characters remaining");

        // Trim text if it exceeds maxLength
        if (textLength > maxLength) {
            $(this).val($(this).val().substring(0, maxLength));
            $('#charCount').text("0 characters remaining");
        }
    });

    $(document).on('submit', 'form#medicine_meal_store_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var submitButton = $(form).find('button[type="submit"]');

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
                    $('div.add_dosage_view').modal('hide');
                    toastr.success(result.msg);
                    var newOption = new Option(result.data.text, result.data.id, true, true);
                    $('.dosage_form').append(newOption).trigger('change');
                    if (typeof medicine_meal_table !== 'undefined') {
                        medicine_meal_table.ajax.reload();
                    }
                    var evt = new CustomEvent("mealAdded", {
                        detail: result.data
                    });
                    window.dispatchEvent(evt);

                } else {
                    toastr.error(result.msg);
                    submitButton.prop('disabled', false).text(
                        'Submit'
                    );
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = '';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += xhr.responseJSON.message;
                } else {
                    errorMessage += status;
                }

                toastr.error(errorMessage);
                submitButton.prop('disabled', false).text(
                    'Submit');
            },
            complete: function() {
                submitButton.prop('disabled', false).text(
                    'Submit'
                );
            }
        });
    });
    $(document).on('submit', 'form#durations_store_form', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var submitButton = $(form).find('button[type="submit"]');

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
                    $('div.add_dosage_view').modal('hide');
                    toastr.success(result.msg);

                    var newOption = new Option(result.data.value, result.data.id, true, true);
                    $('.medication_duration').append(newOption).trigger('change');

                    if (typeof durations_table !== 'undefined') {
                        durations_table.ajax.reload();
                    }
                    var evt = new CustomEvent("durationsAdded", {
                        detail: result.data
                    });
                    window.dispatchEvent(evt);

                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = '';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += xhr.responseJSON.message;
                } else {
                    errorMessage += status;
                }

                toastr.error(errorMessage);
                submitButton.prop('disabled', false).text(
                    'Submit');
            },
            complete: function() {
                submitButton.prop('disabled', false).text(
                    'Submit'
                );
            }
        });
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

    $(document).on('submit', 'form#disease_add_form_clinic', function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        var submitButton = $(form).find('button[type="submit"]');

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
                    $('div.add_dosage_view').modal('hide');
                    toastr.success('Chief complain added successfully.');
                    $('.complain_create_our_system').remove();
                    var newField = `<div class="complainField row mt-1">                         
                        <div class="col-md-10">
                            <input type='text' name='complain_name[]' value='${result.disease_data.text}' class='form-control custom-input hide' readonly required>
                        <input type='text' name='complain_comment[]' value='${result.disease_data.text}' class='form-control custom-input'>
                        </div>
                        <div class="col-md-2">
                            <input type="hidden" name="chief_complain_id[]" value="${result.disease_data.id}">
                            <button type="button" class="btn btn-danger btn-xs removeComplain"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>`;
                    $('#complainFieldsContainer').append(newField);
                    $('#search_complain_input').val('').focus();
                    var newOption = new Option(result.disease_data.text, result.disease_data.id, true, true);
                    $('#chief_complain_id').append(newOption).trigger('change');
                    if (typeof cheif_complain_table !== 'undefined') {
                        cheif_complain_table.ajax.reload();
                    }
                    var evt = new CustomEvent("complainAdded", {
                        detail: result.disease_data
                    });
                    window.dispatchEvent(evt);

                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = '';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += xhr.responseJSON.message;
                } else {
                    errorMessage += status;
                }

                toastr.error(errorMessage);
                submitButton.prop('disabled', false).text(
                    'Submit');
            },
            complete: function() {
                submitButton.prop('disabled', false).text(
                    'Submit'
                );
            }
        });
    });
    var otherPrescriptions = @json($otherPrescription); // Convert PHP array to JavaScript

    // Loop through options and add data-prescription-id
    $('#other_prescription_app_id option').each(function() {
        var appointmentId = $(this).val();
        if (otherPrescriptions[appointmentId]) {
            $(this).attr('data-prescription-id', otherPrescriptions[appointmentId].prescription_id);
        }
    });


    // Define the base template URL once
    var appointmentId = @json($appointment->id ?? '');
    var baseTemplateUrl =
        "{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'getTemplateData'], ['__APPOINTMENT_ID__', '__TEMPLATE_ID__']) }}";
    var tempId = '';
    $(document).on('change', '#other_prescription_app_id', function() {
        var appId = $(this).val();
        var presId = $(this).find(':selected').data('prescription-id');

        $('#view_prescription_btn').attr('data-href', '');
        $('#load_prescription').attr('href', '');

        if (!appId || !presId) {
            console.error("Invalid prescription selection.");
            return;
        }

        var urlView =
            "{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'show'], ['__ID__']) }}"
            .replace('__ID__', presId);
        var url =
            "{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'showInDoctor'], ['__ID__']) }}"
            .replace('__ID__', appId);

        console.log("Updated URLs:", urlView, url);

        $('#view_prescription_btn').attr('data-href', urlView);
        $('#load_prescription').attr('href', url);
    });

    // Fix button click event
    $('#view_prescription_btn').on('click', function() {
        let selectedAppointmentId = $('#other_prescription_app_id').val();

        if (!selectedAppointmentId) {
            toastr.error("Please select a visit first.");
            return;
        }

        let url = $(this).attr('data-href').replace('APPOINTMENT_ID', selectedAppointmentId);

        $.ajax({
            url: url,
            type: 'GET',
            success: function(response) {
                $('.view_modal_visit_form').html(response).modal('show');
            },
            error: function() {
                toastr.error('Error loading prescription details.');
            }
        });
    });


    // Load Prescription button click event
    $(document).on('click', '#load_prescription', function(event) {
        event.preventDefault();
        var selectedValue = $('#other_prescription_app_id').val();

        if (!selectedValue) {
            toastr.error('Please select a valid visit.');
            return false;
        }

        var appointmentId = $(this).data('appointment-id');
        var prescriptionId = $('#other_prescription_app_id option:selected').data('prescription-id');

        var url = $(this).attr('data-href').replace('APPOINTMENT_ID', appointmentId).replace('PRESCRIPTION_ID', prescriptionId);
        swal({
            title: "Do you want to load the prescription?",
            text: "Loading a previous prescription will leave the current prescription empty.",
            icon: "warning",
            buttons: ["Cancel", "Yes, Load it"],
            dangerMode: true,
        }).then((willLoad) => {
            if (willLoad) {
                window.location.href = url;
            }
        });
    });

    // Template ID change event to update the load template button URL
    $('#template_id').on('change', function() {
        var prescriptionId = $(this).val();
        if (prescriptionId) {
            var loadUrl = baseTemplateUrl.replace('__APPOINTMENT_ID__', appointmentId)
                .replace('__TEMPLATE_ID__', prescriptionId);

            // Collect input values
            var data = {
                current_height_feet: $('input[name="current_height_feet"]').val() || null,
                current_height_inches: $('input[name="current_height_inches"]').val() || null,
                height: $('input[name="current_height"]').val() || null,
                weight: $('input[name="current_weight"]').val() || null,
                pulse: $('input[name="pulse_rate"]').val() || null,
                systolic_pressure: $('input[name="systolic_pressure"]').val() || null,
                diastolic_pressure: $('input[name="diastolic_pressure"]').val() || null,
                bp: $('input[name="blood_pressure"]').val() || null,
                respiratory: $('input[name="respiratory"]').val() || null,
                body_temp: $('input[name="body_temp"]').val() || null,
                bmi: $('input[name="bmi"]').val() || null,
                body_fat_percent: $('input[name="body_fat_percent"]').val() || null,
                fat_mass_percent: $('input[name="fat_mass_percent"]').val() || null,
                lean_mass_percent: $('input[name="lean_mass_percent"]').val() || null
            };

            // Remove null values from the object
            Object.keys(data).forEach(key => (data[key] === null) && delete data[key]);

            // Convert to query parameters
            var queryParams = $.param(data);

            // Append query parameters to the URL
            if (queryParams) {
                loadUrl += '?' + queryParams;
            }

            // Assign URL to the link
            $('#load_template').attr('href', loadUrl);
        }
    });


    // View Template button click event
    $('#view_template_btn').on('click', function() {
        var prescriptionId = $('#template_id').val();
        if (!prescriptionId) {
            toastr.error('Please select a valid template.');
            return false;
        }

        // Generate the view URL for the template
        var urlView =
            "{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'show'], ['__ID__']) }}"
            .replace('__ID__', prescriptionId) + '?is_template=1';
        $.ajax({
            url: urlView,
            type: 'GET',
            success: function(response) {
                $('.view_template_modal').html(response).modal('show');
            },
            error: function() {
                toastr.error('Error loading prescription details.');
            }
        });
        $('#view_template_btn').attr('data-href', urlView);
    });

    // Load Template button click event
    $(document).on('click', '#load_template', function(event) {
        event.preventDefault();
        var selectedValue = $('#template_id').val();

        if (!selectedValue) {
            toastr.error('Please select a valid template.');
            return false;
        }

        var url = $(this).attr('href');
console.log("Urrr temp: "+url);

        swal({
            title: "Are you sure?",
            text: "Loading will erase all current field data and replace it with the template's default values. Continue?",
            icon: "warning",
            buttons: ["Cancel", "Yes, Load it"],
            dangerMode: true,
        }).then((willLoad) => {
            if (willLoad) {
                window.location.href = url;
            }
        });
    });

    $('#template_add_form').submit(function(event) {
            event.preventDefault();
            return false;
        })
        .validate({
            rules: {
                'template_name_hidden': {
                    required: true
                }
            },
            messages: {
                'template_name_hidden': {
                    required: 'This field is required',
                },
            },
            submitHandler: function(form) {
                event.preventDefault();
                submitTemplateForm(form);
            },
        });

    function submitTemplateForm(form) {
        var data = $(form).serialize();
        $.ajax({
            method: 'POST',
            url: $(form).attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success == true) {
                    $('div.template_modal').modal('hide');
                    toastr.success(result.msg);
                    $('.saveAsTempBtn').click();
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr, status, error) {
                var errorMessage = '';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage += xhr.responseJSON.message;
                } else {
                    errorMessage += status;
                }
                toastr.error(errorMessage);
            }
        });
    }

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
                        </button>
                    `);
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
        console.log('Clearing placeholder...');
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

    $(document).on('click', '.add_new_template', function() {
        $('.template_modal').modal('show');
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
                    <input type='text' name='advice_name[]' value='${advice.text}' class='form-control custom-input' readonly required>
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

    $(document).ready(function() {
        let rowCount = $('#investigationRows .row').length;

        // Function to initialize date picker
        function initializeDatePicker() {
            $('.date-format').datepicker({
                autoclose: true,
                format: datepicker_date_format
            });
        }

        // Initialize date picker for existing rows
        initializeDatePicker();

        // Add new row
        $('#addRowBtn').click(function() {
            let newRow = `
            <div class="row mb-2">
                <div style="padding-right: 5px; padding-left: 5px" class="col-md-3">
                    <input type="text" name="investigation_history[${rowCount}][date]" class="form-control custom-input date-format" readonly />
                </div>
                <div style="padding-right: 5px; padding-left: 5px" class="col-md-4">
                    <input type="text" name="investigation_history[${rowCount}][test_name]" class="form-control custom-input" placeholder="@lang('clinic::lang.test_name')" />
                </div>
                <div style="padding-right: 5px; padding-left: 5px" class="col-md-4">
                    <input type="text" name="investigation_history[${rowCount}][result_value]" class="form-control custom-input" placeholder="@lang('clinic::lang.result_value')" />
                </div>
                <div style="padding-right: 0; padding-left: 0" class="col-md-1 d-flex align-items-end">
                    <button type="button" class="btn btn-danger removeRow btn-xs"><i class="fas fa-minus"></i></button>
                </div>
            </div>`;
            $('#investigationRows').append(newRow);
            rowCount++;

            // Reinitialize date picker for new row
            initializeDatePicker();
        });

        // Remove row
        $(document).on('click', '.removeRow', function() {
            $(this).closest('.row').remove();
            rowCount--;
        });

        $('#ipd_admission_checkbox').on('ifChanged', function () {
            if ($(this).is(':checked')) {
                $('#ipd_days_input').prop('disabled', false).focus();
            } else {
                $('#ipd_days_input').prop('disabled', true).val('');
            }
        });
    });
</script>
