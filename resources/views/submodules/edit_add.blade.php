@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    {{app()->getLocale()}}
    <div class="page-content fade-in-up">
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subModuleTitle'])}} <a href="{{route(Config::get('constants.defines.APP_SUBMODULES_INDEX'))}}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <!-- /.box-header -->
                <!-- form start -->
                <form role="form" action="{{route(Config::get('constants.defines.APP_SUBMODULES_CREATE'))}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="box-body">
                                <div class="form-group {{$errors->has('submodule_id') ? 'has-error':''}}">
                                    <label for="name">{{__('Sub Module Id')}}</label>
                                    <input type="text" class="form-control" name="submodule_id" id="name" placeholder="{{__('Sub Module Id')}}" value="{{isset($user->name) ? $user->name : ''}}" required/>
                                    @if($errors->has('submodule_id'))
                                        <label class="help-block error">{{$errors->first('submodule_id')}}</label>
                                    @endif
                                </div>
<!--                                --><?php //dd($modules) ?>
                                <div class="form-group {{$errors->has('module_name') ? 'has-error':''}}">
                                    <label for="email">{{__('Module')}}</label>
                                    <select class="form-control" name="module_id" id="module_name">

                                        @if(!empty($modules))
                                            @foreach($modules as $module)
                                                <option value="{{$module->id}}">{{(app()->getLocale() == 'tr') ? ($module->name_tr) : ($module->name)}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    {{--<input type="text" class="form-control" name="module_name" id="email" placeholder="{{__('Module Name')}}" value="{{isset($user->email) ? $user->email : ''}}" required/>--}}
                                    @if($errors->has('module_name'))
                                        <label class="help-block error">{{$errors->first('module_name')}}</label>
                                    @endif
                                </div>
                                <div class="form-group {{$errors->has('submodule_name') ? 'has-error':''}}">
                                    <label for="email">{{__('Sub Module Name')}}</label>
                                    <input type="text" class="form-control" name="submodule_name" id="email" placeholder="{{__('Sub Module Name')}}" value="{{isset($user->email) ? $user->email : ''}}" required/>
                                    @if($errors->has('submodule_name'))
                                        <label class="help-block error">{{$errors->first('submodule_name')}}</label>
                                    @endif
                                </div>
                                <div class="form-group {{$errors->has('submodule_icon') ? 'has-error':''}}">
                                    <label for="password">{{__('Sub Module Icon')}}</label>
                                    <input type="text" class="form-control" name="submodule_icon" id="password" placeholder="{{__('Sub Module Icon')}}" required>
                                    @if($errors->has('submodule_icon'))
                                        <label class="help-block error">{{$errors->first('submodule_icon')}}</label>
                                    @endif
                                </div>
                                <div class="form-group {{$errors->has('sequence') ? 'has-error':''}}">
                                    <label for="sequence">{{__('Sequence')}}</label>
                                    <input type="text" class="form-control" name="sequence" id="password_confirmation" placeholder="{{__('Sequence')}}" required>
                                    @if($errors->has('sequence'))
                                        <label class="help-block error">{{$errors->first('sequence')}}</label>
                                    @endif
                            </div>
                            <div class="form-group {{$errors->has('controller_name') ? 'has-error':''}}">
                                <label for="controller_name">{{__('Controller Name')}}</label>
                                <input type="text" class="form-control" name="controller_name" id="controller_name" placeholder="{{__('Controller Name')}}" required>
                                @if($errors->has('controller_name'))
                                    <label class="help-block error">{{$errors->first('controller_name')}}</label>
                                @endif
                        </div>
                        <div class="form-group {{$errors->has('default_method') ? 'has-error':''}}">
                            <label for="default_method">{{__('Default Method Name')}}</label>
                            <input type="text" class="form-control" name="default_method" id="default_method" placeholder="{{__('Default Method Name')}}" required>
                            @if($errors->has('default_method'))
                                <label class="help-block error">{{$errors->first('default_method')}}</label>
                            @endif
                        </div>
                        <div class="form-group {{$errors->has('user_type') ? 'has-error':''}}">
                            <label for="user_type_id">{{__('User Type')}}</label>
                            <select class="form-control select2_demo_1" multiple="multiple" data-placeholder="{{__('User Type')}}"
                                    style="width: 100%;" name="user_type_id[]" id="user_type_id" required>
                                <option value="" >{{__('Please select')}}</option>
                                @foreach($user_types as $key=>$value)
                                <option value="{{$key}}">{{$value}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('user_type_id'))
                            <label class="help-block error">{{$errors->first('user_type_id')}}</label>
                            @endif
                        </div>
                    </div>
                            <!-- /.box-body -->

                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                                <a href="{{route(Config::get('constants.defines.APP_SUBMODULES_INDEX'))}}" class="btn btn-primary">{{__('Back')}}</a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <!-- /.box -->
        </div>
    </div>
@endsection
@section('js')
    <script src="{{asset('adminca')}}/assets/js/scripts/form-plugins.js"></script>
    {{--<script>
        $('.select2').select2()
    </script>--}}
@endsection