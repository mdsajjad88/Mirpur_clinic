<?php

namespace Modules\Crm\Http\Controllers;

use App\Category;
use App\Division;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\CallCampaign;
use Modules\Crm\Entities\ContactFilter;
use Yajra\DataTables\Facades\DataTables;
use Modules\Clinic\Entities\SurveyType;
use App\Contact;
use App\{District, CustomerGroup};
use Illuminate\Support\Facades\{Auth, DB, Log};
use Carbon\Carbon;
use Modules\Clinic\Entities\{DoctorProfile, Problem};

class ContactFilterController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('call.campaign_filter.view')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $campaigns = ContactFilter::select('contact_filters.*');

            return DataTables::of($campaigns)
                ->addColumn('action', function ($row) {
                    $html = '';
                    // Edit process are disabled some time
                    // if (auth()->user()->can('call.campaign_filter.update')) {
                    //     $html .= '<a href="' . action([self::class, 'edit'], $row->id) . '" class="btn btn-xs btn-primary">
                    //         <i class="fas fa-edit"></i> ' . __('Edit') . '
                    //     </a>';
                    // }
                    if (auth()->user()->can('call.campaign_filter.delete')) {
                        $deleteUrl = action([\Modules\Crm\Http\Controllers\ContactFilterController::class, 'destroy'], [$row->id]);

                        $html .= '<a data-href="' . $deleteUrl . '" class="btn btn-xs btn-danger delete_campaign_filter" style="margin-left: 5px;">
                            <i class="fas fa-trash"></i> ' . __('Delete') . '
                        </a>';
                    }
                    if (auth()->user()->can('call.campaign_filter.view')) {
                        $html .= '<a href="' . action([self::class, 'show'], $row->id) . '" class="btn btn-xs btn-info" style="margin-left: 5px;">
                            <i class="fas fa-eye"></i> ' . __('View') . '
                        </a>';
                    }
                    return $html;
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

        return view('crm::contact_filter.index');
    }

    public function create()
    {
        if (!auth()->user()->can('call.campaign_filter.create')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $life_stages = Category::forDropdown($business_id, 'life_stage');
        $districts = District::pluck('name', 'id');
        $divisions = Division::pluck('name', 'id');
        $doctors = DoctorProfile::select(DB::raw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) as name"), 'user_id')
            ->pluck('name', 'user_id');
        $problems = Problem::pluck('name', 'id')->unique();
        $customer_groups = CustomerGroup::forDropdown($business_id);

        return view('crm::contact_filter.create', compact('life_stages', 'districts', 'divisions', 'doctors', 'problems', 'customer_groups'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('call.campaign_filter.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();

            $input = $request->only([
                'name',
                'description',
                'contact_type',
                'crm_life_stage',
                'minimum_visit_frequency',
                'minimum_visit_frequency_in',
                'average_spend_per_visit_range',
                'district_id',
                'gender',
                'age_range_min',
                'age_range_max',
                'has_transaction',
                'last_transaction_days',
                'doctor_user_id',
                'problem_ids',
                'customer_group_ids',
                'patient_type',
                'months',
                'year',
            ]);

            $contact_filter = new ContactFilter;
            $contact_filter->name = $input['name'];
            $contact_filter->description = $input['description'];
            $contact_filter->created_by = Auth::user()->id;
            $contact_filter->save();

            $filtered_data = $this->getFilteredData($input, $contact_filter->id);

            if ($filtered_data['success']) {
                DB::commit();
                $output = [
                    'success' => true,
                    'msg' => __('Campaign created successfully'),
                    'redirect' => action([self::class, 'index'])
                ];
            } else {
                DB::rollBack();
                $output = [
                    'success' => false,
                    'msg' => $filtered_data['msg']
                ];
            }
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


    private function getFilteredData($input, $contact_filter_id = null)
    {
        try {
            Log::info('Create campaign with filters in getFilteredData', $input);

            $appliedFilters = [];

            $query = DB::table('contacts')
                ->select('contacts.id', 'contacts.name', 'contacts.mobile')
                ->where('contacts.type', $input['contact_type']);

            Log::info('Step 1: Initialized base contacts query with contact_type filter');
            Log::info('Step 1 Count: ' . (clone $query)->count());
            $appliedFilters['contact_type'] = $input['contact_type'];
            if (!empty($input['patient_type'])) {
                $query->where('contacts.patient_type', $input['patient_type']);
                $appliedFilters['patient_type'] = $input['patient_type'];
            }
            if (!empty($input['customer_group_ids'])) {
                $groupIds = array_filter($input['customer_group_ids']); // removes null, false, 0, ''

                if (!empty($groupIds)) {
                    $query->whereIn('contacts.customer_group_id', $groupIds);
                    $appliedFilters['customer_group_ids'] = $groupIds;
                }
            }

            $query->leftJoin('patient_profiles', 'contacts.id', '=', 'patient_profiles.patient_contact_id');
            Log::info('Step 2: Joined with patient_profiles table');
            Log::info('Step 2 Count: ' . (clone $query)->count());

            if (!empty($input['crm_life_stage']) && $input['contact_type'] === 'lead') {
                $query->where('contacts.crm_life_stage', $input['crm_life_stage']);
                $appliedFilters['crm_life_stage'] = $input['crm_life_stage'];
                Log::info('Step 3: Applied crm_life_stage filter');
                Log::info('Step 3 Count: ' . (clone $query)->count());
            }

            if (!empty($input['gender'])) {
                $query->where('patient_profiles.gender', $input['gender']);
                $appliedFilters['gender'] = $input['gender'];
                Log::info('Step 4: Applied gender filter');
                Log::info('Step 4 Count: ' . (clone $query)->count());
            }
            if (!empty($input['doctor_user_id'])) {
                $doctorUserId = $input['doctor_user_id'];

                $query->whereIn('contacts.id', function ($subquery) use ($doctorUserId) {
                    $subquery->select('patient_contact_id')
                        ->from('patient_appointment_requests')
                        ->where('remarks', 'prescribed')
                        ->where('doctor_user_id', $doctorUserId)
                        ->groupBy('patient_contact_id');
                });

                $appliedFilters['doctor_user_id'] = $doctorUserId;
                Log::info('Step 5: Applied doctor_user_id filter');
                Log::info('Step 5: Doctor filter Count: ' . (clone $query)->count());
            }

            if (!empty($input['district_id'])) {
                $query->where('patient_profiles.district_id', $input['district_id']);
                $appliedFilters['district_id'] = $input['district_id'];
                Log::info('Step 6: Applied district_id filter');
                Log::info('Step 6 Count: ' . (clone $query)->count());
            }

            if (!empty($input['age_range_min'])) {
                $query->where('patient_profiles.age', '>=', (int)$input['age_range_min']);
                $appliedFilters['age_range_min'] = $input['age_range_min'];
                Log::info('Step 7: Applied age_range_min filter');
                Log::info('Step 7 Count: ' . (clone $query)->count());
            }

            if (!empty($input['age_range_max'])) {
                $query->where('patient_profiles.age', '<=', (int)$input['age_range_max']);
                $appliedFilters['age_range_max'] = $input['age_range_max'];
                Log::info('Step 8: Applied age_range_max filter');
                Log::info('Step 8 Count: ' . (clone $query)->count());
            }
            if (!empty($input['problem_ids']) && count($input['problem_ids']) > 0) {
                $query->whereIn('contacts.id', function ($subquery) use ($input) {
                    $subquery->select('intake_form_info.patient_contact_id')
                        ->from('report_and_problems')
                        ->join('intake_form_info', 'report_and_problems.intake_form_id', '=', 'intake_form_info.id')
                        ->whereIn('report_and_problems.problem_id', $input['problem_ids']);
                });

                $appliedFilters['problem_ids'] = $input['problem_ids'];
                Log::info('Step disease: Applied problem_ids filter using report_and_problems + intake_form_info');
                Log::info('Step disease Count: ' . (clone $query)->count());
            }


            if (!empty($input['minimum_visit_frequency']) && !empty($input['minimum_visit_frequency_in'])) {
                $fromDate = Carbon::now()->subDays((int)$input['minimum_visit_frequency_in'] * 30)->toDateString();

                $query->whereIn('contacts.id', function ($subquery) use ($fromDate, $input) {
                    $subquery->select('patient_contact_id')
                        ->from('patient_appointment_requests')
                        ->where('remarks', 'prescribed')
                        ->whereDate('created_at', '>=', $fromDate)
                        ->groupBy('patient_contact_id')
                        ->havingRaw('COUNT(*) >= ?', [(int)$input['minimum_visit_frequency']]);
                });
                $appliedFilters['minimum_visit_frequency'] = $input['minimum_visit_frequency'];
                $appliedFilters['minimum_visit_frequency_in'] = $input['minimum_visit_frequency_in'];
                Log::info('Step 9: Applied minimum_visit_frequency filter');
                Log::info('Step 9 Count: ' . (clone $query)->count());
            }
            // ✅ Month + Year Filter
            if (!empty($input['year']) || !empty($input['months'])) {
                $appliedFilters['year'] = $input['year'] ?? null;
                $appliedFilters['months'] = $input['months'] ?? [];

                $query->whereIn('contacts.id', function ($sub) use ($input) {
                    $sub->select('contact_id')
                        ->from('transactions')
                        ->where('status', 'final');

                    if (!empty($input['year'])) {
                        $sub->whereYear('transactions.transaction_date', $input['year']);
                    }

                    if (!empty($input['months'])) {
                        $sub->whereIn(DB::raw('MONTH(transactions.transaction_date)'), $input['months']);
                    }

                    $sub->groupBy('contact_id');
                });

                Log::info('Step Month-Year: Applied year + month filter', [
                    'year' => $input['year'] ?? null,
                    'months' => $input['months'] ?? []
                ]);
                Log::info('Step Month-Year Count: ' . (clone $query)->count());
            }


            if (isset($input['has_transaction'])) {
                $appliedFilters['has_transaction'] = $input['has_transaction'];

                $txnDate = null;
                if (!empty($input['last_transaction_days'])) {
                    $txnDate = Carbon::now()->subDays((int)$input['last_transaction_days']);
                    $appliedFilters['last_transaction_days'] = $input['last_transaction_days'];
                }

                // ✅ has_transaction = 1 → ট্রানজেকশন আছে
                if ($input['has_transaction'] == '1') {

                    if ($txnDate) {
                        // যাদের final ট্রানজেকশন আছে এবং সেটি শেষ X দিনের মধ্যে হয়েছে (created_at দিয়ে)
                        $query->whereIn('contacts.id', function ($sub) use ($txnDate) {
                            $sub->select('contact_id')
                                ->from('transactions')
                                ->where('status', 'final')
                                ->whereDate('created_at', '>=', $txnDate)
                                ->groupBy('contact_id');
                        });
                        Log::info('✅ Step: ট্রানজেকশন আছে এবং created_at দিয়ে শেষ ' . $input['last_transaction_days'] . ' দিনে হয়েছে');
                    } else {
                        // যাদের যেকোনো সময়ের final ট্রানজেকশন আছে
                        $query->whereIn('contacts.id', function ($sub) {
                            $sub->select('contact_id')
                                ->from('transactions')
                                ->where('status', 'final')
                                ->groupBy('contact_id');
                        });
                        Log::info('✅ Step: ট্রানজেকশন আছে (last days check নাই)');
                    }
                }

                // ✅ has_transaction = 0 → ট্রানজেকশন নেই বা শেষ X দিনে ট্রানজেকশন হয়নি
                elseif ($input['has_transaction'] == '0') {

                    if ($txnDate) {
                        // যাদের ট্রানজেকশন আছে কিন্তু সেটি শেষ X দিনের মধ্যে হয়নি
                        $query->whereIn('contacts.id', function ($sub) {
                            $sub->select('contact_id')
                                ->from('transactions')
                                ->where('status', 'final')
                                ->groupBy('contact_id');
                        });

                        $query->whereNotIn('contacts.id', function ($sub) use ($txnDate) {
                            $sub->select('contact_id')
                                ->from('transactions')
                                ->where('status', 'final')
                                ->whereDate('created_at', '>=', $txnDate)
                                ->groupBy('contact_id');
                        });

                        Log::info('✅ Step: ট্রানজেকশন আছে কিন্তু created_at দিয়ে শেষ X দিনে হয়নি');
                    } else {
                        // যাদের একটিও final ট্রানজেকশন নেই
                        $query->whereNotIn('contacts.id', function ($sub) {
                            $sub->select('contact_id')
                                ->from('transactions')
                                ->where('status', 'final')
                                ->groupBy('contact_id');
                        });
                        Log::info('✅ Step: কোনো ট্রানজেকশন নেই');
                    }
                }

                Log::info('Total Filtered Contacts: ' . (clone $query)->count());
            }





            if (!empty($input['average_spend_per_visit_range'][0]) || !empty($input['average_spend_per_visit_range'][1])) {
                $minProvided = !empty($input['average_spend_per_visit_range'][0]);
                $maxProvided = !empty($input['average_spend_per_visit_range'][1]);

                $min = $minProvided ? (float)$input['average_spend_per_visit_range'][0] : null;
                $max = $maxProvided ? (float)$input['average_spend_per_visit_range'][1] : null;

                // 1️⃣ Separate query: Get contact_id + avg_spend for logging + filtering
                $avgSpendQuery = DB::table('transactions')
                    ->select('contact_id', DB::raw('AVG(final_total) as avg_spend'))
                    ->where('status', 'final')
                    ->whereNotNull('contact_id')
                    ->groupBy('contact_id');

                if ($minProvided && $maxProvided) {
                    $avgSpendQuery->havingRaw('AVG(final_total) BETWEEN ? AND ?', [$min, $max]);
                } elseif ($minProvided) {
                    $avgSpendQuery->havingRaw('AVG(final_total) >= ?', [$min]);
                } elseif ($maxProvided) {
                    $avgSpendQuery->havingRaw('AVG(final_total) <= ?', [$max]);
                }

                $avgMatches = $avgSpendQuery->get();

                // 2️⃣ Log contact_id and average
                // foreach ($avgMatches as $match) {
                //     Log::info("Contact ID: {$match->contact_id}, Avg Spend: " . round($match->avg_spend, 2));
                // }

                // 3️⃣ Apply filter using whereIn
                $query->whereIn('contacts.id', $avgMatches->pluck('contact_id')->toArray());

                $appliedFilters['average_spend_per_visit_range'] = [$min ?? 'any', $max ?? 'any'];
                Log::info('Applied average_spend_per_visit_range filter', $appliedFilters);
                Log::info('Filtered count: ' . (clone $query)->count());
            }


            Log::info('Step 14: Final query prepared, fetching results');
            $results = $query->get();
            Log::info('Step 15: Result count = ' . $results->count());
            Log::info('Step 16: Sample result', $results->take(5)->toArray());

            if ($contact_filter_id) {

                $campaignContacts = [];
                foreach ($results as $contact) {
                    $campaignContacts[] = [
                        'contact_id' => $contact->id,
                    ];
                }



                DB::table('contact_filters')
                    ->where('id', $contact_filter_id)
                    ->update([
                        'target_count' => count($campaignContacts),
                        'filters' => json_encode($appliedFilters),
                        'contact_ids' => json_encode($campaignContacts),
                    ]);
            }

            $output = [
                'success' => true,
                'msg' => 'Contacts fetched successfully',
            ];
        } catch (\Exception $e) {
            Log::error('Error in getFilteredData: ' . $e->getMessage(), ['line' => $e->getLine(), 'file' => $e->getFile()]);
            $output = [
                'success' => false,
                'msg' => 'Something went wrong !' . $e->getMessage(),
            ];
        }
        return $output;
    }




    public function show($id)
    {
        if (!auth()->user()->can('call.campaign_filter.view')) {
            abort(403, 'Unauthorized action.');
        }

        $contact_filter = ContactFilter::findOrFail($id);
        if (request()->ajax()) {
            if (request()->ajax()) {
                // Extract raw contact_ids from the array
                $contactIds = collect(json_decode($contact_filter->contact_ids, true))->pluck('contact_id')->toArray();
                // Query filtered contacts
                $contacts = DB::table('contacts')
                    ->whereIn('contacts.id', $contactIds)
                    ->select(
                        'contacts.id',
                        'contacts.name',
                        'contacts.mobile',
                        'contacts.contact_id'
                    );

                return DataTables::of($contacts)->make(true);
            }
        }
        $filters = json_decode($contact_filter->filters);
        $filterData = $this->formatFilterData($filters);
        return view('crm::contact_filter.show', compact('contact_filter', 'filterData'));
    }

    public function edit($id)
    {
        if (!auth()->user()->can('call.campaign_filter.update')) {
            abort(403, 'Unauthorized action.');
        }

        $campaign = CallCampaign::findOrFail($id);
        $surveyTypes = SurveyType::pluck('name', 'id');

        return view('crm::contact_filter.edit', compact('campaign', 'surveyTypes'));
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('call.campaign_filter.update')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $campaign = CallCampaign::findOrFail($id);

            $input = $request->only(['name', 'description', 'survey_type_id', 'start_date', 'end_date', 'status']);

            // Process filters
            $filters = [];
            if ($request->has('contact_type')) {
                $filters['contact_type'] = $request->contact_type;
            }
            if ($request->has('has_transaction')) {
                $filters['has_transaction'] = $request->has_transaction;
            }
            // Add more filters as needed

            $input['filters'] = json_encode($filters);

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
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }

    public function destroy($id)
    {
        if (!auth()->user()->can('call.campaign_filter.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $campaign = ContactFilter::findOrFail($id);

            // Check if there are any CallCampaigns using this filter
            $hasCampaigns = CallCampaign::where('contact_filter_id', $id)->exists();
            if ($hasCampaigns) {
                return [
                    'success' => false,
                    'msg' => __('Filter cannot be deleted as it is associated with campaigns')
                ];
            }


            $campaign->delete();

            $output = [
                'success' => true,
                'msg' => __('Filter deleted successfully')
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());

            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }

        return $output;
    }

    public function getFilteredFieldData($id)
    {
        try {
            $contact_filter = ContactFilter::findOrFail($id);
            $filters = json_decode($contact_filter->filters);
            $data = $this->formatFilterData($filters);



            $output = [
                'success' => true,
                'data' => $data,
                'target_contact' => $contact_filter->target_count
            ];
        } catch (\Exception $e) {
            Log::emergency("File:" . $e->getFile() . "Line:" . $e->getLine() . "Message:" . $e->getMessage());
            $output = [
                'success' => false,
                'msg' => __('messages.something_went_wrong')
            ];
        }


        return $output;
    }


    private function formatFilterData($filters)
    {
        if (is_object($filters)) {
            $filters = (array) $filters;
        }

        $data = [];

        foreach ($filters as $key => $value) {
            if ($key == 'crm_life_stage') {
                $category = DB::table('categories')->find($value);
                $data['Life Stage'] = $category->name ?? $value;
            } elseif ($key == 'district_id') {
                $district = DB::table('districts')->find($value);
                $data['District'] = $district->name ?? $value;
            } elseif ($key == 'has_transaction') {
                $data['Has Transaction'] = $value == 1 ? 'Yes' : 'No';
            } elseif ($key == 'customer_group_ids') {
                $ids = is_array($value) ? $value : json_decode($value, true);
                $groups = DB::table('customer_groups')->whereIn('id', $ids)->pluck('name')->toArray();
                $data['Customer Groups'] = implode(', ', $groups);
            } elseif ($key == 'doctor_user_id') {
                $doctor = DB::table('doctor_profiles')->where('user_id', $value)->first();
                $data['Doctor'] = ($doctor->first_name ?? '') . ' ' . ($doctor->last_name ?? '');
            } elseif ($key == 'average_spend_per_visit_range') {
                $data['Average Spend Per Visit'] = $value[0] . ' - ' . $value[1];
            } elseif ($key == 'problem_ids') {
                $ids = is_array($value) ? $value : json_decode($value, true);
                $problems = DB::table('problems')->whereIn('id', $ids)->pluck('name')->toArray();
                $data['Health Concerns'] = implode(', ', $problems);
            } else {
                $label = ucwords(str_replace('_', ' ', $key));
                $data[$label] = $value;
            }
        }

        return $data;
    }
}
