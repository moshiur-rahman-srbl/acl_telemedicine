<form method="get" action="{{route(Config::get('constants.defines.APP_TESTPOS_INDEX'))}}">
    <div class="row font13">
        <div class="col-md-2 col-lg-1 content-center">
            {{--<span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">--}}
                {{--<i class="fa fa-calendar fa-2x"></i>--}}
            {{--</span>--}}
        </div>
        <div class="col-md-10 col-lg-11">
            {{--<input type="hidden" value="{{isset($search['date_range']) ? $search['date_range'] : ""}}" id="date_range"--}}
                   {{--name="date_range"/>--}}
            <div class="row">
                <div class="col-md-6"></div>
                <div class="col-md-4 col-sm-6">
                    <select name="status" class="selectpicker form-control custome-padding2">
                        <option value="">{{__("Status")}}</option>
                        @foreach($status_list as $key=>$value)
                            <option {{$search['status'] !==null && $search['status']== $key ? 'selected' : ''}} value="{{$key}}">{{__($value)}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6">
                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded">
                        {{__("Submit")}}
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>
@section('css')
    @include('partials.css_blade.bootstrap-select')
@endsection
@section('scripts')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.bootstrap-select')
    <script>
        (function ($) {
            $.fn.selectpicker.defaults = {
                noneSelectedText: "{{__('Nothing selected')}}",
                    selectAllText: "{{__('Select All')}}",
                deselectAllText: "{{__('Deselect All')}}"
            };
        })(jQuery);

    </script>
@endsection



