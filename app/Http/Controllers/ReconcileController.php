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
use Illuminate\Support\Facades\DB;
class ReconcileController extends Controller
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
            $query = Reconcile::with('creator', 'updater')->latest()->get();
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
                        $html .= '<li><a href="' . action([\App\Http\Controllers\ReconcileController::class, 'edit'], [$row->id]) . '" class="edit_reconcile_button">
                                <i class="glyphicon glyphicon-edit"></i>' . __('messages.edit') . '</a></li>';
                        
                    }

                    if (!$row->is_default && auth()->user()->can('customer.delete')) {
                        $html .= '<li><a href="' . action([\App\Http\Controllers\ReconcileController::class, 'destroy'], [$row->id]) . '" class="delete_reconcile_button">
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
                ->setRowAttr([
                    'data-href' => function ($row) {
                        // Check for view permission before creating the link
                        if (auth()->user()->can('sell.view') || auth()->user()->can('view_own_sell_only')) {
                            return action([\App\Http\Controllers\ReconcileController::class, 'show'], [$row->id]);
                        } else {
                            return ''; // No link if no permission
                        }
                    }
                ])
                ->rawColumns(['action', 'name', 'creator', 'updater'])
                ->make(true);
                

            Log::info('Returning Reconcile data to the view.');
            return $reconciles;
        }
        return view('reconciles.index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('reconciles.create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'msg' => $validator->errors()->first(),
            ]);
        }

        // Create a new reconcile entry
        try {
            Log::info($request->all());
            $reconcile = new Reconcile();
            $reconcile->name = $request->input('name');
            $date =  $this->commonUtil->uf_date($request->input('date'));
            $reconcile->date = $date;
            $reconcile->created_by = auth()->user()->id;
            // Add any additional fields here

            $reconcile->save(); // Save to database
            $this->commonUtil->activityLog($reconcile, 'created');
            return response()->json([
                'success' => true,
                'msg' => __('business.reconcile_added_successfully'), // Customize your success message
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
        $reconcileDetails  = ReconcileDetails::with('reconcile', 'creator', 'updater')->where('reconcile_id', $id)->get();
        return view('reconciles.view', compact('reconcileDetails'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $reconcile  = Reconcile::find($id);
        return view('reconciles.edit', compact('reconcile'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
   

    public function update(Request $request, $id)
{
    // Find the reconcile record by ID
    $reconcile = Reconcile::findOrFail($id);

    // Validate the incoming request data
    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'date' => 'required|date_format:Y-m-d', // Ensure the format is correct
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'msg' => $validator->errors()->first(),
        ]);
    }

    try {
        Log::info($request->all());

        // Update the reconcile entry
        $reconcile->name = $request->input('name');

        // Use the date directly if it's in Y-m-d format
        $date = $request->input('date');
        Log::info("Raw date input: " . $date);
        
        // Optionally validate/format here
        $reconcile->date = $date; 

        $reconcile->updated_by = auth()->user()->id; // Update the user who made the change

        $reconcile->save(); // Save changes to the database

        $this->commonUtil->activityLog($reconcile, 'updated');

        return response()->json([
            'success' => true,
            'msg' => "Reconcile Updated Successfully", 
        ]);
    } catch (\Exception $e) {
        Log::error($e->getMessage());
        return response()->json(['success' => false, 'msg' => "An error occurred"]);
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
            DB::beginTransaction();
    
            try {
                $reconcile = Reconcile::find($id);
                Log::info('Reconcile id: ' . $id . " name: " . ($reconcile ? $reconcile->name : 'not found'));
    
                if (!$reconcile) {
                    return response()->json([
                        'success' => false,
                        'msg' => "Reconcile not found",
                    ]);
                }
    
                $details = ReconcileDetails::where('reconcile_id', $id)->get();
                if ($details->isNotEmpty()) {
                    foreach ($details as $detail) {
                        $detail->delete();
                        $this->commonUtil->activityLog($detail, 'deleted'); // Log each detail deletion
                    }
                }
    
                $reconcile->delete();
                $this->commonUtil->activityLog($reconcile, 'deleted');
    
                Log::info("Reconcile with ID {$reconcile->id} deleted.");
    
                DB::commit(); // Commit the transaction
    
                return response()->json([
                    'success' => true,
                    'msg' => "Reconcile Deleted Successfully",
                ]);
            } catch (\Exception $e) {
                DB::rollBack(); // Roll back on error
                Log::error('Error deleting reconcile: ' . $e->getMessage()); // Log the error
    
                return response()->json([
                    'success' => false,
                    'msg' => "Something went wrong.",
                ]);
            }
        }
    }

public function checkUniqueName(Request $request)
    {
        Log::info('Checking name ID '. $request->input('name')); 
        $name = $request->input('name');


        $valid = 'true';
        if (! empty($name)) {
            $query = Reconcile::where('name', $name);
            
            $count = $query->count();
            if ($count > 0) {
                $valid = 'false';
            }
        }
        echo $valid;
        exit;
    }




}
