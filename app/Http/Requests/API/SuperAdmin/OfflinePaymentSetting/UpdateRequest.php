<?php

namespace App\Http\Requests\API\SuperAdmin\OfflinePaymentSetting;

use App\Helper\ApiResponseHelper;
use App\Http\Resources\SuperAdmin\OfflinePaymentSetting\OfflinePaymentSetting as OfflinePaymentSettingResource;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\API\SuperAdmin\SuperAdminBaseRequest;

class UpdateRequest extends SuperAdminBaseRequest
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
            'id' => 'required|exists:offline_payment_methods,id',
            'name' => [
                'required',
                Rule::unique('offline_payment_methods')->where(function ($query) {
                    return $query->whereNull('company_id');
                })
            ],
            'description' => 'required'
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

        $responseObj = new OfflinePaymentSettingResource($data);

        return $responseObj->additional($response);
    }
}
