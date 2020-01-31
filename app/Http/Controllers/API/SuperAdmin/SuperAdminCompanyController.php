<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Company;
use App\Currency;
use App\GlobalCurrency;
use App\Helper\ApiResponseHelper;
use App\Package;
use App\Role;
use App\StripeInvoice;
use App\User;

use App\Http\Resources\SuperAdmin\Companies\Company as CompanyResource;
use App\Http\Resources\SuperAdmin\Companies\CompanyCollection;

use App\Http\Requests\API\SuperAdmin\Companies\AssignPackageRequest;
use App\Http\Requests\API\SuperAdmin\Companies\CreateEditDataRequest;
use App\Http\Requests\API\SuperAdmin\Companies\DeleteRequest;
use App\Http\Requests\API\SuperAdmin\Companies\CompaniesRequest;
use App\Http\Requests\API\SuperAdmin\Companies\StoreRequest;
use App\Http\Requests\API\SuperAdmin\Companies\UpdateRequest;

use App\Traits\CurrencyExchange;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SuperAdminCompanyController extends SuperAdminBaseController
{
    use CurrencyExchange;

    /**
     * AdminProductController constructor.
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * @param CompaniesRequest $request
     * @return \App\Http\Resources\General\General|CompanyCollection|null
     */
    public function index(CompaniesRequest $request)
    {

        if($request->errors() != null ){
            return $request->errors() ;
        }

        $id = (isset($request->id) && $request->id != '') ? $request->id : 0;

        $companies = Company::with('currency', 'package');

        if ($id) {
            $companies = $companies->where('id', $request->id);

            $companies = $companies->get();

        } else {

            if ($request->package != 'all' && $request->package != '') {
                $companies = $companies->where('package_id', $request->package);
            }

            if ($request->type != 'all' && $request->type != '') {
                $companies = $companies->where('package_type', $request->type);
            }

            $companies = $companies->paginate(10);
        }

        return $request->successResponse($companies);
    }

    public function createEditData(CreateEditDataRequest $request)
    {
        if($request->errors() != null ){
            return $request->errors() ;
        }

        $data = array();

        if($request->id > 0){

            $id = $request->id;

            $this->company = Company::find($id);

            $this->currencies = Currency::where('company_id', $id)->get();

            $data['company'] = $this->company;

            $data['currencies'] = $this->currencies;

        }else{

            $this->currencies = GlobalCurrency::all();

            $data['currencies'] = $this->currencies;
        }

        $this->timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);

        $this->packages = Package::all();

        $data['timezones'] = $this->timezones;

        $data['currencies'] = $this->currencies;

        $data['packages'] = $this->packages;

        return $request->successResponse($data);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreRequest $request
     * @return \App\Http\Resources\General\General|CompanyResource|null
     */
    public function store(StoreRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }

        DB::beginTransaction();

        $company = new Company();

        $companyDetail = $this->storeAndUpdate($company, $request);

        $globalCurrency = GlobalCurrency::findOrFail($request->currencyId);
        $currency = Currency::where('currency_code', $globalCurrency->currency_code)
            ->where('company_id', $companyDetail->id)->first();

        if (is_null($currency)) {
            $currency = new Currency();

            $currency->currency_name = $globalCurrency->currency_name;

            $currency->currency_symbol = $globalCurrency->currency_symbol;

            $currency->currency_code = $globalCurrency->currency_code;

            $currency->is_cryptocurrency = $globalCurrency->is_cryptocurrency;

            $currency->usd_price = $globalCurrency->usd_price;

            $currency->company_id = $companyDetail->id;

            $currency->save();
        }

        $company->currency_id = $currency->id;
        $company->save();

        $adminRole = Role::where('name', 'admin')->where('company_id', $companyDetail->id)->withoutGlobalScope('active')->first();

        $user = new User();

        $user->company_id = $companyDetail->id;

        $user->name = 'Admin';

        $user->email = $request->email;

        $user->password = bcrypt($request->password);

        $user->save();

        $user->roles()->attach($adminRole->id);

        $employeeRole = Role::where('name', 'employee')->where('company_id', $user->company_id)->first();
        $user->roles()->attach($employeeRole->id);

        DB::commit();

        return $request->successResponse($company);
    }

    /**
     *
     * @param UpdateRequest $request
     * @return \App\Http\Resources\General\General|CompanyResource
     */
    public function update(UpdateRequest $request)
    {
        if($request->errors() != null){
            return $request->errors();
        }
        $id = $request->companyId;

        $company = Company::find($id);

        $this->storeAndUpdate($company, $request);

        $company->currency_id = $request->currency_id;

        $company->save();

        return $request->successResponse($company);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteRequest $request
     * @return CompanyResource|null
     */
    public function destroy(DeleteRequest $request)
    {
        if($request->errors() != null ){
            return $request->errors() ;
        }

        $id = $request->id;

        Company::destroy($id);

        return $request->successResponse(array(), ApiResponseHelper::DELETE_MSG,  ApiResponseHelper::DELETE_CODE);
    }


    /**
     * @param AssignPackageRequest $request
     * @return CompanyResource|null
     */
    public function updatePackage(AssignPackageRequest $request)
    {
        if($request->errors() != null ){
           return $request->errors() ;
        }

        $companyId = $request->companyId;

        $company = Company::find($companyId);

        try {
            $package = Package::find($request->package);

            $company->package_id = $package->id;

            $company->package_type = $request->packageType;

            $company->status = 'active';

            $payDate = $request->pay_date ? Carbon::parse($request->pay_date) : Carbon::now();

            $company->licence_expire_on = ($company->package_type == 'monthly') ?
                $payDate->copy()->addMonth()->format('Y-m-d') :

                $payDate->copy()->addYear()->format('Y-m-d');

            $nextPayDate = $request->next_pay_date ? Carbon::parse($request->next_pay_date) : $company->licence_expire_on;

            if ($company->isDirty('package_id') || $company->isDirty('package_type')) {
                $stripeInvoice = new StripeInvoice();

            } else {
                $stripeInvoice = StripeInvoice::where('company_id', $companyId)->orderBy('created_at', 'desc')->first();

            }

            $stripeInvoice->company_id = $company->id;

            $stripeInvoice->package_id = $company->package_id;

            $stripeInvoice->amount = $request->amount ?: $package->{$request->packageType . '_price'};

            $stripeInvoice->pay_date = $payDate;

            $stripeInvoice->next_pay_date = $nextPayDate;

            $stripeInvoice->save();

            $company->save();

            return $request->successResponse($company);

        } catch (\Exception $e) {

            return $request->errorResponse(array('Some unknown error occur. Please try again.'));
        }
    }

    /**
     * @param $company
     * @param $request
     * @return mixed
     */
    public function storeAndUpdate($company, $request)
    {
        $company->company_name = $request->companyName;
        $company->company_email = $request->companyEmail;
        $company->company_phone = $request->companyPhone;
        $company->website = $request->website;
        $company->address = $request->address;
        $company->currency_id = $request->currencyId;
        $company->timezone = $request->timezone;
        $company->locale = $request->locale;
        $company->status = $request->status;

        if ($request->hasFile('logo')) {
            $company->logo = $request->logo->hashName();
            $request->logo->store('user-uploads/app-logo');
        }

        $company->last_updated_by = 8;//$this->user->id;

        $company->save();

        // will enable later
        //$this->updateExchangeRatesCompanyWise($company);

        return $company;
    }
}
