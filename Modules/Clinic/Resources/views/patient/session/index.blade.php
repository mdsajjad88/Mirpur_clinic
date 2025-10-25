@extends('clinic::layouts.app2')
@section('title', 'Sessions')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('clinic::lang.subscription')
            <small>@lang('clinic::lang.manage_your_session')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('clinic::lang.all_your_session')])
            @if (auth()->user()->can('subscription.store'))
                @slot('tool')
                    <div class="box-tools">
                        <button type="button" class="btn btn-block btn-primary btn-modal"
                            data-href="{{ action([\Modules\Clinic\Http\Controllers\SessionController::class, 'create']) }}"
                            data-container=".session_modal">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    </div>
                @endslot
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="session_table">
                    <thead>
                        <tr>
                            <th>@lang('clinic::lang.session_name')</th>
                            <th>@lang('clinic::lang.session_ammount')</th>
                            <th>@lang('clinic::lang.duration_month')</th>
                            <th>@lang('clinic::lang.total_visit')</th>
                            <th>@lang('clinic::lang.type')</th>
                            <th>@lang('clinic::lang.sub_type')</th>
                            <th>@lang('clinic::lang.status')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade session_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var sessionTable = $('#session_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/session-info",
                },
                columns: [{
                        data: 'session_name',
                        name: 'session_name'
                    },
                    {
                        name: 'session_amount',
                        data: 'session_amount',

                    },
                    {
                        data: 'duration_month',
                        name: 'duration_month',
                    },
                    {
                        data: 'total_visit',
                        name: 'total_visit',
                    },
                    {
                        data: 'type',
                        name: 'type',
                    },
                    {
                        data: 'sub_type',
                        name: 'sub_type',
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                    }
                ]
            });

            $(document).on('click', '.edit_session', function() {
                $('div.session_modal').load($(this).data('href'), function() {
                    $(this).modal('show');

                    $('form#session_edit_form_clinic').submit(function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var data = form.serialize();

                        $.ajax({
                            method: 'POST',
                            url: $(this).attr('action'),
                            dataType: 'json',
                            data: data,
                            beforeSend: function(xhr) {
                                __disable_submit_button(form.find(
                                    'button[type="submit"]'));
                            },
                            success: function(result) {
                                if (result.success == true) {
                                    $('div.session_modal').modal('hide');
                                    toastr.success(result.msg);
                                    sessionTable.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });

            $(document).on('click', '.delete_session_info', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    text: 'Are you sure you want to delete?',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        var href = $(this).attr('href');
                        var data = $(this).serialize();
                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    sessionTable.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('submit', 'form#session_add_form_clinic', function(e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button(form.find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('div.session_modal').modal('hide');
                            toastr.success(result.msg);
                            if (typeof sessionTable !== 'undefined') {
                                sessionTable.ajax.reload();
                            }
                            var evt = new CustomEvent("sessionAdded", {
                                detail: result.data
                            });
                            window.dispatchEvent(evt);
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });

        })


        function toggleConsultationFields() {
            var subcription_type = $('#subcription_type').val();
            var sub_type = $('#subcription_sub_type').val();
            if (subcription_type == 'therapy' && sub_type == 'regular') {
                $('#only_consultation').hide();
                $('#only_consultation').find('input, select').removeAttr('required');
            } else {
                $('#only_consultation').show();
                $('#only_consultation').find('input, select').attr('required', true);
            }
        }

        // run when dropdown changes
        $(document).on('change', '#subcription_type, #subcription_sub_type', toggleConsultationFields);

        // run once on page/modal load (for edit case)
        $(document).ready(function() {
            toggleConsultationFields();
        });
    </script>
@endsection
