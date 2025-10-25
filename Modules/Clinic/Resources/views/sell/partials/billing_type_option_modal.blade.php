<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">{{ ucfirst($type) }} Informations</h4>
        </div>

        <div class="modal-body">
            <table class="table table-striped table-bordered" id="billing_options_table">
                <thead>
                    <tr>
                        <th>{{ ucfirst($type) }} Name</th>
                        <th>Category</th>
                        <th>Price</th>
                    </tr>
                </thead>
                <tbody>
                   @forelse ($products as $product)
                       <tr>
                            <td>{{$product->name}}</td>
                            <td>{{$product->category_name}}</td>
                            <td>{{ number_format($product->sell_price, 2)}}</td>
                       </tr>
                   @empty
                       
                   @endforelse
                </tbody>
            </table>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->