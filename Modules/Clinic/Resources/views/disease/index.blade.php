@extends('clinic::layouts.app2')
@php
        $d_or_c = '';
        if ($type == 'doctor_dashboard') {
            $d_or_c = 'All Your Chief Complaint';
            $title = 'Chief Complain';
        } elseif ($type == 'disease') {
            $d_or_c = 'All Your Disease';
            $title = 'Disease';
        }
    @endphp
@section('title', $title)

@section('content')

    <!-- Content Header (Page header) -->
    
    @if ($type != 'doctor_dashboard')
        <section class="content-header">
            <h1>@lang('clinic::lang.disease')
                <small>@lang('clinic::lang.manage_your_disease')</small>
            </h1>
        </section>
    @endif
    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => $d_or_c])
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                        data-href="{{ action([\Modules\Clinic\Http\Controllers\DiseasesController::class, 'create'])."?type=$type" }}"
                        data-container=".disease_modal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="clinic_disease_table">
                    <thead>
                        <tr>
                            <th>@if($type != 'doctor_dashboard') @lang('clinic::lang.disease') @else Name @endif</th>
                            @if($type != 'doctor_dashboard')
                            <th>@lang('clinic::lang.disease_name_bangla')</th>
                            <th>@lang('clinic::doctor.description')</th>
                            <th>Category</th>
                            @endif
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade disease_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var diseaseTable = $('#clinic_disease_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('clinic-diseases.index') }}" + "?type={{ $type }}",
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    @if($type != 'doctor_dashboard')
                    {
                        data: 'bn_name',
                        name: 'bn_name'
                    },
                    {
                        data: 'description',
                        name: 'description'
                    },
                    {
                        data: 'category_name',
                        name: 'category_name',
                        searchable: true,
                    },
                    @endif
                    {
                        data: 'action',
                        name: 'action'
                    }
                ]
            });

            $(document).on('click', 'button.edit_disease_button_clinic', function() {
                $('div.disease_modal').load($(this).data('href'), function() {
                    $(this).modal('show');

                    $('form#disease_edit_form_clinic').submit(function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var data = form.serialize();
                        var submitButton = form.find('button[type="submit"]');

                        $.ajax({
                            method: 'POST',
                            url: $(this).attr('action'),
                            dataType: 'json',
                            data: data,
                            beforeSend: function(xhr) {
                                __disable_submit_button(
                                    submitButton);
                            },
                            success: function(result) {
                                if (result.success == true) {
                                    $('div.disease_modal').modal('hide');
                                    form.trigger('reset');
                                    toastr.success(result.msg);
                                    $('#clinic_disease_table').DataTable().ajax
                                        .reload();

                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                            error: function(xhr, status, error) {
                                var errorMessage = '';
                                if (xhr.responseJSON && xhr.responseJSON
                                    .message) {
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
            });


            $(document).on('click', 'button.delete_disease_button_clinic', function() {
                swal({
                    title: LANG.sure,
                    text: 'Are you sure want to delete this disease?',
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
                                    diseaseTable.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });

        });
    </script>
@endsection
