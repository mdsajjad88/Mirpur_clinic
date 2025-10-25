@extends('clinic::layouts.app2')
@section('title', __('Patients'))
@section('content')
<style>
    .custom-shadow {
            /* Adjust the values to get the desired shadow effect */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2), 0 6px 20px rgba(0, 0, 0, 0.19);
            padding: 10px;
        }
        .text-left {
            text-align: left;
            line-height: 20px;
        }
        .text-right {
            text-align: right;
            line-height: 20px;
        }
        .custom-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }
        .icon-large {
            font-size: 4rem; /* Adjust this value to make the icon larger or smaller */
            color: #6375E1;  /* Optional: Customize the color */
        }
        .doctorCard{
            margin-bottom: 10px;
            margin-top: 5px;
        }
        .mt-2{
            margin-top: 5px;
        }
        .fa-user-tie{
            font-size: 50px;
        }
        
</style>
<div class="container-fluid mt-5">
    <div class="row">
        <!-- Doctor Card -->

        <!-- Another Doctor Card -->
        <div class="col-md-3 doctorCard">
            <div class="card custom-shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <i class="fas fa-user-tie"></i>                      
                        </div>
                        <div class="col-md-6">
                            <h5 class="card-title">Rubel Hasan</h5>
                        </div>
                        <div class="col-md-3"> 
                            <div class="custom-switch">
                                <input type="checkbox" class="custom-control-input" id="customSwitch">
                                <label class="custom-control-label" for="customSwitch"></label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="custom-row d-flex mt-2">
                        <div class="text-left"><strong>Serial Number:</strong></div>
                        <div class="text-right">A-33499</div>
                    </div>
                    <div class="custom-row mt-2">
                        <div class="text-left"><strong>Email:</strong></div>
                        <div class="text-right">smshahriarjahan@gmail.com</div>
                    </div>
                    <div class="custom-row mt-2">
                        <div class="text-left"><strong>Mobile:</strong></div>
                        <div class="text-right">01681462633</div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12">
                            <a href="{{action([\Modules\Clinic\Http\Controllers\PatientController::class, 'profile'])}}" class="btn btn-info form-control">@lang('clinic::lang.info')</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
       

    </div>
        
    
</div>         
</div>


@endsection 