<div class="modal-dialog" role="document">
    <div class="modal-content">
  
      {!! Form::open(['url' => action([\Modules\Clinic\Http\Controllers\doctor\MedicineUseController::class, 'update'],[$medicine->id]), 'method' => 'PUT', 'id' => 'use_medicine_update_form' ]) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Edit Medicine Info</h4>
      </div>
  
      <div class="modal-body">
        <div class="form-group">
          {!! Form::label('name', 'Medicine Name' . ':*') !!}
            {!! Form::text('name', $medicine->name, ['class' => 'form-control', 'required', 'placeholder' => 'Medicine Name']) !!}
        </div>  
        <div class="form-group">
          {!! Form::label('value', 'Value' . ':*') !!}
            {!! Form::text('value', $medicine->value, ['class' => 'form-control', 'required', 'placeholder' => 'Value']) !!}
        </div>  
        <div class="form-group">
          {!! Form::label('status', 'Status' . ':*') !!}
          {!! Form::select('status', [1 => 'Active', 0 => 'Inactive'], $medicine->status ?? null, ['class' => 'form-control', 'required']) !!}
        </div>  
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->