<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Prescription</h4>
        </div>
        <div class="modal-body">
            <div class="container-fluid">
                <div class="row" style="padding: 0 30px;">
                    <div class="col-md-12">
                        <div class="row mb-4 doctor_info">
                            <div class="col-md-3">
                                @if (!empty(Session::get('business.logo')))
                                    <img src="{{ asset('uploads/business_logos/' . Session::get('business.logo')) }}"
                                        alt="" height='75px;' width='230px;'>
                                @endif
                            </div>
                            <div class="col-md-9 text-right">
                                <p class="dr_name"> {{ $prescription->assigned_doctor_first_name }}
                                    {{ $prescription->assigned_doctor_last_name }}
                                </p>
                                    {!! $prescription->show_in_pad !!}
                                {{-- <b class="font-weight-bold">
                                    {!! str_replace(',', '<br>', $prescription->doctor_specialities) !!}
                                </b> --}}
                            </div>
                        </div>
                        <div class="row patient_info_section" style="text-align: center;">
                            <div class="col-md-4 ">
                                <p>Patient Name</p>
                                <b>{{ $prescription->patient_first_name }} {{ $prescription->patient_last_name ?? '' }}
                                </b>
                            </div>
                            <div class="col-md-2 ">
                                <p>Age</p>
                                <p>{{ $prescription->age }}</p>
                            </div>
                            <div class="col-md-2 ">
                                <p>Sex</p>
                                <p>{{ ucfirst($prescription->gender) }}</p>
                            </div>
                            <div class="col-md-2 ">
                                <p>Date</p>
                                <p>{{ date('d/m/Y', strtotime($prescription->visit_date)) }}</p>
                            </div>
                            <div class="col-md-2">
                                <p>
                                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($prescription->customerId, 'C39', 1.0, 30, [39, 48, 54], false) }}"
                                        style="width: 80px; margin-top: 10px;">
                                </p>
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

                                <div style="display: flex; flex-direction: column; gap: 0;">
                                    <!-- Heading -->
                                    <h3 style="margin-bottom: 0;">I/H:</h3>
                                
                                    <!-- Row for History -->
                                    @if (!empty($InvestigationHistory))
                                    <ol style="margin-left: 0; padding-left: 15px;">
                                        @foreach ($InvestigationHistory as $history)
                                            <li>
                                                {{ $history->date }}:{{ $history->test_name }} ({{ $history->result_value }})
                                            </li>
                                        @endforeach
                                    </ol>
                                    @else
                                        
                                    @endif
                                </div>
                                
                                <div style="display: flex; flex-direction: column; gap: 0;">
                                    <h3 style="margin-bottom: 0;">Dx:</h3>
                                    <ol style="margin-left: 0; padding-left: 15px;">
                                        @forelse ($diseaseHistories as $history)
                                            <li>{{ $history->chief_complaint }}</li>
                                        @empty
                                        @endforelse
                                    </ol>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 0;">
                                    <h3 style="margin-bottom: 0;">Investigation:</h3>
                                    <ol style="margin-left: 0; padding-left: 15px;">
                                        @forelse ($prescribedTest as $test)
                                            <li>{{ $test->test_name }}
                                                @if (!empty($test->comment))
                                                    ({{ $test->comment }})
                                                @endif
                                            </li>
                                        @empty
                                        @endforelse
                                    </ol>
                                </div>


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
                                                    <div style="padding: 2px;" class="col-md-1">{{ $loop->iteration }}
                                                    </div>
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
                                            <p>No prescribed medicines found.</p>
                                        @endif
                                    </div>

                                </div>
                                @if (!empty($prescription->advices))
                                    <div id="advice_section" style="margin-left: 40px;">
                                        <h3 style="margin-bottom: 5px;">Advice:</h3>
                                        <!-- Reduce spacing below the heading -->
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
                                            <li style="margin-bottom: 2px;">Admission in IPD for {{ $ipdAdmission->admission_days }} days</li>
                                        </ul>
                                    </div>
                                @endif
                                @if (!empty($prescription->therapies))
                                    <div id="therapy_section" style="margin-left: 40px;">
                                        <h3 style="margin-bottom: 5px;">Therapy:</h3>
                                        <!-- Reduce spacing below the heading -->
                                        <ol style="padding-left: 20px; margin-top: 0; margin-bottom: 0;">
                                            @forelse ($prescribedTherapy as $therapy)
                                                <li style="margin-bottom: 2px;">{{ $therapy->therapy_name }}
                                                    ({{ $therapy->frequency }})
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
        </div>
        <div class="modal-footer">
            <a href="#" data-id="{{ $prescription->id }}" class="btn make_app direct_print_prescription"
                style="margin-left: 5px;">
                <i class="fas fa-print" aria-hidden="true"></i> Print
            </a>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Avenir', sans-serif;
        font-size: 14px;
        line-height: 1.5;
    }

    .container {
        width: 100%;
    }

    .row.mt-1 {
        display: flex;
        align-items: center;
        margin-top: 20px;
        /* Removed redundant margin-top: 10px */
    }

    /* Page Breaks Between Sections */
    .row.mb-4 {
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

    b {
        margin: 0;
        padding: 0;
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

    .patient_info_section {
        border: 1px solid #000000;
        border-radius: 10px;
    }

    /* Headings for more prominence */
    h1,
    h2,
    h3,
    h4 {
        font-family: 'Avenir', sans-serif;
        font-weight: bold;
    }
    .custom-padding td {
        padding-left: 0 !important;
        padding-right: 0 !important;
        padding-top: 0 !important;
    }
</style>
