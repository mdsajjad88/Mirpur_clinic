@extends('clinic::layouts.app2')
@section('title', 'Feedback Answer')
@section('content')
    <div class="container-fluid">
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Feedback Answer List'])
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                        data-href="{{ action([\Modules\Clinic\Http\Controllers\FeedbackAnswerController::class, 'create']) }}"
                        data-container=".feedback_answer_modal">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot
            <div class="row">
                <div class="col">
                    <table class="table table-bordered table-striped ajax_view" id="feedback_answer_table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>SL</th>
                                <th>Role</th>
                                <th>Question</th>
                                <th>Answer</th>
                                <th>Display Rating</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        @endcomponent
        <div class="modal fade feedback_answer_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

            $('#feedback_answer_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('feedback-answer') }}",
                    dataSrc: 'data' // Ensure this matches the structure of your server response
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
                        name: 'role_name',
                        data: 'role_name',
                    },
                    {
                        data: 'ques_text',
                        name: 'ques_text',
                        title: 'Question'
                    },
                    {
                        data: 'answers',
                        name: 'answers',
                        title: 'Answers',
                        render: function(data) {
                            if (!data) return '';

                            const items = data.split('||');
                            let html = '<ul style="list-style: none; padding-left: 0;">';

                            items.forEach(item => {
                                const parts = item.split('::');
                                const id = parts[0];
                                const text = parts[1];
                                const isChecked = parts[2] == 1 ? 'checked' : '';

                                html += `<li>
                                    <label>
                                        <input type="checkbox" class="answer-checkbox" data-id="${id}" value="${id}" ${isChecked}>
                                        ${text}
                                    </label>
                                </li>`;
                            });

                            html += '</ul>';
                            return html;
                        }
                    },
                    {
                        data: 'display_rating_value',
                        name: 'display_rating_value',
                    },
                    {
                        data: 'action',
                        name: 'action',
                        title: 'Action',
                        orderable: false,
                        searchable: false
                    }
                ],
                drawCallback: function(settings) {
                    $('input[type="checkbox"].answer-checkbox').iCheck({
                        checkboxClass: 'icheckbox_square-blue',
                        radioClass: 'iradio_square-blue'
                    });
                }
            });


            $(document).on('click', '.delete_feedback_answer', function(e) {
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
                                    $('#feedback_answer_table').DataTable().ajax
                                        .reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    }
                });
            });
            $(document).on('click', '.edit_feedback_answer', function() {
                $('div.feedback_answer_modal').load($(this).data('href'), function() {
                    $(this).modal('show');
                    $('form#feedback_answer_update_form').submit(function(e) {
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
                                    $('div.feedback_answer_modal').modal(
                                        'hide');
                                    toastr.success(result.msg);
                                    $('#feedback_answer_table').DataTable()
                                        .ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });
            $(document).on('submit', '#feedback_answer_store_form', function(e) {
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
                            $('div.feedback_answer_modal').modal('hide');
                            toastr.success(result.msg);
                            $('#feedback_answer_table').DataTable().ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
            $(document).on('click', '.add_new_option_row', function() {
                let parentRow = $(this).closest('.option-row');
                let newRow = parentRow.clone();
                newRow.find('.label-holder').remove();

                newRow.find('input').val('');
                newRow.find('.add_new_option_row')
                    .removeClass('add_new_option_row')
                    .addClass('remove_option_this_row')
                    .html('<i class="fa fa-minus-circle text-danger fa-lg"></i>');
                parentRow.after(newRow);
            });

            // Handle Remove Option Row
            $(document).on('click', '.remove_option_this_row', function() {
                $(this).closest('.option-row').remove();
            });

            $(document).on('ifChanged', '.answer-checkbox', function() {
                const answerId = $(this).data('id');
                const is_checked = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    method: 'POST',
                    url: `/is/show/answer/in/form/${answerId}`,
                    dataType: 'json',
                    data: {
                        is_checked: is_checked,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            $('#feedback_answer_table').DataTable()
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
        })
    </script>
@endsection
