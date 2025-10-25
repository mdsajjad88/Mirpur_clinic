<div class="modal-dialog" role="document">
    <div class="modal-content">
  
      {!! Form::open(['url' => action([\Modules\Clinic\Http\Controllers\DiseasesController::class, 'update'], [$disease->id]), 'method' => 'PUT', 'id' => 'disease_edit_form_clinic' ]) !!}
  
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title"> @if($type == 'doctor_dashboard') Edit Chief Complaint @else @lang( 'clinic::lang.edit_disease' ) @endif</h4>
      </div>
      @php 
      $name = '';
      if($type == 'doctor_dashboard'){
        $name = 'Complaint Name';
      }elseif($type == 'disease'){
        $name = 'Disease Name';
      }

      @endphp 
      <div class="modal-body">
        
        <div class="form-group">
          {!! Form::label('name', $name . ':') !!}<span class="star">*</span>
            {!! Form::text('name', $disease->name, ['class' => 'form-control', 'required', 'placeholder' => $name]) !!}
        </div>
        @if($type != 'doctor_dashboard')
        <div class="form-group">
          {!! Form::label('bn_name', __( 'clinic::lang.disease_name_bangla' ) . ':') !!}
            {!! Form::text('bn_name', $disease->bn_name??'', ['class' => 'form-control',  'placeholder' => __( 'clinic::lang.disease_name_bangla' )]) !!}
        </div>
        @endif
        <div class="form-group">
          {!! Form::label('category_id', 'Select Category:') !!}<span class="star">*</span>
          {!! Form::select('category_id', $disease_categories, $disease->category_id, ['class'=>'form-control select2', 'required', 'placeholder'=>'Select a Category', 'id'=>'category_id', 'style'=>'width:100% !important;' ]) !!}
        </div>
        @if($type != 'doctor_dashboard')
        <div class="form-group">
          {!! Form::label('description', __( 'clinic::lang.disease_description' ) . ':') !!}
            {!! Form::text('description', $disease->description??'', ['class' => 'form-control','placeholder' => __( 'clinic::lang.disease_description' )]) !!}
        </div>  
        @endif
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
  <script>
    $('#category_id').select2({
        allowClear: false,
    });
  </script>