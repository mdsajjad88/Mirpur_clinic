@extends('clinic::layouts.app2')
@section('title', 'Feedback Question')
@section('content')
    <div class="container-fluid">
        <div class="row">
            @component('components.filters', ['title' => 'Filters', 'class' => 'box-primary'])
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('survey_types_filter', 'Survey Types:') !!}
                        {!! Form::select('survey_types_filter', $serveyTypes, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('feedback_roles_filter', 'Feedback Roles:') !!}
                        {!! Form::select('feedback_roles_filter', $roles, null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'placeholder' => __('lang_v1.all'),
                        ]) !!}
                    </div>
                </div>
            @endcomponent
        </div>

        <div class="row">
            @component('components.widget', ['class' => 'box-primary'])
                <div class="box-header">
                    <h4 class="box-title">Feedback Question List (Survey Name: <span id="survey_name_field"></span>)</h4>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-block btn-primary btn-modal add_new_feedback_question"
                            data-href="{{ action([\Modules\Clinic\Http\Controllers\FeedbackQuestionController::class, 'create']) }}">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    </div>
                </div>
               
                <div class="col">
                    <table class="table table-bordered table-striped ajax_view" id="feedback_question_table"
                        style="width: 100%">
                        <thead>
                            <tr>
                                
                                <th>position</th>
                                <th style="min-width: 50px;">SL No.</th>
                                {{-- <th>Survey</th> --}}
                                <th>Role</th>
                                <th>Question</th>
                                <th>Question Bn</th>
                                <th>Type</th>
                                <th>Options</th>
                                <th>Is Mandatory?</th>
                                {{-- <th>Display Bn</th> --}}
                                <th style="min-width: 100px;">Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcomponent
        </div>

        <div class="modal fade feedback_question_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var feedback_question_table = $('#feedback_question_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('feedback-question') }}",
                    data: function(d) {
                        d.survey_types_filter = $('#survey_types_filter').val();
                        d.feedback_roles_filter = $('#feedback_roles_filter').val();
                    }
                },
                aaSorting: [0, 'asc'],
                columns: [{
                        data: 'position',
                        name: 'position',
                        visible: false,
                    },
                {
                        data: 'position_design',
                        name: 'position_design',
                        orderable: false,
                        searchable: false
                    },
                    // {
                    //     data: 'survey_type_name',
                    //     name: 'survey_types.name',
                    // },
                    {
                        data: 'role_name',
                        name: 'role_name'
                    },
                    {
                        data: 'question_text',
                        name: 'question_text'
                    },
                    {
                        data: 'question_text_bn',
                        name: 'question_text_bn'
                    },
                    {
                        data: 'question_type',
                        name: 'question_type'
                    },
                    {
                        data: 'options',
                        name: 'options',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'is_show_form',
                        name: 'is_show_form',
                        orderable: false,
                        searchable: false
                    },
                    // {
                    //     data: 'display_bn',
                    //     name: 'display_bn'
                    // },
                    {
                        data: 'action',
                        name: 'action'
                    },

                ],
                drawCallback: function(settings) {
                    $('input[type="checkbox"].question-icheck, input[type="radio"].question-icheck')
                        .iCheck({
                            checkboxClass: 'icheckbox_square-blue',
                            radioClass: 'iradio_square-blue'
                        });


                    var api = this.api();
                    var data = api.rows({
                        page: 'current'
                    }).data();
                    if (data.length > 0) {
                        $('#survey_name_field').text(data[0].survey_type_name || '');
                    } else {
                        $('#survey_name_field').text('');
                    }
                }
            });
            $(document).on('change', '#survey_types_filter, #feedback_roles_filter', function() {
                feedback_question_table.ajax.reload();
            });
            $(document).on('click', '.delete_feedback_question', function(e) {
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
                                    $('#feedback_question_table').DataTable().ajax
                                        .reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('click', '.edit_feedback_question', function() {
                $('div.feedback_question_modal').load($(this).data('href'), function() {
                    $(this).modal('show');
                    $('form#feedback_question_update_form').submit(function(e) {
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
                                    $('div.feedback_question_modal').modal(
                                        'hide');
                                    toastr.success(result.msg);
                                    $('#feedback_question_table').DataTable()
                                        .ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });
            $(document).on('submit', '#feedback_question_store_form', function(e) {
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
                        form[0].reset();
                        if (result.success == true) {
                            $('div.feedback_question_modal').modal('hide');
                            toastr.success(result.msg);
                            $('#feedback_question_table').DataTable().ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
            $(document).on('click', '.remove_option_this_row', function() {
                $(this).closest('.option-row').remove();
            });
            $(document).on('ifChanged', '.question-icheck', function() {
                const answerId = $(this).data('id');
                const is_checked = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    method: 'POST',
                    url: `/is/show/question/in/form/${answerId}`,
                    dataType: 'json',
                    data: {
                        is_checked: is_checked,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            $('#feedback_question_table').DataTable()
                                .ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }

                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                    }
                });
            });

            $('#feedback_question_table tbody').sortable({
                helper: function(e, tr) {
                    const originals = tr.children();
                    const helper = tr.clone();
                    helper.children().each(function(index) {
                        $(this).width(originals.eq(index).width());
                    });
                    return helper;
                },
                update: function(event, ui) {
                    let order = [];
                    $('#feedback_question_table tbody tr').each(function(index, element) {
                        let id = $(element).data('id');
                        if (id) {
                            order.push({
                                id: id,
                                position: index + 1
                            });
                        }
                    });

                    // Send to backend
                    $.ajax({
                        url: '{{ route('update.feedback.question.position') }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            order: order
                        },
                        success: function(response) {
                            $('#feedback_question_table').DataTable().ajax.reload();
                        }
                    });
                }
            });

            $(document).on('click', '.add_new_feedback_question', function() {
                var url = $(this).data('href');
                $.ajax({
                    method: 'GET',
                    dataType: 'html',
                    url: url,
                    success: function(response) {
                        $('.feedback_question_modal').html(response).modal({
                            backdrop: 'static',
                            keyboard: false
                        }).modal('show');
                    },
                });
            });
        })
    </script>
@endsection
