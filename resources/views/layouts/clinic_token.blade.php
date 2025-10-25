@inject('request', 'Illuminate\Http\Request')

@php
    $pos_layout = true;
@endphp

<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) ? 'rtl' : 'ltr'}}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!-- Tell the browser to be responsive to screen width -->
        <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title') - {{ Session::get('business.name') }}</title> 

        @include('layouts.partials.css')

        @yield('css')
    </head>

    <body class="hold-transition lockscreen">
        <div class="wrapper">
            <script type="text/javascript">
                if(localStorage.getItem("upos_sidebar_collapse") == 'true'){
                    var body = document.getElementsByTagName("body")[0];
                    body.className += " sidebar-collapse";
                }
            </script>
        
            <!-- Content Wrapper. Contains page content -->
            <div class="container-fluid">
                @include('layouts.partials.header-clinic')

                @yield('content')

                <!-- This will be printed -->
                <section class="invoice print_section" id="receipt_section">
                </section>
                
            </div>
            @include('home.todays_profit_modal')
            <!-- /.content-wrapper -->

            {{-- @include('layouts.partials.footer-restaurant') --}}

        </div>

        @include('layouts.partials.javascripts')
        <script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
        <div class="modal fade view_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel"></div>
    </body>

</html>