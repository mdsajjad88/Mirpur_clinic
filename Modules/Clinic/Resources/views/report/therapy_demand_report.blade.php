@extends('clinic::layouts.app2')

@section('title', 'Therapy Demand Report')

@section('css')
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
        flex-wrap: nowrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    .summary-row .small-box {
        flex: 1 1 0;
        max-width: 24%;
        color: #fff;
        position: relative;
        display: block;
        padding: 10px;
        border-radius: 5px;
    }
    .summary-row .small-box .inner h3 {
        font-size: 28px;
        margin: 0 0 10px 0;
    }
    .summary-row .small-box .icon {
        position: absolute;
        top: 10px;
        right: 10px;
        font-size: 40px;
    }
</style>
@endsection

@section('content')
@include('clinic::report.layouts.nav')
<section class="content-header">
    <h1>Therapy Analysis Report</h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('doctor_id', 'Doctor:') !!}
                    {!! Form::select('doctor_id', $doctors, null, [
                        'class' => 'form-control select2',
                        'id' => 'doctor_id',
                        'placeholder' => 'All Doctors',
                        'style' => 'width: 100%',
                    ]) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('date_range', 'Date Range:') !!}
                    {!! Form::text('date_range', null, [
                        'class' => 'form-control',
                        'id' => 'date_range',
                        'readonly',
                        'placeholder' => 'Select date range',
                    ]) !!}
                </div>
            </div>
        </div>
    @endcomponent

    <!-- Summary Cards -->
    <div class="summary-row">
        <div class="small-box bg-aqua">
            <div class="inner">
                <h3 id="totalTherapies">0</h3>
                <p>Total Therapies</p>
            </div>
            <div class="icon">
                <i class="fa fa-pills"></i>
            </div>
        </div>
        <div class="small-box bg-primary">
            <div class="inner">
                <h3 id="totalPrescribed">0</h3>
                <p>Total Prescribed</p>
            </div>
            <div class="icon">
                <i class="fa fa-file-prescription"></i>
            </div>
        </div>
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="totalSold">0</h3>
                <p>Total Sold</p>
            </div>
            <div class="icon">
                <i class="fa fa-shopping-cart"></i>
            </div>
        </div>
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="averageConversion">0%</h3>
                <p>Average Conversion Rate</p>
            </div>
            <div class="icon">
                <i class="fa fa-percentage"></i>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Doctor Prescription vs Sales</h3>
                </div>
                <div class="box-body">
                    <div class="chart-container">
                        <canvas id="doctorConversionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Top 10 Therapies by Sales</h3>
                </div>
                <div class="box-body">
                    <div class="chart-container">
                        <canvas id="topTherapiesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Therapy Demand Analysis Details'])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="demand_table">
                <thead>
                    <tr>
                        <th>Therapy Name</th>
                        <th>Prescription Count</th>
                        <th>Sold Count</th>
                        <th>Fulfillment Rate</th>
                        <th>Breakdown by Doctors</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    @endcomponent
</section>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    // Initialize date range picker
    $('#date_range').daterangepicker({
        locale: { format: 'YYYY-MM-DD' },
        startDate: moment().subtract(6, 'days'),
        endDate: moment(),
        ranges: {
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'Last 90 Days': [moment().subtract(89, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Last 3 Months': [moment().subtract(3, 'month').startOf('month'), moment().subtract(3, 'month').endOf('month')]
        }
    });

    // Initialize DataTable
    var demandTable = $('#demand_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/therapy-demand-report',
            data: function(d) {
                var dateRange = $('#date_range').data('daterangepicker');
                d.start_date = dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '';
                d.end_date = dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '';
                d.category_id = $('#category_id').val();
                d.doctor_id = $('#doctor_id').val();
            }
        },
        columns: [
            { data: 'therapy_name', name: 'therapy_name' },
            { data: 'prescription_count', name: 'prescription_count' },
            { data: 'sold_count', name: 'sold_count' },
            { 
                data: 'fulfillment_rate', 
                name: 'fulfillment_rate',
                render: function(data, type, row) {
                    var color = data >= 80 ? 'success' : (data >= 50 ? 'warning' : 'danger');
                    return '<span class="badge bg-' + color + '">' + data + '%</span>';
                }
            },
            { 
                data: 'breakdown', 
                name: 'breakdown',
                orderable: false,
                searchable: false,
                render: function(data) {
                    let html = '';
                    data.forEach(function(doctor) {
                        html += doctor.doctor_name + ' <span title="Prescription Count" class="badge bg-light-blue">' + doctor.prescription_count + '</span><br>';
                    });
                    return html;
                }
            }
        ],
        order: [[3, 'desc']],
        drawCallback: function(settings) {
            loadSummaryData();
        }
    });

    // Filter handlers
    $('#category_id, #doctor_id, #date_range').change(function() {
        demandTable.ajax.reload();
        loadChartData();
        loadSummaryData();
    });

    function updateSummaryCards(jsonData) {
        if (!jsonData) return;
        
        $('#totalTherapies').text(jsonData.totalTherapies?.toLocaleString() || 0);
        $('#totalPrescribed').text(jsonData.totalPrescribed?.toLocaleString() || 0);
        $('#totalSold').text(jsonData.totalSold?.toLocaleString() || 0);
        $('#averageConversion').text(jsonData.averageConversion + '%' || '0%');
    }

    // Initialize charts
    let doctorConversionChart, topTherapiesChart;
    initializeCharts();

    function initializeCharts() {
        // Doctor Conversion Chart
        doctorConversionChart = new Chart(document.getElementById('doctorConversionChart'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Prescribed',
                    data: [],
                    backgroundColor: '#00a65a'
                }, {
                    label: 'Sold',
                    data: [],
                    backgroundColor: '#dd4b39'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Top Therapies Chart
        topTherapiesChart = new Chart(document.getElementById('topTherapiesChart'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Sold Count',
                    data: [],
                    backgroundColor: '#00a65a'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function loadChartData() {
        var dateRange = $('#date_range').data('daterangepicker');
        var data = {
            chart_data: 1,
            start_date: dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '',
            end_date: dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '',
            category_id: $('#category_id').val(),
            doctor_id: $('#doctor_id').val()
        };

        $.ajax({
            url: '/therapy-demand-report',
            data: data,
            success: function(response) {
                // Update doctor conversion chart
                doctorConversionChart.data.labels = response.doctorConversions.names || [];
                doctorConversionChart.data.datasets[0].data = response.doctorConversions.prescribed || [];
                doctorConversionChart.data.datasets[1].data = response.doctorConversions.sold || [];
                doctorConversionChart.update();

                // Update top therapies chart
                if (response.topTherapies) {
                    topTherapiesChart.data.labels = response.topTherapies.names || [];
                    topTherapiesChart.data.datasets[0].data = response.topTherapies.counts || [];
                    topTherapiesChart.update();
                }
            },
            error: function(xhr, status, error) {
                console.error('Chart data load error:', error);
            }
        });
    }

    function loadSummaryData() {
        var dateRange = $('#date_range').data('daterangepicker');
        var data = {
            summary_data: 1,
            start_date: dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '',
            end_date: dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '',
            category_id: $('#category_id').val(),
            doctor_id: $('#doctor_id').val()
        };

        $.ajax({
            url: '/therapy-demand-report',
            data: data,
            success: function(response) {
                updateSummaryCards(response);
            },
            error: function(xhr, status, error) {
                console.error('Summary data load error:', error);
            }
        });
    }

    // Load initial chart and summary data
    loadChartData();
    loadSummaryData();
});
</script>
@endsection