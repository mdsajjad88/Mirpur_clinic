@extends('clinic::layouts.app2')
@section('title', __('Doctor Profile'))
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col custom-row mt-2 doctor-heading">
                <div class="text-left"> <a
                        href="{{ action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'index']) }}"><i
                            class="fas fa-backward"></i>&nbsp; </a> <strong>@lang('clinic::lang.doctor_info')</strong></div>
                <div class="text-right">
                    <a href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'edit'], [$doctor->id]) }}"
                        class="btn btn-warning edit_profile"><i class="fas fa-pen"></i>&nbsp; @lang('clinic::lang.edit_info')</a>
                    <a href="{{ action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'doctorAppointment'], [$doctor->id]) }}"
                        class="btn make_app">
                        <i class="fas fa-plus"></i>&nbsp; @lang('clinic::lang.make_app')
                    </a>
                </div>
            </div>
        </div>
        <div class="row">
            <!-- First Instance of the Component -->
            <div class="col-md-4 mb-4">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="row">
                        <div class="col-md-3">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                <path
                                    d="M224 256A128 128 0 1 0 224 0a128 128 0 1 0 0 256zm-96 55.2C54 332.9 0 401.3 0 482.3C0 498.7 13.3 512 29.7 512l388.6 0c16.4 0 29.7-13.3 29.7-29.7c0-81-54-149.4-128-171.1l0 50.8c27.6 7.1 48 32.2 48 62l0 40c0 8.8-7.2 16-16 16l-16 0c-8.8 0-16-7.2-16-16s7.2-16 16-16l0-24c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 24c8.8 0 16 7.2 16 16s-7.2 16-16 16l-16 0c-8.8 0-16-7.2-16-16l0-40c0-29.8 20.4-54.9 48-62l0-57.1c-6-.6-12.1-.9-18.3-.9l-91.4 0c-6.2 0-12.3 .3-18.3 .9l0 65.4c23.1 6.9 40 28.3 40 53.7c0 30.9-25.1 56-56 56s-56-25.1-56-56c0-25.4 16.9-46.8 40-53.7l0-59.1zM144 448a24 24 0 1 0 0-48 24 24 0 1 0 0 48z" />
                            </svg>
                        </div>
                        <div class="col-md-9">
                            <h3>{{ $doctor->first_name ?? '' }} {{ $doctor->last_name ?? '' }}</h3>
                            @if ($doctor->is_active == 1)
                                <small class="label pull-right bg-primary"><i class="fas fa-check"></i> Active</small>
                            @else
                                <small class="label pull-right bg-red">Inactive</small>
                            @endif
                        </div>
                    </div>
                    <div class="custom-row d-flex mt-2">
                        <div class="text-left"><strong>BMDC Number:</strong></div>
                        <div class="text-right">{{ $doctor->bmdc_number ?? '' }}</div>
                    </div>
                    <div class="custom-row mt-2">
                        <div class="text-left"><strong>Email:</strong></div>
                        <div class="text-right">{{ $doctor->email ?? '' }}</div>
                    </div>
                    <div class="custom-row mt-2">
                        <div class="text-left"><strong>Mobile:</strong></div>
                        <div class="text-right">{{ $doctor->mobile ?? '' }}</div>
                    </div>
                    
                    <div class="custom-row mt-2">
                        <div class="text-left"><strong>Gender:</strong></div>
                        <div class="text-right">{{ $doctor->gender ? ucfirst($doctor->gender) : '' }}</div>
                    </div>
                    <div class="custom-row mt-2">
                        <div class="text-left"><strong>Blood Group:</strong></div>
                        <div class="text-right">{{ $doctor->blood_group ?? '' }}</div>
                    </div>
                    <div class="custom-row mt-2">
                        <div class="text-left"><strong>Finger ID:</strong></div>
                        <div class="text-right">{{ $doctor->rf_id ?? '' }}</div>
                    </div>
                    <div class="custom-row mt-2">
                        <div class="text-left"><strong>Appointment Prefix:</strong></div>
                        <div class="text-right">{{ $doctor->serial_prefix ?? '' }}</div>
                    </div>
                    <div class="custom-row mt-2">
                        <div class="text-left"><strong>Type:</strong></div>
                        <div class="text-right">{{ ucfirst($doctor->type) }}</div>
                    </div>
                @endcomponent
            </div>

            <!-- Second Instance of the Component -->
            <div class="col-md-8 mb-4">
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="row">
                        <div class="btn-container bg-secodary">
                            <div class="text-left"><strong>Degrees</strong></div>
                            <div class="text-right">
                                <a href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'addDegrees'], [$doctor->id]) }}"
                                    class="btn btn-success add_degree"><i class="fas fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <table class="table table-striped table-hover text-center" id="degree_table">
                                <thead>
                                    <tr>
                                        <th>Degree Name</th>
                                        <th>Degree Specification</th>
                                        <th>Institute</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                @endcomponent
                @component('components.widget', ['class' => 'box-primary'])
                    <div class="row">
                        <div class="btn-container bg-secodary">
                            <div class="text-left"><strong>Trainings</strong></div>
                            <div class="text-right">
                                <a href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DoctorController::class, 'addSpecilities'], [$doctor->id]) }}"
                                    class="btn btn-success add_specilities"><i class="fas fa-plus"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <table class="table table-striped table-hover text-center" id="specilities_table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Institute</th>
                                        <th>Training Duration</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                @endcomponent
            </div>
        </div>
        <div class="row doctor_details">
            <div class="col-12">
                @component('components.widget')
                    <ul class="nav nav-tabs" id="doctorTab" role="tablist">
                        {{-- <li class="nav-item">
                            <a class="nav-link" id="exam_room-tab" data-toggle="tab" href="#exam_room" role="tab"
                                aria-controls="exam_room" aria-selected="true">Exam Room</a>
                        </li> --}}

                        <li class="nav-item">
                            <a class="nav-link" id="business_day-tab" data-toggle="tab" href="#business_day" role="tab"
                                aria-controls="business_day" aria-selected="false">Working Day</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="sloot-tab" data-toggle="tab" href="#sloot" role="tab"
                                aria-controls="sloot" aria-selected="false">Slot</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="daily_sloot-tab" data-toggle="tab" href="#daily_sloot" role="tab"
                                aria-controls="daily_sloot" aria-selected="false">Daily Slot</a>
                        </li>

                    </ul>
                    <div class="tab-content" id="doctorTabContent">
                        {{-- <div class="tab-pane fade" id="exam_room" role="tabpanel" aria-labelledby="exam_room-tab">
                            <h4>Exam Room Content</h4>
                            <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Voluptatibus dolorem repudiandae, esse
                                amet rem iure magnam placeat soluta non corrupti!</p>
                        </div> --}}

                        <div class="tab-pane fade" id="business_day" role="tabpanel" aria-labelledby="business_day-tab">
                            @include('clinic::provider.tab.business_day')
                        </div>
                        <div class="tab-pane fade" id="sloot" role="tabpanel" aria-labelledby="sloot-tab">
                            @include('clinic::provider.tab.monthly_slot')

                        </div>
                        <div class="tab-pane fade" id="daily_sloot" role="tabpanel" aria-labelledby="daily_sloot-tab">
                            @include('clinic::provider.tab.daily_slot')

                        </div>

                    </div>
                @endcomponent
            </div>
        </div>
        <div class="modal fade add_degree_form" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade edit_profile_form" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade edit_degree_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade add_specilities_form" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade edit_specilities_form" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade edit_business_day_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade create_business_day_modal" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade monthly_sloot_generate" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_monthly_slot_data" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_daily_slot_data" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
        <div class="modal fade view_daily_slot_details" tabindex="-1" role="dialog"
            aria-labelledby="gridSystemModalLabel">
        </div>
    </div>
@endsection
<script>
    var doctorId = @json($doctor->id);
</script>
@include('clinic::provider.providerjs')
