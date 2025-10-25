<div class="modal-dialog" role="document">
    <div class="modal-content">      
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">Session Name</h4>
      </div>
  
      <div class="modal-body">
            <table class="table table-striped table-bordered" id="session_details_info_table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Finalize By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($patientSessionDetails as $session_detail)
                        <tr>
                            <td>{{$session_detail->visit_date}}</td>
                            <td>{{$session_detail->doctorProfile->first_name}} {{$session_detail->doctorProfile->last_name ?? ''}}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2">No session details available</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
      </div>
  
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button>
      </div>
  
      {!! Form::close() !!}
  
    </div>
  </div>