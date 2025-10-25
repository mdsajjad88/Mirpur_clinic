<div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h4 class="modal-title"> Convert patient height Fit to Centemeter</h4>
        </div>
        <div class="modal-body">
            {!! Form::number('patient_height_fit', null, [
                'class' => 'form-control',
                'placeholder' => 'Enter height (Feet)',
                'id' => 'patient_height_fit' 
            ]) !!}
            {!! Form::number('patient_height_cm', null, [
                'class' => 'form-control mt-1',
                'placeholder' => 'Enter height (cm)',
                'id' => 'patient_height_cm', 
                'readonly' => true 
            ]) !!}
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default close-modal-btn" data-dismiss="modal">
                @lang('messages.close')
            </button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
<script>
$(document).ready(function () {
    const feetToCmFactor = 30.48; 
    $('#patient_height_fit').on('input', function () {
        const feetValue = parseFloat($(this).val());

        if (!isNaN(feetValue)) {
            const cmValue = feetValue * feetToCmFactor;
            $('#patient_height_cm').val(cmValue.toFixed(2)); // Round to 2 decimal places
        } else {
            $('#patient_height_cm').val(''); // Clear if input is invalid
        }
    });
});
</script>
