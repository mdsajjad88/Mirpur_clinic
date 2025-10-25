@extends('clinic::layouts.app2')
@section('title', 'Feedback Role')
@section('content')
    <div class="container-fluid">
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Feedback Role List'])
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                        data-href="{{ action([\Modules\Clinic\Http\Controllers\FeedbackRoleController::class, 'create']) }}"
                        data-container=".role_create_modal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot
            <div class="row">
                <div class="col">
                    <table class="table table-bordered table-striped ajax_view" id="feedback_roles_table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Survey Type</th>
                                <th>Role Name</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        @endcomponent
    </div>
    <div class="modal fade role_create_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var feedback_roles_table = $('#feedback_roles_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('feedback-role') }}",
                },
                columns: [{
                        data: null,
                        title: 'SL No',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        orderable: false,
                        searchable: false
                    }, 
                    {
                        data: 'survey_type',
                        name: 'survey_type'
                    },
                    {
                        data: 'role_name',
                        name: 'role_name'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ]
            });

            // Handle delete button
            $(document).on('click', '.delete_feedback_role', function(e) {
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
                                    $('#feedback_roles_table').DataTable().ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });

            // Handle edit button
            $(document).on('click', '.edit_feedback_role', function() {
                $('div.role_create_modal').load($(this).data('href'), function() {
                    $(this).modal('show');
                    $('form#feedback_role_update_form').submit(function(e) {
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
                                    $('div.role_create_modal').modal('hide');
                                    toastr.success(result.msg);
                                    $('#feedback_roles_table').DataTable().ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });

            // Handle create form submission
            $(document).on('submit', '#feedback_role_store_form', function(e) {
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
                            form[0].reset();
                            $('div.role_create_modal').modal('hide');
                            toastr.success(result.msg);
                            $('#feedback_roles_table').DataTable().ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });

            // Handle select KPI role button
            $(document).on('click', '.select_kpi_role', function(e) {
                e.preventDefault();
                var href = $(this).attr('href');
                $.ajax({
                    method: 'POST',
                    url: href,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            $('#feedback_roles_table').DataTable().ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
        });
    </script>
@endsection