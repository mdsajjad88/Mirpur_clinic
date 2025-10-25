<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\App\Http\Controllers\Restaurant\ProductModifierSetController::class, 'update'], [$modifer_set->id]), 'method' => 'post', 'id' => 'table_add_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'restaurant.products_for_modifier' ): <span class="text-success">{{$modifer_set->name}}</span></h4>
      <p>Total Modifiers: <span class="text-info">{{ $variation_count }}</span></p>
    </div>

    <div class="modal-body">
      <div class="row">
        <div class="col-sm-12">
          <div class="form-group">
            {!! Form::text('name', null, ['class' => 'form-control', 'placeholder' => __( 'lang_v1.search_product_placeholder' ), 'id' => 'search_product' ]) !!}
          </div>
        </div>
        
        <div class="col-sm-12">
          <table class="table table-condensed" id="add-modifier-table">
            <thead>
              <tr>
                <th>@lang( 'restaurant.products')</th>
                <th>@lang( 'lang_v1.modifier_limit' )</th>
                <th>&nbsp;</th>
              </tr>
            </thead>
            <tbody>
              @foreach($modifer_set->modifier_products as $product)
                <tr>
                  <td>{{$product->name}} ({{$product->sku}})</td>
                  <td>
                    <input type="number" name="modifier_limits[{{$product->id}}]" class="form-control" value="{{$product->pivot->modifier_limit ?? 1}}" min="0">
                  </td>
                  <input type="hidden" name="products[]" value="{{$product->id}}">
                  <td><button type="button" class="btn btn-danger btn-xs remove_modifier_product"><i class="fa fa-times"></i></button></td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
      <!-- Hidden input field to store removed product IDs -->
      <div id="removed_products"></div>
    </div>

    <div class="modal-footer">
      <button id="modifier_submit" type="button" class="btn btn-primary">@lang( 'messages.save' )</button>
      <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
  $(document).ready(function(){
    // Autocomplete for product search
    $( "#search_product" ).autocomplete({
      source: function(request, response) {
        $.getJSON("/products/list-no-variation", { term: request.term }, response);
      },
      minLength: 2,
      appendTo: "#table_add_form",
      response: function(event, ui) {
        if (ui.content.length == 1) {
          ui.item = ui.content[0];
          if (ui.item.qty_available > 0) {
            $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
            $(this).autocomplete('close');
          }
        } else if (ui.content.length == 0) {
          swal(LANG.no_products_found)
            .then((value) => {
              $('input#search_product').select();
            });
        }
      },
      select: function(event, ui) {
        add_product_row(ui.item.product_id);
      }
    })
    .autocomplete( "instance" )._renderItem = function( ul, item ) {
      var string =  "<div>" + item.name;
      string += ' (' + item.sku + ')' + " </div>";
      return $( "<li>" ).append(string).appendTo( ul );
    };

    // Function to add a product row
    function add_product_row(product_id) {
      $.ajax({
        method: "GET",
        url: '/modules/product-modifiers/product-row/' + product_id,
        dataType: "html",
        success: function(result) {
          $('table#add-modifier-table').append(result);
        }
      });
    }

    // Form submission validation
    $('#modifier_submit').on('click', function(e) {
      // Get the variation count from the hidden input or directly from the view
      const variationCount = parseInt($('#variation_count').val()) || {{ $variation_count }};

      // Check each modifier limit
      let isValid = true;
      $('input[name^="modifier_limits["]').each(function() {
        const modifierLimit = parseInt($(this).val());
        if (modifierLimit > variationCount) {
          isValid = false;
          return false; // Exit the loop early
        }
      });

      // If any modifier limit exceeds the variation count, show an alert and prevent form submission
      if (!isValid) {
        e.preventDefault();
        swal({
          title: LANG.error,
          text: `The modifier limit cannot exceed the total number of modifiers: ${variationCount}.`,
          icon: "error",
          button: "OK",
        });
      } else {
        // If validation passes, submit the form
        $('#table_add_form').submit();
      }
    });
  });
</script>