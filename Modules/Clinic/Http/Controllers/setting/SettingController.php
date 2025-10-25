<?php

namespace Modules\Clinic\Http\Controllers\setting;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\BusinessLocation;
use App\Business;
use App\User;
use App\Utils\BusinessUtil;
class SettingController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    protected $businessUtil;

     public function __construct(BusinessUtil $businessUtil)
     {
         $this->businessUtil = $businessUtil;
     }

    public function index()
    {
        $business_id = request()->session()->get('user.business_id');
        $business_locations = BusinessLocation::forDropdownSell($business_id, false);
        $business = Business::where('id', $business_id)->first();
    
        $common_settings = is_string($business->common_settings) ? json_decode($business->common_settings, true) : (array) $business->common_settings;

        $business->clinic_location = is_array($business->clinic_location) ? $business->clinic_location : [];
    
        return view('clinic::settings.index', compact('business_locations', 'common_settings', 'business'));
    }
    
    public function setting1(){
        return view('clinic::settings.setting1');
    }
    public function setting2(){
        return view('clinic::settings.setting2');
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
    // Check if the user has permission to access business settings
    if (!auth()->user()->can('business_settings.access')) {
        abort(403, 'Unauthorized action.');
    }

    try {
        // Check if there are restrictions in demo mode
        $notAllowed = $this->businessUtil->notAllowedInDemo();
        if (!empty($notAllowed)) {
            return $notAllowed;
        }

        // Validate the request input (you may need to adjust the validation rules)
        $validatedData = $request->validate([
            'common_settings' => 'required|array', // Ensure common_settings is an array
            'common_settings.clinic_location' => 'required|integer|exists:businesses,id', // Validate clinic_location
        ]);

        // Get the 'common_settings' from the validated request
        $common_settings = $validatedData['common_settings'];

        // If it's a string, decode it
        if (is_string($common_settings)) {
            $common_settings = json_decode($common_settings, true);
        } elseif (!is_array($common_settings)) {
            $common_settings = [];
        }

        // Extract the clinic_location
        $clinic_location = $common_settings['clinic_location']; 

        // Retrieve the Business object by the clinic_location (check if it exists)
        $business = Business::where('id', $clinic_location)->first();

        if (!$business) {
            // If no business was found, log an error and return a response
            \Log::error('Business not found for clinic_location: ' . $clinic_location);

            $output = [
                'success' => 0,
                'msg' => __('messages.business_not_found'),
            ];
            return redirect('clinic-settings')->with('status', $output);
        }

        // Update the common_settings for the business
        $business->common_settings = $common_settings;
        $business->save();

        // Success message
        $output = [
            'success' => 1,
            'msg' => __('business.settings_updated_success'),
        ];

    } catch (\Exception $e) {
        // Log the error (use error instead of emergency for general errors)
        \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine() . ' Message: ' . $e->getMessage());

        // Return an error message if something went wrong
        $output = [
            'success' => 0,
            'msg' => __('messages.something_went_wrong'),
        ];
    }

    // Redirect with the status message
    return redirect('clinic-settings')->with('status', $output);
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
