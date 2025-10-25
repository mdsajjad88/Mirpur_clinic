<?php

namespace Modules\Clinic\Http\Controllers\doctor;

use App\Utils\BusinessUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Clinic\Entities\DoctorAdvice;
use Yajra\DataTables\DataTables;

class DoctorAdviceController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $businessUtil;
    public function __construct(BusinessUtil $businessUtil)
    {
        $this->businessUtil = $businessUtil;
    }
    public function index()
    {
        if (! auth()->user()->can('doctor.advice.view') && ! auth()->user()->can('doctor.advice.create')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $is_admin = $this->businessUtil->is_admin(auth()->user());
            $query = DoctorAdvice::orderBy('id', 'desc');
            // if (!$is_admin) {
            //     $query->where('created_by', auth()->user()->id);
            // }
            $advices = $query->get();

            if ($advices->isEmpty()) {
                return Datatables::of(collect())
                    ->make(false);
            }

            return Datatables::of($advices)
                ->addIndexColumn() // Adds DT_RowIndex
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (auth()->user()->can('doctor.advice.update')) {
                        $edit = action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'edit'], [$row->id]);
                        $html .= '<a href="#" data-href="' . $edit . '" class="btn btn-xs btn-modal btn-primary edit_doctor_advice">
                            <i class="fas fa-edit" aria-hidden="true"></i> ' . __('Edit') . '
                         </a>';
                    }
                    if (auth()->user()->can('doctor.advice.delete')) {
                        $delete = action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'destroy'], [$row->id]);
                    $html .= '<a href="' . $delete . '" class="btn btn-xs btn-danger delete_doctor_advice" style="margin-left: 5px;">
                                     <i class="fas fa-trash" aria-hidden="true"></i> ' . __('Delete') . '
                                   </a>';
                    }

                    
                    return $html;
                })
                ->addColumn('status', function ($row) {
                    // Check condition for status and return button HTML
                    if ($row->status == 1) {
                        return '<button class="btn btn-success btn-xs">Active</button>';
                    } else {
                        return '<button class="btn btn-danger btn-xs">Inactive</button>';
                    }
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        // Default return for non-AJAX requests
        return view('clinic::doctor_dashboard.doctor_advice.index');
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (! auth()->user()->can('doctor.advice.create')) {
            abort(403, 'Unauthorized action.');
        }
        $type = request()->get('type', 'doctor'); 
        return view('clinic::doctor_dashboard.doctor_advice.create', compact('type'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('doctor.advice.create')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $input = $request->only(['value', 'status', 'type']);
            $input['created_by'] = $request->session()->get('user.id');
            $advice = DoctorAdvice::create($input);
            $typeMsg = '';
            if($advice->type == 'doctor'){
                $typeMsg = 'Advice Added Successfully';
            }else if($advice->type == 'treatment_plan'){
                $typeMsg = 'Treatment Plan Added Successfully';
            }elseif($advice->type == 'home_advice'){
                $typeMsg = 'Home Advice Added Successfully';
            
            }elseif($advice->type == 'on_examination'){
                $typeMsg = 'On Examination Added Successfully';
            }
            $output = [
                'success' => true,
                'data' => $advice,
                'msg' => $typeMsg,
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
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
        if (! auth()->user()->can('doctor.advice.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $advice = DoctorAdvice::find($id);
            return view('clinic::doctor_dashboard.doctor_advice.edit')
                ->with(compact('advice'));
        }
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        if (! auth()->user()->can('doctor.advice.update')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $input = $request->only(['value', 'status']);
                $business_id = $request->session()->get('user.business_id');

                $advice = DoctorAdvice::findOrFail($id);
                $advice->value = $input['value'];
                $advice->status = $input['status'];
                $advice->modified_by = $request->session()->get('user.id');
                $advice->save();
                $output = [
                    'success' => true,
                    'msg' => 'Advice successfully updated',
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (! auth()->user()->can('doctor.advice.delete')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            try {
                $advice = DoctorAdvice::findOrFail($id);
                $advice->delete();
                $output = [
                    'success' => true,
                    'msg' => 'Advice Deleted Successfully',
                ];
            } catch (\Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }
    public function getAdvices()
    {
        if (request()->ajax()) {
            $term = request()->term;
            $searchData = $term;
            if (empty($term)) {
                return json_encode([]);
            }
            $type = request()->type;
            if(empty($type)){
                $type = 'doctor';
            }

            $advices = DoctorAdvice::where('doctor_advise.value', 'like', '%' . $term . '%')
                ->active()
                ->where('doctor_advise.type', $type)
                ->select(
                    'doctor_advise.id as advice_id',
                    'doctor_advise.value'
                )
                ->groupBy('doctor_advise.id')
                ->get();
            $button_html = '<a href="' . action([\Modules\Clinic\Http\Controllers\doctor\DoctorAdviceController::class, 'create']) . '" id="add_new_advice" class="btn">Add <b>' . $term . '</b> as a new advice <i class="fas fa-plus add_new_dosage_btn"></i></a>';
            if ($advices->isEmpty()) {
                return response()->json([
                    'results' => [],
                    'message' => 'No advices found for your search term.',
                    'button_html' => $button_html,
                    'term' => $searchData

                ]);
            }

            return response()->json([
                'results' => $advices->map(function ($advice) {
                    return [
                        'id' => $advice->advice_id,
                        'text' => $advice->value,
                    ];
                }),
                'message' => '',
                'button_html' => ''
            ]);
        }
    }
}
