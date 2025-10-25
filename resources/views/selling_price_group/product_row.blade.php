@foreach ($products as $product)
    @foreach ($product->variations as $variation)
        <tr>
            @if ($product->type == 'variable')
                <td>{{ $variation->product_variation->name }} - {{ $variation->name }} ({{ $variation->sub_sku }})</td>
            @else
                <td>
                    {{ $product->name }} ({{ $variation->sub_sku }}) 
                    {!! Form::hidden('variation_id', $variation->id, ['id' => 'variation_id']) !!}
                </td>
            @endif
            <td>
                <span class="display_currency" data-currency_symbol="true" id="base-price" data-base-price="{{ $variation->sell_price_inc_tax }}">
                    {{ number_format($variation->sell_price_inc_tax, 2) }}
                </span>
            </td>
            @foreach ($price_groups as $price_group)
                <td style="width: 200px;">
                    @php
                        // Use the provided percentage discount for final price calculation
                        $discount_percentage = $percentage_val ?? 0;
                        $final_price = $variation->sell_price_inc_tax * (1 - ($discount_percentage / 100));
                    @endphp

                    {!! Form::text(
                        'group_prices[' . $variation->id . '][price]', 
                        $discount_percentage,
                        ['class' => 'form-control input_number input-sm group-price-input', 'data-variation-id' => $variation->id, 'data-price-group-id' => $price_group->id]
                    ) !!}

                    <select name="group_prices[{{ $variation->id }}][price_type]" class="form-control group-price-type" data-variation-id="{{ $variation->id }}" data-price-group-id="{{ $price_group->id }}">
                        <option value="percentage" selected>@lang('lang_v1.percentage')</option>
                    </select>
                </td>
            @endforeach
            <td>
                <span class="final-price" data-currency_symbol="true">{{ number_format($final_price, 2) }}</span>
            </td>
            <td class="text-center">
                <i class="fa fa-trash remove_product_row cursor-pointer" aria-hidden="true"></i>
            </td>
        </tr>
    @endforeach
@endforeach
