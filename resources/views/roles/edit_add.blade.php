@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_ROLES_INDEX'))}}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form role="form" action="{{route($dynamic_route)}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{$errors->has('title') ? 'has-error':''}}">
                                    <label for="id">{{__('Title')}}</label>
                                    <input type="text" class="form-control" name="title" id="title" placeholder="{{__('Title')}}" value="" required/>
                                    @if($errors->has('title'))
                                        <label class="help-block error">{{$errors->first('title')}}</label>
                                    @endif
                                </div>
                            </div>

                            @if(\common\integration\BrandConfiguration::call
                            ([\common\integration\Brand\Configuration\Backend\BackendAdmin::class, 'allowAccessRoleTurkishVersion']))

                                <div class="col-md-6">
                                    <div class="form-group {{$errors->has('title_tr') ? 'has-error':''}}">
                                        <label for="id">{{__('Title TR')}}</label>
                                        <input type="text" class="form-control" name="title_tr" id="title_tr" placeholder="{{__('Title Tr')}}" value="" required/>
                                        @if($errors->has('title'))
                                            <label class="help-block error">{{$errors->first('title_tr')}}</label>
                                        @endif
                                    </div>
                                </div>

                            @endif

                            <div class="col-md-6 d-none">
                                <div class="form-group mt-4">
                                    <label class="checkbox checkbox-primary mt-2">
                                        <input type="checkbox" id="is_allow_merchant_auth_email" name="is_allow_merchant_auth_email" value="1">
                                        <span class="input-span"></span>{{__("Allow to see merchant's authorization email")}}</label>
                                </div>
                            </div>
                            <div class="col-md-12 mt-4">
                                <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                                <a href="{{route(Config::get('constants.defines.APP_ROLES_INDEX'))}}" class="btn btn-primary">{{__('Back')}}</a>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.box -->
            </div>
        </div>
@endsection
@push('css')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.select2')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.datepicker')
    @include('partials.css_blade.datetimepicker')
    @include('partials.css_blade.clockpicker')
    @include('partials.css_blade.ionRangeSlider')
    @include('partials.css_blade.bootstrap-tagsinput')
    @include('partials.css_blade.bootstrap-touchspin')
    @include('partials.css_blade.multi-select')
    @include('partials.css_blade.intlTelInput')
@endpush
@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.datepicker')
    @include('partials.js_blade.datetimepicker')
    @include('partials.js_blade.clockpicker')
    @include('partials.js_blade.knob')
    @include('partials.js_blade.ionRangeSlider')
    @include('partials.js_blade.bootstrap-tagsinput')
    @include('partials.js_blade.bootstrapMaxLength')
    @include('partials.js_blade.bootstrap-touchspin')
    @include('partials.js_blade.multi-select')
    <script src="{{asset('adminca')}}/assets/js/scripts/form-plugins.js"></script>
    <script>
        $('.subm').hide();
        $('#module_id').bind('change keyup', function() {
            var val = $(this).val();
            $('.subm').hide();
            $('.m' + val).show();
        });
        // $('#available_to_company').bind('change', function() {
        //     if($(this).prop('checked') == true) {
        //         $(this).val(1);
        //     } else {
        //         $(this).val(0);
        //     }
        // });
    </script>
@endpush
