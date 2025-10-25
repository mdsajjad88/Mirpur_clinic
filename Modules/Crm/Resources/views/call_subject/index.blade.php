@extends('clinic::layouts.app2')

@section('title', __('crm::lang.call_subject'))

@section('content')
@include('crm::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.call_subject')])
        <div class="row">
            <div class="col-md-12">
                @slot('tool')
                    @can('crm.call_subject_store')
                    <div class="box-tools">
                        <button type="button" class="btn btn-primary btn-modal"
                            data-href="{{ action([\Modules\Crm\Http\Controllers\CrmCallSubjectController::class, 'create']) }}"
                            data-container=".call_subject_modal">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    </div>
                    @endcan
                @endslot

                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="call_subject_table">
                        <thead>
                            <tr>
                                <th>@lang('messages.action')</th>
                                <th>@lang('crm::lang.name')</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal fade call_subject_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    @endcomponent
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var call_subject_table = $('#call_subject_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ action([\Modules\Crm\Http\Controllers\CrmCallSubjectController::class, 'index']) }}"
            },
            columns: [
                { data: 'action', name: 'action', searchable: false, sortable: false },
                { data: 'name', name: 'name' }
            ]
        });

        $(document).on('submit', 'form#call_subject_add_form', function(e) {
            e.preventDefault();

            var form = $(this);
            var data = form.serialize();
            var submitBtn = form.find('button[type="submit"]');

            submitBtn.prop('disabled', true).html(
                '<i class="fa fa-spinner fa-spin"></i> {{ __("messages.saving") }}'
            );

            $.ajax({
                method: 'POST',
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success) {
                        $('.call_subject_modal').modal('hide');
                        toastr.success(result.msg);
                        form[0].reset();
                        if (typeof call_subject_table !== 'undefined') {
                            call_subject_table.ajax.reload();
                        }
                    } else {
                        toastr.error(result.msg);
                    }
                },
                error: function() {
                    toastr.error('{{ __("messages.something_went_wrong") }}');
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html('{{ __("messages.save") }}');
                }
            });
        });

        $(document).on('click', '.subject_edit', function() {
            $('.call_subject_modal').load($(this).data('href'), function() {
                $(this).modal('show');

                $('form#call_subject_update_form').submit(function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var data = form.serialize();
                    var submitBtn = form.find('button[type="submit"]');

                    submitBtn.prop('disabled', true).html(
                        '<i class="fa fa-spinner fa-spin"></i> {{ __("messages.saving") }}'
                    );

                    $.ajax({
                        method: 'POST',
                        url: form.attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success) {
                                $('.call_subject_modal').modal('hide');
                                toastr.success(result.msg);
                                call_subject_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                        complete: function() {
                            submitBtn.prop('disabled', false).html('{{ __("messages.save") }}');
                        }
                    });
                });
            });
        });

        $(document).on('click', '.subject_delete', function() {
            swal({
                title: LANG.sure,
                text: '{{ __("crm::lang.call_subject") }} will be deleted.',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(function(willDelete) {
                if (willDelete) {
                    var href = $(this).data('href');
                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        success: function(result) {
                            if (result.success) {
                                toastr.success(result.msg);
                                call_subject_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            }.bind(this));
        });
    });
</script>
@endsection
