<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Modules\Clinic\Entities\PatientAppointmentRequ;
use Modules\Clinic\Entities\PatientSessionInfo;

class UpdateExpiredAppointments extends Command
{
    protected $signature = 'appointments:update-expired';

    // The console command description.
    protected $description = 'Update expired appointments where the confirmation is not done and the request date is past today';

    // Create a new command instance.
    public function __construct()
    {
        parent::__construct();
    }

    // Execute the console command.
    public function handle()
    {
        $today = Carbon::now()->setTime(12, 0, 0); // Set to 10 PM today
        
        PatientAppointmentRequ::where('request_date', '=', $today->toDateString())->where('confirm_status', '!=', 1)
            ->update([
                'cancel_status' => 1,
                'remarks' => 'expired',
                'updated_at' => Carbon::now(),
            ]);

        PatientAppointmentRequ::where('request_date', '=', $today->toDateString())->where('confirm_status', '=', 1)
            ->whereNull('can_visit')
            ->where('is_visited', '!=', 1)
            ->where('remarks', '=', 'confirmed')
            ->update([
                'cancel_status' => 1,
                'remarks' => 'expired',
                'updated_at' => Carbon::now(),
            ]);
        $this->info('Expired appointments updated successfully.');
    }
}
