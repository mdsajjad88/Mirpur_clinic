<?php

namespace Modules\Clinic\Http\Controllers;

use App\CustomerGroup;
use App\User;
use App\Contact;
use App\BusinessLocation;
use Spatie\Activitylog\Models\Activity;
use App\Utils\ContactUtil;
use App\Utils\TransactionUtil;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use App\Events\ContactCreatedOrModified;
use Illuminate\Support\Facades\DB;
use App\TransactionPayment;
use App\Transaction;
use App\Http\Controllers\Controller;
use Modules\Clinic\Utils\PatientUtil;
use Modules\Clinic\Entities\Disease;
use Modules\Clinic\Entities\PatientProfile;
use Modules\Clinic\Entities\{Problem, Prescription, Intakeform, PatientSessionDetails, PatientSessionInfo, ReportAndProblem};
use Modules\Clinic\Entities\PatientUser;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $transactionUtil;
    protected $contactUtil;
    protected $moduleUtil;
    protected $commonUtil;
    protected $patientUtil;
    public function __construct(
        ContactUtil $contactUtil,
        TransactionUtil $transactionUtil,
        ModuleUtil $moduleUtil,
        Util $commonUtil,
        PatientUtil $patientUtil
    ) {
        $this->commonUtil = $commonUtil;
        $this->contactUtil = $contactUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
        $this->patientUtil = $patientUtil;
    }

    public function index(Request $request)
    {
        if (!auth()->user()->can('clinic.patient.view')) {
            abort(403, 'Unauthorized action.');
        }


        $business_id = $request->session()->get('user.business_id');

        $type = 'customer';
        $types = ['supplier', 'customer'];

        if (empty($type) || !in_array($type, $types)) {
            return redirect()->back();
        }

        if ($request->ajax()) {
            Log::info('AJAX request detected.');

            if ($type == 'customer') {
                return $this->indexCustomer();
            } else {
                return response()->json(['error' => 'Not Found'], 404);
            }
        } else {
            Log::info('Non-AJAX request detected.');
        }

        $reward_enabled = ($request->session()->get('business.enable_rp') == 1 && in_array($type, ['customer']));

        $users = User::forDropdown($business_id);
        $customer_groups = [];
        if ($type == 'customer') {
            $customer_groups = CustomerGroup::forDropdown($business_id);
            Log::info('Customer groups retrieved for dropdown.');
        }
        $diseases = Problem::all();

        return view('clinic::patient.patients.index')->with(compact('type', 'reward_enabled', 'customer_groups', 'users', 'diseases'));
    }
    private function indexCustomer()
    {

        // Authorization check
        if (!auth()->user()->can('clinic.patient.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $is_admin = $this->contactUtil->is_admin(auth()->user());

        $query = $this->contactUtil->getPatientQuery($business_id, 'customer');
        if (!empty(request()->input('is_patient'))) {
            $query->havingRaw('is_patient = "Yes"');
        }

        if (!empty(request()->input('customer_group_id'))) {
            $query->where('contacts.customer_group_id', request()->input('customer_group_id'));
        }

        // Contact status filter
        if (!empty(request()->input('status_filter'))) {
            $query->where('contacts.contact_status', request()->input('status_filter'));
        }

        $contacts = Datatables::of($query)
            ->addColumn(
                'action',
                function ($row) {
                    $html = '<div class="btn-group">
                <button type="button" class="btn btn-info dropdown-toggle btn-xs" 
                    data-toggle="dropdown" aria-expanded="false">' .
                        __('messages.actions') .
                        '<span class="caret"></span><span class="sr-only">Toggle Dropdown
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-left" role="menu">';

                    if (auth()->user()->can('clinic.patient.pay')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\PatientPaymentController::class, 'getPayContactDue'], [$row->id]) . '?type=sell" class="pay_sale_due"><i class="fas fa-money-bill-alt" aria-hidden="true"></i>' . __('lang_v1.pay') . '</a></li>';
                    }

                    if (auth()->user()->can('clinic.patient.view')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'show'], [$row->id]) . '"><i class="fas fa-eye" aria-hidden="true"></i>' . __('messages.view') . '</a></li>';
                    }
                    $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'edit'], [$row->id]) . '" class="edit_patient_button"><i class="glyphicon glyphicon-edit"></i>' . __('messages.edit') . '</a></li>';

                    if (auth()->user()->can('clinic.patient.profile')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'profile'], [$row->id]) . '" ><i class="fas fa-user" aria-hidden="true"></i>' . __('clinic::lang.profile') . '</a></li>';
                    }
                    $html .= '<li class="divider"></li>';
                    if (auth()->user()->can('clinic.patient.delete')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'destroy'], [$row->id]) . '" class="delete_contact_button"><i class="glyphicon glyphicon-trash"></i>' . __('messages.delete') . '</a></li>';
                    }

                    if (auth()->user()->can('clinic.patient.deactive')) {
                        $html .= '<li><a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'updateStatus'], [$row->id]) . '"class="update_patient_status"><i class="fas fa-power-off"></i>';

                        if ($row->contact_status == 'active') {
                            $html .= __('messages.deactivate');
                        } else {
                            $html .= __('messages.activate');
                        }

                        $html .= '</a></li>';
                    }

                    $html .= '<li class="divider"></li>';
                    if (auth()->user()->can('clinic.patient.ledger' || auth()->user()->can('clinic.patient.sales') || auth()->user()->can('clinic.patient.document_and_note'))) {
                        $html .= '
                            <li>
                                <a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'show'], [$row->id]) . '?view=ledger">
                                    <i class="fas fa-scroll" aria-hidden="true"></i>
                                    ' . __('lang_v1.ledger') . '
                                </a>
                            </li>';

                        if (in_array($row->type, ['both', 'supplier'])) {
                            $html .= '<li>
                            <a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'show'], [$row->id]) . '?view=purchase">
                                <i class="fas fa-arrow-circle-down" aria-hidden="true"></i>
                                ' . __('purchase.purchases') . '
                            </a>
                        </li>
                        <li>
                            <a href="' . action([\App\Http\Controllers\ContactController::class, 'show'], [$row->id]) . '?view=stock_report">
                                <i class="fas fa-hourglass-half" aria-hidden="true"></i>
                                ' . __('report.stock_report') . '
                            </a>
                        </li>';
                        }

                        if (in_array($row->type, ['both', 'customer'])) {
                            $html .= '<li>
                            <a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'show'], [$row->id]) . '?view=sales">
                                <i class="fas fa-arrow-circle-up" aria-hidden="true"></i>
                                ' . __('sale.sells') . '
                            </a>
                        </li>';
                        }

                        $html .= '<li>
                            <a href="' . action([\Modules\Clinic\Http\Controllers\PatientController::class, 'show'], [$row->id]) . '?view=documents_and_notes">
                                <i class="fas fa-paperclip" aria-hidden="true"></i>
                                 ' . __('lang_v1.documents_and_notes') . '
                            </a>
                        </li>';
                    }
                    $html .= '</ul></div>';
                    return $html;
                }
            )
            ->editColumn('opening_balance', function ($row) {
                $html = '<span data-orig-value="' . $row->opening_balance . '">' . $this->transactionUtil->num_f($row->opening_balance, true) . '</span>';

                return $html;
            })
            
            ->editColumn('name', function ($row) {
                $name = $row->name;
                if ($row->contact_status == 'inactive') {
                    $name = $row->name . ' <small class="label pull-right bg-red no-print">' . __('lang_v1.inactive') . '</small>';
                }
                if ($row->is_patient == 'Yes') {
                    $name .= ' <small class="label pull-right bg-green no-print">P</small>';
                }
                if (! empty($row->converted_by)) {
                    $name .= '<span class="label bg-info label-round no-print" data-toggle="tooltip" title="Converted from leads"><i class="fas fa-sync-alt"></i></span>';
                }

                return $name;
            })
            ->editColumn('mobile', function ($row) {
                $phoneId = 'phone_' . $row->appId;
                $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';

                if (auth()->user()->can('patient.phone_number')) {
                    return $row->mobile;
                } else {
                    return $phoneIcon .
                        ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->mobile . '</span>';
                }
            })
            ->editColumn('pay_term', '
            @if(!empty($pay_term_type) && !empty($pay_term_number))
                {{$pay_term_number}}
                @lang("lang_v1.".$pay_term_type)
            @endif
        ')
            ->addColumn('address', '{{implode(", ", array_filter([$address_line_1, $address_line_2, $city, $state, $country, $zip_code]))}}')
            ->rawColumns(['action', 'opening_balance', 'name', 'balance', 'mobile'])
            ->make(true);


        return $contacts;
    }




    public function callHistories()
    {
        $data = [
            [
                'call_id' => 'C001',
                'caller_name' => 'Alice Johnson',
                'receiver_name' => 'Bob Smith',
                'call_start_time' => '2024-08-01 09:00:00',
                'call_end_time' => '2024-08-01 09:30:00',
                'duration' => '30 minutes',
                'call_type' => 'Incoming',
                'notes' => 'Discussed project details',
                'action' => '<i class="fas fa-eye"></i>',
            ],
            [
                'call_id' => 'C002',
                'caller_name' => 'Carol White',
                'receiver_name' => 'David Brown',
                'call_start_time' => '2024-08-02 14:00:00',
                'call_end_time' => '2024-08-02 14:45:00',
                'duration' => '45 minutes',
                'call_type' => 'Outgoing',
                'notes' => 'Follow-up on the meeting',
                'action' => '<i class="fas fa-eye"></i>',
            ],
            [
                'call_id' => 'C003',
                'caller_name' => 'Eve Black',
                'receiver_name' => 'Frank Green',
                'call_start_time' => '2024-08-03 16:00:00',
                'call_end_time' => '2024-08-03 16:20:00',
                'duration' => '20 minutes',
                'call_type' => 'Incoming',
                'notes' => 'Inquired about the report',
                'action' => '<i class="fas fa-eye"></i>',
            ],
            [
                'call_id' => 'C004',
                'caller_name' => 'Grace Lee',
                'receiver_name' => 'Hank Wilson',
                'call_start_time' => '2024-08-04 11:00:00',
                'call_end_time' => '2024-08-04 11:30:00',
                'duration' => '30 minutes',
                'call_type' => 'Outgoing',
                'notes' => 'Discussed budget adjustments',
                'action' => '<i class="fas fa-eye"></i>',
            ],
            [
                'call_id' => 'C005',
                'caller_name' => 'Ivy Davis',
                'receiver_name' => 'Jack Martin',
                'call_start_time' => '2024-08-05 13:00:00',
                'call_end_time' => '2024-08-05 13:50:00',
                'duration' => '50 minutes',
                'call_type' => 'Incoming',
                'notes' => 'Scheduled next week’s meeting',
                'action' => '<i class="fas fa-eye"></i>',
            ],
        ];
        return DataTables::of(collect($data))->make(true);
    }

    public function AddSubscription()
    {
        return view('clinic::patient.patients.partials.subscription-modal');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {


        $business_id = request()->session()->get('user.business_id');

        //Check if subscribed or not
        if (! $this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse();
        }

        $types = [];

        if (auth()->user()->can('clinic.patient.create')) {
            $types['customer'] = __('report.customer');
        }

        $customer_groups = CustomerGroup::forDropdown($business_id);
        $selected_type = request()->type;

        //Added check because $users is of no use if enable_contact_assign if false
        $diseases = Problem::all();
        $users = config('constants.enable_contact_assign') ? User::forDropdown($business_id, false, false, false, true) : [];

        return view('clinic::patient.patients.partials.add_patient')
            ->with(compact('customer_groups', 'selected_type', 'users', 'diseases'));
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        if (! auth()->user()->can('clinic.patient.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = $request->session()->get('user.business_id');

            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input = $request->only([
                'type',
                'supplier_business_name',
                'prefix',
                'first_name',
                'middle_name',
                'last_name',
                'tax_number',
                'pay_term_number',
                'pay_term_type',
                'mobile',
                'landline',
                'alternate_number',
                'city',
                'state',
                'country',
                'address_line_1',
                'address_line_2',
                'customer_group_id',
                'zip_code',
                'contact_id',
                'custom_field1',
                'custom_field2',
                'custom_field3',
                'custom_field4',
                'custom_field5',
                'custom_field6',
                'custom_field7',
                'custom_field8',
                'custom_field9',
                'custom_field10',
                'email',
                'shipping_address',
                'position',
                'dob',
                'shipping_custom_field_details',
                'assigned_to_users',
                'age',
                'gender',
                'disease',
                'send_sms',
            ]);

            $name_array = [];

            if (! empty($input['prefix'])) {
                $name_array[] = $input['prefix'];
            }
            if (! empty($input['first_name'])) {
                $name_array[] = $input['first_name'];
            }
            if (! empty($input['middle_name'])) {
                $name_array[] = $input['middle_name'];
            }
            if (! empty($input['last_name'])) {
                $name_array[] = $input['last_name'];
            }

            $input['contact_type'] = $request->input('contact_type_radio');

            $input['name'] = trim(implode(' ', $name_array));

            if (! empty($request->input('is_export'))) {
                $input['is_export'] = true;
                $input['export_custom_field_1'] = $request->input('export_custom_field_1');
                $input['export_custom_field_2'] = $request->input('export_custom_field_2');
                $input['export_custom_field_3'] = $request->input('export_custom_field_3');
                $input['export_custom_field_4'] = $request->input('export_custom_field_4');
                $input['export_custom_field_5'] = $request->input('export_custom_field_5');
                $input['export_custom_field_6'] = $request->input('export_custom_field_6');
            }

            if (! empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');

            $input['credit_limit'] = $request->input('credit_limit') != '' ? $this->commonUtil->num_uf($request->input('credit_limit')) : null;
            $input['opening_balance'] = $this->commonUtil->num_uf($request->input('opening_balance'));

            DB::beginTransaction();
            $response = $this->patientUtil->createNewContact($input);
            if ($response['success'] == true) {
                $output = $response;
                $contact = $response['data']['contact'];
                $patient = $response['data']['patient'];
                event(new ContactCreatedOrModified($input, 'added'));
                $this->moduleUtil->getModuleData('after_contact_saved', ['contact' => $response['data'], 'input' => $request->input()]);
                $this->patientUtil->activityLog($contact, 'added');
                $this->patientUtil->activityLog($patient, 'added');
            } else if ($response['success'] == false) {
                $output = [
                    'success' => false,
                    'msg' => 'Try Again later',
                ];
            }

            DB::commit();
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
    public function storeApi(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');

            if (! $this->moduleUtil->isSubscribed($business_id)) {
                return $this->moduleUtil->expiredResponse();
            }

            $input = $request->only([
                'type',
                'supplier_business_name',
                'prefix',
                'first_name',
                'middle_name',
                'last_name',
                'tax_number',
                'pay_term_number',
                'pay_term_type',
                'mobile',
                'landline',
                'alternate_number',
                'city',
                'state',
                'country',
                'address_line_1',
                'address_line_2',
                'customer_group_id',
                'zip_code',
                'contact_id',
                'custom_field1',
                'custom_field2',
                'custom_field3',
                'custom_field4',
                'custom_field5',
                'custom_field6',
                'custom_field7',
                'custom_field8',
                'custom_field9',
                'custom_field10',
                'email',
                'shipping_address',
                'position',
                'dob',
                'shipping_custom_field_details',
                'assigned_to_users',
                'age',
                'gender',
                'disease',
                'send_sms',
            ]);

            $name_array = [];

            if (! empty($input['prefix'])) {
                $name_array[] = $input['prefix'];
            }
            if (! empty($input['first_name'])) {
                $name_array[] = $input['first_name'];
            }
            if (! empty($input['middle_name'])) {
                $name_array[] = $input['middle_name'];
            }
            if (! empty($input['last_name'])) {
                $name_array[] = $input['last_name'];
            }

            $input['contact_type'] = $request->input('contact_type_radio');

            $input['name'] = trim(implode(' ', $name_array));

            if (! empty($request->input('is_export'))) {
                $input['is_export'] = true;
                $input['export_custom_field_1'] = $request->input('export_custom_field_1');
                $input['export_custom_field_2'] = $request->input('export_custom_field_2');
                $input['export_custom_field_3'] = $request->input('export_custom_field_3');
                $input['export_custom_field_4'] = $request->input('export_custom_field_4');
                $input['export_custom_field_5'] = $request->input('export_custom_field_5');
                $input['export_custom_field_6'] = $request->input('export_custom_field_6');
            }

            if (! empty($input['dob'])) {
                $input['dob'] = $this->commonUtil->uf_date($input['dob']);
            }

            $input['business_id'] = $business_id;
            $input['created_by'] = $request->session()->get('user.id');

            $input['credit_limit'] = $request->input('credit_limit') != '' ? $this->commonUtil->num_uf($request->input('credit_limit')) : null;
            $input['opening_balance'] = $this->commonUtil->num_uf($request->input('opening_balance'));

            DB::beginTransaction();
            $output = $this->patientUtil->createNewContactApi($input);
            $contact = $output['data']['contact'];
            $patient = $output['data']['patient'];

            // Log activities for both contact and patient


            event(new ContactCreatedOrModified($input, 'added'));

            $this->moduleUtil->getModuleData('after_contact_saved', ['contact' => $output['data'], 'input' => $request->input()]);

            $this->patientUtil->activityLog($contact, 'added');
            $this->patientUtil->activityLog($patient, 'added');

            DB::commit();
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

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        if (! auth()->user()->can('clinic.patient.view')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact = $this->contactUtil->getPatientInfo($business_id, $id);

        $is_selected_contacts = User::isSelectedContacts(auth()->user()->id);
        $user_contacts = [];
        if ($is_selected_contacts) {
            $user_contacts = auth()->user()->contactAccess->pluck('id')->toArray();
        }


        if (! auth()->user()->can('clinic.patient.view')) {
            if ($contact->created_by != auth()->user()->id & ! in_array($contact->id, $user_contacts)) {
                abort(403, 'Unauthorized action.');
            }
        }

        $reward_enabled = (request()->session()->get('business.enable_rp') == 1 && in_array($contact->type, ['customer', 'both'])) ? true : false;

        $contact_dropdown = Contact::contactDropdown($business_id, false, false);

        $business_locations = BusinessLocation::forDropdown($business_id, true);

        $view_type = request()->get('view');
        if (is_null($view_type)) {
            $view_type = 'ledger';
        }

        $contact_view_tabs = $this->moduleUtil->getModuleData('get_contact_view_tabs');

        $activities = Activity::forSubject($contact)
            ->with(['causer', 'subject'])
            ->latest()
            ->get();
        return view('clinic::patient.patients.show')
            ->with(compact('contact', 'reward_enabled', 'contact_dropdown', 'business_locations', 'view_type', 'contact_view_tabs', 'activities'));
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        $patient = PatientProfile::where('patient_contact_id', $id)->first();
        $allDiseases = Problem::all();
        $patientDiseases = $patient ? $patient->problems()->pluck('disease_id')->toArray() : [];

        return view('clinic::patient.patients.partials.patient_profile_update')
            ->with(compact('patient', 'allDiseases', 'patientDiseases'));
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            // Check if the request is AJAX
            if ($request->ajax()) {
                $input = $request->only([
                    'first_name',
                    'last_name',
                    'nick_name',
                    'email',
                    'gender',
                    'date_of_birth',
                    'nid',
                    'age',
                    'blood_group',
                    'address',
                    'marital_status',
                    'height_cm',
                    'weight_kg',
                    'body_fat_percentage',
                    'work_phone',
                    'city',
                    'state',
                    'post_code',
                    'country',
                    'emergency_contact_person',
                    'emergency_phone',
                    'mobile',
                    'disease*',
                ]);
                $updateData = collect($input)->only([
                    'first_name',
                    'mobile',
                    'dob',
                    'city',
                    'email',
                    'address'
                ])->toArray();
                DB::beginTransaction();
                $patient = PatientProfile::findOrFail($id);
                $contact = Contact::where('id', $patient->patient_contact_id)->first();
                
                    $patient_contact_id = $patient->patient_contact_id;
                    $patient->update($input);
                    if ($patient) {
                        // Sync diseases with the patient profile (this will add new ones, and remove deleted ones)
                        $patient->problems()->sync($request->input('disease', []));  // Sync the diseases
                    }
                    // Prepare contact data from patient data
                    $contactInput = [
                        'name' => $input['first_name'] . ' ' . $input['last_name'],
                        'first_name' => $input['first_name'],
                        'last_name' => $input['last_name'],
                        'email' => $input['email'],
                        'mobile' => $input['mobile'],
                        'address_line_1' => $input['address'],
                        'city' => $input['city'],
                        'state' => $input['state'],
                        'country' => $input['country'],
                        'zip_code' => $input['post_code'],
                    ];

                    $this->contactUtil->updateContact($contactInput, $patient_contact_id, $business_id);
                
                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Profile updated successfully'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid request type.'
                ], 400); // Bad Request
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }


    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        // Check if the authenticated user has permission to delete a clinic patient
        if (!auth()->user()->can('clinic.patient.delete')) {
            abort(403, 'Unauthorized action.');
        }

        // Ensure the request is an AJAX request
        if (request()->ajax()) {
            // Initialize the output variable with a default value
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'), // Default error message
            ];

            try {
                // Find the patient profile by ID
                $patient = PatientProfile::where('patient_contact_id', $id)->first();

                if ($patient) {
                    $business_id = request()->user()->business_id;

                    // Check if any transaction related to this contact exists
                    $count = Transaction::where('business_id', $business_id)
                        ->where('contact_id', $id)
                        ->count();

                    // If no transactions are found, proceed with deletion
                    if ($count == 0) {
                        // Find the associated contact
                        $contact = Contact::where('business_id', $business_id)
                            ->findOrFail($id);

                        // Ensure the contact is not a default contact
                        if (!$contact->is_default) {
                            // Log the deletion activity
                            $log_properties = [
                                'id' => $contact->id,
                                'name' => $contact->name,
                                'supplier_business_name' => $contact->supplier_business_name,
                            ];
                            $this->contactUtil->activityLog($contact, 'contact_deleted', $log_properties);

                            // Disable login for associated users
                            User::where('crm_contact_id', $contact->id)
                                ->update(['allow_login' => 0]);
                            $contact->delete();

                            $patient_user = PatientUser::where('id', $patient->patient_user_id)->first();
                            $patient_user->delete();
                            $patient->delete();

                            // Trigger an event for contact deletion
                            event(new ContactCreatedOrModified($contact, 'deleted'));

                            // Update the output for success
                            $output = [
                                'success' => true,
                                'msg' => __('contact.deleted_success'),
                            ];
                        } else {
                            // Update the output if the contact is a default contact
                            $output = [
                                'success' => false,
                                'msg' => __('lang_v1.default_contact_cannot_be_deleted'),
                            ];
                        }
                    } else {
                        // Update the output if transactions exist
                        $output = [
                            'success' => false,
                            'msg' => __('lang_v1.you_cannot_delete_this_contact'),
                        ];
                    }
                } else {
                    // Update the output if the patient is not found
                    $output = [
                        'success' => false,
                        'msg' => __('messages.patient_not_found'),
                    ];
                }
            } catch (\Exception $e) {
                // Log the exception
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                // Update the output for exceptions
                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            // Return the output response
            return $output;
        }
    }

    public function getContactPayments($contact_id)
    {
        $business_id = request()->session()->get('user.business_id');
        Log::info('getContactPayments called', [
            'business_id' => $business_id,
            'contact_id' => $contact_id,
        ]);
        if (request()->ajax()) {
            $payments = TransactionPayment::leftjoin('transactions as t', 'transaction_payments.transaction_id', '=', 't.id')
                ->leftjoin('transaction_payments as parent_payment', 'transaction_payments.parent_id', '=', 'parent_payment.id')
                ->where('transaction_payments.business_id', $business_id)
                ->whereNull('transaction_payments.parent_id')
                ->with(['child_payments', 'child_payments.transaction'])
                ->where('transaction_payments.payment_for', $contact_id)
                ->select(
                    'transaction_payments.id',
                    'transaction_payments.amount',
                    'transaction_payments.is_return',
                    'transaction_payments.method',
                    'transaction_payments.paid_on',
                    'transaction_payments.payment_ref_no',
                    'transaction_payments.parent_id',
                    'transaction_payments.transaction_no',
                    't.invoice_no',
                    't.ref_no',
                    't.type as transaction_type',
                    't.return_parent_id',
                    't.id as transaction_id',
                    'transaction_payments.cheque_number',
                    'transaction_payments.card_transaction_number',
                    'transaction_payments.bank_account_number',
                    'transaction_payments.id as DT_RowId',
                    'parent_payment.payment_ref_no as parent_payment_ref_no'
                )
                ->groupBy('transaction_payments.id')
                ->orderByDesc('transaction_payments.paid_on')
                ->paginate();

            Log::info('Payments retrieved', [
                'count' => $payments->count(),
                'contact_id' => $contact_id,
            ]);
            $payment_types = $this->transactionUtil->payment_types(null, true, $business_id);

            return view('clinic::patient.patients.partials.contact_payments_tab')
                ->with(compact('payments', 'payment_types'));
            Log::warning('getContactPayments called without AJAX', [
                'business_id' => $business_id,
                'contact_id' => $contact_id,
            ]);
        }
    }
    public function profile($id)
    {
        $patient = PatientProfile::where('patient_contact_id', $id)->first();
        if ($patient) {
            $contact = Contact::with('lifeStage')->find($patient->patient_contact_id);
            if (!$contact) {
                abort(403, 'Unauthorized action.');
            }
        } else {
            abort(403, 'Unauthorized action.');
        }
        $physicalInfo = [];
        $prescription = Prescription::where('patient_contact_id', $id)->latest()->first();
        $familyHistoryDisease = [];
        $chironicDisease = [];
        $healthConcerns = [];

        // Get all active sessions for the patient
        $sessions = PatientSessionInfo::where('patient_contact_id', $id)
            ->where('remaining_visit', '>', 0)
            ->with('session', 'transaction')
            ->get();
            
         // Get visit history from PatientSessionDetails
        $visitHistory = PatientSessionDetails::where('patient_contact_id', $id)
            ->with(['patientSession' => function($query) {
                $query->with('session');
            }])
            ->orderBy('visit_date', 'desc')
            ->get();

        // Calculate next estimated visit (30 days after last visit)
        $nextEstimatedVisit = null;
        if ($visitHistory->count() > 0) {
            $lastVisitDate = \Carbon\Carbon::parse($visitHistory->first()->visit_date);
            $nextEstimatedVisit = $lastVisitDate->addDays(30)->format('M d, Y');
        }

        $intakeForm =  Intakeform::where('patient_contact_id', $id)->first();
        if ($intakeForm && !empty($intakeForm->family_history_disease)) {
            $problemIds = json_decode($intakeForm->family_history_disease);

            if (is_array($problemIds) && count($problemIds)) {
                $familyHistoryDisease = Problem::whereIn('id', $problemIds)->pluck('name')->toArray();
            }
        }
        if ($intakeForm && !empty($intakeForm->chironic_illness)) {
            $diseaseIds = json_decode($intakeForm->chironic_illness);
            if (is_array($diseaseIds) && count($diseaseIds)) {
                $chironicDisease = Problem::whereIn('id', $diseaseIds)->pluck('name')->toArray();
            }
        }
        if ($intakeForm) {
            $concerns = ReportAndProblem::where('intake_form_id', $intakeForm->id)->pluck('problem_id')->toArray();
            if (is_array($concerns) && count($concerns)) {
                $healthConcerns = Problem::whereIn('id', $concerns)->pluck('name')->toArray();
            }
        }
        if (!empty($prescription)) {
            $physicalInfo = [
                'weight' => $prescription->current_weight ?? '',
                'height' => ($prescription->current_height_feet !== null || $prescription->current_height_inches !== null)
                    ? ($prescription->current_height_feet ?? '0') . '.' . ($prescription->current_height_inches ?? '0')
                    : '',
                'bmi' => $prescription->bmi ?? '',
                'blood_pressure' => ($prescription->systolic_pressure !== null || $prescription->diastolic_pressure !== null)
                    ? ($prescription->systolic_pressure ?? '0') . '/' . ($prescription->diastolic_pressure ?? '0')
                    : '',
            ];
        }
        $service_types = [
            'therapy' => 'Therapy',
            'test' => 'Test',
            'ipd' => 'IPD',
            'consultation' => 'Consultation'
        ];
        return view('clinic::patient.patients.new_profile', compact('patient', 'contact', 'physicalInfo', 'familyHistoryDisease', 'chironicDisease', 'healthConcerns', 'id', 'service_types', 'sessions', 'visitHistory', 'nextEstimatedVisit'));
    }
    public function getLedger()
    {
        if (! auth()->user()->can('supplier.view') && ! auth()->user()->can('customer.view') && ! auth()->user()->can('supplier.view_own') && ! auth()->user()->can('customer.view_own')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $contact_id = request()->input('contact_id');

        $is_admin = $this->contactUtil->is_admin(auth()->user());

        $start_date = request()->start_date;
        $end_date = request()->end_date;
        $format = request()->format;
        $location_id = request()->location_id;

        $contact = Contact::find($contact_id);

        $is_selected_contacts = User::isSelectedContacts(auth()->user()->id);
        $user_contacts = [];
        if ($is_selected_contacts) {
            $user_contacts = auth()->user()->contactAccess->pluck('id')->toArray();
        }

        if (! auth()->user()->can('supplier.view') && auth()->user()->can('supplier.view_own')) {
            if ($contact->created_by != auth()->user()->id & ! in_array($contact->id, $user_contacts)) {
                abort(403, 'Unauthorized action.');
            }
        }
        if (! auth()->user()->can('customer.view') && auth()->user()->can('customer.view_own')) {
            if ($contact->created_by != auth()->user()->id & ! in_array($contact->id, $user_contacts)) {
                abort(403, 'Unauthorized action.');
            }
        }

        $line_details = $format == 'format_3' ? true : false;

        $ledger_details = $this->transactionUtil->getLedgerDetails($contact_id, $start_date, $end_date, $format, $location_id, $line_details);

        $location = null;
        if (! empty($location_id)) {
            $location = BusinessLocation::where('business_id', $business_id)->find($location_id);
        }
        if (request()->input('action') == 'pdf') {
            $output_file_name = 'Ledger-' . str_replace(' ', '-', $contact->name) . '-' . $start_date . '-' . $end_date . '.pdf';
            $for_pdf = true;
            if ($format == 'format_2') {
                $html = view('clinic::patient.patients.ledger.ledger_format_2')
                    ->with(compact('ledger_details', 'contact', 'for_pdf', 'location'))->render();
            } elseif ($format == 'format_3') {
                $html = view('clinic::patient.patients.ledger.ledger_format_3')
                    ->with(compact('ledger_details', 'contact', 'location', 'is_admin', 'for_pdf'))->render();
            } else {
                $html = view('clinic::patient.patients.ledger.ledger')
                    ->with(compact('ledger_details', 'contact', 'for_pdf', 'location'))->render();
            }

            $mpdf = $this->getMpdf();
            $mpdf->WriteHTML($html);
            $mpdf->Output($output_file_name, 'I');
        }

        if ($format == 'format_2') {
            return view('clinic::patient.patients.ledger.ledger_format_2')
                ->with(compact('ledger_details', 'contact', 'location'));
        } elseif ($format == 'format_3') {
            return view('clinic::patient.patients.ledger.ledger_format_3')
                ->with(compact('ledger_details', 'contact', 'location', 'is_admin'));
        } else {
            return view('clinic::patient.patients.ledger.ledger')
                ->with(compact('ledger_details', 'contact', 'location', 'is_admin'));
        }
    }
    public function updateStatus($id)
    {
        if (! auth()->user()->can('supplier.update') && ! auth()->user()->can('customer.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $contact = Contact::where('business_id', $business_id)->find($id);
            $contact->contact_status = $contact->contact_status == 'active' ? 'inactive' : 'active';
            $contact->save();

            $output = [
                'success' => true,
                'msg' => __('contact.updated_success'),
            ];

            return $output;
        }
    }
    public function getReference($id)
    {
        $latestTransaction = Transaction::where('contact_id', $id)
            ->latest()
            ->first();

        if ($latestTransaction) {
            return response()->json([
                'success' => true,
                'reference_id' => $latestTransaction->reference_id
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No transaction found for this contact_id'
            ]);
        }
    }
    public function getClinicCustomer()
    {
        if (request()->ajax()) {
            $term = request()->input('q', '');

            $business_id = request()->session()->get('user.business_id');
            $user_id = request()->session()->get('user.id');

            $contacts = Contact::where('contacts.business_id', $business_id)
                ->leftJoin('customer_groups as cg', 'cg.id', '=', 'contacts.customer_group_id')
                // ->leftJoin('patient_profiles as p_profile', 'p_profile.patient_contact_id', '=', 'contacts.id')
                ->active();
            // ->whereNotNull('p_profile.patient_contact_id');

            // if (!request()->has('all_contact')) {
            //     $contacts->onlyCustomers();
            // }

            if (!empty($term)) {
                $contacts->where(function ($query) use ($term) {
                    $query->where('contacts.name', 'like', '%' . $term . '%')
                        ->orWhere('supplier_business_name', 'like', '%' . $term . '%')
                        ->orWhere('contact_id', 'like', '%' . $term . '%')
                        ->orWhere('contacts.contact_id', 'like', '%' . $term . '%')
                        ->orWhere('contacts.mobile', 'like', '%' . $term . '%'); // Specify the table for mobile
                });
            }

            $contacts->select(
                'contacts.id',
                DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', contacts.name, CONCAT(contacts.name, ' (', contacts.contact_id, ')')) AS text"),
                'contacts.mobile', // Specify the table for mobile
                'contacts.address_line_1',
                'contacts.address_line_2',
                'contacts.city',
                'contacts.state',
                'contacts.country',
                'contacts.zip_code',
                'contacts.shipping_address',
                'contacts.pay_term_number',
                'contacts.pay_term_type',
                'contacts.balance',
                'contacts.supplier_business_name',
                'cg.amount as discount_percent',
                'cg.price_calculation_type',
                'cg.selling_price_group_id',
                'contacts.shipping_custom_field_details',
                'contacts.is_export',
                'contacts.type',
                'contacts.crm_life_stage',
                'contacts.export_custom_field_1',
                'contacts.export_custom_field_2',
                'contacts.export_custom_field_3',
                'contacts.export_custom_field_4',
                'contacts.export_custom_field_5',
                'contacts.export_custom_field_6'
            );

            if (request()->session()->get('business.enable_rp') == 1) {
                $contacts->addSelect('contacts.total_rp');
            }

            $contacts = $contacts->get();

            return json_encode($contacts);
        }
    }
    public function updateCustomerInfo(Request $request, $id)
    {
        try {
            Log::info('updateCustomerInfo called', [
                'contact_id_param' => $id,
                'request_data' => $request->all()
            ]);

            $contact = DB::table('contacts')->where('contact_id', $id)->first();
            Log::info('Contact found in database ' . json_encode($contact));
            if (!$contact) {
                Log::warning('Contact not found', ['contact_id' => $id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Contact not found.',
                ], 404);
            }

            // ✅ Validation
            $validator = Validator::make($request->all(), [
                'first_name' => 'nullable|string|max:255',
                'mobile' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'city' => 'nullable|string|max:255',
                'address' => 'nullable|string|max:255',
            ]);

            Log::info('Validation result', [
                'request_data' => $request->all()
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // ✅ Input processing
            $input = $request->only(['first_name', 'mobile', 'city', 'email', 'address']);

            // ✅ Concat name
            $name_array = [];
            if (!empty($input['prefix'])) {
                $name_array[] = $input['prefix'];
            }
            if (!empty($input['first_name'])) {
                $name_array[] = $input['first_name'];
            }
            if (!empty($input['middle_name'])) {
                $name_array[] = $input['middle_name'];
            }
            if (!empty($input['last_name'])) {
                $name_array[] = $input['last_name'];
            }
            $input['name'] = trim(implode(' ', $name_array));

            // ✅ Format address
            if (isset($input['address'])) {
                $input['address_line_1'] = $input['address'];
                unset($input['address']);
            }

            // ✅ Filter out nulls only (NOT empty string)
            $updateData = array_filter($input, function ($value) {
                return !is_null($value);
            });

            // ✅ Add updated_at
            $updateData['updated_at'] = now();

            // ✅ Do update
            DB::beginTransaction();
            $contact = Contact::where('contact_id', $id)->first();

            if ($contact) {
                $contact->update($updateData);

                $profile =  PatientProfile::where('patient_contact_id', $contact->id)->first();
                $profile->first_name = $input['name'];
                $profile->mobile = $input['mobile'];
                $profile->city = $input['city'];
                $profile->email = $input['email'];
                $profile->address = $input['address'];
                $profile->save();
            }
            DB::commit();
            Log::info('Update process result', [
                'update_data' => $updateData,
                'affected_rows' => $contact,
            ]);

            if ($contact === 0) {
                return response()->json([
                    'success' => true,
                    'message' => 'No changes made (data may be same).',
                ], 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Contact information updated successfully.',
                'updated_data' => DB::table('contacts')->where('contact_id', $id)->first()
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Exception in updateCustomerInfo', [
                'contact_id' => $id,
                'error' => $th->getMessage(),
            ]);
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Internal Server Error: Failed to update contact information.',
            ], 500);
        }
    }
    public function patientOnlyNameEdit($id)
    {
        $contact = Contact::where('id', $id)->first();
        return view('clinic::patient.patients.onlyNameEdit', compact('contact'));
    }
    public function updatePatientName(Request $request, $id)
    {
        

        

        try {
            DB::beginTransaction();

            $contact = Contact::findOrFail($id); // get contact first
            // যদি request এ first_name না থাকে বা null হয়, তাহলে পুরাতনটা ব্যবহার করবে
            $firstName = $request->input('first_name') ?? $contact->first_name;
            
            $payload = [
                'first_name' => $firstName,
                'mobile'     => $request->filled('mobile') ? $request->mobile : $contact->mobile, // pass new or current
            ];
    

            // Update local DB
            $contact->name = $firstName;
            $contact->first_name = $firstName;
            $contact->mobile = $payload['mobile'];
            $contact->save();

            $profile = PatientProfile::where('patient_contact_id', $contact->id)->first();
            if ($profile) {
                $profile->first_name = $firstName;
                $profile->mobile = $payload['mobile'];
                $profile->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'msg' => 'Patient name updated successfully.',
                'data'=> $contact
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update patient name: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'msg' => 'Update failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getMobileUpdateModal($id){
        $contact = Contact::find($id);
        return view('clinic::patient.patients.mobile_no_update_modal', compact('contact'));
    }
}
