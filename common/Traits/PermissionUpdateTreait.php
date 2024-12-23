<?php

namespace App\Http\Controllers\Traits;

use App\Models\Profile;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendMerchant;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\Brand\Configuration\Frontend\FrontendMerchant;
use common\integration\GlobalMerchant;
use common\integration\Utility\Arr;
use DB;
use Config;
use Session;
use App\Models\Module;
use App\Models\Merchant;
use App\Models\MerchantSettings;
use common\integration\ManageLogging;
use common\integration\BrandConfiguration;

trait PermissionUpdateTreait
{
    public function getPermissionList($user_id)
    {
        $merchant = new Merchant();
        $merchantObj = $merchant->getMerchantByUserId(\auth()->user()->merchant_parent_user_id);

        $ignorableRoutes = [];
        if (!empty($merchantObj)) {
            $merchantSettings = (new MerchantSettings())->getMerchantSettingByMerchantId($merchantObj->id, [
                'is_allow_dpl', 'is_allow_manual_pos', 'is_allow_one_page_payment', 'is_allow_virtual_card'
            ]);

            if (
                (
                    !empty($merchantSettings) &&
                    !($merchantSettings->is_allow_dpl == 1 || $merchantSettings->is_allow_one_page_payment == 1)
                )
                ||
                (
                    $merchantObj->type == Merchant::OXIVO_MERCHANT
                )
            ) {
                $ignorableRoutes = array_merge($ignorableRoutes, [
                    config('constants.defines.APP_DPL_INDEX'),
                    config('constants.defines.APP_DPL_STORE'),
                    config('constants.defines.APP_DPL_LINK'),
                    config('constants.defines.APP_DPL_SETTING_INDEX'),
                    config('constants.defines.APP_DPL_SETTING_EDIT'),
                    config('constants.defines.APP_DPL_CREATED_LINKS_INDEX'),
                ]);
            }

            if (
                (
                    !empty($merchantSettings) && $merchantSettings->is_allow_manual_pos != 1
                )
                ||
                (
                    $merchantObj->type == Merchant::OXIVO_MERCHANT
                )
            ) {
                $ignorableRoutes = array_merge($ignorableRoutes, [
                    config('constants.defines.APP_MANUAL_POS_INDEX'),
                    config('constants.defines.APP_MANUAL_POS_MAKE_PAYMENT')
                ]);
            }
            
            if (!empty($merchantSettings) && $merchantSettings->is_allow_virtual_card != MerchantSettings::ACTIVE) 
            {
                $ignorableRoutes = array_merge($ignorableRoutes, [
                    config('constants.defines.APP_VIRTUAL_CARD_INDEX'),
                    config('constants.defines.APP_VIRTUAL_CARD_LIST'),
                    config('constants.defines.APP_VIRTUAL_CARD_DEPOSIT')
                ]);
            }

            if ($merchantObj->type == Merchant::OXIVO_MERCHANT) {
                $ignorableRoutes = array_merge(
                    $ignorableRoutes,
                    [
                        config('constants.defines.APP_MERCHANT_SETTING_API_INDEX'),
                        config('constants.defines.APP_MERCHANT_SETTING_API_EDIT'),
                        config('constants.defines.APP_MERCHANT_SETTING_API_GET_APPSECRET'),
                        config('constants.defines.APP_MERCHANT_SETTING_PAYMENT_INDEX'),
                        config('constants.defines.APP_MERCHANT_SETTING_PAYMENT_EDIT'),
                        config('constants.defines.APP_MERCHANT_SETTING_INTEGRATION_INDEX'),

                        config('constants.defines.APP_C2B_INDEX'),
                        config('constants.defines.APP_C2B_EXPORT'),

                        config('constants.defines.MERCHANT_RECURRING_PLAN_INDEX'),
                        config('constants.defines.MERCHANT_RECURRING_PLAN_ADD_RECURRING'),
                        config('constants.defines.MERCHANT_RECURRING_PLAN_UPDATE_RECURRING'),
                        config('constants.defines.MERCHANT_RECURRING_PLAN_CARD_INDEX'),
                    ]
                );
            }

            // This will hide/show Money Transfer in merchant panel  Send Money -> Money Transfer
            if (!empty($merchantObj) && $merchantObj->is_allow_pay_bill != Merchant::MERCHANT_SEND_MONEY_B2U_ACTIVE)
            {
                $ignorableRoutes = Arr::merge($ignorableRoutes, [
                  config('constants.defines.APP_MONEY_TRANSFER_INDEX')
                ]);
            }
        }

        $SUPER_SUPER_ADMIN_ID = Config::get('constants.defines.SSADMIN_ID');

        $permissions = $this->getAllPermission($user_id, Profile::MERCHANT);

        if (!empty($permissions)) {
            list($permissions, $permittedRouteName) = $this->getPermittedPageRouteName($permissions, $ignorableRoutes);

            /// IF MERCHANT IS INACTIVE , GET ONLY PERMITED MODULES AND SUBMODULES
            //            $merchantObj->status = Merchant::MERCHANT_INACTIVE;
            if (!empty($merchantObj) && Arr::keyExists($merchantObj->status, GlobalMerchant::getMerchantDeactiveStatusList())) {
                list($unique_modules, $unique_sub_modules, $unique_pages) = $this->getModuleSubmoduleAssocForInactiveMerchant($permissions);
            } else {
                $permissionCollection = collect($permissions);
                $unique_modules = $permissionCollection->unique('module_id')->pluck('module_id');
                $unique_sub_modules =  $permissionCollection->unique('submodule_id')->pluck('submodule_id');
                $unique_pages = $permissionCollection->unique('page_id')->pluck('page_id');
            }

            list($hide_module_ids, $hide_sub_module_ids) = $this->hideModulesSubModules();
            $dpl_module_id = '';
            if(!GlobalMerchant::isMarketplaceMerchant($merchantObj)){
                // $hide_sub_module_ids = config()->get('constants.defines.NOT_SHOW_SUBMODULES_TO_INDIVIDUAL_MERCHANT');
                $hide_sub_module_ids = array_merge( $hide_sub_module_ids,[
                    config()->get('constants.defines.NOT_SHOW_SUBMODULES_TO_INDIVIDUAL_MERCHANT'),
                    config()->get('constants.defines.SHOW_ANNOUNCEMENTS_ONLY_MARKETPLACE_MERCHANT'),
                ]); 
            }
           if(!GlobalMerchant::isAlowDPLModule($merchantSettings)){
               $dpl_module_id = config()->get('constants.defines.DPL_MODULE_ID');
            }
            $modules = Module::with(['submodules.pages' => function ($query) use ($unique_pages) {
                $query->whereIn('id', $unique_pages);
            }])->with(['submodules' => function ($query) use ($unique_sub_modules, $hide_sub_module_ids) {
                $query->whereIn('id', $unique_sub_modules)->whereNotIn('id', $hide_sub_module_ids)->orderBy('sequence', 'ASC');
            }])->whereNotIn('id', $hide_module_ids)
                ->whereIn('id', $unique_modules)->orderBy('sequence', 'ASC');

            if(!empty($dpl_module_id)){
                $modules = $modules->where('id','!=', $dpl_module_id);
            }

            $modules = $modules->get();

            Session::put('modules', $modules);
            Session::put('user_permission', $permissions);

            Session::put('permittedRouteNames', $permittedRouteName);

            // Session::put('permission_version', $permissions[0]->permission_version);
            Session::put('permission_version', current($permissions)->permission_version);

            //commented due to unexpected session removal on merchant role page
//            Session::save();
        }
        return $permissions;
    }

    private function getModuleSubmoduleAssocForInactiveMerchant($permissions){

        $inactiveModuleList = [];
        $inactiveSubModuleList = [];
        $allPagesMerchantList = config('constants.defines.MERCHANT_INFORMATION_PAGE');
        if(!empty($permissions)){
            foreach ($permissions as $permission){
                if(in_array($permission->page_id,$allPagesMerchantList)) {
                    $inactiveModuleList[$permission->page_id] = $permission->module_id;
                    $inactiveSubModuleList[$permission->page_id] = $permission->submodule_id;
                }
            }
        }

        $unique_moudule_ids = array_unique(array_values($inactiveModuleList));
        $unique_sub_moudule_ids = array_unique(array_values($inactiveSubModuleList));

        Session::put('permitted_submodule',$unique_sub_moudule_ids );
        Session::put('permitted_module', $unique_moudule_ids);

        return [$unique_moudule_ids,$unique_sub_moudule_ids,$allPagesMerchantList];

    }

    private function getPermittedPageRouteName($permissions, $ignorableRoutes)
    {
        $permittedRouteName = [];
        $ignorableSubModule = [];
        foreach ($permissions as $key => $permission) {
            $routeName = strtolower(str_replace(" ", "", $permission->submodule_name)) . "." . strtolower($permission->method_name);
            if (in_array($routeName, $ignorableRoutes)) {
                if (!in_array($permission->submodule_id, $ignorableSubModule)) {
                    $ignorableSubModule[] = $permission->submodule_id;
                }
            } else {
                $permittedRouteName[$key] = $routeName;
            }
        }
        $permissions = collect($permissions)->whereNotIn('submodule_id', $ignorableSubModule)->toArray();
        return [$permissions, $permittedRouteName];
    }

    public function getSubModules($request_controller)
    {


        if ($request_controller == "dashboard") {
            return array();
        }
        $current_submodule_arr = array();
        $modules = Session::get('modules');
        if (!empty($modules)) {
            foreach ($modules as $module) {
                foreach ($module->submodules as $submodule) {

                    $submodule['controller_name'] = trim($submodule['controller_name']);
                    //echo $request_controller."=====".$submodule['controller_name']."<br/>";
                    if (strtolower($request_controller) == strtolower($submodule['controller_name'])) {
                        //echo "Sds";exit;
                        $current_submodule_arr = array($submodule['name'] => $submodule['id']);
                        break 2;
                    }
                }//exit;
            }
        }
        return $current_submodule_arr;
    }


    public function getPages($request_controller)
    {

        if ($request_controller == "dashboard") {
            return array();
        }
        $pages_arr = array();
        $current_submodule_arr = $this->getSubModules($request_controller);

        $modules = Session::get('modules');

        if (!empty($modules)) {
            foreach ($modules as $module) {
                foreach ($module->submodules as $submodule) {
                    if (current($current_submodule_arr) == $submodule['id']) {
                        foreach ($submodule['pages'] as $page) {
                            $pages_arr[$page['method_name']] = $page['id'];

                        }
                        break 2;
                    }
                }
            }
        }
        return $pages_arr;
    }

    public function hideModulesSubModules($only_role_page_permission = false){
        $hide_menu_ids = $hide_sub_modules_ids = $hide_page_ids = [];

        // MODULES

        if(BrandConfiguration::hideSendMonyModuleFromMerchantPanel()){
            $hide_menu_ids = array_merge($hide_menu_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.APP_CASHOUT_API_INDEX'),
            ]);
        }

        if(!BrandConfiguration::allowTakePaymentFeature()){
            $hide_menu_ids = array_merge($hide_menu_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.APP_TAKEPAYMENT_INDEX'),
            ]);
        }

        if(!BrandConfiguration::call([Mix::class, 'allowTerminalOnMerchantPanel'])){
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_TERMINAL'),
            ]);
        }



        // SUB MODULES
        if(!BrandConfiguration::isWalletPaymentExist()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_C2B_INDEX'),
            ]);
        }

        if(!BrandConfiguration::allowSendMoneyB2U()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MONEY_TRANSFER_INDEX'),
            ]);
        }


       // SUB MODULES
       if(!BrandConfiguration::allowDplSavedLink()){
          $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
            config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_DPL_SAVED_LINKS_INDEX'),
          ]);
       }
       
       // SUB MODULES
        if (!BrandConfiguration::allowSendAnnouncementToSubMerchant()) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_ANNOUNCEMENT_INDEX'),
            ]);
        }
        // SUB MODULES
        if (BrandConfiguration::hideMerchantPanelWithdrawalMenu()) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_WITHDRAW_INDEX'),
            ]);
        }

        if (BrandConfiguration::hideMerchantPanelDepositMenu()) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_DEPOSIT_INDEX'),
            ]);
        }

        if (!BrandConfiguration::call([BackendMix::class,'isAllowMerchantGroup'])) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
              config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_ACCESS_GROUP_MERCHANT_INDEX'),
            ]);
        }
        // PAGES

       // hide hidden merchant for all brand and sipay merchant panel only
       $hide_page_ids = array_merge($hide_page_ids, [
         config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_USERS_HIDDEN_MERCHANT'),
         config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_USERS_MODIFY_HIDDEN_MERCHANT'),
       ]);

        if (!BrandConfiguration::call([BackendMix::class,'showFinancializationReport'])) {
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.FINANCIALIZATION_MERCHANT_REPORT'),
            ]);
        }

        if (!BrandConfiguration::call([FrontendMerchant::class,'shouldShowAgreementPage'])){
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_AGREEMENT'),
            ]);
        }

        if (!BrandConfiguration::call([BackendMix::class, 'isAllowedMerchantUserTransactionLimit'])) {
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_USERS_FILTER_MONEY_TRANSFER'),
            ]);
        }

        // SUB MODULES
        if (!BrandConfiguration::isAllowedSiteSettingHelpDocument()) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.IGNORE_HELP_GUIDE'),
            ]);
        }
//        dd($hide_sub_modules_ids);

        if (!BrandConfiguration::call([Mix::class, 'isAllowedInstallmentTransactionReport'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_INSTALLMENT_MERCHANT_REPORT_INDEX'),
            ]);
        } else {
            $merchant = new Merchant();
            $merchantObj = $merchant->getMerchantByUserId(\auth()->user()->merchant_parent_user_id);
            $merchantSettings = (new MerchantSettings())->getMerchantSettingByMerchantId($merchantObj->id, ['is_installment_wise_settlement']);

            if (empty($merchantSettings) || $merchantSettings->is_installment_wise_settlement != MerchantSettings::ACTIVE) {
                $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_INSTALLMENT_MERCHANT_REPORT_INDEX'),
                ]);
            }
        }
        if (!BrandConfiguration::call([BackendMerchant::class, 'isAllowPaymentReport'])){
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_PAYMENT_TRANSACTION_INDEX'),
            ]);
        }

        return [
            $hide_menu_ids,
            $hide_sub_modules_ids,
            $hide_page_ids,
        ];
    }


    public function getSalerPermissionList($user_id, $user_type = Profile::SALES_ADMIN){

        $permissions = $this->getAllPermission($user_id,$user_type);

        $ignorableRoutes = [];

        if (!empty($permissions)) {
            list($permissions, $permittedRouteName) = $this->getPermittedPageRouteName($permissions, $ignorableRoutes);

            $permissionCollection = collect($permissions);
            $unique_modules = $permissionCollection->unique('module_id')->pluck('module_id');
            $unique_sub_modules =  $permissionCollection->unique('submodule_id')->pluck('submodule_id');
            $unique_pages = $permissionCollection->unique('page_id')->pluck('page_id');

            //list($hide_module_ids, $hide_sub_module_ids) = $this->hideModulesSubModules();
            $hide_module_ids = $hide_sub_module_ids = [];

            $modules = Module::with(['submodules.pages' => function ($query) use ($unique_pages) {
                $query->whereIn('id', $unique_pages);
            }])->with(['submodules' => function ($query) use ($unique_sub_modules, $hide_sub_module_ids) {
                $query->whereIn('id', $unique_sub_modules)->whereNotIn('id', $hide_sub_module_ids)->orderBy('sequence', 'ASC');
            }])->whereNotIn('id', $hide_module_ids)
                ->whereIn('id', $unique_modules)->orderBy('sequence', 'ASC');

            if(!empty($dpl_module_id)){
                $modules = $modules->where('id','!=', $dpl_module_id);
            }

            $modules = $modules->get();

            Session::put('modules', $modules);
            Session::put('user_permission', $permissions);

            Session::put('permittedRouteNames', $permittedRouteName);

            // Session::put('permission_version', $permissions[0]->permission_version);
            Session::put('permission_version', current($permissions)->permission_version);

        }
        return $permissions;
    }

    public function getAllPermission($user_id, $user_type= Profile::MERCHANT)
    {
        $sql = 'SELECT  users.id AS user_id, users.permission_version, pages.id AS page_id, pages.name AS page_name,
         user_usergroups.usergroup_id AS usergroup_id, modules.id AS module_id,sub_modules.controller_name,sub_modules.name AS submodule_name, sub_modules.id AS submodule_id,pages.method_name,pages.method_type,sub_modules.default_method  FROM users
                INNER JOIN user_usergroups ON users.id = user_usergroups.user_id
                INNER JOIN usergroups ON user_usergroups.usergroup_id = usergroups.id
                INNER JOIN usergroup_roles ON usergroups.id = usergroup_roles.usergroup_id
                INNER JOIN roles ON usergroup_roles.role_id = roles.id
                INNER JOIN role_pages ON roles.id = role_pages.role_id
                INNER JOIN pages ON role_pages.page_id = pages.id
                INNER JOIN sub_modules ON pages.sub_module_id = sub_modules.id
                INNER JOIN modules ON sub_modules.module_id = modules.id
                INNER JOIN usertype_submodules ON sub_modules.id = usertype_submodules.sub_module_id 
                WHERE usertype_submodules.user_type_id = ' . $user_type . ' AND  users.id = ' . $user_id;
        $permissions = DB::select($sql);

        if (empty($permissions)) {
            (new ManageLogging)->createLog([
              'action' => 'PERMISSION_LOG',
              'message' => 'PERMISSION LIST IS EMPTY',
              'user_id' => auth()->check() ? auth()->user()->id : '',
              'user_email' => auth()->check() ? auth()->user()->email : '',
            ]);
        }

        return $permissions;
    }

}
