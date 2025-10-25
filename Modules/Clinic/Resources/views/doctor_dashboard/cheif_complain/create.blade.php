<div class="modal-dialog" role="document">
    <div class="modal-content">
      {!! Form::open(['url' => action([\Modules\Clinic\Http\Controllers\doctor\ChiefComplainController::class, 'store']), 'method' => 'post', 'id' => 'cheif_complain_store_form' ]) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Add New cheif complain</h4>
      </div>
  
      <div class="modal-body">
        <div class="form-group">
          {!! Form::label('name', 'Name' . ':*') !!}
            {!! Form::text('value', null, ['class' => 'form-control complain_value', 'required', 'placeholder' => 'Enter cheif complain Name']) !!}
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