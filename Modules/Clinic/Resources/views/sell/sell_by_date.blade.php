<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title" id="modalTitle">
                @lang('sale.sell_details') by {{ $product->name }} -- ({{ $start_date }} to {{ $end_date }})
            </h4>
        </div>
        <div class="modal-body" style="overflow-x:auto;">
            <table class="table table-bordered table-striped table-sm" id="sell_by_date_table"
                style="width:100% !important;">
                <thead style="width: 100% !important">
                    <tr>
                        <th id="tableHeaderInvoice">Invoice No</th>
                        <th>Customer Name</th>
                        <th>Quantity</th>
                        <th>Unit Price (Inc Tax)</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sellData as $row)
                        <tr>
                            <td>{{ $row->invoice_no }}</td>
                            <td>{{ $row->customer_name }}</td>
                            <td>{{ $row->quantity }}</td>
                            <td>{{ number_format($row->unit_price_inc_tax, 2) }}</td>
                            <td>{{ number_format($row->quantity * $row->unit_price_inc_tax, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="width: 100% !important">
                    <tr>
                        <th colspan="2">Total</th>
                        <th>{{ $totalQty }}</th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function() {
        
    const table = $('#sell_by_date_table').DataTable({
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        lengthChange: true,
        pageLength: 25,
        dom: 'Bfltip',
        scrollX: true,
        responsive: true,
        autoWidth: false,
        footerCallback: function(row, data, start, end, display) {
            const api = this.api();
            const parseValue = i => typeof i === 'string' ?
                i.replace(/[\$,]/g, '') * 1 :
                typeof i === 'number' ?
                i : 0;

            const total = api.column(4).data().reduce((a, b) => parseValue(a) + parseValue(b), 0);
            const qty = api.column(2).data().reduce((a, b) => parseValue(a) + parseValue(b), 0);
            const pageTotal = api.column(4, {
                page: 'current'
            }).data().reduce((a, b) => parseValue(a) + parseValue(b), 0);
            const qtyTotal = api.column(2, {
                page: 'current'
            }).data().reduce((a, b) => parseValue(a) + parseValue(b), 0);

            $(api.column(4).footer()).html(
                pageTotal.toFixed(2) + ' (All: ' + total.toFixed(2) + ')'
            );
            $(api.column(2).footer()).html(
                qtyTotal.toFixed(2) + ' (All: ' + qty.toFixed(2) + ')'
            );
        },
        initComplete: function() {
            $(this.api().table().container()).css('width', '100%');
        }
    });

    setTimeout(() => {
    table.columns.adjust().responsive.recalc();
    }, 300);


    });
</script>
