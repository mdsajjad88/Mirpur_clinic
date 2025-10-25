<!-- Notes1 Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="notes1Modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">@lang('lang_v1.add_note')</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
				        <div class="form-group">
				            {!! Form::label('notes_content', __('lang_v1.notes') . ':' ) !!}
				            <div class="input-group">
				                <span class="input-group-addon">
				                    <i class="fa fa-sticky-note"></i>
				                </span>
				                {!! Form::textarea('notes_content', null, ['class' => 'form-control', 'rows' => 3, 'id' => 'notes_content', 'placeholder' => __('lang_v1.notes')]) !!}
				            </div>
				        </div>
				    </div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary" id="saveNotes1">@lang('messages.update')</button>
			    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div><!-- /.modal -->
