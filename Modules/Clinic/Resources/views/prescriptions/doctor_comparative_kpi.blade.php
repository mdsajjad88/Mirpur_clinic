@extends('clinic::layouts.app2')

@section('title')
    Doctor Comparative KPI Report
@endsection

@section('content')
    <section class="content-header">
        <h1>Doctor Comparative KPI Report</h1>
    </section>

    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
                {{-- Date Range Filter --}}
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', $start_date . ' - ' . $end_date, [
                            'class' => 'form-control',
                            'id' => 'date_range',
                            'readonly',
                        ]) !!}
                    </div>
                </div>
            </div>
        @endcomponent
        <div id="loading-indicator" style="display: none; text-align: center; margin-top: 20px;">
            <i class="fa fa-spinner fa-spin fa-3x"></i>
            <p>Loading data...</p>
        </div>
        <div id="data-container">
            @component('components.widget', ['class' => 'box-primary', 'title' => 'KPI Report'])
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th></th>
                            <!-- Doctor headers will be populated via AJAX -->
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Table rows will be populated via AJAX -->
                    </tbody>
                </table>
            @endcomponent

            <!-- KPI Chart Section -->
            <div id="chart-container">
                @component('components.widget', ['class' => 'box-primary', 'title' => 'KPI Chart'])
                    <canvas id="kpiChart"></canvas>
                @endcomponent
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <!-- Include necessary charts and filters JS libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Daterange picker initialization
        $('#date_range').daterangepicker(
            $.extend({}, dateRangeSettings, {
                startDate: moment('{{ $start_date }}'),
                endDate: moment('{{ $end_date }}'),
                maxDate: moment()
            }),
            function(start, end) {
                $('#date_range').val(start.format(moment_date_format) + ' - ' + end.format(moment_date_format));
                // Call function to fetch data when the date range is applied
                fetchReportData(start.format('DD-MM-YYYY'), end.format('DD-MM-YYYY'));
            }
        );
    
        // Call fetchReportData on page load with the default date range
        $(document).ready(function() {
            fetchReportData('{{ $start_date }}', '{{ $end_date }}');
        });
    
        // Variable to store the chart instance
        let kpiChart = null;
    
        // Predefined colors (excluding red)
        const predefinedColors = [
            'rgba(54, 162, 235, 0.8)', // Blue
            'rgba(75, 192, 192, 0.8)', // Green
            'rgba(255, 206, 86, 0.8)', // Yellow
            'rgba(153, 102, 255, 0.8)', // Purple
            'rgba(255, 159, 64, 0.8)', // Orange
            'rgba(0, 128, 128, 0.8)', // Teal
            'rgba(128, 0, 128, 0.8)', // Indigo
            'rgba(0, 128, 0, 0.8)', // Dark Green
            'rgba(128, 128, 0, 0.8)', // Olive
            'rgba(0, 0, 128, 0.8)', // Navy
        ];
    
        // Function to fetch report data
        function fetchReportData(startDate, endDate) {
            $('#loading-indicator').show();
            $('#data-container').hide();
    
            $.ajax({
                url: '{{ route('doctor_comparative_kpi') }}',
                type: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                },
                success: function(data) {
                    let tableRows = '';
                    let doctors = [];
                    let kpiData = {
                        patients_seen: [],
                        avg_waiting_time: [],
                        therapies: [],
                        avg_therapy_per_patient: [],
                        investigations: [],
                        avg_investigation_per_patient: [],
                        us_supplements: [],
                        avg_us_supplement_per_patient: [],
                        foreign_supplements: [],
                        avg_foreign_supplement_per_patient: [],
                        bd_medicines: [],
                        avg_bd_medicine_per_patient: []
                    };
    
                    // Update the doctor headers
                    let doctorHeaders = '<th></th>';
                    data.forEach((item) => {
                        doctorHeaders += `<th>${item.doctor}</th>`;
                        doctors.push(item.doctor);
                    });
                    $('thead tr').html(doctorHeaders);
    
                    // Data rows
                    const metrics = [{
                            label: 'Patients seen:',
                            key: 'patients_seen'
                        },
                        {
                            label: 'Average patient waiting time:',
                            key: 'avg_waiting_time'
                        },
                        {
                            label: 'Therapies suggested (Total count):',
                            key: 'therapies'
                        },
                        {
                            label: 'Avg. therapy per patient:',
                            key: 'avg_therapy_per_patient',
                            round: true
                        },
                        {
                            label: 'Investigations suggested (Total count):',
                            key: 'investigations'
                        },
                        {
                            label: 'Avg Investigation per patient:',
                            key: 'avg_investigation_per_patient',
                            round: true
                        },
                        {
                            label: 'USA Supplements suggested (Total count):',
                            key: 'us_supplements'
                        },
                        {
                            label: 'Average US Supplement per patient:',
                            key: 'avg_us_supplement_per_patient',
                            round: true
                        },
                        // {
                        //     label: 'Foreign Supplement suggested (Total count):',
                        //     key: 'foreign_supplements'
                        // },
                        // {
                        //     label: 'Avg. Foreign supplement per patient:',
                        //     key: 'avg_foreign_supplement_per_patient',
                        //     round: true
                        // },
                        {
                            label: 'BD medicine Suggested (Total count):',
                            key: 'bd_medicines'
                        },
                        {
                            label: 'Avg BD medicine per patient:',
                            key: 'avg_bd_medicine_per_patient',
                            round: true
                        }
                    ];
    
                    metrics.forEach((metric) => {
                        tableRows += `<tr><td>${metric.label}</td>`;
                        data.forEach((item) => {
                            const value = metric.round ? Math.round(item[metric.key] * 10) /
                                10 : item[metric.key];
                            tableRows += `<td>${value}</td>`;
                            kpiData[metric.key].push(value);
                        });
                        tableRows += '</tr>';
                    });
    
                    $('tbody').html(tableRows);
    
                    // Destroy the existing chart if it exists
                    if (kpiChart) {
                        kpiChart.destroy();
                    }
    
                    // Initialize the KPI Chart
                    const kpiChartCtx = document.getElementById('kpiChart').getContext('2d');
                    kpiChart = new Chart(kpiChartCtx, {
                        type: 'bar',
                        data: {
                            labels: doctors,
                            datasets: Object.keys(kpiData).map((metricKey, index) => ({
                                label: metricKey.replace(/_/g, ' ').toUpperCase(),
                                data: kpiData[metricKey],
                                backgroundColor: predefinedColors[index % predefinedColors.length], // Use predefined colors
                                borderColor: predefinedColors[index % predefinedColors.length], // Use predefined colors
                                borderWidth: 1
                            }))
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'bottom', // Ensure the legend is on top if necessary
                                }
                            }
                        }
                    });
    
                    $('#loading-indicator').hide();
                    $('#data-container').show();
                },
                error: function() {
                    toastr.error('Failed to fetch data. Please try again.')
                    $('#loading-indicator').hide();
                    $('#data-container').show();
                }
            });
        }
    </script>
@endsection
