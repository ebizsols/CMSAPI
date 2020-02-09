<?php

namespace App\Http\Resources\SuperAdmin\Invoice;

use App\Http\Resources\BaseResource;
use App\Helper\ApiResponseHelper;

class Invoice extends BaseResource
{
    /**
     * Transform the resource into an array.
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
