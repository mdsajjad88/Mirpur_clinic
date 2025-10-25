@extends('clinic::layouts.app2')
@section('title', __('Prima Subscription Details'))
@section('content')
<div class="container-fluid">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Prima Subscription Details'])
    <div class="row">
        <div class="col">
            <table class="table table-bordered table-striped ajax_view" id="subscription_details_table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Mobile</th>
                        <th>Subscript Date</th>
                        <th>Expiry Date</th>
                        <th>Used Consultancy</th>
                        <th>Remaining</th>
                        <th>Transaction</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
    @endcomponent
    <div class="modal fade subs_payment_details" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
    </div>
</div>

@endsection
@section('javascript')
<script type="text/javascript">
    $(document).ready(function() {
        var subscription_details_table = $('#subscription_details_table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ url('subs-payment') }}",
            },
            columns: [
                { data: 'name', name: 'name' },
                { data: 'mobile', name: 'mobile' },
                { data: 'subscript_date', name: 'subscript_date' },
                { data: 'expiry_date', name: 'expiry_date' },
                { data: 'used_consultancy', name: 'used_consultancy' },
                { data: 'remaining', name: 'remaining' },
                { data: 'transaction', name: 'transaction' },
            ]
        });
    })
</script>
@endsection