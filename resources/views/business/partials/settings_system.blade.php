<div class="pos-tab-content">
    <div class="row">
       <div class="col-sm-4">
           <div class="form-group">
               {!! Form::label('theme_color', __('lang_v1.theme_color')) !!}
               {!! Form::select('theme_color', $theme_colors,   $business->theme_color, 
                   ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'style' => 'width: 100%;']) !!}
           </div>
       </div>
       <div class="col-sm-4">
           <div class="form-group">
               @php
                   $page_entries = [25 => 25, 50 => 50, 100 => 100, 200 => 200, 500 => 500, 1000 => 1000, -1 => __('lang_v1.all')];
               @endphp
               {!! Form::label('default_datatable_page_entries', __('lang_v1.default_datatable_page_entries')) !!}
               {!! Form::select('common_settings[default_datatable_page_entries]', $page_entries, !empty($common_settings['default_datatable_page_entries']) ? $common_settings['default_datatable_page_entries'] : 25 , 
                   ['class' => 'form-control select2', 'style' => 'width: 100%;', 'id' => 'default_datatable_page_entries']) !!}
           </div>
       </div>
       <div class="col-sm-4">
           <div class="form-group">
               <div class="checkbox">
                 <label>
                   {!! Form::checkbox('enable_tooltip', 1, $business->enable_tooltip , 
                   [ 'class' => 'input-icheck']) !!} {{ __( 'business.show_help_text' ) }}
                 </label>
               </div>
           </div>
       </div>
   </div>
   <div class="row"> 
       <div class="col-sm-4">
           <div class="form-group">
               {!! Form::label('exclude_period', __('Exclude Products which Checked'), ['class' => 'control-label']) !!} @show_tooltip('Exclude products which have been checked in Stock Audit. Default is the last 7 days. You can adjust this period in the settings.')
               {!! Form::select('common_settings[exclude_checked]', $exclude_checked_options, $selected_exclude_checked, 
                   ['class' => 'form-control select2', 'placeholder' => __('Select days'), 'style' => 'width: 100%;']) !!}
           </div>
       </div>        
       <div class="col-sm-4">
           <div class="form-group">
               {!! Form::label('exclude_period', __('Expiring Soon'), ['class' => 'control-label']) !!} @show_tooltip('Enter the number of days before a product is considered expiring soon. Default is 30 days if no value is provided. (color: red)')
               {!! Form::number('common_settings[expiring_soon]', $expiring_soon, 
                   ['class' => 'form-control', 'placeholder' => __('Enter days'), 'style' => 'width: 100%;']) !!}
           </div>
       </div>        
       <div class="col-sm-4">
           <div class="form-group">
               {!! Form::label('exclude_period', __('Expiring Later'), ['class' => 'control-label']) !!} @show_tooltip('Enter the number of days before a product is considered expiring later. Default is 90 days if no value is provided. (color: orange)')
               {!! Form::number('common_settings[expiring_later]', $expiring_later, 
                   ['class' => 'form-control', 'placeholder' => __('Enter days'), 'style' => 'width: 100%;']) !!}
           </div>
       </div>
       <div class="col-sm-4">
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        {!! Form::checkbox('common_settings[enable_reconciliation]', 1, !empty($common_settings['enable_reconciliation']) ? $common_settings['enable_reconciliation'] : 0, 
                        ['class' => 'input-icheck']) !!}
                        {{ __('Enable Reconciliation') }}
                        @show_tooltip('Enable reconciliation to show reconciliation options in stock audit.')
                    </label>
                </div>
            </div>
        </div>
        <div class="col-sm-4"> 
            <div class="form-group">
                {!! Form::label('reconciliation_location', __('lang_v1.reconciliation_location') . ':') !!} 
                @show_tooltip(__('lang_v1.reconciliation_location_tooltip'))
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-map-marker"></i>
                    </span>
                    {!! Form::select('common_settings[reconciliation_location]', $business_locations, $common_settings['reconciliation_location'] ?? '', ['class' => 'form-control select2', 'style' => 'width: 100%;', 'placeholder' => __('None')]) !!}
                </div>
            </div>        
        </div>
        <div class="col-sm-4"> 
            <div class="form-group">
                {!! Form::label('meeting_link', __('lang_v1.meeting_link') . ':') !!} 
                @show_tooltip(__('lang_v1.meeting_link_tooltip'))
                <div class="input-group">
                    <span class="input-group-addon">
                        <i class="fa fa-link"></i>
                    </span>
                    {!! Form::text('common_settings[meeting_link]', $common_settings['meeting_link'] ?? '', ['class' => 'form-control', 'placeholder' => __('Enter meeting link (e.g. Zoom, Google Meet, etc.)')]) !!}
                </div>
            </div>        
        </div>        
   </div>                
</div>