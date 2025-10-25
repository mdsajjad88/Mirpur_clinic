<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Brands;
use App\Category;
use App\Variation;
use App\Warranty;
use App\SellingPriceGroup;
use App\Product;
use App\TaxRate;
use App\BusinessLocation;
use App\Unit;
use App\PurchaseLine;
use App\Media;
use App\VariationLocationDetails;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use App\Events\ProductsCreatedOrModified;
use App\VariationPriceHistory;
use Spatie\Activitylog\Models\Activity;
use Modules\Clinic\Entities\DoctorProfile;
use Modules\Clinic\Entities\TestParameterHeading;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */

    protected $productUtil;

    protected $moduleUtil;

    private $barcode_types;

    public function __construct(ProductUtil $productUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->moduleUtil = $moduleUtil;

        //barcode types
        $this->barcode_types = $this->productUtil->barcode_types();
    }


    public function index()
    {
        if (!auth()->user()->can('clinic.test.view') && !auth()->user()->can('clinic.test.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);
        $is_woocommerce = $this->moduleUtil->isModuleInstalled('Woocommerce');

        if (request()->ajax()) {
            //Filter by location
            $location_id = request()->get('location_id', null);
            $permitted_locations = auth()->user()->permitted_locations();

            $query = Product::with(['media'])
                ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
                ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
                ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
                ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
                ->join('variations as v', 'v.product_id', '=', 'products.id')
                ->leftJoin('variation_location_details as vld', function ($join) use ($permitted_locations) {
                    $join->on('vld.variation_id', '=', 'v.id');
                    if ($permitted_locations != 'all') {
                        $join->whereIn('vld.location_id', $permitted_locations);
                    }
                })
                ->whereNull('v.deleted_at')
                ->where('products.business_id', $business_id)
                ->where('products.product_type', 'test');

            if (!empty($location_id) && $location_id != 'none') {
                if ($permitted_locations == 'all' || in_array($location_id, $permitted_locations)) {
                    $query->whereHas('product_locations', function ($query) use ($location_id) {
                        $query->where('product_locations.location_id', '=', $location_id)->where('vld.location_id', '=', $location_id)->addSelect('vld.qty_available as quantity');
                    });
                }
            } elseif ($location_id == 'none') {
                $query->doesntHave('product_locations');
            } else {
                if ($permitted_locations != 'all') {
                    $query->whereHas('product_locations', function ($query) use ($permitted_locations) {
                        $query->whereIn('product_locations.location_id', $permitted_locations);
                    });
                } else {
                    $query->with('product_locations');
                }
            }

            $products = $query->select(
                'products.id',
                'products.name as product',
                'products.type',
                'brands.name as brand',
                'c1.name as category',
                'c1.id as category_id',
                'c1.is_us_product as is_us_product',
                'c1.description as category_description',
                'c2.name as sub_category',
                'tax_rates.name as tax',
                'products.sku',
                'products.image',
                'products.enable_stock',
                'products.is_inactive',
                'products.not_for_selling',
                'products.product_custom_field1',
                'products.product_custom_field2',
                'products.product_custom_field3',
                'products.product_custom_field4',
                'products.product_custom_field5',
                'products.product_custom_field6',
                'products.product_custom_field7',
                'products.product_custom_field8',
                'products.product_custom_field9',
                'products.product_custom_field10',
                'products.product_custom_field11',
                'products.product_custom_field12',
                'products.product_custom_field13',
                'products.product_custom_field14',
                'products.product_custom_field15',
                'products.product_custom_field16',
                'products.product_custom_field17',
                'products.product_custom_field18',
                'products.product_custom_field19',
                'products.product_custom_field20',
                'products.alert_quantity',
                DB::raw('SUM(vld.qty_available) as current_stock'),
                DB::raw('MAX(v.sell_price_inc_tax) as max_price'),
                DB::raw('MAX(v.foreign_s_price_inc_tex) as max_foreign_s_price'),
                DB::raw('MIN(v.sell_price_inc_tax) as min_price'),
                DB::raw('MAX(v.dpp_inc_tax) as max_purchase_price'),
                DB::raw('MAX(v.foreign_p_price_inc_tex) as max_foreign_p_price'),
                DB::raw('MAX(v.currency_rate) as foreign_currency_rate'),
                DB::raw('MIN(v.dpp_inc_tax) as min_purchase_price'),
                DB::raw('(v.sell_price_inc_tax - v.dpp_inc_tax) as profit_per_unit'),
                DB::raw('((v.sell_price_inc_tax - v.dpp_inc_tax) / v.sell_price_inc_tax * 100) as profit_margin_percentage')
            );

            //if woocomerce enabled add field to query
            if ($is_woocommerce) {
                $products->addSelect('woocommerce_disable_sync');
            }

            $products->groupBy('products.id');

            $types = request()->get('type', null);
            if (!empty($types)) {
                $products->whereIn('products.type', $types);
            }

            $category_ids = request()->get('category_id', null);
            if (!empty($category_ids)) {
                $products->whereIn('products.category_id', $category_ids);
            }

            $brand_ids = request()->get('brand_id', null);
            if (!empty($brand_ids)) {
                $products->whereIn('products.brand_id', $brand_ids);
            }

            $unit_ids = request()->get('unit_id', null);
            if (!empty($unit_ids)) {
                $products->whereIn('products.unit_id', $unit_ids);
            }

            $tax_ids = request()->get('tax_id', null);
            if (!empty($tax_ids)) {
                $products->whereIn('products.tax', $tax_ids);
            }

            $active_state = request()->get('active_state', null);
            if ($active_state == 'active') {
                $products->Active();
            }
            if ($active_state == 'inactive') {
                $products->Inactive();
            }
            // $not_for_selling = request()->get('not_for_selling', null);
            // if ($not_for_selling == 'true') {
            //     $products->ProductNotForSales();
            // }

            $woocommerce_enabled = request()->get('woocommerce_enabled', 0);
            if ($woocommerce_enabled == 1) {
                $products->where('products.woocommerce_disable_sync', 0);
            }

            if (!empty(request()->get('repair_model_id'))) {
                $products->where('products.repair_model_id', request()->get('repair_model_id'));
            }

            $stock_status = request()->get('stock_status', null);
            if (!empty($stock_status)) {
                if ($stock_status == 'in_stock') {
                    $products->having(DB::raw('SUM(vld.qty_available)'), '>', 0);
                } elseif ($stock_status == 'out_of_stock') {
                    $products->having(DB::raw('SUM(vld.qty_available)'), '<=', 0);
                }
            }
            if (!request()->show_with_modifier) {
                $query->where('products.type', '!=', 'modifier');
            }
            if (request()->show_with_modifier) {
                $query->whereIn('products.type', ['modifier', 'single']);
            }
            return Datatables::of($products)
                ->addColumn(
                    'product_locations',
                    function ($row) {
                        return $row->product_locations->implode('name', ', ');
                    }
                )
                ->editColumn('category', '{{$category}} @if(!empty($sub_category))<br/> -- {{$sub_category}}@endif')
                ->addColumn(
                    'action',
                    function ($row) use ($selling_price_group_count) {
                        $html =
                            '<div class="btn-group"><button type="button" class="btn btn-info dropdown-toggle btn-xs" data-toggle="dropdown" aria-expanded="false">' . __('messages.actions') . '<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu dropdown-menu-left" role="menu">';

                        if (auth()->user()->can('clinic.test.view')) {
                            $html .=
                                '<li><a href="' . action([\Modules\Clinic\Http\Controllers\TestController::class, 'view'], [$row->id]) . '" class="view-product"><i class="fa fa-eye"></i> ' . __('messages.view') . '</a></li>';
                        }

                        if (auth()->user()->can('clinic.test.edit')) {
                            $html .=
                                '<li><a href="' . action([\Modules\Clinic\Http\Controllers\TestController::class, 'edit'], [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __('messages.edit') . '</a></li>';
                        }

                        if (auth()->user()->can('clinic.test.delete')) {
                            $html .=
                                '<li><a href="' . action([\Modules\Clinic\Http\Controllers\TestController::class, 'destroy'], [$row->id]) . '" class="delete-product"><i class="fa fa-trash"></i> ' . __('messages.delete') . '</a></li>';
                        }

                        if ($row->is_inactive == 1) {
                            $html .=
                                '<li><a href="' . action([\Modules\Clinic\Http\Controllers\TestController::class, 'activate'], [$row->id]) . '" class="activate-product"><i class="fas fa-check-circle"></i> ' . __('lang_v1.reactivate') . '</a></li>';
                        }

                        $html .= '<li class="divider"></li>';
                        if (auth()->user()->can('clinic.test.stock_history')) {
                            $html .=
                                '<li><a href="' . action([\Modules\Clinic\Http\Controllers\ClinicProductController::class, 'productStockHistory'], [$row->id]) . '"><i class="fas fa-history"></i> ' . __('lang_v1.product_stock_history') . '</a></li>';
                        }
                        if (auth()->user()->can('clinic.test.add_group_price')) {
                            if ($selling_price_group_count > 0) {
                                $html .=
                                    '<li><a href="' . action([\Modules\Clinic\Http\Controllers\TestController::class, 'addSellingPrices'], [$row->id]) . '"><i class="fas fa-money-bill-alt"></i> ' . __('lang_v1.add_selling_price_group_prices') . '</a></li>';
                            }

                            $html .=
                                '<li><a href="' . action([\Modules\Clinic\Http\Controllers\TestController::class, 'create'], ['d' => $row->id]) . '"><i class="fa fa-copy"></i> ' . __('lang_v1.duplicate_product') . '</a></li>';
                        }

                        if (!empty($row->media->first())) {
                            $html .=
                                '<li><a href="' . $row->media->first()->display_url . '" download="' . $row->media->first()->display_name . '"><i class="fas fa-download"></i> ' . __('lang_v1.product_brochure') . '</a></li>';
                        }

                        $html .= '</ul></div>';

                        return $html;
                    }
                )
                ->editColumn('product', function ($row) use ($is_woocommerce) {
                    $product = $row->is_inactive == 1 ? $row->product . ' <span class="label bg-gray">' . __('lang_v1.inactive') . '</span>' : $row->product;

                    $product = $row->not_for_selling == 1 ? $product . ' <span class="label bg-gray">' . __('lang_v1.not_for_selling') .
                        '</span>' : $product;

                    if ($is_woocommerce && !$row->woocommerce_disable_sync) {
                        $product = $product . '<br><i class="fab fa-wordpress"></i>';
                    }

                    return $product;
                })
                ->editColumn('image', function ($row) {
                    return '<div style="display: flex;"><img src="' . $row->image_url . '" alt="Product image" class="product-thumbnail-small"></div>';
                })
                ->editColumn('type', '@lang("lang_v1." . $type)')
                ->addColumn('mass_delete', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id . '">';
                })
                ->editColumn('current_stock', function ($row) {
                    if ($row->enable_stock) {
                        if ($row->quantity) {
                            $stock = $this->productUtil->num_f($row->quantity, false, null, true);
                        } else {
                            $stock = $this->productUtil->num_f($row->current_stock, false, null, true);
                        }

                        return $stock . ' ' . $row->unit;
                    } else {
                        return '--';
                    }
                })
                ->addColumn(
                    'purchase_price',
                    '<div style="white-space: nowrap;">@if($is_us_product == 1) 
                     $ {{number_format($max_foreign_p_price, 2)}} </br>
                     ৳ {{number_format($max_purchase_price, 2)}} <br>
                     $&#8644;৳ {{number_format($foreign_currency_rate, 2)}}
                      @else
                       @format_currency($min_purchase_price) @if($max_purchase_price != $min_purchase_price && $type == "variable") -  @format_currency($max_purchase_price)@endif </div>  
                       @endif <span></span>
                     '
                )
                ->addColumn(
                    'selling_price',
                    '<div style="white-space: nowrap;">@if($is_us_product == 1)
                     $ {{number_format($max_foreign_s_price, 2)}} </br>
                     ৳ {{number_format($min_price, 2)}} <br>
                     $&#8644;৳ {{number_format($foreign_currency_rate, 2)}}
                     @else
                      @format_currency($min_price) @if($max_price != $min_price && $type == "variable") -  @format_currency($max_price)@endif </div> @endif '
                )
                ->addColumn('profit_margin', function ($row) {
                    return number_format($row->profit_margin_percentage, 2) . '%';
                })
                ->filterColumn('products.sku', function ($query, $keyword) {
                    $query->whereHas('variations', function ($q) use ($keyword) {
                        $q->where('sub_sku', 'like', "%{$keyword}%");
                    })
                        ->orWhere('products.sku', 'like', "%{$keyword}%");
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        if (auth()->user()->can('clinic.test.view')) {
                            return  action([\Modules\Clinic\Http\Controllers\TestController::class, 'view'], [$row->id]);
                        } else {
                            return '';
                        }
                    },
                ])->orderColumn('profit_margin', 'profit_margin_percentage $1')
                ->rawColumns(['profit_margin', 'action', 'image', 'mass_delete', 'product', 'selling_price', 'purchase_price', 'category', 'current_stock'])
                ->make(true);
        }

        $rack_enabled = (request()->session()->get('business.enable_racks') || request()->session()->get('business.enable_row') || request()->session()->get('business.enable_position'));

        $categories = Category::forDropdown($business_id, 'test');

        $sub_type = 'test';
        $brands = Brands::forDropdownWithSubType($business_id, false, false, false, $sub_type);

        $units = Unit::forDropdown($business_id);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, false);
        $taxes = $tax_dropdown['tax_rates'];

        $business_locations = BusinessLocation::forDropdown($business_id);
        $business_locations->prepend(__('lang_v1.none'), 'none');

        $pos_module_data = $this->moduleUtil->getModuleData('get_filters_for_list_product_screen');

        $is_admin = $this->productUtil->is_admin(auth()->user());

        return view('clinic::test.index')
            ->with(compact(
                'rack_enabled',
                'categories',
                'brands',
                'units',
                'taxes',
                'business_locations',
                'pos_module_data',
                'is_woocommerce',
                'is_admin'
            ));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (!auth()->user()->can('clinic.test.create')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not, then check for products quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        } elseif (!$this->moduleUtil->isQuotaAvailable('products', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('products', $business_id, action([\App\Http\Controllers\ProductController::class, 'index']));
        }

        $categories = Category::forDropdown($business_id, 'test');
        $sub_type = 'test';

        // Correct function call with all parameters in the right order
        $brands = Brands::forDropdownWithSubType($business_id, false, false, false, $sub_type);
        $units = Unit::forDropdown($business_id, true);

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];

        $barcode_types = $this->barcode_types;
        $barcode_default = $this->productUtil->barcode_default();

        $default_profit_percent = request()->session()->get('business.default_profit_percent');

        //Get all business locations
        $business_locations = BusinessLocation::forDropdown($business_id);

        //Duplicate product
        $duplicate_product = null;
        $rack_details = null;

        $sub_categories = [];
        if (!empty(request()->input('d'))) {
            $duplicate_product = Product::where('business_id', $business_id)->find(request()->input('d'));
            $duplicate_product->name .= ' (copy)';

            if (!empty($duplicate_product->category_id)) {
                $sub_categories = Category::where('business_id', $business_id)
                    ->where('parent_id', $duplicate_product->category_id)
                    ->pluck('name', 'id')
                    ->toArray();
            }

            //Rack details
            if (!empty($duplicate_product->id)) {
                $rack_details = $this->productUtil->getRackDetails($business_id, $duplicate_product->id);
            }
        }

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');
        $product_types = $this->product_types();


        $common_settings = session()->get('business.common_settings');

        $warranties = Warranty::forDropdown($business_id);

        //product screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_product_screen_top_view');
        $variation = Variation::get();
        $foreign_cat = Category::where('is_us_product', 1)->first();
        $doctors = DoctorProfile::all();
        return view('clinic::test.create')
            ->with(compact('categories', 'brands', 'units', 'taxes', 'barcode_types', 'default_profit_percent', 'tax_attributes', 'barcode_default', 'business_locations', 'duplicate_product', 'sub_categories', 'rack_details', 'selling_price_group_count', 'module_form_parts', 'product_types', 'common_settings', 'warranties', 'pos_module_data', 'variation', 'foreign_cat'));
    }
    private function product_types()
    {
        //Product types also includes modifier.
        return [
            'single' => __('lang_v1.single'),
            'variable' => __('lang_v1.variable'),
            'combo' => __('lang_v1.combo'),
        ];
    }
    public function addSellingPrices($id)
    {
        if (!auth()->user()->can('clinic.test.add_group_price') && !auth()->user()->can('ipd.add_group_price') && !auth()->user()->can('consultation.add_group_price')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $product = Product::where('business_id', $business_id)
            ->with(['variations', 'variations.group_prices', 'variations.product_variation'])
            ->findOrFail($id);
        $price_groups = SellingPriceGroup::where('business_id', $business_id)
            ->active()
            ->get();
        $variation_prices = [];
        foreach ($product->variations as $variation) {
            foreach ($variation->group_prices as $group_price) {
                $variation_prices[$variation->id][$group_price->price_group_id] = ['price' => $group_price->price_inc_tax, 'price_type' => $group_price->price_type];
            }
        }
        return view('clinic::test.add-selling-prices')->with(compact('product', 'price_groups', 'variation_prices'));
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
    public function view($id)
    {
        if (!auth()->user()->can('clinic.test.view')) {
            abort(403, 'Unauthorized action.');
        }
        // $variation = Variation::where('product_id', $product->id)->get();
        $PriceHistory = VariationPriceHistory::where('variation_id', $id)
            ->where('type', 'product')
            ->orderBy('created_at', 'desc')
            ->get();
        try {
            $business_id = request()->session()->get('user.business_id');

            $product = Product::where('business_id', $business_id)
                ->with(['brand', 'unit', 'category', 'sub_category', 'product_tax', 'variations', 'variations.product_variation', 'variations.group_prices', 'variations.media', 'product_locations', 'warranty', 'media', 'parameters'])
                ->findOrFail($id);

            $price_groups = SellingPriceGroup::where('business_id', $business_id)->active()->pluck('name', 'id');

            $allowed_group_prices = [];
            foreach ($price_groups as $key => $value) {
                if (auth()->user()->can('selling_price_group.' . $key)) {
                    $allowed_group_prices[$key] = $value;
                }
            }

            $group_price_details = [];

            foreach ($product->variations as $variation) {
                foreach ($variation->group_prices as $group_price) {
                    $group_price_details[$variation->id][$group_price->price_group_id] = ['price' => $group_price->price_inc_tax, 'price_type' => $group_price->price_type, 'calculated_price' => $group_price->calculated_price];
                }
            }

            $rack_details = $this->productUtil->getRackDetails($business_id, $id, true);

            $combo_variations = [];
            if ($product->type == 'combo') {
                $combo_variations = $this->productUtil->__getComboProductDetails($product['variations'][0]->combo_variations, $business_id);
            }

            $activities = Activity::forSubject($product)
                ->with(['causer', 'subject'])
                ->latest()
                ->get();

            return view('clinic::test.view-modal')->with(compact(
                'product',
                'PriceHistory',
                'rack_details',
                'allowed_group_prices',
                'group_price_details',
                'combo_variations',
                'activities'
            ));
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
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
    {
        if (!auth()->user()->can('clinic.test.edit')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $categories = Category::forDropdown($business_id, 'test');

        $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);
        $taxes = $tax_dropdown['tax_rates'];
        $tax_attributes = $tax_dropdown['attributes'];
        $sub_type = 'test';
        $brands = Brands::forDropdownWithSubType($business_id, false, false, false, $sub_type);
        $barcode_types = $this->barcode_types;
        $product = Product::where('business_id', $business_id)
            ->with(['product_locations', 'parameters'])
            ->where('id', $id)
            ->firstOrFail();
        $sub_categories = [];
        $sub_categories = Category::where('business_id', $business_id)
            ->where('parent_id', $product->category_id)
            ->pluck('name', 'id')
            ->toArray();
        $sub_categories = ['' => 'None'] + $sub_categories;
        $default_profit_percent = request()->session()->get('business.default_profit_percent');

        $business_locations = BusinessLocation::forDropdown($business_id);

        $rack_details = $this->productUtil->getRackDetails($business_id, $id);

        $selling_price_group_count = SellingPriceGroup::countSellingPriceGroups($business_id);

        $module_form_parts = $this->moduleUtil->getModuleData('product_form_part');
        $product_types = $this->product_types();
        $common_settings = session()->get('business.common_settings');
        $warranties = Warranty::forDropdown($business_id);

        //product screen view from module
        $pos_module_data = $this->moduleUtil->getModuleData('get_product_screen_top_view');

        $alert_quantity = !is_null($product->alert_quantity) ? $this->productUtil->num_f($product->alert_quantity, false, null, true) : null;

        return view('clinic::test.edit')
            ->with(compact('categories', 'sub_categories', 'taxes', 'tax_attributes', 'barcode_types', 'product', 'default_profit_percent', 'business_locations', 'rack_details', 'selling_price_group_count', 'module_form_parts', 'product_types', 'common_settings', 'warranties', 'pos_module_data', 'alert_quantity', 'brands'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('clinic.test.edit') && !auth()->user()->can('doctor.consultation.update') && !auth()->user()->can('ipd.update')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $business_id = $request->session()->get('user.business_id');
            $product_details = $request->only(['name', 'category_id', 'tax', 'barcode_type', 'sku', 'alert_quantity', 'tax_type', 'weight', 'product_description', 'preparation_time_in_minutes', 'product_custom_field1', 'product_custom_field2', 'product_custom_field3', 'product_custom_field4', 'product_custom_field5', 'product_custom_field6', 'product_custom_field7', 'product_custom_field8', 'product_custom_field9', 'product_custom_field10', 'product_custom_field11', 'product_custom_field12', 'product_custom_field13', 'product_custom_field14', 'product_custom_field15', 'product_custom_field16', 'product_custom_field17', 'product_custom_field18', 'product_custom_field19', 'product_custom_field20', 'brand_id']);

            DB::beginTransaction();

            $product = Product::where('business_id', $business_id)
                ->where('id', $id)
                ->with(['product_variations'])
                ->first();

            $module_form_fields = $this->moduleUtil->getModuleFormField('product_form_fields');
            if (!empty($module_form_fields)) {
                foreach ($module_form_fields as $column) {
                    $product->$column = $request->input($column);
                }
            }
            $product->name = $product_details['name'];
            $product->category_id = $product_details['category_id'];
            $product->brand_id = $product_details['brand_id'];
            $product->tax = $product_details['tax'];
            $product->barcode_type = $product_details['barcode_type'];
            $product->sku = $product_details['sku'];
            $product->alert_quantity = !empty($product_details['alert_quantity']) ? $this->productUtil->num_uf($product_details['alert_quantity']) : $product_details['alert_quantity'];
            $product->tax_type = $product_details['tax_type'];
            $product->weight = $product_details['weight'];
            $product->product_custom_field1 = $product_details['product_custom_field1'] ?? '';
            $product->product_custom_field2 = $product_details['product_custom_field2'] ?? '';
            $product->product_custom_field3 = $product_details['product_custom_field3'] ?? '';
            $product->product_custom_field4 = $product_details['product_custom_field4'] ?? '';
            $product->product_custom_field5 = $product_details['product_custom_field5'] ?? '';
            $product->product_custom_field6 = $product_details['product_custom_field6'] ?? '';
            $product->product_custom_field7 = $product_details['product_custom_field7'] ?? '';
            $product->product_custom_field8 = $product_details['product_custom_field8'] ?? '';
            $product->product_custom_field9 = $product_details['product_custom_field9'] ?? '';
            $product->product_custom_field10 = $product_details['product_custom_field10'] ?? '';
            $product->product_custom_field11 = $product_details['product_custom_field11'] ?? '';
            $product->product_custom_field12 = $product_details['product_custom_field12'] ?? '';
            $product->product_custom_field13 = $product_details['product_custom_field13'] ?? '';
            $product->product_custom_field14 = $product_details['product_custom_field14'] ?? '';
            $product->product_custom_field15 = $product_details['product_custom_field15'] ?? '';
            $product->product_custom_field16 = $product_details['product_custom_field16'] ?? '';
            $product->product_custom_field17 = $product_details['product_custom_field17'] ?? '';
            $product->product_custom_field18 = $product_details['product_custom_field18'] ?? '';
            $product->product_custom_field19 = $product_details['product_custom_field19'] ?? '';
            $product->product_custom_field20 = $product_details['product_custom_field20'] ?? '';

            $product->product_description = $product_details['product_description'];
            $product->preparation_time_in_minutes = $product_details['preparation_time_in_minutes'];
            $product->warranty_id = !empty($request->input('warranty_id')) ? $request->input('warranty_id') : null;
            $product->secondary_unit_id = !empty($request->input('secondary_unit_id')) ? $request->input('secondary_unit_id') : null;

            if (!empty($request->input('enable_stock')) && $request->input('enable_stock') == 1) {
                $product->enable_stock = 1;
            } else {
                $product->enable_stock = 0;
            }

            $product->not_for_selling = (!empty($request->input('not_for_selling')) && $request->input('not_for_selling') == 1) ? 1 : 0;

            if (!empty($request->input('sub_category_id'))) {
                $product->sub_category_id = $request->input('sub_category_id');
            } else {
                $product->sub_category_id = null;
            }

            $expiry_enabled = $request->session()->get('business.enable_product_expiry');
            if (!empty($expiry_enabled)) {
                if (!empty($request->input('expiry_period_type')) && !empty($request->input('expiry_period')) && ($product->enable_stock == 1)) {
                    $product->expiry_period_type = $request->input('expiry_period_type');
                    $product->expiry_period = $this->productUtil->num_uf($request->input('expiry_period'));
                } else {
                    $product->expiry_period_type = null;
                    $product->expiry_period = null;
                }
            }

            if (!empty($request->input('enable_sr_no')) && $request->input('enable_sr_no') == 1) {
                $product->enable_sr_no = 1;
            } else {
                $product->enable_sr_no = 0;
            }

            //upload document
            $file_name = $this->productUtil->uploadFile($request, 'image', config('constants.product_img_path'), 'image');
            if (!empty($file_name)) {

                //If previous image found then remove
                if (!empty($product->image_path) && file_exists($product->image_path)) {
                    unlink($product->image_path);
                }

                $product->image = $file_name;
                //If product image is updated update woocommerce media id
                if (!empty($product->woocommerce_media_id)) {
                    $product->woocommerce_media_id = null;
                }
            }

            $product->save();
            $product->touch();
            $removedIds = explode(',', $request->input('removed_parameter_ids', ''));
            if (!empty($removedIds)) {
                TestParameterHeading::whereIn('id', $removedIds)->delete();
            }

            // ২. Existing/new parameters update
            $names = $request->input('parameter_name', []);
            $descriptions = $request->input('parameter_description', []);
            $paramIds = $request->input('parameter_id', []);

            foreach ($names as $index => $name) {
                if (!empty($name)) {
                    if (!empty($paramIds[$index])) {
                        // update existing
                        $param = TestParameterHeading::find($paramIds[$index]);
                        if ($param) {
                            $param->update([
                                'name' => $name,
                                'description' => $descriptions[$index] ?? null,
                                'reference_value' => $request->input('reference_value')[$index] ?? null,
                                'unit' => $request->input('unit')[$index] ?? null
                            ]);
                        }
                    } else {
                        // new parameter create
                        TestParameterHeading::create([
                            'test_id' => $product->id,
                            'name' => $name,
                            'description' => $descriptions[$index] ?? null,
                            'reference_value' => $request->input('reference_value')[$index] ?? null,
                            'unit' => $request->input('unit')[$index] ?? null
                        ]);
                    }
                }
            }

            $this->productUtil->activityLog($product, 'edited');

            event(new ProductsCreatedOrModified($product, 'updated'));

            //Add product locations
            $product_locations = !empty($request->input('product_locations')) ?
                $request->input('product_locations') : [];

            $permitted_locations = auth()->user()->permitted_locations();
            //If not assigned location exists don't remove it
            if ($permitted_locations != 'all') {
                $existing_product_locations = $product->product_locations()->pluck('id');

                foreach ($existing_product_locations as $pl) {
                    if (!in_array($pl, $permitted_locations)) {
                        $product_locations[] = $pl;
                    }
                }
            }

            $product->product_locations()->sync($product_locations);
            $foreign_cat = Category::where('is_us_product', 1)->first();

            if ($product->type == 'single' && $product->category_id ==  (isset($foreign_cat) ? $foreign_cat->id : null)) {

                $single_data = $request->only(['single_variation_id', 'single_dpp', 'single_dpp_inc_tax', 'single_dsp_inc_tax', 'profit_percent', 'single_dsp']);
                $variation = Variation::find($single_data['single_variation_id']);

                $variation->sub_sku = $product->sku;
                $variation->default_purchase_price = $this->productUtil->num_uf($single_data['single_dpp']) * $foreign_cat->description;
                $variation->dpp_inc_tax = $this->productUtil->num_uf($single_data['single_dpp_inc_tax']) * $foreign_cat->description;
                $variation->profit_percent = $this->productUtil->num_uf($single_data['profit_percent']);
                $variation->default_sell_price = round(($this->productUtil->num_uf($single_data['single_dsp']) * $foreign_cat->description) / 10) * 10;
                $variation->sell_price_inc_tax = round(($this->productUtil->num_uf($single_data['single_dsp_inc_tax']) * $foreign_cat->description) / 10) * 10;

                $variation->foreign_p_price = $this->productUtil->num_uf($single_data['single_dpp']);
                $variation->foreign_p_price_inc_tex = $this->productUtil->num_uf($single_data['single_dpp_inc_tax']);
                $variation->currency_code = $foreign_cat->short_code;
                $variation->currency_rate = $foreign_cat->description;
                $variation->is_foreign = 1;
                $variation->foreign_s_price = $this->productUtil->num_uf($single_data['single_dsp']);
                $variation->foreign_s_price_inc_tex = $this->productUtil->num_uf($single_data['single_dsp_inc_tax']);

                $variation_history = Variation::find($single_data['single_variation_id']);
                $oldPrice = $this->productUtil->num_uf($single_data['single_dpp_inc_tax']) * $foreign_cat->description;
                $newPrice = round(($this->productUtil->num_uf($single_data['single_dsp_inc_tax']) * $foreign_cat->description) / 10) * 10;
                $userId = auth()->id();

                // Update the variation's price
                if ($variation_history->sell_price_inc_tax != $newPrice || $variation_history->dpp_inc_tax != $oldPrice) {
                    // Create a new price history entry

                    $variationId = $id;
                    $old_price = '$ ' . number_format($variation->foreign_p_price_inc_tex, 2) . '<br>৳ ' . number_format($oldPrice, 2) . '<br>$⇄৳ ' . number_format($foreign_cat->description, 2);
                    $new_price = '$ ' . number_format($variation->foreign_s_price_inc_tex, 2) . '<br>৳ ' . number_format($newPrice, 2) . '<br>$⇄৳ ' . number_format($foreign_cat->description, 2);
                    $type = 'product';
                    $h_type = 'Edited';

                    $this->productUtil->variationPriceHistory($variationId, $old_price, $new_price, $type, $h_type);
                }

                $variation->save();

                Media::uploadMedia($product->business_id, $variation, $request, 'variation_images');
            } elseif ($product->type == 'single') {
                $single_data = $request->only(['single_variation_id', 'single_dpp', 'single_dpp_inc_tax', 'single_dsp_inc_tax', 'profit_percent', 'single_dsp']);
                $variation = Variation::find($single_data['single_variation_id']);

                $variation->sub_sku = $product->sku;
                $variation->default_purchase_price = $this->productUtil->num_uf($single_data['single_dpp']);
                $variation->dpp_inc_tax = $this->productUtil->num_uf($single_data['single_dpp_inc_tax']);
                $variation->profit_percent = $this->productUtil->num_uf($single_data['profit_percent']);
                $variation->default_sell_price = $this->productUtil->num_uf($single_data['single_dsp']);
                $variation->sell_price_inc_tax = $this->productUtil->num_uf($single_data['single_dsp_inc_tax']);

                $variation_history = Variation::find($single_data['single_variation_id']);
                $oldPrice = $this->productUtil->num_uf($single_data['single_dpp_inc_tax']);
                $newPrice = $this->productUtil->num_uf($single_data['single_dsp_inc_tax']);
                $userId = auth()->id();

                // Update the variation's price
                if ($variation_history->sell_price_inc_tax != $newPrice || $variation_history->dpp_inc_tax != $oldPrice) {
                    // Create a new price history entry
                    $variationId = $id;
                    $old_price = '৳ ' . $oldPrice;
                    $new_price = '৳ ' . $newPrice;
                    $type = 'product';
                    $h_type = 'Edited';
                    $this->productUtil->variationPriceHistory($variationId, $old_price, $new_price, $type, $h_type);
                }

                $variation->save();

                Media::uploadMedia($product->business_id, $variation, $request, 'variation_images');
            } elseif ($product->type == 'variable') {
                //Update existing variations
                $input_variations_edit = $request->get('product_variation_edit');
                if (!empty($input_variations_edit)) {
                    $this->productUtil->updateVariableProductVariations($product->id, $input_variations_edit);
                }

                //Add new variations created.
                $input_variations = $request->input('product_variation');
                if (!empty($input_variations)) {
                    $this->productUtil->createVariableProductVariations($product->id, $input_variations);
                }
            } elseif ($product->type == 'combo') {

                //Create combo_variations array by combining variation_id and quantity.
                $combo_variations = [];
                if (!empty($request->input('composition_variation_id'))) {
                    $composition_variation_id = $request->input('composition_variation_id');
                    $quantity = $request->input('quantity');
                    $unit = $request->input('unit');

                    foreach ($composition_variation_id as $key => $value) {
                        $combo_variations[] = [
                            'variation_id' => $value,
                            'quantity' => $quantity[$key],
                            'unit_id' => $unit[$key],
                        ];
                    }
                }

                $combo_variation_id = $request->input('combo_variation_id');
                $variation = Variation::find($combo_variation_id);
                
                if (!$variation) {
                    throw new \Exception("Variation not found with ID: {$combo_variation_id}");
                }

                $variation->sub_sku = $product->sku;
                $variation->default_purchase_price = $this->productUtil->num_uf($request->input('item_level_purchase_price_total'));
                $variation->dpp_inc_tax = $this->productUtil->num_uf($request->input('purchase_price_inc_tax'));
                $variation->profit_percent = $this->productUtil->num_uf($request->input('profit_percent'));
                $variation->default_sell_price = $this->productUtil->num_uf($request->input('selling_price'));
                $variation->sell_price_inc_tax = $this->productUtil->num_uf($request->input('selling_price_inc_tax'));

                // Get old prices before saving
                $oldPurchasePrice = $variation->dpp_inc_tax;
                $oldSellPrice = $variation->sell_price_inc_tax;

                $variation->combo_variations = $combo_variations;
                $variation->save();

                // Check if prices changed and create price history
                $newPurchasePrice = $this->productUtil->num_uf($request->input('purchase_price_inc_tax'));
                $newSellPrice = $this->productUtil->num_uf($request->input('selling_price_inc_tax'));

                if ($oldPurchasePrice != $newPurchasePrice || $oldSellPrice != $newSellPrice) {
                    $variationId = $combo_variation_id;
                    $old_price = 'Purchase: ৳ ' . number_format($oldPurchasePrice, 2) . ', Sell: ৳ ' . number_format($oldSellPrice, 2);
                    $new_price = 'Purchase: ৳ ' . number_format($newPurchasePrice, 2) . ', Sell: ৳ ' . number_format($newSellPrice, 2);
                    $type = 'combo';
                    $h_type = 'Edited';

                    $this->productUtil->variationPriceHistory($variationId, $old_price, $new_price, $type, $h_type);
                }
            }

            //Add product racks details.
            $product_racks = $request->get('product_racks', null);
            if (!empty($product_racks)) {
                $this->productUtil->addRackDetails($business_id, $product->id, $product_racks);
            }

            $product_racks_update = $request->get('product_racks_update', null);
            if (!empty($product_racks_update)) {
                $this->productUtil->updateRackDetails($business_id, $product->id, $product_racks_update);
            }

            //Set Module fields
            if (!empty($request->input('has_module_data'))) {
                $this->moduleUtil->getModuleData('after_product_saved', ['product' => $product, 'request' => $request]);
            }

            Media::uploadMedia($product->business_id, $product, $request, 'product_brochure', true);

            DB::commit();
            $output = [
                'success' => 1,
                'msg' => __('product.product_updated_success'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => 0,
                'msg' => $e->getMessage(),
            ];
        }

        if ($request->input('submit_type') == 'update_n_edit_opening_stock') {
            return redirect()->action(
                [\App\Http\Controllers\OpeningStockController::class, 'add'],
                ['product_id' => $product->id]
            );
        } elseif ($request->input('submit_type') == 'submit_n_add_selling_prices') {
            return redirect()->action(
                [\App\Http\Controllers\ProductController::class, 'addSellingPrices'],
                [$product->id]
            );
        } elseif ($request->input('submit_type') == 'save_n_add_another') {
            return redirect()->action(
                [\Modules\Clinic\Http\Controllers\TestController::class, 'create']
            )->with('status', $output);
        } elseif ($request->input('product_type') == 'consultation') {
            return redirect()->action(
                [\Modules\Clinic\Http\Controllers\DoctorConsultationController::class, 'index']
            )->with('status', $output);
        } elseif ($request->input('product_type') == 'ipd') {
            return redirect()->action(
                [\Modules\Clinic\Http\Controllers\IPDController::class, 'index']
            )->with('status', $output);
        }

        return redirect('clinic-test')->with('status', $output);
    }


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('clinic.test.delete') && !auth()->user()->can('ipd.delete') && !auth()->user()->can('doctor.consultation.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $can_be_deleted = true;
                $error_msg = '';

                //Check if any purchase or transfer exists
                $count = PurchaseLine::join(
                    'transactions as T',
                    'purchase_lines.transaction_id',
                    '=',
                    'T.id'
                )
                    ->whereIn('T.type', ['purchase'])
                    ->where('T.business_id', $business_id)
                    ->where('purchase_lines.product_id', $id)
                    ->count();
                if ($count > 0) {
                    $can_be_deleted = false;
                    $error_msg = __('lang_v1.purchase_already_exist');
                } else {
                    //Check if any opening stock sold
                    $count = PurchaseLine::join(
                        'transactions as T',
                        'purchase_lines.transaction_id',
                        '=',
                        'T.id'
                    )
                        ->where('T.type', 'opening_stock')
                        ->where('T.business_id', $business_id)
                        ->where('purchase_lines.product_id', $id)
                        ->where('purchase_lines.quantity_sold', '>', 0)
                        ->count();
                    if ($count > 0) {
                        $can_be_deleted = false;
                        $error_msg = __('lang_v1.opening_stock_sold');
                    } else {
                        //Check if any stock is adjusted
                        $count = PurchaseLine::join(
                            'transactions as T',
                            'purchase_lines.transaction_id',
                            '=',
                            'T.id'
                        )
                            ->where('T.business_id', $business_id)
                            ->where('purchase_lines.product_id', $id)
                            ->where('purchase_lines.quantity_adjusted', '>', 0)
                            ->count();
                        if ($count > 0) {
                            $can_be_deleted = false;
                            $error_msg = __('lang_v1.stock_adjusted');
                        }
                    }
                }

                $product = Product::where('id', $id)
                    ->where('business_id', $business_id)
                    ->with('variations')
                    ->first();

                //Check if product is added as an ingredient of any recipe
                if ($this->moduleUtil->isModuleInstalled('Manufacturing')) {
                    $variation_ids = $product->variations->pluck('id');

                    $exists_as_ingredient = \Modules\Manufacturing\Entities\MfgRecipeIngredient::whereIn('variation_id', $variation_ids)
                        ->exists();
                    if ($exists_as_ingredient) {
                        $can_be_deleted = false;
                        $error_msg = __('manufacturing::lang.added_as_ingredient');
                    }
                }

                if ($can_be_deleted) {
                    if (!empty($product)) {
                        DB::beginTransaction();
                        //Delete variation location details
                        VariationLocationDetails::where('product_id', $id)
                            ->delete();
                        $product->delete();
                        event(new ProductsCreatedOrModified($product, 'deleted'));
                        DB::commit();
                        VariationPriceHistory::where('variation_id', $id)->delete();
                    }

                    $output = [
                        'success' => true,
                        'msg' => __('lang_v1.product_delete_success'),
                    ];
                } else {
                    $output = [
                        'success' => false,
                        'msg' => $error_msg,
                    ];
                }
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }
    public function activate($id)
    {
        if (!auth()->user()->can('clinic.test.edit') && !auth()->user()->can('doctor.consultation.update') && !auth()->user()->can('ipd.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');
                $product = Product::where('id', $id)
                    ->where('business_id', $business_id)
                    ->update(['is_inactive' => 0]);

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.updated_success'),
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
}
