<div class="modal-dialog" role="document">
    {!! Form::open(['action' => '\Modules\AssetManagement\Http\Controllers\AssetAllocationController@store', 'id' => 'asset_allocation_form', 'method' => 'post']) !!}
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                @lang('assetmanagement::lang.asset_allocation')
            </h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('ref_no', __('assetmanagement::lang.allocation_code') . ':' )!!}
                        {!! Form::text('ref_no', null, ['class' => 'form-control', 'placeholder' => __('assetmanagement::lang.allocation_code')]) !!}
                        <p class="help-block">
                            @lang('lang_v1.leave_empty_to_autogenerate')
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        {!! Form::label('receiver', __('assetmanagement::lang.allocate_to') . ':*' )!!}
                        {!! Form::select('receiver', $users, null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']) !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="button" id="add_asset_row" class="btn btn-success"> <i class="fa fa-plus"></i>
                        @lang('assetmanagement::lang.add_asset')
                    </button>
                </div>
            </div>

            <table class="table table-bordered" id="asset_allocation_table">
                <thead>
                    <tr>
                        <th style="width: 60%">@lang('assetmanagement::lang.asset')</th>
                        <th>@lang('lang_v1.quantity')</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('transaction_datetime', __('assetmanagement::lang.allocate_from') . ':*' )!!}
                        {!! Form::text('transaction_datetime', null, ['class' => 'form-control datetimepicker', 'placeholder' => __('assetmanagement::lang.allocate_from'), 'readonly', 'required']) !!}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        {!! Form::label('allocated_upto', __('assetmanagement::lang.allocated_upto') . ':' )!!}
                        {!! Form::text('allocated_upto', null, ['class' => 'form-control datepicker', 'placeholder' => __('assetmanagement::lang.allocated_upto'), 'readonly']) !!}
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('reason', __('assetmanagement::lang.reason') . ':') !!}
                        {!! Form::textarea('reason', null, ['class' => 'form-control', 'rows' => '3', 'placeholder' => __('assetmanagement::lang.reason')]) !!}
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">
                @lang('messages.close')
            </button>
            <button type="submit" class="btn btn-primary">
                @lang('messages.save')
            </button>
        </div>
    </div>
    {!! Form::close() !!}
</div>

<script>
    let rowIndex = 0; // Define rowIndex properly for JavaScript
    document.getElementById('add_asset_row').addEventListener('click', function () {
        const tableBody = document.querySelector('#asset_allocation_table tbody');
        const newRow = document.createElement('tr');
        newRow.innerHTML = `
            <td>
                {!! Form::select('asset_id[]', $assets['assets'], null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required', 'style' => 'width: 100%;']) !!}
            </td>
            <td>
                <input type="text" name="quantity[]" class="form-control input_number" placeholder="@lang('lang_v1.quantity')" required min="1">
            </td>
            <td>
                <button type="button" class="btn btn-danger remove_row">
                  <i class="fas fa-trash"></i>  @lang('messages.delete') 
                </button>
            </td>
        `;
        tableBody.appendChild(newRow);
        rowIndex++;

        // Re-initialize select2 for the newly added select element
        $('.select2').select2({
            width: '100%', // Ensure it takes full width
            dropdownParent: $('#allocate_asset_modal') // Prevents select2 dropdown issues with modals
        });
    });

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove_row')) {
            e.target.closest('tr').remove();
        }
    });
</script>
