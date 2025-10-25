<?php

namespace App\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\CashRegister;
use App\Transaction;
use App\User;
use App\Utils\CashRegisterUtil;
use App\Utils\ModuleUtil;
use Illuminate\Http\Request;
use Session;
use Illuminate\Support\Facades\DB;

class CashRegisterController extends Controller
{
    /**
     * All Utils instance.
     */
    protected $cashRegisterUtil;

    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param  CashRegisterUtil  $cashRegisterUtil
     * @return void
     */
    public function __construct(CashRegisterUtil $cashRegisterUtil, ModuleUtil $moduleUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cash_register.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //like:repair
        $sub_type = request()->get('sub_type');
        $business_id = request()->session()->get('user.business_id');
        $business = Business::findOrFail($business_id);
        $common_settings = $business->common_settings;
        $clinic_location = $common_settings['clinic_location'] ?? null;
        $user_id = auth()->user()->id;
        $count = CashRegister::where('user_id', $user_id)
            ->where('status', 'open')
            ->where('location_id', $clinic_location)
            ->count();
        //Check if there is a open register, if yes then redirect to POS screen.
        if ($this->cashRegisterUtil->countOpenedRegister() != 0 && $count == 0) {
            return redirect()->action([\App\Http\Controllers\SellPosController::class, 'create'], ['sub_type' => $sub_type]);
        }
        $business_locations = BusinessLocation::forDropdownSell($business_id);

        $user_id = auth()->user()->id;
        // Find the last cash register for the authenticated user
        $cashRegister = Cashregister::where('user_id', $user_id)->latest()->first();
        $cash_register_id = $cashRegister ? $cashRegister->id : null;

        return view('cash_register.create')->with(compact('business_locations', 'sub_type', 'cash_register_id', 'count'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //like:repair
        $sub_type = request()->get('sub_type');

        try {
            $initial_amount = 0;
            if (!empty($request->input('amount'))) {
                $initial_amount = $this->cashRegisterUtil->num_uf($request->input('amount'));
            }
            $user_id = auth()->user()->id;
            $business_id = $request->session()->get('user.business_id');

            $register = CashRegister::create([
                'business_id' => $business_id,
                'user_id' => $user_id,
                'status' => 'open',
                'location_id' => $request->input('location_id'),
                'created_at' => \Carbon::now()->format('Y-m-d H:i:00'),
            ]);
            if (!empty($initial_amount)) {
                $register->cash_register_transactions()->create([
                    'amount' => $initial_amount,
                    'pay_method' => 'cash',
                    'type' => 'credit',
                    'transaction_type' => 'initial',
                ]);
            }
            Session::put('register_open', 1);
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
        }

        return redirect()->action([\App\Http\Controllers\SellPosController::class, 'create'], ['sub_type' => $sub_type]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CashRegister  $cashRegister
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);
        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = !empty($register_details['closed_at']) ? $register_details['closed_at'] : \Carbon::now()->toDateTimeString();
        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types(null, false, $business_id);
        $sell_return = $this->sellreturn($open_time, $close_time, $user_id, $business_id);
        return view('cash_register.register_details')
            ->with(compact('register_details', 'details', 'payment_types', 'close_time', 'id', 'sell_return'));
    }

    public function clinicShow($id)
    {
        if (!auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);
        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = !empty($register_details['closed_at']) ? $register_details['closed_at'] : \Carbon::now()->toDateTimeString();
        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
        $details = $this->cashRegisterUtil->getClinicRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types(null, false, $business_id);
        $sell_return = $this->sellreturn($open_time, $close_time, $user_id, $business_id);
        return view('clinic::cash_register.register_details')
            ->with(compact('register_details', 'details', 'payment_types', 'close_time', 'id', 'sell_return'));
    }

    public function posPrint($id)
    {
        if (!auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);
        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = !empty($register_details['closed_at']) ? $register_details['closed_at'] : \Carbon::now()->toDateTimeString();
        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types(null, false, $business_id);

        return view('cash_register.pos_register_details')
            ->with(compact('register_details', 'details', 'payment_types', 'close_time'));
    }
    public function a4Print($id)
    {
        if (!auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);
        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = !empty($register_details['closed_at']) ? $register_details['closed_at'] : \Carbon::now()->toDateTimeString();
        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types(null, false, $business_id);
        $sell_return = $this->sellreturn($open_time, $close_time, $user_id, $business_id);
        // dd($sell_return);
        $common_settings = session()->get('business.common_settings');

        return view('cash_register.a4_register_details')
            ->with(compact('register_details', 'details', 'payment_types', 'close_time', 'common_settings', 'sell_return', 'user_id'));
    }
    public function clinicA4Print($id)
    {
        if (!auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);
        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = !empty($register_details['closed_at']) ? $register_details['closed_at'] : \Carbon::now()->toDateTimeString();
        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');
        $details = $this->cashRegisterUtil->getClinicRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types(null, false, $business_id);
        $sell_return = $this->sellreturn($open_time, $close_time, $user_id, $business_id);
        // dd($sell_return);
        $common_settings = session()->get('business.common_settings');

        return view('clinic::cash_register.a4_register_details')
            ->with(compact('register_details', 'details', 'payment_types', 'close_time', 'common_settings', 'sell_return', 'user_id'));
    }
    private function sellreturn($open_time, $close_time, $user_id, $business_id)
    {
        $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
            ->join('business_locations AS bl', 'transactions.location_id', '=', 'bl.id')
            ->join('transactions as T1', 'transactions.return_parent_id', '=', 'T1.id')
            ->leftJoin('transaction_payments AS TP', 'transactions.id', '=', 'TP.transaction_id')
            ->join('users AS u', 'transactions.created_by', '=', 'u.id')
            ->leftJoin('transaction_sell_lines AS TSL', 'T1.id', '=', 'TSL.transaction_id')
            ->leftJoin('variations AS v', 'TSL.variation_id', '=', 'v.id')
            ->leftJoin('product_variations AS pv', 'v.product_variation_id', '=', 'pv.id')
            ->leftJoin('products AS p', 'v.product_id', '=', 'p.id')
            // Join for modifiers
            ->leftJoin('transaction_sell_lines AS child_TSL', function ($join) {
                $join->on('TSL.id', '=', 'child_TSL.parent_sell_line_id')
                    ->where('child_TSL.children_type', 'modifier')
                    ->where('child_TSL.quantity_returned', '!=', 0); // Ensuring only modifiers are joined
            })
            ->leftJoin('variations AS v_child', 'child_TSL.variation_id', '=', 'v_child.id') // Get variation name for modifiers
            ->where('transactions.business_id', $business_id)
            ->where('transactions.type', 'sell_return')
            ->where('transactions.status', 'final')
            ->where('TSL.children_type', '!=', 'modifier')
            ->where('TSL.quantity_returned', '!=', 0)
            ->select(
                'transactions.id',
                'transactions.transaction_date',
                'transactions.invoice_no',
                'u.first_name as created_by',
                'contacts.name',
                'contacts.supplier_business_name',
                'transactions.final_total',
                'transactions.payment_status',
                'bl.name as business_location',
                'T1.invoice_no as parent_sale',
                'T1.id as parent_sale_id',
                'T1.transaction_date as parent_transaction_date',
                'p.type as product_type',
                'pv.name as product_variation_name',
                'v.sub_sku as sku',
                'TP.method',
                DB::raw("GROUP_CONCAT(DISTINCT p.name ORDER BY p.name SEPARATOR ', ') as product_names"),
                DB::raw("GROUP_CONCAT(DISTINCT v.name ORDER BY v.name SEPARATOR ', ') as variation_names"),
                DB::raw("GROUP_CONCAT(DISTINCT v_child.name ORDER BY v_child.name SEPARATOR ', ') as modifier_names") // New column for modifiers
            );

        $permitted_locations = auth()->user()->permitted_locations();
        if ($permitted_locations != 'all') {
            $sells->whereIn('transactions.location_id', $permitted_locations);
        }

        $sells->where('transactions.created_by', $user_id);

        $start = $open_time;
        $end = !empty($close_time) ? $close_time : now();
        $sells->whereBetween('transactions.transaction_date', [$start, $end]);

        $sellReturn = $sells->groupBy('transactions.id')->get();
        return $sellReturn;
    }

    /**
     * Shows register details modal.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getRegisterDetails()
    {
        if (!auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterDetails();

        $user_id = auth()->user()->id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();

        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types($register_details->location_id, true, $business_id);
        $sell_return = $this->sellreturn($open_time, $close_time, $user_id, $business_id);
        return view('cash_register.register_details')
            ->with(compact('register_details', 'details', 'payment_types', 'close_time', 'sell_return'));
    }

    /**
     * Shows close register form.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getCloseRegister($id = null)
    {
        if (!auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);

        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();

        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types($register_details->location_id, true, $business_id);

        $pos_settings = !empty(request()->session()->get('business.pos_settings')) ? json_decode(request()->session()->get('business.pos_settings'), true) : [];
        $sell_return = $this->sellreturn($open_time, $close_time, $user_id, $business_id);
        return view('cash_register.close_register_modal')
            ->with(compact('register_details', 'details', 'payment_types', 'pos_settings', 'sell_return'));
    }


    public function getClinicCloseRegister($id = null)
    {
        if (!auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        $business_id = request()->session()->get('user.business_id');
        $register_details = $this->cashRegisterUtil->getRegisterDetails($id);

        $user_id = $register_details->user_id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();

        $is_types_of_service_enabled = $this->moduleUtil->isModuleEnabled('types_of_service');

        $details = $this->cashRegisterUtil->getClinicRegisterTransactionDetails($user_id, $open_time, $close_time, $is_types_of_service_enabled);

        $payment_types = $this->cashRegisterUtil->payment_types($register_details->location_id, true, $business_id);

        $pos_settings = !empty(request()->session()->get('business.pos_settings')) ? json_decode(request()->session()->get('business.pos_settings'), true) : [];
        $sell_return = $this->sellreturn($open_time, $close_time, $user_id, $business_id);
        return view('clinic::cash_register.close_register_modal')
            ->with(compact('register_details', 'details', 'payment_types', 'pos_settings', 'sell_return'));
    }

    /**
     * Closes currently opened register.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postCloseRegister(Request $request)
    {
        if (!auth()->user()->can('close_cash_register')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            //Disable in demo
            if (config('app.env') == 'demo') {
                $output = [
                    'success' => 0,
                    'msg' => 'Feature disabled in demo!!',
                ];

                return redirect()->action([\App\Http\Controllers\HomeController::class, 'index'])->with('status', $output);
            }

            $input = $request->only(['closing_amount', 'total_card_slips', 'total_cheques', 'closing_note']);
            $input['closing_amount'] = $this->cashRegisterUtil->num_uf($input['closing_amount']);
            $user_id = $request->input('user_id');
            $input['closed_at'] = \Carbon::now()->format('Y-m-d H:i:s');
            $input['status'] = 'close';
            $input['denominations'] = !empty(request()->input('denominations')) ? json_encode(request()->input('denominations')) : null;

            CashRegister::where('user_id', $user_id)
                ->where('status', 'open')
                ->update($input);
            request()->session()->forget('register_open');
            $output = [
                'success' => 1,
                'msg' => __('cash_register.close_success'),
            ];
        } catch (\Exception $e) {
            \Log::emergency('File:' . $e->getFile() . 'Line:' . $e->getLine() . 'Message:' . $e->getMessage());
            $output = [
                'success' => 0,
                'msg' => __('messages.something_went_wrong'),
            ];
        }

        return redirect()->back()->with('status', $output);
    }

    public function getRegisterDetailsOverall(Request $request)
    {

        if (!auth()->user()->can('view_cash_register')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $users = User::forDropdown($business_id, false);

        return view('cash_register.overall_report')
            ->with(compact('users'));
    }
    public function overallRegisterData(Request $request)
    {
        $user_id = $request->input('user_id');
        $business_id = request()->session()->get('user.business_id');

        $register_details = $this->cashRegisterUtil->getRegisterAccountDetails();

        $open_time = !empty($register_details['open_time'])
            ? $register_details['open_time']
            : \Carbon::now()->startOfYear()->toDateTimeString();

        $close_time = !empty($register_details['close_time'])
            ? $register_details['close_time']
            : \Carbon::now()->endOfYear()->toDateTimeString();

        $details = $this->cashRegisterUtil->getRegisterTransactionAcountDetails($user_id, $open_time, $close_time, $this->moduleUtil->isModuleEnabled('types_of_service'));
        $payment_types = $this->cashRegisterUtil->payment_types($register_details->location_id, true, $business_id);

        // Render the view and get HTML
        $content = view('cash_register.overall_data', compact('register_details', 'details', 'payment_types', 'open_time', 'close_time'))->render();
        return response()->json(['content' => $content]);
    }
}