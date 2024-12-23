{{--{{dd($page)}}--}}
@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')
<style>
    #aulist li:not(:first-child), #sulist li:not(:first-child) {
        cursor: pointer;
    }
    #aulist li:hover:not(:first-child), #sulist li:hover:not(:first-child) {
        background-color: #e1eaec;
    }
</style>
@php
    $checkeduser = array();
    if(!empty($userusergroups)) {
        foreach($userusergroups as $userusergroup) {
            $checkeduser[$userusergroup->user_id] = $userusergroup->user_id;
        }
    }
@endphp
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_USERGROUPS_INDEX'))}}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                    <!-- /.box-header -->
                    <!-- form start -->

                    <form role="form" action="{{route($dynamic_route, $usergroup->id)}}" method="post" enctype="multipart/form-data">

                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="box-body">
                                    <div class="form-group {{$errors->has('group_name') ? 'has-error':''}}">
                                        <label for="group_name">{{__('Group Name')}}</label>
                                        <input type="text" class="form-control" name="group_name" id="group_name" placeholder="{{__('Group Name')}}" value="{{$usergroup->group_name}}" {{$isEdit ? 'required' : 'readonly'}}/>
                                        @if($errors->has('group_name'))
                                            <label class="help-block error">{{$errors->first('group_name')}}</label>
                                        @endif
                                    </div>
                                </div>
                                <div class="box-body">
                                    <div class="form-group {{$errors->has('dashboard_url') ? 'has-error':''}}">
                                        <label for="dashboard_url">{{__('Dashboard URL')}}</label>
                                        <input type="text" class="form-control" name="dashboard_url" id="dashboard_url" placeholder="{{__('Dashboard URL')}}" value="{{$usergroup->dashboard_url}}" {{$isEdit ? 'required' : 'readonly'}}/>
                                        @if($errors->has('dashboard_url'))
                                            <label class="help-block error">{{$errors->first('dashboard_url')}}</label>
                                        @endif
                                    </div>
                                </div>
                                <div class="box-body">
                                    <label for="">{{__('Select Users')}}</label>
                                    <div class="row mb-4" id="swap-roles">
                                        <div class="col p-0 rounded border border-top-0 border-right-0 border-left-0 border-primary" style="margin: 0 15px;">
                                            <ul class="list-group list-group-bordered" id="aulist">
                                                <li class="list-group-item active flexbox"><label class="m-0 p-0">{{__('Available Users')}}</label></li>
                                                @if(!empty($users))
                                                    @foreach($users as $user)
                                                        @if(!(in_array($user->id, $checkeduser)))
                                                            <li class="list-group-item flexbox {{$isEdit ? 'notsel' : ''}}" data="{{$user->id}}">{{\common\integration\GlobalFunction::nameCaseConversion($user->name)}}</li>
                                                        @endif
                                                    @endforeach
                                                @else
                                                    <li class="list-group-item flexbox">&nbsp;</li>
                                                @endif
                                            </ul>
                                        </div>
                                        <div class="col p-0 rounded border border-top-0 border-right-0 border-left-0 border-primary" style="margin: 0 15px;">
                                            <ul class="list-group list-group-bordered" id="sulist">
                                                <li class="list-group-item active flexbox"><label class="m-0 p-0">{{__('Selected Users')}}</label></li>
                                                @if(!empty($checkeduser))
                                                    @foreach($users as $user)
                                                        @if(in_array($user->id, $checkeduser))
                                                            <li class="list-group-item flexbox {{$isEdit ? 'seltd' : ''}}" data="{{$user->id}}"><input type="hidden" name="selected_users[]" value="{{$user->id}}" />{{\common\integration\GlobalFunction::nameCaseConversion($user->name)}}</li>
                                                        @endif
                                                    @endforeach
                                                @endif
                                                @if(empty($users))
                                                    <li class="list-group-item flexbox">&nbsp;</li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <!-- /.box-body -->
                                <div class="box-footer">
                                    @if($isEdit)
                                    <input type="hidden" name="id" value="{{$usergroup->id}}">
                                    <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                                    @endif
                                    <a href="{{route(Config::get('constants.defines.APP_USERGROUPS_INDEX'))}}" class="btn btn-primary">{{__('Back')}}</a>
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
        $(document).on('click', '.notsel', function() {
            $('#sulist').append('<li class="list-group-item flexbox seltd" data="'+$(this).attr("data")+'"><input type="hidden" name="selected_users[]" value="'+$(this).attr("data")+'" /> '+$(this).text()+'</li>');
            $(this).remove();
        });

        $(document).on('click', '.seltd', function() {
            $('#aulist').append('<li class="list-group-item flexbox notsel" data="'+$(this).attr("data")+'">'+$(this).text()+'</li>');
            $(this).remove();
        });
    </script>
@endpush
