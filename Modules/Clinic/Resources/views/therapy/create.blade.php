@extends('clinic::layouts.app2')
@section('title', __('Add New Test'))
@section('content')
    <section class="content">
        @php
            $form_class = empty($duplicate_product) ? 'create' : '';
            $is_image_required = !empty($common_settings['is_product_image_required']);
            $clinic_location = session('clinic_location');
        @endphp
        {!! Form::open([
            'url' => action([\App\Http\Controllers\ProductController::class, 'store']),
            'method' => 'post',
            'id' => 'product_add_form',
            'class' => 'product_form ' . $form_class,
            'files' => true,
        ]) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">
                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('name', __('clinic::lang.therapy_name') . ':*') !!}
                        {!! Form::text('name', !empty($duplicate_product->name) ? $duplicate_product->name : null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => __('clinic::test.name'),
                        ]) !!}
                    </div>
                </div>
            
                <div class="col-sm-4 @if (!session('business.enable_category')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('category_id', __('product.category') . ':*') !!}
                        {!! Form::select(
                            'category_id',
                            $categories,
                            !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null,
                            [
                                'placeholder' => __('messages.please_select'),
                                'class' => 'form-control select2',
                                'required',
                                'id' => 'category_id',
                            ],
                        ) !!}
                    </div>
                </div>
                <div class="col-sm-4 @if (!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
                    <div class="form-group">
                        {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
                        {!! Form::select(
                            'sub_category_id',
                            $sub_categories,
                            !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null,
                            ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'],
                        ) !!}
                    </div>
                </div>
                
                {{-- <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('product_locations', __('business.business_locations') . ':') !!} @show_tooltip(__('lang_v1.product_location_help'))
                        {!! Form::select('product_locations[]', $business_locations, $clinic_location, [
                            'class' => 'form-control select2',
                            'multiple',
                            'required',
                            'id' => 'product_locations',
                        ]) !!}
                    </div>
                </div> --}}
                {!! Form::hidden('product_locations[]', $clinic_location) !!}
                <div hidden class="col-sm-4 @if(!session('business.enable_brand')) hide @endif">
                    <div class="form-group">
                      {!! Form::label('brand_id', __('clinic::lang.brand') . ':*') !!}
                      <div class="input-group">
                        {!! Form::select('brand_id', $brands, null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2','required']) !!}
                        <span class="input-group-btn">
                            <button type="button" @if (!auth()->user()->can('clinic.brand.create')) disabled @endif
                                class="btn btn-default bg-white btn-flat btn-modal"
                                data-href="{{ action([\Modules\Clinic\Http\Controllers\ClinicBrandController::class, 'create']) . '?sub_type=therapy&quick_add=true' }}"
                                title="@lang('brand.add_brand')" data-container=".view_modal">
                                <i class="fa fa-plus-circle text-primary fa-lg"></i>
                            </button>

                        </span>
                      </div>
                    </div>
                  </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <br>
                        <label>
                            {!! Form::checkbox('enable_stock', 1, !empty($duplicate_product) ? $duplicate_product->enable_stock : false, [
                                'class' => 'input-icheck',
                                'id' => 'enable_stock',
                            ]) !!} <strong>@lang('product.manage_stock')</strong>
                        </label>@show_tooltip(__('tooltip.enable_stock')) <p class="help-block"><i>@lang('product.enable_stock_help')</i></p>
                    </div>
                </div>

                <div class="clearfix"></div>

                <!-- include module fields -->
                @if (!empty($pos_module_data))
                    @foreach ($pos_module_data as $key => $value)
                        @if (!empty($value['view_path']))
                            @includeIf($value['view_path'], ['view_data' => $value['view_data']])
                        @endif
                    @endforeach
                @endif
                <div class="clearfix"></div>
                <div class="col-sm-8">
                    <div class="form-group">
                        {!! Form::label('product_description', __('clinic::lang.therapy_description') . ':') !!}
                        {!! Form::textarea(
                            'product_description',
                            !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null,
                            ['class' => 'form-control'],
                        ) !!}
                    </div>
                </div>
            </div>
        @endcomponent
      {!! Form::hidden('woocommerce_disable_sync',1) !!}
      {!! Form::hidden('unit_id', 1) !!}
        @component('components.widget', ['class' => 'box-primary'])
            <div class="row">

                <div class="col-sm-4 @if (!session('business.enable_price_tax')) hide @endif">
                    <div class="form-group">
                        {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
                        {!! Form::select(
                            'tax',
                            $taxes,
                            !empty($duplicate_product->tax) ? $duplicate_product->tax : null,
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
                            !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive',
                            ['class' => 'form-control select2', 'required'],
                        ) !!}
                    </div>
                </div>

                <div class="clearfix"></div>

                <div class="col-sm-4">
                    <div class="form-group">
                        {!! Form::label('type', __('clinic::lang.therapy_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
                        {!! Form::hidden('product_type', 'therapy') !!}
                        {!! Form::select('type', $product_types, !empty($duplicate_product->type) ? $duplicate_product->type : null, [
                            'class' => 'form-control select2',
                            'required',
                            'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add',
                            'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0',
                        ]) !!}
                    </div>
                </div>

                <div class="form-group col-sm-12" id="product_form_part">
                    @include('clinic::product.single_product_form_part', [
                        'profit_percent' => $default_profit_percent,
                    ])
                </div>

                <input type="hidden" id="variation_counter" value="1">
                <input type="hidden" id="default_profit_percent" value="{{ $default_profit_percent }}">
                @if ($foreign_cat)
                    <input type="hidden" id="foreign_cat_id" value="{{ $foreign_cat->id }}">
                @endif

            </div>
        @endcomponent
        <div class="row">
            <div class="col-sm-12">
                <input type="hidden" name="submit_type" id="submit_type">
                <div class="text-center">
                    <div class="btn-group">
                        @if ($selling_price_group_count)
                            <button type="submit" value="submit_n_add_selling_prices"
                                class="btn btn-warning btn-big submit_product_form">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
                        @endif

                        <button type="submit" value="save_n_add_another"
                            class="btn bg-maroon btn-big submit_product_form">@lang('lang_v1.save_n_add_another')</button>

                        <button type="submit" value="submit"
                            class="btn btn-primary btn-big submit_product_form">@lang('messages.save')</button>
                    </div>

                </div>
            </div>
        </div>
        {!! Form::close() !!}

    </section>
    <!-- /.content -->

@endsection

@section('javascript')
    @php $asset_v = env('APP_VERSION'); @endphp
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            __page_leave_confirmation('#product_add_form');
            onScan.attachTo(document, {
                suffixKeyCodes: [13], // enter-key expected at the end of a scan
                reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
                onScan: function(sCode, iQty) {
                    $('input#sku').val(sCode);
                },
                onScanError: function(oDebug) {
                    console.log(oDebug);
                },
                minLength: 2,
                ignoreIfFocusOn: ['input', '.form-control']
                // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
                //     console.log('Pressed: ' + iKeyCode);
                // }
            });
        });
    </script>
@endsection
