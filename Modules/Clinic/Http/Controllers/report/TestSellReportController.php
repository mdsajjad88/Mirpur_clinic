<?php

namespace Modules\Clinic\Http\Controllers\report;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\BusinessLocation;
use App\Transaction;
use App\InvoiceScheme;
use App\User;
use App\Category;
use App\Brands;
use App\Product;
use App\Variation;
use App\SellingPriceGroup;

use App\Contact;

use Spatie\Activitylog\Models\Activity;
use App\TransactionSellLine;
use App\Utils\ContactUtil;
use App\Utils\ProductUtil;
use App\Utils\BusinessUtil;
use App\Utils\TransactionUtil;
use App\Utils\ModuleUtil;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Modules\Clinic\Utils\ClinicSellUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\NotificationUtil;
use App\TaxRate;
use App\Unit;
class TestSellReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $contactUtil;

    protected $businessUtil;

    protected $transactionUtil;

    protected $productUtil;
    protected $moduleUtil;
    protected $clinicSellUtil;
    protected $cashRegisterUtil;
    protected $notificationUtil;

    public function __construct(ContactUtil $contactUtil, BusinessUtil $businessUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil, ProductUtil $productUtil, ClinicSellUtil $clinicSellUtil, CashRegisterUtil $cashRegisterUtil, NotificationUtil $notificationUtil)
    {
        $this->contactUtil = $contactUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->clinicSellUtil = $clinicSellUtil;
        $this->moduleUtil = $moduleUtil;
        $this->productUtil = $productUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->notificationUtil = $notificationUtil;
        $this->dummyPaymentLine = [
            'method' => '',
            'amount' => 0,
            'note' => '',
            'card_transaction_number' => '',
            'card_number' => '',
            'card_type' => '',
            'card_holder_name' => '',
            'card_month' => '',
            'card_year' => '',
            'card_security' => '',
            'cheque_number' => '',
            'bank_account_number' => '',
            'is_return' => 0,
            'transaction_no' => '',
        ];

        $this->shipping_status_colors = [
            'ordered' => 'bg-yellow',
            'packed' => 'bg-info',
            'shipped' => 'bg-navy',
            'delivered' => 'bg-green',
            'cancelled' => 'bg-red',
        ];
    }
    public function index(Request $request)
    {
        if (!auth()->user()->can('clinic.test.view')) {
            abort(403, 'Unauthorized action.');
        }
        
        $business_id = request()->session()->get('user.business_id');
        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');
        $vld_str = '';
        if (!empty($location_id)) {
            $vld_str = "AND vld.location_id=$location_id";
        }
        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $today = $request->get('transaction_date');
            $single = $request->get('single');

            $query = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->leftjoin('categories as cat', 'p.category_id', '=', 'cat.id')
                ->leftjoin('brands as b', 'p.brand_id', '=', 'b.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.sub_type', 'test')
                ->where('t.status', 'final')
                ->select(
                    'p.name as product_name',
                    'p.enable_stock',
                    'cat.name as category_name',
                    'b.name as brand_name',
                    'p.type as product_type',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    't.id as transaction_id',
                    't.transaction_date as transaction_date',
                    'transaction_sell_lines.parent_sell_line_id',
                    DB::raw('DATE_FORMAT(t.transaction_date, "%Y-%m-%d") as formated_date'),
                    DB::raw("(SELECT SUM(vld.qty_available) FROM variation_location_details as vld WHERE vld.variation_id=v.id $vld_str) as current_stock"),
                    DB::raw('SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as total_qty_sold'),
                    'u.short_name as unit',
                    DB::raw('SUM((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
                )
                ->groupBy('v.id');

            if ($single == 2) {
                $query->groupBy('formated_date');
            }
            // if (!empty($today)) {
            //     $query->whereDate('t.transaction_date', $today);
            // }

            if (!empty($variation_id)) {
                $query->where('transaction_sell_lines.variation_id', $variation_id);
            }
            
            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $customer_id = $request->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('t.contact_id', $customer_id);
            }

            $customer_group_id = $request->get('customer_group_id', null);
            if (!empty($customer_group_id)) {
                $query->leftjoin('contacts AS c', 't.contact_id', '=', 'c.id')
                    ->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
                    ->where('CG.id', $customer_group_id);
            }
           
            $category_id = $request->get('category_id', null);
            if (!empty($category_id)) {
                $query->whereIn('p.category_id', $category_id);
            }

            $brand_id = $request->get('brand_id', null);
            if (!empty($brand_id)) {
                $query->whereIn('p.brand_id', $brand_id);
            }
            
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            Log::info('Start Date: ' . $start_date . ' | End Date: ' . $end_date);
            
            if (!empty($start_date) && !empty($end_date)) {
                // Check if dates are in the correct format
                if (\Carbon\Carbon::hasFormat($start_date, 'Y-m-d') && \Carbon\Carbon::hasFormat($end_date, 'Y-m-d')) {
                    Log::info('Valid date formats detected');
                    
                    // Apply the date range filter
                    $query->whereDate('t.transaction_date', '>=', $start_date)
                          ->whereDate('t.transaction_date', '<=', $end_date);
                } else {
                    Log::error('Invalid date formats: Start Date - ' . $start_date . ', End Date - ' . $end_date);
                }
            }
            $types = request()->get('type', null);
            if (!empty($types)) {
                $query->whereIn('p.type', $types);
            }
            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }
                    $html = '<a href="#" data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->transaction_id]) . '" class="btn-modal" data-container=".view_modal">' . $product_name . '</a>';
                    return $html;
                })
                ->editColumn('transaction_date', '{{@format_date($formated_date)}}')
                ->editColumn('total_qty_sold', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency sell_qty" data-currency_symbol=false data-orig-value="' . (float) $row->total_qty_sold . '" data-unit="' . $row->unit . '" >' . (float) $row->total_qty_sold . '</span> ' . $row->unit;
                })
                ->editColumn('current_stock', function ($row) use ($start_date) {
                    if ($row->enable_stock) {
                        return '<span data-is_quantity="true" class="display_currency current_stock" data-currency_symbol=false data-orig-value="' . (float) $row->current_stock . '" data-unit="' . $row->unit . '" >' . (float) $row->current_stock . '</span> ' . $row->unit;
                    } else {
                        return '';
                    }
                })
                ->editColumn('subtotal', function ($row) {
                    $class = is_null($row->parent_sell_line_id) ? 'row_subtotal' : '';

                    return '<span class="' . $class . '" data-orig-value="' . $row->subtotal . '">' .
                        $this->transactionUtil->num_f($row->subtotal, true) . '</span>';
                })
                ->editColumn('transaction_date', '{{format_datetime($transaction_date)}}')
                ->rawColumns(['product_name', 'current_stock', 'subtotal', 'total_qty_sold'])
                ->make(true);
        }
        $rack_enabled = (request()->session()->get('business.enable_racks') || request()->session()->get('business.enable_row') || request()->session()->get('business.enable_position'));
 
         $categories = Category::forDropdown($business_id, 'test');
 
         $brands = Brands::forDropdown($business_id);
 
         $units = Unit::forDropdown($business_id);
 
         $tax_dropdown = TaxRate::forBusinessDropdown($business_id, false);
         $taxes = $tax_dropdown['tax_rates'];
 
         $business_locations = BusinessLocation::forDropdown($business_id);
         $business_locations->prepend(__('lang_v1.none'), 'none');

         $pos_module_data = $this->moduleUtil->getModuleData('get_filters_for_list_product_screen');
 
         $is_admin = $this->productUtil->is_admin(auth()->user());
        return view('clinic::report.test_sell_report.index', compact('rack_enabled',
                 'categories',
                 'brands',
                 'units',
                 'taxes',
                 'business_locations',
                 'pos_module_data',
                 'is_woocommerce',
                 'is_admin'));
    }
    public function todayTestSellReport(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->get('location_id', null);

        $vld_str = '';
        if (!empty($location_id)) {
            $vld_str = "AND vld.location_id=$location_id";
        }
        if ($request->ajax()) {
            $variation_id = $request->get('variation_id', null);
            $today = $request->get('transaction_date');
            $single = $request->get('single');

            $query = TransactionSellLine::join(
                'transactions as t',
                'transaction_sell_lines.transaction_id',
                '=',
                't.id'
            )
                ->join(
                    'variations as v',
                    'transaction_sell_lines.variation_id',
                    '=',
                    'v.id'
                )
                ->join('product_variations as pv', 'v.product_variation_id', '=', 'pv.id')
                ->join('products as p', 'pv.product_id', '=', 'p.id')
                ->leftjoin('units as u', 'p.unit_id', '=', 'u.id')
                ->leftjoin('categories as cat', 'p.category_id', '=', 'cat.id')
                ->leftjoin('brands as b', 'p.brand_id', '=', 'b.id')
                ->where('t.business_id', $business_id)
                ->where('t.type', 'sell')
                ->where('t.sub_type', 'test')
                ->where('t.status', 'final')
                ->select(
                    'p.name as product_name',
                    'p.enable_stock',
                    'cat.name as category_name',
                    'b.name as brand_name',
                    'p.type as product_type',
                    'p.product_type as product_sub_type',
                    'pv.name as product_variation',
                    'v.name as variation_name',
                    'v.sub_sku',
                    't.id as transaction_id',
                    't.transaction_date as transaction_date',
                    'transaction_sell_lines.parent_sell_line_id',
                    DB::raw('DATE_FORMAT(t.transaction_date, "%Y-%m-%d") as formated_date'),
                    DB::raw("(SELECT SUM(vld.qty_available) FROM variation_location_details as vld WHERE vld.variation_id=v.id $vld_str) as current_stock"),
                    DB::raw('SUM(transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) as total_qty_sold'),
                    'u.short_name as unit',
                    DB::raw('SUM((transaction_sell_lines.quantity - transaction_sell_lines.quantity_returned) * transaction_sell_lines.unit_price_inc_tax) as subtotal')
                )
                ->groupBy('v.id');

            if ($single == 2) {
                $query->groupBy('formated_date');
            }
            if (!empty($today)) {
                $query->whereDate('t.transaction_date', $today);
            }
            if (!empty(request()->input('product_sub_type'))) {
                $query->where('p.product_type', request()->input('product_sub_type'));
            }

            if (!empty($variation_id)) {
                $query->where('transaction_sell_lines.variation_id', $variation_id);
            }
            $start_date = $request->get('start_date');
            $end_date = $request->get('end_date');
            if (!empty($start_date) && !empty($end_date)) {
                $query->whereDate('t.transaction_date', '>=', $start_date)
                    ->whereDate('t.transaction_date', '<=', $end_date);
            }

            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $query->whereIn('t.location_id', $permitted_locations);
            }

            if (!empty($location_id)) {
                $query->where('t.location_id', $location_id);
            }

            $customer_id = $request->get('customer_id', null);
            if (!empty($customer_id)) {
                $query->where('t.contact_id', $customer_id);
            }

            $customer_group_id = $request->get('customer_group_id', null);
            if (!empty($customer_group_id)) {
                $query->leftjoin('contacts AS c', 't.contact_id', '=', 'c.id')
                    ->leftjoin('customer_groups AS CG', 'c.customer_group_id', '=', 'CG.id')
                    ->where('CG.id', $customer_group_id);
            }

            $category_id = $request->get('category_id', null);
            if (!empty($category_id)) {
                $query->whereIn('p.category_id', $category_id);
            }

            $brand_id = $request->get('brand_id', null);
            if (!empty($brand_id)) {
                $query->whereIn('p.brand_id', $brand_id);
            }
            $unit_ids = request()->get('unit_id', null);
            if (!empty($unit_ids)) {
                $query->whereIn('p.unit_id', $unit_ids);
            }

            $tax_ids = request()->get('tax_id', null);
            if (!empty($tax_ids)) {
                $query->whereIn('p.tax', $tax_ids);
            }

            $types = request()->get('type', null);
            if (!empty($types)) {
                $query->whereIn('p.type', $types);
            }
            return Datatables::of($query)
                ->editColumn('product_name', function ($row) {
                    $product_name = $row->product_name;
                    if ($row->product_type == 'variable') {
                        $product_name .= ' - ' . $row->product_variation . ' - ' . $row->variation_name;
                    }
                    $html = '<a href="#" data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicSellController::class, 'show'], [$row->transaction_id]) . '" class="btn-modal" data-container=".view_modal">' . $product_name . '</a>';
                    return $html;
                })
                ->editColumn('transaction_date', '{{@format_date($formated_date)}}')
                ->editColumn('total_qty_sold', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency sell_qty" data-currency_symbol=false data-orig-value="' . (float) $row->total_qty_sold . '" data-unit="' . $row->unit . '" >' . (float) $row->total_qty_sold . '</span> ' . $row->unit;
                })
                ->editColumn('current_stock', function ($row) use ($start_date) {
                    if ($row->enable_stock) {
                        return '<span data-is_quantity="true" class="display_currency current_stock" data-currency_symbol=false data-orig-value="' . (float) $row->current_stock . '" data-unit="' . $row->unit . '" >' . (float) $row->current_stock . '</span> ' . $row->unit;
                    } else {
                        return '';
                    }
                })
                ->editColumn('subtotal', function ($row) {
                    $class = is_null($row->parent_sell_line_id) ? 'row_subtotal' : '';

                    return '<span class="' . $class . '" data-orig-value="' . $row->subtotal . '">' .
                        $this->transactionUtil->num_f($row->subtotal, true) . '</span>';
                })
                ->addColumn('product_sub_type', function($row) {
                    return $row->product_sub_type;
                })
                ->editColumn('transaction_date', '{{format_datetime($transaction_date)}}')
                ->rawColumns(['product_name', 'current_stock', 'subtotal', 'total_qty_sold'])
                ->make(true);
        }
    }
    public function totalTestReport(Request $request)
    {
        
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
