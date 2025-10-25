<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\Contact;
use App\ProductWaitlist;
use App\User;
use App\Utils\TransactionUtil;
use App\Utils\BusinessUtil;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductWaitlistController extends Controller
{

        /**
     * All Utils instance.
     */
    protected $contactUtil;

    protected $productUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $cashRegisterUtil;

    protected $moduleUtil;

    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */
    public function __construct(TransactionUtil $transactionUtil, BusinessUtil $businessUtil) {
        $this->transactionUtil = $transactionUtil;
        $this->businessUtil = $businessUtil;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('product.view') && !auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {
            // Filter by location
            $permitted_locations = auth()->user()->permitted_locations();

            $this->updateStatus();

            // Main query for products on the waitlist
            $query = ProductWaitList::with(['product'])
                ->leftJoin('products', 'product_waitlists.product_id', '=', 'products.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('contacts as c', 'product_waitlists.contact_id', '=', 'c.id')
                ->leftJoin('transactions as t', 'product_waitlists.transaction_id', '=', 't.id')
                ->leftJoin('users as u', 'product_waitlists.added_by', '=', 'u.id')
                ->leftJoin('business_locations as location', 'product_waitlists.location_id', '=', 'location.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', function ($join) use ($permitted_locations) {
                    $join->on('vld.variation_id', '=', 'v.id');
                    if ($permitted_locations != 'all') {
                        $join->whereIn('vld.location_id', $permitted_locations);
                    }
                })
                ->whereNull('v.deleted_at')
                ->whereColumn('vld.location_id', 'product_waitlists.location_id'); // Ensure proper column match
            
            // Apply filters
            if ($status = request()->get('status')) {
                $query->where('product_waitlists.status', $status);
            }
            if ($call_status = request()->get('call_status')) {
                $query->where('product_waitlists.call_status', $call_status);
            }
            if ($sms_status = request()->get('sms_status')) {
                $query->where('product_waitlists.sms_status', 'like', "%{$sms_status}%");
            }
            if ($reference = request()->get('reference')) {
                $query->where('product_waitlists.reference', 'like', "%{$reference}%");
            }
            if ($added_by = request()->get('added_by')) {
                $query->where('product_waitlists.added_by', $added_by);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end = request()->end_date;
                $query->whereDate('product_waitlists.created_at', '>=', $start)
                    ->whereDate('product_waitlists.created_at', '<=', $end);
            }

            // Select the required columns
            $products = $query->select(
                'product_waitlists.id',
                'product_waitlists.transaction_id',
                'product_waitlists.waitlist_no',
                'product_waitlists.quantity_requested',
                'product_waitlists.status',
                'product_waitlists.call_status',
                'product_waitlists.sms_status',
                'product_waitlists.email_status',
                'product_waitlists.reference',
                'product_waitlists.restock_date',
                'product_waitlists.notification_sent_date',
                'product_waitlists.notes',
                'products.name as product_name',
                'products.sku as product_sku',
                'products.enable_stock',
                't.is_direct_sale',
                'vld.qty_available as quantity',
                'units.actual_name as unit',
                'u.first_name as added_by_first_name',
                'u.last_name as added_by_last_name',
                'c.name as customer',
                'c.mobile as customer_phone_number',
                'location.name as location_name',
                'product_waitlists.created_at',
                DB::raw('COALESCE(SUM(vld.qty_available), 0) as current_stock')
            )
            ->groupBy('product_waitlists.id', 'products.id', 'v.id', 'u.id', 'c.id', 'location.id', 'units.id');

            //  // Array of columns in DataTable order
            //  if (request()->has('order')) {
            //     $columns = [
            //         'created_at', 'product_name', 'product_sku', 'customer',
            //         'quantity_requested', 'status', 'added_by', 'location_name', 'reference',
            //         'restock_date', 'notification_sent_date', 'sms_status', 'notes', 'customer', 'call_status'
            //     ];

            //     $orderColumnIndex = request()->input('order.0.column');
            //     $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            //     $orderDirection = request()->input('order.0.dir', 'desc');

            //     $query->orderBy($orderColumn, $orderDirection);
            // } else {
            //     // Default ordering and DataTable ordering logic
            //     $query->orderBy('product_waitlists.created_at', 'desc');
            // }

            $call_statuses = $this->call_statuses();

            // Generate DataTable
            return Datatables::of($products)
                ->setRowId(function($row) {
                    return 'row_id_' . $row->id; // Assign unique row ID
                })
                ->addColumn('customer', function ($row) {
                    return $row->customer . '<br>' . $row->customer_phone_number;
                })
                ->addColumn('added_by', function ($row) {
                    return $row->added_by_first_name . ' ' . $row->added_by_last_name;
                })
                ->addColumn('product', function ($row) {
                    return $row->product_name;
                })
                ->addColumn('status', function ($row) {
                    if ($row->enable_stock) {
                        if ($row->quantity) {
                            $stock = $this->businessUtil->num_f($row->quantity, false, null, true);
                        } else {
                            $stock = $this->businessUtil->num_f($row->current_stock, false, null, true);
                        }

                        $current_stock = $stock . ' ' . $row->unit;
                    } else {
                        $current_stock = '--';
                    }

                    $statusColor = 'btn-secondary'; // Default color for unknown status
                    
                    // Determine the color class based on the status
                    switch ($row->status) {
                        case 'Pending':
                            $statusColor = 'btn-warning'; // Yellow for pending
                            break;
                        case 'Available':
                            $statusColor = 'btn-success'; // Green for available
                            break;
                        case 'Complete':
                            $statusColor = 'btn-primary'; // Blue for complete
                            break;
                    }
                    
                    // Return the HTML with the determined color class
                    return '<span style="padding: 0 2px; cursor: default;" class="btn ' . $statusColor . ' btn-sm">' . $row->status . '</span><br><small><b>(' . $current_stock . ')</b></small>';
                })
                ->editColumn('call_status', function ($row) use ($call_statuses) {
                    $status_color = $this->call_status_colors()[$row->call_status] ?? 'bg-gray';
                    return $row->call_status ? "<a href='#' class='cursor-pointer edit-call-status' data-href='" . action([ProductWaitlistController::class, 'editCallStatus'], $row->id) . "' data-container='.view_modal'><span class='label {$status_color}'>{$call_statuses[$row->call_status]}</span></a>" : '';
                })               
                ->addColumn('quantity_requested', function ($row) {
                    return $row->quantity_requested . ' ' . $row->unit;
                })
                ->addColumn('send_sms', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id . '">';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">
                                    ' . __('messages.actions') . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    // View option
                    if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view') || auth()->user()->can('view_own_sell_only')) {
                        $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->transaction_id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> '.__('messages.view').'</a></li>';
                    }

                    // Edit option
                    if ($row->is_direct_sale == 0){
                        if (auth()->user()->can('product.update')) {
                            $html .= '<li><a href="' . action([\App\Http\Controllers\SellPosController::class, 'edit'], [$row->transaction_id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                        }
                    }
                    else{
                        if (auth()->user()->can('product.update')) {
                            $html .= '<li><a href="' . action([\App\Http\Controllers\SellController::class, 'edit'], [$row->transaction_id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                        }
                    }

                    // Archive option
                    // if (auth()->user()->can('sell.delete')) {
                    //     $html .= '<li><a href="javascript:void(0);" data-href="'.action([\App\Http\Controllers\ProductWaitlistController::class, 'destroy'], [$row->id]).'" class="delete-product-waitlist"><i class="fas fa-file-archive"></i> ' . __('Archive').'</a></li>';
                    // }

                    // Delete option
                    if (auth()->user()->can('sell.delete')) {
                        $html .= '<li><a href="javascript:void(0);" data-href="'.action([\App\Http\Controllers\ProductWaitlistController::class, 'bothDelete'], [$row->id]).'" class="both-delete-product-waitlist"><i class="fas fa-trash"></i> '.__('messages.delete').'</a></li>';
                    }

                    $html .= '</ul></div>';

                    return $html;
                })
                ->editColumn('created_at', function ($row) {
                    return \Carbon\Carbon::parse($row->created_at)->format('d F Y, g:i A');
                })
                ->rawColumns(['action', 'status', 'send_sms', 'customer', 'call_status', 'created_at'])
                ->make(true);
        }

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);

        $created_by = User::forDropdown($business_id, false, false, true);
        $call_statuses = array_filter($this->call_statuses(), function ($key) {
            return $key !== 'done';
        }, ARRAY_FILTER_USE_KEY);

        return view('product_waitlists.index')->with(compact('created_by', 'business_locations', 'customers', 'call_statuses'));
    }
    
    public function editCallStatus($id)
    {
        $waitlist = ProductWaitlist::findOrFail($id);
        $statuses = $this->call_statuses();
        return view('product_waitlists.call_status_edit_modal', compact('waitlist', 'statuses'));
    }
    
    public function updateCallStatus(Request $request)
    {
        $waitlist = ProductWaitlist::findOrFail($request->id);
        $waitlist->call_status = $request->call_status;
        $waitlist->notes = $request->notes;  // Ensure this is being set correctly
        $waitlist->save();

        // If call status is 'done', automatically delete the product waitlist
        if ($request->call_status == 'done') {
            return $this->destroy($request->id); // Call destroy method to delete the item
        }
    
        return response()->json(['success' => __('messages.updated_success')]);
    }
    
    protected function call_status_colors()
    {
        return [
            'pending' => 'bg-yellow',
            'no_response' => 'bg-info',
            'switch_off' => 'bg-navy',
            'away' => 'bg-green',
            'busy' => 'bg-red',
            'done' => 'bg-aqua',
        ];
    }
    
    protected function call_statuses()
    {
        return [
            'pending' => __('lang_v1.pending'),
            'no_response' => __('lang_v1.no_response'),
            'switch_off' => __('lang_v1.switch_off'),
            'away' => __('lang_v1.away'),
            'busy' => __('lang_v1.busy'),
            'done' => __('lang_v1.done'),
        ];
    }

    protected function updateStatus(){
        // Filter by location
            $permitted_locations = auth()->user()->permitted_locations();
            
            // Main query for products on the waitlist
            $query = ProductWaitList::with(['product'])
                ->leftJoin('products', 'product_waitlists.product_id', '=', 'products.id')
                ->leftJoin('business_locations as location', 'product_waitlists.location_id', '=', 'location.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', function ($join) use ($permitted_locations) {
                    $join->on('vld.variation_id', '=', 'v.id');
                    if ($permitted_locations != 'all') {
                        $join->whereIn('vld.location_id', $permitted_locations);
                    }
                })
                ->whereNull('v.deleted_at')
                ->whereColumn('vld.location_id', 'product_waitlists.location_id'); // Ensure proper column match

            // Select the required columns
            $query->select(
                'product_waitlists.id',
                'product_waitlists.status',
                DB::raw('COALESCE(SUM(vld.qty_available), 0) as current_stock')
            )
            ->groupBy('product_waitlists.id');

            // Update the status in the query itself
            $query->get()->each(function ($product) {
                // Determine the status based on stock availability
                $new_status = $product->current_stock > 0 ? 'Available' : 'Pending';

                // Update the status only if it differs from the current one
                if ($product->status !== $new_status) {
                    ProductWaitList::where('id', $product->id)->update(['status' => $new_status]);
                }
            });

    }

    public function archive()
    {
        if (!auth()->user()->can('product.view') && !auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            // Filter by location

            // Main query for products on the waitlist
            $query = ProductWaitList::onlyTrashed()->with(['product'])
                ->leftJoin('products', 'product_waitlists.product_id', '=', 'products.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('contacts as c', 'product_waitlists.contact_id', '=', 'c.id')
                ->leftJoin('users as u', 'product_waitlists.added_by', '=', 'u.id')
                ->leftJoin('business_locations as location', 'product_waitlists.location_id', '=', 'location.id');

            $products = $query->select(
                'product_waitlists.id',
                'product_waitlists.transaction_id',
                'product_waitlists.waitlist_no',
                'product_waitlists.quantity_requested',
                'product_waitlists.status',
                'product_waitlists.call_status',
                'product_waitlists.sms_status',
                'product_waitlists.email_status',
                'product_waitlists.reference',
                'product_waitlists.restock_date',
                'product_waitlists.notification_sent_date',
                'product_waitlists.notes',
                'products.name as product_name',
                'products.sku as product_sku',
                'units.actual_name as unit',
                'u.first_name as added_by_first_name',
                'u.last_name as added_by_last_name',
                'c.name as customer',
                'c.mobile as customer_phone_number',
                'location.name as location_name',
                'product_waitlists.created_at'
            );

            // Array of columns in DataTable order
            $columns = [
                'created_at', 'product_name', 'product_sku', 'customer',
                'quantity_requested', 'status', 'added_by', 'location_name', 'reference',
                'restock_date', 'notification_sent_date', 'sms_status', 'notes', 'customer', 'call_status'
            ];

             // Apply ordering
             if (request()->has('order')) {
                $orderColumnIndex = request()->input('order.0.column');
                $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
                $orderDirection = request()->input('order.0.dir', 'asc');

                // Custom ordering for the "status" column
                if ($orderColumn == 'status') {
                    $query->orderByRaw("CASE 
                        WHEN product_waitlists.status = 'Complete' THEN 1 
                        WHEN product_waitlists.status = 'Available' THEN 2
                        WHEN product_waitlists.status = 'Pending' THEN 3 
                        ELSE 4
                        END {$orderDirection}");
                } else {
                    $query->orderBy($orderColumn, $orderDirection);
                }
            } else {
                // Default ordering if no ordering is specified
                $query->orderByRaw("CASE 
                    WHEN product_waitlists.status = 'Complete' THEN 1 
                        WHEN product_waitlists.status = 'Available' THEN 2
                        WHEN product_waitlists.status = 'Pending' THEN 3 
                        ELSE 4
                    END ASC")
                    ->orderBy('product_waitlists.created_at', 'desc');
            }

            $call_statuses = $this->call_statuses();
            // Generate DataTable
            return Datatables::of($products)
                ->addColumn('customer', function ($row) {
                    return $row->customer . '<br>' . $row->customer_phone_number;
                })
                ->addColumn('added_by', function ($row) {
                    return $row->added_by_first_name . ' ' . $row->added_by_last_name;
                })
                ->addColumn('product', function ($row) {
                    return $row->product_name;
                })
                ->addColumn('status', function ($row) {
                    $statusColor = 'btn-secondary'; // Default color for unknown status
                
                    // Determine the color class based on the status
                    switch ($row->status) {
                        case 'Pending':
                            $statusColor = 'btn-warning'; // Yellow for pending
                            break;
                        case 'Available':
                            $statusColor = 'btn-success'; // Green for available
                            break;
                        case 'Complete':
                            $statusColor = 'btn-primary'; // Blue for complete
                            break;
                    }
                
                    // Return the HTML with the determined color class
                    return '<span style="padding: 0 2px; cursor: default;" class="btn ' . $statusColor . ' btn-sm">' . $row->status . '</span>';
                })
                ->editColumn('call_status', function ($row) use ($call_statuses) {
                    $status_color = $this->call_status_colors()[$row->call_status] ?? 'bg-gray';
                    return $row->call_status ? "<a href='#' class='cursor-pointer edit-call-status' data-href='" . action([ProductWaitlistController::class, 'editCallStatus'], $row->id) . "' data-container='.view_modal'><span class='label {$status_color}'>{$call_statuses[$row->call_status]}</span></a>" : '';
                })                 
                ->addColumn('quantity_requested', function ($row) {
                    return $row->quantity_requested . ' ' . $row->unit;
                })
                ->addColumn('send_sms', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id . '">';
                })
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                                <button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">
                                    ' . __('messages.actions') . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    
                    // View option
                    if (auth()->user()->can('sell.view') || auth()->user()->can('direct_sell.view') || auth()->user()->can('view_own_sell_only')) {
                        $html .= '<li><a href="#" data-href="'.action([\App\Http\Controllers\SellController::class, 'show'], [$row->transaction_id]).'" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> '.__('messages.view').'</a></li>';
                    }
                
                    // // Edit option
                    if (auth()->user()->can('product.update')) {
                        $html .= '<li><a href="' . action([\App\Http\Controllers\SellController::class, 'edit'], [$row->transaction_id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                    }
                
                    // Delete option
                    if (auth()->user()->can('sell.delete')) {
                        $html .= '<li><a href="javascript:void(0);" data-href="'.action([\App\Http\Controllers\ProductWaitlistController::class, 'forceDelete'], [$row->id]).'" class="force-delete-product-waitlist"><i class="fas fa-trash"></i> '.__('messages.delete').'</a></li>';
                    }
                
                    $html .= '</ul></div>';
                
                    return $html;
                })
                
                ->editColumn('created_at', function ($row) {
                    return \Carbon\Carbon::parse($row->created_at)->format('d F Y, g:i A');
                })
                ->rawColumns(['action', 'status', 'send_sms', 'customer', 'call_status'])
                ->make(true);
        }

        return view('product_waitlists.index');
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ProductWaitlist  $productWaitlist
     * @return \Illuminate\Http\Response
     */
    public function show(ProductWaitlist $productWaitlist)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ProductWaitlist  $productWaitlist
     * @return \Illuminate\Http\Response
     */
    public function edit(ProductWaitlist $productWaitlist)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ProductWaitlist  $productWaitlist
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ProductWaitlist $productWaitlist)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProductWaitlist  $productWaitlist
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            //Begin transaction
            $waitlist = ProductWaitlist::findOrFail($id);
            $business_id = request()->session()->get('user.business_id');
            DB::beginTransaction();
            $this->transactionUtil->deleteSale($business_id, $waitlist->transaction_id);
            DB::commit();
            $waitlist->delete();
    
            return response()->json(['success' => true, 'msg' => __('Product waitlist deleted successfully')]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['success' => false, 'msg' => __('Something went wrong')], 500);
        }
    }


    public function bothDelete($id)
    {
        if (!auth()->user()->can('sell.delete') && !auth()->user()->can('direct_sell.delete') && !auth()->user()->can('so.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Begin transaction
            $waitlist = ProductWaitlist::withTrashed()->findOrFail($id);
            $business_id = request()->session()->get('user.business_id');
            DB::beginTransaction();
            $this->transactionUtil->deleteSale($business_id, $waitlist->transaction_id);
            DB::commit();
            $waitlist->forceDelete();
    
            return response()->json(['success' => true, 'msg' => __('Product waitlist deleted successfully')]);
        } catch (\Exception $e) {
            \Log::error($e);
            return response()->json(['success' => false, 'msg' => __('Something went wrong')], 500);
        }
    }

    public function forceDelete($id)
    {
        // Check if the user has the necessary permissions
        if (!auth()->user()->can('sell.delete') && !auth()->user()->can('direct_sell.delete') && !auth()->user()->can('so.delete')) {
            abort(403, 'Unauthorized action.');
        }
    
        try {
            // Find the waitlist by ID or throw a 404 if not found
            $waitlist = ProductWaitlist::withTrashed()->findOrFail($id); // Fetch trashed records too
    
            // Permanently delete the waitlist (force delete)
            $waitlist->forceDelete();
        
            return response()->json(['success' => true, 'msg' => __('Product waitlist forcefully deleted successfully')]);
        } catch (\Exception $e) {
            \Log::error($e);  // Log any exceptions for debugging
            return response()->json(['success' => false, 'msg' => __('Something went wrong')], 500);
        }
    }
    


    public function sendSMS(Request $request)
    {
        try {
            // Get selected product waitlists by their IDs
            $selected_rows = explode(',', $request->selected_rows); // Ensure selected rows are extracted from the request

            // Retrieve the product waitlists along with necessary relationships and data
            $productWaitlists = ProductWaitlist::with(['product'])
                ->leftJoin('products', 'product_waitlists.product_id', '=', 'products.id')
                ->join('units', 'products.unit_id', '=', 'units.id')
                ->leftJoin('contacts as c', 'product_waitlists.contact_id', '=', 'c.id')
                ->leftJoin('users as u', 'product_waitlists.added_by', '=', 'u.id')
                ->leftJoin('business_locations as location', 'product_waitlists.location_id', '=', 'location.id')
                ->whereIn('product_waitlists.id', $selected_rows)
                ->where('product_waitlists.status', 'Available')
                ->select(
                    'product_waitlists.id',
                    'product_waitlists.sms_status',
                    'product_waitlists.notification_sent_date',
                    'products.name as product_name',
                    'c.id as customer_id',
                    'c.name as customer',
                    'c.mobile as customer_phone_number',
                    'location.name as location_name'
                )
                ->get();

            // Group product waitlists by customer
            $groupedWaitlists = $productWaitlists->groupBy('customer_id');

            // Loop through each group (each customer) and send a single SMS per customer
            foreach ($groupedWaitlists as $customer_id => $waitlists) {
                $customer_phone = $waitlists->first()->customer_phone_number;
                $customer = $waitlists->first()->customer;

                // Concatenate all product names available for the customer
                $product_names = $waitlists->pluck('product_name')->implode(', ');

                // Construct the message for the customer
                $message = 'Dear ' . $customer . ', your products ' . $product_names . ' are available at ' 
                        . $waitlists->first()->location_name;

                // Send SMS via AamarSMS API
                $response = $this->sendBulkSMS($customer_phone, $message);
                Log::info('SMS Response: ', [$response]);

                // Update SMS status and notification date for each product waitlist of this customer
                foreach ($waitlists as $productWaitlist) {
                    if ($response['success'] == 1) {
                        $productWaitlist->sms_status = 'SMS Sent Successfully';
                    } else {
                        $productWaitlist->sms_status = 'Failed to send SMS';
                    }
                    $productWaitlist->notification_sent_date = now();
                    $productWaitlist->save(); // Save each waitlist's status
                }
            }

            // Return success response
            return response()->json(['success' => true, 'message' => 'SMS sent successfully.']);

        } catch (\Exception $e) {
            Log::error('Error sending SMS: ', ['error' => $e->getMessage()]); // Log the exception
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }




    private function sendBulkSMS($number, $message)
    {
        $business_id = request()->session()->get('user.business_id');
        $business = Business::where('id', $business_id)->first();
        $sms_settings = $business->sms_settings;

        // Extract username, password, and URL from sms_settings
        $username = $sms_settings['send_to_param_name'];
        $password = $sms_settings['msg_param_name'];
        $url = $sms_settings['url'];

        // Log for debugging purposes
        Log::info('SMS Information', [
            'username' => $username,
            'password' => $password,
            'url' => $url
        ]);

        $params = [
            'user' => $username,
            'password' => $password,
            'from' => 'AWC DHAKA',
            'to' => $number,
            'text' => $message
        ];

        // Send the HTTP GET request
        $response = Http::get($url, $params);

        return $response->json();
    }

    public function sendEmail(Request $request){
        $productWaitlists = ProductWaitlist::whereIn('id', $request->ids)->get();

        foreach($productWaitlists as $productWaitlist){
            // Send email code here
        }

        return response()->json(['success' => true, 'message' => 'Email sent successfully.']);
    }

    public function customerWaitlistModal($contact_id)
    {
        $this->updateStatus();
        // Fetch the waitlist items
        $waitlists = ProductWaitlist::where('contact_id', $contact_id)
            ->join('products', 'product_waitlists.product_id', '=', 'products.id')
            ->join('variations as v', 'v.product_id', '=', 'products.id')
            ->join('units', 'products.unit_id', '=', 'units.id')
            ->select(
                'product_waitlists.id',
                'products.name as product_name',
                'products.sku',
                'v.id as variation_id',
                'units.actual_name as unit',
                'product_waitlists.created_at',
                'product_waitlists.quantity_requested',
                'product_waitlists.status',
                'product_waitlists.call_status',
                'product_waitlists.notes'
            )
            ->get();

        // Check if waitlists are empty
        if ($waitlists->isEmpty()) {
            // Return an empty response or a message if no waitlists are found
            return response()->json(['message' => 'No waitlist data available.'], 404);
        }

        // Get statuses
        $statuses = $this->call_statuses();

        // Return the view with waitlists and statuses if data is available
        return view('sale_pos.partials.customer_waitlist_modal', compact('waitlists', 'statuses'));
    }

    public function updateQuantity(Request $request)
    {
        $waitlist = ProductWaitlist::where('product_id', $request->product_id)
                                ->where('contact_id', $request->contact_id)
                                ->first();

        if ($waitlist) {
            $waitlist->quantity_requested += $request->quantity;
            $waitlist->save();

            return response()->json(['success' => 1, 'message' => 'Quantity updated']);
        }

        return response()->json(['success' => 0, 'message' => 'Product not found in waitlist']);
    }


}
