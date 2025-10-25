<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <!-- Form open tag removed since this is just for viewing -->
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">    
                    @lang('clinic::doctor.show_reference_info')
            </h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="clearfix customer_fields"></div>
                <div class="clearfix"></div>

                <!-- First Name -->
                <div class="col-md-3 individual">
                    <div class="form-group">
                        <label>@lang('business.first_name'):</label>
                        <p class="form-control-static">{{ $provider->first_name }}</p>
                    </div>
                </div>

                <!-- Last Name -->
                <div class="col-md-3 individual">
                    <div class="form-group">
                        <label>@lang('business.last_name'):</label>
                        <p class="form-control-static">{{ $provider->last_name }}</p>
                    </div>
                </div>

                <!-- Mobile Number -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>@lang('contact.mobile'):</label>
                        <p class="form-control-static">{{ $provider->mobile }}</p>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>@lang('business.email'):</label>
                        <p class="form-control-static">{{ $provider->email }}</p>
                    </div>
                </div>

                <!-- Date of Birth -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>@lang('lang_v1.dob'):</label>
                        <p class="form-control-static">{{ !empty($provider->date_of_birth) ? \Carbon\Carbon::parse($provider->date_of_birth)->format('d/m/Y') : 'N/A' }}</p>
                    </div>
                </div>

                <!-- Address -->
                <div class="col-md-3">
                    <div class="form-group">
                        <label>@lang('clinic::doctor.address'):</label>
                        <p class="form-control-static">{{ $provider->address }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>@lang('clinic::doctor.age'):</label>
                        <p class="form-control-static">{{ $provider->age }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>@lang('clinic::doctor.gender'):</label>
                        <p class="form-control-static">{{ $provider->gender }}</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>@lang('clinic::doctor.designation'):</label>
                        <p class="form-control-static">{{ $provider->designation }}</p>
                    </div>
                </div>

            </div><!-- /.row -->

        </div><!-- /.modal-body -->

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>

    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
