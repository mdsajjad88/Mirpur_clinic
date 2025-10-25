@extends('clinic::layouts.app2')
@section('title', __('crm::lang.marge_contacts'))
@section('content')
    @include('crm::layouts.nav')
    <div class="container-fluid">
        <form action="{{ route('call-campaign.merge.process', $campaign->id) }}" method="POST" id="merge_contacts_form">
            @csrf

            <div class="row">
                @component('components.widget', ['class' => 'box-primary', 'title' => __('Summary Report:')])
                    <div class="row">
                        <div class="col-md-3">
                            <p><strong>{{ __('Total Dummy Contacts:') }}</strong> <span>{{ $report['total_dummy'] }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>✅ {{ __('Matched with existing contact(s):') }}</strong>
                                <span>{{ $report['matched_count'] }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>🆕 {{ __('New contact(s) created:') }}</strong>
                                <span>{{ $report['created_count'] }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>🆕 {{ __('Api Call Failed:') }}</strong>
                                <span>{{ $report['processFailedCount'] }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>⚠️ {{ __('Multiple matches found:') }}</strong>
                                <span>{{ $report['multiple_count'] }}</span></p>
                        </div>
                        <div class="col-md-3">
                            <p><strong>❌ {{ __('Invalid mobile numbers:') }}</strong>
                                <span>{{ $report['invalid_count'] }}</span></p>
                        </div>
                    </div>
                @endcomponent
            </div>

            {{-- Multiple Matches --}}
            @if (!empty($multipleMatches))
                <div class="row">
                    @foreach ($multipleMatches as $match)
                        <div class="col-md-4">
                            <div class="alert alert-info">
                                <strong>{{ $match['name'] }} ({{ $match['mobile'] }})</strong><br>
                                Matches:
                                <ul>
                                    @foreach ($match['matches'] as $contact)
                                        <li style="list-style-type: none">
                                            <input class="form-check-input" type="checkbox"
                                                name="approve_multiple[{{ $match['dummy_id'] }}][]"
                                                value="{{ $contact->id }}"
                                                id="chk_{{ $match['dummy_id'] }}_{{ $contact->id }}">
                                            <label class="form-check-label"
                                                for="chk_{{ $match['dummy_id'] }}_{{ $contact->id }}">
                                                ID: {{ $contact->contact_id }} - {{ $contact->name }}
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>

                                {{-- New Contact Create Option --}}
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="create_new_contact[]"
                                        value="{{ $match['dummy_id'] }}" id="create_new_{{ $match['dummy_id'] }}">
                                    <label class="form-check-label" for="create_new_{{ $match['dummy_id'] }}">
                                        👉 Create new Contact (Ignore above matches)
                                    </label>
                                </div>
                            </div>
                        </div>
                    @endforeach

                </div>
            @endif

            {{-- Invalid Mobiles --}}
            @if (!empty($invalidMobiles))
                <div class="row">
                    @component('components.widget', ['class' => 'box-primary', 'title' => __('crm::lang.invalid_contacts')])
                        @foreach ($invalidMobiles as $invalid)
                            <div class="col-md-3 mb-3">
                                <div>
                                    <label for="mobile_{{ $invalid['dummy_id'] }}">
                                        Dummy ID: {{ $invalid['dummy_id'] }}, Name: {{ $invalid['name'] }}
                                    </label>
                                    <input type="text" name="mobiles[{{ $invalid['dummy_id'] }}]"
                                        value="{{ $invalid['mobile'] }}" class="form-control"
                                        id="mobile_{{ $invalid['dummy_id'] }}">
                                </div>
                            </div>
                        @endforeach
                    @endcomponent
                </div>
            @endif

            {{-- Submit --}}
            <div class="row">
                <div class="col-md-12 text-center mt-2 mb-4">
                    <button type="submit" class="btn btn-primary">Save & Process</button>
                </div>
            </div>

        </form>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $(document).on('submit', '#merge_contacts_form', function(e) {
                e.preventDefault();
                var form = $(this);
                var url = form.attr('action');
                var data = form.serialize();
                form.find('button[type="submit"]').attr('disabled', true);
                $.ajax({
                    method: 'POST',
                    url: url,
                    dataType: 'json',
                    data: data,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.msg);
                            window.location.href =
                                '{{ route('call-campaigns.show', $campaign->id) }}';
                        } else {
                            toastr.error(response.msg);
                            form.find('button[type="submit"]').attr('disabled', false);

                        }
                    },
                    error: function(xhr, status, error) {
                        var errorMessage = xhr.responseJSON?.message || status;
                        toastr.error(errorMessage);
                        form.find('button[type="submit"]').attr('disabled', false);
                    }
                });
            })
        })
    </script>
@endsection
