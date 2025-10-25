<table style="width:100%; border: none; font-size: 12px;">
    <tr>
        <!-- Left Column -->
        <td style="border:none; text-align:center; vertical-align:middle;">
            <img src="{{ asset('uploads/cms/logo.png') }}" alt="Logo" style="max-height:60px; display:inline-block; vertical-align:middle;">
        </td>
        <td style="border:none; text-align: left;">
            <div style="margin-bottom: 4px;">
                <strong>Patient:</strong>
                {{ $patient->first_name . ' ' . $patient->last_name ?? '-' }}  {{ $profile->age ? '| Age: ' . $profile->age : '' }} {{ $profile->gender ? ' | Gender: '.ucfirst($profile->gender) : '' }}

            </div>
            <div style="margin-bottom: 4px;">
                <strong>Health Concerns:</strong>
                @forelse ($diseases->take(4) as $disease)
                    <span class="badge bg-info">{{ $disease->name }}</span>
                @empty
                    <span class="badge bg-secondary">N/A</span>
                @endforelse
            </div>

            <div style="margin-bottom: 4px;">
                <strong>Patient of :</strong>
                {{ trim(($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '')) }}
            </div>
            <div style="margin-bottom: 4px;">
                <strong>Nutritionist ID:</strong>
                {{ $prescription->editor
                    ? $prescription->editor->username ?? '-'
                    : ($prescription->creator
                        ? $prescription->creator->username ?? '-'
                        : '-') }}
            </div>
        </td>

        <!-- Right Column -->
        <td style="border:none; text-align: right;">
            <div style="margin-bottom: 4px;">
                <strong>Date:</strong> {{ $prescription->prescription_date }}
            </div>
            <div>
                <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($patient->contact_id, 'C39', 1.0, 20, [39, 48, 54], false) }}"
                    style="border: 1px solid #ddd; background: white; padding: 2px;">
            </div>
        </td>
    </tr>
</table>
