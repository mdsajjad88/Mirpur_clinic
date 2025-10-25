<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Clinic\Utils\ClinicProductUtil;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Exports\ProductsExport;
use App\Media;
use App\Product;
use App\ProductVariation;
use App\PurchaseLine;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Unit;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Variation;
use App\VariationGroupPrice;
use App\VariationLocationDetails;
use App\VariationTemplate;
use App\Warranty;
use Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use App\Events\ProductsCreatedOrModified;
use App\RandomCheck;
use App\RandomCheckDetail;
use App\VariationPriceHistory;
use App\FinalizeReport;
use App\ReportItem;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
class ClinicProductController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $productUtil;
    protected $moduleUtil;

    private $barcode_types;

    /**
     * Constructor
     *
     * @param  ProductUtils  $product
     * @return void
     */

    public function __construct(ClinicProductUtil $productUtil,  ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;

        $this->barcode_types = $this->productUtil->barcode_types();
    }

    public function index()
    {
        return view('clinic::index');
    }
    public function getProducts()
    {
        if (request()->ajax()) {
            $search_term = request()->input('term', '');
            $sub_type = request()->input('sub_type', null);
            $location_id = request()->input('location_id', null);
            $check_qty = request()->input('check_qty', false);
            $price_group_id = request()->input('price_group', null);
            $business_id = request()->session()->get('user.business_id');
            $not_for_selling = request()->get('not_for_selling', null);
            $price_group_id = request()->input('price_group', '');
            $product_types = request()->get('product_types', []);

            $search_fields = request()->get('search_fields', ['name', 'sku']);
            if (in_array('sku', $search_fields)) {
                $search_fields[] = 'sub_sku';
            }
            $result = $this->productUtil->filterProduct($sub_type, $business_id, $search_term, $location_id, $not_for_selling, $price_group_id, $product_types, $search_fields, $check_qty);

            return json_encode($result);
        }
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
    public function quickAdd()
    {
        if (!auth()->user()->can('product.create')) {
            abort(403, 'Unauthorized action.');
        }

        $product_name = !empty(request()->input('product_name')) ? request()->input('product_name') : '';

        $product_for = !empty(request()->input('product_for')) ? request()->input('product_for') : null;

        $business_id = request()->session()->get('user.business_id');
        $categories = Category::forDropdown($business_id, 'product');
        $brands = Brands::forDropdown($business_id);
        $units = Unit::forDropdown($business_id, true);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;

        $default_profit_percent = Business::where('id', $business_id)->value('default_profit_percent');

        $locations = BusinessLocation::forDropdown($business_id);

        $enable_expiry = request()->session()->get('business.enable_product_expiry');
        $enable_lot = request()->session()->get('business.enable_lot_number');

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);
        $foreign_cat = Category::where('is_us_product', 1)->first();

        return view('clinic::product.quick_add_product')
            ->with(compact('categories', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'product_name', 'locations', 'product_for', 'enable_expiry', 'enable_lot', 'module_form_parts', 'business_locations', 'common_settings', 'warranties', 'foreign_cat'));
    }
    public function productStockHistory($id)
    {
        if (!auth()->user()->can('view_stock_history')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        if (request()->ajax()) {

            //for ajax call $id is variation id else it is product id
            $stock_details = $this->productUtil->getVariationStockDetails($business_id, $id, request()->input('location_id'));
            $stock_history = $this->productUtil->getVariationStockHistory($business_id, $id, request()->input('location_id'));
            $product_locations = VariationLocationDetails::where('variation_id', $id)->leftjoin('business_locations as l', 'variation_location_details.location_id', '=', 'l.id')->select('variation_location_details.qty_available as stock', 'l.name as location_name')->get();
            //if mismach found update stock in variation location details
            if (isset($stock_history[0]) && (float) $stock_details['current_stock'] != (float) $stock_history[0]['stock']) {
                VariationLocationDetails::where(
                    'variation_id',
                    $id
                )
                    ->where('location_id', request()->input('location_id'))
                    ->update(['qty_available' => $stock_history[0]['stock']]);
                $stock_details['current_stock'] = $stock_history[0]['stock'];
            }

            return view('clinic::product.stock_history_details')
                ->with(compact('stock_details', 'stock_history', 'product_locations'));
        }

        $product = Product::where('business_id', $business_id)
            ->with(['variations', 'variations.product_variation'])
            ->findOrFail($id);
        $activities = Activity::forSubject($product)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();
        $priceHistory = VariationPriceHistory::where('variation_id', $id)
            ->where('type', 'product')
            ->orderBy('created_at', 'desc') // You can change the order as per your requirement
            ->get();
        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        return view('clinic::product.stock_history')
            ->with(compact('product', 'business_locations', 'priceHistory', 'activities'));
    }
    public function showBillingOptions(Request $request)
    {
        $products = DB::table('products')
            ->join('variations', 'products.id', '=', 'variations.product_id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->where('products.type', '!=', 'modifier')
            ->where('products.product_type', $request->sub_type)
            ->select('products.*', 'variations.sell_price_inc_tax as sell_price', 'categories.name as category_name')
            ->get();
        $type = $request->sub_type;
        return view('clinic::sell.partials.billing_type_option_modal', compact('products', 'type'));
    }
    public function productListInApi()
    {
        if (!auth()->user()->can('pharmacy_drug.view')) {
            abort(403, 'Unauthorized action.');
        }
        // For normal page load, get data and build filter dropdowns
        if (!request()->ajax()) {
            $products = $this->getCachedProducts();

            $brands = collect($products)->pluck('brand')->filter()->unique('id')
                ->mapWithKeys(fn($brand) => [$brand['id'] => $brand['name']])->toArray();

            $categories = collect($products)->pluck('category')->filter()->unique('id')
                ->mapWithKeys(fn($cat) => [$cat['id'] => $cat['name']])->toArray();

            return view('clinic::doctor_dashboard.api_product_list', compact('categories', 'brands'));
        }

        // For DataTables AJAX call
        $products = collect($this->getCachedProducts());

        // Apply filters
        $category = request('category');
        $brand = request('brand');
        $stock = request('stock');

        if ($category) {
            $products = $products->filter(fn($p) => isset($p['category']['id']) && $p['category']['id'] == $category);
        }

        if ($brand) {
            $products = $products->filter(fn($p) => isset($p['brand']['id']) && $p['brand']['id'] == $brand);
        }
        if ($stock == 1) {
            $products = $products->filter(fn($p) => isset($p['qty_available']) && $p['qty_available'] > 0);
        }
        if ($stock == 0) {
            $products = $products->filter(fn($p) => isset($p['qty_available']) && $p['qty_available'] == 0);
        }

        return DataTables::of($products)
            ->addColumn('name', fn($row) => $row['name'])
            ->addColumn('qty_available', fn($row) => isset($row['qty_available']) ? round($row['qty_available']) : '-')
            ->addColumn('selling_price', fn($row) => isset($row['selling_price']) ? number_format($row['selling_price'], 2) : '-')
            ->addColumn('sku', fn($row) => $row['sku'])
            ->addColumn('category', fn($row) => $row['category']['name'] ?? '-')
            ->addColumn('brand', fn($row) => $row['brand']['name'] ?? '-')
            ->make(true);
    }


    private function getCachedProducts()
    {
        return Cache::remember('api_products', 300, function () {
            $response = Http::withoutVerifying()->get('https://awc.careneterp.com:82/api/stock-products');
            if ($response->failed()) {
                return []; // fail-safe
            }
            return $response->json();
        });
    }





    public function bdDrugListInApi()
    {
        if (!auth()->user()->can('pharmacy_drug.view')) {
            abort(403, 'Unauthorized action.');
        }
        // For normal page load, get data and build filter dropdowns
        if (!request()->ajax()) {
            $drugs = $this->getCachedDrugs();

            $indications = collect($drugs)->pluck('indication')->filter()->unique('id')->mapWithKeys(fn($m) => [$m['id'] => $m['name']])->toArray();
            $manufacturers = collect($drugs)->pluck('manufacturer')->filter()->unique('id')->mapWithKeys(fn($m) => [$m['id'] => $m['name']])->toArray();
            $generics = collect($drugs)->pluck('generic')->filter()->unique('id')->mapWithKeys(fn($m) => [$m['id'] => $m['name']])->toArray();
            $drug_classes = collect($drugs)->pluck('drug_class')->filter()->unique()->mapWithKeys(fn($d) => [$d['id'] => $d['name']])->toArray();
            $dosage_forms = collect($drugs)->pluck('dosage_form')->filter()->unique('id')->mapWithKeys(fn($d) => [$d['id'] => $d['name']])->toArray();

            return view('clinic::doctor_dashboard.api_drug_list', compact(
                'indications',
                'manufacturers',
                'drug_classes',
                'generics',
                'dosage_forms'
            ));
        }

        // For DataTables AJAX call
        $drugs = collect($this->getCachedDrugs());
        // Apply filters
        $indication = request('indication');
        $manufacturer = request('manufacturer');
        $drug_class = request('drug_class');
        $generic = request('generic');
        $dosage_form = request('dosage_form');
        if ($indication) {
            $drugs = $drugs->filter(fn($p) => $p['indication']['id'] == $indication);
        }
        if ($manufacturer) {
            $drugs = $drugs->filter(fn($p) => $p['manufacturer']['id'] == $manufacturer);
        }
        
        if ($drug_class) {
            $drugs = $drugs->filter(fn($p) => $p['drug_class']['id'] == $drug_class);
        }
        if ($generic) {
            $drugs = $drugs->filter(fn($p) => $p['generic']['id'] == $generic);
        }
        if ($dosage_form) {
            $drugs = $drugs->filter(fn($p) => $p['dosage_form']['id'] == $dosage_form);
        }

        return DataTables::of($drugs)
            ->addColumn('name', fn($row) => $row['name'] . ' (' . $row['size']. ')')
            ->addColumn('category', fn($row) => $row['category']['name'] ?? '-')
            ->addColumn('indication', fn($row) => $row['indication']['name'] ?? '-')
            ->addColumn('manufacturer', fn($row) => $row['manufacturer']['name'] ?? '-')
            ->addColumn('drug_class', fn($row) => $row['drug_class']['name'] ?? '-')
            ->addColumn('generic', fn($row) => $row['generic']['name'] ?? '-')
            ->addColumn('dosage_form', fn($row) => $row['dosage_form']['name'] ?? '-')
            ->setRowAttr([
                'data-href' => fn($row) => action([\Modules\Clinic\Http\Controllers\ClinicProductController::class, 'drugInfoShow'], [$row['id']]),
                'class' => 'cursor-pointer btn-modal',
                'data-container'=>'.drug_data_show_modal'
            ])            
            ->make(true);
    }
    private function getCachedDrugs()
    {
        return Cache::remember('api_drugs', 300, function () {
            $response = Http::withoutVerifying()->get('https://awc.careneterp.com:82/api/bd-drug-data');
            if ($response->failed()) {
                return []; // fail-safe
            }
            return $response->json();
        });
    }
    public function drugInfoShow($id)
    {
        // Hitting external app API
        Log::info('drug showing id is '.$id);
        $response = Http::withoutVerifying()->get("https://awc.careneterp.com:82/api/show/drug/info/{$id}");
    
        if ($response->successful()) {
            $medicine = $response->json();
            return view('clinic::doctor_dashboard.api_drug_show', compact('medicine'));
        }
    
        abort(404, 'Medicine info not found');
    }
    public function stockExpireReport(){
        $view_stock_filter = [
            Carbon::now()->subDay()->format('Y-m-d') => __('report.expired'),
            Carbon::now()->addWeek()->format('Y-m-d') => __('report.expiring_in_1_week'),
            Carbon::now()->addDays(15)->format('Y-m-d') => __('report.expiring_in_15_days'),
            Carbon::now()->addMonth()->format('Y-m-d') => __('report.expiring_in_1_month'),
            Carbon::now()->addMonths(3)->format('Y-m-d') => __('report.expiring_in_3_months'),
            Carbon::now()->addMonths(6)->format('Y-m-d') => __('report.expiring_in_6_months'),
            Carbon::now()->addYear()->format('Y-m-d') => __('report.expiring_in_1_year'),
        ];
        
        return view('clinic::doctor_dashboard.stock_expire_report', compact('view_stock_filter'));

    }
    
}
