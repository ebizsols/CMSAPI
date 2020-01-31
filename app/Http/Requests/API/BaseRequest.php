<?php

namespace App\Http\Requests\API;

use App\Helper\ApiResponseHelper;
use App\Http\Resources\General\General as GeneralResource;
use Illuminate\Foundation\Http\FormRequest;

class BaseRequest extends FormRequest
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

    public function successResponse($data = array(),  $message = '', $code = '')
    {
        if($message == ''){
            $message = ApiResponseHelper::SUCCESS_MSG;
        }

        if($code == ''){
            $code = ApiResponseHelper::SUCCESS_CODE;
        }

        $response = ApiResponseHelper::responseArray($code, $message);
        $responseObj = new GeneralResource($data);

        return $responseObj->additional($response);
    }

    public function errorResponse($error = array(), $message = '', $code = '')
    {
        if($message == ''){
            $message = ApiResponseHelper::ERR_UNKNOWN;
        }

        if($code == ''){
            $code = ApiResponseHelper::ERR_GENERIC_CODE;
        }

        $response = ApiResponseHelper::responseArray($code, $message, $error);
        $responseObj = new GeneralResource(array());

        return $responseObj->additional($response);
    }

}