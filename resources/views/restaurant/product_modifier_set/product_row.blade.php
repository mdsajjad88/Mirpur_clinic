<tr>
	<td>{{$product->name}} ({{$product->sku}})</td>
	<td>
        <input type="number" name="modifier_limits[{{ $product->id }}]" class="form-control" value="1" min="0" placeholder="@lang('restaurant.modifier_limit')">
    </td>
	<input type="hidden" name="products[]" value="{{$product->id}}">
	<td><button type="button" class="btn btn-danger btn-xs remove_modifier_product"><i class="fa fa-times"></i></button></td>
</tr>