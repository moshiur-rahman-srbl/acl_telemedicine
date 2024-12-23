<?php

namespace common\integration;

use App\Http\Controllers\Traits\ResourceContainerTrait;
use App\Models\BtoC;
use App\Models\BinRangeResponse;
use App\Models\BinResponse;
use App\Models\CCPayment;
use App\Models\CurrenciesSettings;
use App\Models\Currency;
use App\Models\DPL;
use App\Models\ImportedTransaction;
use App\Models\Merchant;
use App\Models\MerchantPosPFSetting;
use App\Models\MerchantSettings;
use App\Models\MerchantTerminals;
use App\Models\MerchantWebHookKeys;
use App\Models\PaidBill;
use App\Models\Pos;
use App\Models\Profile;
use App\Models\PurchaseRequest;
use App\Models\Sector;
use App\Models\Settlement;
use App\Models\Statistics;
use App\Models\subMerchant;
use App\Models\Transaction;
use App\Models\TransactionState;
use App\Models\UserSetting;
use App\Models\Wallet;
use App\Rules\EmailValidationRule;
use App\Rules\MerchantValidationRule;
use App\Rules\PhoneNoValidationRule;
use App\Rules\SubMerchantValidationRule;
use App\Rules\TerminalNoValidationRule;
use App\User;
use common\integration\Brand\Configuration\All\Mix;
use common\integration\Brand\Configuration\Backend\BackendAdmin;
use common\integration\Brand\Configuration\Backend\BackendMix;
use common\integration\Brand\Configuration\Frontend\FrontendAdmin;
use common\integration\Brand\Configuration\Frontend\FrontendMix;
use common\integration\Brand\Configuration\Frontend\FrontendWallet;
use common\integration\Brand\PaymentFeatureTrait;
use common\integration\CashInOutProcess\CashInOutManager;
use common\integration\CashInOutProcess\Service\Paratek;
use common\integration\CustomFileRules\FileValidation;
use common\integration\Models\Cashback;
use common\integration\Models\EarlySettlement;
use common\integration\Models\GovBTransEPHPYCNI;
use common\integration\Models\GovBTransEPKBB;
use common\integration\Models\GovBTransOKKIB;
use common\integration\Models\GovBTransReportHistory;
use common\integration\Models\GovBTransYT;
use common\integration\Models\IntegratorCommission;
use common\integration\Models\MerchantBankRegistration;
use common\integration\Models\MerchantBankRegistrationTerminal;
use common\integration\Models\MerchantChannel;
use common\integration\Models\MerchantCustomizedCostSetting;
use common\integration\Models\MerchantMoneyTransferLimit;
use common\integration\Models\MerchantPackage;
use common\integration\Payment\PhysicalPos\BrandImport;
use common\integration\Payment\PhysicalPos\Hugin;
use common\integration\Payment\PhysicalPos\Pax;
use common\integration\Payment\PhysicalPos\Paygo;
use common\integration\Payment\PhysicalPos\SariTaxi;
use common\integration\Payment\PhysicalPos\Verifone;
use common\integration\Replication\ReplicationRepository;
use common\integration\Utility\Arr;
use common\integration\Utility\Helper;
use common\integration\Traits\HttpServiceInfoTrait;
use common\integration\Utility\Language;
use common\integration\Utility\Number;
use common\integration\Utility\Str;
use common\integration\Utility\System;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\ImageFile;
use Mpdf\Tag\Sub;
use Illuminate\Validation\Rules\File as ValidateImageFile;
use Illuminate\Validation\Rule;
class AppRequestValidation
{

    use ResourceContainerTrait;
    /* ******* Withdrawl ******** */

    const WITHDRAWAL_ADD = [
        'sub_merchant_id' => 'required',
        'amount' => 'required|numeric|gt:0',
        'currency' => 'required|string'
    ];
    const WITHDRAWAL_LIST = [
        'sub_merchant_id' => 'required'
    ];
    const WITHDRAWAL_DETAIL = [
        'sub_merchant_id' => 'required',
        'transactionId' => 'required|string'
    ];

    /* ******* Withdrawl ******** */

    /* ******* Money Transfer ******** */

    const CASHOUTTOWALLET = [
        'sub_merchant_id' => 'required',
        'amount' => 'required|gt:0|numeric',
        'currency' => 'required'
    ];

    const CTOC = [
        'sender_sub_merchant_id' => 'required',
        'receiver_sub_merchant_id' => 'required',
        'amount' => 'required|gt:0|numeric',
        'currency' => 'required',
        'explanation' => 'required'
    ];

    /* ******* Money Transfer ******** */

   /* ******* Kyc Verification ******** */

      const KYCRULES = [
        'name' => 'required',
        'surname' => 'required',
        'birth_year' => 'required|numeric',
        'tckn' => 'required',
        'hash_key' => 'required',
      ];


   /* ******* Kyc Verification ******** */

    /* ******* Wallet ******** */
    public static function getApiServiceAddWalletValidationRules(): array
    {
        return
            [
                'merchant_key' => ['required',new MerchantValidationRule(true)],
                'sub_merchant_id' => ['required', new SubMerchantValidationRule()],
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES')))
            ];
    }

    public static function getApiServiceDeleteWalletsValidationRules(): array
    {
        return
            [
                'merchant_key' => ['required',new MerchantValidationRule(true)],
                'sub_merchant_id' => ['required', new SubMerchantValidationRule()],
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES')))
            ];
    }

    public static function getApiServiceListWalletsValidationRules(): array
    {
        return
            [
                'merchant_key' => ['required',new MerchantValidationRule(true)],
                'sub_merchant_id' => ['required', new SubMerchantValidationRule()],
            ];
    }

    public static function getApiServiceWalletManagementValidationMessages(): array
    {
        return
            [
                'required' => 'The :attribute field is required',
                'currency_code.in' => 'The :attribute must be one of the following types: :values'
            ];
    }

    public static function getSubMerchantManagementMessages():array
    {
        return
            [
                'required' => 'The :attribute field is required'
            ];

    }

    /* ******* Wallet ******** */

    /* ****** Deposit ******* */
    //create
    const DEPOSIT_CREATE = [
        'amount' => 'required|numeric|gt:0|max:99999999',
        'currency_code' => 'required|string',
        'sub_merchant_id' => 'required',
        'cc_holder_name' => 'required|string',
        'cc_no' => 'required|regex:/^\d{15,20}$/',
        'expiry_month' => 'required',
        'expiry_year' => 'required',
        'cvv' => 'required|regex:/^\d{3,4}$/',
    ];
    //Details
    const DEPOSIT_DETAILS = [
        'sub_merchant_id' => 'required',
    ];
    //list
    const DEPOSIT_LIST = [
        'page_limit' => 'required|numeric',
        'sub_merchant_id' => 'required',
    ];

    /* ****** Deposit ******* */

    /* ****** Transaction ******* */
    //TRANSACTION list
    const TRANSACTION_LIST = [
        'page_limit' => 'required|numeric',
        'sub_merchant_id' => 'required',
    ];
    //TRANSACTION details
    const TRANSACTION_DETAILS = [
        'sub_merchant_id' => 'required',
    ];
    /* ****** Transaction ******* */


    /* ****** User Management : Sub Merchant ******* */

    public static function getAddSubMerchantRules(): array
    {

        return [
            'merchant_key' => ['required', 'string', 'max:255',new MerchantValidationRule(true)],
            'sub_merchant_name' => 'required|string|regex:/^[\p{L}\s-]+$/|max:255',
            'sub_merchant_email' => ['required','email','max:255',new EmailValidationRule(Profile::SUB_MERCHANT,auth()->user()->merchant_id)],
            'sub_merchant_phone' => ['required', 'min:13','regex:/^[+]\d+$/',new PhoneNoValidationRule(Profile::SUB_MERCHANT,auth()->user()->merchant_id)],
            'identity_number' => 'required|digits_between:10,11',
            'terminal_no' => ['sometimes', 'string', 'max:255',new TerminalNoValidationRule(auth()->user()->merchant_id)],
            'sub_merchant_description' => 'required|string|max:255',
            'full_company_name' => 'required|string|max:255',
            'contact_person_phone' =>'required|min:13|regex:/^[+]\d+$/',
            'business_area' => ['required',
                       function($attribute, $value, $fail)
                          {

                             list($is_valid, $fail_message) = self::validateBusinessArea($value);
                              if (!$is_valid)
                              {
                                 $fail($fail_message);
                              }
                          }
                       ],
            'zip_code' => 'required|digits:5',
            'iban_no' => ['required', 'string', 'regex:/^((TR)[ \-]?[0-9]{2})(?=(?:[ \-]?[A-Z0-9]){9,30}$)((?:[ \-]?[A-Z0-9]{3,5}){2,7})([ \-]?[A-Z0-9]{1,3})?$/',
                function($attribute, $value, $fail)
                {
                    list($is_valid, $fail_message) = self::validateIbanNo($value);
                    if (!$is_valid)
                    {
                        $fail($fail_message);
                    }
                }
            ],
            'settlement_id' => ['required', 'integer',
            function($attribute, $value, $fail)
            {

               list($is_valid, $fail_message) = self::validateSettlement($value);
               if (!$is_valid)
               {
                  $fail($fail_message);
               }
            }
          ],
            'auto_approval_days' => 'sometimes|integer|min:-1|max:365',
            'is_enable_auto_withdrawal' => 'sometimes|in:0,1',
            'automatic_withdrawal_configuration' => 'sometimes|array|min:1',
            'automatic_withdrawal_configuration.*.auto_withdrawal_settlement_id' => ['required', 'integer',
                function($attribute, $value, $fail)
                {
                    list($is_valid, $fail_message) = self::validateSettlement($value);
                        if (!$is_valid)
                        {
                            $fail($fail_message);
                        }
                    }
                ],
            'automatic_withdrawal_configuration.*.auto_withdrawal_remain_amount' => 'required|numeric|min:0',
            'automatic_withdrawal_configuration.*.currency_code' => ['required', 'string',
                function($attribute, $value, $fail)
                {
                    list($is_valid, $fail_message) = (new self())->validateMerchantCurrency($value);
                    if (!$is_valid)
                    {
                        $fail($fail_message);
                    }
                }
            ]

        ];
    }

    public static function getDeleteSubMerchantRules(): array
    {
        return [
            'merchant_key' => ['required','string','max:255', new MerchantValidationRule(true)],
            'sub_merchant_id' => ['required','string','max:255', new SubMerchantValidationRule()]
        ];
    }

    public static function getEditSubMerchantRules(): array
    {

        return [
            'merchant_key' =>  ['required', 'string', 'max:255',new MerchantValidationRule(true)],
            'sub_merchant_id' => ['required','string','max:255', new SubMerchantValidationRule()],
            'change_data' => 'required|array|min:1',
            'change_data.sub_merchant_name' => 'string|regex:/^[\p{L}\s-]+$/|max:255',
            'change_data.terminal_no' =>  ['string', 'max:255',new TerminalNoValidationRule(auth()->user()->merchant_id,request()->sub_merchant_id)],
            'change_data.sub_merchant_description' => 'string|max:255',
            'change_data.full_company_name' => 'string|max:255',
            'change_data.contact_person_phone' =>'sometimes|min:13|regex:/^[+]\d+$/',
            'change_data.business_area' => ['sometimes',
              function($attribute, $value, $fail)
              {

                 list($is_valid, $fail_message) = self::validateBusinessArea($value);
                 if (!$is_valid)
                 {
                    $fail($fail_message);
                 }
              }
            ],
            'change_data.zip_code' => 'digits:5',
            'change_data.identity_number' => 'digits:11',
            'change_data.iban_no' =>  ['string', 'regex:/^((TR)[ \-]?[0-9]{2})(?=(?:[ \-]?[A-Z0-9]){9,30}$)((?:[ \-]?[A-Z0-9]{3,5}){2,7})([ \-]?[A-Z0-9]{1,3})?$/',
                function($attribute, $value, $fail)
                {
                    list($is_valid, $fail_message) = self::validateIbanNo($value);
                    if (!$is_valid)
                    {
                        $fail($fail_message);
                    }
                }
            ],
            'change_data.auto_approval_days' => 'integer|min:-1|max:365',
            'change_data.settlement_id' => ['sometimes', 'integer',
               function($attribute, $value, $fail)
               {

                  list($is_valid, $fail_message) = self::validateSettlement($value);
                  if (!$is_valid)
                  {
                     $fail($fail_message);
                  }
               }
             ],
            'change_data.is_enable_auto_withdrawal' => 'sometimes|in:0,1',
            'change_data.automatic_withdrawal_configuration' => 'sometimes|array|min:1',
            'change_data.automatic_withdrawal_configuration.*.auto_withdrawal_settlement_id' => ['required', 'integer',
                function($attribute, $value, $fail)
                {
                    list($is_valid, $fail_message) = self::validateSettlement($value);
                        if (!$is_valid)
                        {
                            $fail($fail_message);
                        }
                    }
                ],
            'change_data.automatic_withdrawal_configuration.*.auto_withdrawal_remain_amount' => 'required|numeric|min:0',
            'change_data.automatic_withdrawal_configuration.*.currency_code' => ['required', 'string',
                function($attribute, $value, $fail)
                {
                    list($is_valid, $fail_message) = (new self())->validateMerchantCurrency($value);
                    if (!$is_valid)
                    {
                        $fail($fail_message);
                    }
                }
            ]

        ];
    }


    public static function getListSubMerchantsRules(): array
    {
        return [
            'merchant_key' => ['required','string','max:255',new MerchantValidationRule(true)]
        ];
    }
    /* ****** User Management : Sub Merchant ******* */


    /**
     * Payments
     */

    public function paySmart3DValidationRules($input):array
    {
        return [
            'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
            'total' => 'required|numeric|min:0|not_in:0',
            'items.*.name' =>'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0|not_in:0',
            'items.*.qty' => 'required_without:quantity,qnantity|numeric|min:0|not_in:0',
            'items.*.quantity' => 'required_without:qty,qnantity|numeric|min:0|not_in:0',
            'items.*.qnantity' => 'required_without:qty,quantity|numeric|min:0|not_in:0',
            'items' => 'required|array|min:1|max:100',
            'installments_number' => 'required|digits|min:1',
            'cc_holder_name' => 'required|string|max:100|regex:/^((?!\d).)*$/',
            'cc_no' => 'required|regex:/^\d{15,20}$/',
            'expiry_month' => 'required|integer|between:1,12',
            'expiry_year' => 'required|integer|between:'.date('y').','.(date('y')+10),
            'name' => 'string|max:70',
            'surname' => 'string|max:30',
            'cancel_url' => 'required|url',
            'return_url' => 'bail|required|url',
            'merchant_key' => 'bail|required|string|max:255',
            'hash_key' => 'bail|required',
            'invoice_id' => 'bail|required|string|max:50',
            'cvv' => 'regex:/^\d{3,4}$/',
        ];
    }

    //marketplace

    public static function getMarketplace3DSecurePaymentValidationRules():array
    {
        return array_merge(self::getMarketplaceBasicPaymentRules(),
            [
                'cancel_url' => 'required',
                'return_url' => 'required',
            ]);
    }

    public static function getMarketplaceNonSecurePaymentValidationRules():array
    {
        return self::getMarketplaceBasicPaymentRules();
    }

    public static function getMarketplaceBasicPaymentRules():array
    {
        return [
                'invoice_id' => self::invoiceIdRule(),
                'items' =>
                    ['required',
                        function($attribute, $value, $fail)
                        {
                            list($is_valid, $fail_message) = self::validMarketPlaceItems($value);
                            if(!$is_valid){
                                $fail($fail_message);
                            }
                        }
                    ],
            /*
                'items.*.item_id' =>'required|string|min:1|max:32',
                'items.*.price' => 'required|numeric|gt:0',
                'items.*.sub_merchant_id' => 'required|string|max:64',
                'items.*.sub_merchant_share'=>'required|numeric|min:0',
                'items.*.quantity'=>'required|numeric|gt:0',
                'items.*.description'=>'required|string|max:128',
                'items.*.sub_merchant_settlement_id' => 'required|integer|gt:0',
            */
                'total' => 'required|numeric|gt:0',
                'currency_code' => 'required|string|max:4',
                'cc_holder_name' => 'required|string|max:50|regex:/^((?!\d).)*$/',
                'cc_no' => 'required|regex:/^\d{16,20}$/',
                'expiry_month' => 'required',
                'expiry_year' => 'required',
                'cvv' => 'required|numeric|regex:/^\d{3,4}$/',
                'installments_number' => 'required|min:0',
                'merchant_key' => 'required|string|max:128',
                'name' => 'string|max:70',
                'surname' => 'string|max:30',
                'hash_key' => 'required|max:256'
        ];
    }

    public static function getMarketplaceRefundRules():array
    {
        return [
            'merchant_key' => 'required|string|max:128',
            'invoice_id' => self::invoiceIdRule(),
            'order_id' => 'required|string|max:50',
            "refund_reason" => "sometimes|string|max:200",
            'type' => ['sometimes' , 'max:10', 'in:'.implode(',', array_values([BankRefund::DISPUTE_TYPE_REFUND, BankRefund::DISPUTE_TYPE_CHARGEBACK]))],
            'hash_key' => 'required|string|max:256',
            'refund_items' =>  ['required',
                function($attribute, $value, $fail)
                {
                    list($is_valid, $fail_message) = self::validateMarketPlaceRefundItems($value);
                    if(!$is_valid){
                        $fail($fail_message);
                    }
                }
            ],
        ];
    }

    public static function getMarketplaceCheckStatusRules()
    {
        return [
            'invoice_id' => self::invoiceIdRule(),
            'merchant_key' => 'required',
            'hash_key' => 'required'
        ];
    }

    //fastpay mobile qr payment

    public static function getFastpayMobileQrPaymentGetReferenceCodeRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'phone_number' => ['required', 'max:13','regex:/^\d+$/']
            ];
    }

    public static function  getFastpayMobileQrPaymentSaleQrRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'fastpay_merchant_id' => ['required','string','max:255'],
                'fastpay_terminal_id' => ['required','string','max:255'],
                'fastpay_merchant_username'=>['required','string','max:255'],
                'fastpay_merchant_password'=>['required','string','max:255'],
                'fastpay_reference_code'=> ['required','string','max:255'],
                'items.*.item_id' =>'required|string|max:255',
                'items.*.name' =>'required|string|max:255',
                'items.*.price' => 'required|numeric|min:0|not_in:0',
                'items.*.quantity' => 'required|numeric|min:0|not_in:0',
                'items.*.description' => 'required|string|max:255',
                'items' => 'required|array|min:1|max:100',
                'invoice_id' => ['required','string','max:255'],
                'total'=>['required','numeric','min:0','not_in:0'],
                'installments_number' => 'required|numeric',
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
                'hash_key' => 'required|string|max:255'
            ];

    }

    public static function  getFastpayMobileQrPaymentRefundQrRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'fastpay_merchant_id' => ['required','string','max:255'],
                'fastpay_terminal_id' => ['required','string','max:255'],
                'fastpay_merchant_username'=>['required','string','max:255'],
                'fastpay_merchant_password'=>['required','string','max:255'],
                'invoice_id' => ['required','string','max:255'],
                'amount'=>['required','numeric','min:0','not_in:0'],
                'cancellation_reason' => ['required','string','max:255'],
                'hash_key' => 'required|string|max:255'
            ];


    }

    public static function  getFastpayPaymentsSaleQrWalletRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'qr_reference_code'=> ['required','string','max:255'],
                'fastpay_wallet_user_code'=>['required','string','max:255'],
                'invoice_id' => ['required','string','max:255'],
                'total'=>['required','numeric','min:0','not_in:0'],
                'installments_number' => 'required|numeric',
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
                'items.*.item_id' =>'required|string|max:255',
                'items.*.name' =>'required|string|max:255',
                'items.*.price' => 'required|numeric|min:0|not_in:0',
                'items.*.quantity' => 'required|numeric|min:0|not_in:0',
                'items.*.description' => 'required|string|max:255',
                'items' => 'required|array|min:1|max:100',
                'hash_key' => 'required|string|max:255'
            ];

    }

    public static function  getFastpayPaymentsSaleQrNonSecureRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'qr_reference_code'=> ['required','string','max:255'],
                'card_token' => ['required','string','max:255'],
                'fastpay_wallet_user_code'=>['required','string','max:255'],
                'invoice_id' => ['required','string','max:255'],
                'total'=>['required','numeric','min:0','not_in:0'],
                'installments_number' => 'required|numeric',
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
                'items.*.item_id' =>'required|string|max:255',
                'items.*.name' =>'required|string|max:255',
                'items.*.price' => 'required|numeric|min:0|not_in:0',
                'items.*.quantity' => 'required|numeric|min:0|not_in:0',
                'items.*.description' => 'required|string|max:255',
                'items' => 'required|array|min:1|max:100',
                'hash_key' => 'required|string|max:255'
            ];

    }


    public static function  getFastpayPaymentsSaleQr3dSecureRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'qr_reference_code'=> ['required','string','max:255'],
                'card_token' => ['required','string','max:255'],
                'fastpay_wallet_user_code'=>['required','string','max:255'],
                'invoice_id' => ['required','string','max:255'],
                'total'=>['required','numeric','min:0','not_in:0'],
                'installments_number' => 'required|numeric',
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
                'items.*.item_id' =>'required|string|max:255',
                'items.*.name' =>'required|string|max:255',
                'items.*.price' => 'required|numeric|min:0|not_in:0',
                'items.*.quantity' => 'required|numeric|min:0|not_in:0',
                'items.*.description' => 'required|string|max:255',
                'items' => 'required|array|min:1|max:100',
                'cancel_url' => 'required|url',
                'return_url' => 'required|url',
                'hash_key' => 'required|string|max:255'
            ];

    }


    public static function  getFastpayPaymentsSaleMobileWalletRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'fastpay_wallet_user_code'=>['required','string','max:255'],
                'invoice_id' => ['required','string','max:255'],
                'total'=>['required','numeric','min:0','not_in:0'],
                'installments_number' => 'required|numeric',
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
                'items.*.item_id' =>'required|string|max:255',
                'items.*.name' =>'required|string|max:255',
                'items.*.price' => 'required|numeric|min:0|not_in:0',
                'items.*.quantity' => 'required|numeric|min:0|not_in:0',
                'items.*.description' => 'required|string|max:255',
                'items' => 'required|array|min:1|max:100',
                'hash_key' => 'required|string|max:255'
            ];

    }


    public static function  getFastpayPaymentsSaleMobileNonSecureRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'fastpay_wallet_user_code'=>['required','string','max:255'],
                'card_token' => ['required','string','max:255'],
                'invoice_id' => ['required','string','max:255'],
                'total'=>['required','numeric','min:0','not_in:0'],
                'installments_number' => 'required|numeric',
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
                'items.*.item_id' =>'required|string|max:255',
                'items.*.name' =>'required|string|max:255',
                'items.*.price' => 'required|numeric|min:0|not_in:0',
                'items.*.quantity' => 'required|numeric|min:0|not_in:0',
                'items.*.description' => 'required|string|max:255',
                'items' => 'required|array|min:1|max:100',
                'hash_key' => 'required|string|max:255'
            ];

    }


    public static function  getFastpayPaymentsSaleMobile3dSecureRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'fastpay_wallet_user_code'=>['required','string','max:255'],
                'card_token' => ['required','string','max:255'],
                'invoice_id' => ['required','string','max:255'],
                'total'=>['required','numeric','min:0','not_in:0'],
                'installments_number' => 'required|numeric',
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
                'items.*.item_id' =>'required|string|max:255',
                'items.*.name' =>'required|string|max:255',
                'items.*.price' => 'required|numeric|min:0|not_in:0',
                'items.*.quantity' => 'required|numeric|min:0|not_in:0',
                'items.*.description' => 'required|string|max:255',
                'items' => 'required|array|min:1|max:100',
                'cancel_url' => 'required|url',
                'return_url' => 'required|url',
                'hash_key' => 'required|string|max:255'
            ];

    }


    public static function  getFastpayPaymentRefundRules():array
    {
        return
            [
                'merchant_key' => ['required','string','max:255'],
                'invoice_id' => self::invoiceIdRule(),
                'fastpay_wallet_refund_method' => ['sometimes', 'in:' .  implode(',', array_values([FastpayPayment::FASTPAY_WALLET_REFUND_METHOD_REFUND, FastpayPayment::FASTPAY_WALLET_REFUND_METHOD_CANCEL]))],
                'amount'=>['required','numeric','gt:0'],
                'refund_transaction_id' => ['required', 'string', 'max:255'],
                'cancellation_reason' => ['required','string','max:255'],
                'hash_key' => 'required|string|max:255',
                'trn_code' => 'sometimes|string|max:10',
                'trn_code_detail' => 'sometimes|string|max:10',
                'trn_code_special' => 'sometimes|string|max:10',
            ];


    }

    public static function  getFastpayCreateQrRules():array
    {
        return
            [
                'fastpay_wallet_user_code' => ['required','string','max:255'],
                'fastpay_wallet_payment_method' => ['required', 'in:' .  implode(',', array_values([FastpayPayment::FASTPAY_WALLET_PAYMENT_METHOD_CARD, FastpayPayment::FASTPAY_WALLET_PAYMENT_METHOD_WALLET]))],
                'card_token' => 'required_if:fastpay_wallet_payment_method,==,'. FastpayPayment::FASTPAY_WALLET_PAYMENT_METHOD_CARD,
                'qr_ip' => 'sometimes|max:40|min:2',
                'qr_port' => 'sometimes|max:5|min:1',
                'qr_device_id' => 'sometimes|max:100|min:1'
            ];


    }

    public static function  getFastpayPaymentSaleRules():array
    {
       return
            [
                'merchant_key' => ['required','string','max:255'],
                'fastpay_wallet_payment_information_source' => 'sometimes|max:10, in:' . implode(',', [FastpayPayment::FASTPAY_WALLET_PAYMENT_INFORMATION_SOURCE_QR, FastpayPayment::FASTPAY_WALLET_PAYMENT_INFORMATION_SOURCE_INREQUEST]),
                'fastpay_wallet_payment_source' => [
                    'required' , 'max:255',
                    Rule::when(
                        request()->input("fastpay_wallet_payment_information_source") == FastpayPayment::FASTPAY_WALLET_PAYMENT_INFORMATION_SOURCE_INREQUEST,
                        'in:' . FastpayPayment::FASTPAY_WALLET_PAYMENT_SOURCE_MERCHANT_TERMINAL,
                        'in:' .  implode(',', array_values([FastpayPayment::FASTPAY_WALLET_PAYMENT_SOURCE_MERCHANT_TERMINAL, FastpayPayment::FASTPAY_WALLET_PAYMENT_SOURCE_MOBILE_APPLICATION]))
                    )
                ],
                'fastpay_wallet_payment_method' => [
                    'required_if:fastpay_wallet_payment_source,==,'. FastpayPayment::FASTPAY_WALLET_PAYMENT_SOURCE_MOBILE_APPLICATION,
                    'required_if:fastpay_wallet_payment_information_source,==,'. FastpayPayment::FASTPAY_WALLET_PAYMENT_INFORMATION_SOURCE_INREQUEST,
                    'in:' .  implode(',', array_values([FastpayPayment::FASTPAY_WALLET_PAYMENT_METHOD_CARD, FastpayPayment::FASTPAY_WALLET_PAYMENT_METHOD_WALLET])) 
                ],
                'card_token' => 'required_if:fastpay_wallet_payment_method,==,'. FastpayPayment::FASTPAY_WALLET_PAYMENT_METHOD_CARD,
                'security_type' => 'required_if:fastpay_wallet_payment_source,==,'. FastpayPayment::FASTPAY_WALLET_PAYMENT_SOURCE_MOBILE_APPLICATION .'|in:'.implode(',', array_values([FastpayPayment::FASTPAY_WALLET_PAYMENT_CARD_NON_SECURE_SECURITY_TYPE,FastpayPayment::FASTPAY_WALLET_PAYMENT_CARD_3D_SECURE_SECURITY_TYPE])),
                'qr_reference_code'=> [
                    Rule::requiredIf(function () {
                        // Check if 'secondKey' is not equal to 'InRequest'
                        return
                            request()->input('fastpay_wallet_payment_source') == FastpayPayment::FASTPAY_WALLET_PAYMENT_SOURCE_MERCHANT_TERMINAL
                            && request()->input("fastpay_wallet_payment_information_source") != FastpayPayment::FASTPAY_WALLET_PAYMENT_INFORMATION_SOURCE_INREQUEST
                            ;}),
                    'digits:6',
                ],
                'merchant_terminal_id' => ['required_if:fastpay_wallet_payment_source,==,'. FastpayPayment::FASTPAY_WALLET_PAYMENT_SOURCE_MERCHANT_TERMINAL,'string','max:20'],
                'provider_sub_merchant_code' => 'required|string|max:255',
                'provider_sub_merchant_name' => 'required|string|max:255',
                'fastpay_wallet_user_code'=>'required_if:fastpay_wallet_payment_source,==,'. FastpayPayment::FASTPAY_WALLET_PAYMENT_SOURCE_MOBILE_APPLICATION .'|string|max:255',
                'invoice_id' => self::invoiceIdRule(),
                'total'=>['required','numeric','gt:0'],
                'installments_number' => 'required|numeric',
                'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
                'items.*.item_id' =>'sometimes|string|max:255',
                'items.*.name' =>'sometimes|string|max:255',
                'items.*.price' => 'sometimes|numeric|gt:0',
                'items.*.quantity' => 'sometimes|numeric|gt:0',
                'items.*.description' => 'sometimes|string|max:255',
                'items' => 'sometimes|array|min:1|max:100',
                //'transaction_date' => 'required|date_format:Y-m-d H:i:s',
                'transaction_date' => ['required',
                    function($attribute, $value, $fail){
                        if (!self::validISO8601Date($value)) {
                            $fail('The '.$attribute.' is invalid.');
                        }
                        }],
                'cancel_url' => 'required_if:security_type,==,'.FastpayPayment::FASTPAY_WALLET_PAYMENT_CARD_3D_SECURE_SECURITY_TYPE.'|url',
                'return_url' => 'required_if:security_type,==,'.FastpayPayment::FASTPAY_WALLET_PAYMENT_CARD_3D_SECURE_SECURITY_TYPE.'|url',
                'hash_key' => 'required|string|max:255',
                'fastpay_wallet_user_fee'=>'required|numeric|min:0',
                'trn_code' => 'required|string|max:10',
                'trn_code_detail' => 'required|string|max:10',
                'trn_code_special' => 'required|string|max:10',
                'fastpay_wallet_user_fee_type' => 'sometimes|max:10, in:' . implode(',', [FastpayPayment::FASTPAY_WALLET_USER_FEE_TYPE_DO_NOT_ADD, FastpayPayment::FASTPAY_WALLET_USER_FEE_TYPE_ADD]),
                'channel'   => 'sometimes|string:max:10',

            ];

    }

    public static function validISO8601Date($date):bool
    {
        if (preg_match(
            '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2}).(\d{3})Z$/',
            $date,
            $parts) == true) {

            $time = gmmktime($parts[4], $parts[5], $parts[6], $parts[2], $parts[3], $parts[1]);

            $input_time = strtotime($date);

            if ($input_time === false){
                return false;
            }else{
                return $input_time == $time;
            }

        }else{
            return false;
        }

    }

    public static function validMarketPlaceItems($items)
    {

        list($is_valid, $items) = self::validItems($items);
        if(!$is_valid){
            return [false, "Invalid Format of Items"];
        }

        $tmp = [];
        $i = 1;
        foreach ($items as $item){
            $rules = [
                'item_id' =>'required|string|max:255',
                'name' =>'sometimes|string|max:255',
                'price' => 'sometimes|numeric|gt:0',
                'quantity' => 'sometimes|numeric|gt:0',
                'description' => 'sometimes|string|max:255',
               // 'sub_merchant_settlement_id' => 'required|integer|gt:0'
            ];
            $validator = Validator::make($item,$rules);

            if ($validator->fails()) {
                return[false,"items.$i.".$validator->errors()->first()];
            }

            if(isset($item['sub_merchant_id']) && isset($item['item_id'])){
                $tmp[] = $item['sub_merchant_id'].'-item_id:'.$item['item_id'];
            }

            if(isset($item['sub_merchant_share']) && isset($item['price'])){
                if($item['sub_merchant_share'] > $item['price']){
                    return[false, "Sub merchant {$item['sub_merchant_id']} share {$item['sub_merchant_share']} exceeds item price {$item['price']} for item_id {$item['item_id']}"];
                }

            }
            $i++;
        }

        if(count($tmp) == count(array_unique($tmp))){
           return [true,"Success"];
        }
        return [false,'Please use unique item_id for each item for the same sub_merchant'];

    }

    public static function validItems($items):array
    {
        if(is_string($items)) {
            $items = json_decode($items, true);
        }

        if(!is_array($items)||empty($items)|| in_array($items, ['null','NULL'])){
            return false;
        }

        return [true,$items];

    }

   public static function validateBusinessArea($value)
   {
      $business_areas = (new Sector())->getAllActiveSectors(Sector::MERC_SECTOR_TYPE);

      if (!in_array($value, $business_areas->pluck('name')->toArray()) &&
        !in_array($value, $business_areas->pluck('name_tr')->toArray())) {
         $fail = __('Invalid business area');
         return [false, $fail];
      }

      return [true, null];

   }

   public static function getSettlementValidationRules()
   {
      return [
        'merchant_key' => 'required',
      ];
   }


   public static function transactionApproveRules()
   {
      return [
        'merchant_key' => 'required|string|max:128',
        'invoice_id' => self::invoiceIdRule(),
        'order_id' => 'required|string|max:50',
        'hash_key' => 'required|string|max:256',
        'data' => 'required|array|min:1|max:100',
        'data.*.sub_merchant_id' =>'required|string|max:32',
        'data.*.item_id' => 'required|array|min:1'
      ];
   }

   public static function validateSettlement($value)
   {
      $settlementObj = (new Settlement())->getById($value);

      if (empty($settlementObj)) {
         $fail = __('Invalid settlement id');
         return [false, $fail];
      }

      return [true, null];

   }

   public static function validateMarketplaceRefundItems($marketplace_refund_items):array
   {


           $validator = Validator::make($marketplace_refund_items,[
               '*.item_id' => 'required|string|max:50',
               '*.quantity' => 'sometimes|integer|min:1',
               '*.sub_merchant_id' => 'required|string|max:50',
           ]);

           if ($validator->fails()) {
               return[false,$validator->errors()->first()];
           }

       $sub_merchant_refund_items_assoc = BankRefund::prepareMarketplaceSubMerchantRefundItemsAssoc($marketplace_refund_items);
           foreach($sub_merchant_refund_items_assoc as $sub_merchant_id => $refund_items){
               $item_ids = [];
               foreach($refund_items as $refund_item){
                   $item_ids[] = $refund_item["item_id"];
               }
               if(count($item_ids) != count(array_unique($item_ids))){
                   return [false, "Sub merchant $sub_merchant_id has duplicate item_id."];
               }
           }

           return [true,"Success"];


   }

    public static function validateIbanNo ($value)
    {
        $banktmp = (new CashInOut())->findBankByIbanCodeAndUserType($value, User::SUB_MERCHANT);
        $bank = $banktmp->getData()->data;
        if (empty($bank)) {
            $fail = __('Invalid IBAN number');
            return [false, $fail];
        }
        return [true, null];
    }

    public function validateMerchantCurrency ($value)
    {
        $key = 'SUBMERCUR'.$value;
        $currencies = $this->getResource($key);

        if (empty($currencies)) {
            $currencyObj = new Currency();
            $currencies = $currencyObj->getCurrenciesByUser(Auth::user()->getMerchants->user);
            $this->setResource($key, $currencies);
        }
        $validCur = false;
        $fail = __('Currency :curr is not available for this merchant', ['curr' => $value]);

        if (!empty($currencies)) {
            $currency = $currencies->where('code', $value)->first();
            if (!empty($currency)) {
                $validCur = true;
                $fail = null;
            }
        }

        return [$validCur, $fail];
    }

   public static function getMarketplacePayoutRules():array
   {
      return [
        'merchant_key' => 'required|string|max:128',
        'total' => 'required|numeric|gt:0',
        'hash_key' => 'required|string|max:256',
        'payout_data' => 'required|array|min:1|max:10',
        'payout_data.*.sub_merchant_id' =>'required|distinct|max:32',
        'payout_data.*.amount' => 'required|numeric|gt:0',
        'payout_data.*.currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
        'payout_data.*.description' => 'sometimes|string|max:200'
      ];

   }



   public function validateMerchant($merchant_key, $payment_type = '', $api_name = '', $merchantObj = null, $merchantSettingObj = null, $is_pay_by_marketplace = false, CCPayment $ccpayment = null)
   {
      $status = false;

      $auth_id = auth()->id();

      if (empty($merchantObj)) {
         $merchant = new Merchant();
         if (empty($ccpayment->merchantObj)) {
            $merchantObj = $merchant->getActiveMerchantByKey($merchant_key);
            $ccpayment->merchantObj = $merchantObj;
         }

      } else {
         $ccpayment->merchantObj = $merchantObj;
      }


      if (!empty($ccpayment->merchantObj)) {

         if (empty($merchantSettingObj)) {
            $merchant_settings_instance = new MerchantSettings;
            if (empty($ccpayment->merchantSettingsObj)) {
               $merchantSettingObj = $merchant_settings_instance->getMerchantSettingByMerchantId($ccpayment->merchantObj->id);
               $ccpayment->merchantSettingsObj = $merchantSettingObj;
            }

         } else {
            $ccpayment->merchantSettingsObj = $merchantSettingObj;
         }

         if (!empty($ccpayment->merchantSettingsObj)) {

            if ($payment_type == '3d' || $payment_type == 'card_token'
              || $payment_type == 'marketplace' || BrandConfiguration::ignoreTokenValidation()) {
               $auth_id = $ccpayment->merchantSettingsObj->id;
            }


            if ($auth_id == $ccpayment->merchantSettingsObj->id) {
               $status = true;
            }
         }

         if ($is_pay_by_marketplace) {
            $status = GlobalMerchant::isMarketplaceMerchant($ccpayment->merchantObj);
         }
      }


      return array($status, $ccpayment->merchantObj, $ccpayment->merchantSettingsObj);
   }


   public function validateCurrency($data, CCPayment $ccpayment){

      $errorCode = $errorMessage = '';

      $currency = new  Currency();

      if (isset($data['currency_code']) && empty($ccpayment->currencyObj)){
         $ccpayment->currencyObj = $currency->getCurrencyByCode($data['currency_code']);
      }

      if (empty($ccpayment->currencyObj)){
         $errorCode = 12;
         $errorMessage = "Invalid currency code";
      }

      return [$errorCode, $errorMessage, $ccpayment->currencyObj];
   }


   public function getWalletPaymentValidationRules($input){

      $api_individual_rules = [
        'invoice_id' => self::invoiceIdRule(),
        'currency_code' => PurchaseRequest::getCurrencyCodeValidationRules(),
        'total' => 'required|numeric|gt:0',
        'merchant_key' => ['required', 'string', 'max:255', new MerchantValidationRule(true)],
        'gsm_number' => 'required|min:13|regex:/^[+]\d+$/',
        'items' => 'required',
      ];

      if(isset($input['is_bill_payment']) && $input['is_bill_payment'] == 1 ){
         $api_individual_rules = $api_individual_rules + BillPaymentApiService::getBillPaymentValidateionRules($input);
      }

      return $api_individual_rules;

   }


   public static function validateItemsArray($data)
   {

      $errorCode = $errorMessage = '';
      $item_total = 0;
      if (!isset($data['items']) || !is_array($data['items'])) {
         $errorCode = 12;
         $errorMessage = "Items must be an array";
      }

      if (empty($errorCode)) {
         foreach ($data['items'] as $item) {
            if (isset($item['quantity']) || isset($item['qty'])){
               $item['qnantity'] = $item['quantity'] ?? $item['qty'];
               if(isset($data['second_hand_request']) && $data['second_hand_request'] ==1 ){
                  $item['qnantity'] = (int)($item['qnantity']);
               }
            }
            if (!isset($item['name']) || !isset($item['price']) || !isset($item['qnantity'])) {
               $errorCode = 12;
               $errorMessage = "Invalid format of items";

            } else if (!is_numeric($item['price'])) {
               $errorCode = 10;
               $errorMessage = "Price must be numeric value";
               break;
            } else if (!is_int($item['qnantity'])) {
               $errorCode = 33;
               $errorMessage = "Quantity must be integer value";
               break;
            } else {
               $item_total += ($item['price'] * $item['qnantity']);
            }

         }
      }

      $errorMessage = Language::isLocalize($errorMessage);
      return [$errorCode, $errorMessage, $item_total];
   }


   public function validateSaleWebHookKey($data, $merchantObj)
   {

      $errorCode = $errorMessage = '';
      $merchantWebHookKeyObj = null;
      if (isset($data['sale_web_hook_key']) && !empty($data['sale_web_hook_key']) && !empty($merchantObj)) {

         $merchantWebHookKey = new MerchantWebHookKeys();
         $merchantWebHookKeyObj = $merchantWebHookKey->findMerchantWebHookKeyByMerchant($merchantObj->id
           , $data['sale_web_hook_key'], MerchantWebHookKeys::SALE_WEB_HOOK);

         if (empty($merchantWebHookKeyObj)) {
            $errorCode = 56;
            $errorMessage = "Invalid sale web hook key! Please check key name on " . config('brand.name');
         }

      }

      return [$errorCode, $errorMessage, $merchantWebHookKeyObj];
   }

   public function validateBillPayment($data, $merchantObj = null, $merchantSettingObj = null){

      $errorCode = $errorMessage = '';
      if (isset($data['is_bill_payment']) && $data['is_bill_payment'] == 1) {

         $billPayment = new PaycellBillPayment();
         $billPayment->billPaymentValidation($merchantObj, $merchantSettingObj);

         if (!empty($billPayment->status_code)) {
            $errorCode = $billPayment->status_code;
            $errorMessage = $billPayment->status_description;
         }
      }

      return [$errorCode, $errorMessage];
   }

   public function validateUser($data, CCPayment $ccpayment)
   {

      $errorCode = $errorMessage = '';
      if (isset($data['gsm_number']) && !empty($data['gsm_number']) && empty($ccpayment->customerUserObj)) {

         $user_type = $data['user_type'] ?? Profile::CUSTOMER;
         $ccpayment->customerUserObj = (new Profile())->getUserByPhone($data['gsm_number'], $user_type);
      }

      if (empty($ccpayment->customerUserObj)) {
         $errorCode = 45;
         $errorMessage = "User not found";
      }

      return [$errorCode, $errorMessage];
   }


   public function getWalletPaymentOtpValidationRules($input)
   {

      $api_individual_rules = [
        'otp' => isset($input['resend_otp']) && $input['resend_otp'] ? 'nullable' : 'required',
        'ref' => 'required',
        'merchant_key' => ['required', 'string', 'max:255', new MerchantValidationRule(true)],
        'gsm_number' => 'required|min:13|regex:/^[+]\d+$/'
      ];


      return $api_individual_rules;
   }

   public function validateUserWallet($user_id, $currency_id, $amount, $is_lock = false){

      $status_code = '';
      $status_message = '';
      if ($is_lock) {
         $walletObj = (new Wallet())->getUserWalletByLock($user_id, $currency_id);
      } else {
         $walletObj = (new Wallet())->getUserWallet($user_id, $currency_id);
      }

      if (empty($walletObj)) {
         $status_code = 3;
         $status_message = 'Wallet not found';
      }

      if (empty($status_code)){
         $wallet_total_amount = $walletObj->withdrawable_amount + $walletObj->non_withdrawable_amount;
         if ($amount > $wallet_total_amount) {
            $status_code = 2;
            $status_message = 'Insufficient balance';
         }
      }

      if(empty($status_code)){
         $status_code = 100;
      }


      return [$status_code, $status_message, $walletObj];

   }

   public function getCompletePaymentRules():array
   {

       return [
               'merchant_key' => 'required|string|max:64',
               'invoice_id' => 'required|string|max:50',
               'order_id' => 'required|string|max:50',
               'status' => ['required' , 'max:10', 'in:'.implode(',', array_values([CompletePayment::API_REQUEST_STATUS_CANCEL, CompletePayment::API_REQUEST_STATUS_COMPLETE]))],
               'hash_key' => 'required|string|max:256',
              ];

   }

   public function isInsurancePaymentCompatibleApi($api_name):bool
   {
       return in_array($api_name,
           [
               'PAY_2D',
               'PAY_SMART_2D'
           ]
       );
   }


   public function isValidInsurancePaymentIntegration($input, ?MerchantSettings $merchantSettings = null)
   {
       $payment_type = $input["payment_type"] ?? "";

       if($payment_type == "2d" && $merchantSettings->is_allow_insurance_payment){
           return true;
       }

       return false;
   }

   public function getInsurancePaymentRules($input, $rules):array
   {
       if(isset($input["vpos_type"]) && $input["vpos_type"] == CCPayment::VPOS_TYPE_INSURANCE){
           unset($rules["cc_no"]);
           unset($rules["cvv"]);
           unset($rules["expiry_month"]);
           unset($rules["expiry_year"]);

          $rules = $rules +
           [
               'vpos_type' =>  ['sometimes' , 'max:10', 'in:'.implode(',', array_values([CCPayment::VPOS_TYPE_INSURANCE,CCPayment::VPOS_TYPE_REGULAR]))],
               'identity_number' => 'required_if:vpos_type,==,'. CCPayment::VPOS_TYPE_INSURANCE .'|digits_between:10,11',
               'cc_no' =>
                   ['required',
                       function($attribute, $value, $fail)
                       {
                           if(!preg_match ('/^(\d{6}\*{6}\d{4})$/', $value)) {
                               if (!preg_match ('/^(\d{8}\*{4}\d{4})$/', $value)){
                                   $fail("Validation error! For insurance payment 'cc_no' format = 'first_six_digits' + '******' + 'last_four_digits or first_eight_digits' + '****' + 'last_four_digits'");
                               }
                           }
                       }
                   ]
           ];
       }

       return $rules;

   }

   /* SALE REFERENCE VALIDATION RULES */
   public static function getSaleReferenceValidation(){
       return [
           'sale_reference' => ['required'],
           'merchant_key' => ['required'],
           'hash_key' => ['required'],
       ];
   }


    public function validateTenantPayment($input, $bankObj, $merchantObj)
    {
        $errorCode = '';
        $errorMessage = '';

        if (BrandConfiguration::isTenant() &&
            $bankObj->payment_provider == config('constants.PAYMENT_PROVIDER.SOFTROBOTICS_TENANT')
        ) {
            if ($merchantObj->tenant_approval_status != Merchant::TENANT_STATUS_APPROVED) {
                $errorCode = 14;
                $errorMessage = "Tenant merchant is not approved";
            }
        }
        if (isset($input['is_tenant_payment']) &&
            $input['is_tenant_payment'] == 1
        ) {
            if ($merchantObj->type != Merchant::TENANT_MERCHANT) {
                $errorCode = 14;
                $errorMessage = "Tenant payment is not allowed for this merchant type";
            }
            if ($merchantObj->tenant_approval_status != Merchant::TENANT_STATUS_APPROVED) {
                $errorCode = 14;
                $errorMessage = "Tenant merchant is not approved";
            }
        }
        return [$errorCode, $errorMessage];
    }

    public static function isTenantAllowed($merchantObj, $avoid_ip_restriction = 0)
    {
        $isTenantMerchant = $merchantObj->type==Merchant::TENANT_MERCHANT;
        $isLicenseOwner= BrandConfiguration::isLicenseOwnerTenant();
        if($isLicenseOwner && $isTenantMerchant){
            if ($merchantObj->tenant_approval_status != Merchant::TENANT_STATUS_APPROVED) {
                return false;
            }
            if(! $avoid_ip_restriction) {
                $ip = (new class {
                    use HttpServiceInfoTrait;
                })->getServerIpAddress(true);

                (new ManageLogging())->createLog([
                    'action' => 'REQUEST_FROM',
                    'ip' => $ip
                ]);
                $tenant_server_ips = (new Statistics)->findDataByColumn('tenant_server_ips');
                if (!empty($tenant_server_ips)) {
                    $ipList = explode(',', $tenant_server_ips);
                    if (in_array($ip, $ipList)) {
                        return true;
                    }
                }
                return false;
            }else{
                return true;
            }
        }
        return true;
    }
    
    public static function getBulkAwaitingRefundRules()
    {
       return [
           'refund_request_type' => 'required|in:0,1',
           'refund_data' => 'required|array|min:1',
       ];
    }

    public static function otpSubmitRules():array
    {
        return [
            'otp' => ['required']
        ];
    }

    public static function userPasswordUpdateRules($auth_user): array
    {

        if (config('constants.PASSWORD_SECURITY_TYPE') == Profile::ALPHANUMERIC_PASSWORD) {
            $pass_rules = ['min:8','regex:'.Profile::ALPHANUMERIC_PASSWORD_REGEX];
        } else {
            $pass_rules = ['min:6','max:6','regex:'.Profile::NORMAL_PASSWORD_REGEX];
        }


        return [
            'old_password' => Self::currentPasswordValidationRules($auth_user),
            'password' => array_merge(['required_with:password_confirmation','same:password_confirmation','different:old_password', function ($attribute, $value, $fail) use ($auth_user){

                $profileObj = new Profile();
                $validation_status = $profileObj->checkOldPassword($value, $auth_user->id);
                if(!empty($validation_status)){
                    return $fail(__('When changing a password, the new password cannot be the same with the last :limit passwords.',['limit' => config('constants.PASSWORD_DENY_LAST_USED')]));
                }

            }], $pass_rules),
            'password_confirmation' => ['required']
        ];
    }

    public static function userPhoneUpdateRules($digit, $auth_user) : array
    {
        return [
            'current_password' => Self::currentPasswordValidationRules($auth_user),
            'old_phone' => ['required', function ($attribute, $value, $fail) {

                if($value != auth()->user()->phone){
                    return $fail(__('The current GSM number is not matched.'));
                }

            },'min:' . ($digit),'regex:/^[+]\d+$/'],
            'phone' => ['required', function ($attribute, $value, $fail) use ($auth_user){
                $profileObj = new Profile();
                $validation_status = $profileObj->validateUserByPhone(
                    [
                        'phone' => $value
                    ],
                    $auth_user->user_type
                );

                if($validation_status != ApiService::API_SERVICE_SUCCESS_CODE){
                    return $fail(__('GSM number already exists'));
                }

            },'min:' . ($digit),'regex:/^[+]\d+$/','different:old_phone'],
        ];
    }

    public static function userSecretQuestionUpdateRules($auth_user) : array
    {
        return [
            'secret_question_id' => ['required', 'numeric', 'min:1','max:5'],
            'question_answer' => ['required','max:255'],
            'current_password' => Self::currentPasswordValidationRules($auth_user),
        ];
    }

    public static function currentPasswordValidationRules($auth_user) : array
    {

        return ['required', function ($attribute, $value, $fail) use ($auth_user) {

            if (!\Hash::check($value, $auth_user->password)) {
                return $fail(__('The current password is incorrect.'));
            }

        }];

    }

    public static function gsmValidationRule ($key, $role = 'required'): array
    {
        return [
            $key => $role . '|min:13|regex:/^[+]\d+$/'
        ];
    }

    /**
     * @param array $fields
     * @param string $validation_field_name
     * @param bool $is_validation_field_array
     * @param array $validation_type
     * @param array $supported_types
     * @param array $size_in_mb
     * @return array|ImageFile[][]
     */
    public static function imageValidationRules(
        array $fields,
        string $validation_field_name,
        bool $is_validation_field_array = false,
        array $validation_type = ['types', 'size'],
        array $supported_types= UserSetting::UPLOAD_FILE_TYPES,
        array $size_in_mb = ['min_size'=> 0, 'max_size'=> UserSetting::UPLOAD_FILE_SIZE]
    )
    {
        $image_validation = ValidateImageFile::image();
        if (in_array('types', $validation_type)){
            $image_validation = $image_validation->types($supported_types);
        }
        if (in_array('size', $validation_type)){
            $image_validation = $image_validation->min($size_in_mb['min_size'] * 1024)->max($size_in_mb['max_size'] * 1024);
        }

        $rules = [];

        if ($is_validation_field_array){
            $rules = array_merge($rules,  [$validation_field_name => ['array']]);
            $rules = array_merge($rules,  [$validation_field_name . '.*' => [$image_validation]]);
        }else{
            $rules = array_merge($rules,  [$validation_field_name => [$image_validation]]);
        }
        return $rules;
    }

    public static function resendEmailValidationRulesAndMessages()
    {
        $rules = [
            'phone' => 'required|min:13|regex:/^[+]\d+$/',
            'email' => 'required|email|max:255'
        ];
        $messages = [
            'phone.regex' => __('GSM number is invalid'),
            'phone.required' => __('GSM number is required'),
            'phone.min' => __('GSM number is invalid'),
            'email.unique_email' => __("Email is required")
        ];
        return [
            'rules' => $rules,
            'messages' => $messages
        ];
    }

    public function validateLogin($request)
    {
        if(!$request->remote_login){
            Validator::extend('adminCaptcha', function ($attribute, $value, $parameters) {
                return (GlobalFunction::getBrandSession('login_captcha') == $value);
            }, __('message.wrong_captcha_text'));
        }
        
        $password_min_length = BrandConfiguration::call([Mix::class, 'enableUserPasswordRequired']) ? "|min:4" : "";

        $rules = [
            'email'    => 'required|string',
            'password' => 'required|string'.$password_min_length,
        ];
        $message =  [
            'email.required'    => 'Email is required',
            'password.required' => 'Password is required',
        ];
        if(!$request->remote_login) {
            if (BrandConfiguration::isAllowLoginCaptcha() && !(!empty($request->apply_captcha) && $request->apply_captcha == 'no')) {
                $rules = array_merge($rules, ['captcha' => 'required|adminCaptcha']);
                $message = array_merge($message, ['captcha.required' => 'Captcha is required']);
            }
        }
        return ['rules'=>$rules, 'message'=> $message];
    }


    public static function getFtpRequestValidationRules($activity = null)
    {
        if ($activity == "delete") {
            return [
                'id' => 'required',
            ];
        } else {
            return [
                'host' => 'required',
                'username' => 'required',
                'password' => 'required',
                'port' => 'required',
                'type' => 'required',
            ];
        }
    }





    public static function huginImportTransactionRequestRules()
    {
        $required_if_roles_condition = Rule::requiredIf(function () {
            return
                Arr::isAMemberOf( request()->input('status'), [Hugin::STATUS_APPROVED, Hugin::STATUS_FAILED] );
        }
        );
        return [
            "paymentId" => 'required',
            "date" => "required",
            "type" => "required|in:".implode(',',Hugin::ALLOWED_REMOTE_TRANSACTION_TYPE_LIST),
            "status" => "required",
            "currency" => "$required_if_roles_condition|in:" . implode(',', config('constants.SYSTEM_SUPPORTED_CURRENCIES')),
            "terminalId" => "required",
            "amount" => "$required_if_roles_condition|numeric",
            "installment" => "$required_if_roles_condition|integer",
            "maskedCardNo" => $required_if_roles_condition,
            "hashedCardNo" => $required_if_roles_condition,
            "provisionNo" => $required_if_roles_condition,
            "bankId" => $required_if_roles_condition,
            "acquirerResponseCode" => $required_if_roles_condition,
            "rrn" => "string|nullable",
            "posEntryMode" => "string|nullable",
            "pinEntryInfo" => "string|nullable",
            "cardType" => "string|nullable",
        ];
    }
    public static function huginBatchRequestRules()
    {
        return [
            "batchId" => 'required|string|max:100',
            "bankId" => "required|string|max:11",
            "merchantId" => "required|string|max:100",//previous => merchant_id
            "terminalId" => "required|string|max:50|exists:merchant_terminals,terminal_id", //previous => terminal_id
            "date" => "required|max:11",
            "saleCount" => "required|integer|digits_between:1,10",
            "voidCount" => "required|integer|digits_between:1,10",
            "refundCount" => "required|integer|digits_between:1,10",
            "installmentSaleCount" => "required|integer|digits_between:1,10",
            "saleAmount" => "required|integer|digits_between:1,10",
            "voidAmount" => "required|integer|digits_between:1,10",
            "refundAmount" => "required|integer|digits_between:1,10",
            "installmentSaleAmount" => "required|integer|digits_between:1,10",
            "uniqueId" => "string|max:50",
            "institutionId" => "required|integer|digits_between:1,10",
            "vendor" => "required|string|max:100",
        ];
    }
    /**
     * @param bool $isAdmin
     * @return array
     */
    public function validateTicket(bool $isAdmin = false):array
    {
        $rules =  [
            'category' => 'required|integer',
            'title' => 'required|max:255',
            'message' => 'required|min:'.GlobalMerchant::getMerchantSupportTicketLength().'|max:1000',
            'attachment' => 'nullable|mimes:png,jpg,jpeg,webp,tif,tiff,pdf|max:5120'
        ];

        $messages = [
            'category.required' => __(':attribute is required!'),
            'category.integer' => __('Please select :attribute with options'),
            'title.required' => __(':attribute is required!'),
            'title.max.string' => __('The :attribute may not be greater than :max.'),
            'message.required' => __(':attribute is required!'),
            'message.max.string' => __('The :attribute may not be greater than :max.'),
            'message.min.string' => __('The :attribute cannot be less than :max.'),
            'attachment.mimes' => __('The :attribute must be a file of type: :values.'),
            'attachment.size.file' => __('The :attribute must be :size kilobytes.'),
        ];

        if ($isAdmin){
            $rules = array_merge($rules,  ['user_id' => 'required']);
            $messages = array_merge($messages,  ['user_id.required' => __('Merchant Id is required')]);
        }

        return ['rules'=>$rules, 'messages'=>$messages];
    }

    
    public static function getBinRangeRequestValidationRules(){
       return [
            'card_type' => 'required',
        ];
    
    }


    public function validateMarketplace($input, $merchantObj, $merchantSettlementObj, CCPayment $ccpayment, ?PaymentMethods $paymentMethods = null):array
    {
        $error_code = "";
        $error_message = "";
        $sub_merchant_settlement_assoc = [];
        $is_sari_taxi = $input['is_sari_taxi'] ?? 0;

        if(!$is_sari_taxi && ! GlobalMerchant::isMarketplaceMerchant($merchantObj)){
            $error_code = 14;
            $error_message = "Merchant is not a marketplace merchant";
        }

        if(empty($error_code)) {

            $sub_merchant_ids = array();

            foreach ($input['items'] as $item) {
                $sub_merchant_ids[] = $item['sub_merchant_id'];
            }
            if (!empty($sub_merchant_ids)) {
                $sub_merchant_ids = array_unique($sub_merchant_ids);
                $subMerchantCollection = (new subMerchant())->getSubMerchantsByIds($sub_merchant_ids, $merchantObj->id);
            }
            if (empty($sub_merchant_ids) || count($sub_merchant_ids) != count($subMerchantCollection)) {
                $error_code = 14;
                $error_message = "Sub Merchant not found";
            }
        }


        if(empty($error_code)) {

            $merchantSettlementDateObj = (new CalculateSettlement())->getSettlementDate($merchantSettlementObj->code, $merchantSettlementObj->value);

            $sub_merchant_settlement_ids = $subMerchantCollection->pluck('settlement_id')->toArray();
            $settlement = new Settlement();
            $subMerchantSettlementCollection = $settlement->getSettlements(array_unique($sub_merchant_settlement_ids))->keyBy('id');

            foreach ($subMerchantCollection as $subMerchantObj) {

                $sub_merchant_id = $subMerchantObj->sub_merchant_id;
                $sub_merchant_settlement_id = $subMerchantObj->settlement_id;
                $subMerchantSettlementObj = $subMerchantSettlementCollection[$sub_merchant_settlement_id]??null;

                if(empty($subMerchantSettlementObj)){

                    $error_code = 1;
                    $error_message = "Sub merchant $sub_merchant_id 's settlement_id $sub_merchant_settlement_id not found";
                    break;

                }else{
                    $subMerchantSettlementDateObj = (new CalculateSettlement())->getSettlementDate($subMerchantSettlementObj->code, $subMerchantSettlementObj->value);
                    if($merchantSettlementDateObj->greaterThan($subMerchantSettlementDateObj)){
                        $error_code = 43;
                        $error_message = "Sub merchant $sub_merchant_id can't have settlement date before Merchant.";
                        break;
                    }
                }

                $sub_merchant_settlement_assoc[$sub_merchant_id]['code'] = $subMerchantSettlementObj->code;
                $sub_merchant_settlement_assoc[$sub_merchant_id]['value'] = $subMerchantSettlementObj->value;
                $sub_merchant_settlement_assoc[$sub_merchant_id]['auto_approval_days'] = $subMerchantObj->auto_approval_days;


            }

        }

        if(empty($error_code)){
            $ccpayment->sub_merchant_assoc = $input['sub_merchant_assoc'];
            $ccpayment->sub_merchant_settlement_assoc = $sub_merchant_settlement_assoc;
            if(!empty($paymentMethods)){
                $paymentMethods->sub_merchant_settlement_assoc = $sub_merchant_settlement_assoc;
            }
        }

        return [$error_code, $error_message];
    }


    public function validateMerchantSettlement($payment_data, $merchantCommission, CCPayment $ccpayment, $is_for_new_api_service = false, ?PaymentMethods $paymentMethods)
    {
        $error_code = "";
        $error_message = "";
        $settlement_id = BrandConfiguration::chooseSettlementIdByInstallment(
            $payment_data['installments_number'] ?? null,
            $merchantCommission->settlement_id,
            $merchantCommission->single_payment_settlement_id,
                $paymentMethods->card_type ?? '',
            $merchantCommission->debit_card_settlement_id
        );
        $settlement = new Settlement();
        $settlement = $settlement->getById($settlement_id);
        $ccpayment->settlementObj = $settlement;
        if(!empty($paymentMethods)){
            $paymentMethods->settlementObj = $settlement;
        }
        if (empty($settlement)){
            if($is_for_new_api_service){
             $error_code = ApiService::API_SERVICE_SETTLEMENT_NOT_FOUND;
            }else {
                $error_code = 43;
            }
            $error_message = "Settlement date was not set";
        }

        return [$error_code, $error_message];
    }

    public static function sariTaxiImportTransactionRequestRules()
    {
        return [
            "currency_code" => "required|in:".implode(',',(new GlobalCurrency())->getSystemSupportedCurrencyInfoByIndex()),
            "invoice_id" => self::invoiceIdRule(),
            "terminal_no" => "required",
            "invoice_description" => "required",
            "transaction_type" => "required|in:".implode(',',SariTaxi::REMOTE_SALE_TRANSACTION_TYPE_LIST),
            "status" => "required|in:".implode(',',SariTaxi::STATUS_LIST),
            'cc_no' => 'required|regex:/^\d{16,20}$/',
            'merchant_key' => 'required|string|max:128',
            'hash_key' => 'required|max:256',
            'items' =>
                    ['required',
                        function($attribute, $value, $fail)
                        {
                            list($is_valid, $fail_message) = self::validMarketPlaceItems($value);
                            if(!$is_valid){
                                $fail($fail_message);
                            }
                        }
                    ],
            'total' => 'required|numeric|gt:0',
        ];
    }
    public static function getMerchantTerminalRequestValidationRules($activity = null , $is_virtual = false, $merchant_id = null, $input = []): array
    {
        $virtual_rules = [
            'status_code' => 'required',
            'brand_sharing' => 'required',
        ];

        if ($is_virtual) {
            $virtual_rules = array_merge($virtual_rules, [
                'virtual_pos_url' => 'required',
                'hosting_tax_no' => 'required',
                'payment_gw_tax_no' => 'required',
            ]);
        } else {
             $virtual_rules = array_merge($virtual_rules, [
                'serial_no' => 'nullable|min:8|max:22',
                'brand_code' => 'required',
                'model' => 'required',
                'pin_pad' => 'required',
                'contact_less' => 'required',
                'connection_type' => 'required',
            ]);
        }
        if(BrandConfiguration::call([BackendMix::class, 'allowToCreateMerchantTerminalOnlySelectedField'])){
            $virtual_rules = [];
        }
        $numeric = '';
        if (BrandConfiguration::call([BackendMix::class, 'isMerchantTerminalIdNumeric'])) {
            $numeric = '|numeric';
        }
        if ($activity == "edit_terminal") {

            $rules = [
                'terminal_id' => 'nullable'.$numeric,
                'type' => 'nullable',
                'status' => 'required',
            ];

            if (BrandConfiguration::isAllowedIKSMerchant()) {
                $rules = array_merge($rules, $virtual_rules);
            }
        } else {
            $rules = [
                'terminal_id' => 'required'.$numeric,
                'type' => 'required',
                'status' => 'required',
            ];
			
			if($activity == 'import_add' || $activity == 'import_add_ignore_import_file'){
				
				$rules = Arr::merge($rules, [
					'terminal_id' => 'required|array|min:1',
					'terminal_id.*' => 'required'.$numeric,
					'import_file' => 'required|mimes:xlsx,xls',
				]);
				
				if(isset($virtual_rules['serial_no']) && !empty($virtual_rules['serial_no'])){
					$virtual_rules = Arr::merge($virtual_rules, [
						'serial_no' => 'nullable|array',
						'serial_no.*' => 'nullable|min:8|max:22',
					]);
				}
			}
			
			if($activity == 'import_add_ignore_import_file' && isset($rules['import_file'])){
				unset($rules['import_file']);
			}
			
            if (BrandConfiguration::isAllowedIKSMerchant()) {
                $rules = array_merge($rules, $virtual_rules);
            }
        }
        if($activity == MerchantTerminals::EDIT_TERMINAL_ACTIVITY || $activity == MerchantTerminals::IMPORT_ADD_IGNORE_IMPORT_FILE_ACTIVITY){
            if (
                BrandConfiguration::call([BackendMix::class, 'allowImportPavoTransactionByBkmSerialNo'])
                && !empty($input['type'])
                && $input['type'] == MerchantTerminals::PAVO_TYPE
            ) {
                $rules['serial_no'] = [
                    'required',
                    function ($attribute, $value, $fail) use ($merchant_id) {
                        $is_duplicate = self::isExistSerialNoMerchantWise($value, $merchant_id);
                        $message = __('The serial number :serial_no must be unique for merchant.', ['serial_no' => (Arr::isOfType($value) ? Arr::implode(', ', $value) : $value)]);
                        if ($is_duplicate) {
                            $fail($message);
                        }
                    }
                ];
            }
        }
        return $rules;
    }

    public static function getMerchantTransactionLimitValidationRules($request): array
    {
        $rules = [
            'payment_type_id' => 'required',
            'transaction_type' => 'required',
            'transaction_wise_min_amount' => 'numeric|min:0|max:99999999',
            'transaction_wise_max_amount' => 'numeric|min:0|max:99999999',
            'currency_id' => 'required|integer',
            'merchant_id' => 'required|integer',
            'daily_max_amount' => 'numeric|min:0|max:'.GlobalMerchant::getMerchantTransactionDailyAmountLimit(),
            'daily_max_no' => 'integer|digits_between:0,99999999',
            'monthly_max_amount' => 'numeric|min:0|max:9999999999',
            'monthly_max_no' => 'integer|digits_between:0,99999999',
        ];
        
        if ($request->has('payment_type_id') && is_array($request->payment_type_id)){
            $rules = array_merge($rules, [
                'payment_type_id.*' => 'required|integer',
            ]);

        }
        return $rules;
        
    }


    public static function ytReportRequestRules(){
        return [
            "transaction_id" => "required|string|max:100",
            "record_type" => "required|string|max:1|in:".Arr::implode(',', Reports\Abstracts\AbstractBTransReport::recordTypes()),
            "transaction_type"=>"required|integer|in:".Arr::implode(',',array_keys(GovBTransYT::transactionTypes())),
            "record_unique_id" => "required|string|unique:gov_btrans_yt|max:50",
            "sender_brand_full_company_name" => "required|string|max:300",
            "is_client_sender" => "required|string|max:1|in:".Arr::implode(',',[GovBTransYT::IS_CLIENT_YES,GovBTransYT::IS_CLIENT_NO]),
            "merchant_id" => "required|integer|digits_between:1,20",
            "operation_type" => "required|string|max:5",
            "sender_vkn" => "required|string|max:10",
            "sender_company_name" => "nullable|string|max:300",
            "sender_name" => "nullable|string|max:50",
            "sender_surname" => "nullable|string|max:50",
            "sender_tckn" => "nullable|string|max:50",
            "sender_country_code" => "nullable|string|max:2",
            "sender_address" => "nullable|string|max:100",
            "sender_city" => "nullable|string|max:50",
            "sender_district" => "nullable|string|max:10",
            "sender_zip_code" => "nullable|string|max:5",
            "sender_license_tag" => "nullable|string|max:3",
            "sender_authorized_phone" => "nullable|string|max:15",
            "sender_authorized_email" => "nullable|string|max:30",
            "receiver_vkn" => "nullable|string|max:10",
            "receiver_company_name" => "nullable|string|max:300",
            "receiver_name" => "nullable|string|max:50",
            "receiver_surname" => "nullable|string|max:50",
            "receiver_tckn" => "nullable|string|max:50",
            "receiver_country_code" => "nullable|string|max:2",
            "receiver_address" => "nullable|string|max:100",
            "receiver_city" => "nullable|string|max:50",
            "receiver_district" => "nullable|string|max:10",
            "receiver_zip_code" => "nullable|string|max:5",
            "receiver_license_tag" => "nullable|string|max:3",
            "receiver_authorized_phone" => "nullable|string|max:15",
            "receiver_authorized_email" => "nullable|string|max:30",
            "currency_code" => "nullable|string|max:3",
            "activation_date" => "nullable|string|max:8",
            "balance" => "nullable|".self::amountValidationRule(16),
            "brand_bank_name" => "nullable|string|max:50",
            "brand_bank_code" => "nullable|string|max:8",
            "brand_bank_branch_code" => "nullable|string|max:50",
            "merchant_bank_account_Iban" => "nullable|string|max:40",
            "merchant_bank_account" => "nullable|string|max:40",
            "sender_brand_vkn"=> "nullable|string|max:10",
            "sender_id_type"=>"nullable|integer|digits:1",
            "sender_credit_card_no"=> "nullable|string|max:26",
            "sender_debit_card_no"=> "nullable|string|max:26",
            "is_client_receiver"=> "nullable|string|max:1|in:".Arr::implode(',',[GovBTransYT::IS_CLIENT_YES,GovBTransYT::IS_CLIENT_NO]),
            "receiver_id_type"=> "nullable|digits:1",
            "receiver_bank_code"=> "nullable|string|max:8",
            "receiver_bank_branch_code"=> "nullable|string|max:50",
            "receiver_bank_account_Iban"=> "nullable|string|max:40",
            "receiver_bank_account"=> "nullable|string|max:40",
            "receiver_bank_name"=> "nullable|string|max:50",
            "receiver_brand_vkn"=> "nullable|string|max:10",
            "receiver_brand_full_company_name"=> "nullable|string|max:300",
            "sender_bank_name"=> "nullable|string|max:50",
            "sender_bank_code"=> "nullable|string|max:8",
            "sender_bank_branch_code"=> "nullable|string|max:50",
            "sender_bank_account_Iban"=> "nullable|string|max:40",
            "sender_bank_account"=> "nullable|string|max:40",
            "transactionable_type"=> "nullable|string|max:300",
            "sender_e_money_account"=>"nullable|string|max:26",
            "sender_card_no"=>"nullable|string|max:16",
            "receiver_brand_bank_iban"=>"nullable|string|max:26",
            "receiver_e_money_account"=>"nullable|string|max:26",
            "receiver_card_no"=>"nullable|string|max:16",
            "receiver_credit_card_no"=>"nullable|string|max:16",
            "receiver_debit_card_no"=>"nullable|string|max:16",
            "transaction_date"=>"required|nullable|string|max:8",
            "transaction_time"=>"required|nullable|string|max:6",
            "transaction_amount"=>"required|numeric",
            "transaction_description_enum"=>"required|integer|between:1,9",
            "transaction_channel"=>"nullable|integer|between:1,6",
            "branch_authorised_vkn"=>"nullable|string|max:10",
            "branch_authorised_name"=>"nullable|string|max:300",
            "branch_authorised_city"=>"nullable|string|max:20",
            "client_description"=>"nullable|string|max:300",
            "process_date" => "required|string|max:8",
            "process_time" => "nullable|string|max:6",
            "net_amount" => "nullable|".self::amountValidationRule(16),
            "commission" => "nullable|".self::amountValidationRule(16),
            "company_code" => "required|string|max:5",
        ];
    }

    public static function epkbbRequestRules(){
        return [
            "merchant_id"=>"required|integer|digits_between:1,20",
            "record_type" => "required|string|max:1|in:".Arr::implode(',', Reports\Abstracts\AbstractBTransReport::recordTypes()),
            "record_unique_id" => "required|string|unique:gov_btrans_epkbb|max:50",
            "operation_type" => "required|string|max:5",
            "transaction_id" => "required|string|max:100",
            "merchant_country_code"=>"required|max:2",
            "merchant_city"=>"required|string|max:20",
            "merchant_district"=>"required|string|max:16",
            "merchant_address"=>"required|string|max:100",
            "merchant_license_tag"=>"required|string|max:30",
            "merchant_authorized_phone"=>"required|string|max:15",
            "transaction_type"=>"required|integer||in:".Arr::implode(',',array_keys(GovBTransEPKBB::transactionTypes())),
            "merchant_vkn"=>"nullable|string|max:10",
            "merchant_company_name"=>"nullable|string|max:300",
            "users_name"=>"nullable|string|max:50",
            "users_surname"=>"nullable|string|max:50",
            "merchant_tckn"=>"nullable|string|max:50",
            "merchant_zip_code"=>"nullable|string|max:5",
            "merchant_authorized_email"=>"nullable|email|max:30",
            "merchant_currency_code"=>"nullable|string|max:3",
            "merchant_id_type"=>"nullable|integer|between:1,5",
            "merchant_status"=>"nullable|integer|between:1,2",
            "merchant_close_date"=>"nullable|string|max:8",
            "card_status"=>"nullable|string|max:20",
            "card_activation_date"=>"nullable|string|max:8",
            "card_close_date"=>"nullable|string|max:8",
            "merchant_activation_date"=>"nullable|string|max:8",
            "card_number"=>"nullable|string",
            "account_type"=>"nullable|string|max:8",
            "merchant_balance"=>"nullable|".self::amountValidationRule(16),
            "company_code" => "required|string|max:5",
        ];
    }

    public static function okkibRequestRules(){
        return [
            "record_type"=>"required|string|max:1|in:".Arr::implode(',', Reports\Abstracts\AbstractBTransReport::recordTypes()),
            "transaction_id" => "required|string|max:100",
            "record_unique_id" => "required|string|unique:gov_btrans_okkib|max:50",
            "operation_type" => "required|string|max:5",
            "card_holder_vkn"=>"nullable|string|max:10",
            "card_holder_title"=>"nullable|string|max:300",
            "card_holder_name"=>"nullable|string|max:50",
            "card_holder_surname"=>"nullable|string|max:50",
            "card_holder_id_type"=>"nullable|string|max:1",
            "card_holder_tckn"=>"nullable|string|max:50",
            "card_no"=>"required|string|max:16",
            "bank_type"=>"required|string|max:1",
            "bank_eft_code"=>"required|string|max:4",
            "bank_atm_code"=>"required|string|max:15",
            "process_date"=>"required|string|max:8",
            "transaction_amount"=>"required|numeric|decimal:2",
            "net_amount"=>"required|".self::amountValidationRule(16),
            "commission"=>"required|".self::amountValidationRule(16),
            "currency_code"=>"required|string|max:3",
            "client_description"=>"nullable|string|max:300",
            "transactionable_type"=> "nullable|string|max:300",
            "company_code" => "required|string|max:5",
        ];
    }

    public static function ephpycniRequestRules(){
        return [
            "record_type"=>"required|string|max:1|in:".Arr::implode(',', Reports\Abstracts\AbstractBTransReport::recordTypes()),
            "transaction_id" => "required|string|max:100",
            "record_unique_id" => "required|string|unique:gov_btrans_ephpycni|max:50",
            "operation_type" => "required|string|max:5",
            "is_client_sender"=>"required|string|max:1|in:".Arr::implode(',',[GovBTransEPHPYCNI::IS_CLIENT_YES,GovBTransEPHPYCNI::IS_CLIENT_NO]),
            "sender_brand_vkn"=>"nullable|string|max:10",
            "sender_brand_full_company_name"=>"nullable|string|max:300",
            "sender_name"=>"nullable|string|max:50",
            "sender_surname"=>"nullable|string|max:50",
            "sender_id_type"=>"nullable|integer|digits:1",
            "sender_tckn"=>"nullable|string|max:50",
            "merchants_country_code"=>"required|string|max:2",
            "merchants_address"=>"nullable|string|max:100",
            "merchants_district"=>"nullable|string|max:16",
            "merchants_zip_code"=>"nullable|string|max:5",
            "merchants_license_tag"=>"nullable|string|max:3",
            "merchants_city"=>"nullable|string|max:20",
            "merchants_authorized_phone"=>"required|string|max:15",
            "merhcants_authorized_email"=>"nullable|string|max:30",
            "merchant_id"=>"required|integer|digits_between:1,20",
            "merchants_currency"=>"required|string|max:3",
            "account_type"=>"required|integer|digits:1",
            "receiver_name"=>"required|string|max:50",
            "receiver_surname"=>"required|string|max:50",
            "receiver_id_type"=>"required|integer|digits:1",
            "receiver_tckn"=>"required|string|max:50",
            "process_date"=>"required|string|max:8",
            "transaction_channel"=>"nullable|integer|digits:1",
            "receiver_bank_name"=>"nullable|string|max:50",
            "net_amount_try"=>"required|numeric|decimal:2",
            "net_amount"=>"required|".self::amountValidationRule(16),
            "commission"=>"required|".self::amountValidationRule(16),
            "currency_code"=>"required|string|max:3",
            "client_description"=>"nullable|string|max:300",
            "transactionable_type"=>"nullable|string|max:300",
            "company_code" => "required|string|max:5",
        ];
    }

    public static function reportStatusRules(){
        return [
            'status' =>'nullable|integer|digits:1|in:'.implode(',',array_keys(Reports\Abstracts\AbstractBTransReport::reportModelStatuses())),
            'report_type' =>'nullable|integer|digits:1|in:'.implode(',',array_keys(Reports\Abstracts\AbstractBTransReport::reportTypes())),
            'limit' =>'nullable|integer|digits_between:1,3',
        ];
    }

    public static function invoiceIdRule()
    {
        return 'required|string|max:50';
    }


    public static function getScheduleReportValidationRulesMessages()
    {
        $rules = [
            'report_type' => 'required',
            'destination_type' => 'required',
            'settlement_id' => 'required',
            'credentials' => 'required'
        ];
        $messages = [
            'report_type.required' => __('Report Type is required'),
            'destination_type.required' => __('Destination Type is required'),
            'settlement_id.required' => __('Settlement is required'),
            'credentials.required' => __('Credentials is required'),
        ];

        return [
            'rules' => $rules,
            'messages' => $messages
        ];
    }

    public static function walletUserDateOfBirthValidationRules($validate_year = null)
    {
		if(empty($validate_year)){
			$validate_year = Profile::VALIDATE_KYC_YEAR_13;
		}
		
	    $date_of_birth_rules = 'required|date_format:Y|before:-'.$validate_year.' years';
	    if(BrandConfiguration::userAgeRestrictionOnKycUpdate()){
		    $date_of_birth_rules = 'required|date_format:d/m/Y|after:-'.$validate_year.' years';
	    }
	    if(BrandConfiguration::isAllowDayAndMonthOnKyc()){
		    $date_of_birth_rules = 'required|date_format:d/m/Y|before:-'.$validate_year.' years';
	    }
        return $date_of_birth_rules;
    }

    public static function getSiteSettingValidationRules()
    {
        $rules = [
            'integration' => 'sometimes|mimes:zip|max:51,200',
            'logo_path' => 'sometimes|mimes:png,jpg,jpeg|max:2048',
        ];
        if (BrandConfiguration::isAllowedSiteSettingHelpDocument()){
            $rules = array_merge($rules, ['help_doc' => 'sometimes|mimes:zip,pdf,docx|max:10240']);
        }
        return $rules;
    }

    /**
     * @param $amount_length
     * the length of amount before decimal
     *
     * @param $decimal_length
     * the length of amount after decimal
     *
     * For 100.20 $amount_length is 3, $decimal_length is 2
     *
     * @return string
     */
    public static function amountValidationRule($amount_length, $decimal_length=2): string
    {
        return "regex:/^\d{1,$amount_length}(\.\d{1,$decimal_length})?$/";
    }

    public static function importedTransactionRequestRules()
    {
        return [
            'merchant_id' => 'required|integer',
            'merchant_terminals_id' => 'required|integer',
            'merchant_terminal_no' => 'required|string',
            'imported_transaction_history_id' => 'sometimes|integer',
            'invoice_id' => self::invoiceIdRule(),
            'remote_acquirer_reference' => 'sometimes|string',
            'remote_rrn' => 'sometimes|integer',
            'remote_transaction_state_id' => 'sometimes|integer',
            'is_refunded_transaction' => 'sometimes|integer',
            'imported_transaction_data' => 'required|array',
            'type' => 'required|integer',
        ];
    }

    public static function getSendMoneyCommissionInfoRequestRules()
    {
        return [
            'amount' => 'required|numeric|gt:0',
            'currency_code' => "required|in:".implode(',',(new GlobalCurrency())->getSystemSupportedCurrencyInfoByIndex()),
        ];
    }

    public static function notificationEventRequestRules()
    {
        return[
            'event_id' =>'required|numeric',
            'notification_type' =>'required|numeric',
            'user_type' =>'required|numeric',
            'receivers' =>'required|array',
            'status' =>'required|numeric',
        ];
    }

    public static function walletUserKycUpdateValidationRulesAndMessages()
    {
		$validated_year = BrandConfiguration::call([FrontendWallet::class, 'validKycBirthYear']);
		
        $rules = [
            'name' => 'required|max:255|string',
            'surname' => 'required|max:255|string',
            'date_of_birth' => self::walletUserDateOfBirthValidationRules($validated_year),
            'tckn' => 'required|max:255',
            'sector'=>'required',
        ];
        $messages = [
            'name.required' => 'Name is required',
            'surname.required' => 'Surname is required',
            'date_of_birth.required' => 'Date of birth is required',
            'date_of_birth.before' => __('You must be over :year years old to register',
	            [
					'year' => $validated_year,
		            'brand_name' => Str::titleCase(config()->get('brand.name')),
	            ],
            ),
            'tckn.required' => 'TCKN is required',
            'sector.required' => 'Profession is required',
        ];
        if( ! BrandConfiguration::hideSecrectQuestionSectionFromUserKycForm() ){
            $rules['question_one'] = 'required';
            $rules['answer_one'] = 'required|max:255';
            
            $messages['question_one.required'] = 'Question is required';
            $messages['answer_one.required'] = 'Answer is required';
            $messages['answer_one.max'] = 'Answer is too long';
        }

        if (BrandConfiguration::isAllowedIncomeInfo()) {
            $rules['income_info'] = 'nullable|numeric';
            $messages['income_info.numeric'] = "Invalid income info";
        }

        if (BrandConfiguration::call([Mix::class, 'shouldApplyKycVerificationStaticInfo'])) {
            $rules['purpose_of_ac_opening'] = 'required|array';
            $rules['monthly_estimated_trade_volume_range_start'] = 'required|integer|min:0';
            $rules['monthly_estimated_trade_volume_range_end'] = 'required|integer';
            $rules['estimated_number_of_transactions_per_month_range_start'] = 'required|integer|min:0';
            $rules['estimated_number_of_transactions_per_month_range_end'] = 'required|integer';
            $rules['average_income_information_range_start'] = 'required|integer|min:0';
            $rules['average_income_information_range_end'] = 'required|integer';

            $messages['purpose_of_ac_opening.required'] = 'Purpose of Account Opening is required';

            $messages['monthly_estimated_trade_volume_range_start.required'] = 'Estimated Monthly Trading Volume is required';
            $messages['monthly_estimated_trade_volume_range_end.required'] = 'Estimated Monthly Trading Volume is required';
            $messages['monthly_estimated_trade_volume_range_start.integer'] = 'Invalid Estimated Monthly Trading Volume';
            $messages['monthly_estimated_trade_volume_range_end.integer'] = 'Invalid Estimated Monthly Trading Volume';

            $messages['estimated_number_of_transactions_per_month_range_start.required'] = 'Estimated Number of Transactions per Month is required';
            $messages['estimated_number_of_transactions_per_month_range_end.required'] = 'Estimated Number of Transactions per Month is required';
            $messages['estimated_number_of_transactions_per_month_range_start.integer'] = 'Invalid Estimated Number of Transactions per Month';
            $messages['estimated_number_of_transactions_per_month_range_end.integer'] = 'Invalid Estimated Number of Transactions per Month';

            $messages['average_income_information_range_start.required'] = 'Average Income Information is required';
            $messages['average_income_information_range_end.required'] = 'Average Income Information is required';
            $messages['average_income_information_range_start.integer'] = 'Invalid Average Income Information';
            $messages['average_income_information_range_end.integer'] = 'Invalid Average Income Information';
        }
        return [
            'rules' => $rules,
            'messages' => $messages
        ];
    }
    public static function businessApplicationRequestRules()
    {
        $phone_format = '|regex:/^[+]\d+$/';
        $email_validation_rules = "email";

        $rules =  [
            'merchant_name' => 'required|min:2|max:50',
            'company_name' => 'required|min:2|max:40',
            'website' => 'required|max:60',
            'auth_person_name' => 'required|min:2|max:40',
            'auth_person_email' => 'required|'. $email_validation_rules.'|string|max:50|unique_email:' . User::MERCHANT,
            'auth_person_phone' => 'required|min:2|max:20|unique_phone:'  . User::MERCHANT.$phone_format,
        ];

        if( config('brand.name_code') != config('constants.BRAND_NAME_CODE_LIST.PL')) {
            $rules = array_merge($rules,
                [
                    //            'google_captcha' => 'required',
                    'address' => 'required|min:5|max:300',
                    'zip_code' => 'max:30',
                    'city' => 'max:30',
                    'country' => 'max:40',
                    'tax_office' => 'max:40',
                    'tax_number' => 'max:40',
                    'business_area' => 'max:50',
                    'expected_volume' => 'max:60',
                    'preferred_payment_method' => 'required',
                    'contract_person_name' => 'max:100',
                    'contract_person_phone' => 'max:100',
                    'contract_person_email' => 'max:100',
                    'merchant_logo' => 'mimes:png,jpg,jpeg|max:2048',
                    'document' => 'array',
                    'document.*' => 'mimes:png,jpg,jpeg,csv,txt,xlx,xls,pdf,zip|min:2|max:25600'
                ]);
        }

        if(BrandConfiguration::call([BackendMix::class, "isAllowIframeMerchantApplication"])) {
            $rules = Arr::merge($rules, [
                'tckn_no' => 'required|string|max:11',
                'application_type' => 'sometimes|array',
                'application_type.*' => 'numeric',
                'contract_person_name' => 'max:100|string',
                'contract_person_phone' => 'max:40|string',
                'address' => 'sometimes|string',
                'city' => 'sometimes|string|max:30',
                'country' => 'sometimes|string|max:40',
                'business_area' => 'sometimes|string|max:50',
                'preferred_payment_method' => 'required|array',
                'merchant_activity_duration' => 'sometimes|numeric',
                'expected_volume' => 'sometimes|string|max:60',
                'district' => 'sometimes|string|max:50',
                'tax_number' => 'sometimes|string|max:40',
                'number_of_merchant_employee' => 'sometimes|numeric',
                'reference_no' => 'sometimes|string|max:20',
                'contact_way' => 'sometimes|numeric',
                'number_of_store' => 'sometimes|numeric',
                'application_date' => 'sometimes|string'
            ]);
        }
        return $rules;

    }


    public static function transactionDetailsRequestRules ($type): array
    {
        if ($type == Transaction::WITHDRAWAL_TRANS || $type == Transaction::CASHOUT_TRANS) {
            $rules = [
                'transaction_id' => 'required_without:unique_id',
                'unique_id' => 'required_without:transaction_id',
            ];
        } else {
            $rules = ['sale_reference' => 'required'];
        }
        return $rules + [
            'merchant_key' => 'required',
            'hash_key' => 'required'
        ];
    }

    public static function manualPosRequestRules()
    {
        $rules =  [
            'bill_phone' => 'sometimes|regex:/^[0-9]+$/',
            'bill_tckn' => 'sometimes|regex:/^[0-9]+$/|digits:11',
            'bill_tax_no' => 'sometimes|regex:/^[0-9]+$/',
        ];
        $messages = [
          'bill_tckn' => __("The tckn format is invalid"),
          'bill_tckn.digits' => __("The tckn must be 11 digits"),
          'bill_phone' => __("The phone format is invalid"),
          'bill_tax_no' => __("The  tax no format is invalid"),
        ];
        
        return [$rules, $messages];

    }

    public static function ccHolderNameRule()
    {
        $rule = 'required|string|max:100';
        if(BrandConfiguration::isBrand_whichDoesntWantCCHolderNameParamValidation()){
            $rule =  'sometimes|string|max:100';
        }
        return $rule;
    }

    public static function announcementValidation():array
    {
        return  [
            'panel_attachment' => 'sometimes|mimes:jpeg,png,jpg,webp,tiff|max:5120',
            'email_attachment' => 'sometimes|mimes:jpeg,png,jpg,webp,tiff,pdf,csv,xls,xlsx,doc,docx,ppt,pptx|max:5120',
        ];
    }

    public static function userDeviceValidation():array
    {
        return [
            'push_notification_key' => ['required_without:huawei_push_notification_key'],
            'device_name' => ['nullable', 'max:191'],
            'device_brand' => ['nullable', 'max:191'],
            'first_connection_ip' => ['nullable', 'max:191'],
            'network_operator' => ['nullable', 'max:191'],
            'system_version' => ['nullable', 'max:191'],
            'user_agent' => ['nullable', 'max:191'],
            'is_tablet' => ['nullable', 'boolean'],
            'huawei_push_notification_key' => ['nullable', 'max:191'],
            'apns_token' => ['nullable', 'max:191'],
            'is_active' => ['nullable', 'boolean'],
            'app_unique_key' => ['nullable', 'max:191'],
        ];
    }

    public static function requestMoneyValidator($request){

        $rules = [
            'amount' => 'required|numeric|gt:0',
            'currency' => 'required',
            'explanation' => BrandConfiguration::disableSendMoneyExplainFieldRequired() ? 'nullable' : 'required',
        ];
        if(!empty($request['customer_number'])) {
            $rules = Arr::merge($rules, [
                'customer_number' => 'required|regex:/^[0-9]{10}$/'
            ]);
        } else {
            $rules = Arr::merge($rules, [
                'phone' => 'required|min:13|regex:/^[+]\d+$/'
            ]);
        }

        return Validator::make($request, $rules, [
            'phone.min' => __('Phone number format is invalid'),
            'phone.required' => __('Please enter phone number'),
            'explanation.required' => __('Explanation is a required field')
        ]);
    }

    public static function ruleAlphaNumericSpaces()
    {
        return [
            'rule'=>'regex:/^[a-z0-9\s]*$/i',
            'message'=>__(':attribute should include only english characters')
        ];
    }

    public static function integratorCommissionValidation(){

        $rules =  [
            'integrator_id' => 'required|integer',
            'merchant_id' => 'required_if:commission_type,'. IntegratorCommission::MERCHANT_WISE,
            'commission_percentage' => 'nullable|gte:0',
            'commission_fixed' => 'nullable|gte:0',
            'installment.*.merchant_com_percentage' =>'nullable|gte:0',
            'installment.*.merchant_com_fixed' => 'nullable|gte:0',
            'status' => 'required',
        ];

        $messages = [
            'merchant_id.required_if' => __("Merchant is required"),
            'commission_percentage.gte' => __("Commission percentage should be greater than or equal to 0"),
            'commission_fixed.gte' => __("Commission fixed should be greater than or equal to 0")
        ];
        
        return [$rules,$messages];
    }

    public static function integratorRevenueReportRequestValidationRules(): array
    {
        $rules = [
            'merchant_id' => ['nullable', 'array'],
            'merchant_id.*' => ['numeric'],
            'daterange' => ['nullable', 'string']
        ];

        return [
            $rules, $messages ?? []
        ];
    }

    public static function pointPaymentQueryRequestValidationRules() : array
    {
        $rules =  [
            'cc_holder_name'=> 'required|string|max:50|regex:/^((?!\d).)*$/',
            'expiry_month'  => 'required',
            'expiry_year'   => 'required|integer',
            'pos_id'        => 'required',
            'customer_id'   => 'required',
            'cc_no'         => 'required|regex:/^\d{16,20}$/',
            'customer_ip'   => 'sometimes',
            'currency_code' => 'required|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
            'hash_key'      => 'required|string'
        ];

        return [
            $rules, $messages ?? []
        ];
    }

    public static function getRemainingLimitValidationRules()
    {
        return [
            'currency_code' => ['required', Rule::in((new GlobalCurrency())->getSystemSupportedCurrencyInfoByIndex())],
            'transaction_type' => ['required', Rule::in((new Transaction())->transactionTypeMappingForRemainingLimit())],
        ];
    }

    public static function appVersionValidationRules($input,$id=null): array
    {
        $rules['rules'] = [
            'platform_type' => 'required',
            'app_code' => ['required','max:50',
                Rule::unique('app_versions')
                    ->where('platform_type',$input['platform_type'])
                    ->ignore($id)
            ],
            'version' => 'required|max:20',
            'description' => 'nullable',
            'is_force_upgrade' => 'required'
        ];
        $rules['messages'] =[
            'app_code.required' => __("Code is required")
        ];

        return $rules;
    }

    public static function appVersionQueryValidation($input): array
    {
        $rules['rules'] = [
            'platform_type' => 'required|in:'.implode(',', array_keys(System::DEVICE_LIST)),
            'app_code' => 'required|max:50|'.Rule::exists('app_versions')->where('platform_type', $input['platform_type'] ?? null),
        ];
        return $rules;
    }
    
    public static function pointPaymentRequestValidationRules($input): array
    {
        $rules['rules'] = [
            'point_total'       => ['required','numeric','min:0','not_in:0'],
            'point_usage_type'  => 'sometimes|in:'.implode(',',[CCPayment::POINT_USAGE_TYPE_BRAND,CCPayment::POINT_USAGE_TYPE_STANDARD]),
        ];
        return $rules;
    }

    public static function merchantStatusCheckVknTcknValidationRules($input): array
    {
        $rules['rules'] = [
            'vkn' => 'sometimes|digits:10|required_without:tckn',
            'tckn' => 'sometimes|digits:11|required_without:vkn',
        ];
        $rules['messages'] = [
            'vkn.required_without' => __('VKN number is required.'),
            'vkn.digits' => __('VKN must be 10 digits.'),
            'tckn.digits' => __('TCKN must be 11 digits.')
        ];

        return $rules;
    }



    public static function ccBlockCreateBinSingleRequest(): array
    {
        $bin_digit_length = BrandConfiguration::call([Mix::class, 'customBlockCardBinDigit']);

        $rules = [
            "card_no" => "required|min:4|max:".$bin_digit_length."|regex:/^[0-9]{".$bin_digit_length."}$/",
            "block_reason" => "required|max:64",
            "blocked_from" => "required"
        ];
        return $rules;
    }
	
	public static function passwordResetRequest($input_data): array
	{
		$rules = [
			'email' => 'required|email',
			'decoded_email' => 'email|same:email',
		];
		$message = [
			'email.email' => __('Email format is invalid'),
			'decoded_email.email' => __('Invalid email'),
		];
		
		
		if (isset($input_data['security_image']) || BrandConfiguration::call([Mix::class, 'allowSecurityImageOnCreateUserAdminMerchant'])) {
			
			$rules = Arr::merge($rules, [
				'security_image' => 'required',
			]);
			
		}

        if (isset($input_data['is_read_and_approve']) || (@$input_data['user_type'] == Profile::MERCHANT &&
        BrandConfiguration::call([Mix::class, 'isAllowAgreementReadAndApproveOnMerchantPanel']))) {
            $rules = Arr::merge($rules,[
                'is_read_and_approve' => 'required',
            ]);
            $message = [
                'is_read_and_approve.required' => __('You must accept terms and condition'),
            ];
        }
		
		if((isset($input_data['question_one']) && isset($input_data['answer_one'])) || BrandConfiguration::call([Mix::class, 'allowSecurityImageOnCreateUserAdminMerchant'])){
			
			$rules = Arr::merge($rules, [
				'question_one' => 'required',
				'answer_one' => 'required|max:255'
			]);
			
			$message = Arr::merge($message, [
				'decoded_email.same' => __('Invalid email'),
				'answer_one.max' => __('Answer is too long')
			]);
			
		}
		
		return [
			$rules,
			$message,
		];
	}

    public static function customizedCostRequest() : array
    {
        $rules = [
            'name' => ['required', 'string', 'min:2'],
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id'
            ],
            'iban' => ['required', 'string', 'regex:/^((TR)[ \-]?[0-9]{2})(?=(?:[ \-]?[A-Z0-9]){9,30}$)((?:[ \-]?[A-Z0-9]{3,5}){2,7})([ \-]?[A-Z0-9]{1,3})?$/',
                function($attribute, $value, $fail)
                {
                    list($is_valid, $fail_message) = self::validateIbanNo($value);
                    if (!$is_valid)
                    {
                        $fail($fail_message);
                    }
                }
            ],
            'phone' => ['required', 'min:10', 'max:13', 'regex:/^\+\d+/'],
            'type' => ['required', 'integer', 'in:'. implode(',', Arr::keys(MerchantCustomizedCostSetting::getTypes()))],
            'from_bank_id' => ['required', 'integer', 'exists:brand_bank_accounts,id']
        ];
        $message = [
            'currency_id.required' => __('Currency Must Required'),
            'currency_id.integer' => __('Currency is invalid'),
            'currency_id.exists' => __('Undefined Currency'),
            'iban.required' => __('IBAN is required'),
            'iban.max' => __("IBAN can't be max than :max"),
            'iban.min' => __("IBAN can't be minimum than :min"),
            'phone.regex' => __("Invalid Receiver GSM number"),
            'phone.required' => __('Receiver GSM must be required'),
            'phone.max' => __("Receiver GSM can't be more than :max"),
            'phone.min' => __("Receiver GSM can't be less than :min"),
            'type.integer' => __('Invalid type'),
            'type.in' => __('Undefined Type'),
            'from_bank_id.required' => __('Report type is required'),
            'from_bank_id.integer' => __('Report type must be in integer'),
            'from_bank_id.exists' => __('Undefined From Bank')
        ];

        return [$rules, $message];
    }

    public static function merchantApplicationRequestValidationRules() : array
    {
        $rules = [
            'merchant_name'=> 'required|string|min:5|max:50|not_regex:/[\.,:]/',
            'company_name'=> 'required|string|min:5|max:50|not_regex:/[\.,:]/',
            'auth_person_name'=> 'required|string|min:6',
            'auth_person_phone'=> 'required|min:10|max:15',
            'tax_number'=> 'sometimes|digits:10',
            'expected_volume'=> 'sometimes|min:4',
        ];

        $messages = [
            'merchant_name.required' => __("Merchant name is required"),
            'merchant_name.min' => __("The Merchant name cannot be less than :min."),
            'merchant_name.not_regex' => __("Merchant name is invalid."),
            'company_name.required' => __("Company Name is required"),
            'company_name.min' => __("The Company Name cannot be less than :min."),
            'company_name.not_regex' => __("Company Name is invalid."),
            'auth_person_name.required' => __("Authorized Person Name is required"),
            'auth_person_name.min' => __("The Authorized Person Name cannot be less than :min."),
            'auth_person_phone_.required' => __("Authorized Person Phone Number is required"),
            'auth_person_phone_.min' => __("The Authorized Person Phone Number  cannot be less than :min."),
            'tax_number.digits' => __("The tax number must be :digits digits"),
            'expected_volume.min' => __("The expected volume cannot be less than :min."),
        ];

        if(BrandConfiguration::call([BackendMix::class, 'showCustomMerchantApplicationFields'])) {

            $rules['expected_volume'] = 'sometimes|string';
            $rules['auth_person_name']= 'required|string';
            $rules['auth_person_phone']= 'required|string';
            $rules += [
                'tckn_no' => 'required|numeric|digits:11',
                'website' => 'sometimes|string|max:100',
                'auth_person_phone'=> 'string',
                'auth_person_email' => 'sometimes|email',
                'address' => 'sometimes|string',
                'zip_code' => 'sometimes|string|max:30',
                'city' => 'sometimes|string|max:30',
                'country' => 'sometimes|string|max:40',
                'tax_office' => 'sometimes|string|max:100',
                'business_area' => 'sometimes|string|max:50',
                'preferred_payment_method' => 'sometimes|string|max:40',
                'contract_person_name' => 'sometimes|string|max:100',
                'contract_person_phone' => 'sometimes|string|max:40',
                'contract_person_email' => 'sometimes|email',
                'application_date' => 'sometimes|string|max:40',
                'contact_person_tckn' => 'sometimes|numeric|digits:11',
                'merchant_activity_duration' => 'sometimes|numeric',
                'number_of_store' => 'sometimes|numeric',
                'district' => 'sometimes|string',
            ];
            $messages = [];
            return [$rules, $messages];
        }

        return [$rules, $messages];
    }


    public static function updateAwaitingRefundRules()
    {
        return [
            'merchant_key' => 'required|string|max:128',
            'invoice_id' => self::invoiceIdRule(),
            'order_id' => 'required|string|max:50',
            'type' => 'required|integer',
            'hash_key' => 'required|string|max:255'
        ];
    }

    public static function saleRestoreValidationRules()
    {
        $rules = [
            'backup_table_type' => ['required', Rule::in(ReplicationRepository::TABLE_TYPE_SUCCESS_BACKUP)],
            'sale_id' => ['required'],
        ];

        $messages = [
            "backup_table_type.required" => __("Must be from backup sale"),
            "sale_id.required" => __("Sale id is required")
        ];

        return [$rules, $messages];
    }

    public static function nameSurnameValidationRules()
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
        ];
        return $rules;
    }
    
    public static function getBrandImportTransactionRequestRules($input)
    {
        $brand_import_transaction_rules = [
          "transaction_datetime" => "required",
          "payment_method" => "required|in:".Arr::implode(',',Arr::keys(BrandImport::getMethodTypes())),
          "type" => "string|required|in:".implode(',',BrandImport::REMOTE_SALE_TRANSACTION_TYPE_LIST),
          "status" => "string|required|in:".implode(',',BrandImport::REMOTE_SALE_STATUS_LIST),
          'cc_holder_name' => self::ccHolderNameRule(),
          "cc_no" => "required",
          "invoice_description" => "required",
          'currency_code' => PurchaseRequest::getCurrencyCodeValidationRules(),
          'installments_number' => 'required|numeric',
          'pos_bank_code' => 'sometimes|numeric',
          'pos_id' => 'sometimes|numeric|gt:0',
          'bin_number' => 'sometimes|numeric',
          'invoice_id' => self::invoiceIdRule(),
          "merchant_key" => "required",
          'total' => 'required|numeric|gt:0',
          "items" => 'required|array|min:1',
          "remote_original_bank_error_code" => 'sometimes|string',
          "remote_original_bank_error_description" => 'sometimes|string'
        ];


        if (isset($input['calculate_commission']) && $input['calculate_commission'] == BrandImport::CALCULATE_COMMISSION) {
            $brand_import_transaction_rules['remote_merchant_commission_percentage'] = "required";
            $brand_import_transaction_rules['remote_merchant_commission_fixed'] = "required";
            $brand_import_transaction_rules['remote_end_user_commission_percentage'] = "required";
            $brand_import_transaction_rules['remote_end_user_commission_fixed'] = "required";

            $brand_import_transaction_rules['remote_cot_percentage'] = "required";
            $brand_import_transaction_rules['remote_cot_fixed'] = "required";

            $brand_import_transaction_rules['remote_merchant_refund_commission'] = "required";
            $brand_import_transaction_rules['remote_merchant_refund_commission_fixed'] = "required";
            $brand_import_transaction_rules['remote_merchant_chargeback_commission'] = "required";
            $brand_import_transaction_rules['remote_merchant_chargeback_commission_fixed'] = "required";
        }
        return $brand_import_transaction_rules;
    }

    public static function getBrandImportedTransactionRefundRules(): array
    {
        return
          [
            "type" => "string|required|in:".implode(',',BrandImport::REMOTE_REFUND_TRANSACTION_TYPE_LIST),
            'merchant_key' => ['required', 'string', 'max:255'],
            'invoice_id' => self::invoiceIdRule(),
            'amount' => ['required', 'numeric', 'gt:0'],
            "refund_reason" => "sometimes|string|max:200",
            'refund_transaction_id' => ['required', 'string', 'max:255'],
            'remote_refund_transaction_datetime' => "required|date",
          ];

    }

    public static function validateTcknVknBlacklist($id = null)
    {
        $rules = [
            'tckn' => empty($id) ? 'required_without:vkn|unique:merchant_vkn_tckn_blacklist,tckn' : (BrandConfiguration::changeMerchantStausTcknVknDigit() ? 'required_without:vkn|max:11|unique:merchant_vkn_tckn_blacklist,tckn,' . $id : 'required_without:vkn|unique:merchant_vkn_tckn_blacklist,tckn,' . $id),
            'vkn' => empty($id) ? 'required_without:tckn|unique:merchant_vkn_tckn_blacklist,vkn' : (BrandConfiguration::changeMerchantStausTcknVknDigit() ? 'required_without:tckn|max:10|unique:merchant_vkn_tckn_blacklist,vkn,' . $id : 'required_without:tckn|unique:merchant_vkn_tckn_blacklist,vkn,' . $id),
            "black_list_reason" => 'required'
        ];

        $messages = [
            'tckn.required_without' => 'TCKN or VKN, One of them is mandatory',
            'vkn.required_without' => 'TCKN or VKN, One of them is mandatory',
            'black_list_reason' => 'Reason field is required'
        ];

        return [$rules, $messages];
    }

    public static function excelValidationTcknVknBlacklist($excelallData)
    {
        $status = true;
        $message = '';
        foreach ($excelallData as $key => $excelData) {
            $validator = Validator::make($excelData, [
                "tckn" => "required_without:vkn",
                "vkn" => 'required_without:tckn',
                "black_list_reason" => 'required'
            ]);
            if ($validator->fails()) {
                $status = false;
                $message = "Row " . $key . ": " . $validator->errors()->first();
            }
        }
        return [$status, $message];

    }

    public static function excelValidationIpRange($excelallData)
    {
        $status = true; $message = '';
        foreach ($excelallData as $key => $excelData) {
            $validator = Validator::make($excelData, [
              "ip_from" => "required",
              "ip_to" => "required"
            ]);
            if ($validator->fails()) {
                $status = false;
                $message = "Row " . $key . ": " . $validator->errors()->first();
            }
        }
        return [$status, $message];

    }

    public static function convertFailedTransactionToSuccessRules()
    {
        return [
            'merchant_key' => 'required|string|max:128',
            'invoice_id' => self::invoiceIdRule(),
            'hash_key' => 'required|string|max:255'
        ];
    }

    public static function addBankRegistrationValidation($input = null)
    {
        $rules = [
            "type"                        => "required|numeric",
            "bank_id"                     => "required|numeric|digits_between:1,10",
            "issuer_name"                 => "required|string|max:255",
            "bank_code"                   => "required|string|max:30",
            "main_dealer_merchant_number" => "required|numeric|digits_between:1,12",
            "merchant_id"                 => "required|string|max:10",
            "commercial_name"             => "required|string|min:6|max:40",
            "sign_name"                   => "required|string|min:6|max:40",
            "mcc"                         => "required|numeric|digits:4",
            "bkm_unique_id"               => "required|numeric|digits_between:1,8",
            "contact_name"                => "required|string",
            "email"                       => "required|email|max:50",
            "address"                     => "required|string",
            'phone_no'                    => 'required|numeric|digits:10',
            'contact_gsm_phone_no'        => 'required|numeric|digits:10',

        ];

        if ($input['type'] == MerchantBankRegistration::PHYSICAL_POS) {
            if(empty($input['address'])){
                $rules += [
                    "district_dscr"         => "required|string|max:2",
                    "district"              => "required|string|max:30",
                    "main_street_dscr"      => "required|string|max:2",
                    "main_street"           => "required|string|max:30",
                    "street_dscr"           => "required|string|max:2",
                    "street"                => "required|string|max:30",
                    "estate"                => "required|string|max:30",
                    "estate_dscr"           => "required|string|max:3",
                    "building_number"       => "required|string|max:5",
                    "floor"                 => "required|string|max:5",
                    "flat"                  => "required|string|max:5",
                ];
            }
            $rules += [
                "district_dscr"         => "sometimes|string|max:2",
                "district"              => "sometimes|string|max:30",
                "main_street_dscr"      => "sometimes|string|max:2",
                "main_street"           => "sometimes|string|max:30",
                "street_dscr"           => "sometimes|string|max:2",
                "street"                => "sometimes|string|max:30",
                "estate"                => "sometimes|string|max:30",
                "estate_dscr"           => "sometimes|string|max:3",
                "building_number"       => "sometimes|string|max:5",
                "floor"                 => "sometimes|string|max:5",
                "flat"                  => "sometimes|string|max:5",
                "term_type"             => "required|string|size:2",
                "brand"                 => "sometimes|string|size:1",
                "model"                 => "required|string|max:10",
                "serial_no"             => "sometimes|string|max:20",
                "collection_type"       => "sometimes|string|size:1",
                "allowed_international" => "required|string|size:1",
                "allowed_installment"   => "required|string|size:1",

                "remote_merchant_id"   => "sometimes|string|max:12",
            ];
        } else {
            $rules["merchant_code"] = "required";
        }

        if ($input["merchant_type"] == Merchant::CORPORATE_MERCHANT_TYPE) {
            $rules["merchant_vkn"] = "required|string|min:10|max:11";
        } else {
            $rules["merchant_tckn"]       = "required|string|min:10|max:11";
            $rules["merchant_birth_date"] = "required|date";
        }

        if (!isset($input['address'])) {
            $rules["town_code"]   = "required|numeric|digits:4";
            $rules["city_code"]   = "required|numeric|digits_between:1,3";
            $rules["postal_code"] = "required|numeric";
            $rules["phone_no"]    = "required|numeric";
        }

        return $rules;
    }

    public static function bankTerminalvalidation($is_bulk_import = false)
    {
        $rules = [
            'terminal_id'           => 'required',
            'term_type'             => 'required|in:'. Arr::implode(',',Arr::keys(MerchantBankRegistrationTerminal::getTermType())),
            'brand'                 => 'required_if:term_type,' . MerchantBankRegistrationTerminal::TERM_TYPE_MOBIL_POS,
            'model'                 => 'required',
            'serial_no'             => 'required',
            'allowed_international' => 'required|in:'. Arr::implode(',', Arr::keys(MerchantBankRegistrationTerminal::getAllowedInstallment())),
            'allowed_installment'   => 'required|in:'. Arr::implode(',', Arr::keys(MerchantBankRegistrationTerminal::getAllowedInstallment()))
        ];

        if ($is_bulk_import)
        {
            $rules['collection_type'] = 'required|in:' . Arr::implode(',', Arr::keys(MerchantBankRegistrationTerminal::getCollectionType()));
            $rules = Arr::unset($rules, ['serial_no', 'model']);
        }

        return $rules;
    }

    public static function importedTransactionWebhookRules()
    {
        return [
            'merchant_id' => 'required|integer',
            'merchant_terminals_id' => 'required|integer',
            'merchant_terminal_no' => 'required|string',
            'imported_transaction_history_id' => 'sometimes|integer',
            'invoice_id' => self::invoiceIdRule(),
            'remote_acquirer_reference' => 'sometimes|string',
            'remote_rrn' => 'sometimes|integer',
            'remote_transaction_state_id' => 'sometimes|integer',
            'is_refunded_transaction' => 'sometimes|integer',
            'imported_transaction_data' => 'required|array',
            'type' => 'required|integer',
            'hash_key' => 'required|string|max:256'
        ];
    }

     public static function tenantValidationRules()
    {
        $rules = [
            'tenant_name' => 'required|max:150',
            'base_url' => 'required|url|max:200',
        ];

        $messages = [
            'tenant_name.required' => __("Tenant name is required"),
            'tenant_name.max' => __("The Tenant name cannot be greater than :max."),
            'base_url.required' => __("Base URL is required"),
            'base_url.max' => __("The Base URL cannot be greater than :max."),
            'base_url.url' => __("Not a valid URL"),
        ];

        return [$rules, $messages];
    }


    public static function emailVerificationValidationRules($input, $type = '')
    {
        $rules = [];
        $action_rules = [
          'action' => 'required',
        ];
        if ($type == "send_email_verification_otp") {
            $rules = [
              'phone' => 'required',
              'email' => 'required|email',
            ];
        } elseif ($type == "verify_email_otp") {
            $rules = [
              'phone' => 'required',
              'email' => 'required|email',
              'otp' => 'required',
            ];
        } elseif ($type == "resend_email_verification_otp") {
            $rules = [
              'phone' => 'required',
              'email' => 'required|email'
            ];
        }

        $rules = $action_rules + $rules;
        return $rules;
    }

	public static function b2bValidations($inputs){
		$rules = [
			'merchant_id' => 'required|numeric',
			'receiver_merchant_id' => 'required|numeric',
			'currency_id' => 'required|numeric',
			'description' => 'required',
			'amount' => 'numeric|min:0|required|max:' . \common\integration\Models\Wallet::getMaxValueForDoubleDataType()
		];

		if(BrandConfiguration::call([BackendAdmin::class, 'allowReasonOnBTOB'])){
			$rules = Arr::merge($rules, self::b2bAndb2cReasonValidation());
		}

		return $rules;
	}

	public static function b2bAndb2cReasonValidation(){
		return [
			'reason' => "string|required|max:255",
		];
	}

	public static function b2cValidations($inputs){

		if ($inputs['cashout_type'] == BtoC::CASHOUT_TO_BANK) {
			$rules = [
				'user_name' => 'required',
				'bank_name' => 'required',
				'iban' => 'required',
				'round_amount' => 'integer|min:0|required|max:' . \common\integration\Models\Wallet::getMaxValueBeforeDecimal(),
                'cent_amount' => 'numeric|max:' . \common\integration\Models\Wallet::getMaxValueAfterDecimal(),
				'currency' => 'required',
				'gsm_number' => 'required|regex:/^[+]\d+$/'
			];
		} elseif($inputs['cashout_type'] == BtoC::CASHOUT_TO_WALLET) {
			$rules = [
				// 'user_name' => 'required',
				'round_amount' => 'numeric|min:0|max:99999999|required',
				'currency' => 'required',
				'gsm_number' => 'required|regex:/^[+]\d+$/'
			];

		} elseif($inputs['cashout_type'] == BtoC::CASHOUT_TO_WALLETGATE) {

			$rules = [
				'round_amount' => 'numeric|min:0|max:99999999|required',
				'currency' => 'required',
				'gsm_number' => 'required|regex:/^[+]\d+$/',
				'account_number' => 'required'
			];
		}

		if(BrandConfiguration::call([BackendAdmin::class, 'allowReasonOnBTOC'])){
			$rules = Arr::merge($rules, self::b2bAndb2cReasonValidation());
		}

		return $rules;

	}

    public static function RestoreBulkTransactionValidationRules()
    {
        return [
            'transaction_file'  => ['required',  new FileValidation(['xlsx', 'xls'])]
        ];
    }

    public static function BulkRefundValidationRules()
    {
        return [
            'transaction_file'  => ['required',  new FileValidation(['xlsx'])]
        ];
    }

    public static function BulkMerchantTerminalValidationRules()
    {
        return [
            'transaction_file'  => ['required',  new FileValidation(['xlsx'])]
        ];
    }

    public static function addEditbinValidationRules($input)
    {

        $messages = [];
        $rules = [
          'card_type' => 'required|string',
          'card_association' => 'required|string',
          'card_brand' => 'string',
          'bank_name' => 'required|string',
          'bank_code' => 'required',
        ];

        if (isset($input['bin_table']) && $input['bin_table'] == BinResponse::BIN_RESPONSES_TABLE) {

            $rules['bin_number'] = ['regex:/^(.{6}|.{8})$/', 'integer'];
            $messages['bin_number.regex'] = __("Bin :input Must be 6 or 8 digits");

        } elseif (isset($input['bin_table']) && $input['bin_table'] == BinRangeResponse::BIN_RANNGE_RESPONSES_TABLE) {

            $rules['bin_from'] = 'required|integer';
            $rules['bin_to'] = 'required|integer';

            $messages['bin_from.regex'] = __("Bin :input Must be 6 or 8 digits");
            $messages['bin_to.regex'] = __("Bin :input Must be 6 or 8 digits");
        }

        return [$rules, $messages];
    }
    public static function returnRules(){
        return [
            'return_reason'=>'nullable|string|max:255',
            'others_reason'=>'nullable|string|max:250'
        ];
    }

    public static function controlRules(){
        return [
            'information_document_control_status'=>'nullable|numeric|max:3',
            'risk_compliance_assessment_status'=>'nullable|numeric|max:3'
        ];
    }
	
	public static function depositMethodValidationRules(){
		
		$details['rules'] = [
			'method_id' => 'nullable|exists:deposit_methods,id'
		];
		
		$details['message'] = [
			'method_id.exists' => __(':method_id is not exits on system',['method_id' => 'Method id']),
		];
		
		return $details;
	}

	public static function getBankRegistrationValidationRules()
    {
        $rules = [
            'main_dealer_merchant_number' => 'required',
            'merchant_number' => 'required',
        ];

        $messages = [
            'main_dealer_merchant_number.required' => __("Main merchant number is required"),
            'merchant_number.required' => __("Merchant number is required"),
        ];

        return [$rules, $messages];
    }

    public static function getTransactionSummaryValidationRules()
    {
        $rules = [
            'id' => 'required',
            'payment_date' => 'required',
        ];

        $messages = [
            'id.required' => __("Main dealer merchant number is required"),
            'payment_date.required' => __("Payment Date is required"),
        ];

        return [$rules, $messages];
    }

    public static function getTransactionDetailsValidationRules()
    {
        $rules = [
            'main_dealer_merchant_number' => 'required',
            'sub_merchant_number' => 'required',
            'payment_facilitator_terminal_number' => 'required',
            'payment_date' => 'required',
        ];

        $messages = [
            'main_dealer_merchant_number.required' => __("Main dealer merchant number is required"),
            'sub_merchant_number.required' => __("Sub merchant number is required"),
            'payment_facilitator_terminal_number.required' => __("Payment Facilator terminal number is required"),
            'payment_date.required' => __("Payment Date is required"),
        ];

        return [$rules, $messages];
    }

    public static function merchantApplicationValidationRules($input = null, $is_api = false)
    {
        $rules = [];
        if(BrandConfiguration::isMerchantApplicationCustomizedWay()){
            $merchant_types = Arr::keys(\common\integration\Models\Merchant::MERCHANT_TYPES);
            $merchant_types = Arr::implode(',', $merchant_types);
            $rules = [
                'zip_code'            => 'required|string|max:30',
                'city'                => 'required|string|max:30',
                'app_entry_user_code' => 'required|string',
                'document_request_no' => 'required|string',
                'tax_number'          => 'required|string',
                'tckn_no'             => 'required|string',
                'digital_slip_code'   => 'required|numeric',
                'digital_slip_msg'    => 'nullable|string|max:200',
                'customer_number'     => 'required|string',
                'branch_name'         => 'nullable',
                'branch_code'         => 'required|string',
                'district'            => 'sometimes|string|max:50',
                "mcc"                 => 'sometimes|string|max:5',
                'merchant_type'       => 'sometimes|string|in:' . $merchant_types,
            ];
        }

        if(!empty($input['package_code'])){
            $rules = Arr::merge($rules, ['package_code' => 'required|string|max:20']);
        }else{
            if(BrandConfiguration::isMerchantApplicationCustomizedWay()){
                $rules = Arr::merge(
                    $rules,
                    [
                        'credit_card_commission_rate'       => 'required|numeric',
                        'credit_card_settlement_days'       => 'required|numeric',
                        'debit_card_commission_rate'        => 'required|numeric',
                        'debit_card_settlement_days'        => 'required|numeric',
                        'foreign_card_commission_rate'      => 'required|numeric',
                        'foreign_card_settlement_days'      => 'required|numeric',
                        'installments'                      => 'required|array',
                        'installments.*.installment_number' => 'required|numeric',
                        'installments.*.commission_rate'    => 'required|numeric',
                        'installments.*.settlement_days'    => 'required|numeric',
                    ]
                );
            }

        }

        $messages = [

        ];

        if (BrandConfiguration::call([Mix::class, 'allowDOBOnMerchantApplication'])) {
            $rules['dob'] = 'nullable|date' . ($is_api ? '|date_format:' . ManipulateDate::FORMAT_DATE_d_m_Y : '');

            $messages['dob.date'] = __("The Date of Birth (dob) must be a valid date");

            if ($is_api) {
                $messages['dob.date_format'] = __("The Date of Birth (dob) does not match the format :format", ['format' => ManipulateDate::FORMAT_DATE_d_m_Y]);
            }
        }
        if (BrandConfiguration::call([Mix::class, 'isAllowAddNewCheckBoxForMerchantExtras'])) {
            $rules = Arr::merge($rules, [
                'is_allow_pre_authorization' => 'sometimes|in:0,1|integer',
                'is_allow_multi_currency' => 'sometimes|in:0,1|integer',
                'is_allow_cancel' => 'sometimes|in:0,1|integer',
            ]);
        }

        return [$rules, $messages];
    }

    public static function getCashOutApprovalValidation ($service_type, array $input_data)
    {
        $rules = $messages = [];

        if ($service_type == CashInOutManager::SERVICE_TYPE_PARATEK) {
            $rules = [
                'WebhookEvent' => 'required',
                'Amount' => 'required',
                'Currency' => 'required',
                'ExtTransactionId' => 'required',
                'ToIban' => 'required'
            ];
            $messages = [
                'WebhookEvent.required' => 'WebhookEvent is required',
                'Amount.required' => 'Amount is required',
                'Currency.required' => 'Currency is required',
                'ExtTransactionId.required' => 'ExtTransactionId is required',
                'ToIban.required' => 'ToIban is required'
            ];

            /*if(Arr::keyExists('WebhookEvent', $input_data) && (Paratek::WEBHOOK_COMPLETE_EVENT == $input_data['WebhookEvent'])) {
                $rules['FromIban'] = 'required';
                $messages['FromIban.required'] = 'FromIban is required';
            }*/
        }

        return [$rules, $messages];
    }
    public static function cashbackCalculationRules(){

        $rules = [
            'name' => 'required|string|max:50',
            'min_transaction_amount' => 'required|numeric|min:0',
            'max_transaction_amount' => 'required|numeric|gt:0',
            'amount_percentage' => 'numeric|max:100|required_if:amount_fixed,0',
            'amount_fixed' => 'numeric|required_if:amount_percentage,0',
            'currency_id' => 'required|numeric',
            'status' => 'required|numeric'
        ];

        $messages = [
            'name.required' => 'Name is required',
            'min_transaction_amount.required' => 'Min Transaction Amount is required',
            'max_transaction_amount.required' => 'Max Transaction Amount is required',
            'amount_percentage.required' => 'Amount Percentage is required',
            'amount_fixed.required' => 'Amount Fixed is required',
            'currency_id.required' => 'Currency is required',
            'status.required' => 'Status is required'
        ];

        return [$rules, $messages];

    }

	public static function getImageValidation()
	{
		$details['rules'] = [
			'file' => ['required','mimes:jpeg,jpg,png']
		];
		$details['messages']= [
			'file.mimes' => __('Upload files must be only jpg,jpeg,png.'),
		];

		return $details;
	}

    public static function addCommercialCardCommissionValidationRules()
    {
        $rules = [
            'merchant_id' => ['required','array'],
            'program' => 'required',
            'currency_id' => 'required',
            'min_installment' => ['required','lte:max_installment'],
            'max_installment' => ['required','gte:min_installment'],
            'installment'=> ['required','array']
        ];

        $messages = [
            'merchant_id.required' => __("Merchant name is required"),
            'program.required' => __("Program is required"),
            'currency_id.required' => __("Currency is required"),
            'min_installment.required' => __("Min installment is required"),
            'max_installment.required' => __("Max installment is required"),
            'min_installment.lte' => __("Min installment must be less than or equal to Max installment"),
            'max_installment.gte' => __("Max installment must be greater than or equal to Min installment"),
        ];

        return [$rules, $messages];
    }

    public static function cashBackChannelRules($input){
        $category_type = isset($input['category']) ? (Arr::isOfType($input['category']) ? 'array' : 'int') : 'int';
        return [
            'name' => 'required|string|max:50',
            'category' => 'required |'.$category_type,
            'status' => 'numeric|max:1'
        ];
    }

    public static function cashBackBrandRules(){
        return [
            'name' => 'required|string|max:50',
            'status' => 'numeric|max:1'
        ];
    }

    public static function cashBackEntityRules(){
        $rules = [
            'name' => 'required|string|max:50',
            'status' => 'numeric|max:1',
            'start_date' => 'nullable|date_format:m/d/Y',
            'end_date' => 'nullable|date_format:m/d/Y',
            'channel_ids' => 'required|array',
            'transaction_types' => 'required|array',
        ];

        if (!BrandConfiguration::call([BackendMix::class, 'isAllowCustomCashback'])) {
            $rules = Arr::merge($rules, [
                'brand_ids' => 'required|array',
            ]);
        }
        return $rules;
    }

    public static function cashBackTimeRules(){
        return [
            'name' => 'required|string|max:50',
            'from_date' => 'required|date_format:Y-m-d',
            'to_date' => 'required|date_format:Y-m-d',
            'from_time' => 'required',
            'to_time' => 'required',
            'status' => 'required|in:1,0,2',
            'day' => 'required|array',
        ];
    }

    public static function cashbackRules($id = null){
        $rules = [
            'name'                  => 'required|string|max:50',
            'max_allowed_amount'    => 'required',
            'total_system_usage_limit'  => 'required|numeric',
            'per_user_usage_limit'  => 'required|numeric',
            'total_system_usage_amount' => 'required|numeric|gt:0',
            'total_user_usage_amount' => 'required|numeric|min:0',
            'status'                => 'required|in:1,0,2',
            'cashback_entity_id'    => 'required|' . Rule::unique('cashback_enitity_rules', 'cashback_rule_id')->ignore($id),
            'cashback_time_ids.*'   => 'required',
            'cashback_calculation_ids.*'   => 'required',
        ];
        if (BrandConfiguration::call([BackendMix::class, 'isAllowCashbackRuleTerminalAndNationalSwitchId'])){
            $rules['terminal_ids']          = 'sometimes|string|max:255';
            $rules['national_switch_ids']   = 'sometimes|string|max:255';
        }
        return $rules;
    }

    public function merchantPackageRules(){
        return [
            "channel_code"  => "required_without:package_code|string",
            "package_code"  => "required_without:channel_code|string"
        ];
    }
    public static function MerchantPackageChannelRules($id=null){
        return [
            "channel_name"  => "required|string",
            'channel_code' => [
                'required',
                Rule::unique('merchant_channels', 'channel_code')->ignore($id),
            ],
        ];
    }

    public function cashbackCreateValidationRules(){
        $rules = [
            "cashback_entity_rule_id"       => 'required',
            "cashback_rule_id"              => 'required',
            "cashback_channel_id"           => 'required',
            "cashback_time_id"              => 'required',
            "cashback_calculation_id"       => 'required',
            "transaction_amount"            => 'required|numeric',
            "currency_id"                   => 'required|int',
            "cashback_amount"               => 'required|numeric',
            "currency_symbol"               => 'required|string',
            "currency"                      => 'required|string',
            "card_type"                     => 'required|int|in:' . Arr::implode(',', [Cashback::CARD_TYPE_VIRTUAL_CARD, Cashback::CARD_TYPE_PHYSICAL_CARD]),
            "calculation_min_amount"        => 'required|numeric',
            "calculation_fixed_amount"      => 'sometimes|numeric',
            "calculation_percentage"        => 'sometimes|numeric',
        ];
        return $rules;
    }
    public static function merchantServiceRules()
    {
        $rules = [
            'merchant_id' => 'required|numeric|digits_between:1,50',
            'action' => 'required|in:0,1,2|integer',
            'name' => 'max:191|string',
            'mcc' => 'numeric|digits_between:1,5',
            'digital_slip_code' => 'in:1,2,3|max:3|integer',
            'is_allow_2d_cvvless' => 'in:0,1|integer',
            'is_allow_3d_cvvless' => 'in:0,1|integer',
            'rrn_refund' => 'in:0,1|integer',
            'cvv_pass' => 'in:0,1|integer',
            'authorized_person_name' => 'max:100|string',
            'authorized_person_surname' => 'max:100|string',
            'address1' => 'max:300|string',
            'address2' => 'max:300|string',
            'authorized_person_email' => 'email|max:50',
            'authorized_person_phone_number' => 'max:100|string',
            'auth_person_name' => 'max:100|string',
            'site_url' => 'string|max:191',
            'free_refund' => 'in:0,1|integer',
            'bonus_installment' => 'numeric|in:0,1',
            'request_number' => 'max:25',
            'automatic_batch' => 'in:0,1', // 0 is active and 1 in inactive only for validation
            'zip_code'      => 'sometimes|string|max:10',
            'city'          => 'sometimes|string|max:50',
            'district'      => 'sometimes|string|max:50',
            'neighborhood'  => 'sometimes|string|max:50', //area of district
        ];

        //for terminal
        $rules = Arr::merge($rules, [
            'terminal_number'       => 'required_with:terminal_id|max:50|string', // only fast pay terminal_number = terminal_id
            'terminal_id'           => 'max:50|string', // only fast pay terminal_id = serial no or terminal number
            'status'                => 'integer|in:0,1',//1=active,0=inactive
            'status_code'           => 'integer|in:0,1',//0=open,1=closed
            'terminal_close_reason' => 'required_if:status,1|max:255|string',
        ]);
        
        //for bank
        $rules = Arr::merge($rules, [
            'account_list' => ['array',
                function ($attribute, $value, $fail) {
                    if (Arr::count($value) <= 0) {
                        $fail('Minimum one bank account is required');
                    }
                    $currency_id = collect($value)->pluck('currency_id')->toArray();
                    $bank_currency_status = Arr::isValueUnique($currency_id);
                    if (!$bank_currency_status) {
                        $fail('You can not create account with same currency id at a time.');
                    }
                }
            ],
            'account_list.*.account_no'          => 'required_with:account_list|string|max:50',
            'account_list.*.account_holder_name' => 'required_with:account_list.*.account_no|string|max:150',
            'account_list.*.branch_code'         => 'required_with:account_list.*.account_no|max:100|string',
            'account_list.*.branch_name'         => 'required_with:account_list.*.account_no|max:150',
            'account_list.*.iban_no'             => 'required_with:account_list.*.account_no|max:50',
            'account_list.*.swift_code'          => 'required_with:account_list.*.account_no|max:50',
            'account_list.*.currency_id'         => 'required_with:account_list.*.account_no|exists:currencies,id|max:50',//. Rule::exists(Currency::class, 'id')    ['required',
        ]);

        return $rules;
    }

    public static function getTransactionRules($input){
        return [
            "merchantid" => "required|string",
            "from_date" => ['required', "date_format:Y-m-d"],
            "to_date" => ['required', "date_format:Y-m-d", "after:from_date"],
            "page_limit" => 'sometimes|numeric|between:0,100',
            // "invoiceid" => "sometimes|string",
            // "transactionId" => "sometimes|string",
	        "order_id" => "sometimes|string|max:50",
	        "payment_id" => "sometimes|string|max:50",
	        
            "offset" => "sometimes|numeric"
        ];
    }
    public static function MerchantIncomIngCommissionRateValidationRule(){
        return [
            'merchant_id'=>'required|string'
        ];
    }
    public static function merchantOpenPosRules(){
        return [
            "merchant_id" => "required|numeric|digits_between:1,11",
        ];
    }
    public static function merchantVirtualPosRules(){
        return [
            "merchant_id" => "required|numeric|digits_between:1,11",
        ];
    }

    public static function merchantPhysicalPosRules(){
        return [
            "merchant_id" => "required|numeric|digits_between:1,11",
        ];
    }

    public function merchantPackageValidationRules($input = null){
        $rules = [
            "package_name"                          => 'required|string|max:255',
            "package_code"                          => 'required|string|max:50|' . Rule::unique('merchant_packages', 'package_code')->ignore($input['id']??''),
            "credit_card_rate_percentage"           => 'required|numeric',
            "credit_card_block_days"                => 'required|int|max:999',
            "package_validation_month"              => 'required|int|max:12',
            "foreign_credit_card_rate_percentage"   => 'required|array',
            "foreign_credit_card_rate_percentage.*" => 'required|numeric',
            "foreign_credit_card_settlement_id"     => 'required|array',
            "foreign_credit_card_settlement_id.*"   => 'required|int',
            "debit_card_rate_percentage"            => 'required|array',
            "debit_card_rate_percentage.*"          => 'required|numeric',
            "debit_card_settlement_id"              => 'required|array',
            "debit_card_settlement_id.*"            => 'required|int',
            "settlement_id"                         => 'array',
            "settlement_id.*"                       => 'int',
            "merchant_channel_ids"                  => 'required|array',
            "merchant_channel_ids.*"                => 'required|int|max:20',
            'min_installment'                       => $input['is_installment']==1?'required|int|min:'.MerchantPackage::MIN_INSTALLMENT.'|max:'.MerchantPackage::MIN_INSTALLMENT:'nullable',
            'max_installment'                       => $input['is_installment']==1?'required|int|min:'.MerchantPackage::MIN_INSTALLMENT.'|max:'.MerchantPackage::MAX_INSTALLMENT:'nullable',
            'installment_rate_percentage'           => $input['is_installment']==1?'required':'nullable',
            'installment_block_days'                => $input['is_installment']==1?'required':'nullable',
            'package_turnover'                      => 'sometimes|numeric'
        ];
        return $rules;
    }

    public static function merchantVknNumberValidationRules() : array
    {
        return $rules = [
          'vkn' => 'required|string|max:50'
        ];
    }


    public static function updateMerchantPosCommissionValidationRule($input)
    {
        $rules = [
            'merchant_id' => "required|numeric|digits_between:1,11",
            'currency_code' => 'required|string|in:' .  implode(',', array_values(config('constants.SYSTEM_SUPPORTED_CURRENCIES'))),
            'debit_card_commission_rate' => 'required|numeric|between:0,100',
            'foreign_card_commission_rate' => 'required|numeric|between:0,100',
            'credit_card_rate_percentage' => 'required|numeric|between:0,100',
            'credit_card_block_days' => 'required|numeric',
            'installments' => ['required', 'array', 'min:1', 'max:19',

                function ($installments, $values, $fail) {

                    $status = true;
                    $installment_numbers = collect($values)->pluck('installment_number')->toArray();
                    $sorted_installments_numbers = collect($installment_numbers)->sort()->values()->all();

                    if ($installment_numbers != $sorted_installments_numbers) {
                        $status = false;
                        $fail('The installments must be in sequence.');
                    }

                    if($status){

                        $settlement_days = collect($values)->pluck('installment_settlement_days')->toArray();
                        $sorted_settlement_days = collect($settlement_days)->sort()->values()->all();

                        if ($settlement_days != $sorted_settlement_days)
                        {
                            $fail('The installment settlement days must be in sequence.');
                        }
                    }

                    foreach ($values as $installment) {

                        $installment_commission_percentage = $installment['installment_commission_percentage'] ?? '';

                        if (!Number::isNum($installment_commission_percentage)) {
                            $fail('Installment commission percentage must be a numeric value.');
                        } elseif ($installment_commission_percentage < 0) {
                            $fail('Installment commission percentage must not be a negative value.');
                        } elseif ($installment_commission_percentage >= 100) {
                            $fail('Installment commission percentage must not be greater than 100.');
                        }
                    }


                },
            ],
            'debit_card_block_days' => ['required', 'integer',
                function($attribute, $value, $fail)
                {

                    list($is_valid, $fail_message) = self::validateSettlementValue($value);
                    if (!$is_valid)
                    {
                        $fail($fail_message);
                    }
                }
            ],
            'foreign_card_block_days' => ['required', 'integer',
                function($attribute, $value, $fail)
                {

                    list($is_valid, $fail_message) = self::validateSettlementValue($value);
                    if (!$is_valid)
                    {
                        $fail($fail_message);
                    }
                }
            ]
        ];

        return $rules;
    }
    public static function packageAssignValidationRules($request) : array
    {
        return $rules = [
            'merchant_id' => !isset($request['merchant_id']) && !isset($request['merchant_ids'])?'required':'',
            isset($request['merchant_id']) ? 'merchant_id':'merchant_ids' =>   isset($request['merchant_id']) ?'required|max:20':'required',
            'package_code' => !isset($request['package_code']) && !isset($request['package_id'])?'required|string|max:50':'',
            isset($request['package_id']) ? 'package_id' : 'package_code' => isset($request['package_id'])?'required|integer':'required|string|max:50'
        ];

    }

    public static function packageUnAssignValidationRules(): array
    {
        return $rules = [
                        'merchant_id' => 'required|integer',
                        'package_id' => 'required|integer'
                        ];

    }

    public static function userShippingAddressValidationRules(): array
    {
        return [
            'addressType' => 'required',
            'countryCode' => 'required|numeric',
            'cityCode' => 'required|numeric',
            'townCode' => 'required|numeric',
            'address1' => 'required|max:50',
            'address2' => 'max:50',
            'address3' => 'max:50',
            'address4' => 'max:50'
        ];
    }

    public static function userShippingAddressValidationMessages(): array
    {
        return [
            '*.required' =>  __('The :attribute field is required.'),
            'max' =>  __('The :attribute may not be greater than :max characters.'),
            'numeric' =>  __('The :attribute must be a number.')
        ];
    }
    public static function posTypeValidation(): array
    {
        return [
            'pos_type' =>[ 'required','integer','min:0','max:2'],
            'pos_id' => ['required', 'integer','digits_between:1,11',
                function($attribute, $value, $fail)
                {

                    $is_valid= (new Pos())->findByPosId($value);
                    if (empty($is_valid))
                    {
                        $fail(__('Invalid Pos id'));
                    }
                }
            ]
        ];
    }


    public static function validateData($inputs,$rules,$messages=[])
    {
        $status_code = $status_message = $errors_message ='';
        $data = [];
        $validator = Validator::make($inputs,$rules,$messages);

        if ($validator->fails()) {
            $status_code = ApiService::API_SERVICE_DEFAULT_VALIDATION_ERROR;
            $status_message = $validator->errors()->first();
            $errors_message = $validator->errors();
        }

        $data =[
            'status_code'=>$status_code,
            'status_message'=>$status_message,
            'errors_message'=>$errors_message
        ];

        return $data;
    }

    public static function validateSettlementValue($value)
    {
        $settlementObj = (new Settlement())->getByValues([$value]);
        if (Arr::count($settlementObj) == 0) {
            $fail = __('Invalid settlement');
            return [false, $fail];
        }
        return [true, null];

    }

    public static function roleManagementValidation($input, $company_id)
    {
        $rules = [
            'title' => 'required|max:100|unique:roles,title,'.$input->title.',title,company_id,'.$company_id,
            'action' => 'required|in:add_role,edit_role'
        ];

        $messages = [
            'title.required' => __("Title is required"),
            'action.required' => __("Action is required"),
        ];

        if(BrandConfiguration::call([BackendAdmin::class, 'allowAccessRoleTurkishVersion'])){
            $rules = Arr::merge($rules, [
                'title_tr' => 'required|max:100|unique:roles,title_tr,'.$input->title_tr.',title_tr,company_id,'.$company_id,
            ]);

            $messages = Arr::merge($messages, [
                'title_tr.required' => __("Title TR is required"),
            ]);
        }

        return [$rules, $messages];
    }

    public static function userGroupManagementValidation()
    {
        $rules = [
            'group_name' => 'required|max:100',
            'dashboard_url' => 'required|max:100',
            'action' => 'required|in:add_usergroup,edit_usergroup'
        ];

        $messages = [
            'group_name.required' => __("Group Name is required"),
            'dashboard_url.required' => __("Dashboard URL is required"),
            'action.required' => __("Action is required")
        ];

        return [$rules, $messages];
    }

    public static function validateMerchantApplicationRequest($request){
        $rules = [
          'merchant_name' => ['required','string'],
          'company_name' => ['required','string'],
          'website' => ['required','string'],
          'auth_person_name' => ['required','string'],
          'auth_person_email' => ['required','email'],
          'auth_person_phone' => ['required','max:50','regex:/^[+]\d+$/'],
          'contract_person_phone' => ['max:50','regex:/^[+]\d+$/'],
          'contract_person_email' => ['email'],
          'address' => ['required','string'],
          'country' => ['required','string'],
          'preferred_payment_method' => ['required','string'],
          'merchant_logo' => ['required','max:2048','mimes:png,jpg,jpeg'],
          'signature' => ['required','max:2048','mimes:png,jpg,jpeg,webp,tiff,pdf,doc,docx'],
          'tax_board' => ['required','max:2048','mimes:png,jpg,jpeg,webp,tiff,pdf,doc,docx'],
          'trade_registry' => ['required','max:2048','mimes:png,jpg,jpeg,webp,tiff,pdf,doc,docx'],
          'partner_identity' => ['required','max:2048','mimes:png,jpg,jpeg,webp,tiff,pdf,doc,docx'],
          'others_one' => ['max:5000','mimes:png,jpg,jpeg,webp,tiff,pdf,doc,docx'],
          'others_two' => ['max:5000','mimes:png,jpg,jpeg,webp,tiff,pdf,doc,docx'],
          'others_three' => ['max:5000','mimes:png,jpg,jpeg,webp,tiff,pdf,doc,docx'],
          'price_offer' => ['max:5000','mimes:pdf,doc,docx'],
          'agreements' => ['max:5000','mimes:pdf,doc,docx'],
//          'working_condition' => ['required'],
//          'installment_count' => ['required']
        ];

        if (BrandConfiguration::call([Mix::class, 'isAllowedOnboardingPanel'])) {
            if (BrandConfiguration::call([Mix::class, 'isAllowedMerchantApplicationExtraCols'])) {
                $rules['company_established_date'] = ['required', 'date_format:m/d/Y', 'string'];
            }

            if (BrandConfiguration::isAllowedIKSMerchant()) {
                $rules['district'] = isset($request->district) ? 'required' : 'nullable';
                $rules['mcc'] = isset($request->mcc) ? 'required' : 'nullable';

                if (BrandConfiguration::call([Mix::class, 'isAllowedMerchantApplicationExtraCols'])) {
                    $rules['neighborhood'] = isset($request->neighborhood) ? 'required' : 'nullable';
                }
            }

            $rules['tckn_no'] = ['required'];
            $rules['business_area'] = BrandConfiguration::isNotRequiredBusinessArea() ? "nullable" : "required";
        }


        $validation = Validator::make($request, $rules);

        return $validation;
    }

    public static function reverseRefundAndChargebackRules($allow_partial_refund = false){
        $rules = [
            'sale_id' => 'required',
            'is_awaiting_refund' => 'sometimes|in:0,1',
            'reverse_explanation' => 'required|string|max:255'
        ];

        if($allow_partial_refund){
            $rules = Arr::merge(['refund_history_id' => 'required'], $rules);
        }

        return $rules;
    }

    public static function physicalCardMatchingValidationRules(): array
    {
        return [
            'card_no' => 'required',
            'cvv' => 'required|numeric|regex:/^\d{3,4}$/',
            'month' => 'required|numeric',
            'year' => 'required|numeric'
        ];
    }

    public static function physicalCardMatchingValidationMessages(): array
    {
        return [
            '*.required' =>  __('The :attribute field is required.'),
            'numeric' =>  __('The :attribute must be a number.')
        ];
    }
	
	public static function profileAndLogValidation(): array
	{
		$rules = [
			'img_path' => ['sometimes',  new FileValidation(['png','jpg','jpeg','webp','tiff'])],
			'logo_path' => ['sometimes',  new FileValidation(['png','jpg','jpeg','webp','tiff'])],
		];
		
		$messages = [
			'img_path' => __('Uploaded image must be only png,jpg,jpeg,webp,tiff.'),
			'logo_path' => __('Upload logo must be only png,jpg,jpeg,webp,tiff.'),
		];
		
		return [$rules, $messages];
	}
    public static function limitCommercialCardValidation($input){
        $min_installment = 'required';
        $max_installment = 'required';
        if(BrandConfiguration::call([Mix::class, 'isOptionalMinAndMaxInstallmentForLimitCommercialCardCommission']) && $input['action_type'] == 'update'){
            $min_installment = "required_with:limit_commercial_card_max_installment";
            $max_installment = "required_with:limit_commercial_card_min_installment";
        }

        $rules = [
            'pos_id' => "required|numeric",
            'currency_id' => "required|numeric",
            'limit_commercial_card_min_installment' => $min_installment."|numeric|lte:limit_commercial_card_max_installment",
            'limit_commercial_card_max_installment' => $max_installment."|numeric|gte:limit_commercial_card_min_installment",
        ];

        return $rules;

    }

    public static function installmentTransactionReportValidation(): array {
        $rules = [
            'date_range' => 'required',
            'file_type' => 'required',
        ];

        return $rules;
    }
    public static function paxImportTransactionRequestRules($input)
    {
        $rules =  [
            "paymentId" => 'required|string|max:100',
            "date" => "required|string|max:15",
            "type" => "required|string|in:".Arr::implode(',',Arr::merge(Pax::REMOTE_SALE_TRANSACTION_TYPE_LIST, Pax::REMOTE_REFUND_TRANSACTION_TYPE_LIST)),
            "status" => "required|string",
            "currency" => "required|string|in:" . Arr::implode(',', config('constants.SYSTEM_SUPPORTED_CURRENCIES')),
            "terminalId" => "required|string",
            "amount" => "required|numeric|gt:0",
            "installment" => "required|integer",
            "maskedCardNo" => "required|string",
            "hashedCardNo" => "required|string",
            "provisionNo" => "required|string",
            "bankId" => "required|string",
            "acquirerResponseCode" => "required|string",
            "posEntryMode" => "string|nullable",
            "pinEntryInfo" => "nullable|boolean",
            "cardType" => "string|nullable",
            "PaxID" => "required|string",
            "tranNo" => ["required", "integer", function($attribute, $value, $fail) use ($input)
            {
                $importedTransactionObj = (new ImportedTransaction())->findByFiltering(['type'=>ImportedTransaction::TYPE_PAX, 'remote_original_reference' => $value,'remote_batch_no'=>$input['batchNo'],'merchant_terminals_id'=>$input['merchant_terminals_id']]);
                if(!empty($importedTransactionObj)){
                    $fail(__('Transaction no. already exist'));
                }
            }],
            "rrn" => ["required", "string","max:100", function($attribute, $value, $fail) use ($input)
            {
                $transaction_type = ImportedTransaction::TRANSACTION_TYPE_SALE;
                if (Arr::isAMemberOf($input['type'], Pax::REMOTE_REFUND_TRANSACTION_TYPE_LIST)) {
                    $transaction_type = ImportedTransaction::TRANSACTION_TYPE_REFUND;
                }
                    $importedTransactionObj = (new ImportedTransaction())->findByFiltering(['transaction_type' => $transaction_type, 'type' => ImportedTransaction::TYPE_PAX, 'remote_rrn' => $value]);
                    if (!empty($importedTransactionObj)) {
                        $fail(__('Reference no. already exist'));
                    }
            }],
            'batchNo' => "required|integer"
        ];
        if(isset($input['type']) && $input['type'] == Pax::REMOTE_REFUND_TRANSACTION_IPTAL){
            $rules['orgTranNo'] = "required|integer";
        }
        if(isset($input['type']) && $input['type'] == Pax::REMOTE_REFUND_TRANSACTION_IADE){
            $rules['orgRefNo'] = "required|string";
        }
        return $rules;
    }
    public static function paygoImportTransactionRequestRules($input)
    {
        $rules =  [
            "paymentId" => 'required|string|max:100',
            "date" => "required|string|max:15",
            "type" => "required|string|in:".Arr::implode(',',Arr::merge(Paygo::REMOTE_SALE_TRANSACTION_TYPE_LIST, Paygo::REMOTE_REFUND_TRANSACTION_TYPE_LIST)),
            "status" => "required|string",
            "terminalId" => "required|string",
            "amount" => "required|numeric",
            "acquirerResponseCode" => "required|string",
            "posEntryMode" => "sometimes|string",
            "pinEntryInfo" => "sometimes|string",
            'batchNo' => "required|string",
            'merchant_terminals_id' => "required"
        ];
        $requiredOrSometimes = 'sometimes';
        if (isset($input['type']) && Arr::isAMemberOf($input['type'], Paygo::REMOTE_SALE_TRANSACTION_TYPE_LIST)) {
            $requiredOrSometimes = 'required';
        }
        $rules["currency"] = $requiredOrSometimes . "|string|in:" . Arr::implode(',', config('constants.SYSTEM_SUPPORTED_CURRENCIES'));
        $rules["installment"] = $requiredOrSometimes . "|integer";
        $rules["maskedCardNo"] = $requiredOrSometimes . "|string";
        $rules["hashedCardNo"] = $requiredOrSometimes . "|string";
        $rules["provisionNo"] = $requiredOrSometimes . "|string";
        $rules["bankId"] = $requiredOrSometimes . "|string";
        $rules["cardType"] = $requiredOrSometimes . "|string";

        $importedTransactionObj = (new ImportedTransaction())->getByFilters(['type'=>ImportedTransaction::TYPE_PAYGO, 'merchant_terminals_id'=>$input['merchant_terminals_id']], false);

        $rules["tranNo"] = ["required", "string", "max:100", function($attribute, $value, $fail) use ($input, $importedTransactionObj)
            {
                $importedTransactionObjTN = $importedTransactionObj->where('remote_original_reference', $value)->where('remote_batch_no', $input['batchNo'])->first();
                if(!empty($importedTransactionObjTN)){
                    $fail(__('Transaction no. already exist'));
                }
            }];
        $rules["rrn"] = ["required", "string","max:100", function($attribute, $value, $fail) use ($input, $importedTransactionObj)
            {
                $transaction_type = ImportedTransaction::TRANSACTION_TYPE_SALE;
                if (Arr::isAMemberOf($input['type'], Paygo::REMOTE_REFUND_TRANSACTION_TYPE_LIST)) {
                    $transaction_type = ImportedTransaction::TRANSACTION_TYPE_REFUND;
                }
                $importedTransactionObjRN = $importedTransactionObj->where('transaction_type',  $transaction_type)->where('remote_rrn', $value)->first();
                if (!empty($importedTransactionObjRN)) {
                    $fail(__('Reference no. already exist'));
                }
            }];

        if(isset($input['type']) && $input['type'] == Paygo::REMOTE_REFUND_TRANSACTION_VOID){
            $rules['orgTranNo'] = "required|string|max:100";
        }
        if(isset($input['type']) && $input['type'] == Paygo::REMOTE_REFUND_TRANSACTION_REFUND){
            $rules['orgRrn'] = "required|string|max:100";
        }

        return $rules;
    }


    public static function terminalParameterRequestRules()
    {
        $rules = [
//            "appInfo.tid" => "required|string|max:20",
            "deviceSerial" => "required|string|max:22",
            "date" => "required|integer"
        ];
        $messages = [
            'appInfo.tid.required' => __("Terminal ID is required"),
            'appInfo.tid.max' => __("Terminal ID cannot greater than :max"),
            'date.required' => __("Date is required"),
            'date.integer' => __("Incorrect date format")
        ];

        return [$rules, $messages];
    }


	public static function updateCategoryUpdateValidation($category_lists)
	{
		
		$details['rules'] = [
			'category_id' => ['required', 'in:'. Arr::implode(',',$category_lists)]
		];
		
		$details['message'] = [
			'category_id.in' => __(':category_id is not exits on system',['category_id' => 'Category Id']),
		];
		
		return $details;
	}

    public static function userLoginAlertSettingRequestRules($is_email, $is_sms)
    {
        $rules = [
            "from_time" => 'required',
            "to_time" => "required|after:from_time",
        ];

        if ($is_email) {
            $rules = Arr::merge($rules, ['email_addresses' => 'required']);
        }
        if ($is_sms) {
            $rules = Arr::merge($rules, ['sms_numbers' => 'required']);
        }
        return $rules;
    }


    public static function getTerminalSettingsValidation($request, $is_parameter_settings = false)
    {
        $rules = array(
            'default_debit_bank' => ['required', 'string', 'max:10'],
            'default_credit_bank' => ['required', 'string', 'max:10'],
            'limit_alert_message'=> ['required', 'max:250'],
            'batch_report_time' => ['required', 'max:10'],
        );
        $messages = array(
            'batch_report_time.required' => 'Batch Time is required.',
            'batch_report_time.max' => 'Batch Time is Invalid.'
        );

        if($is_parameter_settings) {
            $rules += array(
                'max_transaction_daily'=> ['required', 'numeric', 'regex:/^\d{1,16}(\.\d{1,4})?$/'],
                'max_transaction_single'=> ['required', 'numeric', 'regex:/^\d{1,12}(\.\d{1,4})?$/'],
                'default_foreign_credit_bank'=> ['required'],
            );
            $messages += array(
                'max_transaction_daily.regex' => 'Max Transaction must be in 16 digit.',
                'max_transaction_daily.required' => 'Max Transaction is required.',
                'max_transaction_daily.numeric' => 'Max Transaction must be in number.',
                'max_transaction_single.regex' => 'Maximum Transaction Amount to be Made at One Time must be in 12 digit.',
                'max_transaction_single.required' => 'Maximum Transaction Amount to be Made at One Time is required.',
                'max_transaction_single.numeric' => 'Maximum Transaction Amount to be Made at One Time must be in number.',
            );
        } else {
            $rules += array(
                'max_transaction_limit' => ['required', 'integer'],
                'max_transaction_amount' => ['required', 'numeric', 'regex:/^\d{1,16}(\.\d{1,4})?$/'],
            );
            $messages += array(
                'max_transaction_limit.regex' => 'Max Transaction must be in 16 digit.',
                'max_transaction_limit.required' => 'Max Transaction is required.',
                'max_transaction_limit.numeric' => 'Max Transaction must be in number.',
                'max_transaction_amount.regex' => 'Maximum Transaction Amount to be Made at One Time must be in 12 digit.',
                'max_transaction_amount.required' => 'Maximum Transaction Amount to be Made at One Time is required.',
                'max_transaction_amount.numeric' => 'Maximum Transaction Amount to be Made at One Time must be in number.',
            );
        }

        if (BrandConfiguration::call([Mix::class, 'isAllowDefaultForeignCreditBank'])) {
            $rules['default_foreign_credit_bank'] = ['required', 'max:10'];
        }

        if(BrandConfiguration::call([Mix::class, 'isAllowExtraValidationInMerchantTerminalSettings'])) {
            $rules['max_transaction_amount'][1]= 'integer';
            $rules['max_transaction_amount'][3]= 'max:99999999';
            $rules['batch_report_time'][1]= 'max:2400';
            $rules['batch_report_time'][2]= 'integer';
        }

        return Validator::make($request->all(), $rules, $messages);
    }
	
	
	public static function sentApiEmailValidation($request)
	{
		
		$details['rules'] = [
			'recipient_addresses' => ['required', 'array'],
			'recipient_addresses.*' => ['required','email:rfc,dns'],
			'subject' => ['required', 'string'],
			'email_body' => ['required', 'string']
		];
		
		
		foreach ($request['recipient_addresses'] as $key => $value) {
			$details['message']['recipient_addresses.' . $key . '.email'] = __(':email is not valid email', ['email' =>
				$value]);
		}
		
		return $details;
	}
    public static function merchantWinningApplicationRules()
    {
        return [
            'source' => ['required', 'array', function($attribute, $value, $fail)
            {
                list($is_valid, $fail_message) = self::validateSource($value);
                if (!$is_valid)
                {
                    $fail($fail_message);
                }
            }],
            'application_type' => ['required', Rule::in(Arr::keys(Merchant::MERCHANT_TYPES))],
            'tckn' => 'required_if:application_type,1|string|max:50',
            'vkn' => 'required_if:application_type,2| string|max:50',
            'company_name' => 'required|string|max:100',
            'merchant_name' => 'required|string|max:100'
            ];
    }

    public static function validateSource ($value)
    {

        $merchantChannelObj = (new MerchantChannel())->getData(['channel_codes' => $value]);
        $merchant_channels = $merchantChannelObj->pluck('channel_code')->toArray();
        $not_match_channels = Arr::diff($value, $merchant_channels);
        if (!empty($not_match_channels)) {
            $fail = __('Not match channels are ').Arr::implode(', ', $not_match_channels);
            return [false, $fail];
        }
        return [true, null];
    }

    public static function getMerchantAnnouncementRules()
    {
        return [
            'merchant_id' => ['required','integer','exists:merchants,id'],
            "from_date" => ['required', "date_format:Y-m-d"],
            "to_date" => ['required', "date_format:Y-m-d", "after:from_date"],
            "language" => ['required','string','max:2']
        ];
    }
    public static function dplValidation($data, $is_from_api = false, $is_merchant_new_api = false)
    {
        $statuscode = config('apiconstants.API_SUCCESS');
        $description = config('apiconstants.API_DESCRIPTIONS_SUCCESS');
        $errors = [];

        $expire_date_check = !empty($data['expire_date']) ? true : false;
        $input_field_max_length = (new DPL())->getInputFieldMaxLength();

        $rulesArray = [
            'currency' => 'required|integer',
            'payment_link_type' => 'required|integer|in:'.Arr::implode(',', Arr::keys(DPL::PAYMENT_LINK_TYPES)),
            'max_number_of_uses' => 'integer|nullable|max:99999',
            'name_of_product' => 'required|string|max:' . $input_field_max_length['name_of_product'],
            'description' => 'max:' . $input_field_max_length['description'],
            'min_installment_limit' => [
                Rule::requiredIf(request()->input('payment_link_type') == Dpl::ONE_PAGE_PAYMENT_LINK),
                'integer',
                isset($data['max_installment_limit']) ? 'max:' . $data['max_installment_limit'] : ''
            ],
            'max_installment_limit' => [
                Rule::requiredIf(request()->input('payment_link_type') == Dpl::ONE_PAGE_PAYMENT_LINK),
                'integer',
                isset($data['min_installment_limit']) ? 'min:' . $data['min_installment_limit'] : ''
            ],
            'dpl_pos_option' => 'sometimes|integer|in:'.Arr::implode(',', Arr::keys(Merchant::ALLOW_POS_OPTION_LIST)),
            "expire_date" => [Rule::requiredIf($expire_date_check), 'date', 'after_or_equal:today'],
            'expire_datetime' => [Rule::requiredIf($expire_date_check), 'date', 'after:now']
        ];
		
        if ((!empty($data['gsm'])||BrandConfiguration::allowRequiredFieldForDPL()) && !$is_from_api) {
            $rulesArray['gsm'] = 'regex:/^[+]\d+$/';
        }
        if ((!empty($data['email'])||BrandConfiguration::allowRequiredFieldForDPL()) && !$is_from_api) {
            $rulesArray['email'] = 'required|email|max:100';
        }
        if (BrandConfiguration::allowRequiredFieldForDPL() && !$is_from_api) {
            $rulesArray['phonecode'] = 'required';
        }
        if (isset($data['is_recurring']) && $data['is_recurring']){
            $rulesArray['payment_number'] = 'required|integer|min:2';
            $rulesArray['payment_interval'] = 'required|integer|min:1';
            $rulesArray['payment_cycle'] = 'required';
            $rulesArray['recurring_webhook_key'] = 'required';
        }

        if (isset($data['is_save_link'])) {
            $rulesArray['saved_link_title'] = 'required|min:5|max:255';
        }

        if (!empty($data['product_photo']))
        {
            if (BrandConfiguration::allowDplMultipleResource()) {
                $multiImgRules = AppRequestValidation::imageValidationRules(
                    [],
                    'product_photo',
                    true,
                    ['types', 'size'],
                    ['jpg', 'jpeg', 'png'],
                    ['min_size' => 0, 'max_size' => UserSetting::UPLOAD_FILE_SIZE]
                );
                $rulesArray = Arr::merge($rulesArray, $multiImgRules);
            } else {
                if (!$is_merchant_new_api) {
                    $rulesArray['product_photo'] = 'sometimes|image|mimes:png,jpg,jpeg,webp|max:5120';
                }
            }
        }

        if (!BrandConfiguration::hideTakeComissionFromUser()) {
            unset($data['is_comission_from_user']);
            unset($data['commission_for_installment']);
        } else {
            if (!isset($data['is_comission_from_user'])) {
                $data['is_comission_from_user'] = DPL::DO_NOT_TAKE_COMISSION_FROM_USER;
            }
        }

        if (isset($data['is_comission_from_user'])) {
            $rulesArray['is_comission_from_user'] = 'integer|in:' . DPL::TAKE_COMISSION_FROM_USER . ',' . DPL::DO_NOT_TAKE_COMISSION_FROM_USER;
        }

        $rulesArray['commission_for_installment'] = 'array|required_if:is_comission_from_user, '.DPL::TAKE_COMISSION_FROM_USER;
        $rulesArray['commission_for_installment.*'] = 'integer';

        if (!isset($data['is_amount_set_by_user']) || !$data['is_amount_set_by_user']) {
            if (BrandConfiguration::isQPNewMerchantTemplate()) {
                $rulesArray['amount'] = 'required';
            } else {
                $rulesArray['amount'] = 'required|integer|digits_between:1,'.BrandConfiguration::dplDigitLimit();
            }
            $rulesArray['cent_amount'] = ['integer', function($attribute, $value, $fail) use ($data) {
                list($is_valid, $fail_message) = self::validateCentAmount($attribute, $value, $fail, $data['amount'] ?? 0);
                if (!$is_valid) {
                    $fail($fail_message);
                }
            }];
        }

        if (isset($data['transaction_type']) && $data['transaction_type']) {
            $rulesArray['transaction_type'] = 'integer|in:0,1';
        } elseif (isset($data['is_recurring']) && $data['is_recurring']){
            $rulesArray['payment_number'] = 'required|integer|min:2';
            $rulesArray['payment_interval'] = 'required|integer|min:1';
            $rulesArray['payment_cycle'] = 'required|in:'.Arr::implode(',', Arr::keys(config('constants.OCCURRENCE')));
            $rulesArray['recurring_webhook_key'] = 'required';
        }

        $messages = [
            'gsm.regex' => __('The gsm number format is invalid'),
            'email.max' => __('The :attribute length may not be longer than :max'),
            'required' => __('The :attribute field is required.'),
            'max_installment_limit.min' => __('The :attribute field can\'t be less then min installment limit.'),
            'expire_datetime.after' => __('The expire datetime must be a datetime after now.'),
            'expire_date.after_or_equal' => __('The expire datetime must be a datetime after now.')
        ];

        if ($expire_date_check) {
            $expired_hour = Str::fill($data['expire_hour'] ?? 0, 2, '0', true, STR_PAD_LEFT);
            $data['expire_datetime'] = ManipulateDate::createFormatDate($data['expire_date'] . ' ' . $expired_hour, ManipulateDate::FORMAT_DATE_d_m_Y_H_DOT, ManipulateDate::FORMAT_DATE_Y_m_d_H_i_s);
        }

        $validator = Validator::make($data, $rulesArray, $messages);

        if ($validator->fails()) {
            $statuscode = config('apiconstants.API_VALIDATION_FAILED');
            $description = $validator->errors()->first();
            $errors = $validator->errors();
        }

        // as it is added only for validation purpose
        if ($expire_date_check) {
            unset($data['expire_datetime']);
        }

        return [$statuscode, $description, $errors, $data];
    }

	public static function merchantSaleRecurringValidation() : array
	{
        return [
            'merchant_id'   => [ 'required', 'integer', 'exists:merchants,id' ],
            'status'        => [ 'sometimes','integer', 'in:0,1' ],
            'currency'      => [ 'sometimes', 'array', 'exists:currencies,id' ],
            'page_limit'    => [ 'sometimes', 'integer', 'between:1,100' ],
            'from_date'     => [ 'sometimes', 'date', 'date_format:Y-m-d' ],
            'to_date'       => [ 'sometimes', 'date', 'after_or_equal:from_date', 'date_format:Y-m-d' ],
        ];
	}
    public static function getMerchantInformationValidation(){
        return $rules = [
            'merchant_id'=> 'required|integer|exists:merchants,id'
        ];
    }


    public static function webhookApiAddUpdateRequestValidation($is_update = false)
    {
        $rules = [
            'merchant_id' => ['required','integer','exists:merchants,id'],
            'type' => 'required|integer',
            'key_name' => 'required',
            'value' => 'required|string|max:150'
        ];
        if($is_update){
            unset($rules['type']);
            unset($rules['key_name']);
            $rules['id']= 'required';

        }
        return $rules;
    }

    public static function webhookApiGetListRequestValidation()
    {
        return [
            'merchant_id' => ['required','integer','exists:merchants,id'],
            'key_name' => 'string',
            'value' => 'url',
            'type' => 'integer',
            'per_page' => 'integer|between:1,100'
        ];
    }

    public static function webhookApiDeleteRequestValidation(){
        return [
            'id' => 'required|integer',
            'merchant_id' => ['required','integer','exists:merchants,id']
        ];
    }


    public static function merchantReportListValidationRules(): array
    {
        return [
            "merchant_id"       => "required",
            "unique_report_id"  => "nullable",
            "from_date"         => ["required", "date", "date_format:Y-m-d"],
            "to_date"           => ["required", "date", "after_or_equal:from_date", "date_format:Y-m-d"],
            "page_limit"        => ["sometimes", "integer", "between:1,100"]
        ];
    }


    public static function settlementCalendarValidation($request)
    {
        $validation = Validator::make($request, [
            'settlement_month' => [
                'required',
                'date_format:Y-m'
            ]
        ]);

        return $validation;
    }

    public static function merchantCommissionDataValidation($request)
    {
        $validation = Validator::make($request, [
            'currency_code' => [
                'required',
                'string',
                Rule::in((new GlobalCurrency())->getSystemSupportedCurrencyInfoByIndex())
            ]
        ]);

        return $validation;
    }

    public static function dplSettingValidation($request) {
        $rules = Validator::make($request, [
            'show_logo' => 'required|integer|in:'.DPL::IS_INACTIVE.','.DPL::IS_ACTIVE,
            'distance_sale_contract' => 'required|string',
            'show_dsc' => 'required|integer|in:'.DPL::IS_INACTIVE.','.DPL::IS_ACTIVE,
            'agreement_name' => 'required|array|min:3|max:3',
            'agreement_name.*' => 'max:70',
            'agreement_content' => 'required|array|min:3|max:3',
//            'agreement_content.*' => 'string',
            'show_agreement' => 'required|array|min:3|max:3',
            'show_agreement.*' => 'integer|in:'.DPL::IS_INACTIVE.','.DPL::IS_ACTIVE,
            'static_fields_enable' => 'sometimes|array|min:5|max:5',
            'static_fields_enable.*' => 'integer|in:'.DPL::IS_INACTIVE.','.DPL::IS_ACTIVE,
            'static_fields_mendatory' => 'sometimes|array|min:5|max:5',
            'static_fields_mendatory.*' => 'integer|in:'.DPL::IS_INACTIVE.','.DPL::IS_ACTIVE,
        ]);

        if (BrandConfiguration::call([FrontendMix::class, 'isAllowedDistanceSaleContractAsFile'])) {
            $rules['distance_sale_contract'] = 'required|max:2048|mimes:'.Arr::implode(',', DplService::DISTANCE_SALE_CONTRACT_FILE_TYPE);
        }

        return $rules;
    }
    public static function isExistSerialNoMerchantWise($serial_no = null, $merchant_id = ''){
        $is_duplicate = false;
        if(!empty($serial_no) && !empty($merchant_id)){
            $merchantTerminalObj = (new MerchantTerminals())->checkUniqueSerialNoMerchantWise($merchant_id, $serial_no);
            if(!empty($merchantTerminalObj)){
                $is_duplicate =  true;
            }
        }
        return $is_duplicate;
    }
    public static function newMerchantTerminalRules($merchant_account_info){

//        rules for creating merchant bank account
        $rules=[
            'CurrentAccountBranchCode'          =>  'required|numeric',
            'CurrentAccountNumber'              =>  'required|numeric',
            'CurrentAccountSuffix'              =>  'required|numeric',
            'CurrentAccountEftIbanNumber'       =>  'required|string',
        ];
//rules for creating merchant terminal
        if (empty($merchant_account_info['IsVirtualPos'])) {
            $rules = Arr::merge($rules, [
                'TerminalSerialNo'              =>  'required|string',
                'TerminalStatus'                =>  'required|string',
                'HasTerminalDeviceDetail'       =>  'required|boolean',
                'TerminalBrandCode'             =>  'required|string',
                'TerminalModelCode'             =>  'required|string',
                'SubMerchantNumber'             =>  'required|string'
            ]);
        }
//        rules for creating pavo terminal
        if (BrandConfiguration::call([BackendMix::class, 'allowImportPavoTransactionByBkmSerialNo']) && MerchantTerminals::terminalType($merchant_account_info["TerminalBrandCode"] ?? 0) == MerchantTerminals::PAVO_TYPE) {
            $merchant_id = $merchant_account_info['FastpayMerchantNumber'] ?? 0;
            $rules = Arr::merge($rules, [
                'TerminalNo',
                'TerminalSerialNo' => [
                    'required',
                    function ($attribute, $value, $fail) use ($merchant_id) {
                        $is_duplicate = self::isExistSerialNoMerchantWise($value, $merchant_id);
                        $message = __('The serial number :serial_no must be unique for merchant.', ['serial_no' => (Arr::isOfType($value) ? Arr::implode(', ', $value) : $value)]);
                        if ($is_duplicate) {
                            $fail($message);
                        }
                    }
                ]
            ]);
        }

        return $rules;
    }

    public static function makePaymentValidation(){
        return [
            "currency_code"       => "required",
            "installments_number"  => "required",
            "invoice_id"         => "required",
            "total"           => "required",
            "merchant_id"           => "required|exists:merchants,id",
            "merchant_user_id"           => "required|exists:merchants,user_id",
        ];
    }

    public static function paxImportBatchServiceRequestRules()
    {
        $rules =  [
            "date" => "required|string|max:15",
            "bankId" => "required|string",
            "time" => "sometimes|string|nullable",
            "voidAmount" => "sometimes|numeric|nullable",
            "refundAmount" => "sometimes|numeric|nullable",
            "paymentAmount" => "sometimes|numeric|nullable",
            "voidCount" => "sometimes|integer|nullable",
            "refundCount" => "sometimes|integer|nullable",
            "paymentCount" => "sometimes|integer|nullable",
            "merchant_terminals_id" => "required|integer|nullable"
            ];
        return $rules;
    }

    public static function merchantCustomBulkCommissionRules($input)
    {
        return Validator::make($input, [
            'merchant_ids' => 'required|array',
            'commission_id' => 'required|integer',
        ]);

    }

    public static function validateCentAmount($attribute, $value, $fail, $amount)
    {
        if ($value == 0 && $amount == 0) {
            $fail = __('Amount can not be 0.0');
            return [false, $fail];
        }
        return [true, null];
    }

    public static function validateUserSecurityImageInformation($request): array
    {
        return self::validateData($request->all(), [
            'security_image' => 'required'
        ]);
    }

    public static function settlementCalendarSearchValidation($request) {
        return Validator::make($request, [
            'merchant_id' => 'required',
        ]);
    }

    public static function settlementReportExportValidation($request) {
        return Validator::make($request, [
            'merchant_id' => 'required',
        ]);
    }

    public static function validationCustomCommissionRules()
    {
        return [
            'merchant_com_file' => ['required', new FileValidation(['xlsx', 'xls'])],
            'merchant_custom_commission_type' => 'required|integer',
        ];
    }

    public static function validationTmpMerchantBankTerminalAttemptResetRules()
    {
        return [
            'merchant_id'   => "required|exists:merchants,id"
        ];
    }

    public static function checkStatusValidationRules($has_order_id = false){
        $rules = ['merchant_key' => 'required|string|max:255'];
        if($has_order_id){
            $rules += [
                "invoice_id" => "required_without:order_id|string|max:50",
                "order_id" => "required_without:invoice_id|string|max:50",
            ];
        }else{
            $rules += ['invoice_id' => self::invoiceIdRule()];
        }

        return $rules;
    }

    public static function earlySettlementValidation() {
        $rules = [
            'merchant_id' => ['required','integer','exists:merchants,id'],
            'currency_id' => ['required', 'integer', 'exists:currencies,id'],
            'merchant_sale_ids' => 'required|array|min:1|max:' . EarlySettlement::DATA_LIMIT,
            'merchant_sale_ids.*' => 'integer',
            'sale_settlement_ids' => 'nullable|array',
            'sale_settlement_ids.*' => 'integer'
        ];

        $messages = [
            'merchant_sale_ids.required' => __('Invalid merchant sale id'),
            'merchant_sale_ids.array' => __('Invalid merchant sale id'),
            'merchant_sale_ids.*.integer' => __('Invalid merchant sale id'),
            'merchant_sale_ids.min' => __('Minimum 1 transactions should be selected'),
            'merchant_sale_ids.max' => __('Transaction selection should not be more than ' . EarlySettlement::DATA_LIMIT),
            'sale_settlement_ids.array' => __('Invalid sale settlement id'),
            'sale_settlement_ids.*.integer' => __('Invalid sale settlement id'),
            'merchant_id.integer' => __('Invalid merchant id'),
            'currency_id.integer' => __('Invalid currency id'),
        ];

        return [$rules, $messages];
    }

    public static function getMerchantDeviceWiseTerminalRequestValidationRules($type=null)
    {
        $rules = [
            'serial_no' => 'required|between:8,22',
            'terminal_id' => 'required|array|between:1,12',
            'terminal_id.*' => 'required',
            'bank_iban' => 'required|array|between:1,12',
            'bank_iban.*' => 'required',
            'type' => 'required|integer',
            'status' => 'sometimes|array',
            'model' => 'required|string|max:10',
            'device_infrastructure_type' => 'required|integer',
            'brand_code' => 'required|string',
            'batch_message' => 'required|string',
        ];

        if($type ==  MerchantTerminals::MERCHANT_TERMINAL_BULK_UPLOAD) {
            $rules += [
                "merchant_id" => "required|exists:merchants,id"
            ];
        }

        $messages = [
            'merchant_id.required' => __('Merchant Id is required'),
            'merchant_id.exists' => __('Merchant not found'),
            'serial_no.required' => __('Serial No. is required'),
            'terminal_id.required' => __('Terminal ID is required'),
            'terminal_id.array' => __('Invalid Terminal Id'),
            'terminal_id.between' => __('You can add terminals between :between'),
            'terminal_id.*.required' => __('Terminal ID is required'),
            'bank_iban.required' => __('Terminal Bank is required'),
            'bank_iban.array' => __('Invalid Terminal Bank'),
            'bank_iban.*.required' => __('Terminal Bank is required'),
            'type.required' => __('Device type is required'),
            'type.integer' => __('Invalid Device Type'),
            'status.array' => __('Invalid Status'),
            'model.required' => __('Model is required'),
            'device_infrastructure_type.required' => __('Device Infrastructure Type is required'),
            'device_infrastructure_type.integer' => __('Invalid Device Infrastructure Type'),
            'brand_code.required' => __('Brand Code is required'),
            'batch_message.required' => __('Batch Message is required'),
        ];

        return [$rules, $messages];
    }

    public static function binMappingRequestValidationRules()
    {
        $rules = [
            'store_arr.from_bank_code.*' => 'required',
            'store_arr.card_type.*' => 'required',
            'store_arr.to_bank_code.*' => 'required',
            'update_arr.from_bank_code.*' => 'required',
            'update_arr.card_type.*' => 'required',
            'update_arr.to_bank_code.*' => 'required',
        ];

        $messages = [
            'store_arr.from_bank_code.*.required' => __('Bank code fields are required'),
            'store_arr.card_type.*.required' => __('Card type fields are required'),
            'store_arr.to_bank_code.*.required' => __('Bank code fields are required'),
            'update_arr.from_bank_code.*.required' => __('Bank code fields are required'),
            'update_arr.card_type.*.required' => __('Card type fields are required'),
            'update_arr.to_bank_code.*.required' => __('Bank code fields are required'),
        ];

        return [$rules, $messages];
    }

    public static function validateMerchantInfoRules($input_data): array
    {
        $validation_rules = [
            'address1'          => ['required'],
            'city'              => ['required', 'max:50'],
            'zip_code'          => ['required', 'max:10'],
            'latitude'          => ['sometimes', 'regex:/^[-]?(([0-8]?[0-9])\.(\d+))|(90(\.0+)?)$/'],
            'longitude'         => ['sometimes', 'regex:/^[-]?((((1[0-7][0-9])|([0-9]?[0-9]))\.(\d+))|180(\.0+)?)$/'],
            'country_id'        => ['required', 'integer', 'exists:countries,id'],
            'mcc'               => ['required'],
            'iso_country_code'  => ['required'],
        ];

        if($input_data['merchant_type'] == Merchant::CORPORATE_MERCHANT_TYPE) {
            $validation_rules['tckn'] = ['required'];

            if(BrandConfiguration::call([FrontendAdmin::class, 'isAllowValidationForTcknAndVkn'])) {
                $validation_rules['tckn'] = Arr::merge($validation_rules['tckn'], ['max:11']);
            }
        }

        if($input_data['merchant_type'] == Merchant::INDIVIDUAL_MERCHANT_TYPE) {
            $validation_rules['vkn'] = ['required'];

            if(BrandConfiguration::call([FrontendAdmin::class, 'isAllowValidationForTcknAndVkn'])) {
                $validation_rules['vkn'] = !empty($input_data['is_merchant_billing_information']) ? Arr::merge($validation_rules['vkn'], ['max:11'])
                                                                                                  : Arr::merge($validation_rules['vkn'], ['max:10']);
            }
        }

        if(BrandConfiguration::call([FrontendAdmin::class, 'isAllowDateOfBirthFieldInMerchantBillingInformation'])) {
            $validation_rules['date_of_birth'] = ['sometimes', 'date', 'date_format:Y-m-d'];
        }

        return $validation_rules;
    }

    public static function validateVirtualCardAccountAndWallet($request)
    {
        $validation = Validator::make($request->all(), [
            'account_number' => ['required'],
            'wallet_number' => ['required']
        ], [
            'account_number.required' => __("Account Number is required"),
            'wallet_number.required' => __("Wallet Number is required")
        ]);

        return $validation;
    }

    public static function paxTerminalParameterRequestRules()
    {
        $rules = [
            "name" => "sometimes|array",
            "name.*" => "sometimes|string|max:20",
            "acqId" => "required|array",
            "acqId.*" => "required|string|max:20",
            "tid" => "required|array",
            "tid.*" => "required|string|max:50",
            "mid" => "required|array",
            "mid.*" => "required|string|max:20",
            "deviceSerial" => "required|string|max:50",
            "date" => "required|integer",
            "vendor" => "sometimes|string|max:10"
        ];
        $messages = [
            'name.array' => __("Name must be an array"),
            'name.*.required' => __("Name is required"),
            'acqId.array' => __("Bank ID must be an array"),
            'acqId.*.required' => __("Bank ID must be an required"),
            'acqId.*.string' => __("Each Bank ID must be a string"),
            'acqId.*.max' => __("Bank ID cannot greater than :max"),
            'mid.array' => __("Merchant ID  must be an array"),
            'mid.*.required' => __("Merchant ID is required"),
            'mid.*.max' => __("Merchant ID cannot greater than :max"),
            'tid.array' => __("Terminal ID  must be an array"),
            'tid.*.required' => __("Terminal ID is required"),
            'tid.*.max' => __("Terminal ID cannot greater than :max"),
            'deviceSerial.required' => __("PaxID is required"),
            'deviceSerial.max' => __("PaxID cannot greater than :max"),
            'date.required' => __("Date is required"),
            'date.integer' => __("Incorrect date format")
        ];

        return [$rules, $messages];
    }

    public static function validateWithdrawalData($input)
    {
        $is_destination_type_walletgate = !empty($input['destination_type']) && $input['destination_type'] == WithdrawalService::DESTINATION_TYPE_WALLET_GATE;
        $currency_settings_obj = (new CurrenciesSettings())->findByCurrencyId(Currency::TRY, $input['merchant_user_type']);
        $max_walletgate_withdraw_limit = $currency_settings_obj->max_walletgate_withdraw_limit;

        $rules = [
            'amount' => [
                'numeric',
                'min:0',
                'max:' . \common\integration\Models\Wallet::getMaxValueForDoubleDataType(),
                'required',
                Rule::when(
                    $is_destination_type_walletgate,
                    ['max:' . $max_walletgate_withdraw_limit]
                ),
            ],
        ];

        if (BrandConfiguration::call([Mix::class, 'isAllowWithdrawToWalletGate'])){
            $rules['destination_type'] = 'required';
        }

        if (!empty($input['destination_type']) && $input['destination_type'] == WithdrawalService::DESTINATION_TYPE_WALLET_GATE) {
            $rules = Arr::merge($rules, [
                'wallet_number' => 'required',
            ]);
        } else {
            $rules = Arr::merge($rules, [
                'bank_name' => 'required',
                'account_holder_name' => 'required',
                'platform_id' => 'required',
                'currency_id' => 'required',
            ]);
        }

        $messages = [];

        if ($is_destination_type_walletgate) {
            $messages = [
                'amount.max' => __('Withdraw to Walletgate amount should not be greater than :amount TRY', ['amount' => $max_walletgate_withdraw_limit]),
            ];
        }

        return Validator::make($input, $rules, $messages);
    }

    public static function validateWithdrawalDataWhileCreate($inputData)
    {
        $rules['amount'] = 'required|numeric';

        if (BrandConfiguration::call([Mix::class, 'isAllowWithdrawToWalletGate'])
            && (!empty($inputData['destination_type'])
                && $inputData['destination_type'] == WithdrawalService::DESTINATION_TYPE_WALLET_GATE)) {

            $rules['destination_type'] = 'required';
        } else {

            $rules = Arr::merge($rules, [
                'bank_name' => 'required',
                'account_holder_name' => 'required',
                'platform_id' => 'required',
                'currency_id' => 'required|numeric',
            ]);
        }

        return Validator::make($inputData, $rules);
    }
    public static function merchantPosCommissionBulkUpdateFromFileValidationRules(){
        return [
            'pos_commission_management_bulk_import' => ['required', new FileValidation(['xlsx'])]
        ];
    }

    public static function distanceSaleContractValidation($input) {
        $rules['distance_sale_contract'] = 'sometimes|max:2048|mimes:'.Arr::implode(',', DplService::DISTANCE_SALE_CONTRACT_FILE_TYPE);

        $messages = [
          'distance_sale_contract.mimes' => __('File type must be ' . Arr::implode(',', DplService::DISTANCE_SALE_CONTRACT_FILE_TYPE))
        ];

        return Validator::make($input, $rules, $messages);
    }

    public static function validateChargeback() : array
    {
        $rules = [
            'trans_id' => 'required',
            'transaction_amount' => 'gt:0|numeric'
        ];
        return $rules;
    }

    public static function verifoneResponseValidation($data)
    {
        $rules = [
            "tranId" => ["required"],
            "tranDatetime" => ["required"],
            "bankRefNo" => ["required"],
            "bankAcquirerId" => ["required"],
            "bankTerminalId" => ["required"],
            "mainTrxType" => ["required", 'in:'. Arr::implode(',', Verifone::REMOTE_ALL_TRANSACTION_TYPE_LIST)],
            "bankTranAmount" => ["required"],
            "cardPan" => ["required"],
            "bankBatchNo" => ["required"],
            "Installment" => ['nullable']
        ];
        $message = [
            "tranId.required" => "tranId is missing.",
            "tranDatetime.required" => "tranDatetime is missing.",
            "bankRefNo.required" => "bankRefNo is missing.",
            "bankAcquirerId.required" => "bankAcquirerId is missing.",
            "bankTerminalId.required" => "bankTerminalId is missing.",
            "mainTrxType.required" => "mainTrxType is missing.",
            "mainTrxType.in" => "mainTrxType transaction type not found.",
            "bankTranAmount.required" => "bankTranAmount is missing.",
            "cardPan.required" => "cardPan is missing.",
            "bankBatchNo.required" => "bankBatchNo is missing.",
            "Installment.required" => "Installment is missing.",
        ];

        return Validator::make($data, $rules, $message);
    }

    public static function commercialCardRequestValidation(): array
    {
        $rules = [
            'min_installment' => 'required|lte:max_installment',
            'max_installment' => 'required|gte:min_installment',
            'installment.*.merchant_com_percentage' => 'required|numeric|gte:0',
            'installment.*.merchant_com_fixed' => 'required|numeric|gte:0',
            'installment.*.end_user_com_percentage' => 'required|numeric|gte:0',
            'installment.*.end_user_com_fixed' => 'required|numeric|gte:0'
        ];

        $messages = [
            'min_installment.required' => __('Min Installment field is required'),
            'min_installment.lte' => __('Min Installment field must be less than or equal to Max Installment'),
            'max_installment.required' => __('Max Installment field is required'),
            'max_installment.gte' => __('Max Installment field must be greater than or equal to Min Installment'),
            'installment.*.merchant_com_percentage.required' => __('Merchant Commission Percentage fields are required'),
            'installment.*.merchant_com_percentage.numeric' => __('Merchant Commission Percentage fields must be numeric'),
            'installment.*.merchant_com_percentage.gte' => __('Merchant Commission Percentage fields must be greater than or equal to 0'),
            'installment.*.merchant_com_fixed.required' => __('Merchant Commission Fixed fields are required'),
            'installment.*.merchant_com_fixed.numeric' => __('Merchant Commission Fixed fields must be numeric'),
            'installment.*.merchant_com_fixed.gte' => __('Merchant Commission Fixed fields must be greater than or equal to 0'),
            'installment.*.end_user_com_percentage.required' => __('End User Percentage Commission fields are required'),
            'installment.*.end_user_com_percentage.numeric' => __('End User Commission Percentage fields must be numeric'),
            'installment.*.end_user_com_percentage.gte' => __('End User Commission Percentage must be greater than or equal to 0'),
            'installment.*.end_user_com_fixed.required' => __('End User Commission Fixed fields are required'),
            'installment.*.end_user_com_fixed.numeric' => __('End User Commission Fixed fields must be numeric'),
            'installment.*.end_user_com_fixed.gte' => __('End User Commission Fixed fields must be greater than or equal to 0'),
        ];

        return [$rules, $messages];
    }

    public static function merchantSaleRecurringDetailsValidation($input)
    {
        $rules = array(
            'merchant_id'   => [ 'required', 'integer', 'exists:merchants,id' ],
            'plan_code'     => [ 'required' ]
        );

        return Validator::make($input, $rules);
    }

    public static function merchantPosPfSettingsFileValidationRules(){
        $rules =  [
            'merchant_pos_pf_settings_file' => ['required','max:'.\common\integration\Models\MerchantPosPFSetting::MAX_UPLOAD_FILESIZE, new FileValidation(['xlsx','xls'])],
        ];
        $message = ['merchant_pos_pf_settings_file.max' => __('The size of the excel file you imported exceeds :file_size MB. Please import a smaller excel file.',['file_size' => \common\integration\Models\MerchantPosPFSetting::MAX_UPLOAD_FILESIZE / 1024])];
        return [$rules ,$message ];
    }

    public static function merchantPosPfSettingsInsertValidationRules($input){

        if(!empty($input['is_send_pf_group_id'] )){
            $input['is_send_pf_group_id'] =  Str::ucFirst( Str::toLower($input['is_send_pf_group_id']));
        }

        if(!empty($input['is_send_tckn_vkn'])){
            $input['is_send_tckn_vkn'] =  Str::ucFirst(Str::toLower($input['is_send_tckn_vkn']));
        }

        $rules =  [
            'merchant_id' => ['required','integer'],
            'sub_merchant_id' => ['required','max:50'],
            'pos_id' => ['required','integer'],
            'bank_page_display_pf_name'=> ['nullable'],
            'is_send_pf_group_id' => ['required','in:'.Arr::implode(',',Arr::values(MerchantPosPFSetting::SEND_PF_GROUP_ID_STATUS_LIST))],
            'group_id_pf_name' => ['nullable'],
            'is_send_tckn_vkn' => ['required','in:'.Arr::implode(',',Arr::values(MerchantPosPFSetting::SEND_TCKN_VKN_STATUS_LIST))]
        ];

        if(!Arr::keyExists('is_send_pf_group_id',$input)){
            $rules = Arr::unset($rules,['is_send_pf_group_id']);
        }

        if(!Arr::keyExists('is_send_tckn_vkn',$input)){
            $rules = Arr::unset($rules,['is_send_tckn_vkn']);
        }

        $message =  [
            'is_send_pf_group_id.in' => 'Send Group ID PF Name must be in Yes or No',
            'is_send_tckn_vkn.in' => 'Send TCKN/VKN must be in Yes or No',
        ];


        return [$rules, $message , $input];
    }

    public static function earlySettlementBulkImportFileValidation(): array
    {
        $rules = [
            'transaction_file' => 'required|file|mimes:xlsx,xls',
        ];

        $messages = [
            'transaction_file.required' => __("Please upload a file"),
            'transaction_file.file' => __("The uploaded file must be a valid file"),
            'transaction_file.mimes' => __("The file must be a type of xlsx or xls"),
        ];

        return [$rules, $messages];
    }

    public static function earlySettlementBulkImportDataValidation(): array
    {
        $rules = [
            '*.merchant_id' => 'required|integer',
            '*.transaction_id' => 'required',
            '*.installments_number' => 'required|integer|min:0',
            '*.total_installment_count' => 'required|integer|min:0',
            '*.commission_fixed' => 'required|numeric|min:0',
            '*.commission_percentage' => 'required|numeric|min:0',
            '*.cot_fixed' => 'required|numeric|min:0',
            '*.cot_percentage' => 'required|numeric|min:0',
        ];

        $messages = [
            '*.merchant_id.required' => __("Merchant ID is required for row") . " :attribute.",
            '*.merchant_id.integer' => __("Merchant ID should be numeric for row") . " :attribute.",
            '*.transaction_id.required' => __("Transaction ID is required for row") . " :attribute.",
            '*.installments_number.required' => __("Installments Number is required for row") . " :attribute.",
            '*.installments_number.integer' => __("Installments Number should be numeric for row") . " :attribute.",
            '*.installments_number.min' => __("Installments Number must be at least 0 for row") . " :attribute.",
            '*.total_installment_count.required' => __("Total Installment Count is required for row") . " :attribute.",
            '*.total_installment_count.integer' => __("Total Installment Count should be numeric for row") . " :attribute.",
            '*.total_installment_count.min' => __("Total Installment Count must be at least 0 for row") . " :attribute.",
            '*.commission_fixed.required' => __("Commission Fixed is required for row") . " :attribute.",
            '*.commission_fixed.numeric' => __("Commission Fixed should be numeric for row") . " :attribute.",
            '*.commission_fixed.min' => __("Commission Fixed must be at least 0 for row") . " :attribute.",
            '*.commission_percentage.required' => __("Commission Percentage is required for row") . " :attribute.",
            '*.commission_percentage.numeric' => __("Commission Percentage should be numeric for row") . " :attribute.",
            '*.commission_percentage.min' => __("Commission Percentage must be at least 0 for row") . " :attribute.",
            '*.cot_fixed.required' => __("Cot Fixed is required for row") . " :attribute.",
            '*.cot_fixed.numeric' => __("Cot Fixed should be numeric for row") . " :attribute.",
            '*.cot_fixed.min' => __("Cot Fixed must be at least 0 for row") . " :attribute.",
            '*.cot_percentage.required' => __("Cot Percentage is required for row") . " :attribute.",
            '*.cot_percentage.numeric' => __("Cot Percentage should be numeric for row") . " :attribute.",
            '*.cot_percentage.min' => __("Cot Percentage must be at least 0 for row") . " :attribute.",
        ];

        return [$rules, $messages];
    }
    public static function posProjectValidation(): array
    {
        $rules = [
            'name' => 'required|max:20',
            'status' => 'required'
        ];
        $errmsg = [
            'name.required' => __("Name EN is required"),
            'name.max' => __('Name length should be max 20.'),
            'status.required' => __("Status is required"),
        ];

        return [$rules, $errmsg];
    }

    public static function validateBankData($inputData, $id = null) {
        $rules = [
//            'code' => 'required|string|max:30|unique:banks,code,'.$id,
            'payment_provider' => 'required|string',
            'main_bank_code' => 'required|string|max:20',
            'bank_code_postfix' => 'string|max:10',
            'issuer_name' => 'required|string|max:100',
            'name' => 'required|string|max:100',
            'country_id' => 'required|numeric',
            'pp_client_id' => 'sometimes|string|max:200',
            'pp_client_secret' => 'sometimes|string|max:200',
            'vp_client_id' => 'sometimes|string|max:200',
            'vp_client_secret' => 'sometimes|string|max:200',
            'terminal_id' => BrandConfiguration::call([BackendAdmin::class, 'isTerminalIdRequired']) && empty($inputData['is_acquirer_bank'])?'required|max:50' :'max:50',
            'pf_id' => [
                Rule::requiredIf(function() use ($inputData) {
                    return BrandConfiguration::call([PaymentFeatureTrait::class, 'isAllowBankWisePFId']) &&
                        $inputData['payment_provider'] == config('constants.PAYMENT_PROVIDER.QNB_FINANSBANK');
                }),
            ],
        ];

        if (!empty($id)) {
            $rules['merchant_pos_pf_base_url'] = 'sometimes|string|max:256';
            $rules['logo'] = ['sometimes', new FileValidation(['png', 'jpg', 'jpeg', 'svg'])];
        }

        return Validator::make($inputData, $rules);
    }
    public static function validateMerchantPosAcquiringSetting($input_data)
    {
        $rules = [
            'merchant_id' => 'required|integer',
            'pos_acquiring_pos_ids' => 'required|array',
            'pos_acquiring_pos_ids.*' => 'required|integer',
            'client_ids' => 'required|array',
            'client_ids.*' => 'required|string|max:200',
            'store_keys' => 'required|array',
            'store_keys.*' => 'required|string|max:200',
            'terminal_ids' => 'required|array',
            'terminal_ids.*' => 'required|string|max:50',
        ];

        $message = [
            "pos_acquiring_pos_ids.*.required" => "Pos Field is required",
            "pos_acquiring_pos_ids.*.integer" => "Pos Field must be integar",
            'client_ids.*.required' => "Client ID Field is required",
            'client_ids.*.string' => "Client ID Field must be string",
            'client_ids.*.max' => "Client ID Field must be less than or equal 200",
            'store_keys.*.required' => "Store Key Field is required",
            'store_keys.*.string' => "Store Key Field must be string",
            'store_keys.*.max' => "Store Key Field must be less than or equal 200",
            'terminal_ids.*.required' => "Terminal ID Field is required",
            'terminal_ids.*.string' => "Terminal ID Field must be string",
            'terminal_ids.*.max' => "Terminal ID Field must be less than or equal 50",
        ];

        return Validator::make($input_data, $rules, $message);
    }

    public static function validateDplPaymentLink($input, $dpl)
    {
        $minimum_range_for_max_number_of_uses = $dpl->number_of_uses ? $dpl->number_of_uses : 1;
        $is_not_one_time_payment_link = $dpl->type != DPL::FIRST_TIME_PAYMENT_LINK;

        $rules = [
            "currency" => ['required'],
            "is_enabled" => ['nullable', 'string', Rule::in(['on'])],
            "max_number_of_uses" => [Rule::requiredIf($is_not_one_time_payment_link), 'numeric', 'min:' . $minimum_range_for_max_number_of_uses],
            "expire_date" => [Rule::requiredIf($is_not_one_time_payment_link), 'date', 'date_format:'. ManipulateDate::FORMAT_DATE_d_m_Y_DOT, 'after_or_equal:today'],
        ];

        return Validator::make($input, $rules);
    }

    public static function validateMerchantsMoneyTransferLimit($currencies, $types, $input_data) {
        $rules = [];
        $messages = [];
        foreach ($currencies as $currency) {
            foreach ($types as $key => $type) {
                $currencyCode = $currency->code;

                $rules["enable_{$key}.{$currencyCode}"] = 'boolean';
                $rules["{$key}_daily_limit.{$currencyCode}"] = 'sometimes|numeric|min:'. MerchantMoneyTransferLimit::DEFAULT_MIN_LIMIT .'|required_if:enable_'.$key.'.'.$currencyCode.','.MerchantMoneyTransferLimit::STATUS_ACTIVE.'|max:' .  \common\integration\Models\Wallet::getMaxValueForDoubleDataType();
                $rules["{$key}_weekly_limit.{$currencyCode}"] = 'sometimes|numeric|gte:'.$key.'_daily_limit.'.$currencyCode.'|required_if:enable_'.$key.'.'.$currencyCode.','.MerchantMoneyTransferLimit::STATUS_ACTIVE.'|max:' .  \common\integration\Models\Wallet::getMaxValueForDoubleDataType();
                $rules["{$key}_monthly_limit.{$currencyCode}"] = 'sometimes|numeric|gte:'.$key.'_weekly_limit.'.$currencyCode.'|required_if:enable_'.$key.'.'.$currencyCode.','.MerchantMoneyTransferLimit::STATUS_ACTIVE.'|max:' .  \common\integration\Models\Wallet::getMaxValueForDoubleDataType();
                $formated_type = Str::replace('_', ' ', $key);

                $messages["{$key}_daily_limit.{$currencyCode}.min"] = __(':type Daily limit must be at least :min_limit', ['type' => $formated_type, 'min_limit' => MerchantMoneyTransferLimit::DEFAULT_MIN_LIMIT]);

                $messages["{$key}_weekly_limit.{$currencyCode}.gte"] = __(':type Weekly limit must be greater than or equal to the Daily limits', ['type' => $formated_type]);
                $messages["{$key}_weekly_limit.{$currencyCode}.required_if"] = __(':type Weekly limit must be greater than or equal to the Daily limits', ['type' => $formated_type]);

                $messages["{$key}_monthly_limit.{$currencyCode}.gte"] = __(':type Monthly limit must be greater than or equal Weekly and Daily limit', ['type' => $formated_type]);
                $messages["{$key}_monthly_limit.{$currencyCode}.required_if"] = __(':type Monthly limit must be greater than or equal Weekly and Daily limit', ['type' => $formated_type]);
            }
        }

        return Validator::make($input_data, $rules, $messages);
    }
}
