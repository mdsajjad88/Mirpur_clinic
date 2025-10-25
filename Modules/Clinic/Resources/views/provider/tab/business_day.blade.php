<div class="openHoursContainer">
    <div class="row">
        <div class="col-md-6">
            <table style="width: 100%" id="business_days_table" class="table table-bordered">
                <thead>
                    <tr>
                        <th>Working Day Content</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
        <div class="col-md-6">

            {!! Form::open([
                'url' => action(
                    [\Modules\Clinic\Http\Controllers\doctor\DoctorSlotController::class, 'updateBreakTimeSetting'],
                    [$doctor->id],
                ),
                'method' => 'PUT',
                'id' => 'business_day_break_form',
            ]) !!}
            <table class="table table-bordered table-sm table-striped" id="doctors_duty_time_break_table" style="width: 100% ; margin-top: 5px">
                <thead>
                    <tr>
                        <th>Break Type</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>
                            <button type="button" class="btn btn-success btn-sm" id="add_new_break">
                                <i class="fa fa-plus"></i>
                            </button>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (json_decode($doctor->duty_time_breaks, true) ?? [] as $index => $break)
                        <tr>
                            <td><input type="text" name="breaks[{{ $index }}][break_type]" class="form-control" value="{{ $break['break_type'] }}"></td>
                            <td><input type="time" name="breaks[{{ $index }}][start_time]" class="form-control" value="{{ $break['start_time'] }}"></td>
                            <td><input type="time" name="breaks[{{ $index }}][end_time]" class="form-control" value="{{ $break['end_time'] }}"></td>
                            <td><button type="button" class="btn btn-danger remove-break-type-row"><i
                                        class="fa fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            {!! Form::submit('Update', ['class' => 'btn btn-primary']) !!}
            {!! Form::close() !!}

        </div>
    </div>

</div>
