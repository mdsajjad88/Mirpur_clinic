<?php

namespace Modules\Crm\Http\Controllers;

use App\Business;
use App\Contact;
use App\CustomerGroup;
use App\SellingPriceGroup;
use App\Transaction;
use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Variation;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Modules\Crm\Entities\Campaign;
use Modules\Crm\Entities\CrmContact;
use Modules\Crm\Notifications\SendCampaignNotification;
use Notification;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Modules\Crm\Entities\CrmSendingDetail;

class CampaignController extends Controller
{
    protected $notificationUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  NotificationUtil  $notificationUtil
     * @return void
     */
    public function __construct(NotificationUtil $notificationUtil, ModuleUtil $moduleUtil)
    {
        $this->notificationUtil = $notificationUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function showCampWiseModal($id)
    {
        // Log the ID of the campaign being queried
        Log::info('Fetching sending details for campaign ID: ' . $id);

        // Retrieve the campaign and decode contact_ids if necessary
        $campaign = Campaign::findOrFail($id);
        $contact_ids = is_array($campaign->contact_ids) ? $campaign->contact_ids : json_decode($campaign->contact_ids, true);

        // Calculate the total number of contacts associated with this campaign
        $total_contacts = 0;
        if (is_array($contact_ids) && !empty($contact_ids)) {
            $total_contacts = Contact::whereIn('id', $contact_ids)->count();
        }

        // Prepare the base query for CrmSendingDetail
        $sendingQuery = CrmSendingDetail::with('campaign', 'sendBy')
            ->where('crm_campaign_id', $id);

        // Clone the query for separate counts
        $pendingCountQuery = clone $sendingQuery;
        $deliveredCountQuery = clone $sendingQuery;
        $failedCountQuery = clone $sendingQuery;

        // Get count for each status
        $pending = $pendingCountQuery->where('status', 'Pending')->count();
        $delivered = $deliveredCountQuery->where('status', 'Delivered')->count();
        $failed = $failedCountQuery->where('status', 'Failed')->count();

        // Retrieve all sending details with no status filtering
        $sending_details = $sendingQuery->get();
        // Return the view with all the data needed
        return view('crm::campaign.camp_modal', compact('sending_details', 'failed', 'pending', 'total_contacts', 'delivered','id'));
    }
    public function getProductRow(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if ($request->ajax()) {
            try {
                $row_index = $request->input('row_index');
                $contact_id = $request->input('contact_id');
                $location_id = $request->input('location_id');
                $leads = $request->input('leads') ?? null;
                $group_id = $request->input('customer_group_id') ?? null;
                $transactions_count = $request->input('transactions_count') ?? null;
                $days = $request->input('transactions_days') ?? null;

                $contact = Contact::where('contacts.business_id', $business_id)->where('send_sms',1)
                    ->leftJoin('customer_groups as cg', 'cg.id', '=', 'contacts.customer_group_id')
                    ->active(); // Assuming 'active' is a local scope or a query filter

                if (!empty($contact_id)) {
                    $contact->where('contacts.id', $contact_id);
                }
                if (!empty($leads) && $leads == 'lead' || $leads == 'customer') {
                    $contact->where('type', $leads);
                }
                if (!empty($group_id)) {
                    $contact->where('cg.id', $group_id);
                }
                if ($leads == 'transaction_activity') {
                    $day = Carbon::now()->subDays($days)->toDateTimeString();

                    // Create the base query to find the last shopped date for each contact
                    $query = Transaction::where('business_id', $business_id)
                        ->select('contact_id', DB::raw('MAX(transaction_date) as last_shopped'));

                    // Add condition based on transaction activity type
                    if ($transactions_count == 'has_transactions') {
                        $query->having('last_shopped', '>=', $day);
                    } elseif ($transactions_count == 'has_no_transactions') {
                        $query->having('last_shopped', '<=', $day);
                    }

                    // Fetch transactions based on the conditions
                    $transactions = $query->groupBy('contact_id')->get();

                    // Extract contact IDs
                    $contact_ids = $transactions->pluck('contact_id')->toArray();
                    if (!empty($contact_ids)) {
                        $contact->whereIn('contacts.id', $contact_ids);
                    }
                    Log::info([
                        'contact' => $contact->count(),
                        'transactions' => $transactions->count(),
                        'day' => $day
                    ]);
                }


                // $notifiable_users = CrmContact::find($contact_ids);

                $contact->select(
                    'contacts.id',
                    DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', contacts.name, CONCAT(contacts.name, ' (', contacts.contact_id, ')')) AS text"),
                    'mobile',

                );
                $getContact = $contact->get(); // Fetch all contacts if no specific contact_id is provided
                return view('crm::campaign.row_customer')
                    ->with(compact('row_index', 'contact_id', 'getContact'));
            } catch (ModelNotFoundException $e) {
                Log::error('Model not found in getProductRow', [
                    'row_index' => $row_index,
                    'variation_id' => $contact_id,
                    'location_id' => $location_id,
                    'exception' => $e->getMessage()
                ]);
                return response()->json(['error' => 'Product not found.'], 404);
            } catch (\Exception $e) {
                Log::error('Error in getProductRow', [
                    'row_index' => $row_index,
                    'variation_id' => $contact_id,
                    'location_id' => $location_id,
                    'exception' => $e->getMessage()
                ]);
                return response()->json(['error' => 'An error occurred while processing your request.'], 500);
            }
        }
        return response()->json(['error' => 'Invalid request.'], 400);
    }
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $can_access_all_campaigns = auth()->user()->can('crm.access_all_campaigns');
        $can_access_own_campaigns = auth()->user()->can('crm.access_own_campaigns');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module')) || ! ($can_access_all_campaigns || $can_access_own_campaigns)) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $campaigns = Campaign::with('createdBy')
                ->where('business_id', $business_id)
                ->select('*');

            if (! $can_access_all_campaigns && $can_access_own_campaigns) {
                $campaigns->where('created_by', auth()->user()->id);
            }

            if (! empty(request()->get('campaign_type'))) {
                $campaigns->where('campaign_type', request()->get('campaign_type'));
            }

            return Datatables::of($campaigns)
                ->addColumn('action', function ($row) {
                    $html = '<a data-href="' . action([\Modules\Crm\Http\Controllers\CampaignController::class, 'show'], ['campaign' => $row->id]) . '" class="cursor-pointer view_a_campaign btn btn-xs btn-info m-2">
                            <i class="fa fa-eye"></i>
                            ' . __('messages.view') . '
                            </a>';

                    if (empty($row->sent_on)) {
                        $html .= '
                            <a href="' . action([\Modules\Crm\Http\Controllers\CampaignController::class, 'edit'], ['campaign' => $row->id]) . '"class="cursor-pointer btn btn-xs btn-primary m-2">
                                <i class="fa fa-edit"></i>
                                ' . __('messages.edit') . '
                            </a>';
                    }

                    $html .= '<a data-href="' . action([\Modules\Crm\Http\Controllers\CampaignController::class, 'destroy'], ['campaign' => $row->id]) . '" class="cursor-pointer delete_a_campaign btn btn-xs btn-danger m-2">
                            <i class="fas fa-trash"></i>
                            ' . __('messages.delete') . '
                            </a>';

                    if (empty($row->sent_on)) {
                        $html .= '<a data-href="' . action([\Modules\Crm\Http\Controllers\CampaignController::class, 'sendNotification'], ['id' => $row->id]) . '" class="cursor-pointer send_campaign_notification btn btn-xs btn-warning m-2">
                                <i class="fas fa-envelope-square"></i>
                                ' . __('crm::lang.send_notification') . '
                            </a>';
                    }
                    $html .= '<a data-href="' . action([\Modules\Crm\Http\Controllers\CampaignController::class, 'showCampWiseModal'], ['id' => $row->id]) . '" class="show_data_campaign_wise btn btn-xs btn-success m-2">
                        <i class="fas fa-envelope-square"></i>
                        ' . __('crm::lang.view_modal') . '
                        </a>';


                    return $html;
                })
                ->editColumn('campaign_type', '
                        @if($campaign_type == "sms")
                            {{__("crm::lang.sms")}}
                        @elseif($campaign_type == "email")
                            {{__("business.email")}}
                        @endif
                    ')
                ->editColumn('created_at', '
                        {{@format_date($created_at)}}
                    ')
                ->editColumn('name', function ($row) {
                    $is_notified = '';
                    if (! empty($row->sent_on)) {
                        $is_notified = '<br> <span class="label label-success">' .
                            __('crm::lang.sent') .
                            '</span>';
                    }

                    return $row->name . $is_notified;
                })
                ->editColumn('start', function ($row) {
                    $is_notified = '';
                    if (! empty($row->start_on)) {
                        $is_notified =$row->start_on;
                    }

                    return $is_notified;
                })
                ->editColumn('createdBy', function ($row) {
                    return $row->createdBy?->user_full_name;
                })
                ->removeColumn('id')
                ->rawColumns(['action','start','name', 'campaign_type', 'createdBy', 'created_at'])
                ->make(true);
        }

        return view('crm::campaign.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        $can_access_all_campaigns = auth()->user()->can('crm.access_all_campaigns');
        $can_access_own_campaigns = auth()->user()->can('crm.access_own_campaigns');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module')) || ! ($can_access_all_campaigns || $can_access_own_campaigns)) {
            abort(403, 'Unauthorized action.');
        }

        $tags = Campaign::getTags();
        $contact_ids = $request->get('contact_ids', '');
        $leads = CrmContact::leadsDropdown($business_id, false);
        $customers = CrmContact::customersDropdown($business_id, false);

        $contacts = [];
        foreach ($leads as $key => $lead) {
            $contacts[$key] = $lead;
        }

        foreach ($customers as $key => $customer) {
            $contacts[$key] = $customer;
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);
        return view('crm::campaign.create')
            ->with(compact('tags', 'leads', 'customers', 'contact_ids', 'contacts','customer_groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only('name', 'campaign_type', 'subject', 'email_body', 'sms_body');

            $input['business_id'] = $business_id;
            $input['created_by'] = $request->user()->id;
            $customers = $request->input('contact_id', []);
            $leads = $request->input('lead_id', []);
            $contacts = $request->input('contact', []); //birthday_wishes
            // dd($customers);
            $input['contact_ids'] = array_merge($customers, $leads, $contacts);

            $input['additional_info'] = [
                'to' => $request->input('to'),
                'trans_activity' => $request->input('trans_activity'),
                'in_days' => $request->input('in_days'),
            ];

            DB::beginTransaction();

            $campaign = Campaign::create($input);

            DB::commit();

            if ($request->get('send_notification') && ! empty($campaign)) {
                $this->__sendCampaignNotification($campaign->id, $business_id);
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (Exception $e) {
            DB::rollBack();

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()
            ->action([\Modules\Crm\Http\Controllers\CampaignController::class, 'index'])
            ->with('status', $output);
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $can_access_all_campaigns = auth()->user()->can('crm.access_all_campaigns');
        $can_access_own_campaigns = auth()->user()->can('crm.access_own_campaigns');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module')) || ! ($can_access_all_campaigns || $can_access_own_campaigns)) {
            abort(403, 'Unauthorized action.');
        }

        $query = Campaign::with('createdBy')
            ->where('business_id', $business_id);

        if (! $can_access_all_campaigns && $can_access_own_campaigns) {
            $query->where('created_by', auth()->user()->id);
        }

        $campaign = $query->findOrFail($id);

        $notifiable_users = CrmContact::find($campaign->contact_ids);

        return view('crm::campaign.show')
            ->with(compact('campaign', 'notifiable_users'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $can_access_all_campaigns = auth()->user()->can('crm.access_all_campaigns');
        $can_access_own_campaigns = auth()->user()->can('crm.access_own_campaigns');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module')) || ! ($can_access_all_campaigns || $can_access_own_campaigns)) {
            abort(403, 'Unauthorized action.');
        }

        $query = Campaign::where('business_id', $business_id);

        if (! $can_access_all_campaigns && $can_access_own_campaigns) {
            $query->where('created_by', auth()->user()->id);
        }

        $campaign = $query->findOrFail($id);
        $notifiable_users = $campaign->contact_ids;
        $contacts = Contact::whereIn('contacts.id', $notifiable_users)->select(
            'contacts.id',
            DB::raw("IF(contacts.contact_id IS NULL OR contacts.contact_id='', contacts.name, CONCAT(contacts.name, ' (', contacts.contact_id, ')')) AS text"),
            'mobile',

        )->get();
        $tags = Campaign::getTags();
        $leads = CrmContact::leadsDropdown($business_id, false);
        $customers = CrmContact::customersDropdown($business_id, false);
        $customer_groups = CustomerGroup::forDropdown($business_id);
        return view('crm::campaign.edit')
            ->with(compact('tags', 'campaign', 'contacts','customer_groups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $business_id = request()->session()->get('user.business_id');
        $can_access_all_campaigns = auth()->user()->can('crm.access_all_campaigns');
        $can_access_own_campaigns = auth()->user()->can('crm.access_own_campaigns');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module')) || ! ($can_access_all_campaigns || $can_access_own_campaigns)) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $input = $request->only('name', 'campaign_type', 'subject', 'email_body', 'sms_body');

            $customers = $request->input('contact_id', []);
            $leads = $request->input('lead_id', []);
            $contacts = $request->input('contact', []); //birthday_wishes

            $input['contact_ids'] = array_merge($customers, $leads, $contacts);

            $input['additional_info'] = [
                'to' => $request->input('to'),
                'trans_activity' => $request->input('trans_activity'),
                'in_days' => $request->input('in_days'),
            ];

            $query = Campaign::where('business_id', $business_id);

            if (! $can_access_all_campaigns && $can_access_own_campaigns) {
                $query->where('created_by', auth()->user()->id);
            }

            $campaign = $query->findOrFail($id);

            DB::beginTransaction();

            $campaign->update($input);

            DB::commit();

            if ($request->get('send_notification') && ! empty($campaign)) {
                $this->__sendCampaignNotification($campaign->id, $business_id);
            }

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (Exception $e) {
            DB::rollBack();

            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()
            ->action([\Modules\Crm\Http\Controllers\CampaignController::class, 'index'])
            ->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $business_id = request()->session()->get('user.business_id');
        $can_access_all_campaigns = auth()->user()->can('crm.access_all_campaigns');
        $can_access_own_campaigns = auth()->user()->can('crm.access_own_campaigns');

        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module')) || ! ($can_access_all_campaigns || $can_access_own_campaigns)) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                $query = Campaign::where('business_id', $business_id);

                if (! $can_access_all_campaigns && $can_access_own_campaigns) {
                    $query->where('created_by', auth()->user()->id);
                }

                $query->where('id', $id)
                    ->delete();

                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.success'),
                ];
            } catch (Exception $e) {
                \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());

                $output = [
                    'success' => false,
                    'msg' => __('messages.something_went_wrong'),
                ];
            }

            return $output;
        }
    }

    public function sendNotification($id)
    {
        $business_id = request()->session()->get('user.business_id');
        if (! (auth()->user()->can('superadmin') || $this->moduleUtil->hasThePermissionInSubscription($business_id, 'crm_module'))) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $output = $this->__sendCampaignNotification($id, $business_id);

            return $output;
        }
    }

    public function __sendCampaignNotification($campaign_id, $business_id)
    {
        try {
            $campaign = Campaign::where('business_id', $business_id)
                ->findOrFail($campaign_id);
                $campaign->start_on = Carbon::now();
            $business = Business::findOrFail($business_id);
            $user_id = request()->session()->get('user.id');
            $sms_settings = $business->sms_settings;

            // Extract username, password, and URL from sms_settings
            $username = $sms_settings['send_to_param_name'];
            $password = $sms_settings['msg_param_name'];
            $url = $sms_settings['url'];
            $notifiable_users = $campaign->contact_ids;
            $contacts = Contact::whereIn('contacts.id', $notifiable_users)->get();
            
            if (!empty($notifiable_users) && $campaign->campaign_type == 'sms') {
                $notification_data['sms_settings'] = request()->session()->get('business.sms_settings');

                foreach ($contacts as $user) {
                    // Check if there is an existing record with the campaign and customer, and if delivered status exists
                    $crmSendingDetail = CrmSendingDetail::where('crm_campaign_id', $campaign_id)
                        ->where('customer_id', $user->id)
                        ->first();

                    // Initialize flag to track if a new record was created
                    $is_new_record = false;

                    if (!$crmSendingDetail) {
                        // Create a new record if it does not exist
                        $crmSendingDetail = CrmSendingDetail::create([
                            'crm_campaign_id' => $campaign_id,
                            'customer_id' => $user->id,
                            'customer_name' => $user->name,
                            'mobile' => $user->mobile,
                            'send_by' => $user_id,
                            'notification_date' => now(),
                            'status' => 'Pending',
                        ]);
                        Log::info([
                            'crm_campaign_id' => $campaign_id,
                            'customer_id' => $user->id,
                            'customer_name' => $user->name,
                            'mobile' => $user->mobile,
                            'send_by' => $user_id,
                            'notification_date' => now(),
                            'status' => 'Pending',
                        ]);
                        $is_new_record = true;
                    }

                    // Check if the status is not 'Delivered' (only send if not delivered)
                    if ($is_new_record || $crmSendingDetail->status !== 'Delivered') {
                        // Send SMS notification
                        $params = [
                            'user' => $username,
                            'password' => $password,
                            'to' => $user->mobile,
                            'text' => $campaign->sms_body,
                        ];

                        try {
                            $response = Http::get($url, $params)->json();

                            // Log::info([
                            //     'response' => $response,
                            //     'success' => $response['success'],
                            // ]);

                            // Update status based on SMS response
                            if ($response['success'] == 1) {
                                $crmSendingDetail->status = 'Delivered';
                                $crmSendingDetail->notification_date = now();
                            } else {
                                $crmSendingDetail->status = 'Failed';
                                $crmSendingDetail->notification_date = now();
                            }
                        } catch (\Exception $e) {
                            // Log error and set status to failed in case of exception
                            Log::error('SMS sending failed for user: ' . $user->mobile . ', Error: ' . $e->getMessage());
                            $crmSendingDetail->status = 'Failed';
                            $crmSendingDetail->notification_date = now();
                        }

                        // Save the updated status
                        $crmSendingDetail->save();
                    }
                }
            } elseif (! empty($notifiable_users) && $campaign->campaign_type == 'email') {
                Notification::send($notifiable_users, new SendCampaignNotification($campaign, $business));
            }
            DB::beginTransaction();

            
            $campaign->sent_on = Carbon::now();
            $campaign->save();

            DB::commit();

            $output = [
                'success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (Exception $e) {
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
