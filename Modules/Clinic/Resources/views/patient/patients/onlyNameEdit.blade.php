<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        <!-- Header -->
        <div class="modal-header text-white">
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fa fa-id-card"></i> Update Patient Name
            </h4>
        </div>
        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\PatientController::class, 'updatePatientName'], [$contact->id]),
            'method' => 'PUT',
            'id' => 'patient_name_update_form',
        ]) !!}
        <!-- Body -->
        <div class="modal-body">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        {!! Form::label('first_name', __('clinic::lang.first_name') . ':*') !!}
                        {!! Form::text('first_name', $contact->first_name, ['class' => 'form-control', 'required']) !!}
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}

    </div>
</div>
