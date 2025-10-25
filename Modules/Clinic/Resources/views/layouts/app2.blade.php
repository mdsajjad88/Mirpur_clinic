@inject('request', 'Illuminate\Http\Request')

@if (
    $request->segment(1) == 'pos' &&
        ($request->segment(2) == 'create' || $request->segment(3) == 'edit' || $request->segment(2) == 'payment'))
    @php
        $pos_layout = true;
    @endphp
@else
    @php
        $pos_layout = false;
    @endphp
@endif

@php
    $whitelist = ['127.0.0.1', '::1'];
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}"
    dir="{{ in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title') - {{ Session::get('business.name') }}</title>

    @include('layouts.partials.css')
    @include('clinic::layouts.partials.clinic_css')
    @yield('css')
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
</head>

<body
    class="@if ($pos_layout) hold-transition lockscreen @else hold-transition skin-@if (!empty(session('business.theme_color'))){{ session('business.theme_color') }}@else{{ 'blue-light' }} @endif sidebar-mini @endif">
    <div class="wrapper thetop">
        <script type="text/javascript">
            if (localStorage.getItem("upos_sidebar_collapse") == 'true') {
                var body = document.getElementsByTagName("body")[0];
                body.className += " sidebar-collapse";
            }
        </script>
        @if (!$pos_layout)
            @include('layouts.partials.header')

            @include('layouts.partials.clinic_sidebar')
        @else
            @include('layouts.partials.header-pos')
        @endif

        @if (in_array($_SERVER['REMOTE_ADDR'], $whitelist))
            <input type="hidden" id="__is_localhost" value="true">
        @endif

        <!-- Content Wrapper. Contains page content -->
        <div class="@if (!$pos_layout) content-wrapper @endif">
            <!-- empty div for vuejs -->
            <div id="app">
                @yield('vue')
            </div>
            <!-- Add currency related field-->
            @if (!empty(session('currency')))
                <input type="hidden" id="__code" value="{{ session('currency')['code'] }}">
                <input type="hidden" id="__symbol" value="{{ session('currency')['symbol'] }}">
                <input type="hidden" id="__thousand" value="{{ session('currency')['thousand_separator'] }}">
                <input type="hidden" id="__decimal" value="{{ session('currency')['decimal_separator'] }}">
                <input type="hidden" id="__symbol_placement"
                    value="{{ session('business.currency_symbol_placement') }}">
                <input type="hidden" id="__precision" value="{{ session('business.currency_precision', 2) }}">
                <input type="hidden" id="__quantity_precision"
                    value="{{ session('business.quantity_precision', 2) }}">
            @else
                <script>
                    window.location = "{{ route('home') }}";
                </script>
            @endif
            <!-- End of currency related field-->
            @can('view_export_buttons')
                <input type="hidden" id="view_export_buttons">
            @endcan
            @if (isMobile())
                <input type="hidden" id="__is_mobile">
            @endif
            @if (session('status'))
                <input type="hidden" id="status_span" data-status="{{ session('status.success') }}"
                    data-msg="{{ session('status.msg') }}">
            @endif
            @yield('content')

            <div class='scrolltop no-print'>
                <div class='scroll icon'><i class="fas fa-angle-up"></i></div>
            </div>

            @if (config('constants.iraqi_selling_price_adjustment'))
                <input type="hidden" id="iraqi_selling_price_adjustment">
            @endif

            <!-- This will be printed -->
            <section class="invoice print_section" id="receipt_section">
            </section>

        </div>
        @include('home.todays_profit_modal')
        <!-- /.content-wrapper -->

        @if (!$pos_layout)
            @include('layouts.partials.footer')
        @else
            @include('layouts.partials.footer_pos')
        @endif

        <audio id="success-audio">
            <source src="{{ asset('/audio/success.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/success.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="error-audio">
            <source src="{{ asset('/audio/error.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/error.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
        <audio id="warning-audio">
            <source src="{{ asset('/audio/warning.ogg?v=' . $asset_v) }}" type="audio/ogg">
            <source src="{{ asset('/audio/warning.mp3?v=' . $asset_v) }}" type="audio/mpeg">
        </audio>
    </div>

    @if (!empty($__additional_html))
        {!! $__additional_html !!}
    @endif

    @include('layouts.partials.javascripts')
    <div class="modal fade view_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel"></div>

    @if (!empty($__additional_views) && is_array($__additional_views))
        @foreach ($__additional_views as $additional_view)
            @includeIf($additional_view)
        @endforeach
    @endif
    @include('clinic::layouts.clinic_product_js')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    @php
        $is_doctor = false;
        $data = DB::table('doctor_profiles')
            ->where('user_id', auth()->user()->id)
            ->first();
        if ($data) {
            $is_doctor = true;
        }
        if($is_doctor) {
            $log = DB::table('doctor_available_status_logs')->where('doctor_profile_id', $data->id)->whereNull('end_time')
                    ->latest('break_start_time')->first();
        $expectDuration = $log ? $log->expect_duration : 0;
        $breakStart = $log ? $log->break_start_time : null;
        }
        
    @endphp
    <div class="modal fade" id="doctorUnavailableModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="doctorUnavailableForm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Mark Doctor as Unavailable</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="expect_duration">Break Time (in minutes)</label>
                            <input type="number" class="form-control" id="expect_duration" name="expect_duration"
                                required placeholder="Enter break time in minutes">
                        </div>
                        <div class="form-group">
                            <label for="reason">Reason</label>
                            <textarea class="form-control" id="reason" name="reason" required placeholder="Enter break reason"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger" id="doctorUnavailableSubmit">Confirm
                            Unavailability</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if ($is_doctor)
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            $(document).ready(function() {
            initDoctorStatusToggle({
                doctorId: "{{ $data->id }}",
                isAvailable: {{ $data->is_available ?? 0 }},
                breakStart: "{{ $breakStart }}",
                expectDuration: {{ $expectDuration }},
            });
        });
        </script>
    @endif
</body>

</html>
