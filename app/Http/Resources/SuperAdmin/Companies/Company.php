<?php

namespace App\Http\Resources\SuperAdmin\Companies;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Helper\ApiResponseHelper;
use App\Http\Resources\SuperAdmin\Packages\Package as PackageResources;
use App\Http\Resources\SuperAdmin\Packages\PackageCollection;
use App\Package;

class Company extends JsonResource
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
