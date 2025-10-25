{{-- modifier_for_product.blade.php --}}
@php
  $id = 'modifier_' . $row_count . '_' . time();
@endphp
<div>
  <span class="selected_modifiers">
    @if(!empty($edit_modifiers) && !empty($product->modifiers) )
      @include('restaurant.product_modifier_set.add_selected_modifiers', array('index' => $row_count, 'modifiers' => $product->modifiers ) )
    @endif
  </span>&nbsp;  
  <i class="fa fa-external-link-alt cursor-pointer text-white select-modifiers-btn btn btn-sm btn-success" title="@lang('restaurant.modifiers_for_product')" data-toggle="modal" data-target="#{{$id}}"> Change Options</i>
</div>
<div class="modal fade modifier_modal" id="{{$id}}" tabindex="-1" role="dialog">
<div class="modal-dialog" role="document">
  <div class="modal-content">

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'restaurant.modifiers_for_product' ): <span class="text-success"></span>
      </h4>
    </div>

    <div class="modal-body">
      @if(!empty($product_ms))
        <div class="panel-group" id="accordion{{$id}}" role="tablist" aria-multiselectable="true">

      @foreach($product_ms as $modifier_set)
        @php
          $collapse_id = 'collapse'. $modifier_set->id . $id;
        @endphp

        <div class="panel panel-default">
          <div role="button" data-toggle="collapse" data-parent="#accordion{{$id}}" 
          href="#{{$collapse_id}}" 
          aria-expanded="true" aria-controls="collapseOne" class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
              {{$modifier_set->name}}
            </h4>
          </div>
          <input type="hidden" class="modifiers_exist" value="true">
          <input type="hidden" class="index" value="{{$row_count}}">

          <div id="{{$collapse_id}}" class="panel-collapse collapse @if($loop->index==0) in @endif" role="tabpanel" aria-labelledby="headingOne">
            <div class="panel-body">
              <div class="modifier-btn-group" role="group" aria-label="Modifier Options">
                @foreach($modifier_set->variations as $modifier)
                  <label class="btn modifier-btn @if(!empty($edit_modifiers) && in_array($modifier->id, $product->modifiers_ids)) active @endif" 
                         aria-pressed="@if(!empty($edit_modifiers) && in_array($modifier->id, $product->modifiers_ids)) true @else false @endif">
                    <input type="checkbox" 
                           class="modifier-checkbox visually-hidden" 
                           data-limit="{{$modifier_set->pivot->modifier_limit}}" 
                           name="modifiers[{{$modifier_set->id}}][]" 
                           value="{{$modifier->id}}" 
                           @if(!empty($edit_modifiers) && in_array($modifier->id, $product->modifiers_ids)) checked @endif> 
                    {{$modifier->name}} (৳{{ number_format($modifier->sell_price_inc_tax) }})
                  </label>
                @endforeach
              </div>
            </div>
          </div>
        </div>
          
        
      @endforeach

        </div>
      @endif
    </div>

    <div class="modal-footer">
      <button data-url="{{action([\App\Http\Controllers\Restaurant\ProductModifierSetController::class, 'add_selected_modifiers'])}}" type="button" class="btn btn-primary add_modifier" data-dismiss="modal">
        @lang( 'messages.add' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
</div>

<script type="text/javascript">
if( typeof $ !== 'undefined'){
  $(document).ready(function(){
    $('div#{{$id}}').modal('show');
  });
}
</script>