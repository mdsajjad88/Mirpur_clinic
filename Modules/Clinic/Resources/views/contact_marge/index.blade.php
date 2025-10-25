@extends('clinic::layouts.app2')
@section('content')
    <div class="container-fluid" id="top">
        {{-- 🔝 Filter & Buttons --}}
        <div class="row mb-3">
            <div class="col-md-6 text-center">
                {{-- Compare Toggle --}}
                <a href="{{ route('duplicate-contact-marge.index', [
                    'is_number_with_name' => $is_number_with_name,
                    'compare_with_pharmacy' => $compare_with_pharmacy ? null : 1,
                ]) }}"
                    class="btn btn-primary btn-sm">
                    @if ($compare_with_pharmacy)
                        ❌ Remove Pharmacy Compare
                    @else
                        ✅ Compare With Pharmacy
                    @endif
                </a>
            </div>
            <div class="col-md-6 text-end">
                {{-- 🔍 Search Box --}}
                <form method="GET" action="{{ route('duplicate-contact-marge.index') }}" class="d-inline-block">
                    <input type="hidden" name="is_number_with_name" value="{{ $is_number_with_name }}">
                    <input type="hidden" name="compare_with_pharmacy" value="{{ $compare_with_pharmacy }}">

                    <div class="row">
                        <div class="col-md-9">
                            <input type="text" name="search_mobile" value="{{ $searchMobile }}" class="form-control"
                                placeholder="Search by mobile...">
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-info" type="submit">Search</button>
                        </div>
                    </div>
                </form>
            </div>


        </div>


        {{-- 🔽 Cards --}}
        <div class="row">
            @foreach ($duplicates as $dup)
                <div class="col-md-6 mb-3">
                    <div class="card border-info h-100" style="margin-top: 10px;">
                        <div class="card-header text-white d-flex justify-content-between align-items-center"
                            style="background-color: #a8a8a4; padding: 5px">
                            <span>📞 {{ $dup['mobile'] }} </span>

                        </div>
                        <div class="card-body p-2">
                            <div class="row">
                                <!-- Clinic Side -->
                                <div
                                    @if ($compare_with_pharmacy == 1) class="col-md-6 border-end" @else class="col-md-12 border-end" @endif>
                                    <h4 class="text-primary">Clinic ({{ count($dup['rows']) }})</h4>
                                    <table class="table table-sm table-borderless mb-0">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Transactions</th>
                                                <th><input type="checkbox" class="select-all input-icheck"
                                                        data-mobile="{{ $dup['mobile'] }}"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dup['rows'] as $index => $c)
                                                <tr>
                                                    <td>{{ $c->first_name }} {{ $c->last_name }} ({{ $c->contact_id }})
                                                    </td>
                                                    <td>
                                                        @foreach ($c->transactions as $t)
                                                            <div>💰 {{ round($t->final_total) }} | 📅
                                                                {{ $t->transaction_date }} |
                                                                {{ ucfirst($t->payment_status) }}
                                                            </div>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <input type="checkbox" class="contact-checkbox input-icheck"
                                                            name="contact_ids[]" value="{{ $c->id }}"
                                                            data-mobile="{{ $dup['mobile'] }}"
                                                            data-contact-id="{{ $c->contact_id }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pharmacy Side -->
                                @if ($compare_with_pharmacy == 1)
                                    <div class="col-md-6">
                                        <h4 class="text-success">Pharmacy
                                            ({{ isset($pharmacyMap[$dup['mobile']]) ? count($pharmacyMap[$dup['mobile']]) : 0 }})
                                        </h4>
                                        @if ($compare_with_pharmacy && !empty($pharmacyMap[$dup['mobile']]))
                                            <table class="table table-sm table-borderless mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Name</th>
                                                        <th>Transactions</th>
                                                        <th><input type="checkbox" class="select-all input-icheck"
                                                                data-mobile="{{ $dup['mobile'] }}"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($pharmacyMap[$dup['mobile']] as $index => $p)
                                                        <tr>
                                                            <td>{{ $p['first_name'] ?? '' }} {{ $p['last_name'] ?? '' }}
                                                                {{ $p['contact_id'] ?? '' }}
                                                            </td>
                                                            <td>
                                                                @foreach ($c->transactions as $t)
                                                                    <div>💰 {{ round($t->final_total) }} | 📅
                                                                        {{ $t->transaction_date }} |
                                                                        {{ ucfirst($t->payment_status) }}
                                                                    </div>
                                                                @endforeach
                                                            </td>
                                                            <td>
                                                                <input type="checkbox" class="contact-checkbox input-icheck"
                                                                    name="pharmacy_contact_ids[]"
                                                                    value="{{ $p['contact_id'] ?? '' }}"
                                                                    data-mobile="{{ $dup['mobile'] }}"
                                                                    data-contact-id="{{ $p['contact_id'] ?? '' }}">

                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <p class="text-muted">N/A</p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-md-12 mt-2">
            <button id="markInactive" class="btn btn-danger">Mark Selected Inactive</button>
        </div>


    </div>

    {{-- Pagination --}}
    <div class="row">
        <div class="col-md-12">
            {{ $groups->links() }}
        </div>
    </div>
    </div>
@endsection
@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {
            $('.input-icheck').iCheck({
                checkboxClass: 'icheckbox_square-blue',
                radioClass: 'iradio_square-blue',
                increaseArea: '20%'
            });

            // Select all per mobile
            // Clinic select all
           // Common select-all (clinic + pharmacy একসাথে নিয়ন্ত্রণ করবে)
$('.select-all').on('ifChanged', function() {
    let mobile = $(this).data('mobile');
    let checked = $(this).is(':checked');

    // Clinic side সব চেকবক্স control
    $(`.contact-checkbox[name="contact_ids[]"][data-mobile="${mobile}"]`)
        .iCheck(checked ? 'check' : 'uncheck');

    // Pharmacy side সব চেকবক্স control
    $(`.contact-checkbox[name="pharmacy_contact_ids[]"][data-mobile="${mobile}"]`)
        .iCheck(checked ? 'check' : 'uncheck');

    // দুই দিকের select-all একে অপরের সাথে sync
    $(`.select-all[data-mobile="${mobile}"]`).iCheck(checked ? 'check' : 'uncheck');
});


            // Sync clinic <-> pharmacy checkboxes
            $('.contact-checkbox').on('ifChanged', function() {
                let contactId = $(this).data('contact-id');
                let checked = $(this).is(':checked');
                $(`.contact-checkbox[data-contact-id="${contactId}"]`).iCheck(checked ? 'check' :
                'uncheck');
            });

            // AJAX submit
            $('#markInactive').on('click', function() {
                let selected = [];
                $('.contact-checkbox:checked').each(function() {
                    selected.push($(this).val());
                });

                if (selected.length === 0) {
                    alert('No contacts selected!');
                    return;
                }

                $.ajax({
                    url: "{{ route('contacts.markInactive') }}",
                    method: 'POST',
                    data: {
                        contact_ids: selected,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            location.reload(); // Or AJAX reload partial
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(err) {
                        toastr.error(err.responseJSON.message);
                    }
                });
            });
        });
    </script>
@endsection
