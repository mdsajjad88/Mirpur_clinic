<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Validator;
use App\Reconcile;
use App\ReconcileDetails;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use App\Utils\Util;

class ReconcileDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $commonUtil;
    public function __construct(
       
        Util $commonUtil,
    ) {
       
        $this->commonUtil = $commonUtil;
    }
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ReconcileDetails::with('creator', 'updater', 'reconcile')->latest()->get();
            $reconciles = Datatables::of($query)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                            <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                                data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can('customer.update')) {
                        $html .= '<li><a href="' . action([\App\Http\Controllers\ReconcileDetailsController::class, 'edit'], [$row->id]) . '" class="edit_reconcile_details_button">
                                <i class="glyphicon glyphicon-edit"></i>' . __('messages.edit') . '</a></li>';
                    }

                    if (!$row->is_default && auth()->user()->can('customer.delete')) {
                        $html .= '<li><a href="' . action([\App\Http\Controllers\ReconcileDetailsController::class, 'destroy'], [$row->id]) . '" class="delete_reconcile_details_button">
                                <i class="glyphicon glyphicon-trash"></i>' . __('messages.delete') . '</a></li>';
                    }
                    $html .= '<li class="divider"></li>';
                    $html .= '</ul></div>';

                    return $html;
                })
                ->editColumn('creator', function ($row) {
                    return $row->creator->username ?? "";
                })
                ->editColumn('updater', function ($row) {
                    return $row->updater->username ?? "";
                })
                ->editColumn('reconcile', function ($row) {
                    return $row->reconcile->name ?? "";
                })
                ->rawColumns(['action', 'name', 'creator', 'updater', 'reconcile'])
                ->make(true);

            Log::info('Returning Reconcile data to the view.');
            return $reconciles;
        }
        return view('reconciles_details.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        $reconciles = Reconcile::pluck('name', 'id'); 
        return view('reconciles_details.create', compact('reconciles'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        // Create a new reconcile entry
        try {
            Log::info($request->all());
            $reconcileDetails = new ReconcileDetails();
            $reconcileDetails->reconcile_id = $request->input('reconcile_id');
            $reconcileDetails->name = $request->input('name');
            $reconcileDetails->sku = $request->input('sku');
            $reconcileDetails->physical_qty = $request->input('physical_qty');
            $reconcileDetails->software_qty = $request->input('software_qty');
            $reconcileDetails->difference = $request->input('difference');
            $reconcileDetails->difference_percentage = $request->input('difference_percentage');
            $reconcileDetails->created_by = auth()->user()->id;

            $reconcileDetails->save(); // Save to database
            $this->commonUtil->activityLog($reconcileDetails, 'created');
            return response()->json([
                'success' => true,
                'msg' => "Reconcile Details Added Successfully", // Customize your success message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'msg' => __('business.error_occurred') . ': ' . $e->getMessage(),
            ]);
        }
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
    {        $reconciles = Reconcile::pluck('name', 'id'); 

        $reconcileDetails = ReconcileDetails::find($id);
        return view('reconciles_details.edit', compact('reconcileDetails', 'reconciles'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        $reconcileDetails = ReconcileDetails::findOrFail($id);

    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'reconcile_id' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'msg' => $validator->errors()->first(),
        ]);
    }

    try {
        Log::info($request->all());

        $reconcileDetails->reconcile_id = $request->input('reconcile_id');
            $reconcileDetails->name = $request->input('name');
            $reconcileDetails->sku = $request->input('sku');
            $reconcileDetails->physical_qty = $request->input('physical_qty');
            $reconcileDetails->software_qty = $request->input('software_qty');
            $reconcileDetails->difference = $request->input('difference');
            $reconcileDetails->difference_percentage = $request->input('difference_percentage');
            $reconcileDetails->updated_by = auth()->user()->id;
        $reconcileDetails->save(); // Save changes to the database

        $this->commonUtil->activityLog($reconcileDetails, 'updated');

        return response()->json([
            'success' => true,
            'msg' => "Reconcile Updated Successfully", 
        ]);
    } catch (\Exception $e) {
        Log::error($e->getMessage());
        return response()->json(['success' => false, 'msg' => "An error occurred ". $e->getMessage()]);
    }
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
                
                $reconcileDetails = ReconcileDetails::find($id);
                Log::info('Reconcile Details id: ' . $id . " name: " . ($reconcileDetails ? $reconcileDetails->name : 'not found'));

                if (!$reconcileDetails) {
                    return response()->json([
                        'success' => false,
                        'msg' => "Reconcile not found",
                    ]);
                }
                $reconcileDetails->delete();
                $this->commonUtil->activityLog($reconcileDetails, 'deleted');

                Log::info("Reconcile with ID {$reconcileDetails->id} deleted.");

               

                return response()->json([
                    'success' => true,
                    'msg' => "Reconcile Deleted Successfully",
                ]);
            } catch (\Exception $e) {
                
                $output = [
                    'success' => false,
                    'msg' =>"Something went wrong.",
                ];
            }

            return response()->json($output);
    }
    }
}
