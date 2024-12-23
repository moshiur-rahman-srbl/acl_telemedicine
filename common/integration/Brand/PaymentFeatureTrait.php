<?php

namespace common\integration\Brand;

use App\Models\Bank;
use common\integration\Utility\Arr;

trait PaymentFeatureTrait
{
    public static function isAllowedForAvoidPendingPayment()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function isAllowedFor_moreFrequentCheckOrderStatus()
    {
        //return true;
        //Now, check order status process max_attempt = 1 for 15 minutes old transactions. For new, it will be checked with max_attempt = 3
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isBrandAllowedCardIssuerNameInGetPosResponse(){
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }


    public static function isBrand_whichIncludesCardNumberInHashKey()
    {
        //return true;
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isBrand_whichDoesntWantRateLimiterForCheckStatus()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
	        config('constants.BRAND_NAME_CODE_LIST.PIN'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);

    }

    public static function isBrand_whichDoesntWantGracePeriodValidationForRefund()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);

    }


    public static function isBrand_whichDoesntWantCCHolderNameParamValidation()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isBrandWhichWantsOriginalMdStatusIn3dModelPendingResponse()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);

    }

    public static function allowMoneyTransferMaxExceedUserCategoryWise(){
        // no use of this method
        // no need this as it is checked before already
        $brand_code = config('brand.name_code');
        $brand_list = [
            // config('constants.BRAND_NAME_CODE_LIST.PL'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isBrand_whichDoesntWantRateLimiterForPaySmart2D()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP')
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);

    }

    public static function isBrandWhichWantsPosBankNameInPaymentResponse() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PB'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);

    }

    public static function isBrandWhichWantsMerchantProductPriceRequired() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);

    }

    public static function isSendPFGroupIDWhenSendPFRecordInactive() {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
	        config('constants.BRAND_NAME_CODE_LIST.PIN'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function shouldSubmit3dForm($bankObj = null) : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list) && !empty($bankObj) && $bankObj->store_type == Bank::STORE_TYPE_3D;
    }

    public static function isSendVisaPFforNestpay(): bool
    {
        return false;
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
	        config('constants.BRAND_NAME_CODE_LIST.PIN'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isBrandForAkbankProvider()
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PC'),
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PIN'),

        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function shouldCheckOrderStatusWithOrderId() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.HP')
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function shouldEnableCommercialCOTOnPos() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
	        config('constants.BRAND_NAME_CODE_LIST.PIN'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    
    public static function shouldApplyRemoteSubMerchantIdForAkbankProvider() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.VP'),
            config('constants.BRAND_NAME_CODE_LIST.PB'),
	        config('constants.BRAND_NAME_CODE_LIST.PIN'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isBrandWantsRemoteTransactionTimeAsSaleTransactionTime() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
	        config('constants.BRAND_NAME_CODE_LIST.PIN'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }
    
    public static function isBrand_WantsZeroInSubMerchantCodeForQnbBank() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isBrand_WhichDoesntWantPosRedirectionForPreAuth() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
	        config('constants.BRAND_NAME_CODE_LIST.PIN'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isAllowBankWisePFId() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
            config('constants.BRAND_NAME_CODE_LIST.FP'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isBrandForNestpaySPAcquiringProvider() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP')

        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function isBrandWhichDoesntWantMerchantTypeManipulationForVakif() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];
        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function shouldApplyIsoToCountryCodeOnPFRecords() : bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
            config('constants.BRAND_NAME_CODE_LIST.SP'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

    public static function shouldApplyMerchantIdForSubMerchantIdOnPFRecords(): bool
    {
        $brand_code = config('brand.name_code');
        $brand_list = [
          config('constants.BRAND_NAME_CODE_LIST.SP'),
          config('constants.BRAND_NAME_CODE_LIST.PM'),
        ];

        return Arr::isAMemberOf($brand_code, $brand_list);
    }

}