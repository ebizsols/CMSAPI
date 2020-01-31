<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Company;
use App\Helper\ApiResponseHelper;
use App\Helper\Reply;
use App\Module;
use App\ModuleSetting;
use App\Package;

use App\Http\Resources\SuperAdmin\Packages\Package as PackageResources;
use App\Http\Resources\SuperAdmin\Packages\PackageCollection;

use App\Http\Requests\API\SuperAdmin\Packages\PackagesRequest;
use App\Http\Requests\API\SuperAdmin\Packages\CreateEditDataRequest;
use App\Http\Requests\API\SuperAdmin\Packages\DeleteRequest;
use App\Http\Requests\API\SuperAdmin\Packages\StoreRequest;
use App\Http\Requests\API\SuperAdmin\Packages\UpdateRequest;


class SuperAdminPackageController extends SuperAdminBaseController
{
    /**
     * AdminProductController constructor.
     */
    public function __construct() {
        parent::__construct();

    }

    /**
     * @param PackagesRequest $request
     * @return \App\Http\Resources\General\General|PackageCollection|null
     */
    public function index(PackagesRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $id = (isset($request->id) && $request->id != '') ? $request->id : 0;

        if($id){
            $packages = Package::where('id', '=', $id)->get();
        }else{
            $packages = Package::where('default', '!=', 'trial')->paginate(10);
        }

        return $request->successResponse($packages);
    }

    public function createEditData(CreateEditDataRequest $request)
    {
        if ($request->errors() != null) {
            return $request->errors();
        }

        $data = array();

        $request->successResponse($data);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreRequest $request
     * @return \App\Http\Resources\General\General|PackageResources|null
     */
    public function store(StoreRequest $request)
    {

        if($request->errors() != null){
            return $request->errors();
        }

        $package = new Package();

        $this->storeAndUpdate($package, $request, 'add');

        return $request->successResponse($package);
    }

    /**
     * Update the specified resource in storage.
     * @param UpdateRequest $request
     * @return \App\Http\Resources\General\General|PackageResources|null
     */
    public function update(UpdateRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $id = $request->id;

        $package = Package::find($id);


        $this->storeAndUpdate($package, $request, 'edit');

        return $request->successResponse($package);
    }

    /**
     * Remove the specified resource from storage.
     * @param DeleteRequest $request
     * @return \App\Http\Resources\General\General|PackageResources|null
     */
    public function destroy(DeleteRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $id = $request->id;

        $companies = Company::where('package_id', $id)->get();
        if($companies){

            $defaultPackage = Package::where('default', 'yes')->first();

            if($defaultPackage){

                foreach($companies as $company){
                    ModuleSetting::where('company_id', $company->id)->delete();

                    $moduleInPackage = (array)json_decode($defaultPackage->module_in_package);

                    $clientModules = ['projects', 'tickets', 'invoices', 'estimates', 'events', 'tasks', 'messages', 'payments', 'contracts', 'notices'];

                    if($moduleInPackage){

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
                    $company->package_id = $defaultPackage->id;

                    $company->save();
                }
            }
        }

        Package::destroy($id);

        return $request->successResponse(array(), ApiResponseHelper::DELETE_MSG,  ApiResponseHelper::DELETE_CODE);
    }


    /**
     * @param $package
     * @param $request
     * @param $type
     */
    public function storeAndUpdate( $package, $request, $type) {

        $package->name = $request->name;
        $package->description = $request->description;
        $package->annual_price = $request->annualPrice;
        $package->monthly_price = $request->monthlyPrice;
        $package->max_employees = $request->maxEmployees;
        $package->module_in_package = json_encode($request->moduleInPackage);
        $package->stripe_annual_plan_id = $request->stripeAnnualPlanId;
        $package->stripe_monthly_plan_id = $request->stripeMonthlyPlanId;
        $package->razorpay_annual_plan_id = $request->razorpayAnnualPlanId;
        $package->razorpay_monthly_plan_id = $request->razorpayMonthlyPlanId;
        $package->currency_id = $this->global->currency_id;

        $package->save();

        ModuleSetting::whereNull('company_id')->delete();

        if($type == 'edit'){
            if($request->has('module_in_package')){
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
        }

    }
}
