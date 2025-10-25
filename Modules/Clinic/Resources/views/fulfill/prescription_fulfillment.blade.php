@extends('clinic::layouts.app2')

@section('title', 'Prescription Fulfillment Report')

@section('content')
@include('clinic::report.layouts.nav')
    <section class="content-header">
        <h1>Prescription Fulfillment Report</h1>
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
                        {!! Form::label('category', 'Category:') !!}
                        {!! Form::select('category', [
                            '' => 'All Categories',
                            'test' => 'Test',
                            'therapy' => 'Therapy',
                            'us_supplement' => 'US Supplement',
                            'ipd' => 'IPD',
                        ], null, [
                            'class' => 'form-control select2',
                            'id' => 'category',
                            'style' => 'width: 100%',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('status', 'Status:') !!}
                        {!! Form::select('status', [
                            '' => 'All Status',
                            'fully_taken' => 'Fully Taken',
                            'partially_taken' => 'Partially Taken',
                            'not_taken' => 'Not Taken',
                            'admitted' => 'Admitted',
                            'not_admitted' => 'Not Admitted',
                            'na' => 'N/A'
                        ], null, [
                            'class' => 'form-control select2',
                            'id' => 'status',
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
        <div class="row">
            <div class="col-md-2 col-sm-6 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-aqua"><i class="fa fa-file-prescription"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Prescriptions</span>
                        <span class="info-box-number" id="totalPrescriptions">0</span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-green"><i class="fa fa-vial"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Tests Rate</span>
                        <span class="info-box-number" id="TestsRate">
                            0/0<small class="pull-right">(0%)</small>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-blue"><i class="fa fa-hand-holding-medical"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Therapies Rate</span>
                        <span class="info-box-number" id="TherapiesRate">
                            0/0<small class="pull-right">(0%)</small>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-purple"><i class="fa fa-pills"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">US Supp Rate</span>
                        <span class="info-box-number" id="USSupplementsRate">
                            0/0<small class="pull-right">(0%)</small>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-orange"><i class="fa fa-procedures"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">IPD Rate</span>
                        <span class="info-box-number" id="IPDRate">
                            0/0<small class="pull-right">(0%)</small>
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-md-2 col-sm-6 col-xs-6">
                <div class="info-box">
                    <span class="info-box-icon bg-red"><i class="fa fa-chart-line"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Avg Score</span>
                        <span class="info-box-number" id="avgScore">0%</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- N/A Progress Bars Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Not Applicable (N/A) Distribution</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-gray"><i class="fa fa-vial"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Tests N/A</span>
                                        <span class="info-box-number" id="testNA">0</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-gray" id="testNAProgress" style="width: 0%"></div>
                                        </div>
                                        <span class="progress-description" id="testNAPercent">0% of prescriptions</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-gray"><i class="fa fa-hand-holding-medical"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">Therapies N/A</span>
                                        <span class="info-box-number" id="therapyNA">0</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-gray" id="therapyNAProgress" style="width: 0%"></div>
                                        </div>
                                        <span class="progress-description" id="therapyNAPercent">0% of prescriptions</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-gray"><i class="fa fa-pills"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">US Supplements N/A</span>
                                        <span class="info-box-number" id="usNA">0</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-gray" id="usNAProgress" style="width: 0%"></div>
                                        </div>
                                        <span class="progress-description" id="usNAPercent">0% of prescriptions</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-gray"><i class="fa fa-procedures"></i></span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">IPD N/A</span>
                                        <span class="info-box-number" id="ipdNA">0</span>
                                        <div class="progress">
                                            <div class="progress-bar bg-gray" id="ipdNAProgress" style="width: 0%"></div>
                                        </div>
                                        <span class="progress-description" id="ipdNAPercent">0% of prescriptions</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Analytics Charts Section -->
        <div class="row">
            <div class="col-md-3">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Test Fulfillment</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="testChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title">Therapy Fulfillment</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="therapyChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box box-warning">
                    <div class="box-header with-border">
                        <h3 class="box-title">US Supplement Fulfillment</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="usSupplementChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">IPD Fulfillment</h3>
                    </div>
                    <div class="box-body">
                        <div class="chart-container">
                            <canvas id="IPDChart" width="400" height="200"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @component('components.widget', ['class' => 'box-primary', 'title' => 'Prescription Fulfillment Report'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="prescription_report_table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Patient Info</th>
                            <th>Doctor</th>
                            <th>Test Status</th>
                            <th>Therapy Status</th>
                            <th>US Supplement Status</th>
                            <th>IPD Status</th>
                            <th>Adherence Score @show_tooltip('The total score is the sum of the adherence scores for each category. Only categories with prescribed items are considered. Unprescribed items (N/A) are excluded from calculation.')</th>
                            <th>Score @show_tooltip('Score is a weighted adherence percentage: For prescribed items, (Taken/Prescribed rate per category) × Weights [Therapy: 35%, Tests: 30%, IPD: 25%, US Supplements: 10%], summed and scaled to 0–100%. Unprescribed items (N/A) contribute 0% (no penalty or boost).')</th>
                            <th>Actions</th>
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
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });

        // Cache original status options
        const allStatusOptions = {
            'fully_taken': 'Fully Taken',
            'partially_taken': 'Partially Taken',
            'not_taken': 'Not Taken',
            'admitted': 'Admitted',
            'not_admitted': 'Not Admitted',
            'na': 'N/A'
        };

        // Function to repopulate status options
        function populateStatusOptions(category) {
            let filteredOptions = {};

            if (category === 'ipd') {
                // Only show admitted-related options
                filteredOptions = {
                    'admitted': 'Admitted',
                    'not_admitted': 'Not Admitted',
                    'na': 'N/A'
                };
            } else {
                // Show all except admitted and not_admitted
                filteredOptions = Object.fromEntries(
                    Object.entries(allStatusOptions).filter(
                        ([key]) => key !== 'admitted' && key !== 'not_admitted'
                    )
                );
            }

            // Rebuild the status dropdown
            const $status = $('#status');
            $status.empty();
            $.each(filteredOptions, function(value, text) {
                $status.append($('<option>', { value: value, text: text }));
            });

            // Refresh Select2 (if used)
            $status.val('').trigger('change.select2');
        }

        // Enable/disable and update status based on category
        $('#category').change(function() {
            const category = $(this).val();
            const $status = $('#status');

            if (category) {
                $status.prop('disabled', false);
                populateStatusOptions(category);
            } else {
                $status.prop('disabled', true);
                $status.val('').trigger('change');
            }
        });

        // Initialize with status disabled
        $('#status').prop('disabled', true);


        // Initialize DataTable
        var prescriptionTable = $('#prescription_report_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/prescription-fulfillment-report',
                data: function(d) {
                    var dateRange = $('#date_range').data('daterangepicker');
                    d.start_date = dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '';
                    d.end_date = dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '';
                    d.doctor_id = $('#doctor_id').val();
                    d.category = $('#category').val();
                    d.status = $('#status').val();
                }
            },
            columns: [
                { data: 'prescription_date_formatted', name: 'prescription_date' },
                { data: 'patient_info', name: 'patient_name' },
                { data: 'doctor_name', name: 'doctor_first_name' },
                { data: 'test_status', name: 'test_status', orderable: true, searchable: false },
                { data: 'therapy_status', name: 'therapy_status', orderable: true, searchable: false },
                { data: 'us_supplement_status', name: 'us_supplement_status', orderable: true, searchable: false },
                { data: 'ipd_status', name: 'ipd_status', orderable: true, searchable: false },
                { data: 'adherence_score', name: 'adherence_score', orderable: true, searchable: false },
                { data: 'score', name: 'score', orderable: true, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']],
            drawCallback: function(settings) {
                loadSummaryData();
                loadChartData();
            }
        });

        $('#doctor_id, #status, #date_range, #category').change(function() {
            prescriptionTable.ajax.reload(null, false);
            loadSummaryData();
            loadChartData();
        });

        // Initialize charts
        let testChart, therapyChart, usSupplementChart, ipdChart;

        function initializeCharts() {
            // Test Chart
            testChart = new Chart(document.getElementById('testChart'), {
                type: 'doughnut',
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
                            position: 'bottom',
                            labels: {
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // Therapy Chart
            therapyChart = new Chart(document.getElementById('therapyChart'), {
                type: 'doughnut',
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
                            position: 'bottom',
                            labels: {
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // US Supplement Chart
            usSupplementChart = new Chart(document.getElementById('usSupplementChart'), {
                type: 'doughnut',
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
                            position: 'bottom',
                            labels: {
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });

            // IPD Chart
            ipdChart = new Chart(document.getElementById('IPDChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Admitted', 'Not Admitted'],
                    datasets: [{
                        data: [0, 0],
                        backgroundColor: ['#00a65a', '#dd4b39']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: { size: 11 }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.raw || 0;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                    return `${label}: ${value} (${percentage}%)`;
                                }
                            }
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
                doctor_id: $('#doctor_id').val(),
                category: $('#category').val(),
                status: $('#status').val()
            };

            $.ajax({
                url: '/prescription-fulfillment-report',
                data: data,
                success: function(response) {
                    const totalPrescriptions = response.total_prescriptions || 1;

                    // Update test chart (exclude N/A)
                    testChart.data.datasets[0].data = [
                        response.test.fully_taken || 0,
                        response.test.partially_taken || 0,
                        response.test.not_taken || 0
                    ];
                    testChart.update();

                    // Update therapy chart (exclude N/A)
                    therapyChart.data.datasets[0].data = [
                        response.therapy.fully_taken || 0,
                        response.therapy.partially_taken || 0,
                        response.therapy.not_taken || 0
                    ];
                    therapyChart.update();

                    // Update US supplement chart (exclude N/A)
                    usSupplementChart.data.datasets[0].data = [
                        response.us_supplement.fully_taken || 0,
                        response.us_supplement.partially_taken || 0,
                        response.us_supplement.not_taken || 0
                    ];
                    usSupplementChart.update();

                    // Update IPD chart (exclude N/A)
                    ipdChart.data.datasets[0].data = [
                        response.ipd.admitted || 0,
                        response.ipd.not_admitted || 0
                    ];
                    ipdChart.update();

                    // Update N/A progress bars
                    updateNAProgressBars(response, totalPrescriptions);
                }
            });
        }

        function updateNAProgressBars(chartData, totalPrescriptions) {
            // Test N/A
            const testNA = chartData.test.na || 0;
            const testNAPercent = totalPrescriptions > 0 ? Math.round((testNA / totalPrescriptions) * 100) : 0;
            $('#testNA').text(testNA);
            $('#testNAProgress').css('width', testNAPercent + '%');
            $('#testNAPercent').text(testNAPercent + '% of prescriptions');

            // Therapy N/A
            const therapyNA = chartData.therapy.na || 0;
            const therapyNAPercent = totalPrescriptions > 0 ? Math.round((therapyNA / totalPrescriptions) * 100) : 0;
            $('#therapyNA').text(therapyNA);
            $('#therapyNAProgress').css('width', therapyNAPercent + '%');
            $('#therapyNAPercent').text(therapyNAPercent + '% of prescriptions');

            // US Supplement N/A
            const usNA = chartData.us_supplement.na || 0;
            const usNAPercent = totalPrescriptions > 0 ? Math.round((usNA / totalPrescriptions) * 100) : 0;
            $('#usNA').text(usNA);
            $('#usNAProgress').css('width', usNAPercent + '%');
            $('#usNAPercent').text(usNAPercent + '% of prescriptions');

            // IPD N/A
            const ipdNA = chartData.ipd.na || 0;
            const ipdNAPercent = totalPrescriptions > 0 ? Math.round((ipdNA / totalPrescriptions) * 100) : 0;
            $('#ipdNA').text(ipdNA);
            $('#ipdNAProgress').css('width', ipdNAPercent + '%');
            $('#ipdNAPercent').text(ipdNAPercent + '% of prescriptions');
        }

        function loadSummaryData() {
            var dateRange = $('#date_range').data('daterangepicker');
            var data = {
                summary_data: 1,
                start_date: dateRange ? dateRange.startDate.format('YYYY-MM-DD') : '',
                end_date: dateRange ? dateRange.endDate.format('YYYY-MM-DD') : '',
                doctor_id: $('#doctor_id').val(),
                category: $('#category').val(),
                status: $('#status').val()
            };

            $.ajax({
                url: '/prescription-fulfillment-report',
                data: data,
                success: function(response) {
                    $('#totalPrescriptions').text(response.total_prescriptions?.toLocaleString() || 0);
                    
                    // Tests - Total Taken / Total Prescribed (Percentage)
                    $('#TestsRate').html(
                        (response.tests_data?.taken || 0).toLocaleString() + '/' + 
                        (response.tests_data?.prescribed || 0).toLocaleString() + 
                        '<small class="pull-right">(' + (response.tests_rate?.toLocaleString() || 0) + '%)</small>'
                    );
                    
                    // Therapies - Total Taken / Total Prescribed (Percentage)
                    $('#TherapiesRate').html(
                        (response.therapies_data?.taken || 0).toLocaleString() + '/' + 
                        (response.therapies_data?.prescribed || 0).toLocaleString() + 
                        '<small class="pull-right">(' + (response.therapies_rate?.toLocaleString() || 0) + '%)</small>'
                    );
                    
                    // US Supplements - Total Taken / Total Prescribed (Percentage)
                    $('#USSupplementsRate').html(
                        (response.us_data?.taken || 0).toLocaleString() + '/' + 
                        (response.us_data?.prescribed || 0).toLocaleString() + 
                        '<small class="pull-right">(' + (response.us_supplements_rate?.toLocaleString() || 0) + '%)</small>'
                    );
                    
                    // IPD - Total Taken / Total Prescribed (Percentage)
                    $('#IPDRate').html(
                        (response.ipd_data?.taken || 0).toLocaleString() + '/' + 
                        (response.ipd_data?.prescribed || 0).toLocaleString() + 
                        '<small class="pull-right">(' + (response.ipd_rate?.toLocaleString() || 0) + '%)</small>'
                    );
                    
                    $('#avgScore').text((response.avg_score || 0) + '%');
                }
            });
        }

        // Initialize charts and load initial data
        initializeCharts();
        loadSummaryData();
        loadChartData();
    });
</script>
@endsection