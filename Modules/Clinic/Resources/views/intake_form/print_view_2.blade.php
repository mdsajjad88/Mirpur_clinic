<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/css/bootstrap-grid.min.css"
        integrity="sha512-Aa+z1qgIG+Hv4H2W3EMl3btnnwTQRA47ZiSecYSkWavHUkBF2aPOIIvlvjLCsjapW1IfsGrEO3FU693ReouVTA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        /* General Styles for Screen View */
        body {
            font-size: 14px;
            /* Default font size for normal text */
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        thead {
            font-size: 16px !important;
            /* Heading font size */
        }

        p,
        span,
        strong,
        tbody {
            font-size: 14px !important;
            /* Normal text font size */
        }

        .form-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .bg-color {
            background-color: #cadcdd;
        }

        .input-dcheck {
            height: 10px;
            width: 10px;
            margin-right: 5px;
        }

        .ques {
            width: 30% !important;
        }

        .ans {
            width: 70% !important;
        }

        /* Print Styles */
        @media print {
            body {
                width: 210mm;
                /* A4 width */
                height: 297mm;
                /* A4 height */
                margin: 0;
                padding: 0;
                font-size: 14px;
                visibility: hidden;
            }

            h1,
            h2,
            h3,
            h4,
            h5,
            h6,
            .custom-table th,
            .custom-diet-table th {
                font-size: 18px !important;
                /* Heading font size for print */
            }

            p,
            span,
            strong,
            .form-group,
            .custom-table td,
            .custom-diet-table td {
                font-size: 14px !important;
                /* Normal text font size for print */
            }

            .btn {
                display: none;
                /* Hide buttons in print view */
            }

            .page-break {
                page-break-before: always;
            }

            /* Ensure columns maintain their layout in print view */
            .row {
                display: flex !important;
                flex-wrap: wrap !important;
                margin: 0;
            }

            .col-lg-12 {
                width: 100% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-lg-6 {
                width: 50% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-lg-4 {
                width: 33.33% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-lg-8 {
                width: 66.66% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-lg-3 {
                width: 25% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-lg-2 {
                width: 16.66% !important;
                float: left !important;
                padding: 0 5px;
            }

            .col-md-5,
            .col-md-7 {
                flex: 0 0 auto !important;
                width: 50%;
            }

            .d-flex {
                display: flex !important;
            }

            p {
                margin: 0;
                /* Remove extra margins for printing */
            }

            .form-group {
                margin-bottom: 5px;
                /* Reduce margin for form groups */
            }

            .mt_print {
                margin-top: 2.7in !important;
                visibility: visible;
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }

            .print-none {
                display: none !important;
            }

            .select2-selection__arrow {
                display: none !important;
            }

            .line-container {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 100%;
            }

            .line {
                flex-grow: 1;
                border-top: 1px solid black;
            }

            .text {
                margin: 0 15px;
                font-size: 14px;
                /* Adjusted to match normal text size */
                font-weight: bold;
            }

            .custom-table {
                border-collapse: collapse;
                width: 100%;
            }

            .custom-table th,
            .custom-table td {
                border: 1px solid #2d2a2a;
                /* Custom border color and width */
                padding: 5px;
                text-align: center;
            }

            .custom-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }

            .custom-table td {
                background-color: #fff;
            }

            .custom-table tr:nth-child(even) td {
                background-color: #f9f9f9;
            }

            .custom-diet-table {
                border-collapse: collapse;
                width: 100%;
            }

            .custom-diet-table th,
            .custom-diet-table td {
                border: 1px solid #2d2a2a;
                /* Custom border color */
                padding: 5px;
                text-align: center;
            }

            .custom-diet-table th {
                background-color: #f2f2f2;
                font-weight: bold;
            }

            .custom-diet-table td {
                background-color: #fff;
            }

            .custom-diet-table tr:nth-child(even) td {
                background-color: #f9f9f9;
            }

            .ques {
                width: 30% !important;
            }

            .ans {
                width: 70% !important;
            }

            @page {
                size: A4 portrait;
                /* Set page size and orientation */
                margin: 5mm;
                /* Set minimum margin */
            }
        }
    </style>
</head>
<body onload="handlePrint()">
    <div class="row mt_print">
        <div class="col-lg-12">
            <div class="form-header print-none">
                <h2>Health Information</h2>
                <p>Please provide medical and laboratory test reports from the past 8 years with this form.</p>
            </div>

            {!! Form::open(['url' => 'submit-form', 'method' => 'post']) !!}

            <!-- Personal Information Section for Print -->
            <div class="container mt-4">
                <div class="row">
                    <div class="col-lg-3">
                        <strong>Date:</strong>
                        <span>{{ $report->last_visited_date ? \Carbon\Carbon::parse($report->last_visited_date)->format('Y-m-d') : 'N/A' }}</span>
                    </div>
                    <div class="col-lg-3">
                        <strong>Name:</strong>
                        <span>{{ $report->first_name . ' ' . $report->last_name ?? 'N/A' }}</span>
                    </div>
                    <div class="col-lg-3">
                        <strong>Age:</strong>
                        <span>{{ $report->age ?? 'N/A' }}</span>
                    </div>
                    <div class="col-lg-3">
                        <strong>Gender:</strong>
                        <span>{{ $report->gender == 'male' ? 'Male' : ($report->gender == 'female' ? 'Female' : 'Others') }}</span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-3">
                        <strong>Profession:</strong>
                        <span>{{ $report->profession ?? 'N/A' }}</span>
                    </div>
                    <div class="col-lg-3">
                        <strong>Blood Group:</strong>
                        <span>{{ $report->blood_group ? $report->blood_group : 'N/A' }}</span>
                    </div>
                    <div class="col-lg-3">
                        <strong>Marital Status:</strong>
                        <span>{{ $report->marital_status == 'married' ? 'Married' : 'Unmarried' }}</span>
                    </div>
                    <div class="col-lg-3">
                        <strong>Email:</strong>
                        <span>{{ $report->email ? $report->email : 'N/A' }}</span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-6">
                        <strong>Address:</strong>
                        <span>
                            {{ implode(', ', array_filter([$report->address, $report->district?->name, $report->division?->name])) ?: 'N/A' }}
                        </span>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-12">
                        <div class="line-container">
                            <div class="line"></div>
                            <h4 class="text">Health Concerns</h4>
                            <div class="line"></div>
                        </div>
                    </div>
                </div>

                <div class="row mt-1 align-items-start">
                    <!-- Left Column (40% width for label) -->
                    <div class="d-flex">
                        <div style="flex-basis: 35%;">
                            <strong style="margin-left:-6px">What is your primary health concern(s)?:</strong>
                        </div>
                    
                        <!-- Right Column (60% width for the problems list) -->
                        <div style="flex-basis: 65%;">
                            @php
                                $problemIds = $select_problems->pluck('problem_id')->toArray();
                                $checkedProblems = $problems->filter(fn($problem) => in_array($problem->id, $problemIds));
                            @endphp
                    
                            <div class="row">
                                @foreach ($checkedProblems as $problem)
                                    <div class="col-md-4">
                                        <p>✔ {{ $problem->name ?? $problem->name }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-1 align-items-start">
                    <div class="col-lg-12">
                        <p><strong>Description (optional) : </strong>
                            {{ $report->main_disease ? $report->main_disease : 'N/A' }}</p>
                    </div>
                </div>
                


                <div class="row mt-1">

                    <div class="col-lg-12">
                        <p><strong>How long have you been experiencing this issue(s)?:</strong> &nbsp;&nbsp;&nbsp;&nbsp;
                            {{ $report->main_disease_duration_day ? $report->main_disease_duration_day . ' Days' : 'N/A' }}
                        </p>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-12">
                        <div class="line-container">
                            <div class="line"></div>
                            <h4 class="text"> Medical History</h4>
                            <div class="line"></div>
                        </div>
                    </div>
                </div>

                <div class="row mt-3">
                    @php
                        $chironicIll = is_string($report->chironic_illness)
                            ? json_decode($report->chironic_illness, true)
                            : $report->chironic_illness;
                        if (!is_array($chironicIll)) {
                            $chironicIll = explode(',', $report->chironic_illness);
                        }
                    @endphp
                    <div class="col-lg-12">
                        <strong>Did you have any chronic illness from childhood?:</strong>
                        <span>
                            @if ($report->childhood_fitness_good == 1)
                                ✘ No
                            @else
                                &diams; Yes
                                <div class="row">
                                    @foreach ($chironic_diseases as $disease)
                                        @if (in_array($disease->id, $chironicIll))
                                            <div class="col-md-3 mb-2">
                                                <p> ✔{{ $disease->name }}</p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row mt-1 align-items-center">
                    <div class="col-lg-12 d-flex">
                        <div class="flex-shrink-0" style="width: 62%;">
                            <strong>
                                Does your family have any history of health conditions or disease?:
                            </strong>
                        </div>
                        @if ($report->is_family_disease == 1)
                            <div class="flex-grow-1" style="width: 38%;">
                                @php
                                    $familyHistoryDiseases = is_string($report->family_history_disease)
                                        ? json_decode($report->family_history_disease, true)
                                        : $report->family_history_disease;

                                    if (!is_array($familyHistoryDiseases)) {
                                        $familyHistoryDiseases = explode(',', $report->family_history_disease);
                                    }

                                    // Check for matching diseases
                                    $hasMatches = $family_problems->contains(function ($disease) use (
                                        $familyHistoryDiseases,
                                    ) {
                                        return in_array($disease->id, $familyHistoryDiseases);
                                    });
                                @endphp

                                <div>
                                    @if ($hasMatches)
                                        @foreach ($family_problems as $disease)
                                            @if (in_array($disease->id, $familyHistoryDiseases))
                                                <span class="me-2">✔{{ $disease->name }}</span>
                                            @endif
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                                </div>
                            </div>
                        @else
                            N/A
                        @endif

                    </div>
                </div>
                <div class="row mt-1">
                    <div class="col-lg-12">
                        <strong>Are you taking treatment with any other doctor / hospital ?: </strong>
                        <span>
                            @if ($report->is_other_doctor_reference == 0)
                                N/A
                            @else
                                ✔ Yes
                                @if (!empty($report->reference_doctor_id))
                                    <div class="row mt-2">
                                        <p>
                                            {{ optional($report->reference_doctor)->dr_name }}
                                            @if (!empty(optional($report->reference_doctor)->hospital_name))
                                                , {{ $report->reference_doctor->hospital_name }}
                                            @endif
                                        </p>

                                    </div>
                                @else
                                    N/A
                                @endif
                            @endif
                        </span>
                    </div>
                </div>

                <div class="row mt-1">
                    <div class="col-lg-12">
                        <p><b>List all medications or supplement(for more than 1 month):</b>
                            @if($report->is_old_prescribed_medicine == 1)
                            @if (!empty($old_medicines) && count($old_medicines) > 0)
                                {{ implode(', ', $old_medicines->toArray()) }}
                            @else
                                N/A
                            @endif
                            @else
                                ✘ No
                            @endif

                        </p>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-12">
                        <div class="line-container">
                            <div class="line"></div>
                            <h4 class="text">Lifestyle & Habits</h4>
                            <div class="line"></div>
                        </div>
                    </div>
                </div>
                <!-- Sleep Hours and Exercise Minutes Section -->
                <div class="row mt-2">
                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <div style="flex-basis: 78%;">
                            <strong style="margin-left:-6px">How many minutes do you exercise daily? :</strong>
                        </div>
                        <div style="flex-basis: 21%;">
                            {{ $report->daily_exercize_minute ? $report->daily_exercize_minute . ' Minutes' : 'N/A' }}
                        </div>
                    </div>

                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <div style="flex-basis: 78%;">
                            <strong>How many hours do you sleep per night? :</strong>
                        </div>
                        <div style="flex-basis: 21%;">
                            {{ $report->daily_sleeping_hourse ? $report->daily_sleeping_hourse . ' Hours' : 'N/A' }}
                        </div>
                    </div>
                </div>

                <!-- Sleep Problems Section -->
                <div class="row mt-3">
                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <div style="flex-basis: 78%;">
                            <strong style="margin-left:-6px">Do you have trouble sleeping at night? :</strong>
                        </div>
                        <div style="flex-basis: 21%;">
                            @if ($report->is_sleeping_problem == 1)
                                ✔ Yes
                            @elseif ($report->is_sleeping_problem == 0) 
                                ✘ No
                            @else
                                N/A
                            @endif
                        </div>
                    </div>

                    @if ($report->is_sleeping_problem == 1)
                        <div class="col-md-6 d-flex justify-content-between align-items-center">
                            <div style="flex-basis: 78%;">
                                <strong>If yes, why do you sleep less at night? :</strong>
                            </div>
                            <div style="flex-basis: 21%;">
                                {{ !empty($report->reason_for_less_sleeping) ? $report->reason_for_less_sleeping : 'N/A' }}
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Stress or Anxiety Section -->
                <div class="row mt-3">
                    <div class="col-md-6 d-flex justify-content-between align-items-center">
                        <div style="flex-basis: 78%;">
                            <strong style="margin-left:-6px">Do you have chronic stress or anxiety? :</strong>
                        </div>
                        <div style="flex-basis: 21%;">
                            @if ($report->is_mentally_stress === 1)
                                ✔ Yes
                            @elseif ($report->is_mentally_stress === 0)
                                ✘ No
                            @else
                                N/A
                            @endif

                        </div>
                    </div>
                </div>


                <table class="custom-diet-table mt-3" style="width: 100%;">
                    <thead>
                        <tr>
                            <th colspan="2" class="text-center" style="padding: 10px;">
                                Diet Discussion <p>List all the food and drinks consumed in the past day</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="ques"><strong>Breakfast</strong></td>
                            <td class="ans">{{ $report->breakfast ? $report->breakfast : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="ques"><strong>Lunch</strong></td>
                            <td class="ans">{{ $report->lunch ? $report->lunch : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="ques"><strong>Afternoon Snacks</strong></td>
                            <td class="ans">{{ $report->afternoon_snaks ? $report->afternoon_snaks : 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td class="ques"><strong>Dinner</strong></td>
                            <td class="ans">{{ $report->dinner ? $report->dinner : 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {!! Form::close() !!}
            <div class="col mt-1">
                <div class="form-group text-center">
                    <button type="button" class="btn btn-success" onclick="printForm()">Print Report</button>
                </div>
            </div>
        </div>
        <div class="col-md-12 mt-3">
            <div class="row">
                <div class="col-md-1"></div>
                <div class="col-md-10" style="text-align: center">
                    <i>[আপনার ইউজার আইডি <b> {{ $report->contact->contact_id }}</b> । আপনি এই ইউজার আইডি ব্যবহার করে
                        আমাদের ক্লিনিকের সকল সার্ভিস নিতে পারবেন।] </i>
                </div>
                <div class="col-md-1"></div>
            </div>
        </div>
    </div>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/js/bootstrap.min.js"
        integrity="sha512-EKWWs1ZcA2ZY9lbLISPz8aGR2+L7JVYqBAYTq5AXgBkSjRSuQEGqWx8R1zAX16KdXPaCjOCaKE8MCpU0wcHlHA=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <script>
        function handlePrint() {
            window.print();
            window.onafterprint = function() {
                window.history.back();
            };
        }
    </script>
</body>

</html>
