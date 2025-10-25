@extends('clinic::layouts.app2')
@php
    $title = !empty($type) ? ucfirst($type) . ' Type List' : 'Survey Type List';
@endphp

@section('title', __($title))
@section('content')
    <div class="container-fluid">
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Survey Type List'])
            @if (auth()->user()->can('survey.type.store'))
                @slot('tool')
                    <div class="box-tools">
                        @if ($type == 'seminar')
                            <button type="button" class="btn btn-block btn-primary btn-modal"
                                data-href="{{ action([\Modules\Clinic\Http\Controllers\SurveyTypeController::class, 'create']) . '?type=seminar' }}"
                                data-container=".survey_type_create_modal">
                                <i class="fa fa-plus"></i>@lang('messages.add')
                            </button>
                        @else
                            <button type="button" class="btn btn-block btn-primary btn-modal"
                                data-href="{{ action([\Modules\Clinic\Http\Controllers\SurveyTypeController::class, 'create']) }}"
                                data-container=".survey_type_create_modal">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </button>
                        @endif
                    </div>
                @endslot
            @endif

            <div class="row">
                <div class="col">
                    <table class="table table-bordered table-striped ajax_view" id="servey_type_table" style="width: 100%">
                        <thead>
                            <tr>

                                <th>SL</th>
                                <th>Name</th>
                                @if ($type == 'seminar')
                                    <th>Total Patient</th>
                                    <th>Fee</th>
                                    <th>Mobile</th>
                                    <th>Website Url</th>
                                    <th>Already Register url</th>
                                    <th>Show Division</th>
                                    <th>Show District</th>
                                    <th>Show Primary Disease</th>
                                    <th>Show Secondary Disease</th>
                                    <th>Is Active</th>
                                @else
                                    <th>Days Prior</th>
                                    <th>Extend Days</th>
                                @endif
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        @endcomponent

        <div class="modal fade survey_type_create_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var servey_type_table = $('#servey_type_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('survey-types') }}",
                    data: function(d) {
                        d.type = "{{ $type }}";
                    }
                },
                columns: [{
                        data: null,
                        title: 'SL No',
                        render: function(data, type, row, meta) {
                            return meta.row + 1;
                        },
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'name',
                        name: 'name'
                    },
                    @if ($type == 'seminar')
                        {
                            data: 'patient_allow_count',
                            name: 'patient_allow_count'
                        }, {
                            data: 'fee',
                            name: 'fee'
                        }, {
                            data: 'mobile',
                            name: 'mobile'
                        }, {
                            data: 'website_url',
                            name: 'website_url'
                        }, {
                            data: 'already_registered_link',
                            name: 'already_registered_link'
                        }, {
                            data: 'is_show_division',
                            name: 'is_show_division'
                        }, {
                            data: 'is_show_district',
                            name: 'is_show_district'
                        }, {
                            data: 'is_show_primary_disease',
                            name: 'is_show_primary_disease'
                        }, {
                            data: 'is_show_secondary_disease',
                            name: 'is_show_secondary_disease'
                        }, {
                            data: 'is_active',
                            name: 'is_active'
                        },
                    @else
                        {
                            data: 'date_counting',
                            name: 'date_counting'
                        }, {
                            data: 'date_counting_with_pre_date',
                            name: 'date_counting_with_pre_date'
                        },
                    @endif {
                        data: 'action',
                        name: 'action'
                    },

                ]
            });

            $(document).on('submit', '#survey_type_store_form', function(e) {
                e.preventDefault();

                var form = $(this)[0]; // Get raw DOM element
                var formData = new FormData(form); // ✅ Includes file input

                $.ajax({
                    method: 'POST',
                    url: $(form).attr('action'),
                    dataType: 'json',
                    data: formData,
                    processData: false, // ✅ Must be false for file upload
                    contentType: false, // ✅ Must be false for file upload
                    beforeSend: function(xhr) {
                        $('button[type="submit"]').attr('disabled', true);
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $(form).trigger('reset');
                            $('div.survey_type_create_modal').modal('hide');
                            toastr.success(result.msg);
                            $('#servey_type_table').DataTable().ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                        $('button[type="submit"]').attr('disabled', false);
                    },
                    error: function(data) {
                        $('button[type="submit"]').attr('disabled', false);
                        var errors = data.responseJSON;
                        if (errors && errors.errors) {
                            $.each(errors.errors, function(key, value) {
                                toastr.error(value);
                            });
                        }
                    }
                });
            });

            $(document).on('click', '.delete_survey_type', function(e) {
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
                                    $('#servey_type_table').DataTable().ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('click', '.edit_survey_type', function() {
                $('div.survey_type_create_modal').load($(this).data('href'), function() {
                    $(this).modal('show');
                    tinymce.init({
                        selector: 'textarea#description',
                    });
                    // Make sure to unbind any previous submit handler
                    $(document).off('submit', 'form#survey_type_update_form').on('submit',
                        'form#survey_type_update_form',
                        function(e) {
                            e.preventDefault();
                            var form = $(this)[0]; // Get raw DOM element
                            var formData = new FormData(form); // ✅ Includes file input

                            $.ajax({
                                method: 'POST',
                                url: $(this).attr('action'),
                                data: formData,
                                processData: false, // ✅ Prevent jQuery from processing data
                                contentType: false, // ✅ Prevent jQuery from setting content type
                                dataType: 'json',
                                beforeSend: function() {
                                    $('button[type="submit"]').attr('disabled',
                                        true);
                                },
                                success: function(result) {
                                    if (result.success === true) {
                                        $('div.survey_type_create_modal').modal(
                                            'hide');
                                        toastr.success(result.msg);
                                        $('#servey_type_table').DataTable().ajax
                                            .reload();
                                    } else {
                                        toastr.error(result.msg);
                                    }
                                    $('button[type="submit"]').attr('disabled',
                                        false);
                                },
                                error: function(xhr) {
                                    $('button[type="submit"]').attr('disabled',
                                        false);
                                    if (xhr.responseJSON && xhr.responseJSON
                                        .errors) {
                                        $.each(xhr.responseJSON.errors, function(
                                            key, msg) {
                                            toastr.error(msg);
                                        });
                                    } else {
                                        toastr.error('Something went wrong.');
                                    }
                                }
                            });
                        });
                });
            });
            $(document).on('shown.bs.modal', '.survey_type_create_modal', function(e) {
                if (tinymce.get('description')) {
                    tinymce.get('description').remove();
                }

                tinymce.init({
                    selector: 'textarea#description',
                });
            });


        });
    </script>
@endsection
