<?php
namespace App\Http\Controllers\Traits;
use App\User;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\Brand\Configuration\Frontend\FrontendAdmin;
use common\integration\Utility\Arr;
use DB;
use App\Models\Module;
use Illuminate\Support\Facades\Auth;
use Session;
use Config;
use common\integration\BrandConfiguration;
use common\integration\ManageLogging;

trait PermissionUpdateTreait
{
    public function getPermissionList($user_id){
        $SUPER_SUPER_ADMIN_ID = Config::get('constants.defines.SSADMIN_ID');
        $sql = 'SELECT  users.id AS user_id,users.permission_version,pages.id AS page_id, pages.name AS page_name,
                        user_usergroups.usergroup_id AS usergroup_id,
                                 modules.id AS module_id,sub_modules.controller_name,sub_modules.name AS submodule_name, sub_modules.id
                                 AS submodule_id,pages.method_name,pages.method_type,sub_modules.default_method  FROM users
					INNER JOIN user_usergroups ON users.id = user_usergroups.user_id
					INNER JOIN usergroups ON user_usergroups.usergroup_id = usergroups.id
					INNER JOIN usergroup_roles ON usergroups.id = usergroup_roles.usergroup_id
					INNER JOIN roles ON usergroup_roles.role_id = roles.id
					INNER JOIN role_pages ON roles.id = role_pages.role_id
					INNER JOIN pages ON role_pages.page_id = pages.id
					INNER JOIN sub_modules ON pages.sub_module_id = sub_modules.id
					INNER JOIN modules ON sub_modules.module_id = modules.id
					WHERE  users.id = '.$user_id;

        $permissions = DB::select($sql);

        if(empty($permissions)){
            (new ManageLogging)->createLog([
                'action' => 'PERMISSION_LOG',
                'message' => 'PERMISSION LIST IS EMPTY',
                'user_id' => auth()->check() ? auth()->user()->id : '',
                'user_email' => auth()->check() ? auth()->user()->email : '',
            ]);
        }

        if(!empty($permissions)){
//        if ($user_id == $SUPER_SUPER_ADMIN_ID) {
//             $modules = Module::with('submodules', 'submodules.pages')->get();
//        } else {
//             $modules = Module::with('submodules', 'submodules.pages')->get();
//        }


        //  $hide_menu_ids = config('constants.defines.IGNORABLE_MODULES');
        //  if(empty($hide_menu_ids) && count($hide_menu_ids)==0){
        //      $hide_menu_ids = [];
        //  }

        list($hide_module_ids, $hide_sub_module_ids, $hide_page_ids) = $this->hideModulesSubModules();
        //  $modules = Module::with('submodules', 'submodules.pages')->whereNotIn('id', $hide_menu_ids)->get();

        $modules = Module::with(['submodules' => function ($query) use ($hide_sub_module_ids){
            if(!empty($hide_sub_module_ids)){
                $query->whereNotIn('id',$hide_sub_module_ids);
            }
        },'submodules.pages' => function ($query) use ($hide_page_ids){
            if(!empty($hide_page_ids)){
                $query->whereNotIn('id',$hide_page_ids);
            }
        }])->whereNotIn('id', $hide_module_ids)->get();

         Session::put('modules', $modules);
         Session::put('user_permission', $permissions);

         $permittedRouteName = $this->getPermittedPageRouteName($permissions);
         Session::put('permittedRouteNames', $permittedRouteName);

         // Session::put('permission_version',$permissions[0]->permission_version);
            Session::put('permission_version', current($permissions)->permission_version);
        }
         return $permissions;

    }

    public function hideModulesSubModules($only_role_page_permission = false, $hide_page_permission_from_merchant_area = false)
    {
        $hide_menu_ids = $hide_sub_modules_ids = $hide_page_ids = [];

        // MODULES
        $permissionSettingModule = config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.APP_PERMISSION_SETTINGS');

        $hide_menu_ids = array_merge($hide_menu_ids, [
            $permissionSettingModule,
        ]);

        if(!empty($only_role_page_permission)){
            if (in_array($permissionSettingModule, $hide_menu_ids)) {
                unset($hide_menu_ids[array_search($permissionSettingModule, $hide_menu_ids)]);
            }
        }

        if(!BrandConfiguration::isWalletPaymentExist()){
            $hide_menu_ids = array_merge($hide_menu_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.APP_OPERATORS_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.APP_CASHOUTS_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.APP_B2B_PAYMENT_INDEX'),
            ]);
        }

        if(!BrandConfiguration::allowCardService()){
            $hide_menu_ids = array_merge($hide_menu_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.APP_CARDS_INDEX'),

            ]);
        }

        if(!BrandConfiguration::call([Mix::class, 'allowTerminalOnMerchantPanel'])){
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_TERMINAL'),
            ]);
        }

        if (!BrandConfiguration::isAllowMerchantMonthlyFee()){
            $hide_menu_ids = array_merge($hide_menu_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.MERCHANT_MONTHLY_FEE_INDEX')
            ]);
        }
        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowCashback'])){
            $hide_menu_ids = array_merge($hide_menu_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.APP_CASHBACK_INDEX')
            ]);
        }

        // SUB MODULES
        $adminMakerCheckerSM = config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_ADMIN_MAKER_CHECKER');

        $hide_sub_modules_ids = [
            config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_BANK_ACCOUNT_INDEX'),
            $adminMakerCheckerSM
        ];

        if(empty(BrandConfiguration::call([Mix::class, 'isAllowUserAccountActivationHistory']))){
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MERCHANT_ACTIVATION_REPORTS_INDEX'),
            ]);
        }

        if(!empty($only_role_page_permission)){
            if (BrandConfiguration::allowedAdminMakerCheckerBrand()) {
                if (in_array($adminMakerCheckerSM, $hide_sub_modules_ids)) {
                    unset($hide_sub_modules_ids[array_search($adminMakerCheckerSM, $hide_sub_modules_ids)]);
                }
            }
        }
        if (BrandConfiguration::call([BackendMix::class, 'isAllowCustomCashback'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CASHBACK_CHANNEL'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CASHBACK_ENTITY'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CASHBACK_TIME_MANAGEMENT'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CASHBACK_CALCULATION'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_RULE_MANAGEMENT')
            ]);
        }
        else{
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CUSTOM_CASHBACK')
            ]);
        }

        if(!BrandConfiguration::allowSessionTimeDynamic()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_SESSION_TIMEOUT_SETTING')
            ]);
        }

        if (!BrandConfiguration::call([BackendAdmin::class, 'isAllowCustomBulkCommission'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.MERCHANT_CUSTOM_BULK_COMMISSION'),
            ]);;
        }

        if(!BrandConfiguration::allowLoginBlockTime()){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
            [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_BLOCK_SETTING')
            ]);
        }
        if(!BrandConfiguration::isAllowMerchantSettlementReports()){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MERCHANT_SETTLEMENT_REPORT_INDEX')
                ]);
        }


        if(!BrandConfiguration::allowPayBillSubModule()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_PAIDBILLS_INDEX')
            ]);
        }

        if (!BrandConfiguration::allowWetSignedMerchantReprotSubModule()) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_WET_SIGNED_MERCHANT_REPORT_INDEX')
            ]);
        }

        if(!BrandConfiguration::allowLoginSessions()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_LOGIN_SESSIONS')
            ]);
        }

        if(!BrandConfiguration::isWalletPaymentExist()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_WITHDRAWAL_BANK_NAME_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_WALLET_ANNOUNCEMENT_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CUSTOMERS_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CONTENTS_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_WITHDRAWALS_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_DEPOSITS_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MONEY_TRANSFER_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_USER_ANALYTICS_INDEX'),
                //config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_WALLETS_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_IDENTIFICATION_COMMISSIONS_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_AUTOMATIC_REPORTS_INDEX'),
            ]);
        }

        if(!BrandConfiguration::isAllowedFinflowService()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.FINFLOW_SETTINGS_INDEX'),
            ]);
        }


        if(BrandConfiguration::hideCurrencyExchangeRate()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CURRENCY_EXCHANGE_RATES_INDEX'),
            ]);
        }

        if(BrandConfiguration::hideOnBoundApis()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MERCHANT_ONBOARDING_API_SETTING'),
            ]);
        }

        if(!BrandConfiguration::call([Mix::class, 'isAllowedOnboardingPanel'])){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_SALERS_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_SALE_ANNOUNCEMENT'),
            ]);
        }

        if(!BrandConfiguration::isAllowStaticContent()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_STATIC_CONTENT_INDEX')
            ]);
        }

         if(!BrandConfiguration::isAllowedIKSMerchant()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.IKS_MERCHANT_INDEX')
            ]);
        }


        // SMS LOG SHOULD HIDE FOR NOW IT WILL BE HIDE FOR ALL
        $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
            config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_SMS_LOGS_INDEX')
        ]);

        //if(!BrandConfiguration::allowSmsLogs()){
        //    $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
        //        config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_SMS_LOGS_INDEX')
        //    ]);
        //}

        if(!BrandConfiguration::userLoginAlertSettings()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_USER_LOGIN_ALERT_SETTING_INDEX')
            ]);
        }

        if(BrandConfiguration::hideManagementContents()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CONTENTS_INDEX')
            ]);
        }

        if(!BrandConfiguration::isAllowedServiceCredentials()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_SERVICE_CREDENTIAL_MENU')
            ]);
        }
        if (!BrandConfiguration::isAllowMerchantMonthlyFee()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids,[
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.MERCHANT_FEE_LIST')
                ]);
        }

        if (!BrandConfiguration::isAllowImportExportMerchantPosCommission()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.MERCHANT_POS_COMMISSION_LIST')
            ]);
        }

        if (!BrandConfiguration::allowSimCardBlock()){
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_USERS_SIM_BLOCK')
            ]);
        }

        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowPosPfIntegration'])){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MERCHANT_BANK_REGISTRATION_INDEX')
            ]);
        }

        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowCommercialCardCommissionSetting'])){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_IDENTIFICATION_COMMERCIAL_CARD_SETTING_INDEX')
            ]);
        }

        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowMerchantInvoiceSummaryReport'])){
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MERCHANT_INVOICE_SUMMARY_REPORT_INDEX')
            ]);
        }

        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowPosCommissionSummaryReport'])){
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_POS_DISSOLUTION_AND_BANK_COMMISSION_REPORT_INDEX')
            ]);
        }

        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowPosWiseSummaryReport'])){
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_POS_WISE_SUMMARY_REPORT_INDEX')
            ]);
        }

        if (!BrandConfiguration::call([Mix::class, 'isAllowedInstallmentTransactionReport'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_INSTALLMENT_TRANSACTION_REPORT_INDEX')
            ]);
        }


//        if (!BrandConfiguration::call([BackendAdmin::class, 'isAllowAllTransaction'])) {
//            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
//                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_ALL_TRANSACTION_INDEX')
//            ]);
//        }

        if (!BrandConfiguration::call([BackendAdmin::class, 'isAllowPaymentTransaction'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_PAYMENT_TRANSACTION_INDEX')
            ]);
        }


        // PAGES
        if(!BrandConfiguration::allowedAdminMakerCheckerBrand()){
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.MAKER_CHEKER_APPROVE'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.MAKER_CHEKER_REJECT')
            ]);
        }

        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowCashback'])){
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_OPERATORS_CASHBACK')
            ]);
        }

        if(BrandConfiguration::call([BackendAdmin::class, 'isAllowCommercialCardCommissionSetting'])){
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_SINGLE_PAYMENT_COMMISSION_CREATE_COMMERCIAL_CARD')
            ]);
        }

        if(BrandConfiguration::isHideAddCard()){

            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_BLOCK_CC_CREATE'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_BLOCK_CC_EDIT'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_BLOCK_CC_SHOW'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_BLOCK_CC_DELETE'),
            ]);

        }

        if(BrandConfiguration::isHideRecurringPanel()){

            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_RECURRING_PLAN_VIEW'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.MERCHANT_RECURRING_PLAN_ADD_RECURRING'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.MERCHANT_RECURRING_PLAN_UPDATE_RECURRING'),
            ]);

        }

        if(!BrandConfiguration::isWalletPaymentExist()){
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_B2C'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_B2B'),
            ]);
        }

        if(!BrandConfiguration::allowRestrictFilterForMerchantAndUsers() || $hide_page_permission_from_merchant_area){
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_USERS_HIDDEN_MERCHANT'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_USERS_MODIFY_HIDDEN_MERCHANT'),
            ]);
        }

        if (!BrandConfiguration::allowVirtualCard())
        {
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_VIRTUAL_CARD')
            ]);
        }

         if (!BrandConfiguration::allowMerchantCustomizedCost()) {
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_CUSTOMIZED_COST')
            ]);
        }

         if (BrandConfiguration::hideExchangeHistory()) {
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_EXCHANGE')
            ]);
        }
        if (!BrandConfiguration::isAllowMerchantMonthlyFee()){
            $hide_page_ids = array_merge($hide_page_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.MERCHANT_FEE_LIST_INDEX')
            ]);
        }

        if (!BrandConfiguration::isAllowImportExportMerchantPosCommission()){
            $hide_page_ids = array_merge($hide_page_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.MERCHANT_POS_COMMISSION_INDEX')
            ]);
        }


        /*
         * Transaction Reversals for wallet users
         */
        if (!User::isEnabledReverseProcess(User::CUSTOMER)) {
            $hide_page_ids = array_merge($hide_page_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.REVERSE_WITHDRAWAL_USER'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.REVERSE_DEPOSIT_USER'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.REVERSE_B2C_WALLET')
            ]);
        }
        /*
         * Transaction Reversals for merchant users
         */
        if (!User::isEnabledReverseProcess(User::MERCHANT)) {
            $hide_page_ids = array_merge($hide_page_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.REVERSE_WITHDRAWAL_MERCHANT'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.REVERSE_DEPOSIT_MERCHANT'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.REVERSE_B2C_BANK'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.REVERSE_B2B')
            ]);
        }

        if(!BrandConfiguration::call([FrontendAdmin::class, 'shouldAllowCompactFraudManagement'])){
            $hide_page_ids = array_merge($hide_page_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_FRAUD_MANAGEMENT_VPOS_MONITORING_PHYSICAL_POS_LIST'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_FRAUD_MANAGEMENT_VPOS_MONITORING_WALLET_LIST'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_FRAUD_MANAGEMENT_VPOS_MONITORING_DPL_LIST'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_FRAUD_MANAGEMENT_VPOS_MONITORING_WALLET_VIEW'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_FRAUD_MANAGEMENT_VPOS_MONITORING_WALLET_MODIFY_AML'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_FRAUD_MANAGEMENT_VPOS_MONITORING_PHYSICAL_POS_EXPORT'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_FRAUD_MANAGEMENT_VPOS_MONITORING_DPL_EXPORT'),
            ]);
        }


        if(!BrandConfiguration::isAllowManageBinRange()){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_BIN_RANGE_MANAGEMENT_INDEX')
                ]);
        }

        if(!BrandConfiguration::allowMerchantCustomizedCost()){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.MERCHANT_CUSTOMIZE_COST_LIST')
                ]);
        }

        if(!BrandConfiguration::call([FrontendAdmin::class,'showAwaitingPhysicalPosTransactionPage'])) {
            $hide_page_ids = array_merge($hide_page_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_ALL_TRANSACTION_PHYSICAL_POS_PAGE'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_PAYMENT_TRANSACTION_PHYSICAL_POS_PAGE'),
            ]);
        }

        if(!BrandConfiguration::isAllowedPanelWiseAml()){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_WALLET_MONITORING')
                ]);
        }

        if(!BrandConfiguration::isEnabledCronjobSetting()){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CRONJOB_SETTINGS')
                ]);
        }
        if (!BrandConfiguration::allowPosAnalytics()) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_POS_ANALYTICS_INDEX'),
            ]);
        }

        if(!BrandConfiguration::isEnabledReconciliationReport()){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_DAILY_RECONCILIATION_REPORT')
                ]);
        }

        if(! BrandConfiguration::isAllowedBTrans()){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.BTRANS_REPORT_HISTORY'),
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.BTRANS_FAILED_REPORT_INDEX')
                ]);
        }

        if(! BrandConfiguration::call([Mix::class, "allowAppVersionControl"])){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_VERSION')
                ]);
        }



        if(BrandConfiguration::isHideDepositMethod()){
            $hide_sub_modules_ids = array_merge( $hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_DEPOSITS_METHODS_INDEX')
                ]);
        }

        if (BrandConfiguration::isHideWithdrawalMethod()) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_WITHDRAWAL_METHODS_INDEX')
                ]);
        }

        if (BrandConfiguration::isHidePayTokens()) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_PAYMENT_TOKEN_INDEX')
                ]);
        }

        if(!BrandConfiguration::hideBankReportConfiguration()){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.BANK_REPORT_CONFIGURATION'),
            ]);
        }

        if(!BrandConfiguration::call([BackendMix::class, 'showFinancializationReport'])){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.FINANCIALIZATION_REPORT'),
            ]);
        }


        if(!BrandConfiguration::isNewFlowIntegratorCommission()){
            $hide_page_ids = array_merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANT_INTEGRATOR_COMMISSION_SETTING'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANT_INTEGRATOR_COMMISSION_SETTING_STORE'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANT_INTEGRATOR_COMMISSION_SETTING_UPDATE'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANT_INTEGRATOR_COMMISSION_SETTING_DELETE'),
            ]);
        }

        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowedSettlementWalletLog'])){
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_SETTLEMENT_WALLETLOG'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_SETTLEMENT_WALLETLOG_EXPORT'),
            ]);
        }
        if(!BrandConfiguration::call([BackendAdmin::class, 'allowMerchantBatchService'])){
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_MERCHANTS_BATCH_EXPORT'),
            ]);
        }
        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowUserLoginAlertSettings'])){
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_USER_LOGIN_ALERT_SETTING_UPDATE'),
            ]);
        }

        if(!BrandConfiguration::call([BackendMix::class, 'isAllowToCreateBulkTerminal'])){
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_BULK_TERMINAL_CREATE'),
            ]);
        }

        if(!BrandConfiguration::isLicenseOwnerTenant()) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.TENANTS_INDEX')
                ]);
        }
        if ( ! BrandConfiguration::call([Mix::class, 'isEnabledReconcileReport']) ) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids,
                [
                    config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_DAILY_RECONCILIATION_REPORT_RECONCILEINDEX')
                ]);
        }
        //Sub Module can be removed later if no one complains
        $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
            config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_AUTOMATIC_REPORTS_INDEX')
        ]);

        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowApiPackageService'])){
            $hide_menu_ids = array_merge($hide_menu_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_MODULES.API_PACKAGE_SERVICE')
            ]);
        }
        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowPackageService'])){

            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_IDENTIFICATION_PACKAGE_INDEX'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_IDENTIFICATION_PACKAGE_CHANNEL_INDEX')
            ]);
        }
        if (!BrandConfiguration::call([Mix::class, 'isAllowedMerchantApiV2'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_TRANSACTION_INVOICE'),
            ]);
        }
        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowMerchantTerminalSubMenu' ])){
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MERCHANT_TERMINAL')
            ]);
        }

        if (!BrandConfiguration::call([BackendAdmin::class, 'isAllowedEarlySettlement'])) {
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_EARLY_SETTLEMENT'),
            ]);

            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_ALL_EARLY_SETTLEMENT_INDEX')
            ]);
        }

        if (!BrandConfiguration::call([Mix::class, 'isAllowTerminalParameterSettings'])) {
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_BIN_MAPPING'),
            ]);
        }

        if (!BrandConfiguration::call([BackendAdmin::class, 'isAllowedPhysicalPOSPFDefinitionReports'])) {
            $hide_sub_modules_ids = array_merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_PHYSICAL_POS_PF_DEFINITION_REPORTS')
            ]);
        }

        if(!BrandConfiguration::call([Mix::class, 'isAllowTerminalParameterSettings'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MANAGEMENT_PARAMETER_SETTINGS_INDEX')
            ]);
        }

        if(!BrandConfiguration::call([FrontendAdmin::class, 'showWaitingPhysicalPosTransactionMenu'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_CUSTOMERS_WAITING_PHYSICAL_TRANSACTION_INDEX')
            ]);
        }

        if(!BrandConfiguration::call([FrontendAdmin::class, 'shouldShowMerchantPosPFSettingsMenu'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_MERCHANT_POS_PF_SETTINGS')
            ]);
        }


        if(!BrandConfiguration::call([Mix::class, 'isAllowNewFlowFinalizationReport'])){
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_REPORTS_AND_ANALYTICS_ACCOUNT_STATEMENT_NEW'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_REPORTS_AND_ANALYTICS_FINANCIALIZATION_REPORT_NEW')
            ]);
        }

        if(!BrandConfiguration::call([Mix::class, 'isAllowMerchantBinRedirectionReport'])) {
            $hide_page_ids = Arr::merge($hide_page_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_REPORT_BIN_REDIRECTION'),
            ]);
        }
        if(!BrandConfiguration::call([BackendAdmin::class, 'isAllowApiPaxService'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_API_PAX_SERVICE'),
            ]);
        }
        if(!BrandConfiguration::call([Mix::class,'showSpecificFieldsAsAdminMakerCheckerPermission'])) {
            $hide_page_ids = array_merge($hide_page_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_CONFIGURATION_SHOW_HASHKEY_PASSWORD'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_ACCESS_CONTROL_SHOW_CLIENT_ID_CLIENT_SECRET'),
            ]);
        }

        if(BrandConfiguration::call([Mix::class, 'isAllowedFraudRuleStructureFeature'])) {
            $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, [
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_ADMIN_WALLET_RULES'),
            ]);
        }

//        if (BrandConfiguration::call([BackendAdmin::class, 'hideAllTransactionDuplicateList'])) {
//            $hide_page_ids = Arr::merge($hide_page_ids, [
//                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_ALL_TRANSACTION_INDEX'),
//            ]);
//        }
        if(!BrandConfiguration::call([Mix::class, 'isAllowPosProject'])){
            $hide_page_ids = array_merge($hide_page_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_IDENTIFICATION_GENERAL_POS_PROJECT_ADD'),
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_IDENTIFICATION_GENERAL_POS_PROJECT_EDIT'),
            ]);
        }
        if(!BrandConfiguration::call([Mix::class, 'isAllowDownloadAllMerchantPosWise'])){
            $hide_page_ids = array_merge($hide_page_ids,[
                config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES_PAGES.APP_MERCHANTS_ALL_MERCHANT_REPORT_POS_WISE'),
            ]);
        }

        /*
         * if wallet panel is diabled for a brand, then, all menus
         * specifically related with wallet panel should be hide as well
         */
        $this->walletPanelSpecificMenuToHide($hide_menu_ids, $hide_sub_modules_ids, $hide_page_ids);

        $hide_menu_ids = collect($hide_menu_ids)->filter();
        $hide_sub_modules_ids = collect($hide_sub_modules_ids)->filter();
        $hide_page_ids = collect($hide_page_ids)->filter();

        return [
            $hide_menu_ids,
            $hide_sub_modules_ids,
            $hide_page_ids,
        ];
    }

    private function getPermittedPageRouteName($permissions){
        $permittedRouteName = [];
        foreach ($permissions as $key=>$permission){
            $permittedRouteName[$key] = strtolower(str_replace(" ", "", $permission->submodule_name)) . "." . strtolower($permission->method_name);
        }
        return $permittedRouteName;
    }


        public function getSubModules($request_controller){


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


        public function getPages($request_controller){

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

    public function walletPanelSpecificMenuToHide (&$hide_menu_ids, &$hide_sub_modules_ids, &$hide_page_ids): void
    {
        if (!empty(BrandConfiguration::walletPanelRedirection())
            && BrandConfiguration::call([FrontendAdmin::class, 'shouldHideWalletPanelSpecificMenuInAdminPanel'])
        ) {
            $menu_list = config('constants.WALLET_PANEL_SPECIFIC_ADMIN_PANEL_MENU');

            if (!empty($menu_list['MODULE_IDS']) && Arr::isOfType($menu_list['MODULE_IDS'])) {
                $hide_menu_ids = Arr::merge($hide_menu_ids, $menu_list['MODULE_IDS']);
            }
            if (!empty($menu_list['SUB_MODULE_IDS']) && Arr::isOfType($menu_list['SUB_MODULE_IDS'])) {
                $hide_sub_modules_ids = Arr::merge($hide_sub_modules_ids, $menu_list['SUB_MODULE_IDS']);
            }
            if (!empty($menu_list['PAGE_IDS']) && Arr::isOfType($menu_list['PAGE_IDS'])) {
                $hide_page_ids = Arr::merge($hide_page_ids, $menu_list['PAGE_IDS']);
            }
        }
    }

}
