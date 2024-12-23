<form id="searchboxfrm" method="get" action="{{route(Config::get('constants.defines.APP_MERCHANTS_SETTLEMENT_WALLETLOG'),$merchantid)}}">
    <input type="hidden" name="search" value="search"/>
    <div class="row mb-4 font13">
        <div class="col-md-4 mb-3">
            <input name="date_range" type="text" value="{{$search['date_range']}}" id="dateprangepicker" class="form-control rounded p-3" readonly autocomplete="off"/>
        </div>
        <div class="col-md-4 mb-3">
            <select name="currency_id" class="selectpicker form-control custome-padding2">
                <option value="">{{__('Select Currency')}}</option>
                @foreach($currencies as $currency)
                    <option value="{{$currency->id}}" {{$search['currency_id'] == $currency->id ? 'selected' : ''}}>{{$currency->code}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <select name="event_name[]"
                    class="selectpicker form-control custome-padding"
                    data-title="{{__("Event Name")}}"
                    data-actions-box="true"
                    data-virtual-scroll="true"
                    multiple>
                @foreach( config('constants.WALLET_EVENT_NAME') as $key => $event_name )
                        <?php
                        $slctd = '';
                        if(\common\integration\Utility\Arr::isOfType($search['event_name']) && \common\integration\Utility\Arr::isAMemberOf($event_name, $search['event_name'])) {
                            $slctd = 'selected';
                        }
                        ?>
                    <option value="{{ $event_name }}" {{ $slctd }}>{{ __($event_name)  }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-3">
            <select name="backup_table_type" class="form-control" data-title="{{ __("Backup Type") }}" data-style-base="form-control">
                @foreach($table_types as $key => $table_type)
                    <option value="{{ $key }}"{{ $key == $search["backup_table_type"] ? 'selected':''}}>{{ __($table_type) }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-md-4 mb-3">
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
    </div>

    <div class="row mb-4">
        <div class="col-md-7 d-flex flex-column flex-md-row text-right">
            <button type="submit" id="searchbtn" class="btn btn-outline-primary btn-fix btn-rounded ml-2">
                {{__("Search")}} <i class="fa fa-caret-right"></i>
            </button>
            @if(isset($exportRouteName) && auth()->user()->hasPermissionOnAction($exportRouteName))
                <div class="ml-2 mb-3">
                    <select name="file_type" class="form-control-rounded form-control">
                        <option value="{{\App\Models\MerchantReportHistory::FORMAT_CSV}}">CSV</option>
                        <option value="{{\App\Models\MerchantReportHistory::FORMAT_XLS}}">XLS</option>
                    </select>
                </div>
                <a id="exportbtn" href="{{route($exportRouteName,$merchantid)}}"
                   class="btn btn-outline-secondary btn-fix btn-rounded ml-2">
                    <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i></span>
                </a>
            @endif
        </div>
    </div>
</form>
@section('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.alertify')
@endsection
@section('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.alertify')
    <script>

        (function ($) {
            $.fn.selectpicker.defaults = {
                noneSelectedText: "{{__('Nothing selected')}}",
                selectAllText: "{{__('Select All')}}",
                deselectAllText: "{{__('Deselect All')}}"
            };
        })(jQuery);

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

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
        });

    </script>
@endsection



