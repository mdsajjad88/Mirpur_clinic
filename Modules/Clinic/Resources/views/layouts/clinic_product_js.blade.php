<script type="text/javascript">
    let productEntryCount = 0;
    if ($('#search_product_clinic').length) {
        $('#search_product_clinic').autocomplete({
            delay: 1000,
            source: function(request, response) {
                var price_group = '';
                var search_fields = [];
                $('.search_fields:checked').each(function(i) {
                    search_fields[i] = $(this).val();
                });

                if ($('#price_group').length > 0) {
                    price_group = $('#price_group').val();
                }
                var sub_type = $('#sub_type').val();
                $.getJSON(
                    '/get/search/product/', {
                        sub_type: sub_type,
                        price_group: price_group,
                        term: request.term,
                        not_for_selling: 0,
                        search_fields: search_fields,
                    },
                    response
                );
            },
            minLength: 2,
            response: function(event, ui) {
                var status = $('#status').val();
                var is_demand_status = status === 'demand';

                if (ui.content.length == 1) {
                    ui.item = ui.content[0];

                    if (is_demand_status && ui.item.qty_available > 0) {
                        ui.item = null;
                    }

                    if (ui.item) {
                        $(this)
                            .data('ui-autocomplete')
                            ._trigger('select', 'autocompleteselect', ui);
                        $(this).autocomplete('close');
                    } else {
                        toastr.error(LANG.no_item_found);
                        $('input#search_product_clinic').select();
                    }
                } else if (ui.content.length == 0) {
                    toastr.error(LANG.no_item_found);
                }
            },
            select: function(event, ui) {
                var searched_term = $(this).val();
                var status = $('#status').val();
                var sub_type = $('#sub_type').val();
                var is_demand_status = status === 'demand';
                var customer_id = $('select#customer_id_clinic').val();

                if ((!customer_id || customer_id === '') && (sub_type === 'therapy' || sub_type ===
                        'consultation')) {
                    swal({
                        title: 'Patient not found',
                        text: "First select a Patient before searching for " + sub_type,
                        icon: 'warning',
                        dangerMode: true,
                    });
                    return false;
                }

                if (sub_type === 'therapy' && productEntryCount > 0) {
                    // Show SweetAlert for confirmation to clear existing products
                    swal({
                        title: 'Clear existing therapy?',
                        text: "There is already a therapy entered. Do you want to clear it?",
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then(willDelete => {
                        if (willDelete) {
                            // Clear existing products and allow new entry
                            clearProductEntries();
                            addProduct(ui); // Add the new product
                            $('#search_product_clinic').val('');
                        } else {
                            $(this).val(null); // Reset the input field
                        }
                    });
                } else if (status === '' || status === null) {
                    swal({
                        title: "Status not selected!",
                        text: "Please select a status before adding products.",
                        icon: 'warning',
                    });
                    return false;
                } else {
                    addProduct(ui); // No existing product, add the product directly
                    $('#search_product_clinic').val('');
                }
            }
        }).autocomplete('instance')._renderItem = function(ul, item) {
            var status = $('#status').val();
            var is_demand_status = status === 'demand';

            if (is_demand_status && item.qty_available > 0) {
                // Render out-of-stock items differently if status is 'demand'
                var string = '<li class="ui-state-disabled"><span>' + item.name;
                var qty_available = __currency_trans_from_en(item.qty_available, false, false, __currency_precision,
                    true);
                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }
                var selling_price = parseFloat(item.selling_price).toFixed(2);
                if (item.variation_group_price) {
                    selling_price = parseFloat(item.variation_group_price).toFixed(2);
                }
                if (is_demand_status) {
                    string += ' (' + item.sub_sku + ')' + '<br> &nbsp; ৳' + selling_price + ' - Current Stock: ' +
                        qty_available + item.unit + '</span></li><hr/>';
                } else {
                    string += ' (' + item.sub_sku + ')' + '<br> &nbsp; ৳' + selling_price +
                        ' (Out of stock)</span></li><hr/>';
                }
                // if (item.total_quantity > 0) {
                //     string += '</span></li><b> Store:' + parseFloat(item.total_quantity - item.qty_available).toFixed(2) + '</b><hr/>';
                // } 
                // else {
                //     string += '| Store:' + parseFloat(item.total_quantity - item.qty_available).toFixed(2) + '</span></li><hr/>';
                // }
                return $(string).appendTo(ul);
            } else {
                var location_id = $('input#location_id').val();
                var string = '<div>' + item.name;

                if (item.type == 'variable') {
                    string += '-' + item.variation;
                }

                var selling_price = parseFloat(item.selling_price).toFixed(2);
                if (item.variation_group_price) {
                    selling_price = parseFloat(item.variation_group_price).toFixed(2);
                }
                var brandName = item.brand_id ? item.brand.name : 'No brand';

                string += ' (' + item.sub_sku + ')' + ' | Brand: ' + brandName + '<br> ৳' + selling_price;

                if (item.enable_stock == 1) {
                    var qty_available = __currency_trans_from_en(item.qty_available, false, false,
                        __currency_precision, true);
                    if (item.total_quantity <= 0) {
                        string += ' (Out of stock)';
                    } else {
                        string += ' - Current Stock: ' + qty_available + item.unit;
                    }

                }
                // Determine the color based on the expiration date
                if (item.nearest_exp_date) {
                    var exp_date = new Date(item.nearest_exp_date);
                    var today = new Date();
                    var days_to_expiry = (exp_date - today) / (1000 * 60 * 60 * 24);
                    var expiringSoon = $('#expiring_soon').val();
                    var expiringLater = $('#expiring_later').val();

                    if (days_to_expiry <= parseInt(expiringSoon) && item.total_quantity > 0) {
                        if (item.total_quantity <= 0 && !is_demand_status) {
                            string = '<div class="ui-state-disabled" style="border-left: 5px solid red;">' + string;
                        } else {
                            string = '<div style="border-left: 5px solid red;">' + string;
                        }
                    } else if (days_to_expiry <= parseInt(expiringLater) && item.total_quantity > 0) {
                        if (item.total_quantity <= 0 && !is_demand_status) {
                            string = '<div class="ui-state-disabled" style="border-left: 5px solid orange;">' +
                                string;
                        } else {
                            string = '<div style="border-left: 5px solid orange;">' + string;
                        }
                    } else {
                        if (item.total_quantity <= 0 && !is_demand_status) {
                            string = '<div class="ui-state-disabled">' + string;
                        }
                    }
                } else if (item.enable_stock == 0) {
                    string = '<div>' + string;
                } else {
                    if (item.total_quantity <= 0 && !is_demand_status) {
                        string = '<div class="ui-state-disabled">' + string;
                    }
                }

                return $('<li>').append(string).appendTo(ul);
            }
        };
    }

    function addProduct(ui) {
        var searched_term = $('#search_product_clinic').val();
        var purchase_line_id = ui.item.purchase_line_id && searched_term == ui.item.lot_number ?
            ui.item.purchase_line_id :
            null;
        pos_product_row(ui.item.variation_id, purchase_line_id);
        productEntryCount++; // Increment the product entry count
        serviceCharge();
    }

    function serviceCharge() {
        var sub_type = $('#sub_type').val();
        var allow_charge = $('#clinic_service_charge').val();
        if (sub_type == 'test' && allow_charge == 1) {
            var exp = 20 + (25 * productEntryCount);
            $("#additional_expense_value_1").val(exp);
            $("#service_charge_text").text(exp);
        } else {
            $("#additional_expense_key_1").val(null);
        }
        pos_total_row();
    }
    //Remove row on click on remove row
    $('table#pos_table tbody').on('click', 'i.pos_remove_row', function() {
        $(this).parents('tr').remove();
        serviceCharge();
        pos_total_row();
        // Check if there are no rows left, then set product_row_count to 0
        if ($('table#pos_table tbody tr').length === 0) {
            $('input#product_row_count').val(0);
        }
    });

    function clearProductEntries() {
        // Add logic here to clear existing product rows
        $('#pos_table tbody').empty(); // Clear the product table rows
        productEntryCount = 0; // Reset the product entry count
        pos_total_row(); // Update the totals after clearing
    }



    function pos_product_row(
        variation_id = null,
        purchase_line_id = null,
        weighing_scale_barcode = null,
        quantity = 1
    ) {
        // First check if product already exists in the table
        var existingRow = null;
        var existingQty = 0;
        var enable_sr_no = false;
        var modifiers_exist = false;

        $('#pos_table tbody tr').each(function() {
            var row_v_id = $(this).find('.row_variation_id').val();
            if (row_v_id == variation_id) {
                existingRow = $(this);
                existingQty = __read_number($(this).find('.pos_quantity'));
                enable_sr_no = $(this).find('.enable_sr_no').val() === '1';
                modifiers_exist = $(this).find('input.modifiers_exist').length > 0;
                return false; // break the loop
            }
        });

        if (existingRow && !enable_sr_no && !modifiers_exist) {
            // Product exists - show confirmation to increase quantity
            swal({
                title: 'Item already added!',
                text: 'This item is already in the list with quantity ' + existingQty +
                    '. Do you want to increase the quantity?',
                icon: 'warning',
                buttons: {
                    cancel: "No",
                    confirm: {
                        text: "Yes",
                        value: true
                    }
                },
                dangerMode: true
            }).then((increase) => {
                if (increase) {
                    // Increase quantity by 1 (or by the passed quantity parameter)
                    var newQty = existingQty + quantity;
                    var qtyElement = existingRow.find('.pos_quantity');
                    __write_number(qtyElement, newQty);
                    qtyElement.change();

                    round_row_to_iraqi_dinnar(existingRow);
                }
                $('input#pos_table').focus().select();
            });
            return false;
        }

        // Proceed with adding new product
        var product_row = $('input#product_row_count').val();
        var location_id = $('input#location_id').val();
        var customer_id = $('select#customer_id_clinic').val();
        var status = $('#status').val();
        var is_direct_sell = false;

        if ($('input[name="is_direct_sale"]').length > 0 && $('input[name="is_direct_sale"]').val() == 1) {
            is_direct_sell = true;
        }

        var disable_qty_alert = false;
        if ($('#disable_qty_alert').length) {
            disable_qty_alert = true;
        }

        var is_sales_order = $('#sale_type').length && $('#sale_type').val() == 'sales_order' ? true : false;

        var price_group = '';
        if ($('#price_group').length > 0) {
            price_group = parseInt($('#price_group').val());
        }

        if ($('#default_price_group').length > 0 && price_group === '') {
            price_group = $('#default_price_group').val();
        }

        if ($('#types_of_service_price_group').length > 0 && $('#types_of_service_price_group').val()) {
            price_group = $('#types_of_service_price_group').val();
        }

        var is_draft = false;
        if ($('input#status') && ($('input#status').val() == 'quotation' || $('input#status').val() == 'draft')) {
            is_draft = true;
        }

        $.ajax({
            method: 'GET',
            url: '/clinic/sells/pos/get_product_row/' + variation_id + '/' + location_id + '/' + status,
            async: false,
            data: {
                product_row: product_row,
                customer_id: customer_id,
                is_direct_sell: true,
                price_group: price_group,
                purchase_line_id: purchase_line_id,
                weighing_scale_barcode: weighing_scale_barcode,
                quantity: quantity,
                is_sales_order: is_sales_order,
                disable_qty_alert: disable_qty_alert,
                is_draft: is_draft,
            },
            dataType: 'json',
            success: function(result) {
                if (result.success) {
                    $('table#pos_table tbody').append(result.html_content);
                    $('input#product_row_count').val(parseInt(product_row) + 1);
                    productEntryCount++;

                    var this_row = $('table#pos_table tbody').find('tr').last();
                    pos_each_row(this_row);

                    var line_total = __read_number(this_row.find('input.pos_line_total'));
                    this_row.find('span.pos_line_total_text').text(line_total);

                    pos_total_row();

                    if (__getUnitMultiplier(this_row) > 1) {
                        this_row.find('select.sub_unit').trigger('change');
                    }

                    if (result.enable_sr_no == '1') {
                        var new_row = $('table#pos_table tbody').find('tr').last();
                        new_row.find('.row_edit_product_price_model').modal('show');
                    }

                    round_row_to_iraqi_dinnar(this_row);
                    __currency_convert_recursively(this_row);

                    $('input#pos_table').focus().select();

                    if (result.html_modifier) {
                        $('table#pos_table tbody')
                            .find('tr')
                            .last()
                            .find('td:first')
                            .append(result.html_modifier);
                    }

                    $('.pos_product_div').animate({
                        scrollTop: $('.pos_product_div').prop('scrollHeight')
                    }, 1000);
                } else {
                    toastr.error(result.msg);
                    $('input#pos_table').focus().select();
                }
            },
        });
    }


    function round_row_to_iraqi_dinnar(row) {
        if (iraqi_selling_price_adjustment) {
            var element = row.find('input.pos_unit_price_inc_tax');
            var unit_price = round_to_iraqi_dinnar(__read_number(element));
            __write_number(element, unit_price);
            element.change();
        }
    }
    $('#comment_store_form').on('submit', function(e) {
        e.preventDefault();

        var data = $(this).serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success) {
                    toastr.success(result.msg);
                    $('.btn-close').click(); // Close modal
                    $('#comment_store_form')[0].reset(); // Reset form fields

                    // Append the new comment to a dropdown or list
                    var newComment = $('<option>', {
                        value: result.comment.id,
                        text: result.comment.name,
                        selected: true
                    });
                    $('.new-comment').append(newComment);
                } else {
                    toastr.error(result.msg);
                }
            },
            error: function(xhr) {
                var message = xhr.responseJSON?.message ||
                    'Something went wrong. Please try again.';
                toastr.error(message);
            }
        });
    });
    $(document).ready(function() {
        $('#disease').select2({
            placeholder: 'Select Health Concerns',
            allowClear: true,
        });
        $('form#patient_add_form').submit(function(event) {
                event.preventDefault();
                return false;
            })
            .validate({
                rules: {
                    contact_id: {
                        remote: {
                            url: '/contacts/check-contacts-id',
                            type: 'post',
                            data: {
                                contact_id: function() {
                                    return $('#contact_id').val();
                                },
                                hidden_id: function() {
                                    if ($('#hidden_id').length) {
                                        return $('#hidden_id').val();
                                    } else {
                                        return '';
                                    }
                                },
                            },
                        },
                    },
                },

                messages: {
                    contact_id: {
                        remote: LANG.contact_id_already_exists,
                    },
                },
                submitHandler: function(form) {
                    event.preventDefault();
                    $.ajax({
                        method: 'POST',
                        url: base_path + '/check-mobile',
                        dataType: 'json',
                        data: {
                            contact_id: function() {
                                return $('#hidden_id').val();
                            },
                            mobile_number: function() {
                                return $('#mobile').val();
                            },
                            first_name: function() {
                                return $('#first_name').val();
                            },
                        },

                        beforeSend: function(xhr) {
                            __disable_submit_button($(form).find('button[type="submit"]'));
                        },
                        success: function(result) {
                            if (result.is_mobile_exists === true) {
                                toastr.error(result
                                    .msg);

                            } else {
                                submitContactForm(form);
                            }
                        },

                        error: function(xhr, status, error) {
                            console.error('AJAX Request Error:', error);
                            console.error('Response:', xhr
                                .responseText); // Logs full response in case of error

                            var errorMessage =
                                'An unexpected error occurred. Please try again later.';
                            if (xhr.responseJSON && xhr.responseJSON.msg) {
                                errorMessage = xhr.responseJSON
                                    .msg; // If the server sends a custom error message
                            }
                            toastr.error(errorMessage);
                        }
                    });
                },
            });

        $('#patient_add_form').trigger('contactFormvalidationAdded');

        function submitContactForm(form) {
            var data = $(form).serialize();
            $.ajax({
                method: 'POST',
                url: $(form).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('#patient_add_form')[0].reset();
                        $('div.patient_add_modal').modal('hide');
                        var newPatient = $('<option>', {
                            value: result.data.contact.id,
                            text: result.data.patient.first_name + ' ' + (result.data
                                .patient.last_name || ''),
                            selected: true,
                        });
                        $('#customer_id_clinic').append(newPatient);
                        var newPatientUserId = $('<option>', {
                            value: result.data.patient.id,
                            text: result.data.patient.first_name + ' ' + (result.data
                                .patient.last_name || ''),
                            selected: true,
                        });

                        $('.patient_profile_id').append(newPatientUserId);
                        $('.mobile_no_is').val(result.data.contact.mobile);
                        toastr.success(result.msg);
                        $('#patients_table').DataTable().ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'An unexpected error occurred. Please try again later.';
                    if (xhr.responseJSON && xhr.responseJSON.msg) {
                        errorMessage = xhr.responseJSON
                            .msg; // If the server sends a custom error message
                    }
                    toastr.error(errorMessage);
                }
            });
        }
    });
    $(document).ready(function() {

        $('form#doctor_add_form').submit(function(event) {
                event.preventDefault();
                return false;
            })
            .validate({
                rules: {
                    email: {
                        remote: {
                            url: '{{ route('doctors.checkEmailId') }}',
                            type: 'post',
                            data: {
                                email: function() {
                                    return $('#email').val();
                                },
                            },
                        },
                    },
                    mobile: {
                        required: true,
                    },

                },
                messages: {
                    email: {
                        remote: LANG.email_id_already_exists,
                    },
                },
                submitHandler: function(form) {
                    event.preventDefault();
                    $.ajax({
                        method: 'POST',
                        url: '{{ route('doctors.checkEmailId') }}',
                        dataType: 'json',
                        data: {
                            email: function() {
                                return $('#email').val();
                            },
                        },
                        beforeSend: function(xhr) {
                            __disable_submit_button($(form).find(
                                'button[type="submit"]'));
                        },
                        success: function(result) {
                            if (result.is_email_exists == true) {
                                swal({
                                    title: LANG.sure,
                                    text: result.msg,
                                    icon: 'warning',
                                    buttons: true,
                                    dangerMode: true
                                }).then(willContinue => {
                                    if (willContinue) {
                                        submitDoctorForm(form);
                                    } else {
                                        $('#mobile').select();
                                    }
                                });

                            } else {
                                submitDoctorForm(form);
                            }
                        },
                    });
                },
            });

        $('#doctor_add_form').trigger('contactFormvalidationAdded');

        function submitDoctorForm(form) {
            var data = $(form).serialize();

            $.ajax({
                method: 'POST',
                url: $(form).attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success == true) {
                        $('div.doctor_modal').modal('hide');
                        var newRef = $('<option>', {
                            value: result.data.id,
                            text: result.data.first_name,
                            selected: true
                        });

                        $('#reference_id').append(newRef);
                        toastr.success(result.msg);
                        $(form).trigger('reset');
                        $('#doctors_table').DataTable().ajax.reload();

                    } else {
                        toastr.error(result.msg);
                    }
                },
            });
        }
        $('select#customer_id_clinic').select2({
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
                let additionalInfo = '';
                if (data.type === 'lead') {
                    additionalInfo = `<span class="label label-warning">${LANG.lead}</span>`;
                }
                template += data.text + '<br>' + LANG.mobile + ': ' + data.mobile + ' ' +
                    additionalInfo;

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

                // Build the edit URL dynamically
                const editUrl = `/patients/${data.id}/edit`;
                const onlyNameEditUrl = `/patient/only-name/edit/${data.id}`;

                // Update the "Edit" button dynamically
                const editButton = $('.edit_patient_button');
                const nameEditButton = $('.edit_customer_button_call_log');
                editButton.attr('data-id', data.id); // Update the data-id attribute
                editButton.attr('href', editUrl); // Update the href attribute

                nameEditButton.attr('data-id', data.id);
                nameEditButton.attr('href', onlyNameEditUrl);

                if (data.type === 'lead') {
                    $('.show_patient_profile').hide();
                    $('.life_stage_hide').removeClass('hide');
                    $('#life_stage').val(data.crm_life_stage).trigger('change');
                    $('#life_stage').prop('required', true);
                } else {
                    $('.show_patient_profile').show();
                    $('.life_stage_hide').addClass('hide');
                    $('#life_stage').prop('required', false);

                }


                let label = data.text;
                if (data.type === 'lead') {
                    label += ' <span class="label label-warning" style="margin-left:5px;">' + LANG
                        .lead + '</span>';
                }
                return label;
            },
            minimumInputLength: 1,
            language: {
                inputTooShort: function(args) {
                    return LANG.please_enter + args.minimum + LANG.or_more_characters;
                },
                noResults: function() {
                    const name = $('#customer_id_clinic').data('select2').dropdown.$search.val();
                    if (/^\d+$/.test(name)) {
                        $('.mobile').val(name);
                    }
                    return (
                        '<button type="button" data-name="' +
                        name +
                        '" class="btn btn-link add_new_patients">' +
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

        $(document).on('click', '.edit_patient_button', function(e) {
            e.preventDefault();
            var customer_id = $('#customer_id_clinic').val();
            if (!customer_id) {
                toastr.error('Please select a patient first.');
                return false;
            }
            $('div.edit_contact_modal').load($(this).attr('href'), function() {
                $('.select2-dropdown').hide();
                $(this).modal('show');
            });
        });
        $(document).on('click', '.edit_customer_button_call_log', function(e) {
            e.preventDefault();
            var customer_id = $('#customer_id_clinic').val();
            if (!customer_id) {
                toastr.error('Please select a patient first.');
                return false;
            }
            $('div.edit_customer_button_call_log_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });

    });
    $(document).on('submit', 'form#disease_add_form_clinic', function(e) {
        e.preventDefault();
        var form = $(this);
        var submitButton = form.find('button[type="submit"]');
        var data = form.serialize();

        $.ajax({
            method: 'POST',
            url: $(this).attr('action'),
            dataType: 'json',
            data: data,
            beforeSend: function(xhr) {
                __disable_submit_button(
                    submitButton);
            },
            success: function(result) {
                if (result.success == true) {
                    $('div.disease_modal').modal('hide');
                    $('div.problem_add_modal').modal('hide');
                    form.trigger('reset');
                    toastr.success(result.msg);
                    var newProblem = $('<option>', {
                        value: result.data.id,
                        text: result.data.name,
                        selected: true
                    });
                    $('.multipleProblem').append(newProblem);
                    $('#clinic_disease_table').DataTable().ajax.reload();
                    var evt = new CustomEvent("diseaseAdded", {
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


    function initDoctorStatusToggle({
        doctorId,
        isAvailable,
        breakStart,
        expectDuration
    }) {
        // 1. Show the indicator section
        $('#doctor_status_indicator').removeClass('hide');

        // 2. Set initial text and toggle state
        $('#doctor_status_text').text(isAvailable == 1 ? 'Available' : 'Unavailable');
        $('#doctor_status_toggle').prop('checked', isAvailable == 1);
        if (isAvailable == 0 && breakStart && expectDuration > 0) {
            let breakStartTime = new Date(breakStart);
            let expireTime = new Date(breakStartTime.getTime() + expectDuration * 60000);
            let now = new Date();

            if (now >= expireTime) {
                // already time over → make available instantly
                autoMakeAvailable(doctorId);
            } else {
                // wait until expire → then make available
                let msLeft = expireTime - now;
                setTimeout(function() {
                    autoMakeAvailable(doctorId);
                }, msLeft);
            }
        }
        // 3. On toggle change
        $('#doctor_status_toggle').on('change', function() {
            let isActive = $(this).is(':checked');

            // যদি Available থেকে Unavailable হচ্ছে → modal দেখাও
            if (!isActive) {
                // toggle টাকে আবার আগের state এ ফিরিয়ে দাও যাতে modal এর আগে change না হয়
                $('#doctor_status_toggle').prop('checked', true);

                // modal open করো
                $('#doctorUnavailableModal').modal('show');

                // যখন modal form submit হবে তখন request পাঠাও
                $('#doctorUnavailableForm').off('submit').on('submit', function(e) {
                    e.preventDefault();
                    let $submitBtn = $('#doctorUnavailableSubmit');
                    $submitBtn.prop('disabled', true).text('Submitting...');
                    let expect_duration = $('#expect_duration').val();
                    let reason = $('#reason').val();

                    $.ajax({
                        type: "POST",
                        url: "{{ action('\Modules\Clinic\Http\Controllers\ProviderController@updateDoctorStatus') }}",
                        data: {
                            is_active: false,
                            doctor_id: doctorId,
                            expect_duration: expect_duration,
                            reason: reason,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.msg);
                                $('#doctor_status_text').text('Unavailable');
                                $('#doctor_status_toggle').prop('checked', false);
                                $('#doctor_appointment_table').DataTable().ajax.reload();
                                $('#doctorUnavailableForm')[0].reset();
                                $('#doctorUnavailableModal').modal('hide');
                            } else {
                                toastr.error(response.msg);
                                $submitBtn.prop('disabled', false).text('Submit Again');
                            }
                        },
                        error: function() {
                            toastr.error('Something went wrong. Please try again.');
                        },
                        complete: function() {
                            $submitBtn.prop('disabled', false).text(
                                'Confirm Unavailability');
                        }
                    });
                });
            } else {
                // যদি Unavailable থেকে Available হচ্ছে → direct request পাঠাও
                $.ajax({
                    type: "POST",
                    url: "{{ action('\Modules\Clinic\Http\Controllers\ProviderController@updateDoctorStatus') }}",
                    data: {
                        is_active: true,
                        doctor_id: doctorId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.msg);
                            $('#doctor_status_text').text('Available');
                            $('#doctor_appointment_table').DataTable().ajax.reload();
                        } else {
                            toastr.error(response.msg);
                        }
                    },
                    error: function() {
                        toastr.error('Something went wrong. Please try again.');
                    }
                });
            }
        });
    }




    function autoMakeAvailable(doctorId) {
        $.ajax({
            type: "POST",
            url: "{{ action('\Modules\Clinic\Http\Controllers\ProviderController@updateDoctorStatus') }}",
            data: {
                is_active: true,
                doctor_id: doctorId,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#doctor_status_text').text('Available');
                    $('#doctor_status_toggle').prop('checked', true);
                    $('#doctor_appointment_table').DataTable().ajax.reload();
                    toastr.success('Available status updated! You are now available for appointments.');
                }
            },
            error: function() {
                console.error("Auto-availability update failed");
            }
        });
    }


    //test parameter create process
    $(document).ready(function() {

        let removedIds = [];

        $('#parameter_heading_table').on('click', '.remove-row', function() {
            let row = $(this).closest('tr');
            let id = row.data('id');
            if (id) {
                removedIds.push(id);
                $('#removed_parameter_ids').val(removedIds.join(','));
            }
            row.remove();
        });

        $('#add_parameter_row').on('click', function() {
            $('#parameter_heading_table tbody').append(`
            <tr>
                <td><input type="text" name="parameter_name[]" class="form-control" placeholder="Parameter Name" required /></td>
                <td><input type="number" name="reference_value[]" class="form-control" placeholder="Reference Value" required /></td>
                <td><input type="text" name="unit[]" class="form-control" placeholder="Unit" required /></td>
                <td><input type="text" name="parameter_description[]" class="form-control" placeholder="Parameter Description" /></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-trash"></i></button></td>
            </tr>
        `);
        });

    })
</script>
