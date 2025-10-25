<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        @php

            $form_id = 'session_end_date_update_form';
            $url = action([\Modules\Clinic\Http\Controllers\SubsPatientController::class, 'endDateUpdate'], [$session->id]);
                
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">End Date Edit</h4>
      </div>
  
      <div class="modal-body">
          <div class="form-group">
            {!! Form::label('end_date', 'End Date:') !!}
            {!! Form::date('end_date',$session->end_date, ['class'=>'form-control', 'required']) !!}
            {!! Form::hidden('session_id', $session->id) !!}
          </div>
          @if ($is_admin)
            <div class="form-group">
              {!! Form::label('visited_count', 'Visited Count:') !!}
              {!! Form::number('visited_count',$session->visited_count, ['class'=>'form-control', 'required', 'min' => '0', 'max' => $session->total_visit]) !!}
            </div>
            <div class="form-group">
              {!! Form::label('remaining_visit', 'Remaining Visit:') !!}
              {!! Form::number('remaining_visit',$session->remaining_visit, ['class'=>'form-control', 'required', 'min' => '0', 'max' => $session->total_visit]) !!}
            </div>
          @endif
          
          
          <div class="form-group">
            {!! Form::label('is_closed', 'Is Closed ?') !!}
             {!! Form::checkbox('is_closed', 1, $session->is_closed == 1 ? true : false, ['id' => 'is_closed', 'class'=>'input-icheck']) !!}
          </div>
           
      </div>
  
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div>
  </div>
  <script>
    $('input[type="checkbox"].input-icheck').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue'
        });
    $(document).on('submit', 'form#session_end_date_update_form', function(e) {
                e.preventDefault();
                var form = $(this);
                var data = form.serialize();

                $.ajax({
                    method: 'POST',
                    url: $(this).attr('action'),
                    dataType: 'json',
                    data: data,
                    beforeSend: function(xhr) {
                        __disable_submit_button(form.find('button[type="submit"]'));
                    },
                    success: function(result) {
                        if (result.success == true) {
                            $('div.edit_end_date_modal').modal('hide');
                            toastr.success(result.msg);
                            $('#subcription_details_table').DataTable().ajax.reload();
                            } else {
                            toastr.error(result.msg);
                        }
                    },
                });
            });
  </script>