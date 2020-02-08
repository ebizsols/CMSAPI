<?php

namespace App\Http\Resources\SuperAdmin\OfflinePaymentSetting;

use App\Http\Resources\BaseResourceCollection;
use App\Helper\ApiResponseHelper;

class OfflinePaymentSettingCollection extends BaseResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }

    public function with($request)
    {
        return [
            'ApiInfo'=>ApiResponseHelper::withEveryResponse()
        ];
    }
}
