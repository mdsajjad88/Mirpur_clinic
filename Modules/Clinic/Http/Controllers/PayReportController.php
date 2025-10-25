<?php

namespace Modules\Clinic\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Yajra\DataTables\DataTables;

class PayReportController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index(Request $request)
    {
        return view('clinic::report.peyment');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function getPayReport(Request $request){
        $data = [
            [
                'id' => 1,
                'tnx_id' => 'TXN123',
                'pname' => 'John Doe',
                'p_id' => 'P001',
                'sname' => 'Dr. Smith',
                'amount' => 150.00,
                'pay_method' => 'Credit Card',
                'date' => '2024-09-01',
                'helped_by' => 'Nurse Kelly'
            ],
            [
                'id' => 2,
                'tnx_id' => 'TXN124',
                'pname' => 'Jane Doe',
                'p_id' => 'P002',
                'sname' => 'Dr. Brown',
                'amount' => 200.00,
                'pay_method' => 'Cash',
                'date' => '2024-09-02',
                'helped_by' => 'Nurse John'
            ],
            // Add more sample records as needed
        ];

        return DataTables::of(collect($data))->make(true);
    }
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
}
