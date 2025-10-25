<script type="text/javascript">
    $(document).ready(function() {
        var show_medicine_name_as = @json($common_settings['show_medicine_name_as']);
        checkAPIConnection();
        tinymce.init({
            selector: 'textarea#guidline_description',
            height: 450
        });

        function checkAPIConnection() {
            $.ajax({
                url: 'https://awc.careneterp.com:82/api/products',
                type: 'GET',
                data: {
                    term: 'test545'
                },
                dataType: "json",
                success: function(response) {
                    $('#search_life_style_products').prop('disabled', false);
                    $('#search_life_style_products').css('border', '');
                    $('#search_food_products').prop('disabled', false);
                    $('#search_food_products').css('border', '');
                },
                error: function(xhr, status, error) {
                    $('#search_life_style_products').prop('disabled', true);
                    $('#search_life_style_products').css('border', '1px solid red');
                    $('#search_food_products').prop('disabled', true);
                    $('#search_food_products').css('border', '1px solid red');
                }
            });
        }




        function setupAutocomplete(inputSelector, tableId, nameField, productIdField, genericIdField) {
            $(inputSelector).autocomplete({
                source: function(request, response) {
                    $.getJSON('https://awc.careneterp.com:82/api/products', {
                        term: request.term
                    }, function(data) {
                        response(data);
                    }).fail(function() {
                        toastr.error('Failed to fetch products. Please try again.',
                            'Error');
                    });
                },
                minLength: 2,
                select: function(event, ui) {
                    addProductToTable(ui.item, tableId, nameField, productIdField, genericIdField);
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
                    string += '<br>Brand: ' + item.brand.name + '\t QTY: ' + (item.qty_available > 0 ?
                        parseInt(item.qty_available) : 'Out of Stock') + '</div>';
                } else {
                    string += '<br>Name: ' + item.name + '\t QTY: ' + (item.qty_available > 0 ? parseInt(
                        item.qty_available) : 'Out of Stock') + '</div>';
                }
                return $('<li>').append(string).appendTo(ul);
            };
        }

        function addProductToTable(product, tableId, nameField, productIdField) {
            var isAlreadyAdded = false;
            var fullProductName = (product.short_name ? product.short_name + '. ' : '') + product.name + ' ' + (
                product.strength ? '(' + product.strength + ') ' : ''
            );

            $(`#${tableId} tbody tr`).each(function() {
                var existingSKU = $(this).find('td:nth-child(1) input[type="text"]').val().trim();
                if (existingSKU === fullProductName.trim()) {
                    isAlreadyAdded = true;
                    return false;
                }
            });

            if (isAlreadyAdded) {
                swal({
                    icon: 'warning',
                    title: 'Product already added!',
                    text: 'The selected product is already in the list.',
                });
                return;
            }

            // 🔹 সব ফিল্ড hidden input হিসেবে
            var rowHtml = `<tr>
                            <td>
                                <input ${show_medicine_name_as === 'generic' ? 'type="hidden"' : 'readonly required type="text"'} 
                                    name='${nameField}[]' 
                                    value='${fullProductName}' 
                                    class='form-control custom-input'>
                            </td>
                            ${tableId === 'life_style_products_table'
                                ? `<td>
                                        <input type="text" name="lifestyle_meal_time[]" 
                                            class="form-control meal_time_input custom-input">
                                        <div class="meal_time_info"></div>
                                </td>`
                                : `<td>
                                        <input type="text" name="food_meal_time[]" 
                                            class="form-control meal_time_input custom-input">
                                        <div class="meal_time_info"></div>
                                </td>`
                            }
                            ${tableId === 'life_style_products_table'
                                ? `<td>
                                        <input type="text" name="lifestyle_instruction[]" 
                                            class="form-control instruction_input custom-input">
                                        <div class="instruction_info"></div>
                                </td>`
                                : `<td>
                                        <input type="text" name="food_instruction[]" 
                                            class="form-control instruction_input custom-input">
                                        <div class="instruction_info"></div>
                                </td>`
                            }
                            <td>
                                <input type="hidden" name='${productIdField}[]' value='${product.product_id}'>
                                <input type="hidden" name="product_name[]" value="${product.name}">
                                <input type="hidden" name="nu_prescription_food_id[]" value="${product.id || ''}"> <!-- update এর জন্য -->
                                <button type="button" class="btn btn-danger btn-remove-row btn-xs">
                                    <i class="fa fa-times" style="font-size: 12px; cursor: pointer;"></i>
                                </button>
                            </td>
                        </tr>`;

            $(`#${tableId} tbody`).append(rowHtml);
            $('#nutritionist_prescription_form').valid();
        }


        // Food products
        setupAutocomplete('#search_food_products', 'food_products_table', 'medicine_name', 'product_id');

        // Life Style products
        setupAutocomplete('#search_life_style_products', 'life_style_products_table', 'life_style_name',
            'life_style_product_id');

        // Remove row handler
        $(document).on('click', '.btn-remove-row', function() {
            $(this).closest('tr').remove();
        });

        function validateForm() {
            $('#nutritionist_prescription_form').valid();
        }


        var clickedButton = null;

        $('.submit_btn').on('click', function() {
            clickedButton = $(this).val();
            console.log("Clicked Button:", clickedButton);
        });

        $('#nutritionist_prescription_form').on('submit', function(e) {
            e.preventDefault();

            // Table validation
            if ($('#food_products_table tbody tr').length < 1) {
                toastr.warning('Please add at least one Food Product.');
                return false;
            }
            if ($('#life_style_products_table tbody tr').length < 1) {
                toastr.warning('Please add at least one Life Style Product.');
                return false;
            }

            // Form validation
            if (!$(this).valid()) return;
            tinymce.triggerSave();
            var formData = $(this).serializeArray();

            if (clickedButton) {
                formData.push({
                    name: 'action',
                    value: clickedButton
                });
            }

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                dataType: 'json',
                beforeSend: function() {
                    $('.btn-primary, .btn-success').prop('disabled', true);
                },
                success: function(response) {
                    toastr.success(response.message || 'Saved successfully!');
                    if (clickedButton === 'Save and Print') {
                        window.location.href = response.print_url;

                    } else {
                        window.location.href = response.redirect_url || window.location
                            .href;
                    }
                },
                complete: function() {
                    $('.btn-primary, .btn-success').prop('disabled', false);
                }
            });
        });




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


        $(document).on('keyup', '.instruction_input', function() {
            var $input = $(this);
            var instruction = $input.val();
            var $td = $input.closest('td');
            var $infoBox = $td.find('.instruction_info');

            if (instruction.length > 1) {
                $.ajax({
                    url: '/get/instruction/info',
                    type: 'GET',
                    data: {
                        term: instruction
                    },
                    success: function(response) {
                        if (response.length > 0) {
                            var dropdown = '<ul class="dropdown-list">';
                            $.each(response, function(index, item) {
                                dropdown +=
                                    '<li class="instruction-item" data-value="' +
                                    item.value + '">' + item.value + '</li>';
                            });
                            dropdown += '</ul>';

                            // শুধু current td তে বসাও
                            $infoBox.html(dropdown).show();
                        } else {
                            $infoBox.hide();
                        }
                    }
                });
            } else {
                $infoBox.hide();
            }
        });

        // Click to select item
        $(document).on('click', '.instruction-item', function() {
            var value = $(this).data('value');

            var $td = $(this).closest('td');
            $td.find('.instruction_input').val(value); // শুধু current row এর input এ বসাও
            $td.find('.instruction_info').hide(); // শুধু current row এর dropdown hide
        });
        $(document).on('keyup', '.meal_time_input', function() {
            var $input = $(this);
            var meal_time = $input.val();
            var $td = $input.closest('td');
            var $infoBox = $td.find('.meal_time_info');

            if (meal_time.length > 1) {
                $.ajax({
                    url: '/get/meal_time/info',
                    type: 'GET',
                    data: {
                        term: meal_time
                    },
                    success: function(response) {
                        if (response.length > 0) {
                            var dropdown = '<ul class="dropdown-list">';
                            $.each(response, function(index, item) {
                                dropdown +=
                                    '<li class="meal_time-item" data-value="' +
                                    item.name + '">' + item.name + '</li>';
                            });
                            dropdown += '</ul>';

                            // শুধু current td তে বসাও
                            $infoBox.html(dropdown).show();
                        } else {
                            $infoBox.hide();
                        }
                    }
                });
            } else {
                $infoBox.hide();
            }
        });

        // Click to select item
        $(document).on('click', '.meal_time-item', function() {
            var value = $(this).data('value');

            var $td = $(this).closest('td');
            $td.find('.meal_time_input').val(value);
            $td.find('.meal_time_info').hide();
        });


        $(document).on('change', '#guidlines_select_box', function() {
            var selectedValue = $(this).val();
            if (!selectedValue) {
                $('#guidlines_info').addClass('hide');
                tinymce.get('guidline_description').setContent('');
                return;
            }
            $.ajax({
                url: '/get/guidelines-info/' + selectedValue,
                type: 'GET',
                success: function(response) {
                    if (response.success === true) {
                        $('#guidlines_info').removeClass('hide');
                        tinymce.get('guidline_description').setContent(response.data
                            .description);
                        $('#food_products_table tbody').empty();
                        $('#life_style_products_table tbody').empty();
                        if (response.products.length > 0) {
                            response.products.forEach(function(product) {
                                var row = `
                                    <tr>
                                        <td>
                                            <input type="text" readonly name="medicine_name[]" value="${product.name}" class="form-control custom-input">
                                        </td>
                                        <td>
                                            <input type="text" name="food_meal_time[]" value="" class="form-control meal_time_input custom-input">
                                            <div class="meal_time_info"></div>
                                        </td>
                                        <td>
                                            <input type="text" name="food_instruction[]" value="" class="form-control instruction_input custom-input">
                                            <div class="instruction_info"></div>
                                        </td>
                                        <td>
                                            <input type="hidden" name="product_id[]" value="${product.id}">
                                            <input type="hidden" name="nu_prescription_food_id[]" value=""> <!-- null initially -->
                                            <button type="button" class="btn btn-danger btn-remove-row btn-xs">
                                                <i class="fa fa-times" style="font-size: 12px; cursor: pointer;"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                $('#food_products_table tbody').append(row);
                            });

                        }
                        if (response.lifeStyles.length > 0) {
                            response.lifeStyles.forEach(function(product) {
                                var row2 = `
                                    <tr>
                                        <td>
                                            <input type="text" readonly name="life_style_name[]" value="${product.name}" class="form-control custom-input">
                                        </td>
                                        <td>
                                            <input type="text" name="lifestyle_meal_time[]" value="" class="form-control meal_time_input custom-input">
                                            <div class="meal_time_info"></div>
                                        </td>
                                        <td>
                                            <input type="text" name="lifestyle_instruction[]" value="" class="form-control instruction_input custom-input">
                                            <div class="instruction_info"></div>
                                        </td>
                                        <td>
                                            <input type="hidden" name="life_style_product_id[]" value="${product.id}">
                                            <input type="hidden" name="nu_prescription_food_id[]" value=""> <!-- null initially -->
                                            <button type="button" class="btn btn-danger btn-remove-row btn-xs">
                                                <i class="fa fa-times" style="font-size: 12px; cursor: pointer;"></i>
                                            </button>
                                        </td>
                                    </tr>
                                `;
                                $('#life_style_products_table tbody').append(row2);
                            });

                        }

                    } else {
                        $('#guidlines_info').addClass('hide');
                        tinymce.get('guidline_description').setContent('');
                    }
                }
            })
        });


        $(document).on('change', '#old_prescription', function() {
            var selectedValue = $(this).val();
            var baseUrl = "{{ url('load/old/prescription/' . $prescriptionId) }}";
            swal({
                title: "Are you sure?",
                text: "You want to load old prescription?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willLoad) => {
                if (willLoad) {
                    window.location.href = baseUrl + '/' + selectedValue;
                }
            });
        });
    });
</script>
