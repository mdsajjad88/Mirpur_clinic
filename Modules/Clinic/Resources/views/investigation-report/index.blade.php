@extends('clinic::layouts.app2')
@section('title', __('All Bills'))
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>Bills
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">

        @component('components.filters', ['title' => __('report.filters')])
            @include('clinic::sell.partials.sell_list_filters')
            @if (!empty($sources))
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('sell_list_filter_source', __('lang_v1.sources') . ':') !!}

                        {!! Form::select('sell_list_filter_source', $sources, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
            @endif
            <div class="col-md-3">
                <div class="form-group">
                    @php

                        $sub_type = [
                            'test' => 'Test',
                            'therapy' => 'Therapy',
                            'ipd' => 'IPD',
                            'consultation' => 'Consultation',
                        ];
                    @endphp
                    {!! Form::label('sub_type', __('clinic::lang.billing_type') . ':') !!}

                    {!! Form::select('sub_type', $sub_type, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'id' => 'sub_type',
                        'placeholder' => __('lang_v1.all'),
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('categories', __('clinic::lang.category') . ':') !!}
                    {!! Form::select('category_id', $categories, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%',
                        'multiple' => 'multiple',
                        'id' => 'product_list_filter_category_id',
                    ]) !!}
                </div>
            </div>
        @endcomponent
        <div class="row">
            <div class="col-md-12">
            @component('components.widget', ['class' => 'box-primary', 'title' => __('All Lab Bills')])
                @if (auth()->user()->can('clinic.sell.create'))
                    @slot('tool')
                        <div class="box-tools">
                            <a class="btn btn-block btn-primary"
                                href="{{ action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'create']) }}">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </a>
                        </div>
                    @endslot
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped ajax_view" id="clinic_sell_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('messages.date')</th>
                                <th>@lang('sale.invoice_no')</th>
                                <th>@lang('clinic::lang.patient_name')</th>
                                <th>@lang('lang_v1.contact_no')</th>
                                <th>@lang('sale.location')</th>
                                <th>@lang('clinic::lang.type')</th>
                                <th>@lang('lang_v1.payment_method')</th>
                                <th>@lang('sale.payment_status')</th>
                                <th>@lang('sale.total_amount')</th>
                                <th>@lang('sale.total_paid')</th>
                                <th>@lang('clinic::lang.bill_due')</th>
                                <th>@lang('Campaign Discount')</th>
                                <th>@lang('Special Discount')</th>
                                {{-- <th>@lang('Sell Return (total amount)')</th> --}}
                                <th><small>@lang('clinic::lang.bill_refund_and_total')</small></th>
                                <th>@lang('lang_v1.shipping_status')</th>
                                <th>@lang('lang_v1.total_items')</th>
                                <th>@lang('lang_v1.types_of_service')</th>
                                <th>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1') }}
                                </th>
                                <th>{{ $custom_labels['sell']['custom_field_1'] ?? '' }}</th>
                                <th>{{ $custom_labels['sell']['custom_field_2'] ?? '' }}</th>
                                <th>{{ $custom_labels['sell']['custom_field_3'] ?? '' }}</th>
                                <th>{{ $custom_labels['sell']['custom_field_4'] ?? '' }}</th>
                                <th>@lang('lang_v1.added_by')</th>
                                <th>@lang('clinic::lang.bill_note')</th>
                                <th>@lang('sale.staff_note')</th>
                                <th>@lang('sale.shipping_details')</th>
                                <th>@lang('restaurant.table')</th>
                                <th>@lang('restaurant.service_staff')</th>
                                <th>Reference</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                        <tfoot>
                            <tr style="font-size: 12px" class="bg-gray footer-total text-left">
                                <td class="text-center" colspan="5"><strong>@lang('sale.total'):</strong></td>
                                <td colspan="2" class="payment_method_count"></td>
                                <td class="footer_payment_status_count"></td>
                                <td class="footer_sale_total"></td>
                                <td class="footer_total_paid"></td>
                                <td class="footer_total_remaining"></td>
                                <td class="footer_total_campaign_discount"></td>
                                <td class="footer_total_special_discount"></td>
                                <td class="footer_total_sell_return_due"></td>
                                <td colspan="15"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endcomponent
            </div>
        </div>
    </section>
    <!-- /.content -->
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>

    <input type="hidden" id="stock_expiry_alert_days"
        value="{{ \Carbon::now()->addDays(session('business.stock_expiry_alert_days', 30))->format('Y-m-d') }}">
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <input type="hidden" id="register_report" value="{{ $register }}" data-register="{{ $register }}">
    <!-- This will be printed -->
    <!-- <section class="invoice print_section" id="receipt_section">
                                                                                                                        </section> -->

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            //Date range as a button
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    clinic_sell_table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                clinic_sell_table.ajax.reload();
            });

            clinic_sell_table = $('#clinic_sell_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": "/investigation-report",
                    "data": function(d) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                        d.is_direct_sale = 1;

                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.customer_id = $('#sell_list_filter_customer_id').val();
                        d.payment_status = $('#sell_list_filter_payment_status').val();
                        d.created_by = $('#created_by').val();
                        d.sales_cmsn_agnt = $('#sales_cmsn_agnt').val();
                        d.service_staffs = $('#service_staffs').val();
                        d.discount_type = $('#sell_list_filter_discount_type').val();
                        d.payment_method = $('#sell_list_filter_payment_method').val();
                        d.sub_type = $('#sub_type').val();
                        if ($('#shipping_status').length) {
                            d.shipping_status = $('#shipping_status').val();
                        }


                        if ($('#sell_list_filter_source').length) {
                            d.source = $('#sell_list_filter_source').val();
                        }

                        if ($('#only_subscriptions').is(':checked')) {
                            d.only_subscriptions = 1;
                        }
                        if ($('#delivery_sales').is(':checked')) {
                            d.delivery_sales = 1;
                        }

                        d = __datatable_ajax_callback(d);
                    }
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [{
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'transaction_date',
                        name: 'transaction_date'
                    },
                    {
                        data: 'invoice_no',
                        name: 'invoice_no'
                    },
                    {
                        data: 'conatct_name',
                        name: 'conatct_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'business_location',
                        name: 'bl.name'
                    },
                    {
                        data: 'sub_type',
                        name: 'sub_type',
                        render: function(data, type, row) {
                            if (data) {
                                return data.charAt(0).toUpperCase() + data.slice(1);
                            }
                            return data;
                        },

                    },
                    {
                        data: 'payment_methods',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'final_total',
                        name: 'final_total'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid',
                        "searchable": false
                    },
                    {
                        data: 'total_remaining',
                        name: 'total_remaining'
                    },
                    {
                        data: 'line_discount_amount',
                        name: 'line_discount_amount',
                        "searchable": false
                    },
                    {
                        data: 'discount_amount',
                        name: 'discount_amount'
                    },
                    {
                        data: 'return_due',
                        orderable: false,
                        "searchable": false
                    },
                    {
                        data: 'shipping_status',
                        name: 'shipping_status'
                    },
                    {
                        data: 'total_items',
                        name: 'total_items',
                        "searchable": false
                    },
                    {
                        data: 'types_of_service_name',
                        name: 'tos.name',
                        @if (empty($is_types_service_enabled))
                            visible: false
                        @endif
                    },
                    {
                        data: 'service_custom_field_1',
                        name: 'service_custom_field_1',
                        @if (empty($is_types_service_enabled))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_1',
                        name: 'transactions.custom_field_1',
                        @if (empty($custom_labels['sell']['custom_field_1']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_2',
                        name: 'transactions.custom_field_2',
                        @if (empty($custom_labels['sell']['custom_field_2']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_3',
                        name: 'transactions.custom_field_3',
                        @if (empty($custom_labels['sell']['custom_field_3']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'custom_field_4',
                        name: 'transactions.custom_field_4',
                        @if (empty($custom_labels['sell']['custom_field_4']))
                            visible: false
                        @endif
                    },
                    {
                        data: 'added_by',
                        name: 'u.first_name'
                    },
                    {
                        data: 'additional_notes',
                        name: 'additional_notes'
                    },
                    {
                        data: 'staff_note',
                        name: 'staff_note'
                    },
                    {
                        data: 'shipping_details',
                        name: 'shipping_details'
                    },
                    {
                        data: 'table_name',
                        name: 'tables.name',
                        @if (empty($is_tables_enabled))
                            visible: false
                        @endif
                    },
                    {
                        data: 'waiter',
                        name: 'ss.first_name',
                        @if (empty($is_service_staff_enabled))
                            visible: false
                        @endif
                    },
                    {
                        data: 'reference',
                        name: 'dpro.first_name',
                    },
                ],
                "fnDrawCallback": function(oSettings) {
                    __currency_convert_recursively($('#clinic_sell_table'));
                },
                "footerCallback": function(row, data, start, end, display) {
                    var footer_sale_total = 0;
                    var footer_total_paid = 0;
                    var footer_total_remaining = 0;
                    var footer_total_sell_return_due = 0;
                    var footer_total_special_discount = 0;
                    var footer_total_campaign_discount = 0;

                    // Initialize an object to store payment method counts and subtotals
                    var paymentMethodTotals = {};

                    // Loop through each data row
                    for (var r in data) {
                        // Accumulate totals for sale total, total paid, total remaining, and sell return due
                        footer_sale_total += $(data[r].final_total).data('orig-value') ? parseFloat($(
                            data[r].final_total).data('orig-value')) : 0;
                        footer_total_paid += $(data[r].total_paid).data('orig-value') ? parseFloat($(
                            data[r].total_paid).data('orig-value')) : 0;
                        footer_total_remaining += $(data[r].total_remaining).data('orig-value') ?
                            parseFloat($(data[r].total_remaining).data('orig-value')) : 0;
                        footer_total_sell_return_due += $(data[r].return_due).find('.sell_return_due')
                            .data('orig-value') ? parseFloat($(data[r].return_due).find(
                                '.sell_return_due').data('orig-value')) : 0;

                        // Special Discount: Ensure proper parsing and data retrieval
                        footer_total_special_discount += $(data[r].discount_amount).data('orig-value') ?
                            parseFloat($(data[r].discount_amount).data('orig-value')) : 0;
                        footer_total_campaign_discount += $(data[r].line_discount_amount).data(
                            'orig-value') ? parseFloat($(data[r].line_discount_amount).data(
                            'orig-value')) : 0;

                        // Count payment methods and calculate subtotal based on payment methods
                        var paymentMethods = $(data[r].payment_methods).text().split(',');
                        for (var i = 0; i < paymentMethods.length; i++) {
                            var paymentMethod = paymentMethods[i].trim();
                            if (!paymentMethodTotals.hasOwnProperty(paymentMethod)) {
                                paymentMethodTotals[paymentMethod] = {
                                    count: 0,
                                    subtotal: 0
                                };
                            }
                            paymentMethodTotals[paymentMethod].count++;
                            paymentMethodTotals[paymentMethod].subtotal += parseFloat($(data[r]
                                .final_total).data('orig-value'));
                        }
                    }

                    // Render payment method counts and subtotals
                    var paymentMethodHtml = '';
                    for (var method in paymentMethodTotals) {
                        paymentMethodHtml += method + '(' + paymentMethodTotals[method].count + ')-' +
                            __currency_trans_from_en(paymentMethodTotals[method].subtotal) + '<br>';
                    }

                    // Update footer elements with totals and payment method information
                    $('.footer_total_campaign_discount').html(__currency_trans_from_en(
                        footer_total_campaign_discount));
                    $('.footer_total_special_discount').html(__currency_trans_from_en(
                        footer_total_special_discount));
                    $('.footer_total_sell_return_due').html(__currency_trans_from_en(
                        footer_total_sell_return_due));
                    $('.footer_total_remaining').html(__currency_trans_from_en(footer_total_remaining));
                    $('.footer_total_paid').html(__currency_trans_from_en(footer_total_paid));
                    $('.footer_sale_total').html(__currency_trans_from_en(footer_sale_total));
                    $('.footer_payment_status_count').html(__count_status(data, 'payment_status'));
                    $('.service_type_count').html(__count_status(data, 'types_of_service_name'));
                    $('.payment_method_count').html(paymentMethodHtml);
                },

                createdRow: function(row, data, dataIndex) {
                    $(row).find('td:eq(6)').attr('class', 'clickable_td');
                }
            });

            $(document).on('change',
                '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status, #sell_list_filter_source, #sell_list_filter_payment_method, #sell_list_filter_discount_type, #shipping_status, #sub_type',
                function() {
                    clinic_sell_table.ajax.reload();
                    today_payment_table.ajax.reload();
                });

            $('#only_subscriptions').on('ifChanged', function(event) {
                clinic_sell_table.ajax.reload();
            });
            $('#delivery_sales').on('ifChanged', function(event) {
                clinic_sell_table.ajax.reload();
            });

            // Event handler for the due filter
            $('#due_filter').on('click', function() {
                // Set payment status filter to 'due'
                $('#sell_list_filter_payment_status').val('due').trigger('change');

                // Reload the DataTable
                clinic_sell_table.ajax.reload();
                today_payment_table.draw();
            });
        });

        $(document).on('change', '#sub_type', function() {
            var sub_type = $(this).val();
            if (!sub_type) {
                sub_type = 'all';
            }
            $.ajax({
                url: '/get/billing/sub_type/category/' + sub_type,
                type: 'GET',
                success: function(response) {
                    var categoryDropdown = $(
                        '#product_list_filter_category_id');
                    categoryDropdown.empty();

                    $.each(response.data, function(index, category) {
                        categoryDropdown.append('<option value="' + category.id + '">' +
                            category.name + '</option>');
                    });
                }
            })
        })
        $(document).on('click', '.sell-return-btn', function(e) {
            e.preventDefault(); // Prevent the default action of the link

            const isDirectSale = $(this).data('is_direct_sale'); // Get the is_direct_sale value
            const register = $('#register_report').data('register'); // Get the register value

            if (isDirectSale == 0 && !register) {
                swal({
                    title: "No Bill Return can be processed as the register is closed.",
                    icon: "warning",
                    dangerMode: true,
                });
            } else {
                // Proceed with the normal link action if conditions are not met
                window.location.href = $(this).attr('href');
            }
        });
    </script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection

