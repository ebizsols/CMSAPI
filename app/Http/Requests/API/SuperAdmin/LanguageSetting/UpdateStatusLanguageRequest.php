<?php

namespace App\Http\Requests\API\SuperAdmin\LanguageSetting;

use App\Helper\ApiResponseHelper;
use App\Http\Resources\SuperAdmin\LanguageSetting\UpdateStatusLanguageData as UpdateStatusLanguageResourece;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\API\SuperAdmin\SuperAdminBaseRequest;

class UpdateStatusLanguageRequest extends SuperAdminBaseRequest
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
           'id'=>'required|exists:language_settings,id',
           'status'=>'required'
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
        $responseObj = new UpdateStatusLanguageResourece($data);

        return $responseObj->additional($response);
    }



}
?>