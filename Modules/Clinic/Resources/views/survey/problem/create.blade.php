<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\DiseasesController::class, 'store']),
            'method' => 'post',
            'id' => 'disease_add_form_clinic',
        ]) !!}

        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">@lang('clinic::lang.add_disease')</h4>
        </div>

        <div class="modal-body">

            <div class="form-group">
                {!! Form::label('name', __('clinic::lang.disease_name') . ':') !!}<span class="star">*</span>
                {!! Form::text('name', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('clinic::lang.disease_name'),
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('bn_name', __('clinic::lang.disease_name_bangla') . ':') !!}
                {!! Form::text('bn_name', null, [
                    'class' => 'form-control',
                    'placeholder' => __('clinic::lang.disease_name_bangla'),
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('category_id', 'Select Category:') !!}<span class="star">*</span>
                {!! Form::select('category_id', $categories, null, [
                    'class' => 'form-control select2',
                    'required',
                    'placeholder' => 'Select a Category',
                    'id' => 'category_id',
                    'style' => 'width:100% !important;',
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('description', __('clinic::lang.disease_description') . ':') !!}
                {!! Form::text('description', null, [
                    'class' => 'form-control',
                    'placeholder' => __('clinic::lang.disease_description'),
                ]) !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div>
</div>
<script>
    $('#category_id').select2({
        allowClear: false,
    });
</script>
