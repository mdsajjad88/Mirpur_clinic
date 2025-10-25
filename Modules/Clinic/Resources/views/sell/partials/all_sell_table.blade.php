<table class="table table-bordered table-striped ajax_view" id="clinic_sell_table">
    <thead>
        <tr>
            <th>@lang('messages.action')</th>
            <th>@lang('messages.date')</th>
            <th>@lang('sale.invoice_no')</th>
            <th>@lang('clinic::lang.patient_name')</th>
            <th>@lang('lang_v1.contact_no')</th>
            <th>@lang('sale.location')</th>
            <th>@lang('clinic::lang.type')</th>
            <th>@lang('lang_v1.payment_method')</th>
            <th>@lang('sale.payment_status')</th>
            <th>@lang('sale.total_amount')</th>
            <th>@lang('sale.total_paid')</th>
            <th>@lang('clinic::lang.bill_due')</th>
            <th>@lang('Campaign Discount')</th>
            <th>@lang('Special Discount')</th>
            {{-- <th>@lang('Sell Return (total amount)')</th> --}}
            <th><small>@lang('clinic::lang.bill_refund_and_total')</small></th>
            <th>@lang('lang_v1.shipping_status')</th>
            <th>@lang('lang_v1.total_items')</th>
            <th>@lang('lang_v1.types_of_service')</th>
            <th>{{ $custom_labels['types_of_service']['custom_field_1'] ?? __('lang_v1.service_custom_field_1' )}}</th>
            <th>{{ $custom_labels['sell']['custom_field_1'] ?? '' }}</th>
            <th>{{ $custom_labels['sell']['custom_field_2'] ?? ''}}</th>
            <th>{{ $custom_labels['sell']['custom_field_3'] ?? ''}}</th>
            <th>{{ $custom_labels['sell']['custom_field_4'] ?? ''}}</th>
            <th>@lang('lang_v1.added_by')</th>
            <th>@lang('clinic::lang.bill_note')</th>
            <th>@lang('sale.staff_note')</th>
            <th>@lang('sale.shipping_details')</th>
            <th>@lang('restaurant.table')</th>
            <th>@lang('restaurant.service_staff')</th>
            <th>Reference</th>
        </tr>
    </thead>
    <tbody></tbody>
    <tfoot>
        <tr style="font-size: 12px" class="bg-gray footer-total text-left">
            <td class="text-center" colspan="5"><strong>@lang('sale.total'):</strong></td>
            <td colspan="2" class="payment_method_count"></td>
            <td class="footer_payment_status_count"></td>
            <td class="footer_sale_total"></td>
            <td class="footer_total_paid"></td>
            <td class="footer_total_remaining"></td>
            <td class="footer_total_campaign_discount"></td>
            <td class="footer_total_special_discount"></td>
            <td class="footer_total_sell_return_due"></td>
            <td colspan="15"></td>
        </tr>
    </tfoot>
</table> 