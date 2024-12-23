@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_PAGES_INDEX'))}}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-list-ul"></i> {{__('List')}}</a>
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
                                    <div class="form-group {{$errors->has('id') ? 'has-error':''}}">
                                        <label for="id">{{__('ID')}}</label>
                                        <input type="text" class="form-control" name="id" id="id" placeholder="{{__('ID')}}" value="" required/>
                                        @if($errors->has('id'))
                                            <label class="help-block error">{{$errors->first('id')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('module_id') ? 'has-error':''}}">
                                        <label for="module_id">{{__('Module')}}</label>
                                        <select class="form-control" name="module_id" id="module_id" required>
                                            <option value="" selected disabled>{{__('Please select')}}</option>
                                            @foreach($modules as $module)
                                                <option value="{{$module->id}}">{{__($module->name)}}</option>
                                            @endforeach
                                        </select>
                                        @if($errors->has('module_id'))
                                            <label class="help-block error">{{$errors->first('module_id')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('sub_module_id') ? 'has-error':''}}">
                                        <label for="sub_module_id">{{__('Sub Module')}}</label>
                                        <select class="form-control" name="sub_module_id" id="sub_module_id" required>
                                            <option value="" selected disabled>{{__('Please select')}}</option>
                                            {{--@foreach($submodules as $submodule)--}}
                                                {{--<option value="{{$submodule->id}}" class="subm {{'m'.$submodule->module_id}}">{{__($submodule->name)}}</option>--}}
                                            {{--@endforeach--}}
                                        </select>
                                        @if($errors->has('sub_module_id'))
                                            <label class="help-block error">{{$errors->first('sub_module_id')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('name') ? 'has-error':''}}">
                                        <label for="name">{{__('Name')}}</label>
                                        <input type="text" class="form-control" name="name" id="name" placeholder="{{__('Name')}}" value="" required/>
                                        @if($errors->has('name'))
                                            <label class="help-block error">{{$errors->first('name')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('method_name') ? 'has-error':''}}">
                                        <label for="method_name">{{__('Method Name')}}</label>
                                        <input type="text" class="form-control" name="method_name" id="method_name" placeholder="{{__('Method Name')}}" value="" required/>
                                        @if($errors->has('method_name'))
                                            <label class="help-block error">{{$errors->first('method_name')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('method_type') ? 'has-error':''}}">
                                        <label for="method_type">{{__('Method Type')}}</label>
                                        <select class="form-control" name="method_type" id="method_type" required>
                                            <option value="" selected disabled>{{__('Please select')}}</option>
                                            <option value="1">Post</option>
                                            <option value="2">Get</option>
                                            <option value="3">Put</option>
                                            <option value="4">Delete</option>
                                        </select>
                                        @if($errors->has('method_type'))
                                            <label class="help-block error">{{$errors->first('method_type')}}</label>
                                        @endif
                                    </div>
                                    <div class="form-group {{$errors->has('available_to_company') ? 'has-error':''}}">
                                        <label class="checkbox checkbox-ebony">
                                            <input type="checkbox" name="available_to_company" id="available_to_company" value="1">
                                            <span class="input-span"></span>{{__('Available To Company')}}</label>
                                        @if($errors->has('module_icon'))
                                            <label class="help-block error">{{$errors->first('module_icon')}}</label>
                                        @endif
                                    </div>
                                </div>
                                <!-- /.box-body -->

                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                                    <a href="{{route(Config::get('constants.defines.APP_PAGES_INDEX'))}}" class="btn btn-primary">{{__('Back')}}</a>
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
    <script>
        $(document).on('change', '#module_id', function () {
            var selectedValue = $(this).val();
            $("#sub_module_id").html('<option value="" selected disabled>{{__("Please select")}}</option>');
            $('#loader').removeClass('d-none');
            if(selectedValue) {
                $.ajax({
                    type: "GET",
                    url: '{{url(config("constants.defines.ADMIN_URL_SLUG")."/pages/getassociation/")}}/'+selectedValue,
                    dataType: "json",
                    success: function (response) {
                        $('#loader').addClass('d-none');
                        $("#sub_module_id").html(response.submodule_content);
                    },
                    error: function (xhr, status, error) {
                        console.log('error');
                    }
                });
            }
        });

        $(document).ready(function(){
            $('#module_id').change();
        });
    </script>
@endsection