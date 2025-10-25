<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">
                @lang('clinic::lang.guidline_details')
            </h4>
        </div>


        <div class="modal-body">
            <div class="container-fluid">

                <div class="card shadow-sm p-3 mb-4 rounded-3 border-0">
                    <h4 class="text-secondary border-bottom pb-2 mb-3">
                        {{ $guidline->name }}
                    </h4>

                    <p>
                        <strong>@lang('clinic::lang.guidline_description'):</strong><br>
                        {!! $guidline->description ?? '-' !!}
                    </p>

                    <p>
                        <strong>@lang('clinic::lang.created_by'):</strong>
                        {{ $guidline->creator->first_name ?? '' }} {{ $guidline->creator->last_name ?? '' }}
                    </p>

                </div>

                <div class="card shadow-sm p-3 rounded-3 border-0">
                    <h5 class="text-secondary border-bottom pb-2 mb-3">
                        @lang('clinic::lang.products')
                    </h5>

                    @if ($guidline->products->count() > 0)
                        <ul class="list-group">
                            @foreach ($guidline->products as $product)
                                <li class="list-group-item">
                                    {{ $product->product_id }} - {{ $product->product_name ?? 'N/A' }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">No products attached.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>


    </div>
</div>
