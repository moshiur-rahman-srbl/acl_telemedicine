{{--{{dd($settings)}}--}}

@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subModuleTitle'])}}
                </div>
            </div>
            <div class="ibox-body">
                @php
                    $storagePath = \common\integration\Utility\File::getStoragePath();
                @endphp

                <!-- /.box-header -->
                <!-- form start -->
                <form role="form" action="{{route($dynamic_route)}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ibox">
                                <div class="ibox-body">
                                    <div class="form-group">
                                        <label for="name">{{__('Page Title')}}</label>
                                        <input type="text" class="form-control" name="title" id="title"
                                               placeholder="{{__('Page Title')}}"
                                               value="{{isset($settings->title) ? $settings->title : ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label class="btn btn-primary file-input mr-2">
                                            <span class="btn-icon"><i class="la la-cloud-upload"></i>{{__('Logo Path')}}</span>
                                            <input type="file" name="logo_path">
                                        </label>
                                        @if(!empty($settings->logo_path) && is_file($storagePath.$settings->logo_path))
                                            <img src="{{secure_file_link($settings->logo_path, 'public')}}" alt="" width="100"
                                                 height="auto">
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="email">{{__('Admin Name')}}</label>
                                        <input type="text" class="form-control" name="admin_name" id="admin_name"
                                               placeholder="{{__('Admin Name')}}"
                                               value="{{isset($settings->admin_name) ? $settings->admin_name : ''}}">
                                    </div>
                                    <div class="form-group">
                                        <label for="email">{{__('Admin Phone')}}</label>
                                        <input name="admin_phone_"
                                            value="{{$settings->admin_phone ?? ''}}" type="text"
                                            id="phone" class="form-control number-only telNoSelector"/>
                                        <input type="hidden" name="admin_phone"
                                            value="{{$settings->admin_phone ?? ''}}"
                                            id="phonecode" class="telNoValue"/>
                                    </div>
                                    <div class="form-group">
                                        <label for="full_company_name">{{__('Full Company Name')}}</label>
                                        <input name="full_company_name"
                                               value="{{$settings_extra_data['full_company_name'] ?? ''}}" type="text"
                                               id="full_company_name" class="form-control" placeholder="{{__('Enter Full Company Name')}}" required/>
                                    </div>
                                    <div class="form-group">
                                        <label for="btrns_company_code">{{__('BTRANS Company Code')}}</label>
                                        <input name="btrns_company_code"
                                               value="{{$settings_extra_data['btrns_company_code'] ?? ''}}" type="text"
                                               id="btrns_company_code" class="form-control" placeholder="{{__('Enter BTRANS Company Code')}}" required/>
                                    </div>
                                    <div class="form-group">
                                        <label for="tax_number">{{__('TAX Number')}}</label>
                                        <input name="tax_number"
                                               value="{{$settings_extra_data['tax_number'] ?? ''}}" type="text"
                                               id="tax_number" class="form-control" placeholder="{{__('Enter TAX Number')}}" required/>
                                    </div>
                                    <div class="form-group">
                                        <label for="tax_office">{{__('TAX Office')}}</label>
                                        <input name="tax_office"
                                               value="{{$settings_extra_data['tax_office'] ?? ''}}" type="text"
                                               id="tax_office" class="form-control" placeholder="{{__('Enter TAX Office')}}" required/>
                                    </div>
                                    @if(\common\integration\BrandConfiguration::allowLogChecker())
                                    <div class="form-group" style="margin-top: 25px;">
                                        <label class="checkbox checkbox-ebony">
                                            <input name="is_application_online" value="1" type="checkbox" class="bulk-action" {{$is_application_online == 1 ? 'checked':''}}>
                                            <span class="input-span"></span>
                                            {{__('Application Online')}}
                                        </label>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="ibox">
                                <div class="ibox-body">

                                    <div class="form-group">
                                        <label for="email">{{__('Company Address')}}</label>
                                        <input type="text" class="form-control" name="company_address"
                                               id="company_address" placeholder="{{__('Company Address')}}"
                                               value="{{isset($settings->company_address) ? $settings->company_address : ''}}">
                                    </div>
                                    {{--<div class="form-group">--}}
                                        {{--<label class="btn btn-primary file-input mr-2">--}}
                                            {{--<span class="btn-icon"><i class="la la-cloud-upload"></i>{{__('Favicon Path')}}</span>--}}
                                            {{--<input type="file" name="favicon_path">--}}
                                        {{--</label>--}}
                                        {{--@if(!empty($settings->favicon_path) && is_file($storagePath.$settings->favicon_path))--}}
                                            {{--<img src="{{Storage::url($settings->favicon_path)}}" alt="" width="100"--}}
                                                 {{--height="auto">--}}
                                        {{--@endif--}}
                                    {{--</div>--}}
                                    <div class="form-group">
                                        <label for="email">{{__('Footer')}}</label>
                                        <input type="text" class="form-control" name="footer" id="footer"
                                               placeholder="{{__('Footer')}}"
                                               value="{{isset($settings->footer) ? $settings->footer : ''}}">
                                    </div>
                                   <div class="form-group">
                                        <label for="footer_tr">{{__('Footer TR')}}</label>
                                        <input type="text" class="form-control" name="footer_tr" id="footer_tr"
                                               placeholder="{{__('Footer TR')}}"
                                               value="{{isset($settings->footer_tr) ? $settings->footer_tr : ''}}">
                                    </div>

                                    <div class="form-group">
                                        <label for="email">{{__('Merchant Integration')}}</label>
                                        @if(Storage::exists('merchant/integration/integration.zip'))
                                            <a class="text-primary" href="{{secure_file_link('merchant/integration/integration.zip', 'public')}}">({{__('Download Link')}})</a>
                                        @endif
                                        {{--<input type="file" class="form-control" name="integration" id="integration">--}}
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="integration" id="validatedCustomFile" title="">
                                            <label class="custom-file-label" for="validatedCustomFile">{{__('Choose File...')}}</label>
                                        </div>
                                    </div>
                                    @if(\common\integration\BrandConfiguration::isAllowedSiteSettingHelpDocument())
                                        <div class="form-group">
                                            <label for="help-doc">{{__('Help Document')}}</label>
                                            @if(Storage::exists(!empty($settings_extra_data['help_doc']) ? $settings_extra_data['help_doc'] : "null.zip"))
                                                <a class="text-primary" href="{{secure_file_link($settings_extra_data['help_doc'], 'public')}}">({{__('Download Link')}})</a>
                                            @endif
                                            <div class="custom-file-doc">
                                                <input type="file" class="custom-file-input" name="help_doc" id="validatedCustomDocFile" title="">
                                                <label class="custom-file-label" for="validatedCustomDocFile">{{__('Choose File...')}}</label>
                                            </div>
                                        </div>
                                    @endif
                                    @if(\common\integration\BrandConfiguration::hideSiteSettingsAdvertisementCodeSection())
                                    <div class="form-group" style="display: {{$advertisement_code_show == false ? 'none' : 'show'}};">
                                        <label for="email">{{__('Advertisement Code')}}</label>

                                        @if($first_column)
                                            <input type="hidden" name="company_id" value="{{\App\Models\Setting::ADMIN_COMPANY_ID}}" />
                                        @endif

                                        <textarea class="form-control" name="advertisment"
                                                  placeholder="{{__('Advertisement Code')}}">{{isset($settings->advertisment) ? $settings->advertisment : ''}}</textarea>
                                    </div>

                                    @endif


                                </div>
                            </div>


                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <!-- /.box-body -->

                            <div class="box-footer">
                                @if($isEdit)
                                    <input type="hidden" name="id" value="{{isset($settings->id) ? $settings->id :""}}">
                                    <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                                @endif
                                {{--<a href="{{route('/')}}" class="btn btn-primary">{{__('Back')}}</a>--}}
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>
    </div>
@endsection
@push('css')
    @include('partials.css_blade.select2')
    @include('partials.css_blade.datepicker')
    @include('partials.css_blade.datetimepicker')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.clockpicker')
    @include('partials.css_blade.clockpicker')
    @include('partials.css_blade.ionRangeSlider')
    @include('partials.css_blade.bootstrap-tagsinput')
    @include('partials.css_blade.bootstrap-touchspin')
    @include('partials.css_blade.multi-select')
    @include('partials.css_blade.intlTelInput_new')
    <style>
        .custom-file-label::after {
                content: "{{ __('Browse') }}" !important;
        }
    </style>
@endpush
@push('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.datepicker')
    @include('partials.js_blade.datetimepicker')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.clockpicker')
    @include('partials.js_blade.knob')
    @include('partials.js_blade.ionRangeSlider')
    @include('partials.js_blade.bootstrap-tagsinput')
    @include('partials.js_blade.bootstrapMaxLength')
    @include('partials.js_blade.bootstrap-touchspin')
    @include('partials.js_blade.multi-select')
    @include('partials.js_blade.intlTelInput_new')
    <script src="{{asset('adminca')}}/assets/js/scripts/form-plugins.js"></script>
    <script>
        $('document').ready(function(){
            $("#validatedCustomFile").change(function() {
                filename = this.files[0].name;
                if(filename){
                    $('.custom-file').find('label').text(filename);
                }else{
                    $('.custom-file').find('label').text("{{__('Browse')}}");
                }
            });

            $("#validatedCustomDocFile").change(function() {
                filename = this.files[0].name;
                if(filename){
                    $('.custom-file-doc').find('label').text(filename);
                }else{
                    $('.custom-file-doc').find('label').text("{{__('Browse')}}");
                }
            });
        });
    </script>
@endpush
