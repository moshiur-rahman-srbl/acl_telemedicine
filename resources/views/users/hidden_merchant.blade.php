@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}"
                                                    class="ml-3 btn btn-sm btn-primary pull-right"><i
                            class="fa fa-list-ul"></i> {{__('List')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <div class="box-header with-border">
                    {{--<h3 class="box-title">{{__('Create User')}}</h3>--}}
                </div>
                <!-- /.box-header -->
                <!-- form start -->
                <form role="form"
                      action="{{ route(Config::get('constants.defines.APP_USERS_HIDDEN_MERCHANT'), [$id]) }}"
                      method="post" enctype="multipart/form-data">
                    @csrf

                    <div class="row">
                        <div class="col-md-6">
                            <div class="box-body">
                                <div class="col-md-10 form-group">
                                    <label for="language">{{__('Merchants')}}</label>
                                    <select class="form-control selectpicker"
                                            multiple
                                            data-actions-box="true"
                                            data-live-search="true"
                                            data-title="{{__("Merchant Name")}}"
                                            name="merchant_id[]"
                                            data-virtual-scroll="true"
                                            data-style="''"
                                            data-style-base="form-control">
                                        @if(isset($merchantList) && !empty($merchantList))
                                            @foreach($merchantList as $merchant)
                                                <?php
                                                $slctd = '';
                                                if (is_array($hiddenMerchantList) && in_array($merchant->id, $hiddenMerchantList)) {
                                                    $slctd = 'selected';
                                                }
                                                ?>
                                                <option
                                                    value="{{$merchant->id}}" {{$slctd}}>{{ $merchant->name . ' (' . $merchant->id . ')' }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            @if (Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_USERS_MODIFY_HIDDEN_MERCHANT')))
                                <div class="box-footer">
                                    <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
                                    <a href="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}"
                                       class="btn btn-primary">{{__('Cancel')}}</a>
                                </div>
                            @endif
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
    @include('partials.css_blade.multi-select')

@endpush
@push('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.multi-select')

    <script>

        (function ($) {
            $.fn.selectpicker.defaults = {
                noneSelectedText: "{{__('Nothing selected')}}",
                selectAllText: "{{__('Select All')}}",
                deselectAllText: "{{__('Deselect All')}}",
                noneResultsText: '{{__('No results matched')}} {0}',
            };
        })(jQuery);
    </script>

@endpush
