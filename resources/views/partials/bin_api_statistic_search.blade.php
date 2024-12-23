<form method="get" action="{{route(Config::get('constants.defines.APP_BIN_API_STATISTIC_INDEX'))}}">
    <div class="row font13">
        <div class="col-md-12 col-lg-12">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <input value="{{ $search['date_range'] }}" placeholder="{{__('Date range')}}" type="text"
                           id="dateprangepicker" class="selectpicker show-tick form-control"
                           name="date_range"
                           autocomplete="off" readonly/>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="input-group">
                        <input name="bin" id="bin" class="form-control" value="{{ $search['bin'] }}" type="text" placeholder="Bin" style="height: 41px;">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="input-group">
                        <input name="brand_code" id="brand_code" class="form-control" value="{{ $search['brand_code'] }}" type="text" placeholder="{{__('Bin Code')}}" style="height: 41px;">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-search"></i>
                    </span>
                        <input class="form-control"
                               value="{{isset($search['search_key']) ? $search['search_key'] : ""}}" type="text"
                               name="search_key"
                               placeholder="{{__("Search")}} ..."/>
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 float-right pt-2">
                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded float-right">
                        {{__("Search")}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@section('css')
    @include('partials.css_blade.daterangepicker')
@endsection
@section('scripts')

    @include('partials.js_blade.validate')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.daterangepicker')
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
            "startDate": "{{isset($search['from_date']) ? $search['from_date'] : ""}}",
            "endDate": "{{isset($search['to_date']) ? $search['to_date'] : ""}}"
        });

        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
            //do something, like clearing an input
            console.log(picker.startDate.format('YYYY/MM/DD'));
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#date_range").val(dateranges);
        });


    </script>
@endsection



