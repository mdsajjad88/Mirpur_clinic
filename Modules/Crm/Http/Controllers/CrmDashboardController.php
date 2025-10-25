<?php

namespace Modules\Crm\Http\Controllers;

use App\Category;
use App\Contact;
use App\User;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\CrmCallLog;
use Modules\Crm\Entities\CrmContact;
use Modules\Crm\Entities\Schedule;
use Modules\Crm\Utils\CrmUtil;
use Carbon\Carbon;
use Modules\Clinic\Entities\Reference;
use Yajra\DataTables\Facades\DataTables;
class CrmDashboardController extends Controller
{
    protected $crmUtil;

    /**
     * Constructor
     *
     * @param  Util  $commonUtil
     * @return void
     */
    public function __construct(CrmUtil $crmUtil)
    {
        $this->crmUtil = $crmUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $business_id = request()->session()->get('user.business_id');

        $contacts = Contact::where('business_id', $business_id)
                    ->Active()
                    ->get();

        $customers = $contacts->whereIn('type', ['customer', 'both']);

        $leads = $contacts->where('type', 'lead');

        $total_customers = $customers->count();

        $total_leads = $leads->count();
        $sources = Reference::get();
        $total_sources = $sources->count();

        $life_stages = Category::where('business_id', $business_id)
                                ->where('category_type', 'life_stage')
                                ->get();

        $total_life_stage = $life_stages->count();
        $leads_by_life_stage = $leads->groupBy('crm_life_stage');

        $contacts_count_by_source = CrmContact::getContactsCountBySourceOfGivenTyps($business_id);

        $leads_count_by_source = CrmContact::getContactsCountBySourceOfGivenTyps($business_id, ['lead']);

        $customers_count_by_source = CrmContact::getContactsCountBySourceOfGivenTyps($business_id, ['customer', 'both']);

        $todays_birthdays = array_merge($this->getBirthdays($customers)['todays_birthdays'], $this->getBirthdays($leads)['todays_birthdays']);

        $upcoming_birthdays = array_merge($this->getBirthdays($customers)['upcoming_birthdays'], $this->getBirthdays($leads)['upcoming_birthdays']);

        $my_follow_ups = $this->myFollowUps();

        $my_follow_ups_arr = [];
        foreach ($my_follow_ups as $follow_up) {
            if (! empty($follow_up->status)) {
                $my_follow_ups_arr[$follow_up->status] = $follow_up->total_follow_ups;
            } else {
                $my_follow_ups_arr['__other'] = $follow_up->total_follow_ups;
            }
        }

        $statuses = Schedule::statusDropdown();

        $my_leads = $this->myLeads();

        $my_conversion = $this->myConversion();

        $todays_followups = $this->todaysFollowUp();

        $filter_user_id = request()->get('filter_user_id');
        $my_call_logs = config('constants.enable_crm_call_log') ? $this->getMyCallLogs($filter_user_id) : [];


        $followup_category = Category::forDropdown($business_id, 'followup_category');

        $is_admin = $this->crmUtil->is_admin(auth()->user());

        $users = User::allUsersDropdown($business_id);

        return view('crm::crm_dashboard.index')->with(compact('total_customers', 'total_leads', 'total_sources', 'total_life_stage', 'leads_by_life_stage', 'sources', 'life_stages', 'todays_birthdays', 'upcoming_birthdays', 'leads_count_by_source', 'contacts_count_by_source', 'customers_count_by_source', 'my_follow_ups_arr', 'statuses', 'my_leads', 'my_conversion', 'todays_followups', 'my_call_logs', 'followup_category', 'is_admin', 'users'));
    }

    /**
     * Function to fetch all the followups of the logged in user
     */
    private function myFollowUps()
    {
        $my_follow_ups = User::user()
                    ->where('users.id', auth()->user()->id)
                    ->join('crm_schedule_users as su', 'su.user_id', '=', 'users.id')
                    ->join('crm_schedules as follow_ups', 'follow_ups.id', '=', 'su.schedule_id')
                    ->select(
                        'follow_ups.status',
                        DB::raw('COUNT(su.id) as total_follow_ups')
                    )->groupBy('follow_ups.status')->get();

        return $my_follow_ups;
    }

    /**
     * Function to fetch call log statistic of the logged in user
     */
    private function getMyCallLogs($filter_user_id = null)
    {
        $business_id = request()->session()->get('user.business_id');

        // Check if the user is admin or has permission to access all call logs
        $is_admin = $this->crmUtil->is_admin(auth()->user(), $business_id) || auth()->user()->can('crm.access_all_call_log');

        $today = Carbon::now()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');
        $first_day_of_month = Carbon::now()->startOfMonth()->format('Y-m-d');

        $query = CrmCallLog::where('business_id', $business_id);

        // Filter by user only if not admin and no user filter is applied
        if ($filter_user_id && $is_admin) {
            $query->where('created_by', $filter_user_id);
        } elseif (! $is_admin) {
            $query->where('created_by', auth()->user()->id);
        }

        return $query->select(
            DB::raw("SUM(IF(DATE(start_time) = '{$today}' AND call_type = 'inbound', 1, 0)) as today_inbound"),
            DB::raw("SUM(IF(DATE(start_time) = '{$today}' AND call_type = 'outbound', 1, 0)) as today_outbound"),
            DB::raw("SUM(IF(DATE(start_time) = '{$yesterday}' AND call_type = 'inbound', 1, 0)) as yesterday_inbound"),
            DB::raw("SUM(IF(DATE(start_time) = '{$yesterday}' AND call_type = 'outbound', 1, 0)) as yesterday_outbound"),
            DB::raw("SUM(IF(DATE(start_time) >= '{$first_day_of_month}' AND call_type = 'inbound', 1, 0)) as month_inbound"),
            DB::raw("SUM(IF(DATE(start_time) >= '{$first_day_of_month}' AND call_type = 'outbound', 1, 0)) as month_outbound")
        )->first();
    }

    public function getAllUsersCallLogs()
    {
        $business_id = request()->session()->get('user.business_id');

        $query = CrmCallLog::where('crm_call_logs.business_id', $business_id)
            ->join('users as u', 'crm_call_logs.created_by', '=', 'u.id')
            ->join('patient_appointment_requests as par', 'crm_call_logs.contact_id', '=', 'par.patient_contact_id');

        if (request()->input('start_date') && request()->input('end_date')) {
            $start = Carbon::parse(request()->input('start_date'))->startOfDay();
            $end = Carbon::parse(request()->input('end_date'))->endOfDay();

            $query->whereBetween('par.request_date', [$start, $end]);
        }

        $query->select(
                'u.id as user_id',
                'u.first_name',
                'u.last_name',
                DB::raw("SUM(CASE WHEN par.remarks IN ('prescribed', 'expired') THEN 1 ELSE 0 END) as par_count"),
                DB::raw("SUM(CASE WHEN par.remarks = 'prescribed' AND par.type = 'New' THEN 1 ELSE 0 END) as new_count"),
                DB::raw("SUM(CASE WHEN par.remarks = 'prescribed' AND par.type = 'Followup' THEN 1 ELSE 0 END) as followup_count"),
                DB::raw("SUM(CASE WHEN par.remarks = 'prescribed' AND par.type = 'Old' THEN 1 ELSE 0 END) as old_count"),
                DB::raw("SUM(CASE WHEN par.remarks = 'prescribed' THEN 1 ELSE 0 END) as confirm_count"),
                DB::raw("SUM(CASE WHEN par.remarks = 'expired' THEN 1 ELSE 0 END) as not_confirmed_count"),
                
            )
            ->groupBy('u.id');

        return Datatables::of($query)
            ->editColumn('name', function ($row) {
                return $row->first_name . ' ' . $row->last_name;
            })
            ->addColumn('New', function ($row) {
                return $row->new_count;
            })
            ->addColumn('Followup', function ($row) {
                return $row->followup_count;
            })
            ->addColumn('Old', function ($row) {
                return $row->old_count;
            })
            ->addColumn('Confirm', function ($row) {
                return $row->confirm_count;
            })
            ->addColumn('NotConfirmed', function ($row) {
                return $row->not_confirmed_count;
            })
            ->make(true);
    }

    public function getCallSubjectSummary(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');

            $query = DB::table('crm_call_logs')
                ->join('crm_call_log_call_subject', 'crm_call_logs.id', '=', 'crm_call_log_call_subject.crm_call_log_id')
                ->join('crm_call_subjects as subjects', 'crm_call_log_call_subject.crm_call_subject_id', '=', 'subjects.id')
                ->where('crm_call_logs.business_id', $business_id)
                ->select(
                    'subjects.name as subject_name',
                    DB::raw("SUM(IF(crm_call_logs.call_type = 'inbound', 1, 0)) as inbound"),
                    DB::raw("SUM(IF(crm_call_logs.call_type = 'outbound', 1, 0)) as outbound"),
                    DB::raw("COUNT(DISTINCT crm_call_logs.id) as total")
                )
                ->groupBy('subjects.id', 'subjects.name');

            if ($request->filled('user_id')) {
                $query->where('crm_call_logs.created_by', $request->user_id);
            }

            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->whereDate('crm_call_logs.start_time', '>=', $request->start_date)
                    ->whereDate('crm_call_logs.start_time', '<=', $request->end_date);
            }

            // Apply ordering after all filters
            $query->orderByDesc('total');

            return DataTables::of($query)->make(true);

        } catch (\Exception $e) {
            \Log::error('Error in getCallSubjectSummary: ' . $e->getMessage());
            return response()->json(['error' => 'An error occurred while processing your request'], 500);
        }
    }

    /**
     * Function to fetch all the followups of the logged in user
     */
    private function todaysFollowUp()
    {
        $todays_followups = Schedule::whereHas('users', function ($q) {
            $q->where('user_id', auth()->user()->id);
        })
                    ->whereIn('status', ['open', 'scheduled'])
                    ->whereDate('start_datetime', Carbon::now()->format('Y-m-d'))
                    ->count();

        return $todays_followups;
    }

    /**
     * Function to count all the leads of the logged in user
     */
    private function myLeads()
    {
        $business_id = request()->session()->get('user.business_id');

        $total_leads = CrmContact::where('contacts.business_id', $business_id)
                        ->where('contacts.type', 'lead')
                        ->whereHas('leadUsers', function ($q) {
                            $q->where('user_id', auth()->user()->id);
                        })->count();

        return $total_leads;
    }

    /**
     * Function to count all the leads to customer conversion of the logged in user
     */
    private function myConversion()
    {
        $count = Contact::where('converted_by', auth()->user()->id)
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->count();

        return $count;
    }

    private function getBirthdays($contacts)
    {
        $todays_birthdays = [];
        $upcoming_birthdays = [];

        $today = Carbon::now();
        $thirty_days_from_today = Carbon::now()->addDays(30)->format('Y-m-d');
        foreach ($contacts as $contact) {
            if (empty($contact->dob)) {
                continue;
            }

            $dob = Carbon::parse($contact->dob);
            $dob_md = $dob->format('m-d');

            $next_birthday = Carbon::parse($today->format('Y').'-'.$dob_md);
            if ($next_birthday->lt($today)) {
                $next_birthday->addYear();
            }

            if ($today->format('m-d') == $dob->format('m-d')) {
                $todays_birthdays[] = ['id' => $contact->id, 'name' => $contact->name];
            } elseif ($next_birthday->between($today->format('Y-m-d'), $thirty_days_from_today)) {
                $upcoming_birthdays[] = ['name' => $contact->name, 'id' => $contact->id, 'dob' => $dob->format('m-d')];
            }
        }

        return [
            'todays_birthdays' => $todays_birthdays,
            'upcoming_birthdays' => $upcoming_birthdays,
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('crm::create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        return view('crm::show');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        return view('crm::edit');
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function conversionChartData(Request $request)
    {
        $year = now()->year;

        $userId = $request->get('filter_user_id');

        $query = Contact::selectRaw('MONTH(created_at) as month, COUNT(*) as total')
                        ->whereYear('created_at', $year)
                        ->whereNotNull('converted_by');

        if (!empty($userId)) {
            $query->where('converted_by', $userId);
        }

        $conversions = $query->groupBy(DB::raw('MONTH(created_at)'))
                            ->orderBy('month')
                            ->get()
                            ->pluck('total', 'month');

        $monthlyData = collect(range(1, 12))->map(function ($month) use ($conversions) {
            return $conversions->get($month, 0);
        });

        return response()->json([
            'labels' => [
                'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
            ],
            'data' => $monthlyData
        ]);
    }


    public function getCallSubjectChartData(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');

            $query = DB::table('crm_call_logs')
                ->join('crm_call_log_call_subject', 'crm_call_logs.id', '=', 'crm_call_log_call_subject.crm_call_log_id')
                ->join('crm_call_subjects as subjects', 'crm_call_log_call_subject.crm_call_subject_id', '=', 'subjects.id')
                ->where('crm_call_logs.business_id', $business_id)
                ->select(
                    'subjects.name as subject_name',
                    DB::raw("COUNT(DISTINCT crm_call_logs.id) as total")
                )
                ->groupBy('subjects.id', 'subjects.name');

            // Filter by user if provided
            if ($request->filled('user_id')) {
                $query->where('crm_call_logs.created_by', $request->user_id);
            }

            // Filter by date range if provided
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $start_date = $request->start_date;
                $end_date = $request->end_date;
                
                // Validate dates
                if (!Carbon::hasFormat($start_date, 'Y-m-d') || !Carbon::hasFormat($end_date, 'Y-m-d')) {
                    throw new \Exception('Invalid date format');
                }

                $query->whereBetween('crm_call_logs.start_time', [$start_date, $end_date]);
            }

            $query->orderByDesc('total');

            $results = $query->get();

            // Handle case where no results are found
            if ($results->isEmpty()) {
                return response()->json([
                    'labels' => ['No data available'],
                    'data' => [0],
                    'message' => 'No call data found for the selected filters'
                ]);
            }

            return response()->json([
                'labels' => $results->pluck('subject_name'),
                'data' => $results->pluck('total'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getCallSubjectChartData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error processing chart data',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getCallsPerMonthChartData(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $is_admin = $this->crmUtil->is_admin(auth()->user(), $business_id) || 
                    auth()->user()->can('crm.access_all_call_log');

            $query = DB::table('crm_call_logs')
                ->where('business_id', $business_id);

            // Apply user filter if provided and admin
            if ($request->filled('user_id') && $is_admin) {
                $query->where('created_by', $request->user_id);
            } elseif (!$is_admin) {
                $query->where('created_by', auth()->user()->id);
            }

            // Get data for last 12 months including current month
            $start_date = Carbon::now()->subMonths(11)->startOfMonth();
            $end_date = Carbon::now()->endOfMonth();

            // First get all months in the range
            $months = [];
            $current = clone $start_date;
            while ($current <= $end_date) {
                $months[$current->format('Y-m')] = [
                    'inbound' => 0,
                    'outbound' => 0,
                    'total' => 0
                ];
                $current->addMonth();
            }

            // Then get actual data from database
            $results = $query
                ->select(
                    DB::raw("DATE_FORMAT(start_time, '%Y-%m') as month"),
                    DB::raw("SUM(CASE WHEN call_type = 'inbound' THEN 1 ELSE 0 END) as inbound"),
                    DB::raw("SUM(CASE WHEN call_type = 'outbound' THEN 1 ELSE 0 END) as outbound"),
                    DB::raw("COUNT(*) as total")
                )
                ->whereBetween('start_time', [$start_date, $end_date])
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Merge the actual data with our months array
            foreach ($results as $result) {
                if (isset($months[$result->month])) {
                    $months[$result->month] = [
                        'inbound' => (int)$result->inbound,
                        'outbound' => (int)$result->outbound,
                        'total' => (int)$result->total
                    ];
                }
            }

            // Prepare the response data
            $labels = array_keys($months);
            $datasets = [
                [
                    'label' => 'Inbound',
                    'data' => array_column($months, 'inbound'),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Outbound',
                    'data' => array_column($months, 'outbound'),
                    'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                    'borderColor' => 'rgba(255, 99, 132, 1)',
                    'borderWidth' => 1
                ]
            ];

            return response()->json([
                'labels' => $labels,
                'datasets' => $datasets
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getCallsPerMonthChartData: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error processing monthly chart data',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function getCallsThisMonthDailyChartData(Request $request)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $is_admin = $this->crmUtil->is_admin(auth()->user(), $business_id) || 
                        auth()->user()->can('crm.access_all_call_log');

            $query = DB::table('crm_call_logs')
                ->where('business_id', $business_id);

            // Apply user filter if provided and admin
            if ($request->filled('user_id') && $is_admin) {
                $query->where('created_by', $request->user_id);
            } elseif (!$is_admin) {
                $query->where('created_by', auth()->user()->id);
            }

            // Get data for current month
            $start_date = Carbon::now()->startOfMonth();
            $end_date = Carbon::now()->endOfMonth();
            $today = Carbon::now();

            $results = $query
                ->select(
                    DB::raw("DATE(start_time) as date"),
                    DB::raw("SUM(IF(call_type = 'inbound', 1, 0)) as inbound"),
                    DB::raw("SUM(IF(call_type = 'outbound', 1, 0)) as outbound"),
                    DB::raw("COUNT(*) as total")
                )
                ->whereBetween('start_time', [$start_date, $end_date])
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            // Fill in missing days with zero values
            $days = [];
            $current = clone $start_date;
            while ($current <= $today) { // Only show up to today
                $days[$current->format('Y-m-d')] = [
                    'inbound' => 0,
                    'outbound' => 0,
                    'total' => 0,
                    'label' => $current->format('M j') // Format like "Jul 15"
                ];
                $current->addDay();
            }

            foreach ($results as $result) {
                if (isset($days[$result->date])) {
                    $days[$result->date] = [
                        'inbound' => $result->inbound,
                        'outbound' => $result->outbound,
                        'total' => $result->total,
                        'label' => Carbon::parse($result->date)->format('M j')
                    ];
                }
            }

            return response()->json([
                'labels' => array_column($days, 'label'),
                'datasets' => [
                    [
                        'label' => 'Inbound',
                        'data' => array_column($days, 'inbound'),
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'borderColor' => 'rgba(54, 162, 235, 1)',
                        'borderWidth' => 1
                    ],
                    [
                        'label' => 'Outbound',
                        'data' => array_column($days, 'outbound'),
                        'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                        'borderColor' => 'rgba(255, 99, 132, 1)',
                        'borderWidth' => 1
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error in getCallsThisMonthDailyChartData: ' . $e->getMessage());
            return response()->json(['error' => 'Error processing daily chart data'], 500);
        }
    }

}
