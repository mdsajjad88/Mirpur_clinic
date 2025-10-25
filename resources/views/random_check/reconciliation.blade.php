@extends('layouts.app')

@section('title', __('reconciliation'))
@section('content')
<!-- Content Header (Page header) -->
<section class="content-header" style="display: flex; justify-content: space-between; align-items: center;">
    <h1>@lang('Today Reconciliation Check')
        {{-- <small><strong>Location: </strong>{{ $location ? $location->name : 'No Location' }}</small> --}}
    </h1>
    <button type="button" class="btn btn-primary" onclick="location.reload();">
        <i class="fas fa-sync-alt"></i> @lang('Refresh')
    </button>
</section>


<section class="content">
    @component('components.widget')
    {!! Form::open([
        'url' => action([\App\Http\Controllers\CheckController::class, 'reconciliationStore']),
        'method' => 'post',
    ]) !!}
    @csrf

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>SKU</th>
                    <th>Product Name</th>
                    <th>Current Stock</th>
                    <th>Qty Deducted</th>
                    <th style="width: 10%;">Phy. Count Diff.</th>
                    <th style="width: 25%;">Comment</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $id => $product)
                    <tr>
                        <td>{{ !empty($product['category_name']) ? $product['category_name'] : 'Uncategorized' }}</td>
                        <td>{{ $product['sku'] }}</td>
                        <td>
                            {!! Form::hidden("products[{$id}][product_id]", $product['id']) !!}
                            {!! Form::hidden("products[{$id}][current_stock]", $product['current_stock'])!!}
                            {!! Form::hidden("products[{$id}][product]", $product['product'])!!}
                            {!! Form::hidden("products[{$id}][brand]", $product['brand'])!!}
                            {!! Form::hidden("products[{$id}][category_name]", $product['category_name'])!!}
                            {!! Form::hidden("products[{$id}][sku]", $product['sku'])!!}
                            {!! Form::hidden("products[{$id}][location_id]", $product['location_id'])!!}
                            {{ $product['product'] }}
                        </td>
                        <td>{{ number_format($product['current_stock'],2) }} {{ $product['unit_name'] }}</td>
                        <td>{{ number_format($product['quantity_deducted'],2) }} {{ $product['unit_name'] }}</td>
                        <td>
                            <div class="input-group input-number">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-flat quantity-down-int" data-index="{{ $id }}">
                                        <i class="fa fa-minus text-danger"></i>
                                    </button>
                                </span>
                                {!! Form::number("products[{$id}][physical_count]", 0, [
                                    'class' => 'form-control input_number',
                                    'required' => true,
                                    'id' => "physical_count_{$id}",
                                    'data-id' => $id
                                ]) !!}
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default btn-flat quantity-up-int" data-index="{{ $id }}">
                                        <i class="fa fa-plus text-success"></i>
                                    </button>
                                </span>
                            </div>
                            <div style="text-align: center;">
                                <small id="physical_count_text_{{ $id }}" class="form-text"></small>
                            </div>
                        </td>
                        <td>
                            <span class="comment-placeholder" style="cursor: pointer; color: blue;">Click to add comment...</span>
                            {!! Form::textarea("products[{$id}][comment]", null, [
                                'class' => 'form-control comment-textarea hide',
                                'rows' => 2,
                                'placeholder' => 'Comments...'
                            ]) !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="col-md-7">
            <ul>
                <li><strong>Checked by: </strong>{{ Auth::user()->first_name }} {{ Auth::user()->last_name }}</li>
                <li><strong>Checked at: </strong>{{ now()->format('d F Y, g:i A') }}</li>
            </ul>
        </div>
        <div class="col-md-5">
            {!! Form::textarea("comment", null, [
                'class' => 'form-control',
                'rows' => 3,
                'placeholder' => 'Overall comments...'
            ]) !!}
        </div>
    </div>

    {!! Form::submit('Save', [
        'class' => 'btn btn-primary',
        'style' => 'display: block; width: 160px; height: 50px; margin: 0 auto; margin-top: 10px; font-size: 18px;',
    ]) !!}
    {!! Form::close() !!}
    @endcomponent
</section>
@endsection

@section('javascript')
<script>
    $(document).ready(function() {

        function updatePhysicalCountText(id, value) {
            const textElement = $(`#physical_count_text_${id}`);
            if (value === 0) {
                textElement.text('0 (match)');
            } else if (value < 0) {
                textElement.text(`${value} (missing)`);
            } else if (value > 0) {
                textElement.text(`+${value} (surplus)`);
            }
        }

        $('.quantity-down-int').on('click', function() {
            const index = $(this).data('index');
            const input = $(`#physical_count_${index}`);
            let value = parseInt(input.val()) || 0; // Ensure value is an integer
            input.val(value - 1);
            updatePhysicalCountText(index, parseInt(input.val()));
        });

        $('.quantity-up-int').on('click', function() {
            const index = $(this).data('index');
            const input = $(`#physical_count_${index}`);
            let value = parseInt(input.val()) || 0; // Ensure value is an integer
            input.val(value + 1);
            updatePhysicalCountText(index, parseInt(input.val()));
        });

        // Initialize text elements
        $('.input_number').each(function() {
            const id = $(this).data('id');
            const value = parseInt($(this).val()) || 0; // Ensure value is an integer
            updatePhysicalCountText(id, value);
        });

        $('.comment-placeholder').on('click', function() {
            $(this).hide();
            $(this).next('.comment-textarea').removeClass('hide').focus();
        });
    });
</script>
@endsection
