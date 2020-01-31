<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Helper\ApiResponseHelper;
use App\User;

Use App\Http\Requests\API\User\UserRequest;


class UserController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getLoginInUserDetail(UserRequest $request)
    {
        $userInfo = Auth::user();

        $userId = $userInfo->id;

        $users = User::with('role')
            ->withoutGlobalScope('active')
            ->join('role_user', 'role_user.user_id', '=', 'users.id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('users.*', 'roles.name as roleName', 'roles.id as roleId', \DB::raw("(select user_roles.role_id from role_user as user_roles where user_roles.user_id = users.id ORDER BY user_roles.role_id DESC limit 1) as `current_role`"));

        $users = $users->where('users.id', $userId)->get();

        if(isset($users[0])){
            $userInfo = $users[0];
        }else{
            $userInfo->setAttribute('roleId', '-1');

            $userInfo->setAttribute('roleName', 'Not Assign');
        }

        return $request->successResponse($userInfo, ApiResponseHelper::SUCCESS_MSG, ApiResponseHelper::SUCCESS_CODE);

    }



}
?>
