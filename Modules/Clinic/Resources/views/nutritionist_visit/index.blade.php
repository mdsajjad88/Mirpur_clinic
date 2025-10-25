@extends('clinic::layouts.app2')

@section('title', __('clinic::lang.nutritionist_visit'))
@section('content')
    <section class="content no-print">

        @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-primary'])
            <div class="row">
                <div class="col-md-3">
                    {!! Form::label('Date', __('clinic::lang.appointment_date')) !!}
                    {!! Form::date('appointment_date', '2025-09-04', [
                        'class' => 'form-control',
                        'id' => 'appointment_date',
                        'min' => '',
                        'max' => '',
                    ]) !!}
                </div>
            </div>
        @endcomponent
        @component('components.widget', ['class' => 'box-primary'])
            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="nutritionist_visit_table" style="width: 100%;">
                    <thead>
                        <tr>
                            <th>Created Time</th>
                            <th>@lang('clinic::lang.patient_name')</th>
                            <th>@lang('clinic::lang.contact_id')</th>
                            <th>@lang('clinic::lang.doctor')</th>
                            <th>@lang('clinic::lang.disease')</th>
                            {{-- <th>@lang('clinic::lang.waiting_time')</th> --}}
                            <th>@lang('clinic::lang.completed_by')</th>
                            <th>@lang('clinic::lang.last_updated')</th>
                            <th style="width: 85px;">@lang('clinic::lang.action')</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        @endcomponent
    </section>

@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            var nutritionist_visit_table = $('#nutritionist_visit_table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                "ajax": {
                    "url": "/nutritionist-visit",
                    "data": function(d) {
                        d.appointment_date = $('#appointment_date').val();
                    }
                },
                order: [
                    [0, 'desc']
                ],
                columns: [
                    {
                        data: 'pres_created_at',
                        name: 'pres.created_at',
                        visible: false
                    },
                    {
                        data: 'patient_name',
                        name: 'pp.first_name'
                    },

                    {
                        data: 'contact_id',
                        name: 'contacts.contact_id'
                    },
                    {
                        data: 'doctor_name',
                        name: 'doctor_name'
                    },
                    {
                        data: 'diseases',
                        name: 'diseases'
                    },
                    // {
                    //     data: 'waiting_time',
                    //     name: 'waiting_time'
                    // },
                    {
                        data: 'completed_by',
                        name: 'completed_by'
                    },
                    {
                        data: 'editor_name',
                        name: 'editor_name',
                        render: data => data && data.trim() ? data : ''
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],

            });

            $('#appointment_date').change(function() {
                nutritionist_visit_table.ajax.reload();
            });
        })
    </script>

@endsection
