<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title text-center">
                @if (!empty($source))
                    Source: {{ $source->name }}
                @endif Show Details
            </h4>
        </div>
        <div class="modal-body">

            <h4>Contact: {{ $contact->first_name }} {{ $contact->last_name }}</h4>

            @if ($transactions->isEmpty())
                <p>No transactions found.</p>
            @else
                <table class="table table-bordered">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Total Amount</th>
                        <th>Total Paid</th>
                        <th>Total Due</th>
                        <th>Status</th>
                    </tr>
                    @foreach ($transactions as $tx)
                        @foreach ($tx->payment_lines as $pl)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($tx->transaction_date)->format('d-m-Y') }}</td>
                                <td>{{ ucfirst($tx->sub_type) }}
                                    @if ($tx->sub_type == 'therapy' && $tx->sell_lines->isNotEmpty())
                                        <ul class="mb-0">
                                            @foreach ($tx->sell_lines as $line)
                                                <li>{{ $line->product->name ?? 'N/A' }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                                <td>{{ number_format($tx->final_total, 2) }}</td>
                                <td>{{ number_format($pl->amount, 2) }}</td>
                                <td>{{ number_format($tx->final_total - $pl->amount, 2) }}</td>
                                <td>{{ ucfirst($tx->payment_status) }}</td>
                            </tr>
                        @endforeach
                    @endforeach
                    @php
                        $subtype_data = $transactions->groupBy('sub_type')->map(function ($group) {
                            $total_final = $group->sum('final_total');
                            $total_paid = $group->pluck('payment_lines')->flatten()->sum('amount');
                            $total_due = $total_final - $total_paid;
                            $count = $group->count();

                            // যদি therapy হয়, sell_lines অনুযায়ী count বের করা
                            $therapy_counts = [];
                            if ($group->first()->sub_type == 'therapy') {
                                foreach ($group as $tx) {
                                    foreach ($tx->sell_lines as $line) {
                                        $product_name = $line->product->name ?? 'N/A';
                                        if (!isset($therapy_counts[$product_name])) {
                                            $therapy_counts[$product_name] = 0;
                                        }
                                        $therapy_counts[$product_name]++;
                                    }
                                }
                            }

                            return [
                                'final_total' => $total_final,
                                'paid' => $total_paid,
                                'due' => $total_due,
                                'count' => $count,
                                'therapy_counts' => $therapy_counts,
                            ];
                        });
                    @endphp

                    @foreach ($subtype_data as $sub_type => $data)
                        <tr class="bg-gray font-17 text-center footer-total">
                            <td colspan="2">
                                <strong>{{ ucfirst($sub_type) }} ({{ $data['count'] }})</strong>
                                @if ($sub_type == 'therapy' && !empty($data['therapy_counts']))
                                    <ul class="mb-0" style="text-align:left;">
                                        @foreach ($data['therapy_counts'] as $therapy_name => $qty)
                                            <li>{{ $therapy_name }} : {{ $qty }} time(s)</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </td>
                            <td>৳{{ number_format($data['final_total'], 2) }}</td>
                            <td>৳{{ number_format($data['paid'], 2) }}</td>
                            <td>৳{{ number_format($data['due'], 2) }}</td>
                            <td></td>
                        </tr>
                    @endforeach

                    <tr class="bg-gray font-17 text-center footer-total">
                        <td colspan="2"><strong>Total:</strong></td>
                        <td>৳{{ number_format($total_final, 2) }}</td>
                        <td>৳{{ number_format($total_paid, 2) }}</td>
                        <td>৳{{ number_format($total_due, 2) }}</td>
                        <td></td>
                    </tr>

                </table>
            @endif


        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
