<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'store']),
            'method' => 'post',
            'id' => 'doctor_advice_store_form',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>

            <h4 class="modal-title">
                @switch($type)
                    @case('treatment_plan')
                        Add New Treatment Plan
                    @break

                    @case('home_advice')
                        Add New Home Advice
                    @break

                    @case('on_examination')
                        Add New On Examination
                    @break

                    @default
                        Add New Advice
                @endswitch
            </h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                @php
                    $labels = [
                        'treatment_plan' => 'Treatment Plan',
                        'home_advice' => 'Home Advice',
                        'on_examination' => 'On Examination',
                    ];

                    // Pick from array or fallback to "Advice"
                    $fieldLabel = $labels[$type] ?? 'Advice';
                @endphp

                {!! Form::label('value', $fieldLabel . ':*') !!}

                {!! Form::text('value', $name ?? null, [
                    'class' => 'form-control advice_value',
                    'required',
                    'placeholder' => $fieldLabel,
                ]) !!}


                {!! Form::hidden('status', 1) !!}
                {!! Form::hidden('type', $type) !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
