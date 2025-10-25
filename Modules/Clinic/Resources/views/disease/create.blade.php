<div class="modal-dialog" role="document">
    <div class="modal-content">
  
      {!! Form::open(['url' => action([\Modules\Clinic\Http\Controllers\DiseasesController::class, 'store']), 'method' => 'post', 'id' => $quick_add ? 'quick_add_disease_form' : 'disease_add_form_clinic' ]) !!}
      
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">@if($type == 'doctor_dashboard') Add Chief Complaint @else @lang( 'clinic::lang.add_disease' ) @endif</h4>
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
          {!! Form::label('name', $name. ':') !!}<span class="star">*</span>
            {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => $name ]) !!}
        </div>
        @if($type != 'doctor_dashboard')
        <div class="form-group">
          {!! Form::label('bn_name', __( 'clinic::lang.disease_name_bangla' ) . ':') !!}
            {!! Form::text('bn_name', null, ['class' => 'form-control', 'placeholder' => __( 'clinic::lang.disease_name_bangla' )]) !!}
        </div>
        @endif
        {{-- <div class="form-group">
          {!! Form::label('category_id', 'Select Category:') !!}<span class="star">*</span>
          {!! Form::select('category_id', $categories, null, ['class'=>'form-control select2', 'required', 'placeholder'=>'Select a Category', 'id'=>'category_id', 'style'=>'width:100% !important;']) !!}
        </div> --}}
        <div class="form-group">
          {!! Form::label('category_id', 'Select Category:') !!}<span class="star">*</span>
          {!! Form::select('category_id', $disease_categories, null, ['class' => 'form-control select2', 'required', 'placeholder' => 'Select a Category', 'id' => 'category_id', 'style' => 'width:100% !important;']) !!}
      </div>
      @if($type != 'doctor_dashboard')
        <div class="form-group">
          {!! Form::label('description', __( 'clinic::lang.disease_description' ) . ':') !!}
            {!! Form::text('description', null, ['class' => 'form-control','placeholder' => __( 'clinic::lang.disease_description' )]) !!}
        </div>
      @endif
         
  
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div>
  </div>
  <script>
    $('#category_id').select2({
        allowClear: false,
    });
  </script>