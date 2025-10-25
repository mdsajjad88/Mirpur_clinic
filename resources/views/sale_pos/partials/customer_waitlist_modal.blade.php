<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('lang_v1.customer_product_waitlist')</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                @foreach ($waitlists as $waitlist)
                    <div class="col-md-4">
                        <div class="card" style="border: none; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); border-radius: 8px; margin-bottom: 16px;">
                            <div class="card-header" style="background-color: #6c757d; color: #fff; border-top-left-radius: 8px; border-top-right-radius: 8px; padding: 12px;" 
                                 data-sku="{{ $waitlist->sku }}" 
                                 data-variation_id="{{ $waitlist->variation_id }}" 
                                 >
                                {{ $waitlist->product_name }}, SKU({{ $waitlist->sku }})
                            </div>
                            <div class="card-body" style="padding: 16px;">
                                <h6 class="card-title" style="margin-bottom: 8px;">
                                    <strong>@lang('lang_v1.quantity_requested'): </strong> {{ $waitlist->quantity_requested }} {{ $waitlist->unit }}
                                </h6>
                                <p style="margin-bottom: 8px;">
                                    <strong>@lang('lang_v1.date'): </strong> {{ Carbon::parse($waitlist->created_at)->format('d M Y, h:i A') }}
                                </p>
                                <p style="margin-bottom: 8px;">
                                    <strong>@lang('lang_v1.status'): </strong> 
                                    @php
                                        // Default color for unknown status
                                        $statusColor = 'btn-secondary';

                                        // Determine the color class based on the status
                                        switch ($waitlist->status) {
                                            case 'Pending':
                                                $statusColor = 'btn-warning'; // Yellow for pending
                                                break;
                                            case 'Available':
                                                $statusColor = 'btn-success'; // Green for available
                                                break;
                                            case 'Complete':
                                                $statusColor = 'btn-primary'; // Blue for complete
                                                break;
                                        }
                                    @endphp
                                    <span class="btn {{ $statusColor }} btn-sm" style="padding: 2px 8px; cursor: default;">
                                        {{ $waitlist->status }}
                                    </span>
                                </p>
                                <p style="margin-bottom: 8px;">
                                    <strong>@lang('lang_v1.call_status'): </strong> 
                                    @lang('lang_v1.' . $waitlist->call_status)
                                </p>
                                <p style="margin-bottom: 16px;">
                                    <strong>@lang('lang_v1.notes'): </strong> {{ $waitlist->notes }}
                                </p>

                                <!-- Delete Button -->
                                <button type="button" class="btn btn-danger btn-sm delete-waitlist"
                                        data-id="{{ $waitlist->id }}" data-toggle="tooltip" title="@lang('messages.delete')">
                                    <i class="fa fa-trash"></i> @lang('messages.delete')
                                </button>

                                <!-- Add to Cart Button -->
                                @if ($waitlist->status == 'Available')
                                <button type="button" class="btn btn-primary btn-sm add-to-cart"
                                        data-id="{{ $waitlist->id }}"
                                        data-sku="{{ $waitlist->sku }}"
                                        data-product_name="{{ $waitlist->product_name }}"
                                        data-quantity="{{ $waitlist->quantity_requested }}"
                                        data-variation_id="{{ $waitlist->variation_id }}" 
                                        data-toggle="tooltip" title="@lang('messages.add_to_cart')">
                                    <i class="fa fa-cart-plus"></i> @lang('messages.add_to_cart')
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.cancel')</button>
        </div>
    </div>
</div>
