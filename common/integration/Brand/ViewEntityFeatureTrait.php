<?php

namespace common\integration\Brand;

use common\integration\Utility\Arr;

trait ViewEntityFeatureTrait
{
    public static function isPLNewMerchantTemplate()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isPLNewWalletTemplate(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PL')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function passwordPageUserAgreement(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PL')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function allowTenDigitPhoneNumber(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PL')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowedIntegratorMerchantCommission(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PB')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isCToCMoneyTransferDisclaimerDisabled (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function getMerchantAllActivitiesModuleName(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.MOP'),
        ];
        if(Arr::isAMemberOf($brand_code, $brand_list)) {
            return "Homepage";
        }
        return "Main";
    }

    public static function getEmailChangeVerificationBody(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];
        if(Arr::isAMemberOf($brand_code, $brand_list)) {
            return [
                "body" => "You can continue to use your account by verifying your email address.",
                "body_tr" => "E-posta adresini doğrulayarak hesabını kullanmaya devam edebilirsin."
            ];
        }
        return [
            "body"=>"Click To Verify Your Email",
            "body_tr"=>"E-postanızı doğrulamak için tıklayın"
        ];
    }

    public static function isMerchantAnnouncementEditable (): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PB')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function showBonusInstallmentInReceipt(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isHidePhoneNumberSendMoney(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function changeRequestMoneyReceiverSuccessMailContent(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        if(Arr::isAMemberOf($brand_code, $brand_list)){
            return [
                'content_en' => 'request money',
                'content_tr' => 'para talebi'
            ];
        }
        return [
            'content_en' => 'money transfer',
            'content_tr' => 'para transferi talebinde'
        ];
    }
    public static function hideBankReportConfiguration(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isChangeSubModuleTitleForFraudRules(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.YP'),
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];
        if(Arr::isAMemberOf($brand_code, $brand_list)){
            return 'V-POS Rules Management';
        }
        return 'Rule Management';
    }


    public static function skipWalletLoginOnBrandedDPLPayment()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.YP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function isShowCustomerNumberInReceipt()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function showProductTitleAndDescriptionOnDplReceipt() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
	        config('constants.BRAND_NAME_CODE_LIST.PIN'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function isWithdrawalRequestsEmailContentChange(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        if(Arr::isAMemberOf($brand_code, $brand_list)){
            return [
                'tr_content' => 'Para çekim işlem talepleri ekteki dosyadadır.',
                'en_content' => 'Withdrawal transaction requests are in the attached file.'
            ];
        }
        return [
            'tr_content' => 'Ekteki çekim işlemlerinin yapılması uygun mudur?',
            'en_content' => 'Is it appropriate to do the attached shooting procedures?'
        ];
    }

    public static function showCustomerTckninPaidBill() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.PL'),
          config('constants.BRAND_NAME_CODE_LIST.PP'),
          config('constants.BRAND_NAME_CODE_LIST.SR'),
          config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isTransactionMoneyFlowSignShow(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function getB2CPaymentSmsContent($data)
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PP'),
            config('constants.BRAND_NAME_CODE_LIST.SR'),
            config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];

        $brand_name = config('brand.name');

        if(Arr::isAMemberOf($brand_code, $brand_list)){
            return [
                'en_content' => "Your $brand_name wallet was deposited with {$data['amount']} {$data['currency_code']} on {$data['created_at']}. B003",
                'tr_content' => "$brand_name cüzdanınıza {$data['created_at']} tarihinde {$data['amount']} {$data['currency_code']} yüklenmiştir. B003"
            ];
        }

        return [
            'en_content' => "{$data['amount']} {$data['currency_code']} from {$data['merchant_name']} was sent to your $brand_name wallet on {$data['created_at']}.",
            'tr_content' => "{$data['merchant_name']} firmasından {$data['created_at']} tarihinde $brand_name cüzdanınıza {$data['amount']} {$data['currency_code']} gönderilmiştir."
        ];
    }
    public static function allowAuthCodeColumnInAllTransaction() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    public static function isChangewalletAccountInfoContent(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isHideSocialMediaFooter()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FL'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }



}