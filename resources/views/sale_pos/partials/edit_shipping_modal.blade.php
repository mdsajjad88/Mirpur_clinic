<!-- Edit Shipping Modal -->
<div class="modal fade" tabindex="-1" role="dialog" id="posShippingModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">@lang('sale.shipping')</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Shipping Details -->
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('shipping_details_modal', __('sale.shipping_details') . ':*' ) !!}
                            {!! Form::textarea('shipping_details_modal', isset($transaction->shipping_details) ? $transaction->shipping_details : '', ['class' => 'form-control', 'placeholder' => __('sale.shipping_details'), 'required', 'rows' => '3']) !!}
                        </div>
                    </div>

                    <!-- Region, City, Area Select -->
                    {{-- <div class="col-md-4">
                        <div class="form-group">
                            <label for="division">@lang('sale.region')</label>
                            <select id="division" name="division" class="form-control">
                                <option value="">@lang('sale.select_region')</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}" data-name="{{ $division->name }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="district">@lang('sale.city')</label>
                            <select id="district" name="district" class="form-control">
                                <option value="">@lang('sale.select_city')</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="upazila">@lang('sale.area')</label>
                            <select id="upazila" name="upazila" class="form-control">
                                <option value="">@lang('sale.select_area')</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="address">@lang('sale.address')</label>
                            <input type="text" id="address" name="address" class="form-control" placeholder="@lang('sale.address')" onkeyup="updateShippingAddress()">
                        </div>
                    </div>

					<!-- Shipping Address -->
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('shipping_address', __('lang_v1.shipping_address') . ':') !!}
                            {!! Form::textarea('shipping_address', isset($transaction->shipping_address) ? $transaction->shipping_address : '', ['class' => 'form-control', 'id' => 'shipping_address_modal', 'placeholder' => __('lang_v1.shipping_address'), 'rows' => '3', 'readonly']) !!}
                        </div>
                    </div> --}}

                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('shipping_address', __('lang_v1.shipping_address') . ':') !!}
                            {!! Form::textarea('shipping_address', isset($transaction->shipping_address) ? $transaction->shipping_address : '', ['class' => 'form-control', 'placeholder' => __('lang_v1.shipping_address'), 'rows' => '3']) !!}
                        </div>
                    </div>

                    <!-- Shipping Charges -->
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('shipping_charges_modal', __('sale.shipping_charges') . ':*' ) !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                {!! Form::text('shipping_charges_modal', isset($transaction->shipping_charges) ? @num_format($transaction->shipping_charges) : 0, ['class' => 'form-control input_number','placeholder' => __('sale.shipping_charges')]) !!}
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Status -->
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('shipping_status_modal', __('lang_v1.shipping_status') . ':') !!}
                            {!! Form::select('shipping_status_modal', $shipping_statuses, isset($transaction->shipping_status) ? $transaction->shipping_status : null, ['class' => 'form-control','placeholder' => __('messages.please_select')]) !!}
                        </div>
                    </div>

                    <!-- Delivered To -->
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('delivered_to_modal', __('lang_v1.delivered_to') . ':*' ) !!}
                            {!! Form::text('delivered_to_modal', isset($transaction->delivered_to) ? $transaction->delivered_to : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.delivered_to')]) !!}
                        </div>
                    </div>

                    <!-- Delivery Person -->
                    <div class="col-md-6">
                        <div class="form-group">
                            {!! Form::label('delivery_person_modal', __('lang_v1.delivery_person') . ':') !!}
                            {!! Form::select('delivery_person_modal', $users, isset($transaction->delivery_person) ? $transaction->delivery_person : null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select')]) !!}
                        </div>
                    </div>

                    <!-- Custom Fields -->
                    @foreach([1, 2, 3, 4, 5] as $i)
                        @php
                            $custom_label = isset($custom_labels['shipping']['custom_field_' . $i]) ? $custom_labels['shipping']['custom_field_' . $i] : '';
                            $is_required = isset($custom_labels['shipping']['is_custom_field_' . $i . '_required']) && $custom_labels['shipping']['is_custom_field_' . $i . '_required'] == 1;
                        @endphp
                        @if(!empty($custom_label))
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('shipping_custom_field_' . $i, $custom_label . ($is_required ? ' *' : '') ) !!}
                                    {!! Form::text('shipping_custom_field_' . $i, !empty($transaction->{'shipping_custom_field_' . $i}) ? $transaction->{'shipping_custom_field_' . $i} : null, ['class' => 'form-control', 'placeholder' => $custom_label, 'required' => $is_required]) !!}
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="posShippingModalUpdate">@lang('messages.update')</button>
                <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.cancel')</button>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
