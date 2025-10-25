<?php

namespace Modules\Crm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Crm\Entities\FacebookLead;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FacebookLeadController extends Controller
{
    // ✅ API Receiver for Second App (POST) - Only store received data
    public function webhook(Request $request)
    {
        Log::info('📥 Received data in second app:', $request->all());

        $validator = Validator::make($request->all(), [
            'fb_lead_id' => 'nullable|string',
            'page_id' => 'nullable|string',
            'created_time' => 'nullable|string',
            'ad_id' => 'nullable|string',
            'ad_name' => 'nullable|string',
            'adset_id' => 'nullable|string',
            'adset_name' => 'nullable|string',
            'campaign_name' => 'nullable|string',
            'form_id' => 'nullable|string',
            'platform' => 'nullable|string',
            'is_organic' => 'nullable|boolean',
            'full_name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone_number' => 'nullable|string',
            'city' => 'nullable|string',
            'lead_status' => 'nullable|string',
            'raw_data' => 'nullable|array',
            'raw_payload' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            Log::error('❌ Invalid data received for second app: ' . $validator->errors()->toJson());
            return response()->json(['error' => 'Invalid data'], 400);
        }

        $data = $validator->validated();

        $lead = FacebookLead::updateOrCreate(
            ['fb_lead_id' => $data['fb_lead_id']],
            $data
        );

        Log::info("✅ Lead {$lead->fb_lead_id} stored in second app successfully.");

        return response()->json(['success' => true], 200);
    }
}