<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Template</h4>
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
                                <p class="dr_name">Dr. Sharif Ahmed
                                </p>
                                <b>
                                    Bachelor of Ayurvedic Medicine and Surgery (BAMS) <br>Bachelor of Dental Surgery(BDS) <br>Doctor of Medicine(MD) <br>PhD in Medical Sciences
                                </b>
                                
                            </div>
                        </div>
                        <div class="row patient_info_section" style="text-align: center;">
                            <div class="col-md-4 ">
                                <p>Patient Name</p>
                                
                            </div>
                            <div class="col-md-2 ">
                                <p>Age</p>
                            </div>
                            <div class="col-md-2 ">
                                <p>Sex</p>
                            </div>
                            <div class="col-md-2 ">
                                <p>Date</p>
                            </div>
                            <div class="col-md-2">
                                <p>
                                    <img
                                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG('D00000D', 'C128', 1.2, 30, [30, 48, 54], false) }}" style="width: 80px; margin-top: 10px;">
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
                                <table class="table table-borderless">
                                        <tr>
                                            <td>Height:</td>
                                            <td></td>
                                        </tr>
                                    
                                        <tr>
                                            <td>Weight:</td>
                                            <td></td>
                                        </tr>
                                   
                                       
                                    
                                        <tr>
                                            <td>Pulse Rate:</td>
                                            <td></td>
                                        </tr>
                                    
                                        <tr>
                                            <td>Blood Pressure:</td>
                                            <td></td>
                                        </tr>
                                    
                                        <tr>
                                            <td>Respiratory:</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>Body Temperature:</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>BMI:</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>Body Fat %:</td>
                                            <td></td>
                                        </tr>
                                    
                                        <tr>
                                            <td>Fat Mass %:</td>
                                            <td></td>
                                        </tr>
                                    
                                        <tr>
                                            <td>Lean Mass %:</td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td>Comments:</td>
                                            <td></td>
                                        </tr>
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
                                    <h3 style="margin-bottom: 0;">D/H:</h3>
                                    <ol style="margin-left: 0; padding-left: 15px;">
                                        @forelse ($diseaseHistories as $history)
                                            <li>{{ $history->chief_complaint }}</li>
                                        @empty
                                        @endforelse
                                    </ol>
                                </div>

                                <div style="display: flex; flex-direction: column; gap: 0;">
                                    <h3 style="margin-bottom: 0;">Investigation:</h3>
                                    <p style="margin-top: 0; margin-bottom: 0;">
                                        @forelse ($prescribedTest as $test)
                                            <p>{{ $test->test_name }} ({{ $test->comment }})</p>
                                        @empty
                                        
                                        @endforelse
                                    </p>
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
                                                    <div style="padding: 2px;" class="col-md-1">{{ $loop->iteration }}</div>
                                                    <div style="padding: 2px;" class="col-md-11">
                                                        <div class="row">
                                                            <div style="padding: 2px;" class="col-md-12">
                                                                {{ $medicine->x_medicine_name ?? '-' }}
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
                                                                {{ $medicine->comment ?? '-' }}
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
                                <div id="advice_section" style="margin-left: 40px;">
                                    <h3 style="margin-bottom: 5px;">Advice:</h3>
                                    <!-- Reduce spacing below the heading -->
                                    <ol style="padding-left: 20px; margin-top: 0; margin-bottom: 0;">
                                        @foreach (explode(',', $prescription->advices) as $advice)
                                            <li style="margin-bottom: 2px;">{{ trim($advice) }}</li>
                                            <!-- Reduce space between items -->
                                        @endforeach
                                    </ol>
                                    @if ($ipdAdmission && $ipdAdmission->is_ipd_admission == 1)
                                        <p>Admission in IPD for {{ $ipdAdmission->admission_days }} days</p>
                                    @endif
                                </div>
                                <div id="therapy_section" style="margin-left: 40px;">
                                    <h3 style="margin-bottom: 5px;">Therapy:</h3>
                                    <!-- Reduce spacing below the heading -->
                                    <ol style="padding-left: 20px; margin-top: 0; margin-bottom: 0;">
                                        @forelse ($prescribedTherapy as $therapy)
                                            <li style="margin-bottom: 2px;">{{ $therapy->therapy_name }} ({{ $therapy->frequency }})</li>
                                        @empty
                                            
                                        @endforelse
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
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
        font-size: 30px !important;
        font-weight: bold !important;
        margin: 0;
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
</style>
