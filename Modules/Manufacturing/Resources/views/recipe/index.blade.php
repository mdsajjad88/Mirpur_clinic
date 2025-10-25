@extends('layouts.app')
@section('title', __('manufacturing::lang.recipe'))

@section('content')
@include('manufacturing::layouts.nav')
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('manufacturing::lang.recipe')</h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-solid'])
        @can("manufacturing.add_recipe")
        @slot('tool')
            <div class="box-tools">
                <button class="btn btn-block btn-primary btn-modal" data-container="#recipe_modal" data-href="{{action([\Modules\Manufacturing\Http\Controllers\RecipeController::class, 'create'])}}">
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
            </div>
        @endslot
        @endcan
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="recipe_table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all-row" data-table-id="recipe_table"></th>
                        <th>@lang( 'manufacturing::lang.recipe' )</th>
                        <th>@lang( 'manufacturing::lang.auto_mfg' )</th>
                        <th>@lang( 'product.category' )</th>
                        <th>@lang( 'product.sub_category' )</th>
                        <th>@lang( 'lang_v1.quantity' )</th>
                        <th>@lang( 'lang_v1.price' ) @show_tooltip(__('manufacturing::lang.price_updated_live'))</th>
                        <th>Production @lang( 'sale.unit_price' )</th>
                        <th>Current Purchase Price</th>
                        <th>@lang( 'messages.action' )</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="9">
                            <button  type="button" class="btn btn-xs btn-danger" id="mass_add_product_auto_mfg" >@lang('manufacturing::lang.mass_add_product_auto_mfg')</button> @show_tooltip(__('manufacturing::lang.mass_add_product_auto_mfg_help'))
                        <span style="margin-right: 20px;"></span>
                            <button  type="button" class="btn btn-xs btn-danger" id="mass_remove_product_auto_mfg" >@lang('manufacturing::lang.mass_remove_product_auto_mfg')</button> @show_tooltip(__('manufacturing::lang.mass_remove_product_auto_mfg_help'))
                        <span style="margin-right: 20px;"></span>
                            <button type="button" class="btn btn-xs btn-danger" id="mass_update_product_price" >@lang('manufacturing::lang.update_product_price')</button> @show_tooltip(__('manufacturing::lang.update_product_price_help'))
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade" id="recipe_modal" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
@stop
@section('javascript')
    @include('manufacturing::layouts.partials.common_script')
@endsection
