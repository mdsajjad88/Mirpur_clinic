<table style="width: 100%" class="table table-bordered table-striped ajax_view hide-footer" id="therapy_sell_table">
    <thead>
        <tr>
            <th class="service_t_or_c">@lang('clinic::lang.service_name')</th>
            <th>@lang('clinic::lang.selections')</th>
            <th>@lang('product.sku')</th>
            <th>@lang('clinic::lang.category')</th>
            <th>@lang('clinic::lang.brand')</th>
            <th>@lang('clinic::lang.total_service')</th>
            <th>@lang('clinic::lang.total_selection')</th>
            <th>@lang('clinic::lang.total_amount')</th>
        </tr>
    </thead>
    <tfoot>
        <tr class="bg-gray font-17 footer-total text-center">
            <td colspan="4"><strong>@lang('sale.total'):</strong></td>
            <td id="footer_total_today_sold"></td>
            <td id="footer_today_total_qty"></td>
            <td id="footer_today_total_modifier_qty"></td>
            <td><span class="display_currency" id="footer_today_subtotal" data-currency_symbol="true"></span></td>
        </tr>
    </tfoot>
</table>