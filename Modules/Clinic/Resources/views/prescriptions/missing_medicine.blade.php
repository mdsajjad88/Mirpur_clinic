<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h3 class="modal-title">Missing Medicine Details</h3>
        </div>
        <div class="modal-body">
            @if($missingMedicines->isNotEmpty())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Medicine Name</th>
                            <th>Prescription No</th>
                            <th>Visit Date</th>
                            <th>Patient Name</th>
                            <th>Doctor Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($missingMedicines as $test)
                            <tr>
                                <td>{{ $test->madicine_name }}</td>
                                <td>{{ $test->prescription_number }}</td>
                                <td>{{ \Carbon\Carbon::parse($test->visit_date)->format('d M Y') }}</td>
                                <td>{{ $test->patient_first_name }} {{ $test->patient_last_name }}</td>
                                <td>{{ $test->doctor_first_name }} {{ $test->doctor_last_name }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No data available for this Medicine.</p>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
