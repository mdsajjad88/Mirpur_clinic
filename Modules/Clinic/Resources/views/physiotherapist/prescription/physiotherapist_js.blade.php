<script type="text/javascript">
    $(document).ready(function() {
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
                string += '<br>Brand: ' + item.brand.name + '\t QTY: ' + (item.qty_available > 0 ? parseInt(
                    item
                    .qty_available) : 'Out of Stock') + '</div>';
            } else {
                string += '<br>Name: ' + item.name + '\t QTY: ' + (item.qty_available > 0 ? parseInt(item
                    .qty_available) : 'Out of Stock') + '</div>';
            }
            return $('<li>').append(string).appendTo(ul);
        };

        function addProductToPrescription(product) {
            var isProductAlreadyAdded = false;
            var index = 0;

            var fullProductName = (product.short_name ? product.short_name + '. ' : '') + product.name + ' ' + (
                product.size ?
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

        function validateForm() {
            $('#create_therapist_prescriptions').valid();
        }
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
        $(document).on('click', '.removeField', function() {
        $(this).closest('.testField').remove();
    });

        function clearPlaceholder() {
            $('#search_advice_input').val('').focus();
            $('#home_advice_search_box').val('').focus();
            $('#on_examination_search_box').val('').focus();
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
        $(document).on('click', '.removeComplain', function() {
        $(this).closest('.complainField').remove();
    });
        // advice maintain process 
        function initAdviceAutocomplete(inputSelector, containerSelector, type) {
            var advice_name = ''; // local to this function

            $(inputSelector).autocomplete({
                source: function(request, response) {
                    $.getJSON('/get/doctor/advice/', {
                        term: request.term,
                        type: type // pass dynamic type
                    }, function(data) {
                        if (data.results.length === 0) {
                            $('.ui-menu-item').remove();
                            advice_name = $(inputSelector).val();
                            $('.advice_create_our_system').remove();
                            $(inputSelector).after(`
                                <button type="button" class="btn btn-link advice_create_our_system" data-type="${type}" data-name="${advice_name}" data-container="${containerSelector}">
                                    <i class="fa fa-plus-circle fa-lg"></i> Add "${advice_name}" as New ${type}
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
                    addAdviceToContainer(ui.item, containerSelector, type);
                    clearPlaceholder();
                    setTimeout(function() {
                        $(inputSelector).val('').focus();
                        $('.advice_create_our_system').remove();
                    }, 0);
                }
            }).autocomplete('instance')._renderItem = function(ul, item) {
                return $('<li>').append(`<div>${item.text}</div>`).appendTo(ul);
            };
        }



        function addAdviceToContainer(advice, containerSelector, type) {
            var isAdviceAdded = false;  

            $(containerSelector + ' .adviceField').each(function() {
                // dynamically get the correct input
                var existingInput = $(this).find(`input[name='${type}[]']`);
                if (existingInput.length && existingInput.val().trim() === advice.text) {
                    isAdviceAdded = true;
                    return false; // break the loop
                }
            });

            if (isAdviceAdded) {
                swal({
                    icon: 'warning',
                    title: 'already added!',
                    text: 'The selected option is already in the list.',
                });
            } else {
                var newField = `<div class="adviceField row mt-1">
                    <div class="col-md-11">
                        <input type='text' name='${type}[]' value='${advice.text}' class='form-control custom-input' required>
                    </div>
                    <div class="col-md-1" style="margin:0px; padding:0px">
                        <input type="hidden" name="${type}_id[]" value="${advice.id}">
                        <button type="button" class="btn btn-danger btn-xs removeAdvice"><i class="fas fa-minus"></i></button>
                    </div>
                </div>`;

                $(containerSelector).append(newField);
                clearPlaceholder();
            }
        }

        $(document).on('click', '.advice_create_our_system', function() {
            var advice_name = $(this).data('name');
            var type = $(this).data('type');

            // Choose which “add” button to trigger
            var button;

            if (type === 'home_advice') {
                button = $('#add_new_home_advice');
            } else if (type === 'treatment_plan') {
                button = $('#add_new_advice');
            } else if (type === 'on_examination') {
                button = $('#add_new_on_examination'); // your new button id
            } else {
                console.warn('Unknown advice type:', type);
                return; // stop if no match
            }

            // Store the advice name in data attribute
            button.data('name', advice_name);

            // Listen for the modal to finish loading before assigning value
            var container = $(button.data('container')); // your modal container

            container.off('shown.bs.modal').on('shown.bs.modal', function() {
                // Set value after modal content is in DOM
                container.find('.advice_value').val(advice_name);
            });

            // Trigger modal open
            button.trigger('click');

            $(this).remove();
        });


        initAdviceAutocomplete('#search_advice_input', '#adviceFieldsContainer', 'treatment_plan');

        // Home advice
        initAdviceAutocomplete('#home_advice_search_box', '#homeAdviceFieldsContainer', 'home_advice');
        initAdviceAutocomplete('#on_examination_search_box', '#onExaminationFieldsContainer', 'on_examination');

        $(document).on('click', '.removeAdvice', function() {
            $(this).closest('.adviceField').remove();
        })

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

                        // Get the advice data returned from backend
                        var advice = response
                            .data; // should contain at least { id, value, type }

                        // Determine container based on type
                        var containerSelector = '';
                        switch (advice.type) {
                            case 'treatment_plan':
                                containerSelector = '#adviceFieldsContainer';
                                break;
                            case 'home_advice':
                                containerSelector = '#homeAdviceFieldsContainer';
                                break;
                            case 'on_examination':
                                containerSelector = '#onExaminationFieldsContainer';
                                break;
                            default:
                                containerSelector = '#adviceFieldsContainer'; // fallback
                        }

                        // Append the new advice
                        var newField = `<div class="adviceField row mt-1">
                    <div class="col-md-11">
                        <input type='text' name='${advice.type}[]' value='${advice.value}' class='form-control custom-input' required>
                    </div>
                    <div class="col-md-1" style="margin:0px; padding:0px">
                        <input type="hidden" name="${advice.type}_id[]" value="${advice.id}">
                        <button type="button" class="btn btn-danger btn-xs removeAdvice"><i class="fas fa-minus"></i></button>
                    </div>
                </div>`;

                        $(containerSelector).append(newField);
                        $('.advice_create_our_system').remove();
                        clearPlaceholder();
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
                    submitButton.prop('disabled', true).text(
                        'Submit');
                },
                success: function(result) {
                    if (result.success == true) {
                        $('div.view_modal_duration_form').modal('hide');
                        toastr.success(result.msg);

                        var newOption = new Option(result.data.value, result.data.id, false,
                            false);
                        $('.medication_duration').append(newOption).trigger('change');
                    } else {
                        toastr.error(result.msg);
                        submitButton.prop('disabled', false).text(
                            'Submit');
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

        $(document).on('submit', '#create_therapist_prescriptions', function(e) {
            e.preventDefault();

            var form = $(this);
            var formData = form.serialize();
            var formAction = form.attr('action');

            form.find('button[type=submit]').prop('disabled', true);

            $.ajax({
                url: formAction,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                        form.find('button[type=submit]').prop('disabled', false);
                        window.location.reload();
                    } else if (response.success == false) {
                        toastr.error(response.message);
                        form.find('button[type=submit]').prop('disabled', false);
                    }
                },
            });
        })
    })
</script>
