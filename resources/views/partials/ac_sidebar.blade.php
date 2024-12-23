<?php
/*
 * For every page of a submodule please maintain prefix of the constant as same.
 * Structure:
 * {route_name_prefix}.{action}
 *
 * Example:
 * 'walletmonitoring.index'
 * 'walletmonitoring.view'
 * 'walletmonitoring.edit'
 */

$menu_modules = Session::get('modules');
$user_permissions = Session::get('user_permission');
$routeModuleAssoc = [];
preg_match('/([a-z]*)@/i', Request::route()->getActionName(), $matches);
$current_route_name_prefix = explode('.', Route::currentRouteName())[0] ?? '';
$controllerName = $matches[1];
//dd($controllerName);exit;
?>

<nav class="page-sidebar custom-page-sidebar" id="sidebar">
    <div class="page-brand bg-white text-center">
        <?php $cache = Cache::get('logo_path'); ?>
        <span class="brand"><a href="{{route('home')}}"><img src="{{Storage::url($cache)}}" alt=""></a></span>
        <span class="brand-mini"><a href="{{route('home')}}">AC</a></span>
    </div>

    <div id="sidebar-collapse">
        <p class="m-0 mr-4 mt-0 mt-sm-4 p-0 d-lg-none text-right"><span class="ti-close qp-side-menu-close-button" onclick="closeQpNav()" id="close-btn"></span></p>
        <ul class="side-menu metismenu">

            <?php
            $access_module = array();

            if (!empty($user_permissions)) {
                foreach ($user_permissions as $row) {

                    if (!in_array($row->module_id, $access_module))
                        $access_module[] = $row->module_id;
                }
            }
            $access_submodule = array();

            if (!empty($user_permissions)) {
                foreach ($user_permissions as $row) {
                    if (!in_array($row->submodule_id, $access_submodule))
                        $access_submodule[] = $row->submodule_id;
                }
            }

            ?>


            <?php

            $route_modules = [];
            if(!empty($menu_modules)){
                $menu_modules = $menu_modules->where('display_name', '!=', 'N/A');
            foreach($menu_modules as $module){
            if(in_array($module['id'], $access_module)){
            $icon = '<i class="sidebar-item-icon fa fa-codepen" aria-hidden="true"></i>';
            if ($module['icon'] != "") {
                $icon = $module['icon'];
            }
            ?>

            <?php
            $submodule_controller_arr = array();
            $submodule_assoc_pages_method = array();
            foreach ($module->submodules as $key => $submodule) {
                //print_r($submodule->pages);exit;
                // $submodule_controller_arr[$submodule['id']]= strtolower(str_replace(" ", "", $module['name'])).".".strtolower(str_replace(" ", "", $submodule['name'])).".".strtolower($submodule['default_method']);
                $submodule_controller_arr[$submodule['id']] = strtolower(str_replace(" ", "", $submodule['name'])) . "." . strtolower($submodule['default_method']);

            }

            ?>
            <?php

            foreach ($module->submodules as $submodule) {

                if (in_array($submodule['id'], $access_submodule)) {
                    $sub_icon = '<i class="fa fa-circle-o"></i>';
                    if ($submodule['icon'] != "") {
                        $sub_icon = $submodule['icon'];
                    }


                    $routeModuleAssoc[$submodule['controller_name']] = $module['id'];
                }

                $route_name_prefix = explode('.', $submodule_controller_arr[$submodule['id']])[0] ?? '';
                if (!empty($route_name_prefix)) {
                    $route_modules[$route_name_prefix] = $module['id'];
                }


            }
            //dd($routeModuleAssoc);exit;
                $is_sub_module_active = false;
                if (isset($route_modules[$current_route_name_prefix]) && $route_modules[$current_route_name_prefix] == $module['id']) {
                    $is_sub_module_active = true;
                }
            ?>


            <li class="{{ $is_sub_module_active ? 'active' : '' }}">

                <?php
                $doc = new DOMDocument();
                $doc->loadHTML($icon);
                $xpath = new DOMXPath($doc);
                $src = $xpath->evaluate("string(//img/@src)");

                if (!empty($src)){
                    $icon = '<img src='.url($src).' class="menu_icon-color">';
                }
                ?>

                <a class="main-menu" href="javascript:;" style="text-transform: uppercase !important;">

                    {!! $icon !!}
                    <span class="nav-label">{{__(strtoupper($module['display_name']))}}</span>
                    <i class="fa fa-angle-down arrow "></i></a>

                <ul class="nav-2-level collapse">
                    <?php
                    foreach($module->submodules as $submodule ){
                        $all_transaction_submodule_id = 0;
                        if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Backend\BackendAdmin::class, 'hideAllTransaction'])){
                            $all_transaction_submodule_id = config('constants.IGNORABLE_MODULES_SUBMODULES.IGNORABLE_SUBMODULES.APP_ALL_TRANSACTION_INDEX');
                        }
                    if(\common\integration\Utility\Arr::isAMemberOf($submodule['id'], $access_submodule) && $submodule['id'] != $all_transaction_submodule_id){
                    $sub_icon = '<i class="fa fa-circle-o"></i>';
                    if ($submodule['icon'] != "") {
                        $sub_icon = $submodule['icon'];
                    }

                    $route = $submodule_controller_arr[$submodule['id']];


                    /*
                    if($submodule['id'] == 2050) {
                        $route = 'users.index';
                    } elseif($submodule['id'] == 2051) {
                        $route = 'roles.index';
                    } elseif($submodule['id'] == 2052) {
                        $route = 'usergroups.index';
                    } elseif($submodule['id'] == 2053) {
                        $route = 'usergroup.role.index';
                    } elseif($submodule['id'] == 2054) {
                        $route = 'rolepages.index';
                    }elseif($submodule['id'] == 2072) {
                        $route = 'user.usergroup.index';
                    }elseif($submodule['id'] == 2070) {
                        $route = 'settings.edit';
                    }else if($submodule['id'] == 2001){
                        $route = 'companies.index';
                    }else if($submodule['id'] == 2020){
                        $route = 'modules.index';
                    }else if($submodule['id'] == 2021){
                        $route = 'submodules.index';
                    }else if($submodule['id'] == 2022){
                        $route = 'pages.index';
                    }
                    else if($submodule['id'] == 2103){
                        $route = 'withdrawals.index';
                    }
                    else if($submodule['id'] == 2104){
                        $route = 'deposits.index';
                    }

                    */

                    ?>
                    <li>
                            <a class="{{($submodule['controller_name'] == $controllerName) ? 'active':''}}"
                               href="{{Route::has($route) ? route($route):"#"}}">{{__($submodule['display_name'])}}</a>
                    </li>
                    <?php }
                    }
                    ?>
                </ul>

            </li>
            <?php
            }
            }
            }

            ?>
            @if(Auth::user()->id == 0)
                <li class="{{(Route::is('modules.index') ? 'active' : '')}}">
                    <a href="javascript:;"><i class="sidebar-item-icon ti-align-justify"></i>
                        <span class="nav-label">{{__('Master Data')}}</span><i class="fa fa-angle-left arrow"></i></a>
                    <ul class="nav-2-level collapse">
                        <li>
                            <a class="{{(Route::is('modules.index') ? 'active' : '')}}"
                               href="{{route('modules.index')}}">{{__('Module Management')}}</a>
                        </li>
                        <li>
                            <a class="{{(Route::is('submodules.index') ? 'active' : '')}}"
                               href="{{route('submodules.index')}}">{{__('Sub Module Management')}}</a>
                        </li>
                        <li>
                            <a class="{{(Route::is('pages.index') ? 'active' : '')}}"
                               href="{{route('pages.index')}}">{{__('Page Management')}}</a>
                        </li>

                    </ul>
                </li>

                {{--<li class="heading">{{__('Access Control')}}</li>--}}
                {{--<li class="{{(Route::is('users.index') ? 'active' : '')}}">--}}
                {{--<a href="javascript:;"><i class="sidebar-item-icon ti-paint-roller"></i>--}}
                {{--<span class="nav-label">{{__('Access Control')}}</span><i class="fa fa-angle-left arrow"></i></a>--}}
                {{--<ul class="nav-2-level collapse">--}}
                {{--<li>--}}
                {{--<a class="{{(Route::is('users.index') ? 'active' : '')}}" href="{{route('users.index')}}"> {{__('User Management')}}</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                {{--<a class="{{(Route::is('roles.index') ? 'active' : '')}}" href="{{route('roles.index')}}">{{__('Role Management')}}</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                {{--<a class="{{(Route::is('usergroups.index') ? 'active' : '')}}" href="{{route('usergroups.index')}}">{{__('User Group Management')}}</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                {{--<a class="{{(Route::is('rolepages.index') ? 'active' : '')}}" href="{{route('rolepages.index')}}">{{__('Role & Page Association')}}</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                {{--<a class="{{(Route::is('usergroup.role.index') ? 'active' : '')}}" href="{{route('usergroup.role.index')}}">{{__('User Group & Role Association')}}</a>--}}
                {{--</li>--}}
                {{--<li>--}}
                {{--<a class="{{(Route::is('user.usergroup.index') ? 'active' : '')}}" href="{{route('user.usergroup.index')}}">{{__('User & User Group Association')}}</a>--}}
                {{--</li>--}}
        </ul>
        </li>
        @endif
        {{----}}
        </ul>

        @include('partials.sidebar_footer_text', ['css_class' => 'position-relative'])

    <!--        <div class="sidebar-footer">
            {{--<a href="javascript:;"><i class="ti-announcement"></i></a>--}}
    {{--<a href="calendar.html"><i class="ti-calendar"></i></a>--}}
    {{--<a href="javascript:;"><i class="ti-comments"></i></a>--}}
    {{--<a href="login.html"><i class="ti-power-off"></i></a>--}}
        </div>-->
    </div>
</nav>


