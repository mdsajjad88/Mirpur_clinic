<?php

namespace App\Http\Controllers;

use App\Brands;
use App\BusinessLocation;
use App\Category;
use App\Product;
use App\PurchaseLine;
use App\Unit;
use App\Utils\ProductUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class ExpiryDateController extends Controller
{
    protected $productUtil;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ProductUtil $productUtil)
    {
        $this->productUtil = $productUtil;
    }

    public function index(Request $request)
    { 
        if (!auth()->user()->can('expiry_report.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = $request->session()->get('user.business_id');

        //TODO:: Need to display reference number and edit expiry date button

        //Return the details in ajax call
        if ($request->ajax()) {
            $query = PurchaseLine::leftjoin(
                'transactions as t',
                'purchase_lines.transaction_id',
                '=',
                't.id'
            )
                ->leftjoin(
                    'products as p',
                    'purchase_lines.product_id',
                    '=',
                    'p.id'
                )
                ->leftjoin(
                    'variations as v',
                    'purchase_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->leftjoin(
                    'product_variations as pv',
                    'v.product_variation_id',
                    '=',
                    'pv.id'
                )
                ->leftjoin('business_locations as l', 't.location_id', '=', 'l.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->where('t.business_id', $business_id)
                //->whereNotNull('p.expiry_period')
                //->whereNotNull('p.expiry_period_type')
                //->whereNotNull('exp_date')
                ->where('p.enable_stock', 1);
            // ->whereRaw('purchase_lines.quantity > purchase_lines.quantity_sold + quantity_adjusted + quantity_returned');

            $permitted_locations = auth()->user()->permitted_locations();

            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($request->input('location_id'))) {
                $location_id = $request->input('location_id');
                $query->where('t.location_id', $location_id)
                    //If filter by location then hide products not available in that location
                    ->join('product_locations as pl', 'pl.product_id', '=', 'p.id')
                    ->where(function ($q) use ($location_id) {
                        $q->where('pl.location_id', $location_id);
                    });
            }

            if (!empty($request->input('category_id'))) {
                $query->where('p.category_id', $request->input('category_id'));
            }
            if (!empty($request->input('sub_category_id'))) {
                $query->where('p.sub_category_id', $request->input('sub_category_id'));
            }
            if (!empty($request->input('brand_id'))) {
                $query->where('p.brand_id', $request->input('brand_id'));
            }
            if (!empty($request->input('unit_id'))) {
                $query->where('p.unit_id', $request->input('unit_id'));
            }
            if (!empty($request->input('exp_date_filter'))) {
                $query->whereDate('exp_date', '<=', $request->input('exp_date_filter'));
            }

            $only_mfg_products = request()->get('only_mfg_products', 0);
            if (!empty($only_mfg_products)) {
                $query->where('t.type', 'production_purchase');
            }

            $report = $query->select(
                'p.name as product',
                'p.sku',
                'p.type as product_type',
                'v.name as variation',
                'v.sub_sku',
                'pv.name as product_variation',
                'l.name as location',
                'mfg_date',
                'exp_date',
                'u.short_name as unit',
                DB::raw('SUM(COALESCE(quantity, 0) - COALESCE(quantity_sold, 0) - COALESCE(quantity_adjusted, 0) - COALESCE(quantity_returned, 0)) as stock_left'),
                't.ref_no',
                't.id as transaction_id',
                'purchase_lines.id as purchase_line_id',
                'purchase_lines.lot_number'
            )
                ->having('stock_left', '>', 0)
                ->groupBy('purchase_lines.variation_id')
                ->groupBy('purchase_lines.exp_date')
                ->groupBy('purchase_lines.lot_number');

            return Datatables::of($report)
                ->editColumn('product', function ($row) {
                    if ($row->product_type == 'variable') {
                        return $row->product . ' - ' .
                            $row->product_variation . ' - ' . $row->variation . ' (' . $row->sub_sku . ')';
                    } else {
                        return $row->product . ' (' . $row->sku . ')';
                    }
                })
                ->editColumn('mfg_date', function ($row) {
                    if (!empty($row->mfg_date)) {
                        return $this->productUtil->format_date($row->mfg_date);
                    } else {
                        return '--';
                    }
                })
                ->editColumn('ref_no', function ($row) {
                    return '<button type="button" data-href="' . action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->transaction_id])
                        . '" class="btn btn-link btn-modal" data-container=".view_modal"  >' . $row->ref_no . '</button>';
                })
                ->editColumn('stock_left', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency stock_left" data-currency_symbol=false data-orig-value="' . $row->stock_left . '" data-unit="' . $row->unit . '" >' . $row->stock_left . '</span> ' . $row->unit;
                })
                ->editColumn('lot_number', function ($row) {
                    return !empty($row->lot_number) ? $row->lot_number : '-';
                })

                ->addColumn('edit', function ($row) {
                    $html = '<button type="button" class="btn btn-primary btn-xs stock_expiry_edit_btn" data-transaction_id="' . $row->transaction_id . '" data-purchase_line_id="' . $row->purchase_line_id . '"> <i class="fa fa-edit"></i> ' . __('messages.edit') .
                        '</button>';

                    if (!empty($row->exp_date)) {
                        $carbon_exp = Carbon::createFromFormat('Y-m-d', $row->exp_date);
                        $carbon_now = Carbon::now();
                        if ($carbon_now->diffInDays($carbon_exp, false) < 0) {
                            $html .= ' <button type="button" class="btn btn-warning btn-xs remove_from_stock_btn" data-href="' . action([\App\Http\Controllers\StockAdjustmentController::class, 'removeExpiredStock'], [$row->purchase_line_id]) . '"> <i class="fa fa-trash"></i> ' . __('lang_v1.remove_from_stock') .
                                '</button>';
                        }
                    }

                    return $html;
                })
                ->editColumn('exp_date', function ($row) {
                    if (!empty($row->exp_date)) {
                        $carbon_exp = Carbon::createFromFormat('Y-m-d', $row->exp_date);
                        $carbon_now = Carbon::now();
                        if ($carbon_now->diffInDays($carbon_exp, false) >= 0) {
                            return $this->productUtil->format_date($row->exp_date) . '<br><small>( <span class="time-to-now">' . $row->exp_date . '</span> )</small>';
                        } else {
                            return $this->productUtil->format_date($row->exp_date) . ' &nbsp; <span class="label label-danger no-print">' . __('report.expired') . '</span><span class="print_section">' . __('report.expired') . '</span><br><small>( <span class="time-from-now">' . $row->exp_date . '</span> )</small>';
                        }
                    } else {
                        return '--';
                    }
                })
                ->editColumn('ref_no', function ($row) {
                    return '<button type="button" data-href="' . action([\App\Http\Controllers\PurchaseController::class, 'show'], [$row->transaction_id]) . '" class="btn btn-link btn-modal" data-container=".view_modal">' . $row->ref_no . '</button>';
                })
                ->rawColumns(['exp_date', 'ref_no', 'edit', 'stock_left', 'lot_number'])
                ->make(true);
        }

        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id, false, false, 'pos');
        $units = Unit::where('business_id', $business_id)
            ->pluck('short_name', 'id');
        $business_locations = BusinessLocation::forDropdown($business_id, true);
        $view_stock_filter = [
            Carbon::now()->subDay()->format('Y-m-d') => __('report.expired'),
            Carbon::now()->addWeek()->format('Y-m-d') => __('report.expiring_in_1_week'),
            Carbon::now()->addDays(15)->format('Y-m-d') => __('report.expiring_in_15_days'),
            Carbon::now()->addMonth()->format('Y-m-d') => __('report.expiring_in_1_month'),
            Carbon::now()->addMonths(3)->format('Y-m-d') => __('report.expiring_in_3_months'),
            Carbon::now()->addMonths(6)->format('Y-m-d') => __('report.expiring_in_6_months'),
            Carbon::now()->addYear()->format('Y-m-d') => __('report.expiring_in_1_year'),
        ];

        return view('report.stock_expiry_report')
            ->with(compact('categories', 'brands', 'units', 'business_locations', 'view_stock_filter'));
    }

    public function create(){
        $business_id = request()->user()->business_id;
        $categories = Category::forDropdown($business_id, 'product');

        $brands = Brands::forDropdown($business_id, false, false, 'pos');
        
        return view('expiry_date.create', compact('brands', 'categories'));
    }


    public function getExpiryProducts(Request $request)
    {
        $categoryIds = $request->input('category_id', []);
        $brandIds = $request->input('brand_id', []);
        $sellStatus = $request->input('product_sall_status');
        $expiryStatus = $request->input('expiry_date_filter');
        $permitted_locations = auth()->user()->permitted_locations();

        $query = Product::query()
        ->leftJoin('categories as cat', 'products.category_id', '=', 'cat.id')
        ->leftJoin('brands as b', 'products.brand_id', '=', 'b.id')
        ->leftJoin('purchase_lines as PL', 'products.id', '=', 'PL.product_id')
        ->join('variations as v', 'v.product_id', '=', 'products.id')
        ->leftJoin('variation_location_details as vld', function ($join) use ($permitted_locations) {
            $join->on('vld.variation_id', '=', 'v.id');
            if ($permitted_locations != 'all') {
                $join->whereIn('vld.location_id', $permitted_locations);
            }
        })
        ->select(
            'products.id as product_id',
            'products.name as product_name',
            'products.sku',
            'b.name as brand_name',
            'cat.name as category_name',
            DB::raw('COALESCE(SUM(vld.qty_available), 0) as current_stock'),
            'PL.transaction_id',
            'PL.exp_date',
            'PL.lot_number',
            DB::raw('COALESCE(SUM(PL.quantity), 0) as total_quantity'),
            DB::raw('COALESCE(SUM(PL.quantity_sold + PL.quantity_adjusted + PL.quantity_returned + PL.mfg_quantity_used + PL.	po_quantity_purchased), 0) as total_sold')
        )
        ->where('products.is_inactive', 0)
        ->havingRaw('current_stock > 0')  // Fix the 'having' condition for stock
        ->havingRaw('total_quantity > total_sold')  // Ensure total quantity is greater than total sold
            ->groupBy(
                'products.id',
                'products.name',
                'products.sku',
                'b.name',
                'cat.name',
                'PL.quantity',
                'PL.exp_date',
                'PL.lot_number'
            );

        if (!empty($categoryIds)) {
            $query->whereIn('products.category_id', $categoryIds);
        }

        if (!empty($brandIds)) {
            $query->whereIn('products.brand_id', $brandIds);
        }

        if ($sellStatus) {
            // Assuming you have a field to filter by selling status
            $query->where('products.not_for_selling', $sellStatus);
        }
        if ($expiryStatus === 'with_expiry') {
            // Filter products with an existing expiry date
            $query->whereNotNull('PL.exp_date');
        } elseif ($expiryStatus === 'without_expiry') {
            // Filter products without an existing expiry date
            $query->whereNull('PL.exp_date');
        }

        $products = $query->get();

        $expiryContent = view('expiry_date.expiry_content', compact('products'))->render();

        return response()->json(['content' => $expiryContent]);
    }


    public function generateLotNumber($productId)
     {
         $today_prefix = now()->format('ymd'); // Get current date prefix in YYMMDD format
 
         // Find the last lot number with the current date prefix
         $last_lot_number = PurchaseLine::where('product_id', $productId)
                                     ->where('lot_number', 'LIKE', $today_prefix . '-%')
                                     ->pluck('lot_number')
                                     ->sortDesc()
                                     ->first();
 
         if ($last_lot_number) {
             // Extract the numeric part after the prefix
             $numeric_part = substr($last_lot_number, 8); // Assuming format is YYMMDD-XXXX
 
             // Remove any non-numeric characters and increment the numeric part
             $numeric_part = preg_replace('/\D/', '', $numeric_part);
             $next_number = intval($numeric_part) + 1;
 
             // Return the new lot number with leading zeros
             return $today_prefix . '-' . sprintf('%03d', $next_number); // Format to 3 digits
         } else {
             // If no previous lot number exists, start with 001
             return $today_prefix . '-001';
         }
     }


    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'purchases.*.product_id' => 'required|integer|exists:products,id',
            'purchases.*.transaction_id' => 'required|integer|exists:purchase_lines,transaction_id',
            'purchases.*.lot_number' => 'nullable|string|max:100',
            'purchases.*.expiry_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            foreach ($request->purchases as $purchase) {
                // Find the purchase line by transaction_id and product_id
                $purchaseLine = PurchaseLine::where('product_id', $purchase['product_id'])
                    ->where('transaction_id', $purchase['transaction_id'])
                    ->first();
                    $date = Carbon::createFromFormat('d-m-Y', $purchase['expiry_date'])->format('Y-m-d');
                // If purchase line is found, update the lot number and expiry date
                if ($purchaseLine) {
                    $purchaseLine->lot_number = !empty($purchase['lot_number']) ? $purchase['lot_number'] : $this->generateLotNumber($purchase['product_id']);
                    $purchaseLine->exp_date = $date;
                    $purchaseLine->save();
                }
                Log::info('expiry date formate check', ['date' => $purchaseLine->exp_date]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Expiry dates and lot numbers successfully updated.',
                'url' => route('expiry_dates.create') // Redirect to the expiry date index or any other page
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Handle the error, log it if necessary
            return response()->json([
                'success' => false,
                'msg' => 'An error occurred while saving the expiry dates and lot numbers: ' . $e->getMessage()
            ], 500);
        }
    }


}
