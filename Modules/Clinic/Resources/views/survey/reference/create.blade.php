<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@lang('messages.add') Sources</h4>
        </div>

        <form id="add_reference_form" method="POST" autocomplete="off">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label for="parent_id">Parent Source (Optional)</label>
                    <select class="form-control select2" id="parent_id" name="parent_id" style="width: 100%;">
                        <option value="">-- No Parent --</option>
                        @foreach($parentReferences as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    <span id="parent_id_error" class="help-block text-danger" style="display: none;"></span>
                </div>

                <div class="form-group">
                    <label for="name">@lang('messages.name')*</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                    <span id="name_error" class="help-block text-danger" style="display: none;"></span>
                </div>

                <div class="form-group">
                    <label for="details">Details</label>
                    <textarea class="form-control" id="details" name="details" rows="3"></textarea>
                    <span id="details_error" class="help-block text-danger" style="display: none;"></span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
            </div>
        </form>
    </div>
</div>