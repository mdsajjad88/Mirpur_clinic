<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\{CallCampaign, DummyPatient, CrmContact, CampaignContact, ContactFilter, CrmCallLog, SeminarRegistration};
use Modules\Clinic\Entities\SurveyType;
use Yajra\DataTables\Facades\DataTables;
use App\{Contact, User, Category};
use App\District;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Carbon\Carbon;
use Illuminate\Support\Str;
use Modules\Clinic\Entities\{FeedbackFormCallCenter, FedbackQuestion};
use Modules\Clinic\Entities\Intakeform;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use Modules\Clinic\Entities\PatientFeedbackQuestion;
use Modules\Clinic\Entities\{PatientProfile, DoctorProfile};
use Modules\Clinic\Entities\Reference;
use Illuminate\Support\Facades\Http;
use App\Utils\ModuleUtil;
use Illuminate\Support\Collection;

class CallCampaignController extends Controller
{

    protected $moduleUtil;
    public function __construct(ModuleUtil $moduleUtil)
    {
        $this->moduleUtil = $moduleUtil;
    }
    public function index()
    {
        if (!auth()->user()->can('crm.view_call_campaign')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $campaigns = CallCampaign::with(['surveyType'])
                ->select('call_campaigns.*');

            return DataTables::of($campaigns)
                ->addColumn('action', function ($row) {
                    $html = '';
                    if (auth()->user()->can('crm.update_call_campaign')) {
                        $html .= '<a href="' . action([self::class, 'edit'], $row->id) . '" class="btn btn-xs btn-primary">
                            <i class="fas fa-edit"></i> ' . __('Edit') . '
                        </a>';
                    }


                    if (auth()->user()->can('crm.view_call_campaign')) {
                        $html .= '<a href="' . action([self::class, 'show'], $row->id) . '" class="btn btn-xs btn-info" style="margin-left: 5px;">
                            <i class="fas fa-eye"></i> ' . __('View') . '
                        </a>';
                    }
                    $marge_contact_url = action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'margeContactCampaign'], [$row->id]);
                    $html .= '<a href="' . $marge_contact_url . '" class="btn btn-xs btn-success marge_contact" style="margin-left: 5px;" >Marge</a>';

                    if (auth()->user()->can('crm.delete_call_campaign')) {
                        $deleteUrl = action([\Modules\Crm\Http\Controllers\CallCampaignController::class, 'destroy'], [$row->id]);

                        $html .= '<a data-href="' . $deleteUrl . '" class="btn btn-xs btn-danger delete_campaign" style="margin-left: 20px;">
                            <i class="fas fa-trash"></i> ' . __('Delete') . '
                        </a>';
                    }
                    return $html;
                })
                ->editColumn('status', function ($row) {
                    $statuses = [
                        'draft' => 'secondary',
                        'active' => 'success',
                        'completed' => 'primary',
                        'paused' => 'warning'
                    ];
                    return '<span class="badge badge-' . ($statuses[$row->status] ?? 'info') . '">' . ucfirst($row->status) . '</span>';
                })
                ->editColumn('progress', function ($row) {
                    if ($row->target_count == 0) return '0%';
                    $percent = round(($row->completed_count / $row->target_count) * 100);
                    return '<div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: ' . $percent . '%;" aria-valuenow="' . $percent . '" aria-valuemin="0" aria-valuemax="100">' . $percent . '%</div>
                    </div>';
                })
                ->rawColumns(['action', 'status', 'progress'])
                ->make(true);
        }

        return view('crm::call_campaign.index');
    }

    public function create()
    {
        if (!auth()->user()->can('crm.create_call_campaign')) {
            abort(403, 'Unauthorized action.');
        }

        $surveyTypes = SurveyType::where('type', 'survey')->pluck('name', 'id');
        $filters = ContactFilter::pluck('name', 'id');
        return view('crm::call_campaign.create', compact('surveyTypes', 'filters'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('crm.create_call_campaign')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only(['name', 'description', 'survey_type_id', 'start_date', 'end_date', 'contact_filter_id']);
            $input['status'] = 'draft';
            $input['created_by'] = auth()->user()->id;

            DB::beginTransaction();
            $campaign = CallCampaign::create($input);

            $this->generateCampaignContacts($campaign);

            DB::commit();
            $output = [
                'success' => true,
                'msg' => __('Campaign created successfully'),
                'redirect' => action([self::class, 'index'])
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }

    public function show($id)
    {
        if (!auth()->user()->can('crm.view_call_campaign')) {
            abort(403, 'Unauthorized action.');
        }

        $campaign = CallCampaign::with(['surveyType'])->findOrFail($id);
        $common_settings = session('business.common_settings');
        $is_admin = $this->moduleUtil->is_admin(auth()->user(), $campaign->business_id);
        $call_statuses = $common_settings['call_status'] ?? [];
        $callStatusColors = [];
        foreach ($call_statuses as $status) {
            $name = strtolower(trim($status['call_status']));
            $callStatusColors[$name] = $status['call_status_color'] ?? '#000000';
        }
        if (request()->ajax()) {
            $contacts = DB::table('campaign_contacts')
                ->join('contacts', 'campaign_contacts.contact_id', '=', 'contacts.id')
                ->leftJoin('users', 'campaign_contacts.assigned_to', '=', 'users.id')
                ->where('campaign_contacts.campaign_id', $id)
                ->where('campaign_contacts.status', '!=', 'completed');
            if (!$is_admin) {
                $contacts->where(function ($query) {
                    $query->whereNull('campaign_contacts.assigned_to')
                        ->orWhere('campaign_contacts.assigned_to', auth()->id());
                });
            }

            $contacts = $contacts->select(
                'campaign_contacts.*',
                'contacts.name as contact_name',
                'contacts.mobile',
                'contacts.type as contact_type',
                'users.first_name as agent_first_name',
                'users.last_name as agent_last_name'
            );

            $hasInProgress = false;
            if (!$is_admin) {
                $hasInProgress = DB::table('campaign_contacts')
                    ->where('campaign_id', $id)
                    ->where('assigned_to', auth()->id())
                    ->where('status', 'in_progress')
                    ->exists();
            }


            return DataTables::of($contacts)
                ->addColumn('action', function ($row) use ($campaign, $hasInProgress) {
                    $html = '';
                    $disabled = '';

                    // Disable button if there is another in_progress, and current row is not that one
                    if ($hasInProgress && $row->status !== 'in_progress') {
                        $disabled = 'disabled';
                    }

                    $html .= '<a href="#" class="btn btn-xs btn-primary start-call ' . $disabled . '" 
                        data-contact-id="' . $row->contact_id . '" 
                        data-campaign-id="' . $campaign->id . '" ' . $disabled . '>' . __('Call') . ' <i class="fas fa-arrow-right"></i>
                    </a>';

                    return $html;
                })
                ->editColumn('status', function ($row) use ($callStatusColors) {
                    $callStatus = $row->status ?? 'Pending';
                    $statusKey = strtolower(trim($callStatus));
                    $color = $callStatusColors[$statusKey] ?? '#6c757d';
                    if ($callStatus == 'Pending') {
                        $color = 'rgb(245, 7, 7)';
                    }
                    if ($callStatus == 'in_progress') {
                        $color = 'rgba(100, 175, 100, 1)';
                    }



                    return '<span style="font-size: 11px; background-color: ' . $color . '; color: white; padding: 2px 4px; border-radius: 4px; display: inline-block;">' . $callStatus . '</span>';
                })


                ->editColumn('assigned_to_name', function ($row) {
                    return $row->agent_first_name . ' ' . $row->agent_last_name ?? '';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }

        return view('crm::call_campaign.second_show', compact('campaign'));
    }

    public function edit($id)
    {
        if (!auth()->user()->can('crm.update_call_campaign')) {
            abort(403, 'Unauthorized action.');
        }

        $campaign = CallCampaign::findOrFail($id);
        $surveyTypes = SurveyType::pluck('name', 'id');
        $filters = ContactFilter::pluck('name', 'id');
        return view('crm::call_campaign.edit', compact('campaign', 'surveyTypes', 'filters'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('crm.update_call_campaign')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $campaign = CallCampaign::findOrFail($id);

            $input = $request->only(['name', 'description', 'survey_type_id', 'start_date', 'end_date', 'status', 'contact_filter_id']);

            $campaign->update($input);

            // Regenerate contacts if filters changed
            if ($request->regenerate_contacts) {
                $this->generateCampaignContacts($campaign, true);
            }

            $output = [
                'success' => true,
                'msg' => __('Campaign updated successfully'),
                'redirect' => action([self::class, 'index'])
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('crm.delete_call_campaign')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            Log::info('Removing Id is ' . $id . ' from storage');
            $campaign = CallCampaign::findOrFail($id);
            $campaign->delete();

            $output = [
                'success' => true,
                'msg' => __('Campaign deleted successfully')
            ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }

    public function importContacts(Request $request, $campaignId)
    {
        if (!auth()->user()->can('crm.update_call_campaign')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $campaign = CallCampaign::findOrFail($campaignId);

            // Process CSV file and import contacts
            $file = $request->file('contacts_file');
            $csvData = array_map('str_getcsv', file($file));

            $header = array_shift($csvData);
            $contacts = [];

            foreach ($csvData as $row) {
                $contacts[] = array_combine($header, $row);
            }

            DB::beginTransaction();

            foreach ($contacts as $contactData) {
                // Find or create contact
                $contact = Contact::firstOrCreate(
                    ['mobile' => $contactData['mobile']],
                    ['name' => $contactData['name'], 'type' => $contactData['type'] ?? 'customer']
                );

                // Add to campaign if not already exists
                DB::table('campaign_contacts')->updateOrInsert(
                    ['campaign_id' => $campaignId, 'contact_id' => $contact->id],
                    ['status' => 'pending', 'created_at' => now(), 'updated_at' => now()]
                );
            }

            // Update campaign target count
            $campaign->target_count = DB::table('campaign_contacts')
                ->where('campaign_id', $campaignId)
                ->count();
            $campaign->save();

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('Contacts imported successfully'),
                'redirect' => action([self::class, 'show'], $campaignId)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('Failed to import contacts: ') . $e->getMessage()
            ];
        }

        return $output;
    }

    protected function generateCampaignContacts($campaign, $regenerate = false)
    {
        if ($regenerate) {
            DB::table('campaign_contacts')->where('campaign_id', $campaign->id)->delete();
        }

        $contact_filter = ContactFilter::find($campaign->contact_filter_id);
        $contactIdsRaw = json_decode($contact_filter->contact_ids, true);

        $contactIds = collect($contactIdsRaw)->pluck('contact_id')->toArray();

        Log::info('Decoded Contact IDs:', $contactIds);

        // Step 2: Directly build insert array from contact IDs
        $campaignContacts = [];
        foreach ($contactIds as $contact_id) {
            $campaignContacts[] = [
                'campaign_id' => $campaign->id,
                'contact_id' => $contact_id,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($campaignContacts)) {
            DB::table('campaign_contacts')->insert($campaignContacts);
        }

        // Step 3: Update target count
        $campaign->target_count = count($campaignContacts);
        $campaign->save();
    }


    public function startCall(Request $request)
    {
        if (!auth()->user()->can('crm.update_call_campaign')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        try {
            $campaignId = $request->campaign_id;
            $contactId = $request->contact_id;
            $existingAssignment = DB::table('campaign_contacts')
                ->where('campaign_id', $campaignId)
                ->where('contact_id', $contactId)
                ->where('status', 'in_progress')
                ->whereNotNull('assigned_to')
                ->where('assigned_to', '!=', auth()->id())
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'msg' => 'This contact is already assigned to another agent.'
                ]);
            }
            // Update campaign contact status
            DB::table('campaign_contacts')
                ->where('campaign_id', $campaignId)
                ->where('contact_id', $contactId)
                ->update([
                    'status' => 'in_progress',
                    'assigned_to' => auth()->user()->id,
                    'updated_at' => now()
                ]);

            $contact = Contact::findOrFail($contactId);
            $campaign = CallCampaign::findOrFail($campaignId);

            // Feedback Questions with their answers via JOIN
            $survey_type_id = $campaign->survey_type_id;
            $questions = DB::table('feedback_questions')
                ->leftJoin('feedback_answer', 'feedback_answer.feedback_question_id', '=', 'feedback_questions.id')
                ->where('survey_type_id', $survey_type_id)
                ->orderBy('feedback_questions.position', 'asc')
                ->select(
                    'feedback_questions.*',
                    'feedback_answer.id as answer_id',
                    'feedback_answer.option_text',
                    'feedback_answer.feedback_question_id'
                )
                ->get()
                ->groupBy('id');

            $common_settings = session('business.common_settings');
            $call_statuses = $common_settings['call_status'] ?? [];
            $callDropdown = [];
            foreach ($call_statuses as $status) {
                $callDropdown[$status['call_status']] = $status['call_status'];
            }

            $feedbackForm = FeedbackFormCallCenter::where('survey_type_id', $survey_type_id)->first();
            $answers = collect();

            if ($feedbackForm) {
                $answers = PatientFeedbackQuestion::where('feedback_form_call_center_id', $feedbackForm->id)
                    ->get()
                    ->groupBy('feedback_question_id');
            }

            $appointment = PatientAppointmentRequ::where('patient_contact_id', $contactId)->latest()->first();
            $lastAppointment = PatientAppointmentRequ::where('patient_contact_id', $contactId)->where('remarks', 'prescribed')->latest()->first();
            $patientProfile = null;
            $doctorName = null;
            $lastVisitDate = null;
            $patientType = null;
            if ($lastAppointment) {
                $doctor = DoctorProfile::where('id', $lastAppointment->doctor_profile_id)->first();
                $doctorName = $doctor->first_name . ' ' . $doctor->last_name ?? '';
                $lastVisitDate = \Carbon\Carbon::parse($lastAppointment->request_date)->format('d F Y');
            }
            if ($appointment) {
                $patientProfile = PatientProfile::where('id', $appointment->patient_profile_id)->first();
                $patientType = $appointment->type;
            }
            $district = null;
            if ($patientProfile) {
                $district = District::where('id', $patientProfile->district_id)->first();
            }
            $calling_status = $feedbackForm->call_status ?? '';

            // Handle case where intake form might not exist
            $diseases = collect();
            if ($patientProfile) {
                $intakeForm = Intakeform::where('patient_profile_id', $patientProfile->id)->first();
                if ($intakeForm) {
                    $diseases = DB::table('report_and_problems')
                        ->join('problems', 'report_and_problems.problem_id', '=', 'problems.id')
                        ->where('report_and_problems.intake_form_id', $intakeForm->id)
                        ->select('report_and_problems.*', 'problems.name as problem_name')
                        ->get();
                }
            }
            $surveyName = optional(SurveyType::find($survey_type_id))->name;

            $start_time = now();

            $life_stages = Category::forDropdown($business_id, 'life_stage', $contact->type);
            $sources = Reference::where('parent_id', null)->pluck('name', 'id');
            $sub_sources = [];
            if ($contact->crm_source) {
                $sub_sources = Reference::where('parent_id', $contact->crm_source)->pluck('name', 'id');
            }
            return view('crm::call_campaign._form', compact(
                'contact',
                'survey_type_id',
                'questions',
                'answers',
                'patientProfile',
                'district',
                'diseases',
                'callDropdown',
                'calling_status',
                'surveyName',
                'start_time',
                'campaignId',
                'doctorName',
                'lastVisitDate',
                'patientType',
                'life_stages',
                'sources',
                'sub_sources',
            ));
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            return response()->json([
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ]);
        }
    }

    public function saveCallResult(Request $request)
    {

        $business_id = $request->session()->get('user.business_id');
        DB::beginTransaction();
        try {
            $campaignId = $request->campaign_id;
            $contactId = $request->contact_id;
            $status = $request->status; // 'completed' or 'failed'
            $notes = $request->notes;
            $campaignContact = DB::table('campaign_contacts')
                ->where('campaign_id', $campaignId)
                ->where('contact_id', $contactId)
                ->first();

            if (!$campaignContact) {
                throw new \Exception("Campaign contact not found");
            }

            $updateData = [
                'status' => $status,
                'called_at' => now(),
                'notes' => $notes,
                'updated_at' => now()
            ];

            // If call was successful and feedback was collected
            if ($status == 'completed') {

                // $feedbackFormId = null;
                // $updateData['feedback_form_id'] = $request->feedback_form_id;

                // Increment completed count in campaign
                DB::table('call_campaigns')
                    ->where('id', $campaignId)
                    ->increment('completed_count');
                $feedbackForm = FeedbackFormCallCenter::updateOrCreate(
                    [
                        'survey_type_id' => $request->survey_type_id,
                        'patient_contact_id' => $contactId,
                        'campaign_id' => $campaignId,
                    ],
                    [
                        'call_status' => $request->call_status,
                        'comment' => $request->comment,
                        'updated_by' => auth()->user()->id
                    ]
                );

                if ($feedbackForm->wasRecentlyCreated) {
                    $feedbackForm->created_by = auth()->user()->id;
                    $feedbackForm->save();
                }
                $feedbackFormId = $feedbackForm->id;
                $updateData['feedback_form_id'] = $feedbackFormId;
                foreach ($request->all() as $key => $value) {
                    if (!Str::startsWith($key, 'question_') || Str::endsWith($key, '_na')) {
                        continue;
                    }

                    $questionId = str_replace('question_', '', $key);
                    $question = FedbackQuestion::find($questionId);

                    if (!$question) continue;

                    $isNA = false;
                    $defaultAnswerId = 0;

                    if (
                        $value === '__NA__' ||
                        $request->has($key . '_na') ||
                        (empty($value) && $request->has($key . '_na'))
                    ) {
                        $isNA = true;
                        $value = $defaultAnswerId;
                    }

                    // Different question type handling
                    switch ($question->question_type) {
                        case 'short_text':
                            PatientFeedbackQuestion::create([
                                'feedback_form_call_center_id' => $feedbackFormId,
                                'survey_type_id' => $question->survey_type_id,
                                'feedback_question_id' => $questionId,
                                'answer_text' => $isNA ? null : $value,
                                'feedback_answer_id' => $defaultAnswerId,
                                'is_n_a' => $isNA
                            ]);
                            break;

                        case 'checkbox':
                            if (is_array($value)) {
                                foreach ($value as $answerId) {
                                    PatientFeedbackQuestion::create([
                                        'feedback_form_call_center_id' => $feedbackFormId,
                                        'survey_type_id' => $question->survey_type_id,
                                        'feedback_question_id' => $questionId,
                                        'feedback_answer_id' => $answerId !== '__NA__' ? $answerId : null,
                                        'is_n_a' => $answerId === '__NA__' ? 1 : 0
                                    ]);
                                }
                            }
                            break;

                        case 'star_rating':
                            PatientFeedbackQuestion::create([
                                'feedback_form_call_center_id' => $feedbackFormId,
                                'survey_type_id' => $question->survey_type_id,
                                'feedback_question_id' => $questionId,
                                'rating_value' => $isNA ? null : $value,
                                'feedback_answer_id' => null,
                                'is_n_a' => $isNA
                            ]);
                            break;

                        default:
                            PatientFeedbackQuestion::create([
                                'feedback_form_call_center_id' => $feedbackFormId,
                                'survey_type_id' => $question->survey_type_id,
                                'feedback_question_id' => $questionId,
                                'feedback_answer_id' => $value ?: null,
                                'is_n_a' => $isNA
                            ]);
                            break;
                    }
                }
            }


            DB::table('campaign_contacts')
                ->where('campaign_id', $campaignId)
                ->where('contact_id', $contactId)
                ->update($updateData);

            $callLog = CrmCallLog::create([
                'business_id' => $business_id,
                'campaign_id' => $campaignId,
                'user_id' => auth()->id(),
                'call_type' => 'outbound',
                'contact_id' => $contactId,
                'start_time' => $request->start_time,
                'end_time' => now(),
                'duration' => (int) now()->diffInSeconds(Carbon::parse($request->start_time)),
                'note' => $request->comment ?? '',
                'created_by' => auth()->id()
            ]);

            $contact = Contact::find($contactId);

            $contact->crm_life_stage = $request->life_stage_form;
            $contact->crm_source = $request->source_id;
            $contact->sub_source_id = $request->sub_source_id;
            $contact->save();

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('Call result saved successfully')
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }


    public function storeDummyLead(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        try {
            DB::beginTransaction();
            $input = $request->only(['type', 'prefix', 'first_name',  'mobile',  'alternate_number', 'city', 'state', 'country',  'email', 'crm_source', 'crm_life_stage', 'dob', 'address_line_1', 'address_line_2', 'zip_code', 'age']);

            $input['name'] = $input['first_name'];



            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');

            $assigned_to = $request->input('user_id');
            $response = $this->storeContactViaApi($input);
            if ($response->successful()) {
                $responseData = $response->json()['data'];
                $input['contact_id'] = $responseData['contact_id'];
            } else {
                $output = [
                    'success' => false,
                    'msg' => $response->json()['message'],
                ];
                return $output;
            }
            $contact = CrmContact::createNewLead($input, $assigned_to);
            $callCampaign = CallCampaign::find($request->input('campaign_id'));
            $callCampaign->target_count = $callCampaign->target_count + 1;
            $callCampaign->save();
            $campaignContact = CampaignContact::create([
                'campaign_id' => $request->input('campaign_id'),
                'contact_id' => $contact->id,
            ]);
            if (! empty($contact)) {
                $this->moduleUtil->getModuleData('after_contact_saved', ['contact' => $contact, 'input' => $request->input()]);
            }
            $dummy = DummyPatient::where('mobile', $input['mobile'])->latest()->first();
            if (!empty($dummy)) {
                $dummy->is_done = 1;
                $dummy->save();
            }
            DB::commit();
            $output = [
                'success' => true,
                'msg' => __('contact.added_success'),
                'data' => $contact
            ];
        } catch (\Exception $e) {
            Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            DB::rollBack();
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }

    private function storeContactViaApi($input)
    {
        $apiUrl = 'https://awc.careneterp.com:82/api/contacts';
        // $apiUrl = 'http://192.168.11.17/Projects/Lacuna_main_only/public/api/contacts';

        try {
            $data = [
                'type' => $input['type'],
                'prefix' => $input['prefix'] ?? '',
                'contact_type_radio' => 'individual',
                'first_name' => $input['first_name'],
                'mobile' => $input['mobile'],
                'email' => $input['email'],
                'dob' => $input['dob'] ?? '',
                'age' => $input['age'],
                'customer_group_id' => $input['customer_group_id'] ?? '',
                'send_sms' => $input['send_sms'] ?? 1,
                'alternate_number' => $input['alternate_number'] ?? '',
                'address_line_1' => $input['address_line_1'] ?? '',
                'address_line_2' => $input['address_line_2'] ?? '',
                'city' => $input['city'] ?? '',
                'state' => $input['state'] ?? '',
                'country' => $input['country'] ?? '',
                'zip_code' => $input['zip_code'] ?? '',
            ];
            return Http::post($apiUrl, $data);
        } catch (\Exception $e) {
            Log::error('API call failed in storeContactViaApi method', [
                'error' => $e->getMessage(),
                'url' => $apiUrl,
                'input' => $input,
            ]);
            throw new \Exception('Error Processing Request: Unable to reach API.', 1);
        }
    }
    public function addCampaignContact(Request $request)
    {
        try {

            $campaign_id = $request->input('campaign_id');
            $contact_id = $request->input('id');
            $campaignContact = CampaignContact::where('campaign_id', $campaign_id)->where('contact_id', $contact_id)->first();
            $contact = Contact::find($contact_id);
            $dummy = DummyPatient::where('mobile', $contact->mobile)->first();

            if (!empty($dummy)) {
                $dummy->is_done = 1;
                $dummy->save();
            }
            if ($campaignContact) {
                $output = [
                    'success' => false,
                    'msg' => 'Contact already added this campaign',
                ];
                DB::rollBack();
                return $output;
            }
            DB::beginTransaction();
            $callCampaign = CallCampaign::find($request->input('campaign_id'));
            $callCampaign->target_count = $callCampaign->target_count + 1;
            $callCampaign->save();

            $campaignContact = CampaignContact::create([
                'campaign_id' => $request->input('campaign_id'),
                'contact_id' => $contact_id,
            ]);
            DB::commit();
            $output = [
                'success' => true,
                'msg' => 'Contact added to campaign successfully',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return $output;
    }
    private function processDummyContact(DummyPatient $dummy, Collection $contacts, int $campaignId)
    {
        $name = trim(strtolower($dummy->name));
        $mobile = preg_replace('/[^0-9]/', '', $dummy->mobile);
        $business_id = request()->session()->get('user.business_id');
        if (!preg_match('/^01[0-9]{9}$/', $mobile)) {
            return [
                'status' => 'invalid',
                'dummy' => $dummy,
            ];
        }


        $matchedContacts = $contacts->filter(function ($contact) use ($name, $mobile) {
            return strtolower(preg_replace('/[^0-9]/', '', $contact->mobile) === $mobile);
        });
        $callCamp = CallCampaign::find($campaignId);

        if ($matchedContacts->count() === 1) {
            $contact = $matchedContacts->first();

            $campaignContact = CampaignContact::updateOrCreate(
                ['campaign_id' => $campaignId, 'contact_id' => $contact->id],
                ['status' => 'pending']
            );

            if ($campaignContact->wasRecentlyCreated) {
                $callCamp->target_count = $callCamp->target_count + 1;
                $callCamp->save();
            }


            $dummy->is_done = 1;
            $dummy->save();

            return [
                'status' => 'matched',
                'dummy' => $dummy,
                'contact' => $contact,
            ];
        } elseif ($matchedContacts->count() > 1) {
            return [
                'status' => 'multiple',
                'dummy' => $dummy,
                'matches' => $matchedContacts,
                'contact_ids' => $matchedContacts,
            ];
        } else {
            $prepareData = $this->prepareApiInputFromDummy($dummy);
            $response = $this->storeContactViaApi($prepareData);

            // Extract parts safely
            $responseBody = $response->json();
            $httpCode = $response->status();
            $isSuccess = $responseBody['success'] ?? false;
            $message = $responseBody['msg'] ?? 'No message';

            Log::info('API Contact Store Attempt', [
                'dummy_id' => $dummy->id,
                'dummy_name' => $dummy->name,
                'http_code' => $httpCode,
                'success' => $isSuccess,
                'msg' => $message,
                'response' => $responseBody,
            ]);

            if ($httpCode === 201 && $isSuccess) {
                $responseData = $responseBody['data'] ?? [];
                $newContactId = $responseData['contact_id'] ?? null;

                if (!$newContactId) {
                    Log::warning('API returned no contact_id after successful response', [
                        'dummy_id' => $dummy->id,
                        'dummy_name' => $dummy->name,
                        'response_data' => $responseData,
                    ]);

                    return [
                        'status' => 'process_failed',
                        'dummy' => $dummy,
                        'msg' => 'Contact ID missing in API response',
                    ];
                }

                Log::info('New contact creation started', [
                    'dummy_id' => $dummy->id,
                    'dummy_name' => $dummy->name,
                    'contact_id' => $newContactId,
                    'api_input' => $prepareData,
                ]);

                $newContact = Contact::create([
                    'name' => $dummy->name,
                    'first_name' => $dummy->name,
                    'mobile' => $dummy->mobile,
                    'type' => 'lead',
                    'business_id' => $business_id,
                    'contact_id' => $newContactId,
                ]);

                Log::info('New contact created successfully in DB', [
                    'new_contact_db_id' => $newContact->id,
                    'linked_contact_id' => $newContact->contact_id,
                    'campaign_id' => $campaignId,
                ]);

                CampaignContact::create([
                    'campaign_id' => $campaignId,
                    'contact_id' => $newContact->id,
                    'status' => 'pending',
                ]);
                $callCamp->target_count = $callCamp->target_count + 1;
                $callCamp->save();
                $dummy->is_done = 1;
                $dummy->save();

                return [
                    'status' => 'created',
                    'dummy' => $dummy,
                    'contact' => $newContact,
                ];
            } else {
                $output = [
                    'success' => false,
                    'msg' => $response->json()['msg'],
                    'status' => 'process_failed',
                ];
                return $output;
            }
        }
    }


    public function margeContactCampaign($id)
    {
        $callCampaign = CallCampaign::findOrFail($id);
        $dummyContacts = DummyPatient::where('is_done', 0)->orderBy('id', 'desc')->limit(2000)->get();
        $contacts = Contact::all();

        $matched = [];
        $multipleMatches = [];
        $invalidMobiles = [];

        $matchedCount = 0;
        $createdCount = 0;
        $invalidCount = 0;
        $multipleCount = 0;
        $processFailedCount = 0;
        foreach ($dummyContacts as $dummy) {
            $result = $this->processDummyContact($dummy, $contacts, $id);

            switch ($result['status']) {
                case 'matched':
                    $matched[] = $result['contact']->id;
                    $matchedCount++;
                    break;
                case 'created':
                    $matched[] = $result['contact']->id;
                    $createdCount++;
                    break;
                case 'multiple':
                    $multipleMatches[] = [
                        'dummy_id' => $result['dummy']->id,
                        'name' => $result['dummy']->name,
                        'mobile' => $result['dummy']->mobile,
                        'matches' => $result['matches'], // Collection
                    ];

                    $multipleCount++;
                    break;
                case 'invalid':
                    $invalidMobiles[] = [
                        'dummy_id' => $result['dummy']->id,
                        'name' => $result['dummy']->name,
                        'mobile' => $result['dummy']->mobile,
                        'reason' => 'Invalid Mobile',
                    ];
                    $invalidCount++;
                    break;
                case 'process_failed':
                    $processFailedCount++;
                    break;
            }
        }
        Log::info('report', [
            'matched_count' => $matchedCount,
            'created_count' => $createdCount,
            'multiple_count' => $multipleCount,
            'invalid_count' => $invalidCount,
        ]);
        return view('crm::call_campaign.marge_contacts', [
            'campaign' => $callCampaign,
            'matchedContacts' => $matched,
            'multipleMatches' => $multipleMatches,
            'invalidMobiles' => $invalidMobiles,
            'report' => [
                'total_dummy' => $dummyContacts->count(),
                'matched_count' => $matchedCount,
                'created_count' => $createdCount,
                'multiple_count' => $multipleCount,
                'invalid_count' => $invalidCount,
                'processFailedCount' => $processFailedCount
            ],
        ]);
    }

    public function mergeProcess(Request $request, $id)
    {
        $callCampaign = CallCampaign::findOrFail($id);
        $contacts = Contact::all();

        try {
            DB::beginTransaction();

            $approvedMultiple = $request->input('approve_multiple', []);
            $correctedMobiles = $request->input('mobiles', []);
            $createNewContactIds = $request->input('create_new_contact', []);

            // Process multiple checkbox-approved
            foreach ($createNewContactIds as $dummyId) {
                $dummy = DummyPatient::find($dummyId);
                $cleanName = trim(strtolower($dummy->name));
                $cleanMobile = preg_replace('/[^0-9]/', '', $dummy->mobile);

                $existingContact = Contact::whereRaw('LOWER(TRIM(name)) = ?', [$cleanName])
                    ->whereRaw('REGEXP_REPLACE(mobile, "[^0-9]", "") = ?', [$cleanMobile])
                    ->first();

                if ($existingContact) {
                    Log::info('Contact already exists, skipping creation', [
                        'dummy_id' => $dummy->id,
                        'dummy_name' => $dummy->name,
                        'mobile' => $dummy->mobile,
                        'matched_contact_id' => $existingContact->id,
                    ]);

                    CampaignContact::updateOrCreate(
                        ['campaign_id' => $callCampaign->id, 'contact_id' => $existingContact->id],
                        ['status' => 'pending']
                    );

                    $callCampaign->increment('target_count');
                    $dummy->update(['is_done' => 1]);

                    continue; // Skip to next dummyId
                }
                if (!$dummy) continue;

                Log::info('Processing dummy: ' . $dummy->name . ' with id: ' . $dummy->id);

                $business_id = request()->session()->get('user.business_id');
                $prepareData = $this->prepareApiInputFromDummy($dummy);
                $response = $this->storeContactViaApi($prepareData);

                // Extract parts safely
                $responseBody = $response->json();
                $httpCode = $response->status();
                $isSuccess = $responseBody['success'] ?? false;
                $message = $responseBody['msg'] ?? 'No message';

                Log::info('API Contact Store Attempt', [
                    'dummy_id' => $dummy->id,
                    'dummy_name' => $dummy->name,
                    'http_code' => $httpCode,
                    'success' => $isSuccess,
                    'msg' => $message,
                    'response' => $responseBody,
                ]);

                if ($httpCode === 201 && $isSuccess) {
                    $responseData = $responseBody['data'] ?? [];
                    $newContactId = $responseData['contact_id'] ?? null;

                    if (!$newContactId) {
                        Log::warning('API returned no contact_id after successful response', [
                            'dummy_id' => $dummy->id,
                            'dummy_name' => $dummy->name,
                            'response_data' => $responseData,
                        ]);

                        return [
                            'status' => 'process_failed',
                            'dummy' => $dummy,
                            'msg' => 'Contact ID missing in API response',
                        ];
                    }



                    $newContact = Contact::create([
                        'name' => $dummy->name,
                        'first_name' => $dummy->name,
                        'mobile' => $dummy->mobile,
                        'type' => 'lead',
                        'business_id' => $business_id,
                        'contact_id' => $newContactId,
                    ]);

                    CampaignContact::create([
                        'campaign_id' => $callCampaign->id,
                        'contact_id' => $newContact->id,
                        'status' => 'pending',
                    ]);

                    $callCampaign->target_count++;
                    $callCampaign->save();

                    $dummy->is_done = 1;
                    $dummy->save();
                } else {
                    Log::error('Error in creating contact: ' . $response->json()['msg']);

                    $output = [
                        'success' => false,
                        'msg' => $response->json()['msg'],
                        'status' => 'process_failed',
                    ];
                    return $output;
                }
            }

            // ✅ Step 2: Process existing matched contactIds
            foreach ($approvedMultiple as $dummyId => $contactIds) {
                // Skip if this dummy already handled in create_new_contact
                if (in_array((string)$dummyId, $createNewContactIds)) continue;

                $dummy = DummyPatient::find($dummyId);
                if (!$dummy) continue;

                Log::info('Processing dummy: ' . $dummy->name . ' with id: ' . $dummy->id);

                foreach ($contactIds as $contactId) {
                    $contact = Contact::find($contactId);
                    if (!$contact) continue;

                    $campaignContact = CampaignContact::updateOrCreate(
                        ['campaign_id' => $callCampaign->id, 'contact_id' => $contact->id],
                        ['status' => 'pending']
                    );

                    if ($campaignContact->wasRecentlyCreated) {
                        $callCampaign->target_count++;
                        $callCampaign->save();
                    }
                }

                $dummy->is_done = 1;
                $dummy->save();
            }


            // Process corrected invalids
            foreach ($correctedMobiles as $dummyId => $newMobile) {
                $dummy = DummyPatient::find($dummyId);
                if (!$dummy) continue;

                Log::info('Processing dummy: ' . $dummy->name . ' with id: ' . $dummy->id);

                $dummy->mobile = $newMobile;
                $dummy->save();

                $this->processDummyContact($dummy, $contacts, $id);
            }

            DB::commit();

            return response()->json(['success' => true, 'msg' => 'Merge processing completed!']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in merging contacts', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request' => $request->all(),
            ]);

            return response()->json(['success' => false, 'msg' => 'Something went wrong!']);
        }
    }


    private function prepareApiInputFromDummy($dummy)
    {
        return [
            'type' => 'lead',
            'prefix' => $dummy->prefix ?? '',
            'contact_type_radio' => 'individual',
            'first_name' => $dummy->name,
            'mobile' => $dummy->mobile,
            'email' => $dummy->email ?? '',
            'dob' => $dummy->dob ?? '',
            'age' => $dummy->age ?? '',
            'customer_group_id' => $dummy->customer_group_id ?? '',
            'send_sms' => $dummy->send_sms ?? 1,
            'alternate_number' => $dummy->alternate_number ?? '',
            'address_line_1' => $dummy->address_line_1 ?? '',
            'address_line_2' => $dummy->address_line_2 ?? '',
            'city' => $dummy->city ?? '',
            'state' => $dummy->state ?? '',
            'country' => $dummy->country ?? '',
            'zip_code' => $dummy->zip_code ?? '',
        ];
    }
    public function storeSeminarLead(Request $request)
    {
        try {
            $data = $request->all();
            $dummy = new \stdClass();
            $dummy->name = $data['name'] ?? '';
            $dummy->mobile = $data['mobile'] ?? '';
            $dummy->age = $data['age'] ?? '';
            $dummy->type = $data['lead'] ?? '';
            $dummy->address_line_1 = $data['address'] ?? '';

            $checkContact = Contact::where('mobile', $dummy->mobile)->where('first_name', $dummy->name)->first();
            if ($checkContact) {
                $patient_contact_id = $checkContact->id;
            } else {

                $apiInput = $this->prepareApiInputFromDummy($dummy);
                $response = $this->storeContactViaApi($apiInput);

                $responseBody = $response->json();
                $httpCode = $response->status();
                $isSuccess = $responseBody['success'] ?? false;
                $message = $responseBody['msg'] ?? 'No message';

                if ($httpCode === 201 && $isSuccess) {
                    $responseData = $responseBody['data'] ?? [];
                    $newContactId = $responseData['contact_id'] ?? null;

                    $newContact = Contact::create([
                        'business_id'  => 1,
                        'name'          => $dummy->name,
                        'first_name'    => $dummy->name,
                        'mobile'        => $dummy->mobile,
                        'type'          => 'lead',
                        'contact_type'  => 'individual',
                        'contact_id'    => $newContactId,
                        'division_id'   => $data['division_id'] ?? '',
                        'district_id'   => $data['district_id'] ?? '',
                        'upazila_id'    => $data['upazila_id'] ?? '',
                    ]);
                    $patient_contact_id = $newContact->id;
                } else {
                    Log::error('Error in storing seminar lead', [
                        'response' => $responseBody,
                        'request'  => $request->all(),
                        'message'  => $message

                    ]);
                    return response()->json([
                        'success' => false,
                        'msg'     => $message
                    ]);
                }
            }

            $invoice_no = 'AWC141' . rand(111111, 999999) . time();
            $seminarRegi = new SeminarRegistration();
            $seminarRegi->survey_type_id = $data['seminar_id'];
            $seminarRegi->patient_contact_id = $patient_contact_id;
            $seminarRegi->invoice_number = $invoice_no;
            $seminarRegi->comment = $data['comment'] ?? '';
            $seminarRegi->primary_diseases_id = $data['primary_diseases_id'] ?? '';
            $seminarRegi->secondary_diseases_id = $data['secondary_diseases_id'] ?? '';
            $seminarRegi->save();

            return response()->json([
                'success'    => true,
                'invoice_no' => $seminarRegi->invoice_number
            ]);
        } catch (\Exception $e) {
            Log::error('Error in storing seminar lead', [
                'error'   => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'msg'     => 'Something went wrong!'
            ]);
        }
    }


    public function updateSeminarPaymentStatus($invoice, $tnx)
    {
        $seminarRegi = SeminarRegistration::where('invoice_number', $invoice)->first();
        if ($seminarRegi) {
            $seminarRegi->status = 'paid';
            $seminarRegi->trx_id = $tnx;
            $seminarRegi->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }
    
}
