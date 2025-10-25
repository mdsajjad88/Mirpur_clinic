@extends('clinic::layouts.app2')
@section('title', 'Diseases Wise Patient Reports')
@section('content')

    <div class="container-fluid">
        @component('components.filters', ['title' => 'Filters'])
            <div class="row">
                @php
                    $url = action([
                        \Modules\Clinic\Http\Controllers\Survey\ProblemController::class,
                        'problemWisePatientChartFilter',
                    ]);
                @endphp
                {!! Form::open([
                    'url' => $url,
                    'method' => 'GET',
                ]) !!}
                <div class="row">
                    <div class="col-sm-3">
                        {!! Form::label('start_date', 'Start Date') !!}
                        {!! Form::date('start_date', $startDate, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-sm-3">
                        {!! Form::label('end_date', 'End Date') !!}
                        {!! Form::date('end_date', $endDate, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-sm-3">
                        {!! Form::label('problem_id', 'Specific Disease') !!}
                        {!! Form::select('problem_id', ['' => 'All'] + $problems->toArray(), $problemId, [
                            'class' => 'form-control select2',
                            'style' => 'width:100% !important;',
                            'id' => 'problem_id',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        {!! Form::label('district_id', 'Specific District') !!}
                        {!! Form::select('district_id', ['' => 'All'] + $districts->toArray(), $districtId, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'id' => 'district_id',
                        ]) !!}
                    </div>
                    <div class="col-sm-3">
                        {!! Form::label('category', 'Category') !!}
                        {!! Form::select('category_id', ['' => 'All'] + $categories->pluck('name', 'id')->toArray(), $categoryId, [
                            'class' => 'form-control select2',
                            'style' => 'width:100%',
                            'id'=>'category_id'
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
                        <li >
                            <a href="#old_report_disease_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-cubes"
                                    aria-hidden="true"></i> Old Report</a>

                        </li>
                        <li>
                            <a href="#new_report_disease_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-reply"
                                    aria-hidden="true"></i> New Report</a>

                        </li>
                        <li class="active">
                            <a href="#chart_tab" data-toggle="tab" aria-expanded="true"><i class="fa fa-chart-area"
                                    aria-hidden="true"></i> Show Chart</a>

                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane" id="old_report_disease_tab">
                            @component('components.widget', ['class' => 'box-primary', 'title' => 'Diseases Wise Patient Reports'])
                                <table class="table table-striped data-table fs-9" id="diseases_wise_patient_table"
                                    style="width: 100% !important;">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Diseases Name</th>
                                            <th>Patient Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            @endcomponent
                        </div>
                        <div class="tab-pane" id="new_report_disease_tab">
                            @component('components.widget', ['class' => 'box-primary', 'title' => 'Diseases Wise Patient Reports'])
                                <table id="problem_wise_patient_new" class="table table-striped table-bordered"
                                    style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Problem Name</th>
                                            <th>Patient Name</th>
                                            <th>Patient Mobile</th>
                                            <th>Age</th>
                                            <th>District</th>
                                        </tr>
                                    </thead>
                                </table>
                            @endcomponent
                        </div>
                        <div class="tab-pane active" id="chart_tab">
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
            $('#diseases_wise_patient_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('problem.wise.patient') }}",
                    data: function(d) {
                        d.problem_id = $('#problem_id').val();
                        d.district_id = $('#district_id').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.category_id = $('#category_id').val();
                    }
                },
                columns: [{
                        data: null,
                        render: function(data, type, row, meta) {
                            return meta.row + meta.settings._iDisplayStart + 1;
                        }
                    },
                    {
                        data: 'problem_name_with_count',
                        name: 'problem_name_with_count',

                    },
                    {
                        data: 'patient_info',
                        name: 'patient_info',
                    },

                ],
                order: [
                    [0, 'desc']
                ]
            });
            var reportNewTable = $('#problem_wise_patient_new').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('problem.wise.patient.new') }}",
                    data: function(d) {
                        d.district_id = $('#district_id').val();
                        d.problem_id = $('#problem_id').val(); 
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.category_id = $('#category_id').val();
                    }
                },
                columns: [{
                        data: 'problem_name',
                        name: 'problem_name'
                    },
                    {
                        data: 'patient_info',
                        name: 'patient_info'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
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
                ]
            });

            $(document).on('change', '#problem_id, #start_date, #end_date, #district_id, #category_id', function() {
                $('#diseases_wise_patient_table').DataTable().ajax.reload();
                $('#problem_wise_patient_new').DataTable().ajax.reload();

            });

        });
    </script>
@endsection
