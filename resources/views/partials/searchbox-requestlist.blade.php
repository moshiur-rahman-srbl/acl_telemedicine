<form id="searchboxfrm" method="get" action="">
    <input type="hidden" name="search" value="search"/>
    <div class="row mb-4 font13">
        <div class="col-md-1 content-center">
            <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        <input name="date_range" type="hidden" value="{{$search['date_range']}}" id="date_range"/>
        <div class="col-md-3">
            <select name="currency_id" class="selectpicker form-control custome-padding2">
                <option value="">{{__('Select Currency')}}</option>
                @foreach($currencies as $currency)
                    <option value="{{$currency->id}}" {{$search['currency_id'] == $currency->id ? 'selected' : ''}}>{{$currency->code}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="request_type" class="selectpicker form-control custome-padding2">
                <option value="{{\App\Models\RequestList::REQUEST_LIST}}" {{$search['request_type'] == \App\Models\RequestList::REQUEST_LIST ? 'selected' : ''}}>{{__('Request List')}}</option>
                <option value="{{\App\Models\RequestList::ARCHIEVED_REQUEST_LIST}}" {{$search['request_type'] == \App\Models\RequestList::ARCHIEVED_REQUEST_LIST ? 'selected' : ''}}>{{__('Archieved Requests')}}</option>
            </select>
        </div>
        <div class="col-md-3">
            <input name="reference" type="text" value="{{$search['reference']}}" class="form-control rounded" placeholder="{{__('Reference')}}">
        </div>
        <div class="col-md-2 text-right">
            <!--<a href="{{isset($selfUrls) ? $selfUrls : ""}}">{{__("Clear filter")}}</a>-->
            <button type="submit" id="searchbtn" class="btn btn-outline-primary btn-fix btn-rounded ml-2">
                {{__("Search")}} <i class="fa fa-caret-right"></i>
            </button>
        </div>
    </div>
    <!--<div class="row mb-4 mt-3">
        <div class="col-md-1"></div>
        <div class="col-md-11 text-right">
            <a href="{{isset($selfUrls) ? $selfUrls : ""}}">{{__("Clear filter")}}</a>
        &nbsp;&nbsp;    <button type="submit" id="searchbtn" class="btn btn-outline-primary btn-fix btn-rounded ml-2">
                {{__("Search")}} <i class="fa fa-caret-right"></i>
            </button>

            @if(isset($exportBtnRouteName))
                @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                    <a id="exportbtn" href="{{ isset($selfUrls) ? $selfUrls."/export" : ""}}"
                       class="btn btn-outline-secondary btn-fix btn-rounded ml-2">
                        <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i></span>
                    </a>
                @endif
            @endif
        </div>
    </div>-->

</form>

@push('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.bootstrap-select')

    <script>
        $('#dateprangepicker').daterangepicker({
            maxDate: new Date(),
            "autoApply": false,
            locale: {
                format: 'YYYY/MM/DD',
                customRangeLabel: "{{__('Custom Range')}}",
                applyLabel: "{{__('Apply')}}",
                cancelLabel: "{{__('Cancel')}}"
            },
            ranges: {
                "{{__('Today')}}": [moment(), moment()],
                "{{__('Yesterday')}}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                "{{__('Last 7 Days')}}": [moment().subtract(6, 'days'), moment()],
                "{{__('Last 30 Days')}}": [moment().subtract(29, 'days'), moment()],
                "{{__('This Month')}}": [moment().startOf('month'), moment().endOf('month')],
                "{{__('Last Month')}}": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            "alwaysShowCalendars": true,
            "startDate": "{{isset($search['from_date']) ? $search['from_date'] : $from_date}}",
            "endDate": "{{isset($search['to_date']) ? $search['to_date'] : $to_date}}"
        });

         $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
             //do something, like clearing an input
             // console.log(picker.startDate.format('YYYY/MM/DD'));
             var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
             $("#date_range").val(dateranges);
         });

        {{--$("#exportbtn").on("click", function (event) {--}}
        {{--    event.preventDefault();--}}
        {{--    var fromurl = $(this).attr('href');--}}
        {{--    // console.log(fromurl);--}}
        {{--    $("#searchboxfrm").attr('action', fromurl);--}}
        {{--    $("#searchboxfrm").submit();--}}
        {{--});--}}

        {{--$("#searchbtn").on("click", function (event) {--}}
        {{--    event.preventDefault();--}}
        {{--    $("#searchboxfrm").attr('action', '{{isset($selfUrls) ? $selfUrls : Request::url()}}');--}}
        {{--    $("#searchboxfrm").submit();--}}
        {{--});--}}


    </script>
@endpush



