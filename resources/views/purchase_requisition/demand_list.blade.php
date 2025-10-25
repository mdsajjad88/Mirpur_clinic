{{-- purchase_requisition/demand_list.blade.php --}}
<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Product Wait List From Customer Demand</h4>
        </div>
        <div class="modal-body">
            <table class="table table-bordered table-striped" id="product_waitlist_table">
                <thead>
                    <tr>
                        <th style="width: 8%">@lang('Request Date')</th>
                        <th>@lang('Waitlist No')</th>
                        <th>@lang('sale.product')</th>
                        <th>@lang('SKU')</th>
                        <th>@lang('sale.customer_name')</th>
                        <th>@lang('lang_v1.contact_no')</th>
                        <th>@lang('Quantity Requested')</th>
                        <th>@lang('Added By')</th>
                        <th>@lang('Location')</th>
                        <th>@lang('Reference')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($waitlists as $waitlist)
                        <tr>
                            <td>{{ $waitlist->created_at->format('Y-m-d') }}</td>
                            <td>{{ $waitlist->waitlist_no }}</td>
                            <td>{{ $waitlist->product_name }}</td>
                            <td>{{ $waitlist->product_sku }}</td>
                            <td>{{ $waitlist->customer_name }}</td>
                            <td>{{ $waitlist->customer_phone_number }}</td>
                            <td>{{ $waitlist->quantity_requested  }} {{ $waitlist->unit }}</td>
                            <td>{{ $waitlist->added_by_first_name }} {{ $waitlist->added_by_last_name }}</td>
                            <td>{{ $waitlist->location_name }}</td>
                            <td>{{ $waitlist->reference }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center">No pending waitlist entries found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default no-print" data-dismiss="modal">Close</button>
        </div>
    </div>
</div>
