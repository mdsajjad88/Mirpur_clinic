<div class="row">
    <div class="col-md-4">
        @component('components.widget', ['class' => 'box-primary'])
            <!-- Next Estimated Visit Section -->
            @if($nextEstimatedVisit)
                <h4 class="mt-3">Next Estimated Visit</h4>
                <div class="alert alert-info">
                    <i class="fas fa-calendar-check"></i> 
                    Next recommended visit: <strong>{{ $nextEstimatedVisit }}</strong>
                </div>
            @endif
            <!-- Add Remaining Visits Section -->
            <h4 class="mt-3">Remaining Visits</h4>
            @if ($sessions->count())
                <div class="list-group">
                    @foreach ($sessions as $session)
                        <div class="list-group-item">
                            <strong>{{ $session->transaction->sell_lines->first()->product->name }}</strong><br>
                            Remaining: {{ $session->remaining_visit }}<br>
                            Expires: {{ \Carbon\Carbon::parse($session->end_date)->format('M d, Y') }}
                        </div>
                    @endforeach
                </div>
            @else
                <p>No remaining visits</p>
            @endif
            
            <!-- Add Visit History Section -->
            <h4 class="mt-3">Visit History</h4>
            @if ($visitHistory->count())
                <div class="list-group">
                    @foreach ($visitHistory as $history)
                        <div class="list-group-item">
                            <strong>{{ $history->patientSession->session->name ?? 'Consultation' }}</strong><br>
                            Visit Date: {{ \Carbon\Carbon::parse($history->visit_date)->format('M d, Y') }}<br>
                        </div>
                    @endforeach
                </div>
            @else
                <p>No visit history</p>
            @endif
        @endcomponent
    </div>
    <div class="col-md-8" style="padding: 5px;">
        @component('components.widget', ['class' => 'box-primary'])
            <h4> <i class="fas fa-notes-medical"></i> Medical History</h4>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="medical_history">
                        <h5> <i class="fas fa-heartbeat"></i> Chronic Disease</h5>
                        @if (!empty($chironicDisease) && count($chironicDisease))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($chironicDisease as $problems)
                                    <span class="badge badge-pill badge-warning px-3 py-2">{{ $problems }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="medical_history">
                        <h5> <i class="fas fa-disease"></i> Diabates Emergencies </h5>
                    </div>

                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="medical_history">
                        <h5> <i class="fas fa-sun"></i> Surgery</h5>
                    </div>

                </div>
                <div class="col-md-6">
                    <div class="medical_history">
                        <h5> <i class="fas fa-gem"></i> Family Disease</h5>
                        @if (!empty($familyHistoryDisease) && count($familyHistoryDisease))
                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($familyHistoryDisease as $disease)
                                    <span class="badge badge-pill badge-primary px-3 py-2">{{ $disease }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="medical_history">
                        <h5> <i class="fas fa-circle"></i> Diabates Related Complications</h5>
                    </div>
                </div>
            </div>
        @endcomponent
    </div>
</div>
