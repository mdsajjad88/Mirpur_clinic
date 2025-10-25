<div class="row mt-5">
    <div class="col">
        <div class="row">
            <div class="col-md-10"></div>
            <div class="col-md-2">
                <a href="{{ action([\Modules\Clinic\Http\Controllers\doctor\DoctorSlotController::class, 'dailySlotGenerate'], [$doctor->id]) }}" class="btn btn-primary daily_sloot_generate">Generate Slot</a>
            </div>
        </div>
        <table class="table table-striped text-center" id="daily_slot_table" style="width:100%">
            <thead>
                <tr>
                    <th>Day</th>
                    <th>Total Slot </th>
                    <th>Booked</th>
                    <th>Available</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                
            </tbody>
        </table>
    </div>
</div>
