<div class="modal-dialog" role="document">
    <div class="modal-content">
        <!-- Header -->
        <div class="modal-header">
            
            <button type="button" class="close" style="color: black;" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times; <i class="fa fa-close"></i> </span>
            </button>

            <h4 class="modal-title" style="color: black;">@lang('clinic::lang.payment_details')</h4>
        </div>

        <div class="modal-body">
            
            @if($payments->isNotEmpty())
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>@lang('clinic::lang.amount')</th>
                            <th>@lang('clinic::lang.pay_method')</th>
                            <th>@lang('clinic::lang.date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payments as $payment)
                            <tr>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td> @if($payment->method == 'custom_pay_1')
                                        Bkash
                                    @else 
                                    {{ $payment->method }}
                                    @endif 
                                </td>
                                <td>{{ $payment->paid_on }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
