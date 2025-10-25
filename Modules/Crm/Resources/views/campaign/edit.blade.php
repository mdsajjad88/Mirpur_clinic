@extends('layouts.app')

@section('title', __('crm::lang.campaigns'))
@section('css')
    <style>
        .contact_div_height {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
        }

        #contact_section {
            margin-left: 2px;
            display: flex;
            flex-wrap: wrap;
            /* Allows divs to wrap to a new line */
            gap: 5px;
        }
        .contact-col {

            display: flex;
        }
    </style>
@endsection
@section('content')
    @include('crm::layouts.nav')
    <!-- Content Header (Page header) -->
    <section class="content-header no-print">
        <h1>
            @lang('crm::lang.campaigns')
            <small>@lang('messages.edit')</small>
        </h1>
    </section>
    <section class="content no-print">
        <div class="box box-solid">
            <div class="box-body">
                {!! Form::open([
                    'url' => action([\Modules\Crm\Http\Controllers\CampaignController::class, 'update'], ['campaign' => $campaign->id]),
                    'method' => 'put',
                    'id' => 'campaign_form',
                ]) !!}
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            {!! Form::label('name', __('crm::lang.campaign_name') . ':*') !!}
                            {!! Form::text('name', $campaign->name, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('campaign_type', __('crm::lang.campaign_type') . ':*') !!}
                            {!! Form::select(
                                'campaign_type',
                                ['sms' => __('crm::lang.sms'), 'email' => __('business.email')],
                                $campaign->campaign_type,
                                [
                                    'class' => 'form-control select2',
                                    'placeholder' => __('messages.please_select'),
                                    'required',
                                    'style' => 'width: 100%;',
                                ],
                            ) !!}
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            {!! Form::label('to', __('crm::lang.to') . ':*') !!}
                            {!! Form::select(
                                'to',
                                [
                                    'customer' => __('lang_v1.customers'),
                                    'lead' => __('crm::lang.leads'),
                                    'transaction_activity' => __('crm::lang.transaction_activity'),
                                    'contact' => __('contact.contact'),
                                ],
                                $campaign->additional_info['to'] ?? null,
                                [
                                    'class' => 'form-control select2 select_to',
                                    'placeholder' => __('messages.please_select'),
                                    'required',
                                    'style' => 'width: 100%;',
                                ],
                            ) !!}
                        </div>
                    </div>
                    
                    <div class="col-md-3 transaction_activity_div" style="display: none;">
                        <div class="form-group">
                            {!! Form::label('trans_activity', __('crm::lang.transaction_activity') . ':*') !!}
                            {!! Form::select(
                                'trans_activity',
                                [
                                    'has_transactions' => __('crm::lang.has_transactions'),
                                    'has_no_transactions' => __('crm::lang.has_no_transactions'),
                                ],
                                $campaign->additional_info['trans_activity'] ?? null,
                                ['class' => 'form-control select2 transactions_count', 'required', 'style' => 'width: 100%;'],
                            ) !!}
                        </div>
                    </div>
                    <div class="col-md-3 transaction_activity_div" style="display: none;">
                        <div class="form-group">
                            <label for="in_days">{{ __('crm::lang.in_days') }}:*</label>
                            <div class="input-group">
                                <div class="input-group-addon">{{ __('crm::lang.in') }}</div>
                                <input type="text" class="form-control input_number transactions_days" id="in_days" placeholder="0"
                                    name="in_days" value="{{$campaign->additional_info['in_days'] ?? null}}" required >
                                <div class="input-group-addon">{{ __('lang_v1.days') }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 customer_group_id" style="display: none;">
                        <div class="form-group">
                            {!! Form::label('customer_group_id', 'Insert '.__('lang_v1.customer_group') . ':') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-users"></i>
                                </span>
                                {!! Form::select('customer_group_id', $customer_groups, '', ['class' => 'form-control customer_group']) !!}
                            </div>
                          </div>
                    </div>
                    <div class="col-md-2">

                        <button type="button" style="margin-top:25px;" class="btn btn-info select-all">Generare Contacts</button>
                    </div>
                    </div>
                    <div class="row">
                    <div class="col-md-8 customer_div" style="">
                        <div class="form-group" style="margin-bottom: 2px;">
                            <div class="" style="margin-bottom: 2px;">
                                <button type="button" class="btn btn-primary btn-xs deselect-all">
                                    @lang('lang_v1.deselect_all')
                                </button>
                            </div>
                            <div class="input-group search_product" style="display: none;">
                                <span class="input-group-addon">
                                    <i class="fa fa-search"></i>
                                </span>
                                {!! Form::text('search_product', null, [
                                    'class' => 'form-control',
                                    'id' => 'search_customer',
                                    'placeholder' => __('stock_adjustment.search_product'),
                                ]) !!}
                            </div>
                        </div>
                        <div class="contact_div_height">


                            <input type="hidden" id="contact_row_index" value="0">
                            <input type="hidden" id="total_amount" name="final_total" value="0">
                            <div class="row" id="contact_section">
                                @foreach ($contacts as $item)
                                <div style="margin:0 2px 2px 0;" class="tr">
                                    <input type="hidden" name="contact_id[]" value="{{$item->id}}">
                                    <button type="button" class="btn btn-info btn-xs">
                                        <span class="remove_contact_row" style="margin-right: 2px;">×</span>
                                       {{$item->text}}  ({{ $item->mobile}})
                                    </button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    
                </div>

                <div class="row email_div">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('subject', __('crm::lang.subject') . ':*') !!}
                            {!! Form::text('subject', $campaign->subject, ['class' => 'form-control', 'required']) !!}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('email_body', __('crm::lang.email_body') . ':*') !!}
                            {!! Form::textarea('email_body', $campaign->email_body, [
                                'class' => 'form-control ',
                                'id' => 'email_body',
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <div class="row sms_div">
                    <div class="col-md-12">
                        <div class="form-group">
                            {!! Form::label('sms_body', __('crm::lang.sms_body') . ':') !!}
                            {!! Form::textarea('sms_body', $campaign->sms_body, [
                                'class' => 'form-control ',
                                'id' => 'sms_body',
                                'rows' => '6',
                                'required',
                            ]) !!}
                        </div>
                    </div>
                </div>
                <strong>@lang('lang_v1.available_tags'):</strong>
                <p class="help-block">
                    {{ implode(', ', $tags) }}
                </p>

                <button type="submit" class="btn btn-primary btn-sm submit-button pull-right draft m-5"
                    name="send_notification" value="0" data-style="expand-right">
                    <span class="ladda-label">
                        <i class="fas fa-save"></i>
                        @lang('messages.update')
                    </span>
                </button>

                <button type="submit" class="btn btn-warning btn-sm pull-right submit-button notif m-5"
                    name="send_notification" value="1" data-style="expand-right">
                    <span class="ladda-label">
                        <i class="fas fa-envelope-square"></i>
                        @lang('crm::lang.send_notification')
                    </span>
                </button>
                {!! Form::close() !!}
            </div>
        </div>
    @stop
    @section('javascript')
        <script src="{{ asset('modules/crm/js/crm.js?v=' . $asset_v) }}"></script>
        <script type="text/javascript">
        $(document).ready(function() {
                if ($('#search_customer').length > 0) {
                    // Customer autocomplete
                    $('#search_customer')
                        .autocomplete({
                            source: function(request, response) {
                                $.getJSON(
                                    '/contacts/customers', // Adjust to your endpoint
                                    {
                                        q: request.term // Query term
                                    },
                                    response
                                );
                            },
                            minLength: 2,
                            response: function(event, ui) {
                                if (ui.content.length == 1) {
                                    ui.item = ui.content[0];
                                    $(this)
                                        .data('ui-autocomplete')
                                        ._trigger('select', 'autocompleteselect', ui);
                                    $(this).autocomplete('close');
                                } else if (ui.content.length == 0) {
                                    swal("No customers found.");
                                }
                            },
                            select: function(event, ui) {
                                // When a customer is selected, do something
                                // For example, log customer data
                                console.log("Selected customer:", ui.item.id);
                                contact_row(ui.item.id);
                            }
                        })
                        .autocomplete('instance')._renderItem = function(ul, item) {
                            var string = '<div style="border-bottom: 1px solid gray;">' + item.text;
                            if (item.mobile) {
                                string += ' (' + item.mobile + ') ' + ' (' + item.id + ')';
                            }
                            return $('<li>').append(string).appendTo(ul);
                        };
                }
                // Adding product row
                $('.select-all').click(function() {
                    var to = $('.select_to').val();
                    console.log(to);
                    if (to !== 'customer' || to !== 'lead'|| to !== 'transaction_activity' || to !== 'contact') {
                        swal({
                            title: 'Not Select To',
                            text: "Please select To First",
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        })
                        return;
                    }
                    var contact_id = null;
                    if ($('#contact_section').find('input[name="contact_id[]"]').length > 0) {
                        swal({
                            title: LANG.sure,
                            text: "Are you sure you want to Remove existing and add all customers to the list?",
                            icon: 'warning',
                            buttons: true,
                            dangerMode: true,
                        }).then((willDelete) => {
                            if (willDelete) {
                                $('.tr').remove();
                                contact_row(contact_id);
                            }
                        });
                    }else{
                        contact_row(contact_id);
                    }
                })
                function contact_row(contact_id) {
                    // Check if the product is already exist in the table
                    if ($('#contact_section').find('input[name="contact_id[]"][value="' + contact_id +
                            '"]').length > 0) {
                        swal({
                            title: "Contact Exists",
                            text: "This Person is already in the list.",
                            icon: "warning",
                            button: "OK",
                        });
                        return;
                    }
                    var row_index = parseInt($('#contact_row_index').val());
                    var location_id = 1;
                    var leads = $('.select_to').val();
                    var transactions_count = $('.transactions_count').val();
                    var transactions_days = $('.transactions_days').val();
                    $.ajax({
                        method: 'POST',
                        url: '/crm/get-product-group-row',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            row_index: row_index,
                            contact_id: contact_id,
                            location_id: location_id,
                            leads:leads,
                            transactions_count: transactions_count,
                            transactions_days: transactions_days,
                        },
                        dataType: 'html',
                        success: function(result) {
                            $('#contact_section').append(result);
                            // update_table_total();
                            $('#contact_row_index').val(row_index + 1);
                        },
                        error: function(xhr, status, error) {
                            console.error('Error: ' + error);
                        }
                    });
                    $('.contact_div_height').animate({
                            scrollTop: $('.contact_div_height').prop('scrollHeight')
                        },
                        1000
                    );
                };
                $('.deselect-all').click(function(){
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $('.tr').remove();
                        }
                    });
                })
                // Remove product row
                $(document).on('click', '.remove_contact_row', function() {
                    swal({
                        title: LANG.sure,
                        icon: 'warning',
                        buttons: true,
                        dangerMode: true,
                    }).then((willDelete) => {
                        if (willDelete) {
                            $(this).closest('.tr').remove();
                        }
                    });
                });
            });
            $(function() {

                $('select#to').change(function() {
                    toggleFieldBasedOnTo($(this).val());
                });

                function toggleFieldBasedOnTo(to) {
                    if (to == 'customer') {
                        $('div.customer_div').show();
                        $('div.customer_group_id').show();
                        $('div.lead_div').hide();
                        $('div.transaction_activity_div').hide();
                        $('div.contact_div').hide();
                        $('div.search_product').hide();
                    } else if (to == 'lead') {
                        // $('div.lead_div').show();
                        $('div.customer_div').show();
                        $('div.transaction_activity_div').hide();
                        $('div.customer_group_id').hide();
                        $('div.contact_div').hide();
                        $('div.search_product').hide();
                    } else if (to == 'transaction_activity') {
                        $('div.transaction_activity_div').show();
                        $('div.customer_div').show();
                        $('div.lead_div').hide();
                        $('div.contact_div').hide();
                        $('div.search_product').hide();
                        $('div.customer_group_id').hide();
                    } else if (to == 'contact') {
                        // $('div.contact_div').show();
                        $('div.transaction_activity_div').hide();
                        $('div.customer_div').show();
                        $('div.search_product').show();
                        $('div.lead_div').hide();
                        $('div.customer_group_id').hide();
                    } else {
                        $('div.transaction_activity_div, div.customer_div, div.lead_div, div.contact_div').hide();
                    }
                }

                toggleFieldBasedOnTo($('select#to').val());
            });
        </script>
    @endsection
