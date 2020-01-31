<?php

namespace App\Http\Resources\SuperAdmin\Packages;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\ApiResponseHelper;

class Package extends JsonResource
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

        if (isset($return['module_in_package'])) {
            $return['module_in_package'] = json_decode($return['module_in_package']);
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