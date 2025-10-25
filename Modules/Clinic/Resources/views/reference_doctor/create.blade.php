<div class="modal-dialog" role="document">
    <div class="modal-content">
      {!! Form::open(['url' => action([\Modules\Clinic\Http\Controllers\Survey\ReferenceDoctorController::class, 'store']), 'method' => 'post', 'id' =>'reference_doctor_add_form' ]) !!}

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@lang( 'clinic::lang.add_reference_doctor' )</h4>
      </div>
  
      <div class="modal-body">
        <div class="form-group">
          {!! Form::label('dr_name', __( 'clinic::lang.dr_name' ) . ':*') !!}
            {!! Form::text('dr_name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'clinic::lang.dr_name' ) ]) !!}
        </div>
  
        <div class="form-group">
          {!! Form::label('hospital_name', __( 'clinic::lang.hospital_name' ) . ':') !!}
            {!! Form::text('hospital_name', null, ['class' => 'form-control','placeholder' => __( 'clinic::lang.hospital_name' )]) !!}
        </div>
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div>
  </div>