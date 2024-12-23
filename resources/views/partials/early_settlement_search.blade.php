<form id="searchboxfrm" method="get" action="{{Request::url()}}">
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
                @if(!empty($is_for_all_merchant))
                    <div class="col-md-3 mb-3">
                        <select class="form-control selectpicker"
                                multiple
                                data-actions-box="true"
                                data-live-search="true"
                                data-title="{{__("Merchant Name")}}"
                                name="merchant_id[]"
                                data-virtual-scroll="true"
                                data-style-base="form-control"
                        >
                            @foreach($merchantList as $merchant)
                                @php
                                    $selected = '';
                                    if(\common\integration\Utility\Arr::isOfType($search['merchant_id']) && \common\integration\Utility\Arr::isAMemberOf($merchant->id, $search['merchant_id'])) {
                                        $selected = 'selected';
                                    }
                                @endphp
                                <option value="{{$merchant->id}}" {{$selected}}>{{ $merchant->name . ' (' . $merchant->id . ')' }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-md-3">
                    <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-long-arrow-down"></i>
                    </span>
                        <input class="form-control float-number-only" type="text"
                               min="0"
                               value="{{isset($search['min_amount']) ? $search['min_amount'] : ""}}" name="min_amount"
                               placeholder="{{__("Min Amount")}}"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-long-arrow-up"></i>
                    </span>
                        <input class="form-control float-number-only" type="text"
                               min="0"
                               value="{{isset($search['max_amount']) ? $search['max_amount'] : ""}}" name="max_amount"
                               placeholder="{{__("Max Amount")}}"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="currency[]"
                            class="selectpicker form-control custome-padding"
                            data-title="{{ __("Currency") }}"
                            data-actions-box="true"
                            multiple
                    >
                        @foreach($currencies as $currency_id => $currency_name)
                            <option value="{{ $currency_id }}"
                                @selected(in_array($currency_id, $search['currency']))
                            >{{ $currency_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status[]"
                            class="selectpicker form-control custome-padding"
                            data-title="{{ __("Status") }}"
                            data-actions-box="true"
                            multiple
                    >
                        @foreach($status_list as $key => $value)
                            <option value="{{ $key }}"
                                @selected(in_array($key, $search['status']))
                            >{{ $value }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row mb-4 mt-3">
                <div class="col-md-12 text-right">
                    <div class="d-flex flex-column flex-md-row pull-right pr-2 pl-md-0">
                        <a id="searchbtn"
                           href="{{ $self_url }}"
                           class="btn btn-outline-primary btn-fix btn-rounded ml-2">
                            {{__("Submit")}}
                        </a>
                    </div>
                    @if(empty($is_for_all_merchant))
                        @if(Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_MERCHANTS_EARLY_SETTLED_TRANSACTION_EXPORT')))
                            <a id="exportbtn"
                               href="{{ route(Config::get('constants.defines.APP_MERCHANTS_EARLY_SETTLED_TRANSACTION_EXPORT'), $merchant_id) }}"
                               class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                                <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i
                                        class="fa fa-caret-right"></i></span>
                            </a>
                        @endif
                    @elseif(!empty($is_for_all_merchant))
                        @if(Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_EARLY_SETTLEMENT_EXPORT')))
                            <a id="exportbtn"
                               href="{{route(Config::get('constants.defines.APP_EARLY_SETTLEMENT_EXPORT'))}}"
                               class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                                <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i
                                        class="fa fa-caret-right"></i></span>
                            </a>
                        @endif
                    @endif
                    <a class="btn btn-info btn-fix mb-2 mb-md-auto"
                       href="{{route(Config::get('constants.defines.APP_EXPORT_REPORTS_HISTORY_INDEX'))}}">
                        {{__('Export Report History')}}
                    </a>

                </div>
            </div>
        </div>
    </div>
</form>

@push('css')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.select2')
    @include('partials.css_blade.alertify')
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.alertify')
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
            //console.log(picker.startDate.format('YYYY/MM/DD'));
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#daterange").val(dateranges);
        });

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');

            var msg = "<strong class='p-4 m-0 d-block'>" + "{{__('Please click on Continue button to export the list.')}}" + "</strong>";

            alertify.confirm(msg, function () {
                $("#searchboxfrm").attr('action', fromurl);
                $("#searchboxfrm").submit();
            }, function () {
                return false;
            }, );

            $('button.ok').addClass('btn btn-outline-primary rounded').text("<?php echo e(__('Continue')); ?>");
            $('button.cancel').addClass('btn btn-light rounded').text("<?php echo e(__('Cancel')); ?>");
        });

        $('#searchbtn').on('click', function(event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
        })
    </script>
@endpush
