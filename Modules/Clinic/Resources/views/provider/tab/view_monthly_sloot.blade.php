<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content rounded-lg shadow-lg border-0">
        <div class="modal-header rounded-top">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

            <h4 class="modal-title d-flex align-items-center">
                Monthly Sloot Info
                @if (!empty($breakTimes))
                    <span class="ml-3" style="font-size: 13px; font-weight: normal;">
                        (
                        @foreach ($breakTimes as $index => $break)
                            {{ $break['break_type'] ?? 'Break' }}:
                            {{ \Carbon\Carbon::createFromFormat('H:i', $break['start_time'])->format('g:i A') }}
                            to
                            {{ \Carbon\Carbon::createFromFormat('H:i', $break['end_time'])->format('g:i A') }}
                            @if (!$loop->last)
                                ,
                            @endif
                        @endforeach
                        )
                    </span>
                @endif
            </h4>
        </div>

        <div class="modal-body">
            <div class="row">
                @foreach ($slootData as $slot)
                    @php
                        $availableSlots = json_decode($slot->slots, true);
                        $appointment = \DB::table('patient_appointment_requests')
                            ->where('doctor_appointment_slot_id', $slot->id)
                            ->where(function ($query) {
                                $query->where('cancel_status', 0)->orWhereNull('cancel_status');
                            })
                            ->count();
                        $slot_date = \Carbon\Carbon::parse($slot->calendar_date)->startOfDay();
                        $today = \Carbon\Carbon::today();

                    @endphp
                    <div class="col-md-6 col-lg-4 mb-4 mt-2">
                        <div class="card slot-card shadow-sm">
                            <div class="card-body">
                                <h4 class="card-title text-primary text-center">
                                    {{ $slot->calendar_date }}
                                    ({{ \Carbon\Carbon::parse($slot->calendar_date)->format('l') }})
                                    {!! $slot->slot_active ? '<i class="fas fa-check"></i>' : '' !!}
                                    @if ($appointment === 0 && $slot_date->greaterThan($today))
                                        <input type="button" class="btn btn-danger btn-sm slot-delete-btn"
                                            value="Delete" data-id="{{ $slot->id }}">
                                    @endif
                                </h4>
                                <ul class="list-group">
                                    @foreach ($availableSlots as $date => $times)
                                        <li class="list-group-item">
                                            <strong>{{ $date }}</strong>
                                            <ul class="list-unstyled ml-3">
                                                @foreach ($times as $key => $time)
                                                    <li class="d-flex justify-content-between align-items-center py-2">
                                                        <span class="font-weight-semibold">
                                                            {{ $time['start'] }} - {{ $time['end'] }}</span>
                                                        <span class="badge badge-info">Reserved:
                                                            {{ $time['reserved'] }}</span>
                                                        <span class="badge badge-warning">Booked:
                                                            {{ $time['booked'] }}</span>
                                                        <span class="badge badge-success">Capacity:
                                                            {{ $time['capacity'] }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div><!-- /.modal-content -->
</div>


<style>
    .card-body {
        padding: 8px;
        min-height: 320px;
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .slot-card {
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        border: none;
        /* Remove default border */
    }

    .slot-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
    }

    .list-group-item {
        background-color: #ffffff;
        /* White background for better contrast */
        border: 1px solid #dee2e6;
        /* Light border */
        border-radius: 0.5rem;
    }

    .list-group-item:hover {
        background-color: #e0f7fa;
        /* Softer highlight on hover */
    }

    .badge-info {
        background-color: #007bff;
        /* Bootstrap primary color */
    }

    .badge-warning {
        background-color: #ffc107;
        /* Bootstrap warning color */
    }

    .badge-success {
        background-color: #28a745;
        /* Bootstrap success color */
    }

    .font-weight-semibold {
        font-weight: 600;
    }

    .text-muted {
        color: #6c757d;
        /* Muted text for less emphasis */
    }

    .modal-body {
        padding: 2rem;
        /* More padding for better spacing */
    }
</style>
<script>
    $(document).ready(function() {
        $('.slot-delete-btn').click(function(e) {
            e.preventDefault();
            var $button = $(this);
            var id = $button.data('id');
            var url = "{{ route('provider.slot.delete', ':id') }}";
            url = url.replace(':id', id);
            swal({
                title: "Are you sure?",
                text: "You want to delete this slot?",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.msg);
                                $button.closest('.col-md-6').fadeOut(300,
                                    function() {
                                        $(this).remove();
                                    });

                            } else {
                                toastr.error(response.msg);
                            }
                        },
                        error: function(xhr) {
                            toastr.error("Something went wrong!");
                        }
                    });
                }
            })

        });
    });
</script>
