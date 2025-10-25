@extends('clinic::layouts.app2')
@section('title', __('Follow Up Prescription'))

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">

                @php
                    $sessionAppointments = collect($sessionAppointments);
                @endphp

                @foreach ($sessionAppointments as $index => $appointmentId)
                    @if ($index === 0)
                        @continue {{-- Skip first item --}}
                    @endif

                    @php
                        $visitNumber = $index;
                        $isCurrentAppointment = $appointment->id == $appointmentId;
                        $notesForThisAppointment = $sessionNotes->get($appointmentId, collect());

                        // unique IDs for collapse
                        $headingId = "heading_session_{$visitNumber}";
                        $collapseId = "collapse_session_{$visitNumber}";
                    @endphp

                    <div class="panel panel-default">
                        <div class="panel-heading" role="tab" id="{{ $headingId }}" style="cursor: pointer;">
                            <h4 class="panel-title">
                                <a role="button" data-toggle="collapse" href="#{{ $collapseId }}"
                                    aria-expanded="{{ $isCurrentAppointment ? 'true' : 'false' }}"
                                    style="display: block; width: 100%; height: 100%; padding: 10px 15px; text-decoration: none; color: black; background-color: #f5f5f5;">
                                    Session Visit {{ $visitNumber }} Prescription
                                </a>
                            </h4>
                        </div>

                        <div id="{{ $collapseId }}"
                            class="panel-collapse collapse {{ $isCurrentAppointment ? 'in' : '' }}" role="tabpanel">
                            <div class="panel-body">
                                {{-- Existing Notes --}}
                                @if ($notesForThisAppointment->isNotEmpty())
                                    <ul>
                                        @foreach ($notesForThisAppointment as $note)
                                            <li>
                                                <strong>{{ $note->created_at->format('d/m/Y H:i') }}</strong> -
                                                {!! nl2br(e($note->note)) !!}
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted">No notes for this visit.</p>
                                @endif
                                @php
    $existingNote = ($sessionNotes->get($appointmentId) ?? collect())->first();
@endphp

                                {{-- Only current appointment editable --}}
                                @if ($isCurrentAppointment)
                                    <form
                                        action="{{ action([\Modules\Clinic\Http\Controllers\physiotherapist\TherapySessionNoteController::class, 'store']) }}"
                                        method="POST" class="mt-3">
                                        @csrf
                                        <input type="hidden" name="prescription_id" value="{{ $prescription->id ?? '' }}">
                                        <input type="hidden" name="appointment_id" value="{{ $appointment->id }}">
                                        <input type="hidden" name="session_id"
                                            value="{{ $appointment->patient_session_info_id }}">
                                        <input type="hidden" name="doctor_user_id" value="{{ $doctor->user_id }}">
                                        <input type="hidden" name="patient_contact_id"
                                            value="{{ $patient->patient_contact_id }}">

                                        <div class="form-group">
                                            <label for="note_{{ $appointmentId }}">Session Note:</label>
                                            <textarea name="note" id="note_{{ $appointmentId }}" class="form-control" rows="3" required>{{ old('note', $existingNote->note ?? '') }}</textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-2">Save Note</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>



        <div class="row">
            <div class="col">
                @component('components.filters', [
                    'title' => 'Show Prescription',
                    'icon' => '<i class="fa fa-prescription" aria-hidden="true"></i>',
                ])
                    <div class="row" style="padding: 0 30px;">
                        <div class="col-md-12">
                            {{-- Doctor Info --}}
                            <div class="row mb-4 doctor_info">
                                <div class="col-md-3"></div>
                                <div class="col-md-9 text-right">
                                    <p class="dr_name"> {{ $doctor_name }}</p>
                                    {!! $doctor->degrees ?? '' !!}
                                </div>
                            </div>

                            {{-- Patient Info --}}
                            <div class="row patient_info_section text-center">
                                <div class="col-md-4">
                                    <p>Patient Name</p>
                                    <b>{{ $patient->first_name }} {{ $patient->last_name ?? '' }}</b>
                                </div>
                                <div class="col-md-1">
                                    <p>Age</p>
                                    <p>{{ $patient->age }}</p>
                                </div>
                                <div class="col-md-1">
                                    <p>Sex</p>
                                    <p>{{ ucfirst($patient->gender) }}</p>
                                </div>
                                <div class="col-md-2">
                                    <p>Date</p>
                                    <p>{{ $now->format('d/m/Y') }}</p>
                                </div>
                            </div>

                            {{-- Medicines --}}
                            <div id="medicine_section">
                                <i class="fas fa-prescription prescription_icon"></i>

                                @if ($prescribedMedicines && $prescribedMedicines->isNotEmpty())
                                    <table class="table table-bordered mt-2">
                                        <thead class="table-light">
                                            <tr>
                                                <th>#</th>
                                                <th>Medicine Name</th>
                                                <th>Dosage</th>
                                                <th>Meal Instruction</th>
                                                <th>Duration</th>
                                                <th>Comments</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($prescribedMedicines as $medicine)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $medicine->x_medicine_name ?? '-' }}</td>
                                                    <td>{{ $medicine->dosage->value ?? '-' }}</td>
                                                    <td>{{ $medicine->medicineMeal->value ?? '-' }}</td>
                                                    <td>{{ $medicine->duration->value ?? '-' }}</td>
                                                    <td>{{ $medicine->comment ?? '' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted mt-2">No medicines prescribed.</p>
                                @endif
                            </div>


                            {{-- Tests --}}
                            <div id="test_section">
                                @if ($prescribedTest && $prescribedTest->isNotEmpty())
                                    <h5>Tests:</h5>
                                    <ul>
                                        @foreach ($prescribedTest as $test)
                                            <li>{{ $test->test_name }} {{ $test->comment ? '(' . $test->comment . ')' : '' }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            {{-- Advices --}}
                            <div id="advice_section">
                                @if ($treatmentPlans || $home_advices || $on_examinations)
                                    <h5>Advices:</h5>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <strong>Treatment Plan</strong>
                                            <ul>
                                                @foreach ($treatmentPlans as $advice)
                                                    <li>{{ $advice->advise_name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>Home Advice</strong>
                                            <ul>
                                                @foreach ($home_advices as $advice)
                                                    <li>{{ $advice->advise_name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <strong>On Examination</strong>
                                            <ul>
                                                @foreach ($on_examinations as $advice)
                                                    <li>{{ $advice->advise_name }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Therapy --}}

                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
    </div>
@endsection
