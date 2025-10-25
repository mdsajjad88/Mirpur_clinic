@extends('clinic::layouts.app2')
@section('title', __('business.business_settings'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('business.business_settings')</h1>
        <br>
        @include('layouts.partials.search_settings')
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\BusinessController::class, 'postBusinessSettings']),
            'method' => 'post',
            'id' => 'bussiness_edit_form',
            'files' => true,
        ]) !!}
        <div class="row">
            <div class="col-xs-12">
                <!--  <pos-tab-container> -->
                <div class="col-xs-12 pos-tab-container">
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-2 pos-tab-menu">
                        <div class="list-group">
                            <a href="#" class="list-group-item text-center active">@lang('business.business')</a>
                            <a href="#" class="list-group-item text-center">@lang('business.tax')
                                @show_tooltip(__('tooltip.business_tax'))</a>
                            <a href="#" class="list-group-item text-center">@lang('business.product')</a>
                            <a href="#" class="list-group-item text-center">@lang('contact.contact')</a>
                            <a href="#" class="list-group-item text-center">@lang('business.sale')</a>
                            <a href="#" class="list-group-item text-center">@lang('sale.pos_sale')</a>
                            <a href="#" class="list-group-item text-center">@lang('purchase.purchases')</a>
                            <a href="#" class="list-group-item text-center">@lang('lang_v1.payment')</a>
                            <a href="#" class="list-group-item text-center">@lang('business.dashboard')</a>
                            <a href="#" class="list-group-item text-center">@lang('business.system')</a>
                            <a href="#" class="list-group-item text-center">@lang('lang_v1.prefixes')</a>
                            <a href="#" class="list-group-item text-center">@lang('lang_v1.email_settings')</a>
                            <a href="#" class="list-group-item text-center">@lang('lang_v1.sms_settings')</a>
                            <a href="#" class="list-group-item text-center">@lang('lang_v1.reward_point_settings')</a>
                            <a href="#" class="list-group-item text-center">@lang('lang_v1.modules')</a>
                            <a href="#" class="list-group-item text-center">@lang('lang_v1.custom_labels')</a>
                            <a href="#" class="list-group-item text-center">@lang('lang_v1.foreign_category')</a>
                            <a href="#" class="list-group-item text-center">@lang('lang_v1.cash_register')</a>
                            <a href="#"
                                class="list-group-item text-center @if (request()->get('tab') == 'clinic-tab') active @endif">@lang('lang_v1.clinic')</a>
                        </div>
                    </div>
                    <div class="col-lg-10 col-md-10 col-sm-10 col-xs-10 pos-tab">
                        <!-- tab 1 start -->
                        @include('business.partials.settings_business')
                        <!-- tab 1 end -->
                        <!-- tab 2 start -->
                        @include('business.partials.settings_tax')
                        <!-- tab 2 end -->
                        <!-- tab 3 start -->
                        @include('business.partials.settings_product')

                        @include('business.partials.settings_contact')
                        <!-- tab 3 end -->
                        <!-- tab 4 start -->
                        @include('business.partials.settings_sales')
                        @include('business.partials.settings_pos')
                        <!-- tab 4 end -->
                        <!-- tab 5 start -->
                        @include('business.partials.settings_purchase')

                        @include('business.partials.settings_payment')
                        <!-- tab 5 end -->
                        <!-- tab 6 start -->
                        @include('business.partials.settings_dashboard')
                        <!-- tab 6 end -->
                        <!-- tab 7 start -->
                        @include('business.partials.settings_system')
                        <!-- tab 7 end -->
                        <!-- tab 8 start -->
                        @include('business.partials.settings_prefixes')
                        <!-- tab 8 end -->
                        <!-- tab 9 start -->
                        @include('business.partials.settings_email')
                        <!-- tab 9 end -->
                        <!-- tab 10 start -->
                        @include('business.partials.settings_sms')
                        <!-- tab 10 end -->
                        <!-- tab 11 start -->
                        @include('business.partials.settings_reward_point')
                        <!-- tab 11 end -->
                        <!-- tab 12 start -->
                        @include('business.partials.settings_modules')
                        <!-- tab 12 end -->
                        @include('business.partials.settings_custom_labels')
                        <!-- tab 13 start -->
                        @include('business.partials.settings_us_category')
                        <!-- tab 13 end -->
                        <!-- tab 14 start -->
                        @include('business.partials.settings_cash_register')
                        <!-- tab 14 end -->
                        {{-- tab 15 start --}}
                        @include('business.partials.clinic_settings')
                        <!-- tab 15 end -->
                    </div>
                </div>
                <!--  </pos-tab-container> -->
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12 text-center">
                <button class="btn btn-danger btn-big" type="submit">@lang('business.update_settings')</button>
            </div>
        </div>
        {!! Form::close() !!}
    </section>
    <!-- /.content -->
@stop
@section('javascript')
    {{-- <script src="{{ asset('js/purchase.js?v=' . $asset_v) }}"></script> --}}
    <script type="text/javascript">
        __page_leave_confirmation('#bussiness_edit_form');
        $(document).on('ifToggled', '#use_superadmin_settings', function() {
            if ($('#use_superadmin_settings').is(':checked')) {
                $('#toggle_visibility').addClass('hide');
                $('.test_email_btn').addClass('hide');
            } else {
                $('#toggle_visibility').removeClass('hide');
                $('.test_email_btn').removeClass('hide');
            }
        });

        $(document).ready(function() {
            //get suppliers
            $('#supplier_id')
                .select2({
                    ajax: {
                        url: '/purchases/get_suppliers',
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
                    minimumInputLength: 1,
                    escapeMarkup: function(m) {
                        return m;
                    },
                    templateResult: function(data) {
                        if (!data.id) {
                            return data.text;
                        }
                        var html = data.text + ' - ' + data.business_name + ' (' + data.contact_id + ')';
                        return html;
                    },
                    language: {
                        noResults: function() {
                            var name = $('#supplier_id').data('select2').dropdown.$search.val();
                            return (
                                '<button type="button" data-name="' +
                                name +
                                '" class="btn btn-link add_new_supplier"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
                                __translate('add_name_as_new_supplier', {
                                    name: name
                                }) +
                                '</button>'
                            );
                        },
                    },
                })
                .on('select2:select', function(e) {
                    var data = e.params.data;
                });
            $('#supplier_id').on('change', function() {
                var supplier_id = $(this).val();
                $('#show_purchase_contact').val(supplier_id);
                // alert(supplier_id);
            })
            $('#test_email_btn').click(function() {
                var data = {
                    mail_driver: $('#mail_driver').val(),
                    mail_host: $('#mail_host').val(),
                    mail_port: $('#mail_port').val(),
                    mail_username: $('#mail_username').val(),
                    mail_password: $('#mail_password').val(),
                    mail_encryption: $('#mail_encryption').val(),
                    mail_from_address: $('#mail_from_address').val(),
                    mail_from_name: $('#mail_from_name').val(),
                };
                $.ajax({
                    method: 'post',
                    data: data,
                    url: "{{ action([\App\Http\Controllers\BusinessController::class, 'testEmailConfiguration']) }}",
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            swal({
                                text: result.msg,
                                icon: 'success'
                            });
                        } else {
                            swal({
                                text: result.msg,
                                icon: 'error'
                            });
                        }
                    },
                });
            });

            $('#test_sms_btn').click(function() {
                var test_number = $('#test_number').val();
                if (test_number.trim() == '') {
                    toastr.error('{{ __('lang_v1.test_number_is_required') }}');
                    $('#test_number').focus();

                    return false;
                }

                var data = {
                    url: $('#sms_settings_url').val(),
                    send_to_param_name: $('#send_to_param_name').val(),
                    msg_param_name: $('#msg_param_name').val(),
                    request_method: $('#request_method').val(),
                    param_1: $('#sms_settings_param_key1').val(),
                    param_2: $('#sms_settings_param_key2').val(),
                    param_3: $('#sms_settings_param_key3').val(),
                    param_4: $('#sms_settings_param_key4').val(),
                    param_5: $('#sms_settings_param_key5').val(),
                    param_6: $('#sms_settings_param_key6').val(),
                    param_7: $('#sms_settings_param_key7').val(),
                    param_8: $('#sms_settings_param_key8').val(),
                    param_9: $('#sms_settings_param_key9').val(),
                    param_10: $('#sms_settings_param_key10').val(),

                    param_val_1: $('#sms_settings_param_val1').val(),
                    param_val_2: $('#sms_settings_param_val2').val(),
                    param_val_3: $('#sms_settings_param_val3').val(),
                    param_val_4: $('#sms_settings_param_val4').val(),
                    param_val_5: $('#sms_settings_param_val5').val(),
                    param_val_6: $('#sms_settings_param_val6').val(),
                    param_val_7: $('#sms_settings_param_val7').val(),
                    param_val_8: $('#sms_settings_param_val8').val(),
                    param_val_9: $('#sms_settings_param_val9').val(),
                    param_val_10: $('#sms_settings_param_val10').val(),
                    test_number: test_number
                };

                $.ajax({
                    method: 'post',
                    data: data,
                    url: "{{ action([\App\Http\Controllers\BusinessController::class, 'testSmsConfiguration']) }}",
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            swal({
                                text: result.msg,
                                icon: 'success'
                            });
                        } else {
                            swal({
                                text: result.msg,
                                icon: 'error'
                            });
                        }
                    },
                });

            });

            $('select.custom_labels_products').change(function() {
                value = $(this).val();
                textarea = $(this).parents('div.custom_label_product_div').find(
                    'div.custom_label_product_dropdown');
                if (value == 'dropdown') {
                    textarea.removeClass('hide');
                } else {
                    textarea.addClass('hide');
                }
            })
            // for Categories EXP date set
            $('#add_purchase_exp_item').click(function() {
                var index =
                    {{ !empty($common_settings['purchases_exp']) ? count($common_settings['purchases_exp']) : 0 }};
                var newItem = `
               <div class="purchases_exp_item row mb-3">
                   <div class="form-group col-md-3">
                       <label for="purchases_exp[` + index + `][category_id]">@lang('Category Name')</label>
                       <select name="common_settings[purchases_exp][` + index + `][category_id]" class="form-control select2" style="width: 100%;">
                           @foreach ($category_dropdown as $key => $value)
                               <option value="{{ $key }}">{{ $value }}</option>
                           @endforeach
                       </select>
                   </div>
                   <div class="form-group col-md-3">
                       <label for="purchases_exp[` + index + `][month]">Month:</label>
                       <input type="number" name="common_settings[purchases_exp][` + index + `][month]" class="form-control" placeholder="Add EXP Month">
                   </div>
                   <div class="form-group col-md-2">
                <button type="button" class="btn btn-danger remove-purchase-exp" style="margin-top: 25px;">Remove</button>
            </div>
               </div>
               `;
                $('#purchases_exp_container').append(newItem);
                index++;
            });
            // Remove an item when the remove button is clicked
            $(document).on('click', '.remove-purchase-exp', function() {
                $(this).closest('.purchases_exp_item').remove();
            });

            // JavaScript for Discount Rules
            let discountRuleIndex =
                {{ !empty($pos_settings['discount_rules']) ? count($pos_settings['discount_rules']) : 0 }};

            $('#add_discount_rule').on('click', function() {
                let newRow = `<tr>
                            <td>
                                <input type="number" name="pos_settings[discount_rules][${discountRuleIndex}][max_discount]" class="form-control" placeholder="Max Discount">
                            </td>
                            <td>
                                <select name="pos_settings[discount_rules][${discountRuleIndex}][discount_type]" class="form-control discount-type">
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed</option>
                                </select>
                            </td>
                            <td>
                                <input type="number" name="pos_settings[discount_rules][${discountRuleIndex}][min_sell_amount]" class="form-control" placeholder="Minimum Sell Amount">
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger remove_discount_rule">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
                $('#discount_rules_table tbody').append(newRow);
                discountRuleIndex++;
                updateDiscountOptions();
            });

            // Event delegation for removing discount rules
            $('#discount_rules_table').on('click', '.remove_discount_rule', function() {
                $(this).closest('tr').remove();
                updateDiscountOptions();
            });

            // Function to update discount type options based on the first row's selection
            $('#discount_rules_table').on('change', '.discount-type', function() {
                updateDiscountOptions();
            });

            function updateDiscountOptions() {
                let firstRowDiscountType = $('#discount_rules_table tbody tr:first .discount-type').val();

                $('#discount_rules_table tbody tr:gt(0) .discount-type').each(function() {
                    if (firstRowDiscountType === 'percentage') {
                        // Disable "Fixed" in all other rows if "Percentage" is selected in the first row
                        $(this).find('option[value="fixed"]').attr('disabled', true);
                        $(this).find('option[value="percentage"]').attr('disabled', false);
                    } else if (firstRowDiscountType === 'fixed') {
                        // Disable "Percentage" in all other rows if "Fixed" is selected in the first row
                        $(this).find('option[value="percentage"]').attr('disabled', true);
                        $(this).find('option[value="fixed"]').attr('disabled', false);
                    } else {
                        // Enable both options if the first row has no selection
                        $(this).find('option[value="percentage"]').attr('disabled', false);
                        $(this).find('option[value="fixed"]').attr('disabled', false);
                    }
                });
            }

            // Initial call to apply rules on page load
            updateDiscountOptions();   

                $('#add_call_status').click(function() {
                let rowIndex = $('#call_status_table tbody tr').length;
                const newRow = `
                    <tr>
                        <td>
                            <input type="text" name="common_settings[call_status][${rowIndex}][call_status]" class="form-control" placeholder="Status" value="">
                        </td>
                        <td>
                        <input type="color" name="common_settings[call_status][${rowIndex}][call_status_color]" class="form-control">
                        </td>   
                        <td>
                            <button type="button" class="btn btn-danger remove-status-row">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                $('#call_status_table tbody').append(newRow);
            });

            $(document).on('click', '.remove-status-row', function() {
                $(this).closest('tr').remove();
                reIndexRowsStatus();
            });

            function reIndexRowsStatus() {
                $('#call_status_table tbody tr').each(function(index) {
                    $(this).find('input, select').each(function() {
                        let name = $(this).attr('name');
                        name = name.replace(/\[\d+\]/, `[${index}]`);
                        $(this).attr('name', name);
                    });
                });
            }
            });

    loadCustomerGroups();
    // Load customer groups via API
    function loadCustomerGroups() {
        $.ajax({
            url: 'https://203.190.9.99:82/api/customer-groups',
            type: 'GET',
            success: function(response) {
                if (response.status) {
                    var select = $('#doctor_visit_customer_group'); // ✅ match Blade id
                    select.empty();
                    select.append($('<option>', {
                        value: '',
                        text: 'Please Select'
                    }));
                    
                    $.each(response.data, function(index, group) {
                        select.append($('<option>', {
                            value: group.id,
                            text: group.name
                        }));
                    });
                    
                    // ✅ Set selected value if exists
                    var selectedValue = "{{ $common_settings['doctor_visit_customer_group_id'] ?? '' }}";
                    if (selectedValue) {
                        select.val(selectedValue).trigger('change');
                    }
                }
            },
            error: function(xhr) {
                console.error('Error loading customer groups:', xhr.responseText);
            }
        });
    }

    </script>

@endsection
