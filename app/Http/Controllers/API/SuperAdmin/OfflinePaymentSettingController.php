<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Http\Controllers\SuperAdmin\SuperAdminBaseController;
use App\OfflinePaymentMethod;

use App\Http\Requests\API\SuperAdmin\OfflinePaymentSetting\ListingRequest;
use App\Http\Requests\API\SuperAdmin\OfflinePaymentSetting\StoreRequest;
use App\Http\Requests\API\SuperAdmin\OfflinePaymentSetting\UpdateRequest;
use App\Http\Requests\API\SuperAdmin\OfflinePaymentSetting\DeleteRequest;
use App\Http\Requests\API\SuperAdmin\OfflinePaymentSetting\CreateEditDataRequest;


class OfflinePaymentSettingController extends SuperAdminBaseController
{
    public function __construct() {
        parent::__construct();
    }

    /**
     * @param ListingRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\OfflinePaymentSetting\OfflinePaymentSettingCollection|null
     */
    public function index(ListingRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        //$this->offlineMethods = OfflinePaymentMethod::withoutGlobalScope('company')->whereNull('company_id')->get();

        $this->offlineMethods =  OfflinePaymentMethod::all();
        return $request->successResponse($this->offlineMethods);
    }

    /**
     * @param StoreRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\OfflinePaymentSetting\OfflinePaymentSetting|null
     */
    public function store(StoreRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $method = new OfflinePaymentMethod();

        $method->name = $request->name;

        $method->description = $request->description;

        $method->save();

        return $request->successResponse($method);
    }

    /**
     * @param UpdateRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\OfflinePaymentSetting\OfflinePaymentSetting|null
     */
    public function update(UpdateRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $id = $request->id;

        $method = OfflinePaymentMethod::findOrFail($id);

        $method->name = $request->name;

        $method->description = $request->description;

        $method->status = $request->status;

        $method->save();

        return $request->successResponse($method, __('messages.methodsUpdated'));
    }

    /**
     * @param DeleteRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\OfflinePaymentSetting\OfflinePaymentSetting|null
     */
    public function destroy(DeleteRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $id = $request->id;

        OfflinePaymentMethod::destroy($id);

        return $request->successResponse(array(), __('messages.methodsDeleted'));
    }

    /**
     * @param CreateEditDataRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\OfflinePaymentSetting\OfflinePaymentSetting|null
     */
    public function createEditData(CreateEditDataRequest $request)
    {
        if($request->errors() != null ){
            return $request->errors() ;
        }

        $id = $request->id;

        $data = array();

        $data['method'] = OfflinePaymentMethod::findOrFail($id);;

        return $request->successResponse($data);
    }

}
