<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Helper\Reply;
//use App\Http\Requests\SuperAdmin\Profile\UpdateSuperAdmin;
use App\Http\Requests\API\SuperAdmin\Profile\UpdateRequest;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

class SuperAdminProfileController extends SuperAdminBaseController
{

    public function __construct() {
        parent::__construct();
    }


    /**
     * @param UpdateRequest $request
     * @return \App\Http\Resources\General\General|\App\Http\Resources\SuperAdmin\Profile\Profile|null
     */
    public function update(UpdateRequest $request) {

        if($request->errors() != null){
            return  $request->errors();
        }
        $id = $request->id;

        $user = User::withoutGlobalScope('active')->where('super_admin', '1')->findOrFail($id);

        $user->name = $request->input('name');

        $user->email = $request->input('email');

        if ($request->password != '') {

            $user->password = Hash::make($request->input('password'));
        }
        $user->mobile = $request->input('mobile');

        $user->gender = $request->input('gender');



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

        return $request->successResponse($user->getAttributes(), __('messages.superAdminUpdated'));
    }

    public function updateOneSignalId(Request $request){
        $user = User::find($this->user->id);
        $user->onesignal_player_id = $request->userId;
        $user->save();
    }
}
