<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\FooterMenu;
use App\Helper\Reply;
use App\Http\Requests\SuperAdmin\FooterSetting\StoreRequest;
use App\Http\Requests\SuperAdmin\FooterSetting\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\API\SuperAdmin\FrontSetting\AllFooterMenuRequest;
use App\Http\Requests\API\SuperAdmin\FrontSetting\AddEditFooterMenuRequest;
use App\Http\Requests\API\SuperAdmin\FrontSetting\DeleteFooterMenuRequest;
use App\Http\Requests\API\SuperAdmin\FrontSetting\AddFooterMenuRequest;
use App\Http\Requests\API\SuperAdmin\FrontSetting\UpdateFooterMenuRequest;
class SuperAdminFooterSettingController extends SuperAdminBaseController
{
    public function __construct() {
        parent::__construct();
        $this->pageTitle = 'Front Footer Settings';
        $this->pageIcon = 'icon-settings';
    }

    /**
     * Display edit form of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AllFooterMenuRequest $request)
    {
        $data=array();
        $data['footer'] = FooterMenu::all();
        return $request->successResponse($data);
    }
    public function addEdit(AddEditFooterMenuRequest $request)
    {
        $data=array();
        if($request->id > 0)
        {
            $id=$request->id;
            if($request->errors() != null )
             {
                return $request->errors() ;
             }
            $data['footer'] = FooterMenu::findOrFail($id);
        }
        else
        {
            $data['footer'] = FooterMenu::all();
        }
        return $request->successResponse($data);
    }

    /**
     * @param StoreRequest $request
     * @return array
     */
    public function store(AddFooterMenuRequest $request)
    {
        if($request->errors() != null )
        {
           return $request->errors() ;
        }
        $footer = new FooterMenu();
        $footer->name = $request->title;
        $footer->slug = Str::slug($request->title);
        $footer->description = $request->description;
        $footer->save();
        return $request->successResponse($footer, __('messages.feature.addedSuccess'));
       
    }

    /**
     * @param UpdateRequest $request
     * @param $id
     * @return array
     */
    public function update(UpdateFooterMenuRequest $request)
    {
        if($request->errors() != null )
        {
           return $request->errors() ;
        }
        $id=$request->id;
        $footer = FooterMenu::findOrFail($id);
        $footer->name = $request->title;
        $footer->description = $request->description;
        $footer->save();
        return $request->successResponse($footer, __('messages.feature.addedSuccess'));
    }

    /**
     * @param Request $request
     * @param $id
     * @return array
     */
    public function destroy(DeleteFooterMenuRequest $request)
    {
        if($request->errors() != null )
        {
           return $request->errors() ;
        }
        $id=$request->id;
        FooterMenu::destroy($id);
        $data=array();
        return $request->successResponse($data, __('messages.feature.deletedSuccess'));
    }
}
