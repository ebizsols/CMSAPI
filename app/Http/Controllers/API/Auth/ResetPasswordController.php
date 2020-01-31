<?php

namespace App\Http\Controllers\API\Auth;


use App\Helper\ApiResponseHelper;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Validator;

use App\Http\Requests\API\Auth\ResetPasswordRequest;


class ResetPasswordController extends Controller
{

    use ResetsPasswords;

    public function reset(ResetPasswordRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }


        $response = $this->broker()->reset(
            $this->credentials($request), function ($user, $password) {
            $this->resetPassword($user, $password);
        }
        );

        if ($response == 'passwords.reset') {

            return $request->successResponse(null, ApiResponseHelper::PASSWORD_RESET_MSG, ApiResponseHelper::SUCCESS_CODE);

        } else {

            return $request->errorResponse(ApiResponseHelper::EXPIRE_RESET_PASSWORD_MSG, ApiResponseHelper::ERR_GENERIC_MSG, ApiResponseHelper::ERR_GENERIC_CODE);
        }


    }

}
