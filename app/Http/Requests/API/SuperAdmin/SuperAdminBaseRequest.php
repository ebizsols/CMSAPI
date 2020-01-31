<?php

namespace App\Http\Requests\API\SuperAdmin;

use App\Http\Requests\API\BaseRequest;

class SuperAdminBaseRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

}