@component('components.filters', ['title' => __('report.filters'), 'class' => 'box-primary'])
    <div class="row">
        <div class="col-md-3">
            {!! Form::label('service_type', __('clinic::lang.service_type') . ':') !!}
            {!! Form::select('service_type', $service_types, null, [
                'class' => 'form-control select2',
                'style' => 'width:100%',
                'placeholder' => __('lang_v1.all'),
                'id' => 'service_type',
            ]) !!}
        </div>
        <div class="col-md-3"></div>
        <div class="col-md-3"></div>
        <div class="col-md-3"></div>
    </div>
@endcomponent
@component('components.widget', ['class' => 'box-primary'])
    <table class="table table-striped" id='patient_transaction_table'>
        <thead>
            <tr>
                <th>Date</th>
                <th>Invoice</th>
                <th>Type</th>
                <th>Pay Method</th>
                <th>Pay Status</th>
                <th>Total</th>
                <th>Paid Amount</th>
                <th>Due Amount</th>
                <th>Campaign Discount</th>
                <th>Special Discount</th>
                <th>Total Items</th>
                <th>Bill Note</th>
            </tr>
        </thead>
    </table>
@endcomponent
