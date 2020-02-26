<?php

namespace App\Http\Resources\SuperAdmin\FrontSetting;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\ApiResponseHelper;
use App\Package;

class AddEditFooterMenu extends JsonResource
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

        return $return;
    }

    public function with($request)
    {
        return [
            'ApiInfo'=>ApiResponseHelper::withEveryResponse()
        ];
    }
}
?>