@extends('clinic::layouts.app2')

@section('title', 'Service Comparison Report')

@section('css')
<style>
    .chart-container {
        position: relative;
        height: 300px;
    }
    .small-box .inner h3 {
        font-size: 24px;
        font-weight: bold;
    }
    
    /* Side by side comparison styling */
    .comparison-container {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .period-panel {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .period-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        margin-bottom: 20px;
    }
    
    .service-section {
        background: white;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    
    .section-header {
        color: #667eea;
        font-size: 16px;
        font-weight: bold;
        padding-bottom: 10px;
        margin-bottom: 12px;
        border-bottom: 2px solid #667eea;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .section-header small {
        font-size: 12px;
        color: #999;
        margin-left: auto;
    }
    
    .section-header::before {
        content: '';
        width: 4px;
        height: 18px;
        background: #667eea;
        border-radius: 2px;
    }
    
    .header-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        font-weight: bold;
        padding: 8px 10px;
        background: #f8f9fa;
        border-radius: 4px;
        margin-bottom: 10px;
    }
    
    .header-row span {
        font-weight: bold;
    }
    
    .header-row span:nth-child(2),
    .header-row span:nth-child(3) {
        text-align: right;
    }
    
    .service-row {
        display: grid;
        grid-template-columns: 2fr 1fr 1fr;
        padding: 8px 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .service-row:last-child {
        border-bottom: none;
    }
    
    .service-row.total-row {
        font-weight: bold;
        background: #f8f9fa;
        margin-top: 5px;
        border-radius: 4px;
    }
    
    .service-label {
        color: #666;
        font-size: 14px;
    }
    
    .service-count {
        text-align: right;
        color: #333;
        font-weight: 600;
        font-size: 14px;
    }
    
    .service-revenue {
        text-align: right;
        color: #28a745;
        font-weight: bold;
        font-size: 14px;
    }
    
    .header-revenue {
        text-align: right;
    }
    
    .show-revenue-btn {
        background: #a1a1a1;
        color: white;
        padding: 10px 25px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        transition: all 0.3s;
        margin-bottom: 20px;
    }
    
    .show-revenue-btn:hover {
        background: #939493;
        transform: translateY(-1px);
    }
    
    .revenue-hidden .service-revenue,
    .revenue-hidden .header-revenue {
        display: none;
    }
    
    .revenue-hidden .header-row,
    .revenue-hidden .service-row {
        grid-template-columns: 2fr 1fr;
    }
    
    @media (max-width: 768px) {
        .comparison-container {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@section('content')
@include('clinic::report.layouts.nav')
<section class="content-header">
    <h1>Service Comparison Report</h1>
</section>

<section class="content">
    @component('components.widget', ['title' => __('report.filters')])
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('date_range1', 'Date Range 1:') !!}
                    {!! Form::text('date_range1', null, [
                        'class' => 'form-control',
                        'id' => 'date_range1',
                        'readonly',
                        'placeholder' => 'Select date range 1',
                    ]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('date_range2', 'Date Range 2:') !!}
                    {!! Form::text('date_range2', null, [
                        'class' => 'form-control',
                        'id' => 'date_range2',
                        'readonly',
                        'placeholder' => 'Select date range 2',
                    ]) !!}
                </div>
            </div>
        </div>
        <div class="row">
            <!-- Show Revenue Button (if permission exists) -->
            @if(auth()->user()->can('service.revenue.view'))
            <div class="text-center" style="margin-top: 20px;">
                <button class="show-revenue-btn" id="toggleRevenueBtn" onclick="toggleRevenue()">
                    <i class="fa fa-lock"></i> <span id="revenueToggleText">Show Revenue</span>
                </button>
            </div>
            @endif
        </div>
    @endcomponent

    <div class="export-content">
        <!-- Side by Side Comparison -->
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Detailed Service Comparison</h3>
            </div>
            <div class="box-body">
                <div class="comparison-container revenue-hidden" id="comparisonContainer">
                    <div class="period-panel">
                        <div class="period-header">Data Range 1</div>
                        <div id="range1Content"></div>
                    </div>
                    
                    <div class="period-panel">
                        <div class="period-header">Data Range 2</div>
                        <div id="range2Content"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aggregate Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Patients Comparison</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="patientsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Services Comparison</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="servicesChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subcategory Charts -->
        <div class="row">
            <div class="col-md-4">
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title">Test Categories Comparison</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="testChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">Therapy Types Comparison</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="therapyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">IPD Cabins Comparison</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="ipdChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Revenue Chart (if permission exists) -->
        <div class="row hidden" id="revenueChartRow">
            @if(auth()->user()->can('service.revenue.view'))
                <div class="col-md-12">
                    <div class="box box-danger">
                        <div class="box-header with-border">
                            <h3 class="box-title">Revenue Comparison (BDT)</h3>
                        </div>
                        <div class="box-body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@section('javascript')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    let revenueVisible = false;
    
    // Initialize date range pickers
    $('#date_range1').daterangepicker({
        locale: { format: 'YYYY-MM-DD' },
        startDate: moment().subtract(30, 'days'),
        endDate: moment(),
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    $('#date_range2').daterangepicker({
        locale: { format: 'YYYY-MM-DD' },
        startDate: moment().subtract(60, 'days'),
        endDate: moment().subtract(30, 'days'),
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });

    // Chart instances
    var patientsChart, servicesChart, revenueChart, testChart, therapyChart, ipdChart;

    $('#date_range1, #date_range2').on('apply.daterangepicker', function(ev, picker) {
        loadAllData();
    });

    function loadAllData() {
        loadComparisonData();
        loadChartData();
    }

    function loadComparisonData() {
        var dr1 = $('#date_range1').data('daterangepicker');
        var dr2 = $('#date_range2').data('daterangepicker');
        
        if (!dr1 || !dr2) return;

        $.ajax({
            url: '/service-comparison-report',
            data: {
                start_date1: dr1.startDate.format('YYYY-MM-DD'),
                end_date1: dr1.endDate.format('YYYY-MM-DD'),
                start_date2: dr2.startDate.format('YYYY-MM-DD'),
                end_date2: dr2.endDate.format('YYYY-MM-DD')
            },
            success: function(response) {
                renderComparisonData(response);
            }
        });
    }

    function renderComparisonData(data) {
        renderPeriodData(data.range1, 'range1Content');
        renderPeriodData(data.range2, 'range2Content');
        
        // Apply revenue visibility
        if (!revenueVisible) {
            $('#comparisonContainer').addClass('revenue-hidden');
        } else {
            $('#comparisonContainer').removeClass('revenue-hidden');
        }
    }

    function renderPeriodData(items, containerId) {
        let html = '';
        let currentSection = '';
        
        items.forEach(function(item) {
            if (item.section) {
                // Close previous section
                if (currentSection) {
                    html += '</div>'; 
                }
                currentSection = item.section;
                html += `<div class="service-section">`;
                html += `<div class="section-header">${item.section}</div>`;
                
                // Header row (revenue column always present, hidden via CSS if needed)
                html += `<div class="header-row">
                    <span>Item</span>
                    <span style="text-align: right;">Count</span>
                    <span class="header-revenue" style="text-align: right;">Revenue (BDT)</span>
                </div>`;
            } else if (item.label) {
                // Service row
                const isBold = item.bold ? 'total-row' : '';
                html += `
                    <div class="service-row ${isBold}">
                        <span class="service-label">${item.label}</span>
                        <span class="service-count">${item.count || 0}</span>
                        ${item.revenue !== undefined ? `<span class="service-revenue">৳${item.revenue}</span>` : ''}
                    </div>
                `;
            }
        });
        
        if (currentSection) {
            html += '</div>'; // Close last section
        }
        
        $('#' + containerId).html(html);
    }

    function loadChartData() {
        var dr1 = $('#date_range1').data('daterangepicker');
        var dr2 = $('#date_range2').data('daterangepicker');
        
        if (!dr1 || !dr2) return;

        $.ajax({
            url: '/service-comparison-report',
            data: {
                chart_data: true,
                start_date1: dr1.startDate.format('YYYY-MM-DD'),
                end_date1: dr1.endDate.format('YYYY-MM-DD'),
                start_date2: dr2.startDate.format('YYYY-MM-DD'),
                end_date2: dr2.endDate.format('YYYY-MM-DD')
            },
            success: function(response) {
                updateCharts(response);
            }
        });
    }

    function updateCharts(data) {
        const range1Label = data.range_labels.range1;
        const range2Label = data.range_labels.range2;

        // ==============================
        // PATIENTS CHART
        // ==============================
        if (patientsChart) patientsChart.destroy();
        patientsChart = new Chart(document.getElementById('patientsChart'), {
            type: 'bar',
            data: {
                labels: data.patients_chart.labels,
                datasets: [
                    {
                        label: range1Label,
                        data: data.patients_chart.range1,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: range2Label,
                        data: data.patients_chart.range2,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // ==============================
        // SERVICES CHART
        // ==============================
        if (servicesChart) servicesChart.destroy();
        servicesChart = new Chart(document.getElementById('servicesChart'), {
            type: 'bar',
            data: {
                labels: data.services_chart.labels,
                datasets: [
                    {
                        label: range1Label,
                        data: data.services_chart.range1,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    },
                    {
                        label: range2Label,
                        data: data.services_chart.range2,
                        backgroundColor: 'rgba(255, 159, 64, 0.5)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // ==============================
        // TEST CHART
        // ==============================
        if (testChart) testChart.destroy();
        testChart = new Chart(document.getElementById('testChart'), {
            type: 'line',
            data: {
                labels: data.test_chart.labels,
                datasets: [
                    {
                        label: range1Label,
                        data: data.test_chart.range1,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1,
                        tension: 0.4
                    },
                    {
                        label: range2Label,
                        data: data.test_chart.range2,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // ==============================
        // THERAPY CHART
        // ==============================
        if (therapyChart) therapyChart.destroy();
        therapyChart = new Chart(document.getElementById('therapyChart'), {
            type: 'line',
            data: {
                labels: data.therapy_chart.labels,
                datasets: [
                    {
                        label: range1Label,
                        data: data.therapy_chart.range1,
                        backgroundColor: 'rgba(75, 192, 192, 0.5)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1,
                        tension: 0.4
                    },
                    {
                        label: range2Label,
                        data: data.therapy_chart.range2,
                        backgroundColor: 'rgba(255, 159, 64, 0.5)',
                        borderColor: 'rgba(255, 159, 64, 1)',
                        borderWidth: 1,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // ==============================
        // IPD CHART
        // ==============================
        if (ipdChart) ipdChart.destroy();
        ipdChart = new Chart(document.getElementById('ipdChart'), {
            type: 'line',
            data: {
                labels: data.ipd_chart.labels,
                datasets: [
                    {
                        label: range1Label,
                        data: data.ipd_chart.range1,
                        backgroundColor: 'rgba(153, 102, 255, 0.5)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1,
                        tension: 0.4
                    },
                    {
                        label: range2Label,
                        data: data.ipd_chart.range2,
                        backgroundColor: 'rgba(255, 206, 86, 0.5)',
                        borderColor: 'rgba(255, 206, 86, 1)',
                        borderWidth: 1,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true } }
            }
        });

        // ==============================
        // REVENUE CHART (if permission)
        // ==============================
        @if(auth()->user()->can('service.revenue.view'))
        if (revenueChart) revenueChart.destroy();
        revenueChart = new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: data.revenue_chart.labels,
                datasets: [
                    {
                        label: range1Label,
                        data: data.revenue_chart.range1,
                        backgroundColor: 'rgba(255, 99, 132, 0.5)',
                        borderColor: 'rgba(255, 99, 132, 1)',
                        borderWidth: 1
                    },
                    {
                        label: range2Label,
                        data: data.revenue_chart.range2,
                        backgroundColor: 'rgba(153, 102, 255, 0.5)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '৳' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return '৳' + context.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        @endif
    }

    // Toggle revenue visibility
    window.toggleRevenue = function() {
        revenueVisible = !revenueVisible;
        const btn = $('#toggleRevenueBtn');
        const text = $('#revenueToggleText');
        const icon = btn.find('i');
        
        if (revenueVisible) {
            $('#comparisonContainer').removeClass('revenue-hidden');
            text.text('Hide Revenue');
            icon.removeClass('fa-lock').addClass('fa-unlock');
            $('#revenueChartRow').removeClass('hidden');
        } else {
            $('#comparisonContainer').addClass('revenue-hidden');
            text.text('Show Revenue');
            icon.removeClass('fa-unlock').addClass('fa-lock');
            $('#revenueChartRow').addClass('hidden');
        }
    };

    // Initial load
    loadAllData();
});
</script>
@endsection