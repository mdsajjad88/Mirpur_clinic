<?php

namespace Modules\Clinic\Http\Controllers\doctor;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Clinic\Entities\{DoctorAppointmentSloot, PatientAppointmentRequ, DoctorBusinessDay, DoctorSL, DoctorProfile, DoctorSlotDeleteLog};
use Illuminate\Support\Facades\{Log, DB};
use Illuminate\Support\Carbon;
use Yajra\DataTables\DataTables;
use App\Business;

class DoctorSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    public function index() {}

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */

    //
    public function storeOld(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'doctor_profile_id' => 'required|integer',
            'slot_duration' => 'required|integer|min:1',
            'slot_capacity' => 'required|integer|min:1',
            'month_id' => 'required|integer|min:1|max:12',
        ]);

        $monthId = $request->input('month_id');
        $currentYear = date('Y'); // Get the current year

        // Prepare the calendar year and month
        $calendar_year = $currentYear;
        $calendar_month = $monthId;
        $sloot = 0;

        // Create a Carbon instance for the month
        $month = Carbon::create($calendar_year, $calendar_month, 1);
        $daysInMonth = $month->daysInMonth;

        // Loop through each day of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($calendar_year, $calendar_month, $day);

            // Check if the day is an off day
            $isOffDay = DoctorBusinessDay::where('doctor_profile_id', $request->input('doctor_profile_id'))
                ->where('business_day_number', $date->format('l'))
                ->where('is_off_day', 1)
                ->exists();

            if ($isOffDay) {
                Log::info('Skipping off day', [
                    'doctor_profile_id' => $request->input('doctor_profile_id'),
                    'calendar_date' => $date->toDateString(),
                ]);
                continue; // Skip this date if it's an off day
            }

            // Fetch the operating hours for the day
            $operatingHours = DoctorBusinessDay::where('doctor_profile_id', $request->input('doctor_profile_id'))
                ->where('business_day_number', $date->format('l'))
                ->first();

            if (!$operatingHours) {
                Log::info('No operating hours defined', [
                    'doctor_profile_id' => $request->input('doctor_profile_id'),
                    'calendar_date' => $date->toDateString(),
                ]);
                continue; // No operating hours defined for this day
            }

            $businessHours = json_decode($operatingHours->business_operating_hours, true);
            $slotData = [];

            foreach ($businessHours as $hours) {
                $startTime = Carbon::createFromFormat('H:i', $hours['start']);
                $endTime = Carbon::createFromFormat('H:i', $hours['end']);

                while ($startTime->lt($endTime)) {
                    $sloot++;
                    $slotEndTime = $startTime->copy()->addMinutes($request->input('slot_duration'));

                    // Check if the slot end time exceeds the business hours
                    if ($slotEndTime->gt($endTime)) {
                        break; // Stop if the slot exceeds business hours
                    }

                    $startHour = (int)$startTime->format('G');

                    // Prepare the slot information
                    $slotData[] = [
                        'start' => $startTime->format('H:i'),
                        'end' => $slotEndTime->format('H:i'),
                        'booked' => 0,
                        'capacity' => $request->input('slot_capacity'),
                        'reserved' => 0,
                        'startHour' => $startTime->format('G'),
                        'startMinute' => $startTime->format('i'),
                        'endHour' => $slotEndTime->format('G'),
                        'endMinute' => $slotEndTime->format('i'),
                        'percentage' => 100,
                    ];
                    $slotsArray["slot_$startHour"][] = $slotData;

                    // Move to the next slot time
                    $startTime->addMinutes($request->input('slot_duration'));
                }
            }

            if (!empty($slotData)) {
                // Check for existing slots to avoid duplicates
                $existingSlot = DoctorAppointmentSloot::where('doctor_profile_id', $request->input('doctor_profile_id'))
                    ->where('calendar_year', $calendar_year)
                    ->where('calendar_month', $calendar_month)
                    ->where('calendar_date', $date->toDateString())
                    ->exists();

                if ($existingSlot) {
                    Log::info('Slots already exist for this date, skipping', [
                        'doctor_profile_id' => $request->input('doctor_profile_id'),
                        'calendar_date' => $date->toDateString(),
                    ]);
                    continue; // Skip saving if slots already exist
                }

                // Create and save the new DoctorAppointmentSloot
                $slot = new DoctorAppointmentSloot();
                $slot->doctor_profile_id = $request->input('doctor_profile_id');
                $slot->slot_capacity = $request->input('slot_capacity');
                $slot->slot_duration = $request->input('slot_duration');
                $slot->calendar_year = $calendar_year;
                $slot->calendar_month = $calendar_month;
                $slot->calendar_date = $date->toDateString();
                $slot->calendar_day = $day;
                $slot->type = 'monthly';
                $slot->doctor_appointment_day_id = $operatingHours->id; // Assigning the ID here
                $slot->slots = json_encode([$date->toDateString() => $slotData]);

                // Try to save the model and log any errors
                try {
                    $slot->save();
                    Log::info('Slot saved successfully', [
                        'doctor_profile_id' => $slot->doctor_profile_id,
                        'calendar_year' => $slot->calendar_year,
                        'calendar_month' => $slot->calendar_month,
                        'calendar_dates' => $slot->calendar_date,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error saving slot: ' . $e->getMessage(), [
                        'doctor_profile_id' => $slot->doctor_profile_id,
                        'calendar_date' => $slot->calendar_date,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'msg' => 'Data processed successfully.',
            'request_data' => $request->all(),
            'calendar_year' => $calendar_year,
            'calendar_month' => $calendar_month,
            'calendar_dates' => $month->toDateString(),
        ]);
    }
    public function store(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'doctor_profile_id' => 'required|integer',
            'slot_duration' => 'required|integer|min:1',
            'slot_capacity' => 'required|integer|min:1',
            'month_id' => 'required|integer|min:1|max:12',
        ]);

        $monthId = $request->input('month_id');
        $currentYear = date('Y'); // Get the current year

        // Prepare the calendar year and month
        $calendar_year = $currentYear;
        $calendar_month = $monthId;

        $doctor = DoctorProfile::find($request->input('doctor_profile_id'));
        $breakTimes = $doctor->duty_time_breaks ? json_decode($doctor->duty_time_breaks, true) : [];

        // Create a Carbon instance for the month
        $month = Carbon::create($calendar_year, $calendar_month, 1);
        $daysInMonth = $month->daysInMonth;

        // Loop through each day of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = Carbon::create($calendar_year, $calendar_month, $day);

            // Check if the day is an off day
            $isOffDay = DoctorBusinessDay::where('doctor_profile_id', $request->input('doctor_profile_id'))
                ->where('business_day_number', $date->format('l'))
                ->where('is_off_day', 1)
                ->exists();

            if ($isOffDay) {
                Log::info('Skipping off day', [
                    'doctor_profile_id' => $request->input('doctor_profile_id'),
                    'calendar_date' => $date->toDateString(),
                ]);
                continue; // Skip this date if it's an off day
            }

            // Fetch the operating hours for the day
            $operatingHours = DoctorBusinessDay::where('doctor_profile_id', $request->input('doctor_profile_id'))
                ->where('business_day_number', $date->format('l'))
                ->first();

            if (!$operatingHours) {
                Log::info('No operating hours defined', [
                    'doctor_profile_id' => $request->input('doctor_profile_id'),
                    'calendar_date' => $date->toDateString(),
                ]);
                continue; // No operating hours defined for this day
            }

            $businessHours = json_decode($operatingHours->business_operating_hours, true);
            $slotData = [];

            foreach ($businessHours as $hours) {
                $startTime = Carbon::createFromFormat('H:i', $hours['start']);
                $endTime = Carbon::createFromFormat('H:i', $hours['end']);

                while ($startTime->lt($endTime)) {
                    $slotEndTime = $startTime->copy()->addMinutes($request->input('slot_duration'));

                    if ($slotEndTime->gt($endTime)) {
                        break;
                    }

                    $isBreakTime = false;

                    foreach ($breakTimes as $break) {
                        $breakStart = Carbon::createFromFormat('H:i', $break['start_time']);
                        $breakEnd = Carbon::createFromFormat('H:i', $break['end_time']);

                        if ($startTime->lt($breakEnd) && $slotEndTime->gt($breakStart)) {
                            // update start time directly to break end and try again
                            $startTime = $breakEnd->copy();
                            $isBreakTime = true;
                            break;
                        }
                    }

                    // If break matched, restart the loop with updated startTime
                    if ($isBreakTime) {
                        continue;
                    }

                    // Valid slot
                    $slotData[] = [
                        'start' => $startTime->format('H:i'),
                        'end' => $slotEndTime->format('H:i'),
                        'booked' => 0,
                        'capacity' => $request->input('slot_capacity'),
                        'reserved' => 0,
                        'startHour' => $startTime->format('G'),
                        'startMinute' => $startTime->format('i'),
                        'endHour' => $slotEndTime->format('G'),
                        'endMinute' => $slotEndTime->format('i'),
                        'percentage' => 100,
                    ];

                    // Move to next time
                    $startTime->addMinutes($request->input('slot_duration'));
                }
            }

            if (!empty($slotData)) {
                // Check for existing slots to avoid duplicates
                $existingSlot = DoctorAppointmentSloot::where('doctor_profile_id', $request->input('doctor_profile_id'))
                    ->where('calendar_year', $calendar_year)
                    ->where('calendar_month', $calendar_month)
                    ->where('calendar_date', $date->toDateString())
                    ->exists();

                if ($existingSlot) {
                    Log::info('Slots already exist for this date, skipping', [
                        'doctor_profile_id' => $request->input('doctor_profile_id'),
                        'calendar_date' => $date->toDateString(),
                    ]);
                    continue;
                }

                // Create and save the new DoctorAppointmentSloot
                $slot = new DoctorAppointmentSloot();
                $slot->doctor_profile_id = $request->input('doctor_profile_id');
                $slot->slot_capacity = $request->input('slot_capacity');
                $slot->slot_duration = $request->input('slot_duration');
                $slot->calendar_year = $calendar_year;
                $slot->calendar_month = $calendar_month;
                $slot->calendar_date = $date->toDateString();
                $slot->calendar_day = $day;
                $slot->type = 'monthly';
                $slot->doctor_appointment_day_id = $operatingHours->id;
                $slot->slots = json_encode([$date->toDateString() => $slotData]);
                $slot->created_by = auth()->user()->id;
                try {
                    $slot->save();
                } catch (\Exception $e) {
                    Log::error('Error saving slot: ' . $e->getMessage(), [
                        'doctor_profile_id' => $slot->doctor_profile_id,
                        'calendar_date' => $slot->calendar_date,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'msg' => 'Data processed successfully.',
            'request_data' => $request->all(),
            'calendar_year' => $calendar_year,
            'calendar_month' => $calendar_month,
            'calendar_dates' => $month->toDateString(),
        ]);
    }






    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        $slot = DoctorAppointmentSloot::find($id);
        return view('clinic::provider.tab.daily_sloot_view', compact('slot'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('clinic::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (request()->ajax()) {
            try {
                DB::beginTransaction();

                // Find the doctor profile
                $slot = DoctorAppointmentSloot::find($id);

                if (!$slot) {
                    DB::rollBack();

                    return response()->json([
                        'success' => false,
                        'msg' => 'Data not found',
                    ]);
                }
                $deleteLog = new DoctorSlotDeleteLog();
                $deleteLog->doctor_profile_id = $slot->doctor_profile_id;
                $deleteLog->appointment_date = $slot->calendar_date;
                $deleteLog->type = $slot->type;
                $deleteLog->slot_capacity = $slot->slot_capacity;
                $deleteLog->created_by = $slot->created_by ?? null;
                $deleteLog->deleted_by = auth()->user()->id;
                $deleteLog->save();
                $slot->delete();

                DB::commit();

                return response()->json([
                    'success' => true,
                    'msg' => 'Slot Deleted Successfully',
                ]);
            } catch (\Exception $e) {
                // Log the exception details
                DB::rollBack();

                Log::emergency('Error deleting doctor: ' . $e->getMessage(), [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'id' => $id,
                ]);

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return response()->json($output);
        }
    }
    public function dailySlotGenerate($id)
    {
        $doctor = DoctorProfile::find($id);
        $breakTimes = $doctor->duty_time_breaks ? json_decode($doctor->duty_time_breaks, true) : [];
        $existingSlotDates = DoctorAppointmentSloot::where('doctor_profile_id', $id)
            ->whereDate('calendar_date', '>=', \Carbon\Carbon::today())
            ->pluck('calendar_date')
            ->map(fn($date) => \Carbon\Carbon::parse($date)->format('Y-m-d'))
            ->toArray();

        $allAvailableDates = collect(range(0, 30))
            ->map(fn($i) => \Carbon\Carbon::today()->addDays($i)->format('Y-m-d'))
            ->toArray();

        $availableDatesWithoutSlot = array_diff($allAvailableDates, $existingSlotDates);

        return view('clinic::provider.tab.daily_slot_generate', compact('doctor', 'breakTimes', 'availableDatesWithoutSlot'));
    }
    public function storeDailySloot(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'doctor_profile_id' => 'required|integer',
            'slot_duration' => 'required|integer|min:1',
            'slot_capacity' => 'required|integer|min:1',
            'calendar_date' => 'required|date',
            'start_time' => 'required|array',
            'close_time' => 'required|array',
        ]);

        // Ensure both start and close times arrays have the same count
        if (count($request->input('start_time')) !== count($request->input('close_time'))) {
            return response()->json(['success' => false, 'msg' => 'Start and close times must match.'], 400);
        }

        // Create a Carbon instance from the calendar date
        $calendarDate = Carbon::parse($request->input('calendar_date'));
        $day = $calendarDate->day;
        $month = $calendarDate->month;
        $year = $calendarDate->year;

        // Prepare to track slots
        $slotData = [];
        $sloot = 0;

        $startTimes = $request->input('start_time');
        $closeTimes = $request->input('close_time');
        // Fetch business settings for break times
        $doctor = DoctorProfile::find($request->input('doctor_profile_id'));
        $breakTimes = $doctor->duty_time_breaks ? json_decode($doctor->duty_time_breaks, true) : [];
        // Prepare operating hours
        $operatingHours = [];
        foreach ($startTimes as $index => $startTime) {
            if (isset($closeTimes[$index])) {
                $operatingHours[] = [
                    'start' => $startTime,
                    'end' => $closeTimes[$index],
                ];
            }
        }

        // Loop through each defined business hour
        foreach ($operatingHours as $hours) {
            $startTime = Carbon::createFromFormat('H:i', $hours['start']);
            $endTime = Carbon::createFromFormat('H:i', $hours['end']);

            // Check if Carbon instances were created correctly
            if (!$startTime || !$endTime) {
                return response()->json(['success' => false, 'msg' => 'Invalid time format.'], 400);
            }

            while ($startTime->lt($endTime)) {
                $slotEndTime = $startTime->copy()->addMinutes($request->input('slot_duration'));

                // Check if the slot end time exceeds the business hours
                if ($slotEndTime->gt($endTime)) {
                    break; // Stop if the slot exceeds business hours
                }

                $isBreakTime = false;

                foreach ($breakTimes as $break) {
                    $breakStart = Carbon::createFromFormat('H:i', $break['start_time']);
                    $breakEnd = Carbon::createFromFormat('H:i', $break['end_time']);

                    if ($startTime->lt($breakEnd) && $slotEndTime->gt($breakStart)) {
                        // update start time directly to break end and try again
                        $startTime = $breakEnd->copy();
                        $isBreakTime = true;
                        break;
                    }
                }

                // If break matched, restart the loop with updated startTime
                if ($isBreakTime) {
                    continue;
                }
                // Prepare the slot information
                $slotData[] = [
                    'start' => $startTime->format('H:i'),
                    'end' => $slotEndTime->format('H:i'),
                    'booked' => 0,
                    'capacity' => $request->input('slot_capacity'),
                    'reserved' => 0,
                    'startHour' => (int)$startTime->format('G'),
                    'startMinute' => (int)$startTime->format('i'),
                    'endHour' => (int)$slotEndTime->format('G'),
                    'endMinute' => (int)$slotEndTime->format('i'),
                    'percentage' => 100,
                ];

                // Move to the next slot time
                $startTime->addMinutes($request->input('slot_duration'));
                $sloot++;
            }
        }

        // Check for existing slots to avoid duplicates
        $existingSlot = DoctorAppointmentSloot::where('doctor_profile_id', $request->input('doctor_profile_id'))
            ->where('calendar_year', $year)
            ->where('calendar_month', $month)
            ->where('calendar_date', $calendarDate->toDateString())
            ->exists();

        if ($existingSlot) {
            $output = ['success' => false, 'msg' => 'Slots already exist for this date.'];
            return $output;
        }

        // Create and save the new DoctorAppointmentSloot
        $slot = new DoctorAppointmentSloot();
        $slot->doctor_profile_id = $request->input('doctor_profile_id');
        $slot->slot_capacity = $request->input('slot_capacity');
        $slot->slot_duration = $request->input('slot_duration');
        $slot->calendar_year = $year;
        $slot->calendar_month = $month;
        $slot->calendar_date = $calendarDate->toDateString();
        $slot->calendar_day = $day;
        $slot->type = 'daily';
        $slot->slots = json_encode([$calendarDate->toDateString() => $slotData]);
        $slot->created_by = auth()->user()->id;
        // Try to save the model and log any errors
        try {
            $slot->save();
            Log::info('Slot saved successfully', [
                'doctor_profile_id' => $slot->doctor_profile_id,
                'calendar_year' => $slot->calendar_year,
                'calendar_month' => $slot->calendar_month,
                'calendar_date' => $slot->calendar_date,
            ]);
            $output = ['success' => true, 'msg' => 'Slot saved successfully'];
        } catch (\Exception $e) {
            Log::error('Error saving slot: ' . $e->getMessage(), [
                'doctor_profile_id' => $slot->doctor_profile_id,
                'calendar_date' => $slot->calendar_date,
            ]);
            $output = ['success' => false, 'msg' => 'Error saving slot.'];
        }
        return $output;
    }

    public function getDailySlootData($id)
    {
        $doctorSlots = DoctorAppointmentSloot::where('doctor_profile_id', $id)
            ->where('type', 'daily')
            ->get();

        return DataTables::of($doctorSlots)
            ->addColumn('total_reserved', function ($slot) {
                $total = 0;
                $slotsData = json_decode($slot->slots, true);
                foreach ($slotsData as $date => $slotsArray) {
                    foreach ($slotsArray as $item) {
                        $total += $item['reserved'] ?? 0;
                    }
                }
                return $total;
            })
            ->addColumn('total_capacity', function ($slot) {
                $total = 0;
                $slotsData = json_decode($slot->slots, true);
                foreach ($slotsData as $date => $slotsArray) {
                    foreach ($slotsArray as $item) {
                        $total += $item['capacity'] ?? 0;
                    }
                }
                return $total;
            })
            ->addColumn('total_available', function ($slot) {
                $capacity = 0;
                $reserved = 0;
                $slotsData = json_decode($slot->slots, true);
                foreach ($slotsData as $date => $slotsArray) {
                    foreach ($slotsArray as $item) {
                        $capacity += $item['capacity'] ?? 0;
                        $reserved += $item['reserved'] ?? 0;
                    }
                }
                return $capacity - $reserved;
            })
            ->addColumn('action', function ($slot) {
                $isAccess = auth()->user()->can('delete.daily.slots');
                $appointment = PatientAppointmentRequ::where('doctor_appointment_slot_id', $slot->id)->count();

                $viewBtn = '<a href="' . action([\Modules\Clinic\Http\Controllers\doctor\DoctorSlotController::class, 'show'], [$slot->id]) . '" class="btn btn-success daily_sloot_view_btn">View</a>';

                $deleteBtn = '';
                if ($isAccess && $appointment === 0) {
                    $deleteBtn = '<a href="' . action([\Modules\Clinic\Http\Controllers\doctor\DoctorSlotController::class, 'destroy'], [$slot->id]) . '" class="btn btn-danger daily_sloot_delete">Delete</a>';
                }

                return $viewBtn . ' ' . $deleteBtn;
            })
            ->make(true);
    }
    public function monthlySlotGenerate($id, $month)
    {
        $doctor = DoctorProfile::find($id);
        $breakTimes = $doctor->duty_time_breaks ? json_decode($doctor->duty_time_breaks, true) : [];
        return view('clinic::provider.tab.generate_monthly_slot', compact('doctor', 'month', 'breakTimes'));
    }
    public function getMonthlySlootData($id)
    {
        $months = [
            ['id' => 1, 'name' => 'January'],
            ['id' => 2, 'name' => 'February'],
            ['id' => 3, 'name' => 'March'],
            ['id' => 4, 'name' => 'April'],
            ['id' => 5, 'name' => 'May'],
            ['id' => 6, 'name' => 'June'],
            ['id' => 7, 'name' => 'July'],
            ['id' => 8, 'name' => 'August'],
            ['id' => 9, 'name' => 'September'],
            ['id' => 10, 'name' => 'October'],
            ['id' => 11, 'name' => 'November'],
            ['id' => 12, 'name' => 'December'],
        ];

        $sloot = DoctorAppointmentSloot::where('doctor_profile_id', $id)
            ->where('type', 'monthly')
            ->get()
            ->groupBy('calendar_month');
        $data = [];

        foreach ($months as $month) {
            $monthId = $month['id'];
            $slotsExist = isset($sloot[$monthId]) && $sloot[$monthId]->isNotEmpty();
            $totalSlots = $slotsExist ? count($sloot[$monthId]) : 0;

            $totalBooked = 0;
            $totalReserved = 0;
            $totalCapacity = 0;

            if ($slotsExist) {
                foreach ($sloot[$monthId] as $slot) {
                    $slotsData = json_decode($slot->slots, true);
                    if (!is_array($slotsData)) continue;

                    foreach ($slotsData as $date => $daySlots) {
                        foreach ($daySlots as $item) {
                            $totalBooked += $item['booked'] ?? 0;
                            $totalReserved += $item['reserved'] ?? 0;
                            $totalCapacity += $item['capacity'] ?? 0;
                        }
                    }
                }
            }

            $data[] = [
                'month' => $month['name'],
                'total_slots' => $totalSlots,
                'booked' => $totalBooked,
                'reserved' => $totalReserved,
                'capacity' => $totalCapacity,
                'available' => $totalCapacity - $totalReserved,
                'action' => $slotsExist
                    ? '<a href="' . action([\Modules\Clinic\Http\Controllers\doctor\DoctorSlotController::class, 'ViewSlot'], [$id, $monthId]) . '" class="btn btn-success view_monthly_slot btn-sm">View</a>'
                    : '<a href="' . action([\Modules\Clinic\Http\Controllers\doctor\DoctorSlotController::class, 'monthlySlotGenerate'], [$id, $monthId]) . '" class="btn make_app monthly_sloot_generate_btn btn-sm">Generate</a>',
            ];
        }

        return response()->json(['data' => $data]);
    }


    public function ViewSlot($id, $month)
    {
        $slootData = DoctorAppointmentSloot::where('doctor_profile_id', $id)
            ->where('calendar_month', $month)
            ->where('type', 'monthly')
            ->get();
        $doctor = DoctorProfile::find($id);
        $breakTimes = $doctor->duty_time_breaks ? json_decode($doctor->duty_time_breaks, true) : [];
        return view('clinic::provider.tab.view_monthly_sloot', compact('slootData', 'breakTimes'));
    }
    public function deleteMonthlySloot($month, $id)
    {
        try {
            $deletedCount = DoctorAppointmentSloot::where('doctor_profile_id', $id)
                ->where('calendar_month', $month)
                ->where('type', 'monthly')
                ->delete();

            // Check if any rows were deleted
            if ($deletedCount > 0) {
                $output = [
                    'success' => true,
                    'msg' => 'Monthly Data Deleted Successfully',
                ];
            } else {
                Log::warning("No data found to delete for doctor ID: $id and month: $month");
                $output = [
                    'success' => false,
                    'msg' => 'No Data Found to Delete',
                ];
            }
        } catch (\Exception $e) {
            // Handle the exception
            Log::error("Error deleting monthly slots for doctor ID: $id and month: $month. Error: " . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => 'An error occurred: ' . $e->getMessage(),
            ];
        }

        return $output;
    }
    public function slotInfo($id, $date, $serial = null)
    {
        try {
            // Get doctor slot for the given date
            $doctorSlot = DoctorAppointmentSloot::where('doctor_profile_id', $id)
                ->where('calendar_date', $date)
                ->first();

            // Get all serial numbers for this doctor and date
            $serials = DoctorSL::where('appointment_date', $date)
                ->where('doctor_profile_id', $id)
                ->get();
            $serialNo = null;
            if ($serial) {
                $serialRecord = $serials->where('id', $serial)->first();
                if ($serialRecord) {
                    $serialNo = $serialRecord->sl_without_prefix;
                }
            }

            if ($doctorSlot) {
                return response()->json([
                    'success' => true,
                    'data' => $doctorSlot,
                    'serial' => $serials,
                    'serialNo' => $serialNo
                ]);
            }

            return response()->json([
                'success' => false,
                'msg' => 'No slot found for this date.'
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving slot info: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'An error occurred while retrieving the slot information.'
            ]);
        }
    }

    public function individualSlotDelete($id)
    {
        try {
            DB::beginTransaction();

            $slot = DoctorAppointmentSloot::find($id);
            $slot_date = \Carbon\Carbon::parse($slot->calendar_date)->startOfDay();
            $today = \Carbon\Carbon::today();
            $appointment = PatientAppointmentRequ::where('doctor_appointment_slot_id', $id)->where(function ($query) {
                $query->where('cancel_status', 0)->orWhereNull('cancel_status');
            })->count();
            if ($appointment > 0 && $slot_date->greaterThan($today)) {
                DB::rollBack();
                return response()->json(['success' => false, 'msg' => 'This Days Appointment Already Booked']);
            }
            if (!$slot) {
                DB::rollBack();
                return response()->json(['success' => false, 'msg' => 'Data not found']);
            }
            $deleteLog = new DoctorSlotDeleteLog();
            $deleteLog->doctor_profile_id = $slot->doctor_profile_id;
            $deleteLog->appointment_date = $slot->calendar_date;
            $deleteLog->type = $slot->type;
            $deleteLog->slot_capacity = $slot->slot_capacity;
            $deleteLog->created_by = $slot->created_by ?? null;
            $deleteLog->deleted_by = auth()->user()->id;
            $deleteLog->save();

            $slot->delete();
            DB::commit();
            return response()->json(['success' => true, 'msg' => 'Slot Deleted Successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting individual slot for doctor ID: ' . $id, [
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return response()->json([
                'success' => false,
                'msg' => 'An error occurred while deleting the slot.'
            ]);
        }
    }
    public function updateBreakTimeSetting(Request $request, $id)
    {
        try {
            $breakTimes = $request->input('breaks');
            $doctor = DoctorProfile::findOrFail($id);
            $doctor->duty_time_breaks = json_encode($breakTimes);
            $doctor->save();

            return response()->json([
                'success' => true,
                'msg' => 'Break Time Updated Successfully'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error updating break times for doctor ID: ' . $id, [
                'error_message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'input_breaks' => $request->input('breaks')
            ]);

            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ], 500);
        }
    }

    public function getDateWiseDoctor($date)
    {
        $type = request()->get('type') ?? 'doctor';
        // Get all doctor IDs who have slots on $date
        $doctorIdsWithSlots = DoctorAppointmentSloot::where('calendar_date', $date)
            ->pluck('doctor_profile_id')
            ->unique()
            ->toArray();

        // Get doctors with those IDs and is_doctor = 1
        $doctors = DoctorProfile::whereIn('id', $doctorIdsWithSlots)
            ->where('is_doctor', 1)
            ->where('type', $type)
            ->get()
            ->mapWithKeys(function ($doctor) use ($date) {
                $appointment = PatientAppointmentRequ::where('request_date', $date)
                    ->where('doctor_profile_id', $doctor->id)
                    ->where(function ($q) {
                        $q->whereNull('cancel_status')
                            ->orWhere('cancel_status', 0);
                    })
                    ->count();

                $name = $doctor->first_name . ' ' . ($doctor->last_name ?? '');
                $type = '';
                if ($doctor->type === 'therapist') {
                    $type = ' (Therapist)';
                }
                return [$doctor->id => $name . ' (' . $appointment . ' Booked)'. $type];
            });

        return response()->json([
            'success' => true,
            'data' => $doctors->toArray(),
        ]);
    }
}
