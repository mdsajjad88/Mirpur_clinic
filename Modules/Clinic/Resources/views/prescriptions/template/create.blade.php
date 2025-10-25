<div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title">Template</h4>
        </div>
        @php
            $form_id = 'template_add_form';           
            $url = action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'storeTemplate']); 
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'post', 'id' => $form_id]) !!}
        <div class="modal-body">
            {!! Form::text('template_name_hidden', null, ['class'=>'form-control', 'required', 'placeholder'=>'Enter Template Name']) !!}
            {!! Form::hidden('appointment_id', $appointment->id) !!}
        </div>
        <div class="modal-footer">
            {!! Form::submit(__('messages.save'), ['class' => 'btn btn-primary']) !!}
            <button type="button" class="btn btn-default close-modal-btn" data-dismiss="modal">
                @lang('messages.close')
            </button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
