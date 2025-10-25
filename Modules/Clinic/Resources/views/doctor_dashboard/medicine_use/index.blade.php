@extends('clinic::layouts.app2')
@section('title', 'Medicine Usage')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                @component('components.widget', ['title' => 'Dosage Time', 'class' => 'box-primary'])
                    @slot('tool')
                        <div class="box-tools">
                            <button type="button" class="btn btn-block btn-primary btn-modal"
                                data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\MedicineUseController::class, 'create']) }}"
                                data-container=".medicine_use_modal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        </div>
                    @endslot
                    <table class="table table-bordered table-striped ajax_view" id="medicine_use_table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Name</th>
                                <th>Value</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                @endcomponent
            </div>
        </div>
    </div>
    <div class="modal fade medicine_use_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection
@section('javascript')
    <script type='text/javascript'>
        $(document).ready(function() {
            var medicine_use_table = $('#medicine_use_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/medicine-use",
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
                        data: 'name',
                        name: 'name'
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
            $(document).on('click', '.delete_medicine', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    text: 'Are you sure you want to delete this medicine?',
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
                                    $('#medicine_use_table').DataTable().ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('click', '.edit_medicine', function() {
                $('div.medicine_use_modal').load($(this).data('href'), function() {
                    $(this).modal('show');
                    $('form#use_medicine_update_form').submit(function(e) {
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
                                    $('div.medicine_use_modal').modal('hide');
                                    toastr.success(result.msg);
                                    medicine_use_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });
            $(document).on('submit', 'form#use_medicine_store_form', function(e) {
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
                            $('div.medicine_use_modal').modal('hide');
                            toastr.success(result.msg);
                            if (typeof medicine_use_table !== 'undefined') {
                                medicine_use_table.ajax.reload();
                            }
                            var evt = new CustomEvent("usageMedicineAdded", {
                                detail: result.data
                            });
                            window.dispatchEvent(evt);

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
        });
    </script>
@endsection
