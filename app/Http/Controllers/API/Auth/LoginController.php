<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use Froiden\Envato\Traits\AppBoot;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\User\User as UserResource;
use App\Http\Resources\Login\Login as LoginResource;
use App\Helper\ApiResponseHelper;
use App\User;

Use App\Http\Requests\API\Auth\LoginRequest;


class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, AppBoot;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function index(LoginRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $this->validateLogin($request);


        if ($this->hasTooManyLoginAttempts($request)) {

            $this->fireLockoutEvent($request);


            return $request->errorResponse(ApiResponseHelper::TOO_MANY_LOGIN_MSG, ApiResponseHelper::ERR_GENERIC_MSG, ApiResponseHelper::TOO_MANY_LOGIN_CODE);
        }

        if ($this->attemptLogin($request)) {
            $userObj        = Auth::user();

            $token          = $userObj->createToken("ApiToken")->accessToken;

            $data = array();

            $data['token'] = $token;

            return $request->successResponse($data , ApiResponseHelper::SUCCESS_MSG, ApiResponseHelper::SUCCESS_CODE);
        }

        $this->incrementLoginAttempts($request);

        return $request->errorResponse(ApiResponseHelper::INVALID_LOGIN_MSG, ApiResponseHelper::ERR_GENERIC_MSG, ApiResponseHelper::INVALID_LOGIN_CODE);
    }

}
?>
