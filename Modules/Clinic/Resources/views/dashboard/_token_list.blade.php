<!-- Token Display -->
<div class="row bg-white mt-3 shadow-sm rounded">
    <div class="col-12">
        <!-- Custom Header Row -->
        <div class="doctor-header-row mb-3 text-center text-white">
            <div class="header-doctor-name">
                <div class="header-box">
                    <h4 class="header-title m-0">@lang('clinic::lang.doctor')</h4>
                </div>
            </div>
            <div class="header-current-served">
                <div class="header-box">
                    <h4 class="header-title m-0">@lang('clinic::lang.currntly_being_served')</h4>
                </div>
            </div>
            <div class="header-waiting-queue">
                <div class="header-box">
                    <h4 class="header-title m-0">@lang('clinic::lang.waiting_queue')</h4>
                </div>
            </div>
        </div>
        <div class="token-display">
            <table class="table table-bordered text-center">
                @foreach ($groupedTokens as $doctor)
                    <tr class="doctor-row">
                        <!-- Doctor Name -->
                        <td class="doctor-name-col">
                            <div class="doctor-card">
                                <h4 class="doctor-name"> {{ $doctor['doctor_name'] }} ({{ $doctor['serial_prefix'] }})
                                </h4>
                                <span class="doctor-room">@lang('clinic::lang.room'): {{ $doctor['room'] ?? 'N/A' }}</span>
                            </div>
                        </td>
                        <!-- Currently Being Served -->
                        <td class="current-served">
                            <div class="doctor-card">
                                @if ($doctor['current'])
                                    <div class="token-current" data-token="{{ $doctor['current'] }}"
                                        data-doctor="{{ $doctor['doctor_name'] }}"
                                        data-counter="Counter {{ $doctor['serial_prefix'] }}">
                                        {{ $doctor['current'] }}
                                    </div>
                                @else
                                    <p class="text-muted">@lang('clinic::lang.no_current_token')</p>
                                @endif
                            </div>
                        </td>

                        <!-- Waiting Queue -->
                        <td class="waiting-queue">
                            <div class="doctor-card">
                                <div class="waiting-tokens">
                                    @if (count($doctor['waiting']) > 0)
                                        @foreach ($doctor['waiting'] as $waitingToken)
                                            <span class="token-waiting">{{ $waitingToken }}</span>
                                        @endforeach
                                        {{-- <span class="waiting-count">(Waiting: {{ count($doctor['waiting']) }})</span> --}}
                                    @else
                                        <p class="text-muted">@lang('clinic::lang.no_waiting_token')</p>
                                    @endif
                                    @if (count($doctor['skipped']) > 0)
                                        <div class="skipped-tokens">
                                            <label class="skipped-label">@lang('clinic::lang.skipped_token'):</label>
                                            <div class="skipped-token-list">
                                                @foreach (array_chunk($doctor['skipped'], 2) as $chunk)
                                                    <ul class="token-row">
                                                        @foreach ($chunk as $skippedToken)
                                                            <li class="token-skipped">{{ $skippedToken }}</li>
                                                        @endforeach
                                                    </ul>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                @for ($i = 0; $i < 6 - count($groupedTokens); $i++)
                    <div class="doctor-row empty"></div> <!-- Empty rows to keep layout -->
                @endfor
            </table>
        </div>
    </div>
</div>

<!-- Last Call -->
<div class="row mt-5">
    <div class="col-12 text-center">
        <div class="alert alert-info">
            <p class="text-white"> @lang('clinic::lang.token_process_message') </p>
            @if ($lastCall)
                <p class="text-white">
                   @lang('clinic::lang.last_call') : {{ $lastCall['token'] }} @lang('clinic::lang.room'): {{ $lastCall['counter'] }}
                </p>
            @endif
        </div>
    </div>
</div>
