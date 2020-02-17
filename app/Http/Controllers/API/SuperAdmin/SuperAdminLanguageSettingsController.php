<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Helper\Reply;
use App\Http\Requests\SuperAdmin\Language\StoreRequest;
use App\Http\Requests\SuperAdmin\Language\UpdateRequest;
use App\LanguageSetting;
use Illuminate\Http\Request;
use App\Http\Requests\API\SuperAdmin\LanguageSetting\AllLanguageRequest;
use App\Http\Requests\API\SuperAdmin\LanguageSetting\AddEditLanguageRequest;
use App\Http\Requests\API\SuperAdmin\LanguageSetting\UpdateLanguageRequest;
use App\Http\Requests\API\SuperAdmin\LanguageSetting\DeleteLanguageRequest;
use App\Http\Requests\API\SuperAdmin\LanguageSetting\StoreLanguageRequest;
use App\Http\Requests\API\SuperAdmin\LanguageSetting\UpdateStatusLanguageRequest;

class SuperAdminLanguageSettingsController extends SuperAdminBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __( 'app.menu.settings');
        $this->pageIcon = 'icon-settings';
    }

    public function index(AllLanguageRequest $request){
        $data=array();
        $data['languages'] = LanguageSetting::all();
        return $request->successResponse($data);
    }
    public function createEditData(AddEditLanguageRequest $request)
    {
        $data = array();

        if($request->id > 0)
        {
            if($request->errors() != null )
             {
                return $request->errors() ;
             }
            $id = $request->id;
             $data['languageSetting'] = LanguageSetting::findOrFail($id);
        }
        else
        {

            
        }
        return $request->successResponse($data);
    }
    public function update(UpdateStatusLanguageRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }
        
        $id=$request->id;
        $setting = LanguageSetting::findOrFail($id);
        $setting->status = $request->status;
        $setting->save();
         return $request->successResponse($setting,__('messages.settingsUpdated'));
    }

    /**
     * @param UpdateRequest $request
     * @param $id
     * @return array
     */
    public function updateData(UpdateLanguageRequest $request)
    {
         if($request->errors() != null){
            return $request->errors();
        }
        $id=$request->id;
        $setting = LanguageSetting::findOrFail($id);
        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->status = $request->status;
        $setting->save();
        session(['language_setting' => \App\LanguageSetting::where('status', 'enabled')->get()]);
        return $request->successResponse($setting);
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(StoreLanguageRequest $request)
    {
         if($request->errors() != null){
            return $request->errors();
        }
        $setting = new LanguageSetting();
        $setting->language_name = $request->language_name;
        $setting->language_code = $request->language_code;
        $setting->status = $request->status;
        $setting->save();
        session(['language_setting' => \App\LanguageSetting::where('status', 'enabled')->get()]);
        return $request->successResponse($setting,__('messages.languageAdded'));
    }
    public function destroy(DeleteLanguageRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }
        $id=$request->id;
        $data=array();
        LanguageSetting::destroy($id);
        return $request->successResponse($data, __('messages.languageDeleted'));
    }
}
