@extends('clinic::layouts.app2')
@section('title', 'Overall Report')

@section('content')
    <section class="content-header">
        <h1>@lang('Account Report')
            <small>@lang('Manage your register account report')</small>
        </h1>
    </section>

<section class="content">
    <div class="row">
        <div class="col-md-12 mt-1">
            @component('components.widget', ['title' => 'Filter'])
                {!! Form::open([
                    'url' => action([\App\Http\Controllers\ReportController::class, 'getStockReport']),
                    'method' => 'get',
                    'id' => 'register_report_filter_form',
                ]) !!}
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('register_user_id_overall', __('report.user') . ':') !!}
                        {!! Form::select('register_user_id_overall', $users, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('report.all_users'),
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('register_status', __('sale.status') . ':') !!}
                        {!! Form::select(
                            'register_status',
                            ['open' => __('cash_register.open'), 'close' => __('cash_register.close')],
                            null,
                            ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('report.all')],
                        ) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('register_report_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('register_report_date_range', null, [
                            'placeholder' => __('lang_v1.select_a_date_range'),
                            'class' => 'form-control',
                            'id' => 'register_report_date_range',
                            'readonly',
                        ]) !!}
                    </div>
                </div>
                {!! Form::close() !!}
            @endcomponent
            @component('components.widget')
            <div id="reportDataShow">

            </div>
            @endcomponent
                
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
<script>
    $(document).ready(function() {
        var moment_date_format = 'YYYY-MM-DD'; // Adjust according to your desired format

        // Initialize date range picker with today as default selection
        var dateRangeSettings = {
            ranges: ranges,
            startDate: moment(), // Set today's date as the start date
            endDate: moment(), // Set today's date as the end date
            locale: {
                cancelLabel: LANG.clear,
                applyLabel: LANG.apply,
                customRangeLabel: LANG.custom_range,
                format: moment_date_format,
                toLabel: '~',
            },
            maxDate: moment()
        };

        $('#register_report_date_range').daterangepicker(
            dateRangeSettings,
            function(start, end) {
                $('#register_report_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                getOverallRegisterDetails(); // Fetch data whenever date range is selected
            }
        );

        $('#register_report_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $('#register_report_date_range').val(''); // Clear the input value when cancelled
        });

        // Set the initial value of the date range input to today's date
        $('#register_report_date_range').val(moment().format(moment_date_format) + ' ~ ' + moment().format(moment_date_format));

        // Initial call to populate data
        getOverallRegisterDetails();

        // Fetch data on user selection change
        $('#register_user_id_overall, #register_status').change(function() {
            getOverallRegisterDetails();
        });

        function getOverallRegisterDetails() {
            $('#reportDataShow').html('<p>Loading...</p>'); // Show loading message
            $.ajax({
                url: '{{ route("overall.register.data") }}',
                method: 'GET',
                data: {
                    user_id: $('#register_user_id_overall').val(),
                    status: $('#register_status').val(),
                    start_date: $('input#register_report_date_range')
                        .data('daterangepicker')
                        .startDate.format('YYYY-MM-DD'),
                    end_date: $('input#register_report_date_range')
                        .data('daterangepicker')
                        .endDate.format('YYYY-MM-DD')
                },
                success: function(response) {
                    if (response.content) {
                        $('#reportDataShow').html(response.content);
                    } else {
                        $('#reportDataShow').html('<p>No content returned</p>');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    $('#reportDataShow').html('<p>Error fetching data</p>');
                }
            });
        }


        // Initial call to populate data
        getOverallRegisterDetails();

        // Fetch data on user selection change
        $('#register_user_id_overall, #register_status, #register_report_date_range').change(function() {
            getOverallRegisterDetails();
        });
    });
</script>
@endsection

<style type="text/css">
    @media print {
        body {
            margin: 0;
            padding: 0;
        }
    }
</style>