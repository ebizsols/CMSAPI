<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\GlobalCurrency;
use App\GlobalSetting;
use App\Helper\ApiResponseHelper;
use App\Helper\Reply;
use App\Http\Requests\Currency\StoreCurrencyExchangeKey;
use App\Traits\GlobalCurrencyExchange;
use GuzzleHttp\Client;

Use App\Http\Requests\API\SuperAdmin\CurrencySetting\DeleteRequest;
Use App\Http\Requests\API\SuperAdmin\CurrencySetting\ListingRequest;
Use App\Http\Requests\API\SuperAdmin\CurrencySetting\StoreRequest;
Use App\Http\Requests\API\SuperAdmin\CurrencySetting\UpdateRequest;

class SuperAdminCurrencySettingController extends SuperAdminBaseController
{
    use GlobalCurrencyExchange;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param ListingRequest $request
     * @return \App\Http\Resources\General\General|null
     */
    public function index(ListingRequest $request)
    {
        if($request->errors()){
            return $request->errors();
        }

        $this->currencies = GlobalCurrency::all();

        return $request->successResponse($this->currencies);
    }

    /**
     * @param StoreRequest $request
     * @return \App\Http\Resources\General\General
     */
    public function store(StoreRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        $currency = new GlobalCurrency();

        $currency->currency_name = $request->currencyName;

        $currency->currency_symbol = $request->currencySymbol;

        $currency->currency_code = $request->currencyCode;

        $currency->usd_price = $request->usdPrice;

        $currency->is_cryptocurrency = $request->isCryptocurrency;

        $currencyApiKey = GlobalSetting::first()->currency_converter_key;
        $currencyApiKey = ($currencyApiKey) ? $currencyApiKey : env('CURRENCY_CONVERTER_KEY');

        /*if ($request->isCryptocurrency == 'no') {
            // get exchange rate
            $client = new Client();
            $res = $client->request('GET', 'https://free.currencyconverterapi.com/api/v6/convert?q='. $this->global->currency->currency_code . '_' . $currency->currency_code.'&compact=ultra&apiKey='.$currencyApiKey, ['verify' => false]);
            $conversionRate = $res->getBody();
            $conversionRate = json_decode($conversionRate, true);

            if (!empty($conversionRate)) {
                $currency->exchange_rate = $conversionRate[strtoupper($this->global->currency->currency_code . '_' . $currency->currency_code)];
            }
        } else {

            if ($this->global->currency->currency_code != 'USD') {
                // get exchange rate
                $client = new Client();
                $res = $client->request('GET', 'https://free.currencyconverterapi.com/api/v6/convert?q='.$this->global->currency->currency_code.'_USD&compact=ultra&apiKey='.$currencyApiKey, ['verify' => false]);
                $conversionRate = $res->getBody();
                $conversionRate = json_decode($conversionRate, true);

                $usdExchangePrice = $conversionRate[strtoupper($this->global->currency->currency_code) . '_USD'];
                $currency->exchange_rate = ceil(($currency->usd_price / $usdExchangePrice));
            }
        }*/

        $currency->save();

        try {
            $this->updateExchangeRates();
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $request->successResponse($currency);
    }

    public function update(UpdateRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }
        $id = $request->id;
        $currency = GlobalCurrency::findOrFail($id);
        $currency->currency_name = $request->currencyName;
        $currency->currency_symbol = $request->currencySymbol;
        $currency->currency_code = $request->currencyCode;
        $currency->exchange_rate = $request->exchangeRate;

        $currencyApiKey = GlobalSetting::first()->currency_converter_key;
        $currencyApiKey = ($currencyApiKey) ? $currencyApiKey : env('CURRENCY_CONVERTER_KEY');

        $currency->usd_price = $request->usdPrice;
        $currency->is_cryptocurrency = $request->isCryptocurrency;

        /*if ($request->is_cryptocurrency == 'no') {
            // get exchange rate
            $client = new Client();
            $res = $client->request('GET', 'https://free.currencyconverterapi.com/api/v6/convert?q='. $this->global->currency->currency_code . '_' . $currency->currency_code.'&compact=ultra&apiKey='.$currencyApiKey, ['verify' => false]);
            $conversionRate = $res->getBody();
            $conversionRate = json_decode($conversionRate, true);

            if (!empty($conversionRate)) {
                $currency->exchange_rate = $conversionRate[strtoupper($this->global->currency->currency_code) . '_' . $currency->currency_code];
            }
        } else {

            if ($this->global->currency->currency_code != 'USD') {
                // get exchange rate
                $client = new Client();
                $res = $client->request('GET', 'https://free.currencyconverterapi.com/api/v6/convert?q='.$this->global->currency->currency_code.'_USD&compact=ultra&apiKey='.$currencyApiKey, ['verify' => false]);
                $conversionRate = $res->getBody();
                $conversionRate = json_decode($conversionRate, true);

                $usdExchangePrice = $conversionRate[strtoupper($this->global->currency->currency_code) . '_USD'];
                $currency->exchange_rate = $usdExchangePrice;
            }
        }*/

        $currency->save();


        try {
            $this->updateExchangeRates();
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $request->successResponse($currency);
    }


    public function destroy(DeleteRequest $request)
    {
        if($request->errors()){
            return $request->errors();
        }

        $id = $request->id;

        if ($this->global->currency_id == $id) {

            return $request->errorResponse(__('modules.currencySettings.cantDeleteDefault'), ApiResponseHelper::ERR_GENERIC_MSG, ApiResponseHelper::ERR_GENERIC_CODE );
        }

        GlobalCurrency::destroy($id);

        return $request->successResponse(array(), ApiResponseHelper::DELETE_MSG,  ApiResponseHelper::DELETE_CODE);
    }



    public function edit($id)
    {
        $this->currency = GlobalCurrency::findOrFail($id);
        return view('super-admin.currency-settings.edit', $this->data);
    }





    public function exchangeRate($currency)
    {
        $currencyApiKey = GlobalSetting::first()->currency_converter_key;
        $currencyApiKey = ($currencyApiKey) ? $currencyApiKey : env('CURRENCY_CONVERTER_KEY');

        // get exchange rate
        $client = new Client();
        $res = $client->request('GET', 'https://free.currencyconverterapi.com/api/v6/convert?q='. $this->global->currency->currency_code . '_' . $currency.'&compact=ultra&apiKey='.$currencyApiKey, ['verify' => false]);
        $conversionRate = $res->getBody();
        $conversionRate = json_decode($conversionRate, true);

        return $conversionRate[strtoupper($this->global->currency->currency_code) . '_' . $currency];
    }

    public function updateExchangeRate()
    {
        try {
            $this->updateExchangeRates();
        } catch (\Throwable $th) {
            //throw $th;
        }
        return Reply::success(__('messages.exchangeRateUpdateSuccess'));
    }

    public function currencyExchangeKey(){
             return view('super-admin.currency-settings.currency_exchange_key', $this->data);
    }

    public function currencyExchangeKeyStore(StoreCurrencyExchangeKey $request){
        $this->global->currency_converter_key = $request->currency_converter_key;
        $this->global->save();
        return Reply::success(__('messages.currencyConvertKeyUpdated'));
    }


}
