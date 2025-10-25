<?php

namespace App\Http\Controllers;

use App\District;
use App\Upazila;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function getDistricts($divisionId)
    {
        $districts = District::where('division_id', $divisionId)->get();
        return response()->json($districts);
    }

    public function getUpazilas($districtId)
    {
        $upazilas = Upazila::where('district_id', $districtId)->get();
        return response()->json($upazilas);
    }
}
