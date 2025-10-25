@extends('layouts.app')
@section('title', __('Reconcile-Details'))
@section('content')
    
<section class="content">
    @component('components.widget', [
        'class' => 'box-primary',
        'title' => __('Reconcile Details List'),
    ])
        @slot('tool')
                <div class="box-tools">
                    <button type="button" class="btn btn-primary btn-modal"
            data-href="{{ action([\App\Http\Controllers\ReconcileDetailsController::class, 'create']) }}"
            data-container=".reconcile_details_add_modal">
            <i class="fa fa-plus"></i> @lang('messages.add')</button>
                </div>
            @endslot
        <table class="table table-bordered table-striped" id="reconcile_details_table" style="width:100%">
            <thead>
                <tr>
                    <th>Action</th>
                    <th>Reconcile Name</th>
                    <th>Name</th>
                    <th>sku</th>
                    <th>physical_qty</th>
                    <th>software_qty</th>
                    <th>difference</th>
                    <th>difference_percentage</th>
                    <th>Created by</th>
                    <th>Updated by</th>
                </tr>
            </thead>
        </table>
    @endcomponent
    <div class="modal fade reconcile_details_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade edit_reconcile_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div> 
    {{-- <div class="modal fade view_doctor_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div> --}}
</section>

@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var reconcile_details_table = $('#reconcile_details_table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                ajax: {
                    url: "{{ route('reconcile-details.index') }}",
                },
                columns: [{
                        data: 'action',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'reconcile',
                        name: 'reconcile'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    
                    {
                        data: 'sku',
                        name: 'sku'
                    },
                    
                    {
                        data: 'physical_qty',
                        name: 'physical_qty'
                    },
                    
                    {
                        data: 'software_qty',
                        name: 'software_qty'
                    },
                    
                    {
                        data: 'difference',
                        name: 'difference'
                    },
                    
                    {
                        data: 'difference_percentage',
                        name: 'difference_percentage'
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

            reconcile_details_table.ajax.reload();
        });

        $(document).on('click', '.delete_reconcile_details_button', function(e) {
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
                                $('#reconcile_details_table').DataTable().ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
    
        $(document).on('click', '.edit_reconcile_details_button', function(e) {
        e.preventDefault();
        $('div.edit_reconcile_details_modal').load($(this).attr('href'), function() {
            $(this).modal('show');
        });
    });
    
    </script>
@endsection
