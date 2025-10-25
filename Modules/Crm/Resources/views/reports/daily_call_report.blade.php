<div class="row">
    <div class="col-md-12">
        <div class="box box-primary">
            <!-- Filter Section -->
            <div class="box-header with-border bg-primary">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="box-title" style="color: white; font-weight: bold;">
                            <i class="fa fa-phone"></i> @lang('crm::lang.daily_call_report')
                        </h2>
                    </div>
                    <div class="col-md-6 text-right">
                        <form id="report-filter-form" method="GET" class="form-inline">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon" style="background-color: #fff;">
                                        <i class="fa fa-calendar"></i>
                                    </span>
                                    <input class="form-control" type="date" value="{{ request()->get('report_date', Carbon::today()->format('Y-m-d')) }}" name="report_date" id="report_date_filter" max="{{ Carbon::today()->format('Y-m-d') }}">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- This section will be reloaded via AJAX -->
            <div class="report_print_section">
                @include('crm::reports.partials.call_report', ['call_report' => $call_report])
            </div>

            <!-- Footer with export options -->
            <div class="box-footer text-center" style="background-color: #f8f9fa; border-top: 1px solid #ddd;">
                <div class="btn-group">
                    <button type="button" class="btn btn-default btn-sm" id="print-report-btn">
                        <i class="fa fa-print"></i> @lang('messages.print')
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
