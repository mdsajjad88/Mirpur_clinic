@extends('layouts.app')

@section('title', __('Product Stock Audit'))

@section('css')
<style>
    @media print {
        .print-font {
            font-size: 10px !important;
        }
        .print-exclude {
            display: none !important;
        }
    }
    .break-after-6 {
            word-break: break-all;
            width: 8ch; /* 6 characters width */
        }
</style>
@endsection

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>@lang('Product Stock Audit')</h1>
    </section>

    <!-- Main content -->
    <section class="content no-print">
        @component('components.filters', ['title' => __('report.filters')])
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        {!! Form::label('random_check_filter_location_id', __('purchase.business_location') . ':') !!}
                        {!! Form::select('random_check_filter_location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="random_check_filter_physical_count">@lang('Physical Count:')</label>
                        <select id="random_check_filter_physical_count" class="form-control select2" style="width: 100%;">
                            <option value="">@lang('All')</option>
                            <option value="surplus">@lang('Surplus')</option>
                            <option value="match">@lang('Match')</option>
                            <option value="missing">@lang('Missing')</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label for="category_name">@lang('Category:')</label>
                        <select id="category_name" class="form-control select2" multiple="multiple" style="width: 100%;">
                            @foreach ($categories as $value)
                                <option value="{{ $value }}">{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        {!! Form::label('random_check_table_filter_date_range', __('report.date_range') . ':') !!}
                        {!! Form::text('random_check_table_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']) !!}
                    </div>
                </div>
            </div>
        @endcomponent
        <div class="row">
            <div class="col-md-12">
                <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#random_check_tab" data-toggle="tab"><i class="fa fa-random"></i> @lang('Random Checks')</a></li>
                        @if($enable_reconciliation == 1)
                        <li><a href="#reconciliation_tab" data-toggle="tab"><i class="fas fa-stream"></i> @lang('Reconciliation')</a></li>
                        @endif
                        <li><a href="#random_check_details_tab" data-toggle="tab"><i class="fa fa-list"></i> @lang('Random Check Details')</a></li>
                        <li><a href="#report_tab" data-toggle="tab"><i class="fas fa-file-invoice"></i> @lang('Reports')</a></li>
                        <li><a href="#archive_tab" data-toggle="tab"><i class="fas fa-archive"></i> @lang('Archive')</a></li>
                    </ul>

                    <div class="tab-content">
                        <!-- Random Check Tab -->
                        <div class="tab-pane active" id="random_check_tab">
                            <button type="button" class="btn btn-primary pull-right btn-modal" 
                                    data-href="{{ action([\App\Http\Controllers\CheckController::class, 'createRandomCheck']) }}" 
                                    data-container=".random_check_modal">
                                <i class="fa fa-random"></i> @lang('Check')
                            </button>
                            <br><br>
                            @include('random_check.partials.random_check_table')
                        </div>
                        
                        <!-- Reconciliation Tab -->
                        @if($enable_reconciliation == 1)
                        <div class="tab-pane" id="reconciliation_tab">
                            <a class="btn btn-primary pull-right" 
                               href="{{ action([\App\Http\Controllers\CheckController::class, 'reconciliationCheck']) }}">
                                <i class="fa fa-random"></i> @lang('Check')
                            </a>
                            <br><br>
                            @include('random_check.partials.reconciliation_table')
                        </div>
                        @endif
                        
                        <!-- Random Check Details Tab -->
                        <div class="tab-pane" id="random_check_details_tab">
                            @include('random_check.partials.random_check_details_table')
                        </div>
                        
                        <!-- Reports Tab -->
                        <div class="tab-pane" id="report_tab">
                            <a class="btn btn-primary pull-right" href="{{ url('random/check-report') }}">
                                <i class="fa fa-plus"></i> @lang('messages.add')
                            </a>
                            <br><br>
                            @include('random_check.partials.report_table')
                        </div>
                        
                        <!-- Archive Tab -->
                        <div class="tab-pane" id="archive_tab">
                            @include('random_check.partials.archive_table')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modals -->
        <div class="modal fade random_check_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade random_check_edit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade view_random_check_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
        <div class="modal fade report_item_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel" aria-hidden="true"></div>
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script>
$(document).ready(function(){
    $('#random_check_table_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#random_check_table_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            random_check_table.ajax.reload();
            random_check_details_table.ajax.reload();
            random_check_archive_table.ajax.reload();
            reports_table.ajax.reload();
            reconciliation_table.ajax.reload();
        }
    );
    $('#random_check_table_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#random_check_table_filter_date_range').val('');
        random_check_table.ajax.reload();
        random_check_details_table.ajax.reload();
        random_check_archive_table.ajax.reload();
        reports_table.ajax.reload();
        reconciliation_table.ajax.reload();
    });

    var random_check_table = $('#random_check_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/random/random-check-index',
            data: function (d) {
                if ($('#random_check_table_filter_date_range').val()) {
                    var start = $('#random_check_table_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#random_check_table_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                if ($('#random_check_filter_location_id').length) {
                    d.location_id = $('#random_check_filter_location_id').val();
                }
                if ($('#random_check_filter_physical_count').length) {
                    d.physical_count_filter = $('#random_check_filter_physical_count').val();
                }
            }
        },
        columns: [
            { data: 'check_no', name: 'check_no', searchable: true},
            { data: 'created_at', name: 'created_at', searchable: false},
            { data: 'checked_by', name: 'checked_by', searchable: false },
            { data: 'location_name', name: 'location_name', searchable: false},
            { data: 'total_product_count', name: 'total_product_count', searchable: false },
            { data: 'total_physical_count', name: 'total_physical_count', searchable: false },
            { data: 'random_check_comment', name: 'random_check_comment', searchable: false },
            { data: 'action', name: 'action', searchable: false }
        ],
        order: [[0, 'desc']], // Default sorting by date descending
    });

    var reconciliation_table = $('#reconciliation').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/random/reconciliation',
            data: function (d) {
                if ($('#random_check_table_filter_date_range').val()) {
                    var start = $('#random_check_table_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#random_check_table_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                if ($('#random_check_filter_location_id').length) {
                    d.location_id = $('#random_check_filter_location_id').val();
                }
                if ($('#random_check_filter_physical_count').length) {
                    d.physical_count_filter = $('#random_check_filter_physical_count').val();
                }
            }
        },
        columns: [
            { data: 'check_no', name: 'check_no', searchable: true },
            { data: 'created_at', name: 'created_at', searchable: false },
            { data: 'checked_by', name: 'checked_by', searchable: false },
            { data: 'location_name', name: 'location_name', searchable: false },
            { data: 'total_product_count', name: 'total_product_count', searchable: false },
            { data: 'total_physical_count', name: 'total_physical_count', searchable: false },
            { data: 'random_check_comment', name: 'random_check_comment', searchable: false },
            { data: 'action', name: 'action', searchable: false }
        ],
        order: [[0, 'desc']], // Default sorting by date descending
    });



    var random_check_archive_table = $('#random_check_archive_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/random/archived-random-check',
            data: function (d) {
                if ($('#random_check_table_filter_date_range').val()) {
                    var start = $('#random_check_table_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#random_check_table_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                if ($('#random_check_filter_location_id').length) {
                    d.location_id = $('#random_check_filter_location_id').val();
                }
                if ($('#random_check_filter_physical_count').length) {
                    d.physical_count_filter = $('#random_check_filter_physical_count').val();
                }
            },
            error: function (xhr, error, code) {
                console.log('Error:', error);
                console.log('Code:', code);
                console.log('Response:', xhr.responseText);  // Logs server response for debugging
            }
        },
        columns: [
            { data: 'check_no', name: 'check_no' },
            { data: 'created_at', name: 'created_at', searchable: false},
            { data: 'checked_by', name: 'checked_by', searchable: false },
            { data: 'location_name', name: 'location_name', searchable: false },
            { data: 'total_product_count', name: 'total_product_count', searchable: false },
            { data: 'total_physical_count', name: 'total_physical_count', searchable: false },
            { data: 'random_check_comment', name: 'random_check_comment', searchable: false },
            { data: 'action', name: 'action', searchable: false }
        ],
        order: [[0, 'desc']]
    });


    var random_check_details_table = $('#random_check_details_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/random/random-check-details',
            data: function (d) {
                if ($('#random_check_table_filter_date_range').val()) {
                    var start = $('#random_check_table_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#random_check_table_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                if ($('#random_check_filter_location_id').length) {
                    d.location_id = $('#random_check_filter_location_id').val();
                }
                if ($('#random_check_filter_physical_count').length) {
                    d.physical_count_filter = $('#random_check_filter_physical_count').val();
                }
                if ($('#category_name').length) {
                    d.category_name = $('#category_name').val();
                }
            }
        },
        columns: [
            { data: 'check_no', name: 'check_no', searchable: false},
            { data: 'category_name', name: 'category_name', searchable: false },
            { data: 'product_name', name: 'product_name', searchable: true },
            { data: 'sku', name: 'sku', searchable: true },
            { data: 'brand_name', name: 'brand_name', searchable: false },
            { data: 'current_stock', name: 'current_stock', searchable: false },
            { data: 'physical_count', name: 'physical_count', searchable: false },
            { data: 'comment', name: 'comment', searchable: false },
            { data: 'created_at', name: 'created_at', searchable: false },
            { data: 'action', name: 'action', searchable: false }
        ],
        columnDefs: [
            {
                targets: [8], // Indices of created_at and updated_at
                render: function (data, type, row) {
                    return type === 'display' ? moment(data).format('D MMM YYYY, h:mm A') : data;
                }
            }
        ]
    });

    var reports_table = $('#reports_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/random/check-report-index',
            data: function (d) {
                if ($('#report_table_filter_date_range').val()) {
                    var start = $('#report_table_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#report_table_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                if ($('#report_filter_location_id').length) {
                    d.location_id = $('#report_filter_location_id').val();
                }
            }
        },
        columns: [
            { data: 'report_no', name: 'report_no', searchable: true },
            { data: 'date', name: 'date', searchable: false },
            { data: 'finalized_by', name: 'finalized_by', searchable: false },
            { data: 'location_name', name: 'location_name', searchable: false },
            { data: 'date_range_covered', name: 'date_range_covered', searchable: false },
            { data: 'number_of_checks_covered', name: 'number_of_checks_covered', searchable: false },
            { data: 'net_result', name: 'net_result', searchable: false },
            { data: 'status', name: 'status', searchable: false },
            { data: 'comments', name: 'comments' }
        ],
        order: [[0, 'desc']]
    });


    $(document).on('change', '#random_check_filter_location_id, #random_check_filter_physical_count, #category_name', function () {
        random_check_table.ajax.reload();
        random_check_details_table.ajax.reload();
        random_check_archive_table.ajax.reload();
        reports_table.ajax.reload();
        reconciliation_table.ajax.reload();
    });

    // Initialize Product Random Check Modal
    $(document).on('click', 'button.btn-modal', function () {
        var container = $(this).data('container');
        var href = $(this).data('href');
        $(container).load(href, function () {
            $(this).modal('show');
        });
    });

    $(document).on('click', '.edit-random-check', function (e) {
        e.preventDefault();
        var href = $(this).data('href');
        $('.random_check_edit_modal').load(href, function () {
            $(this).modal('show');
        });
    });

    $(document).on('click', '.view_random_check', function (e) {
        e.preventDefault();
        var href = $(this).data('href');
        $('.view_random_check_modal').load(href, function () {
            $(this).modal('show');
        });
    });

    $(document).on('click', '.view_report_item', function (e) {
        e.preventDefault();
        var href = $(this).data('href');
        
        $('.report_item_modal').load(href, function () {
            $(this).modal('show');
        });
    });


    // Delete random check
    $(document).on('click', '.delete-random-check', function(e) {
        e.preventDefault();
        var url = $(this).data('href');
        
        // SweetAlert confirmation
        swal({
            title: "Are you sure?",
            text: "You will not be able to recover this random check until it is restored from the archive!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.success);
                            random_check_table.ajax.reload();
                            random_check_details_table.ajax.reload();
                            random_check_archive_table.ajax.reload();
                            reconciliation_table.ajax.reload();
                            reports_table.ajax.reload();
                        } else {
                            toastr.error(response.error);
                        }
                    },
                    error: function(response) {
                        toastr.error('Failed to delete random check. Please try again.');
                    }
                });
            }
        });
    });


    $(document).on('click', '.delete-permanent-check', function(e) {
    e.preventDefault();
    var url = $(this).data('href');
    
    // SweetAlert confirmation
    swal({
        title: "Are you sure?",
        text: "This action will permanently delete the random check and cannot be undone!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willDelete) => {
        if (willDelete) {
            $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.success);
                        random_check_table.ajax.reload();
                        random_check_details_table.ajax.reload();
                        random_check_archive_table.ajax.reload();
                        reconciliation_table.ajax.reload();
                        reports_table.ajax.reload();
                    } else {
                        toastr.error(response.error);
                    }
                },
                error: function(response) {
                    toastr.error('Failed to permanently delete random check. Please try again.');
                }
            });
        }
    });
});


$(document).on('click', '.restore-check', function(e) {
    e.preventDefault();
    var url = $(this).data('href');

    // SweetAlert confirmation
    swal({
        title: "Are you sure?",
        text: "This action will restore the random check from the archive!",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    }).then((willRestore) => {
        if (willRestore) {
            $.ajax({
                url: url,
                type: 'POST', // Use POST for restoring data
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.success);
                        random_check_table.ajax.reload();
                        random_check_details_table.ajax.reload();
                        random_check_archive_table.ajax.reload();
                        reconciliation_table.ajax.reload();
                        reports_table.ajax.reload();
                    } else if (response.error) {
                        toastr.error(response.error);
                    }
                },
                error: function(xhr, status, error) {
                    toastr.error('Failed to restore random check. Please try again.');
                }
            });
        }
    });
});


});
    </script>
@endsection