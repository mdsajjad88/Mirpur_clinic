@extends('clinic::layouts.app2')
@section('title', __('clinic::lang.patients'))

@php
    $api_key = env('GOOGLE_MAP_API_KEY');
    $type = 'customer';
@endphp

@if (!empty($api_key))
    @section('css')
        @include('contact.partials.google_map_styles')
        <style>
            .select2-container {
                width: 100% !important;
            }
        </style>
    @endsection
@endif

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            @lang('clinic::lang.patients')
            <small>@lang('clinic::lang.manage_you_patients')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.filters', ['title' => __('report.filters')])
            @if ($type == 'customer')
                <div class="col-md-3">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('has_sell_due', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_due']) !!}
                            <strong>@lang('lang_v1.sell_due')</strong>
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('has_sell_return', 1, false, ['class' => 'input-icheck', 'id' => 'has_sell_return']) !!}
                            <strong>@lang('lang_v1.sell_return')</strong>
                        </label>
                    </div>
                </div>
            @elseif($type == 'supplier')
                <div class="col-md-3">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('has_purchase_due', 1, false, ['class' => 'input-icheck', 'id' => 'has_purchase_due']) !!}
                            <strong>@lang('report.purchase_due')</strong>
                        </label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>
                            {!! Form::checkbox('has_purchase_return', 1, false, ['class' => 'input-icheck', 'id' => 'has_purchase_return']) !!}
                            <strong>@lang('lang_v1.purchase_return')</strong>
                        </label>
                    </div>
                </div>
            @endif
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_advance_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_advance_balance']) !!}
                        <strong>@lang('lang_v1.advance_balance')</strong>
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label>
                        {!! Form::checkbox('has_opening_balance', 1, false, ['class' => 'input-icheck', 'id' => 'has_opening_balance']) !!}
                        <strong>@lang('lang_v1.opening_balance')</strong>
                    </label>
                </div>
            </div>

            @if ($type == 'customer')
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="has_no_sell_from">@lang('lang_v1.has_no_sell_from'):</label>
                        {!! Form::select(
                            'has_no_sell_from',
                            [
                                'one_month' => __('lang_v1.one_month'),
                                'three_months' => __('lang_v1.three_months'),
                                'six_months' => __('lang_v1.six_months'),
                                'one_year' => __('lang_v1.one_year'),
                            ],
                            null,
                            ['class' => 'form-control', 'id' => 'has_no_sell_from', 'placeholder' => __('messages.please_select')],
                        ) !!}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="form-group">
                        <label for="cg_filter">@lang('lang_v1.customer_group'):</label>
                        {!! Form::select('cg_filter', $customer_groups, null, ['class' => 'form-control', 'id' => 'cg_filter']) !!}
                    </div>
                </div>
            @endif

            <div class="col-md-3">
                <div class="form-group">
                    <label for="status_filter">@lang('sale.status'):</label>
                    {!! Form::select(
                        'status_filter',
                        ['active' => __('business.is_active'), 'inactive' => __('lang_v1.inactive')],
                        null,
                        ['class' => 'form-control', 'id' => 'status_filter', 'placeholder' => __('lang_v1.none')],
                    ) !!}
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <br>
                    {!! Form::checkbox('is_patient', 1, false, ['class' => 'input-icheck', 'id' => 'is_patient']) !!}
                    <strong>Only Patient</strong>

                </div>
            </div>
        @endcomponent

        <input type="hidden" value="{{ $type }}" id="contact_type">

        @component('components.widget', [
            'class' => 'box-primary',
            'title' => __('clinic::lang.all_your_patients', ['contacts' => __('lang_v1.' . $type . 's')]),
        ])
            @if (auth()->user()->can('clinic.patient.create'))
                @slot('tool')
                    <div class="box-tools">
                        <button type="button" class="btn btn-block btn-primary btn-modal add_new_patients">
                            <i class="fa fa-plus"></i> @lang('messages.add')
                        </button>
                    </div>
                @endslot
            @endif

            @if (auth()->user()->can('clinic.patient.view'))
                <table class="table table-bordered table-striped" id="patients_table" style="width: 100%">
                    <thead>
                        <tr>
                            <th>@lang('messages.action')</th>
                            <th>@lang('lang_v1.contact_id')</th>
                            {{-- <th>@lang('business.business_name')</th> --}}
                            <th>@lang('user.name')</th>
                            <th>@lang('business.email')</th>
                            <th>@lang('business.age')</th>
                            <th>@lang('business.gender')</th>
                            <th>@lang('clinic::lang.disease')</th>

                            <th class="dating">@lang('lang_v1.added_on')</th>
   
                            <th>@lang('business.address')</th>
                            <th>@lang('contact.mobile')</th>
                        </tr>
                    </thead>
                </table>
            @endif
        @endcomponent
        <div class="modal fade edit_contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade patient_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
            @include('clinic::patient.patients.partials.add_patient')
        </div>
        <div class="modal fade pay_contact_due_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>


    </section>
    <!-- /.content -->
@stop

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var patients_table = $('#patients_table').DataTable({
                processing: true,
                serverSide: true,
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                ajax: {
                    url: "/patients",
                    data: function(d) {
                        d.type = $('#contact_type').val();
                        d = __datatable_ajax_callback(d);

                        // Additional filters
                        if ($('#has_advance_balance').length > 0 && $('#has_advance_balance').is(
                                ':checked')) {
                            d.has_advance_balance = true;
                        }
                        if ($('#is_patient').length > 0 && $('#is_patient').is(
                                ':checked')) {
                            d.is_patient = true;
                        }
                        d.status_filter = $('#status_filter').val();
                        
                    }
                },
                aaSorting: [
                    [8, 'desc']
                ],
                columns: [{
                        data: 'action',
                        searchable: false,
                        orderable: false
                    },
                    {
                        data: 'contact_id',
                        name: 'contact_id'
                    },
                    // {
                    //     data: 'supplier_business_name',
                    //     name: 'supplier_business_name'
                    // },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'age',
                        name: 'p_profile.age',
                        
                    },
                    {
                        data: 'gender',
                        name: 'p_profile.gender',
                        render: function(data, type, row) {
                            if (data && typeof data === 'string') {
                                return data.charAt(0).toUpperCase() + data.slice(1);
                            }
                            return data; 
                        }
                    },
                    
                    {
                        data: 'disease_names',
                        name: 'disease_names',
                        searchable: false,
                    },
                    {
                        data: 'contact_created_at',
                        name: 'contacts.created_at',
                        render: function(data) {
                            return moment(data).format('YYYY-MM-DD');
                        }
                    },
                  
                    {
                        data: 'address',
                        name: 'address',
                        orderable: false
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                ],
                fnDrawCallback: function(oSettings) {
                    __currency_convert_recursively($('#patients_table'));
                },

            });
            patients_table.ajax.reload();

            $('#disease').select2({
                placeholder: 'Select Health Concerns',
                allowClear: true,
            });


        });
        $(document).on('click', '.edit_patient_button', function(e) {
            e.preventDefault();
            $('div.edit_contact_modal').load($(this).attr('href'), function() {
                $(this).modal('show');
            });
        });
        // $(document).on('shown.bs.modal', '.patient_edit_modal', function(e) {
        //     initAutocomplete();
        // });

        $(document).on('click', '.more_btn', function() {
            $("div").find('.add_more_info_customer').toggleClass('hide');
        });
        $(document).on('ifChanged',
            '#has_sell_due, #has_sell_return, #has_purchase_due, #has_purchase_return, #has_advance_balance, #has_opening_balance',
            function() {
                $('#patients_table').DataTable().ajax.reload();
            });
        $(document).on('change', '#status_filter', function() {
            $('#patients_table').DataTable().ajax.reload();
        })
        $(document).on('ifChanged', '#is_patient', function() {
            $('#patients_table').DataTable().ajax.reload();
        });

        $(document).on('click', 'a.update_patient_status', function(e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $.ajax({
                url: href,
                dataType: 'json',
                success: function(data) {
                    if (data.success == true) {
                        toastr.success(data.msg);
                        $('#patients_table').DataTable().ajax.reload();
                    } else {
                        toastr.error(data.msg);
                    }
                },
            });
        });
        $(document).on('click', '.add_new_patients', function() {
            $('#customer_id').select2('close');
            var name = $(this).data('name');
            $('.patient_add_modal').find('input#name').val(name);
            $('.patient_add_modal')
                .find('select#contact_type')
                .val('customer')
                .closest('div.contact_type_div')
                .addClass('hide');
            $('.patient_add_modal').modal('show');
        });
    </script>
@endsection
