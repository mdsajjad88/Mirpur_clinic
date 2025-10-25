<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            @if ($sending_details->isNotEmpty())
                <h4 class="modal-title">Campaign Name: {{ $sending_details->first()->campaign->name ?? 'No Campaign' }}
                </h4>
            @endif
        </div>
        
        <div class="modal-body">
            <div class="row mt-2">
                <div class="col-md-1"></div>
                <div class="col-md-10 text-center">
                    {{-- <a 
                    data-href="{{ action([\Modules\Crm\Http\Controllers\CampaignController::class, 'sendNotification'], ['id' => $id]) }}" 
                    class="cursor-pointer send_campaign_notification btn btn-warning m-2"
                >
                    Re-Send
                </a> --}}
                    <button class="btn text-info">
                        List - <span>{{ $total_contacts ?? 0 }}</span>
                    </button>
                    <button class="btn text-warning">
                        Pending - <span>{{ $pending ?? 0 }}</span>
                    </button>
                    <button class="btn text-success">
                        Delivered - <span>{{ $delivered ?? 0 }}</span>
                    </button>
                    @if ($failed >0)
                    <a 
                    data-href="{{ action([\Modules\Crm\Http\Controllers\CampaignController::class, 'sendNotification'], ['id' => $sending_details->first()->campaign->id]) }}" 
                    class="cursor-pointer send_campaign_notification btn btn-warning m-2"
                >
                    Failed ({{ $failed }}) Re-Send
                </a>
                    @else
                    <button class="btn text-danger">
                        Failed - 0
                        </button>
                        @endif
                </div>                
                <div class="col-md-1"></div>
            </div><br><br>
            <table class="table table-striped table-bordered" id="camp-wise-data-table">
                <thead>
                    <tr>
                        <th>Customer Name</th>
                        <th>Mobile</th>
                        <th>Send By</th>
                        <th>Notification Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sending_details as $sending_detail)
                        <tr>
                            <td>{{ $sending_detail->customer_name }}</td>
                            <td>{{ $sending_detail->mobile }}</td>
                            <td>{{ $sending_detail->sendBy->first_name }}</td>
                            <td>{{ $sending_detail->notification_date }}</td>
                            <td>{{ $sending_detail->status }}</td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No users found for this role.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->