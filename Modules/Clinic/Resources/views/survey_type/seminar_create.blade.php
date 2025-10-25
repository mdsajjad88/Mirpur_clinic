<div class="modal-dialog" role="document">
    <div class="modal-content">

        {!! Form::open([
            'url' => action([\Modules\Clinic\Http\Controllers\SurveyTypeController::class, 'store']) . '?type=seminar',
            'method' => 'post',
            'id' => 'survey_type_store_form',
            'files' => true,
        ]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title">Add New Seminar Info</h4>
        </div>

        <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __('clinic::lang.survey_name') . ':*') !!}
                {!! Form::text('name', null, [
                    'class' => 'form-control',
                    'required',
                    'placeholder' => __('clinic::lang.survey_name'),
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('image', 'Banner Image') !!}               
                {!! Form::file('banner_img', [
                            'id' => 'upload_banner_image',
                            'accept' => 'image/*',
                            'required',
                        ]) !!}
                <small>
                    <p class="help-block">@lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p>
                </small>
            </div>
            <div class="form-group">
                {!! Form::label('description', 'Description') !!}
                {!! Form::textarea('description', $surveyType->description ?? null, ['class' => 'form-control', 'placeholder' => 'Description']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('patient_allow_count', 'How many patients want to allow seminar?') !!}
                {!! Form::number('patient_allow_count', null, [
                    'class' => 'form-control',
                    'min' => '1',
                    'placeholder' => 'Patient Counting',
                    'required',
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('fee', 'How much should patient charge for the seminar?') !!}
                {!! Form::number('fee', null, [
                    'class' => 'form-control',
                    'min' => '1',
                    'placeholder' => 'Seminar Fee',
                    'required',
                ]) !!}
            </div>
            <div class="form-group">
                {!! Form::label('mobile', 'Seminar Mobile No?') !!}
                {!! Form::number('mobile', null, ['class' => 'form-control', 'placeholder' => 'Seminar Mobile No', 'required']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('website_url', 'Our Website url?') !!}
                {!! Form::text('website_url', null, ['class' => 'form-control', 'placeholder' => 'Website url', 'required']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('already_registered_link', 'Already Registered Link?') !!}
                {!! Form::text('already_registered_link', null, [
                    'class' => 'form-control',
                    'placeholder' => 'If already registered which link should I show?',
                    'required',
                ]) !!}
            </div>
            <div class="form-group">
                <div class="row">
                    <div class="col-xs-6">
                        {!! Form::label('is_show_division', 'Show Division?') !!}
                        {!! Form::checkbox('is_show_division', 1, true, ['class' => 'input-icheck', 'id' => 'is_show_division']) !!}
                    </div>
                    <div class="col-xs-6">
                        {!! Form::label('is_show_district', 'Is show district?') !!}
                        {!! Form::checkbox('is_show_district', 1, true, ['class' => 'input-icheck', 'id' => 'is_show_district']) !!}
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-6">
                        {!! Form::label('is_show_primary_disease', 'Is show primary disease?') !!}
                        {!! Form::checkbox('is_show_primary_disease', 1, true, [
                            'class' => 'input-icheck',
                            'id' => 'is_show_primary_disease',
                        ]) !!}
                    </div>
                    <div class="col-xs-6">
                        {!! Form::label('is_show_secondary_disease', 'Is show secondary disease?') !!}
                        {!! Form::checkbox('is_show_secondary_disease', 1, true, [
                            'class' => 'input-icheck',
                            'id' => 'is_show_secondary_disease',
                        ]) !!}
                    </div>
                </div>
            </div>
            <div class="form-group">
                {!! Form::label('is_active', 'Is active this seminar?') !!}
                {!! Form::checkbox('is_active', 1, true, ['class' => 'input-icheck', 'id' => 'is_active']) !!}
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

        {!! Form::close() !!}

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
<script>
    $(document).ready(function() {
        $('#is_show_division, #is_show_district, #is_show_primary_disease, #is_show_secondary_disease, #is_active')
            .iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%' // optional
            });

         var img_fileinput_setting = {
        showUpload: false,
        showPreview: true,
        browseLabel: LANG.file_browse_label,
        removeLabel: LANG.remove,
        previewSettings: {
            image: { width: 'auto', height: 'auto', 'max-width': '100%', 'max-height': '100%' },
        },
    };
    $('#upload_banner_image').fileinput(img_fileinput_setting);

    })
</script>
