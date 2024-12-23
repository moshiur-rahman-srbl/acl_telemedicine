<form method="get" action="{{route(Config::get('constants.defines.APP_PAYMENT_TOKEN_INDEX'))}}" id="search-form">
    <div class="row font13">
        <div class="col-md-12 col-lg-12">
            <div class="row pb-4">
                <div class="col-md-3 col-sm-6 pb-2">
                    <input value="{{ $search['daterange'] }}" placeholder="{{__('Date range')}}" type="text"
                           id="dateprangepicker" class="selectpicker show-tick rounded form-control"
                           name="daterange"
                           autocomplete="off" readonly/>
                </div>

                <div class="col-md-3 col-sm-6 pb-2">
                    <select name="status" class="form-control-rounded form-control">
                        <option value="">{{__("Status")}}</option>
                        <option value="{{\App\Models\YapiToken::NOT_PROCESSED_YET}}" {{ isset($search['status']) && $search['status'] == \App\Models\YapiToken::NOT_PROCESSED_YET ? 'selected':''}} >{{ __("NOT PROCESSED YET")}}</option>
                        <option value="{{\App\Models\YapiToken::PROCESSED}}" {{ isset($search['status']) && $search['status'] == \App\Models\YapiToken:: PROCESSED ? 'selected':''}}>{{ __("PROCESSED")}}</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="input-group-icon input-group-icon-right">
                        <input class="form-control" value="{{isset($search['merchant_id']) ? $search['merchant_id'] : ""}}" type="text" name="merchant_id" placeholder="{{__('Merchant ID')}}">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="input-group-icon input-group-icon-right">
                        <input class="form-control" value="{{isset($search['order_id']) ? $search['order_id'] : ""}}" type="text" name="order_id" placeholder="{{__('Order ID')}}">
                    </div>
                </div>


                <div class="col-md-3 col-sm-6 pb-2">
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

                <div class="col-md-12 col-sm-12 pb-2 text-right">
                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded ml-4" name="submit" value="search-btn">
                        {{__("Search")}}
                    </button>

                </div>
            </div>
        </div>
    </div>

</form>

@push('scripts')
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
            $("#daterange").val(dateranges);
        });


    </script>
@endpush



