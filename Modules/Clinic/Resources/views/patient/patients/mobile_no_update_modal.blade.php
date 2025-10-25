<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title col-sm-10">Update Patient Mobile No</h5>
            <button type="button" class="close col-sm-2" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        @php
            $form_id = 'patient_mobile_no_update_form';
            $url = action([\Modules\Clinic\Http\Controllers\PatientController::class, 'updatePatientName'], [$contact->id]);
        @endphp

        {!! Form::open(['url' => $url, 'method' => 'put', 'id' => $form_id]) !!}

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::hidden('first_name', $contact->first_name) !!}
                        {!! Form::label('mobile', __('clinic::lang.mobile') . ':*') !!}
                        {!! Form::number('mobile', null, [
                            'class' => 'form-control',
                            'required',
                            'placeholder' => 'Enter mobile number',
                            
                        ]) !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>

        {!! Form::close() !!}
    </div>
</div>
