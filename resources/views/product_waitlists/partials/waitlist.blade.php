<div class="table-responsive">
<table class="table table-bordered table-striped" id="product_waitlist_table">
    <thead>
        <tr>
            <th>
                <input type="checkbox" id="select-all-row" data-table-id="product_waitlist_table">
            </th>
            <th>@lang('messages.actions')</th>
            <th style="width: 8%">@lang('Request Date')</th>
            {{-- <th>@lang('Waitlist No')</th> --}}
            <th>@lang('sale.product')</th>
            <th>@lang('SKU')</th>
            <th>@lang('Customer')</th>
            <th hidden>@lang('Mobile')</th>
            {{-- <th>@lang('lang_v1.contact_no')</th> --}}
            <th>@lang('Quantity Requested')</th>
            <th style="min-width: 60px !important;">@lang('Status')</th>
            <th style="min-width: 60px !important;">@lang('Call Status')</th>
            <th>@lang('notes')</th>
            <th>@lang('Added By')</th>
            {{-- <th>@lang('Location')</th> --}}
            <th>@lang('Ref.')</th>
            <th>@lang('Restock Date')</th>
            <th>@lang('Notification Send Date')</th>
            <th>@lang('SMS Status')</th>
        </tr>
    </thead>
    <tfoot>
        <tr>
            <td colspan="15">
                <div style="display: flex; width: 100%;">
                    @can('send_sms')
                        {!! Form::open(['url' => action([\App\Http\Controllers\ProductWaitlistController::class, 'sendSMS']), 'method' => 'post', 'id' => 'mass_delete_form' ]) !!}
                        {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']) !!}
                        {!! Form::submit(__('Send SMS'), array('class' => 'btn btn-xs btn-primary', 'id' => 'send-sms-selected')) !!}
                        {!! Form::close() !!}
                    @endcan
                    &nbsp;
                    @can('')
                        {!! Form::open(['url' => action([\App\Http\Controllers\ProductWaitlistController::class, 'sendEmail']), 'method' => 'post', 'id' => 'mass_delete_form' ]) !!}
                        {!! Form::hidden('selected_rows', null, ['id' => 'selected_rows']) !!}
                        {!! Form::submit(__('Send Email'), array('class' => 'btn btn-xs btn-primary', 'id' => 'send-email-selected')) !!}
                        {!! Form::close() !!}
                    @endcan
                    @show_tooltip("SMS will only be sent successfully to the customer if the product status is marked as 'Available'.")
                </div>
            </td>
        </tr>
    </tfoot>
</table>
</div>