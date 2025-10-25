<div class="modal-dialog" role="document">
    <div class="modal-content">
      {!! Form::open(['url' => action([\Modules\Clinic\Http\Controllers\doctor\MedicineMealController::class, 'store']), 'method' => 'post', 'id' => 'medicine_meal_store_form' ]) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add New Dosage Time</h4>
      </div>
  
      <div class="modal-body">
        <div class="form-group">
          {!! Form::label('value', 'Name' . ':*') !!}
          {!! Form::hidden('type', $type) !!}
            {!! Form::text('value', null, ['class' => 'form-control', 'required', 'placeholder' => 'Name']) !!}
            {!! Form::hidden('status', 1) !!}
        </div>  
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
  <script>
    $(document).on('submit', 'form#medicine_meal_store_form', function(e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        form.find('button[type="submit"]').prop('disabled', true);
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('div.medicine_meal_modal').modal('hide');
                            $('div.add_new_meal_time').modal('hide');
                            var newMeal = $('<option>', {
                                value: result.data.id,   // id as value
                                text: result.data.text   // value as text
                            });

                            $('.dosage_form').append(newMeal).trigger('change');
                            toastr.success(result.msg);
                            $("#medicine_meal_table").DataTable().ajax.reload();

                            form.find('button[type="submit"]').prop('disabled', false);
                        } else {
                            toastr.error(result.msg);
                            form.find('button[type="submit"]').prop('disabled', false);
                        }
                    },
                });
            });
  </script>