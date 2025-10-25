@extends('clinic::layouts.app2')
@section('title', __('Prescriptions'))
@section('content')
    <div class="container-fluid">
        @component('components.widget', ['class' => 'box-primary', 'title' => 'Prescriptions'])
            <div class="row">
                <table class="table table-bordered table-striped" id="confirm_appointment_table" style="width: 100%">
                    <thead>
                        <tr>

                            <th>Doctor Name</th>
                            <th>Patient Name</th>
                         <th>Mobile</th>
                            {{-- 
                        <th>Request Date</th>
                        <th>Status</th>
                        <th>creator</th>
                        <th>Created time</th> --}}
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent
    </div>
   

@endsection


@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var confirm_appointment_table = $('#confirm_appointment_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [1, 'desc']
                ],
                "ajax": {
                    "url": "{{ route('prescriptions.index') }}",
                    "data": function(d) {
                        d = __datatable_ajax_callback(
                            d); // Assuming this function is handling additional data
                    }
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [

                    {
                        data: 'assigned_doctor',
                        name: 'assigned_doctor',

                    },
                    {
                        data: 'patient_name',
                        name: 'patient_name',

                    },
                    {
                        data: 'patient_mobile',
                        name: 'patient_mobile'
                    },
                    // {
                    //     data: 'created_at',
                    //     name: 'created_at',
                    //     render: function(data) {
                    //         return moment(data).format('YYYY-MM-DD');
                    //     }
                    // },
                    // {
                    //     data: 'status',
                    //     name: 'status'
                    // },
                    // {
                    //     data: 'creator_first_name',
                    //     name: 'creator_first_name',
                    //     render: function(data, type, row) {
                    //         return row.creator_first_name + ' ' + row.creator_last_name;
                    //     }
                    // },
                    // {
                    //     data: 'created_at',
                    //     name: 'created_at',
                    //     render: function(data) {
                    //         return moment(data).format('YYYY-MM-DD');
                    //     }
                    // },
                ]
            });
        });
    </script>
@endsection
