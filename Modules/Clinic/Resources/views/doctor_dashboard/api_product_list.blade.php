@extends('clinic::layouts.app2')
@section('title', 'Medicine List')
@section('content')
    <div class="container-fluid">
        @component('components.filters', ['title'=>'Filters', 'class'=>'box-primary'])
            <div class="row">
                <div class="col-md-3">
                    {!! Form::label('Category', 'Category:') !!}
                    {!! Form::select('category_id', ['' => 'All'] + $categories, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_category',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('brand', 'Brand:') !!}
                    {!! Form::select('brand_id', ['' => 'All'] + $brands, null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_brand',
                    ]) !!}
                </div>
                <div class="col-md-3">
                    {!! Form::label('stock', 'Stock:') !!}
                    {!! Form::select('stock', [2 => 'All', 1 => 'In Stock', 0 => 'Out of Stock'], null, [
                        'class' => 'form-control select2',
                        'style' => 'width:100%;',
                        'id' => 'filter_with_stock',
                    ]) !!}
                </div>
            </div>
        @endcomponent
        <div class="row">
            <div class="col">
                @component('components.widget', ['title' => 'Medicine List', 'class' => 'box-primary'])
                    <table class="table table-striped" id="api_product_list_table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Brand</th>
                                <th>Name</th>
                                <th>Stock</th>
                                <th>Selling Price</th>
                                <th>SKU</th>
                                <th>Type</th>

                            </tr>
                        </thead>
                    </table>
                @endcomponent

            </div>
        </div>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            var api_product_list_table = $('#api_product_list_table').DataTable({
                processing: true,
                serverSide: true,
                aaSorting: [
                    [3, 'desc']
                ],
                "ajax": {
                    "url": "/product/show/in/doctor",
                    "data": function(d) {
                         d.category = $('#filter_with_category').val();
                         d.brand = $('#filter_with_brand').val();
                         d.stock = $('#filter_with_stock').val();

                    }
                },
                scrollY: "75vh",
                scrollX: true,
                scrollCollapse: true,
                columns: [
                    { data: 'category', name: 'category' },
                    { data: 'brand', name: 'brand' },
                    { data: 'name', name: 'name', className: 'text-center' },
                { data: 'qty_available', name: 'qty_available', },
                { data: 'selling_price', name: 'selling_price', visible:false },
                { data: 'sku', name: 'sku' },
                { data: 'type', name: 'type', className: 'text-center', visible:false},
            ],
            });
            $(document).on('change',
                '#filter_with_category, #filter_with_brand, #filter_with_stock',
                function() {
                    api_product_list_table.ajax.reload();
                })
            
        });
    </script>
@endsection
