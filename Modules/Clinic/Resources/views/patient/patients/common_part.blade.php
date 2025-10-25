<div class="row" style="padding: 10px;">
    <div class="col-md-2">
        <div class="row">
            <div class="col">
                @if (!empty($patient->image))
                    <img src="{{ asset('uploads/patient/' . $patient->image) }}"
                        style="height: 90%; width: 90%; margin: 5px; padding: 5px; border: 1px solid #ddd; border-radius: 5px;"
                        alt="{{ $patient->first_name }} {{ $patient->last_name }}" />
                @else
                    <i class="fas fa-user-plus"
                        style="font-size: 120px; margin: 5px; padding: 5px; border: 1px solid #ddd; border-radius: 5px;"></i>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">{{ $contact->prefix ?? '' }} {{ $patient->first_name ?? '' }}
                {{ $patient->last_name ?? '' }}
                {{ $patient->nick_name ? '(' . $patient->nick_name . ')' : '' }}
                @if (!empty($contact->mobile))
                    <i class="fas fa-phone-volume"></i> {{ $contact->mobile }}
                @endif
                @if (!empty($contact->email))
                    <i class="far fa-inbox"></i> {{ $contact->email }}
                @endif
            </div>
            <div class="col-md-6">
                @if (!empty($contact->lifeStage->name))
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-user-tag text-primary mr-2" aria-hidden="true"></i>
                        <strong class="mr-1">Life Stage:</strong>
                        <span class="badge badge-primary">{{ $contact->lifeStage->name }}</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col">
                @if (!empty($patient->gender))
                    <i class="fas fa-user"></i>
                    {{ $patient->gender ? ucfirst($patient->gender) : '' }}
                @endif
                @if (!empty($patient->address))
                    <i class="fas fa-map-marker-alt"></i> {{ $patient->address ?? '' }}
                @endif
                @if (!empty($patient->profession))
                    <i class="fas fa-briefcase"></i> {{ $patient->profession ?? '' }}
                @endif
                @if (!empty($patient->age))
                    <i class="fas fa-calendar"></i> {{ $patient->age ?? '' }}
                @endif
            </div>
        </div>
        <div class="row" style="margin-top: 10px;">
            <div class="col-md-6">
                <div class="row">
                    @if (!empty($physicalInfo['bmi']))
                        <div class="col-md-3 mb-3">
                            <div class="info-box w-100">
                                <div class="info-value">{{ $physicalInfo['bmi'] }}</div>
                                <div class="info-title">BMI</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($physicalInfo['weight']))
                        <div class="col-md-3 mb-3">
                            <div class="info-box w-100">
                                <div class="info-value">{{ $physicalInfo['weight'] }} kg</div>
                                <div class="info-title">Weight</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($physicalInfo['height']))
                        <div class="col-md-3 mb-3">
                            <div class="info-box w-100">
                                <div class="info-value">{{ $physicalInfo['height'] }} ft</div>
                                <div class="info-title">Height</div>
                            </div>
                        </div>
                    @endif

                    @if (!empty($physicalInfo['blood_pressure']))
                        <div class="col-md-3 mb-3">
                            <div class="info-box w-100">
                                <div class="info-value">{{ $physicalInfo['blood_pressure'] }}</div>
                                <div class="info-title">Blood Pressure</div>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
            <div class="col-md-6">
                <div class="row">

                </div>
            </div>

        </div>
    </div>
    <div class="col-md-2">
        <button type="button" class="btn btn-success btn-flat btn-modal pull-right"
            data-href="{{ action([\Modules\Clinic\Http\Controllers\PatientController::class, 'edit'], [$patient->patient_contact_id ?? 0], ['quick_add' => true]) }}"
            data-container=".view_modal">
            <i class="fas fa-edit"></i> Edit
        </button>
        <a href="#" class="btn btn-info" id="patient_details">View Details</a>

        <h4>Health Barriers</h4>
        @if (!empty($healthConcerns) && count($healthConcerns))
            <div class="d-flex flex-wrap gap-2">
                @foreach ($healthConcerns as $concern)
                    <span class="badge badge-pill badge-info px-3 py-2">{{ $concern }}</span>
                @endforeach
            </div>
        @endif
    </div>
</div>
