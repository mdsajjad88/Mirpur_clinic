{{-- 'clinic::dashboard.clinic_dashboard' --}}
@extends('clinic::layouts.app2')

@section('css')
<style>
    .swal2-popup.scrollable-swal {
        max-width: 70% !important;
        padding: 1rem;
    }
    .swal2-container {
        z-index: 1030 !important; /* Lower than Bootstrap modal (1050) */
    }
</style>
@endsection

@section('title', __('clinic::lang.clinic_dashboard'))

@section('content')
    <section class="content-header">       
        <h1>{{__('clinic::lang.clinic_dashboard')}}</h1>
    </section>
    @if(auth()->user()->can('admin'))
    <section class="content no-print">
        <div class="row">
            <div class="col-md-4 col-xs-12">
                <input type="hidden" id="dashboard_location" value="{{ $clinic_location }}">
            </div>
            <div class="col-md-8 col-xs-12">
                <div class="form-group pull-right">
                    <div class="input-group">
                        <button type="button" class="btn btn-primary" id="dashboard_date_filter">
                            <span>
                                <i class="fa fa-calendar"></i> {{ __('messages.filter_by_date') }}
                            </span>
                            <i class="fa fa-caret-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <br>

        <!-- Total Bill Income -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom total_sell_container cursor-pointer">
            <div style="position: relative; background-color: #e0f7fa; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #00bcd4;"><i class="ion ion-ios-cart-outline"></i></span>
                <div style="text-align: left;">
                    <span style="display: block; font-size: 18px; font-weight: 600; color: #333;">@lang('clinic::lang.total_bill_income')</span>
                    <span class="info-box-number total_sell" style="display: inline-block; font-size: 22px; font-weight: bold; color: #009688;">0</span>
                    <span class="pull-right info-box-number total_sell_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;"></span>
                </div>
            </div>
        </div>

        <!-- Due Income -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom due-income-container cursor-pointer">
            <div style="position: relative; background-color: #e8f5e9; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #4caf50;"><i class="ion ion-ios-pricetag-outline"></i></span>
                <div style="text-align: left;">
                    <span style="display: block; font-size: 18px; font-weight: 600; color: #333;">@lang('clinic::lang.due_income')</span>
                    <span class="info-box-number due_income" style="display: inline-block; font-size: 22px; font-weight: bold; color: #2e7d32;">0</span>
                    <span class="pull-right info-box-number due_income_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;">(0)</span>
                </div>
            </div>
        </div>

        <!-- Due Bill -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom due-bill-container cursor-pointer">
            <div style="position: relative; background-color: #fffde7; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #ffeb3b;"><i class="ion ion-ios-paper-outline"></i></span>
                <div style="text-align: left;">
                    <span style="display: block; font-size: 18px; font-weight: 600; color: #333;">@lang('clinic::lang.bill_due')</span>
                    <span class="info-box-number due_bill" style="display: inline-block; font-size: 22px; font-weight: bold; color: #ffc107;">0</span>
                    <span class="pull-right info-box-number due_bill_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;">(0)</span>
                </div>
            </div>
        </div>

        <!-- Return / Refund -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom return-refund-container cursor-pointer">
            <div style="position: relative; background-color: #ffebee; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #f44336;"><i class="ion ion-ios-undo-outline"></i></span>
                <div style="text-align: left;">
                    <span style="display: block; font-size: 18px; font-weight: 600; color: #333;">@lang('clinic::lang.return_refund')</span>
                    <span class="info-box-number total_return_refund" style="display: inline-block; font-size: 22px; font-weight: bold; color: #d32f2f;">0</span>
                    <span class="pull-right info-box-number total_return_refund_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;">(0)</span>
                </div>
            </div>
        </div>

        <!-- Net Income -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
            <div style="position: relative; background-color: #e3f2fd; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #2196f3;"><i class="ion ion-ios-pie-outline"></i></span>
                <div style="text-align: left;">
                    <span style="display: block; font-size: 18px; font-weight: 600; color: #333;">@lang('clinic::lang.net_income')</span>
                    <span class="info-box-number net_income" style="display: inline-block; font-size: 22px; font-weight: bold; color: #1976d2;">0</span>
                </div>
            </div>
        </div>

        <!-- Cash -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom cash-container cursor-pointer">
            <div style="position: relative; background-color: #ede7f6; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #9c27b0;"><i class="fas fa-money-bill-alt"></i></span>
                <div style="text-align: left;">
                    <span style="display: block; font-size: 18px; font-weight: 600; color: #333;">Cash</span>
                    <span class="info-box-number cash" style="display: inline-block; font-size: 22px; font-weight: bold; color: #7b1fa2;">0</span>
                </div>
            </div>
        </div>

        <!-- Special Discount -->
        <div class="col-md-3 col-sm-6 col-xs-12 col-custom special_discount-container cursor-pointer">
            <div style="position: relative; background-color: #f0f4c3; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #8bc34a;"><i class="fas fa-tag"></i></span>
                <div style="text-align: left;">
                    <span style="display: block; font-size: 18px; font-weight: 600; color: #333;">@lang('clinic::lang.special_discount')</span>
                    <span class="info-box-number special_discount" style="display: inline-block; font-size: 22px; font-weight: bold; color: #689f38;">0</span>
                </div>
            </div>
        </div>
        

        <div class="row mb-4">
            <!-- Pie Chart: Total Bill Income by Service Sector -->
            <div class="col-md-6">
                @component('components.widget', [
                    'class' => 'box-primary',
                    'title' => __('clinic::lang.total_bill_income_by_service_sector'),
                ])
                    <canvas style="max-height: 380px" id="serviceSectorChart"></canvas>
                @endcomponent
            </div>

            <div class="col-md-6">
                @component('components.widget', [
                    'class' => 'box-primary',
                    'title' => __('clinic::lang.patient_per_doctor'),
                ])
                    <canvas style="max-height: 400px" id="doctorChart"></canvas>
                @endcomponent
            </div>
        </div>

        <!-- Consultation Summary -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="box box-primary" style="background-color: #f8f9fe;">
                    <div class="box-header">
                        <h3 class="box-title">Consultation Summary</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12 col-custom">
                                <div style="position: relative; background-color: #fae2e7; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                                    <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #FF4069;"><i class="fas fa-check"></i></span>
                                    <div style="text-align: left;">
                                        <span style="display: block; font-size: 18px; font-weight: 600; color: #000000;">Total Consultation</span>
                                        <span class="info-box-number total_session_amount" style="display: inline-block; font-size: 22px; font-weight: bold; color: #FF4069;">0</span>
                                        <span class="pull-right info-box-number total_session_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;">(0)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 col-sm-12 col-custom">
                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-aqua"><i
                                            class="ion ion-ios-calendar-outline"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Appointments</span>
                                        <span class="info-box-number appointments_count">0</span>
                                    </div>
                                </div>

                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-green"><i class="ion ion-ios-people-outline"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">New Patients</span>
                                        <span class="info-box-number new_patients_count"></span>
                                    </div>
                                </div>

                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-yellow"><i
                                            class="ion ion-ios-refresh-outline"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Follow-up Patients</span>
                                        <span class="info-box-number followup_patients_count"></span>
                                    </div>
                                </div>

                                <div class="info-box info-box-new-style">
                                    <span class="info-box-icon bg-red"><i class="ion ion-ios-people-outline"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Old Patients</span>
                                        <span class="info-box-number old_patients_count"></span>
                                    </div>
                                </div>

                            </div>
                            <div class="col-md-6 col-sm-12 col-custom">
                                <div id="SummaryCardsContainer"></div>
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Consultation Summary Chart'])
                    <canvas style="max-height: 400px" id="todaySummaryChart"></canvas>
                @endcomponent
            </div>
        </div>


        <!-- Therapy Services -->
        {{-- <div class="row mb-4">
            <div class="col-md-6">
                <div class="box box-primary" style="background-color: #f8f9fe;">
                    <div class="box-header">
                        <h3 class="box-title">Therapy Services</h3>
                    </div>
                    <div class="box-body">
                        <div class="col-md-12 col-custom">
                            <div style="position: relative; background-color: #d4f1f1; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: rgb(91, 177, 233)"><i class="ion ion-ios-medkit-outline"></i></span>
                                <div style="text-align: left;">
                                    <span style="display: block; font-size: 18px; font-weight: 600; color: #000000;">Total Therapy</span>
                                    <span class="info-box-number total_therapy_amount" style="display: inline-block; font-size: 22px; font-weight: bold; color: #4BC0C0">0</span>
                                    <span class="pull-right info-box-number therapy_bill_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;">(0)</span>
                                    <span class="pull-right info-box-number therapy_item_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;">(0)</span>
                                </div>
                            </div>
                        </div>
                        <div id="therapyCardsContainer"></div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart: Therapy Service -->
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Therapy Service Chart'])
                    <canvas style="max-height: 400px" id="therapySectorChart"></canvas>
                @endcomponent
            </div>
        </div>

        <!-- Test Services -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="box box-primary" style="background-color: #f8f9fe;">
                    <div class="box-header">
                        <h3 class="box-title">Test Services Category</h3>
                    </div>
                    <div class="box-body">
                            <div class="col-md-12 col-custom">
                                <div style="position: relative; background-color: #f6efdf; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                                    <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #f6bb24"><i class="ion ion-ios-flask-outline"></i></span>
                                    <div style="text-align: left;">
                                        <span style="display: block; font-size: 18px; font-weight: 600; color: #000000;">Total Tests</span>
                                        <span class="info-box-number total_test_amount" style="display: inline-block; font-size: 22px; font-weight: bold; color: #f3b416">0</span>
                                        <span class="pull-right info-box-number test_bill_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;">(0)</span>
                                        <span class="pull-right info-box-number test_item_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;">(0)</span>
                                    </div>
                                </div>
                            </div>
                        <div id="testCardsContainer"></div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart: Test Service -->
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Test Services Category Chart'])
                    <canvas style="max-height: 400px" id="testSectorChart"></canvas>
                @endcomponent
            </div>
        </div>

        <!-- IPD Services -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="box box-primary" style="background-color: #f8f9fe;">
                    <div class="box-header">
                        <h3 class="box-title">IPD Services</h3>
                    </div>
                    <div class="box-body">
                        <div class="col-md-12 col-custom">
                            <div style="position: relative; background-color: #d9ecfa; border-radius: 8px; padding: 20px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); margin-bottom: 20px;">
                                <span style="position: absolute; top: 10px; right: 10px; font-size: 24px; color: #43a9ed"><i class="ion ion-ios-home-outline"></i></span>
                                <div style="text-align: left;">
                                    <span style="display: block; font-size: 18px; font-weight: 600; color: #000000;">Total IPD</span>
                                    <span class="info-box-number total_ipd_amount" style="display: inline-block; font-size: 22px; font-weight: bold; color: #36A2EB">0</span>
                                    <span class="pull-right info-box-number total_ipd_count" style="display: inline-block; padding-left: 10px; font-size: 16px; color: #777;">(0)</span>
                                </div>
                            </div>
                        </div>
                        <div id="IpdCardsContainer"></div>
                    </div>
                </div>
            </div>

            <!-- Pie Chart: IPD Service -->
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'IPD Service Chart'])
                    <canvas style="max-height: 400px" id="IpdSectorChart"></canvas>
                @endcomponent
            </div>
        </div> --}}


        <!-- sales chart start -->
        @if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view'))
            @if (!empty($all_locations))
                <div class="row">
                    <div class="col-sm-12">
                        @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_last_30_days')])
                            {!! $sells_chart_1->container() !!}
                        @endcomponent
                    </div>
                </div>
            @endif
            @if (!empty($all_locations))
                <div class="row">
                    <div class="col-sm-12">
                        @component('components.widget', ['class' => 'box-primary', 'title' => __('home.sells_current_fy')])
                            {!! $sells_chart_2->container() !!}
                        @endcomponent
                    </div>
                </div>
            @endif
        @endif
        <!-- sales chart end -->

        <!-- Sales Due Report -->
        <div class="row mb-4">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Sales Due Report'])
                <table style="width: 100%" class="table table-bordered table-striped" id="credit_sales_report_table">
                    <thead>
                        <tr>
                            <th>Bill date</th>
                            <th>@lang('sale.invoice_no')</th>
                            <th>Patient</th>
                            <th>Total Amount</th>
                            <th>Due amount</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="3"><strong>@lang('sale.total'):</strong></td>
                            <td class="footer_total_amount"></td>
                            <td class="footer_total_due"></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
                @endcomponent
            </div>
        </div>

    </section>
    @endif
@endsection

@section('javascript')
    @if (!empty($all_locations))
        {!! $sells_chart_1->script() !!}
        {!! $sells_chart_2->script() !!}
    @endif
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            if ($('#dashboard_date_filter').length == 1) {
                dateRangeSettings.startDate = moment();
                dateRangeSettings.endDate = moment();
                $('#dashboard_date_filter').daterangepicker(dateRangeSettings, function(start, end) {
                    $('#dashboard_date_filter span').html(
                        start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
                    );
                    update_statistics(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
                });

                update_statistics(moment().format('YYYY-MM-DD'), moment().format('YYYY-MM-DD'));
            }
        });

        // Use `apply.daterangepicker` instead of `.change()`
        $('#dashboard_date_filter').on('apply.daterangepicker', function(ev, picker) {
            var start = picker.startDate.format('YYYY-MM-DD');
            var end = picker.endDate.format('YYYY-MM-DD');
            $('#dashboard_date_filter span').html(start + ' ~ ' + end);
            update_statistics(start, end);
            credit_sales_report_table.ajax.reload();
        });

        var serviceSectorChart; // Declare a global variable for the chart
        var todaySummaryChart; // Declare a global variable for the chart
        var therapySectorChart; // Declare a global variable for the chart
        var testSectorChart; // Declare a global variable for the chart
        var IpdSectorChart; // Declare a global variable for the chart
        var doctorChart; // Declare a global variable for the chart
        const hexColors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBD0', '#008000', '#800080', '#FF1493', '#1E90FF', '#FF8C00', '#32CD32', '#808000', '#00CED1'];

        function update_statistics(start, end) {
            var location_id = '';
            if ($('#dashboard_location').length > 0) {
                location_id = $('#dashboard_location').val();
            }
            var data = {
                start: start,
                end: end,
                location_id: location_id
            };

            // Show loader for all metrics
            var loader = '<i class="fas fa-sync fa-spin fa-fw margin-bottom"></i>';
            $('.total_sell_count, .total_sell').html(loader);
            $('.due_income_count, .due_income').html(loader);
            $('.due_bill_count, .due_bill').html(loader);
            $('.total_return_refund_count, .total_return_refund').html(loader);
            $('.net_income').html(loader);
            $('.cash').html(loader);
            $('.special_discount').html(loader);
            $('.appointments_count').html(loader);
            $('.old_patients_count').html(loader);
            $('.followup_patients_count').html(loader);
            $('.new_patients_count').html(loader);
            $('.total_test_amount').html(loader);
            $('.test_item_count').html(loader);
            $('.test_bill_count').html(loader);
            $('.total_therapy_amount').html(loader);
            $('.therapy_item_count').html(loader);
            $('.therapy_bill_count').html(loader);
            $('.total_ipd_count').html(loader);
            $('.total_ipd_amount').html(loader);
            $('.total_session_count').html(loader);
            $('.total_session_amount').html(loader);

            // Fetch data via AJAX
            $.ajax({
                method: 'get',
                url: '/clinic/get-totals',
                dataType: 'json',
                data: data,
                success: function(data) {
                    console.log(data);

                    // Update metrics in the Blade view
                    $('.total_sell_count').html(data.count_total_bill_income + ' bill');
                    $('.total_sell').html(__currency_trans_from_en(data.total_bill_income, true));

                    $('.total_sell_container').click(function(e) {
                        e.preventDefault();

                        let details = data.bill_income_per_customer; // This should be an array of objects

                        let tableRows = '';
                        if (Array.isArray(details)) {
                            tableRows = details.map(function(row, index) {
                                return `<tr>
                                            <td>${index + 1}</td>
                                            <td>
                                                <a data-href="${base_path}/clinic-sell/${row.id}" class="btn-modal cursor-pointer" data-container=".view_modal">${row.invoice_no}</a>
                                            </td>
                                            <td>${row.customer_name || '-' } (${row.mobile})</td>
                                            <td>${__currency_trans_from_en(row.final_total || 0, true)}</td>
                                            <th>${row.transaction_date}</th>
                                        </tr>`;
                            }).join('');
                        }

                        Swal.fire({
                            title: 'Bill Details',
                            width: '80%',
                            customClass: {
                                popup: 'scrollable-swal'
                            },
                            html: `<div style="max-height: 70vh; overflow-y: auto;">
                                    <table class="table table-bordered table-striped">
                                        <thead class="bg-gray font-17">
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice No</th>
                                                <th>Customer Name</th>
                                                <th>Bill Amount</th>
                                                <th>Bill Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-left font-17">
                                            ${tableRows}
                                        </tbody>
                                        <tfoot class="bg-gray font-17">
                                            <tr>
                                                <th colspan="3">Total</th>
                                                <th>${__currency_trans_from_en(data.total_bill_income, true)}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>`,
                            showCloseButton: true,
                            confirmButtonText: 'Close'
                        })
                    });

                    $('.due_income_count').html(data.count_due_income + ' bill');
                    $('.due_income').html(__currency_trans_from_en(data.due_income, true));

                    $('.due-income-container').click(function(e) {
                        e.preventDefault();

                        let details = data.due_income_details; // This should be an array of objects

                        let tableRows = '';
                        if (Array.isArray(details)) {
                            tableRows = details.map(function(row, index) {
                                return `<tr>
                                            <td>${index + 1}</td>
                                            <td>
                                                <a data-href="${base_path}/clinic-sell/${row.id}" class="btn-modal cursor-pointer" data-container=".view_modal">${row.invoice_no}</a>
                                            </td>
                                            <td>${row.customer_name || '-' } (${row.mobile})</td>
                                            <td>${__currency_trans_from_en(row.final_total || 0, true)}</td>
                                            <td>${__currency_trans_from_en(row.amount || 0, true)} (${ row.method == 'custom_pay_1' ? 'bkash' : row.method })</td>
                                            <td>${row.transaction_date || '-'}</td>
                                            <td>${row.payment_date || '-'}</td>
                                        </tr>`;
                            }).join('');
                        }

                        Swal.fire({
                            title: 'Due Income Details',
                            width: '80%',
                            customClass: {
                                popup: 'scrollable-swal'
                            },
                            html: `
                                <div style="max-height: 70vh; overflow-y: auto;">
                                    <table class="table table-bordered table-striped">
                                        <thead class="bg-gray font-17">
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice No</th>
                                                <th>Customer</th>
                                                <th>Final Total</th>
                                                <th>Paid</th>
                                                <th>Bill Date</th>
                                                <th>Payment Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-left font-17">
                                            ${tableRows || '<tr><td colspan="6" class="text-center">No data found</td></tr>'}
                                        </tbody>
                                        <tfoot class="bg-gray font-17">
                                            <tr>
                                                <th class="text-center" colspan="4">Total Due Income:</th>
                                                <th>${__currency_trans_from_en(data.due_income, true)}</th>
                                                <th colspan="2"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            `,
                            showCloseButton: true,
                            confirmButtonText: 'Close'
                        });
                    });

                    $('.due_bill_count').html(data.count_due_bill + ' bill');
                    $('.due_bill').html(__currency_trans_from_en(data.due_bill, true));

                    $('.due-bill-container').click(function(e) {
                        e.preventDefault();

                        let details = data.due_bill_details; // This should be an array of objects

                        let tableRows = '';
                        if (Array.isArray(details)) {
                            tableRows = details.map(function(row, index) {
                                return `<tr>
                                            <td>${index + 1}</td>
                                            <td>
                                                <a data-href="${base_path}/clinic-sell/${row.id}" class="btn-modal cursor-pointer" data-container=".view_modal">${row.invoice_no}</a>
                                            </td>
                                            <td>${row.customer_name} (${row.mobile})</td>
                                            <td>${__currency_trans_from_en(row.final_total, true)}</td>
                                            <td>${__currency_trans_from_en(row.total_paid, true)}</td>
                                            <td>${__currency_trans_from_en(row.final_total - row.total_paid, true)}</td>
                                            <td>${row.transaction_date}</td>
                                        </tr>`;
                            }).join('');
                        }

                        Swal.fire({
                            title: 'Due Bill Details',
                            width: '80%',
                            customClass: {
                                popup: 'scrollable-swal'
                            },
                            html: `
                                <div style="max-height: 70vh; overflow-y: auto;">
                                    <table class="table table-bordered table-striped">
                                        <thead class="bg-gray font-17">
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice No</th>
                                                <th>Customer</th>
                                                <th>Final Total</th>
                                                <th>Total Paid</th>
                                                <th>Due</th>
                                                <th>Bill Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-left font-17">
                                            ${tableRows || '<tr><td colspan="6" class="text-center">No data found</td></tr>'}
                                        </tbody>
                                        <tfoot class="bg-gray font-17">
                                            <tr>
                                                <th class="text-center" colspan="5">Total Due Bill:</th>
                                                <th>${__currency_trans_from_en(data.due_bill, true)}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            `,
                            showCloseButton: true,
                            confirmButtonText: 'Close'
                        })
                    });

                    $('.total_return_refund_count').html(data.count_return_refund + ' bill');
                    $('.total_return_refund').html(__currency_trans_from_en(data.return_refund, true));

                    $('.return-refund-container').click(function(e) {
                        e.preventDefault();

                        let details = data.return_refund_details; // This should be an array of objects

                        let tableRows = '';
                        if (Array.isArray(details)) {
                            tableRows = details.map(function(row, index) {
                                return `<tr>
                                            <td>${index + 1}</td>
                                            <td>
                                                <a data-href="${base_path}/clinic-sell/${row.id}" class="btn-modal cursor-pointer" data-container=".view_modal">${row.invoice_no}</a>
                                            </td>
                                            <td>${row.customer_name} (${row.mobile})</td>
                                            <td>${__currency_trans_from_en(row.amount, true)} (${ row.method == 'custom_pay_1' ? 'bkash' : row.method })</td>
                                            <td>${row.transaction_date}</td>
                                        </tr>`;
                            }).join('');
                        }

                        Swal.fire({
                            title: 'Return Refund Details',
                            width: '80%',
                            customClass: {
                                popup: 'scrollable-swal'
                            },
                            html: `
                                <div style="max-height: 70vh; overflow-y: auto;">
                                    <table class="table table-bordered table-striped">
                                        <thead class="bg-gray font-17">
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice No</th>
                                                <th>Customer</th>
                                                <th>Return Total</th>
                                                <th>Return Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-left font-17">
                                            ${tableRows || '<tr><td colspan="5" class="text-center">No data found</td></tr>'}
                                        </tbody>
                                        <tfoot class="bg-gray font-17">
                                            <tr>
                                                <th class="text-center" colspan="3">Total Return Refund:</th>
                                                <th>${__currency_trans_from_en(data.return_refund, true)}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            `,
                            showCloseButton: true,
                            confirmButtonText: 'Close'
                        })
                    });

                    $('.net_income').html(__currency_trans_from_en(data.net_income, true));
                    $('.cash').html(__currency_trans_from_en(data.cash_income, true));

                    $('.cash-container').click(function(e) {
                        e.preventDefault();

                        let details = data.current_cash_income_details;
                        let due_details = data.due_cash_income_details;

                        // Merge both arrays
                        let mergedDetails = [...details, ...due_details];

                        let tableRows = '';
                        if (Array.isArray(mergedDetails)) {
                            tableRows = mergedDetails.map(function(row, index) {
                                return `<tr>
                                            <td>${index + 1}</td>
                                            <td>
                                                <a data-href="${base_path}/clinic-sell/${row.id}" class="btn-modal cursor-pointer" data-container=".view_modal">${row.invoice_no}</a>
                                            </td>
                                            <td>${row.customer_name} (${row.mobile})</td>
                                            <td>${__currency_trans_from_en(row.final_total, true)}</td>
                                            <td>${__currency_trans_from_en(row.amount, true)}</td>
                                            <td>${row.transaction_date}</td>
                                            <td>${row.payment_date}</td>
                                        </tr>`;
                            }).join('');
                        }

                        Swal.fire({
                            title: 'Cash Details',
                            width: '80%',
                            customClass: {
                                popup: 'scrollable-swal'
                            },
                            html: `
                                <div style="max-height: 70vh; overflow-y: auto;">
                                    <table class="table table-bordered table-striped">
                                        <thead class="bg-gray font-17">
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice No</th>
                                                <th>Customer</th>
                                                <th>Final Total</th>
                                                <th>Cash</th>
                                                <th>Bill Date</th>
                                                <th>Payment Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-left font-17">
                                            ${tableRows || '<tr><td colspan="6" class="text-center">No data found</td></tr>'}
                                        </tbody>
                                        <tfoot class="bg-gray font-17">
                                            <tr>
                                                <th class="text-center" colspan="4">Total Cash:</th>
                                                <th>${__currency_trans_from_en(data.cash_income, true)}</th>
                                                <th></th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            `,
                            showCloseButton: true,
                            confirmButtonText: 'Close'
                        });
                    });


                    $('.special_discount').html(__currency_trans_from_en(data.special_discount, true));

                    $('.special_discount-container').click(function(e) {
                        e.preventDefault();

                        let details = data.special_discount_details; // This should be an array of objects

                        let tableRows = '';
                        if (Array.isArray(details)) {
                            tableRows = details.map(function(row, index) {
                                return `<tr>
                                            <td>${index + 1}</td>
                                            <td>
                                                <a data-href="${base_path}/clinic-sell/${row.id}" class="btn-modal cursor-pointer" data-container=".view_modal">${row.invoice_no}</a>
                                            </td>
                                            <td>${row.sub_type}</td>
                                            <td>${row.customer_name} (${row.mobile})</td>
                                            <td>${__currency_trans_from_en(row.final_total, true)}</td>
                                            <td>${__currency_trans_from_en(row.special_discount, true)}</td>
                                            <td>${row.transaction_date}</td>
                                        </tr>`;
                            }).join('');
                        }

                        Swal.fire({
                            title: 'Special Discount Details',
                            width: '80%',
                            customClass: {
                                popup: 'scrollable-swal'
                            },
                            html: `
                                <div style="max-height: 70vh; overflow-y: auto;">
                                    <table class="table table-bordered table-striped">
                                        <thead class="bg-gray font-17">
                                            <tr>
                                                <th>#</th>
                                                <th>Invoice No</th>
                                                <th>Bill Type</th>
                                                <th>Customer</th>
                                                <th>Final Total</th>
                                                <th>Special Discount</th>
                                                <th>Bill Date</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-left font-17">
                                            ${tableRows || '<tr><td colspan="6" class="text-center">No data found</td></tr>'}
                                        </tbody>
                                        <tfoot class="bg-gray font-17">
                                            <tr>
                                                <th class="text-center" colspan="5">Total Special Discount:</th>
                                                <th>${__currency_trans_from_en(data.special_discount, true)}</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            `,
                            showCloseButton: true,
                            confirmButtonText: 'Close'
                        })
                    });

                    $('.total_test_amount').html(__currency_trans_from_en(data.total_test_amount, true));
                    $('.total_therapy_amount').html(__currency_trans_from_en(data.total_therapy_amount, true));
                    $('.total_ipd_amount').html(__currency_trans_from_en(data.total_ipd_amount, true));
                    $('.total_session_amount').html(__currency_trans_from_en(data.total_session_amount, true));
                    $('.appointments_count').html(data.appointments_count);
                    $('.old_patients_count').html(data.old_patients_count);
                    $('.followup_patients_count').html(data.followup_patients_count);
                    $('.new_patients_count').html(data.new_patients_count);
                    $('.test_item_count').html(data.test_item_count + ' tests,');
                    $('.test_bill_count').html(data.test_bill_count + ' bill');
                    $('.therapy_item_count').html(data.therapy_item_count + ' therapies,');
                    $('.therapy_bill_count').html(data.therapy_bill_count + ' bill');
                    $('.total_ipd_count').html(data.total_ipd_count + ' bill');
                    $('.total_session_count').html(data.total_session_count + ' bill');


                    var sessionCardsHtml = '';
                    data.session_information.forEach(function(session, index) {
                        var bgColor = hexColors[index % 7] || '#f39c12';
                        sessionCardsHtml += `
                                <div class="info-box info-box-new-style">
                                    <span style="background-color: ${bgColor}" class="info-box-icon"><i class="fas fa-check"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">${session.name}</span>
                                        <span class="info-box-number" style="display: inline-block;">${__currency_trans_from_en(session.amount, true)}</span>
                                        <span class="info-box-number pull-right" style="display: inline-block; padding-right: 15px;">${session.count}</span>
                                    </div>
                                </div>`;
                    });
                    
                    $('#SummaryCardsContainer').html(sessionCardsHtml);

                    // Prepare data for the pie chart
                    var chartLabels = [];
                    var chartData = [];
                    var serviceCounts = []; // Store counts for use in the legend
                    data.service_details.forEach(function(service) {
                        chartLabels.push(service.name);
                        chartData.push(service.amount);
                        serviceCounts.push(service.count); // Store the count
                    });

                    // Destroy previous chart if it exists
                    if (serviceSectorChart) {
                        serviceSectorChart.destroy();
                    }

                    // Generate Pie chart
                    var ctx = document.getElementById('serviceSectorChart').getContext('2d');
                    serviceSectorChart = new Chart(ctx, {
                        type: 'doughnut',
                        data: {
                            labels: chartLabels,
                            datasets: [{
                                data: chartData,
                                backgroundColor: hexColors,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'left',
                                    labels: {
                                        // Customize the legend labels
                                        font: {
                                            size: 18
                                        },
                                        generateLabels: function(chart) {
                                            var data = chart.data;
                                            if (data.labels.length && data.datasets.length) {
                                                return data.labels.map(function(label, i) {
                                                    var meta = chart.getDatasetMeta(0);
                                                    var style = meta.controller.getStyle(i);
                                                    return {
                                                        text: `${label}: ${__currency_trans_from_en(data.datasets[0].data[i], true)} (${serviceCounts[i]})`,
                                                        fillStyle: style.backgroundColor,
                                                        strokeStyle: style.borderColor,
                                                        lineWidth: style.borderWidth,
                                                        hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                                        index: i
                                                    };
                                                });
                                            }
                                            return [];
                                        }
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            let label = tooltipItem.label || '';
                                            let value = tooltipItem.raw || 0;
                                            let count = serviceCounts[tooltipItem.dataIndex];
                                            return `${label}: ${__currency_trans_from_en(value, true)} (${count})`;
                                        }
                                    }
                                }
                            }
                        }
                    });



                    // Prepare data for the pie chart
                    var summaryChartLabels = [];
                    var summaryChartData = [];
                    data.patient_appointment.forEach(function(appointment) {
                        summaryChartLabels.push(appointment.patient_type);
                        summaryChartData.push(appointment.count);
                    });

                    // Destroy previous chart if it exists
                    if (todaySummaryChart) {
                        todaySummaryChart.destroy();
                    }

                    // Generate Pie chart
                    var ctx = document.getElementById('todaySummaryChart').getContext('2d');
                    todaySummaryChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: summaryChartLabels,
                            datasets: [{
                                data: summaryChartData,
                                backgroundColor: hexColors,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'left',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            let label = tooltipItem.label || '';
                                            let value = tooltipItem.raw || 0;
                                            return label + ': ' + value;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    var therapyCardsHtml = '';
                    data.therapy.forEach(function(tpy, index) {
                        // Define background color logic or use a simple color
                        var bgColor = hexColors[index % 7] || '#f39c12';

                        therapyCardsHtml += `
                            <div class="col-md-6 col-sm-12 col-custom">
                                <div class="info-box info-box-new-style">
                                    <span style="background-color: ${bgColor}" class="info-box-icon"><i class="ion ion-ios-medkit-outline"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">${tpy.name}</span>
                                        <span class="info-box-number" style="display: inline-block;">${__currency_trans_from_en(tpy.amount, true)}</span>
                                        <span class="info-box-number pull-right" style="display: inline-block; padding-right: 15px;">${parseInt(tpy.therapy_item_count)}</span>
                                    </div>
                                </div>
                            </div>`;
                    });

                    $('#therapyCardsContainer').html(therapyCardsHtml);

                    // Prepare data for the pie chart
                    var therapyChartLabels = [];
                    var therapyChartData = [];
                    data.therapy.forEach(function(tpy) {
                        therapyChartLabels.push(tpy.name);
                        therapyChartData.push(tpy.amount);

                    });

                    // Destroy previous chart if it exists
                    if (therapySectorChart) {
                        therapySectorChart.destroy();
                    }

                    // Generate Pie chart
                    var ctx = document.getElementById('therapySectorChart').getContext('2d');
                    therapySectorChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: therapyChartLabels,
                            datasets: [{
                                data: therapyChartData,
                                backgroundColor: hexColors,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'left',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            let label = tooltipItem.label || '';
                                            let value = tooltipItem.raw || 0;
                                            return label + ': ' + __currency_trans_from_en(value,
                                                true);
                                        }
                                    }
                                }
                            }
                        }
                    });



                    var testCardsHtml = '';
                    data.tests.forEach(function(test, index) {
                        // Define background color logic or use a simple color
                        var bgColor = hexColors[index % 7] || '#f39c12';

                        testCardsHtml += `
                            <div class="col-md-6 col-sm-12 col-custom">
                                <div class="info-box info-box-new-style">
                                    <span style="background-color: ${bgColor}" class="info-box-icon"><i class="ion ion-ios-flask-outline"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">${test.name}</span>
                                        <span class="info-box-number" style="display: inline-block;">${__currency_trans_from_en(test.amount, true)}</span>
                                        <span class="info-box-number pull-right" style="display: inline-block; padding-right: 15px;">${parseInt(test.test_item_count)}</span>
                                    </div>
                                </div>
                            </div>`;
                    });

                    $('#testCardsContainer').html(testCardsHtml);

                    // Prepare data for the pie chart
                    var testChartLabels = [];
                    var testChartData = [];
                    data.tests.forEach(function(test) {
                        testChartLabels.push(test.name);
                        testChartData.push(test.amount);

                    });

                    // Destroy previous chart if it exists
                    if (testSectorChart) {
                        testSectorChart.destroy();
                    }

                    // Generate Pie chart
                    var ctx = document.getElementById('testSectorChart').getContext('2d');
                    testSectorChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: testChartLabels,
                            datasets: [{
                                data: testChartData,
                                backgroundColor: hexColors,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'left',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            let label = tooltipItem.label || '';
                                            let value = tooltipItem.raw || 0;
                                            return label + ': ' + __currency_trans_from_en(value,
                                                true);
                                        }
                                    }
                                }
                            }
                        }
                    });


                    var ipdCardsHtml = '';
                    data.ipds.forEach(function(ipd, index) {
                        // Define background color logic or use a simple color
                        var bgColor = hexColors[index % 7] || '#f39c12';

                        ipdCardsHtml += `
                            <div class="col-md-6 col-sm-12 col-custom">
                                <div class="info-box info-box-new-style">
                                    <span style="background-color: ${bgColor}" class="info-box-icon"><i class="ion ion-ios-home-outline"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">${ipd.name}</span>
                                        <span class="info-box-number" style="display: inline-block;">${__currency_trans_from_en(ipd.amount, true)}</span>
                                        <span class="info-box-number pull-right" style="display: inline-block; padding-right: 15px;">${ipd.count}</span>
                                    </div>
                                </div>
                            </div>`;
                    });

                    $('#IpdCardsContainer').html(ipdCardsHtml);

                    // Prepare data for the pie chart
                    var ipdChartLabels = [];
                    var ipdChartData = [];
                    data.ipds.forEach(function(ipd) {
                        ipdChartLabels.push(ipd.name);
                        ipdChartData.push(ipd.amount);

                    });

                    // Destroy previous chart if it exists
                    if (IpdSectorChart) {
                        IpdSectorChart.destroy();
                    }

                    // Generate Pie chart
                    var ctx = document.getElementById('IpdSectorChart').getContext('2d');
                    IpdSectorChart = new Chart(ctx, {
                        type: 'pie',
                        data: {
                            labels: ipdChartLabels,
                            datasets: [{
                                data: ipdChartData,
                                backgroundColor: hexColors,
                            }]
                        },
                        options: {
                            responsive: true,
                            plugins: {
                                legend: {
                                    position: 'left',
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(tooltipItem) {
                                            let label = tooltipItem.label || '';
                                            let value = tooltipItem.raw || 0;
                                            return label + ': ' + __currency_trans_from_en(value,
                                                true);
                                        }
                                    }
                                }
                            }
                        }
                    });


                    // Destroy previous chart if it exists
                    if (doctorChart) {
                        doctorChart.destroy();
                    }

                    // Create chart
                    var ctx = document.getElementById('doctorChart').getContext('2d');
                    doctorChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: data.doctors_patients.labels,
                            datasets: [{
                                label: 'Patients',
                                data: data.doctors_patients.patients,
                                backgroundColor: data.doctors_patients.backgroundColors,
                                borderColor: data.doctors_patients.borderColors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: { beginAtZero: true, title: { display: true, text: 'Patients' } },
                                x: { title: { display: true, text: 'Doctors' } }
                            },
                            plugins: {
                                legend: { position: 'top' },
                                title: { display: false, text: 'Patient Distribution Across Doctors' }
                            }
                        }
                    });

                },
            });
        }


        $(document).ready(function() {
            // Initialize DataTable
            credit_sales_report_table = $('#credit_sales_report_table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                ajax: {
                    url: "/clinic/get-sale-report",
                    data: function(d) {
                        d.start_date = $('#dashboard_date_filter').data('daterangepicker').startDate.format('YYYY-MM-DD');
                        d.end_date = $('#dashboard_date_filter').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    }
                },
                columns: [
                    { data: 'sale_date', name: 'transactions.transaction_date' },
                    { data: 'invoice_no', name: 'invoice_no' },
                    { data: 'customer_name', name: 'c.name' },
                    { data: 'total_amount', name: 'total_amount', searchable: false },
                    { data: 'total_due', name: 'total_due', searchable: false },
                    { data: 'action', name: 'action', searchable: false, orderable: false},
                ],
                "footerCallback": function(row, data, start, end, display) {
                    var total_amount = 0;
                    var total_due = 0;

                    for (var r in data) {
                        total_amount += $(data[r].total_amount).data('orig-value') ? parseFloat($(data[r].total_amount).data('orig-value')) : 0;
                        total_due += $(data[r].total_due).data('orig-value') ? parseFloat($(data[r].total_due).data('orig-value')) : 0;
                    }

                    $('.footer_total_amount').html(__currency_trans_from_en(total_amount));
                    $('.footer_total_due').html(__currency_trans_from_en(total_due));
                }
            });

        });
    </script>
@endsection
