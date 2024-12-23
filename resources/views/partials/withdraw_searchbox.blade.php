@php
    $current_route_name = Route::currentRouteName();

    if ($current_route_name == Config::get('constants.defines.APP_DEPOSITS_INDEX') ){
       $field_name = "username";
       $place_holder = "User Name";
    }
    else{
        $field_name = "merchantname";
        $place_holder = "Merchant Name";
    }

    $filterBtnCls = 'col-lg-12';

@endphp

<form id="searchboxfrm" method="get" action="{{Request::url()}}">
    @include('withdrawals.modals.col_hide')
    <input type="hidden" name="search" value="search"/>
    <input type="hidden" name="page_limit" value="{{$search['page_limit'] ?? ''}}"/>
    <div class="row font13">
        <div class="col-md-1 content-center">
            <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        <div class="col-md-11">
            <input type="hidden" value="{{isset($search['daterange']) ? $search['daterange'] : ""}}" id="daterange"
                   name="from_date"/>
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
                <div class="col-md-3">
                    <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-search"></i>
                    </span>
                        <input class="form-control-rounded form-control"
                               value="{{isset($search['searchkey']) ? $search['searchkey'] : ""}}" type="text"
                               name="searchkey"
                               placeholder="{{__("Search")}} ..."/>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3">

                    <select name="user_types[]" class="selectpicker form-control custome-padding" data-title="{{__('User Type')}}" data-actions-box="true" multiple>

                        @if(isset($user_types))
                            @foreach($user_types as $key => $value)
                                <?php
                                $slctd = '';
                                if(is_array($search['user_types']) && in_array($key, $search['user_types'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$key}}" {{$slctd}}>{{__($value)}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-3">
                    <input class="form-control" name="transid"
                           value="{{isset($search['transid']) ? $search['transid'] : ""}}" type="text"
                           placeholder="{{__("Trans. ID")}}"/>
                </div>

                <div class="col-md-3">

                     @if(isset($isAlltransaction) && $isAlltransaction)
                    <select name="merchantid" class="selectpicker form-control">
                            <option value="">{{__($place_holder)}}</option>
                                @foreach($merchantList as $merchant_id => $merchant)
                                    <option {{$merchant_id == $search['merchantid'] ? 'selected':''}} value="{{$merchant_id}}">{{__($merchant)}}</option>
                                @endforeach
                        </select>
                     @else
                     <div class="input-group-icon input-group-icon-right">
                        <input class="form-control"
                               value="{{ isset($search[$field_name] )? $search[$field_name] : ""}}" type="text"
                               name="{{$field_name}}"
                               placeholder="{{__($place_holder)}}"/>
                     </div>
                     @endif

                </div>
                <div class="col-md-3">
                    <select name="transactionState[]" class="selectpicker form-control custome-padding" data-title="{{__('Status')}}" data-actions-box="true" multiple>
                        {{--@if(isset($transactionStateList[1]))--}}
                            @foreach($transactionStateList as $key => $value)
                                <?php
                                $slctd = '';
                                if(is_array($search['transactionState']) && in_array($key, $search['transactionState'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$key}}" {{$slctd}}>{{__($value)}}</option>
                            @endforeach
                        {{--@endif--}}
                    </select>
                </div>
            </div>
            @if(isset($search['paymentmethodid']) && $search['paymentmethodid']==\App\Models\DepositeMethod::EFT)
                <div class="row mt-3">
                    <div class="col-md-3">
                        <select name="automationStatus" class="selectpicker form-control">
                            <option value='' {{($search['automationStatus'] == '') ? 'selected' : ''}}>{{__('Automation Status')}}</option>
                            <option value='{{\App\Models\Deposit::MANUAL}}' {{($search['automationStatus'] == \App\Models\Deposit::MANUAL) ? 'selected' : ''}}>{{__('Manual')}}</option>
                            <option value='{{\App\Models\Deposit::AUTOMATION}}' {{($search['automationStatus'] == \App\Models\Deposit::AUTOMATION) ? 'selected' : ''}}>{{__('Automation')}}</option>
                        </select>
                    </div>
                </div>
            @endif
            <div class="row mt-3">
                <div class="col-md-3">
                    <select name="processType" id="processType" class="selectpicker form-control">
                        <option value="">{{__('Please select')}}</option>
                        @foreach($withdraw_process_types as $key => $process_type)
                            <option
                                value="{{$key}}" {{ $search['processType'] == $key ? "selected" : '' }}>{{__($process_type)}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="row mb-4 mt-4 align-items-center justify-content-around justify-content-md-end">
        @if(\common\integration\BrandConfiguration::isAllowMassWithdrawalApproval())
        @php $filterBtnCls = 'col-lg-8'; @endphp
        <div class="col-lg-4">
            <div class="row align-items-center">
                <div class="col-6">
                    <select class="form-select">
                        @if(Auth::user()->hasPermissionOnAction( Config::get('constants.defines.APP_MERCHANT_WITHDRAWAL_APPROVE') ) )
                            <option>{{__("Approve")}}</option>
                        @endif
            {{--            @if(Auth::user()->hasPermissionOnAction( Config::get('constants.defines.APP_MERCHANT_WITHDRAWAL_REJECT') ) )
                            <option>{{__("Reject")}}</option>
                        @endif--}}
                    </select>
                </div>
                <div class="col-6">
                    <button type="button" id="withdraw_approve_reject_modal_button"
                            class="btn btn-sm btn-outline-primary btn-rounded">
                        {{__("Apply")}}
                    </button>
                </div>
                <div class="col-md-12">
                    <p class="text-danger mt-2 mb-0" id="bulk_select_error_message"></p>
                </div>
            </div>
        </div>
        @endif
        <div class="{{$filterBtnCls}} text-md-right">
            <div class="d-flex flex-wrap justify-content-around justify-content-md-end align-items-md-stretch align-items-md-center pl-3 pl-md-0">

                {{-- Cear Filters --}}
                <a href="{{isset($selfUrls) ? $selfUrls : ""}}"
                   class="text-center btn px-0">{{__("Clear Filters")}}</a>
                &nbsp;&nbsp;
                {{-- Cear Filters END --}}

                {{-- Submit Button --}}
                <button type="submit"
                        class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                    {{__("Submit")}}
                </button>
                {{-- Submit Button END --}}

                {{-- Col Hide Button --}}
                <button type="button"
                        class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto"
                        data-bs-toggle="modal"
                        data-bs-target="#colHideModal">
                    <span class="btn-icon">{{__("Col. Hide")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-eye"></i></span>
                </button>
                {{-- Col Hide Button END --}}

                {{-- Cashout check status button START --}}
                {{--@if(isset($allowFinflowCheckStatus) && $allowFinflowCheckStatus &&--}}
                {{--    Auth::user()->hasPermissionOnAction(config('constants.defines.APP_MERCHANT_WITHDRAWAL_CASHOUTCHECKSTATUS')))--}}
                {{--    <a id="checkStatusBtn"--}}
                {{--       href="javascript:void(0);"--}}
                {{--       class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">--}}
                {{--        <span class="btn-icon">{{__("Check Status")}}</span>--}}
                {{--    </a>--}}
                {{--@endif--}}
                {{-- Cashout check status button END --}}

                {{-- Export Button --}}
                @if(isset($exportBtnRouteName))
                    @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                        <div class="d-flex">
                        <div class="form-group ml-2">
                            <select class="form-control selectpicker show-tick rounded" name="file_type" id="file_type">
                                <option value="{{\App\Models\MerchantReportHistory::FORMAT_CSV}}">CSV</option>
                                <option value="{{\App\Models\MerchantReportHistory::FORMAT_XLS}}">XLS</option>
                                <option value="{{\App\Models\MerchantReportHistory::FORMAT_PDF}}">PDF</option>
                            </select>
                        </div>
                        <a id="exportbtn"
                           href="{{ isset($selfUrls) ? $selfUrls."/exports" : ""}}"
                           class="btn btn-outline-secondary btn-fix rounded mb-2 mb-md-auto">
                            <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i
                                    class="fa fa-caret-right"></i></span>
                        </a>
                        </div>
                    @endif
                @endif
                {{-- Export Button END --}}

                {{-- Cashout check status button Start --}}
                @if(isset($allowFinflowCheckStatus) && $allowFinflowCheckStatus)
                    <button id="check-status-button" type="button" data-transaction_type="{{ \common\integration\CashInOut::TYPE_WITHDRAW }}" form="bulk-action-form" class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">{{ __("Check Status") }}</button>
                @endif
                {{-- Cashout check status button End --}}

            </div>
        </div>
    </div>
    {{-- Buttons END --}}

</form>

@push('css')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.select2')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.alertify')

    <style>
            .custome-padding div.bs-actionsbox{
                width: 100% !important;
            }
            a.disabled {
                pointer-events: none;
                cursor: default;
            }
    </style>
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.alertify')

    <script>
        (function ($) {
            $.fn.selectpicker.defaults = {
                noneSelectedText: "{{__('Nothing selected')}}",
                selectAllText: "{{__('Select All')}}",
                deselectAllText: "{{__('Deselect All')}}"
            };
        })(jQuery);


        $('select[name=merchantid]').select2({ width: '100%' ,'border-radius':'5px',
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
            placeholder: "{{__($place_holder)}}",
            allowClear: true


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
            // console.log(fromurl);
            var search_form_url = $("#searchboxfrm").attr('action');
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
            $("#searchboxfrm").attr('action', search_form_url);
        });

    </script>
@endpush



