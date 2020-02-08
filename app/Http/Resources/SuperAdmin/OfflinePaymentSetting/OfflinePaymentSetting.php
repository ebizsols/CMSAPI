<?php

namespace App\Http\Resources\SuperAdmin\OfflinePaymentSetting;

use App\Http\Resources\BaseResource;
use App\Helper\ApiResponseHelper;
use App\Http\Resources\SuperAdmin\Packages\Package as PackageResources;

class OfflinePaymentSetting extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $return = parent::toArray($request);

        if(isset($return['package'])){
            $return['package'] = new PackageResources($this->package);
        }

        return $return;
    }

    public function with($request)
    {
        return [
            'ApiInfo'=>ApiResponseHelper::withEveryResponse()
        ];
    }
}
