@extends('clinic::layouts.app2')

@section('css')
    <style>
        .performance-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-bottom: 20px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        .performance-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .performance-card .card-header {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
            border-radius: 12px 12px 0 0 !important;
            border: none;
            padding: 15px 20px;
            font-weight: 600;
        }

        .performance-card .card-body {
            padding: 20px;
        }

        .metric-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .metric-row:last-child {
            border-bottom: none;
        }

        .metric-label {
            font-weight: 500;
            color: #495057;
            flex: 1;
        }

        .metric-value {
            font-weight: 600;
            color: #212529;
            flex: 1;
            text-align: right;
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .score-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            display: inline-block;
            width: auto;
            text-align: center;
        }

        .score-excellent { background-color: #28a745; color: white; }
        .score-good { background-color: #17a2b8; color: white; }
        .score-average { background-color: #ffc107; color: #212529; }
        .score-poor { background-color: #dc3545; color: white; }

        .progress-container {
            background: #e9ecef;
            border-radius: 10px;
            height: 8px;
            margin: 8px 0;
        }

        .progress-bar-custom {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
        }

        .rating-stars {
            color: #ffc107;
            font-size: 16px;
            margin-right: 2px;
        }

        .empty-stars {
            color: #e4e5e9;
            font-size: 16px;
            margin-right: 2px;
        }

        .summary-card {
            background: linear-gradient(135deg, rgb(110, 246, 185, 1) 0%,rgb(158, 248, 133, 1));
            color: rgb(31, 31, 31);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            text-align: center;
        }

        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        @media (max-width: 768px) {
            .performance-grid {
                grid-template-columns: 1fr;
            }
        }

        .btn-outline-primary {
            border-width: 1px;
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .log-entry {
            font-size: 11px;
        }

        .log-entry:last-child {
            border-bottom: none !important;
        }

        .fa-chevron-down, .fa-chevron-up {
            transition: transform 0.3s ease;
        }

        .revenue-breakdown-toggle {
            margin-left: 10px;
        }
    </style>
@endsection
@section('title')
    Doctor KPI Performance Report
@endsection

@section('content')
    <section class="content-header">
        <h1>Doctor KPI Performance Report</h1>
    </section>

    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
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
            <!-- Summary Cards -->
            <div class="row">
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-aqua"><i class="fa fa-user-md"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Doctors</span>
                            <span class="info-box-number" id="total-doctors">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-green"><i class="fa fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Total Patients</span>
                            <span class="info-box-number" id="total-patients">0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-yellow"><i class="fas fa-money-bill"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Avg Revenue/Patient</span>
                            <span class="info-box-number" id="avg-revenue">৳0</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="info-box">
                        <span class="info-box-icon bg-red"><i class="fas fa-chart-line"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Avg KPI Score</span>
                            <span class="info-box-number" id="avg-score">0</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Doctor KPI Table -->
            @component('components.widget', ['class' => 'box-primary', 'title' => 'Doctor KPI Performance'])
                <table class="table table-bordered table-striped" id="kpi-table">
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Type</th>
                            <th>Patients Seen</th>
                            <th>Target Patients</th>
                            <th>Patient Score</th>
                            <th>Satisfaction Score</th>
                            <th>Revenue Score</th>
                            <th>Attendance Score</th>
                            <th>Total Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated via AJAX -->
                    </tbody>
                </table>
            @endcomponent
            
            <!-- Score Distribution Chart -->
            <div class="row">
                <div class="col-md-6">
                    @component('components.widget', ['class' => 'box-info', 'title' => 'Score Distribution'])
                        <canvas id="scoreChart" height="250"></canvas>
                    @endcomponent
                </div>
                <div class="col-md-6">
                    @component('components.widget', ['class' => 'box-success', 'title' => 'Performance by Category'])
                        <canvas id="categoryChart" height="250"></canvas>
                    @endcomponent
                </div>
            </div>
        </div>
        
        <!-- Detail Modal -->
        <div class="modal fade" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="detailModalLabel">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title text-white" id="detailModalLabel">
                            <i class="fa fa-user-md"></i> Performance Details
                        </h4>
                    </div>
                    <div class="modal-body" id="detail-content" style="padding: 20px;">
                        <!-- Details will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Aggregate Details Modal (reused for US, Therapy, Test) -->
        <div class="modal fade" id="aggregateModal" tabindex="-1" role="dialog" aria-labelledby="aggregateModalLabel">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title text-white" id="aggregateModalLabel">
                            <i class="fa fa-list mr-2"></i> Details
                        </h4>
                    </div>
                    <div class="modal-body" id="aggregate-content" style="max-height: 80vh; overflow-y: auto;">
                        <!-- Aggregate table will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Daterange picker initialization
        $('#date_range').daterangepicker(
            $.extend({}, dateRangeSettings, {
                startDate: moment('{{ $start_date }}'),
                endDate: moment('{{ $end_date }}'),
                maxDate: moment(),
                ranges: {
                    'This Month': [moment().startOf('month'), moment().endOf('month')],
                    'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    'Last 3 Months': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')],
                    'Last 6 Months': [moment().subtract(5, 'month').startOf('month'), moment().endOf('month')],
                }
            }),
            function(start, end) {
                $('#date_range').val(start.format(moment_date_format) + ' - ' + end.format(moment_date_format));
                fetchReportData(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            }
        );

        // Call fetchReportData on page load
        $(document).ready(function() {
            fetchReportData('{{ $start_date }}', '{{ $end_date }}');
        });

        // Chart instances
        let scoreChart = null;
        let categoryChart = null;

        // Function to fetch report data
        function fetchReportData(startDate, endDate) {
            $('#loading-indicator').show();
            $('#data-container').hide();

            $.ajax({
                url: '{{ route('doctor_kpi_performance') }}',
                type: 'GET',
                data: {
                    start_date: startDate,
                    end_date: endDate,
                },
                success: function(response) {
                    const data = response.doctors;
                    const clinicMetrics = response.clinic_metrics;
                    
                    updateSummary(data, clinicMetrics);
                    populateTable(data);
                    updateCharts(data);
                    
                    $('#loading-indicator').hide();
                    $('#data-container').show();
                },
                error: function() {
                    toastr.error('Failed to fetch data. Please try again.');
                    $('#loading-indicator').hide();
                    $('#data-container').show();
                }
            });
        }

        // Update summary cards with clinic metrics
        function updateSummary(data, clinicMetrics) {
            const totalDoctors = data.length;
            const totalPatients = clinicMetrics.total_patients;
            const avgRevenuePerPatient = clinicMetrics.avg_revenue_per_patient;
            const avgScore = data.length > 0 ? data.reduce((sum, doctor) => sum + doctor.total_score, 0) / data.length : 0;
            
            $('#total-doctors').text(totalDoctors);
            $('#total-patients').text(totalPatients.toLocaleString());
            $('#avg-revenue').text('৳' + avgRevenuePerPatient.toFixed(2));
            $('#avg-score').text(avgScore.toFixed(1));
        }

        // Populate the table with data
        function populateTable(data) {
            let tableRows = '';
            
            data.forEach(doctor => {
                const progressClass = doctor.total_score >= 80 ? 'progress-bar-success' : 
                                    doctor.total_score >= 60 ? 'progress-bar-warning' : 'progress-bar-danger';
                
                tableRows += `
                    <tr>
                        <td>${doctor.doctor_name}</td>
                        <td>${doctor.doctor_type.replace('_', ' ').toUpperCase()}</td>
                        <td>${doctor.patients_seen}</td>
                        <td>${doctor.target_patients}</td>
                        <td>
                            ${doctor.patient_score.toFixed(1)}
                        </td>
                        <td>${doctor.satisfaction_score.toFixed(1)}</td>
                        <td>${doctor.revenue_score.toFixed(1)}</td>
                        <td>${doctor.attendance_score.toFixed(1)}</td>
                        <td>
                            <div class="progress progress-xs">
                                <div class="progress-bar ${progressClass}" style="width: ${doctor.total_score}%"></div>
                            </div>
                            <span class="badge bg-${progressClass.replace('progress-bar-', '')}">${doctor.total_score.toFixed(1)}</span>
                        </td>
                        <td>
                            <button class="btn btn-xs btn-info view-details" data-doctor-id="${doctor.doctor_id}">
                                <i class="fa fa-eye"></i> Details
                            </button>
                        </td>
                    </tr>
                `;
            });
            
            $('#kpi-table tbody').html(tableRows);
            
            // Add click handlers for detail buttons
            $('.view-details').click(function() {
                const doctorId = $(this).data('doctor-id');
                const doctorData = data.find(d => d.doctor_id === doctorId);
                showDetails(doctorData);
            });
        }

        // Show details in modal with modern design
        function showDetails(doctorData) {
            const details = doctorData.details;
            
            // Store aggregate data
            window.therapyAggregates = details.revenue_breakdown.therapy_product_aggregates || [];
            window.testAggregates = details.revenue_breakdown.test_product_aggregates || [];
            window.usAggregates = details.revenue_breakdown.us_product_aggregates || [];
            window.ipdPatientDetails = details.revenue_breakdown.ipd_patient_details || [];
            window.otherProductsAggregates = details.revenue_breakdown.other_products_product_aggregates || [];
            window.otherProductsCategoryAggregates = details.revenue_breakdown.other_products_category_aggregates || [];
            
            // Get score class for styling
            const getScoreClass = (score, maxScore) => {
                const percentage = (score / maxScore) * 100;
                if (percentage >= 90) return 'score-excellent';
                if (percentage >= 80) return 'score-good';
                if (percentage >= 70) return 'score-average';
                return 'score-poor';
            };

            // Generate star rating HTML
            const generateStars = (rating) => {
                let stars = '';
                const fullStars = Math.floor(rating);
                const hasHalfStar = rating % 1 >= 0.5;
                
                for (let i = 0; i < fullStars; i++) {
                    stars += '<i class="fa fa-star rating-stars"></i>';
                }
                if (hasHalfStar) {
                    stars += '<i class="fas fa-star-half rating-stars"></i>';
                }
                for (let i = fullStars + (hasHalfStar ? 1 : 0); i < 5; i++) {
                    stars += '<i class="fas fa-star empty-stars"></i>';
                }
                return stars;
            };

            // Generate shift change log HTML
            function generateShiftChangeLog(logData) {
                if (!logData || logData.length === 0) {
                    return '<div class="text-center text-muted py-2">No shift changes recorded</div>';
                }
                
                let logHtml = '';
                logData.forEach(entry => {
                    logHtml += `
                        <div class="log-entry" style="border-bottom: 0.5px solid #e9ecef; padding-bottom: 3px; margin-bottom: 3px;">
                            <div style="display: flex; flex-wrap: nowrap; justify-content: space-between; align-items: center;" class="mb-2">
                                <span>${entry.date}</span>
                                <span class="badge bg-danger small">${entry.action}</span>
                                <div>${entry.type} (${entry.capacity} slots)</div>
                            </div>
                        </div>
                    `;
                });
                
                return logHtml;
            }

            const doctorType = doctorData.doctor_type ? doctorData.doctor_type.replace('_', ' ').toUpperCase() : 'DOCTOR';

            let detailHtml = `
                <div class="summary-card">
                    <h2 class="mb-3">${doctorData.doctor_name || 'Unknown Doctor'} <small class="text-muted">${doctorType}</small></h2>
                    <div class="d-flex justify-content-center align-items-center mb-3">
                        <strong class="h3">Overall Score: </strong> <span class="h3 mb-0">${doctorData.total_score ? doctorData.total_score.toFixed(1) : '0.0'}</span>
                    </div>
                    <div class="score-badge ${getScoreClass(doctorData.total_score || 0, 100)} mt-5">
                        ${(doctorData.total_score || 0) >= 90 ? 'Excellent' : 
                        (doctorData.total_score || 0) >= 80 ? 'Good' : 
                        (doctorData.total_score || 0) >= 70 ? 'Average' : 'Needs Improvement'}
                    </div>
                </div>

                <div class="performance-grid">
                    <!-- Patient Metrics Card -->
                    <div class="performance-card">
                        <div class="card-header">
                            <i class="fa fa-users mr-2"></i> Patient Metrics
                        </div>
                        <div class="card-body">
                            <div class="metric-row">
                                <span class="metric-label">Patients Seen</span>
                                <span class="metric-value">${doctorData.patients_seen || 0}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Target Patients</span>
                                <span class="metric-value">${doctorData.target_patients || 0}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Achievement Rate</span>
                                <span class="metric-value">${details.patient_breakdown?.target_achieved ? details.patient_breakdown.target_achieved.toFixed(1) : '0.0'}%</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Bonus Points</span>
                                <span class="metric-value">+${details.patient_breakdown?.bonus_patients ? details.patient_breakdown.bonus_patients.toFixed(1) : '0.0'}</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar-custom bg-success" style="width: ${((doctorData.patient_score || 0)/30)*100}%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="metric-label">Patient Score</span>
                                <span class="score-badge ${getScoreClass(doctorData.patient_score || 0, 30)}">
                                    ${doctorData.patient_score ? doctorData.patient_score.toFixed(1) : '0.0'}/30
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Satisfaction Metrics Card -->
                    <div class="performance-card">
                        <div class="card-header">
                            <i class="fa fa-smile-o mr-2"></i> Satisfaction Metrics
                        </div>
                        <div class="card-body">
                            <div class="metric-row">
                                <span class="metric-label">Average Rating</span>
                                <span class="metric-value">
                                    ${generateStars(details.satisfaction_breakdown?.avg_rating || 0)}
                                    (${details.satisfaction_breakdown?.avg_rating ? details.satisfaction_breakdown.avg_rating.toFixed(1) : '0.0'}/5)
                                </span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Serious Complaints</span>
                                <span class="metric-value">${details.satisfaction_breakdown?.serious_complaints || 0}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Minor Complaints</span>
                                <span class="metric-value">${details.satisfaction_breakdown?.minor_complaints || 0}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Deductions</span>
                                <span class="metric-value text-danger">-${details.satisfaction_breakdown?.deductions || 0}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Bonus</span>
                                <span class="metric-value text-success">+${details.satisfaction_breakdown?.bonus || 0}</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar-custom bg-info" style="width: ${((doctorData.satisfaction_score || 0)/25)*100}%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="metric-label">Satisfaction Score</span>
                                <span class="score-badge ${getScoreClass(doctorData.satisfaction_score || 0, 25)}">
                                    ${doctorData.satisfaction_score ? doctorData.satisfaction_score.toFixed(1) : '0.0'}/25
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Revenue Metrics Card -->
                    <div class="performance-card">
                        <div class="card-header">
                            <i class="fa fa-money mr-2"></i> Revenue Metrics
                        </div>
                        <div class="card-body">
                            <div class="metric-row">
                                <span class="metric-label">Clinic Average Revenue/Patient</span>
                                <span class="metric-value">৳${ details.revenue_breakdown?.clinic_avg_revenue ? details.revenue_breakdown.clinic_avg_revenue.toFixed(2) : '0.00' }</span>
                            </div>
                            <div class="metric-row">
                                <div class="revenue-breakdown-log" style="display: flex; justify-content: space-between; align-items: center;">
                                    <span class="metric-label">Doctor's Revenue/Patient</span>
                                    <button class="btn btn-sm btn-outline-primary revenue-breakdown-toggle" type="button">
                                        <i class="fa fa-history mr-1"></i> Breakdown
                                        <i class="fa fa-chevron-down ml-1"></i>
                                    </button>
                                </div>
                                <span class="metric-value">৳${ details.revenue_breakdown?.doctor_revenue_per_patient ? details.revenue_breakdown.doctor_revenue_per_patient.toFixed(2) : '0.00' }</span>
                            </div>
                            <div class="revenue-breakdown-content" style="display: none; margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border: 1px solid #e9ecef;">
                                <!-- Therapy Revenue Breakdown -->
                                <div class="metric-row" style="border-bottom: none; padding: 5px 0; font-size: 14px; border-bottom: 1px solid #e9ecef;">
                                    <span class="metric-label">Therapy Revenue <small class="text-muted">
                                            (${ details.revenue_breakdown?.conversion_metrics?.therapy?.sold_count || 0 }/${ details.revenue_breakdown?.conversion_metrics?.therapy?.prescribed_count || 0 })
                                        </small></span>
                                    <span class="metric-value">
                                        <button class="btn btn-xs btn-outline-info therapy-details-toggle ml-2" type="button">
                                            <i class="fa fa-list mr-1"></i> Details
                                        </button>
                                        ৳${ details.revenue_breakdown?.therapy_revenue_per_patient || '0.00' }
                                    </span>
                                </div>
                                
                                <!-- Test Revenue Breakdown -->
                                <div class="metric-row" style="border-bottom: none; padding: 5px 0; font-size: 14px; border-bottom: 1px solid #e9ecef;">
                                    <span class="metric-label">Test Revenue <small class="text-muted">
                                            (${ details.revenue_breakdown?.conversion_metrics?.test?.sold_count || 0 }/${ details.revenue_breakdown?.conversion_metrics?.test?.prescribed_count || 0 })
                                        </small>
                                    </span>
                                    <span class="metric-value">
                                        <button class="btn btn-xs btn-outline-info test-details-toggle ml-2" type="button">
                                            <i class="fa fa-list mr-1"></i> Details
                                        </button>
                                        ৳${ details.revenue_breakdown?.test_revenue_per_patient || '0.00' }
                                    </span>
                                </div>
                                
                                <!-- US Supplement Revenue Breakdown -->
                                <div class="metric-row" style="border-bottom: none; padding: 5px 0; font-size: 14px; border-bottom: 1px solid #e9ecef;">
                                    <span class="metric-label">US Revenue <small class="text-muted">
                                            (${ details.revenue_breakdown?.conversion_metrics?.us_supplement?.sold_count || 0 }/${ details.revenue_breakdown?.conversion_metrics?.us_supplement?.prescribed_count || 0 })
                                        </small>
                                    </span>
                                    <span class="metric-value">
                                        <button class="btn btn-xs btn-outline-info us-details-toggle ml-2" type="button">
                                            <i class="fa fa-list mr-1"></i> Details
                                        </button>
                                        ৳${ details.revenue_breakdown?.us_supplement_revenue_per_patient || '0.00' }
                                    </span>
                                </div>

                                <!-- IPD Revenue Breakdown -->
                                <div class="metric-row" style="border-bottom: none; padding: 5px 0; font-size: 14px; border-bottom: 1px solid #e9ecef;">
                                    <span class="metric-label">IPD Revenue <small class="text-muted">
                                            (${ details.revenue_breakdown?.conversion_metrics?.ipd?.sold_count || 0 }/${ details.revenue_breakdown?.conversion_metrics?.ipd?.prescribed_count || 0 })
                                        </small>
                                    </span>
                                    <span class="metric-value">
                                        <button class="btn btn-xs btn-outline-info ipd-details-toggle ml-2" type="button">
                                            <i class="fa fa-list mr-1"></i> Details
                                        </button>
                                        ৳${ details.revenue_breakdown?.ipd_revenue_per_patient || '0.00' }
                                    </span>
                                </div>

                                <!-- Others Revenue Breakdown -->
                                <div class="metric-row" style="border: 1px solid #e9ecef; padding: 5px; font-size: 12px; border-radius: 5px; background-color: #b4f4a4; margin-top: 2px;">
                                    <span class="metric-label">Others Revenue <small class="text-muted">
                                            (${ details.revenue_breakdown?.conversion_metrics?.other_products?.sold_count || 0 }/${ details.revenue_breakdown?.conversion_metrics?.other_products?.prescribed_count || 0 })
                                        </small>
                                    </span>
                                    <span class="metric-value">
                                        <button class="btn btn-xs btn-outline-info other-products-details-toggle ml-2" type="button">
                                            <i class="fa fa-list mr-1"></i> Details
                                        </button>
                                        ৳${ details.revenue_breakdown?.other_products_revenue_per_patient || '0.00' }
                                    </span>
                                </div>
                            </div>
                            <!-- Overall Conversion Rate -->
                            <div class="metric-row">
                                <span class="metric-label">Overall Conversion Rate</span>
                                <span class="metric-value">
                                    <span class="badge ${ (details.revenue_breakdown?.conversion_metrics?.overall_conversion_rate || 0) >= 70 ? 'bg-success' : ((details.revenue_breakdown?.conversion_metrics?.overall_conversion_rate || 0) >= 50 ? 'bg-warning' : 'bg-danger') }">
                                        <strong>${ details.revenue_breakdown?.conversion_metrics?.overall_conversion_rate || 0 }%</strong>
                                    </span>
                                </span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Revenue Score</span>
                                <span class="metric-value">${ details.revenue_breakdown?.revenue_score ? details.revenue_breakdown.revenue_score.toFixed(1) : '0.0' }</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Bonus</span>
                                <span class="metric-value text-success">+${ details.revenue_breakdown?.bonus ? details.revenue_breakdown.bonus.toFixed(1) : '0.0' }</span>
                            </div>
                            <div class="progress-container">
                                <div class="progress-bar-custom bg-warning" style="width: ${ ((doctorData.revenue_score || 0)/35)*100 }%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="metric-label">Revenue Score</span>
                                <span class="score-badge ${ getScoreClass(doctorData.revenue_score || 0, 30) }">
                                    ${ doctorData.revenue_score ? doctorData.revenue_score.toFixed(1) : '0.0' }/30
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Attendance Metrics Card -->
                    <div class="performance-card">
                        <div class="card-header">
                            <i class="fa fa-clock-o mr-2"></i> Attendance Metrics
                        </div>
                        <div class="card-body">
                            <div class="metric-row">
                                <span class="metric-label">Attendance Rate</span>
                                <span class="metric-value">${details.attendance_breakdown?.attendance_day_count || 0}/${details.attendance_breakdown?.duty_day_count || 0}</span>
                            </div>
                            <!-- Deleted Shifts and Shift Change Log in one row -->
                            <div class="metric-row">
                                <span class="metric-label">Deleted Shifts</span>
                                <div class="shift-change-log" style="display: inline-block; margin: 0 10px;">
                                    <button class="btn btn-sm btn-outline-primary shift-log-toggle" type="button">
                                        <i class="fa fa-history mr-1"></i> Shift Change Log
                                        <i class="fa fa-chevron-down ml-1"></i>
                                    </button>
                                </div>
                                <span class="metric-value text-danger">
                                    ${details.attendance_breakdown?.deleted_slots_count || 0} (-${details.attendance_breakdown?.attendance_deduction || 0})
                                </span>
                            </div>
                            
                            <!-- Shift Change Log Content (initially hidden) -->
                            <div class="shift-log-content" style="display: none; margin-top: 10px; padding: 10px; background-color: #f8f9fa; border-radius: 5px; border: 1px solid #e9ecef;">
                                ${generateShiftChangeLog(details.attendance_breakdown?.shift_change_log || [])}
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Late Count</span>
                                <span class="metric-value">${details.attendance_breakdown?.late_count || 0}</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Avg Waiting Time</span>
                                <span class="metric-value">${details.attendance_breakdown?.avg_waiting_time ? details.attendance_breakdown.avg_waiting_time.toFixed(1) : '0.0'} mins</span>
                            </div>
                            <div class="metric-row">
                                <span class="metric-label">Score Breakdown</span>
                                <span class="metric-value">
                                    A:${details.attendance_breakdown?.attendance_score ? details.attendance_breakdown.attendance_score.toFixed(1) : '0.0'} 
                                    L:${details.attendance_breakdown?.late_score ? details.attendance_breakdown.late_score.toFixed(1) : '0.0'} 
                                    W:${details.attendance_breakdown?.waiting_time_score ? details.attendance_breakdown.waiting_time_score.toFixed(1) : '0.0'}
                                </span>
                            </div>
                            
                            
                            <div class="progress-container mt-2">
                                <div class="progress-bar-custom bg-primary" style="width: ${((doctorData.attendance_score || 0)/15)*100}%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="metric-label">Attendance Score</span>
                                <span class="score-badge ${getScoreClass(doctorData.attendance_score || 0, 15)}">
                                    ${doctorData.attendance_score ? doctorData.attendance_score.toFixed(1) : '0.0'}/15
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('#detail-content').html(detailHtml);
            
            // Toggle for shift log
            $('.shift-log-toggle').on('click', function() {
                const content = $(this).closest('.metric-row').next('.shift-log-content');
                const icon = $(this).find('.fa');
                
                content.slideToggle(300, function() {
                    if (content.is(':visible')) {
                        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    } else {
                        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }
                });
            });

            // Toggle for revenue breakdown
            $('.revenue-breakdown-toggle').on('click', function() {
                const content = $(this).closest('.metric-row').next('.revenue-breakdown-content');
                const icon = $(this).find('.fa');
                
                content.slideToggle(300, function() {
                    if (content.is(':visible')) {
                        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                    } else {
                        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                    }
                });
            });
            
            // Click handlers for details toggles
            $('.therapy-details-toggle').on('click', function() {
                const tableHtml = generateAggregateTable(window.therapyAggregates, 'Therapy');
                $('#aggregateModalLabel').text('Therapy Item Details');
                $('#aggregate-content').html(tableHtml);
                $('#aggregateModal').modal('show');
                $('.dataTable').DataTable({
                    paging: false,
                    searching: false,
                    info: true,
                    lengthChange: false,
                    dom: 'Bfrtip',
                });
            });

            $('.test-details-toggle').on('click', function() {
                const tableHtml = generateAggregateTable(window.testAggregates, 'Test');
                $('#aggregateModalLabel').text('Test Item Details');
                $('#aggregate-content').html(tableHtml);
                $('#aggregateModal').modal('show');
                $('.dataTable').DataTable({
                    paging: false,
                    searching: false,
                    info: true,
                    lengthChange: false,
                    dom: 'Bfrtip',
                });
            });

            $('.us-details-toggle').on('click', function() {
                const tableHtml = generateAggregateTable(window.usAggregates, 'US Supplement');
                $('#aggregateModalLabel').text('US Supplement Item Details');
                $('#aggregate-content').html(tableHtml);
                $('#aggregateModal').modal('show');
                $('.dataTable').DataTable({
                    paging: false,
                    searching: false,
                    info: true,
                    lengthChange: false,
                    dom: 'Bfrtip',
                });
            });

            $('.ipd-details-toggle').on('click', function() {
                const tableHtml = generateIpdPatientTable(window.ipdPatientDetails);
                $('#aggregateModalLabel').text('IPD Patient Details');
                $('#aggregate-content').html(tableHtml);
                $('#aggregateModal').modal('show');
                $('.dataTable').DataTable({
                    paging: false,
                    searching: false,
                    info: true,
                    lengthChange: false,
                    dom: 'Bfrtip',
                });
            });

            $('.other-products-details-toggle').on('click', function() {
                const tabHtml = `
                <strong class="d-block mb-3">Category-wise Breakdown</strong>
                ${generateOtherProductsCategoryTable(window.otherProductsCategoryAggregates)}
                <hr>
                <strong class="d-block mb-3">Product-wise Breakdown</strong>
                ${generateAggregateTable(window.otherProductsAggregates, 'Other Products')}
                `;
                
                $('#aggregateModalLabel').text('Other Products Details');
                $('#aggregate-content').html(tabHtml);
                $('#aggregateModal').modal('show');
                $('.dataTable').DataTable({
                    paging: false,
                    searching: false,
                    info: true,
                    lengthChange: false,
                    dom: 'Bfrtip',
                });
            });
            
            $('#detailModal').modal('show');
        }

        // Function to generate aggregate table with footer totals (for products)
        function generateAggregateTable(data, type) {
            if (!data || data.length === 0) {
                return `<div class="text-center text-muted py-4">No ${type} data available</div>`;
            }
            
            let totalPrescribed = 0;
            let totalSold = 0;
            let totalRevenue = 0;
            
            let tableHtml = `
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm dataTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>Item Name</th>
                                <th>Prescribed Count</th>
                                <th>Sold Count</th>
                                <th>Revenue</th>
                                <th>Conversion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(item => {
                const conversion = item.prescribed_count > 0 ? ((item.sold_count / item.prescribed_count) * 100).toFixed(2) : 0;
                totalPrescribed += item.prescribed_count;
                totalSold += item.sold_count;
                totalRevenue += item.revenue;
                
                tableHtml += `
                    <tr>
                        <td style="width: 35%">${item.product_name}</td>
                        <td>${item.prescribed_count}</td>
                        <td>${item.sold_count}</td>
                        <td>৳${item.revenue.toFixed(2)}</td>
                        <td>${conversion}%</td>
                    </tr>
                `;
            });
            
            const overallConversion = totalPrescribed > 0 ? ((totalSold / totalPrescribed) * 100).toFixed(2) : 0;
            
            tableHtml += `
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td><strong>Total (${data.length} Items)</strong></td>
                                <td><strong>${totalPrescribed}</strong></td>
                                <td><strong>${totalSold}</strong></td>
                                <td><strong>৳${totalRevenue.toFixed(2)}</strong></td>
                                <td><strong>${overallConversion}%</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
            
            return tableHtml;
        }

        // Function to generate IPD patient table
        function generateIpdPatientTable(data) {
            if (!data || data.length === 0) {
                return `<div class="text-center text-muted py-4">No IPD data available</div>`;
            }
            
            let totalPrescribed = 0;
            let totalSold = 0;
            let totalRevenue = 0;
            
            let tableHtml = `
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm dataTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>Patient Name</th>
                                <th>Mobile</th>
                                <th>Prescribed Days</th>
                                <th>Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(patient => {
                totalPrescribed += patient.prescribed_count || 0;
                totalSold += patient.sold_count || 0;
                totalRevenue += patient.revenue || 0;
                
                tableHtml += `
                    <tr>
                        <td style="width: 30%">${patient.patient_name}</td>
                        <td>${patient.mobile || ''}</td>
                        <td>${patient.prescribed_days || 0}</td>
                        <td>৳${(patient.revenue || 0).toFixed(2)}</td>
                    </tr>
                `;
            });
            
            const overallConversion = totalPrescribed > 0 ? ((totalSold / totalPrescribed) * 100).toFixed(2) : 0;
            
            tableHtml += `
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td><strong>Total (${data.length} Patients)</strong></td>
                                <td></td>
                                <td></td>
                                <td><strong>৳${totalRevenue.toFixed(2)}</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
            
            return tableHtml;
        }

        // Helper function for category table
        function generateOtherProductsCategoryTable(data) {
            if (!data || data.length === 0) {
                return '<div class="text-center text-muted py-4">No category data available</div>';
            }
            
            let totalPrescribed = 0;
            let totalSold = 0;
            let totalRevenue = 0;
            
            let tableHtml = `
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm dataTable">
                        <thead class="thead-dark">
                            <tr>
                                <th>Category Name</th>
                                <th>Prescribed Count</th>
                                <th>Sold Count</th>
                                <th>Revenue</th>
                                <th>Conversion Rate</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.forEach(item => {
                const conversion = item.prescribed_count > 0 ? ((item.sold_count / item.prescribed_count) * 100).toFixed(2) : 0;
                totalPrescribed += item.prescribed_count;
                totalSold += item.sold_count;
                totalRevenue += item.revenue;
                
                tableHtml += `
                    <tr>
                        <td style="width: 35%">${item.category_name}</td>
                        <td>${item.prescribed_count}</td>
                        <td>${item.sold_count}</td>
                        <td>৳${item.revenue.toFixed(2)}</td>
                        <td>${conversion}%</td>
                    </tr>
                `;
            });
            
            const overallConversion = totalPrescribed > 0 ? ((totalSold / totalPrescribed) * 100).toFixed(2) : 0;
            
            tableHtml += `
                </tbody>
                <tfoot>
                    <tr class="bg-light">
                        <td><strong>Total (${data.length} Categories)</strong></td>
                        <td><strong>${totalPrescribed}</strong></td>
                        <td><strong>${totalSold}</strong></td>
                        <td><strong>৳${totalRevenue.toFixed(2)}</strong></td>
                        <td><strong>${overallConversion}%</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    `;
    
    return tableHtml;
    }

        // Update charts
        function updateCharts(data) {
            // Destroy existing charts if they exist
            if (scoreChart) scoreChart.destroy();
            if (categoryChart) categoryChart.destroy();
            
            // Prepare data for score distribution chart
            const scoreRanges = {
                'Excellent (90-100)': 0,
                'Good (80-89)': 0,
                'Average (70-79)': 0,
                'Needs Improvement (60-69)': 0,
                'Poor (<60)': 0
            };
            
            data.forEach(doctor => {
                const score = doctor.total_score;
                if (score >= 90) scoreRanges['Excellent (90-100)']++;
                else if (score >= 80) scoreRanges['Good (80-89)']++;
                else if (score >= 70) scoreRanges['Average (70-79)']++;
                else if (score >= 60) scoreRanges['Needs Improvement (60-69)']++;
                else scoreRanges['Poor (<60)']++;
            });
            
            // Score distribution chart
            const scoreCtx = document.getElementById('scoreChart').getContext('2d');
            scoreChart = new Chart(scoreCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(scoreRanges),
                    datasets: [{
                        data: Object.values(scoreRanges),
                        backgroundColor: [
                            '#00a65a', // Green
                            '#00c0ef', // Light blue
                            '#f39c12', // Yellow
                            '#f56954', // Red
                            '#d2d6de'  // Gray
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: {
                        position: 'bottom'
                    }
                }
            });
            
            // Prepare data for category performance chart
            const categories = ['Attendance', 'Patient', 'Satisfaction', 'Revenue'];
            const avgScores = categories.map(category => {
                const key = category.toLowerCase() + '_score';
                return data.length > 0 ? 
                    data.reduce((sum, doctor) => sum + doctor[key], 0) / data.length : 0;
            });
            
            const maxScores = [15, 30, 25, 35];
            
            // Category performance chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            categoryChart = new Chart(categoryCtx, {
                type: 'radar',
                data: {
                    labels: categories,
                    datasets: [{
                        label: 'Average Performance',
                        data: avgScores,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)'
                    }, {
                        label: 'Maximum Possible',
                        data: maxScores,
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderColor: 'rgba(255, 132, 132, 1)',
                        pointBackgroundColor: 'rgba(255, 99, 132, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scale: {
                        ticks: {
                            beginAtZero: true,
                            max: Math.max(...maxScores)
                        }
                    }
                }
            });
        }
    </script>
@endsection