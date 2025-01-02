<?php

namespace App;

use App\Http\Controllers\Traits\CommonLogTrait;
use App\Http\Controllers\Traits\SendEmailTrait;
use App\Http\Controllers\Traits\NotificationTrait;
use App\Http\Controllers\Traits\OTPTrait;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Models\Aml;
use App\Models\Deposit;
use App\Models\Merchant;
use App\Models\MerchantApplication;
use App\Models\RandomCustomerId;
use App\Models\Ticket;
use App\Models\Transaction;
use App\Models\Usergroup;
use App\Models\UserUsergroup;
use App\Models\UserSetting;
use App\Models\Withdrawal;
use App\Utils\Date as UtilsDate;
use Carbon\Carbon;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\CommonNotification;
use common\integration\GlobalFunction;
use common\integration\ManipulateDate;
use common\integration\Models\OutGoingEmail;
use common\integration\Traits\ModelActivityTrait;
use common\integration\Utility\Hashing;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Jenssegers\Date\Date;
use Illuminate\Foundation\Auth\User as Authenticatable;

use App\Models\Wallet;
use App\Models\Currency;
use App\Models\TicketConversation;
use App\Models\TicketConversations;
use App\Models\UsergroupRole;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use function JmesPath\search;
use App\Models\BlockTimeSettings;
use App\Models\Profile;
use App\Models\UserProfile;
use common\integration\BrandConfiguration;
use App\Models\UserActionHistory;
use common\integration\ManageLogging;
use App\Models\AccountManager;
use common\integration\GlobalUser;
use common\integration\Utility\Exception;
use common\integration\Utility\SqlBuilder;
use common\integration\Models\Profile as CommonProfile;
use \common\integration\Utility\Str as UtilityStr;

class User extends Authenticatable
{
    
    use Notifiable, SendEmailTrait, CommonLogTrait, OTPTrait, NotificationTrait, FileUploadTrait, ModelActivityTrait;
    const MOVE_TO_TRASH = "move_to_trash";

    const CUSTOMER = 0;
    const ADMIN = 1;
    const MERCHANT = 2;
    const SUB_MERCHANT = 3;
    const INTEGRATOR = 4;
    const SALES_ADMIN = 5;
    const SALES_EXPERT = 6;

    const TYPE_ADMIN=1;

    const TYPE_DOCTOR=2;

    const TYPE_OPERATOR=3;

    const TYPE_RECEPTOR=4;
    const TYPE_PATIENT=5;

    const USER_TYPES = [
        self::CUSTOMER => 'Customer',
        self::ADMIN => 'Admin',
        self::MERCHANT => 'Merchant',
        self::SUB_MERCHANT => 'Sub Merchant',
        self::INTEGRATOR => 'Integrator',
        self::SALES_ADMIN => 'Sales Admin',
        self::SALES_EXPERT => 'Sales Expert'
    ];


    const OTP_OPTION = [
        '0' => 'OTP Not required',
        '1' => 'OTP required'
    ];

    const SESSION_ACTIVE_DURATION = 45;

    // const USER_TYPES = [
    //     1 => 'Unknown',
    //     2 => 'Unverified',
    //     3 => 'Verified',
    //     4 => 'Contracted'
    // ];
    const MOVE_TO_TRASH = "move_to_trash";
    const PENDING = 0;
    const ADMIN_VERIFIED = 1;
    const APPROVED = 1;
    const NOT_APPROVED = 2;

    const NOT_VERIFIED = 1; // new name is Unknown
    const VERIFIED = 2; //new name is Unverified
    const VERIFIED_PLUS = 3; //new name is Verified
    const CONTRACTED = 4;
    const CONTRACTOR = 'contractor';

    const USER_CATEGORIES = [
        self::NOT_VERIFIED => 'Unknown',
        self::VERIFIED => 'Unverified',
        self::VERIFIED_PLUS => 'Verified',
        self::CONTRACTED => 'Contracted'
    ];

    const TURKISH_PHONE_LENGTH = 13;
    const PHONE_LENGTH = 15;

    const USER_PASSWORD_UPDATE_ACTION = 1;
    const USER_UPDATE_ACTION = 2;

    const MALE = 1;
    const FEMALE = 2;

    const OTP_CHANNEL_ALL = 0;
    const OTP_CHANNEL_SMS = 1;
    const OTP_CHANNEL_EMAIL = 2;

    const ADMIN_INACTIVITY_DISABLE_DURATION = 90;

    const WRONG_CAPTCHA_COUNT = 10;
    const WRONG_CAPTCHA_LOCK_TIME = 10;


    public function handleSearch($input): array
    {
        $filters["page_limit"] = $input["page_limit"] ?? 10;
        $filters["filter_key"] = $input["filter_key"] ?? '';
        $filters["status"] = $input["status"] ?? '';
        // $filters["doctor_id"] = $input["doctor_id"] ?? '';
       
        // $filters["start_date"] = $input["start_date"] ?? '';
        // $filters["end_date"] = $input["end_date"] ?? '';
        return $filters;
    }
    public function getDoctors($filters = [], $paginate = false, $page_limit = 10)
    {
        $query = $this->filters($filters)->where("type",self::TYPE_DOCTOR);
        return $paginate ? $query->paginate($page_limit) : $query->get();
    }
    private function filters($filters = [])
    {
        $query = self::query();

        if (!empty($filters["name"])) {
            $query->where("name", $filters["name"]);
        }

        if (!empty($filters["email"])) {
            $query->where("email", $filters["email"]);
        }
        if (!empty($filters["license_number"])) {
            $query->where("license_number", $filters["license_number"]);
        }
        if (!empty($filters["experience_years"])) {
            $query->where("experience_years", $filters["experience_years"]);
        }
        if (!empty($filters["speciality"])) {
            $query->where("speciality", $filters["speciality"]);
        }
        
        
        return $query;
    }
    public function validateDoctors(): array
    {
        $rules = [
            //"status" => "required|string|in:1,2,3",
            // "doctor_id" => "required|integer|exists:users,id",
            "name" => "required|string|max:180|min:6",
            "email" => "required|email|max:150|min:4",
        ];

        $messages = [
            'status.required' => __("The status is required."),
            // 'doctor_id.required' => __("The doctor is required."),
            // 'patient_id.required' => __("The patient is required."),
            // 'appointment_date.after' => __("The appointment date must be in the future."),
        ];

        return [$rules, $messages];
    }
    public function prepareInsertData($input): array
    {
        return [
            "type" => self::TYPE_DOCTOR,
            "user_type" => self::TYPE_ADMIN,
            "name" => $input["name"],
            "email" => $input["email"],
            "license_number" => $input["license_number"],
            "experience_years" => $input["experience_years"],
            "speciality" => $input["speciality"], // Optional field for additional notes
        ];
    }

    public function saveDoctor($input)
    {
        return self::query()->create($input);
    }

    public function updateDoctor($id, $input)
    {
        return self::query()
            ->where("id", $id)
            ->update($input);
    }
    const FILLABLE_COLUMNS = [
        'account_status',
        'activated_at',
        'address',
        'answer_one',
        'avatar',
        'balance',
        'city',
        'company_id',
        'contracted_file',
        'country',
        'created_by_id',
        'created_by_name',
        'currency_id',
        'customer_number',
        'dob',
        'email',
        'failed_login_attempt',
        'fb_id',
        'first_name',
        'first_time_login',
        'gender',
        'img_path',
        'ip',
        'is_admin_verified',
        'is_email_verifyed',
        'is_merchant_restricted_by_account_manager',
        'is_online',
        'is_otp_required',
        'is_self_deactivated',
        'is_send_activation_request',
        'is_ticket_admin',
        'json_data',
        'language',
        'last_activity_datetime',
        'last_failed_login_datetime',
        'last_name',
        'last_seen',
        'login_at',
        'merchant',
        'merchant_parent_user_id',
        'name',
        'nationality',
        'otp_channel',
        'password',
        'permission_version',
        'phone',
        'question_one',
        'remember_token',
        'remote_customer_number',
        'reset_password_token',
        'sector_id',
        'security_image_id',
        'self_deactivation_date',
        'session_id',
        'settings',
        'social',
        'status',
        'tc_number',
        'ticketit_admin',
        'ticketit_agent',
        'token_email',
        'token_email_datetime',
        'token_phone',
        'token_phone_datetime',
        'unique_invitation_link_key',
        'updated_password_at',
        'user_category',
        'user_privilege_type',
        'user_type',
        'username',
        'verification_token',
        'verified',
        'wrong_secret_answer_attempt',
        'user_group_ids'
    ];
    const ADMIN_USER_PERMISSION_VERSION_CACHE_KEY = "admin_user_permission_version_cache_key";
    const MONTENEGRO_PHONE_COUNTRY_CODE = '382';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /*
     * user column activated_at data will insert at creating
     */

    protected static function boot()
    {
        parent::boot();
        parent::bootModelEventLogger();

        static::creating(function ($user) {
            $activated_at = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', \Carbon\Carbon::now());
            // $user->activated_at = $activated_at;
            if (empty($user->password) && BrandConfiguration::call([Mix::class,'enableUserPasswordRequired'])){
                $user->password = Hashing::bcrypt(\common\integration\Models\User::USER_DEFAULT_PASSWORD);
            }
        });
    }
    //new added
    public function handleSearch($input): array
    {
        $filters["page_limit"] = $input["page_limit"] ?? 10;
        $filters["filter_key"] = $input["filter_key"] ?? '';
        $filters["status"] = $input["status"] ?? '';
    
        $filters["start_date"] = $input["start_date"] ?? '';
        $filters["end_date"] = $input["end_date"] ?? '';
        return $filters;
    }
    public function getPatients($filters = [], $paginate = false, $page_limit = 10)
    {
        $query = $this->filters($filters)->where('type',5);
        return $paginate ? $query->paginate($page_limit) : $query->get();
    }
    private function filters($filters = [])
    {
        $query = self::query();

      
     

        if (!empty($filters["start_date"]) && !empty($filters["end_date"])) {
            $query->whereBetween('appointment_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters["filter_key"])) {
            $like_operator = "like";
            $query->where(function ($q) use ($filters, $like_operator) {
                $q->where('doctor_id', $like_operator, '%' . $filters["filter_key"] . '%')
                    ->orWhere('patient_id', $like_operator, '%' . $filters["filter_key"] . '%')
                    ->orWhere('status', $like_operator, '%' . $filters["filter_key"] . '%');
            });
        }

        return $query;
    }
    // public function validateData(): array
    // {
    //     $rules = [
    //         //"status" => "required|string|in:1,2,3",
    //         // "doctor_id" => "required|integer|exists:users,id",
    //         // "patient_id" => "required|integer|exists:patients,id",
    //         // "appointment_date" => "required|date|after:today",
    //     ];

    //     $messages = [
    //         //'status.required' => __("The status is required."),
    //         // 'doctor_id.required' => __("The doctor is required."),
    //         // 'patient_id.required' => __("The patient is required."),
    //         // 'appointment_date.after' => __("The appointment date must be in the future."),
    //     ];

    //     return [$rules, $messages];
    // }

    public function validateData(): array
    {
        $rules = [
            "name" => "required|string|max:191",
            "email" => "required|email|max:191|unique:users,email",
            "phone" => "required|string|max:50",
            "address" => "required|string|max:255",
        ];
    
        $messages = [
            'name.required' => __("The name is required."),
        'name.max' => __("The name must not exceed 191 characters."),
        'email.required' => __("The email is required."),
        'email.email' => __("The email must be a valid email address."),
        'email.unique' => __("The email has already been taken."),
            'phone.required' => __("The phone number is required."),
            'phone.digits_between' => __("The phone number must not exceed 50 characters."),
            'address.required' => __("The address is required."),
            'address.max' => __("The address must not exceed 255 characters."),
        ];
    
        return [$rules, $messages];
    }
    
    public function preparePatientData($input): array
    {
        return [
            "type" => 5,

            "name" => $input["name"],
            "email" => $input["email"],
            "phone" => $input["phone"],
            "address" => $input["address"],
           // "notes" => $input["notes"] ?? '', // Optional field for additional notes
        ];
    }
    public function saveData($input)
    {
        return self::query()->create($input);
    }
    public function updateData($id, $input)
    {
        return self::query()
            ->where("id", $id)
            ->update($input);
    }
    // public function deleteData($ids)
    // {
    //     $query = self::query();
    //     if (Arr::isOfType($ids)) {
    //         $query->whereIn("id", $ids);
    //     } else {
    //         $query->where("id", $ids);
    //     }
    //     return $query->delete();
    // }

    public function deleteData($ids)
    {
        return self::whereIn("id", (array)$ids)->delete();
    }
    


    /**
     * Get all users from database.
     *
     * @return mixed
     */
    public function getAllUniqueUsers()
    {
        return $this->select('id', 'name')
            ->where('user_type', '=', self::CUSTOMER)
            ->orWhereRaw(
                'user_type = ' . self::MERCHANT .
                ' AND users.merchant_parent_user_id = users.id'
            )->get();
    }

    public function findById($id)
    {
        return $this->find($id);
    }

    public function findByCustomerNumber($customer_number){
        return $this->where('customer_number', $customer_number)->first();
    }

    public function add_user($data)
    {
        $password = bcrypt((!empty($data["password"])) ? $data["password"] : '');
        if (!empty($data['source']) && $data['source'] == MerchantApplication::SOURCE_ON_BOARDING) {
            $password = $data['password'] ?? '';
        }

        $this->first_name = (!empty($data["first_name"])) ? $data["first_name"] : '';
        $this->last_name = (!empty($data["last_name"])) ? $data["last_name"] : '';
        $this->name = (!empty($data["cname"])) ? $data["cname"] : $this->first_name . ' ' . $this->last_name;
        $this->username = (!empty($data["username"])) ? $data["username"] : '';
        $this->email = (!empty($data["email"])) ? $data["email"] : '';
        $this->phone = (!empty($data["user_phone"])) ? $data["user_phone"] : '';
        $this->password = $password;
        $this->img_path = (!empty($data["profile"])) ? $data["profile"] : '';
        $this->language = (!empty($data["language"])) ? $data["language"] : 'tr';
        $this->company_id = (!empty($data["company_id"])) ? $data["company_id"] : 0;
        $this->fb_id = (!empty($data["fb_id"])) ? $data["fb_id"] : '';
        $this->permission_version = (!empty($data["permission_version"])) ? $data["permission_version"] : 0;
        $this->status = (isset($search['status'])  && ($search['status'] >= 0)) ? 1 : 0;
        $this->is_admin_verified = (!empty($data["is_admin_verified"])) ? $data["is_admin_verified"] : 0;
        $this->user_type = (!empty($data["user_type"])) ? $data["user_type"] : 1;
        $this->customer_number = (isset($data["customer_number"]) && !empty($data["customer_number"])) ? $data["customer_number"] : '';
        $this->created_by_id = (isset($data["created_by_id"]) && !empty($data["created_by_id"])) ? $data["created_by_id"] : 0;
        $this->created_by_name = (isset($data["created_by_name"]) && !empty($data["created_by_name"])) ? $data["created_by_name"] : '';
        $this->dob = !empty($data["dob"]) ? $data["dob"] : null;
        $this->save();
        return $this;

    }

    public function userGroup()
    {
        return $this->belongsToMany('App\Models\Usergroup', 'user_usergroups', 'user_id', 'usergroup_id');
    }

    public function scopeSearch($query, $s)
    {
        return $query->where('email', 'like', '%' . $s . '%')
            ->orWhere('name', 'like', '%' . $s . '%');
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }

    public function merchants()
    {
        return $this->hasOne(Merchant::class);
    }

    public static function avatar($id){
        return self::find($id)->avatar;
    }

    public function conversations(){
        return $this->hasMany(TicketConversations::class);
    }

    public function displayGlobalDate($date, $lang = "")
    {

        $result = $date;
        if (empty($lang)) {
            $lang = app()->getLocale();
        }
        //dd($lang);
        if ($lang == "tr") {
            //setlocale(LC_TIME, 'tr_TR');
            $result = Date::parse($date)->format('j F Y');
            //dd($result);
        } else {
            $result = date('d M Y', strtotime($date));
        }
        //dd($result);
        return $result;
    }

    public function displayUsername($id)
    {

        if ($this->find($id)) {
            $user = $this->find($id)->firstOrFail();
            $name = $user->name;
        } else {
            $name = '';
        }
        return $name;
    }

    // public function currentCurrency()
    // {
    //     if (!is_null($this->currentWallet())) {
    //         return $this->currentWallet()->currency;
    //     }
    //     $currency = Currency::first();
    //     $wallet = $this->newWallet($currency->id);
    //     $this->currency_id = $currency->id;
    //     $this->save();
    //     return $currency;
    // }

    public function walletsCollection()
    {
        return $this->hasMany(\App\Models\Wallet::class);
    }

    public function currentWallet()
    {
        if (Currency::where('id', $this->currency_id)->first() != NULL) {
            return $this->walletsCollection()->with('Currency')->where('currency_id', $this->currency_id)->first();
        }

        $currency = Currency::first();
        $this->currency_id = $currency->id;
        $this->save();

        return Wallet::with('Currency')->where('currency_id', $this->currency_id)->first();
    }

    public function newWallet($currency_id)
    {
        return Wallet::create([
            'user_id' => $this->id,
            'currency_id' => $currency_id,
            'amount' => 0
        ]);
    }

    public function RecentActivity()
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }

    public function walletByCurrencyId($id)
    {
        if (!is_null($this->walletsCollection()->with('Currency')->where('currency_id', $id)->first())) {
            return $this->walletsCollection()->with('Currency')->where('currency_id', $id)->first();
        }
        return $this->newWallet($id);
    }

    public function createLog($data)
    {
/*        $json_data = GlobalFunction::safe_json_encode($data);
        Log::info($json_data);*/
        (new ManageLogging())->createLog($data);
    }

    // For OTP
    public function OTP()
    {
        return Cache::get($this->OTPKey());
    }

    public function OTPKey()
    {
        return "OTP_for_{$this->id}";
    }

    public function cacheTheOTP()
    {
        $OTP = rand(100000, 999999);
        Cache::put([$this->OTPKey() => $OTP], now()->addSeconds(60 * 5));
        return $OTP;
    }

    public static function is_data_exits($id = null)
    {
        $transaction = Transaction::where('user_id', $id)->first();
        $merchant = Merchant::where('user_id', $id)->first();
        $deposit = Deposit::where('user_id', $id)->first();
        $aml = Aml::where('user_id', $id)->first();
        $withdrawal = Withdrawal::where('user_id', $id)->first();
        $ticket = Ticket::where('user_id', $id)->first();
        $wallet = Wallet::where('user_id', $id)->first();
        if ($transaction == null && $merchant == null && $deposit == null && $aml == null && $withdrawal == null && $ticket == null && $wallet == null) {
            return false;
        } else {
            return true;
        }
    }

    public function sendPasswordResetNotification($token)
    {
        $message['token'] = $token;
        $message['name'] = $this->name;
        $message['reset_url'] =  config('app.url').'/password/reset/'.$token;
        $emailTemplate = "admin_forget_password.admin_forget_password";
        $adminEmail = Config::get('constants.defines.MAIL_FROM_ADDRESS');

        //out_going_email - UNUSED
        $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
        $this->sendEmail($message, "forget_password", $adminEmail, $this->email,
            "", $emailTemplate, $this->language, 1);

        $msg = __("Reset password link has been sent to var");
        $msg = str_replace("var", $this->email, $msg);
        Session::flash('rp-msg', $msg);
        Session::flash('rp-typ', __('sa'));
    }

    public function getUserById($id)
    {
        return User::with('userGroup', 'userGroup.role')->find($id);
    }

    public function getUserDetailsById($user_id){
        $query = $this->where('id',$user_id)->first();
        return $query;
    }

    public function country()
    {
        return $this->hasOne(\App\Models\Country::class, 'id', 'country');
    }

    public function hasPermissionOnAction($routeName)
    {
        $permittedRouteNames = Session::get('permittedRouteNames');
        //some constant value is camelcase but all permission is lower case
        //that is why I convert routename to lower case
        if (in_array(strtolower(trim($routeName)), $permittedRouteNames)) {
            return true;
        }
        return false;
    }

    public function masked_name($user)
    {
        $str = "";
        $str .= substr($user->first_name,0,0) . "***" . substr($user->last_name,1,1) . "***";
        return $str;
    }

    public function isGlobalAdmin($user_id){
        $userGroups = UserUsergroup::where('user_id',$user_id)->get();
        foreach ($userGroups as $group){
            if($group->usergroup_id == \config('constants.defines.GA_USERGROUP_ID')){
                return true;
            }
        }
        return false;
    }

    public function isFinanceDeptUser($user_id){
        $userGroups = UserUsergroup::where('user_id',$user_id)->get();
        foreach ($userGroups as $group){
            if($group->usergroup_id == \config('constants.defines.FDU_USERGROUP_ID')){
                return true;
            }
        }
        return false;
    }

    // For Adding Merchant User
    public function saveUser($data)
    {
        $status = 100;
        $merchantObj = $data['merchantObj'];
        list($status, $errors) = $this->validateUser($data);
        if ($status != 100) {
            return [$status, $errors];
        }

        try {

            //DB::beginTransaction();

            // get random customer number
            $rancustid = new RandomCustomerId();
            if (BrandConfiguration::isAllowSequentialCustomerId()) {
                $customer_number = $rancustid->getAndUpdateSequentialCustomerId();
            } else {
                $customer_number = $rancustid->getRandomCustomerId($data['user_type']);
            }

            $this->first_name = (!empty($data["first_name"])) ? $data["first_name"] : '';
            $this->last_name = (!empty($data["last_name"])) ? $data["last_name"] : '';
            $this->name = (!empty($data["cname"])) ? $data["cname"] : $this->first_name . ' ' . $this->last_name;
            $this->username = (!empty($data["username"])) ? $data["username"] : '';
            $this->email = (!empty($data["email"])) ? $data["email"] : '';
            $this->phone = (!empty($data["phone"])) ? $data["phone"] : '';
            $this->language = (!empty($data["language"])) ? $data["language"] : 'en';
            $this->permission_version = (!empty($data["permission_version"])) ? $data["permission_version"] : 0;
            $this->status = (!empty($data["status"])) ? $data["status"] : 1;
            $this->is_admin_verified = (!empty($data["is_admin_verified"])) ? $data["is_admin_verified"] : 0;

            $this->merchant_parent_user_id = (!empty($data["merchant_parent_user_id"])) ? $data["merchant_parent_user_id"] : 0;
            //$this->save();
            if ($data['isEdit'] ==  false) {
                $this->customer_number = $customer_number;
                $this->user_type = $data["user_type"];
                $this->company_id = (!empty($data["company_id"])) ? $data["company_id"] : 0;
                $length = 8;
                $random_str = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);

                // temporary change 12.05.2022 by amanot
                $this->password = bcrypt($this->user_type == Profile::INTEGRATOR ? '123456' : $random_str);
            }

            $this->user_privilege_type = $data['is_show_all_users_record'] ?? 0;
            $this->otp_channel = $data['otp_channel'] ?? (new GlobalUser())->getDefaultOtpChannel();
            $this->save();
//dd($this, $data);
            // delete used customer number
            if (!BrandConfiguration::isAllowSequentialCustomerId()) {
                $rancustid->deleteRandomCustomerId($customer_number);
            }

            if (!empty($data['usergroup_ids'])) {
                UserUsergroup::where('user_id', $this->id)->delete();
                foreach ($data['usergroup_ids'] as $usergroup_id) {
                    UserUsergroup::create([
                        'usergroup_id' => $usergroup_id,
                        'user_id' => $this->id,
                        'company_id' => $this->company_id
                    ]);
                }
            }

            //DB::commit();

        } catch (\Throwable $exception) {
            DB::rollBack();

            $logData['action'] = "Adding New Merchant User Failed";
            $logData['rollback_status'] = true;
            $logData['message'] = $exception->getMessage();
            $usr = new User();
            $usr->createLog($this->_getCommonLogData($logData));

            $status = 2;
        }

        // temporary change 12.05.2022 by amanot
        if ($data['isEdit'] ==  false && GlobalUser::isSendEmailFromNewUser($this)) {

            $encrypt_email_data = $this->customEncryptionDecryption($this->email, config('app.brand_secret_key'), 'encrypt' ,true);
            $email_data['merchent_panel_link'] = config('app.app_merchant_url');
            $email_data['create_password_link'] = config('app.app_merchant_url')."/password/create/".$encrypt_email_data;
            $email_data['name'] = $this->name;
            $email_data['email'] = $this->email;
            $notification_data['updated_at'] = $this->updated_at;

            $input_data['email_data'] = $email_data;
            $input_data['notification_data'] = $notification_data;

            $this->sendEmailAndNotification($input_data, $this);

            $parentUserObj= $this->findUserById($this->merchant_parent_user_id);
            $data = [
                'updated_at' => $this->created_at,
                'merchant_id' => $merchantObj->id,
                'notification_action' => CommonNotification::USER_CREATED
            ];

            $this->sendNotification($parentUserObj, $data, "usercreate.usercreate",$parentUserObj->language);
        }

        return [$status, $this];
    }

    public function validateUser(Array $data)
    {
        $status = 100;
        $errors = [];
        $customMessages = [];
        $user_type = $data['user_type'] ?? User::MERCHANT;

        $phone_min_length = self::getCustomMinValidationLengthOfUserPhone($data['phone'] ?? '');
        if(isset($data['is_integrator_insert']) && !empty($data['is_integrator_insert'])){
            $phone_min_length = 0;
        }

        if ($data['isEdit']) {

            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique_email:'.$user_type.','.$data['id'],
                'phone' => 'required|min:'.$phone_min_length.'|unique_phone:'.$user_type.','.$data['id'].'|max:50|regex:/^[+]\d+$/',
                'is_admin_verified' => 'required',
//                'role_id' => 'required|exists:roles,id'
            ];

        } else {
            $rules = [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique_email:'.$user_type.'|string|max:255',
                'phone' => 'required|min:'.$phone_min_length.'|unique_phone:'.$user_type.'|max:50|regex:/^[+]\d+$/',
                'is_admin_verified' => 'required',
//                'role_id' => 'required|exists:roles,id'
            ];

        }

        if(BrandConfiguration::isValidatePhoneNumberLength() && !isset($data['is_integrator_insert'])){
            unset($rules['phone']);
            $phone_min_length = 0;
            $unique_phone = isset($data['id']) ? $user_type.','.$data['id'] : $user_type;
            $rules = $rules + [
                    'phone' => 'required|min:'.$phone_min_length.'|unique_phone:'.$unique_phone.'|max:50|regex:/^[+]\d+$/',
                    'phone_' => 'required|digits_between:' . Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE .',' . Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE
                ];

            $customMessages = [
                'phone_.digits_between' => __('User phone number should not be more or less than :min_max_length digit after country code.', ['min_max_length' => Merchant::PHONE_MIN_MAX_LENGTH_AFTER_COUNTRY_CODE])
            ];

        }

        $validation =  Validator::make($data, $rules, $customMessages);

        if($validation->fails()){
            $status = 1;
            $errors = $validation->errors();
        }
        return [$status, $errors];
    }

    public function getByEmail($email , $user_type = null, $question = null)
    {
        $query = self::query();

        if(!empty($user_type)){
            $query = $query->where('user_type', $user_type);
        }
        if(!empty($question)){
            $query = $query->where('question_one', $question);
        }
        $query =  $query->where('email', $email)->first();

        return $query;
    }

    public function findUserById($id){
        return $this->where('id', $id)->first();
    }

    public function getUserByPhoneAndType($phone, $user_type){
        return $this->where('phone', $phone)->where('user_type',$user_type)->first();
    }

    public function updateUser($id, $data, $action = "EDIT",$extra_param=null)
    {

        try{

            //DB::beginTransaction();

            $userObj = $this->findUserById($id);

            if (isset($data['img_path']) && isset($data['temp_path']) && isset($data['perm_path'])) {
                // Remove previous image
                if(!empty($userObj->img_path)){
                    $this->deleteFile($userObj->img_path);
                }

                // Get permanent path
                $permanent_path = $data['perm_path'].explode($data['temp_path'], $data['img_path'])[1];

                // Move temporary picture to permanent picture location
                $this->moveFile($data['img_path'], $permanent_path);

                // Update temporary image to permanent image
                $data['img_path'] = $permanent_path;

            } else {
                unset($data['img_path']);
            }

            $userObj->fillable = self::FILLABLE_COLUMNS;
            $userObj->update($data);

            \DB::commit();

            return $userObj;

        }catch(\Throwable $e){
            (new ManageLogging())->createLog(['action' => 'UPDATE_USER_EXCEPTION', 'exception' => Exception::fullMessage($e), 'rollback' => true]);
            \DB::rollBack();

        }


    }


    public function sendEmailAndNotification($input_data, $user_obj)
    {
        if (BrandConfiguration::welcomeMailNewContentForMerchantV1()) {
            $merchantUserCreate = 'merchant_user_create_v1';
        }
        else{
            $merchantUserCreate = 'merchant_user_create';
        }
        $this->checkAndSendNotification(
            UserSetting::action_lists[$merchantUserCreate],
            $input_data,
            UserSetting::EMAILENABLED,
            UserSetting::SMS_DISABLED,
            UserSetting::PUSH_NOTIFICATION_ENABLED,
            $user_obj
        );
    }

    public function getAllMerchantUsers($parent_user_id, $selected_col = null)
    {
        $query = User::query();
        if (!empty($selected_col)) {
            $query->select($selected_col);
        }
        return $query->where('merchant_parent_user_id', $parent_user_id)->get();
    }


    public function searchAdminByName($data){
        $result = [];
        if(!empty($data)){
            $result = $this->select('id','name AS text')
                ->where('user_type', self::ADMIN)
                ->where('name', SqlBuilder::likeOperator(), '%' . $data . '%')
                ->get();
        }else{
            $result = $this->select('id','name AS text')
                ->where('user_type', self::ADMIN)
                ->where('name','!=', '')
                ->limit(10)
                ->get();
        }

        return $result;
    }

    public function getAllAdminAssocById($admin_id){
        if(!empty($admin_id)) {
            $query = $this->where('user_type', self::ADMIN)
                ->where('id', $admin_id)
                ->pluck("name","id");

            return $query;
        }

        return [];
    }

    public function getUsersByUserCategoryAndType($category, $type){
        return $this->where("user_category", $category)->where('user_type', $type)->get();
    }

    public function getUsersById($ids){
        return $this->whereIn('id', $ids)->get();
    }



    public function getActiveAdminUsers()
    {
        return $this->where('is_admin_verified',\App\Models\Profile::ADMIN_VERIFIED_APPROVED)->where('user_type',self::ADMIN)->get();
    }
    public function get_full_name(){
        return $this->first_name.' '.$this->last_name;
    }

    public static function checkUsersInSameCompany($users_id, $company_id)
    {
        return (bool)self::whereIn('id', $users_id)
        ->where('company_id', '!=',  $company_id)
        ->count();
    }

    /**
     * @param int/array $user_id -- Single User Id Or Array Of User Ids
     * @param string $users_type -- Single Or Array Of User Type
     * @param User $user -- Auth user object
     * @return array
     */
    public static function deleteUsers($users_id, $users_type, User $user): array
    {

        list($statusCode, $statusMessage) = self::checkUsersDeleteValidation($users_id, $users_type, $user);

        if (empty($statusCode) && empty($statusMessage)) {
            $users = self::whereIn('id', $users_id)
                ->get();

            foreach ($users as $user) {
                if ($user->img_path)  $user->deleteFile($user->img_path);
            }

            UserUsergroup::whereIn('user_id', $users_id)
                ->delete();

            self::whereIn('id', $users_id)
                ->delete();


            $statusCode = 100;
            $statusMessage = 'The record has been deleted successfully!';
        }

        return [
            $statusCode, $statusMessage
        ];
    }

    public static function userHasDependantData($user_id)
    {
        if (!is_array($user_id)) {
            $user_id = [
                $user_id
            ];
        }

        $exists = false;

        if (!$exists && Transaction::whereIn('user_id', $user_id)->first()) {
            $exists = true;
        }
        if (!$exists && Merchant::whereIn('user_id', $user_id)->first()) {
            $exists = true;
        }
        if (!$exists && Deposit::whereIn('user_id', $user_id)->first()) {
            $exists = true;
        }
        if (!$exists && Aml::whereIn('user_id', $user_id)->first()) {
            $exists = true;
        }
        if (!$exists && Withdrawal::whereIn('user_id', $user_id)->first()) {
            $exists = true;
        }
        if (!$exists && Ticket::whereIn('user_id', $user_id)->first()) {
            $exists = true;
        }
        if (!$exists && Wallet::whereIn('user_id', $user_id)->first()) {
            $exists = true;
        }
        return $exists;
    }

    public function getBlockedUser($search, $page_limit,  $orderBy = 'DESC'){
        $likeKey = SqlBuilder::likeOperator();

        $query = $this->where('is_admin_verified', \App\Models\Profile::LOCK_USER);


        if (isset($search['from_date']) && isset($search['to_date']) && !empty($search['from_date']) && !empty($search['to_date'])) {
            $search['from_date'] = ManipulateDate::startOfTheDay($search['from_date']);
            $search['to_date'] = ManipulateDate::endOfTheDay($search['to_date']);
            $query = $query->whereBetween('last_failed_login_datetime',
                [
                    // UtilsDate::format(9, $search['from_date']),
                    // UtilsDate::format(9, $search['to_date'])
                    $search['from_date'],
                    $search['to_date']
                ]);

        }

        if (!empty($search) && isset($search['name'])) {
            $query = $query->where(function ($q) use ($search, $likeKey) {
                $q->orWhere('first_name',  $likeKey, '%' . $search['name'] . '%')
                ->orWhere('last_name',  $likeKey, '%' . $search['name']. '%')
                ->orWhere('email',  $likeKey, '%' . $search['name'] . '%')
                ->orWhere('phone',  $likeKey, '%' . $search['name']. '%')
                ->orWhere('ip',  $likeKey, '%' . $search['name'] . '%')
                ->orwhere('name',  $likeKey, '%' . $search['name'] . '%');
            });
        }


        if($orderBy){
            $query = $query->orderBy('id',$orderBy);
        }

        if ($page_limit > 0) {
            $query = $query->paginate($page_limit);
        } else {
            $query = $query->get();
        }

        return $query;
    }
    public function userBlockStatusUpdate($user_id ,$is_admin_verified = \App\Models\Profile::ADMIN_VERIFIED_APPROVED){
        $status = 0;
        $model = $this->findorFail($user_id);

        if($model->update([ 'is_admin_verified' => $is_admin_verified , 'failed_login_attempt' => 0])){
            $status = config('constants.SUCCESS_CODE');
        }
        return [$status,$model];
    }

    public function useGroups()
    {
        return $this->belongsToMany('\App\Models\Usergroup', 'user_usergroups', 'user_id','usergroup_id');
    }

    public function userActionHistoriesForAccessControl()
    {
        return $this->hasMany(UserActionHistory::class, 'user_id', 'id')->orderBy('id','DESC');
    }

    public function getCompanyUserWithRoles($search, $page_limit = 10, $company_id = null){
        // User::where('company_id', Auth::user()->company_id)
        //     ->where(function ($query) use ($s){
        //         $query->where('name','like', '%'.$s.'%')
        //             ->orWhere('email','like', '%'.$s.'%');
        //     })->orderBy('updated_at','DESC')->paginate($page_limit);

        $query = $this;

        if(BrandConfiguration::allowAccessControlExport()){
            $query = $query->with('useGroups.role');
        }

        if(BrandConfiguration::allowAccessControllUpdateAt()){
            $query = $query->with('userActionHistoriesForAccessControl:id,type,user_id,created_at');
        }

        $query = $query->where('users.company_id', $company_id);

        if(!empty($search)){
            $query = $query->where(function($q) use ($search){
                    $q->where('users.name','like', '%'.$search.'%')
                    ->orWhere('users.email','like', '%'.$search.'%');
            });
        }

        $query = $query->orderBy('users.updated_at','DESC');

        if(!empty($page_limit)){
            return $query->paginate($page_limit);
        }else{
            return $query->get();
        }

    }

    public function getActiveSessionUserList($page_limit){
        $current_datetime = date('Y-m-d H:i:s');

        $query = self::query();
        $query->whereNotNull('last_activity_datetime')->whereNotNull('session_id')->whereRaw(SqlBuilder::timestampDiffBuilder('MINUTE', 'login_at', 'last_activity_datetime', '>=', self::SESSION_ACTIVE_DURATION));
        $query->orderBy('users.last_activity_datetime','DESC');

        return $query->paginate($page_limit);
    }

    public static function checkUsersDeleteValidation($users_id, $users_type, $user)
    {
        $statusCode = "";
        $statusMessage = "";

        if (!is_array($users_id)) {
            $users_id = [
                $users_id
            ];
        }

        $status = User::checkUsersInSameCompany($users_id, $user->company_id);
        if ($status) {
            $statusCode = 1;
            $statusMessage = "You are not allowed to delete.";
        }

        if (empty($statusCode) && empty($statusMessage) && !in_array($user->user_type, [
            Profile::SALES_ADMIN,
            Profile::SALES_EXPERT,
        ])) {
            if (in_array($user->id, $users_id)) {
                $statusCode = 3;
                $statusMessage = "You can't delete yourself";
            }
        }

        if ($users_type != self::ADMIN && empty($statusCode) && empty($statusMessage)) {
            if (self::userHasDependantData($users_id)) {
                $statusCode = 3;
                $statusMessage = 'User has dependant data. Can not be deleted.';
            }
        }
        return [
            $statusCode, $statusMessage
        ];
    }

    public function getUserBySearch($search, $request_data = []){

        $query = $this->query();

        if(isset($request_data['get_only_wallet_type_users']) && !empty($request_data['get_only_wallet_type_users']) == 1){
            $query->whereNotIn('user_type', GlobalUser::getDisabledWalletUserType());
        }else{
            $query->where('user_type' , '<>' , User::ADMIN);
        }

        return $query->where(function ($query) use ($search) {
            return $this->scopeSearch($query, $search);
        })->select('name', 'id', 'email', 'user_type')
            ->get();

    }

    public function processUserSessionRevoke($user){

        $log_data['action'] = 'PROCESS_USER_SESSION_REVOKE';

        $url = '';
        if($user->user_type == User::MERCHANT){
            $url = config('app.app_merchant_url') . '/api/user-session-destroy';
        }else if($user->user_type == User::CUSTOMER ){
            $url = config('app.app_frontend_url') . '/api/user-session-destroy';
        }

        $log_data['url'] = $url;
        $log_data['user_data'] = $user;

        $basic_auth = base64_encode(config('constants.BASIC_AUTH_USER') . ":" . config('constants.BASIC_AUTH_PSWD'));
        $header = ['Accept: application/json', 'Content-Type: application/json','Authorization: Basic ' . $basic_auth];

        $data['user_id'] = $user->id;

        $ch = curl_init($url);
        if ($this->isNonSecureConnection()){
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,'POST');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $response = curl_exec($ch);
        $error_msg = 'NA';
        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
        }
        curl_close($ch);

        $response = \json_decode($response, true);

        $log_data['curl_response'] = $response;
        $log_data['curl_error'] = $error_msg;
        (new ManageLogging())->createLog($log_data);

        return;
    }

    public function disableInactiveAdmins()
    {
        return self::select(['id', 'is_admin_verified', 'user_type', 'last_activity_datetime', 'created_at'])
            ->where('user_type', self::ADMIN)
            ->where('is_admin_verified',  Profile::ADMIN_VERIFIED_APPROVED)
            ->where(function ($q) {
                $q->where(function ($s) {
                    $s->whereNull('last_activity_datetime')->where('created_at', '<', Carbon::now()->subDays(self::ADMIN_INACTIVITY_DISABLE_DURATION));
                })->orWhere('last_activity_datetime', '<', Carbon::now()->subDays(self::ADMIN_INACTIVITY_DISABLE_DURATION));
            })->update(['is_admin_verified' => Profile::ADMIN_VERIFIED_NOT_APPROVED]);
    }


    /**
     * @param int $user_type
     * @return mixed
     */
    public function getuserListByUserType(int $user_type): mixed
    {
        return $this->select('id', 'name')->where('user_type', $user_type)->get();
    }
    public function updateUserById($user_id, array $data){
        $query = self::query();
        return $query->where('id',$user_id)->update($data);
    }

    public function findFristAdminUser()
    {
        return self::query()->orderBy('id', 'ASC')->where('user_type', self::ADMIN)->first();
    }

    public function getUserByPhoneAndEmail($user_type, $email, $phone = null , $question = null)
    {
        $query = self::query();
        $query = $query->where('email', $email)->where('user_type', $user_type);

        if(!empty($phone)){
            $query->where('phone', $phone);
        }

        if(!empty($question)){
            $query->where('question_one', $question);
        }

        $result = $query->first();

        return $result;
    }

    public static function getOtpChannelList(){
        return [
            self::OTP_CHANNEL_ALL => __('Both email and sms'),
            self::OTP_CHANNEL_SMS => __('Only SMS'),
            self::OTP_CHANNEL_EMAIL => __('Only Email')
        ];

    }

    public static function isEnabledReverseProcess ($user_type)
    {
        $is_enabled = false;

        if (BrandConfiguration::call([Mix::class, 'isAllowedTransactionReversal'])) {
            if ($user_type == self::MERCHANT
                || ($user_type == self::CUSTOMER
                    && !BrandConfiguration::notAllowWalletService())
            ) {
                $is_enabled = true;
            }
        }

        return $is_enabled;
    }

    public function getUserByCompanyId($company_id){
        return $this->where('company_id', $company_id)->orderBy('name', 'ASC')->get();
    }

    public function processAddUser($user_obj): array
    {
        $data["first_name"] = $user_obj->first_name;
        $data["last_name"] = $user_obj->last_name;
        $data["name"] = $user_obj->first_name.' '.$user_obj->last_name;
        $data["phone"] = $user_obj->phone;
        $data["email"] = $user_obj->email;
        $data["status"] = $user_obj->status;
        $data["is_admin_verified"] = Profile::ADMIN_VERIFIED_APPROVED;
        $data['user_type'] = Profile::INTEGRATOR;
        $data['merchantObj'] = null;
        $data['isEdit'] = false;
        $data['is_integrator_insert'] = true;

        list($status, $user_new_obj) = $this->saveUser($data);

        return [ $status, $user_new_obj ];
    }
    public static function getCustomMinValidationLengthOfUserPhone($phone)
    {
        $min_length = 13;
        if(BrandConfiguration::Call([BackendAdmin::class, 'customMinValidationLengthOfUserPhone']) && !empty($phone)){
            $country_code = UtilityStr::getFirstString($phone, 1, 3);
            if($country_code == self::MONTENEGRO_PHONE_COUNTRY_CODE){
                $min_length = 12;
            }
        }
        return $min_length;
    }
}
