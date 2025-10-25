@extends('clinic::layouts.app2')
@section('title', 'Source')
@section('content')
@include('crm::layouts.nav')

<section class="content">
    @component('components.widget', ['class' => 'box-solid'])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-primary btn-modal pull-right add_reference_button">
                    <i class="fa fa-plus"></i> @lang('messages.add')
                </button>          
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="reference_table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Details</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent

    <div class="modal fade category_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    <div class="modal fade rate_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
</section>
@endsection

@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        // Initialize DataTable
        var referenceTable = $('#reference_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route("survey-sources.index") }}',
                type: 'GET',
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'details', name: 'details' },
                { data: 'created_at', name: 'created_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[2, 'desc']],
            error: function(xhr, error, thrown) {
                console.log('DataTables error:', xhr, error, thrown);
            }
        });

        // Create Reference Modal
        $(document).on('click', '.add_reference_button', function() {
            $.ajax({
                url: '{{ route("survey-references.create") }}',
                type: 'GET',
                success: function(response) {
                    $('.category_modal').html(response).modal('show');
                    // Initialize Select2 for parent dropdown
                    $('#parent_id').select2({
                        dropdownParent: $('.category_modal')
                    });
                },
                error: function(xhr) {
                    toastr.error('Failed to load create form.');
                    console.log('Create modal error:', xhr);
                }
            });
        });

        // Store Reference
        $(document).on('submit', '#add_reference_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            submitBtn.html('<i class="fa fa-circle-o-notch fa-spin"></i> Processing...').prop('disabled', true);

            $.ajax({
                url: '{{ route("survey-references.store") }}',
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('.category_modal').modal('hide');
                        referenceTable.ajax.reload();
                        toastr.success(response.msg);
                    }
                },
                error: function(xhr) {
                    console.log('Store error:', xhr);
                    var errors = xhr.responseJSON?.errors || {};
                    $('.help-block.text-danger').hide().text('');
                    $.each(errors, function(key, value) {
                        $('#' + key + '_error').text(value[0]).show();
                    });
                    if (!Object.keys(errors).length) {
                        toastr.error(xhr.responseJSON?.msg || '@lang("messages.something_went_wrong")');
                    }
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Edit Reference Modal
        $(document).on('click', '.edit_reference_button', function() {
            var url = $(this).data('href');
            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    $('.category_modal').html(response).modal('show');
                    // Initialize Select2 for parent dropdown
                    $('#edit_parent_id').select2({
                        dropdownParent: $('.category_modal')
                    });
                },
                error: function(xhr) {
                    toastr.error('Failed to load edit form.');
                    console.log('Edit modal error:', xhr);
                }
            });
        });

        // Update Reference
        $(document).on('submit', '#edit_reference_form', function(e) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action');
            var submitBtn = form.find('button[type="submit"]');
            var originalText = submitBtn.html();
            submitBtn.html('<i class="fa fa-circle-o-notch fa-spin"></i> Processing...').prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: form.serialize(),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        $('.category_modal').modal('hide');
                        referenceTable.ajax.reload();
                        toastr.success(response.msg);
                    }
                },
                error: function(xhr) {
                    console.log('Update error:', xhr);
                    var errors = xhr.responseJSON?.errors || {};
                    $('.help-block.text-danger').hide().text('');
                    $.each(errors, function(key, value) {
                        $('#edit_' + key + '_error').text(value[0]).show();
                    });
                    if (!Object.keys(errors).length) {
                        toastr.error(xhr.responseJSON?.msg || '@lang("messages.something_went_wrong")');
                    }
                },
                complete: function() {
                    submitBtn.html(originalText).prop('disabled', false);
                }
            });
        });

        // Delete Reference
        $(document).on('click', '.delete_reference_button', function() {
            var url = $(this).data('href');
            swal({
                title: '@lang("messages.sure")',
                text: '@lang("messages.delete_confirmation")',
                icon: "warning",
                buttons: true,
                dangerMode: true,
            }).then((willDelete) => {
                if (willDelete) {
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                referenceTable.ajax.reload();
                                toastr.success(response.msg);
                            }
                        },
                        error: function(xhr) {
                            toastr.error(xhr.responseJSON?.msg || '@lang("messages.something_went_wrong")');
                            console.log('Delete error:', xhr);
                        }
                    });
                }
            });
        });
    });
</script>
@endsection