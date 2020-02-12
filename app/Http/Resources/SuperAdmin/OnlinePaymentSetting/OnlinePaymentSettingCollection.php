<?php

namespace App\Http\Resources\SuperAdmin\OnlinePaymentSetting;

use App\Http\Resources\BaseResourceCollection;
use App\Helper\ApiResponseHelper;

class OnlinePaymentSettingCollection extends BaseResourceCollection
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
