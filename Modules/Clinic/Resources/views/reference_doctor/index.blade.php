@extends('clinic::layouts.app2')
@section('title', 'Reference Doctor')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('clinic::lang.reference_list')
            <small>@lang('clinic::lang.manage_your_reference')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('clinic::lang.all_your_reference')])
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                        data-href="{{ action([\Modules\Clinic\Http\Controllers\Survey\ReferenceDoctorController::class, 'create']) }}"
                        data-container=".doctor_reference">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="doctor_reference_table">
                    <thead>
                        <tr>
                            <th>@lang('clinic::lang.dr_name')</th>
                            <th>@lang('clinic::lang.hospital_name')</th>
                            {{-- <th>@lang('clinic::lang.descriptions')</th> --}}
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade doctor_reference" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var doctor_reference_table = $('#doctor_reference_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('reference-doctor.index') }}",
                },
                columns: [{
                        data: 'dr_name',
                        name: 'dr_name'
                    },
                    {
                        data: 'hospital_name',
                        name: 'hospital_name'
                    },
                    // {
                    //     data: 'description',
                    //     name: 'description'
                    // },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }, // Add the action column here
                ]
            });
            $(document).on('click', 'button.edit_doctor_reference', function() {
                $('div.doctor_reference').load($(this).data('href'), function() {
                    $(this).modal('show');

                    $('form#reference_doctor_edit_form').submit(function(e) {
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
                                    $('div.doctor_reference').modal('hide');
                                    toastr.success(result.msg);
                                    doctor_reference_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });

            $(document).on('click', 'button.delete_doctor_reference', function() {
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
                                    doctor_reference_table.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('submit', 'form#reference_doctor_add_form', function(e) {
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
                            $('div.doctor_reference').modal('hide');
                            toastr.success(result.msg);
                            if (typeof doctor_reference_table !== 'undefined') {
                                doctor_reference_table.ajax.reload();
                            }
                            var evt = new CustomEvent("referenceAdded", {
                                detail: result.data
                            });
                            window.dispatchEvent(evt);
                            //event can be listened as
                            //window.addEventListener("brandAdded", function(evt) {}

                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
        })
    </script>
@endsection
