@extends('clinic::layouts.app2')
@section('title', __('Payment Report'))
@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>Payment Report
        </h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
       

        @component('components.filters', ['title' => __('report.filters')])
            @include('clinic::sell.partials.payment_list_filters')
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
            
        @endcomponent
        <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                     
                        <li>
                            <a href="#sell_payment_tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fas fa-calendar-check"></i>Payment Details</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        
                        <div class="tab-pane active" id="sell_payment_tab">
                            @include('clinic::sell.today_payment')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
                                                                                                                    </section> -->

@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var today_payment_table = $('#today_payment_table').DataTable({
                processing: true,
                serverSide: true,
                
                aaSorting: [
                    [0, 'desc']
                ], // Sort by date
                ajax: {
                    url: "/clinic-sell-payment-report",
                    data: function(d) {
                        if ($('#sell_list_filter_date_range').val()) {
                            var start = $('#sell_list_filter_date_range').data('daterangepicker')
                                .startDate.format('YYYY-MM-DD');
                            var end = $('#sell_list_filter_date_range').data('daterangepicker')
                                .endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                        d.location_id = $('#sell_list_filter_location_id').val();
                        d.customer_id = $('#sell_list_filter_customer_id').val();
                        d.payment_status = $('#sell_list_filter_payment_status').val();
                        d.created_by = $('#created_by').val();
                        d.payment_method = $('#sell_list_filter_payment_method').val();
                        d.sub_type = $('#sub_type').val();
                        d.status = $('#status').val();
                    }
                },
               
                columns: [{
                        data: 'invoice_no',
                        name: 'transactions.invoice_no'
                    },
                    {
                        data: 'pay_date',
                        name: 'transaction_payments.paid_on'
                    },
                    {
                        data: 'sale_date',
                        name: 'transactions.transaction_date'
                    },
                    {
                        data: 'name',
                        name: 'contacts.name'
                    },
                    {
                        data: 'sub_type',
                        name: 'transactions.sub_type'
                    },
                    {
                        data: 'method',
                        name: 'transaction_payments.method'
                    },
                    {
                        data: 'total_items',
                        name: 'total_items'
                    },
                    {
                        data: 'final_total',
                        name: 'transactions.final_total'
                    },
                    {
                        data: 'total_paid',
                        name: 'total_paid'
                    },
                    {
                        data: 'total_payment',
                        name: 'total_payment'
                    },
                    {
                        data: 'payment_status',
                        name: 'transactions.payment_status'
                    },
                    {
                        data: 'added_by',
                        name: 'added_by'
                    }
                ],
                fnDrawCallback: function(oSettings) {
                    let api = this.api();
                    // Calculate the total payment using the correct column index (9 for total_payment)
                    let totalPayment = api.column(9, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        let numericValueB = parseFloat(b.replace(/[^\d.]/g, ''));
                        if (!isNaN(numericValueB)) {
                            return parseFloat(a) + numericValueB;
                        }
                        return parseFloat(a);
                    }, 0);

                    // Update the footer with the totals
                    $('#footer_total_today_pay').text(totalPayment.toFixed(2));
                    __currency_convert_recursively($('#today_payment_table'));
                },
            });
            //Date range as a button
            $('#sell_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    today_payment_table.ajax.reload();
                }
            );
            $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#sell_list_filter_date_range').val('');
                today_payment_table.ajax.reload();
            });

            

            $(document).on('change',
                '#sell_list_filter_location_id, #sell_list_filter_customer_id, #sell_list_filter_payment_status, #created_by, #sales_cmsn_agnt, #service_staffs, #shipping_status, #sell_list_filter_source, #sell_list_filter_payment_method, #sell_list_filter_discount_type, #shipping_status, #sub_type',
                function() {
                    today_payment_table.ajax.reload();
                });

           

            // Event handler for the due filter
            $('#due_filter').on('click', function() {
                // Set payment status filter to 'due'
                $('#sell_list_filter_payment_status').val('due').trigger('change');

                // Reload the DataTable
                today_payment_table.draw();
            });
        });
        
    </script>
    <script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection
