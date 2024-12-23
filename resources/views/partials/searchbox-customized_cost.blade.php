<form action="" method="get" class="pb-3">
    <div class="row justify-content-left">
        <div class="col-lg-2 col-md-2 text-left">
                <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                    <i class="fa fa-calendar fa-2x"></i>
                </span>
        </div>
        <div class="col-lg-3 col-md-3 pt-4">
            <input name="date_range" class="form-control" value="{{$search['date_range']}}" id="date_range" readonly/>
        </div>

        <div class="col-lg-3 col-md-3 pt-4">
            <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-search"></i>
                    </span>
                <input class="form-control-rounded form-control" value="{{$search['search_key']}}" type="text" name="search_key" placeholder="{{__("Search for...")}}">
            </div>
        </div>

        <div class="col-lg- col-md-2 pt-4 text-right">
            <button class="btn btn-outline-secondary btn-block btn-std-padding btn-block" type="submit">{{__('Search')}}</button>
        </div>

    </div>
</form>

@push('css')
    @include('partials.css_blade.daterangepicker')
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
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
            "startDate": "{{isset($search['from_date']) ? $search['from_date'] : $from_date}}",
            "endDate": "{{isset($search['to_date']) ? $search['to_date'] : $to_date}}"
        });

        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#date_range").val(dateranges);
        });

    </script>
@endpush
