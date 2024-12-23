<form id="searchboxfrm" method="get" action="{{route(Config::get('constants.defines.APP_REPORTS_HISTORY_INDEX'))}}">
    <input type="hidden" name="search" value="search"/>
    <div class="row font13">
        <div class="col-md-1 content-center">
            <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        <div class="col-md-11">
            <input type="hidden" value="{{isset($search['daterange']) ? $search['daterange'] : ""}}" id="daterange"
                   name="daterange"/>
            <div class="row">
                <div class="col-md-4">
                    <input class="form-control" name="requestedby"
                           value="{{isset($search['requestedby']) ? $search['requestedby'] : ""}}" type="text"
                           placeholder="{{__("Requested By")}}"/>
                </div>

                <div class="col-md-4">
                    <select name="status" class="selectpicker form-control">
                        <option value=''>{{__('All')}}</option>
                    @foreach(\App\Models\ReportsHistory::APPROVAL_STATUS as $key => $value)
                                <option
                                    value="{{$key}}" {{(isset($search['status']) && $key == $search['status']) ?'selected':'' }}>{{__($value)}}</option>
                            @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <select name="table_name" class="selectpicker form-control">
                        <option value=''>{{__('Table Name')}}</option>
                            @foreach(\App\Models\ReportsHistory::EXPORT_TABLES as $key => $value)
                                <option
                                        value="{{$key}}" {{($key == $search['table_name']) ?'selected':'' }}>{{__($value)}}</option>
                            @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4 mt-3 mr-4">
        <div class="col-md-12 text-right">
            <a href="{{route(Config::get('constants.defines.APP_REPORTS_HISTORY_INDEX'))}}">{{__("Clear Filters")}}</a>
            &nbsp;&nbsp;
            <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded ml-2">
                {{__("Submit")}}
            </button>

        </div>
    </div>

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
            "autoApply": false,
            locale: {
                format: 'YYYY/MM/DD',
                customRangeLabel: "{{__('Custom Range')}}",
                applyLabel: "{{__('Apply')}}",
                cancelLabel: "{{__('Cancel')}}"
            },
            ranges: {
                '{{__('Today')}}': [moment(), moment()],
                '{{__('Yesterday')}}': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                '{{__('Last 7 Days')}}': [moment().subtract(6, 'days'), moment()],
                '{{__('Last 30 Days')}}': [moment().subtract(29, 'days'), moment()],
                '{{__('This Month')}}': [moment().startOf('month'), moment().endOf('month')],
                '{{__('Last Month')}}': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            "alwaysShowCalendars": true,
            "startDate": "{{isset($search['from_date']) ? $search['from_date'] : ""}}",
            "endDate": "{{isset($search['to_date']) ? $search['to_date'] : ""}}"
        });

        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
            //do something, like clearing an input
            console.log(picker.startDate.format('YYYY/MM/DD'));
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#daterange").val(dateranges);
        });

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');
            console.log(fromurl);
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
            $("#searchboxfrm").attr('action', "{{route(Config::get('constants.defines.APP_REPORTS_INDEX'))}}");
        });
    </script>
@endpush



