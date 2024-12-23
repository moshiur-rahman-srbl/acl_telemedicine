<?php
/*
|--------------------------------------------------------------------------
| Brand Information
|--------------------------------------------------------------------------
|
| This is all dynamic information about website owning company.
|
*/
return [
    'name' => env('BRAND_NAME', ''),
    'name_code' => env('BRAND_NAME_CODE', ''),
    'logo' => env('BRAND_LOGO_PATH', ''),
    'logo_2' => env('BRAND_LOGO_2_PATH', ''),
    'logo_white' => env('BRAND_LOGO_WHITE_PATH', ''),
    'favicon' => env('BRAND_FAVICON', ''),
    'base_app_url' => env('BASE_APP_URL', ''),
    'provisioning_url' => env('PROVISIONING_URL', ''),
    'styles' => [
        'colors' => env('COLORS_CSS', ''),
    ],
    'contact_info' => [
        'phone_number' => env('INFO_PHONE_NUMBER', ''),
        'full_phone_number' => env('FULL_PHONE_NUMBER', ''),
        'email' => env('INFO_EMAIL', ''),
        'company_full_name' => env('COMPANY_FULL_NAME', ''),
        'company_full_name_en' => env('COMPANY_FULL_NAME_EN', ''),
        'address_line_1' => env('COMPANY_ADDRESS_LINE_1', ''),
        'address_line_2' => env('COMPANY_ADDRESS_LINE_2', ''),
        'website' => env('COMPANY_WEBSITE', '')
    ],
    'social_media' => [
        'facebook' => env('COMPANY_FACEBOOK', ''),
        'twitter' => env('COMPANY_TWITTER', ''),
        'instagram' => env('COMPANY_INSTAGRAM', ''),
        'linkedin' => env('COMPANY_LINKEDIN', ''),
        'youtube' => env('COMPANY_YOUTUBE', '')
    ],
    'emails' => [
        'compliance' => env('COMPLIANCE_EMAIL', ''),
        'operations' => env('OPERATIONS_EMAIL', ''),
        'sales' => env('SALES_EMAIL', ''),
        'support' => env('SUPPORT_EMAIL', ''),
        'support_tr' => env('SUPPORT_EMAIL_TR', ''),
    ]
];
