@extends('clinic::layouts.app2')
@section('title')Doctor Profile Dashboard @endsection
@section('content')

    <!-- Content Header -->
    <section class="content-header">
        <h1>Doctor Profile Summary</h1>
    </section>

    <!-- Main content -->
    <section class="content">
        {{-- Filter Form --}}
        <form method="GET" action="{{ route('doctor.profile.summary') }}">
            @csrf
            @component('components.filters', ['title' => __('report.filters')])
                <div class="row">
                    {{-- Doctor Selection --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('doctor_id', __('clinic::lang.doctors') . ':') !!}
                            {!! Form::select('doctor_id', $doctors, request('doctor_id'), [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'doctor_id',
                                'placeholder' => __('lang_v1.all'),
                            ]) !!}
                        </div>
                    </div>

                    {{-- Date Range --}}
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('date_range', $start_date . ' - ' . $end_date, [
                                'placeholder' => __('lang_v1.select_a_date_range'),
                                'class' => 'form-control',
                                'id' => 'date_range',
                                'readonly',
                            ]) !!}
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div style="margin-top: 24px" class="col-md-3">
                        <button type="submit" class="btn btn-success">Filter</button>
                    </div>
                </div>
            @endcomponent
        </form>

        <!-- Doctor Profile Summary -->
        <div class="row">
            <div class="col-md-4">
                <!-- Patients Seen -->
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-aqua"><i class="fas fa-user-circle"></i></span>
                    <div class="info-box-content">
                      <span class="info-box-text">{{ __('Patients Seen') }}</span>
                      <span class="info-box-number" id="patientsSeenCount">{{ $patientsSeenCount }} Patients</span>
                    </div>
                    <!-- /.info-box-content -->
               </div>
                @php
                    $hours = floor($averageWaitingTime / 60);
                    $minutes = $averageWaitingTime % 60;
                    $formattedTime = $hours > 0 ? "{$hours} hr {$minutes} min" : "{$minutes} min";
                @endphp
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-green">
                        <i class="fas fa-hourglass-half"></i>
                         
                    </span>

                     <div class="info-box-content">
                       <span class="info-box-text">{{ __('Average Patient Waiting Time') }}</span>
                       <span class="info-box-number ">{{ $formattedTime }}</span>
                     </div>
                     <!-- /.info-box-content -->
                </div>

               <!-- Advice Given Summary -->
               <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">{{ $adviceSummary->sum('count') }} Advice Given</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered datatable-summary table-striped">
                            <thead>
                                <tr>
                                    <th>Advice Name</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($adviceSummary as $advice)
                                    <tr>
                                        <td>{{ $advice->advise_name }}</td>
                                        <td>{{ $advice->count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- IPD Admission Summary -->
                <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">{{ $ipdSummary->sum('count') }} IPD Admissions</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered datatable-summary table-striped">
                            <thead>
                                <tr>
                                    <th>Admission Days</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ipdSummary as $ipd)
                                    <tr>
                                        <td>{{ $ipd->admission_days }}</td>
                                        <td>{{ $ipd->count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Therapies Suggested -->
               <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">{{ $therapySummary->sum('count') }} Therapies Suggested</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered datatable-summary table-striped">
                            <thead>
                                <tr>
                                    <th>Therapy Name</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($therapySummary as $therapy)
                                    <tr>
                                        <td>{{ $therapy->therapy_name }}</td>
                                        <td>{{ $therapy->count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray">
                                <tr>
                                    <td>Average Therapy per Patient</td>
                                    <td>{{ $patientsSeenCount > 0 ? round($therapySummary->sum('count') / $patientsSeenCount, 2) : 0 }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Investigations Suggested -->
               <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">{{ $investigationSummary->sum('count') }} Investigations Suggested</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered datatable-summary table-striped">
                            <thead>
                                <tr>
                                    <th>Investigations Name</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($investigationSummary as $investigation)
                                    <tr>
                                        <td>{{ $investigation->test_name }}</td>
                                        <td>{{ $investigation->count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray">
                                <tr>
                                    <td>Average Investigation per Patient</td>
                                    <td>{{ $patientsSeenCount > 0 ? round($investigationSummary->sum('count') / $patientsSeenCount, 2) : 0 }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- USA Supplements Suggested -->
                <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">{{ $usSupplementsSummary->sum('count') }} USA Supplements Suggested</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered datatable-summary table-striped">
                            <thead>
                                <tr>
                                    <th>USA Supplement Name</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($usSupplementsSummary as $supplement)
                                    <tr>
                                        <td>{{ $supplement->x_medicine_name }}</td>
                                        <td>{{ $supplement->count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray">
                                <tr>
                                    <td>Average USA Supplements per Patient</td>
                                    <td>{{ $patientsSeenCount > 0 ? round($usSupplementsSummary->sum('count') / $patientsSeenCount, 2) : 0 }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- BD Medicines Suggested -->
                <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">{{ $bdMedicinSummary->sum('count') }} BD Medicines Suggested</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered datatable-summary table-striped">
                            <thead>
                                <tr>
                                    <th>BD Medicine Name</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bdMedicinSummary as $bdmedicin)
                                    <tr>
                                        <td>{{ $bdmedicin->x_medicine_name }}</td>
                                        <td>{{ $bdmedicin->count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray">
                                <tr>
                                    <td>Average BD Medicine per Patient</td>
                                    <td>{{ $patientsSeenCount > 0 ? round($bdMedicinSummary->sum('count') / $patientsSeenCount, 2) : 0 }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- Foreign Supplements Suggested -->
                <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">{{ $foreignSupplementsSummary->sum('count') }} Foreign Supplement Suggested</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered datatable-summary table-striped">
                            <thead>
                                <tr>
                                    <th>Foreign Supplement Name</th>
                                    <th>Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($foreignSupplementsSummary as $fmedicin)
                                    <tr>
                                        <td>{{ $fmedicin->x_medicine_name }}</td>
                                        <td>{{ $fmedicin->count }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray">
                                <tr>
                                    <td>Average Foreign Supplements per Patient</td>
                                    <td>{{ $patientsSeenCount > 0 ? round($foreignSupplementsSummary->sum('count') / $patientsSeenCount, 2) : 0 }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

            <div class="col-md-8">
                <!-- Patient Health Concern Bar Chart -->
                <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">Patient Health Concerns</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="healthConcernChart"></canvas>
                    </div>
                </div>

                <!-- Hidden data for jQuery -->
                <div id="health-concern-data" 
                data-categories='@json($patientHealthConsernCategory->pluck("category_name"))' 
                data-counts='@json($patientHealthConsernCategory->pluck("count"))'>
                </div>

                <!-- Patient Age Pie Chart -->
                <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">Patient Age Groups</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="ageGroupChart" style="max-height: 450px;"></canvas>
                    </div>
                </div>

                <!-- Hidden data for jQuery -->
                <div id="age-group-data" 
                    data-labels='@json($ageGroups->pluck("label"))' 
                    data-counts='@json($ageGroups->pluck("count"))'>
                </div>

                <!-- Patient Location Pie Chart -->
                <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">Patient Locations</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="locationChart" style="max-height: 450px;"></canvas>
                    </div>
                </div>

                <!-- Hidden data for jQuery -->
                <div id="location-data" 
                    data-labels='@json($divisionLabels)' 
                    data-counts='@json($divisionCounts)'>
                </div>

                <!-- Highest Waiting Times -->
                <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">Highest Waiting Times</h3>
                    </div>
                    <div class="box-body">
                        <table class="table table-bordered datatable-summary table-striped">
                            <thead>
                                <tr>
                                    <th>Patient Name</th>
                                    <th>Phone</th>
                                    <th>Appointment Date</th>
                                    <th>Waiting Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($highestWaitingTimes as $patient)
                                    @php
                                        $hours = floor($patient->waiting_time / 60);
                                        $minutes = $patient->waiting_time % 60;
                                        $formattedTime = $hours > 0 ? "{$hours} hr {$minutes} min" : "{$minutes} min";
                                    @endphp
                                    <tr>
                                        <td>{{ $patient->first_name }}</td>
                                        <td>{{ $patient->mobile }}</td>
                                        <td>{{ \Carbon\Carbon::parse($patient->confirm_time)->format('d-m-Y H:i') }}</td>
                                        <td>{{ $formattedTime }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4">No highest waiting times found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div> 

                <!-- Medicines and Therapies not in Clinic Software -->
                <div class="box box-solid">
                    <div class="box-body">
                        <div class="row">
                            <!-- Medicines Table -->
                            <div class="col-md-4">
                                <h4>Medicines Not in Clinic Software</h4>
                                <table class="table table-bordered table-striped datatable-summary">
                                    <thead>
                                        <tr>
                                            <th>Medicine Name</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($medicinesNotInSoftware as $medicine)
                                            <tr>
                                                <td>
                                                    <a href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'missingMedicineDetails'], [$medicine->name]) }}" class="view_medicine_detail">
                                                        {{ $medicine->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $medicine->count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Therapies Table -->
                            <div class="col-md-4">
                                <h4>Therapies Not in Clinic Software</h4>
                                <table class="table table-bordered table-striped datatable-summary">
                                    <thead>
                                        <tr>
                                            <th>Therapy Name</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($therapiesNotInSoftware as $therapy)
                                            <tr>
                                                <td>
                                                    <a href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'missingTherapyDetails'], [$therapy->name]) }}" class="view_therapy_detail">
                                                        {{ $therapy->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $therapy->count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Investigations Table -->
                            <div class="col-md-4">
                                <h4>Investigations Not in Clinic Software</h4>
                                <table class="table table-bordered table-striped datatable-summary">
                                    <thead>
                                        <tr>
                                            <th>Investigation Name</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($investigationsNotInSoftware as $investigation)
                                            <tr>
                                                <td>
                                                    <a href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'missingTestDetails'], [$investigation->name]) }}" class="view_test_detail">
                                                        {{ $investigation->name }}
                                                    </a>
                                                </td>
                                                <td>{{ $investigation->count }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Suggested BD Medicine By Brand Bar Chart -->
                <div class="box box-solid">
                    <div class="box-header pb-0">
                        <h3 class="box-title">Suggested BD Medicine By Brand</h3>
                    </div>
                    <div class="box-body">
                        <canvas id="bdMedicinChart"></canvas>
                    </div>
                </div>

                <!-- Hidden data for jQuery -->
                <div id="bd-medicin-data" 
                data-labels='@json($brandLabels)' 
                data-counts='@json($medicineCounts)'>
                </div>
            </div>
        </div>
        <div class="modal fade" id="view_test_detail_modal" tabindex="-1" role="dialog"></div>
        <div class="modal fade" id="view_medicine_detail_modal" tabindex="-1" role="dialog"></div>
        <div class="modal fade" id="view_therapy_detail_modal" tabindex="-1" role="dialog"></div>
    </section>

@stop

@section('javascript')
    <!-- Include necessary charts and filters JS libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Daterange picker initialization
        $('#date_range').daterangepicker(
            $.extend({}, dateRangeSettings, {
                startDate: moment('{{$start_date}}'),
                endDate: moment('{{$end_date}}'),
                maxDate: moment()
            }),
            function (start, end) {
                $('#date_range').val(start.format(moment_date_format) + ' - ' + end.format(moment_date_format));
            }
        );

        $(document).ready(function() {
            $('.datatable-summary').DataTable({
                "paging": true,
                "lengthChange": false,
                "pageLength": 10,
                "searching": false,
                "ordering": false,
                "info": false,
                "autoWidth": false,
                "dom": 'tp' // Only pagination
            });
        });
    $(document).ready(function() {
        // Get data from hidden div
        let categories = $('#health-concern-data').data('categories');
        let counts = $('#health-concern-data').data('counts');

        // Chart.js initialization
        let ctx = $('#healthConcernChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: categories,
                datasets: [{
                    label: 'Patient Count',
                    data: counts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
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
                }
            }
        });
    });
    
    $(document).ready(function() {
        // Get data from hidden div
        let labels = $('#bd-medicin-data').data('labels');
        let counts = $('#bd-medicin-data').data('counts');

        // Chart.js initialization
        let ctx = $('#bdMedicinChart');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'BD Medicin Count',
                    data: counts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
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
                }
            }
        });
    });

    $(document).ready(function() {
        // Get data from hidden div
        let labels = $('#age-group-data').data('labels');
        let counts = $('#age-group-data').data('counts');
        let combinedLabels = labels.map((label, index) => `${label} (${counts[index]})`);

        // Define contrasting colors for each age group
        const backgroundColors = [
            '#FF6384', // 1-10
            '#36A2EB', // 11-20
            '#FFCE56', // 21-30
            '#4CAF50', // 31-40
            '#9966FF', // 41-50
            '#FF9F40', // 51-60
            '#8E44AD', // 61-70
            '#27AEAE', // 71-80
            '#F39C12', // 81-90
            '#D35400'  // 91-100
        ];

        // Chart.js initialization
        let ctx = $('#ageGroupChart');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Patient Count by Age Group',
                    data: counts,
                    backgroundColor: backgroundColors,
                    hoverBackgroundColor: backgroundColors.map(color => shadeColor(color, -20)),
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'left',
                        labels: {
                            font: {
                                size: 18
                            },
                            color: '#333'
                        }
                    }
                }
            }
        });

        // Function to darken colors slightly for hover effect
        function shadeColor(color, percent) {
            let R = parseInt(color.substring(1, 3), 16);
            let G = parseInt(color.substring(3, 5), 16);
            let B = parseInt(color.substring(5, 7), 16);

            R = parseInt(R * (100 + percent) / 100);
            G = parseInt(G * (100 + percent) / 100);
            B = parseInt(B * (100 + percent) / 100);

            R = (R < 255) ? R : 255;
            G = (G < 255) ? G : 255;
            B = (B < 255) ? B : 255;

            let RR = ((R.toString(16).length == 1) ? "0" + R.toString(16) : R.toString(16));
            let GG = ((G.toString(16).length == 1) ? "0" + G.toString(16) : G.toString(16));
            let BB = ((B.toString(16).length == 1) ? "0" + B.toString(16) : B.toString(16));

            return "#" + RR + GG + BB;
        }
    });

    $(document).ready(function() {
        // Get data from hidden div
        let labels = $('#location-data').data('labels');
        let counts = $('#location-data').data('counts');
        let combinedLabels = labels.map((label, index) => `${label} (${counts[index]})`);

        // Define vibrant colors for each division
        const backgroundColors = [
            '#FF6384', // Barishal
            '#36A2EB', // Chattogram
            '#FFCE56', // Dhaka
            '#4CAF50', // Khulna
            '#9966FF', // Rajshahi
            '#FF9F40', // Rangpur
            '#8E44AD', // Mymensingh
            '#27AE60'  // Sylhet
        ];

        // Chart.js setup
        let ctx = $('#locationChart');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Patient Count by Division',
                    data: counts,
                    backgroundColor: backgroundColors,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'left',
                        labels: {
                            font: {
                                size: 18
                            },
                            color: '#333'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                let label = labels[tooltipItem.dataIndex];
                                let value = counts[tooltipItem.dataIndex];
                                return `${label}: ${value} patients`;
                            }
                        }
                    }
                }
            }
        });
    });

    $(document).ready(function() {
        $(document).on('click', '.view_test_detail', function(e) {
            e.preventDefault();
            $('#view_test_detail_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.view_medicine_detail', function(e) {
            e.preventDefault();
            $('#view_medicine_detail_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        $(document).on('click', '.view_therapy_detail', function(e) {
            e.preventDefault();
            $('#view_therapy_detail_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
    })
    </script>
@endsection
