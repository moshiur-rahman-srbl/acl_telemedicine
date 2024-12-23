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

<form id="searchboxfrm" method="get" action="{{ Request::url() }}">
    @include('failed_transaction.modals.col_hide')
    <input type="hidden" name="search" value="search"/>
    <div class="d-flex justify-content-around align-items-center flex-column flex-md-row font13 w-100 row mb-2 mx-0">
        {{-- Date Picker --}}
        <div class="col col-lg-1 col-md-2 mb-2 m-md-0 text-center">
            <span id="dateprangepicker"
                  class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        {{-- Date Picker END --}}
        <div class="col col-lg-11 col-md-12">
            <input type="hidden"
                   value="{{isset($search['daterange']) ? $search['daterange'] : ""}}"
                   id="daterange"
                   name="from_date"/>
            <div class="w-100">
                <div class="row">
                    {{-- Customer GSM --}}
                    <div class="col-md-3 my-2">
                        <input class="form-control"
                               name="customergsm"
                               value="{{ isset($search['customergsm']) ? $search['customergsm'] : "" }}"
                               type="text"
                               placeholder="{{ __("Customer GSM") }}"/>
                    </div>
                    {{-- Customer GSM END --}}

                    {{-- Minamount --}}
                    <div class="col-md-3 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-long-arrow-down"></i>
                            </span>
                            <input class="form-control"
                                   type="text"
                                   value="{{ isset($search['minamount']) ? $search['minamount'] : "" }}"
                                   name="minamount"
                                   placeholder="{{ __("Min Amount") }}"/>
                        </div>
                    </div>
                    {{-- Minamount END --}}

                    {{-- Maxamount --}}
                    <div class="col-md-3 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-long-arrow-up"></i>
                            </span>
                            <input class="form-control"
                                   type="text"
                                   value="{{ isset($search['maxamount']) ? $search['maxamount'] : "" }}"
                                   name="maxamount"
                                   placeholder="{{ __("Max Amount") }}"/>
                        </div>
                    </div>
                    {{-- Maxamount END --}}

                    {{-- Search Field --}}
                    <div class="col-md-3 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-search"></i>
                            </span>
                            <input class="form-control-rounded form-control"
                                   value="{{ isset($search['searchkey']) ? $search['searchkey'] : "" }}"
                                   type="text"
                                   name="searchkey"
                                   placeholder="{{ __("Search") }} ..."/>
                        </div>
                    </div>
                    {{-- Search Field END --}}
                </div>
                <div class="row">
                    {{-- POS ID Selector --}}
                    <div class="col-md-3 my-2">
                        <select class="form-control selectpicker"
                                name="pos_id[]"
                                multiple
                                data-actions-box="true"
                                data-live-search="true"
                                data-virtual-scroll="true"
                                data-title="{{ __("POS") }}"
                                data-style="''"
                                data-style-base="form-control"
                        >
                            @foreach($poses as $pos)
                                <option value="{{ $pos->pos_id }}"
                                    {{ !empty($search['pos_id']) && in_array($pos->pos_id, $search['pos_id']) ? 'selected':''}}>{{$pos->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- POS ID Selector END --}}

                    {{-- Trans ID --}}
                    <div class="col-md-3 my-2">
                        <input class="form-control"
                               name="transid"
                               value="{{isset($search['transid']) ? $search['transid'] : ""}}"
                               type="text"
                               placeholder="{{__("Trans. ID")}}"/>
                    </div>
                    {{-- Trans ID END --}}

                    <div class="col-md-3 my-2">
                        @if(isset($type) && !empty($type))
                            {{-- Type --}}
                            <select name="typeid"
                                    class="selectpicker form-control"
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                <option value="">{{__("Type")}}</option>
                                <option {{$search['typeid'] == 1 ? 'selected':''}}
                                        value="1">{{__("Bank")}}</option>
                                <option {{$search['typeid'] == 2 ? 'selected':''}}
                                        value="2">{{__("Wallet")}}</option>
                            </select>
                            {{-- Type END --}}
                        @else
                            @if(isset($paymentMethods))
                                {{-- Payment Method --}}
                                <select name="paymentmethodid[]"
                                        class="selectpicker form-control"
                                        data-title="{{ __("Payment Method") }}"
                                        data-actions-box="true"
                                        data-live-search="true"
                                        data-virtual-scroll="true"
                                        multiple
                                        data-style="''"
                                        data-style-base="form-control"
                                >
                                    @foreach($paymentMethods as $paymentMethod)
                                        <?php
                                        $slctd = '';
                                        if(is_array($search['paymentmethodid']) && in_array($paymentMethod->id, $search['paymentmethodid'])) {
                                            $slctd = 'selected';
                                        }
                                        ?>
                                        <option value="{{$paymentMethod->id}}"
                                            {{$slctd}}>{{__($paymentMethod->name)}}</option>
                                    @endforeach
                                </select>
                                {{-- Payment Method END --}}
                            @endif
                        @endif
                    </div>

                    <div class="col-md-3 my-2">
                        @if(isset($isAlltransaction) && $isAlltransaction)
                            {{-- Merchant --}}
                            <select class="form-control selectpicker"
                                    name="merchantid[]"
                                    multiple
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-title="{{ __($place_holder) }}"
                                    data-virtual-scroll="true"
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach($merchantList as $merchant)
                                    <option value="{{ $merchant->id }}"
                                        {{ !empty($search['merchantid']) && in_array($merchant->id, $search['merchantid']) ? 'selected':''}}
                                    >{{ $merchant->name . ' (' . $merchant->id . ')' }}</option>
                                @endforeach
                            </select>
                            {{-- Merchant END --}}
                        @else
                            {{-- User --}}
                            <div class="input-group-icon input-group-icon-right">
                                <input class="form-control"
                                       value="{{ isset($search[$field_name] )? $search[$field_name] : ""}}"
                                       type="text"
                                       name="{{$field_name}}"
                                       placeholder="{{__($place_holder)}}"/>
                            </div>
                            {{-- User END --}}
                        @endif

                    </div>
                </div>
                <div class="row">
                    {{-- Currency Selector --}}
                    <div class="col-md-3 my-2">
                        <select name="currencies[]"
                                class="selectpicker form-control"
                                data-title="{{ __("Currency") }}"
                                data-actions-box="true"
                                data-live-search="true"
                                data-virtual-scroll="true"
                                multiple
                                data-style="''"
                                data-style-base="form-control"
                        >
                            @foreach($currencies as $currency)
                                <option value="{{ $currency->id }}"
                                        title={{ $currency->code }}
                                    {{ !empty($search['currencies']) && in_array($currency->id, $search['currencies']) ? 'selected':'' }}>{{ __($currency->name).' ('.$currency->symbol.')' }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Currency Selector END --}}
                    @if(\common\integration\BrandConfiguration::allowMerchantTypeFilter())
                        <div class="col-md-3 my-2">
                        @if(isset($merchant_type))
                            <select class="form-control selectpicker"
                                    multiple
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-title="{{__("Search by Merchant Type")}}"
                                    name="merchant_type[]"
                                    data-virtual-scroll="true"
                                    id="merchant_type"
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach($merchant_type as $key => $value)
                                    <option value="{{ $key }}" {{ !empty($search['merchant_type']) && in_array($key, $search['merchant_type']) ? 'selected':''}}>{{ $value }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    @endif
                </div>
                @if(isset($search['paymentmethodid']) && $search['paymentmethodid']==\App\Models\DepositeMethod::EFT)
                    <div class="row">
                        <div class="col-md-3">
                            <select name="automationStatus"
                                    class="selectpicker form-control"
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                <option value='' {{($search['automationStatus'] == '') ? 'selected' : ''}}>{{__('Automation Status')}}</option>
                                <option value='{{\App\Models\Deposit::MANUAL}}' {{($search['automationStatus'] == \App\Models\Deposit::MANUAL) ? 'selected' : ''}}>{{__('Manual')}}</option>
                                <option value='{{\App\Models\Deposit::AUTOMATION}}' {{($search['automationStatus'] == \App\Models\Deposit::AUTOMATION) ? 'selected' : ''}}>{{__('Automation')}}</option>
                            </select>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    {{-- Buttons --}}
    <div class="row mb-4 mt-4">
        <div class="col-12 text-md-right">
            <div class="d-flex flex-column flex-md-row justify-content-around justify-content-md-end align-items-sm-stretch align-items-md-center pr-3 pl-3 pl-md-0">

                {{-- Cear Filters --}}
                <a href="{{ isset($selfUrls) ? $selfUrls : "" }}"
                   class="text-center">{{__("Clear Filters")}}</a>
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

                {{-- Export Button --}}
                @if(isset($exportBtnRouteName))
                    @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                        <a id="exportbtn"
                           href="{{route(config('constants.defines.APP_FAILED_TRANSACTION_EXPORT'))}}"
                           class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                            <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i
                                    class="fa fa-caret-right"></i></span>
                        </a>
                    @endif
                @endif
                {{-- Export Button END --}}

            </div>
        </div>
    </div>
    {{-- Buttons END --}}
</form>

@push('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')

    <style>
        /* .form-control {
            padding-top: inherit; !important;
            padding-bottom: inherit; !important;
        } */

        .bootstrap-select.btn-group .dropdown-menu {
            width: 100%; !important;
        }

        .custome-padding div.bs-actionsbox{
            width: 100% !important;
        }
    </style>
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.bootstrap-select')

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

        $('.selectpicker').selectpicker();
        $('.text').addClass('text-wrap');
    </script>
@endpush



