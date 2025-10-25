<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.0.1/css/bootstrap-grid.min.css"
        integrity="sha512-Aa+z1qgIG+Hv4H2W3EMl3btnnwTQRA47ZiSecYSkWavHUkBF2aPOIIvlvjLCsjapW1IfsGrEO3FU693ReouVTA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />


    <style>
        .patient_info_section {
            border: 1px solid #000000;
            border-radius: 25px;
        }

        /* Define your Avenir font family */
        @font-face {
            font-family: 'Avenir';
            src: url('path/to/avenir-regular.woff2') format('woff2'),
                url('path/to/avenir-regular.woff') format('woff');
            font-weight: normal;
            font-style: normal;
        }

        /* Apply the font to your page */
        body {
            font-family: 'Avenir', sans-serif;
            font-size: 14px;
            line-height: 1.5;
        }

        h1,
        h2,
        h3,
        h4 {
            font-family: 'Avenir', sans-serif;
            font-weight: bold;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .container {
                width: 100%;
            }

            .row.mt-1 {
                display: flex;
                align-items: center;
            }

            /* Adjust Table-like Rows */
            .row.mt-1 .col-md-1,
            .row.mt-1 .col-md-5,
            .row.mt-1 .col-md-4,
            .row.mt-1 .col-md-2,
            .col-md-8,
            .col-md-4, span,
            li,
            p,
            td {
                font-size: 11px;
                font-family: 'Avenir', sans-serif;

            }

            .row.mt-1 {
                margin-top: 10px;
            }

            /* Page Breaks Between Sections */
            .row.mb-4,
            .row.mt-1 {
                margin-top: 20px;
            }

            #medicine_section {
                min-height: 400px;
                margin-left: 30px !important;
            }

            .text-right {
                text-align: right !important;
            }

            .with-line {
                border-bottom: 1px solid rgb(218, 204, 204);
            }

            .dr_name {
                font-size: 24px !important;
                font-weight: bold !important;
                margin-top: 5px !important;
                padding: 0;
            }

            .dr_name,
            b {
                margin: 0;
                padding: 0;
            }

            b {
                margin-top: 0;
                /* Ensure no extra top margin */
            }


            .prescription_icon {
                font-size: 30px !important;
                color: gainsboro;
                padding-left: 5px;
                margin-top: 20px;
            }

            .doctor_info {
                min-height: 110px;
            }

            .custom-padding td {
                padding-left: 0 !important;
                padding-right: 0 !important;
                padding-top: 0 !important;
            }
        }
    </style>
</head>

<body onload="handlePrint()">
    <div class="container">
        <div class="row" style="padding: 0 30px;">
            <div class="col-md-12">
                <div class="row mb-4 doctor_info">
                    <div class="col-md-3">
                    </div>
                    <div class="col-md-9 text-right">
                        <p class="dr_name"> {{ $prescription->assigned_doctor_first_name }}
                            {{ $prescription->assigned_doctor_last_name }}
                        </p> 
                            {!! $prescription->doctor_degrees !!}
                        {{-- <b class="font-weight-bold">
                            {!! str_replace(',', '<br>', $prescription->doctor_specialities) !!}
                        </b> --}}
                    </div>
                </div>
                <div class="row patient_info_section" style="text-align: center;">
                    <div class="col-md-4 ">
                        <p>Patient Name</p>
                        <b>{{ $prescription->patient_first_name }} {{ $prescription->patient_last_name ?? '' }} {{$prescription->customerId ? '('.$prescription->customerId.')' : ''}}</b>
                    </div>
                    <div class="col-md-1 ">
                        <p>Age</p>
                        <p>{{ $prescription->age }}</p>
                    </div>
                    <div class="col-md-1 ">
                        <p>Sex</p>
                        <p>{{ ucfirst($prescription->gender) }}</p>
                    </div>
                    <div class="col-md-2 ">
                        <p>Date</p>
                        <p>{{ date('d/m/Y', strtotime($prescription->visit_date)) }}</p>
                    </div>
                    <div class="col-md-4 d-flex align-items-center justify-content-center"  style="padding-right: 10px;">
                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($prescription->customerId, 'C39', 1.0, 30, [39, 48, 54], false) }}" 
                             style="max-width: 100%; height: auto; border: 1px solid #ddd; padding: 5px; background: white;">
                    </div>                    
                </div>

            </div>
        </div>
        <div class="row" style="padding: 0 30px;">
            <div class="col-md-12">
                <div class="row">
                    <div style="padding-left: 0; padding-right: 0" class="col-md-3">
                        <div style="display: flex; flex-direction: column; gap: 0;">
                            <h3 style="margin-bottom: 0;">C/C:</h3>
                            <p style="margin-top: 0; margin-bottom: 0;">
                                {!! str_replace(',', '<br>', $prescription->complains) !!}
                            </p>
                            <br>
                        </div>

                        <h3 style="margin-bottom: 5px;">O/E:</h3>
                        <table class="table table-borderless custom-padding">
                            @if (!empty($prescription->current_height_feet))
                                <tr>
                                    <td>Height:</td>
                                    <td>{{ $prescription->current_height_feet }} ft @if (!empty($prescription->current_height_inches)){{ $prescription->current_height_inches }}" @endif</td>
                                </tr>
                            @endif
                            @if (!empty($prescription->current_weight))
                                <tr>
                                    <td>Weight:</td>
                                    <td>{{ $prescription->current_weight }} kg</td>
                                </tr>
                            @endif
                            @if (!empty($prescription->current_height_feet) && !empty($prescription->current_weight))
                            @php
                                $height_cm = ($prescription->current_height_feet * 30.48) + ($prescription->current_height_inches * 2.54);
                            @endphp
                                <tr>
                                    <td>BMI:</td>
                                    <td>
                                        {{ number_format($prescription->current_weight / pow($height_cm / 100, 2), 1) }} kg/m²
                                    </td>
                                </tr>
                            @endif
                            @if (!empty($prescription->pulse_rate))
                                <tr>
                                    <td>Pulse Rate:</td>
                                    <td>{{ $prescription->pulse_rate }} bpm</td>
                                </tr>
                            @endif
                            @if (!empty($prescription->systolic_pressure))
                                <tr>
                                    <td>Blood Pressure:</td>
                                    <td>{{ $prescription->systolic_pressure }}/{{ $prescription->diastolic_pressure ?? '' }} mmHg</td>
                                </tr>
                            @endif
                            @if (!empty($prescription->respiratory))
                                <tr>
                                    <td>Respiratory:</td>
                                    <td>{{ $prescription->respiratory }} b/m</td>
                                </tr>
                            @endif
                            @if (!empty($prescription->body_temp))
                                <tr>
                                    <td>Body temp:</td>
                                    <td>{{ $prescription->body_temp }} °F</td>
                                </tr>
                            @endif
                            @if (!empty($prescription->body_fat_percent))
                                <tr>
                                    <td>Body Fat:</td>
                                    <td>{{ $prescription->body_fat_percent }} %</td>
                                </tr>
                            @endif
                            @if (!empty($prescription->fat_mass_percent))
                                <tr>
                                    <td>Fat Mass:</td>
                                    <td>{{ $prescription->fat_mass_percent }} %</td>
                                </tr>
                            @endif
                            @if (!empty($prescription->lean_mass_percent))
                                <tr>
                                    <td>Lean Mass:</td>
                                    <td>{{ $prescription->lean_mass_percent }} %</td>
                                </tr>
                            @endif
                            @if (!empty($prescription->comments))
                                <tr>
                                    <td>Comments:</td>
                                    <td>{{ $prescription->comments }}</td>
                                </tr>
                            @endif
                        </table>
                        <br>
                        @if (!$InvestigationHistory->isEmpty())
                        <div style="display: flex; flex-direction: column; gap: 0;">
                            <!-- Heading -->
                            <h3 style="margin-bottom: 0;">I/H:</h3>
                            <ol style="margin-left: 0; padding-left: 15px; margin-top: 5px">
                                @foreach ($InvestigationHistory as $history)
                                    <li>
                                       <small>{{ $history->date }}:</small> {{ $history->test_name }} ({{ $history->result_value }})
                                    </li>
                                @endforeach
                            </ol> 
                        </div>
                        @endif
                        @if (!$diseaseHistories->isEmpty())
                        <div style="display: flex; flex-direction: column; gap: 0;">
                            <h3 style="margin-bottom: 0;">Dx:</h3>
                            <ol style="margin-left: 0; padding-left: 15px; margin-top: 5px">
                                @forelse ($diseaseHistories as $history)
                                    <li>{{ $history->chief_complaint }}</li>
                                @empty
                                @endforelse
                            </ol>
                        </div> 
                        @endif
                        @if (!$prescribedTest->isEmpty())
                        <div style="display: flex; flex-direction: column; gap: 0;">
                            <h3 style="margin-bottom: 0;">Investigation:</h3>
                            <ol style="margin-left: 0; padding-left: 15px; margin-top: 5px">
                                @forelse ($prescribedTest as $test)
                                    <li>{{ str_replace('Govt Fixed Rate', '', $test->test_name) }}
                                        @if (!empty($test->comment))
                                            ({{ $test->comment }})
                                        @endif
                                    </li>
                                @empty
                                @endforelse
                            </ol>
                        </div>
                        @endif


                    </div>
                    <div class="col-md-9">
                        <div id="medicine_section">
                            <div class="row">
                                <div class="col-md-12">
                                    <i class="fas fa-prescription prescription_icon"></i>
                                </div>
                            </div>

                            <div style="padding-left:20px;">
                                @if ($prescribedMedicines && $prescribedMedicines->isNotEmpty())
                                    @foreach ($prescribedMedicines as $medicine)
                                        <div class="row mt-1 {{ $loop->last ? '' : 'with-line' }}">
                                            <div style="padding: 2px;" class="col-md-1">{{ $loop->iteration }}</div>
                                            <div style="padding: 2px;" class="col-md-11">
                                                <div class="row">
                                                    <div style="padding: 2px;" class="col-md-12">
                                                        @if ($common_settings['show_medicine_name_as'] == 'generic')
                                                            {{ $medicine->generic_name ?? '-' }}
                                                        @else
                                                            {{ $medicine->x_medicine_name ?? '-' }}
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div style="padding: 2px;" class="col-md-2">
                                                        {{ $medicine->dosage ?? '-' }}
                                                    </div>
                                                    <div style="padding: 2px;" class="col-md-4">
                                                        <span>{{ $medicine->medicine_meal ?? '-' }}</span>
                                                    </div>
                                                    <div style="padding: 2px;" class="col-md-2">
                                                        {{ $medicine->medication_duration ?? '-' }}
                                                    </div>
                                                    <div style="padding: 2px;" class="col-md-4">
                                                        {{ $medicine->comment ?? '' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    
                                @endif
                            </div>
                            

                        </div>
                        @if(!empty($prescription->advices))
                        <div id="advice_section" style="margin-left: 40px;">
                            <h3 style="margin-bottom: 5px;">Advice:</h3> <!-- Reduce spacing below the heading -->
                            <ol style="padding-left: 20px; margin-top: 0; margin-bottom: 0;">
                                @foreach (explode(',', $prescription->advices) as $advice)
                                    <li style="margin-bottom: 2px;">{{ trim($advice) }}</li>
                                    <!-- Reduce space between items -->
                                @endforeach
                            </ol>
                        </div>
                        @endif
                        @if ($ipdAdmission && $ipdAdmission->is_ipd_admission == 1)
                            <div id="advice_section" style="margin-left: 40px;">
                                @if (empty($prescription->advices))
                                    <h3 style="margin-bottom: 5px;">Advice:</h3>
                                @endif
                                <ul style="padding-left: 20px; margin-top: 0; margin-bottom: 0;">
                                    <li>Admission in IPD for {{ $ipdAdmission->admission_days }} days</li>
                                </ul>
                            </div>
                        @endif
                        @if(!empty($prescription->therapies))
                        <div id="therapy_section" style="margin-left: 40px;">
                            <h3 style="margin-bottom: 5px;">Therapy:</h3>
                            <!-- Reduce spacing below the heading -->
                            <ol style="padding-left: 20px; margin-top: 0; margin-bottom: 0;">
                                @forelse ($prescribedTherapy as $therapy)
                                    <li style="margin-bottom: 2px;">{{ $therapy->therapy_name }} 
                                    @if (!empty($therapy->frequency))
                                        ({{ $therapy->frequency }})
                                    @endif 
                                    @if(!empty($therapy->session_count))
                                    ({{ $therapy->session_count }} Session)
                                    @endif
                                    </li>
                                @empty
                                    
                                @endforelse
                            </ol>
                        </div>
                        @endif
                    </div>
                </div>
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
                window.location.href = "{{ url('/doctor-dashboard') }}";
            };

        }
    </script>
</body>

</html>
