@extends('clinic::layouts.app2')
@section('title', __('crm::lang.call_tag'))
@section('content')
    @include('crm::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        @component('components.widget', ['class' => 'box-solid', 'title' => __('crm::lang.call_tag')])
            <div class="row">
                <div class="col">
                    @can('crm.call_tag_store')
                    @slot('tool')
                        <div class="box-tools">
                            <button type="button" class="btn btn-block btn-primary btn-modal"
                                data-href="{{ action([\Modules\Crm\Http\Controllers\CallTagController::class, 'create']) }}"
                                data-container=".call_tag_modal">
                                @lang('messages.add')
                            </button>
                        </div>
                    @endslot
                    @endcan
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="call_tag_table">
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
            <div class="modal fade call_tag_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        @endcomponent
    </section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var call_tag_table = $('#call_tag_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ action([\Modules\Crm\Http\Controllers\CallTagController::class, 'index']) }}"
            },
            columns: [
                { data: 'action', name: 'action', searchable: false, sortable: false },
                { data: 'value', name: 'value' },
            ],
        });

        // Add form submission
        $(document).on('submit', 'form#call_tag_add_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            var submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("messages.saving") }}');

            $.ajax({
                method: 'POST',
                url: form.attr('action'),
                dataType: 'json',
                data: data,
                success: function(result) {
                    if (result.success) {
                        $('div.call_tag_modal').modal('hide');
                        toastr.success(result.msg);
                        form[0].reset();
                        call_tag_table.ajax.reload();
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

        // Edit form loading and submission
        $(document).on('click', '.tag_edit', function() {
            $('div.call_tag_modal').load($(this).data('href'), function() {
                $(this).modal('show');

                $('form#call_tag_update_form').submit(function(e) {
                    e.preventDefault();
                    var form = $(this);
                    var data = form.serialize();
                    var submitBtn = form.find('button[type="submit"]');
                    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> {{ __("messages.saving") }}');

                    $.ajax({
                        method: 'POST',
                        url: form.attr('action'),
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success) {
                                $('div.call_tag_modal').modal('hide');
                                toastr.success(result.msg);
                                call_tag_table.ajax.reload();
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

        // Delete action
        $(document).on('click', '.tag_delete', function() {
            swal({
                title: LANG.sure,
                text: 'This tag will be deleted.',
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(function(willDelete) {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success) {
                                toastr.success(result.msg);
                                call_tag_table.ajax.reload();
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
