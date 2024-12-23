<?php
/*
|--------------------------------------------------------------------------
| Brand Information
|--------------------------------------------------------------------------
|
| This is all dynamic information about website owning company.
|
*/

function getBrandConstants() {

    return [
        'name' => env('BRAND_NAME'),
        'name_code' => env('BRAND_NAME_CODE'),
        'logo' => env('BRAND_LOGO_PATH', 'images/brand/sipay/logo.png'),
        'logo_2' => env('BRAND_LOGO_2_PATH', 'images/brand/sipay/logo2.png'),
        'logo_white' => env('BRAND_LOGO_WHITE_PATH', 'images/brand/sipay/logo_white.png'),
        'logo_v_pos' => 'assets/brand/'.env('BRAND_NAME_CODE').'/logo_v_pos.png',
        'logo_v_pos_white' => 'assets/brand/'.env('BRAND_NAME_CODE').'/logo_v_pos_white.png',
        'favicon' => env('BRAND_FAVICON', 'images/brand/sipay/favicon.png'),
        'base_app_url' => env('BASE_APP_URL'),
        'provisioning_url' => env('PROVISIONING_URL'),
        'support_url' => env('SUPPORT_URL',''),
        'tax_number' => env('TAX_NUMBER',''),
        'tax_office' => env('TAX_OFFICE',''),
        'tax_office_code' => env('TAX_OFFICE_CODE',''),
        'api_docs_link' => env('API_DOCS_LINK',''),
        'styles' => [
            'colors' => env('COLORS_CSS', 'public/css/colors/sipay.css'),
            'decorative_img' => env('LOGIN_DECORATIVE_IMG', 'public/assets/images/pic.png')
        ],
        'contact_info' => [
            'phone_number' => env('INFO_PHONE_NUMBER'),
            'full_phone_number' => env('FULL_PHONE_NUMBER'),
            'email' => env('INFO_EMAIL', ''),
            'company_full_name' => env('COMPANY_FULL_NAME'),
            'company_full_name_en' => env('COMPANY_FULL_NAME_EN'),
            'address_line_1' => env('COMPANY_ADDRESS_LINE_1'),
            'address_line_2' => env('COMPANY_ADDRESS_LINE_2'),
            'website' => env('COMPANY_WEBSITE'),
            'mersis_no' => env('COMPANY_MERSIS_NO'),
            'kep_address' => env('COMPANY_KEP_ADDRESS'),
            'limits_and_fees_link_tr' => env('LIMITS_AND_FEES_LINK_TR'),
            'limits_and_fees_link_en' => env('LIMITS_AND_FEES_LINK_EN')
        ],
        'social_media' => [
            'facebook' => env('COMPANY_FACEBOOK'),
            'twitter' => env('COMPANY_TWITTER'),
            'instagram' => env('COMPANY_INSTAGRAM'),
            'linkedin' => env('COMPANY_LINKEDIN'),
            'youtube' => env('COMPANY_YOUTUBE','#')
        ],
        'emails' => [
            'compliance' => env('COMPLIANCE_EMAIL'),
            'operations' => env('OPERATIONS_EMAIL'),
            'sales' => env('SALES_EMAIL'),
            'support' => env('SUPPORT_EMAIL'),
            'support_tr' => env('SUPPORT_EMAIL_TR'),
        ]
    ];

}
