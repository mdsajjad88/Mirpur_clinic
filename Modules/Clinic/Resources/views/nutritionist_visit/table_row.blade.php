@foreach ($items as $product)
    @php
        $displayName = $common_settings['show_medicine_name_as'] === 'generic'
            ? ($product->generic_name ?? '')
            : ($product->product_name ?? '');
        $productId = $product->product_id ?? $product->life_style_id;
        
        // detect prefix (food_ / lifestyle_)
        $prefix = $nameField == 'medicine_name' ? 'food_' : 'lifestyle_';
    @endphp

    <tr>
        <td>
            <input
                {{ $common_settings['show_medicine_name_as'] === 'generic' ? 'type=hidden' : 'readonly required type=text' }}
                name="{{ $nameField }}[]"
                value="{{ $displayName }}"
                class="form-control custom-input">

            {{-- hidden id for update --}}
            @if (isset($product->id))
                <input type="hidden"
                    name="{{ $nameField == 'medicine_name' ? 'nu_prescription_food_id[]' : 'nu_prescription_life_style_id[]' }}"
                    value="{{ $product->id }}">
            @endif
        </td>

        <td>
            {{-- Meal Time (editable input + autocomplete dropdown) --}}
            <input type="text" name="{{ $prefix }}meal_time[]"
                   value="{{ $product->meal_time }}"
                   class="form-control meal_time_input custom-input">
            <div class="meal_time_info"></div>
        </td>

        <td>
            {{-- Instruction (editable input + autocomplete dropdown) --}}
            <input type="text" name="{{ $prefix }}instruction[]"
                   value="{{ $product->instructions }}"
                   class="form-control instruction_input custom-input">
            <div class="instruction_info"></div>
        </td>

        <td>
            <input type="hidden" name="{{ $productIdField }}[]" value="{{ $productId }}">
            <button type="button" class="btn btn-danger btn-remove-row btn-xs">
                <i class="fa fa-times" style="font-size: 12px; cursor: pointer;"></i>
            </button>
        </td>
    </tr>
@endforeach
