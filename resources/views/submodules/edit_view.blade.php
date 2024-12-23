{{--{{dd($submodules)}}--}}
@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_SUBMODULES_INDEX'))}}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <!-- /.box-header -->
                <!-- form start -->
                <form role="form" action="{{route($dynamic_route, $submodules->id)}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" value="{{isset($submodules->id) ? $submodules->id : ''}}">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="box-body">
                                <div class="form-group {{$errors->has('submodule_id') ? 'has-error':''}}">
                                    <label for="name">{{__('Sub Module Id')}}</label>
                                    <input type="text" class="form-control" name="submodule_id" id="name" placeholder="{{__('Sub Module Id')}}" value="{{isset($submodules->id) ? $submodules->id : ''}}" required {{$isEdit ? '':'disabled'}}/>
                                    @if($errors->has('submodule_id'))
                                        <label class="help-block error">{{$errors->first('submodule_id')}}</label>
                                    @endif
                                </div>
                                {{--<div class="form-group {{$errors->has('module_name') ? 'has-error':''}}">--}}
                                    {{--<label for="email">{{__('Module Name')}}</label>--}}
                                    {{--<input type="text" class="form-control" name="module_name" id="email" placeholder="{{__('Module Name')}}" value="{{isset($submodules->modules['name']) ? $submodules->modules['name'] : ''}}" required {{$isEdit ? '':'disabled'}}/>--}}
                                    {{--@if($errors->has('module_name'))--}}
                                        {{--<label class="help-block error">{{$errors->first('module_name')}}</label>--}}
                                    {{--@endif--}}
                                {{--</div>--}}
                                <div class="form-group {{$errors->has('module_id') ? 'has-error':''}}">
                                    <label for="module_id">{{__('Module')}}</label>
                                    <select class="form-control" name="module_id" id="module_id" value="{{$submodules->module_id}}" {{$isEdit ? 'required' : 'disabled'}}>
                                        <option value="" selected disabled>{{__('Please select')}}</option>
                                        @foreach($modules as $module)
                                            <option value="{{$module->id}}" {{($module->id == $submodules->module_id) ? 'selected' : ''}}>{{__($module->name)}}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('module_id'))
                                        <label class="help-block error">{{$errors->first('module_id')}}</label>
                                    @endif
                                </div>
                                <div class="form-group {{$errors->has('submodule_name') ? 'has-error':''}}">
                                    <label for="name">{{__('Sub Module Name')}}</label>
                                    <input type="text" class="form-control" name="submodule_name" id="name" placeholder="{{__('Sub Module Name')}}" value="{{isset($submodules->name) ? $submodules->name : ''}}" required {{$isEdit ? '':'disabled'}}/>
                                    @if($errors->has('submodule_name'))
                                        <label class="help-block error">{{$errors->first('submodule_name')}}</label>
                                    @endif
                                </div>
                                <div class="form-group {{$errors->has('submodule_icon') ? 'has-error':''}}">
                                    <label for="password">{{__('Sub Module Icon')}}</label>
                                    <input type="text" class="form-control" name="submodule_icon" id="password" placeholder="{{__('Sub Module Icon')}}" value="{{isset($submodules->icon) ? $submodules->icon : ''}}" required {{$isEdit ? '':'disabled'}}/>
                                    @if($errors->has('submodule_icon'))
                                        <label class="help-block error">{{$errors->first('submodule_icon')}}</label>
                                    @endif
                                </div>
                                <div class="form-group {{$errors->has('sequence') ? 'has-error':''}}">
                                    <label for="sequence">{{__('Sequence')}}</label>
                                    <input type="text" class="form-control" name="sequence" id="password_confirmation" placeholder="{{__('Sequence')}}" value="{{isset($submodules->sequence) ? $submodules->sequence : ''}}" required {{$isEdit ? '':'disabled'}}/>
                                    @if($errors->has('sequence'))
                                        <label class="help-block error">{{$errors->first('sequence')}}</label>
                                    @endif
                                </div>
                                <div class="form-group {{$errors->has('controller_name') ? 'has-error':''}}">
                                    <label for="sequence">{{__('Controller Name')}}</label>
                                    <input type="text" class="form-control" name="controller_name" id="password_confirmation" placeholder="{{__('Controller Name')}}" value="{{isset($submodules->controller_name) ? $submodules->controller_name : ''}}" required {{$isEdit ? '':'disabled'}}/>
                                    @if($errors->has('controller_name'))
                                        <label class="help-block error">{{$errors->first('controller_name')}}</label>
                                    @endif
                                </div>
                                <div class="form-group {{$errors->has('default_method') ? 'has-error':''}}">
                                    <label for="default_method">{{__('Default Method Name')}}</label>
                                    <input type="text" class="form-control" name="default_method" id="password_confirmation" placeholder="{{__('Default Method Name')}}" value="{{isset($submodules->default_method) ? $submodules->default_method : ''}}" required {{$isEdit ? '':'disabled'}}/>
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
                                            <option value="{{$key}}"
                                            @foreach ($submodules->usertype_submodules as $usertype_submodule)
                                                {{($key == $usertype_submodule['user_type_id'])? 'selected':''}}
                                            @endforeach
                                            >{{$value}}</option>

                                        @endforeach
                                    </select>
                                    @if($errors->has('user_type_id'))
                                    <label class="help-block error">{{$errors->first('user_type_id')}}</label>
                                    @endif
                                </div>
                            </div>
                            <!-- /.box-body -->

                            <div class="box-footer">
                                @if($isEdit)
                                    <input type="hidden" name="subold_id" value="{{$submodules->id}}">
                                    <input type="hidden" name="subold_name" value="{{$submodules->name}}">
                                    <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                                @endif
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