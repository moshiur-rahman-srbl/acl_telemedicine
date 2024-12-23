<form method="get" action="{{route(Config::get('constants.defines.APP_BANKS_INDEX'))}}">
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
                <div class="col-md-3 col-sm-6">
                    <select name="status" class="selectpicker form-control custome-padding2">
                        <option value="-1" disabled="disabled">{{__("Status")}}</option>
                        @foreach($status_list as $key=>$value)
                            <option {{$search['status'] == $key ? 'selected' : ''}} value="{{$key}}">{{__($value)}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 col-sm-6">
                    @if(isset($country_list))
                        <select name="country_id[]" class="selectpicker form-control custome-padding" data-title="{{__("Country")}}" data-actions-box="true" multiple>
                            {{--                            <option value="" disabled="disabled">{{__('Please select')}}</option>--}}
                            @foreach($country_list as $country)
                                <?php
                                $slctd = '';
                                if(is_array($search['country_id']) && in_array($country->id, $search['country_id'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$country->id}}" {{$slctd}}>{{\common\integration\GlobalFunction::getColumnLang($country)}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                {{--<div class="col-md-3">--}}
                    {{--<select name="merchant_id" class="selectpicker form-control">--}}
                        {{--<option value="">{{__("Merchant Name")}}</option>--}}
                        {{--@foreach($merchant_list as $merchant_id => $merchant)--}}
                            {{--<option {{$merchant_id == $search['merchant_id'] ? 'selected':''}} value="{{$merchant_id}}">{{__($merchant)}}</option>--}}
                        {{--@endforeach--}}
                    {{--</select>--}}
                {{--</div>--}}
                <div class="col-md-3 col-sm-6">
                    <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-search"></i>
                    </span>
                        <input class="form-control-rounded form-control"
                           value="{{isset($search['search_key']) ? $search['search_key'] : ""}}" type="text"
                           name="search_key"
                           placeholder="{{__("Search")}} ..."/>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
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

        $('select[name=merchant_id]').select2({ width: '100%' ,'border-radius':'5px',
            ajax: {
                url: '{{route(config('constants.defines.APP_ALL_TRANSACTION_INDEX'))}}',
                data:function (params) {
                    var query = {
                        merchant_name: params.term,
                        action: 'GET_MERCHANTS'
                    }
                    return query;
                },
                processResults: function (data) {
                  console.log('data',data);
                  return {
                    results: data
                  };
                }
            },
            placeholder: "{{__('Merchant Name')}}",
            allowClear: true


        });

        {{--$('#dateprangepicker').daterangepicker({--}}
            {{--maxDate: new Date(),--}}
            {{--"autoApply": false,--}}
            {{--locale: {--}}
                {{--format: 'YYYY/MM/DD',--}}
                {{--customRangeLabel: "{{__('Custom Range')}}",--}}
                {{--applyLabel: "{{__('Apply')}}",--}}
                {{--cancelLabel: "{{__('Cancel')}}"--}}
            {{--},--}}
            {{--ranges: {--}}
                {{--"{{__('Today')}}": [moment(), moment()],--}}
                {{--"{{__('Yesterday')}}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],--}}
                {{--"{{__('Last 7 Days')}}": [moment().subtract(6, 'days'), moment()],--}}
                {{--"{{__('Last 30 Days')}}": [moment().subtract(29, 'days'), moment()],--}}
                {{--"{{__('This Month')}}": [moment().startOf('month'), moment().endOf('month')],--}}
                {{--"{{__('Last Month')}}": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]--}}
            {{--},--}}
            {{--"alwaysShowCalendars": true,--}}
            {{--"startDate": "{{isset($search['from_date']) ? $search['from_date'] : ""}}",--}}
            {{--"endDate": "{{isset($search['to_date']) ? $search['to_date'] : ""}}"--}}
        {{--});--}}

//        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
//            //do something, like clearing an input
//            console.log(picker.startDate.format('YYYY/MM/DD'));
//            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
//            $("#date_range").val(dateranges);
//        });


    </script>
@endsection



