@extends('clinic::layouts.app2')

@section('title', __('report.reports'))

@section('css')
    <style>
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.1) !important;
        }
        .summary-card {
            transition: all 0.3s ease;
        }
        .summary-card:hover {
            box-shadow: 0 3px 10px rgba(0,0,0,0.1) !important;
        }
    </style>
@endsection
@section('content')
    @include('crm::layouts.nav')

    <section class="content no-print">
        <div class="col-sm-12">
            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs">
                    <li class="active">
                        <a href="#report_tab" data-toggle="tab" aria-expanded="true">@lang('report.reports')</a>
                    </li>
                    <li>
                        <a href="#daily_call_report_tab" data-toggle="tab" aria-expanded="true">@lang('crm::lang.daily_call_report')</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="report_tab">
                        <div class="row">
                            <div class="col-md-12">
                                @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.follow_ups_by_user')])
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                {!! Form::label('follow_up_user_date_range', __('report.date_range') . ':') !!}
                                                {!! Form::text('follow_up_user_date_range', null, [
                                                    'placeholder' => __('lang_v1.select_a_date_range'),
                                                    'class' => 'form-control',
                                                    'readonly',
                                                ]) !!}
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-bordered table-striped" id="follow_ups_by_user_table"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>@lang('role.user')</th>
                                                @foreach ($statuses as $key => $value)
                                                    <th>
                                                        {{ $value }}
                                                    </th>
                                                @endforeach
                                                <th>
                                                    @lang('lang_v1.others')
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
                            <div class="col-md-12">
                                @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.follow_ups_by_contacts')])
                                    <table class="table table-bordered table-striped" id="follow_ups_by_contact_table"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>@lang('contact.contact')</th>
                                                @foreach ($statuses as $key => $value)
                                                    <th>
                                                        {{ $value }}
                                                    </th>
                                                @endforeach
                                                <th>
                                                    @lang('lang_v1.others')
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
                            <div class="col-md-12">
                                @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.lead_to_customer_conversion')])
                                    <table class="table table-bordered table-striped" id="lead_to_customer_conversion"
                                        style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>&nbsp;</th>
                                                <th>@lang('crm::lang.converted_by')</th>
                                                <th>@lang('sale.total')</th>
                                            </tr>
                                        </thead>
                                    </table>
                                @endcomponent
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="daily_call_report_tab">
                        @include('crm::reports.daily_call_report')
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('javascript')
    @include('crm::reports.report_javascripts')
@endsection
