@extends('layouts.app')

@section('title', __('sale.pos_sale'))

@section('css')
<style>
	.status-toggle {
		display: flex;
		align-items: center;
		gap: 10px; /* Space between label and switch */
	}

	.status-label {
		font-weight: bold;
		margin-right: auto; /* Aligns label to the left */
	}

	.switch {
		position: relative;
		display: inline-block;
		width: 36px; /* Reduced width */
		height: 18px; /* Reduced height */
		margin-bottom: 0;
	}

	.switch input {
		opacity: 0;
		width: 0;
		height: 0;
	}

	.slider {
		position: absolute;
		cursor: pointer;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: #ccc;
		transition: 0.4s;
		border-radius: 18px; /* Rounded edges for the slider */
	}

	.slider:before {
		position: absolute;
		content: "";
		height: 14px;
		width: 14px;
		left: 2px;
		bottom: 2px;
		background-color: white;
		transition: 0.4s;
		border-radius: 50%; /* Fully rounded knob */
	}

	input:checked + .slider {
		background-color: #4CAF50;
	}

	input:checked + .slider:before {
		transform: translateX(18px); /* Adjusted for smaller switch */
	}
</style>
@endsection

@section('content')
<section class="content no-print">
	<input type="hidden" id="amount_rounding_method" value="{{$pos_settings['amount_rounding_method'] ?? ''}}">
	<input type="hidden" id="allow_in_stock_to_demand_list" value="{{ $pos_settings['allow_in_stock_to_demand_list'] ?? '' }}">
	<input type="hidden" id="warn_on_walkin_customer" value="{{$pos_settings['warn_on_walkin_customer'] ?? ''}}">
	@if(!empty($pos_settings['allow_overselling']))
		<input type="hidden" id="is_overselling_allowed">
	@endif
	@if(session('business.enable_rp') == 1)
        <input type="hidden" id="reward_point_enabled">
    @endif
    @php
		$is_discount_enabled = $pos_settings['disable_discount'] != 1 ? true : false;
		$is_rp_enabled = session('business.enable_rp') == 1 ? true : false;
	@endphp
	@php
		$expiringSoon = session('expiring_soon');
		$expiringLater = session('expiring_later');
	@endphp
	<input hidden type="number" value="{{ $expiringSoon }}" id="expiring_soon">
	<input hidden type="number" value="{{ $expiringLater }}" id="expiring_later">
	{!! Form::open(['url' => action([\App\Http\Controllers\SellPosController::class, 'store']), 'method' => 'post', 'id' => 'add_pos_sell_form' ]) !!}
	<div class="row mb-12">
		<div class="col-md-12">
			<div class="row">
				<input hidden type="number" name="" id="auto_mfg" value="{{$pos_settings['auto_mfg']}}" id="">
				<div class="@if(empty($pos_settings['hide_product_suggestion'])) col-md-7 @else col-md-10 col-md-offset-1 @endif no-padding pr-12">
					<div class="box box-solid mb-12 @if(!isMobile()) mb-40 @endif">
						<div class="box-body pb-0">
							{!! Form::hidden('location_id', $default_location->id ?? null , ['id' => 'location_id', 'data-receipt_printer_type' => !empty($default_location->receipt_printer_type) ? $default_location->receipt_printer_type : 'browser', 'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '']) !!}
							<!-- sub_type -->
							{!! Form::hidden('sub_type', isset($sub_type) ? $sub_type : null) !!}
							<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
								@include('sale_pos.partials.pos_form')

								@include('sale_pos.partials.pos_form_totals')

								@include('sale_pos.partials.payment_modal')

								@if(empty($pos_settings['disable_suspend']))
									@include('sale_pos.partials.suspend_note_modal')
								@endif

								@if(empty($pos_settings['disable_recurring_invoice']))
									@include('sale_pos.partials.recurring_invoice_modal')
								@endif
							</div>
						</div>
					</div>
				@if(empty($pos_settings['hide_product_suggestion']) && !isMobile())
				<div class="col-md-5 no-padding">
					@include('sale_pos.partials.pos_sidebar')
				</div>
				@endif
			</div>
		</div>
	</div>
	@include('sale_pos.partials.pos_form_actions')
	{!! Form::close() !!}
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>

<div id="customerWaitlistModalContainer" class="modal fade customerWaitlistModal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel" data-backdrop="static">
    <!-- Content dynamically loaded here -->
</div>

@if(empty($pos_settings['hide_product_suggestion']) && isMobile())
	@include('sale_pos.partials.mobile_product_suggestions')
@endif
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

<div class="modal fade" id="expense_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
</div>

@include('sale_pos.partials.configure_search_modal')

@include('sale_pos.partials.recent_transactions_modal')

@include('sale_pos.partials.weighing_scale_modal')

@stop
@section('css')
	<!-- include module css -->
    @if(!empty($pos_module_data))
        @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_css_path']))
                @includeIf($value['module_css_path'])
            @endif
        @endforeach
    @endif
@stop
@section('javascript')
	<script>
		var discountRules = @json($discount_rules);
		var enable_discount_rules = @json($enable_discount_rules);
	</script>
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	@include('sale_pos.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <!-- include module js -->
    @if(!empty($pos_module_data))
	    @foreach($pos_module_data as $key => $value)
            @if(!empty($value['module_js_path']))
                @includeIf($value['module_js_path'], ['view_data' => $value['view_data']])
            @endif
	    @endforeach
	@endif
@endsection