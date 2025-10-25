@extends('clinic::layouts.app2')
@section('title', 'Duration')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                @component('components.widget', ['title' => 'Duration', 'class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <button type="button" class="btn btn-block btn-primary btn-modal"
                                data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DurationController::class, 'create']) }}"
                                data-container=".durations_modal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot
                    <table class="table table-bordered table-striped ajax_view" id="durations_table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                @endcomponent
            </div>
        </div>
    </div>
    <div class="modal fade durations_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection
@section('javascript')
    <script type='text/javascript'>
        $(document).ready(function() {
            var durations_table = $('#durations_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/medicine-durations",
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [{
                        data: null,
                        name: 'sl',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'value',
                        name: 'value'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                    },
                ],
            });
            $(document).on('click', '.delete_duration', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    text: 'Are you sure you want to delete this durations?',
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
                                    $('#durations_table').DataTable().ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('click', '.edit_duration', function() {
                $('div.durations_modal').load($(this).data('href'), function() {
                    $(this).modal('show');
                    $('form#durations_update_form').submit(function(e) {
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
                                    $('div.durations_modal').modal('hide');
                                    toastr.success(result.msg);
                                    durations_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });
            $(document).on('submit', 'form#durations_store_form', function(e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();
                var submitButton = $(form).find('button[type="submit"]');

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
                            $('div.durations_modal').modal('hide');
                            toastr.success(result.msg);
                            if (typeof durations_table !== 'undefined') {
                                durations_table.ajax.reload();
                            }
                            var evt = new CustomEvent("durationsAdded", {
                                detail: result.data
                            });
                            window.dispatchEvent(evt);

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = '';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage += xhr.responseJSON.message;
                        } else {
                            errorMessage += status;
                        }

                        toastr.error(errorMessage);
                        submitButton.prop('disabled', false).text(
                            'Submit');
                    },
                    complete: function() {
                        submitButton.prop('disabled', false).text(
                            'Submit'
                        );
                    }
                });
            });
        });
    </script>
@endsection
