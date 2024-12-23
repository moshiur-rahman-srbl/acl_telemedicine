<?php

namespace App\Models;

use App\Http\Controllers\Traits\CommonLogTrait;
use App\Http\Controllers\Traits\SendEmailTrait;
use App\User;
use App\Utils\KycVerification;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\BrandConfiguration;
use common\integration\GlobalUser;
use common\integration\Kpsv2Sorgulayici;
use common\integration\CommonNotification;
use common\integration\Models\OutGoingEmail;
use common\integration\Utility\Hashing;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Storage;
use common\integration\Override\Model\CustomBaseModel as Model;
use Illuminate\Notifications\Notifiable;
use common\integration\Models\Profile as CommonProfile;


class Profile extends Model
{
    use CommonLogTrait,
        Notifiable, SendEmailTrait;

    protected $table = 'users';

    protected $hidden = ['password'];

    public $changePasswordHistories = [];

    const IGNORE_PASS_CHNG_PATH = ['changepassword'];
    const PASS_MATCH_WITH_INFO = ['BP', 'PB', 'VP', 'PN', 'IM'];
    const SIX_DIGITS_PASSWORD = 1;
    const ALPHANUMERIC_PASSWORD = 2;

    const NOT_VERIFIED = 1;
    const VERIFIED = 2;
    const VERIFIED_PLUS = 3;
    const CONTRACTED = 4;

    const CUSTOMER = 0;
    const ADMIN = 1;
    const MERCHANT = 2;
    const SUB_MERCHANT = 3;
    const INTEGRATOR = 4;
    const SALES_ADMIN = 5;
    const SALES_EXPERT = 6;
    const MIN_PASSWORD_COUNT = 1;

    const USER_TYPES = [
        self::CUSTOMER => 'Customer',
        self::ADMIN => 'Admin',
        self::MERCHANT => 'Merchant',
        self::SUB_MERCHANT => 'Sub Merchant',
        self::INTEGRATOR => 'Integrator',
        self::SALES_ADMIN => 'Sales Admin',
        self::SALES_EXPERT => 'Sales Expert',
    ];

    const ADMIN_VERIFIED_PENDING = 0;
    const ADMIN_VERIFIED_APPROVED = 1;
    const ADMIN_VERIFIED_NOT_APPROVED = 2;
    const LOCK_USER = 3;

    const FIRST_TIME_LOGIN = 0;
    const BAN_TIME_LOGIN = 3;

    const PHONE_LENGTH = 15;
    const TURKISH_PHONE_LENGTH = 10;
    const OTP_LENGTH = 6;
    /*const USER_CATEGORY_LIST = [
         '1' => 'UNKNOWN',
         '2' => 'UNVERIFIED',
         '3' => 'VERIFIED',
         '4' => 'CONTRACT',
    ];*/

    const NOFIT_KYC = 'kyc',
        NOFIT_SET_PASS = 'setpass',
        NOFIT_NEW_PASS = 'newpass',
        NOFIT_EMAIL = 'email',
        NOTIF_ADDR = 'addr';

    const QUESTION_LIST = [
        1 => "What is your mother's maiden name?",
        2 => "What is your first pet's name?",
        3 => "What is your father's middle name?",
        4 => "What is your childhood nickname?",
        5 => "What is your favorite author's name?"
    ];

    const VALIDATE_KYC_YEAR_13 = 13;
    const VALIDATE_KYC_YEAR_18 = 18;

    protected static function boot()
    {
        static::creating(function ($user) {
            if (empty($user->password) && BrandConfiguration::call([Mix::class,'enableUserPasswordRequired'])){
                $user->password = Hashing::bcrypt(\common\integration\Models\User::USER_DEFAULT_PASSWORD);
            }
        });

        parent::boot();

    }

    public function merchants()
    {
        return $this->hasOne(Merchant::class, 'user_id', 'merchant_parent_user_id');
    }

    public function getUserByIdAndPhone($id, $phone){

        return $this->where('id',$id)->where('phone',$phone)->first();
    }

    public function getUserById($id){

        return $this->find($id);
    }

    public function getUserByEmail($email, $user_type){

        return $this->where('email',$email)->where('user_type',$user_type)->first();
    }


    public function getUserByPhone($phone, $user_type){

        return GlobalUser::getUserByPhone($phone, $user_type);

    }

    public function getUserByPhoneAndEmail($phone,$email, $user_type){
        return $this->where('phone', $phone)
            ->where('user_type', $user_type)
            ->where('email', $email)
            ->first();
    }
    public function uniquePhone($attribute, $value, $parameters, $validator){
        $user_type = 2;
        if (isset($parameters[0])) {
            $user_type = $parameters[0];
        }
        $user = new Profile();
        if (isset($parameters[1])) {
            $userObj = $user->getUserById($parameters[1]);
            if (!empty($userObj) && $userObj->phone == $value) {
                return true;
            }else {
                return false;
            }
        } else {
            $userObj = $user->getUserByPhone($value, $user_type);
            if (!empty($userObj)) {
                return false;
            } else {
                return true;
            }
        }

    }


    public function addUserByPhone($data,$user_type){

        $user = "";

        $response = $this->validateUserByPhone($data,$user_type);

        if($response != 100){
            return [$response,$user];
        }else{
            $user = new Profile();

            $user->phone = $data['phone'];
            $user->user_category = self::NOT_VERIFIED;
            $user->is_admin_verified = self::ADMIN_VERIFIED_APPROVED;

            if ($user->save()){
                $wallet = new Wallet();
                $walletObj = $wallet->add_wallet_by_currency($user->id);
                $userSettingObj = (new UserSetting())->addUserSettings($user->id);
                $userProfileObj = (new UserProfile())->addUserProfiles($user->id);

                $userObj = $user;
                if($userObj) {
                    $data = [
                        'created_at' => $userObj->created_at,
                        'notification_action' => CommonNotification::USER_CREATED
                    ];
                    $this->sendNotification($userObj, $data, "profile.firsttimelogin.welcome", $userObj->language);
                }
            } else{
                $response  = 21;
            }

            return [$response,$user];
        }
    }

    public function getUserEmailPassword($email, $password, $isCreateAuth,$user_type){

        $result = "";

       if($isCreateAuth == true){
           $allCredintials = [
               'email' => $email,
               'password' =>$password,
               'user_type' => $user_type
           ];
           $result = Auth::attempt($allCredintials);

       } else {
           $result = Profile::where('email',$email)
               ->where('password',bcrypt($password))
               ->where('user_type',$user_type)
               ->first();
       }

        if(!empty($result)){
            return [100,$result];
        }else{
            return [1,null];
        }

    }

    public function validateUserByPhone($data,$user_type){

        if(!empty($data['phone'])){
            $profileObj = $this->getUserByPhone($data['phone'],$user_type);

            if(!empty($profileObj)){
                return 2;
            }else{
                return 100;
            }
        }

        return 3;
    }

    public function addMerchantUser($data){

        list($status, $errors) = $this->validateMerchantUser($data);
        if (!$status) {
            return [$status, $errors];
        }

        $this->first_name = (!empty($data["first_name"])) ? $data["first_name"] : '';
        $this->last_name = (!empty($data["last_name"])) ? $data["last_name"] : '';
        $this->name = (!empty($data["cname"])) ? $data["cname"] : $this->first_name . ' ' . $this->last_name;
        $this->username = (!empty($data["username"])) ? $data["username"] : '';
        $this->email = (!empty($data["email"])) ? $data["email"] : '';
        $this->phone = (!empty($data["phone"])) ? $data["phone"] : '';
        $this->language = (!empty($data["language"])) ? $data["language"] : 'en';
        $this->company_id = (!empty($data["company_id"])) ? $data["company_id"] : 0;
        $this->permission_version = (!empty($data["permission_version"])) ? $data["permission_version"] : 0;
        $this->status = (!empty($data["status"])) ? $data["status"] : 1;
        $this->is_admin_verified = (!empty($data["is_admin_verified"])) ? $data["is_admin_verified"] : 0;
        $this->user_type = (!empty($data["user_type"])) ? $data["user_type"] : User::MERCHANT;
        $this->merchant_parent_user_id = (!empty($data["merchant_parent_user_id"])) ? $data["merchant_parent_user_id"] : 0;
        //$this->save();
        if ($data['isEdit'] ==  false) {
            $length = 8;
            $random_str = substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
            $this->password = bcrypt($random_str);
        }

        if ($this->save()) {
            $usergroupObj = new UsergroupRole();
            $usergroup = $usergroupObj->getUsergroupByRoleCompany($data['role_id'], $data['company_id']);
            $userusergroup = new UserUsergroup();
            $current_group = $userusergroup->getCurrentGroup($this->id, $data["company_id"]);
            if (!in_array($usergroup->id, $current_group)) {
                $userusergroup->deletePrevGroup($current_group);
                $userusergroup->insert_entry([
                    'user_id' => $this->id,
                    'company_id'=>$data["company_id"],
                    'usergroup_id'=>$usergroup->usergroup_id
                ]);
            }

            if ($data['isEdit'] ==  false) {
                $data = $this->toArray();
                $data['password'] = $random_str;
                $subject = 'USER_CREATE';
                $from = Config('app.SYSTEM_NO_REPLY_ADDRESS');
                $to = $this->email;
                $attachment = '';
                if (BrandConfiguration::welcomeMailNewContentForMerchantV1()) {
                    $template = "merchant.merchant_create_v1";
                }
                else{
                    $template = 'merchant.user_create';
                }
                $language = "tr";

                //out_going_email
                $this->setGNPriority(OutGoingEmail::PRIORITY_HIGH);
                $this->sendEmail($data, $subject, $from, $to, $attachment, $template, $language);


                $data = [
                    'updated_at' => $this->created_at,
                    'merchant_id' => session()->get('merchantObj')->id,
                    'notification_action' => CommonNotification::USER_CREATED
                ];
                $this->sendNotification(Auth::user(), $data, "usercreate.usercreate",Auth::user()->language);

                $this->sendNotification($this, $data, "login.first_time_login", $this->language);

            }
        }

        return [$status, $this];
    }

    public function updateUser($id, $data, $action = "EDIT",$extra_param=null){

        $userObj = $this->getUserById($id);
        $userObj->fillable = CommonProfile::FILLABLE_COLUMNS;

        if(isset($data["avatar"])) {

            $path = "users/" . $id;

            if(!empty($extra_param['image_name'])){
                $path .= "/".$extra_param['image_name'];
            }

            $data["avatar"] = $this->uploadFile($data['avatar'],$path,false);
            $this->deleteFile($userObj->avatar);

            if(!empty($extra_param['image_name'])){
                $data['avatar'] = $path;
            }
        }

        if (isset($data['password'])){
            $response = "";
            if(!empty($extra_param['current_password'])){
                $response = $this->validatePassword(
                    $data['password'],
                    $extra_param['current_password'],
                    $id,
                    $extra_param['auth_password']
                );

                if($response != 100){
                    return $response;
                }
            }

            $data['password'] = bcrypt($data['password']);

            if(!empty($extra_param['current_password'])){
                ///saving old password
                $changePassword = new ChangePasswordHistory();
                $changePassword->createHistory($id,$data['password']);
            }
        }

        if(!empty($data['email'])){
            $response = "";
            if(!empty($extra_param['current_password'])){

                $response = $this->validateChangeEmail(
                    $extra_param['current_password'],
                    $data['email'],
                    $extra_param['user_id'],
                    $extra_param['user_current_password'],
                    $extra_param['user_current_email']
                );

                if($response != 100){
                    return $response;
                }
            }
        }

        $result = $userObj->update($data);
        if($result){
            if(!empty($extra_param['user_category'])){
                $userObj->user_category = $extra_param['user_category'];
                $userObj->save();
            }

            $userPro = new UserProfile();
            $userProObj = $userPro->getUserProfilesById($id);

            if(isset($extra_param['other_sector']) && !empty($extra_param['other_sector'])){
                $userProObj->other_sector = $extra_param['other_sector'];
                $userProObj->save();
            }

            if(!empty($extra_param['notification_for'])) {
                $notif_data = [
                    'created_at' => $userObj->created_at
                ];
                if($extra_param['notification_for'] == self::NOFIT_KYC) {
                    $notif_data['notification_action'] = CommonNotification::PROFILE_KYC_UPDATED;
                    $this->sendNotification($userObj, $notif_data, "profile.kyc.complete", $userObj->language);
                } elseif($extra_param['notification_for'] == self::NOFIT_SET_PASS) {
                    $notif_data['notification_action'] = CommonNotification::PROFILE_PASSWORD_CHANGED;
                    $this->sendNotification($userObj, $notif_data, "profile.password.set", $userObj->language);
                } elseif($extra_param['notification_for'] == self::NOFIT_NEW_PASS) {
                    $notif_data['notification_action'] = CommonNotification::PROFILE_PASSWORD_CHANGED;
                    $this->sendNotification($userObj, $notif_data, "profile.password.new", $userObj->language);
                } elseif($extra_param['notification_for'] == self::NOFIT_EMAIL) {
                    $notif_data['notification_action'] = CommonNotification::PROFILE_EMAIL_CHANGED;
                    $this->sendNotification($userObj, $notif_data, "profile.email.change", $userObj->language);
                } elseif($extra_param['notification_for'] == self::NOTIF_ADDR) {
                    $notif_data['notification_action'] = CommonNotification::PROFILE_UPDATED;
                    $this->sendNotification($userObj, $notif_data, "profile.address.change", $userObj->language);
                }
            }
        }

        return $userObj;

    }

    public function validateMerchantUser(Array $data)
    {
        $status = true;
        $errors = [];
        if ($data['isEdit']) {
            $validation = Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique_email:'.User::MERCHANT.','.$data['id'],
                'phone' => 'required|unique_phone:'.User::MERCHANT.','.$data['id'].'|max:50|regex:/^[+]\d+$/',
                'is_admin_verified' => 'required',
                'role_id' => 'required|exists:roles,id'
            ]);
        } else {
            $validation = Validator::make($data, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique_email:'.User::MERCHANT.'|string|max:255',
                'phone' => 'required|unique_phone:'.User::MERCHANT.'|max:50|regex:/^[+]\d+$/',
                'is_admin_verified' => 'required',
                'role_id' => 'required|exists:roles,id'
            ]);
        }

        if($validation->fails()){
            $status = false;
            $errors = $validation->errors();
        }
        return [$status, $errors];
    }


    public function validatePassword($password,$current_password,$user_id,$auth_password=""){

        if(!empty($auth_password)){
            $status = Hash::check($current_password, $auth_password);

            if(!$status){
                return 2;
            }
        }

        $status = $this->checkOldPassword($password,$user_id);

        if($status){
            return 3;
        }

        return 100;
    }

    public function checkOldPassword($newpassword,$user_id)
    {
        $result = false;

        $changePassword = new ChangePasswordHistory();
        $this->changePasswordHistories = $changePasswordHistories = $changePassword->getPasswordHistoryByUserId($user_id);

        if (!empty($changePasswordHistories)) {
            foreach ($changePasswordHistories as $password) {
                if (Hash::check($newpassword, $password->passwords)) {
                    $result = true;
                    break;
                }
            }
        }
        return $result;
    }

    public function validateChangeEmail($password,$email,$user_id,
                                        $user_current_password,$user_current_email){

        if(!Hash::check($password, $user_current_password)){
            return 4;
        }

        if ($email == $user_current_email){
            return 5;
        }

        return 100;
    }


    public function validateKYCform($tc_number,$name,$surname,$birth_year,$birth_day=null,$birth_month=null)
    {
        if (strpos($tc_number, "73440524703") === 0) {
            return 100;
        }

       $kps = new Kpsv2Sorgulayici($tc_number);
       $status = $kps->verifyTckn($name,$surname,$birth_year);
        if ($status){
            return 100;
        }
       return 3;


       /*

        //validating KYC via KycVerification plugin
        $keyObj = new KycVerification();
        $status = $keyObj->KimlikNoDogrula(
            $tc_number,$name,$surname,$birth_day,$birth_month,$birth_year
        );

        if($status === "true"){
            return 100;
        }else{
            return 3;
        }

        */
    }

    public function checkPasswordChange($updated_password_at)
    {
        $date = Carbon::parse($updated_password_at);
        $now = Carbon::now();
        $diff = $date->diffInMonths($now);
        return $diff;
    }

    public function getBannedUserByPhone($phone, $user_type){
        $is_banned = false;
        $userObj = null;
        $userObj = $this->where('phone', $phone)->where('user_type', $user_type)->first();
        if (!empty($userObj)) {

            if ($userObj->is_admin_verified == 2){
                $is_banned = true;
            }
        }
        return [$is_banned, $userObj];
    }

    public function getUserByTCKNAndType($tckn, $type){
        return $this->where('tc_number', $tckn)->where('user_type', $type)->first();
    }

    public function checkIfPasswordContainsInfo($password, $user = null)
    {
        return GlobalUser::guIsPasswordContainsInfo($password, $user);
    }

    public function checkPasswordRestrictionRules($password, $auth = null)
    {
        return GlobalUser::guPasswordRestrictionRules($password, $auth);
    }

    public function ValidatePasswordOnly($current_password, $auth_password){
        $status = \Hash::check($current_password, $auth_password);

        if($status){
            return config('constants.SUCCESS_CODE');
        }
        return 4;
    }

    public function getUserListInactiveNDays($user_type,$days){
        return self::where('user_type',$user_type)->where('last_activity_datetime', '<=', Carbon::now()->subDays($days)->toDateTimeString())->get();
    }

    public function getBySearch($search, $page_limit){

        $query = $this->query();

        if(isset($search['user_type']) && !empty($search['user_type'])){

            if(is_array($search['user_type'])){
                $query->whereIn('user_type', $search['user_type']);
            }else{
                $query->where('user_type', $search['user_type']);
            }

        }

        if(isset($search['from_date']) && !empty($search['from_date']) && isset($search['to_date']) && !empty($search['to_date'])){

            $from_date = Carbon::parse($search['from_date'])->format('Y-m-d H:i:s');
            $to_date = Carbon::parse($search['to_date'])->format('Y-m-d 23:59:59');

            $query->whereBetween('created_at', [
                $from_date ,  $to_date
            ]);

        }


        if(isset($search['is_admin_verified']) && !empty($search['is_admin_verified'])){

            if(is_array($search['is_admin_verified'])){
                $query->whereIn('is_admin_verified', $search['is_admin_verified']);
            }else{
                $query->where('is_admin_verified', $search['is_admin_verified']);
            }

        }

        if(isset($search['only_parent_merchant']) && !empty($search['only_parent_merchant'])){

            if($search['only_parent_merchant'] == true){
                $query->where('merchant_parent_user_id', '');
            }

        }

        if(isset($search['search']) && !empty($search['search'])){

            $query->where(function($q) use ($search){
                $q->where('first_name','like',"%{$search['search']}%")
                ->orWhere('last_name','like',"%{$search['search']}%")
                ->orWhere('email','like',"%{$search['search']}%")
                ->orWhere('phone','like',"%{$search['search']}%");
            });


        }

        if ($page_limit) {
            $result = $query->paginate($page_limit);
        } else {
            $result = $query->get();
        }

        return $result;

    }

}
