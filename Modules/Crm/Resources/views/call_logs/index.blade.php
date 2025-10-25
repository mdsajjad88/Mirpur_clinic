@extends('clinic::layouts.app2')

@section('title', __('crm::lang.call_log'))

@section('content')
    @include('crm::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('crm::lang.call_log')</h1>
    </section>

    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
                @can('crm.view_all_call_log')
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('user_id', __('crm::lang.call_log_created_by') . ':') !!}
                            {!! Form::select('user_id', $users, null, [
                                'class' => 'form-control select2',
                                'id' => 'user_id',
                                'placeholder' => __('messages.all'),
                                'style' => 'width: 100% !important;',
                            ]) !!}
                        </div>
                    </div>
                @endcan
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('subjects', __('crm::lang.call_subject') . ':') !!}
                        {!! Form::select('call_subject_id[]', $subjects, null, [
                            'class' => 'form-control select2',
                            'id' => 'call_subject_ids',
                            'multiple',
                            'style' => 'width: 100% !important;',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('tags', __('crm::lang.call_tag') . ':') !!}
                        {!! Form::select('call_tag_id[]', $tags, null, [
                            'class' => 'form-control select2',
                            'id' => 'call_tag_ids',
                            'multiple',
                            'style' => 'width: 100% !important;',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('call_log_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('call_log_date_range', null, [
                            'placeholder' => __('lang_v1.select_a_date_range'),
                            'class' => 'form-control',
                            'readonly',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row">
                        <div class="col-md-6">
                            {!! Form::label('start_second', __('crm::lang.start_second') . ':') !!}
                            {!! Form::number('start_second', null, ['id' => 'duration_min', 'class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-6">
                            {!! Form::label('end_second', __('crm::lang.end_second') . ':') !!}
                            {!! Form::number('end_second', null, ['id' => 'duration_max', 'class' => 'form-control']) !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('call_type', __('crm::lang.call_type') . ':') !!}
                        {!! Form::select(
                            'call_type',
                            ['' => __('messages.all'), 'inbound' => __('crm::lang.inbound'), 'outbound' => __('crm::lang.outbound')],
                            null,
                            [
                                'class' => 'form-control',
                                'id' => 'call_type',
                                'style' => 'width: 100% !important;',
                                'placeholder' => __('messages.please_select'),
                            ],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('contact_type', __('contact.contact_type') . ':') !!}
                        {!! Form::select(
                            'contact_type',
                            ['' => __('messages.all'), 'customer' => __('contact.customer'), 'lead' => __('contact.lead')],
                            null,
                            [
                                'class' => 'form-control',
                                'id' => 'contact_type',
                                'style' => 'width: 100% !important;',
                                'placeholder' => __('messages.please_select'),
                            ],
                        ) !!}
                    </div>
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['class' => 'box-solid'])
            @slot('tool')
                <div class="box-tools">
                    <a href="{{ action([\Modules\Crm\Http\Controllers\CallLogController::class, 'create']) }}"
                        class="btn btn-primary">@lang('messages.add') </a>
                </div>
            @endslot
            <table class="table table-bordered table-striped" id="call_logs_table" style="width: 100%;">
                <thead>
                    <tr>
                        @if ($is_admin)
                            <th><input type="checkbox" id="select-all-row" data-table-id="call_logs_table"></th>
                        @endif
                        <th>@lang('crm::lang.created_at')</th>
                        <th>@lang('restaurant.start_time')</th>
                        <th>@lang('crm::lang.agent')</th>
                        <th>@lang('crm::lang.mobile')</th>
                        <th>@lang('crm::lang.call_type')</th>
                        <th>@lang('crm::lang.call_duration')</th>
                        <th>@lang('report.contact')</th>
                        <th>@lang('crm::lang.note')</th>
                        <th>@lang('crm::lang.subject')</th>
                        <th>@lang('crm::lang.tag')</th>
                        <th>@lang('crm::lang.campaign_name')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
                @if ($is_admin)
                    <tfoot>
                        <tr>
                            <td colspan="10">
                                <div style="display: flex; width: 100%;">
                                    {!! Form::open([
                                        'url' => action([\Modules\Crm\Http\Controllers\CallLogController::class, 'massDestroy']),
                                        'method' => 'post',
                                        'id' => 'mass_delete_form',
                                    ]) !!}
                                    {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']) !!}
                                    {!! Form::submit(__('lang_v1.delete_selected'), ['class' => 'btn btn-xs btn-danger', 'id' => 'delete-selected']) !!}
                                    {!! Form::close() !!}
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        @endcomponent

        <div class="modal fade feedback_modal" tabindex="-1" role="dialog" aria-labelledby="feedbackModalLabel"
            aria-hidden="true">
        </div>

        <div class="modal fade call_log_edit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">


    </section>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            // 🔥 Set default to THIS MONTH
            dateRangeSettings.startDate = moment().startOf('month');
            dateRangeSettings.endDate = moment().endOf('month');
            $('#call_log_date_range').daterangepicker(
                dateRangeSettings,
                function(start, end) {
                    $('#call_log_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(
                        moment_date_format));
                    call_logs_table.ajax.reload();
                }
            );
            // ✅ Set visible input field value
            $('#call_log_date_range').val(
                moment().startOf('month').format(moment_date_format) + ' ~ ' +
                moment().endOf('month').format(moment_date_format)
            );
            $('#call_log_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#call_log_date_range').val('');
                call_logs_table.ajax.reload();
            });

            call_logs_table =
                $("#call_logs_table").DataTable({
                    @if ($is_admin)
                        aaSorting: [
                            [1, 'desc']
                        ],
                    @endif
                    processing: true,
                    serverSide: true,
                    scrollY: "75vh",
                    scrollX: true,
                    scrollCollapse: true,
                    fixedHeader: false,
                    'ajax': {
                        url: "{{ action([\Modules\Crm\Http\Controllers\CallLogController::class, 'index']) }}",
                        data: function(d) {
                            d.user_id = $('#user_id').val();
                            d.subject_ids = $('#call_subject_ids').val();
                            d.tag_ids = $('#call_tag_ids').val();
                            if ($('#call_log_date_range').val()) {
                                d.start_time = $('#call_log_date_range').data('daterangepicker').startDate
                                    .format('YYYY-MM-DD');
                                d.end_time = $('#call_log_date_range').data('daterangepicker').endDate
                                    .format('YYYY-MM-DD');
                            }
                            d.duration_min = $('#duration_min').val();
                            d.duration_max = $('#duration_max').val();
                            d.call_type = $('#call_type').val();
                            d.contact_type = $('#contact_type').val();
                        }
                    },
                    @if ($is_admin)
                        aaSorting: [
                            [1, 'desc']
                        ],
                    @else
                        aaSorting: [
                            [0, 'desc']
                        ],
                    @endif
                    columns: [
                        @if ($is_admin)
                            {
                                data: 'mass_delete',
                                searchable: false,
                                orderable: false
                            },
                        @endif {
                            data: 'created_at',
                            name: 'created_at',
                            visible: false
                        },
                        {
                            data: 'start_time',
                            name: 'start_time'
                        },
                        {
                            data: 'created_user_name',
                            name: 'created_user_name'
                        },
                        {
                            data: 'contact_number',
                            name: 'c.mobile',
                            visible: false
                        },
                        {
                            data: 'call_type',
                            name: 'call_type',
                            render: function(data, type, row) {
                                if (data) {
                                    return data.charAt(0).toUpperCase() + data.slice(1);
                                }
                                return data;
                            }
                        },
                        {
                            data: 'duration',
                            name: 'duration'
                        },
                        {
                            data: 'contact_name',
                            name: 'contact_name'
                        },
                        {
                            data: 'note',
                            name: 'note'
                        },
                        {
                            data: 'subject_names',
                            name: 'subject_names'
                        },
                        {
                            data: 'tag_names',
                            name: 'tag_names'
                        },
                        {
                            data: 'campaign_name',
                            name: 'campaign_name'
                        },
                        {
                            data: 'action',
                            name: 'action',
                            searchable: false
                        }
                    ],
                    createdRow: function(row, data, dataIndex) {
                        $(row).find('td:eq(0)').attr('class', 'selectable_td');
                    }
                });

            $(document).on('change', '#user_id, #call_subject_ids, #call_tag_ids, #call_type, #contact_type',
                function(e) {
                    call_logs_table.ajax.reload();
                })
            $(document).on('input', '#duration_min, #duration_max', function(e) {
                call_logs_table.ajax.reload();
            })
        });

        $(document).on('click', '#delete-selected', function(e) {
            e.preventDefault();
            var selected_rows = getSelectedRows();

            if (selected_rows.length > 0) {
                $('input#selected_rows').val(selected_rows);
                swal({
                    title: LANG.sure,
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $('form#mass_delete_form').submit();
                    }
                });
            }

        });

        $(document).on('submit', 'form#call_log_update_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            var submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).html(
                '<i class="fas fa-spinner fa-spin"></i> {{ __('messages.saving') }}');

            $.ajax({
                method: 'POST',
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success) {
                        $('div.call_log_edit_modal').modal('hide');
                        toastr.success(result.msg);
                        form[0].reset();
                        $('#call_logs_table').DataTable().ajax.reload();
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function() {
                    toastr.error('{{ __('messages.something_went_wrong') }}');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('{{ __('messages.save') }}');
                }
            });
        });
    </script>
@endsection
{{-- else {
                $('input#selected_rows').val('');
                swal('@extends('clinic::layouts.app2')');
            } --}}
