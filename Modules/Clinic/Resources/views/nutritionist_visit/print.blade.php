<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Physiotherapy Prescription</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .badge {
            font-size: 0.85rem;
        }

        .barcode {
            border: 1px solid #ddd;
            padding: 5px;
            background: #fff;
            display: inline-block;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>

    <div class="container my-3">

        {{-- Header --}}
        <div class="text-center mb-4">
            <h2 class="fw-bold text-primary mb-1">Physiotherapy Prescription</h2>
            <p class="mb-1"><strong>Date:</strong> {{ $prescription->prescription_date }}</p>
            <p class="mb-1"><strong>Doctor:</strong>
                {{ trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')) }}</p>
            <p class="mb-0">
                <strong>Physiotherapist:</strong>
                {{ $prescription->editor
                    ? trim(($prescription->editor->first_name ?? '') . ' ' . ($prescription->editor->last_name ?? ''))
                    : ($prescription->creator
                        ? trim(($prescription->creator->first_name ?? '') . ' ' . ($prescription->creator->last_name ?? ''))
                        : '-') }}
            </p>
        </div>

        {{-- Patient Information --}}
        <div class="card shadow-sm p-3 mb-4 rounded-3 border-0">
            <h4 class="text-secondary border-bottom pb-2 mb-3">Patient Information</h4>
            <div class="row align-items-center">
                {{-- Left Column: Name + Disease + Age + Gender + Barcode --}}
                <div class="col-md-6 d-flex align-items-center mb-2">
                    <div>
                        <p class="mb-1"><strong>Name:</strong>
                            {{ $patient->first_name . ' ' . $patient->last_name ?? '-' }}</p>
                        <p class="mb-1"><strong>Disease:</strong>
                            @forelse ($diseases as $disease)
                                <span class="badge bg-info text-white me-1">{{ $disease->name }}</span>
                            @empty
                                <span class="badge bg-secondary text-white">N/A</span>
                            @endforelse
                        </p>
                        <p class="mb-1"><strong>Age:</strong> {{ $profile->age ?? '-' }}</p>
                        <p class="mb-1"><strong>Gender:</strong>
                            {{ $profile->gender ? ucfirst($profile->gender) : '-' }}</p>
                    </div>

                </div>

                {{-- Right Column: Height + Weight --}}
                <div class="col-md-6">
                    <div class="ms-3">
                        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($patient->contact_id, 'C39', 1.0, 30, [39, 48, 54], false) }}"
                            class="barcode" style="height:60px;">
                    </div>
                    <p class="mb-1"><strong>Height:</strong>
                        {{ $presOrg->current_height_feet ? $presOrg->current_height_feet . ' ft ' . $presOrg->current_height_inches . ' in' : '-' }}
                    </p>
                    <p class="mb-1"><strong>Weight:</strong> {{ $presOrg->current_weight ?? '-' }} kg</p>
                </div>
            </div>
        </div>
        <h5>@lang('clinic::lang.guidline_description')</h5>
        <div>
            {!! $prescription->guidline_description ?? '-' !!}
        </div>
        {{-- Food Plan --}}
        <h5 class="mb-2">Food Plan</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-primary">
                    <tr>

                        <th>Products</th>
                        <th>Time</th>
                        <th>Instructions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($foods as $food)
                        <tr>
                            <td>{{ $food->product_name ?? '-' }}</td>
                            <td>{{ $food->meal_time ?? '-' }}</td>

                            <td>{{ $food->instructions ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Lifestyle Plan --}}
        <h5 class="mb-2">Lifestyle Recommendations</h5>
        <div class="table-responsive mb-4">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-success">
                    <tr>
                        <th>Products</th>
                        <th>Time</th>
                        <th>Instructions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lifestyles as $lifestyle)
                        <tr>
                            <td>{{ $lifestyle->product_name ?? '-' }}</td>
                            <td>{{ $lifestyle->meal_time ?? '-' }}</td>
                            <td>{{ $lifestyle->instructions ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{-- Print Button --}}
        <div class="text-center mb-4 no-print">
            <button class="btn btn-success" onclick="window.print()">Print</button>
        </div>

    </div>

    <script>
        window.onload = function() {
            window.print();
        };
        window.onafterprint = function() {
            window.location.href = "{{ route('nutritionist-visit.index') }}";

        }
    </script>
</body>

</html>
