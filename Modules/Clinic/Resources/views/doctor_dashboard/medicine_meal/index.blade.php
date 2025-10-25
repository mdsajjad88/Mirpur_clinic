@extends('clinic::layouts.app2')
@section('title', 'Medicine Meal')
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col">
                @component('components.widget', ['title' => 'Medicine Meal', 'class' => 'box-primary'])
                @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                        data-href="{{ action([\Modules\Clinic\Http\Controllers\doctor\MedicineMealController::class, 'create']).'?type='.$type }}"
                        data-container=".medicine_meal_modal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot
                    <table class="table table-bordered table-striped ajax_view" id="medicine_meal_table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>SL</th>
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
    <div class="modal fade medicine_meal_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection
@section('javascript')
    <script type='text/javascript'>
        $(document).ready(function() {
            var medicine_meal_table = $('#medicine_meal_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/medicine-meal" + '?type={{ $type }}',
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
            $(document).on('click', '.delete_meal', function(e) {
                e.preventDefault();
                swal({
                    title: LANG.sure,
                    text: 'Are you sure you want to delete this meal?',
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
                                    $('#medicine_meal_table').DataTable().ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('click', '.edit_meal', function() {
                $('div.medicine_meal_modal').load($(this).data('href'), function() {
                    $(this).modal('show');
                    $('form#meal_medicine_update_form').submit(function(e) {
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
                                    $('div.medicine_meal_modal').modal('hide');
                                    toastr.success(result.msg);
                                    medicine_meal_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });
        });
    </script>
@endsection
