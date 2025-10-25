<div class="modal-dialog modal-lg no-print" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="modalTitle"> @lang('clinic::lang.appointment_details') #({{ $appointment->appointment_number }}) Type: {{ ucfirst($appointment->type) }}
            </h4>
        </div>
        @php
            $url = action([
                \Modules\Clinic\Http\Controllers\AllAppointmentController::class,
                // 'requestToFinalTherapyAppointment',
                'updateRequestToFinal',
            ]);
            $form_id = 'request_to_final_therapy_appointment_form';
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}

        <div class="modal-body">
            <div class="row">
                <div class="col-xs-12">
                    <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($appointment->request_date) }}</p>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4">
                    <b>@lang('clinic::lang.appointment_no'):</b> #{{ $appointment->appointment_number }} <br>
                    <b>@lang('clinic::lang.status'):</b> {{ $appointment->remarks ?? '' }} <br>
                    <b>@lang('clinic::lang.payment_status'):</b> {{ $appointment->payment_status ?? '' }} <br>
                </div>

                <div class="col-sm-4">
                    <b>@lang('clinic::lang.patient_name'):</b> {{ $appointment->patient->first_name ?? '' }}
                    {{ $appointment->patient->last_name ?? '' }}<br>
                    <b>@lang('clinic::lang.address'):</b> {{ $appointment->patient->address ?? '' }} <br>
                    <b>@lang('clinic::lang.mobile'):</b> {{ $appointment->patient->mobile ?? '' }} <br>

                </div>
                <div class="col-sm-4">
                    @if ($payments->isNotEmpty())
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
                                        <td>
                                            @if ($payment->method == 'custom_pay_1')
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
            </div>
            <hr>
            <div class="row">
                <div class="col-md-4">
                    {!! Form::label('product_id', 'Therapy Subscription:') !!}
                    {!! Form::select('product_id', $subscriptions->toArray(), null, [
                        'class' => 'form-control',
                        'placeholder' => 'Select Therapy Subscription',
                        'required'
                    ]) !!}
                    {!! Form::hidden('appointment_id', $appointment->id) !!}
                    {!! Form::hidden('contact_id', $appointment->patient_contact_id) !!}
                    {!! Form::hidden('prefer_payment_method', 'cash') !!}
                    {!! Form::hidden('row_id', null) !!}
                </div>
                {{-- <div class="col-md-4">
                    <div class="form-group">
                        <label>@lang('clinic::lang.remaining_visit'): </label>
                        <span><strong>{{ $session ? $session->remaining_visit : '' }}</strong></span> <br>
                        <label>@lang('clinic::lang.end_date'): </label>
                        <span><strong>{{ $session ? date('d M Y', strtotime($session->end_date)) : '' }}</strong></span>
                    </div>
                </div> --}}
            </div>

        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary no-print">@lang('messages.save')</button>
            <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}

    </div>
</div>
<script>
    $(document).ready(function() {
        $('#request_to_final_therapy_appointment_form').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var formData = form.serialize();
            var formAction = form.attr('action');

            form.find('button[type=submit]').prop('disabled', true);

            $.ajax({
                url: formAction,
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success == true) {
                        toastr.success(response.message);
                        form.find('button[type=submit]').prop('disabled', false);
                        form[0].reset();
                        $('div.getRequestToFinalView').modal('hide');
                        $('#therapy_appointment_table').DataTable().ajax.reload();
                    } else if (response.success == false) {
                        toastr.error(response.message);
                        form.find('button[type=submit]').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    var errorMessage = 'An error occurred. Please try again.';
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response
                                .message;
                        }
                    } catch (e) {
                        toastr.error('Error parsing response:', e);
                    }
                    toastr.error(errorMessage);
                    console.error('Error Details:', xhr.responseText);
                    console.error('Status:', status);
                    console.error('Error:', error);
                    form.find('button[type=submit]').prop('disabled', false);
                }
            });
        });
    });
</script>
