@extends('clinic::layouts.app2')
@section('title', __('clinic::lang.seminar_patient_info'))
@section('content')
    <div class="container-fluid">

        <div class="row">
            @component('components.widget', ['title' => 'Apply Filter', 'class' => 'box-primary'])
                <div class="col-md-4">
                    {!! Form::open([
                        'url' => action([\Modules\Clinic\Http\Controllers\SeminarPatientInfoController::class, 'importCsv']),
                        'method' => 'post',
                        'files' => true,
                        'id' => 'import_form',
                    ]) !!}
                    <div class="row">
                        <div class="col-md-8">
                            {!! Form::label('import', 'Import Patient Info') !!} @show_tooltip(__('tooltip.seminar_patient_info_import_tooltip'))
                            {!! Form::file('import_file', ['accept' => '.csv', 'class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::label('', '') !!} <br>
                            <button type="submit" class="btn btn-primary mt-2 text-right">Upload</button>
                        </div>

                    </div>
                    {!! Form::close() !!}

                </div>



                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('source', __('clinic::lang.source') . ':') !!}
                        {!! Form::select('source_id', $sources, null, [
                            'class' => 'form-control select2',
                            'id' => 'source_id',
                            'placeholder' => __('messages.all'),
                            'style' => 'width: 100%;',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('only_this_source', __('clinic::lang.create_only_this_source') . ':') !!}
                        {!! Form::select('only_this_source', ['' => 'All', 1 => 'Yes', 0 => 'No'], null, [
                            'class' => 'form-control select2',
                            'id' => 'only_this_source',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('only_transaction', __('clinic::lang.only_transaction') . ':') !!}
                        {!! Form::select('only_transaction', ['' => 'All', 1 => 'Yes', 0 => 'No'], null, [
                            'class' => 'form-control select2',
                            'id' => 'only_transaction',
                        ]) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('billing_type', __('clinic::lang.billing_type') . ':') !!}
                        {!! Form::select(
                            'billing_type',
                            ['' => 'All', 'test' => 'Test', 'therapy' => 'Therapy', 'consultation' => 'Consultation'],
                            null,
                            ['class' => 'form-control select2', 'id' => 'billing_type'],
                        ) !!}
                    </div>
                </div>
            @endcomponent
        </div>


        <div class="row">
            @component('components.widget', [
                'class' => 'box-primary',
                'title' => __('clinic::lang.seminar_patient_info_list'),
            ])
                <table class="table table-bordered table-striped" id="seminar_patient_info_table">
                    <thead>
                        <tr>
                            <th>@lang('messages.name')</th>
                            <th>@lang('clinic::lang.mobile')</th>
                            <th>@lang('clinic::lang.contact_id')</th>
                            <th>@lang('clinic::lang.source')</th>
                            <th>@lang('clinic::lang.test_bill_count')</th>
                            <th>@lang('clinic::lang.test_bill_total')</th>
                            <th>@lang('clinic::lang.therapy_bill_count')</th>
                            <th>@lang('clinic::lang.therapy_bill_total')</th>
                            <th>@lang('clinic::lang.consultation_bill_count')</th>
                            <th>@lang('clinic::lang.total_consultation_bill')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr>
                            <th colspan="4" style="text-align:right">Total:</th>
                            <th id="total_test_count"></th>
                            <th id="total_test_bill"></th>
                            <th id="total_therapy"></th>
                            <th id="total_therapy_bill"></th>
                            <th id="total_consultation"></th>
                            <th id="total_consultation_bill"></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            @endcomponent
        </div>
        <div class="modal fade seminar_patient_info_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel"></div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var table = $('#seminar_patient_info_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ action([\Modules\Clinic\Http\Controllers\SeminarPatientInfoController::class, 'index']) }}",
                    type: 'GET',
                    data: function(d) {
                        d.source_id = $('#source_id').val();
                        d._token = "{{ csrf_token() }}";
                        d.only_transaction = $('#only_transaction').val();
                        d.billing_type = $('#billing_type').val();
                        d.only_this_source = $('#only_this_source').val();
                    }
                },
                aaSorting: [
                    [2, 'desc']
                ],
                columns: [{
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'customer_id',
                        name: 'customer_id'
                    },
                    {
                        data: 'source',
                        name: 'source'
                    },
                    {
                        data: 'test_bill_count',
                        name: 'test_bill_count'
                    },
                    {
                        data: 'test_bill_total',
                        name: 'test_bill_total'
                    },
                    {
                        data: 'therapy_bill_count',
                        name: 'therapy_bill_count'
                    },
                    {
                        data: 'therapy_bill_total',
                        name: 'therapy_bill_total'
                    },
                    {
                        data: 'consultation_bill_count',
                        name: 'consultation_bill_count'
                    },
                    {
                        data: 'total_consultation_bill',
                        name: 'total_consultation_bill'
                    },

                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                drawCallback: function(settings) {
                    var api = this.api();

                    var total_test_count = api.column(4, {
                        page: 'current'
                    }).data().reduce((a, b) => a + parseFloat(b || 0), 0);
                    var total_test_bill = api.column(5, {
                        page: 'current'
                    }).data().reduce((a, b) => a + parseFloat(b || 0), 0);
                    var total_therapy_count = api.column(6, {
                        page: 'current'
                    }).data().reduce((a, b) => a + parseFloat(b || 0), 0);
                    var total_therapy_bill = api.column(7, {
                        page: 'current'
                    }).data().reduce((a, b) => a + parseFloat(b || 0), 0);
                    var total_consultation_count = api.column(8, {
                        page: 'current'
                    }).data().reduce((a, b) => a + parseFloat(b || 0), 0);
                    var total_consultation_bill = api.column(9, {
                        page: 'current'
                    }).data().reduce((a, b) => a + parseFloat(b || 0), 0);

                    $('#total_test_count').html(total_test_count);
                    $('#total_test_bill').html(total_test_bill.toFixed(2));
                    $('#total_therapy').html(total_therapy_count);
                    $('#total_therapy_bill').html(total_therapy_bill);
                    $('#total_consultation').html(total_therapy_bill.toFixed(2));
                    $('#total_consultation').html(total_consultation_count);
                    $('#total_consultation_bill').html(total_consultation_bill.toFixed(2));
                }


            });

            // Filter change
            $(document).on('change', '#source_id,#only_transaction, #billing_type, #only_this_source', function() {
                table.ajax.reload();
            });
        });
    </script>
@endsection
