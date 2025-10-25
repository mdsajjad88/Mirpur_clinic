<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Nutritionis t Demo Prescription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            display: flex;
            flex-direction: column;
            page-break-after: always;
            box-sizing: border-box;
        }

        /* Header and footer */
        .header,
        .footer {
            width: 100%;
        }

        .header {
            height: 100px;
        }

        /* Content flex */
        /* .content {
            flex: 1;
            padding: 10px 30px;
        } */

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #333;
            padding: 4px;
            text-align: center;
            font-size: 13px;
        }

        @media print {
            body {
                margin: 0;
            }

            .page {
                width: 210mm;
                min-height: 275mm;
                display: flex;
                flex-direction: column;
                page-break-after: always;
                box-sizing: border-box;
                position: relative;
                /* important */
            }

            .footer {
                border-top: 1px solid #ccc;
                /* padding: 5px 10px; */
                position: absolute;
                /* fix footer bottom inside page */
                bottom: 0;
                left: 0;
                width: 100%;
            }

        }
    </style>
</head>

<body>

    <!-- Page 1: Products + Lifestyles -->
    <div class="page">
        <div class="header">
            @include('clinic::nutritionist_visit.print_header')
        </div>

        <div class="content">
            <table id="main_table" style="margin-top: 8px;">
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Select</th>
                        <th>Product Name</th>
                        <th>Select</th>
                        <th>Product Name</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (collect($filtered_products)->take(60)->chunk(3) as $chunk)
                        <tr>
                            @foreach ($chunk as $id => $name)
                                <td>{{ $name }}</td>
                                <td><input type="checkbox" name="nutritionist_products[{{ $id }}]"
                                        value="{{ $name }}"
                                        {{ isset($selected_products[$id]) ? 'checked' : '' }}
                                        style="transform:scale(1.5);"></td>
                            @endforeach
                            @if ($chunk->count() < 3)
                                @for ($i = 0; $i < 3 - $chunk->count(); $i++)
                                    <td></td>
                                    <td></td>
                                @endfor
                            @endif
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="6" style="border:none;" class="text-center"><b>Lifestyle Products</b></td>
                    </tr>
                    @foreach (collect($filtered_lifestyles)->take(60)->chunk(3) as $chunk)
                        <tr>
                            @foreach ($chunk as $id => $name)
                                <td>{{ $name }}</td>
                                <td><input type="checkbox" name="nutritionist_lifestyles[{{ $id }}]"
                                        value="{{ $name }}"
                                        {{ isset($selected_lifestyles[$id]) ? 'checked' : 'disabled' }}
                                        style="transform:scale(1.5);"></td>
                            @endforeach
                            @if ($chunk->count() < 3)
                                @for ($i = 0; $i < 3 - $chunk->count(); $i++)
                                    <td></td>
                                    <td></td>
                                @endfor
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="footer mt-auto" style="border-top:1px solid #ccc; padding:5px 5px;">
            <div class="d-flex justify-content-between">
                <div>
                    <strong>American Wellness Center</strong><br>
                    <b>09639147470, 01753461857(<small>Whatsapp</small>)</b><br>
                    <span><b>Website:</b> www.awcbd.org, <b>Email:</b> awc.health@gmail.com</span>
                </div>
                <div class="text-end">
                    <strong>Location:</strong><br>
                    <span>2nd Floor, Islam Tower, 102</span><br>
                    <span>Sukrabad (Bus Stop), Dhanmondi-32, Dhaka-1207</span>
                </div>
            </div>
        </div>

    </div>
    @if($prescription->advices->count() > 0)
    <!-- Page 2: Advices -->
    <div class="page">
        <div class="header" style="border-bottom: 1px solid #ccc;">
            @include('clinic::nutritionist_visit.print_header')
        </div>

        <div class="content" style="margin-top: 10px;">
            <h3>Advice:</h3>
            <ol>
                @foreach ($prescription->advices as $advice)
                    <li>{{ $advice->advise_name }}</li>
                @endforeach
            </ol>
        </div>

        <div class="footer mt-auto" style="border-top:1px solid #ccc; padding:5px 5px;">
            <div class="d-flex justify-content-between">
                <div>
                    <strong>American Wellness Center</strong><br>
                    <b>09639147470, 01753461857(<small>Whatsapp</small>)</b><br>
                    <span><b>Website:</b> www.awcbd.org, <b>Email:</b> awc.health@gmail.com</span>
                </div>
                <div class="text-end">
                    <strong>Location:</strong><br>
                    <span>2nd Floor, Islam Tower, 102</span><br>
                    <span>Sukrabad (Bus Stop), Dhanmondi-32, Dhaka-1207</span>
                </div>
            </div>
        </div>

    </div>
    @endif
    <script>
        window.onload = function() {
            window.print();
        }
        window.onafterprint = function() {
            window.location.href = "{{ url('nutritionist-second-index') }}";
        }
    </script>

</body>

</html>
