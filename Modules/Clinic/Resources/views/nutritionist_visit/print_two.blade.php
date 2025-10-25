<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Nutritionist Prescription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            height: 100vh;
        }

        .page {
            width: 210mm; /* A4 width */
            height: 297mm; /* A4 height */
            margin: auto;
            display: flex;
            flex-direction: column;
        }

        .header-space,
        .footer-space {
            height: 100px;
        }

        .content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        table {
            width: 85%;
            margin: auto;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #333;
            padding: 4px;
            text-align: center;
        }

        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none;
            }

            .page {
                page-break-after: always;
            }
        }
    </style>
</head>

<body>
    <div class="page">
        <!-- 100px Header Space -->
        <div class="header-space"></div>

        <!-- Center Content -->
        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>Product Name</th>
                        <th>Select</th>
                        <th>Product Name</th>
                        <th>Select</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (collect($filtered_products)->take(60)->chunk(2) as $chunk)
                        <tr>
                            @foreach ($chunk as $id => $name)
                                <td>{{ $name }}</td>
                                <td>
                                    <input type="checkbox" name="nutritionist_products[{{ $id }}]"
                                        value="{{ $name }}" {{ isset($selected_products[$id]) ? 'checked' : '' }}>
                                </td>
                            @endforeach
                            @if ($chunk->count() < 2)
                                <td></td>
                                <td></td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- 100px Footer Space -->
        <div class="footer-space"></div>
    </div>

    <div class="text-center my-3 no-print">
        <button class="btn btn-success" onclick="window.print()">Print</button>
    </div>
</body>

</html>
