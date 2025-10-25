<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse"
                    data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                @if (auth()->user()->can('admin') || auth()->user()->can('superadmin'))
                    <a class="nav-link" href="{{ action([\Modules\Clinic\Http\Controllers\MedicineFulfillmentReportController::class, 'medicineFulfillmentReport']) }}">
                        <i class="fas fa-fill"></i>
                    </a>
                @endif
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    @if (auth()->user()->can('admin') || auth()->user()->can('superadmin'))
                        <li @if (request()->segment(1) == 'medicine-fulfillment-report') class="active" @endif>
                            <a class="nav-link" href="{{ action([\Modules\Clinic\Http\Controllers\MedicineFulfillmentReportController::class, 'medicineFulfillmentReport']) }}">
                                {{ __('clinic::lang.medicine_fulfillment') }}
                            </a>
                        </li>
                        <li @if (request()->segment(1) == 'stock-out-analysis-report') class="active" @endif>
                            <a
                                href="{{ action([\Modules\Clinic\Http\Controllers\StockOutAnalysisReportController::class, 'stockOutAnalysisReport']) }}">
                                @lang('clinic::lang.stock_out_analysis_report')
                            </a>
                        </li>
                        <li @if (request()->segment(1) == 'demand-report') class="active" @endif>
                            <a
                                href="{{ action([\Modules\Clinic\Http\Controllers\DemandReportController::class, 'demandReport']) }}">
                                @lang('clinic::lang.demand_report')
                            </a>
                        </li>
                        <li class="@if (request()->segment(1) == 'prescription-fulfillment-report') active @endif">
                            <a href="{{ action([\Modules\Clinic\Http\Controllers\PrescriptionReportController::class, 'prescriptionFulfillmentReport']) }}">
                                <span>@lang('clinic::lang.prescription_fulfillment')</span>
                            </a>
                        </li>
                        <li class="@if (request()->segment(1) == 'therapy-demand-report') active @endif">
                            <a href="{{ action([\Modules\Clinic\Http\Controllers\TherapyDemandReportController::class, 'demandReport']) }}">
                                <span>@lang('clinic::lang.therapy_analysis_report')</span>
                            </a>
                        </li>
                        <li class="@if (request()->segment(1) == 'service-comparison-report') active @endif">
                            <a href="{{ action([\Modules\Clinic\Http\Controllers\ServiceComparisonReportController::class, 'serviceComparisonReport']) }}">
                                <span>Service Comparison Report</span>
                            </a>
                        </li>
                    @endif
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
