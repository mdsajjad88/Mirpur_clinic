<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Utils\ModuleUtil;
use App\Utils\Util;
use Illuminate\Support\Facades\DB;
use Mpdf\Tag\Span;
use Yajra\DataTables\Contracts\DataTable;
use Yajra\DataTables\Facades\DataTables;

class SubsPaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        if (request()->ajax()) {
            $patientSubscription = DB::table('patient_subscriptions_with_profiles')->get();
            return DataTables::of($patientSubscription)
                ->addColumn('name', function ($row) {
                    return $row->first_name . ' ' . $row->last_name ?? '';
                })
                ->addColumn('mobile', function ($row) {
                    $phoneId = 'phone_' . $row->id;
                    $phoneIcon = '<i class="fas fa-phone-square-alt phone-icon cursor-pointer text-success" data-id="' . $phoneId . '"></i>';
                    
                    if (auth()->user()->can('patient.phone_number')) {
                        return $row->mobile;
                    } else {
                        return $phoneIcon . 
                            ' <span class="phone-number" id="' . $phoneId . '" style="display:none;">' . $row->mobile . '</span>';
                    }
                })
                ->addColumn('remaining', function ($row) {
                    $remaining = 5 - $row->used_consultancy;
                    return $remaining;
                })
                ->addColumn('transaction', function ($row) {
                    return '<a href="#" data-href="' . action([\Modules\Clinic\Http\Controllers\SubsPaymentController::class, 'primaTransactionDetails'], ['patient_id' => $row->patient_user_id,
                            'subscription_id' => $row->id]) . '" class="btn-modal" data-container=".view_modal"><i class="fas fa-eye" aria-hidden="true"></i> ' . __('See Details') . '</a>';
                })
                ->setRowAttr([
                    'data-href' => function ($row) {
                        return route('prima.transaction.details', [
                            'patient_id' => $row->patient_user_id,
                            'subscription_id' => $row->id
                        ]);
                    },
                    'class' => 'cursor-pointer btn-modal',
                    // 'data-container' => '.subs_payment_details'
                ])
                ->rawColumns(['name','transaction', 'mobile'])
                ->make(true);
        }
        return view('clinic::prima.sub_details');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('clinic::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
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
        return view('clinic::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }
    public function primaTransactionDetails($patient_id, $subscription_id)
    {
        if (request()->ajax()) {
            $transactions = DB::table('patient_subscription_transactions as pst')
                ->leftJoin('patient_subscriptions_with_profiles as pswp', 'pswp.patient_user_id', '=', 'pst.patient_user_id')
                ->where('pst.patient_user_id', $patient_id)
                ->where('pst.patient_subscription_id', $subscription_id)
                ->where('pst.t_type', 1)
                ->select(
                    'pst.*',
                    DB::raw("TRIM(CONCAT(COALESCE(pswp.first_name, ''), ' ', COALESCE(pswp.last_name, ''))) as patient_name")
                )
                ->get();
    
            $patient_name = optional($transactions->first())->patient_name;
    
            return view('clinic::prima.subs_payment_details', compact('transactions', 'patient_name'));
        }
    
        abort(403, 'Unauthorized action.');
    }
    
}
