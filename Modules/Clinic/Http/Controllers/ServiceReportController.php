<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\TransactionSellLine;
use App\Utils\TransactionUtil;

class ServiceReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $transactionUtil;
    public function __construct(TransactionUtil $transactionUtil)
    {
        $this->transactionUtil = $transactionUtil;
    }
    public function index()
    {
        if (request()->ajax()) {
            $subTypes = ['ipd', 'consultation', 'therapy', 'test'];

            // Aggregate payments in subquery, filtered by payment date
            $paymentSubQuery = DB::table('transaction_payments')
                ->select(
                    'transaction_id',
                    DB::raw('SUM(amount) as total_payment'),
                    DB::raw('MAX(created_at) as paid_on') // Optional: latest payment date
                )
                ->when(!empty(request()->start_date) && !empty(request()->end_date), function ($q) {
                    $q->whereDate('created_at', '>=', request()->start_date)
                        ->whereDate('created_at', '<=', request()->end_date);
                })
                ->groupBy('transaction_id');

            // Main query
            $start_date = request()->start_date;
            $end_date = request()->end_date;
            $query = DB::table('transactions as t')
                ->join('transaction_sell_lines as tsl', 'tsl.transaction_id', '=', 't.id')
                ->join('products as p', 'tsl.product_id', '=', 'p.id')
                ->join('variations as v', 'tsl.variation_id', '=', 'v.id')
                ->leftJoinSub($paymentSubQuery, 'tp', function ($join) {
                    $join->on('tp.transaction_id', '=', 't.id');
                })
                ->select(
                    'p.id as product_id',
                    'p.name as product_name',
                    't.sub_type as transaction_sub_type',
                    'tsl.variation_id as variation_id',
                    DB::raw('
                    SUM(CASE
                        WHEN p.type = "modifier" THEN 0
                        ELSE (tsl.quantity - tsl.quantity_returned)
                    END) as total_qty_sold
                '),
                    DB::raw("ROUND(SUM(
                    CASE 
                        WHEN tsl.line_discount_type = 'percentage' 
                        THEN (tsl.line_discount_amount / 100) * v.sell_price_inc_tax 
                        ELSE tsl.line_discount_amount 
                    END
                ), 2) as total_discount"),
                    DB::raw("SUM(tp.total_payment) as sale_amount"),
                    DB::raw('ROUND(MIN(v.sell_price_inc_tax), 2) as price'),
                )
                ->whereIn('t.sub_type', $subTypes)
                ->groupBy('p.id', 'p.name', 't.sub_type');


            // Filter by selected sub_type (if any)
            if (!empty(request()->input('sub_type'))) {
                $query->whereIn('t.sub_type', request()->input('sub_type'));
            }

            // Filter transactions that have payments made in the date range
            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('transaction_payments')
                        ->whereColumn('transaction_payments.transaction_id', 't.id')
                        ->whereDate('transaction_payments.created_at', '>=', request()->start_date)
                        ->whereDate('transaction_payments.created_at', '<=', request()->end_date);
                });
            }
            if (request()->has('search') && !empty(request()->search['value'])) {
                $searchTerm = request()->search['value'];
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('p.name', 'like', "%{$searchTerm}%")
                        ->orWhere('t.sub_type', 'like', "%{$searchTerm}%");
                    // Add other searchable fields here
                });
            }

            return DataTables::of($query)
                ->editColumn('product_name', function ($row) {
                    if ($row->transaction_sub_type == 'therapy') {
                        $name = '<p class="text-blue">' . $row->product_name . '</p>';
                    } else {
                        $name = $row->product_name;
                    }
                    return $name;
                })

                ->editColumn('sale_amount', fn($row) => number_format($row->sale_amount, 2))
                ->editColumn('total_qty_sold', fn($row) => number_format($row->total_qty_sold, 2))
                ->editColumn('total_discount', fn($row) => number_format($row->total_discount, 2))
                ->editColumn('transaction_sub_type', fn($row) => ucfirst($row->transaction_sub_type))
                ->addColumn('row_class', function ($row) {
                    return $row->transaction_sub_type == 'therapy' ? 'cursor-pointer btn-modal' : '';
                })
                ->addColumn('data_href', function ($row) use ($start_date, $end_date) {
                    if ($row->transaction_sub_type == 'therapy') {
                        return route('therapy.selection.report.details.individual', [
                            'variation_id' => $row->variation_id,
                            'start_date' => $start_date,
                            'end_date' => $end_date
                        ]);
                    }
                    return '';
                })

                ->rawColumns(['product_name', 'sale_amount', 'total_qty_sold', 'total_discount', 'row_class', 'data_href'])

                ->make(true);
        }

        $types = [
            'consultation' => 'Consultation',
            'test' => 'Test',
            'therapy' => 'Therapy',
            'ipd' => 'IPD',
        ];

        return view('clinic::report.service_payment', compact('types'));
    }


    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('clinic::create');
    }

    public function testSellReportByCategory(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $location_id = $request->get('location_id', null);
        $vld_str = '';
        if (!empty($location_id)) {
            $vld_str = "AND vld.location_id=$location_id";
        }

        if ($request->ajax()) {
            $payment_subquery = DB::table('transaction_payments')
                ->select('transaction_id', DB::raw('SUM(amount) as total_payment'))
                ->groupBy('transaction_id');
            $start_date = request()->start_date;
            $end_date = request()->end_date;
            $query = DB::table('transaction_sell_lines as tsl')
                ->join('transactions as t', 'tsl.transaction_id', '=', 't.id')
                ->leftJoin('products as p', 'tsl.product_id', '=', 'p.id')
                ->leftJoin('categories as cat', 'p.category_id', '=', 'cat.id')
                ->leftJoinSub($payment_subquery, 'tp', function ($join) {
                    $join->on('tp.transaction_id', '=', 't.id');
                })
                ->where('t.business_id', $business_id)
                ->where('p.product_type', 'test')
                ->where('t.type', 'sell')
                ->where('t.status', 'final')
                ->where('cat.category_type', 'test')
                ->select(
                    'cat.id as category_id',
                    DB::raw("COALESCE(cat.name, '" . __('lang_v1.uncategorized') . "') as category_name"),
                    DB::raw('
                        SUM(CASE
                            WHEN p.type = "modifier" THEN 0
                            ELSE (tsl.quantity - tsl.quantity_returned)
                        END) as total_qty_sold
                    '),
                    DB::raw("SUM(tp.total_payment) as subtotal"),
                    DB::raw("
                        (
                            SELECT SUM(vld.qty_available)
                            FROM variation_location_details as vld
                            WHERE vld.variation_id = tsl.variation_id
                        ) as current_stock
                    ")
                )
                ->groupBy('cat.id', 'cat.name');

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $query->whereExists(function ($subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('transaction_payments')
                        ->whereColumn('transaction_payments.transaction_id', 't.id')
                        ->whereDate('transaction_payments.created_at', '>=', request()->start_date)
                        ->whereDate('transaction_payments.created_at', '<=', request()->end_date);
                });
            }

            return Datatables::of($query)
                ->editColumn('category_name', function ($row) {
                    return '<p class="text-blue">'.$row->category_name ?? __('lang_v1.uncategorized').'</p>' ;
                })
                ->editColumn('total_qty_sold', function ($row) {
                    $value = (float) $row->total_qty_sold;
                    return '<span class="row_qty_sold" data-orig-value="' . $value . '">' . number_format($value, 2) . '</span>';
                })
                ->editColumn('current_stock', function ($row) {
                    return '<span data-is_quantity="true" class="display_currency current_stock" data-currency_symbol=false data-orig-value="' . (float) $row->current_stock . '" data-unit="">' . (float) $row->current_stock . '</span>';
                })
                ->editColumn('subtotal', function ($row) {
                    return '<span class="row_subtotal" data-orig-value="' . $row->subtotal . '">' .
                        $this->transactionUtil->num_f($row->subtotal, true) . '</span>';
                })
                ->setRowAttr([
                    'data-href' => function ($row) use ($start_date, $end_date) {
                        return route('test.category.report.details.individual', [
                            'id' => $row->category_id,
                            'start_date' => $start_date,
                            'end_date' => $end_date
                        ]);
                    },
                    'class' => 'cursor-pointer btn-modal',
                    'data-container' => '.therapy_selection_report_modal'
                ])
                ->rawColumns(['current_stock', 'subtotal', 'total_qty_sold', 'category_name'])
                ->make(true);
        }
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
}
