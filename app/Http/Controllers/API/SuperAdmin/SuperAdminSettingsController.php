<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\GlobalCurrency;
use App\GlobalSetting;
use App\Helper\Reply;
use App\Http\Requests\SuperAdmin\Settings\UpdateGlobalSettings;
use App\Package;
use App\Traits\GlobalCurrencyExchange;
use App\LanguageSetting;
use App\Http\Requests\API\SuperAdmin\Setting\SettingRequest;
class SuperAdminSettingsController extends SuperAdminBaseController
{
    use GlobalCurrencyExchange;
    /**
     * SuperAdminInvoiceController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Settings';
        $this->pageIcon = 'icon-settings';
    }

    /**
     * Display edit form of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SettingRequest $request)
    {
        $data=array();
        $data['global'] = GlobalSetting::first();
        $data['currencies'] = GlobalCurrency::all();
        $data['timezones'] = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $data['languageSettings'] = LanguageSetting::where('status', 'enabled')->get();
        $data['cachedFile'] = \File::exists(base_path('bootstrap/cache/config.php'));
        return $request->successResponse($data);
    }

   public function update(UpdateGlobalSettingsRequest $request)
    {
         if($request->errors() != null){
            return $request->errors();
        }
        
        $id=$request->id;
        $setting = GlobalSetting::findOrFail($id);
        
        $oldCurrencyID = $setting->currency_id;
        $newCurrencyID = $request->currency_id;
        $setting->company_name = $request->company_name;
        $setting->company_email = $request->company_email;
        $setting->company_phone = $request->company_phone;
        $setting->website = $request->website;
        $setting->address = $request->address;
        $setting->currency_id = $request->currency_id;
        $setting->timezone = $request->timezone;
        $setting->locale = $request->locale;
        $setting->week_start = $request->week_start;

        if ($oldCurrencyID != $newCurrencyID) {
            try {
                $this->updateExchangeRates();
            } catch (\Throwable $th) {
                //throw $th;
            }
            $currency = GlobalCurrency::where('id', $newCurrencyID)->first();

            $packages = Package::all();
            foreach ($packages as $package) {
                if ($package->annual_price != 0 && $package->monthly_price != 0) {
                    $package->annual_price = $package->annual_price * $currency->exchange_rate;
                    $package->monthly_price = $package->monthly_price * $currency->exchange_rate;
                    $package->currency_id = $request->input('currency_id');
                    $package->save();
                }
            }
        }

        //        $setting->google_map_key = $request->input('google_map_key');
        $setting->google_recaptcha_key = $request->google_recaptcha_key;
        $setting->google_recaptcha_secret = $request->google_recaptcha_secret;

//        if ($request->hasFile('logo')) {
//            $setting->logo = $request->logo->hashName();
//            $request->logo->store('user-uploads/app-logo');
//        }
//        $setting->last_updated_by = $this->user->id;
//
//        if ($request->hasFile('login_background')) {
//            $request->login_background->storeAs('user-uploads', 'login-background.jpg');
//            $setting->login_background = 'login-background.jpg';
//        }
        $setting->save();
        return $request->successResponse($setting);
    }
}
