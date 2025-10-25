<div class="modal-dialog modal-xl no-print" role="document">
    <div class="modal-content">
      <div class="modal-header">
      <button type="button" class="close no-print" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title" id="modalTitle"> @lang('clinic::lang.appointment_details') #({{$appointment->appointment_number}})
      </h4>
  </div>
  <div class="modal-body">
      <div class="row">
        <div class="col-xs-12">
            <p class="pull-right"><b>@lang('messages.date'):</b> {{ @format_date($appointment->request_date) }}</p>
        </div>
      </div>
      <div class="row">
        {{-- @php
          $custom_labels = json_decode(session('business.custom_labels'), true);
          $export_custom_fields = [];
          if (!empty($sell->is_export) && !empty($sell->export_custom_fields_info)) {
              $export_custom_fields = $sell->export_custom_fields_info;
          }
        @endphp --}}
        <div class="col-sm-4">
          <b>@lang('clinic::lang.appointment_no'):</b>  #{{$appointment->appointment_number}} <br>   
          <b>@lang('clinic::lang.status'):</b> {{$appointment->remarks??""}} <br>   
          {{-- <b>Payment Status:</b>  {{$appointment->payment_status??""}} <br>    --}}
        </div>

        <div class="col-sm-4">
            <b>@lang('clinic::lang.patient_name'):</b>  {{$appointment->patient->first_name ?? ""}} {{$appointment->patient->last_name ?? ""}}<br>
            <b>@lang('clinic::lang.address'):</b>  {{$appointment->patient->address??""}} <br>   
            <b>@lang('clinic::lang.mobile'):</b>  {{$appointment->patient->mobile??""}} <br>   

        </div>
        <div class="col-sm-4">
            <b>@lang('clinic::lang.creator') - </b>{{$appointment->created_name}}  <br>
            @if($appointment->modified_by && $appointment->confirm_status == 1)
            <b>@lang('clinic::lang.confirmed_by') -</b> {{$appointment->contributor->username}}
            @endif

        </div>
        
        
  
        
      </div>
      {{-- <div class="row">
        <div class="col-md-12">
              <strong>{{ __('lang_v1.activities') }}:</strong><br>
              @includeIf('activity_log.activities')
          </div>
      </div> --}}
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default no-print" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
    </div>
  </div>
  
  <script type="text/javascript">
    $(document).ready(function(){
      var element = $('div.modal-xl');
      __currency_convert_recursively(element);
    });
  </script>