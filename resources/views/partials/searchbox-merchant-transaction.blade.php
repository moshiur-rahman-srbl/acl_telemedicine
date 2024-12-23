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

@endphp

<form id="searchboxfrm" method="get" action="{{Request::url()}}">
    <input type="hidden" name="search" value="search"/>
    <input type="hidden" name="merchantid" value="{{$merchantid}}"/>
    @if(isset($route_permission) && !empty($route_permission))
        <input type="hidden" name="route_permission" value="{{ $route_permission }}"/>
    @endif
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
                    <input class="form-control" name="transid"
                           value="{{isset($search['transid']) ? $search['transid'] : ""}}" type="text"
                           placeholder="{{__("Trans. ID")}}"/>
                </div>
                <div class="col-md-3">
                    @if(isset($type) && !empty($type))
                        <select name="typeid" class="selectpicker form-control">
                            <option value="">{{__("Type")}}</option>
                            <option {{$search['typeid'] == 1 ? 'selected':''}} value="1">{{__("Bank")}}</option>
                            <option {{$search['typeid'] == 2 ? 'selected':''}} value="2">{{__("Wallet")}}</option>
                        </select>
                    @else
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
                    @endif
                </div>

                <div class="col-md-3">
                    @if(isset($transactionStateList))
                        <select name="transactionState[]" class="selectpicker form-control custome-padding" data-title="{{__("Status")}}" data-actions-box="true" multiple>
                            {{--                            <option value="" disabled="disabled">{{__('Please select')}}</option>--}}
                            @foreach($transactionStateList as $key=>$value)
                                <?php
                                $slctd = '';
                                if(is_array($search['transactionState']) && in_array($key, $search['transactionState'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$key}}" {{$slctd}}>{{__($value)}}</option>
                            @endforeach
                            <option {{ is_array($search['transactionState']) && in_array(\App\Models\TransactionState::PROVISION , $search['transactionState']) ? 'selected': '' }} value="{{ \App\Models\TransactionState::PROVISION }}">{{__('Pre-Authorization')}}</option>
                        </select>
                    @endif
                </div>
                <div class="col-md-3">
                    <input name="dpl_id" id="dpl_id" class="form-control" value="{{$search['dpl_id']}}"
                           type="text"
                           placeholder="{{__('DPL ID')}}">
                </div>
                @if(isset($table_types) && !empty($table_types))
                   <div class="col-md-3 mt-3">
                        <select name="backup_table_type" class="form-control" data-title="{{ __("Backup Type") }}"
                                data-style-base="form-control">
                            @foreach($table_types as $key => $table_type)
                                <option
                                    value="{{ $key }}"{{ $key == $search["backup_table_type"] ? 'selected':''}}>{{ __($table_type) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                @if(\common\integration\BrandConfiguration::allow2D3DCvvLessFiltter() && isset($filter_2d_3d_cvvless) && !empty($filter_2d_3d_cvvless))
                    <div class="col-md-3 mt-3">
                        <select name="filter_2d_3d_cvvless[]"
                                class="selectpicker form-control custome-padding"
                                data-title="{{ __("Payment Type") }}"
                                data-actions-box="true"
                                multiple

                        >
                            @foreach($filter_2d_3d_cvvless as $key => $val)
                                <option value="{{ $key }}"
                                    {{ !empty($search['filter_2d_3d_cvvless']) && in_array($key, $search['filter_2d_3d_cvvless']) ? 'selected':''}}
                                >{{ __($val) }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'allowFilterByTransactionPosType']) && isset($transactionPostTypes))
                    <div class="col-md-3 mt-3">
                        <select name="pos_transaction_type[]"
                                class="selectpicker form-control"
                                data-title="{{ __("Transaction POS Type") }}"
                                data-actions-box="true"
                                data-live-search="true"
                                data-virtual-scroll="true"
                                multiple
                                data-style="''"
                                data-style-base="form-control"
                        >
                            @foreach($transactionPostTypes as $key => $value)
                                <option value="{{$key}}" @selected(common\integration\Utility\Arr::isAMemberOf($key, $search['pos_transaction_type']))>{{__($value)}}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
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

        </div>
    </div>
    <div class="row mb-4 mt-3">
        <div class="col-md-12 text-right">
         <div class="d-flex flex-column flex-md-row pull-right pr-2 pl-md-0">
            <a href="{{isset($selfUrls) ? $selfUrls : ""}}" class="text-center mt-3">{{__("Clear Filters")}}</a>
            <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded ml-2">
                {{__("Submit")}}
            </button>

            @if(isset($exportBtnRouteName))
                @if(auth()->user()->hasPermissionOnAction($exportBtnRouteName))

                    <div class="form-group ml-1 mr-1">
                        <select class="form-control selectpicker show-tick rounded" name="file_type" id="file_type">
                            <option value="{{\App\Models\MerchantReportHistory::FORMAT_CSV}}">CSV</option>
                            <option value="{{\App\Models\MerchantReportHistory::FORMAT_XLS}}">XLS</option>
                            <option value="{{\App\Models\MerchantReportHistory::FORMAT_PDF}}">PDF</option>
                        </select>
                    </div>


                    <a id="exportbtn" href="{{ route($exportBtnRouteName)}}"
                       class="btn btn-outline-secondary btn-fix mb-2 mb-md-auto">
                        <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i></span>
                    </a>

                    <div class="col- ml-2 mb-3">
                        <a class="btn btn-info rounded"
                           href="{{route(Config::get('constants.defines.APP_EXPORT_REPORTS_HISTORY_INDEX'))}}">
                            {{__('Export Report History')}}</a>
                    </div>
                @endif
            @endif
         </div>
        </div>
    </div>

</form>

@push('css')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.select2')
    @include('partials.css_blade.daterangepicker')

    <style>
        .custome-padding div.bs-actionsbox{
            width: 100% !important;
        }
    </style>
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.daterangepicker')

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
                  //console.log('data',data);
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
            //console.log(picker.startDate.format('YYYY/MM/DD'));
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#daterange").val(dateranges);
        });

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');

            var msg = "<strong class='p-0 m-0 d-block'>" + "{{__('Are you sure?')}}" + "</strong>";
            msg += "<p class='p-0 m-0 pl-4 pr-4 text-left'>" + "{{__('The exported report file will be generated and link of it will be sent via email')}}" + ".</p>";

            alertify.confirm(msg, function () {
                $('#exportbtn').html('<i class="fa fa-spin fa-refresh"></i>');
                // console.log(fromurl);
                $("#searchboxfrm").attr('action', fromurl);
                $("#searchboxfrm").submit();
            }, function () {
                return false;
            });

            $('button.ok').addClass('btn btn-danger rounded').text("<?php echo e(__('Okay')); ?>");
            $('button.cancel').addClass('btn btn-light rounded').text("<?php echo e(__('Cancel')); ?>");
        });


    </script>
@endpush



