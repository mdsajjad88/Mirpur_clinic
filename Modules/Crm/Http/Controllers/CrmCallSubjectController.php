<?php

namespace Modules\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Crm\Entities\CrmCallSubject;
use Illuminate\Support\Facades\{DB, Log};

class CrmCallSubjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth()->user()->can('crm.view_call_subject')) {
            abort(403, 'Unauthorized action.');
        }
        if (request()->ajax()) {
            $query = CrmCallSubject::query();
            return DataTables()->of($query)
                ->addColumn('action', function ($row) {
                    $html = '<div class="btn-group">
                <button class="btn btn-info dropdown-toggle btn-xs" type="button" data-toggle="dropdown" aria-expanded="false">
                    ' . __('messages.action') . '
                    <span class="caret"></span>
                    <span class="sr-only">' . __('messages.action') . '</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-left" role="menu">';
                    if (auth()->user()->can('crm.call.subject.update')) {
                        $html .= '<li>
                        <a href="#" data-href="' . action([\Modules\Crm\Http\Controllers\CrmCallSubjectController::class, 'edit'], ['call_subject' => $row->id]) . '" class="subject_edit">
                            <i class="fa fa-edit"></i> ' . __('messages.edit') . '
                        </a>
                      </li>';
                    }

                    if (auth()->user()->can('crm.call.subject.delete')) {
                        $html .= '<li>
                    <a href="#" data-href="' . action([\Modules\Crm\Http\Controllers\CrmCallSubjectController::class, 'destroy'], ['call_subject' => $row->id]) . '" class="subject_delete">
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

        return view('crm::call_subject.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->can('crm.call_subject_store')) {
            abort(403, 'Unauthorized action.');
        }
        return view('crm::call_subject.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('crm.call_subject_store')) {
            abort(403, 'Unauthorized action.');
        }
        try {
            // Validate name field is unique
            $request->validate([
                'name' => 'required|string|unique:crm_call_subjects,name',
            ]);

            // Create new subject
            $subject = CrmCallSubject::create([
                'name' => $request->input('name'),
            ]);

            $output = [
                'success' => true,
                'msg' => 'Subject added successfully',
                'data' => $subject
            ];
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Handle validation failure
            $output = [
                'success' => false,
                'msg' => $e->validator->errors()->first()
            ];
        } catch (\Exception $e) {
            // Log any other exception
            Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
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
        $call_subject = CrmCallSubject::find($id);
        return view('crm::call_subject.edit', compact('call_subject'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate name (must be unique except this ID)
            $request->validate([
                'name' => 'required|string|unique:crm_call_subjects,name,' . $id,
            ]);

            // Find the subject
            $subject = CrmCallSubject::find($id);

            if (!$subject) {
                return response()->json([
                    'success' => false,
                    'msg' => 'Subject not found',
                ], 404);
            }

            // Update subject with validated data
            $subject->update([
                'name' => $request->input('name'),
            ]);

            $output = [
                'success' => true,
                'msg' => 'Subject updated successfully',
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
            CrmCallSubject::find($id)->delete();
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
