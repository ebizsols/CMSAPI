<?php

namespace App\Http\Controllers\API\Menus;

use App\Http\Controllers\Controller;
use App\Traits\CurrencyExchange;
use App\Http\Resources\Menu\Menu as MenuResource;
use App\Helper\ApiResponseHelper;
use App\User;

use App\Http\Requests\API\Menu\MenuRequest;


class UserLeftMenuController extends Controller
{
    use CurrencyExchange;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param MenuRequest $request
     * @return \App\Http\Resources\General\General|MenuResource|null
     */
    public function index(MenuRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $userId = $request->id;

        $roleId = $request->roleId;

        $userRole = $this->getUserType($userId, $roleId);

        if ($userRole == '') {
            return $request->errorResponse(ApiResponseHelper::ROLE_NOT_FOUND_MSG, ApiResponseHelper::ROLE_NOT_FOUND_MSG, ApiResponseHelper::NOT_FOUND_CODE);
        }

        $menu = array();

        switch ($userRole) {
            case "superAdmin":
                $menu = $this->superAdminLeftMenu();
                break;
            case "admin":
                $menu = $this->superAdminLeftMenu();
                break;
            case "client":
                $menu = $this->superAdminLeftMenu();
                break;
            case "employee":
                $menu = $this->superAdminLeftMenu();
                break;
            default:
        }

        if (empty($menu)) {
            return $request->errorResponse(ApiResponseHelper::MENU_NOT_FOUND_MSG, ApiResponseHelper::MENU_NOT_FOUND_MSG, ApiResponseHelper::NOT_FOUND_CODE);
        }

        $updateResponse = array('menu'=>$menu, 'role'=>$userRole);

        return $request->successResponse($updateResponse);
    }

    public function getUserType($userId, $roleId = 0)
    {
        $type = '';

        $userInfo = User::find($userId);

        $isSuperAdmin = isset($userInfo->super_admin) ? $userInfo->super_admin : 0;

        if ($roleId > 0) {
            $userRoles = User::with('role')
                ->withoutGlobalScope('active')
                ->join('role_user', 'role_user.user_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->select('users.*', 'roles.name as roleName', 'roles.id as roleId');
            $userRoles = $userRoles->where('users.id', $userId)->where('roles.id', $roleId)->get();
        } else {
            $userRoles = User::with('role')
                ->withoutGlobalScope('active')
                ->join('role_user', 'role_user.user_id', '=', 'users.id')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->select('users.*', 'roles.name as roleName', 'roles.id as roleId', \DB::raw("(select user_roles.role_id from role_user as user_roles where user_roles.user_id = users.id ORDER BY user_roles.role_id DESC limit 1) as `current_role`"));
            $userRoles = $userRoles->where('users.id', $userId)->get();
        }

        if ($isSuperAdmin == 1) {
            $type = 'superAdmin';
        } else if (!empty($userRoles)) {
            $type = isset($userRoles['0']->roleName) ? $userRoles['0']->roleName : '';
        }

        return $type;
    }

    public function superAdminLeftMenu()
    {
        $menuList = array(
            array('title' => 'Dashboard', 'route' => '/super-admin/Dashboard'),
            array('title' => 'Package', 'route' => '/super-admin/Packages/'),
            array('title' => 'Companies', 'route' => '/super-admin/Companies/'),
            array('title' => 'Super Admin', 'route' => '/super-admin/SuperAdmin/'),
            array('title' => 'Setting', 'route' => '/super-admin/Setting')
        );

        $menuItems = array();

        foreach ($menuList as $menuListIn) {
            $tempMenuItem = array();

            $tempMenuItem['title'] = $menuListIn['title'];

            $tempMenuItem['route'] = $menuListIn['route'];

            $menuItems[] = $tempMenuItem;
        }

        return $menuItems;
    }
}

?>