<?php

namespace App\Http\Resources\ResetPassword;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\ApiResponseHelper;

class ResetPassword extends JsonResource
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
