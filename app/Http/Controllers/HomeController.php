<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\Merchant;
use App\Models\MerchantFraud;
use App\Models\Profile;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\User;
use common\integration\CommonNotification;
use common\integration\DataCipher;
use common\integration\MerchantPosCommissionService;
use common\integration\SecureDocument;
use common\integration\Utility\File;
use http\Url;
use Illuminate\Http\Request;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Session;
use Cookie;
use App\Models\Page;
use App\Models\SubModule;
use Illuminate\Support\Facades\Cache;
use Jenssegers\Date\Date;
use common\integration\GlobalFunction;
use App\Models\FailedLoginList;
use App\Models\SecurityImage;
use Carbon\Carbon;
use common\integration\BrandConfiguration;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {

        if (request()->get('action') && request()->get('action') == 'get-notifications-data') {
            return $this->getNotifications(request());
        }

        /*$banklist = [
            "Türkiye Cumhuriyeti Ziraat Bankası A.Ş.",
            "Türkiye Halk Bankası A.Ş.",
            "Türkiye Vakıflar Bankası T.A.O.",
            "Akbank T.A.Ş.",
            "Anadolubank A.Ş.",
            "Fibabanka A.Ş.",
            "Şekerbank T.A.Ş.",
            "Turkish Bank A.Ş.",
            "Türk Ekonomi Bankası (TEB) A.Ş.",
            "Türkiye İş Bankası A.Ş.",
            "Yapı ve Kredi Bankası A.Ş.",
            "Alternatifbank A.Ş.",
            "Burgan Bank A.Ş.",
            "Citibank A.Ş.",
            "Denizbank A.Ş.",
            "HSBC Bank A.Ş.",
            "ICBC Turkey Bank A.Ş.",
            "ING Bank A.Ş.",
            "Odea Bank A.Ş.",
            "QNB Finansbank A.Ş.",
            "Turkland Bank A.Ş.",
            "Türkiye Garanti Bankası A.Ş.",
        ];

        $country_name = "Türkiye";
        $i = 1;

        foreach ($banklist as $value){
            $address = "Address ".$i++;
            $b = new Bank();
            $b->name = $value;
            $b->address = $address;
            $b->country = $country_name;
            $b->save();
            echo "INSERT INTO `banks` (`name`, `address`, `country`, `created_at`) VALUES ('".$value."', '".$address."', '".$country_name."', '2019-09-03 14:31:41');
"."</br>";
        }*/

//        if(Auth::user()->failed_login_attempt > 0){
//            $word = Auth::user()->last_failed_login_datetime;
//            if(Auth::user()->language == 'tr'){
//                $date = date("j F",strtotime($word));
//                $result = Date::parse($date)->format('j F Y');
//                $time = date("H:i",strtotime($word));
//                $flash_message = 'You have failed attempt to login in '.$result.' at '.$time;
//            } else {
//                $date = date("d M",strtotime($word));
//                $result = date('d M ', strtotime($date));
//                $time = date("H:i",strtotime($word));
//                $flash_message = 'Giriş yapmak için '.$result.' '.$time.' başarısız denemeniz var ';
//            }

//            flash($flash_message, 'danger');
//            User::where('id', Auth::user()->id)->update(['failed_login_attempt'=>0]);
//        }


        //
        $authUser = Auth::user();
        $lan = $authUser->language;
        app()->setLocale($lan);

        $update_data = [];

        $failed_login_attempt = $authUser->failed_login_attempt;
        $msg = __('Last failed login attempt');

        if (\common\integration\BrandConfiguration::allowLoginBlockTime()) {

            $failed_login_attempt_key = GlobalFunction::createSessionKey($authUser, 'fail_attempts_count');

            if (GlobalFunction::hasBrandSession($failed_login_attempt_key)) {
                $failed_login_attempt = GlobalFunction::getBrandSession($failed_login_attempt_key);
                GlobalFunction::unsetBrandSession($failed_login_attempt_key);
            }
            $msg = '<a href=' . route('visitLastLoginHistory') . '>' . __('Last failed login attempt') . '</a>';
        }


        if ($failed_login_attempt > 0) {
            $date = date("d.m.Y - H:i", strtotime($authUser->last_failed_login_datetime));
            // $msg = ($lan == 'tr') ? "Son başarısız giriş denemesi" : "Last failed login attempt";
            $flash = $msg . ': ' . $date;
            flash($flash, 'danger');
            $update_data['failed_login_attempt'] = 0;
        }

        if ($authUser->first_time_login < 1) {
            if (BrandConfiguration::allowSecurityImageInAdminPanel() && !isset($request->security_image) &&
                !BrandConfiguration::securityImageForResetPasssword()) {
                $security_images = (new SecurityImage())->getSecurityImagesWithBrandCodeAndStatus(SecurityImage::STATUS_ACTIVE);
                return view('auth.login_security_image', compact('security_images'));
            }
            if (isset($request->security_image)) {
                $update_data['security_image_id'] = $request->security_image;
            }
            $update_data['first_time_login'] = 1;
        }

        if (!empty($update_data)) {
            User::where('id', $authUser->id)->update($update_data);
        }

        //session()->put('locale', $lan);
        // app()->setLocale($lan);
        Cookie::queue('locale', $lan, time() + 31556926);

        $home_cache = Setting::first();

        Cache::put('logo_path', $home_cache->logo_path);
        Cache::put('title', $home_cache->title);
        Cache::put('footer', $home_cache->footer);
        Cache::put('favicon_path', $home_cache->favicon_path);

        $merchants = collect();
        $withdrawal_requests = collect();
        $deposit_requests = collect();
        $transactions = collect();
        $customers = collect();
        $withdrawal_redirect_routes = BrandConfiguration::getRedirectRouteDashboard('Withdrawals');
        $deposit_redirect_routes = BrandConfiguration::getRedirectRouteDashboard('Deposits');

        return view('front', compact('merchants', 'withdrawal_requests', 'deposit_requests', 'transactions', 'customers', 'withdrawal_redirect_routes', 'deposit_redirect_routes'));
    }


    private function getDynamicRoute()
    {
        $menu_modules = Session::get('modules');
        $user_permissions = Session::get('user_permission');
        //dd($user_permissions);exit;
        $pagesArray = [];
        $submodulesArray = [];
        $pages = Page::all();
        $submodules = SubModule::all();
        foreach ($submodules as $submodule) {
            $submodule['name'] = strtolower(str_replace(" ", "", $submodule['name']));
            $submodulesArray[$submodule['id']] = ['controller' => $submodule->controller_name, 'name' => $submodule['name']];
        }

        foreach ($pages as $page) {

            $pagesArray[$page['id']] = ['method_name' => $page['method_name'], 'method_type' => $page['method_type']];
        }


        $access_module = array();
        $moduleArray = [];
        foreach ($menu_modules as $module) {
            $moduleArray[$module['id']] = strtolower(str_replace(" ", "", $module['name']));
        }


        foreach ($user_permissions as $pages) {

            if (isset($submodulesArray[$pages->submodule_id]) && isset($moduleArray[$pages->module_id])) {
                //dd($submodulesArray[$pages->submodule_id]['controller']);exit;
                //Route::get('settings/edit', 'SettingController@edit')->name('settings.edit');
                $submodulesArray[$pages->submodule_id]['controller'] = str_replace("controller", "", $submodulesArray[$pages->submodule_id]['controller']);
                $submodulesArray[$pages->submodule_id]['controller'] = str_replace("Controller", "", $submodulesArray[$pages->submodule_id]['controller']);
                $submodulesArray[$pages->submodule_id]['controller'] = $submodulesArray[$pages->submodule_id]['controller'] . "Controller";
                $method_type = "get";
                if ($pages->method_type == 1) {
                    $method_type = "post";
                } else if ($pages->method_type == 2) {
                    $method_type = "get";
                } else if ($pages->method_type == 3) {
                    $method_type = "put";
                } else if ($pages->method_type == 4) {
                    $method_type = "delete";
                }
                if ($pages->module_id >= 1005)
                    echo "Route::" . $method_type . "('" . $moduleArray[$pages->module_id] . '/' .
                        str_replace("_", "", $submodulesArray[$pages->submodule_id]['name']) .
                        "/" . $pages->method_name . "' , '" .
                        ucfirst($submodulesArray[$pages->submodule_id]['controller']) .
                        "@" . $pages->method_name . "')->name('" . $moduleArray[$pages->module_id] .
                        "." . $submodulesArray[$pages->submodule_id]['name'] . "." . $pages->method_name . "');<br/><br/>";
            }
        }
        exit;
    }

    public function loadNotification(Request $request)
    {
        $limit = 100;
//        if (!empty($request->page_no) && is_numeric($request->page_no)){
//            $limit = $limit * $request->page_no;
//        }

        $unread_notifications = \App\Models\Notification::getUnreadUserNotifications(auth()->id(),
            auth()->user()->user_type, $limit); // new query

        $notificationBlade = view('partials.notificationList', compact('unread_notifications'))->render();
        return $notificationBlade;

    }

    public function visitLastLoginHistory(Request $request)
    {

        if (!\common\integration\BrandConfiguration::allowLoginBlockTime()) {
            return redirect()->back();
        }

        $data['cmsInfo'] = [
            'moduleTitle' => __("Fail Login"),
            'subModuleTitle' => __("Fail Login History"),
            'subTitle' => __("Fail Login History"),
        ];

        $page_limit = 10;


        if (isset($request->page_limit)) {
            $page_limit = $request->page_limit;
        }

        if (isset($request->date_range) && !empty($request->date_range)) {
            $search['date_range'] = $request->date_range;
            $date = explode('-', $request->date_range);
            $from_date = trim($date[0]);
            $to_date = trim($date[1]);
            $search['from_date'] = $from_date;
            $search['to_date'] = $to_date;
        } else {
            $search['from_date'] = Carbon::now()->subDays(30)->format('Y/m/d');
            $search['to_date'] = Carbon::now()->format('Y/m/d');
            $search['date_range'] = $search['from_date'] . " - " . $search['to_date'];
        }

        $data['page_limit'] = $page_limit;

        //dd($search['date_range'] );

        $search['page_limit'] = $data['page_limit'] = $page_limit;

        $search['name'] = isset($request->name) ? $request->name : '';
        $search['user_id'] = auth()->user()->id;

        $data['search'] = $search;

        $data['details'] = (new FailedLoginList)->details($search, $page_limit);

        return view('fail_login_history', $data);

    }

    public function getNotifications($request)
    {

        $data['per_page'] = $request->per_page;
        $data['page_no'] = $request->page;
        $data['panel_id'] = $request->panel_id;


        $data['notifications'] = CommonNotification::userAllNotifications($data['per_page']);

        $last_page = $data['notifications']->lastPage();

        $view = \View::make('header_notification.notification', $data)->render();

        return response()->json([
            'last_page' => $last_page,
            'view_page' => $view,
        ]);

    }

    /**
     * @param string $type
     * @param string $ref_id
     * @param string $column
     * @return RedirectResponse|BinaryFileResponse
     */
    public function getFile(string $type, string $ref_id, string $column)
    {
        // type = merchant_document, profile_photo etc, $ref_id = document_id, $column = from which column data
        $resource = (new SecureDocument())->handleSecureDocument($type, $ref_id, $column);
        if (File::isFileExists($resource)) {
            return response()->file($resource);
        }
        flash(__("File not found"), 'danger');
        return redirect()->route('home');
    }

    public function getLink($encrypted_link)
    {
        $decrypted_link = DataCipher::customEncryptionDecryption($encrypted_link, config('app.brand_secret_key'), 'decrypt', 1);

        if (File::isFileExists($decrypted_link)) {
            return response()->file($decrypted_link);
        }

        if (\common\integration\Utility\Url::isValid($decrypted_link)) {
            return redirect()->away($decrypted_link);
        }

        abort(404, 'file not found');
    }
}
