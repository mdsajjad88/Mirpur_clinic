@extends('clinic::layouts.app2')
@section('title', __('X-Medicine'))
@section('content')
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('clinic::lang.patient_old_medicine')])
            @slot('tool')
                @if(auth()->user()->can('patient_old_medicine.store'))
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                        data-href="{{ action([\Modules\Clinic\Http\Controllers\OldMedicineController::class, 'create']) }}"
                        data-container=".old_medicine">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
                @endif
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="old_medicine_table" style="width: 100%">
                    <thead>
                        <tr>
                            <th>@lang('clinic::lang.medicine_name')</th>
                            <th>@lang('messages.action')</th>

                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade old_medicine" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var old_medicine_table = $('#old_medicine_table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                ajax: {
                    url: "{{ route('patient-old-medicine.index') }}",
                },
                columns: [
                    {
                        data: 'medicine_name',
                        name: 'medicine_name'
                    },
                    {
                        data: 'action',
                        searchable: false,
                        orderable: false
                    },
                ],
            });

            old_medicine_table.ajax.reload();
            $(document).on('click', 'button.edit_medicine', function() {
                $('div.old_medicine').load($(this).data('href'), function() {
                    $(this).modal('show');

                    $('form#medicine_update_form').submit(function(e) {
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
                                    $('div.old_medicine').modal('hide');
                                    toastr.success(result.msg);
                                    old_medicine_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });

            $(document).on('click', 'button.delete_medicine', function() {
                swal({
                    title: LANG.sure,
                    text: 'Are you sure you want to delete',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        var href = $(this).data('href');
                        var data = $(this).serialize();

                        $.ajax({
                            method: 'DELETE',
                            url: href,
                            dataType: 'json',
                            data: data,
                            success: function(result) {
                                if (result.success == true) {
                                    toastr.success(result.msg);
                                    old_medicine_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('submit', 'form#medicine_store_form', function(e) {
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
                            $('div.old_medicine').modal('hide');
                            toastr.success(result.msg);
                            if (typeof old_medicine_table !== 'undefined') {
                                old_medicine_table.ajax.reload();
                            }
                            var evt = new CustomEvent("medicineAdded", {
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
