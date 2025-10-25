@extends('clinic::layouts.app2')
@section('title', 'Reference Wise Patient Reports')
@section('content')
    <div class="container-fluid">
        @component('components.filters', ['title' => 'Filters'])
        <div class="row">
            @php
                $url = action([
                    \Modules\Clinic\Http\Controllers\Survey\ReferenceController::class,
                    'referenceWisePatientChartFilter',
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
                    {!! Form::label('reference_id', 'Specific Reference') !!}
                    {!! Form::select('reference_id', ['' => 'All'] + $references->toArray(), null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100% !important;',
                        'id' => 'reference_id',
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
                            <a href="#old_reference_report_tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fa fa-cubes" aria-hidden="true"></i> Old Report</a>

                        </li>
                        <li>
                            <a href="#new_reference_report_tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fa fa-reply" aria-hidden="true"></i> New Report</a>

                        </li>
                        <li>
                            <a href="#chart_reference_report_tab" data-toggle="tab" aria-expanded="true"><i
                                    class="fa fa-chart-area" aria-hidden="true"></i>Show Chart</a>

                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="old_reference_report_tab">
                            @component('components.widget', ['class' => 'box-primary', 'title' => 'Reference wise Patient'])
                                <table class="table table-striped" id="reference_wise_patient">
                                    <thead>
                                        <tr>
                                            <th>SL</th>
                                            <th>Reference</th>
                                            <th>Patient Name</th>
                                        </tr>
                                    </thead>
                                    <tfoot>
                                        <tr>
                                            <th></th>
                                            <th id="total_reference_count">0</th>
                                            <th id="total_patient_count">0</th>
                                        </tr>
                                    </tfoot>
                                    <tbody>

                                    </tbody>
                                </table>
                            @endcomponent
                        </div>
                        <div class="tab-pane" id="new_reference_report_tab">
                            @component('components.widget', ['class' => 'box-primary', 'title' => 'Reference wise Patient'])
                                <table class="table table-striped" id="reference_wise_patient_new_format" style="width: 100%">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Patient</th>
                                            <th>Mobile </th>
                                            <th>Age </th>
                                            <th>District</th>
                                        </tr>
                                    </thead>

                                    <tbody>

                                    </tbody>
                                </table>
                            @endcomponent
                        </div>
                        <div class="tab-pane" id="chart_reference_report_tab">
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
            $('#reference_wise_patient').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('survey-references.index') }}",
                    data: function(d) {
                        d.reference_id = $('#reference_id').val();
                        d.end_date = $('#end_date').val();
                        d.start_date = $('#start_date').val();
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
                        data: 'reference_name_with_count',
                        name: 'reference_name_with_count'
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
                    var totalReference = api.data().count();
                    $('#total_reference_count').text("Reference " + totalReference);

                    var totalPatients = calculatePatients(api);

                    $('#total_patient_count').text('Total Patient ' + totalPatients);

                }
            });
            $('#reference_wise_patient_new_format').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ url('survey/reference-wise-patient') }}",
                    data: function(d) {
                        d.district_id = $('#district_id').val();
                        d.reference_id = $('#reference_id').val();
                        d.end_date = $('#end_date').val();
                        d.start_date = $('#start_date').val();
                    }
                },
                columns: [
                   
                    {
                        data: 'reference_name',
                        name: 'reference_name'
                    },
                    {
                        data: 'patient_name',
                        name: 'patient_name'
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
                ],
            });


            $(document).on('change', '#reference_id, #start_date, #end_date, #district_id', function() {
                $('#reference_wise_patient').DataTable().ajax.reload();
                $('#reference_wise_patient_new_format').DataTable().ajax.reload();
            });

            function calculatePatients(api) {
                var totalPatients = 0;
                api.column(2, {
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
