<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Membership Card</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FontAwesome (optional for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Bootstrap 4 -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        /* Page Setup for Print */
        @page {
            size: 3.370in 2.125in;
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
            background: white;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .print-page {
            width: 100vw;
            height: 100vh;
            /* full height of page */
            display: flex;
            justify-content: center;
            align-items: center;
            background: white;
            position: relative;
        }


        .card-container {
            width: 100%;
            height: 100%;
            color: white;
            padding: 0.15in;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            /* change from absolute */
            left: 0;
            /* reset left and top */
            top: 0;
            transform: none;
            /* remove centering transform */
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }


        #front_view {
            background: #005495;
        }

        #back_view {
            background: #005495;
        }

        .top-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .exclusive-text {
            font-size: 25px;
            font-weight: bold;
            font-family: Verdana;
            transform: scaleY(1.4);
        }

        .membership-text {
            font-size: 16px;
            margin-top: 2px;
        }

        .logo {
            height: 30px;
            margin-bottom: -5px;
        }

        .clinic-name {
            font-size: 22px;
            font-weight: bold;
            margin-top: -3px;

        }

        .clinic-subname {
            font-size: 12px;
        }

        .barcode-image {
            width: auto;
            height: auto;
            margin-top: 5px;
            background: white;
            padding: 3px;
        }

        .patient-info-row {
            display: flex;
            justify-content: flex-end;
            /* Align items to the right */
            align-items: center;
            margin-top: 10px;
            width: 100%;
        }

        .patient-name {
            font-size: 20px;
            font-weight: bold;
            text-align: right;
            /* Align text to right */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-left: auto;
            /* Push to the right */
            padding-right: 10px;
            /* Add some space between name and valid-thru */
        }

        .valid-thru-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            /* Remove margin-left if it was there */
        }

        .valid-thru-fieldset {
            border: 1px solid white;
            padding: 5px;
            font-size: 8px;
        }


        .valid-thru-legend {
            padding: 5px;
            font-size: 11px;
            text-transform: uppercase;
            color: white;
        }

        .not-transferable {
            font-size: 6px;
            margin-top: 4px;
        }

        .clinic-title {
            font-size: 19px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 8px;
        }

        .discount-table {
            width: 100%;
            font-size: 12px;
            margin-top: 5px;

        }

        .discount-table th,
        .discount-table td {
            border: 1px solid white;
            padding: 4px 6px;
        }

        .discount-info {
            font-size: 12px;
            margin-top: 6px;
            border: 1px solid white;
            padding: 4px;
            border-radius: 4px;
            width: 90%;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }


        .contact-info {
            font-size: 11px;
            margin-top: 5px;
        }

        .qrcode-wrapper {
            background: white;
            padding: 5px;
            margin-top: 5px;
            display: inline-block;
        }

        .divider-line {
            width: 30px;
            height: 1px;
            background-color: white;
            display: inline-block;
            margin: 0 5px;
            vertical-align: middle;

        }

        .front-view-logo {
            position: absolute;
            margin-left: -105px;
            bottom: 0;
            width: 100%;
            height: 210px;
            background: url('{{ asset('storage/uploads/logo_ng.png') }}') no-repeat left bottom;
            background-size: auto 70%;
            opacity: 0.2;
        }

        .valid-thru-date {
            font-size: 20px !important;
            text-align: center;
            margin: 0;
            padding-right: 15px !important;
            color: white;
        }

        .valid-thru-container {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            margin-left: 10px;
            /* add some space between name and valid-thru */
        }


        .valid-thru-fieldset {
            border: 2px solid #ffffff;
            padding: 5px;
            margin: 0;
            display: inline-block;
        }

        /* .valid-thru-legend {
            font-size: 6px;
            padding: 0 4px;
        } */

        .valid-thru-fieldset p {
            margin: 0;
            padding: 2px 2px;
            font-size: 10px;
            line-height: 1;
            display: inline-block;
            width: auto;
            height: auto;
            text-align: center;
            margin-top: -12px;
        }

        .not-transferable {
            font-size: 9px;
            text-align: right;
            margin-top: 2px;
        }

        @media print {
            #front_view {
                background: #005495;
                padding: 25px;
            }

            #back_view {
                background: #005495;
            }
        }
    </style>
</head>

<body>

    <!-- Front Side Page -->
    <div class="print-page">
        <div class="card-container" id="front_view">
            <div class="front-view-logo"></div>
            <div class="content-container">
                <div class="top-section">
                    <div>
                        <div class="exclusive-text">EXCLUSIVE</div>
                        <div class="membership-text">MEMBERSHIP CARD</div>
                    </div>
                    <div class="text-center">
                        <img src="{{ asset('storage/uploads/logo_ng.png') }}" class="logo" alt="Logo">
                        <div class="clinic-name">AMERICAN</div>
                        <div class="clinic-subname">WELLNESS CENTER</div>
                    </div>
                </div>
                <br>
                <div class="text-right">
                    <div class="member-id-text">MEMBERSHIP ID:</div>
                    <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($contact->contact_id, 'C39', 1.2, 30, [30, 48, 54], true) }}"
                        class="barcode-image"> 
                </div>

                <div class="patient-info-row">
                    <div class="patient-name">
                        {{ $patient->first_name }} {{ $patient->last_name ?? '' }}
                    </div>
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

    <!-- Back Side Page -->
    <div class="print-page">
        <div class="card-container" id="back_view">
            <div class="clinic-title">
                <span class="divider-line"></span>
                AMERICAN WELLNESS CENTER
                <span class="divider-line"></span>
            </div>

            <div class="row" style="padding: 0px 35px;">
                <div class="col-4 text-center">
                    <div class="qrcode-wrapper">
                        <img src="{{ asset('storage/uploads/awcbd_logo.png') }}" alt="qrcode" height="60px" width="60px">
                    </div>
                    <div style="font-size: 12px;">fb/awc.health</div>
                </div>

                <div class="col-8">
                    <table class="discount-table">
                        <thead>
                            <tr>
                                <th>Membership Discount</th>
                                <th class="text-center">Percentage</th>
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

            <div class="discount-info">
                <b>10% discount</b> at our partner <b>"Green Kitchen Restaurant"</b> <br>
                for healthy nutritious meals & <b>"Gio Naturals"</b> products
            </div>

            <div class="contact-info row no-gutters">
                <div class="contact-column" style="width: 28%;">
                    <p><i class="fas fa-phone"></i> +8809666747470 <br> &nbsp; &nbsp; +88 01753461857</p>
                </div>
                <div class="contact-column" style="width: 42%;">
                    <p><i class="fas fa-map-marker-alt"></i> 2nd & 3rd Floor, Islam Tower, <br> Shukrabad Dhanmondi-32,
                        Dhaka</p>
                </div>
                <div class="contact-column" style="width: 30%;">
                    <p><i class="fas fa-globe"></i> www.awcbd.org <br>
                        <i class="fas fa-envelope"></i> awc.health@gmail.com
                    </p>
                </div>
            </div>

        </div>
    </div>

    <script>
       

        let beforePrint = () => {
            console.log("Print dialog opened");
        };

        let afterPrint = () => {
            window.location.href = "{{ route('patient.profile', ['id' => $contact->id]) }}";
        };

        if (window.matchMedia) {
            let mediaQueryList = window.matchMedia('print');

            mediaQueryList.addEventListener('change', function(mql) {
                if (!mql.matches) {
                    afterPrint();
                }
            });
        }

        window.onbeforeprint = beforePrint;
        window.onafterprint = afterPrint;

        window.onload = () => {
            window.print();
        };
    </script>

</body>

</html>
