@extends('clinic::layouts.app2')
@section('title', 'Dosage')

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Dosage
            <small>Manage your Dosage</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => 'All Your Dosage'])
            @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-block btn-primary btn-modal"
                        data-href="{{ action([\Modules\Clinic\Http\Controllers\DosageController::class, 'create']) }}"
                        data-container=".add_dosage_view">
                        <i class="fa fa-plus"></i> @lang('messages.add')
                    </button>
                </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="clinic_dosage_table">
                    <thead>
                        <tr>
                            <th>Dosage</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

        <div class="modal fade add_dosage_view" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var diseaseTable = $('#clinic_dosage_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('medicine-dosage.index') }}",
                },
                columns: [
                    
                    {
                        data: 'value',
                        name: 'value'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    }
                ] 
            });

            $(document).on('click', 'button.edit_dosage_button', function() {
                $('div.add_dosage_view').load($(this).data('href'), function() {
                    $(this).modal('show');

                    $('form#dosage_update_form').submit(function(e) {
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
                                    $('div.add_dosage_view').modal('hide');
                                    toastr.success(result.msg);
                                    diseaseTable.ajax.reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                        });
                    });
                });
            });

            $(document).on('click', 'button.delete_dosage_button', function() {
                swal({
                    title: LANG.sure,
                    text: 'Are you sure want to delete this dosage?',
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
            
        })
    </script>
@endsection
