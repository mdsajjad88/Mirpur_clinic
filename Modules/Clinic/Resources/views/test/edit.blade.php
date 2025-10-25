@extends('clinic::layouts.app2')
@section('title', __('Edit Test Info'))
@section('content')

    @php
        $is_image_required = !empty($common_settings['is_product_image_required']) && empty($product->image);
    @endphp

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('clinic::lang.edit_test')</h1>
        <!-- <ol class="breadcrumb">
                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                    <li class="active">Here</li>
                </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\TestController::class, 'update'], [$product->id]),
            'method' => 'PUT',
            'id' => 'product_add_form',
            'class' => 'product_form',
            'files' => true,
        ]) !!}
        <input type="hidden" id="product_id" value="{{ $product->id }}">

        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('name', __('clinic::test.name') . ':*') !!}
                        {!! Form::text('name', $product->name, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('product.product_name'),
                        ]) !!}
                    </div>
                </div>

                <div hidden class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('sku', __('product.sku') . ':*') !!} @show_tooltip(__('tooltip.sku'))
                        {!! Form::text('sku', $product->sku, [
                            'class' => 'form-control',
                            'placeholder' => __('product.sku'),
                            'required',
                        ]) !!}
                    </div>
                </div>

                <div hidden class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
                        {!! Form::select('barcode_type', $barcode_types, $product->barcode_type, [
                            'placeholder' => __('messages.please_select'),
                            'class' => 'form-control select2',
                            'required',
                        ]) !!}
                    </div>
                </div>





                <div class="col-sm-4 @if (!session('business.enable_category')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('category_id', __('product.category') . ':*') !!}
                        {!! Form::select('category_id', $categories, $product->category_id, [
                            'placeholder' => __('messages.please_select'),
                            'class' => 'form-control select2',
                            'required',
                        ]) !!}
                    </div>
                </div>

                <div class="col-sm-4 @if (!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
                    <div class="form-group">
                        {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                        {!! Form::select('sub_category_id', $sub_categories, $product->sub_category_id, [
                            'placeholder' => __('messages.please_select'),
                            'class' => 'form-control select2',
                        ]) !!}
                    </div>
                </div>



                <div hidden class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                        {!! Form::select('product_locations[]', $business_locations, $product->product_locations->pluck('id'), [
                            'class' => 'form-control select2',
                            'multiple',
                            'required',
                            'id' => 'product_locations',
                        ]) !!}
                    </div>
                </div>
                <div class="col-sm-4 @if (!session('business.enable_brand')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('brand_id', __('clinic::lang.brand') . ':*') !!}
                        <div class="input-group">
                            {!! Form::select('brand_id', $brands, $product->brand_id, [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                                'required',
                            ]) !!}
                            <span class="input-group-btn">
                                <button type="button" @if (!auth()->user()->can('brand.create')) disabled @endif
                                    class="btn btn-default bg-white btn-flat btn-modal"
                                    data-href="{{ action([\App\Http\Controllers\BrandController::class, 'create'], ['quick_add' => true]) }}"
                                    title="@lang('brand.add_brand')" data-container=".view_modal"><i
                                        class="fa fa-plus-circle text-primary fa-lg"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="clearfix"></div>


                <div class="clearfix"></div>
                <div class="col-sm-7">
                    <div class="form-group">
                        {!! Form::label('product_description', __('clinic::test.description') . ':') !!}
                        {!! Form::textarea('product_description', $product->product_description, ['class' => 'form-control']) !!}
                    </div>
                </div>
                <div class="col-sm-5">
                    <div class="form-group">
                        {!! Form::label('parameter', __('product.parameter') . ':') !!}
                        <table class="table table-bordered table-striped" id="parameter_heading_table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Reference</th>
                                    <th>Unit</th>
                                    <th>Description</th>
                                    <th>
                                        <button type="button" class="btn btn-primary btn-xs" id="add_parameter_row">
                                            <i class="fa fa-plus"></i>
                                        </button>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($product->parameters as $param)
                                    <tr data-id="{{ $param->id }}">
                                        <td>
                                            <input type="text" name="parameter_name[]" value="{{ $param->name }}"
                                                class="form-control" placeholder="Parameter Name" required />
                                            <input type="hidden" name="parameter_id[]" value="{{ $param->id }}">
                                        </td>
                                        <td><input type="number" name="reference_value[]" class="form-control"
                                                placeholder="Reference Value" value="{{ $param->reference_value }}" required /></td>
                                        <td><input type="text" name="unit[]" value="{{ $param->unit }}" class="form-control" placeholder="Unit"
                                                required /></td>
                                        <td><input type="text" name="parameter_description[]"
                                                value="{{ $param->description }}" class="form-control"
                                                placeholder="Parameter Description" /></td>
                                        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i
                                                    class="fa fa-trash"></i></button></td>
                                    </tr>
                                @endforeach
                                <input type="hidden" name="removed_parameter_ids" id="removed_parameter_ids">
                            </tbody>
                        </table>
                    </div>
                </div>
            @endcomponent
            {!! Form::hidden('woocommerce_disable_sync', 1) !!}
            {!! Form::hidden('enable_stock', 0) !!}
            {!! Form::hidden('unit_id', 1) !!}
            {!! Form::hidden('alert_quantity', 00) !!}
            {!! Form::hidden('weight', 00) !!}
            {!! Form::hidden('preparation_time_in_minutes', 00) !!}

            @component('components.widget', ['class' => 'box-primary'])
                <div class="row">
                    <div class="col-sm-4 @if (!session('business.enable_price_tax')) hide @endif">
                        <div class="form-group">
                            {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
                            {!! Form::select(
                                'tax',
                                $taxes,
                                $product->tax,
                                ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                                $tax_attributes,
                            ) !!}
                        </div>
                    </div>

                    <div class="col-sm-4 @if (!session('business.enable_price_tax')) hide @endif">
                        <div class="form-group">
                            {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
                            {!! Form::select(
                                'tax_type',
                                ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')],
                                $product->tax_type,
                                ['class' => 'form-control select2', 'required'],
                            ) !!}
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('type', __('product.product_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
                            {!! Form::select('type', $product_types, $product->type, [
                                'class' => 'form-control select2',
                                'required',
                                'disabled',
                                'data-action' => 'edit',
                                'data-product_id' => $product->id,
                            ]) !!}
                        </div>
                    </div>

                    <div class="form-group col-sm-12 @if(!auth()->user()->can('test.price.section.show')) hide @endif " id="product_form_part"></div>
                    <input type="hidden" id="variation_counter" value="0">
                    <input type="hidden" id="default_profit_percent" value="{{ $default_profit_percent }}">
                </div>
            @endcomponent

            <div class="row">
                <input type="hidden" name="submit_type" id="submit_type">
                <div class="col-sm-12">
                    <div class="text-center">
                        <div class="btn-group">
                            @if ($selling_price_group_count)
                                <button type="submit" value="submit_n_add_selling_prices"
                                    class="btn btn-warning btn-big submit_product_form">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
                            @endif
                            <button type="submit" value="save_n_add_another"
                                class="btn bg-maroon submit_product_form btn-big">@lang('lang_v1.update_n_add_another')</button>

                            <button type="submit" value="submit"
                                class="btn btn-primary submit_product_form btn-big">@lang('messages.update')</button>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            __page_leave_confirmation('#product_add_form');
        });
    </script>
@endsection
