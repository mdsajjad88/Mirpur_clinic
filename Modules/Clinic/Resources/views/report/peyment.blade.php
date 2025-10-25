@extends('clinic::layouts.app2')
@section('title', __('Payment Report'))
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col custom-row report-heading">
                <div class="text-left">
                    <a href="#">
                        <i class="fas fa-list"></i>&nbsp;
                    </a>
                    <strong>@lang('clinic::lang.pay_report')</strong>
                </div>
                
            </div>
        </div>
        <div class="row">
            <div class="col">
                @component('components.filters', ['title' => __('report.filters'), 'class' => 'box-secondary'])
                    <div class="row">
                        <div class="col-md-3">
                            <label for="patient_name">@lang('clinic::lang.pname')</label>
                            <input type="text" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="patient_mobile">@lang('clinic::lang.pmobile')</label>
                            <input type="number" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label for="service_name">@lang('clinic::lang.sname')</label>
                            <select name="" id="" class="form-control">
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="service_name">@lang('clinic::lang.pay_method')</label>
                            <select name="" id="" class="form-control">
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <label for="limit">@lang('clinic::lang.limit')</label>
                            <select name="" id="" class="form-control">
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="service_name">@lang('clinic::lang.helped_by')</label>
                            <select name="" id="" class="form-control">
                                <option value="">Select</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>&nbsp;</label> <!-- To keep the label space, use an empty label -->
                            <div class="d-flex">
                                <button class="btn btn-info me-2">@lang('clinic::lang.search')</button>
                                <button class="btn btn-warning">@lang('clinic::lang.reset')</button>
                            </div>
                        </div>

                    </div>
                @endcomponent
            </div>
        </div>
        <div class="row">
            <div class="col">
                @component('components.widget')
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="payment_report_table">
                            <thead>
                                <tr>
                                    <th>@lang('clinic::lang.sl')</th>
                                    <th>@lang('clinic::lang.tnx-id')</th>
                                    <th>@lang('clinic::lang.pname')</th>
                                    <th>@lang('clinic::lang.p-id')</th>
                                    <th>@lang('clinic::lang.sname')</th>
                                    <th>@lang('clinic::lang.amount')</th>
                                    <th>@lang('clinic::lang.pay_method')</th>
                                    <th>@lang('clinic::lang.date')</th>
                                    <th>@lang('clinic::lang.helped_by')</th>
                                </tr>
                            </thead>

                        </table>
                    </div>
                @endcomponent
            </div>
        </div>

    </div>
@endsection
@section('javascript')
    <script>
        $(document).ready(function() {
            $('#payment_report_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('pay.report.get') }}', // Adjust route as necessary
                columns: [

                    {
                        data: 'id',
                        name: 'id'
                    
                    }, 
                    {
                        data: 'tnx_id',
                        name: 'tnx_id'
                    },
                    {
                        data: 'pname',
                        name: 'pname'
                    },
                    {
                        data: 'p_id',
                        name: 'p_id'
                    },
                    {
                        data: 'sname',
                        name: 'sname'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'pay_method',
                        name: 'pay_method'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'helped_by',
                        name: 'helped_by'
                    }
                ]
            });
        });
    </script>
@endsection
