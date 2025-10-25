{{-- expiry_date/expiry_content.blade.php --}}
@foreach($products as $product)
    <tr>
        <td>{{ $product->product_name }}</td>
        <td>{{ $product->sku }}</td>
        <td>{{ $product->brand_name }}</td>
        <td>{{ $product->category_name }}</td>
        <td>
            <input type="text" class="form-control" value="{{ $product->lot_number }}" name="lot_no" placeholder="auto generate">
        </td>
        <td>
            <div class="input-group">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                <input type="text" class="form-control expiry_datepicker exp_date" name="expiry_date" value="{{ $product->exp_date }}">
            </div>
        </td>
        <td>
            <input type="hidden" name="product_id" value="{{ $product->product_id }}">
            <input type="hidden" name="transaction_id" value="{{ $product->transaction_id }}">
            <button type="button" class="btn btn-danger btn-remove-row btn-sm">
                <i class="fa fa-times" style="font-size: 12px; cursor: pointer;"></i>
            </button>
        </td>
    </tr>
@endforeach
