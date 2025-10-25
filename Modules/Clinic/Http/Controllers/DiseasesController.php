<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Clinic\Entities\Problem;
use Yajra\DataTables\Facades\DataTables;
use App\Category;
use Illuminate\Validation\Rule;

class DiseasesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (!auth()->user()->can('disease.show') && !auth()->user()->can('disease.create')) {
            abort(403, 'Unauthorized action.');
        }
        $type = request()->get('type');
        if (request()->ajax()) {
            $disease = Problem::leftJoin('categories', 'problems.category_id', '=', 'categories.id')
                ->select('problems.*', 'categories.name as category_name')->groupBy('problems.name');
            if (request()->has('order')) {
                $order = request()->input('order')[0];
                $column = request()->input('columns')[$order['column']]['name'];

                if ($column === 'category_name') {
                    $disease->orderBy('categories.name', $order['dir']);
                }
            }
            return Datatables::of($disease)

                ->addColumn('action', function ($disease) use ($type) {
                    $editUrl = route('clinic-diseases.edit', [$disease->id]) . '?type=' . $type;
                    $deleteUrl = route('clinic-diseases.destroy', [$disease->id]);

                    // Check if the user has the 'disease.update' permission
                    $editButton = '';
                    $deleteButton = '';
                    if (auth()->user()->can('disease.update')) {
                        $editButton = '<button data-href="' . $editUrl . '" class="btn btn-xs btn-primary edit_disease_button_clinic"><i class="glyphicon glyphicon-edit"></i> Edit</button>';
                    }

                    if (auth()->user()->can('disease.delete')) {
                        $deleteButton = '<button data-href="' . $deleteUrl . '" class="btn btn-xs btn-danger delete_disease_button_clinic"><i class="glyphicon glyphicon-trash"></i>Delete</button>';
                    }

                    // Return the final action buttons
                    return $editButton . '&nbsp;' . $deleteButton;
                })
                ->addColumn('category_name', function ($row) {
                    return $row->category_name ?? '';
                })
                ->removeColumn('id')
                ->rawColumns(['action', 'category_name'])
                ->make(true);
        }

        return view('clinic::disease.index', compact('type'));
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        if (!auth()->user()->can('disease.store')) {
            abort(403, 'Unauthorized action.');
        }
        $type = request()->get('type');
        $quick_add = false;
        if (! empty(request()->input('quick_add'))) {
            $quick_add = true;
        }
        $categories = Category::where('category_type', 'disease')
            ->get()
            ->mapWithKeys(function ($item) {
                $description = $item->description ? ' -- ' . $item->description : '';
                return [$item->id => $item->name . ' ' . $description];
            });

        $disease_categories = $categories->toArray();
        return view('clinic::disease.create', compact('quick_add', 'disease_categories', 'type'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('disease.store')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $request->validate([
                'name' => [
                    'required',
                    Rule::unique('problems', 'name')->where(function ($query) {
                        $query->whereRaw('LOWER(name) = ?', [strtolower(request()->input('name'))]);
                    })
                ],
            ]);

            $input = $request->only(['category_id', 'name', 'bn_name', 'description']);
            $disease = new Problem();
            $disease->category_id = $input['category_id'];
            $disease->name = $input['name'];
            $disease->bn_name = $input['bn_name']??'';
            $disease->description = $input['description']??'';
            $disease->save();
            $output = [
                'success' => true,
                'data' => $disease,
                'disease_data' => [
                    'id' => $disease->id, 
                    'text' => $disease->name,
                ],
                'msg' => __('clinic::lang.disease_added_success'),
            ];
        }catch (\Illuminate\Validation\ValidationException $e) {
            $validationMessages = $e->errors();
            $formattedMessages = [];
        
            foreach ($validationMessages as $field => $messages) {
                if (!in_array($messages[0], $formattedMessages)) {
                    $formattedMessages[] = $messages[0];
                }
            }
        
            \Log::info('formattedMessages ' . json_encode($formattedMessages));
        
            $output = [
                'success' => false,
                'msg' => $formattedMessages,
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
        if (!auth()->user()->can('disease.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $disease = Problem::find($id);
            $categories = Category::where('category_type', 'disease')
                ->get()
                ->mapWithKeys(function ($item) {
                    $description = $item->description ? ' -- ' . $item->description : '';
                    return [$item->id => $item->name . ' ' . $description];
                });
                $type = request()->get('type');
    \Log::info('type is in edit method '.$type);
            $disease_categories = $categories->toArray();
            return view('clinic::disease.edit')
                ->with(compact('disease', 'disease_categories', 'type'));
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
        if (!auth()->user()->can('disease.update')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->ajax()) {
            try {
                $disease = Problem::findOrFail($id);
                $request->validate([
                    'name' => [
                        'required',
                        Rule::unique('problems', 'name')->where(function ($query) use ($disease) {
                            $query->whereRaw('LOWER(name) = ?', [strtolower(request()->input('name'))])
                                ->where('id', '!=', $disease->id);
                        })
                    ],
                ]);

                $input = $request->only(['name', 'bn_name', 'description', 'category_id']);

                $disease->name = $input['name'];
                $disease->category_id = $input['category_id'];
                $disease->bn_name = $input['bn_name']??'';
                $disease->description = $input['description']??'';
                $disease->save();
                $output = [
                    'success' => true,
                    'msg' => __('clinic::lang.disease_updated_success'),
                ];
            } catch (\Illuminate\Validation\ValidationException $e) {
                $validationMessages = $e->errors();
                $formattedMessages = [];
                foreach ($validationMessages as $field => $messages) {
                    $formattedMessages[] = $messages[0];
                }
                $output = [
                    'success' => false,
                    'msg' => $formattedMessages,
                ];
            } catch (\Exception $e) {
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
        if (!auth()->user()->can('disease.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $disease = Problem::findOrFail($id);
                $disease->delete();

                $output = [
                    'success' => true,
                    'msg' => __('clinic::lang.disease_deleted_success'),
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
