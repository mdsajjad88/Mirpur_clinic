<!-- Report Header -->
<div class="box-header with-border text-center" style="background-color: #f8f9fa; border-bottom: 1px solid #ddd;">
    <h3 class="box-title" style="font-weight: bold;">
        Report Date: {{ $call_report['report_date'] }},
        <small class="text-muted">Generated at: {{ $call_report['generated_at'] }}</small>
    </h3>
</div>

<!-- Report Body -->
<div class="container-fluid">
    <div class="row">
        <!-- Call Statistics -->
        <div class="col-lg-6">
            <div class="card" style="border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 25px;">
                <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #e0e0e0; padding: 12px 15px;">
                    <h3 class="card-title" style="font-weight: 600; margin: 0; color: #333;">
                        <i class="fa fa-phone-square" style="margin-right: 8px; color: #6c757d;"></i> 
                        @lang('crm::lang.call_statistics')
                    </h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="table table-hover" style="margin-bottom: 0;">
                            <tbody>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <th width="60%" style="padding: 12px 15px; font-weight: 500;">@lang('crm::lang.total_calls')</th>
                                    <td class="text-right" style="padding: 12px 15px; font-weight: 600; font-size: 16px;">
                                        {{ $call_report['total_calls'] }}
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <th style="padding: 12px 15px; font-weight: 500;">@lang('crm::lang.inbound_calls')</th>
                                    <td class="text-right" style="padding: 12px 15px; font-weight: 600; color: #28a745;">
                                        {{ $call_report['inbound_calls'] }}
                                        <span class="percentage-badge" style="background-color: #e8f5e9; color: #28a745; padding: 2px 6px; border-radius: 10px; font-size: 12px; font-weight: 500; margin-left: 5px;">
                                            {{ $call_report['total_calls'] > 0 ? round(($call_report['inbound_calls'] / $call_report['total_calls']) * 100, 1) : 0 }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="padding: 12px 15px; font-weight: 500;">@lang('crm::lang.outbound_calls')</th>
                                    <td class="text-right" style="padding: 12px 15px; font-weight: 600; color: #17a2b8;">
                                        {{ $call_report['outbound_calls'] }}
                                        <span class="percentage-badge" style="background-color: #e3f2fd; color: #17a2b8; padding: 2px 6px; border-radius: 10px; font-size: 12px; font-weight: 500; margin-left: 5px;">
                                            {{ $call_report['total_calls'] > 0 ? round(($call_report['outbound_calls'] / $call_report['total_calls']) * 100, 1) : 0 }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Call Duration Summary -->
            @if (isset($call_report['avg_call_duration']))
                <div class="card" style="border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                    <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #e0e0e0; padding: 12px 15px;">
                        <h3 class="card-title" style="font-weight: 600; margin: 0; color: #333;">
                            <i class="fa fa-clock-o" style="margin-right: 8px; color: #6c757d;"></i> 
                            @lang('crm::lang.call_duration')
                        </h3>
                    </div>
                    <div class="card-body" style="padding: 0;">
                        <div class="table-responsive">
                            <table class="table table-hover" style="margin-bottom: 0;">
                                <tbody>
                                    <tr style="border-bottom: 1px solid #f0f0f0;">
                                        <th width="60%" style="padding: 12px 15px; font-weight: 500;">@lang('crm::lang.avg_call_duration')</th>
                                        <td class="text-right" style="padding: 12px 15px; font-weight: 500;">
                                            <span style="font-weight: 600;">{{ $call_report['avg_call_duration'] }}</span> mins
                                        </td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 12px 15px; font-weight: 500;">@lang('crm::lang.total_call_duration')</th>
                                        <td class="text-right" style="padding: 12px 15px; font-weight: 500;">
                                            <span style="font-weight: 600;">{{ $call_report['total_call_duration'] }}</span> hours
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Appointment Statistics -->
        <div class="col-lg-6">
            <div class="card" style="border: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #e0e0e0; padding: 12px 15px;">
                    <h3 class="card-title" style="font-weight: 600; margin: 0; color: #333;">
                        <i class="fa fa-calendar-check-o" style="margin-right: 8px; color: #6c757d;"></i> 
                        @lang('crm::lang.appointment_statistics')
                    </h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div class="table-responsive">
                        <table class="table table-hover" style="margin-bottom: 0;">
                            <tbody>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <th width="60%" style="padding: 12px 15px; font-weight: 500;">@lang('crm::lang.total_appointment_taken')</th>
                                    <td class="text-right" style="padding: 12px 15px; font-weight: 600; font-size: 16px;">
                                        {{ $call_report['total_appointments'] }}
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <th style="padding: 12px 15px; font-weight: 500;">@lang('crm::lang.new_patients')</th>
                                    <td class="text-right" style="padding: 12px 15px; font-weight: 600; color: #28a745;">
                                        {{ $call_report['new_patients'] }}
                                        <span class="percentage-badge" style="background-color: #e8f5e9; color: #28a745; padding: 2px 6px; border-radius: 10px; font-size: 12px; font-weight: 500; margin-left: 5px;">
                                            @if ($call_report['total_appointments'] > 0)
                                                {{ round(($call_report['new_patients'] / $call_report['total_appointments']) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                <tr style="border-bottom: 1px solid #f0f0f0;">
                                    <th style="padding: 12px 15px; font-weight: 500;">@lang('crm::lang.followup_patients')</th>
                                    <td class="text-right" style="padding: 12px 15px; font-weight: 600; color: #17a2b8;">
                                        {{ $call_report['followup_patients'] }}
                                        <span class="percentage-badge" style="background-color: #e3f2fd; color: #17a2b8; padding: 2px 6px; border-radius: 10px; font-size: 12px; font-weight: 500; margin-left: 5px;">
                                            @if ($call_report['total_appointments'] > 0)
                                                {{ round(($call_report['followup_patients'] / $call_report['total_appointments']) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="padding: 12px 15px; font-weight: 500;">@lang('crm::lang.old_patients')</th>
                                    <td class="text-right" style="padding: 12px 15px; font-weight: 600; color: #ffc107;">
                                        {{ $call_report['old_patients'] }}
                                        <span class="percentage-badge" style="background-color: #fff8e1; color: #ffc107; padding: 2px 6px; border-radius: 10px; font-size: 12px; font-weight: 500; margin-left: 5px;">
                                            @if ($call_report['total_appointments'] > 0)
                                                {{ round(($call_report['old_patients'] / $call_report['total_appointments']) * 100, 1) }}%
                                            @else
                                                0%
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="container-fluid" style="margin-bottom: 25px; margin-top: 25px;">
    <div class="row">
        <div class="col-md-3">
            <div class="summary-card" style="background-color: #F8F9FA; border-radius: 6px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); height: 100%;">
                <div class="text-center">
                    <h5 style="margin: 0 0 10px 0; font-weight: 600; color: #6c757d;">@lang('crm::lang.call_volume')</h5>
                    <div style="font-size: 28px; font-weight: 700; color: #333; line-height: 1.2;">
                        {{ $call_report['today_total_calls'] }}
                    </div>
                    <small class="text-muted">@lang('crm::lang.total_calls_today')</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card" style="background-color: #F8F9FA; border-radius: 6px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); height: 100%;">
                <div class="text-center">
                    <h5 style="margin: 0 0 10px 0; font-weight: 600; color: #6c757d;">@lang('crm::lang.total_appointment_taken')</h5>
                    <div style="font-size: 28px; font-weight: 700; color: #28a745; line-height: 1.2;">
                        {{ $call_report['today_appointment_taken'] }}
                    </div>
                    <small class="text-muted">@lang('crm::lang.total_appointment_taken_today')</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card" style="background-color: #F8F9FA; border-radius: 6px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); height: 100%;">
                <div class="text-center">
                    <h5 style="margin: 0 0 10px 0; font-weight: 600; color: #6c757d;">@lang('crm::lang.today_appointments')</h5>
                    <div style="font-size: 28px; font-weight: 700; color: #17a2b8; line-height: 1.2;">
                        {{ $call_report['today_appointments'] }}
                    </div>
                    <small class="text-muted">@lang('crm::lang.scheduled_for_today')</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="summary-card" style="background-color: #F8F9FA; border-radius: 6px; padding: 15px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); height: 100%;">
                <div class="text-center">
                    <h5 style="margin: 0 0 10px 0; font-weight: 600; color: #6c757d;">@lang('crm::lang.next_day_appointments')</h5>
                    <div style="font-size: 28px; font-weight: 700; color: #17a2b8; line-height: 1.2;">
                        {{ $call_report['next_day_appointments'] }}
                    </div>
                    <small class="text-muted">@lang('crm::lang.scheduled_for_tomorrow')</small>
                </div>
            </div>
        </div>
    </div>
</div>