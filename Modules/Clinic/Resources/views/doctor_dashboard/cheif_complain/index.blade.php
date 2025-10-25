@extends('clinic::layouts.app2')
@section('title', 'Chief Complaint')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                @component('components.widget', ['title' => 'Chief Complaint', 'class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <button type="button" class="btn btn-block btn-primary btn-modal"
                                data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\ChiefComplainController::class, 'create']) }}"
                                data-container=".cheif_complain_modal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot
                    <table class="table table-bordered table-striped ajax_view" id="cheif_complain_table" style="width: 100%">
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
    <div class="modal fade cheif_complain_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection
@section('javascript')
    <script type='text/javascript'>
        $(document).ready(function() {
            var cheif_complain_table = $('#cheif_complain_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/chief-complaint",
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
                        name: 'status',
                    },
                    {
                        data: 'action',
                        name: 'action',
                    },
                ],
            });
            $(document).on('click', '.delete_cheif_complain', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    text: 'Are you sure you want to delete this complaint?',
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
                                    $('#cheif_complain_table').DataTable().ajax
                                        .reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('click', '.edit_cheif_complain', function() {
                $('div.cheif_complain_modal').load($(this).data('href'), function() {
                    $(this).modal('show');
                    $('form#cheif_complain_update_form').submit(function(e) {
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
                                    $('div.cheif_complain_modal').modal('hide');
                                    toastr.success(result.msg);
                                    cheif_complain_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });
            $(document).on('submit', 'form#cheif_complain_store_form', function(e) {
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
                            $('div.cheif_complain_modal').modal('hide');
                            toastr.success(result.msg);
                            if (typeof cheif_complain_table !== 'undefined') {
                                cheif_complain_table.ajax.reload();
                            }
                            var evt = new CustomEvent("complainAdded", {
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
