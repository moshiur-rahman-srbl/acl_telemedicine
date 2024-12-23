@php
    $current_route_name = Route::currentRouteName();

    if ($current_route_name == Config::get('constants.defines.APP_COMPLETED_REFUND_INDEX') ){
       $field_name = "username";
       $place_holder = "User Name";
    }
    else{
        $field_name = "merchantname";
        $place_holder = "Merchant Name";
    }
@endphp

<style>
    .none-custom-padding-top ul.dropdown-menu {
        padding-top: 0 !important;
    }
</style>

<form id="searchboxfrm" method="get" action="#">
    @include('completed_refunds.modals.col_hide')
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
                <div class="col-md-3">
                    <input class="form-control" name="customergsm"
                           value="{{isset($search['customergsm']) ? $search['customergsm'] : ""}}" type="text"
                           placeholder="{{__("Customer GSM")}}"/>
                </div>
                <div class="col-md-3">
                    <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-long-arrow-down"></i>
                    </span>
                        <input class="form-control" type="text"
                               value="{{isset($search['minamount']) ? $search['minamount'] : ""}}" name="minamount"
                               placeholder="{{__("Min Amount")}}"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-long-arrow-up"></i>
                    </span>
                        <input class="form-control" type="text"
                               value="{{isset($search['maxamount']) ? $search['maxamount'] : ""}}" name="maxamount"
                               placeholder="{{__("Max Amount")}}"/>
                    </div>
                </div>
                {{-- <div class="col-md-3">
                    <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-search"></i>
                    </span>
                        <input class="form-control-rounded form-control"
                               value="{{isset($search['searchkey']) ? $search['searchkey'] : ""}}" type="text"
                               name="searchkey"
                               placeholder="{{__("Search")}} ..."/>
                    </div>
                </div> --}}
                <div class="col-md-3">
                    <input class="form-control" name="orderid"
                           value="{{isset($search['orderid']) ? $search['orderid'] : ""}}" type="text"
                           placeholder="{{__("Order Id")}}"/>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <input class="form-control" name="transid"
                           value="{{isset($search['transid']) ? $search['transid'] : ""}}" type="text"
                           placeholder="{{__("Trans. ID")}}"/>
                </div>
                <div class="col-md-3">
                    @if(isset($paymentMethods))
                        <select name="paymentmethodid[]" class="selectpicker form-control custome-padding" data-title="{{__("Payment Method")}}" data-actions-box="true" multiple>
                            {{--<option value="" disabled="disabled">{{__('Please select')}}</option>--}}
                            @foreach($paymentMethods as $paymentMethod)
                                <?php
                                $slctd = '';
                                if(is_array($search['paymentmethodid']) && in_array($paymentMethod->id, $search['paymentmethodid'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$paymentMethod->id}}" {{$slctd}}>{{__($paymentMethod->name)}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>

                <div class="col-md-3">
                    <div class="input-group-icon input-group-icon-right">
                        <input class="form-control"
                               value="{{ isset($search[$field_name] )? $search[$field_name] : ""}}" type="text"
                               name="{{$field_name}}"
                               placeholder="{{__($place_holder)}}"/>
                    </div>
                </div>
                <div class="col-md-3">

                    {{-- Type Selector --}}
                    <select name="transactionState[]"
                            multiple
                            data-actions-box="true"
                            class="selectpicker form-control custome-padding"
                            data-title="{{__('Type')}}">
                        <option value="{{\App\Models\TransactionState::REFUNDED}}"
                            {{ in_array(\App\Models\TransactionState::REFUNDED, $search['transactionState']) ? 'selected' : '' }}>{{__("Completed Refunds")}}</option>
                        <option value="{{\App\Models\TransactionState::PARTIAL_REFUND}}"
                            {{ in_array(\App\Models\TransactionState::PARTIAL_REFUND, $search['transactionState']) ? 'selected' : '' }}>{{__("Partial Refunds")}}</option>
                    </select>
                    {{-- Type Selector END --}}

                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3">
                    <input class="form-control" name="invoiceid"
                           value="{{isset($search['invoiceid']) ? $search['invoiceid'] : ""}}" type="text"
                           placeholder="{{__("Invoice Id")}}"/>
                </div>

                {{-- POS ID Selector --}}
                <div class="col-md-3">
                    <select class="form-control selectpicker custome-padding"
                            name="pos_id[]"
                            multiple
                            data-actions-box="true"
                            data-title="{{__("POS")}}">
                        @foreach($poses as $pos)
                            <option value="{{ $pos->pos_id }}"
                                {{ !empty($search['pos_id']) && in_array($pos->pos_id, $search['pos_id']) ? 'selected':''}}>{{$pos->name}}</option>
                        @endforeach
                    </select>
                </div>
                {{-- POS ID Selector END --}}

                {{-- Currency Selector --}}
                <div class="col-md-3">
                    <select name="currencies[]"
                            class="selectpicker form-control custome-padding"
                            data-title="{{__("Currency")}}"
                            data-actions-box="true"
                            multiple>
                        @foreach($currencies as $currency)
                            <option value="{{ $currency->id }}"
                                    title={{ $currency->code }}
                                {{ !empty($search['currencies']) && in_array($currency->id, $search['currencies']) ? 'selected':''}}>{{ __($currency->name).' ('.$currency->symbol.')' }}</option>
                        @endforeach
                    </select>
                </div>

                @if(isset($table_types) && !empty($table_types))
                    <div class="col-md-3">
                        <select name="backup_table_type" class="form-control" data-title="{{ __("Backup Type") }}"
                                data-style-base="form-control">
                            @foreach($table_types as $key => $table_type)
                                <option
                                    value="{{ $key }}"{{ $key == $search["backup_table_type"] ? 'selected':''}}>{{ __($table_type) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                {{-- Currency Selector END --}}

            </div>
        </div>
    </div>


    {{-- Buttons --}}
    <div class="row mb-4 mt-4">
        <div class="col-5">
            <div class="d-flex flex-column flex-md-row pull-right pr-2 pl-md-0">
                {{-- Cear Filters --}}
                <a href="{{isset($selfUrls) ? $selfUrls : ""}}"
                   class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">{{__("Clear Filters")}}</a>
                &nbsp;&nbsp;
                <button type="button"
                        id="searchbtn"
                        class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                    {{__("Submit")}}
                </button>
            </div>
        </div>
        <div class="col-md-7 d-flex flex-column flex-md-row text-md-right">
            <button type="button"
                    class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto"
                    data-bs-toggle="modal"
                    data-bs-target="#colHideModal">
                <span class="btn-icon">{{__("Col. Hide")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-eye"></i></span>
            </button>
            @if(isset($exportBtnRouteName))
                @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                    <div class="form-group ml-1 mr-1">
                        <select class="form-control selectpicker show-tick rounded" name="file_type" id="file_type">
                            <option value="{{\App\Models\MerchantReportHistory::FORMAT_CSV}}">CSV</option>
                            <option value="{{\App\Models\MerchantReportHistory::FORMAT_XLS}}">XLS</option>
                            <option value="{{\App\Models\MerchantReportHistory::FORMAT_PDF}}">PDF</option>
                        </select>
                    </div>

                    <a id="exportbtn"
                       href="{{ isset($selfUrls) ? $selfUrls."/exports" : ""}}"
                       class="btn btn-outline-secondary btn-fix mb-2 mb-md-auto">
                                        <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i
                                                class="fa fa-caret-right"></i></span>
                    </a>

                    <div class="col- ml-2 mb-3">
                        <a class="btn btn-info rounded" href="{{route(Config::get('constants.defines.APP_EXPORT_REPORTS_HISTORY_INDEX'))}}">
                            {{__('Export Report History')}}</a>
                    </div>
                @endif
            @endif

        </div>
    </div>
    {{-- Buttons END --}}
</form>

@push('css')
    @include('partials.css_blade.select2')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.alertify')
    <style>
        .custome-padding div.bs-actionsbox{
            width: 100% !important;
        }
    </style>
@endpush

@push('js-before-app-js')
    @include('partials.js_blade.validate')
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.alertify')

    <script>

        $( document ).ready(function() {
            (function ($) {
                $.fn.selectpicker.defaults = {
                    noneSelectedText: "{{__('Nothing selected')}}",
                    selectAllText: "{{__('Select All')}}",
                    deselectAllText: "{{__('Deselect All')}}",
                    noneResultsText: '{{__('No results matched')}} {0}',
                };
            })(jQuery)
        });

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

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');

            var msg = "<strong class='p-0 m-0 d-block'>"+"{{__('Are you sure?')}}"+"</strong>";
            msg += "<p class='p-0 m-0 pl-4 pr-4 text-left'>"+"{{__('The exported report file will be generated and link of it will be sent via email')}}"+".</p>";

            alertify.confirm(msg, function () {
                $('#exportbtn').html('<i class="fa fa-spin fa-refresh"></i>');
                // console.log(fromurl);
                $("#searchboxfrm").attr('action', fromurl);
                $("#searchboxfrm").submit();
            },function () {
                return false;
            });

            $('button.ok').addClass('btn btn-danger rounded').text("<?php echo e(__('Okay')); ?>");
            $('button.cancel').addClass('btn btn-light rounded').text("<?php echo e(__('Cancel')); ?>");
        });

        $("#searchbtn").on("click", function (event) {
            event.preventDefault();
            $("#searchboxfrm").attr('action', '{{isset($selfUrls) ? $selfUrls : ""}}');
            $("#searchboxfrm").submit();
        });


    </script>
@endpush



