<?php

use common\integration\Brand\Configuration\All\Mix;
use common\integration\BrandConfiguration;


$brands = include('brand.php');

require_once(base_path('common/common_constants.php'));
$common_const = getCommonConstants();

$regular = [
    'defines' => [
        'ADMIN_URL_SLUG' => COMMON_ADMIN_URL_SLUG,
        'USER_GROUP_NAME' => 'ADMIN_USERGROUP',
        'MERCHANT_GROUP_NAME' => 'MERCHANT_USERGROUP',
        'CUSTOMER_USER_TYPE' => 0,
        'ADMIN_USER_TYPE' => 1,
        'MERCHANT_USER_TYPE' => 2,
        'ROLE_TITLE' => 'ADMIN_ROLE',
        'MERCHANT_ROLE_TITLE' => 'MERCHANT_ROLE',
        'ADMIN_ROLE_PAGE' => array(3078, 3075, 3068, 3067, 3066, 3065, 3058, 3057, 3056, 3055, 3049, 3048, 3047,
            3046, 3045, 3053),
        'SSADMIN_ID' => 1,
        'ACTIVE_INACTIVE_LIST' => [
            '3' => 'All',
            '0' => 'Pending',
            '1' => 'Approved',
            '2' => 'Not Approved'
        ],
        'MY_APPLICATION_STATUS_LIST' => ['3' => 'All', '0' => 'Pending', '1' => 'Approved', '2' => 'Rejected'],
        'MALE_FEMALE_LIST' => ['3' => 'All', '1' => 'Male', '2' => 'Female'],
        'USER_CATEGORY_LIST' => \App\User::USER_CATEGORIES,
        'LOGIN_OTP_EXPIRE_TIME' => 3,


        'APP_USERS_INDEX' => 'usermanagement.index',
        'APP_USERS_CREATE' => 'usermanagement.create',
        'APP_USERS_EDIT' => 'usermanagement.edit',
        'APP_USERS_SHOW' => 'usermanagement.show',
        'APP_USERS_DELETE' => 'usermanagement.destroy',
        'APP_USERS_VERIFY_OTP' => 'usermanagement.verifyotp',
        'APP_USERS_RESEND_OTP' => 'usermanagement.resendOTP',
        'APP_USERS_CHANGEPASSWORD' => 'usermanagement.changepassword',
        'APP_SECRET_QUESTION' => 'usermanagement.secrectQuestion',
        'APP_EMPLOYEE_INDEX' => 'employees.index',
        'APP_EMPLOYEE_CREATE' => 'employees.create',
        'APP_EMPLOYEE_DELETE' => 'employee.destroy',
        'APP_EMPLOYEE_EDIT'=>'employee.edit',
        'APP_EMPLOYEE_UPDATE'=>'employees.update',

        'APP_ROLES_INDEX' => 'rolemanagement.index',
        'APP_ROLES_CREATE' => 'rolemanagement.create',
        'APP_ROLES_EDIT' => 'rolemanagement.edit',
        'APP_ROLES_DELETE' => 'rolemanagement.destroy',

        'APP_USERGROUPS_INDEX' => 'usergroupmanagement.index',
        'APP_USERGROUPS_CREATE' => 'usergroupmanagement.create',
        'APP_USERGROUPS_EDIT' => 'usergroupmanagement.edit',
        'APP_USERGROUPS_DELETE' => 'usergroupmanagement.destroy',


        'APP_MODULES_INDEX' => 'modulemanagement.index',
        'APP_MODULES_CREATE' => 'modulemanagement.create',
        'APP_MODULES_EDIT' => 'modulemanagement.edit',
        'APP_MODULES_DELETE' => 'modulemanagement.destroy',

        'APP_SUBMODULES_INDEX' => 'submodulemanagement.index',
        'APP_SUBMODULES_CREATE' => 'submodulemanagement.create',
        'APP_SUBMODULES_EDIT' => 'submodulemanagement.edit',
        'APP_SUBMODULES_DELETE' => 'submodulemanagement.destroy',

        'APP_PAGES_INDEX' => 'pagemanagement.index',
        'APP_PAGES_CREATE' => 'pagemanagement.create',
        'APP_PAGES_EDIT' => 'pagemanagement.edit',
        'APP_PAGES_DELETE' => 'pagemanagement.destroy',

        'APP_USERGROUP_ROLE_ASSOCIATION' => 'usergrouproles.index',
        'APP_USERGROUP_ROLE_ASSOCIATION' => 'usergroup&roleassociation.index',
        'APP_USERGROUP_EDIT_ROLE_ASSOCIATION' => 'usergroup&roleassociation.edit',

        'APP_ROLE_PAGE_ASSOCIATION' => 'role&pageassociation.index',
        'APP_ROLE_PAGE_EDIT_ASSOCIATION' => 'role&pageassociation.edit',
        'APP_ROLE_PAGE_ASSOCIATION_EXPORT' => 'role&pageassociation.exportRolePage',
        'APP_APPOINMENT_INDEX' => 'appoinment.index',
'APP_APPOINMENT_CREATE' => 'appoinment.create',
'APP_APPOINMENT_EDIT' => 'appoinment.edit',
'APP_APPOINMENT_VIEW' => 'appoinment.view',       
'APP_APPOINMENT_DELETE' => 'appoinment.delete',


        'APP_DOCTOR_INDEX' => 'doctor.index',
'APP_DOCTOR_CREATE' => 'doctor.create',
'APP_DOCTOR_EDIT' => 'doctor.edit',
'APP_DOCTOR_VIEW' => 'doctor.view',       
'APP_DOCTOR_DELETE' => 'doctor.delete',

        'APP_SITE_SETTINGS_EDIT' => 'sitesettings.edit',

        'COMPLIANCE_EMAIL' => env('COMPLIANCE_EMAIL', 'compliance@sipay.com.tr'),
        'MAIL_FROM_ADDRESS' => env("SYSTEM_NO_REPLY_ADDRESS"),
        'MAIL_FROM_NAME' => env('BRAND_NAME', 'Sipay'),
        'NON_USER_SEND_MONEY_TIME_DIFFERENCE' => 72 * 60, //72 hours in mins
        'NOT_VERIFIED_USER_SEND_MONEY_TIME_DIFFERENCE' => 24 * 60, //72 hours in mins
        'B2B_TIME_DIFFERENCE' => 72 * 60, //72 hours in mins
        'APP_LOGS_INDEX' => 'logs.index',

        //test Global admin id and Finance dept user id
        'GA_USERGROUP_ID' => 2,
        'FDU_USERGROUP_ID' => 33,
        'PANEL' => 'admin',
    ],
    'USER_STATUS' => [
        'ENABLE' => '1',
        'DISABLED' => '2',
        'AWAITING_DISABLE' => '3',
        'AWAITING_GSM' => '4'
    ],
    "SUCCESS_CODE" => 100,
];
return array_merge($common_const, $regular);
?>
