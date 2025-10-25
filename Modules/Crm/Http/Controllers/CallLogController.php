<?php

namespace Modules\Crm\Http\Controllers;

use App\{Contact, Category};
use App\User;
use App\Utils\Util;
use Carbon\CarbonInterval;
use Illuminate\Support\Facades\{DB, Log, Auth};
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Clinic\Entities\{Reference, PatientProfile, PatientDisease, Intakeform, ReportAndProblem};
use Modules\Crm\Entities\{CrmCallLog, CrmCallSubject, CallTag};
use Yajra\DataTables\Facades\DataTables;
use Modules\Clinic\Entities\Problem;

class CallLogController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $commonUtil;

    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if ((! auth()->user()->can('crm.view_all_call_log') && ! auth()->user()->can('crm.view_own_call_log')) || ! config('constants.enable_crm_call_log')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $query = CrmCallLog::with(['callSubjects:id,name', 'callTags:id,value']) // CHANGED
                ->where('crm_call_logs.business_id', $business_id)
                ->leftJoin('contacts as c', 'crm_call_logs.contact_id', '=', 'c.id')
                ->leftJoin('users as u', 'crm_call_logs.user_id', '=', 'u.id')
                ->leftJoin('users as created_users', 'crm_call_logs.created_by', '=', 'created_users.id')
                ->leftJoin('call_campaigns as camp', 'camp.id', '=', 'crm_call_logs.campaign_id')
                ->leftJoin('feedback_form_call_centers as ffc', function ($join) {
                    $join->on('ffc.patient_contact_id', '=', 'c.id')
                        ->on('ffc.campaign_id', '=', 'camp.id');
                })
                ->select(
                    'crm_call_logs.*',
                    'c.name as customer_name',
                    'c.mobile as mobile_number',
                    'c.supplier_business_name',
                    'c.type as customer_type',
                    'ffc.id as feedback_exists',
                    'ffc.created_at as feedback_created_at',
                    'camp.name as campaign_name',
                    DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user_full_name"),
                    DB::raw("CONCAT(COALESCE(created_users.surname, ''), ' ', COALESCE(created_users.first_name, ''), ' ', COALESCE(created_users.last_name, '')) as created_user_name")
                );

            if (! auth()->user()->can('crm.view_all_call_log')) {
                $query->where('crm_call_logs.created_by', auth()->user()->id);
            }
            if (!empty(request()->get('subject_ids')) && is_array(request()->get('subject_ids'))) {
                $subjectIds = request()->get('subject_ids');

                $query->whereHas('callSubjects', function ($q) use ($subjectIds) {
                    $q->whereIn('crm_call_subjects.id', $subjectIds);
                });
            }
            if (!empty(request()->get('tag_ids')) && is_array(request()->get('tag_ids'))) {
                $tagIds = request()->get('tag_ids');

                $query->whereHas('callTags', function ($q) use ($tagIds) {
                    $q->whereIn('call_tags.id', $tagIds);
                });
            }
            if (!empty(request()->duration_min)) {
                $query->where('crm_call_logs.duration', '>=', (int) request()->duration_min);
            }
            if (!empty(request()->duration_max)) {
                $query->where('crm_call_logs.duration', '<=', (int) request()->duration_max);
            }



            if (! empty(request()->get('user_id'))) {
                $query->where('crm_call_logs.created_by', request()->get('user_id'));
            }

            if (! empty(request()->input('start_time')) && ! empty(request()->input('end_time'))) {
                $start_time = request()->input('start_time');
                $end_time = request()->input('end_time');
                $query->whereDate('crm_call_logs.start_time', '>=', $start_time)
                    ->whereDate('crm_call_logs.start_time', '<=', $end_time);
            }

            if (!empty(request()->get('call_type'))) {
                $callType = request()->get('call_type');
                $query->where('crm_call_logs.call_type', $callType);
            }

            if (!empty(request()->get('contact_type'))) {
                $contactType = request()->get('contact_type');
                $query->where('c.type', $contactType);
            }

            return Datatables::of($query)
                ->addColumn('action', function ($row) {
                    $html = '';
                    $canEdit = false;


                    if (
                        $row->created_by == auth()->id() &&
                        \Carbon\Carbon::parse($row->created_at)->diffInHours(now()) < 24
                    ) {
                        $canEdit = true;
                    }

                    if ($canEdit) {
                        $html .= '<a href="#" data-href="' . action([\Modules\Crm\Http\Controllers\CallLogController::class, 'edit'], [$row->id]) . '" class="btn btn-primary btn-xs btn-modal" data-container=".call_log_edit_modal"><i class="fa fa-edit"></i> ' . __('messages.edit') . '</a>';
                    } else {
                        $html .= '<button class="btn btn-secondary btn-xs" disabled><i class="fa fa-lock"></i> ' . __('messages.edit') . '</button>';
                    }

                    // Add feedback button if feedback exists
                    if (!empty($row->feedback_exists) && $row->created_at == $row->feedback_created_at) {
                        $html .= ' <a href="#" data-href="' . action([\Modules\Crm\Http\Controllers\CallLogController::class, 'showFeedback'], [$row->id]) . '" class="btn btn-info btn-xs btn-modal feedback-modal mt-2" data-container=".feedback_modal"><i class="fa fa-comment"></i> ' . __('Feedback') . '</a>';
                    }

                    return $html;
                })

                ->addColumn('start_time', function ($row) {
                    return \Carbon\Carbon::parse($row->start_time)->format('d M Y h:i A');
                })

                ->editColumn('end_time', '@if(!empty($end_time)) {{@format_datetime($end_time)}} @endif')
                ->editColumn('duration', function ($row) {
                    $duration = ! empty($row->duration) ? CarbonInterval::seconds($row->duration)->cascade()->forHumans() : '';
                    return $duration;
                })
                ->addColumn('contact_number', '{{$mobile_number}} @if(!empty($mobile_name))
                <br> ({{$mobile_name}}) @endif')
                ->addColumn('contact_name', function ($row) {
                    $phoneId = 'phone_' . $row->id;
                    $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';

                    $name = $row->customer_name;
                    $typeLabel = '';

                    if ($row->customer_type == 'customer') {
                        $typeLabel = ' <span class="label label-info pull-right">' . ucfirst($row->customer_type) . '</span>';
                    }

                    if (auth()->user()->can('patient.phone_number')) {
                        return $name . ' ' . $row->mobile_number . $typeLabel;
                    } else {
                        return $name . ' ' . $phoneIcon .
                            ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->mobile_number . '</span>' . $typeLabel;
                    }
                })
                ->addColumn('mass_delete', function ($row) {
                    return  '<input type="checkbox" class="row-select" value="' . $row->id . '">';
                })
                ->filterColumn('user_full_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('created_user_name', function ($query, $keyword) {
                    $query->whereRaw("CONCAT(COALESCE(created_users.surname, ''), ' ', COALESCE(created_users.first_name, ''), ' ', COALESCE(created_users.last_name, '')) like ?", ["%{$keyword}%"]);
                })
                ->filterColumn('contact_name', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('c.name', 'like', "%{$keyword}%")
                            ->orWhere('c.supplier_business_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('contact_number', function ($query, $keyword) {
                    $query->where(function ($q) use ($keyword) {
                        $q->where('mobile_number', 'like', "%{$keyword}%")
                            ->orWhere('mobile_name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('campaign_name', function ($row) {
                    return $row->campaign_name ?? ' ....';
                })
                ->addColumn('subject_names', function ($row) {
                    return $row->callSubjects->pluck('name')->implode(', '); // CHANGED
                })
                ->addColumn('tag_names', function ($row) {
                    return $row->callTags->pluck('value')->implode(', '); // CHANGED
                })
                ->addColumn('call_type', function ($row) {
                    $type = ucfirst($row->call_type);
                    $color = $row->call_type === 'inbound' ? 'success' : 'warning';
                    return '<span class="label label-' . $color . '">' . $type . '</span>';
                })
                ->rawColumns(['campaign_name', 'subject_names', 'tag_names', 'mass_delete', 'contact_name', 'contact_number', 'call_type', 'action', 'start_time'])
                ->make(true);
        }

        $users = User::forDropdown($business_id, false);

        $is_admin = $this->commonUtil->is_admin(auth()->user());
        $subjects = CrmCallSubject::pluck('name', 'id');
        $tags = CallTag::pluck('value', 'id');
        return view('crm::call_logs.index')->with(compact('users', 'is_admin', 'subjects', 'tags'));
    }

    /**
     * Mass deletes call logs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(Request $request)
    {
        $is_admin = $this->commonUtil->is_admin(auth()->user());

        $business_id = $request->session()->get('user.business_id');

        $selected_rows = explode(',', $request->input('selected_rows'));

        if (! empty($selected_rows)) {
            CrmCallLog::where('business_id', $business_id)
                ->whereIn('id', $selected_rows)
                ->delete();
        }
        $output = [
            'success' => 1,
            'msg' => __('lang_v1.deleted_success'),
        ];

        return redirect()->back()->with(['status' => $output]);
    }

    public function allUsersCallLog()
    {
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $today = \Carbon::now()->format('Y-m-d');
            $yesterday = \Carbon::yesterday()->format('Y-m-d');

            $query = CrmCallLog::where('crm_call_logs.business_id', $business_id)
                ->join('users as u', 'crm_call_logs.created_by', '=', 'u.id')
                ->select(
                    'u.username',
                    DB::raw("SUM(IF(DATE(start_time)='{$today}', 1, 0)) as calls_today"),
                    DB::raw("SUM(IF(DATE(start_time)='{$yesterday}', 1, 0)) as calls_yesterday"),
                    DB::raw('COUNT(crm_call_logs.id) as all_calls')
                )->groupBy('u.id');

            return Datatables::of($query)
                ->make(true);
        }
    }

    public function create()
    {
        $business_id = request()->session()->get('user.business_id');
        $contacts = Contact::pluck('first_name', 'id');
        $call_subjects = CrmCallSubject::pluck('name', 'id');
        $start_time = now();
        $tags = CallTag::pluck('value', 'id');
        $tag = '';
        $defaultTag = CallTag::where('value', 'Regular')->first();
        if ($defaultTag) {
            $tag = $defaultTag->id;
        }
        $life_stages = Category::forDropdown($business_id, 'life_stage');
        $sources = Reference::where('parent_id', null)->pluck('name', 'id');
        $disease = Problem::pluck('name', 'id')->unique();
        return view('crm::call_logs.create', compact('contacts', 'call_subjects', 'start_time', 'tags', 'tag', 'life_stages', 'disease', 'sources'));
    }
    public function store(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');

            $validated = $request->validate([
                'call_type' => 'required|in:inbound,outbound',
                'contact_id' => 'required|exists:contacts,id',
                'call_subject_id' => 'required|array',
                'call_subject_id.*' => 'exists:crm_call_subjects,id',
                'call_tag_id' => 'required|array',
                'call_tag_id.*' => 'exists:call_tags,id',
                'note' => 'nullable|string',
                'life_stage' => 'nullable|exists:categories,id',
            ]);

            DB::beginTransaction();

            $input = $request->only([
                'call_type',
                'contact_id',
                'note',
                'start_time',
                'disease_id'
            ]);

            $input['business_id'] = $business_id;
            $input['created_by'] = Auth::id();
            $input['start_time'] = Carbon::parse($input['start_time']);
            $input['end_time'] = Carbon::parse(now());
            $input['duration'] = $input['start_time']->diffInSeconds($input['end_time']);

            $call = CrmCallLog::create($input);

            $call->callSubjects()->sync($validated['call_subject_id']);
            $call->callTags()->sync($validated['call_tag_id']);

            // Update contact's life stage if it was changed
            $contact = Contact::find($validated['contact_id']);
            if ($request->has('life_stage')) {
                $contact->crm_life_stage = $validated['life_stage'];
            }
            if ($request->has('age')) {
                $profile = PatientProfile::where('patient_contact_id', $validated['contact_id'])->first();
                if (!$profile) {
                    return;
                }
                $profile->age = $request->age;
                $profile->save();
                $report = Intakeform::where('patient_profile_id', $profile->id)->first();
                if (!empty($input['disease_id'])) {
                    Log::info('DISEASE found');
                    if (!empty($report)) {
                        $newProblems = array_unique($input['disease_id']);
                        ReportAndProblem::where('intake_form_id', $report->id)
                            ->whereNotIn('problem_id', $newProblems)
                            ->delete();
                        foreach ($newProblems as $problemId) {
                            ReportAndProblem::updateOrCreate(
                                [
                                    'intake_form_id' => $report->id,
                                    'problem_id'     => $problemId,
                                ],
                                []
                            );
                        }
                    } else {
                        $newDiseases = array_unique($input['disease_id']);

                        // ✅ delete only the missing diseases
                        PatientDisease::where('patient_profile_id', $profile->id)
                            ->whereNotIn('disease_id', $newDiseases)
                            ->delete();

                        // ✅ keep existing or insert new
                        foreach ($newDiseases as $diseaseId) {
                            PatientDisease::updateOrCreate(
                                [
                                    'patient_profile_id' => $profile->id,
                                    'disease_id'         => $diseaseId
                                ],
                                []
                            );
                        }
                    }
                }
            }
            if ($request->has('source_id')) {
                $contact->crm_source = $request->source_id;
            }
            if ($request->has('sub_source_id')) {
                $contact->sub_source_id = $request->sub_source_id;
            }
            $contact->save();

            DB::commit();

            $output = [
                'success' => true,
                'msg' => 'Call log stored successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    public function getCallLogInfo($id)
    {
        try {
            $call_logs = CrmCallLog::with(['callSubjects:id,name', 'callTags:id,value']) // ✅ CHANGED
                ->where('crm_call_logs.contact_id', $id)
                ->leftJoin('users as created_users', 'crm_call_logs.created_by', '=', 'created_users.id')
                ->leftJoin('call_campaigns as camp', 'camp.id', '=', 'crm_call_logs.campaign_id')
                ->select(
                    'crm_call_logs.*',
                    DB::raw("CONCAT(COALESCE(created_users.surname, ''), ' ', COALESCE(created_users.first_name, ''), ' ', COALESCE(created_users.last_name, '')) as created_user_name"),
                    'camp.name as campaign_name'
                )
                ->get()
                ->map(function ($row) {
                    $row->formatted_duration = !empty($row->duration)
                        ? \Carbon\CarbonInterval::seconds($row->duration)->cascade()->forHumans()
                        : '';
                    $row->start_time = !empty($row->start_time)
                        ? \Carbon\Carbon::parse($row->start_time)->format('d M Y h:i A') // Ex: 02 Aug 2025 01:19 PM
                        : '';

                    // ✅ Add comma-separated values
                    $row->subject_names = $row->callSubjects->pluck('name')->implode(', ');
                    $row->tag_names = $row->callTags->pluck('value')->implode(', ');

                    return $row;
                });

            $output = [
                'success' => true,
                'data' => $call_logs ?? null
            ];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return response()->json($output);
    }
    public function edit($id)
    {
        $call_log = CrmCallLog::with(['callSubjects', 'callTags'])->findOrFail($id);

        $tags = CallTag::pluck('value', 'id');
        $call_subjects = CrmCallSubject::pluck('name', 'id');

        // Selected IDs for old values
        $selected_subjects = $call_log->callSubjects->pluck('id')->toArray();
        $selected_tags = $call_log->callTags->pluck('id')->toArray();

        return view('crm::call_logs.edit', compact('call_log', 'tags', 'call_subjects', 'selected_subjects', 'selected_tags'));
    }
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'call_subject_id' => 'required|array',
                'call_subject_id.*' => 'exists:crm_call_subjects,id',
                'call_tag_id' => 'required|array',
                'call_tag_id.*' => 'exists:call_tags,id',
                'note' => 'nullable|string',
            ]);

            $call_log = CrmCallLog::findOrFail($id);

            // Update note field
            $call_log->note = $request->input('note');
            $call_log->save();

            // Sync relationships
            $call_log->callSubjects()->sync($validated['call_subject_id']);
            $call_log->callTags()->sync($validated['call_tag_id']);

            $output = [
                'success' => true,
                'msg' => __('lang_v1.updated_success'),
            ];
        } catch (\Exception $e) {
            Log::error('CallLog update failed: ' . $e->getMessage(), [
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            $output = [
                'success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return response()->json($output);
    }

    public function showFeedback($id)
    {
        if ((! auth()->user()->can('crm.view_all_call_log') && ! auth()->user()->can('crm.view_own_call_log')) || ! config('constants.enable_crm_call_log')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $callLog = CrmCallLog::findOrFail($id);

        // Get feedback data with relationships
        $feedbackData = DB::table('feedback_form_call_centers as ffc')
            ->where('ffc.campaign_id', $callLog->campaign_id)
            ->where('ffc.patient_contact_id', $callLog->contact_id)
            ->leftJoin('patient_feedback_questions as pfq', 'ffc.id', '=', 'pfq.feedback_form_call_center_id')
            ->leftJoin('feedback_questions as fq', 'pfq.feedback_question_id', '=', 'fq.id')
            ->leftJoin('feedback_role as fr', 'fq.feedback_role_id', '=', 'fr.id')
            ->leftJoin('feedback_answer as fa', 'pfq.feedback_answer_id', '=', 'fa.id')
            ->select(
                'ffc.*',
                'fr.role_name',
                'fq.question_text',
                'pfq.rating_value',
                'pfq.answer_text',
                'fa.option_text'
            )
            ->get();
        // Get patient and user info
        $patientInfo = DB::table('contacts')
            ->where('id', $callLog->contact_id)
            ->select('name as patient_name', 'mobile')
            ->first();

        $userInfo = DB::table('users')
            ->where('id', $callLog->user_id)
            ->select(DB::raw("CONCAT(COALESCE(surname, ''), ' ', COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as user_name"))
            ->first();
        return view('crm::call_logs.feedback', compact('callLog', 'feedbackData', 'patientInfo', 'userInfo'));
    }

    public function getCallLogInfoDatatable($id)
    {
        try {
            $call_logs = CrmCallLog::with(['callSubjects:id,name', 'callTags:id,value'])
                ->where('crm_call_logs.contact_id', $id)
                ->leftJoin('users as created_users', 'crm_call_logs.created_by', '=', 'created_users.id')
                ->leftJoin('call_campaigns as camp', 'camp.id', '=', 'crm_call_logs.campaign_id')
                ->select(
                    'crm_call_logs.*',
                    DB::raw("CONCAT(COALESCE(created_users.surname, ''), ' ', COALESCE(created_users.first_name, ''), ' ', COALESCE(created_users.last_name, '')) as created_user_name"),
                    'camp.name as campaign_name'
                );

            return DataTables::of($call_logs)
                ->addColumn('call_type', function ($row) {


                    // HTML element তৈরি
                    $labelClass = $row->call_type == 'inbound' ? 'success' : 'warning';
                    $labelText = ucfirst($row->call_type);


                    $html = '<span class=" label label-' . $labelClass . '"';

                    $html .= '>' .  $labelText . '</span>';


                    return $html;
                })
                ->addColumn('formatted_duration', function ($row) {
                    return !empty($row->duration)
                        ? \Carbon\CarbonInterval::seconds($row->duration)->cascade()->forHumans()
                        : '';
                })
                ->editColumn('start_time', function ($row) {
                    return !empty($row->start_time)
                        ? \Carbon\Carbon::parse($row->start_time)->format('d M Y h:i A')
                        : '';
                })
                ->addColumn('subject_names', function ($row) {
                    return $row->callSubjects->pluck('name')->implode(', ');
                })
                ->addColumn('tag_names', function ($row) {
                    return $row->callTags->pluck('value')->implode(', ');
                })
                ->addColumn('campaign_name', function ($row) {
                    $name = $row->campaign_name;
                    $survey_type_id = $row->survey_type_id;

                    $dataHref = null;
                    $icon = '';
                    $classes = 'label';

                    if (
                        auth()->user()->can('call_center_feedback.update')
                        && !is_null($row->appointment_id)
                        && !is_null($survey_type_id)
                    ) {
                        $dataHref = action(
                            [\Modules\Clinic\Http\Controllers\CallCenterFeedbackController::class, 'show'],
                            [$row->appointment_id]
                        ) . '?survey_type_id=' . $survey_type_id;

                        $classes .= ' call_type_clickable btn-modal label-primary';
                        $icon = ' <i class="fas fa-edit"></i>';
                    } else {
                        $classes .= ' label-default';
                    }

                    $html = '<span class="' . e($classes) . '"';
                    if ($dataHref) {
                        $html .= ' data-href="' . e($dataHref) . '" data-container=".callCenterFeedbackModal"';
                    }
                    $html .= '>' . e($name) . $icon . '</span>';

                    return $html;
                })

                ->rawColumns(['subject_names', 'tag_names', 'call_type', 'campaign_name']) // Optional if HTML
                ->make(true);
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . ' Line:' . $e->getLine() . ' Message:' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ]);
        }
    }
}
