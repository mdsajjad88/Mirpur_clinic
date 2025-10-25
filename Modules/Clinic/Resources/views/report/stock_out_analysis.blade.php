@extends('clinic::layouts.app2')

@section('title', 'Stock Out Analysis Report')

@section('css')
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
    .doctor-breakdown {
        max-height: 200px;
        overflow-y: auto;
    }
    .doctor-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 8px;
        border-bottom: 1px solid #f0f0f0;
    }
    .doctor-name {
        flex: 1;
    }
    .doctor-count {
        font-size: 12px;
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
        background: #00c0ef;
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
    <h1>Stock Out Analysis Report</h1>
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
                <h3 id="totalStockOutProducts">0</h3>
                <p>Total Products with Stock Out</p>
            </div>
            <div class="icon">
                <i class="fa fa-exclamation-triangle"></i>
            </div>
        </div>
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 id="totalStockOutCases">0</h3>
                <p>Total Stock Out Cases</p>
            </div>
            <div class="icon">
                <i class="fa fa-times-circle"></i>
            </div>
        </div>
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="affectedDoctors">0</h3>
                <p>Affected Doctors</p>
            </div>
            <div class="icon">
                <i class="fa fa-user-md"></i>
            </div>
        </div>
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="topAffectedProduct">-</h3>
                <p>Most Affected Product</p>
            </div>
            <div class="icon">
                <i class="fa fa-pills"></i>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="row">
        <div class="col-md-12">
            <div class="box box-danger">
                <div class="box-header with-border">
                    <h3 class="box-title">Top Products with Stock Out Cases</h3>
                </div>
                <div class="box-body">
                    <div class="chart-container">
                        <canvas id="stockOutChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Stock Out Analysis Details'])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="stock_out_table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>Stock Out Count</th>
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
    var stockOutTable = $('#stock_out_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/stock-out-analysis-report',
            data: function(d) {
                var dateRange = $('#date_range').data('daterangepicker');
                d.start_date = dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '';
                d.end_date = dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '';
                d.category_id = $('#category_id').val();
                d.doctor_id = $('#doctor_id').val();
            }
        },
        columns: [
            { data: 'product_name', name: 'product_name' },
            { data: 'category', name: 'category' },
            { 
                data: 'total_count', 
                name: 'total_count',
                render: function(data) {
                    return '<span class="badge bg-danger">' + data + '</span>';
                }
            },
            { 
                data: 'doctor_breakdown', 
                name: 'doctor_breakdown',
                orderable: false,
                searchable: false
            }
        ],
        order: [[2, 'desc']],
        drawCallback: function(settings) {
            // Update summary cards based on full dataset
            loadSummaryData();
        }
    });

    // Filter handlers
    $('#category_id, #doctor_id, #date_range').change(function() {
        stockOutTable.ajax.reload();
        loadChartData();
        loadSummaryData();
    });

    function updateSummaryCards(jsonData) {
        if (!jsonData) return;
        
        $('#totalStockOutProducts').text(jsonData.totalStockOutProducts?.toLocaleString() || 0);
        $('#totalStockOutCases').text(jsonData.totalStockOutCases?.toLocaleString() || 0);
        $('#affectedDoctors').text(jsonData.affectedDoctors?.toLocaleString() || 0);
        $('#topAffectedProduct').text(jsonData.topAffectedProduct || '-');
    }

    // Initialize chart
    let stockOutChart;
    initializeChart();

    function initializeChart() {
        stockOutChart = new Chart(document.getElementById('stockOutChart'), {
            type: 'bar',
            data: {
                labels: [],
                datasets: [{
                    label: 'Stock Out Cases',
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
            url: '/stock-out-analysis-report',
            data: data,
            success: function(response) {
                if (response.stockOutAnalysis) {
                    var labels = Object.keys(response.stockOutAnalysis);
                    var values = Object.values(response.stockOutAnalysis);
                    
                    stockOutChart.data.labels = labels;
                    stockOutChart.data.datasets[0].data = values;
                    stockOutChart.update();
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
            doctor_id: $('#doctor_id').val()
        };

        $.ajax({
            url: '/stock-out-analysis-report',
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