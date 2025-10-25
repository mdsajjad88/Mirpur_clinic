@extends('layouts.app')

@section('title', __('Product Waitlists'))

@section('content')
    <section class="content-header">
        <h1>@lang('Product Waitlists')
            <small>@lang('Manage your product waitlists')</small>
        </h1>
    </section>

    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('demand_list_filter_status',  __('lang_v1.status') . ':') !!}
                {!! Form::select('demand_list_filter_status', ['Available'=> 'Available', 'Pending' => 'Pending'] , null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('demand_list_filter_call_status',  __('lang_v1.call_status') . ':') !!}
                {!! Form::select('demand_list_filter_call_status', $call_statuses, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('demand_list_filter_sms_status',  __('lang_v1.sms_status') . ':') !!}
                {!! Form::select('demand_list_filter_sms_status', ['sent'=> 'Sent', 'not_send' => 'Not Send', 'failed' => 'Failed'] , null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('demand_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('demand_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('demand_list_filter_reference',  __('lang_v1.reference') . ':') !!}
                {!! Form::select('demand_list_filter_reference', ['store'=> 'In Store', 'e_commerce' => 'E-Commerce'] , null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('lang_v1.all')]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('added_by',  __('lang_v1.added_by') . ':') !!}
                {!! Form::select('added_by', $created_by, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
            </div>
        </div>

    @endcomponent
        <div class="row">
            <div class="col-md-12">
               <!-- Custom Tabs -->
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#product_wait_list_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes" aria-hidden="true"></i> Product Wait List</a>
                        </li>
                        <li>
                            <a href="#product_archive_tab" data-toggle="tab" aria-expanded="true"><i class="fas fa-file-archive"></i> Archive</a>
                        </li>
                    </ul>
    
                    <div class="tab-content">
                        <div class="tab-pane active" id="product_wait_list_tab">
                                @can('direct_sell.access')
                                    <a class="btn btn-primary pull-right" href="{{action([\App\Http\Controllers\SellController::class, 'create'])}}">
                                    <i class="fa fa-plus"></i> @lang('messages.add')</a> <br><br>
                                @endcan
                            @include('product_waitlists.partials.waitlist')
                        </div>
                        <div class="tab-pane" id="product_archive_tab">
                            @include('product_waitlists.partials.archive')
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade edit_call_status_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>
    </section>
@endsection

@section('javascript')
    <script>
        $(document).ready(function() {
            $('#demand_list_filter_date_range').daterangepicker(
                dateRangeSettings,
                function (start, end) {
                    $('#demand_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
                    product_waitlist_table.ajax.reload();
                }
            );
            $('#demand_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
                $('#demand_list_filter_date_range').val('');
                product_waitlist_table.ajax.reload();
            });

            var product_waitlist_table = $('#product_waitlist_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/waitlists',
                    data: function(d) {
                        if($('#demand_list_filter_date_range').val()) {
                            var start = $('#demand_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                            var end = $('#demand_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                            d.start_date = start;
                            d.end_date = end;
                        }
                        d.status = $('#demand_list_filter_status').val();
                        d.call_status = $('#demand_list_filter_call_status').val();
                        d.sms_status = $('#demand_list_filter_sms_status').val();
                        d.reference = $('#demand_list_filter_reference').val();
                        d.added_by = $('#added_by').val();
                    }
                },
                columns: [
                    { data: 'send_sms', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'created_at', name: 'product_waitlists.created_at', orderable: true, searchable: false},
                    { data: 'product_name', name: 'products.name', orderable: true, searchable: true },
                    { data: 'product_sku', name: 'products.sku', orderable: true, searchable: true },
                    { data: 'customer', name: 'c.name', orderable: true, searchable: true },
                    { data: 'customer_phone_number', name: 'c.mobile', searchable: true },
                    { data: 'quantity_requested', name: 'product_waitlists.quantity_requested', orderable: true, searchable: false },
                    { data: 'status', name: 'product_waitlists.status', orderable: true, searchable: false },
                    { data: 'call_status', name: 'product_waitlists.call_status', orderable: true, searchable: false },
                    { data: 'notes', name: 'product_waitlists.notes', orderable: true },
                    { data: 'added_by', name: 'added_by' },
                    // { data: 'location_name', name: 'location_name', searchable: false },
                    { data: 'reference', name: 'reference', orderable: true },
                    { data: 'restock_date', name: 'restock_date', orderable: true },
                    { data: 'notification_sent_date', name: 'notification_sent_date', orderable: true },
                    { data: 'sms_status', name: 'product_waitlists.sms_status', orderable: true }
                ],
                columnDefs: [
                    {
                        targets: [6], // Index of the mobile number column
                        visible: false // Hides the column but keeps it searchable
                    },
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[5, "desc"]]
            });

            $(document).on('change', '#demand_list_filter_status, #demand_list_filter_call_status, #demand_list_filter_sms_status, #demand_list_filter_reference, #added_by',  function() {
                product_waitlist_table.ajax.reload();
            });

            var archive_product_waitlist_table = $('#archive_product_waitlist_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/waitlists/archive',
                    data: function(d) {
                        // Add any filters here
                    }
                },
                columns: [
                    { data: 'action', name: 'action', orderable: false, searchable: false },
                    { data: 'created_at', name: 'product_waitlists.created_at', orderable: true, searchable: false },
                    { data: 'product_name', name: 'products.name', orderable: true, searchable: true },
                    { data: 'product_sku', name: 'products.sku', orderable: true, searchable: true },
                    { data: 'customer', name: 'c.name', orderable: true, searchable: true },
                    { data: 'quantity_requested', name: 'product_waitlists.quantity_requested', orderable: true, searchable: false },
                    { data: 'status', name: 'product_waitlists.status', orderable: true, searchable: false },
                    { data: 'call_status', name: 'product_waitlists.call_status', orderable: true, searchable: false },
                    { data: 'notes', name: 'product_waitlists.notes', orderable: true },
                    { data: 'added_by', name: 'added_by' },
                    // { data: 'location_name', name: 'location_name', searchable: false },
                    { data: 'reference', name: 'reference', orderable: true },
                    { data: 'restock_date', name: 'restock_date', orderable: true },
                    { data: 'notification_sent_date', name: 'notification_sent_date', orderable: true },
                    { data: 'sms_status', name: 'product_waitlists.sms_status', orderable: true }
                ],
                columnDefs: [
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            $(document).on('click', '.delete-product-waitlist', function(e) {
                e.preventDefault();
                var url = $(this).data('href');  // Use data-href instead of href

                swal({
                    title: LANG.sure,
                    text: 'Are you sure you want to delete this?',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'  // Ensure CSRF token is sent
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.msg);
                                    product_waitlist_table.ajax.reload();
                                } else {
                                    toastr.error(response.msg);
                                }
                            },
                            error: function(xhr) {
                                swal('Error!', 'Something went wrong!', 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.edit-call-status', function(e) {
                e.preventDefault();
                var href = $(this).data('href');
                $('.edit_call_status_modal').load(href, function () {
                    $(this).modal('show');
                    $('#callStatusForm').attr('action', '{{ url("/waitlists/update-call-status") }}');
                });
            });

            $(document).on('submit', '#callStatusForm', function(e) {
                e.preventDefault();
                var form = $(this);
                var formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        $('.edit_call_status_modal').modal('hide');
                        if (response.success === true) {
                        toastr.success(response.msg);
                        }
                        else{
                            toastr.success(response.success);
                        }
                        product_waitlist_table.ajax.reload(null, false);
                        archive_product_waitlist_table.ajax.reload(null, false);
                    },
                    error: function(xhr) {
                        var errors = xhr.responseJSON.errors;
                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                toastr.error(errors[key][0]);
                            }
                        }
                    }
                });
            });

            $(document).on('click', '.force-delete-product-waitlist', function(e) {
                e.preventDefault();
                var url = $(this).data('href'); 

                swal({
                    title: LANG.sure,
                    text: 'This action will permanently delete the waitlist and cannot be undone!',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')  // CSRF token from meta tag
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.msg);
                                    archive_product_waitlist_table.ajax.reload();
                                } else {
                                    toastr.error(response.msg);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Something went wrong!', 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.both-delete-product-waitlist', function(e) {
                e.preventDefault();
                var url = $(this).data('href'); 

                swal({
                    title: LANG.sure,
                    text: 'This action will permanently delete the waitlist and cannot be undone!',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.msg);
                                    product_waitlist_table.ajax.reload();
                                } else {
                                    toastr.error(response.msg);
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Something went wrong!', 'error');
                            }
                        });
                    }
                });
            });

            $('#send-sms-selected').on('click', function(e) {
                e.preventDefault();

                var selectedRows = product_waitlist_table.$('.row-select:checked');
                if (selectedRows.length === 0) {
                    swal({
                        title: "No items selected",
                        text: "Please select at least one waitlist to send SMS.",
                        icon: "warning",
                    });
                    return;
                }

                var selectedIds = [];
                selectedRows.each(function() {
                    selectedIds.push($(this).val());
                });

                swal({
                    title: "Are you sure?",
                    text: "This action will send SMS notifications to the selected customers.",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willSend) => {
                    if (willSend) {
                        $('#selected_rows').val(selectedIds);
                        $('#sms_send_form').submit();
                    }
                });
            });
        });
    </script>
@endsection
