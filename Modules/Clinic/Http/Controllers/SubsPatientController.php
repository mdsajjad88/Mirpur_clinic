<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Modules\Clinic\Entities\PatientSessionInfo;
use Modules\Clinic\Utils\PatientUtil;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\Log;

use Modules\Clinic\Entities\PatientSessionDetails;
use Modules\Clinic\Entities\SessionInfo;

class SubsPatientController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $patientUtil;
    protected $commonUtil;

    public function __construct(PatientUtil $patientUtil, Util $commonUtil)
    {
        $this->patientUtil = $patientUtil;
        $this->commonUtil = $commonUtil;
    }
    public function index()
    {
        if (request()->ajax()) {
            $data = $this->patientUtil->getSubcribePatientInfo();
            if (request()->filled('subscription_id')) {
                $data->where('session_information.id', request('subscription_id'));
            }

            if (request()->has('running_session') && request('running_session') != '') {
                $running = request('running_session');

                if ($running == 1) {
                    // Only closed sessions
                    $data->where('patient_session_info.is_closed', 1);
                } else {
                    // 0 or NULL => open sessions
                    $data->where(function ($q) {
                        $q->where('patient_session_info.is_closed', 0)
                            ->orWhereNull('patient_session_info.is_closed');
                    });
                }
            }



            return Datatables::of($data)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">' . __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\SubsPatientController::class, 'show'], [$row->patient_session_info_id]) . '" class="show_subs_info"><i class="fas fa-eye"></i> Details</a></li>';

                    return $html;
                })
                ->addColumn('session_name', function ($row) {
                    return $row->session_name;
                })
                ->addColumn('session_amount', function ($row) {
                    return $row->session_amount;
                })
                ->addColumn('patient_name', function ($row) {
                    return $row->first_name ?? '' . ' ' . $row->last_name ?? '';
                })
                ->addColumn('mobile', function ($row) {
                    $phoneId = 'phone_' . $row->id;
                    $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';

                    if (auth()->user()->can('patient.phone_number')) {
                        return $phoneIcon .
                            ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->mobile . '</span>';
                    } else {
                        return $phoneIcon .
                            ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->mobile . '</span>';
                    }
                })
                ->editColumn('contact_id', function ($row) {
                    return $row->contact_id ?? '';
                })
                ->addColumn('start_date', function ($row) {
                    return $row->start_date;
                })
                ->addColumn('end_date', function ($row) {
                    $button = '';
                    // if (auth()->user()->hasRole('Admin#' . session('business.id'))) {
                    $button = '<button type="button" class="btn btn-info btn-xs btn-modal"
                            data-container=".edit_end_date_modal" 
                            data-href="' . action([\Modules\Clinic\Http\Controllers\SubsPatientController::class, 'endDateEdit'], [$row->patient_session_info_id]) . '"> 
                            <i class="fas fa-edit"></i></button>';
                    // }
                    return $row->end_date . '&nbsp;' . $button;
                })
                ->addColumn('visited_count', function ($row) {
                    return $row->visited_count;
                })
                ->addColumn('remaining_visit', function ($row) {
                    return $row->remaining_visit;
                })
                ->addColumn('total_visit', function ($row) {
                    return $row->total_visit;
                })
                ->addColumn('is_closed', function ($row) {
                    return $row->is_closed ? 'Yes' : 'No';
                })
                ->rawColumns(['is_closed', 'action', 'end_date', 'mobile', 'contact_id'])
                ->make(true);
        }
        $subscriptions = SessionInfo::where('status', 1)->pluck('session_name', 'id')->toArray();
        return view('clinic::patient.subsPatient.index', compact('subscriptions'));
    }


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
        $patientSessionDetails = PatientSessionDetails::with('doctorProfile')->where('patient_session_id', $id)->get();
        return view('clinic::patient.subsPatient.show', compact('patientSessionDetails'));
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
    public function endDateEdit($id)
    {
        $session = PatientSessionInfo::find($id);
        $is_admin = auth()->user()->hasRole('Admin#' . session('business.id'));
        return view('clinic::patient.subsPatient.editEndDate', compact('session', 'is_admin'));
    }
    public function endDateUpdate(Request $request)
    {
        try {
            $id = $request->input('session_id');
            $session = PatientSessionInfo::find($id);
            if (!$session) {
                $output = ['success' => false, 'msg' => 'Session not found'];
            }
            $session->end_date = $request->input('end_date') ? $request->input('end_date') : $session->end_date;
            $session->visited_count = $request->input('visited_count') ? $request->input('visited_count') : $session->visited_count;
            $session->remaining_visit = $request->input('remaining_visit') ? $request->input('remaining_visit') : $session->remaining_visit;
            $session->is_closed = $request->input('is_closed');
            $session->save();
            $this->commonUtil->activityLog($session, 'End Date Updated');
            $output = ['success' => true, 'msg' => 'End date updated successfully'];
        } catch (\Exception $e) {
            $output = ['success' => false, 'msg' => $e->getMessage()];
        }
        return $output;
    }
}
