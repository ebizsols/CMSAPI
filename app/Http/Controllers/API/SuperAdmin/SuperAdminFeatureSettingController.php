<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Feature;
use App\Helper\Reply;
use App\Http\Requests\SuperAdmin\FeatureSetting\StoreRequest;
use App\Http\Requests\SuperAdmin\FeatureSetting\UpdateRequest;
use App\Http\Requests\SuperAdmin\FrontSetting\UpdateFrontSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Http\Requests\API\SuperAdmin\FrontSetting\AllFeatureImagesRequest;
use App\Http\Requests\API\SuperAdmin\FrontSetting\AddEditImagesRequest;
use App\Http\Requests\API\SuperAdmin\FrontSetting\DeleteImagesRequest;
use App\Http\Requests\API\SuperAdmin\FrontSetting\AddImagesRequest;
use App\Http\Requests\API\SuperAdmin\FrontSetting\UpdateImagesRequest;

class SuperAdminFeatureSettingController extends SuperAdminBaseController
{
    /**
     * SuperAdminInvoiceController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->pageTitle = 'Front Feature Settings';
        $this->pageIcon = 'icon-settings';
    }

    /**
     * Display edit form of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(AllFeatureImagesRequest $request)
    {
        $data=array();
         if($request->errors() != null){
            return $request->errors();
        }
        $type=$request->type;
        $data['features'] = Feature::where('type', $type)->get();
        return $request->successResponse($data);

    }
    public function addEdit(AddEditImagesRequest $request)
    {
        $data=array();
        if($request->id > 0)
        {
            if($request->errors() != null )
             {
                return $request->errors() ;
             }
            $id = $request->id;
             $data['feature'] = Feature::findOrFail($id);
             $data['type'] = $request->type;
        }
        else
        {
            $data['type'] = $request->type;
            $this->type = $request->type;
            $type = $request->type;
            $data['features'] = Feature::where('type', $type)->get();;
            
        }
        return $request->successResponse($data);
    }

    /**
     * @param UpdateFrontSettings $request
     * @param $id
     * @return array
     */
    public function store(AddImagesRequest $request)
    {
        if($request->errors() != null )
        {
           return $request->errors() ;
        }
        $feature = new Feature();
        $type =  $request->type;
        $feature->title = $request->title;
        $feature->type = $request->type;
        $feature->description = $request->description;
        if($request->has('icon')){
            $feature->icon = $request->icon;
        }
        else{
            if ($request->hasFile('image')) {
                $feature->image = $request->image->hashName();
                $request->image->store('front-uploads/feature');
            }
        }

        $feature->save();
        return $request->successResponse($feature, __('messages.feature.addedSuccess'));

    }

    /**
     * @param UpdateFrontSettings $request
     * @param $id
     * @return array
     */
    public function update(UpdateImagesRequest $request)
    {
        if($request->errors() != null )
        {
           return $request->errors() ;
        }
        $id=$request->id;
        $feature = Feature::findOrFail($id);

        $oldImage = $feature->image;

        $feature->title = $request->title;
        $feature->type = $request->type;
        $feature->description = $request->description;
        if($request->has('icon')){
            $feature->icon = $request->icon;
        }
        else{
            if ($request->hasFile('image')) {
                $feature->image = $request->image->hashName();
                $request->image->store('front-uploads/feature');
                if($oldImage){ File::delete('front-uploads/feature/'.$oldImage); }
            }
        }

        $type =  $request->type;
        $feature->save();
        return $request->successResponse($feature, __('messages.feature.addedSuccess'));
       

    }


    /**
     * @param UpdateFrontSettings $request
     * @param $id
     * @return array
     */
    public function destroy(DeleteImagesRequest $request)
    {
        if($request->errors() != null )
        {
           return $request->errors() ;
        }
        $id = $request->id;
        $type =  $request->type;
        Feature::destroy($id);
        $data=array();
        return $request->successResponse($data, __('messages.feature.deletedSuccess'));

    }
}
