<?php

namespace Modules\Clinic\Http\Controllers;

use App\System;
use Composer\Semver\Comparator;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Utils\ModuleUtil;
use App\Utils\Util;
class InstallController extends Controller
{
    public function __construct()
    {
        $this->module_name = 'Clinic';
        $this->appVersion = config('clinic.module_version');
    }

    /**
     * Install
     *
     * @return Response
     */
    public function index()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M');

        $this->installSettings();

        //Check if asset management installed or not.
        $is_installed = System::getProperty($this->module_name.'_version');
        if (empty($is_installed)) {
            DB::statement('SET default_storage_engine=INNODB;');
            Artisan::call('module:migrate', ['module' => 'Clinic', '--force' => true]);
            Artisan::call('module:publish', ['module' => 'Clinic']);
            System::addProperty($this->module_name.'_version', $this->appVersion);
        }

            $output = ['success' => 1,
                'msg' => 'Clinic module installed succesfully',
            ];

        return redirect()
            ->action([\App\Http\Controllers\Install\ModulesController::class, 'index'])
            ->with('status', $output);
    }

    /**
     * Initialize all install functions
     */
    private function installSettings()
    {
        config(['app.debug' => true]);
        Artisan::call('config:clear');
    }

    /**
     * Uninstall
     *
     * @return Response
     */
    public function uninstall()
    {
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            System::removeProperty($this->module_name.'_version');

            $output = ['success' => true,
                'msg' => __('lang_v1.success'),
            ];
        } catch (\Exception $e) {
            $output = ['success' => false,
                'msg' => $e->getMessage(),
            ];
        }

        return redirect()->back()->with(['status' => $output]);
    }

    /**
     * update module
     *
     * @return Response
     */
    public function update()
    {
       
        if (! auth()->user()->can('superadmin')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            DB::beginTransaction();
            ini_set('max_execution_time', 0);
            ini_set('memory_limit', '512M');

            $clinic_version = System::getProperty($this->module_name.'_version');

            if (Comparator::greaterThan($this->appVersion, $clinic_version)) {
                ini_set('max_execution_time', 0);
                ini_set('memory_limit', '512M');
                $this->installSettings();

                DB::statement('SET default_storage_engine=INNODB;');
                Artisan::call('module:migrate', ['module' => 'Clinic', '--force' => true]);
                Artisan::call('module:publish', ['module' => 'Clinic']);
                System::setProperty($this->module_name.'_version', $this->appVersion);
            } else {
                abort(404);
            }

            DB::commit();

            $output = ['success' => 1,
                'msg' => 'Clinic module updated Succesfully to version '.$this->appVersion.' !!',
            ];

            return redirect()->back()->with(['status' => $output]);
        } catch (Exception $e) {
            DB::rollBack();
            exit($e->getMessage());
        }
    }
}
