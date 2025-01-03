<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */


use Illuminate\Support\Facades\Session;

Route::get('/config-cache', function () {
//    dd(Session::get('permittedRouteNames'));

    Artisan::call('config:cache');
    return "Config-Cache is cleared";
});


Route::group(['prefix' => config('constants.defines.ADMIN_URL_SLUG')], function () {

    Route::get('/load-notification', 'HomeController@loadNotification')->name('loadNotification');
    Auth::routes();
    Route::post('/login', 'Auth\LoginController@login')->name('loginpost');
    Route::match(['get', 'post'], '/password/checkSecretQuestion', 'Auth\ForgotPasswordController@checkSecretQuestion')->name('password.checkSecretQuestion');
    Route::get('/verifyOTP', 'OTPController@index')->name('verifyOTP')->middleware('auth');
    Route::post('/verifyOTP', 'OTPController@verify')->name('verifyOTP')->middleware('auth');
    Route::post('/resendOTP', 'OTPController@resend')->name('resendOTP')->middleware('auth');

    Route::get('role-pages-association/getassociation/{id}', 'RolePageController@ajaxGetRequest')->name('role.page.getassociation');
    Route::get('pages/getassociation/{id}', 'PageController@ajaxGetData')->name('pages.getassociation');
    Route::get('usergroup-role-association/getassociation/{id}', 'UsergroupRoleController@ajaxGetData')->name('usergroup.role.getassociation');
    Route::get('user-usergroup-association/getassociation/{id}', 'UserUsergroupController@ajaxGetData')->name('user.usergroup.getassociation');

    Route::get('/clearOTP', 'OTPController@clearOTP')->name('clearOTP');

    Route::post('password/reset', 'Auth\PasswordResetController@passwordReset')->name('password.request');
    Route::get('password/{type}/{reset_token}/{email}', 'Auth\PasswordResetController@passwordResetForm')->name('admin.reset.password');
    Route::post('password/{type}/{reset_token}/{email}', 'Auth\PasswordResetController@passwordUpdate')->name('admin.reset.password');

    Route::get('password/create/{encoded_email}', 'Auth\PasswordResetController@passwordCreateForm')->name('password.create');
    Route::post('password/create/{encoded_email}', 'Auth\PasswordResetController@passwordReset')->name('password.create');

    Route::group(['middleware' => ['TwoFA', 'passCheckr']], function () {
        Route::get('/', 'HomeController@index')->name('home');
        Route::match(['get', 'post'], '/home', 'HomeController@index')->name('home');
        Route::get('/visit-last-login-history', 'HomeController@visitLastLoginHistory')->name('visitLastLoginHistory');
    });

    Route::group(['middleware' => 'auth'], function () {
        Route::match(['get', 'put', 'post'], '/changepassword', 'UserController@changepassword')->name(Config::get('constants.defines.APP_USERS_CHANGEPASSWORD'));
        Route::match(['get', 'post'], 'secret-question', 'UserController@secrectQuestion')->name(Config::get('constants.defines.APP_SECRET_QUESTION'));
    });

    Route::group(['middleware' => ['auth', 'permission', 'TwoFA', 'VisitorLog', 'passCheckr']], function () {

        Route::get('profile/edit/{id}', 'UserController@profileEdit')->name('profile.edit.get');
        Route::post('profile/edit/{id}', 'UserController@profileEdit')->name('profile.edit.post');

        //Users
        Route::get('/users', 'UserController@index')->name(Config::get('constants.defines.APP_USERS_INDEX'));
        Route::match(['get', 'post'], '/users/create', 'UserController@create')->name(Config::get('constants.defines.APP_USERS_CREATE'));
        Route::get('/users/{id}', 'UserController@view')->name(Config::get('constants.defines.APP_USERS_SHOW'));
        Route::match(['get', 'put', 'post'], '/users/{id}/edit/{action}', 'UserController@edit')->name(Config::get('constants.defines.APP_USERS_EDIT'));
        Route::delete('/users/{id}', 'UserController@destroy')->name(Config::get('constants.defines.APP_USERS_DELETE'));


        Route::get('roles', 'RoleController@index')->name(Config::get('constants.defines.APP_ROLES_INDEX'));
        Route::match(['get', 'post'], '/roles/create', 'RoleController@create')->name(Config::get('constants.defines.APP_ROLES_CREATE'));
        Route::match(['get', 'post'], 'roles/edit/{id}', 'RoleController@edit')->name(Config::get('constants.defines.APP_ROLES_EDIT'));
        Route::delete('roles/destroy/{id}', 'RoleController@destroy')->name(Config::get('constants.defines.APP_ROLES_DELETE'));


        Route::get('usergroups', 'UsergroupController@index')->name(Config::get('constants.defines.APP_USERGROUPS_INDEX'));
        Route::match(['get', 'post'], 'usergroups/add', 'UsergroupController@create')->name(Config::get('constants.defines.APP_USERGROUPS_CREATE'));
        Route::match(['get', 'post'], 'usergroups/edit/{id}', 'UsergroupController@edit')->name(Config::get('constants.defines.APP_USERGROUPS_EDIT'));
        Route::delete('usergroups/destroy/{id}', 'UsergroupController@destroy')->name(Config::get('constants.defines.APP_USERGROUPS_DELETE'));


        Route::get('modules', 'ModuleController@index')->name(Config::get('constants.defines.APP_MODULES_INDEX'));
        Route::match(['get', 'post'], 'modules/add', 'ModuleController@create')->name(Config::get('constants.defines.APP_MODULES_CREATE'));
        Route::match(['get', 'post'], 'modules/edit/{id}', 'ModuleController@edit')->name(Config::get('constants.defines.APP_MODULES_EDIT'));
        Route::delete('modules/destroy/{id}', 'ModuleController@destroy')->name(Config::get('constants.defines.APP_MODULES_DELETE'));


        Route::get('submodules', 'SubModuleController@index')->name(Config::get('constants.defines.APP_SUBMODULES_INDEX'));
        Route::match(['get', 'post'], 'submodules/add', 'SubModuleController@create')->name(Config::get('constants.defines.APP_SUBMODULES_CREATE'));
        Route::match(['get', 'post'], 'submodules/edit/{id}', 'SubModuleController@edit')->name(Config::get('constants.defines.APP_SUBMODULES_EDIT'));
        Route::delete('submodules/destroy/{id}', 'SubModuleController@destroy')->name(Config::get('constants.defines.APP_SUBMODULES_DELETE'));

        //Pages
        Route::get('pages', 'PageController@index')->name(Config::get('constants.defines.APP_PAGES_INDEX'));
        Route::match(['get', 'post'], 'pages/add', 'PageController@create')->name(Config::get('constants.defines.APP_PAGES_CREATE'));
        Route::match(['get', 'post'], 'pages/edit/{id}', 'PageController@edit')->name(Config::get('constants.defines.APP_PAGES_EDIT'));
        Route::delete('pages/destroy/{id}', 'PageController@destroy')->name(Config::get('constants.defines.APP_PAGES_DELETE'));


        Route::match(['get', 'post'], 'usergrouproles', 'UsergroupRoleController@index')->name(Config::get('constants.defines.APP_USERGROUP_ROLE_ASSOCIATION'));
        Route::match(['get', 'post'], 'usergrouproles/edit', 'UsergroupRoleController@edit')->name(Config::get('constants.defines.APP_USERGROUP_EDIT_ROLE_ASSOCIATION'));


        Route::match(['get', 'post'], 'rolepages', 'RolePageController@index')->name(Config::get('constants.defines.APP_ROLE_PAGE_ASSOCIATION'));
        Route::match(['get', 'post'], 'rolepages/edit', 'RolePageController@edit')->name(Config::get('constants.defines.APP_ROLE_PAGE_EDIT_ASSOCIATION'));
        Route::get('export/RolePage', 'RolePageController@exportRolePage')->name(Config::get('constants.defines.APP_ROLE_PAGE_ASSOCIATION_EXPORT'));

        Route::get('usergroup-role-association', 'UsergroupRoleController@index')->name('usergroup.role.index');
        Route::post('usergroup-role-association/modify', 'UsergroupRoleController@index')->name('usergroup.role.modify');

        Route::get('user-usergroup-association', 'UserUsergroupController@index')->name('user.usergroup.index');
        Route::post('user-usergroup-association/modify', 'UserUsergroupController@modify')->name('user.usergroup.modify');


        Route::post('profile/verifyOTP/{id}', 'UserController@verifyOTP')->name(Config::get('constants.defines.APP_USERS_VERIFY_OTP'));
        Route::get('profile/resendOTP/{id}', 'UserController@resendOTP')->name(Config::get('constants.defines.APP_USERS_RESEND_OTP'));
        Route::get('logs', 'LogsController@index')->name(Config::get('constants.defines.APP_LOGS_INDEX'));


        //settings
        Route::match(['get', 'post'], 'settings/edit', 'SettingController@edit')->name(Config::get('constants.defines.APP_SITE_SETTINGS_EDIT'));

        Route::get('employee', 'EmployeeController@index')->name(Config::get('constants.defines.APP_EMPLOYEE_INDEX'));
        Route::match(['get', 'post'], '/employee/create', 'EmployeeController@create')->name(Config::get('constants.defines.APP_EMPLOYEE_CREATE'));
        Route::match(['get', 'post'], 'employee/edit/{id}', 'EmployeeController@edit')->name(Config::get('constants.defines.APP_EMPLOYEE_EDIT'));
        Route::delete('employee/destroy/{id}', 'EmployeeController@destroy')->name(Config::get('constants.defines.APP_EMPLOYEE_DELETE'));
    });
});
Route::get('lang/{locale}', 'LocalizationController@index')->name('lang');
Route::get('get-file/{type}/{ref_id}/{column}','HomeController@getFile')->middleware('auth')->name('get_secure_file');
Route::get('/download/generated/{encrypted_link}','HomeController@getLink')->prefix(config('constants.defines.ADMIN_URL_SLUG'))->middleware('auth')->name('generated_link');


