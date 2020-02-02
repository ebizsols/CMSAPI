<?php

namespace App\Http\Controllers\API\SuperAdmin;

use App\Company;
use App\Package;
use App\PaypalInvoice;
use App\RazorpayInvoice;
use App\StripeInvoice;
use App\Traits\CurrencyExchange;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use App\SmtpSetting;
//use App\Http\Resources\SuperAdmin\Dashboard\Dashboard;
//use App\Http\Resources\SuperAdmin\Dashboard\DashboardCollection;
use App\Http\Requests\API\SuperAdmin\Dashboard\DashboardRequest;

class SuperAdminDashboardController extends SuperAdminBaseController
{
    use CurrencyExchange;

    public function __construct() {
        parent::__construct();
        $this->pageTitle = 'app.menu.dashboard';
        $this->pageIcon = 'icon-speedometer';
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function index(DashboardRequest $request) {
          if($request->errors() != null ){
            return $request->errors() ;
        }
        $data = array();
        $totalCompanies = Company::count();
        $data['totalCompanies'] = $totalCompanies;
        $totalPackages = Package::where('default', '!=', 'trial')->count();
        $data['totalPackages']=$totalPackages;
        $activeCompanies = Company::where('status', '=', 'active')->count();
        $data['activeCompanies']=$activeCompanies;
        $inactiveCompanies = Company::where('status', '=', 'inactive')->count();
        $data['inactiveCompanies']=$inactiveCompanies;
        $expiredCompanies =  Company::with('package')->where('status', 'license_expired')->get();
        $expiredCompaniesTotal = $expiredCompanies->count();
        $data['expiredCompanies']=$expiredCompaniesTotal;
        // Collect recent 5 licence expired companies detail
        $recentExpired = $expiredCompanies->sortBy('updated_at')->take(5);
        $data['recentExpired']=$recentExpired;
        // Collect data for earning chart
        $months = [
            '1' => 'jan',
            '2' => 'Feb',
            '3' => 'Mar',
            '4' => 'Apr',
            '5' => 'May',
            '6' => 'Jun',
            '7' => 'Jul',
            '8' => 'Aug',
            '9' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec',
        ];

        $invoices = StripeInvoice::selectRaw('SUM(amount) as amount,YEAR(pay_date) as year, MONTH(pay_date) as month')->whereNotNull('stripe_invoices.pay_date')->havingRaw('year = ?', [Carbon::now()->year])->groupBy('month')->get()->groupBy('month')->toArray();
        $paypalInvoices = PaypalInvoice::selectRaw('SUM(total) as total,YEAR(paid_on) as year, MONTH(paid_on) as month')->where('paypal_invoices.status', 'paid')->havingRaw('year = ?', [Carbon::now()->year])->groupBy('month')->get()->groupBy('month')->toArray();
        $razorpayInvoice = RazorpayInvoice::selectRaw('SUM(amount) as amount,YEAR(pay_date) as year, MONTH(pay_date) as month')->whereNotNull('razorpay_invoices.pay_date')->havingRaw('year = ?', [Carbon::now()->year])->groupBy('month')->get()->groupBy('month')->toArray();
        $chartData = [];
        foreach($months as $key => $month) {
            if(key_exists($key, $invoices)) {
                foreach($invoices[$key] as $amount) {
                    $chartData[] = ['month' => $month, 'amount' => $amount['amount']];
                }
            }
            else{
                $chartData[] = ['month' => $month, 'amount' => 0];
            }

            if(key_exists($key, $razorpayInvoice)) {
                foreach($razorpayInvoice[$key] as $amount) {
                    $chartData[] = ['month' => $month, 'amount' => $amount['amount']];
                }
            }
            else{
                $chartData[] = ['month' => $month, 'amount' => 0];
            }
            if(key_exists($key, $paypalInvoices)) {
                foreach($paypalInvoices[$key] as $amount) {
                    $chartData[] = ['month' => $month, 'amount' => $amount['total']];
                }
            }
            else{
                $chartData[] = ['month' => $month, 'amount' => 0];
            }
        }

        $chartData = json_encode($chartData);
         $data['chartData']=$chartData;
        // Collect data of recent registered 5 companies
        $recentRegisteredCompanies = Company::with('package')->take(5)->latest()->get();

        $data['recentRegisteredCompanies']=$recentRegisteredCompanies;
        $stripe = DB::table("stripe_invoices")
            ->join('packages', 'packages.id', 'stripe_invoices.package_id')
            ->join('companies', 'companies.id', 'stripe_invoices.company_id')
            ->selectRaw('stripe_invoices.id ,companies.company_name, packages.name, companies.package_type,"Stripe" as method, stripe_invoices.pay_date as paid_on, "" as end_on ,stripe_invoices.next_pay_date, stripe_invoices.created_at')
            ->whereNotNull('stripe_invoices.pay_date');

        $razorpay = DB::table("razorpay_invoices")
            ->join('packages', 'packages.id', 'razorpay_invoices.package_id')
            ->join('companies', 'companies.id', 'razorpay_invoices.company_id')
            ->selectRaw('razorpay_invoices.id ,companies.company_name , packages.name as name, companies.package_type, "Razorpay" as method, razorpay_invoices.pay_date as paid_on , "" as end_on,razorpay_invoices.next_pay_date,razorpay_invoices.created_at')
            ->whereNotNull('razorpay_invoices.pay_date');

        $allInvoices = DB::table("paypal_invoices")
            ->join('packages', 'packages.id', 'paypal_invoices.package_id')
            ->join('companies', 'companies.id', 'paypal_invoices.company_id')
            ->selectRaw('paypal_invoices.id,companies.company_name, packages.name, companies.package_type, "Paypal" as method, paypal_invoices.paid_on, paypal_invoices.end_on,paypal_invoices.next_pay_date,paypal_invoices.created_at')
            ->where('paypal_invoices.status', 'paid')
            ->union($stripe)
            ->union($razorpay)
            ->get();

        $recentSubscriptions = $allInvoices->sortByDesc(function ($temp, $key) {
            return Carbon::parse($temp->created_at)->getTimestamp();
        })->take(5);
         $data['recentSubscriptions']=$recentSubscriptions;
        try {
            $client = new Client();
            $res = $client->request('GET', config('froiden_envato.updater_file_path'), ['verify' => false]);
            $lastVersion = $res->getBody();
            $lastVersion = json_decode($lastVersion, true);

            if ($lastVersion['version'] > File::get(public_path().'version.txt')) {
                $this->lastVersion = $lastVersion['version'];
            }
        } catch (\Throwable $th) {
            //throw $th;
        }

        $progressPercent = $this->progressbarPercent();
        $data['progressPercent']=$progressPercent;
         return $request->successResponse($data);
       // return view('super-admin.dashboard.index', $this->data);
    }

    private function progressbarPercent()
    {
        $this->smtpSetting = SmtpSetting::first();
        $this->user =  DB::table('users')->where("id",8)->first();
        $totalItems = 4;
        $completedItem = 1;
        $progress = [];
        $progress['progress_completed'] = false;

        if ($this->global->company_email != 'company@email.com') {
            $completedItem++;
            $progress['company_setting_completed'] = true;
        }

        if ($this->smtpSetting->verified !== 0 || $this->smtpSetting->mail_driver == 'mail') {
            $progress['smtp_setting_completed'] = true;

            $completedItem++;
        }

        if ($this->user->email != 'superadmin@example.com') {
            $progress['profile_setting_completed'] = true;

            $completedItem++;
        }
       

        if ($totalItems == $completedItem) {
            $progress['progress_completed'] = true;
        }

        $this->progress = $progress;


        return ($completedItem / $totalItems) * 100;

    }

}
