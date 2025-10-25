<div class="modal-dialog modal-lg no-print" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title" id="modalTitle">
                Product: {{ $product_name }} and Selection: {{ $modifier_set_names_string }}
            </h3>
        </div>
        <div class="modal-body">
            @if ($product_ms->isNotEmpty())
            @foreach ($product_ms as $index => $set)
            <table class="table table-bordered table-striped" id="therapy_selection_report_details_table_{{ $index }}">
                        <thead>
                            <tr>
                                <th>Variation Name</th>
                                <th>Total Quantity</th>
                                <th>Unit Price</th>
                                <th>Total Discount</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($set->variations as $variation)
                                @php
                                    // Fetch the totals for the current variation from the $variation_totals array
                                    $totals = $variation_totals[$variation->id] ?? null;
                                @endphp
                                <tr>
                                    <td>{{ $variation->name }}</td>
                                    <td>{{ $totals['total_quantity'] ?? 0 }}</td>
                                    <td>{{ number_format($totals['total_unit_price'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($totals['total_discount'] ?? 0, 2) }}</td>
                                    <td>{{ number_format($totals['total_amount'] ?? 0, 2) }}</td>
                                </tr>
                            @endforeach

                        </tbody>

                    </table>
                @endforeach
            @else
                <p class="text-muted">No modifier sets found for this therapy product.</p>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary no-print" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
<script>
    // Initialize DataTables dynamically for each table
    $(document).ready(function() {
        @foreach ($product_ms as $index => $set)
            $('#therapy_selection_report_details_table_{{ $index }}').DataTable({
                paging: true,
                searching: false,
                info: true,
                lengthChange: false,
                pageLength: 25,
                dom: 'Bfrtip',
            });
        @endforeach
    });
</script>
