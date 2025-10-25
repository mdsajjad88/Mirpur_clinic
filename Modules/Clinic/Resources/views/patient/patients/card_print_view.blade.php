<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Membership Card</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- FontAwesome (optional for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>
        body {
            background: #f8f9fa;
            padding: 30px;
        }

        .membership-card {
            background: #005495;
            border-radius: 10px;
            color: white;
            padding: 20px;
            width: 100%;
            max-width: 800px;
            margin: 30px auto;
            font-family: Arial, sans-serif;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        .exclusive-text {
            font-size: 34px;
            font-family: Verdana;
            display: inline-block;
            transform: scaleY(1.1);
            line-height: 1;
        }

        .membership-text {
            font-size: 17px;
            line-height: 1.5;
        }

        .card-logo {
            height: 30px;
            width: 40px;
            margin-bottom: -5px;
        }

        .front_view_logo {
            background-image: url('{{ asset('storage/uploads/logo_ng.png') }}');
            background-repeat: no-repeat;
            background-position: right center;
            width: 100%;
            height: 180px;
            background-size: auto 70%;
            margin-left: -72px;

        }

        .barcode-image {
            padding: 5px;
            background: white;
        }

        .patient-info-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        /* .valid-thru-container {
            text-align: right;
        } */

        /* .valid-thru-fieldset {
            border: 2px solid white;
            border-radius: 5px;
            padding: 7px;
        } */



        .valid-thru-container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .valid-thru-fieldset {
            border: 2px solid #ffffff;
            padding: 5px;
            margin: 0;
            display: inline-block;
        }

        .valid-thru-legend {
            font-size: 12px;
            padding: 0 4px;
        }

        .valid-thru-fieldset p {
            margin: 0;
            padding: 2px 2px;
            font-size: 28px;
            line-height: 1;
            display: inline-block;
            width: auto;
            height: auto;
            text-align: center;
            margin-top: -20px;
        }






        .not-transferable {
            font-size: 10px;
            text-transform: uppercase;
        }

        .clinic-title-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .clinic-title {
            font-size: 20px;
            font-weight: bold;
        }

        .divider-line {
            width: 40px;
            height: 2px;
            background-color: white;
            margin: 0 10px;
        }

        .discount-info {
            border: 2px solid white;
            border-radius: 5px;
            padding: 10px;
            font-size: 14px;
        }

        .qr-code-wrapper {
            display: inline-block;
            background: white;
            padding: 6px;
            border-radius: 6px;
            width: auto;
            height: auto;
        }

        .clinic-name {
            font-size: 30px !important;
        }

        .clinic-subname {
            font-size: 16px !important;
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
            border: 1px solid white;


        }

        /* Print Styles */
        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
            }

            body * {
                visibility: hidden;
            }

            #printableContent,
            #printableContent * {
                visibility: visible;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .membership-card {
                page-break-after: always;
            }

            .barcode-image,
            #qrcode canvas {
                visibility: visible !important;
                display: inline-block !important;
            }

            @page {
                size: auto;
                margin: 0mm;
            }
        }
    </style>
</head>

<body>

    <!-- Main Content -->
    <div id="printableContent">
        <!-- FRONT SIDE -->
        <div class="membership-card" id="front_view">
            <div class="row">
                <div class="col-md-6">
                    <h3>
                        <span class="exclusive-text">EXCLUSIVE</span><br>
                        <span class="membership-text">MEMBERSHIP CARD</span>
                    </h3>
                </div>

                <div class="col-md-6 d-flex align-items-center justify-content-end">
                    <div class="text-center">
                        <div>
                            <img src="{{ asset('storage/uploads/logo_ng.png') }}" alt="Logo" class="card-logo">
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

            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="front_view_logo"></div>
                </div>
                <div class="col-md-9 text-right">
                    <h4 class="member-id-text">MEMBERSHIP ID:</h4>
                    <img class="barcode-image"
                        src="data:image/png;base64,{{ DNS1D::getBarcodePNG($contact->contact_id, 'C39', 1.2, 30, [30, 48, 54], false) }}"
                        style="padding: 5px; background:white">
                    <div class="patient-info-container mt-2">
                        <h3 class="patient-name">{{ $patient->first_name }} {{ $patient->last_name ?? '' }}</h3>
                        <div class="valid-thru-container">
                            <fieldset class="valid-thru-fieldset">
                                <legend class="valid-thru-legend">VALID THRU</legend>
                                <p class="text-center">12/25</p>
                            </fieldset>
                            <p class="not-transferable">Not Transferable</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- BACK SIDE -->
        <div class="membership-card" id="back_view">
            <div class="clinic-title-container mb-3">
                <span class="divider-line"></span>
                <span class="clinic-title">AMERICAN WELLNESS CENTER</span>
                <span class="divider-line"></span>
            </div>
            <div class="row mb-3">
                <div class="col-md-3 text-center">
                    <div class="qr-code-wrapper">
                        <div class="qrcode" data-link="https://awcbd.org/"></div>
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
                                <td>100%</td>
                            </tr>
                            <tr>
                                <td>Diagnostic test</td>
                                <td>35%</td>
                            </tr>
                            <tr>
                                <td>Ozone Therapy</td>
                                <td>20%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="discount-info mb-3">
                <b>10% discount</b> at our partner <b>“Green Kitchen Restaurant”</b>
                for healthy nutritious meals & <b>“Gio Naturals”</b> products
            </div>
            <div class="row">
                <div class="col-md-3">
                    <p><i class="fas fa-phone"></i> +88 09666 747470</p>
                    <p class="indented">+88 01753 461857</p>
                </div>
                <div class="col-md-5">
                    <p><i class="fa fa-map-marker"></i> 2nd & 3rd Floor, Islam Tower</p>
                    <p class="indented">Shukrabad Dhanmondi-32, Dhaka</p>
                </div>
                <div class="col-md-4">
                    <p><i class="fas fa-globe"></i> www.awcbd.org</p>
                    <p><i class="fas fa-at"></i> awc.health@gmail.com</p>
                </div>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>
    <script>
        document.querySelectorAll('.qrcode').forEach(el => {
            el.innerHTML = '';
            new QRCode(el, {
                text: el.dataset.link,
                width: 80,
                height: 80,
                colorDark: "#000000",
                colorLight: "#ffffff",
            });
        });

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
