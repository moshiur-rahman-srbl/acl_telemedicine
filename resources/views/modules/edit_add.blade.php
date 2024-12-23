@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_MODULES_INDEX'))}}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                    <!-- /.box-header -->
                    <!-- form start -->
                    <form role="form" action="{{route($dynamic_route)}}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="box-body">
                                    <div class="form-group {{$errors->has('module_id') ? 'has-error':''}}">
                                        <label for="name">{{__('Module Id')}}</label>
                                        <input type="text" class="form-control" name="module_id" id="name" placeholder="{{__('Module Id')}}" value="{{isset($user->name) ? $user->name : ''}}" required/>
                                        @if($errors->has('module_id'))
                                        <label class="help-block error">{{$errors->first('module_id')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('module_name') ? 'has-error':''}}">
                                        <label for="email">{{__('Module Name')}}</label>
                                        <input type="text" class="form-control" name="module_name" id="email" placeholder="{{__('Module Name')}}" value="{{isset($user->email) ? $user->email : ''}}" required/>
                                        @if($errors->has('module_name'))
                                            <label class="help-block error">{{$errors->first('module_name')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('module_icon') ? 'has-error':''}}">
                                        <label for="password">{{__('Module Icon')}}</label>
                                        <input type="text" class="form-control" name="module_icon" id="password" placeholder="{{__('Module Icon')}}" required>
                                        @if($errors->has('module_icon'))
                                            <label class="help-block error">{{$errors->first('module_icon')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('sequence') ? 'has-error':''}}">
                                        <label for="sequence">{{__('Sequence')}}</label>
                                        <input type="text" class="form-control" name="sequence" id="password_confirmation" placeholder="{{__('Sequence')}}" required>
                                        @if($errors->has('sequence'))
                                            <label class="help-block error">{{$errors->first('sequence')}}</label>
                                        @endif
                                    </div>
                                </div>
                                <!-- /.box-body -->

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                                    <a href="{{route(Config::get('constants.defines.APP_MODULES_INDEX'))}}" class="btn btn-primary">{{__('Back')}}</a>
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