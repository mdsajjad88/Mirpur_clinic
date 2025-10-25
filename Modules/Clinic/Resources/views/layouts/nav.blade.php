<section class="no-print">
    <nav class="navbar navbar-default bg-white m-4">
        <div class="container-fluid">
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                        <li @if (request()->segment(1) == 'clinic') class="active" @endif>
                            <a
                                href="{{ action([\Modules\Clinic\Http\Controllers\ClinicController::class, 'index']) }}">
                                @lang('clinic::lang.clinic')
                            </a>
                        </li>
                </ul>

            </div><!-- /.navbar-collapse -->
        </div><!-- /.container-fluid -->
    </nav>
</section>
