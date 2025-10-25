<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content" style="font-family: Arial, sans-serif;">

        <!-- Modal Header -->
        <div class="modal-header" style="border-bottom: 1px solid #ddd;">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="float:right;">
        <span aria-hidden="true">&times;</span>
    </button>
    <h5 class="modal-title" style="line-height: 1.5;">Prescription Preview</h5>
</div>


        <!-- Modal Body -->
        <div class="modal-body" style="padding: 15px; max-height: 75vh; overflow-y: auto;">

            <!-- Page Container -->
            <div style="width: 100%; margin: auto; display: flex; flex-direction: column;">

                <!-- Header -->
                <div style="margin-bottom: 15px;">
                    <table style="width:100%; border: none; font-size: 12px;">
                        <tr>
                            <!-- Left Column -->
                            <td style="border:none; text-align:center; vertical-align:middle;">
                                <img src="{{ asset('uploads/cms/logo.png') }}" alt="Logo"
                                    style="max-height:60px; display:inline-block; vertical-align:middle;">
                            </td>
                            <td style="border:none; text-align: left;">
                                <div style="margin-bottom: 4px;">
                                    <strong>Patient:</strong>
                                    {{ $patient->first_name . ' ' . $patient->last_name ?? '-' }}
                                    {{ $profile->age ? '| Age: ' . $profile->age : '' }}
                                    {{ $profile->gender ? ' | Gender: ' . ucfirst($profile->gender) : '' }}

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

                </div>

                <!-- Product Table -->
                <div style="overflow-x:auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr>
                                <th style="border:1px solid #333; padding:4px;">Product Name</th>
                                <th style="border:1px solid #333; padding:4px;">Select</th>
                                <th style="border:1px solid #333; padding:4px;">Product Name</th>
                                <th style="border:1px solid #333; padding:4px;">Select</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (collect($filtered_products)->take(60)->chunk(2) as $chunk)
                                <tr>
                                    @foreach ($chunk as $id => $name)
                                        <td style="border:1px solid #333; padding:4px;">{{ $name }}</td>
                                        <td style="border:1px solid #333; padding:8px; text-align:center;">
                                            <input type="checkbox" name="nutritionist_products[{{ $id }}]"
                                                value="{{ $name }}"
                                                {{ isset($selected_products[$id]) ? 'checked' : 'disabled' }}
                                                style="transform: scale(1.5);">
                                        </td>
                                    @endforeach
                                    @if ($chunk->count() < 2)
                                        <td style="border:1px solid #333; padding:4px;"></td>
                                        <td style="border:1px solid #333; padding:4px;"></td>
                                    @endif
                                </tr>
                            @endforeach

                            <tr>
                                <td colspan="4" class="text-center" style="border:none;"><b
                                        style="font-size: 14px">Lifestyle Products</b></td>
                            </tr>

                            @foreach (collect($filtered_lifestyles)->take(60)->chunk(2) as $chunk)
                                <tr>
                                    @foreach ($chunk as $id => $name)
                                        <td style="border:1px solid #333; padding:4px;">{{ $name }}</td>
                                        <td style="border:1px solid #333; padding:8px; text-align:center;">
                                            <input type="checkbox" name="nutritionist_lifestyles[{{ $id }}]"
                                                value="{{ $name }}"
                                                {{ isset($selected_lifestyles[$id]) ? 'checked' : 'disabled' }}
                                                style="transform: scale(1.5);">
                                        </td>
                                    @endforeach
                                    @if ($chunk->count() < 2)
                                        <td style="border:1px solid #333; padding:4px;"></td>
                                        <td style="border:1px solid #333; padding:4px;"></td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if (!empty($prescription->advices))
                    <div id="advice_section" style="margin-left: 40px;">
                        <h3 style="margin-bottom: 5px;">Advice:</h3> <!-- Reduce spacing below the heading -->
                        <ol style="padding-left: 20px; margin-top: 0; margin-bottom: 0;">
                            @foreach ($prescription->advices as $advice)
                                <li>{{ $advice->advise_name }}</li>
                            @endforeach
                        </ol>
                    </div>
                @endif
                <!-- Footer -->
                <div style="margin-top:15px; font-size:12px; display:flex; justify-content:space-between;">
                    <div>
                        <strong>Contact:</strong><br>
                        <span>09639147470, 01753461857 (Whatsapp)</span><br>
                        <span><b>Website:</b> www.awcbd.org, <b>Email:</b> awc@gmail.com</span>
                    </div>
                    <div style="text-align:right;">
                        <strong>Location:</strong><br>
                        <span>2nd Floor, Islam Tower, 102</span><br>
                        <span>Sukrabad Bus Stop, Dhanmondi-32, Dhaka-1207</span>
                    </div>
                </div>

            </div>

        </div>

        <!-- Modal Footer -->
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>

    </div>
</div>
