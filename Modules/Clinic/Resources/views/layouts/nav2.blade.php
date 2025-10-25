<section class="no-print">
    <style type="text/css">
        #contacts_login_dropdown::after {
            display: inline-block;
            width: 0;
            height: 0;
            margin-left: 0.255em;
            vertical-align: 0.255em;
            content: "";
            border-top: 0.3em solid;
            border-right: 0.3em solid transparent;
            border-bottom: 0;
            border-left: 0.3em solid transparent;
        }
    </style>
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><i class="fas fa fa-home"></i> HOME</a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               SCHEDULE
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\DoctorScheduleController::class, 'index'])}}"><i class="fas fa-clipboard-list"></i> Doctor Schedule</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\TherapyScheduleController::class, 'index'])}}"><i class="fas fa-calendar-day"></i> Therapy Schedule</a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               PROVIDER
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\ProviderController::class, 'index'])}}"><i class="fas fa-user"></i> Doctors/Therapist</a> 
                            </div>
                        </li>
                    @endcan                                 
                    
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               PATIENT
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\PatientController::class, 'index'])}}"> <i class="fas fa-bed"></i> Patients</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\SubsPatientController::class, 'index'])}}"><i class="fas fa-hospital-user"></i> Subscription Patient list</a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               APPOINTMENT
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\AllAppointmentController::class, 'index'])}}"> <i class="fas fa-folder-open"></i> All Appointment</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\AppReportController::class, 'index'])}}"> <i class="fas fa-file-medical"></i> Appointment Report</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewDoctorController::class, 'index'])}}"> <i class="fas fa-calendar-check"></i> New Doctor Appointment</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTherapyController::class, 'index'])}}"><i class="fas  fa-calendar-alt"></i> New Therapy Appointment</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\PastDoctorController::class, 'index'])}}"><i class="fas fa-backward"></i> Past Doctor Appointment</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\PastTherapyController::class, 'index'])}}"> <i class="fas fa-fast-backward"></i> Past Theory Appointment</a>

                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NextVisitController::class, 'index'])}}"><i class="fas fa-hand-point-right"></i> Next Visit Date </a>

                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\AppRescheduleController::class, 'index'])}}"><i class="fas fa-clock"></i> Appointment Reschedule </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\AppTestListController::class, 'index'])}}"><i class="fas fa-list"></i> Test List </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-comment-medical"></i> New Test</a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               CRM
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\CrmCallHistoriesController::class, 'index'])}}"><i class="fas fa-phone-volume"></i> Call Histories</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\FollowUpCallListController::class, 'index'])}}"><i class="fas fa-user-edit"></i> Follow up call list </a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               PRESCRIPTION
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\PrescriptionsController::class, 'index'])}}"><i class="fas fa-prescription"></i> Prescriptions</a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               STORE
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\ClinicStoreController::class, 'index'])}}"><i class="fas fa-store"></i> All products and stock</a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               REPORTS
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-shopping-cart"></i> Subscription Payment Report</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-money-bill"></i> Service Payment Report</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-file-invoice-dollar"></i> Consultation Payment Report</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-comments-dollar"></i> Payment Report</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-comment-dollar"></i> Today Payment Report</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-retweet"></i> Refund List </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-percent"></i> Additional Discount Report </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-key"></i> Activity Log </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-chart-pie"></i> Graph-Chart-Report </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-dollar-sign"></i> Income Report </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-book-medical"></i> Patient Medical Test Report </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-reply-all"></i> Total Medical Test Sell List  </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-user-plus"></i> Follo Up Call List </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-heart"></i> Patient Diseases Report </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-chart-area"></i> Age Graph Chart </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-clock"></i> Patient Age Report </a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               MEMOS
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\MemosController::class, 'index'])}}">#######</a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               PAYMENT
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\SubsPaymentController::class, 'index'])}}"><i class="fas fa-money-bill-wave"></i> Subscription Payment</a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               AGENT
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\AgentProfileController::class, 'index'])}}"><i class="fas fa-user-shield"></i> Agent Profile</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\AgentMappingController::class, 'index'])}}"><i class="fas fa-globe"></i> Mapping </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\AgentDetailsController::class, 'index'])}}"><i class="fas fa-address-card"></i> Agent Details</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\AgentCommissionController::class, 'index'])}}"> <i class="fas fa-compass"></i> Agent Commission</a>
                            </div>
                        </li>
                    @endcan
                    @can('crm.access_contact_login')
                        <li class="nav-item @if(request()->segment(2) == 'all-contacts-login' || request()->segment(2) == 'index') active @endif">
                            <a class="nav-link dropdown-toggle" href="#" id="contacts_login_dropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                               SETTING
                            </a>
                            <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-list"></i> Appointment Types</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"> <i class="fas fa-bed"></i> Patient Types </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-users"></i> User Management</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-subscript"></i> Subscriptions</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-info"></i> Clinic Information</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-key"></i> Clinic Setting</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-flask"></i> Lab </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-percent"></i> Discount</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-disease"></i> Patients Disease</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-receipt"></i> Invoice Setting</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-user-tie"></i> Doctor Advice</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-outdent"></i> Medical Test List</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-bolt"></i> Custom Field </a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-comment-dots"></i> SMS Template</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-recycle"></i> Reason</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"> <i class="fas fa-comment-dollar"></i> Send SMS User List</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"><i class="fas fa-address-card"></i> Profile Marge</a>
                              <a class="dropdown-item" href="{{action([\Modules\Clinic\Http\Controllers\NewTestController::class, 'index'])}}"> <i class="fas fa-leaf"></i> Custom Variable</a>
                            </div>
                        </li>
                    @endcan
                    
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>