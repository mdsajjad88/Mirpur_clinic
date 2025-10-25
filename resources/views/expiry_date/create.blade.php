@extends('layouts.app')

@section('title', __('Enter Expiry Dates'))

@section('css')
    <style>
        @media print {
            .print-font {
                font-size: 10px !important;
            }

            .print-exclude {
                display: none !important;
            }
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .swal2-custom {
            width: 500px !important;
            height: 350px !important;
            font-size: 16px !important;
        }

        .swal2-custom .swal2-title {
            font-size: 20px !important;
        }

        .swal2-custom .swal2-content {
            font-size: 14px !important;
        }

        .swal2-custom .swal2-actions .swal2-styled {
            font-size: 14px !important;
        }
    </style>
@endsection

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1><a href="{{ action([\App\Http\Controllers\ProductController::class, 'index']) }}"><i class="fas fa-backward"></i> @lang('Enter Expiry Dates')</a></h1>
    </section>

    <section class="content">
        @component('components.widget', ['title' => __('Filter Products')])
            <form id="expiryForm" method="GET" action="{{ route('expiry_dates.filter') }}">
                <div class="row" style="align-items: center;">
                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('product_sall_status', __('Selling status') . ':') !!}
                            {!! Form::select('product_sall_status', [
                                '0' => 'For Sell',
                                '1' => 'Not For Sell'
                            ], null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'product_sall_status',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            {!! Form::label('expiry_date_filter', __('Expiry Date Status') . ':') !!}
                            {!! Form::select('expiry_date_filter', [
                                'with_expiry' => 'With Existing Expiry Date',
                                'without_expiry' => 'Without Existing Expiry Date'
                            ], null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'expiry_date_filter',
                                'placeholder' => __('lang_v1.all')
                            ]) !!}
                        </div>
                    </div>
                    
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('category_id[]', __('product.category') . ':') !!}
                            {!! Form::select('category_id[]', $categories, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'category_id',
                                'multiple' => 'multiple',
                            ]) !!}
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            {!! Form::label('brand_id[]', __('product.brand') . ':') !!}
                            {!! Form::select('brand_id[]', $brands, null, [
                                'class' => 'form-control select2',
                                'style' => 'width:100%',
                                'id' => 'brand_id',
                                'multiple' => 'multiple',
                            ]) !!}
                        </div>
                    </div>

                    <div class="col-md-1">
                        <div class="form-group">
                            <button type="button" id="filter-button" class="btn btn-primary" style="margin-top: 25px;">
                                @lang('Filter')
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        @endcomponent

        @component('components.widget', ['title' => __('Search Product')])
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                    <div class="input-group">
                        <input type="text" id="search_product_for_expiry" class="form-control" placeholder="@lang('Search by Product Name or SKU')">
                        <div class="input-group-btn">
                            <button type="button" id="search-button" class="btn btn-success">
                                <i class="fa fa-search"></i> @lang('Search')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endcomponent

        @component('components.widget', ['title' => __('Products List')])
            <table class="table table-bordered table-th-green table-striped" id="expiry_product_table">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>SKU</th>
                        <th>Brand</th>
                        <th>Category</th>
                        <th>Lot No.</th>
                        <th>Expiry Date</th>
                        <th><i class="fa fa-trash"></i></th>
                    </tr>
                </thead>
                <tbody id="expiryContent">
                    {{-- Expiry content will be loaded here via AJAX --}}
                </tbody>
            </table>

            <button type="button" id="finalize-button" class="btn btn-primary print-exclude btn-finalize"
                style="display: block; width: 200px; height: 50px; margin: 0 auto; margin-top:30px; font-size: 18px;">@lang('Finalize Expiry Dates')</button>
        @endcomponent
    </section>
@endsection

@section('javascript')
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
    <script>
    $(document).ready(function() {
    // Initialize datepicker for existing rows
    initializeDatepickers();

    // Product search autocomplete
    $('#search_product_for_expiry').autocomplete({
        source: function(request, response) {
            $.getJSON('/products/list', {
                term: request.term
            }, response);
        },
        minLength: 2,
        select: function(event, ui) {
            if (ui.item) {
                addProductToExpiry(ui.item);
            }
        }
    }).autocomplete('instance')._renderItem = function(ul, item) {
        var string = '<div>' + item.name + ' (' + item.sku + ') ';
        if (item.brand_id) {
            string += '<br>Brand: ' + item.brand.name + '</div>';
        }
        return $('<li>').append(string).appendTo(ul);
    };

    // Function to handle search button click
    $('#search-button').on('click', function() {
        var productTerm = $('#search_product_for_expiry').val();
        if (productTerm.trim()) {
            // Trigger autocomplete search programmatically
            $('#search_product_for_expiry').autocomplete('search', productTerm);
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Please enter a product name or SKU to search!',
                customClass: {
                    popup: 'swal2-custom'
                }
            });
        }
    });

    // Function to add product to expiry list
    function addProductToExpiry(product) {
        var isProductAlreadyAdded = false;
        $('#expiry_product_table tbody tr').each(function() {
            var existingSKU = $(this).find('td:nth-child(2)').text().trim();
            if (existingSKU === product.sku) {
                isProductAlreadyAdded = true;
                return false;
            }
        });

        if (isProductAlreadyAdded) {
            Swal.fire({
                icon: 'warning',
                title: 'Product already added!',
                text: 'The selected product is already in the products List.',
                customClass: {
                    popup: 'swal2-custom'
                }
            });
        } else {
            var rowHtml = `
                <tr>
                    <td>${product.name}</td>
                    <td>${product.sku}</td>
                    <td>${product.brand ? product.brand.name : ''}</td>
                    <td>${product.category ? product.category.name : ''}</td>
                    <td><input type="text" value="${product.lot_number ? product.lot_number : ''}" class="form-control" name="lot_no" placeholder="auto generate"></td>
                    <td>
                        <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span> 
                        <input type="text" value="${product.exp_date ? product.exp_date : ''}" class="form-control expiry_datepicker exp_date" name="expiry_date">
                        </div>
                    </td>
                    <td>
                        <input type="hidden" name="product_id" value="${product.product_id}">
                        <input type="hidden" name="transaction_id" value="${product.transaction_id}">
                        <button type="button" class="btn btn-danger btn-remove-row btn-sm">
                            <i class="fa fa-times" style="font-size: 12px; cursor: pointer;"></i>
                        </button>
                    </td>
                </tr>
            `;
            $('#expiryContent').append(rowHtml);
            // Initialize datepicker for newly added row
            initializeDatepickers();
        }
    }

    // Initialize datepicker
    function initializeDatepickers() {
    $('.expiry_datepicker').datepicker({
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
    });
    }

    // Handle the filter button click
    $('#filter-button').on('click', function() {
        var rowCount = $('#expiryContent tr').length;

        if (rowCount > 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Clear existing table rows?',
                text: 'Are you sure you want to clear the existing data and apply the new filter?',
                showCancelButton: true,
                confirmButtonText: 'Yes, filter it!',
                cancelButtonText: 'No, keep existing data',
                customClass: {
                    popup: 'swal2-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#expiryContent').empty(); // Clear existing table rows
                    $('#expiryForm').submit(); // Submit the form to apply filter
                }
            });
        } else {
            $('#expiryForm').submit(); // Directly submit if no rows exist
        }
    });

    // Handle the finalize button click
    $('#finalize-button').on('click', function() {
        var rowCount = $('#expiry_product_table tbody tr').length;

        if (rowCount > 0) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to finalize the expiry dates?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, finalize!',
                cancelButtonText: 'No, cancel',
                customClass: {
                    popup: 'swal2-custom'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#expiryForm').submit(); // Submit the form to finalize
                }
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'No products to finalize!',
                text: 'Please add products to the list before finalizing.',
                customClass: {
                    popup: 'swal2-custom'
                }
            });
        }
    });

    $(document).on('click', '.btn-remove-row', function() {
        $(this).closest('tr').remove();
    });

    // Handle the filter form submission
    $('#expiryForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        $.ajax({
            url: form.attr('action'),
            method: 'GET',
            data: form.serialize(),
            success: function(response) {
                $('#expiryContent').html(response.content);
                initializeDatepickers(); // Initialize datepicker for newly added rows
            }
        });
    });
});


$(document).on('click', '#finalize-button', function() {
    if ($('#expiry_product_table tbody tr').length > 0) {
        // Check if all expiry dates are filled
        var missingExpiryDate = false;

        $('#expiry_product_table tbody tr').each(function() {
            var expiryDate = $(this).find('input[name="expiry_date"]').val();
            if (!expiryDate || expiryDate.trim() === "") {
                missingExpiryDate = true;
                return false; // Exit loop as soon as a missing expiry date is found
            }
        });

        if (missingExpiryDate) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Expiry Date!',
                text: 'Please ensure all products have an expiry date before finalizing.',
                customClass: {
                    popup: 'swal2-custom'
                }
            });
            return; // Stop execution if any expiry date is missing
        }

        // If all expiry dates are filled, proceed with finalization
        Swal.fire({
            icon: 'warning',
            title: 'Finalize your products expiry date.',
            text: 'Are you sure?',
            showCancelButton: true,
            confirmButtonText: 'OK',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'swal2-custom'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Collect data
                var purchases = [];
                $('#expiry_product_table tbody tr').each(function() {
                    var row = $(this);
                    var purchase = {
                        product_id: row.find('input[name="product_id"]').val(),
                        transaction_id: row.find('input[name="transaction_id"]').val(),
                        lot_number: row.find('input[name="lot_no"]').val(),
                        expiry_date: row.find('input[name="expiry_date"]').val()
                    };
                    purchases.push(purchase);
                });

                var data = {
                    purchases: purchases
                };

                // Send data to store method
                $.ajax({
                    url: '{{ route('expiry-date.store') }}', // Updated to match new route
                    type: 'POST',
                    data: data,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success('Success! ' + response.msg);
                            window.location.href = response.url;
                        } else {
                            toastr.error('Error! ' + response.msg);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error occurred:', xhr.responseText);
                    }
                });
            }
        });
    } else {
        Swal.fire({
            icon: 'warning',
            title: 'No products to finalize!',
            text: 'Please add products to the list before finalizing.',
            customClass: {
                popup: 'swal2-custom'
            }
        });
    }
});


    </script>
@endsection
