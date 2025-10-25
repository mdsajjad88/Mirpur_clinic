@inject('request', 'Illuminate\Http\Request')

<div class="row no-print align-items-center text-center" style="margin-bottom: 15px;">
    <!-- Left Button -->
    <div class="col-md-1 text-left">
        <button id="fullscreen-btn" class="btn btn-info btn-flat m-2 hidden-xs btn-sm">
            <i title="Fullscreen" class="fas fa-expand"></i>
        </button>
    </div>

    <!-- Center Content -->
    <div class="col-md-10">
      <div class="header-flex">
        <div class="title-container">
          <h2 class="system-title">
            <i class="fas fa-cog"></i> CareNet Queue Management
          </h2>
          <p class="website-url">www.careneterp.com</p>
        </div>
        <div class="clock-container">
          <p class="system-date" id="current-date">-- --- ----</p>
          <p class="system-time" id="current-time">--:--:-- --</p>
        </div>
      </div>      
    </div>

    @auth
        <!-- Right Button -->
        <div class="col-md-1 text-right">
            <a href="{{ action([\Modules\Clinic\Http\Controllers\ClinicController::class, 'index']) }}"
              title="{{ __('lang_v1.go_back') }}"
              data-toggle="tooltip"
              data-placement="bottom"
              class="btn btn-info btn-flat m-2 hidden-xs btn-sm">
                <strong><i class="fa fa-backward fa-lg"></i></strong>
            </a>
        </div>
    @endauth
</div>

