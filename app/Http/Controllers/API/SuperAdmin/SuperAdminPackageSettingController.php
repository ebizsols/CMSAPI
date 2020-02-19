<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Company;
use App\GlobalSetting;
use App\Helper\Reply;
use App\Http\Requests\SuperAdmin\FrontSetting\UpdateFrontSettings;
use App\Module;
use App\ModuleSetting;
use App\Package;
use App\PackageSetting;
use Illuminate\Http\Request;
use App\Http\Requests\API\SuperAdmin\TrialSetting\AllTrialSettingRequest;
use App\Http\Requests\API\SuperAdmin\TrialSetting\UpdateTrialSettingRequest;
class SuperAdminPackageSettingController extends SuperAdminBaseController
{
    /**
     * SuperAdminInvoiceController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->pageTitle =  __('app.package').' Settings';
        $this->pageIcon = 'icon-settings';
    }

    /**
     * Display edit form of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AllTrialSettingRequest $request)
    {
        $data=array();
        $data['global'] = GlobalSetting::first();
        $data['packageSetting'] = PackageSetting::first();
        $data['package'] = Package::where('default', 'trial')->first();
        $data['modules'] = Module::all();
        return $request->successResponse($data);
    }

    /**
     * @param UpdateFrontSettings $request
     * @param $id
     * @return array
     */
    public function update(UpdateTrialSettingRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }
        $id=$request->id;
        $data=array();
        $setting = PackageSetting::findOrFail($id);

        $setting->no_of_days = $request->input('no_of_days');
        $setting->status = ($request->has('status')) ? 'active' : 'inactive';
        $setting->modules = json_encode($request->module_in_package);
        $setting->notification_before = $request->notification_before;
        $setting->save();
        $data['setting']=$setting;
        $package = Package::where('default', 'trial')->first();
        if($package){
            $package->module_in_package = $setting->modules;
            $package->name = $request->input('name');
            $package->max_employees = $request->input('max_employees');
            $package->currency_id = $this->global->currency_id;
            $package->save();
            $data['package']=$package;
        }

        if($request->has('module_in_package') && !is_null($package)){
            $companies = Company::where('package_id', $package->id)->get();

            foreach($companies as $company){
                ModuleSetting::where('company_id', $company->id)->delete();

                $moduleInPackage = (array)json_decode($package->module_in_package);
                $clientModules = ['projects', 'tickets', 'invoices', 'estimates', 'events', 'tasks', 'messages', 'payments', 'contracts', 'notices'];
                foreach ($moduleInPackage as $module) {

                    if(in_array($module, $clientModules)){
                        $moduleSetting = new ModuleSetting();
                        $moduleSetting->company_id = $company->id;
                        $moduleSetting->module_name = $module;
                        $moduleSetting->status = 'active';
                        $moduleSetting->type = 'client';
                        $moduleSetting->save();
                    }

                    $moduleSetting = new ModuleSetting();
                    $moduleSetting->company_id = $company->id;
                    $moduleSetting->module_name = $module;
                    $moduleSetting->status = 'active';
                    $moduleSetting->type = 'employee';
                    $moduleSetting->save();

                    $moduleSetting = new ModuleSetting();
                    $moduleSetting->company_id = $company->id;
                    $moduleSetting->module_name = $module;
                    $moduleSetting->status = 'active';
                    $moduleSetting->type = 'admin';
                    $moduleSetting->save();
                }
            }
        }
        return $request->successResponse($data,__('messages.uploadSuccess'));
//        return Reply::success(__('messages.uploadSuccess'));

    }
}
