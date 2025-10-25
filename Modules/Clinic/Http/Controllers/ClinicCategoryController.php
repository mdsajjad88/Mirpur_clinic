<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Routing\Controller;
use App\Category;
use App\Utils\ModuleUtil;
use App\Variation;
use App\VariationPriceHistory;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ClinicCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $moduleUtil;

    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {
        if(!auth()->user()->can('clinic.category.view') && !auth()->user()->can('clinic.category.create')){
            abort(403, 'Unauthorized action.');

        }
        $category_type = request()->get('type');
        if ($category_type == ['test', 'therapy', 'consultation', 'ipd'] && !auth()->user()->can('clinic.category.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $can_edit = true;
            if ($category_type == ['test', 'therapy', 'consultation', 'ipd'] && !auth()->user()->can('clinic.category.edit')) {
                $can_edit = false;
            }

            $can_delete = true;
            if ($category_type == ['test', 'therapy', 'consultation', 'ipd'] && !auth()->user()->can('clinic.category.delete')) {
                $can_delete = false;
            }

            $business_id = request()->session()->get('user.business_id');

            $category = Category::where('business_id', $business_id)
                ->where('category_type', $category_type)
                ->select(['name', 'short_code', 'description', 'id', 'parent_id', 'is_us_product']);

            return Datatables::of($category)
                ->addColumn(
                    'action',
                    function ($row) use ($can_edit, $can_delete, $category_type) {
                        $html = '';
                        if ($row->is_us_product == 0 && $can_edit) {
                            $html .= '<button data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicCategoryController::class, 'edit'], [$row->id]) . '?type=' . $category_type . '" class="btn btn-xs btn-primary edit_category_button"><i class="glyphicon glyphicon-edit"></i>' . __('messages.edit') . '</button>';
                        } elseif (auth()->user()->can('category.usa')) {
                            $html .= '<button data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicCategoryController::class, 'edit'], [$row->id]) . '?type=' . $category_type . '" class="btn btn-xs btn-primary edit_category_button"><i class="glyphicon glyphicon-edit"></i>' . __('messages.edit') . '</button>';
                        }
                        if ($row->is_us_product == 0 && $can_delete) {
                            $html .= '&nbsp;<button data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicCategoryController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_category_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';
                        }
                        // elseif(auth()->user()->can('superadmin') && $row->is_us_product == 1){
                        //     $html .= '&nbsp;<button data-href="' . action([\App\Http\Controllers\TaxonomyController::class, 'destroy'], [$row->id]) . '" class="btn btn-xs btn-danger delete_category_button"><i class="glyphicon glyphicon-trash"></i> ' . __('messages.delete') . '</button>';  
                        // }
                        if ($row->is_us_product == 1 && auth()->user()->can('category.history')) {
                            $html .= '&nbsp;<button data-href="' . action([\Modules\Clinic\Http\Controllers\ClinicCategoryController::class, 'getRate']) . '" class="btn btn-xs btn-info rate_category_button"><i class="fas fa-history"></i> ' . __('History') . '</button>';
                        }
                        return $html;
                    }
                )
                ->editColumn('name', function ($row) {
                    if ($row->parent_id != 0) {
                        return '--' . $row->name;
                    } else {
                        return $row->name;
                    }
                })
                ->removeColumn('id')
                ->removeColumn('parent_id')
                ->rawColumns(['action'])
                ->make(true);
        }

        $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);
        $foreign_cat = Category::where('is_us_product', 1)->first();
        if ($foreign_cat) {
            $PriceHistory = VariationPriceHistory::where('variation_id', (isset($foreign_cat) ? $foreign_cat->id : null))
                ->where('type', 'category')
                ->orderBy('created_at', 'desc') // You can change the order as per your requirement
                ->get();
        } else {
            $PriceHistory = VariationPriceHistory::where('type', 'category')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('clinic::category.index')->with(compact('module_category_data', 'module_category_data', 'PriceHistory'));
    }
    public function getCategoryIndexPage(Request $request)
    {
        if(!auth()->user()->can('clinic.category.view')){
            abort(403, 'Unauthorized action.');

        }
        if (request()->ajax()) {
            $category_type = $request->get('category_type');
            $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);
            return view('clinic::category.ajax_index')
                ->with(compact('module_category_data', 'category_type'));
        }
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if(!auth()->user()->can('clinic.category.create')){
            abort(403, 'Unauthorized action.');

        }
        $category_type = request()->get('type');
        if ($category_type == 'product' && !auth()->user()->can('clinic.category.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');

        $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);

        $categories = Category::where('business_id', $business_id)
            ->where('parent_id', 0)
            ->where('category_type', $category_type)
            ->select(['name', 'short_code', 'id'])
            ->get();

        $parent_categories = [];
        if (!empty($categories)) {
            foreach ($categories as $category) {
                $parent_categories[$category->id] = $category->name;
            }
        }

        return view('clinic::category.create')
            ->with(compact('parent_categories', 'module_category_data', 'category_type'));
    }


    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $category_type = request()->input('category_type');
                
        if ($category_type == 'product' && !auth()->user()->can('clinic.category.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $input = $request->only(['name', 'short_code', 'category_type', 'description']);
            if (!empty($request->input('add_as_sub_cat')) && $request->input('add_as_sub_cat') == 1 && !empty($request->input('parent_id'))) {
                $input['parent_id'] = $request->input('parent_id');
            } else {
                $input['parent_id'] = 0;
            }
            $input['business_id'] = $request->session()->get('user.business_id');
            $input['created_by'] = $request->session()->get('user.id');

            $category = Category::create($input);
            $output = [
                'success' => true,
                'data' => $category,
                'msg' => __('category.added_success'),
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
        $category_type = request()->get('type');

        if ($category_type == 'product' && !auth()->user()->can('clinic.category.edit')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $category = Category::where('business_id', $business_id)->find($id);

            $module_category_data = $this->moduleUtil->getTaxonomyData($category_type);

            $parent_categories = Category::where('business_id', $business_id)
                ->where('parent_id', 0)
                ->where('category_type', $category_type)
                ->where('id', '!=', $id)
                ->pluck('name', 'id');
            $is_parent = false;

            if ($category->parent_id == 0) {
                $is_parent = true;
                $selected_parent = null;
            } else {
                $selected_parent = $category->parent_id;
            }

            return view('clinic::category.edit')
                ->with(compact('category', 'parent_categories', 'is_parent', 'selected_parent', 'module_category_data'));
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
        if (!auth()->user()->can('clinic.category.edit')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            
            try {
                $input = $request->only(['name', 'description']);
                $business_id = $request->session()->get('user.business_id');

                $category = Category::where('business_id', $business_id)->findOrFail($id);

                if (!auth()->user()->can('clinic.category.edit')) {
                    abort(403, 'Unauthorized action.');
                }

                $category->name = $input['name'];
                $category->short_code = $request->input('short_code');
                $category->description = $request->input('description');

                if (!empty($request->input('add_as_sub_cat')) && $request->input('add_as_sub_cat') == 1 && !empty($request->input('parent_id'))) {
                    $category->parent_id = $request->input('parent_id');
                } else {
                    $category->parent_id = 0;
                }

                $category->save();
                $output = [
                    'success' => true,
                    'msg' => __('category.updated_success'),
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
        if (!auth()->user()->can('clinic.category.delete')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            try {
                $business_id = request()->session()->get('user.business_id');

                $category = Category::where('business_id', $business_id)->findOrFail($id);

                if ($category->category_type == 'product' && !auth()->user()->can('category.delete')) {
                    abort(403, 'Unauthorized action.');
                }

                $category->delete();

                $output = [
                    'success' => true,
                    'msg' => __('category.deleted_success'),
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
    public function getRate()
    {
        $foreign_cat = Category::where('is_us_product', 1)->first();
        $PriceHistory = VariationPriceHistory::where('variation_id', $foreign_cat->id)
            ->where('type', 'category')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('clinic::category.rate')->with(compact('PriceHistory'));
    }
    public function billingTypeCategory($type){
        if(request()->ajax()){
            if($type == 'all'){
                $data = ['test', 'therapy', 'ipd', 'consultation'];
                $categories = Category::whereIn('category_type', $data)->orderBy('created_at', 'desc')->get();
            }else{
                $categories = Category::where('category_type', $type)->orderBy('created_at', 'desc')->get();

            }
            $category = [
                'data' => $categories,
            ];
            return $category;
        }
        
    }
}
