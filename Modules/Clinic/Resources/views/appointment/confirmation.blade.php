@extends('clinic::layouts.app2')
@section('title', __('Appointment Confirmation'))
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col custom-row mt-2 doctor-heading">
            @if(auth()->user()->can('new.appointment.create'))
            <div class="text-left">
                <a href="{{action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'index']) }}">
                    <i class="fas fa-backward"></i>&nbsp;
                </a>
                <strong>@lang('clinic::lang.app_no')</strong>
            </div>
            @endif
            <div class="text-right">
                <a href="{{action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'index']) }}"
                    class="btn make_app">
                    <i class="fas fa-list"></i>&nbsp; @lang('clinic::lang.app_list')
                </a>
            </div>
        </div>    
    </div>    
    <div class="row">
        @component('components.widget')

        <div class="col">
            <button class="btn ml-2 btn-primary">@lang('clinic::lang.sms')</button>
            <button class="btn btn-warning">@lang('clinic::lang.cng_schedule')</button>
            <button class="btn make_app">@lang('clinic::lang.questionnaire')</button>
            <button class="btn btn-primary">@lang('clinic::lang.pay_and_confirm')</button>
            <button class="btn btn-info">@lang('clinic::lang.cancle_app')</button>
            <button class="btn btn-success">@lang('clinic::lang.patient_pro')</button>
        </div>
        @endcomponent
    </div>
    <div class="row mt-2">
        <div class="col-md-6">
            @component('components.widget' , ['class' => 'box-primary',])
            <div class="row">
                <div class="col-md-5"><b>@lang('clinic::lang.app_no')</b></div>
                <div class="col-md-2"><b>:</b></div>
                <div class="col-md-5">6545615151551</div>
            </div>
            <div class="row separator">
                <div class="col-md-12"></div>
            </div>
            <div class="row">
                <div class="col-md-5"><b>@lang('clinic::lang.chamber')</b></div>
                <div class="col-md-2"><b>:</b></div>
                <div class="col-md-5">American Wellness Center</div>
            </div>
            <div class="row separator">
                <div class="col-md-12"></div>
            </div>
            <div class="row">
                <div class="col-md-5"><b>@lang('clinic::lang.d_name')</b></div>
                <div class="col-md-2"><b>:</b></div>
                <div class="col-md-5">Dr. Samiul Islam</div>
            </div>
            <div class="row separator">
                <div class="col-md-12"></div>
            </div>
            <div class="row">
                <div class="col-md-5"><b>@lang('clinic::lang.slot')</b></div>
                <div class="col-md-2"><b>:</b></div>
                <div class="col-md-5">Sep 22, 2024 10.30 AM</div>
            </div>
            @endcomponent
        </div>
        <div class="col-md-6">
            @component('components.widget', ['class' => 'box-primary',])
            <div class="row">
                <div class="col-md-5"><b>@lang('clinic::lang.pname')</b></div>
                <div class="col-md-2"><b>:</b></div>
                <div class="col-md-5">Shakib Khan</div>
            </div>
            <div class="row separator">
                <div class="col-md-12"></div>
            </div>
            <div class="row">
                <div class="col-md-5"><b>@lang('clinic::lang.p_email')</b></div>
                <div class="col-md-2"><b>:</b></div>
                <div class="col-md-5">patient@gmail.com</div>
            </div>
            <div class="row separator">
                <div class="col-md-12"></div>
            </div>
            <div class="row">
                <div class="col-md-5"><b>@lang('clinic::lang.pmobile')</b></div>
                <div class="col-md-2"><b>:</b></div>
                <div class="col-md-5">01547859658</div>
            </div>
            <div class="row separator">
                <div class="col-md-12"></div>
            </div>
            <div class="row">
                <div class="col-md-5"><b>@lang('clinic::lang.service')</b></div>
                <div class="col-md-2"><b>:</b></div>
                <div class="col-md-5">Doctor Consultation</div>
            </div>
            @endcomponent
        </div>
    </div>
    @component('components.widget')
        <div class="row custom-row2 doctor-heading2">
            <div class="text-left">
                <strong>@lang('clinic::lang.ledgers')</strong>
            </div>
            <div class="text-right">
                <button class="btn make_app">@lang('clinic::lang.invoice')</button>               
            </div>
        </div>
        <div class="row mt-1">
            <div class="col">
                <table class="table" id="appointment_number_table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>@lang('clinic::lang.date/time')</th>
                            <th>@lang('clinic::lang.tnx')</th>
                            <th>@lang('clinic::lang.user')</th>
                            <th>@lang('clinic::lang.pay_from')</th>
                            <th>@lang('clinic::lang.debits')</th>
                            <th>@lang('clinic::lang.credits')</th>
                            <th>@lang('clinic::lang.balance')</th>
                        </tr>
                    </thead>
                    
                </table>
            </div>
        </div>
    @endcomponent
</div>
@endsection 
@section('javascript')
<script>
    $(document).ready(function() {
    var appointment = $('#appointment_number_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("appointment.number") }}',
        columns: [
            {
                data: null,
                render: function(data, type, row) {
                    // Return the HTML for your icon
                    return '<i class="fas fa-receipt" aria-hidden="true" style="font-size:30px;"></i>';// Replace with your desired icon
                },
                orderable: false // Optional: prevents sorting on this column
            },
            { data: 'date_time' },
            { data: 'tnx' },
            { data: 'user' },
            { data: 'pay_form' },
            { data: 'debits' },
            { data: 'credits' },
            { data: 'balance' }
        ]
    });
});
</script>


@endsection