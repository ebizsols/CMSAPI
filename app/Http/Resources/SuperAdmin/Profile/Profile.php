<?php

namespace App\Http\Resources\SuperAdmin\Profile;

use App\Http\Resources\BaseResource;
use App\Helper\ApiResponseHelper;

class Profile extends BaseResource
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
