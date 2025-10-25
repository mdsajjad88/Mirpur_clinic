@extends('clinic::layouts.app2')

@section('title', __('Call Campaigns'))
@section('css')
<style>
    .disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
@endsection
@section('content')
    @include('crm::layouts.nav')
    <section class="content no-print">
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-md-12">
                    @slot('tool')
                        @can('crm.create_call_campaign')
                            <a href="{{ action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'create']) }}"
                                class="btn btn-primary pull-right">
                                <i class="fa fa-plus"></i> @lang('Add New Campaign')
                            </a>
                        @endcan
                    @endslot
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="call_campaigns_table">
                    <thead>
                        <tr>
                            <th>@lang('Name')</th>
                            <th>@lang('Survey Type')</th>
                            <th>@lang('Status')</th>
                            <th>@lang('Progress')</th>
                            <th>@lang('Start Date')</th>
                            <th>@lang('End Date')</th>
                            <th>@lang('Target Contacts')</th>
                            <th>@lang('Completed')</th>
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
            var callCampaignsTable = $('#call_campaigns_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'index']) }}",
                    data: function(d) {
                        // Add any additional filters here
                    }
                },
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'survey_type.name',
                        name: 'surveyType.name'
                    },
                    {
                        data: 'status',
                        name: 'status'
                    },
                    {
                        data: 'progress',
                        name: 'progress',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'start_date',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date',
                        name: 'end_date'
                    },
                    {
                        data: 'target_count',
                        name: 'target_count'
                    },
                    {
                        data: 'completed_count',
                        name: 'completed_count'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });



            $(document).on('click', '.delete_campaign', function(e) {
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
                                    $("#call_campaigns_table").DataTable().ajax
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
            $(document).on('click', '.marge_contact', function() {
    $(this).addClass('disabled').css('pointer-events', 'none');
});

        });
    </script>
@endsection
