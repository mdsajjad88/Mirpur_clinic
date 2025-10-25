<?php

namespace Modules\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Crm\Entities\CallTag;
use Illuminate\Support\Facades\{DB, Log};

class CallTagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('crm.view_call_tag')) {
            abort(403, 'Unauthorized action.');
        }
        
        if (request()->ajax()) {
            $query = CallTag::query();
            return DataTables()->of($query)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                <button class="btn btn-info dropdown-toggle btn-xs" type="button" data-toggle="dropdown" aria-expanded="false">
                    ' . __('messages.action') . '
                    <span class="caret"></span>
                    <span class="sr-only">' . __('messages.action') . '</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can('crm.call_tag_update')) {
                        $html .= '<li>
                    <a href="#" data-href="' . action([\Modules\Crm\Http\Controllers\CallTagController::class, 'edit'], ['call_tag' => $row->id]) . '" class="tag_edit">
                        <i class="fa fa-edit"></i> ' . __('messages.edit') . '
                    </a>
                  </li>';
                    }

                    if (auth()->user()->can('crm.call_tag_delete')) {
                        $html .= '<li>
                        <a href="#" data-href="' . action([\Modules\Crm\Http\Controllers\CallTagController::class, 'destroy'], ['call_tag' => $row->id]) . '" class="tag_delete">
                            <i class="fa fa-trash"></i> ' . __('messages.delete') . '
                        </a>
                      </li>';
                    }


                    $html .= '</ul>
            </div>';

                    return $html;
                })


                ->rawColumns(['action'])
                ->make(true);
        }
        return view('crm::call_tag.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->can('crm.call_tag_store')) {
            abort(403, 'Unauthorized action.');
        }
        return view('crm::call_tag.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('crm.call_tag_store')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            $request->validate([
                'value' => 'required|string|unique:call_tags,value'
            ]);
            $tag = CallTag::create([
                'value' => $request->input('value'),
            ]);
            $output = [
                'success' => true,
                'msg' => 'Tag added successfully',
                'data' => $tag,
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation error response
            $output = [
                'success' => false,
                'msg' => $e->validator->errors()->first() // only show first error
            ];
        } catch (\Exception $e) {
            // Log unexpected exception
            Log::emergency('File: ' . $e->getFile() .
                ' Line: ' . $e->getLine() .
                ' Message: ' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return response()->json($output);
    }


    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('crm::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $call_tag = CallTag::find($id);
        return view('crm::call_tag.edit', compact('call_tag'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate input
            $request->validate([
                'value' => 'required|string|unique:call_tags,value,' . $id . ',id',
            ]);

            // Find the tag
            $tag = CallTag::find($id);

            if (!$tag) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Tag not found'
                ], 404);
            }

            // Update only safe fields
            $tag->update([
                'value' => $request->input('value'),
            ]);

            $output = [
                'success' => true,
                'msg' => 'Tag updated successfully'
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            $output = [
                'success' => false,
                'msg' => $e->validator->errors()->first()
            ];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return response()->json($output);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            CallTag::find($id)->delete();
            $output = [
                'success' => true,
                'msg' => 'Subject deleted successfully'
            ];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }
        return response()->json($output);
    }
}
