@extends('clinic::layouts.app2')

@section('title', 'Demand Report')

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
    <h1>Demand Report</h1>
</section>

<section class="content">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    {!! Form::label('category_id', 'Medicine Category:') !!}
                    {!! Form::select('category_id', $categories, null, [
                        'class' => 'form-control select2',
                        'id' => 'category_id',
                        'placeholder' => 'All Categories',
                        'style' => 'width: 100%',
                    ]) !!}
                </div>
            </div>
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
                    {!! Form::label('demand_level', 'Demand Level:') !!}
                    {!! Form::select('demand_level', [
                        '' => 'All Levels',
                        'high' => 'High Demand',
                        'medium' => 'Medium Demand',
                        'low' => 'Low Demand'
                    ], null, [
                        'class' => 'form-control select2',
                        'id' => 'demand_level',
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
                <h3 id="totalProducts">0</h3>
                <p>Total Products</p>
            </div>
            <div class="icon">
                <i class="fa fa-pills"></i>
            </div>
        </div>
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 id="highDemandProducts">0</h3>
                <p>High Demand Products</p>
            </div>
            <div class="icon">
                <i class="fa fa-chart-line"></i>
            </div>
        </div>
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="mediumDemandProducts">0</h3>
                <p>Medium Demand Products</p>
            </div>
            <div class="icon">
                <i class="fa fa-chart-bar"></i>
            </div>
        </div>
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="lowDemandProducts">0</h3>
                <p>Low Demand Products</p>
            </div>
            <div class="icon">
                <i class="fa fa-chart-area"></i>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Demand Level Distribution</h3>
                </div>
                <div class="box-body">
                    <div class="chart-container">
                        <canvas id="demandLevelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="box box-success">
                <div class="box-header with-border">
                    <h3 class="box-title">Top 10 Products by Demand</h3>
                </div>
                <div class="box-body">
                    <div class="chart-container">
                        <canvas id="topProductsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Demand Analysis Details'])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="demand_table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Prescription Count</th>
                        <th>Demand Level</th>
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
            url: '/demand-report',
            data: function(d) {
                var dateRange = $('#date_range').data('daterangepicker');
                d.start_date = dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '';
                d.end_date = dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '';
                d.category_id = $('#category_id').val();
                d.doctor_id = $('#doctor_id').val();
                d.demand_level = $('#demand_level').val();
            }
        },
        columns: [
            { data: 'product_name', name: 'product_name' },
            { data: 'category', name: 'category' },
            { 
                data: 'prescription_count', 
                name: 'prescription_count',
                render: function(data) {
                    return '<span class="badge bg-primary">' + data + '</span>';
                }
            },
            { 
                data: 'demand_badge', 
                name: 'demand_level',
                orderable: false,
                searchable: false
            }
        ],
        order: [[2, 'desc']],
        drawCallback: function(settings) {
            loadSummaryData();
        }
    });

    // Filter handlers
    $('#category_id, #doctor_id, #demand_level, #date_range').change(function() {
        demandTable.ajax.reload();
        loadChartData();
        loadSummaryData();
    });

    function updateSummaryCards(jsonData) {
        if (!jsonData) return;
        
        $('#totalProducts').text(jsonData.totalProducts?.toLocaleString() || 0);
        $('#highDemandProducts').text(jsonData.highDemandProducts?.toLocaleString() || 0);
        $('#mediumDemandProducts').text(jsonData.mediumDemandProducts?.toLocaleString() || 0);
        $('#lowDemandProducts').text(jsonData.lowDemandProducts?.toLocaleString() || 0);
    }

    // Initialize charts
    let demandLevelChart, topProductsChart;
    initializeCharts();

    function initializeCharts() {
        // Demand Level Chart
        demandLevelChart = new Chart(document.getElementById('demandLevelChart'), {
            type: 'doughnut',
            data: {
                labels: ['High Demand', 'Medium Demand', 'Low Demand'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#dd4b39', '#f39c12', '#00a65a']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'left',
                        labels: {
                            font: { size: 14 },
                            generateLabels: function(chart) {
                                let data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    let total = data.datasets[0].data.reduce((a,b) => a+b, 0);
                                    return data.labels.map((label, i) => {
                                        let value = data.datasets[0].data[i];
                                        let percent = total ? ((value / total) * 100).toFixed(1) : 0;
                                        let meta = chart.getDatasetMeta(0);
                                        let style = meta.controller.getStyle(i);
                                        return {
                                            text: `${label}: ${value} (${percent}%)`,
                                            fillStyle: style.backgroundColor,
                                            strokeStyle: style.borderColor,
                                            lineWidth: style.borderWidth,
                                            hidden: isNaN(value) || meta.data[i].hidden,
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
                                let data = tooltipItem.chart.data.datasets[0].data;
                                let total = data.reduce((a,b) => a+b, 0);
                                let value = tooltipItem.raw;
                                let percent = total ? ((value / total) * 100).toFixed(1) : 0;
                                return `${tooltipItem.label}: ${value} (${percent}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Top Products Chart
        topProductsChart = new Chart(document.getElementById('topProductsChart'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Prescription Count',
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
            doctor_id: $('#doctor_id').val(),
            demand_level: $('#demand_level').val()
        };

        $.ajax({
            url: '/demand-report',
            data: data,
            success: function(response) {
                // Update demand level chart
                demandLevelChart.data.datasets[0].data = [
                    response.demandLevels.high || 0,
                    response.demandLevels.medium || 0,
                    response.demandLevels.low || 0
                ];
                demandLevelChart.update();

                // Update top products chart
                if (response.topProducts) {
                    topProductsChart.data.labels = response.topProducts.names || [];
                    topProductsChart.data.datasets[0].data = response.topProducts.counts || [];
                    topProductsChart.update();
                }
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
            doctor_id: $('#doctor_id').val(),
            demand_level: $('#demand_level').val()
        };

        $.ajax({
            url: '/demand-report',
            data: data,
            success: function(response) {
                updateSummaryCards(response);
            }
        });
    }

    // Load initial chart and summary data
    loadChartData();
    loadSummaryData();
});
</script>
@endsection