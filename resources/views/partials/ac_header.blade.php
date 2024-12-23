<header class="header header-custom-background header-custom-design" id="responsive-header">
    @php
        $storagePath = \common\integration\Utility\File::getStoragePath();
        $cache = Cache::get('logo_path');

        // Localization flag
        $language_flag_img = Config::get('app.locale') == "en" ?
        "adminca/assets/img/flags/uk.png" :
        "adminca/assets/img/flags/Turkey.png";

        // User settings
        $display_name = \common\integration\GlobalFunction::nameCaseConversion(Auth::user()->name);
        if(!empty(Auth::user()->img_path) && is_file($storagePath . Auth::user()->img_path)) {
            $avatar = Storage::url(Auth::user()->img_path);
        } else {
            if(Auth::user()->gender == 1) {
                $avatar = url('/avatar.png');
            } else {
                $avatar = url('/female_default_avatar.png');
            }
        }

        // getting unread notifications
        $unread_notifications = 10; // new query
    @endphp
    <div class="page-brand bg-white text-center">

        {{-- Brand --}}
        <span class="brand">
            <a href="{{ route('home') }}">
                <img src="{{ Storage::url($cache) }}" alt="{{ config('brand.name') }} Logo">
            </a>
        </span>

        {{-- Brand END --}}

        <span class="brand-mini">AC</span>
    </div>
    <div class="flexbox flex-1">
        <!-- START TOP-LEFT TOOLBAR -->
        <ul class="nav navbar-toolbar">

            {{-- Side Navbar Toggler --}}
            <li>
                <a class="nav-link sidebar-toggler js-sidebar-toggler"
                   href="javascript:;" onclick="openQpNav()">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </a>
            </li>
            {{-- Side Navbar Toggler END --}}

        </ul>
        <!-- END TOP-LEFT TOOLBAR -->

        <!-- START TOP-RIGHT TOOLBAR -->
        <ul class="nav navbar-toolbar">

            {{-- Language Toggler --}}
            <li class="timeout-toggler">
                <a class="dropdown-toggle"
                   data-bs-toggle="dropdown">
                    <span style="padding-right: 10px;">
                        @if(is_file($language_flag_img))
                            <img src="{{ asset($language_flag_img) }}">
                        @endif
                    </span><i class="ti-more-alt"></i></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="{{ route('lang','en') }}" id="en">
                        <img src="{{ asset('adminca/assets/img/flags/uk.png') }}">&nbsp;&nbsp; English</a>
                    @if(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.MP'))
                    <a class="dropdown-item" href="{{ route('lang','lt') }}" id="lt">
                            <img src="{{ asset('adminca/assets/img/flags/Lithuania.png') }}">&nbsp;&nbsp; Lithuania</a>
                    @else
                    <a class="dropdown-item" href="{{ route('lang','tr') }}" id="tr">
                        <img src="{{ asset('adminca/assets/img/flags/Turkey.png') }}">&nbsp;&nbsp; Türkçe</a>
                    @endif
                </div>
            </li>
            {{-- Language Toggler END --}}

            {{-- Notifications Toolbar --}}
            <li class="dropdown dropdown-inbox">
                <a class="nav-link dropdown-toggle toolbar-icon" id="notification_icon"  data-bs-toggle="dropdown" href="javascript:;" data-panel_id = "{{\App\Models\Profile::ADMIN}}">
                    <i class="ti-bell rel"></i>
                    <span class="envelope-badge" id="notification_number">{{ $unread_notifications }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right dropdown-menu-media">
                    <div class="dropdown-arrow"></div>
                    <div class="dropdown-header text-center">
                        <div>
                            <span class="text-muted notifications-count">
                                <strong>{{ ($unread_notifications) ? $unread_notifications . ' ' . __("New Notifications") : __("No New Notifications") }}</strong>
                            </span>
                        </div>
                    </div>
                    @if($unread_notifications > 0)
                        <div class="notifications-container">
                            <div class="p-3">
                                <div class="media-list media-list-divider scroller"
                                     data-height="350px"
                                     data-color="#71808f" id="notification_scroller">
                                    <div class="notification-pagination" >
                                   {{--     @include('partials.notificationList', $unread_notifications)--}}
                                    </div>
                                </div>
                            </div>
                            {{-- Read All Notifications --}}
                            <div class="dropdown-divider m-0"></div>
                            <div class="d-flex justify-content-between p-3">
                                <a href="" class="new-blue">{{ __("Show All Notification") }}</a>
                                <a href="" class="new-blue">{{ __("Read All") }}</a>
                            </div>
                            {{-- Read All Notifications END --}}
                        </div>
                    @endif
                </div>
            </li>
            {{-- Notifications Toolbar END --}}

            {{-- User Toolbar --}}
            <li class="dropdown dropdown-user">
                <a class="nav-link dropdown-toggle link"
                   data-bs-toggle="dropdown">
                    <span>{{ $display_name }}</span>
                    @if(is_file($avatar))
                        <img src="{{ $avatar }}" alt="image"/>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-arrow dropdown-menu-right admin-dropdown-menu">
                    <div class="dropdown-arrow"></div>
                    <div class="dropdown-header">
                        <div class="admin-avatar">
                        </div>
                        <div>
                            <h5 class="font-strong text-white">{{$display_name}}</h5>
                            <span class="brand">
                                @if(is_file(Storage::url($cache)))
                                    <img src="{{Storage::url($cache)}}" alt="">
                                @endif
                            </span>
                        </div>
                    </div>
                    <div class="admin-menu-features">
                        @if(Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_USERS_EDIT')))
                            <a class="admin-features-item"
                               href="{{ route(Config::get('constants.defines.APP_USERS_EDIT'), ['id'=>auth()->user()->id,'action'=>\App\User::USER_UPDATE_ACTION]) }}">
                                @if(!empty($avatar))
                                    <img src="{{ $avatar }}" alt="image" width="auto" height="46"/>
                                @endif
                                <span class="d-block pt-2 text-dark">{{__('Profile')}}</span>
                            </a>
                        @endif
                        <a class="admin-features-item"
                           href="{{route(Config::get('constants.defines.APP_USERS_CHANGEPASSWORD'))}}">
                            <i class="fa fa-key text-danger" aria-hidden="true"></i><span
                                class="d-block pt-2 text-dark">{{__("Change Password")}}
                            @if(\common\integration\BrandConfiguration::allowSecrectQuestionOnAdminPanel())
                                <br>
                                {{__("& Secret Question")}}
                            @endif
                            </span>
                        </a>
                        <a class="admin-features-item border-right-0" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                                class="ti-lock text-danger"></i>
                            <span class="d-block text-danger">{{__('Logout')}}</span>
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>
                </div>
            </li>
            {{-- User Toolbar END --}}
            <li>
            </li>
        </ul>
        <!-- END TOP-RIGHT TOOLBAR-->
    </div>
</header>

@push('scripts')

    @include('js_blades.notification.notification_js')

    @if(\common\integration\BrandConfiguration::call
([\common\integration\Brand\Configuration\Backend\BackendAdmin::class, 'allowNewTabOpeningLogoutOnAdminPanel']))

        @include('js_blades.tab_opening.window_closing_js')

    @endif

@endpush
