<?php

namespace App\Http\Requests\API\SuperAdmin\Companies;

use App\Helper\ApiResponseHelper;
use App\Http\Resources\SuperAdmin\Companies\Company as CompanyResource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\API\SuperAdmin\SuperAdminBaseRequest;

class StoreRequest extends SuperAdminBaseRequest
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
            "companyName" => "required|unique:companies,company_name",
            "companyEmail" => "required|email|unique:companies,company_email",
            "companyPhone" => "required",
            "address" => "required",
            "status" => "required",
            'email' => 'required|unique:users',
            'password' => 'required|min:6',
            'timezone' => "required",
            'locale' => "required",
            'currencyId' => "required"
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
            $message = ApiResponseHelper::SUCCESS_MSG;
        }

        if($code == ''){
            $code = ApiResponseHelper::SUCCESS_CODE;
        }

        $response = ApiResponseHelper::responseArray($code, $message);
        $responseObj = new CompanyResource($data);

        return $responseObj->additional($response);
    }
}
