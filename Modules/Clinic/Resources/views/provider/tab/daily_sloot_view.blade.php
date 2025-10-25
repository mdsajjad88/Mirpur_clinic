<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Daily Sloot Info</h4>
        </div>

        <div class="modal-body">
            <div class="col-md-12 mb-4 mt-2">
                <div class="card slot-card shadow-sm">
                    @php
                        $availableSlots = json_decode($slot->slots, true);
                    @endphp
                    <div class="card-body">
                        <h4 class="card-title text-primary text-center">
                            {{ $slot->calendar_date }} ({{ \Carbon\Carbon::parse($slot->calendar_date)->format('l') }}) 
                            {!! $slot->slot_active ? '<i class="fas fa-check"></i>' : '' !!}
                        </h4>
                        <ul class="list-group">
                            @foreach ($availableSlots as $date => $times)
                                <li class="list-group-item">
                                    <ul class="list-unstyled ml-3">
                                        @foreach ($times as $time)
                                            <li class="d-flex justify-content-between align-items-center py-2">
                                                <span class="font-weight-semibold">
                                                    {{ $time['start'] }} - {{ $time['end'] }}
                                                </span>
                                                <span class="badge badge-info">Reserved: {{ $time['reserved'] }}</span>
                                                <span class="badge badge-warning">Booked: {{ $time['booked'] }}</span>
                                                <span class="badge badge-success">Capacity: {{ $time['capacity'] }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
