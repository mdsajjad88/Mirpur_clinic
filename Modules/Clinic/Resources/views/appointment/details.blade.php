@extends('clinic::layouts.app2')
@section('title', __('Appointments Details'))
@section('content')
   
<div class="container-fluid">
    <div class="row">
        <div class="col custom-row mt-2 doctor-heading">
            @if(auth()->user()->can('new.appointment.create'))
            <div class="text-left">
                <a href="{{action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'index']) }}">
                    <i class="fas fa-backward"></i>&nbsp;
                </a>
                <strong>@lang('clinic::lang.appointment_details')</strong>
            </div>
            @endif
            {{-- <div class="text-right">
                <a href="{{action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'index']) }}"
                    class="btn make_app">
                    <i class="fas fa-list"></i>&nbsp; @lang('clinic::lang.app_list')
                </a>
            </div> --}}
        </div>    
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
    <div class="row custom-row3 doctor-heading3">
        <div class="text-left">
            <strong>@lang('clinic::lang.consult_fee')</strong>
        </div>
        <div class="text-right">
            <p><b>@lang('clinic::lang.total_paid') : 00.00</b></p>
        </div>
    </div>
    <form action="" method="post">
        <div class="row">
            <div class="col-md-6">
                <label for="amount">@lang('clinic::lang.amount')</label>
                <input type="number" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="discount_amount">@lang('clinic::lang.dis_amount')</label>
                <input type="number" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="total_amount">@lang('clinic::lang.total_amount')</label>
                <input type="number" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="total_due">@lang('clinic::lang.due_amount')</label>
                <input type="number" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="transaction_media">@lang('clinic::lang.t_media')</label>
                <select name="" id="" class="form-control">
                    <option value="">Cash</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="payment_date">@lang('clinic::lang.pay_date')</label>
                <input type="date" name="" id="" class="form-control">
            </div>
        </div>
        <div class="row">
            <div class="col-md-6">
                <label for="remarks">@lang('clinic::lang.remarcks')</label>
                <textarea name="" id=""  rows="3" class="form-control"></textarea>
            </div>
            <div class="col-md-6"></div>
            
        </div>
        <div class="row">
            <div class="col-md-10"></div>
            <div class="col-md-2">
                <button class="btn btn-info">@lang('clinic::lang.sub_pay_info')</button>
            </div>
        </div>
    </form>
    @endcomponent


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