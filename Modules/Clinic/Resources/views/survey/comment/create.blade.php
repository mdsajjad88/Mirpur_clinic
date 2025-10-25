<div class="modal-dialog" role="document">
    <div class="modal-content">
        @php
            $form_id = 'comment_store_form';
            $url = action([\Modules\Clinic\Http\Controllers\Survey\CommentController::class, 'store']);
        @endphp
        {!! Form::open(['url' => $url, 'method' => 'POST', 'id' => $form_id]) !!}
        <div class="modal-header">
            <h5 class="modal-title" id="commentStoreModalLabel">Add New Comment</h5>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-5">
                    <div class="mb-3">
                        {!! Form::label('name', 'Name', ['class' => 'form-label']) !!}
                        <span class="star">*</span>
                        {!! Form::text('name', null, [
                            'class' => 'form-control',
                            'placeholder' => 'Enter Problem Name',
                            'required',
                        ]) !!}
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        {!! Form::label('description', 'Description', ['class' => 'form-label']) !!}
                        <span class="star">*</span>
                        {!! Form::text('description', null, [
                            'class' => 'form-control',
                            'placeholder' => 'Enter Problem Description',
                            'required',
                        ]) !!}
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            {!! Form::submit('Save', ['class' => 'btn btn-primary']) !!}
            <button type="button" class="btn btn-default btn-close" data-dismiss="modal">Close</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>

