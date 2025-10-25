@extends('clinic::layouts.app2')

@section('title', __('crm::lang.sms_history'))

@section('content')
    @include('crm::layouts.nav')

    <section class="content no-print">

        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('user_id', __('crm::lang.sent_by') . ':') !!}
                        {!! Form::select('user_id', $users, null, [
                            'class' => 'form-control select2',
                            'id' => 'user_id',
                            'placeholder' => __('messages.all'),
                            'style' => 'width: 100% !important;',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('call_sms_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('call_sms_date_range', null, [
                            'placeholder' => __('lang_v1.select_a_date_range'),
                            'class' => 'form-control',
                            'readonly',
                        ]) !!}
                    </div>
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.sms_history')])
            @slot('tool')
                <div class="box-tools">
                    <a href="{{ action([\Modules\Crm\Http\Controllers\SmsController::class, 'create']) }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> @lang('crm::lang.send_sms')
                    </a>
                </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="sms_history_table">
                    <thead>
                        <tr>
                            <th>@lang('crm::lang.sms_body')</th>
                            <th>@lang('crm::lang.total_contacts')</th>
                            <th>@lang('crm::lang.success')</th>
                            <th>@lang('crm::lang.failed')</th>
                            <th>@lang('crm::lang.total_sms')</th>
                            <th>@lang('crm::lang.sent_by')</th>
                            <th>@lang('receipt.date')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // 🔥 Set default to THIS MONTH
            dateRangeSettings.startDate = moment().startOf('month');
            dateRangeSettings.endDate = moment().endOf('month');
            $('#call_sms_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#call_sms_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    sms_history_table.ajax.reload();
                }
            );
            // ✅ Set visible input field value
            $('#call_sms_date_range').val(
                moment().startOf('month').format(moment_date_format) + ' ~ ' +
                moment().endOf('month').format(moment_date_format)
            );
            $('#call_sms_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#call_sms_date_range').val('');
                sms_history_table.ajax.reload();
            });

            // Initialize DataTable for SMS history
            var sms_history_table = $('#sms_history_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('crm.sms.index') }}',
                    data: function(d) {
                        if ($('#call_sms_date_range').val()) {
                            d.start_time = $('#call_sms_date_range').data('daterangepicker').startDate
                                .format('YYYY-MM-DD');
                            d.end_time = $('#call_sms_date_range').data('daterangepicker').endDate
                                .format('YYYY-MM-DD');
                        }
                        d.user_id = $('#user_id').val();
                    }
                },
                columns: [
                    { data: 'sms_body', name: 'sms_body' },
                    { data: 'total_contacts', name: 'total_contacts' },
                    { data: 'success_count', name: 'success_count' },
                    { data: 'fail_count', name: 'fail_count' },
                    { data: 'total_sms_count', name: 'total_sms_count' },
                    { data: 'sent_by', name: 'sent_by' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                order: [[6, 'desc']]
            });


            $(document).on('change', '#user_id',
                function(e) {
                    sms_history_table.ajax.reload();
                })
        });
    </script>
@endsection