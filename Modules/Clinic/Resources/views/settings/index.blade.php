@extends('clinic::layouts.app2')
@section('title', __('Setting 1'))
@section('content')
{{-- {!! Form::open(['url' => action([\App\Http\Controllers\BusinessController::class, 'postBusinessSettings']), 'method' => 'post', 'id' => 'bussiness_edit_form',
           'files' => true ]) !!} --}}
{!! Form::open(['url' => action([\Modules\Clinic\Http\Controllers\setting\SettingController::class, 'store']), 'method' => 'post', 'id' => 'clinic_setting_edit_form',
           'files' => true ]) !!}
<div class="container-fluid mt-1">
    <div class="row">
        <!-- Left Column (Navbar Menu) -->
        <div class="col-md-2">
            <ul class="nav nav-pills nav-stacked" id="myTab" role="tablist">
                <li class="active">
                    <a href="#home" data-toggle="tab">Location</a>
                </li>
                <li>
                    <a href="#profile" data-toggle="tab">Profile</a>
                </li>
                <li>
                    <a href="#contact" data-toggle="tab">Contact</a>
                </li>
            </ul>
        </div>

        <div class="col-md-10">
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade in active" id="home">
                    @include('clinic::settings.locations')
                </div>
                
                <div class="tab-pane fade" id="profile">
                    <h4>Profile Content</h4>
                    <p>This is the content for the Profile tab.</p>
                </div>
                <div class="tab-pane fade" id="contact">
                    <h4>Contact Content</h4>
                    <p>This is the content for the Contact tab.</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            {!! Form::submit('Update', ['class' => 'btn btn-success', 'id' => 'save-button']) !!}
        </div>
        <div class="col-md-4"></div>
    </div>
</div>
{!! Form::close()!!}

@endsection