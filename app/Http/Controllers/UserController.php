<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\FileUploadTrait;
use App\Models\AdminMakerChecker;
use App\Models\AppModel;
use App\Models\ChangePasswordHistory;
use App\Models\Merchant;
use App\Models\Profile;
use App\Models\SecurityImage;
use App\Models\Usergroup;
use App\Models\UserUsergroup;
use App\Models\UserUserGroupSetting;
use App\User;
use App\Utils\CommonFunction;
use Carbon\Carbon;
use common\integration\ApiService;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\BrandConfiguration;
use common\integration\GlobalFunction;
use common\integration\ManageLogging;
use common\integration\ManageOtp;
use common\integration\Models\OutGoingEmail;
use common\integration\Models\UserFormSectionPermissions;
use common\integration\Models\UserHideMerchant;
use common\integration\Services\AdminMakerCheckerService;
use common\integration\Utility\Arr;
use common\integration\Utility\File;
use common\integration\Utility\Str;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Cookie;
use Auth;
use Config;
use Mockery\Exception;
use App\Models\MerchantReportHistory;
use App\Models\UserApiCredential;
use App\Models\UserProfile;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\GlobalUser;
use common\integration\Traits\UniqueKeyGeneratorTrait;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use common\integration\Utility\Exception as CommonException;

class UserController extends Controller
{
    use FileUploadTrait, UniqueKeyGeneratorTrait;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    private $is_reset_otp_limit = false;


    public function index(Request $request)
    {
        if ($request->ajax()) {
            $search = trim($request->input('search'));
            return $this->getusers($search, $request->all());
        }
        $s = '';
        $page_limit = 10;

        if (isset($request->file_type) && !empty($request->file_type)) {
            $page_limit = false;
        }

        if (isset($request->page_limit)) {
            $page_limit = $request->page_limit;
        }
        if (isset($request->s)) {
            $s = $request->s;
        }
        $cmsInfo = [
            'moduleTitle' => __("Access Control"),
            'subModuleTitle' => __("User Management"),
            'subTitle' => __("User List")
        ];

        // $users = User::where('company_id', Auth::user()->company_id)
        //     ->where(function ($query) use ($s){
        //         $query->where('name','like', '%'.$s.'%')
        //             ->orWhere('email','like', '%'.$s.'%');
        //     })->orderBy('updated_at','DESC')->paginate($page_limit);

        $users = (new User)->getCompanyUserWithRoles($s, $page_limit, Auth::user()->company_id);

        $exportBtnRouteName = Config::get('constants.defines.APP_USERS_INDEX');
        $export_types = File::FORMAT_LIST;

        if (BrandConfiguration::allowAccessControlExport() && isset($request->file_type) && !empty($request->file_type)) {

            return $this->exports($users, $request->file_type);

        }

        $allowModifyHiddenMerchant = false;
        $hiddenMerchantBtnRouteName = Config::get('constants.defines.APP_USERS_HIDDEN_MERCHANT');
        if (BrandConfiguration::allowRestrictFilterForMerchantAndUsers() && Auth::user()->hasPermissionOnAction($hiddenMerchantBtnRouteName)) {
            $allowModifyHiddenMerchant = true;
        }

        return view('users.index', compact('users', 'cmsInfo', 'page_limit', 's', 'exportBtnRouteName', 'export_types', 'allowModifyHiddenMerchant', 'hiddenMerchantBtnRouteName'));
    }

    private function exports($users, $file_type = MerchantReportHistory::FORMAT_LIST[MerchantReportHistory::FORMAT_PDF])
    {

        $header = [
            __('ID'),
            __('Name'),
            __('Email'),
            __('Status'),
            __('Roles'),
            __('Created At'),
            __('Updated At'),
        ];

        $data = $users->map(function ($details) {
            return [
                'ID' => $details->id,
                'Name' => \common\integration\GlobalFunction::nameCaseConversion($details->name),
                'Email' => $details->email,
                'Status' => $details->is_admin_verified == \App\Models\Profile::ADMIN_VERIFIED_APPROVED ? __('Active') : __('Inactive'),
                'Roles' => $details->useGroups?->pluck('role.*.title')->flatten()->unique()->implode(', '),
                'Created At' => $details->created_at?->format('Y-m-d H:i:s'),
                'Updated At' => $details->updated_at?->format('Y-m-d H:i:s'),
            ];
        });

        $file_name = __('User Management');

        if ($file_type == MerchantReportHistory::FORMAT_CSV) {

            $file_type = MerchantReportHistory::FORMAT_LIST[$file_type];
            return $this->exportcsv($header, $data, $file_name);

        } elseif ($file_type == MerchantReportHistory::FORMAT_XLS) {

            $file_type = MerchantReportHistory::FORMAT_LIST[$file_type];
            return $this->fileExport($file_type, $data, $file_name, $header);

        } elseif ($file_type == MerchantReportHistory::FORMAT_PDF) {

            $view_file = 'pdf_blades.common';
            $file_type = MerchantReportHistory::FORMAT_LIST[$file_type];
            return $this->fileExport($file_type, $data, $file_name, $header, $view_file);

        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->store($request);
        } else {
            $cmsInfo = [
                'moduleTitle' => __("Access Control"),
                'subModuleTitle' => __("User Management"),
                'subTitle' => __("Create User")
            ];
            $usergroups = Usergroup::where('company_id', Auth::user()->company_id)->get();

            $dynamic_route = route(Config::get('constants.defines.APP_USERS_CREATE'));
            return view('users.add', compact('usergroups', 'dynamic_route', 'cmsInfo'));
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validator($request->all())->validate();
        $uploadPath = "users/img_path";
        /**
         * maker checker specific part
         */
        $extras = ['attachment_folder' => $uploadPath];
        $auth = Auth::user();
        if (!isset($request->approved_by_checker)) {
            $request->merge([
                'created_by_id' => $auth->id,
                'created_by_name' => $auth->name
            ]);
        }

        $input = $request->all();
        $userRequestData = [
            'name' => $input['first_name'] . ' ' . $input['last_name'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'phone' => $input['phone'],
            'company_id' => Auth::user()->company_id,
            'password' => isset($input['password']) ? bcrypt($input['password']) : null,
            'language' => $input['language'],
            'user_type' => 1,
            'created_by_id' => $request->created_by_id ?? Auth::user()->id,
            'created_by_name' => $request->created_by_name ?? Auth::user()->name,
            'updated_password_at' => Carbon::now()
        ];

        if (BrandConfiguration::call([Mix::class, 'isAllowAdminApi']) && !empty($input['usergroup_id']) && Arr::isOfType($input['usergroup_id'])) {
            $userRequestData['user_group_ids'] = Arr::implode(',', $input['usergroup_id']);
        }

        $userRequestData['otp_channel'] = (new GlobalUser())->getDefaultOtpChannel();

        $user = User::create($userRequestData);

        if (BrandConfiguration::call([Mix::class, 'isAllowAdminApi'])) {
            if ($user) {
                $client_id = $this->generateUniqueKey($user->id);
                $client_secret = $this->generateUniqueKey($user->id);

                $userApiCredential = new UserApiCredential();
                $userApiCredentialData = [
                    'user_id' => $user->id,
                    'credential_name' => $user->name,
                    'client_id' => $client_id,
                    'client_secret' => $client_secret
                ];
                try {
                    $stored = $userApiCredential->storeCredential($userApiCredentialData);
                    if (!$stored) {
                        flash(__('Something worng happend during UserApiCredential Model'), 'danger');
                        return redirect()->back();
                    }
                } catch (\Throwable $th) {
                    flash(CommonException::fullMessage($th), 'danger');
                    return redirect()->back();
                }
            }
        }

        (new UserProfile())->addUserProfiles($user->id);

        if (isset($input['password'])) {
            (new ChangePasswordHistory())->createHistory($user->id, $user->password);
        }

        if (!empty($input['img_path'])) {
            if (!empty($user->img_path)) {
                $this->deleteFile($user->img_path);
            }
            if (isset($request->approved_by_checker)) {
                $input['img_path'] = $input['img_path'] ?? '';
            } else {
                $input['img_path'] = $this->uploadFile($input['img_path'], $uploadPath);
            }
            $user->update(['img_path' => $input['img_path']]);
        }

        $input['user_id'] = $user->id;
        if (!empty($input['usergroup_id'])) {
            foreach ($input['usergroup_id'] as $usergroup) {
                UserUsergroup::create([
                    'usergroup_id' => $usergroup,
                    'user_id' => $user->id,
                    'company_id' => Auth::user()->company_id
                ]);
            }
        }

        $businessLogData['action'] = "ADMIN_USER_CREATION";
        $logData = $request->all();
        $remove_keys = ['password', 'password_confirmation', 'client_secret'];
        $logData = Arr::unset($logData, $remove_keys);
        $input = Arr::unset($input, $remove_keys);
        $businessLogData['input_data'] = $logData;
        $businessLogData['model_data'] = $input;
        $businessLogData['message'] = "The record has been saved successfully!";
        $this->createLog($this->_getCommonLogData($businessLogData));
        flash(__('The record has been saved successfully!'), 'success');
        return redirect()->back();


        return redirect()->back();
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function view($id)
    {
        $flag = $this->validateCompany($id, 'users');
        if ($flag) {
            return redirect()->route('login');
        }
        return $this->show($id);
    }

    public function show($id)
    {
        $cmsInfo = [
            'moduleTitle' => __("Access Control"),
            'subModuleTitle' => __("User Management"),
            'subTitle' => __("View User")
        ];
        $usergroups = Usergroup::all();
        $user = User::findOrFail($id);
        $assaigned_usergroups = [];
        $assaigned_groups = UserUsergroup::where('user_id', $user->id)->get();
        if (count($assaigned_groups) > 0) {
            foreach ($assaigned_groups as $assaigned_group) {
                $assaigned_usergroups[] = $assaigned_group->usergroup_id;
            }
        }

        $disabled = 'disabled';

        $userugsObj = new UserUserGroupSetting();
        $userUGS = $userugsObj->findByUserId($id);

        $dynamic_route = route(Config::get('constants.defines.APP_USERS_EDIT'), [$id, User::USER_UPDATE_ACTION]);

        $user_panel_sections_show = true;
        if (!BrandConfiguration::isWalletPaymentExist()) {
            $user_panel_sections_show = false;
        }
        $secretField = false;
        $userApiCredentialData = '';
        if (BrandConfiguration::call([Mix::class, 'isAllowAdminApi'])) {
            $secretField = true;
            $userApiCredential = new UserApiCredential();
            $userApiCredentialData = $userApiCredential->getUserById($id);
        }
        $authUser = Auth::user();
        $is_show_client_id_client_secret = false;

        return view('users.edit_view', compact('usergroups', 'dynamic_route', 'user', 'assaigned_usergroups', 'cmsInfo', 'userUGS', 'disabled', 'user_panel_sections_show', 'secretField', 'userApiCredentialData', 'is_show_client_id_client_secret'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function updateUserClientSecret(int $userId, string $secret)
    {
        $userApiCredential = new UserApiCredential();
        if ($userApiCredential->getUserById($userId)) {
            $userApiCredential->updateCredential(['client_secret' => $secret], $userId);
        } else {
            $userModel = new User();
            $userData = $userModel->findById($userId);
            $userApiCredential = new UserApiCredential();
            $userApiCredentialData = [
                'user_id' => $userData->id,
                'credential_name' => $userData->name,
                'client_id' => $this->generateUniqueKey($userId),
                'client_secret' => $secret ? $secret : $this->generateUniqueKey($userId)
            ];
            $userApiCredential->storeCredential($userApiCredentialData);
        }
    }


    public function edit(Request $request, $id = null)
    {

        if ($request->ajax()) {
            if ($request->action === 'generateClientSecret' && !empty($id) && BrandConfiguration::call([Mix::class, 'isAllowAdminApi'])) {
                return $this->generateUniqueKey($id);
            }
        }

        $flag = $this->validateCompany($id, 'users');
        if ($flag) {
            return redirect()->route('login');
        }
        if ($request->isMethod('put')) {
            //dd($request->client_secret);
            return $this->update($request, $id);
        } else {
            $resendotp = $request->input('resendotp');

            if (isset($resendotp) && !empty($resendotp)) {

                if (ManageOtp::checkResendOtpLimit(ManageOtp::ADMIN_PANEL_USER_EDIT, auth()->user()->id)) {
                    flash(__(ManageOtp::getMaximumOtpLimitExceedMessage()), 'danger');
                    return $this->userEdit($id);
                }
                $data = $this->resendOTP($request, $id);
                return view('users.otpform', $data);
            }
            if (BrandConfiguration::isAllowedUserManagementWithOutOTP() && isset($request->approved_by_checker) && $request->approved_by_checker == 'approved_by_checker') {
                $input = $request->session()->get('userUpdateInfoStore');
                $userObj = new User();
                $user = $userObj->findUserById($request->approved_by_checker ? $request->user_id : $input['user_id']);

                return $this->userInfoUpdate($request, $input, $userObj, $user);
            }

            $otp = $request->input('otp');

            if (isset($otp) && !empty($otp)) {
                $this->verifyOTP($request, $id);
            }

            if (isset($request->action) && $request->action == User::USER_PASSWORD_UPDATE_ACTION) {
                return $this->userPasswordChange($id);
            } else {
                return $this->userEdit($id);
            }
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        return $this->userUpdate($request, $id);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        if ($id == 'move_to_trash') {
            $logData['action'] = "ADMIN_BULK_USER_DELETE";
            $users_id = request()->input('users_id');
            if (!is_array($users_id) || empty($users_id)) return redirect()->route('home');
        } else {
            $logData['action'] = "ADMIN_SINGLE_USER_DELETE";
            $users_id = [
                $id
            ];
        }

        $request->merge([
            'users_id' => $users_id,
        ]);

        if (BrandConfiguration::allowedAdminMakerCheckerBrand()) {
            list($statusCode, $statusMessage) = User::checkUsersDeleteValidation($users_id, User::ADMIN, Auth::user());
            if ($statusCode == 1) {
                Auth::logout();
                flash(__($statusMessage), 'danger');
                return redirect()->route('login');
            } else if ($statusCode == 2 || $statusCode == 3) {
                flash(__($statusMessage), 'danger');
                return redirect()->back();
            }
        }

        $auth = Auth::user();

        list($statusCode, $statusMessage) = User::deleteUsers($users_id, User::ADMIN, Auth::user());

        if ($statusCode == 1) {
            Auth::logout();
            flash(__($statusMessage), 'danger');
            return redirect()->route('login');
        } else if ($statusCode == 2 || $statusCode == 3) {
            flash(__($statusMessage), 'danger');
        } else if ($statusCode == 100) {
            flash(__($statusMessage), 'success');
        } else {
            flash(__('Unknown error'), 'danger');
        }

        $logData['user_id'] = json_encode($users_id);
        $logData['auth_user_id'] = Auth::user()->id;
        $this->createLog($this->_getCommonLogData($logData));

        return back();
    }

    protected function validator(array $data)
    {
        if (config('constants.PASSWORD_SECURITY_TYPE') == Profile::ALPHANUMERIC_PASSWORD) {
            $min_password_length = 8;
            $pass_rules = '|min:' . $min_password_length . '|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-.,]).{8,}$/';
        } else {
            $min_password_length = 6;
            $max_password_length = 6;
            $pass_rules = '|min:' . $min_password_length . '|max:' . $max_password_length . '|regex:/^[0-9]*$/';
        }
        $first_name_rule = 'required|string|max:255';
        $last_name_rule = 'string|max:255';
        if (BrandConfiguration::call([BackendMix::class, "isFirstNameAndSurnameRequiredForUser"])) {
            $first_name_rule = 'required|string|max:255|regex:/^[^\d]+$/u';
            $last_name_rule = 'required|string|max:255|regex:/^[^\d]+$/u';
        }
        if (isset($data['isEdit'])) {
            return Validator::make($data, [
                'first_name' => $first_name_rule,
                'last_name' => $last_name_rule,
                'email' => 'required|email|string|max:255|unique_email:' . User::ADMIN . ',' . $data['id'],
                'language' => 'required',
                'is_admin_verified' => 'required',
                'usergroup_id' => 'required|exists:usergroups,id',
                'phone' => 'required|regex:/^[+]\d+$/|unique_phone:' . User::ADMIN . ',' . $data['id'] . '|max:50',
                'img_path' => 'sometimes|mimes:jpeg,png,jpg|image|max:1024',
            ]);
        } elseif (isset($data['isPasswordChange'])) {
            //dd($data['password'],$data['password_confirmation']);
            return Validator::make($data, [
                //'password' => 'required|min:8|string',
                'password' => 'required' . $pass_rules,
                'password_confirmation' => 'required|same:password',
            ]);
        } else {
            $validation_rules = [
                'first_name' => $first_name_rule,
                'last_name' => $last_name_rule,
                'email' => 'required|email|unique_email:' . User::ADMIN . '|string|max:255',
                'phone' => 'required|unique_phone:' . User::ADMIN . '|max:50|regex:/^[+]\d+$/',
                'password' => 'required' . $pass_rules,
                'password_confirmation' => 'required|same:password',
                'language' => 'required',
                'usergroup_id' => 'required|exists:usergroups,id',
                'img_path' => 'sometimes|mimes:jpeg,png,jpg|image|max:1024',
            ];

            if (BrandConfiguration::isAllowAdminWelcomeMail()) {
                unset($validation_rules['password']);
                unset($validation_rules['password_confirmation']);
            }

            $message = [
                'password.min' => __('The password must be at least :min_password_length characters.', ['min_password_length' => $min_password_length]),
                'password.regex' => __('The password format is invalid.'),
                'password_confirmation.same' => __('The password confirmation and password must match.'),
            ];

            return Validator::make($data, $validation_rules, $message);
        }
    }

    protected function userEdit($id)
    {
        $logger = '';
        $authID = '';
        $profile = Auth::user();

        if ($id == 'profile') {

            $id = $profile->id;

            $cmsInfo = [
                'moduleTitle' => __("Dashboard"),
                'subModuleTitle' => __("Profile"),
                'subTitle' => __("Edit Profile")
            ];
            $logger = $id;
            $dynamic_route = route('profile.edit.post', $id);
        } else {
            $cmsInfo = [
                'moduleTitle' => __("Access Control"),
                'subModuleTitle' => __("User Management"),
                'subTitle' => __("Edit User")
            ];
            $dynamic_route = route(Config::get('constants.defines.APP_USERS_EDIT'), [$id, User::USER_UPDATE_ACTION]);

            $authID = $profile->id;
        }

        $usergroups = Usergroup::where('company_id', Auth::user()->company_id)->get();
        $user = User::findOrFail($id);
        $assaigned_usergroups = [];
        $assaigned_groups = UserUsergroup::where('user_id', $user->id)->get();
        if (count($assaigned_groups) > 0) {
            foreach ($assaigned_groups as $assaigned_group) {
                $assaigned_usergroups[] = $assaigned_group->usergroup_id;
            }
        }

        $disabled = $this->restrictAuthToEdit($user) ? 'disabled' : '';

        $userugsObj = new UserUserGroupSetting();
        $userUGS = $userugsObj->findByUserId($id);

        $user_panel_sections_show = true;
        if (!BrandConfiguration::isWalletPaymentExist()) {
            $user_panel_sections_show = false;
        }
        $userApiCredentialData = '';
        if (BrandConfiguration::call([Mix::class, 'isAllowAdminApi'])) {
            $userApiCredential = new UserApiCredential();
            $userApiCredentialData = $userApiCredential->getUserById($id);
        }
        $is_show_client_id_client_secret = false;


        return view('users.edit_view', compact('userApiCredentialData', 'usergroups', 'dynamic_route', 'user', 'userUGS', 'assaigned_usergroups', 'cmsInfo', 'disabled', 'logger', 'user_panel_sections_show', 'is_show_client_id_client_secret'));
    }

    private function sendSMSToUser(User $user, $OTP)
    {
        ///Send SMS OTP
        $language = $this->getLang($user);
        $header = "";
        $phone = $user->phone;
        $message = view('OTP.profile_change.profile_change_' . $language,
            compact('OTP'))->render();
        $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
        $this->sendSMS($header, $message, $phone, 1);
    }

    private function sendEmailTOUser(User $user, $OTP)
    {
        /// Send E-mail
        $data['OTP'] = $OTP;
        $language = $this->getLang($user);
        $from = config('app.SYSTEM_NO_REPLY_ADDRESS');
        $to = $user->email;
        $template = "user_profile.user_profile";

        //out_going_email
        $this->setGNPriority(OutGoingEmail::PRIORITY_EXPRESS);
        $this->sendEmail($data, "LOGIN_OTP", $from, $to, "", $template, $language, '', 1);
    }

    private function sendOTPforUserInfoUpdate(Request $request, $input)
    {
        if (ManageOtp::checkResendOtpLimit(ManageOtp::ADMIN_PANEL_USER_EDIT, auth()->user()->id)) {
            $this->is_reset_otp_limit = true;
            return false;
        }

        try {
            $request->session()->put('userUpdateInfoStore', $input);

//            $user = User::find($input['user_id']);
            $auth_user_id = auth()->user()->id;
            $user = User::find($auth_user_id);
            $OTP = $user->cacheTheOTP();

            $this->sendSMSToUser($user, $OTP);
            $this->sendEmailTOUser($user, $OTP);

            return true;
        } catch (\Throwable $exception) {
            dd($exception->getFile(), $exception->getMessage(), $exception->getLine());
            return false;
        }
    }

    private function verifyOTP(Request $request, $id)
    {
        $this->validate($request, [
            'otp' => 'required'
        ]);

        $auth_user_id = auth()->user()->id;
        $auth_user = User::find($auth_user_id);
        $cachedOTP = $auth_user->OTP();

        $requestOTP = $request->input('otp');

        $input = $request->session()->get('userUpdateInfoStore');

        if (empty($input) && $request->approved_by_checker) {
            $input = $request->all();
        }


        $userObj = new User();
        $user = $userObj->findUserById($request->approved_by_checker ? $request->user_id : $input['user_id']);
        if ($cachedOTP == $requestOTP || (isset($request->approved_by_checker) && $request->approved_by_checker == 'approved_by_checker')) {

            ManageOtp::clearOtpLimitCache(ManageOtp::ADMIN_PANEL_USER_EDIT, \auth()->user()->id);
            ManageOtp::clearOtpLimitCache(ManageOtp::ADMIN_PANEL_USER_EDIT_OTP_SUBMIT, \auth()->user()->id);

            if (!empty($input['img_path']) && Storage::exists($input['img_path'])) {
                $file_address = Storage::path($input['img_path']);
                $img_info = getimagesize($file_address);
                $file = new UploadedFile($file_address, $file_address, $img_info['mime'], false, false);
                $request->files->set('img_path', $file);
            }

            return $this->userInfoUpdate($request, $input, $userObj, $user);

        } else {

            if (ManageOtp::checkResendOtpLimit(ManageOtp::ADMIN_PANEL_USER_EDIT_OTP_SUBMIT, auth()->user()->id, true)) {
                flash(__(ManageOtp::getMaximumOtpRequestMessage()), 'danger');
                return redirect()->back();
            }

            $vData['cmsInfo'] = [
                'moduleTitle' => __("Dashboard"),
                'subModuleTitle' => __("Profile"),
                'subTitle' => __("Verify OTP")
            ];

            // Remove temporary image
            if (isset($input['img_path'])) {
                $this->deleteFile($input['img_path']);
            }

            flash(__('OTP does not match'), 'danger');
            $vData['user_id'] = $id;
            $vData['dynamic_route'] = route(Config::get('constants.defines.APP_USERS_EDIT'), [$user->id, $request->action]);
            $businessLogData['action'] = "ADMIN_USER_OTP_FAILED";
            $logData = $request->all();
            unset($logData['password']);
            unset($logData['password_confirmation']);
            unset($user['password']);
            unset($user['password_confirmation']);
            $businessLogData['input_data'] = $logData;
            $businessLogData['message'] = 'OTP does not match';
            $businessLogData['model_data'] = $user;
            $this->createLog($this->_getCommonLogData($businessLogData));
            return view('users.otpform', $vData);
        }
    }

    private function userInfoUpdate($request, $input, $userObj, $user)
    {

        $auth = Auth::user();
        $auth_user_id = $auth->id;

        $isPasswordChange = isset($input['isPasswordChange']) && $input['isPasswordChange'] ? true : false;

        $udata = [
            'name' => $input['name'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'email' => $input['email'],
            'language' => $input['language'],
            'is_admin_verified' => $input['is_admin_verified'],
            'phone' => $input['phone'],
            'img_path' => $input['img_path'] ?? null,
            'temp_path' => $input['temp_path'] ?? null,
            'perm_path' => $input['perm_path'] ?? null,
            'otp_channel' => $input['otp_channel'] ?? User::OTP_CHANNEL_ALL, // 0 => Otp enable for all chanel, 1 => only sms, 2 => only email
            'is_merchant_restricted_by_account_manager' => $input['is_merchant_restricted_by_account_manager'] ?? 0,
            'is_otp_required' => $input['is_otp_required'] ?? 0,
        ];

        if (BrandConfiguration::call([Mix::class, 'isAllowAdminApi']) && !empty($input['usergroup_id'])) {
            $udata['user_group_ids'] = Arr::implode(',', $input['usergroup_id']);
        }

        if (!$isPasswordChange) {
            $user_update = $userObj->updateUser(
                $user->id, $udata
            );
            if ($user_update) {
                //session()->put('locale', $input['language']);
                Cookie::queue('locale', $input['language'], time() + 31556926);
            }
        }

        if (isset($input['client_secret']) && !empty($input['client_secret']) && BrandConfiguration::call([Mix::class, 'isAllowAdminApi'])) {
            $this->updateUserClientSecret($user->id, $input['client_secret']);
        }

        $input_data['user_id'] = $user->id;
        $input_data['is_allow_direct_export'] = $input['is_allow_direct_export'];
        $input_data['is_allow_b2b_without_doc'] = $input['is_allow_b2b_without_doc'];
        $input_data['is_allow_create_deposit'] = $input['is_allow_create_deposit'];
        $input_data['is_allow_create_withdrawal'] = $input['is_allow_create_withdrawal'];
        $input_data['is_allow_create_b2b'] = $input['is_allow_create_b2b'];
        $input_data['is_allow_create_b2c'] = $input['is_allow_create_b2c'];
        $input_data['is_show_auth_email'] = $input['is_show_auth_email'];
        $input_data['is_show_website'] = $input['is_show_website'];

        if (!$isPasswordChange) {
            $userGroupSettingObj = new UserUserGroupSetting();
            $userGroupSettingObj->insertOrUpdate($input_data);
        }

        if (!empty($input['password'])) {
            $user->update([
                'password' => bcrypt($input['password']),
                'updated_password_at' => Carbon::now()
            ]);
            Session::forget('AdminChangedPasswordStatus' . $auth_user_id);

            if (BrandConfiguration::call([BackendAdmin::class, 'isAllowDeletePasswordHistory'])) {
                (new GlobalUser)->removePasswordHistory($user);

            }
            (new ChangePasswordHistory())->createHistory($user->id, $user->password);

            if (BrandConfiguration::allowChangePasswordMail()) {

                (new GlobalUser)->sentChangePasswordEmail($user, 'ADMIN_CHANGE_PASSWORD_FROM_PANEL');

            }

        }


        $input['user_id'] = $user->id;
        if (!$isPasswordChange && !empty($input['usergroup_id'])) {
            UserUsergroup::where('user_id', $user->id)->delete();
            foreach ($input['usergroup_id'] as $usergroup) {
                UserUsergroup::create([
                    'usergroup_id' => $usergroup,
                    'user_id' => $user->id,
                    'company_id' => Auth::user()->company_id
                ]);
            }
        }

        $user_profile = (new UserProfile())->getUserProfilesById($user->id);

        if (!$isPasswordChange && !empty($user_profile) && $user_profile->is_welcome_email_sent == false && $udata['is_admin_verified'] == Profile::ADMIN_VERIFIED_APPROVED && BrandConfiguration::isAllowAdminWelcomeMail()) {
            $this->sendWelcomeMailToAdmin($user);

            (new UserProfile())->updateUserProfiles($user->id, [
                'is_welcome_email_sent' => true
            ]);
        }

        $businessLogData['action'] = "ADMIN_USER_OTP_SUCCESS";
        $logData = $request->all();
        unset($logData['password']);
        unset($logData['password_confirmation']);
        unset($input['password']);
        unset($input['password_confirmation']);
        unset($input['user_password']);
        $businessLogData['input_data'] = $logData;
        $businessLogData['message'] = 'The record has been updated successfully!';
        $businessLogData['model_data'] = $input;
        $this->createLog($this->_getCommonLogData($businessLogData));
        flash(__('The record has been updated successfully!'), 'success');

        return redirect()->back();
    }

    private function resendOTP(Request $request, $id)
    {
        $vData['cmsInfo'] = [
            'moduleTitle' => __("Dashboard"),
            'subModuleTitle' => __("Profile"),
            'subTitle' => __("Verify OTP")
        ];
        try {
//            $user = User::find($id);
            $user = auth()->user();
            $OTP = $user->cacheTheOTP();

            $this->sendSMSToUser($user, $OTP);
            $this->sendEmailTOUser($user, $OTP);
            flash(__('Your new OTP is sent, please check'), 'success');
        } catch (\Throwable $exception) {
            flash(__('OTP Resend Failed'), 'danger');
        }
        $vData['user_id'] = $id;
        $vData['action'] = $request->action;
        if (isset($request->action) && $request->action == User::USER_PASSWORD_UPDATE_ACTION) {
            $vData['dynamic_route'] = route(Config::get('constants.defines.APP_USERS_CHANGEPASSWORD'));
        } else {
            $vData['dynamic_route'] = route(Config::get('constants.defines.APP_USERS_EDIT'), [$id, $request->action]);
        }
        return $vData;
    }


    protected function userUpdate(Request $request, $id)
    {
        $businessLogData['action'] = "ADMIN_USER_EDIT_REQUEST";

        $data = $request->all();
        if (!empty($data['approved_by_checker'])) {
            $id = $data['user_id'];
        }
        $user = User::find($id);

        if ($this->restrictAuthToEdit($user)) {
            flash("You can't update your own request", "danger");
            return redirect()->back();
        }

        if (isset($request->action) && $request->action == User::USER_PASSWORD_UPDATE_ACTION) {
            $input['isPasswordChange'] = $data['isPasswordChange'] = true;
            $profileObj = new Profile();

            $isOldPassword = $profileObj->checkOldPassword($request->password, !empty($user) ? $user->id : 0);
            if ($isOldPassword) {
                return back()->withErrors(__('Please, do not use old password again'));
            }

            list($isPassContainInfo, $errorMsg) = $profileObj->checkIfPasswordContainsInfo($request->password, $user);
            if ($isPassContainInfo) {
                return back()->withErrors($errorMsg);
            }
        } else {
            $data['isEdit'] = true;
        }
        $data['id'] = $id;

        $this->validator($data)->validate();

        $input['name'] = $request->input('first_name') . ' ' . $request->input('last_name');
        $input['first_name'] = $request->input('first_name');
        $input['last_name'] = $request->input('last_name');
        $input['email'] = $request->input('email');
        $input['language'] = $request->input('language');
        $input['phone'] = $request->input('phone');
        $input['user_password'] = $request->input('user_password');
        $input['password'] = $request->input('password');
        $input['password_confirmation'] = $request->input('password_confirmation');
        $input['is_admin_verified'] = $request->input('is_admin_verified');
        $input['img_path'] = $request->file('img_path');
        $input['usergroup_id'] = $request->input('usergroup_id');
        $input['client_secret'] = $request->input('client_secret');
        $input['user_id'] = $id;

        if (!empty($input['img_path'])) {
            // Path to save image until image is upload is approved
            $temp_path = 'users/temp/img_path';
            $input['img_path'] = $this->uploadFile($input['img_path'], $temp_path);
            $input['temp_path'] = $temp_path;
            $input['perm_path'] = 'users/img_path';
        }

        $input['is_allow_direct_export'] = 0;
        if (isset($request->is_allow_direct_export) && !empty($request->is_allow_direct_export)) {
            $input['is_allow_direct_export'] = 1;
        }

        $input['is_allow_b2b_without_doc'] = 0;
        if (isset($request->is_allow_b2b_without_doc) && !empty($request->is_allow_b2b_without_doc)) {
            $input['is_allow_b2b_without_doc'] = 1;
        }

        $input['is_allow_create_deposit'] = 0;
        if (isset($request->is_allow_create_deposit) && !empty($request->is_allow_create_deposit)) {
            $input['is_allow_create_deposit'] = 1;
        }

        $input['is_allow_create_withdrawal'] = 0;
        if (isset($request->is_allow_create_withdrawal) && !empty($request->is_allow_create_withdrawal)) {
            $input['is_allow_create_withdrawal'] = 1;
        }

        $input['is_allow_create_b2b'] = 0;
        if (isset($request->is_allow_create_b2b) && !empty($request->is_allow_create_b2b)) {
            $input['is_allow_create_b2b'] = 1;
        }

        $input['is_allow_create_b2c'] = 0;
        if (isset($request->is_allow_create_b2c) && !empty($request->is_allow_create_b2c)) {
            $input['is_allow_create_b2c'] = 1;
        }

        $input['is_show_auth_email'] = 0;
        if (isset($request->is_show_auth_email) && !empty($request->is_show_auth_email)) {
            $input['is_show_auth_email'] = 1;
        }

        $input['is_show_website'] = 0;
        if (isset($request->is_show_website) && !empty($request->is_show_website)) {
            $input['is_show_website'] = 1;
        }

        if (BrandConfiguration::call([BackendMix::class, 'allowDefaultOtpChannelAsSMS'])) {
            $input['otp_channel'] = (new GlobalUser())->getDefaultOtpChannel(); // 0 => Otp enable for all chanel, 1 => only sms, 2 => only email
        } else {
            $input['otp_channel'] = $request->input('otp_channel', User::OTP_CHANNEL_EMAIL); // 0 => Otp enable for all chanel, 1 => only sms, 2 => only email
        }
        $input['is_otp_required'] = 0;
        if (BrandConfiguration::call([BackendAdmin::class, 'adminRemoteLogin']) && isset($request->is_otp_required)) {
            $input['is_otp_required'] = 1;
        }

        $input['is_merchant_restricted_by_account_manager'] = 0;
        if (isset($request->is_merchant_restricted_by_account_manager) && !empty($request->is_merchant_restricted_by_account_manager)) {
            $input['is_merchant_restricted_by_account_manager'] = 1;
        }

        if (!empty($input['user_password']) || !empty($input['password']) || !empty($input['password_confirmation'])) {
            if (config('constants.PASSWORD_SECURITY_TYPE') == Profile::ALPHANUMERIC_PASSWORD) {
                $pass_rule_message = 'The password must be 8 characters long, must contain a mix of upper/lowercase letters, numbers, and special characters';
                $pass_rules = '|min:8|regex:/^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[#?!@$%^&*-.,]).{8,}$/';
            } else {
                $pass_rule_message = 'The password must be 6 characters long';
                $pass_rules = '|min:6|max:6|regex:/^[0-9]*$/';
            }

            $this->validate($request, [
                'user_password' => 'required',
                'password' => 'required' . $pass_rules,
                'password_confirmation' => 'required|same:password',
            ], [
                'user_password.required' => __('Old password is required, please, try again'),
                'password.required' => __('New password is required'),
                'password' => __($pass_rule_message),
                'password_confirmation.required' => __('Confirm password is required'),
                'password_confirmation.same' => __('Confirm password and new password must match')
            ]);

            if (!Hash::check($request->user_password, $user->password)) {
                return back()
                    ->withErrors(__('Old password is incorrect, please, try again'));
            }

        }
        if (BrandConfiguration::isAllowedUserManagementWithOutOTP()) {
            $userObj = new User();

            $businessLogData['model_data'] = $input;
            $this->createLog($this->_getCommonLogData($businessLogData));
            return $this->userInfoUpdate($request, $input, $userObj, $user);
        }

        $status = $this->sendOTPforUserInfoUpdate($request, $input);

        if ($this->is_reset_otp_limit) {
            flash(__(ManageOtp::getMaximumOtpLimitExceedMessage()), 'danger');
            return redirect()->back();
        }

        $logData = $request->all();
        $remove_keys = ['password', 'password_confirmation', 'user_password', 'client_secret'];
        $logData = Arr::unset($logData, $remove_keys);
        $input = Arr::unset($input, $remove_keys);
        $businessLogData['otp_status'] = "OTP sent";
        $businessLogData['input_data'] = $logData;
        $businessLogData['response_code'] = $status;
        $businessLogData['model_data'] = $input;
        $this->createLog($this->_getCommonLogData($businessLogData));
        if ($status) {
            $vData['cmsInfo'] = [
                'moduleTitle' => __("Dashboard"),
                'subModuleTitle' => __("Profile"),
                'subTitle' => __("Verify OTP")
            ];

            $vData['action'] = $request->action;
            $vData['user_id'] = $user->id;

            if ($request->action == User::USER_PASSWORD_UPDATE_ACTION) {
                $vData['dynamic_route'] = route(Config::get('constants.defines.APP_USERS_CHANGEPASSWORD'));
            } else {
                $vData['dynamic_route'] = route(Config::get('constants.defines.APP_USERS_EDIT'), [$user->id, $request->action]);
            }


            if (GlobalFunction::hasBrandSession('flash_message')) {
                GlobalFunction::unsetBrandSession('flash_message');
            }

            return view('users.otpform', $vData);
        } else {
            flash("You can't update your own tanvir", "danger");
            return redirect()->back();
        }

    }


    public function profileEdit(Request $request, $id)
    {
        if ($request->isMethod('post')) {
            return $this->userUpdate($request, $id);
        } else {
            return $this->userEdit($id);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function profileUpdate(Request $request, $id)
    {
        return $this->userUpdate($request, $id);

    }

    private function getusers($search, $request_data = [])
    {
        //$search = trim($inputData['search']);

        if (strlen($search) > 3) {
            $user = (new User())->getUserBySearch($search, $request_data);
//            $query = DB::table('users');
//            $query = $user->scopeSearch($query, $search);
//            $data = $query->select('name', 'id', 'email')->get();
            return \response()->json($user);
        }
    }

    public function testing(Request $request)
    {
//        $cmsInfo = [
//            'moduleTitle' => __("Access Control"),
//            'subModuleTitle' => __("User Management"),
//            'subTitle' => __("User List")
//        ];
//        $page_limit = 10;
//        $s = 'ss';
//        DB::statement('SET sql_mode = ""');
//        dd(DB::statement('ALTER TABLE `tmp_payment_records`ADD COLUMN `attempts` TINYINT(4) NOT NULL DEFAULT 0 AFTER `session_data`;' ));
//
//        $users = User::where('user_type',2)->orderBy('id','DESC')->get();
//
//        return view('users.testing', compact('users', 'cmsInfo', 'page_limit', 's'));
    }

    public function testingdestroy($id)
    {
//        User::destroy($id);
//        flash(__('The record has been deleted successfully!'), 'success');
//        return back();
    }

    protected function userPasswordChange($id)
    {

        $logger = '';
        $cmsInfo = [
            'moduleTitle' => __("Access Control"),
            'subModuleTitle' => __("User Management"),
            'subTitle' => __("Change User Password")
        ];
        $dynamic_route = route(Config::get('constants.defines.APP_USERS_CHANGEPASSWORD'));
        $user = auth()->user();
        $user->answer_one = $this->customEncryptionDecryption($user->answer_one, config('app.brand_secret_key'), 'decrypt');
        $disabled = '';

        return view('users.change_password', compact('dynamic_route', 'user', 'cmsInfo', 'logger', 'disabled'));
    }

    public function changepassword(Request $request)
    {
        $id = auth()->user()->id;

        $isPUTMethod = (isset($request->_method)
            && Str::upperCase($request->_method) == 'PUT'
            && isset($request->approved_by_checker)
            && $request->approved_by_checker == 'approved_by_checker')
            ? true
            : false;

        if ($request->isMethod('put') || $isPUTMethod) {
            return $this->update($request, $id);
        } else {
            $resendotp = $request->input('resendotp');

            if (isset($resendotp) && !empty($resendotp)) {
                $data = $this->resendOTP($request, $id);
                return view('users.otpform', $data);
            }

            $otp = $request->input('otp');

            if (isset($otp) && !empty($otp)) {
                $this->verifyOTP($request, $id);
            }

            return $this->userPasswordChange($id);
        }
    }

    private function restrictAuthToEdit($userObj)
    {
        $auth = auth()->user();

        if (($auth->id == $userObj->created_by_id) && ($userObj->is_admin_verified == Profile::ADMIN_VERIFIED_PENDING)) {
            return true;
        }

        return false;
    }

    public function secrectQuestion(Request $request)
    {

        if (!BrandConfiguration::allowSecrectQuestionOnAdminPanel()) {
            return redirect()->back();
        }

        $authUser = Auth::user();

        if ($request->isMethod('post')) {
            if ($request->has('type') && $request->type == SecurityImage::SECURITY_QUESTION_IMAGE_UPDATE) {
                $request->merge(['auth_user' => $authUser]);
                [$status_code, $status_description] = (new GlobalUser())->updateUserSecurityImage($request);
                flash(__($status_description), $status_code == ApiService::API_SERVICE_SUCCESS_CODE ? 'success' : 'danger');
                return redirect()->back()->withInput();
            }
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'secrect_question_id' => 'required',
                'answer' => 'required|max:255'
            ]);

            if ($validator->fails()) {
                $description = $validator->errors()->first();
                return back()
                    ->withErrors(__('Old password is incorrect, please, try again'));
            }

            $profileObj = new Profile();
            $status = $profileObj->ValidatePasswordOnly($request->current_password, $authUser->password);

            if ($status != config('constants.SUCCESS_CODE')) {
                flash(__('Old password is incorrect, please try again'), 'danger');
                return redirect()->back()->withInput();
            }

            $data = [
                'question_one' => $request->secrect_question_id,
                'answer_one' => $this->customEncryptionDecryption($request->answer, config('app.brand_secret_key'), 'encrypt')
            ];
            $status = $profileObj->updateUser($authUser->id, $data);

            if (!empty($status->id)) {
                flash(__('Secret question updated successfully'), 'success');
                return redirect()->back();
            }
            flash(__('Something going worng'), 'danger');
            return redirect()->back();
        }
    }

    public function sendWelcomeMailToAdmin($user)
    {
        $encrypted_email = $this->customEncryptionDecryption($user->email, config('app.brand_secret_key'), 'encrypt', true);
        $from = Config('app.SYSTEM_NO_REPLY_ADDRESS');
        $to = $user->email;
        $data['name'] = $user->name;
        $data['admin_panel_link'] = config('app.APP_ADMIN_URL');
        $data['create_password_link'] = config('app.APP_ADMIN_URL') . "/password/create/" . $encrypted_email;
        $data['is_sent_email_notification'] = true;
        $data['userObj'] = $user;
        $data['user_type'] = $user->user_type;
        $language = $this->getLang($user);
        $template = "admin.welcome";

        //out_going_email
        $this->setGNPriority(OutGoingEmail::PRIORITY_HIGH);
        $this->sendEmail($data, "Admin_Welcome_Mail", $from, $to, "", $template, $language);
    }

    public function showClientIdClientSecret(Request $request)
    {
        $authUserObj = Auth::user();
        $request->merge(['type' => UserFormSectionPermissions::TYPE_USER_API_CREDENTIALS, 'allowed_user_id' => $authUserObj->id ?? '']);
        list($status_description, $status_code) = (new AdminMakerCheckerService())->makerCheckherAction($request, $authUserObj, UserFormSectionPermissions::TYPE_USER_API_CREDENTIALS);
        flash(__($status_description), $status_code == ApiService::API_SERVICE_SUCCESS_CODE ? 'success' : 'danger');
        return redirect()->back();
    }
}
