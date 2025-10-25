<div class="pos-tab-content">
    <div class="row">
        <div class="col-md-8">
            <h4>{{ __('lang_v1.cash_register_a4_print') }}</h4>
        </div>
        <div class="clearfix"></div>
        
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('common_settings[enable_details_by_bill]', 1, !empty($common_settings['enable_details_by_bill']) ? true : false, 
                        [ 'class' => 'input-icheck']) !!} {{ __( 'lang_v1.details_by_bill' ) }}
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('common_settings[enable_summary]', 1, !empty($common_settings['enable_summary']) ? true : false, 
                        [ 'class' => 'input-icheck']) !!} {{ __( 'lang_v1.summary' ) }}
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('common_settings[enable_details_by_customer]', 1, !empty($common_settings['enable_details_by_customer']) ? true : false, 
                        [ 'class' => 'input-icheck']) !!} {{ __( 'lang_v1.details_by_customer' ) }}
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('common_settings[enable_details_by_customer_group]', 1, !empty($common_settings['enable_details_by_customer_group']) ? true : false, 
                        [ 'class' => 'input-icheck']) !!} {{ __( 'lang_v1.details_by_customer_group' ) }}
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('common_settings[enable_details_of_products_sold]', 1, !empty($common_settings['enable_details_of_products_sold']) ? true : false, 
                        [ 'class' => 'input-icheck']) !!} {{ __( 'lang_v1.details_of_products_sold' ) }}
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('common_settings[enable_details_by_brand]', 1, !empty($common_settings['enable_details_by_brand']) ? true : false, 
                        [ 'class' => 'input-icheck']) !!} {{ __( 'lang_v1.details_by_brand' ) }}
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('common_settings[enable_details_by_category]', 1, !empty($common_settings['enable_details_by_category']) ? true : false, 
                        [ 'class' => 'input-icheck']) !!} {{ __( 'lang_v1.details_by_category' ) }}
                    </label>
                </div>
            </div>
        </div>
        
        <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('common_settings[enable_service_details]', 1, !empty($common_settings['enable_service_details']) ? true : false, 
                        [ 'class' => 'input-icheck']) !!} {{ __( 'lang_v1.service_details' ) }}
                    </label>
                </div>
            </div>
        </div>
        
    </div>
</div>
