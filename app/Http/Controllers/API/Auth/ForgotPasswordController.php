<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Validator;
use App\Helper\ApiResponseHelper;

use App\Http\Requests\API\Auth\ResetPasswordTokenRequest;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(ResetPasswordTokenRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $passwordObj = $this->broker();

        $user = $passwordObj->getUser($this->credentials($request));

        if (!$user) {

            return $request->errorResponse(ApiResponseHelper::USER_NOT_FOUND_MSG,ApiResponseHelper::ERR_GENERIC_MSG,ApiResponseHelper::USER_NOT_FOUND_CODE);
        }

        $token = $passwordObj->createToken($user);

        $data = array();

        $data['token'] = $token;

        return $request->successResponse($data);
    }

}