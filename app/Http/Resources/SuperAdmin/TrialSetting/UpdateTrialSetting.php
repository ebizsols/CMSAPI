<?php

namespace App\Http\Resources\SuperAdmin\TrialSetting;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\ApiResponseHelper;
use App\Package;

class UpdateTrialSetting extends JsonResource
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

//        if(isset($return['package'])){
//            $return['package'] = new PackageResources($this->package);
//        }

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