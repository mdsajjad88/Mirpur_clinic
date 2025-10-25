@extends('clinic::layouts.app2')

@section('title', __('Contact Filtrer'))

@section('content')
@include('crm::layouts.nav')
    <section class="content no-print">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    @slot('tool')
                    @can('crm.create_call_campaign')
                        <a href="{{ action([\Modules\Crm\Http\Controllers\ContactFilterController::class, 'create']) }}" 
                           class="btn btn-primary pull-right">
                            <i class="fa fa-plus"></i> @lang('Contact Filtrer Add')
                        </a>
                    @endcan
                    @endslot
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="contact_filter_table">
                    <thead>
                        <tr>
                            <th>@lang('Name')</th>
                            <th>@lang('Progress')</th>
                            <th>@lang('Target Contacts')</th>
                            <th>@lang('Action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            var callCampaignsTable = $('#contact_filter_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ action([\Modules\Crm\Http\Controllers\ContactFilterController::class, 'index']) }}",
                    data: function(d) {
                        // Add any additional filters here
                    }
                },
                columns: [
                    { data: 'name', name: 'name' },
                    { data: 'progress', name: 'progress', orderable: false, searchable: false },
                    { data: 'target_count', name: 'target_count' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });

            // Delete campaign
            $(document).on('click', '.delete_campaign_filter', function(e) {
                e.preventDefault();
                var deleteUrl = $(this).data('href'); // use data-href, not href

                swal({
                    title: LANG.sure,
                    text: LANG.confirm_delete_campaign ??
                        "Are you sure you want to delete this campaign?",
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then(willDelete => {
                    if (willDelete) {
                        $.ajax({
                            method: 'DELETE',
                            url: deleteUrl,
                            dataType: 'json',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr(
                                    'content') // Include CSRF
                            },
                            success: function(result) {
                                if (result.success) {
                                    toastr.success(result.msg);
                                    $("#contact_filter_table").DataTable().ajax
                                .reload();
                                } else {
                                    toastr.error(result.msg);
                                }
                            },
                            error: function(xhr) {
                                toastr.error('Something went wrong!');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection