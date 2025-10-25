<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      {!! Form::open(['url' => action([\Modules\Clinic\Http\Controllers\OldMedicineController::class, 'store']), 'method' => 'post', 'id' =>'medicine_store_form' ]) !!}

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'clinic::lang.add_new_old_medicine' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="form-group">
          {!! Form::label('medicine_name', __( 'clinic::lang.medicine_name' ) . ':*') !!}
            {!! Form::text('medicine_name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'clinic::lang.medicine_name' ) ]) !!}
        </div>
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div>
  </div>