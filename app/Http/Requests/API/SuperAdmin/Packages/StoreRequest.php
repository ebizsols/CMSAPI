<?php

namespace App\Http\Requests\API\SuperAdmin\Packages;

use App\Helper\ApiResponseHelper;
use App\Http\Resources\SuperAdmin\Packages\PackageCollection;
use App\Http\Resources\SuperAdmin\Packages\Package as PackageResources;
use App\StripeSetting;
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
        $data = [
            'name' => 'required|unique:packages',
            'description' => 'required',
            'annualPrice' => 'required',
            'monthlyPrice' => 'required',
            'maxEmployees' => 'required|numeric',
            'moduleInPackage' => 'required',
        ];


        $stripe = StripeSetting::first();

        if(($this->get('annualPrice') > 0 && $this->get('monthlyPrice') > 0 ) &&  $stripe->razorpay_status == 'active'){
            $data['razorpayAnnualPlanId'] = 'required';
            $data['razorpayMonthlyPlanId'] = 'required';
        }


        if($this->get('annualPrice') > 0 && $this->get('monthlyPrice') > 0  ){
            $data['stripeAnnualPlanId'] = 'required';
            $data['stripeMonthlyPlanId'] = 'required';
        }
        else{
            $this->request->set('stripeAnnualPlanId','00');
            $this->request->set('stripeMonthlyPlanId','00');
        }

        return $data;
    }

    public function messages()
    {
        return [
            'module_in_package.required' => 'Select at-least one module.'
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
        $responseObj = new PackageResources($data);

        return $responseObj->additional($response);
    }



}
