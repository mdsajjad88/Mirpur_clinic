<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Utils\PrescriptionUtil;
use Yajra\DataTables\DataTables;

class AppReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    protected $prescriptionUtil;
    public function __construct(

        PrescriptionUtil $prescriptionUtil,
    ) {

        $this->prescriptionUtil = $prescriptionUtil;
    }
    public function index()
    {
        if(!auth()->user()->can('appointment_report.show')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $query = $this->prescriptionUtil->doctorPrescriptionReport();
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $query->whereDate('p.visit_date', '>=', $start)
                    ->whereDate('p.visit_date', '<=', $end);
            }
            $query = $query->get();
            return DataTables::of($query)
                ->editColumn('avg_waiting_time', function ($row) {
                    return round($row->avg_waiting_time, 2);
                })
                ->editColumn('doctor_name', function ($row) {
                    return ($row->doctor_first_name ?? '') . ' ' . ($row->doctor_last_name ?? '');
                })
                ->editColumn('avg_patient_waiting_time', function ($row) {
                    return round($row->avg_patient_waiting_time, 2);
                })
                ->rawColumns(['avg_waiting_time', 'doctor_name' ,'avg_patient_waiting_time'])                
                ->make(true);
        }
        return view('clinic::appointment.appointment_report');
    }

    public function getDoctorAppointmentData(Request $request) {}

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('clinic::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('clinic::show');
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
        //
    }
}
