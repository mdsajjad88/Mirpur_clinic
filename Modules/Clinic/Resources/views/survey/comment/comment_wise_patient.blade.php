@extends('clinic::layouts.app2')
@section('title', 'Feedback Report')
@section('content')
    <style>
        .c_name {
            min-width: 155px;
        }

        .select2-container {
            width: 100% !important;
        }
    </style>
    <div class="container-fluid">

        @component('components.filters', ['title' => 'Filters'])
            <div class="row">
                @php
                    $url = action([
                        \Modules\Clinic\Http\Controllers\Survey\CommentController::class,
                        'commentWisePatientChartFilter',
                    ]);
                @endphp
                {!! Form::open([
                    'url' => $url,
                    'method' => 'GET',
                ]) !!}
                <div class="row">
                    <div class="col-sm-3">
                        {!! Form::label('start_date', 'Start Date') !!}
                        {!! Form::date('start_date',null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-sm-3">
                        {!! Form::label('end_date', 'End Date') !!}
                        {!! Form::date('end_date', null, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-sm-3">
                        {!! Form::label('comment_id', 'Specific Comment') !!}
                        {!! Form::select('comment_id', ['' => 'All'] + $comments->toArray(), null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100% !important;',
                            'id' => 'comment_id',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        {!! Form::label('district_id', 'Specific District') !!}
                        {!! Form::select('district_id', ['' => 'All'] + $districts->toArray(), null, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'id' => 'district_id',
                        ]) !!}
                    </div>
                </div>
                <br>
                {!! Form::submit('Filter For Chart', ['class' => 'btn btn-primary']) !!}
                {!! Form::close() !!}
            </div>
        @endcomponent
        <div class="row">
            <div class="col">
                <div class="nav-tabs-custom">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#old_report_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                    aria-hidden="true"></i> Old Report</a>

                        </li>
                        <li>
                            <a href="#new_report_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-reply"
                                    aria-hidden="true"></i> New Report</a>

                        </li>
                        <li>
                            <a href="#chart_report_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-chart-area"
                                    aria-hidden="true"></i>Show Chart</a>

                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="old_report_tab">
                            @component('components.widget', ['class' => 'box-primary', 'title' => 'Feedback Report'])
                                <table class="table table-striped data-table fs-9" id="comment_wise_patient_table">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th class="c_name">Comment Name</th>
                                            <th>Description</th>
                                            <th>Patient Name</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th id="total_comment_count">0</th>
                                            <th></th>
                                            <th id="total_patient_count">0</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>

                                    </tbody>
                                </table>
                            @endcomponent
                        </div>
                        <div class="tab-pane" id="new_report_tab">
                            @component('components.widget', ['class' => 'box-primary', 'title' => 'Feedback Report'])
                                <table class="table table-striped data-table fs-9" id="comment_wise_patient_table_new"
                                    style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Comment Info</th>
                                            <th>Patient Info</th>
                                            <th>Mobile</th>
                                            <th>Age</th>
                                            <th>District</th>
                                        </tr>
                                    </thead>


                                    <tbody>

                                    </tbody>
                                </table>
                            @endcomponent
                        </div>
                        <div class="tab-pane" id="chart_report_tab">
                            {!! $chart->script() !!}
                            {!! $chart->container() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var comment_wise_patient_table = $('#comment_wise_patient_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('comment.wise.patient') }}",
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.comment_id = $('#comment_id').val();
                        d.district_id = $('#district_id').val();
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'comment_name_with_count',
                        name: 'comment_name_with_count',
                    },
                    {
                        data: 'comment_description',
                        name: 'comment_description',
                    },
                    {
                        data: 'patient_info',
                        name: 'patient_info',
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();
                    var totalComments = api.data().count();
                    $('#total_comment_count').text("Comment " + totalComments);
                    var totalPatients = calculatePatients(api);
                    $('#total_patient_count').text('Total Patient ' + totalPatients);
                }
            });
            var comment_wise_patient_table_new = $('#comment_wise_patient_table_new').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('comment.wise.patient.new_report') }}",
                    data: function(d) {
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.comment_id = $('#comment_id').val();
                        d.district_id = $('#district_id').val();
                    }
                },
                columns: [{
                        data: 'comment_info',
                        name: 'comment_info',
                    },
                    {
                        data: 'patient_info',
                        name: 'patient_info',
                    },
                    {
                        data: 'mobile',
                        name: 'mobile',
                    },

                    {
                        data: 'age',
                        name: 'age',
                    },
                    {
                        data: 'district_name',
                        name: 'district_name',
                    },
                ],
                order: [
                    [0, 'asc']
                ],
               
            });

            $(document).on('change', '#start_date, #end_date, #comment_id, #district_id', function() {
                $('#comment_wise_patient_table').DataTable().ajax.reload();
                $('#comment_wise_patient_table_new').DataTable().ajax.reload();
            });
           

            function calculatePatients(api) {
                var totalPatients = 0;

                api.column(3, {
                    page: 'current'
                }).data().each(function(value, index) {
                    if (value) {
                        var patients = value.split(',');

                        patients.forEach(function(patient) {
                            var phoneMatch = patient.match(/\(\d{11}\)/);

                            if (phoneMatch) {
                                var phoneNumber = phoneMatch[0].slice(1, -1);
                                if (phoneNumber.length === 11 && /^\d{11}$/.test(
                                        phoneNumber)) {
                                    totalPatients++;
                                }
                            }
                        });
                    }
                });

                return totalPatients;
            }
        });
    </script>
@endsection
