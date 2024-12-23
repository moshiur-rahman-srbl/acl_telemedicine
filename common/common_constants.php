<?php
/**
 * User: user
 * Date: 4/24/2020
 * Time: 12:33 PM
 */

define('COMMON_ADMIN_URL_SLUG', '');

function getCommonConstants()
{
    $mainArray = [
        'TEST_KEY' => 'test value',
        'SYSTEM_SUPPORTED_LANGUAGE' => ['en', 'tr', 'lt', 'es'],
        'ENCRYPTION_FIXED_IV' => env('ENCRYPTION_FIXED_IV'),
        'ENCRYPTION_FIXED_SALT' => env('ENCRYPTION_FIXED_SALT'),
        'FLOAT_NUMBER_PATTER' => '[0-9]+([\.][0-9]{1,})?',
        'PASSWORD_CHANGE_AFTER_MONTHS' => env('PASSWORD_CHANGE_AFTER_MONTHS', 0),
        'PASSWORD_DENY_LAST_USED' => env('PASSWORD_DENY_LAST_USED', 3),
        'PASSWORD_SECURITY_TYPE' => env('PASSWORD_SECURITY_TYPE', 2),
        'BRAND_NAME_CODE_LIST' => [
            'BP' => 'BP',
            'DP' => 'DP',
            'EP' => 'EP',
            'FL' => 'FL',
            'FP' => 'FP',
            'IM' => 'IM',
            'PB' => 'PB',
            'PN' => 'PN',
            'SP' => 'SP',
            'VP' => 'VP',
            'SR' => 'SR',
            'AP' => 'AP',
            'PP' => 'PP',
            'MP' => 'MP',
            'YP' => 'YP',
            'QP' => 'QP',
            'QP_TENANT' => 'QP_TENANT',
            'SD' => 'SD',
            'PL' => 'PL',
            'PC' => 'PC',
            'HP' => 'HP',
            'MOP' => 'MOP',
            'PM' => 'PM',
            'PIN' => 'PIN',
        ],
        'SMS_PROVIDER_NAMES' => [
            'MOBILISIM' => 'MOBILISIM',
            'CODEC' => 'CODEC',
            'ISOBIL' => 'ISOBIL',
            'VERIMOR' => 'VERIMOR',
            'PISANO' => 'PISANO',
            'JETSMS' => 'JETSMS',
            'POSTAGUVERCINI' => 'POSTAGUVERCINI',
            'CODEC_PLUS' => 'CODEC_PLUS',
            'ATPAY' => 'ATPAY',
            'CODEC_FAST' => 'CODEC_FAST',
        ],

        'TRANSACTION_OTP_TIME_OUT' => 3,
        'APP_ENVIRONMENT' => env('APP_ENV'),
        'APP_DOMAIN' => env('APP_DOMAIN', ''),
        'APP_ADMIN_DOMAIN' => env('APP_DOMAIN', ''),


        'MAX_OTP_RESEND_LIMIT' => env('MAX_OTP_RESEND_LIMIT'),
        'MAX_OTP_FAILED_ATTEMPT' => env('MAX_OTP_FAILED_ATTEMPT'),

        'SMS_VERIFICATION_HASH' => env('SMS_VERIFICATION_HASH', ''),
        'SUPPORT_EMAIL_ADDRESS' => env('SUPPORT_EMAIL_ADDRESS', ''),
        'OTP_SEND_URL' => env('OTP_SEND_URL', ''),


        'DEFAULT_PASSWORD_ALPHANUMERIC' => 'Nop@ss1234',
        'DEFAULT_BILL_EMAIL' => env('DEFAULT_BILL_EMAIL', ''),
        'DEFAULT_BILL_PHONE' => env('DEFAULT_BILL_PHONE', ''),


    ];

    return $mainArray;
}
