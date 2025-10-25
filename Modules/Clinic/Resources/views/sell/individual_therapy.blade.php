@extends('clinic::layouts.app2')
@section('title', __('clinic::lang.individual_therapy_report'))

@section('content')
<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>@lang('clinic::lang.individual_therapy_report')</h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('product_id', __('Therapy') . ':') !!}
                {!! Form::select('product_id', $therapy_product, null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                ]) !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('variation_id', __('Selections') . ':') !!}
                {!! Form::select('variation_id', $selections, null, [
                    'class' => 'form-control select2',
                    'style' => 'width:100%',
                    'placeholder' => __('lang_v1.all'),
                ]) !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('created_by',  __('report.user') . ':') !!}
                {!! Form::select('created_by', $sales_representative, null, ['class' => 'form-control select2', 'style' => 'width:100%']) !!}
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('sell_list_filter_date_range', __('report.date_range') . ':') !!}
                {!! Form::text('sell_list_filter_date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'readonly']) !!}
            </div>
        </div>
    @endcomponent
    @component('components.widget', ['class' => 'box-primary'])
        <table style="width: 100%" id="session-details-table" class="table table-bordered table-striped ajax_view hide-footer">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Invoice No</th>
                    <th>Patient</th>
                    <th>S.No.</th>
                    <th>Therapy</th>
                    <th>Selections Taken</th>
                    <th>Campaign Discount</th>
                    <th>Special  Discount</th>
                    <th>Amount</th>
                    <th>Payment Status</th>
                    <th>Payment Method</th>
                    <th>User</th>
                    <th>Remarks</th>
                </tr>
            </thead>
            <tfoot>
                <tr class="bg-gray font-14 text-center footer-total">
                    <td colspan="6"><strong>@lang('sale.total'):</strong></td>
                    <td class="footer_total_line_discount"></td>
                    <td class="footer_total_discount"></td>
                    <td class="footer_final_total"></td>
                    <td colspan="2" class="payment_method_count"></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot> 
        </table>
    @endcomponent
    <div class="modal fade payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

    <div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>
</section>

<!-- /.content -->
@stop

@section('javascript')
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
<script>
$(document).ready(function() {
    //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function(start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        table.ajax.reload();
    });

    // Initialize DataTable
    var table = $('#session-details-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/clinic/session-details-report',
            data: function(d) {
                d.category_id = $('#product_list_filter_category_id').val();
                d.start_date = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                d.end_date = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                d.product_id = $('#product_id').val();
                d.variation_id = $('#variation_id').val();
                d.created_by = $('#created_by').val();
            }
        },
        columns: [
            { data: 'transaction_date', name: 'transaction_date' },
            { data: 'invoice_no', name: 'invoice_no' },
            { data: 'customer_name', name: 'contacts.name' },
            { data: 'session_no', name: 'sd.session_no' },
            { data: 'product_name', name: 'p.name' },
            { data: 'variation_names', name: 'v.name' },
            { data: 'total_line_discount', name: 'total_line_discount', searchable: false },
            { data: 'total_discount', name: 'total_discount', searchable: false },
            { data: 'final_total', name: 'final_total', searchable: false },
            { data: 'payment_status', name: 'payment_status', searchable: false },
            { data: 'payment_methods', name: 'payment_methods', searchable: false },
            { data: 'added_by', name: 'added_by' },
            { data: 'additional_notes', name: 'additional_notes' }
        ],
        "footerCallback": function ( row, data, start, end, display ) {
            var total_line_discount = 0;
            var total_discount = 0;
            var final_total = 0;
            // Initialize an object to store payment method counts and subtotals
            var paymentMethodTotals = {};
            for (var r in data){
                total_line_discount += $(data[r].total_line_discount).data('orig-value') ? 
                parseFloat($(data[r].total_line_discount).data('orig-value')) : 0;

                total_discount += $(data[r].total_discount).data('orig-value') ? 
                parseFloat($(data[r].total_discount).data('orig-value')) : 0;

                final_total += $(data[r].final_total).data('orig-value') ? 
                parseFloat($(data[r].final_total).data('orig-value')) : 0;

                // Count payment methods and calculate subtotal based on payment methods
                var paymentMethods = $(data[r].payment_methods).text().split(',');
                for (var i = 0; i < paymentMethods.length; i++) {
                    var paymentMethod = paymentMethods[i].trim();
                    if (!paymentMethodTotals.hasOwnProperty(paymentMethod)) {
                        paymentMethodTotals[paymentMethod] = {
                            count: 0,
                            subtotal: 0
                        };
                    }
                    paymentMethodTotals[paymentMethod].count++;
                    paymentMethodTotals[paymentMethod].subtotal += parseFloat($(data[r]
                        .final_total).data('orig-value'));
                }
            }
            // Render payment method counts and subtotals
            var paymentMethodHtml = '';
            for (var method in paymentMethodTotals) {
                paymentMethodHtml += method + '(' + paymentMethodTotals[method].count + ')-' +
                    __currency_trans_from_en(paymentMethodTotals[method].subtotal) + '<br>';
            }

            $('.footer_total_line_discount').html(__currency_trans_from_en(total_line_discount));
            $('.footer_total_discount').html(__currency_trans_from_en(total_discount));
            $('.footer_final_total').html(__currency_trans_from_en(final_total));
            $('.payment_method_count').html(paymentMethodHtml);
        },
    });

    // Event listener for Therapy dropdown change
    //     $('#product_id').change(function() {
    //     var productId = $(this).val();
    //     var variationDropdown = $('#variation_id');

    //     if (productId) {
    //         // Fetch variations based on the selected product
    //         $.ajax({
    //             url: '/get-variations-by-product',
    //             method: 'GET',
    //             data: { product_id: productId },
    //             success: function(response) {
    //                 // Clear existing options
    //                 variationDropdown.empty();
    //                 variationDropdown.append('<option value="">' + __('lang_v1.all') + '</option>');

    //                 // Populate new options
    //                 $.each(response, function(key, value) {
    //                     variationDropdown.append('<option value="' + key + '">' + value + '</option>');
    //                 });

    //                 // Refresh the Select2 dropdown
    //                 variationDropdown.trigger('change');
    //             },
    //             error: function(xhr) {
    //                 console.error('Error fetching variations:', xhr);
    //             }
    //         });
    //     } else {
    //         // If no product is selected, reset the variations dropdown
    //         variationDropdown.empty();
    //         variationDropdown.append('<option value="">' + __('lang_v1.all') + '</option>');
    //         variationDropdown.trigger('change');
    //     }
    // });

    // Reload the table when filters change
    $('#sell_list_filter_customer_id, #sell_list_filter_date_range, #product_id, #variation_id, #created_by, #product_list_filter_category_id').change(function() {
        table.ajax.reload();
    });
});
</script>
@endsection