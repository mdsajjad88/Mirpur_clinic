@extends('clinic::layouts.app2')
@php
    if (!empty($status) && $status == 'draft') {
        $title = __('lang_v1.add_draft');
        $col_sm = 'col-sm-3';
        $col_md = 'col-sm-4';
    } else {
        $title = __('clinic::lang.add_bill');
        $col_sm = 'col-sm-4';
        $col_md = 'col-sm-4';
    }

    if ($sale_type == 'sales_order') {
        $title = __('lang_v1.sales_order');
    }
    $expiringSoon = session('expiring_soon');
    $expiringLater = session('expiring_later');
    $businessTBL = DB::table('business_locations')->where('id', $clinic_location)->first();
    $invoiceId = $businessTBL->invoice_scheme_id ?? '';
@endphp

@section('title', $title)

@section('content')
    <section class="content-header">
        <h1>{{ $title }}</h1>
    </section>
    <style>
        .home-collection {
            height: 20px;
            width: 20px;
        }
        .select_readonly_sub_type + .select2-container {
            pointer-events: none !important; /* block mouse */
            touch-action: none !important;   /* block touch */
            background-color: #e9ecef !important; /* gray */
            opacity: 1 !important; /* keep text visible */
        }


    </style>
    <!-- Main content -->
    <section class="content no-print">
        <input type="hidden" id="amount_rounding_method" value="{{ $pos_settings['amount_rounding_method'] ?? '' }}">
        @if (!empty($pos_settings['allow_overselling']))
            <input type="hidden" id="is_overselling_allowed">
        @endif
        @if (session('business.enable_rp') == 1)
            <input type="hidden" id="reward_point_enabled">
        @endif
        @if (count($business_locations) > 0)
            {{-- <div class="row">
                <div class="col-sm-3">
                    <div class="form-group">
                        <div class="input-group">
                            <span class="input-group-addon">
                                <i class="fa fa-map-marker"></i>
                            </span>
                            {!! Form::select(
                                'select_location_id',
                                $business_locations,
                                $clinic_location ?? null,
                                ['class' => 'form-control input-sm', 'id' => 'select_location_id', 'required', 'autofocus'],
                                $bl_attributes,
                            ) !!}
                            <span class="input-group-addon">
                                @show_tooltip(__('tooltip.sale_location'))
                            </span>
                        </div>
                    </div>
                </div>
            </div> --}}
            {!! Form::hidden('location_id', $clinic_location, ['id' => 'location_id']) !!}
        @endif

        @php
            $custom_labels = json_decode(session('business.custom_labels'), true);
            $common_settings = session()->get('business.common_settings');
            $home_collection_fee = $common_settings['clinic_home_collection_charge'] ?? 0;
            $clinic_service_charge = $common_settings['clinic_service_charge'] ?? 0;
            $clinic_service_charge_key = $common_settings['clinic_service_charge_key'] ?? '';
        @endphp
        <!-- Debugging the session values -->


        <input hidden type="number" value="{{ $expiringSoon }}" id="expiring_soon">
        <input hidden type="number" value="{{ $expiringLater }}" id="expiring_later">
        <input hidden type="number" value="{{ $clinic_location }}" id="clinic_location">
        <input hidden type="number" value="{{ $home_collection_fee }}" id="home_collection_fee">
        <input type="hidden" value="{{ $clinic_location }}" id="clinic_location">
        <input type="hidden" value="{{ $clinic_service_charge }}" id="clinic_service_charge">
        <input type="hidden" value="{{ $clinic_service_charge_key }}" id="clinic_service_charge_key">
        <input type="hidden" name="register_open" id="register_open" value="{{ $register_open }}">
        {!! Form::open([
            'url' => action([\App\Http\Controllers\SellPosController::class, 'store']),
            'method' => 'post',
            'id' => 'add_sell_form',
            'files' => true,
        ]) !!}
        @if (!empty($sale_type))
            <input type="hidden" id="sale_type" name="type" value="sell">
        @endif

        <div class="row">
            <div class="col-md-12 col-sm-12">
                @component('components.widget', ['class' => 'box-solid'])
                    {!! Form::hidden('location_id', $clinic_location, [
                        'id' => 'location_id',
                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                            ? $default_location->receipt_printer_type
                            : 'browser',
                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                    ]) !!}

                    {!! Form::hidden('location_id', $clinic_location, [
                        'id' => 'location_id',
                        'data-receipt_printer_type' => !empty($default_location->receipt_printer_type)
                            ? $default_location->receipt_printer_type
                            : 'browser',
                        'data-default_payment_accounts' => $default_location->default_payment_accounts ?? '',
                    ]) !!}
                    <input type="hidden" value="clinic" name="sale_point">
                    @if (!empty($price_groups))
                        @if (count($price_groups) > 1)
                            <div class="col-sm-3 hide">
                                <div class="form-group">
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fas fa-money-bill-alt"></i>
                                        </span>
                                        @php
                                            reset($price_groups);
                                            $selected_price_group =
                                                !empty($default_price_group_id) &&
                                                array_key_exists($default_price_group_id, $price_groups)
                                                    ? $default_price_group_id
                                                    : null;
                                        @endphp
                                        {!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
                                        {!! Form::select('price_group', $price_groups, $selected_price_group, [
                                            'class' => 'form-control select2',
                                            'id' => 'price_group',
                                            'disabled' => 'disabled',
                                        ]) !!}
                                        <span class="input-group-addon">
                                            @show_tooltip(__('lang_v1.price_group_help_text'))
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @else
                            @php
                                reset($price_groups);
                            @endphp
                            {!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
                        @endif
                    @endif


                    {!! Form::hidden('default_price_group', null, ['id' => 'default_price_group']) !!}
                    {!! Form::hidden('patient_session_id', $patient_session_id) !!}
                    {!! Form::hidden('only_therapy_bill', $only_therapy_bill) !!}
                    {!! Form::hidden('appointment_id', $appointment_id) !!}

                    @if (in_array('subscription', $enabled_modules))
                        <div class="col-md-3 pull-right col-sm-6 hide">
                            <div class="checkbox">
                                <label>
                                    {!! Form::checkbox('is_recurring', 1, false, ['class' => 'input-icheck', 'id' => 'is_recurring']) !!} @lang('lang_v1.subscribe')?
                                </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal"
                                    class="btn btn-link"><i
                                        class="fa fa-external-link"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
                            </div>
                        </div>
                    @endif

                    <div class="{{ $col_md }}">
                        <div class="form-group">
                            {!! Form::label('reference_id', 'Reference :*') !!}
                            <div class="input-group">
                                {!! Form::select('reference_id', $doctors, $doctor_id, [
                                    'class' => 'form-control select2',
                                    'id' => 'reference_id',
                                    'autofocus',
                                    'required',
                                    'placeholder' => 'Select a Reference ',
                                    'style' => 'min-width: 230px;',
                                ]) !!}
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat add_new_reference"
                                        data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                </span>
                            </div>
                        </div>
                    </div>
                    @can('admin')
                        <div class="{{ $col_md }}">
                            <div class="form-group">
                                {!! Form::label('transaction_date', __('clinic::lang.bill_date') . ':*') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    {!! Form::text('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']) !!}
                                </div>
                            </div>
                        </div>
                    @else
                        {!! Form::hidden('transaction_date', $default_datetime, ['class' => 'form-control', 'readonly', 'required']) !!}
                    @endcan
                    {{-- @if (!empty($status) && $status == 'draft') 
                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label('appointment_date', __('clinic::lang.appointment_date') . ':*') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    {!! Form::text('appointment_date', $default_datetime, ['class' => 'form-control',  'readonly', 'required']) !!}
                                </div>
                            </div>
                        </div>
                    @endif --}}
                    {{-- @if (in_array('types_of_service', $enabled_modules) && !empty($types_of_service))
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group">
                                {!! Form::label('types_of_service', __('Is Home Collection?')) !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-external-link-square-alt text-primary service_modal_btn"></i>
                                    </span>
                                    {!! Form::select('types_of_service_id', $types_of_service, null, [
                                        'class' => 'form-control',
                                        'id' => 'types_of_service_id',
                                        'style' => 'width: 100%;',
                                        'placeholder' => __('lang_v1.select_types_of_service'),
                                    ]) !!}

                                    {!! Form::hidden('types_of_service_price_group', null, ['id' => 'types_of_service_price_group']) !!}

                                    <span class="input-group-addon">
                                        @show_tooltip(__('lang_v1.types_of_service_help'))
                                    </span>
                                </div>
                                <small>
                                    <p class="help-block hide" id="price_group_text">@lang('lang_v1.price_group'): <span></span></p>
                                </small>
                            </div>
                        </div>
                        <div class="modal fade types_of_service_modal" tabindex="-1" role="dialog"
                            aria-labelledby="gridSystemModalLabel"></div>
                    @endif --}}

                    <div class="{{ $col_md }}">
                        <div class="form-group">
                            <div class="checkbox">
                                <br>
                                <label>
                                    {!! Form::checkbox('is_home_collection', 1, false, ['class' => 'home-collection', 'id' => 'is_home_collection']) !!}
                                    &nbsp; <strong>Home Collection</strong>
                                </label>
                            </div>
                            <small class="text-muted" style="display: block; margin-top: 5px;">Fee:
                                ৳{{ $home_collection_fee }}</small>
                        </div>
                    </div>

                    <div class="clearfix"></div>
                    <div class="@if (!empty($commission_agent)) col-sm-3 @else col-sm-4 @endif">
                        <div class="form-group">
                            {!! Form::label('contact_id', __('clinic::lang.patient') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-user"></i>
                                </span>
                                {{-- <input type="hidden" id="default_customer_id" value="{{ $walk_in_customer['id'] }}">
                                <input type="hidden" id="default_customer_name" value="{{ $walk_in_customer['name'] }}">
                                <input type="hidden" id="default_customer_balance"
                                    value="{{ $walk_in_customer['balance'] ?? '' }}">
                                <input type="hidden" id="default_customer_address"
                                    value="{{ $walk_in_customer['shipping_address'] ?? '' }}">
                                @if (!empty($walk_in_customer['price_calculation_type']) && $walk_in_customer['price_calculation_type'] == 'selling_price_group')
                                    <input type="hidden" id="default_selling_price_group"
                                        value="{{ $walk_in_customer['selling_price_group_id'] ?? '' }}">
                                @endif --}}
                                {!! Form::select('contact_id', $contact ? [$contact->id => $contact->name] : ['' => __('messages.search')], null, [
                                    'class' => 'form-control mousetrap',
                                    'id' => 'customer_id_clinic',
                                    'data-placeholder' => 'Enter Patient name / phone',
                                    'required',
                                ]) !!}
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat edit_patient_button"
                                        data-id="">
                                        <i class="glyphicon glyphicon-edit text-primary"></i>
                                    </button>
                                </span>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat add_new_patients"
                                        data-name=""><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                </span>
                            </div>
                            <small class="text-danger hide contact_due_text"><strong>@lang('account.customer_due'):</strong>
                                <span></span></small>
                        </div>
                        {{-- <small>
                            <strong>
                                @lang('lang_v1.billing_address'):
                            </strong>
                            <div id="billing_address_div">
                                {!! $walk_in_customer['contact_address'] ?? '' !!}
                            </div>
                            <br>
                            <strong>
                                @lang('lang_v1.shipping_address'):
                            </strong>
                            <div id="shipping_address_div">
                                {{ $walk_in_customer['supplier_business_name'] ?? '' }},<br>
                                {{ $walk_in_customer['name'] ?? '' }},<br>
                                {{ $walk_in_customer['shipping_address'] ?? '' }}
                            </div>
                        </small> --}}
                    </div>

                    <div class="col-md-3 hide">
                        <div class="form-group">
                            <div class="multi-input">
                                @php
                                    $is_pay_term_required = !empty($pos_settings['is_pay_term_required']);
                                @endphp
                                {!! Form::label('pay_term_number', __('contact.pay_term') . ':') !!} @show_tooltip(__('tooltip.pay_term'))
                                <br />
                                {!! Form::number('pay_term_number', $walk_in_customer['pay_term_number'] ?? '', [
                                    'class' => 'form-control width-40 pull-left',
                                    'placeholder' => __('contact.pay_term'),
                                    'required' => $is_pay_term_required,
                                ]) !!}

                                {!! Form::select(
                                    'pay_term_type',
                                    ['months' => __('lang_v1.months'), 'days' => __('lang_v1.days')],
                                    $walk_in_customer['pay_term_type'] ?? '',
                                    [
                                        'class' => 'form-control width-60 pull-left',
                                        'placeholder' => __('messages.please_select'),
                                        'required' => $is_pay_term_required,
                                    ],
                                ) !!}
                            </div>
                        </div>
                    </div>

                    @if (!empty($commission_agent))
                        @php
                            $is_commission_agent_required = !empty($pos_settings['is_commission_agent_required']);
                        @endphp
                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label('commission_agent', __('lang_v1.commission_agent') . ':') !!}
                                {!! Form::select('commission_agent', $commission_agent, null, [
                                    'class' => 'form-control select2',
                                    'id' => 'commission_agent',
                                    'required' => $is_commission_agent_required,
                                ]) !!}
                            </div>
                        </div>
                    @endif
                    <div class="col-sm-2">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('age', __('Age') . ':*') !!}
                                    {!! Form::number('age', null, ['class' => 'form-control', 'required', 'id' => 'age']) !!}
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    {!! Form::label('gender', __('Gender') . ':*') !!}
                                    {!! Form::select('gender', [''=>'Select Gender', 'male' => 'Male', 'female' => 'Female', 'other' => 'Other',], null, [
                                        'class' => 'form-control',
                                        'required',
                                        'id' => 'gender',
                                    ]) !!}
                                </div>
                            </div>
                        </div>

                    </div>
                    @if (!empty($status))
                        <input type="hidden" name="status" id="status" value="{{ $status }}">

                        {{-- @if (in_array($status, ['draft', 'quotation']))
						<input type="hidden" id="disable_qty_alert"> --}}
                        {{-- @endif  --}}
                    @else
                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label('status', __('sale.status') . ':*') !!}
                                {!! Form::select('status', array_map('ucfirst', $statuses), 'final', [
                                    'class' => 'form-control select2',
                                    'id' => 'status',
                                    'required',
                                ]) !!}
                            </div>
                        </div>
                    @endif
                    @if ($sale_type != 'sales_order')
                        <div class="col-sm-3 hide">
                            <div class="form-group">
                                {!! Form::label('invoice_scheme_id', __('invoice.invoice_scheme') . ':') !!}
                                {!! Form::select(
                                    'invoice_scheme_id',
                                    $invoice_schemes,
                                    isset($invoiceId) ? $invoiceId : (isset($default_invoice_schemes) ? $default_invoice_schemes->id : ''),
                                    [
                                        'class' => 'form-control select2',
                                        'placeholder' => __('messages.please_select'),
                                    ],
                                ) !!}
                            </div>
                        </div>
                    @endif

                    @can('edit_invoice_number')
                        <div class="col-sm-3 hide">
                            <div class="form-group">
                                {!! Form::label(
                                    'invoice_no',
                                    $sale_type == 'sales_order' ? __('restaurant.order_no') : __('sale.invoice_no') . ':',
                                ) !!}
                                {!! Form::text('invoice_no', null, [
                                    'class' => 'form-control',
                                    'placeholder' => $sale_type == 'sales_order' ? __('restaurant.order_no') : __('sale.invoice_no'),
                                ]) !!}
                                <p class="help-block">@lang('lang_v1.keep_blank_to_autogenerate')</p>
                            </div>
                        </div>
                    @endcan

                    @php
                        $custom_field_1_label = !empty($custom_labels['sell']['custom_field_1'])
                            ? $custom_labels['sell']['custom_field_1']
                            : '';

                        $is_custom_field_1_required =
                            !empty($custom_labels['sell']['is_custom_field_1_required']) &&
                            $custom_labels['sell']['is_custom_field_1_required'] == 1
                                ? true
                                : false;

                        $custom_field_2_label = !empty($custom_labels['sell']['custom_field_2'])
                            ? $custom_labels['sell']['custom_field_2']
                            : '';

                        $is_custom_field_2_required =
                            !empty($custom_labels['sell']['is_custom_field_2_required']) &&
                            $custom_labels['sell']['is_custom_field_2_required'] == 1
                                ? true
                                : false;

                        $custom_field_3_label = !empty($custom_labels['sell']['custom_field_3'])
                            ? $custom_labels['sell']['custom_field_3']
                            : '';

                        $is_custom_field_3_required =
                            !empty($custom_labels['sell']['is_custom_field_3_required']) &&
                            $custom_labels['sell']['is_custom_field_3_required'] == 1
                                ? true
                                : false;

                        $custom_field_4_label = !empty($custom_labels['sell']['custom_field_4'])
                            ? $custom_labels['sell']['custom_field_4']
                            : '';

                        $is_custom_field_4_required =
                            !empty($custom_labels['sell']['is_custom_field_4_required']) &&
                            $custom_labels['sell']['is_custom_field_4_required'] == 1
                                ? true
                                : false;
                    @endphp
                    @if (!empty($custom_field_1_label))
                        @php
                            $label_1 = $custom_field_1_label . ':';
                            if ($is_custom_field_1_required) {
                                $label_1 .= '*';
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('custom_field_1', $label_1) !!}
                                {!! Form::text('custom_field_1', null, [
                                    'class' => 'form-control',
                                    'placeholder' => $custom_field_1_label,
                                    'required' => $is_custom_field_1_required,
                                ]) !!}
                            </div>
                        </div>
                    @endif
                    @if (!empty($custom_field_2_label))
                        @php
                            $label_2 = $custom_field_2_label . ':';
                            if ($is_custom_field_2_required) {
                                $label_2 .= '*';
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('custom_field_2', $label_2) !!}
                                {!! Form::text('custom_field_2', null, [
                                    'class' => 'form-control',
                                    'placeholder' => $custom_field_2_label,
                                    'required' => $is_custom_field_2_required,
                                ]) !!}
                            </div>
                        </div>
                    @endif
                    @if (!empty($custom_field_3_label))
                        @php
                            $label_3 = $custom_field_3_label . ':';
                            if ($is_custom_field_3_required) {
                                $label_3 .= '*';
                            }
                        @endphp
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('custom_field_3', $label_3) !!}
                                {!! Form::text('custom_field_3', null, [
                                    'class' => 'form-control',
                                    'placeholder' => $custom_field_3_label,
                                    'required' => $is_custom_field_3_required,
                                ]) !!}
                            </div>
                        </div>
                    @endif
                    @if (!empty($custom_field_4_label))
                        @php
                            $label_4 = $custom_field_4_label . ':';
                            if ($is_custom_field_4_required) {
                                $label_4 .= '*';
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('custom_field_4', $label_4) !!}
                                {!! Form::text('custom_field_4', null, [
                                    'class' => 'form-control',
                                    'placeholder' => $custom_field_4_label,
                                    'required' => $is_custom_field_4_required,
                                ]) !!}
                            </div>
                        </div>
                    @endif
                    <div class="col-sm-3">
                        <div class="input-group">
                            {!! Form::label('sub_type', 'Billing Type :*') !!} <br>
                            <div class="form-group">

                                {!! Form::select(
                                    'sub_type',
                                    $sub_type,
                                    $only_therapy_bill == true ? 'therapy' : 'test',
                                    [
                                        'class' => 'form-control select2 ' . ($only_therapy_bill ? 'select_readonly_sub_type' : ''),
                                        'id' => 'sub_type',
                                        'required',
                                        'autofocus',
                                        'style' => 'min-width:80% !important',
                                    ],
                                    $bl_attributes,
                                ) !!}
                                <button class="btn btn-success billing_options_button"
                                    data-href="{{ action([\Modules\Clinic\Http\Controllers\ClinicProductController::class, 'showBillingOptions']) }}">Show
                                    Options</button>

                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>

                    @if ((!empty($pos_settings['enable_sales_order']) && $sale_type != 'sales_order') || $is_order_request_enabled)
                        <div class="col-sm-3">
                            <div class="form-group">
                                {!! Form::label('sales_order_ids', __('lang_v1.sales_order') . ':') !!}
                                {!! Form::select('sales_order_ids[]', [], null, [
                                    'class' => 'form-control select2',
                                    'multiple',
                                    'id' => 'sales_order_ids',
                                ]) !!}
                            </div>
                        </div>
                        <div class="clearfix"></div>
                    @endif
                    <!-- Call restaurant module if defined -->
                    @if (in_array('tables', $enabled_modules) || in_array('service_staff', $enabled_modules))
                        <span id="restaurant_module_span">
                        </span>
                    @endif

                @endcomponent

                @component('components.widget', ['class' => 'box-solid'])
                    <div class="col-sm-10 col-sm-offset-1">
                        <div class="form-group">
                            <div class="input-group">
                                <div class="input-group-btn">
                                    {{-- <button type="button" class="btn btn-default bg-white btn-flat disabled" data-toggle="modal"
                                        data-target="#configure_search_modal"
                                        title="{{ __('lang_v1.configure_product_search') }}"><i
                                            class="fas fa-search-plus"></i></button> --}}
                                    <button type="button" class="btn btn-default bg-white btn-flat disabled"
                                        data-toggle="modal" title="{{ __('lang_v1.configure_product_search') }}"><i
                                            class="fas fa-search-plus"></i></button>
                                </div>
                                {!! Form::text('search_product', null, [
                                    'class' => 'form-control mousetrap',
                                    'id' => 'search_product_clinic',
                                    'placeholder' => __('lang_v1.search_product_placeholder'),
                                    'disabled' => is_null($default_location) ? true : false,
                                    'autofocus' => is_null($default_location) ? false : true,
                                ]) !!}
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-default bg-white btn-flat disabled"><i
                                            class="fa fa-plus-circle text-primary fa-lg"></i></button>
                                    {{-- <button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product disabled"
                                        data-href="{{ action([\Modules\Clinic\Http\Controllers\ClinicProductController::class, 'quickAdd']) }}"
                                        data-container=".quick_add_product_modal_clinic"><i
                                            class="fa fa-plus-circle text-primary fa-lg"></i></button> --}}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row col-sm-12 pos_product_div" style="min-height: 0">

                        <input type="hidden" name="sell_price_tax" id="sell_price_tax"
                            value="{{ $business_details->sell_price_tax }}">

                        <!-- Keeps count of product rows -->
                        <input type="hidden" id="product_row_count" value="0">
                        @php
                            $hide_tax = '';
                            if (session()->get('business.enable_inline_tax') == 0) {
                                $hide_tax = 'hide';
                            }
                        @endphp
                        <div class="table-responsive">
                            <table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
                                <thead>
                                    <tr>
                                        <th class="text-center">
                                            @lang('sale.product')
                                        </th>
                                        <th class="text-center">
                                            @lang('sale.qty')
                                        </th>
                                        @if (!empty($pos_settings['inline_service_staff']))
                                            <th class="text-center">
                                                @lang('restaurant.service_staff')
                                            </th>
                                        @endif
                                        <th class="">
                                            @lang('sale.unit_price')
                                        </th>
                                        <th class="@if (!auth()->user()->can('edit_product_discount_from_sale_screen')) hide @endif">
                                            @lang('receipt.discount')
                                        </th>
                                        <th class="text-center {{ $hide_tax }}">
                                            @lang('sale.tax')
                                        </th>
                                        <th class="text-center {{ $hide_tax }}">
                                            @lang('sale.price_inc_tax')
                                        </th>
                                        @if (!empty($common_settings['enable_product_warranty']))
                                            <th>@lang('lang_v1.warranty')</th>
                                        @endif
                                        <th class="text-center">
                                            @lang('sale.subtotal')
                                        </th>
                                        <th class="text-center"><i class="fas fa-times" aria-hidden="true"></i></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-condensed table-bordered table-striped">
                                <tr>
                                    <td>
                                        <div class="pull-left" id="category_subtotals">
                                            <!-- Category subtotals will be inserted here -->
                                        </div>
                                        <div class="pull-right">
                                            <b>@lang('sale.item'):</b>
                                            <span class="total_quantity">0</span>
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <div class="pull-right">
                                                <b>Subtotal:</b> <span class="subtotal_with_discount"></span> <br>
                                                <b>Discount:</b> <span class="discount_amount_in_pos">0</span> <br>
                                                <b>@lang('sale.total'): </b>
                                                <span class="price_total">0</span>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endcomponent
                @component('components.widget', ['class' => 'box-solid'])
                    <div class="col-md-4  @if ($sale_type == 'sales_order') hide @endif">
                        <div class="form-group">
                            {!! Form::label('discount_type', __('sale.discount_type') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                {!! Form::select(
                                    'discount_type',
                                    ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')],
                                    'percentage',
                                    [
                                        'class' => 'form-control',
                                        'placeholder' => __('messages.please_select'),
                                        'required',
                                        'data-default' => 'percentage',
                                    ],
                                ) !!}
                            </div>
                        </div>
                    </div>
                    @php
                        $max_discount = !is_null(auth()->user()->max_sales_discount_percent)
                            ? auth()->user()->max_sales_discount_percent
                            : '';

                        //if sale discount is more than user max discount change it to max discount
                        $sales_discount = $business_details->default_sales_discount;
                        if ($max_discount != '' && $sales_discount > $max_discount) {
                            $sales_discount = $max_discount;
                        }

                        $default_sales_tax = $business_details->default_sales_tax;

                        if ($sale_type == 'sales_order') {
                            $sales_discount = 0;
                            $default_sales_tax = null;
                        }
                    @endphp
                    <div class="col-md-4 @if ($sale_type == 'sales_order') hide @endif">
                        <div class="form-group">
                            {!! Form::label('discount_amount', __('sale.discount_amount') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                {!! Form::text('discount_amount', @num_format($sales_discount), [
                                    'class' => 'form-control input_number',
                                    'data-default' => $sales_discount,
                                    'data-max-discount' => $max_discount,
                                    'data-max-discount-error_msg' => __('lang_v1.max_discount_error_msg', [
                                        'discount' => $max_discount != '' ? @num_format($max_discount) : '',
                                    ]),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 @if ($sale_type == 'sales_order') hide @endif"><br>
                        <b>@lang('sale.discount_amount'):</b>(-)
                        <span class="display_currency" id="total_discount">0</span> <br>
                        <span style="color: red" id="discount_alert"></span>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-12 well well-sm bg-light-gray @if (session('business.enable_rp') != 1 || $sale_type == 'sales_order') hide @endif">
                        <input type="hidden" name="rp_redeemed" id="rp_redeemed" value="0">
                        <input type="hidden" name="rp_redeemed_amount" id="rp_redeemed_amount" value="0">
                        <div class="col-md-12">
                            <h4>{{ session('business.rp_name') }}</h4>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('rp_redeemed_modal', __('lang_v1.redeemed') . ':') !!}
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-gift"></i>
                                    </span>
                                    {!! Form::number('rp_redeemed_modal', 0, [
                                        'class' => 'form-control direct_sell_rp_input',
                                        'data-amount_per_unit_point' => session('business.redeem_amount_per_unit_rp'),
                                        'min' => 0,
                                        'data-max_points' => 0,
                                        'data-min_order_total' => session('business.min_order_total_for_redeem'),
                                    ]) !!}
                                    <input type="hidden" id="rp_name" value="{{ session('business.rp_name') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <p><strong>@lang('lang_v1.available'):</strong> <span id="available_rp">0</span></p>
                        </div>
                        <div class="col-md-4">
                            <p><strong>@lang('lang_v1.redeemed_amount'):</strong> (-)<span id="rp_redeemed_amount_text">0</span></p>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-4  @if ($sale_type == 'sales_order') hide @endif">
                        <div class="form-group">
                            {!! Form::label('tax_rate_id', __('sale.order_tax') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                {!! Form::select(
                                    'tax_rate_id',
                                    $taxes['tax_rates'],
                                    $default_sales_tax,
                                    ['placeholder' => __('messages.please_select'), 'class' => 'form-control', 'data-default' => $default_sales_tax],
                                    $taxes['attributes'],
                                ) !!}

                                <input type="hidden" name="tax_calculation_amount" id="tax_calculation_amount"
                                    value="@if (empty($edit)) {{ @num_format($business_details->tax_calculation_amount) }} @else {{ @num_format($transaction->tax?->amount) }} @endif"
                                    data-default="{{ $business_details->tax_calculation_amount }}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-md-offset-4  @if ($sale_type == 'sales_order') hide @endif">
                        <b>@lang('sale.order_tax'):</b>(+)
                        <span class="display_currency" id="order_tax">0</span>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('sell_note', __('sale.remarks')) !!}
                            {!! Form::textarea('sale_note', null, ['class' => 'form-control', 'rows' => 3]) !!}
                        </div>
                    </div>
                    <input type="hidden" name="is_direct_sale" value="0">
                @endcomponent
                @component('components.widget', ['class' => 'box-solid hide', 'id' => 'shipping_section'])
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('shipping_details', __('sale.shipping_details')) !!}
                            {!! Form::textarea('shipping_details', null, [
                                'class' => 'form-control',
                                'placeholder' => __('sale.shipping_details'),
                                'rows' => '3',
                                'cols' => '30',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('shipping_address', __('lang_v1.shipping_address')) !!}
                            {!! Form::textarea('shipping_address', null, [
                                'class' => 'form-control',
                                'placeholder' => __('lang_v1.shipping_address'),
                                'rows' => '3',
                                'cols' => '30',
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('shipping_charges', __('sale.shipping_charges')) !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-info"></i>
                                </span>
                                {!! Form::text('shipping_charges', @num_format(0.0), [
                                    'class' => 'form-control input_number',
                                    'id' => 'shipping_charges',
                                    'placeholder' => __('sale.shipping_charges'),
                                ]) !!}
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('shipping_status', __('lang_v1.shipping_status')) !!}
                            {!! Form::select('shipping_status', $shipping_statuses, null, [
                                'class' => 'form-control',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('delivered_to', __('lang_v1.delivered_to') . ':') !!}
                            {!! Form::text('delivered_to', null, ['class' => 'form-control', 'placeholder' => __('lang_v1.delivered_to')]) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('delivery_person', __('lang_v1.delivery_person') . ':') !!}
                            {!! Form::select('delivery_person', $users, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%;',
                                'placeholder' => __('messages.please_select'),
                            ]) !!}
                        </div>
                    </div>
                    @php
                        $shipping_custom_label_1 = !empty($custom_labels['shipping']['custom_field_1'])
                            ? $custom_labels['shipping']['custom_field_1']
                            : '';

                        $is_shipping_custom_field_1_required =
                            !empty($custom_labels['shipping']['is_custom_field_1_required']) &&
                            $custom_labels['shipping']['is_custom_field_1_required'] == 1
                                ? true
                                : false;

                        $shipping_custom_label_2 = !empty($custom_labels['shipping']['custom_field_2'])
                            ? $custom_labels['shipping']['custom_field_2']
                            : '';

                        $is_shipping_custom_field_2_required =
                            !empty($custom_labels['shipping']['is_custom_field_2_required']) &&
                            $custom_labels['shipping']['is_custom_field_2_required'] == 1
                                ? true
                                : false;

                        $shipping_custom_label_3 = !empty($custom_labels['shipping']['custom_field_3'])
                            ? $custom_labels['shipping']['custom_field_3']
                            : '';

                        $is_shipping_custom_field_3_required =
                            !empty($custom_labels['shipping']['is_custom_field_3_required']) &&
                            $custom_labels['shipping']['is_custom_field_3_required'] == 1
                                ? true
                                : false;

                        $shipping_custom_label_4 = !empty($custom_labels['shipping']['custom_field_4'])
                            ? $custom_labels['shipping']['custom_field_4']
                            : '';

                        $is_shipping_custom_field_4_required =
                            !empty($custom_labels['shipping']['is_custom_field_4_required']) &&
                            $custom_labels['shipping']['is_custom_field_4_required'] == 1
                                ? true
                                : false;

                        $shipping_custom_label_5 = !empty($custom_labels['shipping']['custom_field_5'])
                            ? $custom_labels['shipping']['custom_field_5']
                            : '';

                        $is_shipping_custom_field_5_required =
                            !empty($custom_labels['shipping']['is_custom_field_5_required']) &&
                            $custom_labels['shipping']['is_custom_field_5_required'] == 1
                                ? true
                                : false;
                    @endphp

                    @if (!empty($shipping_custom_label_1))
                        @php
                            $label_1 = $shipping_custom_label_1 . ':';
                            if ($is_shipping_custom_field_1_required) {
                                $label_1 .= '*';
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_custom_field_1', $label_1) !!}
                                {!! Form::text(
                                    'shipping_custom_field_1',
                                    !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_1'])
                                        ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_1']
                                        : null,
                                    [
                                        'class' => 'form-control',
                                        'placeholder' => $shipping_custom_label_1,
                                        'required' => $is_shipping_custom_field_1_required,
                                    ],
                                ) !!}
                            </div>
                        </div>
                    @endif
                    @if (!empty($shipping_custom_label_2))
                        @php
                            $label_2 = $shipping_custom_label_2 . ':';
                            if ($is_shipping_custom_field_2_required) {
                                $label_2 .= '*';
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_custom_field_2', $label_2) !!}
                                {!! Form::text(
                                    'shipping_custom_field_2',
                                    !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_2'])
                                        ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_2']
                                        : null,
                                    [
                                        'class' => 'form-control',
                                        'placeholder' => $shipping_custom_label_2,
                                        'required' => $is_shipping_custom_field_2_required,
                                    ],
                                ) !!}
                            </div>
                        </div>
                    @endif
                    @if (!empty($shipping_custom_label_3))
                        @php
                            $label_3 = $shipping_custom_label_3 . ':';
                            if ($is_shipping_custom_field_3_required) {
                                $label_3 .= '*';
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_custom_field_3', $label_3) !!}
                                {!! Form::text(
                                    'shipping_custom_field_3',
                                    !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_3'])
                                        ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_3']
                                        : null,
                                    [
                                        'class' => 'form-control',
                                        'placeholder' => $shipping_custom_label_3,
                                        'required' => $is_shipping_custom_field_3_required,
                                    ],
                                ) !!}
                            </div>
                        </div>
                    @endif
                    @if (!empty($shipping_custom_label_4))
                        @php
                            $label_4 = $shipping_custom_label_4 . ':';
                            if ($is_shipping_custom_field_4_required) {
                                $label_4 .= '*';
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_custom_field_4', $label_4) !!}
                                {!! Form::text(
                                    'shipping_custom_field_4',
                                    !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_4'])
                                        ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_4']
                                        : null,
                                    [
                                        'class' => 'form-control',
                                        'placeholder' => $shipping_custom_label_4,
                                        'required' => $is_shipping_custom_field_4_required,
                                    ],
                                ) !!}
                            </div>
                        </div>
                    @endif
                    @if (!empty($shipping_custom_label_5))
                        @php
                            $label_5 = $shipping_custom_label_5 . ':';
                            if ($is_shipping_custom_field_5_required) {
                                $label_5 .= '*';
                            }
                        @endphp

                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('shipping_custom_field_5', $label_5) !!}
                                {!! Form::text(
                                    'shipping_custom_field_5',
                                    !empty($walk_in_customer['shipping_custom_field_details']['shipping_custom_field_5'])
                                        ? $walk_in_customer['shipping_custom_field_details']['shipping_custom_field_5']
                                        : null,
                                    [
                                        'class' => 'form-control',
                                        'placeholder' => $shipping_custom_label_5,
                                        'required' => $is_shipping_custom_field_5_required,
                                    ],
                                ) !!}
                            </div>
                        </div>
                    @endif
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('shipping_documents', __('lang_v1.shipping_documents') . ':') !!}
                            {!! Form::file('shipping_documents[]', [
                                'id' => 'shipping_documents',
                                'multiple',
                                'accept' => implode(',', array_keys(config('constants.document_upload_mimes_types'))),
                            ]) !!}
                            <p class="help-block">
                                @lang('purchase.max_file_size', ['size' => config('constants.document_size_limit') / 1000000])
                                @includeIf('components.document_help_text')
                            </p>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-12 text-center">
                        <button type="button" class="btn btn-primary btn-sm" id="toggle_additional_expense"> <i
                                class="fas fa-plus"></i> @lang('lang_v1.add_additional_expenses') <i class="fas fa-chevron-down"></i></button>
                    </div>
                    <div class="col-md-8 col-md-offset-4" id="additional_expenses_div" style="display: none;">
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <th>@lang('lang_v1.additional_expense_name')</th>
                                    <th>@lang('sale.amount')</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        {!! Form::text('additional_expense_key_1', $clinic_service_charge_key, [
                                            'class' => 'form-control',
                                            'id' => 'additional_expense_key_1',
                                        ]) !!}
                                    </td>
                                    <td>
                                        {!! Form::text('additional_expense_value_1', 0, [
                                            'class' => 'form-control input_number',
                                            'id' => 'additional_expense_value_1',
                                        ]) !!}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        {!! Form::text('additional_expense_key_2', null, [
                                            'class' => 'form-control',
                                            'id' => 'additional_expense_key_2',
                                        ]) !!}
                                    </td>
                                    <td>
                                        {!! Form::text('additional_expense_value_2', 0, [
                                            'class' => 'form-control input_number',
                                            'id' => 'additional_expense_value_2',
                                        ]) !!}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        {!! Form::text('additional_expense_key_3', null, [
                                            'class' => 'form-control',
                                            'id' => 'additional_expense_key_3',
                                        ]) !!}
                                    </td>
                                    <td>
                                        {!! Form::text('additional_expense_value_3', 0, [
                                            'class' => 'form-control input_number',
                                            'id' => 'additional_expense_value_3',
                                        ]) !!}
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        {!! Form::text('additional_expense_key_4', null, [
                                            'class' => 'form-control',
                                            'id' => 'additional_expense_key_4',
                                        ]) !!}
                                    </td>
                                    <td>
                                        {!! Form::text('additional_expense_value_4', 0, [
                                            'class' => 'form-control input_number',
                                            'id' => 'additional_expense_value_4',
                                        ]) !!}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-4 col-md-offset-8">
                        @if (!empty($pos_settings['amount_rounding_method']) && $pos_settings['amount_rounding_method'] > 0)
                            <small id="round_off"><br>(@lang('lang_v1.round_off'): <span id="round_off_text">0</span>)</small>
                            <br />
                            <input type="hidden" name="round_off_amount" id="round_off_amount" value=0>
                        @endif
                        <div><b>@lang('sale.total_payable'): </b>
                            <input type="hidden" name="final_total" id="final_total_input">
                            <span id="total_payable">0</span>
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
        @if (!empty($common_settings['is_enabled_export']) && $sale_type != 'sales_order')
            @component('components.widget', ['class' => 'box-solid', 'title' => __('lang_v1.export')])
                <div class="col-md-12 mb-12">
                    <div class="form-check">
                        <input type="checkbox" name="is_export" class="form-check-input" id="is_export"
                            @if (!empty($walk_in_customer['is_export'])) checked @endif>
                        <label class="form-check-label" for="is_export">@lang('lang_v1.is_export')</label>
                    </div>
                </div>
                @php
                    $i = 1;
                @endphp
                @for ($i; $i <= 6; $i++)
                    <div class="col-md-4 export_div" @if (empty($walk_in_customer['is_export'])) style="display: none;" @endif>
                        <div class="form-group">
                            {!! Form::label('export_custom_field_' . $i, __('lang_v1.export_custom_field' . $i) . ':') !!}
                            {!! Form::text(
                                'export_custom_fields_info[' . 'export_custom_field_' . $i . ']',
                                !empty($walk_in_customer['export_custom_field_' . $i]) ? $walk_in_customer['export_custom_field_' . $i] : null,
                                [
                                    'class' => 'form-control',
                                    'placeholder' => __('lang_v1.export_custom_field' . $i),
                                    'id' => 'export_custom_field_' . $i,
                                ],
                            ) !!}
                        </div>
                    </div>
                @endfor
            @endcomponent
        @endif
        @php
            $is_enabled_download_pdf = config('constants.enable_download_pdf');
            $payment_body_id = 'payment_rows_div';
            if ($is_enabled_download_pdf) {
                $payment_body_id = '';
            }
        @endphp
        @if (
            (empty($status) || !in_array($status, ['quotation', 'draft']) || $is_enabled_download_pdf) &&
                $sale_type != 'sales_order')
            @can('sell.payments')
                @component('components.widget', [
                    'class' => 'box-solid',
                    'id' => $payment_body_id,
                    'title' => __('purchase.add_payment'),
                ])
                    @if ($is_enabled_download_pdf)
                        <div class="well row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('prefer_payment_method', __('lang_v1.prefer_payment_method') . ':') !!}
                                    @show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fas fa-money-bill-alt"></i>
                                        </span>
                                        {!! Form::select('prefer_payment_method', $payment_types, 'cash', [
                                            'class' => 'form-control',
                                            'style' => 'width:100%;',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('prefer_payment_account', __('lang_v1.prefer_payment_account') . ':') !!}
                                    @show_tooltip(__('lang_v1.this_will_be_shown_in_pdf'))
                                    <div class="input-group">
                                        <span class="input-group-addon">
                                            <i class="fas fa-money-bill-alt"></i>
                                        </span>
                                        {!! Form::select('prefer_payment_account', $accounts, null, [
                                            'class' => 'form-control',
                                            'style' => 'width:100%;',
                                        ]) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (empty($status) || !in_array($status, ['quotation', 'draft']))
                        <div class="payment_row" @if ($is_enabled_download_pdf) id="payment_rows_div" @endif>
                            <div class="row">
                                <div class="col-md-12 mb-12">
                                    <strong>@lang('lang_v1.advance_balance'):</strong> <span id="advance_balance_text"></span>
                                    {!! Form::hidden('advance_balance', null, [
                                        'id' => 'advance_balance',
                                        'data-error-msg' => __('lang_v1.required_advance_balance_not_available'),
                                    ]) !!}
                                </div>
                                @if ($clinic_service_charge == 1)
                                    <div class="col-md-12 mb-12">
                                        <strong>{{ $clinic_service_charge_key }} Charge: </strong> <span
                                            id="service_charge_text">0.00</span>
                                    </div>
                                @endif
                            </div>
                            @include('clinic::sell.partials.payment_row_form', [
                                'row_index' => 0,
                                'show_date' => true,
                                'show_denomination' => true,
                            ])
                        </div>
                        <div class="payment_row">
                            <div class="row">
                                <div class="col-md-12">
                                    <hr>
                                    <strong>
                                        @lang('lang_v1.change_return'):
                                    </strong>
                                    <br />
                                    <span class="lead text-bold change_return_span">0</span>
                                    {!! Form::hidden('change_return', $change_return['amount'], [
                                        'class' => 'form-control change_return input_number',
                                        'required',
                                        'id' => 'change_return',
                                    ]) !!}
                                    <!-- <span class="lead text-bold total_quantity">0</span> -->
                                    @if (!empty($change_return['id']))
                                        <input type="hidden" name="change_return_id" value="{{ $change_return['id'] }}">
                                    @endif
                                </div>
                            </div>
                            <div class="row hide payment_row" id="change_return_payment_data">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('change_return_method', __('lang_v1.change_return_payment_method') . ':*') !!}
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="fas fa-money-bill-alt"></i>
                                            </span>
                                            @php
                                                $_payment_method =
                                                    empty($change_return['method']) &&
                                                    array_key_exists('cash', $payment_types)
                                                        ? 'cash'
                                                        : $change_return['method'];

                                                $_payment_types = $payment_types;
                                                if (isset($_payment_types['advance'])) {
                                                    unset($_payment_types['advance']);
                                                }
                                            @endphp
                                            {!! Form::select('payment[change_return][method]', $_payment_types, $_payment_method, [
                                                'class' => 'form-control col-md-12 payment_types_dropdown',
                                                'id' => 'change_return_method',
                                                'style' => 'width:100%;',
                                            ]) !!}
                                        </div>
                                    </div>
                                </div>
                                @if (!empty($accounts))
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            {!! Form::label('change_return_account', __('lang_v1.change_return_payment_account') . ':') !!}
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <i class="fas fa-money-bill-alt"></i>
                                                </span>
                                                {!! Form::select(
                                                    'payment[change_return][account_id]',
                                                    $accounts,
                                                    !empty($change_return['account_id']) ? $change_return['account_id'] : '',
                                                    ['class' => 'form-control select2', 'id' => 'change_return_account', 'style' => 'width:100%;'],
                                                ) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @include('clinic::patient.patients.payment.payment_type_details', [
                                    'payment_line' => $change_return,
                                    'row_index' => 'change_return',
                                ])
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="pull-right"><strong>@lang('lang_v1.balance'):</strong> <span
                                            class="balance_due">0.00</span></div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endcomponent
            @endcan
        @endif

        <div class="row">
            {!! Form::hidden('is_save_and_print', 0, ['id' => 'is_save_and_print']) !!}
            <div class="col-sm-12 text-center">
                <button type="button" id="submit-sell" class="btn btn-primary btn-big">@lang('messages.save')</button>
                <button type="button" id="save-and-print" class="btn btn-success btn-big">@lang('lang_v1.save_and_print')</button>
            </div>
        </div>

        @if (empty($pos_settings['disable_recurring_invoice']))
            @include('clinic::services.recurring_invoice_modal')
        @endif

        {!! Form::close() !!}
    </section>
    <div class="modal fade billing_type_options_modal" tabindex="-1" role="dialog"
        aria-labelledby="gridSystemModalLabel"></div>

    <!-- /.content -->
    <div class="modal fade register_details_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade close_register_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade patient_add_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('clinic::patient.patients.partials.add_patient')
    </div>
    <div class="modal fade edit_contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
    <div class="modal fade doctor_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        @include('clinic::clinic_reference.add_reference')
    </div>

    <!-- quick product modal -->
    <div class="modal fade quick_add_product_modal_clinic" tabindex="-1" role="dialog" aria-labelledby="modalTitle">
    </div>
    @include('clinic::sell.partials.configure_search_modal')

@stop

@section('javascript')
    <script>
        var discountRules = @json($discount_rules);
        var enable_discount_rules = @json($enable_discount_rules);
    </script>
    <script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>

    <!-- Call restaurant module if defined -->
    @if (in_array('tables', $enabled_modules) ||
            in_array('modifiers', $enabled_modules) ||
            in_array('service_staff', $enabled_modules))
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
    <script type="text/javascript">
        $(document).ready(function() {
            var contactId = '{{ $contact?->id }}';
            if (contactId) {
                setTimeout(function() {
                    $('#customer_id_clinic').val(contactId).trigger('change');
                }, 500);
            }
            var register_open = $('#register_open').val();
            if (register_open == 1) {
                swal({
                    title: 'New Register Open',
                    text: 'New Register Open Successfully',
                    icon: 'success',
                });

            }
            $('#status').change(function() {
                if ($(this).val() == 'final') {
                    $('#payment_rows_div').removeClass('hide');
                } else {
                    $('#payment_rows_div').addClass('hide');
                }
            });
            $('.paid_on').datetimepicker({
                format: moment_date_format + ' ' + moment_time_format,
                ignoreReadonly: true,
            });

            $('#shipping_documents').fileinput({
                showUpload: false,
                showPreview: false,
                browseLabel: LANG.file_browse_label,
                removeLabel: LANG.remove,
            });
            // Function to handle discount type change
            function handleDiscountTypeChange() {
                var discountType = $('#discount_type').val();
                var discountAmountInput = $('#discount_amount');

                if (discountType === 'percentage') {
                    discountAmountInput.attr('max', 100);
                } else if (discountType === 'fixed') {
                    discountAmountInput.removeAttr('max');
                }
            }

            // Run on page load
            handleDiscountTypeChange();

            // Run on discount type change
            $('#discount_type').on('change', function() {
                handleDiscountTypeChange();
            });
            $(document).on('change', '#prefer_payment_method', function() {
                var default_accounts = $('#location_id').data('default_payment_accounts') || {};
                var payment_type = $(this).val();
                var default_account = default_accounts[payment_type]?.account || '';
                $('select#prefer_payment_account').val(default_account).change();
            });

            function setPreferredPaymentMethodDropdown() {
                var payment_settings = $('#location_id').data('default_payment_accounts');
                payment_settings = payment_settings ? payment_settings : [];
                enabled_payment_types = [];
                for (var key in payment_settings) {
                    if (payment_settings[key] && payment_settings[key]['is_enabled']) {
                        enabled_payment_types.push(key);
                    }
                }
                if (enabled_payment_types.length) {
                    $("#prefer_payment_method > option").each(function() {
                        if (enabled_payment_types.indexOf($(this).val()) != -1) {
                            $(this).removeClass('hide');
                        } else {
                            $(this).addClass('hide');
                        }
                    });
                }
            }

            setPreferredPaymentMethodDropdown();

            $('#is_export').on('change', function() {
                if ($(this).is(':checked')) {
                    $('div.export_div').show();
                } else {
                    $('div.export_div').hide();
                }
            });

            if ($('.payment_types_dropdown').length) {
                $('.payment_types_dropdown').change();
            }
        });
        $('#types_of_service_id').on('change', function() {
            $('#shipping_section').removeClass('hide');
        });
        $(document).ready(function() {
            var price_total = 0;
            $('#select_location_id').on('ifChanged', function() {
                $('.home-collection').prop('checked', false);
                price_total = 0;
                $('#shipping_charges').val(price_total.toFixed(2));
            });
            $(document).on('click', '.add_new_reference', function() {
                $('#customer_id').select2('close');
                var name = $(this).data('name');
                $('.doctor_modal').find('input#name').val(name);
                $('.doctor_modal')
                    .find('select#contact_type')
                    .val('customer')
                    .closest('div.contact_type_div')
                    .addClass('hide');
                $('.doctor_modal').modal('show');
            });

        });
        $(document).on('click', '.more_btn', function() {
            $("div").find('.add_more_info_customer').toggleClass('hide');
        });


        $(document).on('click', '.add_new_patients', function() {
            $('#customer_id_clinic').select2('close');
            var name = $(this).data('name');
            $('.patient_add_modal').find('input#name').val(name);
            $('.patient_add_modal')
                .find('select#contact_type')
                .val('customer')
                .closest('div.contact_type_div')
                .addClass('hide');
            $('.patient_add_modal').modal('show');
            if ($.isNumeric(name)) {
                $('.first_name').val('');
                $('.mobile').val(name);
            } else {
                $('.mobile').val('');
                $('.first_name').val(name);
            }
        });
        $(document).on('click', '.is_doctor', function() {
            var isChecked = $(this).prop('checked');
            var target = $(this).data('target');
            if (isChecked) {
                $(target).removeClass('hide');
            } else {
                $(target).addClass('hide');
            }
        });

        $(document).on('click', '.billing_options_button', function(e) {
            e.preventDefault();
            var href = $(this).data('href');
            var subTypeValue = $('#sub_type').val(); // Get the value of sub_type input

            // Make an Ajax request
            $.ajax({
                url: href,
                method: 'GET',
                data: {
                    sub_type: subTypeValue // Send the sub_type value to the controller
                },
                success: function(response) {
                    // Load the response content into the modal and show it
                    $('.billing_type_options_modal').html(response).modal('show');

                    // Initialize the DataTable after the modal is loaded
                    $('#billing_options_table').DataTable({
                        paging: true,
                        searching: true,
                        info: true,
                        lengthChange: false,
                        pageLength: 15,
                        dom: 'Bfrtip',
                    });
                }
            });
        });
        $(document).on('change', '#customer_id_clinic', function() {
            var contact_id = $(this).val();
            $.ajax({
                url: '/check-patient-prescription/' + contact_id,
                type: 'GET',
                success: function(response) {
                    if (response.success == true) {
                        $('#reference_id').val(response.doctor_id).trigger('change');
                        $('#age').val(response.age);
                        $('#gender').val(response.gender);
                    } else {
                        $('#reference_id').val('').trigger('change');
                    }
                }
            })
        });
    </script>

@endsection
