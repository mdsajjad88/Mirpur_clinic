<table style="width: 100%;" class="table table-bordered table-striped ajax_view hide-footer" id="today_payment_table">
    <thead >
        <tr>
            <th>Invoice No</th>
            <th>Payment Date</th>
            <th>Bill Date</th>
            <th>Patient</th>
            <th>Bill Type</th>
            <th>Payment Method</th>
            <th>Total Items</th>
            <th>Total Amount</th>
            <th>Total Paid</th>
            <th>Total Payment</th>
            <th>Payment Status</th>
            <th>Received By</th>
        </tr>
    </thead>
    <tfoot>
        <tr class="bg-gray font-17 footer-total text-center">
            
            <td colspan="7"></td>
            <td colspan="2"><strong>@lang('sale.total'):</strong></td>

            <td id="footer_total_today_pay"></td>
            <td colspan="2"></td>
        </tr>
    </tfoot>
</table>