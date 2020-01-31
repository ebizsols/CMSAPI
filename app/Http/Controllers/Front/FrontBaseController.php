<?php

namespace App\Http\Controllers\Front;

use App\FooterMenu;
use App\FrontDetail;
use App\GlobalSetting;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class FrontBaseController extends Controller
{
    /**
     * @var array
     */
    public $data = [];

    /**
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    /**
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->data[$name];
    }

    /**
     * @param $name
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->data[ $name ]);
    }

    /**
     * UserBaseController constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setting = GlobalSetting::first();
        $this->global = $this->setting;
        App::setLocale($this->global->locale);
        Carbon::setLocale($this->global->locale);
        $this->footerSettings = FooterMenu::whereNotNull('slug')->get();

        setlocale(LC_TIME, $this->global->locale . '_' . strtoupper($this->global->locale));

        $this->detail = FrontDetail::first();



    }

}
