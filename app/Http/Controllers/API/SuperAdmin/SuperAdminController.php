<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Helper\ApiResponseHelper;
use App\Helper\Reply;
use App\Http\Requests\SuperAdmin\SuperAdmin\UpdateSuperAdmin;
use App\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

use App\Http\Requests\API\SuperAdmin\SuperAdmin\SuperAdminsRequest;
use App\Http\Requests\API\SuperAdmin\SuperAdmin\DeleteRequest;
use App\Http\Requests\API\SuperAdmin\SuperAdmin\StoreRequest;
use App\Http\Requests\API\SuperAdmin\SuperAdmin\UpdateRequest;

class SuperAdminController extends SuperAdminBaseController
{

    public function __construct() {
        parent::__construct();
    }

    /**
     * @param SuperAdminsRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\SuperAdmin\SuperAdminCollection|null
     */
    public function index(SuperAdminsRequest $request)
    {
        if($request->errors() != null ){
            return $request->errors() ;
        }

        if($request->id > 0 ){

            $id = $request->id;

            $superAdmins = User::getSuperAdminById($id);

        }else{

            $superAdmins = User::allSuperAdmin();
        }

        if($superAdmins->count() <= 0){

            return $request->successResponse($superAdmins, ApiResponseHelper::USER_NOT_FOUND_MSG, ApiResponseHelper::NOT_FOUND_CODE);
        }else{

            return $request->successResponse($superAdmins);
        }
    }

    /**
     * @param StoreRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\SuperAdmin\SuperAdmin|null
     */
    public function create(StoreRequest $request)
    {
        if($request->errors() != null ){
            return $request->errors() ;
        }

        $user = $this->store($request);

        return $request->successResponse($user);

    }

    /**
     * @param UpdateRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\SuperAdmin\SuperAdmin|null
     */
    public function edit(UpdateRequest $request)
    {
        if($request->errors() != null ){
            return $request->errors() ;
        }

        $user = $this->update($request);

        return $request->successResponse($user);
    }

    /**
     * @param DeleteRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\SuperAdmin\SuperAdmin|null
     */
    public function destroy(DeleteRequest $request)
    {
        if($request->errors() != null ){
            return $request->errors() ;
        }

        $id = $request->id;

        User::destroy($id);

        return $request->successResponse(array(), ApiResponseHelper::DELETE_MSG,  ApiResponseHelper::DELETE_CODE);
    }


    /**
     * @param StoreRequest $request
     * @return User
     */
    public function store(StoreRequest $request)
    {
        $user = new User();

        $user->name = $request->input('name');

        $user->email = $request->input('email');

        $user->password = Hash::make($request->input('password'));

        $user->mobile = $request->input('mobile');

        $user->login = 'enable';

        $user->status = 'active';

        $user->super_admin = '1';

        if ($request->hasFile('image')) {
            File::delete('user-uploads/avatar/'.$user->image);

            $user->image = $request->image->hashName();

            $request->image->store('user-uploads/avatar');

            // resize the image to a width of 300 and constrain aspect ratio (auto height)
            $img = Image::make('user-uploads/avatar/'.$user->image);

            $img->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $img->save();
        }

        $user->save();

        return $user;
    }

    /**
     * @param UpdateRequest $request
     * @return mixed
     */
    public function update(UpdateRequest $request)
    {

        $id = $request->id;

        $user = User::withoutGlobalScope('active')->findOrFail($id);

        $user->name = $request->input('name');

        $user->email = $request->input('email');

        if ($request->password != '') {
            $user->password = Hash::make($request->input('password'));
        }

        $user->mobile = $request->input('mobile');

        if ($this->user->id != $user->id)
        {
            $user->status = $request->input('status');
        }

        if ($request->hasFile('image')) {
            File::delete('user-uploads/avatar/'.$user->image);

            $user->image = $request->image->hashName();

            $request->image->store('user-uploads/avatar');

            // resize the image to a width of 300 and constrain aspect ratio (auto height)
            $img = Image::make('user-uploads/avatar/'.$user->image);

            $img->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            $img->save();
        }
        $user->save();

        return $user;
    }
}
