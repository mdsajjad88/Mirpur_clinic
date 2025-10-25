@extends('clinic::layouts.app2')
@section('title', __('Subscribe Patients'))
@section('content')
    <div class="container-fluid">
        <div class="row">
            @component('components.filters', ['title' => 'Filters'])
                    <div class="col-md-4">
                        <div class="form-group">
                        {!! Form::label('subscription', 'Subscription') !!}
                        {!! Form::select('subscription_id', $subscriptions, 1, ['class' => 'form-control select2', 'id' => 'subscription_id', 'style' => 'width: 100%;']) !!}
                    </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                        {!! Form::label('running_session', 'Running/closed Subscription') !!}  <br>
                        {!! Form::select('running_session', ['' => 'All', 0 => 'Running', 1 => 'Closed'], 1, ['class' => 'form-control select2', 'id' => 'running_session', 'style' => 'width: 100%;']) !!}
                    </div>
                    </div>
                    
                @endcomponent
        </div>
        <div class="row">
            <div class="col">
                
                @component('components.widget', ['class' => 'box-primary', 'title' => 'Subscribe Patient List'])
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="subcription_details_table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Session Name</th>
                                    <th>Session Amount</th>
                                    <th>Patient Name</th>
                                    <th>Mobile</th>
                                    <th>Contact ID</th>
                                    <th>Start Date</th>
                                    <th>End Date</th>
                                    <th>Visited</th>
                                    <th>Remaining Visits</th>
                                    <th>Total Visit</th>
                                    <th>Is Closed</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                @endcomponent

            </div>
        </div>
    </div>
    <div class="modal fade patient_subs_info_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade edit_end_date_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var sessionDetailsTable = $('#subcription_details_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "/subs-patients",
                    data: function(d) {
                        d.subscription_id = $('select[name=subscription_id]').val();
                        d.running_session = $('select[name=running_session]').val();
                    }
                },
                aaSorting: [
                    [6, 'desc'],
                ],
                columns: [{
                        data: 'action',
                        name: 'action',
                    },
                    {
                        data: 'session_name',
                        name: 'session_name'
                    },
                    {
                        data: 'session_amount',
                        name: 'session_amount'
                    },
                    {
                        data: 'patient_name',
                        name: 'patient_name'
                    },
                    {
                        data: 'mobile',
                        name: 'contacts.mobile'
                    },
                    {
                        data: 'contact_id',
                        name: 'contacts.contact_id'
                    },
                    {
                        data: 'start_date',
                        name: 'patient_session_info.start_date',
                    },
                    {
                        data: 'end_date',
                        name: 'patient_session_info.end_date',
                    },

                    {
                        data: 'visited_count',
                        name: 'visited_count'
                    },
                    {
                        data: 'remaining_visit',
                        name: 'remaining_visit'
                    },
                    {
                        data: 'total_visit',
                        name: 'total_visit',
                    },
                    {
                        data: 'is_closed',
                        name: 'is_closed'
                    },
                ],
            });

            $(document).on('click', '.show_subs_info', function(e) {
                e.preventDefault();
                $('div.patient_subs_info_modal').load($(this).attr('href'), function() {
                    $(this).modal('show');
                    $('#session_details_info_table').DataTable({
                        paging: true,
                        searching: false,
                        info: true,
                        lengthChange: false,
                        pageLength: 15,
                        dom: 'Bfrtip',
                    });
                });

            });
            $(document).on('change', '#subscription_id, #running_session', function(e) {
                console.log($(this).val());
                console.log('table reloading');
                sessionDetailsTable.ajax.reload();
            })
            // $(document).on('click', '.show_subs_info', function(e) {
            //     e.preventDefault();
            //     var href = $(this).data('href');
            //     $('.patient_subs_info_modal').load(href, function() {
            //         $(this).modal('show');

            //     });
            // });
        });
    </script>
@endsection
