@extends('clinic::layouts.app2')

@section('title', 'Medicine Fulfillment Report')
@section('css')
    <style>
        .medicine-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #ccc;
            padding: 6px 4px;
        }
        .medicine-name {
            flex: 1;
        }
        .medicine-status {
            display: flex;
            gap: 6px;
            white-space: nowrap;
        }
        .badge {
            padding: 3px 6px;
            font-size: 12px;
            border-radius: 6px;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between; /* space between boxes */
            flex-wrap: nowrap; /* keep all in one row */
            gap: 10px; /* optional spacing */
            margin-bottom: 20px;
        }

        .summary-row .small-box {
            flex: 1 1 0; /* allow flexible width */
            max-width: 18%; /* roughly 5 in a row */
            color: #fff;
            position: relative;
            display: block;
            background: #00c0ef; /* fallback */
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
        <h1>Medicine Fulfillment Analysis Report</h1>
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
                        {!! Form::label('status', 'Prescription Status:') !!}
                        {!! Form::select('status', [
                            '' => 'All Status',
                            'fully_taken' => 'Fully Taken',
                            'partially_taken' => 'Partially Taken',
                            'not_taken' => 'Not Taken',
                        ], null, [
                            'class' => 'form-control select2',
                            'id' => 'status',
                            'style' => 'width: 100%',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('stock_status', 'Stock Status:') !!}
                        {!! Form::select('stock_status', [
                            '' => 'All Status',
                            'in_stock' => 'In Stock',
                            'out_of_stock' => 'Out of Stock',
                        ], null, [
                            'class' => 'form-control select2',
                            'id' => 'stock_status',
                            'style' => 'width: 100%',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('demand_status', 'Demand Level:') !!}
                        {!! Form::select('demand_status', [
                            '' => 'All Demand Levels',
                            'high' => 'High Demand',
                            'medium' => 'Medium Demand',
                            'low' => 'Low Demand'
                        ], null, [
                            'class' => 'form-control select2',
                            'id' => 'demand_status',
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
                    <h3 id="prescriptionCount">0</h3>
                    <p>Total Prescriptions</p>
                </div>
                <div class="icon">
                    <i class="fa fa-file-prescription"></i>
                </div>
            </div>
            <div class="small-box bg-info">
                <div class="inner">
                    <h3 id="totalPrescribed">0</h3>
                    <p>Total Prescribed Items</p>
                </div>
                <div class="icon">
                    <i class="fa fa-pills"></i>
                </div>
            </div>
            <div class="small-box bg-success">
                <div class="inner">
                    <h3 id="totalSold">0</h3>
                    <p>Total Sold Items</p>
                </div>
                <div class="icon">
                    <i class="fa fa-shopping-cart"></i>
                </div>
            </div>
            <div class="small-box bg-yellow">
                <div class="inner">
                    <h3 id="notTaken">0</h3>
                    <p>Not Taken Items (in Stock)</p>
                </div>
                <div class="icon">
                    <i class="fa fa-exclamation-triangle"></i>
                </div>
            </div>
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3 id="stockOutCases">0</h3>
                    <p>Stock Out Cases</p>
                </div>
                <div class="icon">
                    <i class="fa fa-exclamation-circle"></i>
                </div>
            </div>
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3 id="fulfillmentRate">0%</h3>
                    <p>Fulfillment Rate</p>
                </div>
                <div class="icon">
                    <i class="fa fa-chart-line"></i>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-4">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Medicine Status Distribution</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Prescription Completion Status</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="completionChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Stock Status Distribution</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="stockStatusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Demand Analysis</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="demandChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Stock Out Analysis (Top Products)</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="stockOutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @component('components.widget', ['class' => 'box-primary', 'title' => 'Medicine Fulfillment Details'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="medicine_report_table">
                    <thead>
                        <tr>
                            <th>Prescription Date</th>
                            <th>Patient Name</th>
                            <th>Doctor Name</th>
                            <th>Status</th>
                            <th>Prescribed</th>
                            <th>Sold</th>
                            <th>Rate</th>
                            <th>Medicine Details</th>
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
            locale: {
                format: 'YYYY-MM-DD'
            },
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
        var medicineTable = $('#medicine_report_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/medicine-fulfillment-report',
                data: function(d) {
                    var dateRange = $('#date_range').data('daterangepicker');
                    d.start_date = dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '';
                    d.end_date = dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '';
                    d.category_id = $('#category_id').val();
                    d.doctor_id = $('#doctor_id').val();
                    d.status = $('#status').val();
                    d.demand_status = $('#demand_status').val();
                    d.stock_status = $('#stock_status').val();
                }
            },
            columns: [
                { data: 'prescription_date_formatted', name: 'prescription_date' },
                { data: 'patient_name', name: 'patient_name' },
                { data: 'doctor_name', name: 'doctor_name' },
                { 
                    data: 'status', 
                    name: 'status',
                    render: function(data, type, row) {
                        var badgeClass = {
                            'fully_taken': 'success',
                            'partially_taken': 'warning',
                            'not_taken': 'secondary',
                        }[data] || 'secondary';
                        
                        var statusText = {
                            'fully_taken': 'Fully Taken',
                            'partially_taken': 'Partially Taken',
                            'not_taken': 'Not Taken',
                        }[data] || data;
                        
                        return '<span class="badge bg-' + badgeClass + '">' + statusText + '</span>';
                    }
                },
                { data: 'prescribed_count', name: 'prescribed_count' },
                { data: 'sold_count', name: 'sold_count' },
                { 
                    data: 'fulfillment_rate', 
                    name: 'fulfillment_rate',
                    render: function(data, type, row) {
                        var color = data >= 80 ? 'success' : (data >= 50 ? 'warning' : 'danger');
                        return '<span class="text-' + color + '">' + data + '%</span>';
                    }
                },
                { 
                    data: 'medicine_details', 
                    name: 'medicine_details',
                    orderable: false,
                    searchable: true
                },
            ],
            order: [[0, 'desc']],
            drawCallback: function(settings) {
                updateSummaryCards(settings.json);
            }
        });

        // Filter handlers
        $('#category_id, #status, #demand_status, #date_range, #doctor_id, #stock_status').change(function() {
            medicineTable.ajax.reload();
            loadChartData();
        });

        function updateSummaryCards(jsonData) {
            if (!jsonData || !jsonData.summary) return;
            
            $('#totalPrescribed').text(jsonData.summary.totalPrescribed?.toLocaleString() || 0);
            $('#totalSold').text(jsonData.summary.totalSold?.toLocaleString() || 0);
            $('#fulfillmentRate').text((jsonData.summary.fulfillmentRate || 0) + '%');
            $('#stockOutCases').text(jsonData.summary.stockOutCases?.toLocaleString() || 0);
            $('#prescriptionCount').text(jsonData.summary.prescriptionCount?.toLocaleString() || 0);
            $('#notTaken').text(jsonData.summary.notTakenInStock?.toLocaleString() || 0);
        }

        // Initialize charts
        let statusChart, completionChart, demandChart, stockOutChart, stockStatusChart;
        initializeCharts();

        function initializeCharts() {
            // Medicine Status Chart
            statusChart = new Chart(document.getElementById('statusChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Taken', 'Not Taken(in stock)', 'Out of Stock'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: ['#00a65a', '#dd4b39', '#f39c12']
                    }]
                },
                options: {
                    responsive: true,
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
                    },
                    maintainAspectRatio: false
                }
            });

            // Prescription Completion Chart
            completionChart = new Chart(document.getElementById('completionChart'), {
                type: 'pie',
                data: {
                    labels: ['Fully Taken', 'Partially Taken', 'Not Taken'],
                    datasets: [{
                        data: [0, 0, 0],
                        backgroundColor: ['#00a65a', '#f39c12', '#dd4b39']
                    }]
                },
                options: {
                    responsive: true,
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
                    },
                    maintainAspectRatio: false
                }
            });

            // Demand Analysis Chart
            demandChart = new Chart(document.getElementById('demandChart'), {
                type: 'bar',
                data: {
                    labels: ['High Demand', 'Medium Demand', 'Low Demand'],
                    datasets: [{
                        label: 'Number of Medicines',
                        data: [0, 0, 0],
                        backgroundColor: ['#dd4b39', '#f39c12', '#00a65a']
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

            // Stock Status Pie Chart (NEW)
            stockStatusChart = new Chart(document.getElementById('stockStatusChart'), {
                type: 'pie',
                data: {
                    labels: ['In Stock', 'Out of Stock'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: ['#00a65a', '#dd4b39']
                    }]
                },
                options: {
                    responsive: true,
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
                    },
                    maintainAspectRatio: false
                }
            });

            // Stock Out Analysis Chart
            stockOutChart = new Chart(document.getElementById('stockOutChart'), {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Stock Out Cases',
                        data: [],
                        backgroundColor: '#ff851b'
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
                status: $('#status').val(),
                demand_status: $('#demand_status').val(),
                doctor_id: $('#doctor_id').val(),
                stock_status: $('#stock_status').val()
            };

            $.ajax({
                url: '/medicine-fulfillment-report',
                data: data,
                success: function(response) {
                    // Update medicine status chart
                    statusChart.data.datasets[0].data = [
                        response.medicineStatus.taken || 0,
                        response.medicineStatus.not_taken_in_stock || 0,
                        response.medicineStatus.out_of_stock || 0
                    ];
                    statusChart.update();

                    // Update completion status chart
                    completionChart.data.datasets[0].data = [
                        response.prescriptionStatus.fully_taken || 0,
                        response.prescriptionStatus.partially_taken || 0,
                        response.prescriptionStatus.not_taken || 0
                    ];
                    completionChart.update();

                    // Update demand analysis chart
                    demandChart.data.datasets[0].data = [
                        response.demandAnalysis.high || 0,
                        response.demandAnalysis.medium || 0,
                        response.demandAnalysis.low || 0
                    ];
                    demandChart.update();

                    // Update stock status pie chart (NEW)
                    stockStatusChart.data.datasets[0].data = [
                        response.stockStatus.in_stock || 0,
                        response.stockStatus.out_of_stock || 0
                    ];
                    stockStatusChart.update();

                    // Update stock out analysis chart
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

        // Load initial chart data
        loadChartData();
    });
</script>
@endsection