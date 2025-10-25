<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Prescription Details</h4>
        </div>

        <div class="modal-body">
            <!-- Prescription Overview -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong class="d-block text-muted small mb-1">Visit Date</strong>
                            <span class="fw-semibold">{{ $prescription->visit_date }}</span>
                        </div>
                        <div class="col-md-4">
                            <strong class="d-block text-muted small mb-1">Patient</strong>
                            <span class="fw-semibold">{{ $patientContact->name }}<br><small
                                    class="text-muted">({{ $patientContact->mobile }})</small></span>
                        </div>
                        <div class="col-md-4">
                            <strong class="d-block text-muted small mb-1">Doctor</strong>
                            <span class="fw-semibold">{{ $doctor->first_name }} {{ $doctor->last_name }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!-- Prescribed Items Grid -->
            <div class="row g-4">
                <!-- Tests Section -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h6 class="card-title mb-0"><i class="fas fa-vial me-2 text-primary"></i>Prescribed Tests
                            </h6>
                            <span class="badge bg-primary fs-6">{{ $testTakenCount }} / {{ $testTotal }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Test Name</th>
                                            <th scope="col" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($tests as $test)
                                            <tr>
                                                <td>{{ $test->test_name }}</td>
                                                <td class="text-center">
                                                    @if ($test->status == 'Taken')
                                                        <span class="badge bg-success">Taken</span>
                                                    @else
                                                        <span class="badge bg-danger">Not Taken</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center py-4 text-muted">No tests
                                                    prescribed</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- US Supplements Section -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h6 class="card-title mb-0"><i class="fas fa-pills me-2 text-primary"></i>US Supplements
                            </h6>
                            <span class="badge bg-primary fs-6">{{ $usTakenCount }} / {{ $usTotal }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Supplement</th>
                                            <th scope="col" class="text-center">Status</th>
                                            <th scope="col" class="text-center">Stock</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($usSupplements as $sup)
                                            <tr>
                                                <td>{{ $sup['name'] }} {{ $sup['size'] }}</td>
                                                <td class="text-center">
                                                    @if ($sup['status'] == 'Taken')
                                                        <span class="badge bg-success">Taken</span>
                                                    @else
                                                        <span class="badge bg-danger">Not Taken</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($sup['stock'] == 'In Stock')
                                                        <span class="badge bg-success">In Stock</span>
                                                    @elseif($sup['stock'] == 'Out of Stock')
                                                        <span class="badge bg-warning">Out of Stock</span>
                                                    @else
                                                        <span class="badge bg-secondary">Unknown</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center py-4 text-muted">No US supplements
                                                    prescribed</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Therapies Section -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h6 class="card-title mb-0"><i
                                    class="fas fa-hand-holding-medical me-2 text-primary"></i>Prescribed Therapies</h6>
                            <span class="badge bg-primary fs-6">{{ $therapyTakenCount }} / {{ $therapyTotal }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Therapy</th>
                                            <th scope="col" class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($therapies as $therapy)
                                            <tr>
                                                <td>{{ $therapy->therapy_name ?? 'Therapy' }}</td>
                                                <td class="text-center">
                                                    @if ($therapy->status == 'Taken')
                                                        <span class="badge bg-success">Taken</span>
                                                    @else
                                                        <span class="badge bg-danger">Not Taken</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center py-4 text-muted">No therapies
                                                    prescribed</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- IPD Admission Section -->
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center bg-light">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-hospital me-2 text-primary"></i>IPD Admission
                            </h6>
                            @if ($ipdStatus !== 'N/A')
                                <span class="badge bg-primary fs-6">Advised</span>
                            @endif
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Status</th>
                                            <th scope="col" class="text-center">Admission Days</th>
                                            <th scope="col" class="text-center">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if ($ipdStatus !== 'N/A')
                                            <tr>
                                                <td>
                                                    @if ($ipdStatus == 'Admitted')
                                                        <span class="badge bg-success fs-6">Admitted</span>
                                                    @else
                                                        <span class="badge bg-danger fs-6">Not Admitted</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($ipdAdmissionDays)
                                                        <span class="fw-semibold">{{ $ipdAdmissionDays }} days</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($ipdStatus == 'Admitted')
                                                        <span class="text-success">Under treatment</span>
                                                    @elseif($ipdStatus == 'Not Admitted')
                                                        <span class="text-danger">Admission advised</span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="3" class="text-center py-4">
                                                    <span class="badge bg-secondary fs-6">{{ $ipdStatus }}</span>
                                                    <p class="text-muted mt-2 mb-0">No IPD admission required</p>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
