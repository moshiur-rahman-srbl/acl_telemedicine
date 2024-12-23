@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
{{--        @if ($errors->any())--}}
{{--            --}}{{--{{dd($errors->all())}}--}}
{{--            @foreach ($errors->all() as $error)--}}
{{--                <div class="alert alert-danger alert-dismissible fade show">--}}
{{--                    <button class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>--}}
{{--                    {{ $error }}--}}
{{--                </div>--}}
{{--            @endforeach--}}
{{--        @endif--}}
        @php
            $allow_security_image = \common\integration\BrandConfiguration::call([common\integration\Brand\Configuration\Backend\BackendAdmin::class,"isAllowToChangeAdminPanelSecurityImage"]);
            $div_col = $allow_security_image ? 4 : 6;
        @endphp
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}"
                                                    class="ml-3 btn btn-sm btn-primary pull-right {{(isset($logger) && !empty($logger)) ? 'd-none' : ''}}"><i
                            class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <div class="box-header with-border">
                    <div class="container">
                        <ul class="nav nav-pills m-auto" id="pills-tab" role="tablist">
                            <li class="nav-item {{"col-sm-".$div_col}}">
                                <a class="nav-link custom-style active" id="pills-change-password-tab" data-bs-toggle="pill"
                                   href="#pills-change-password" role="tab" aria-controls="pills-change-password"
                                   aria-selected="true">{{ __('Change User Password') }}</a>
                            </li>
                            @if(\common\integration\BrandConfiguration::allowSecrectQuestionOnAdminPanel())
                                <li class="nav-item {{"col-sm-".$div_col}} pl-2">
                                    <a class="nav-link custom-style" id="pills-change-secrect-question-tab" data-bs-toggle="pill"
                                       href="#pills-change-secrect-question"
                                       role="tab" aria-controls="pills-change-secrect-question"
                                       aria-selected="false">{{ __('Change Secrect Question') }}</a>
                                </li>
                            @endif
                            @if($allow_security_image)
                                <li class="nav-item {{"col-sm-".$div_col}} pl-2">
                                    <a class="nav-link custom-style" id="pills-change-security-image-tab" data-bs-toggle="pill"
                                       href="#pills-change-security-image"
                                       role="tab" aria-controls="pills-change-security-image"
                                       aria-selected="false">{{ __('Change Security Image') }}</a>
                                </li>
                            @endif
                        </ul>

                        <br>

                        <div class="tab-content custom-style" id="pills-tabContent">
                            <div class="tab-pane fade show active" id="pills-change-password" role="tabpanel"
                                 aria-labelledby="pills-change-password-tab">
                                <form role="form"
                                      action="{{route(Config::get('constants.defines.APP_USERS_CHANGEPASSWORD'))}}"
                                      method="post" enctype="multipart/form-data" id="edit_form">
                                    @csrf
                                    @if((isset($logger) && empty($logger)))
                                        @method('PUT')
                                    @endif
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <input type="hidden" name="action" value="1">
                                                <label for="old_password">{{__('Old Password')}}</label>
                                                <input type="password" class="form-control" name="user_password"
                                                       id="user_password" placeholder="{{__('Old Password')}}"
                                                       autocomplete="new-password">
                                                {{--<!--                                        @if($errors->has('user_password'))--}}
                                                <label
                                                    class="help-block error">{{$errors->first('user_password')}}</label>
                                                {{--@endif-->--}}
                                            </div>
                                            <div class="form-group">
                                                <?php
                                                $err_label = (config('constants.PASSWORD_SECURITY_TYPE') == \App\Models\Profile::ALPHANUMERIC_PASSWORD) ? __('The password must be 8 characters long, must contain a mix of upper/lowercase letters, numbers, and special characters') : __('Password must be 6 digit number only');
                                                ?>
                                                <label for="password">{{__('New Password')}}<br><small
                                                        class="text-primary">[{{$err_label}}]</small></label>
                                                    <input type="password" class="form-control mt-2"
                                                           name="password" id="password"
                                                           placeholder="{{__('New Password')}}"
                                                           autocomplete="new-password">
                                                {{--@if($errors->has('password'))--}}
                                                {{--<label class="help-block error">{{$errors->first('password')}}</label>--}}
                                                {{--@endif--}}
                                            </div>
                                            <div class="form-group">
                                                <label
                                                    for="password_confirmation">{{__('Confirm Password')}}</label>
                                                <input type="password" class="form-control"
                                                       name="password_confirmation" id="password_confirmation"
                                                       placeholder="{{__('Confirm Password')}}"
                                                       autocomplete="new-password">
                                                {{--@if($errors->has('password_confirmation'))--}}
                                                {{--<label class="help-block error">{{$errors->first('password_confirmation')}}</label>--}}
                                                {{--@endif--}}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="box-footer">
                                                @if(empty($disabled))
                                                    <button type="submit"
                                                            class="btn btn-primary">{{__('Update')}}</button>
                                                @endif
                                                <a href="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}"
                                                   class="btn btn-primary">{{__('Cancel')}}</a>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane fade show" id="pills-change-secrect-question" role="tabpanel"
                                 aria-labelledby="pills-change-secrect-question-tab">
                                    <form action="{{route(Config::get('constants.defines.APP_SECRET_QUESTION'))}}"
                                          method="post">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label class="text-info">{{__("Current Password")}}</label>
                                                    <div class="input-group-icon input-group-icon-right">
                                                        <input required name="current_password"
                                                               class="form-control {{ (config('constants.PASSWORD_SECURITY_TYPE') == \App\Models\Profile::SIX_DIGITS_PASSWORD) && (\common\integration\BrandConfiguration::allowSixDigitOnLoginPage()) ? 'number-only digit-len' : ''}}"
                                                               type="password"
                                                               placeholder="{{__("Current Password")}}">
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="text-info">{{__("Secret Question")}}</label>
                                                    <div class="input-group-icon input-group-icon-right">
                                                        <select class="form-control" name="secrect_question_id">
                                                            <option value="">{{ __('Please select') }}</option>
                                                            @foreach (\common\integration\BrandConfiguration::QUESTION_LIST() as $key => $val)
                                                                <option
                                                                    value="{{ $key }}" {{ isset($user) && $user->question_one == $key ? 'selected' :  ''  }}>{{ __($val) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="form-group">
                                                    <label class="text-info">{{__("Answer")}}</label>
                                                    <div
                                                        class="input-group-icon input-group-icon-right">
                                                        <input required name="answer"
                                                               class="form-control rounded" type="text"
                                                               placeholder="{{__("Answer")}}"
                                                               value="{{ isset($user->answer_one) ? $user->answer_one: ''  }}">
                                                    </div>
                                                </div>
                                                <div class="form-group float-right">
                                                    <button id="submitBtn" type="submit"
                                                            class="btn btn-primary">{{__("Update")}}</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                            </div>

                            <div class="tab-pane fade show" id="pills-change-security-image" role="tabpanel"
                                 aria-labelledby="pills-change-security-image-tab">
                                <form action="{{route(Config::get('constants.defines.APP_SECRET_QUESTION'))}}" method="POST" id="submit_form">
                                    @csrf
                                    <input type="hidden" name="type" value="security-image-question-answer-update">
                                    <div class="modal-body">
                                        <div class="col-sm-12 col-md-6 col-lg-6">

                                            @if(\common\integration\BrandConfiguration::allowSecurityImage())
                                                @include('security_images.security', [
                                                    'selected_security_image'=> auth()->user()->security_image_id ?? '',
                                                    'security_images' => (new \App\Models\SecurityImage())->getSecurityImagesWithBrandCodeAndStatus(\App\Models\SecurityImage::STATUS_ACTIVE)
                                                ])
                                            @endif

                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" id="submit_button" class="btn btn-primary">{{ __('Save changes') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css')
    @include('partials.css_blade.select2')
    <style>

        a.nav-link.custom-style {
            border-style: solid;
            border-radius: 35px;
            color: darkturquoise;
            text-align: center;
            background: papayawhip;
            background: papayawhip;
            font-style: inherit;
            font-size: large;
        }

        div#pills-tabContent {
            background: white;
            padding: 12px;
            border-style: solid;
            border-color: white;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/16.0.4/css/intlTelInput.css"
          integrity="sha256-rTKxJIIHupH7lFo30458ner8uoSSRYciA0gttCkw1JE=" crossorigin="anonymous"/>
@endpush
@push('scripts')
    <script src="{{asset('adminca')}}/assets/js/scripts/form-plugins.js"></script>


    @include('partials.phonecodejs')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.select2')
    <script>
        $('#edit_form').on('submit', function() {
            $('.select_check').prop('disabled', false);
        });

    </script>
@endpush
