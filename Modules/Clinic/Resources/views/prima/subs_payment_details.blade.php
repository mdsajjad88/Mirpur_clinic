<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Subscription Payment Details by: {{ $patient_name }}</h4>
        </div>

        <div class="modal-body">
            <table class="table table-striped" style="width: 100%">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Tnx ID</th>
                        <th>Method</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $tnx)
                        <tr>
                            <td>{{ $tnx->t_amount }}</td>
                            <td>{{ $tnx->t_txid }}</td>
                            <td>{{ $tnx->t_media }}</td>
                            <td>{{ $tnx->transaction_requested_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
