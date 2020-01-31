<?php

namespace App\Http\Requests\API\SuperAdmin\Packages;

use App\Helper\ApiResponseHelper;
use App\Http\Resources\SuperAdmin\Companies\Company as CompanyResource;
use App\Http\Resources\SuperAdmin\Packages\Package as PackageResources;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\API\SuperAdmin\SuperAdminBaseRequest;

class DeleteRequest extends SuperAdminBaseRequest
{

    public $errors = null;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'id' => 'required|exists:packages,id',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $validator = new ValidationException($validator);

        $this->errors = $validator->errors();
    }

    public function errors()
    {
        if($this->errors != null){
            return $this->errorResponse($this->errors, ApiResponseHelper::VALIDATION_MSG, ApiResponseHelper::REQUIRE_CODE);
        }

        return $this->errors;
    }

    public function successResponse($data = array(),  $message = '', $code = '')
    {
        if($message == ''){
            $message = ApiResponseHelper::DELETE_MSG;
        }

        if($code == ''){
            $code = ApiResponseHelper::DELETE_CODE;
        }

        $response = ApiResponseHelper::responseArray($code, $message);
        $responseObj = new PackageResources($data);

        return $responseObj->additional($response);
    }

}
