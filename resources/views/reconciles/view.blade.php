<style>
    .dt-buttons {
        display: flex;               /* Use flexbox */
        justify-content: center;     /* Center the buttons */
        margin-bottom: 15px;        /* Optional: space below */
    }

</style>
<div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">@if($reconcileDetails->isNotEmpty())
                {{ $reconcileDetails->first()->reconcile->name }} Reconcile Details
            @else
                No Reconcile Details Available
            @endif</h4>
        </div>

        <div class="modal-body">
            <div class="row" style="padding: 15px">
                <table class="table table-bordered table-striped ajax_view" id="reconcile_details_table_modal" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Physical Quantity</th>
                            <th>Software Quantity</th>
                            <th>Difference</th>
                            <th>Difference Percentage</th>
                            <th>Created by</th>
                            <th>Updated by</th>
                        </tr>
                    </thead>
                    <tbody>                            
                        @foreach ($reconcileDetails as $details)
                        <tr>
                        <td>{{$details->name??""}}</td>
                        <td>{{$details->sku??""}}</td>
                        <td>{{$details->physical_qty??""}}</td>
                        <td>{{$details->software_qty??""}}</td>
                        <td>{{$details->difference??""}}</td>
                        <td>{{$details->difference_percentage??""}}%</td>
                        <td>{{$details->creator->username??""}} </td>
                        <td>{{$details->updater->username??""}} </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {
        $('#reconcile_details_table_modal').DataTable({
            paging: true,
            searching: false,
            info: true,
            lengthChange: false,
            pageLength: 25,
            dom: 'Bfrtip',
        });

       
    });
</script>