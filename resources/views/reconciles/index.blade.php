@extends('layouts.app')
@section('title', __('Reconciles'))
@section('content')
    <section class="content">
        @component('components.widget', [
            'class' => 'box-primary',
            'title' => __('Reconcile List'),
        ])
            @slot('tool')
                    <div class="box-tools">
                        
                        <button type="button" class="btn btn-primary btn-modal"
                data-href="{{ action([\App\Http\Controllers\ReconcileController::class, 'create']) }}"
                data-container=".reconcile_add_modal">
                <i class="fa fa-plus"></i> @lang('messages.add')</button>
                    </div>
                @endslot
            <table class="table table-bordered table-striped ajax_view" id="reconcile_table" style="width:100%">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Name</th>
                        <th>Date</th>
                        <th>Created by</th>
                        <th>Updated by</th>
                  </tr>
                </thead>
            </table>
        @endcomponent
        <div class="modal fade reconcile_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
       <div class="modal fade edit_reconcile_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div> 
        <div class="modal fade view_reconcile_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
    </section>

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var reconcile_table = $('#reconcile_table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                ajax: {
                    url: "{{ route('reconciles.index') }}",
                },
                columns: [{
                        data: 'action',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    
                    {
                        data: 'creator',
                        name: 'creator'
                    },
                    
                    {
                        data: 'updater',
                        name: 'updater'
                    },
                ],
            });

            reconcile_table.ajax.reload();
        });

        $(document).on('click', '.delete_reconcile_button', function(e) {
            e.preventDefault();
            swal({
                title: LANG.sure,
                text: "Are you sure you want to delete",
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
                                $('#reconcile_table').DataTable().ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
    
        $(document).on('click', '.edit_reconcile_button', function(e) {
        e.preventDefault();
        $('div.edit_reconcile_modal').load($(this).attr('href'), function() {
            $(this).modal('show');
        });
    });
    $(document).on('click', '.reconcile_add', function(e) {
        alert('alskdlkasdf')
        e.preventDefault();
        $('div.reconcile_add_modal').load($(this).attr('href'), function() {
            $(this).modal('show');
        });
    });
    
    </script>
@endsection
