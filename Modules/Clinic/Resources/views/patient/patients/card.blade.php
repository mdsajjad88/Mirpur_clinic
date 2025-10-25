<div class="modal-dialog modal-lg" role="document" id="membership_card_modal">
    <div class="modal-content">
        <!-- Header -->
        <div class="modal-header bg-primary text-white">
            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
                <i class="fa fa-id-card"></i> Membership Card Preview
            </h4>
        </div>

        <!-- Body -->
        <div class="modal-body">
            <div class="container-fluid" id="printableContent">
                <!-- FRONT SIDE -->
                <div class="row mb-4 justify-content-center">
                    <div class="col-md-8 membership-card" id="front_view">
                        <div class="row mb-3">
                            <div class="col-md-6 d-flex align-items-center">
                                <div>
                                    <h3 class="text-white">
                                        <span class="exclusive-text">EXCLUSIVE</span><br>
                                        <span class="membership-text">MEMBERSHIP CARD</span>
                                    </h3>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex align-items-center justify-content-end">
                                <div class="text-center">
                                    <div>
                                        <img src="{{ asset('storage/uploads/logo_ng.png') }}" alt="Logo"
                                            class="card-logo">
                                    </div>
                                    <div>
                                        <h4 class="text-white">
                                            <span class="clinic-name">AMERICAN</span><br>
                                            <span class="clinic-subname">WELLNESS CENTER</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Logo & Barcode -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="front_view_logo"></div>
                            </div>
                            <div class="col-md-9">
                                <div class="text-right">
                                    <h4 class="member-id-text">MEMBERSHIP ID:</h4>
                                    <img class="barcode-image"
                                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG($contact->contact_id, 'C39', 1.2, 30, [30, 48, 54], true) }}"
                                        style="padding: 5px; background:white">
                                </div>
                                <div class="patient-info-container">
                                    <h3 class="patient-name">
                                        {{ $patient->first_name }}{{ $patient->last_name ?? '' }}
                                    </h3>
                                    <div class="valid-thru-container">
                                        <fieldset class="valid-thru-fieldset">
                                            <legend class="valid-thru-legend">VALID THRU</legend>
                                            <p class="valid-thru-date">12/25</p>
                                        </fieldset>
                                        <p class="not-transferable">Not Transferable</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- BACK SIDE -->
                <div class="row mt-1 justify-content-center">
                    <div class="col-md-8 membership-card" id="back_view">
                        <div class="row mb-2 text-center">
                            <div class="col-md-12 clinic-title-container">
                                <span class="divider-line"></span>
                                <span class="clinic-title">AMERICAN WELLNESS CENTER</span>
                                <span class="divider-line"></span>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-3 text-center qr-code-container">
                                        <div class="qr-code-wrapper">
                                            <img src="{{ asset('storage/uploads/awcbd_logo.png') }}" alt="qrcode" height="50px" width="50px">
                                        </div>
                                        <p class="qr-code-text">fb/awc.health</p>
                                    </div>
                                    <div class="col-md-9">
                                        <table id="discount_table">
                                            <thead>
                                                <tr>
                                                    <th>Membership Discount</th>
                                                    <th>Percentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Doctor Consultation</td>
                                                    <td class="text-center">100%</td>
                                                </tr>
                                                <tr>
                                                    <td>Diagnostic test</td>
                                                    <td class="text-center">35%</td>
                                                </tr>
                                                <tr>
                                                    <td>Ozone Therapy</td>
                                                    <td class="text-center">20%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-md-12">
                                <h5 class="discount-info">
                                    <b>10% discount</b> at our partner <b>Green Kitchen Restaurant</b>
                                    for healthy nutritious meals & <b>Gio Naturals</b> products
                                </h5>
                            </div>
                        </div>
                        <div class="row contact-info">
                            <div class="col-md-4">
                                <p><i class="fas fa-phone"></i> +88 09666 747470</p>
                                <p class="indented">+88 01753 461857</p>
                            </div>
                            <div class="col-md-4">
                                <p><i class="fa fa-map-marker"></i> 2nd & 3rd Floor, Islam Tower,</p>
                                <p class="indented">Shukrabad Dhanmondi-32, Dhaka</p>
                            </div>
                            <div class="col-md-4">
                                <p><i class="fas fa-globe"></i> www.awcbd.org</p>
                                <p><i class="fas fa-at"></i> awc.health@gmail.com</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" onclick="printMembershipCard()">
                <i class="fa fa-print"></i> Print Card
            </button>
            <button type="button" class="btn btn-default" data-dismiss="modal">
                @lang('messages.close')
            </button>
        </div>
    </div>
</div>

<style>
    /* General Styles */
    .membership-card {
        background: #005495;
        border-radius: 10px;
        color: white;
        padding: 20px;
        width: 100%;
        max-width: 800px;
        margin: 0 auto;
    }

    /* Front View Styles */
    .exclusive-text {
        font-size: 34px;
        font-family: Verdana;
        display: inline-block;
        transform: scaleY(1.1);
        line-height: 1;
        color: white;
    }

    .membership-text {
        font-size: 17px;
        line-height: 1.5;
        color: white;
    }

    .card-logo {
        height: 30px;
        width: 40px;
        margin-bottom: -15px;
    }

    .clinic-name {
        font-size: 26px;
        line-height: 1;
        color: white;
    }

    .clinic-subname {
        font-size: 14px;
        color: white;
    }

    .front_view_logo {
        background-image: url('{{ asset('storage/uploads/logo_ng.png') }}');
        background-repeat: no-repeat;
        background-position: right center;
        width: 100%;
        height: 160px;
        margin-left: -30px;
        background-size: auto 70%;
        padding-left: 20px;
        background-origin: content-box;
    }

    .member-id-text {
        color: white;
        margin: 0 0 5px 0;
    }

    .barcode-image {
        padding: 5px;
        background: white;
    }

    .patient-info-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        color: white;
        font-family: Arial, sans-serif;
    }

    .patient-name {
        margin: 0;
        color: white;
    }

    .valid-thru-container {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        text-align: right;
    }

    .valid-thru-fieldset {
        border: 2px solid white;
        padding: 5px 10px;
        border-radius: 5px;
        min-width: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .valid-thru-legend {
        padding: 0 10px;
        font-size: 12px;
        text-transform: uppercase;
        color: white;
    }

    .valid-thru-date {
        font-size: 20px;
        text-align: center;
        margin: 0;
        color: white;
    }

    .not-transferable {
        font-size: 10px;
        margin-top: 5px;
        text-transform: uppercase;
        color: white;
    }

    /* Back View Styles */
    .clinic-title-container {
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 20px 0;
    }

    .divider-line {
        width: 40px;
        height: 2px;
        background-color: white;
        display: inline-block;
        margin: 0 10px;
    }

    .clinic-title {
        font-size: 20px;
        font-weight: bold;
        color: white;
    }

    #discount_table {
        width: 100%;
        margin-top: 10px;
        border-collapse: separate;
        border-spacing: 0;
    }

    #discount_table th,
    #discount_table td {
        padding: 4px;
        text-align: left;
        color: white;
    }

    #discount_table th {
        min-width: 200px;
    }

    .text-center {
        text-align: center !important;
    }

    .qr-code-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .qr-code-wrapper {
        display: inline-block;
        background: white;
        padding: 8px;
        border-radius: 5px;
    }

    .qr-code-text {
        margin-top: 8px;
        color: white;
    }

    .discount-info {
        border: 2px solid white;
        border-radius: 5px;
        padding: 10px;
        color: white;
        font-size: 14px;
    }

    .contact-info p {
        color: white;
        font-size: 12px;
        text-align: left;
        margin-bottom: 5px;
    }

    .indented {
        padding-left: 20px;
    }

    /* Print Styles */
    @media print {
        body {
            margin: 0;
            padding: 0;
            background: white !important;
        }

        body * {
            visibility: hidden;
        }

        #printableContent,
        #printableContent * {
            visibility: visible;
        }

        #printableContent {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
        }

        .membership-card {
            background-color: #005495 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            color: white !important;
            width: 100%;
            max-width: 800px;
            margin: 0 auto 20px;
            page-break-after: always;
        }

        #front_view,
        #back_view {
            height: auto;
            min-height: 300px;
        }

        .modal-header,
        .modal-footer,
        .close {
            display: none !important;
        }

        /* Ensure all text is white in print */
        #printableContent,
        #printableContent * {
            color: white !important;
        }

        /* Fix for QR code visibility */
        #qrcode canvas {
            visibility: visible !important;
        }

        @page {
            size: auto;
            margin: 0mm;
        }
    }
</style>

<script>
    
    function printMembershipCard() {
        const contactId = "{{ $contact->id }}";
        const fullUrl = "{{ route('membership.card.transaction', ['id' => $contact->id]) }}";

        // Send the AJAX request to the server
        $.ajax({
            url: fullUrl,
            type: 'POST',
            data: {
                contact_id: contactId,
                _token: '{{ csrf_token() }}' // Including CSRF token for the request
            },
            success: function(response) {
                if (response.success) {
                    // After successful response, open the print window and wait until it's closed
                    const printWindow = window.open(response.url); // Use the URL from the response
                    const pollTimer = window.setInterval(function() {
                        if (printWindow.closed !== false) {
                            window.clearInterval(pollTimer);
                            window.location.href = response
                            .url; // Redirect to the patient profile or the card URL
                        }
                    }, 1000);

                    toastr.success(response.message);
                } else {
                    toastr.error(response.message);
                }
            },
            error: function(error) {
                console.error('Error:', error);
                toastr.error('Something went wrong. Please try again.');
            }
        });
    }
</script>
