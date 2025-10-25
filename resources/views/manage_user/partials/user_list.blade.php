@can('user.view')
    <div class="table-responsive">
        <table class="table table-bordered table-striped" id="users_table">
            <thead>
                <tr>
                    <th>@lang('business.username')</th>
                    <th>@lang('user.name')</th>
                    <th>@lang('user.role')</th>
                    <th>@lang('business.email')</th>
                    <th>@lang('messages.action')</th>
                </tr>
            </thead>
        </table>
    </div>
@endcan
