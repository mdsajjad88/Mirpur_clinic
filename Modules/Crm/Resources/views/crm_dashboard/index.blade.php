@extends('clinic::layouts.app2')

@section('title', __('crm::lang.crm'))

@section('content')

@include('crm::layouts.nav')

<section class="content no-print">
    <div class="row">
        <div class="col-md-4">
            @if(auth()->user()->can('crm.access_all_schedule') || auth()->user()->can('crm.access_own_schedule'))
                <div class="col-md-12">
                    <div class="info-box info-box-new-style">
                        <span class="info-box-icon bg-aqua"><i class="fas fa-calendar-check"></i></span>

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('crm::lang.todays_followups') }}</span>
                          <span class="info-box-number">{{$todays_followups}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </div>
            @endif
            @if(auth()->user()->can('crm.access_all_leads') || auth()->user()->can('crm.access_own_leads'))
                <div class="col-md-12">
                    <div class="info-box info-box-new-style">
                        <span class="info-box-icon bg-aqua"><i class="fas fa-user-check"></i></span>

                        <div class="info-box-content">
                          <span class="info-box-text">{{ __('crm::lang.my_leads') }}</span>
                          <span class="info-box-number">{{$my_leads}}</span>
                        </div>
                        <!-- /.info-box-content -->
                    </div>
                </div>
            @endif
            <div class="col-md-12">
                <div class="info-box info-box-new-style">
                    <span class="info-box-icon bg-aqua"><i class="fas fa-exchange-alt"></i></span>

                    <div class="info-box-content">
                      <span class="info-box-text">{{ __('crm::lang.my_leads_to_customer_conversion_in_this_month') }}</span>
                      <span class="info-box-number">{{$my_conversion}}</span>
                    </div>
                    <!-- /.info-box-content -->
                </div>
            </div>
            @if ($is_admin)
                <div class="col-md-12">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h4 class="box-title">Leads to Customer Conversion Chart ({{ now()->year }})</h4>
                        </div>
                        <div class="box-body p-10">
                            <div class="row">
                                <div class="col-md-6">
                                    {!! Form::label('filter_user_id', __('crm::lang.agent') . ':') !!}
                                    {!! Form::select('filter_user_id', $users, request()->get('filter_user_id'), ['class' => 'form-control select2', 'id' => 'filter_user_id', 'placeholder' => __('lang_v1.all')]) !!}
                                </div>
                            </div>
                            <canvas id="conversionChart" height="150"></canvas>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        @if(auth()->user()->can('crm.access_all_schedule') || auth()->user()->can('crm.access_own_schedule'))
            <div class="col-md-4">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('crm::lang.my_followups')</h3>
                    </div>
                    <div class="box-body p-10">
                        <table class="table no-margin">

                            @foreach($statuses as $key => $value)
                                <tr>
                                    <th>{{$value}}</th>
                                    <td>
                                        @if(isset($my_follow_ups_arr[$key]))
                                            {{$my_follow_ups_arr[$key]}}
                                        @else
                                            0
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if(isset($my_follow_ups_arr['__other']))
                                <tr>
                                    <th>@lang('lang_v1.others')</th>
                                    <td>
                                        {{$my_follow_ups_arr['__other']}}
                                    </td>
                                </tr>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        @endif
        @if(config('constants.enable_crm_call_log'))
            <div class="col-md-4">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h3 class="box-title">@lang('crm::lang.my_call_logs')</h3>
                        @can('crm.access_all_call_log')
                            <form method="GET" style="margin-top: 10px;">
                                <div class="row">
                                    <div class="col-md-8">
                                        {!! Form::select('filter_user_id', $users, request()->get('filter_user_id'), ['class' => 'form-control select2', 'placeholder' => __('lang_v1.all')]) !!}
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary btn-block">@lang('crm::lang.filter')</button>
                                    </div>
                                </div>
                            </form>
                        @endcan
                    </div>
                    <div class="box-body p-10">
                        <table class="table table-bordered text-center">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>@lang('crm::lang.inbound')</th>
                                    <th>@lang('crm::lang.outbound')</th>
                                    <th>@lang('crm::lang.total')</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th>@lang('crm::lang.calls_today')</th>
                                    <td>{{ $my_call_logs->today_inbound ?? 0 }}</td>
                                    <td>{{ $my_call_logs->today_outbound ?? 0 }}</td>
                                    <td>{{ ($my_call_logs->today_inbound + $my_call_logs->today_outbound) ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('crm::lang.calls_yesterday')</th>
                                    <td>{{ $my_call_logs->yesterday_inbound ?? 0 }}</td>
                                    <td>{{ $my_call_logs->yesterday_outbound ?? 0 }}</td>
                                    <td>{{ ($my_call_logs->yesterday_inbound + $my_call_logs->yesterday_outbound) ?? 0 }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('crm::lang.calls_this_month')</th>
                                    <td>{{ $my_call_logs->month_inbound ?? 0 }}</td>
                                    <td>{{ $my_call_logs->month_outbound ?? 0 }}</td>
                                    <td>{{ ($my_call_logs->month_inbound + $my_call_logs->month_outbound) ?? 0 }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <div class="col-md-4"></div>
        <div class="col-md-8">
            @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.call_subject_summary')])
                <div class="row @cannot('crm.access_all_call_log') hidden @endcan">
                    <div class="col-md-4">
                        {!! Form::label('filter_user_id', __('crm::lang.agent') . ':') !!}
                        {!! Form::select('filter_user_id', $users, request()->get('filter_user_id') ?? auth()->user()->id, ['class' => 'form-control select2', 'id' => 'filter_subject_user_id', 'placeholder' => __('lang_v1.all')]) !!}
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            {!! Form::label('date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('date_range', null, ['placeholder' => __('report.date_range'), 'class' => 'form-control date_range', 'readonly']) !!}
                        </div>
                    </div>
                </div>
                <div style="margin-top: 15px;" class="table-responsive">
                    <table class="table table-bordered" id="call_subject_summary_table">
                        <thead>
                            <tr>
                                <th>@lang('crm::lang.subject')</th>
                                <th>@lang('crm::lang.inbound')</th>
                                <th>@lang('crm::lang.outbound')</th>
                                <th>@lang('crm::lang.total')</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            @endcomponent
        </div>

    </div>

    <div class="row">
        @if ($is_admin)
            <div class="col-md-6">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <h4 class="box-title">Call Distribution by Subject</h4>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-4">
                                {!! Form::label('filter_chart_user_id', 'Filter by User') !!}
                                {!! Form::select('filter_chart_user_id', $users, null, ['class' => 'form-control select2', 'id' => 'filter_chart_user_id', 'placeholder' => __('lang_v1.all')]) !!}
                            </div>
                            <div class="col-md-4">
                                {!! Form::label('date_range_chart', __('report.date_range') . ':') !!}
                                {!! Form::text('date_range_chart', null, ['placeholder' => __('report.date_range'), 'class' => 'form-control date_range', 'id' => 'date_range_chart', 'readonly']) !!}
                            </div>
                        </div>
                        <canvas id="callSubjectChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        @endif


        <div class="col-md-6">
            @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.user_wise_appointment_summary')])
                <div class="row @cannot('crm.access_all_call_log') hidden @endcan">
                    <div class="col-md-6">
                        {!! Form::label('appointment_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('appointment_date_range', null, ['placeholder' => __('report.date_range'), 'class' => 'form-control appointment_date_range', 'readonly']) !!}
                    </div>
                </div>

                <div style="margin-top: 15px;" class="table-responsive">
                    <table class="table table-bordered" id="users_call_logs_table">
                        <thead>
                            <tr>
                                <th>@lang('user.name')</th>
                                <th>@lang('clinic::lang.appointment')</th>
                                <th>@lang('crm::lang.new')</th>
                                <th>@lang('crm::lang.followup')</th>
                                <th>@lang('crm::lang.old')</th>
                                <th>@lang('crm::lang.not_confirmed')</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            @endcomponent
        </div>

    </div>
    
    @if($is_admin)
        <hr>
        <div class="row row-custom">
            <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
              <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-aqua"><i class="fas fa-user-friends"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">{{ __('lang_v1.customers') }}</span>
                  <span class="info-box-number">{{$total_customers}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
              <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-aqua"><i class="fas fa-user-check"></i></span>

                <div class="info-box-content">
                  <span class="info-box-text">{{ __('crm::lang.leads') }}</span>
                  <span class="info-box-number">{{$total_leads}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
              <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-yellow">
                    <i class="fas fa fa-search"></i>
                </span>

                <div class="info-box-content">
                    <span class="info-box-text">{{ __('crm::lang.sources') }}</span>
                  <span class="info-box-number">{{$total_sources}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->

            <!-- fix for small devices only -->
            <!-- <div class="clearfix visible-sm-block"></div> -->
            <div class="col-md-3 col-sm-6 col-xs-12 col-custom">
              <div class="info-box info-box-new-style">
                <span class="info-box-icon bg-yellow">
                    <i class="fas fa-life-ring"></i>
                </span>

                <div class="info-box-content">
                  <span class="info-box-text">{{ __('crm::lang.life_stages') }}</span>
                  <span class="info-box-number invoice_due">{{$total_life_stage}}</span>
                </div>
                <!-- /.info-box-content -->
              </div>
              <!-- /.info-box -->
            </div>
            <!-- /.col -->
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="box box-solid">
                    <div class="box-body p-10">
                        <table class="table no-margin">
                            <thead>
                                <tr>
                                    <th>{{ __('crm::lang.sources') }}</th>
                                    <th>{{ __('sale.total') }}</th>
                                    <th>{{ __('crm::lang.conversion') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sources as $source)
                                    <tr>
                                        <td>{{$source->name}}</td>
                                        <td>
                                            @if(!empty($leads_count_by_source[$source->id]))
                                                {{$leads_count_by_source[$source->id]['count']}}
                                            @else
                                                0
                                            @endif
                                        </td>
                                        <td>
                                            @if(!empty($customers_count_by_source[$source->id]) && !empty($contacts_count_by_source[$source->id]))
                                                @php
                                                    $conversion = ($customers_count_by_source[$source->id]['count']/$contacts_count_by_source[$source->id]['count']) * 100;
                                                @endphp
                                                {{$conversion . '%'}}
                                            @else 
                                                {{'0 %'}}
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">@lang('lang_v1.no_data')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="box box-solid">
                    <div class="box-body p-10">
                        <table class="table no-margin">
                            <thead>
                                <tr>
                                    <th>{{ __('crm::lang.life_stages') }}</th>
                                    <th>{{ __('sale.total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($life_stages as $life_stage)
                                    <tr>
                                        <td>{{$life_stage->name}}</td>
                                        <td>@if(!empty($leads_by_life_stage[$life_stage->id])){{count($leads_by_life_stage[$life_stage->id])}} @else 0 @endif</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">@lang('lang_v1.no_data')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="box box-solid">
                    <div class="box-header with-border">
                        <i class="fas fa fa-birthday-cake"></i>
                        <h3 class="box-title">@lang('crm::lang.birthdays')</h3>
                        <a data-href="{{action([\Modules\Crm\Http\Controllers\CampaignController::class, 'create'])}}" class="btn btn-success btn-xs" id="wish_birthday">
                            <i class="fas fa-paper-plane"></i>
                            @lang('crm::lang.send_wishes')
                        </a>
                    </div>
                    <div class="box-body p-10">
                        <table class="table no-margin table-striped">
                            <caption>@lang('home.today')</caption>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('user.name')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($todays_birthdays as $key => $birthday)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="contat_id" name="contat_id[]" value="{{$birthday['id']}}" id="contat_id_{{$birthday['id']}}">
                                        </td>
                                        <td>
                                            <label for="contat_id_{{$birthday['id']}}" class="cursor-pointer fw-100">
                                                {{$birthday['name']}}
                                            </label>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">@lang('lang_v1.no_data')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if(!empty($upcoming_birthdays))
                            <hr class="m-2">
                        @endif
                        <table class="table no-margin table-striped">
                            <caption>
                                @lang('crm::lang.upcoming')
                            </caption>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>@lang('user.name')</th>
                                    <th>@lang('crm::lang.birthday_on')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($upcoming_birthdays as $key => $birthday)
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="contat_id" name="contat_id[]" value="{{$birthday['id']}}" id="contat_id_{{$birthday['id']}}">
                                        </td>
                                        <td>
                                            <label for="contat_id_{{$birthday['id']}}" class="cursor-pointer fw-100">
                                                {{$birthday['name']}}
                                            </label>
                                        </td>
                                        <td>
                                            {{Carbon::createFromFormat('m-d', $birthday['dob'])->format('jS M')}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">@lang('lang_v1.no_data')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-12">
                @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.follow_ups_by_user')])
                <div class="row">
                     <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('follow_up_user_date_range', __('report.date_range') . ':') !!}
                            {!! Form::text('follow_up_user_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']) !!}
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('followup_category_id', __('crm::lang.followup_category') .':*') !!}
                            {!! Form::select('followup_category_id', $followup_category, null, ['class' => 'form-control select2', 'style' => 'width: 100%;', 'placeholder' => __('messages.all')]) !!}
                        </div>
                    </div>
                    <br/>
                </div>

                    <table class="table table-bordered table-striped" id="follow_ups_by_user_table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>@lang('role.user')</th>
                                @foreach($statuses as $key => $value)
                                    <th>
                                        {{$value}}
                                    </th>
                                @endforeach
                                <th>
                                    @lang('lang_v1.none')
                                </th>
                                <th>
                                    @lang('crm::lang.total_follow_ups')
                                </th>
                            </tr>
                        </thead>
                    </table>
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.lead_to_customer_conversion')])
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="lead_to_customer_conversion" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>&nbsp;</th>
                                    <th>@lang('crm::lang.converted_by')</th>
                                    <th>@lang('sale.total')</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent
            </div>

            @if(config('constants.enable_crm_call_log'))
                <div class="col-md-6">
                    <div class="box box-solid">
                        <div class="box-header with-border">
                            <h3 class="box-title">@lang('crm::lang.all_users_call_log')</h3>
                        </div>
                        <div class="box-body p-10">
                            <div class="table-responsive">
                                <table class="table" id="all_users_call_log" style="width: 100%;">
                                    <thead>
                                        <tr>
                                            <th>
                                                @lang('role.user')
                                            </th>
                                            <th>
                                                @lang('crm::lang.calls_today')
                                            </th>
                                            <th>
                                                @lang('crm::lang.calls_this_month')
                                            </th>
                                            <th>
                                                @lang('lang_v1.all')
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</section>
@endsection
@section('css')
<style type="text/css">
    .fw-100 {
        font-weight: 100;
    }
    
</style>
@stop
@section('javascript')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
	<script src="{{ asset('modules/crm/js/crm.js?v=' . $asset_v) }}"></script>
    @include('crm::reports.report_javascripts')
    <script type="text/javascript">
        $(document).ready(function () {

            // 🔥 Set default to THIS MONTH
            dateRangeSettings.startDate = moment().startOf('month');
            dateRangeSettings.endDate = moment().endOf('month');
            $('#date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    call_subject_summary_table.ajax.reload();
                }
            );
            // ✅ Set visible input field value
            $('#date_range').val(
                moment().startOf('month').format(moment_date_format) + ' ~ ' +
                moment().endOf('month').format(moment_date_format)
            );
            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#date_range').val('');
                call_subject_summary_table.ajax.reload();
            });

            $(document).on('click', '#wish_birthday', function () {
                var url = $(this).data('href');
                var contact_ids = [];
                $("input.contat_id").each(function(){
                    if ($(this).is(":checked")) {
                        contact_ids.push($(this).val());
                    }
                });

                if (_.isEmpty(contact_ids)) {
                    alert("{{__('crm::lang.plz_select_user')}}");
                } else {
                    location.href = url+'?contact_ids='+contact_ids;
                }
            });

            @if(config('constants.enable_crm_call_log'))
                all_users_call_log = $("#all_users_call_log").DataTable({
                            processing: true,
                            serverSide: true,
                            scrollY: "75vh",
                            scrollX: true,
                            scrollCollapse: true,
                            fixedHeader: false,
                            'ajax': {
                                url: "{{action([\Modules\Crm\Http\Controllers\CallLogController::class, 'allUsersCallLog'])}}"
                            },
                            columns: [
                                { data: 'username', name: 'u.username' },
                                { data: 'calls_today', searchable: false },
                                { data: 'calls_yesterday', searchable: false },
                                { data: 'all_calls', searchable: false }
                            ],
                        });
            @endif
            

        var call_subject_summary_table = $('#call_subject_summary_table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            ajax: {
                url: "{{action([\Modules\Crm\Http\Controllers\CrmDashboardController::class, 'getCallSubjectSummary'])}}",
                data: function (d) {
                    return {
                        user_id: $('#filter_subject_user_id').val(),
                        start_date: $('#date_range').data('daterangepicker').startDate.format('YYYY-MM-DD'),
                        end_date: $('#date_range').data('daterangepicker').endDate.format('YYYY-MM-DD'),
                        // Add any other filters here
                    };
                },
                error: function(xhr, error, thrown) {
                    if (xhr.status === 500) {
                        alert('A server error occurred. Please try again later.');
                    } else {
                        alert('An error occurred while loading data. Please try again later.');
                    }
                    console.error('DataTables error:', xhr, error, thrown);
                }
            },
            columns: [
                { 
                    data: 'subject_name', 
                    name: 'subject_name',
                    render: function(data, type, row) {
                        return data || 'No Subject';
                    }
                },
                { 
                    data: 'inbound', 
                    name: 'inbound', 
                    searchable: false,
                    className: 'text-center'
                },
                { 
                    data: 'outbound', 
                    name: 'outbound', 
                    searchable: false,
                    className: 'text-center'
                },
                { 
                    data: 'total', 
                    name: 'total',
                    className: 'text-center',
                    orderSequence: ['desc', 'asc']
                }
            ],
            order: [[3, 'desc']], // Default sort by total descending
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
                // Add any additional UI enhancements here
            }
        });

        // Refresh table when filters change
        $(document).on('change', '#filter_subject_user_id, #date_range', function() {
            call_subject_summary_table.ajax.reload();
        });


        function loadConversionChart(userId = '') {
            let url = '{{ route('dashboard.conversion_chart') }}';
            if (userId) {
                url += '?filter_user_id=' + userId;
            }

            fetch(url)
                .then(res => res.json())
                .then(chartData => {
                    const ctx = document.getElementById('conversionChart').getContext('2d');

                    if (window.myConversionChart) {
                        window.myConversionChart.destroy(); // destroy old chart before re-rendering
                    }

                    window.myConversionChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Converted Customers',
                                data: chartData.data,
                                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });
                })
                .catch(err => console.error('Chart fetch error:', err));
        }

        // ✅ Initial load
        loadConversionChart();

        // ✅ Reload when dropdown changes
        $('#filter_user_id').on('change', function () {
            const userId = $(this).val();
            loadConversionChart(userId);
        });

        // Initialize date range picker for the chart
        $('#date_range_chart').daterangepicker(
            dateRangeSettings,
            function(start, end) {
                $('#date_range_chart').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                loadCallSubjectChart($('#filter_chart_user_id').val(), start, end);
            }
        );

        // Set default to THIS MONTH
        $('#date_range_chart').val(
            moment().startOf('month').format(moment_date_format) + ' ~ ' +
            moment().endOf('month').format(moment_date_format)
        );

        $('#date_range_chart').on('cancel.daterangepicker', function(ev, picker) {
            $('#date_range_chart').val('');
            loadCallSubjectChart($('#filter_chart_user_id').val());
        });

        // Reload chart on user filter change
        $('#filter_chart_user_id').on('change', function() {
            const selectedUser = $(this).val();
            const dateRange = $('#date_range_chart').data('daterangepicker');
            if (dateRange) {
                loadCallSubjectChart(selectedUser, dateRange.startDate, dateRange.endDate);
            } else {
                loadCallSubjectChart(selectedUser);
            }
        });

        // Initial load
        loadCallSubjectChart();

        function loadCallSubjectChart(userId = '', startDate = null, endDate = null) {
            let url = '{{ route('dashboard.call_subject_chart') }}';
            const params = new URLSearchParams();
            
            if (userId) {
                params.append('user_id', userId);
            }
            
            if (startDate && endDate) {
                params.append('start_date', startDate.format('YYYY-MM-DD'));
                params.append('end_date', endDate.format('YYYY-MM-DD'));
            }
            
            if (params.toString()) {
                url += '?' + params.toString();
            }

            fetch(url)
                .then(res => res.json())
                .then(chartData => {
                    const ctx = document.getElementById('callSubjectChart').getContext('2d');

                    // Destroy previous chart instance if exists
                    if (window.callSubjectChartInstance) {
                        window.callSubjectChartInstance.destroy();
                    }

                    // Generate random background colors
                    const backgroundColors = chartData.labels.map(() =>
                        `hsl(${Math.floor(Math.random() * 360)}, 70%, 70%)`
                    );

                    // Create the bar chart
                    window.callSubjectChartInstance = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: chartData.labels,
                            datasets: [{
                                label: 'Calls by Subject',
                                data: chartData.data,
                                backgroundColor: backgroundColors,
                                borderColor: backgroundColors,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    ticks: {
                                        autoSkip: false,
                                        maxRotation: 45,
                                        minRotation: 0
                                    }
                                },
                                y: {
                                    beginAtZero: true,
                                    stepSize: 1,
                                    title: {
                                        display: true,
                                        text: 'Total Calls'
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return `${context.label}: ${context.parsed.y} calls`;
                                        }
                                    }
                                },
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });
                })
                .catch(err => console.error('Error loading chart data:', err));
        }

        // Initialize the daterangepicker
        $('#appointment_date_range').daterangepicker({
            startDate: moment().subtract(6, 'days'),
            endDate: moment(),
            locale: dateRangeSettings.locale,
            ranges: dateRangeSettings.ranges,
        }, function(start, end) {
            $('#appointment_date_range').val(
                start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format)
            );
            users_call_logs_table.ajax.reload();
        });

        // Set default visible value (last 7 days)
        $('#appointment_date_range').val(
            moment().subtract(6, 'days').format(moment_date_format) +
            ' ~ ' +
            moment().format(moment_date_format)
        );

        // Clear input on cancel
        $('#appointment_date_range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            users_call_logs_table.ajax.reload();
        });


        var users_call_logs_table = $('#users_call_logs_table').DataTable({
            processing: true,
            serverSide: true,
            dom: 'Btirp',
            ajax: {
                url: "{{ action([\Modules\Crm\Http\Controllers\CrmDashboardController::class, 'getAllUsersCallLogs']) }}",
                data: function (d) {
                    d.user_id = $('#filter_user_id').val();
                    if ($('#appointment_date_range').val()) {
                        let dateRange = $('#appointment_date_range').data('daterangepicker');
                        d.start_date = dateRange.startDate.format('YYYY-MM-DD');
                        d.end_date = dateRange.endDate.format('YYYY-MM-DD');
                    }
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTables error:', xhr, error, thrown);
                    alert('Error loading user-wise appointment summary.');
                }
            },
            columns: [
                { data: 'name', name: 'u.first_name' },
                { data: 'par_count', name: 'par_count', className: 'text-center' },
                { data: 'New', name: 'new_count', className: 'text-center' },
                { data: 'Followup', name: 'followup_count', className: 'text-center' },
                { data: 'Old', name: 'old_count', className: 'text-center' },
                { data: 'NotConfirmed', name: 'not_confirmed_count', className: 'text-center' }
            ],
            order: [[0, 'asc']],
            drawCallback: function(settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });

        // Reload on filter change
        $('#appointment_date_range').change(function () {
            users_call_logs_table.ajax.reload();
        });


        });
    </script>
@endsection