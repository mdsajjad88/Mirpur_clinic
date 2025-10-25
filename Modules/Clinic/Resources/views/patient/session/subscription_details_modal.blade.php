<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header bg-primary text-white">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title">Session Details</h4>
        </div>

        <div class="modal-body">
            <h5><strong>Session Name:</strong> {{ $session->session_name }}</h5>
            <p><strong>Amount:</strong> {{ $session->session_amount }}</p>
            <p><strong>Total Visits:</strong> {{ $session->total_visit }}</p>
            <p><strong>Visited:</strong> {{ $patient_session->visited_count }}</p>
            <p><strong>Remaining Visits:</strong> {{ $patient_session->remaining_visit }}</p>

            <hr>
            <h5>Patient Visit History:</h5>
            @if($session_details->count())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Visit Date</th>
                            <th>Doctor Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($session_details as $index => $detail)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $detail->visit_date }}</td>
                                <td>{{ $detail->creator->first_name }} {{$details->creator->last_name??''}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p>No session details found.</p>
            @endif
        </div>
    </div>
</div>
