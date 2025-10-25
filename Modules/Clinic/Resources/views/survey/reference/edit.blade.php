<div class="modal-dialog" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
            <h4 class="modal-title">@lang('messages.edit') Sources</h4>
        </div>

        <form id="edit_reference_form" method="POST" action="{{ action([\Modules\Clinic\Http\Controllers\Survey\ReferenceController::class, 'update'], [$reference->id]) }}" autocomplete="off">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label for="edit_parent_id">Parent Source (Optional)</label>
                    <select class="form-control select2" id="edit_parent_id" name="parent_id" style="width: 100%;">
                        <option value="">-- No Parent --</option>
                        @foreach($parentReferences as $parent)
                            <option value="{{ $parent->id }}" {{ $parent->id == $reference->parent_id ? 'selected' : '' }} {{ $parent->id == $reference->id ? 'disabled' : '' }}>
                                {{ $parent->name }}
                            </option>
                        @endforeach
                    </select>
                    <span id="edit_parent_id_error" class="help-block text-danger" style="display: none;"></span>
                </div>

                <div class="form-group">
                    <label for="edit_name">@lang('messages.name')*</label>
                    <input type="text" class="form-control" id="edit_name" name="name" value="{{ $reference->name }}" required>
                    <span id="edit_name_error" class="help-block text-danger" style="display: none;"></span>
                </div>

                <div class="form-group">
                    <label for="edit_details">Details</label>
                    <textarea class="form-control" id="edit_details" name="details" rows="3">{{ $reference->details }}</textarea>
                    <span id="edit_details_error" class="help-block text-danger" style="display: none;"></span>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
                <button type="submit" class="btn btn-primary">@lang('messages.update')</button>
            </div>
        </form>
    </div>
</div>