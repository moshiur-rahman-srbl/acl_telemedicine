@php
    if ($money_transfer == 'mt') {
       $field_name = "username";
       $place_holder = "User Name";
    }
    else{
        $field_name = "merchantname";
        $place_holder = "Merchant Name";
    }
    $hide_deposite_by_cc = '';
@endphp

<form id="searchboxfrm" method="{{$search['form_submit_method'] ?? 'get'}}" action="{{$selfUrls}}">
    @csrf
    {{-- Col Hide Modal --}}
    @if(isset($search['send_type']))
        @if($search['send_type'] == \App\Models\Send::SENDMONEY || $search['send_type'] == \App\Models\Send::B2BPAYMENT)
            <?php $hide_deposite_by_cc = $search['send_type'] == \App\Models\Send::B2BPAYMENT ? 'd-none' : '';?>
            @include('moneytransfer.modals.col_hide')
        @elseif($search['send_type'] == \App\Models\Send::SENDMONEYTOMERCHANT)
            {{-- // --}}
        @elseif($search['send_type'] == \App\Models\Send::TOBANK)
            {{-- // --}}
        @elseif($search['send_type'] == \App\Models\Send::SENDMONEYTONONSIPAY)
            {{-- // --}}
        @endif
    @endif
    {{-- Col Hide Modal --}}

    <input type="hidden" name="search" value="search"/>
    <input type="hidden" name="page_limit" value="{{$page_limit}}">
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

                <div class="col-md-3"
                    @if(isset($money_transfer))
                        @if($money_transfer == 'mt')
                            style="display:none;"
                        @else
                            style="display:inline;"
                        @endif
                    @endif
                >
                    <select class="form-control selectpicker"
                            multiple
                            data-actions-box="true"
                            data-live-search="true"
                            data-title="{{__("Merchant Name")}}"
                            name="merchantid[]"
                            data-virtual-scroll="true"
                            data-style="''"
                            data-style-base="form-control"
                    >
                        {{--<option value="" disabled="disabled">{{__('Status')}}</option>--}}
                        @if(isset($merchantList[1]))
                            @foreach($merchantList as $merchant)
                                <?php
                                $slctd = '';
                                if(is_array($search['merchantid']) && in_array($merchant->user_id, $search['merchantid'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$merchant->user_id}}" {{$slctd}}>{{ $merchant->name . ' (' . $merchant->id . ')' }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                {{-- CUSTOMER NUMBER --}}
                <div class="col-md-3"
                    @if(isset($money_transfer))
                        @if($money_transfer == 'mt')
                            style="display:inline;"
                        @else
                            style="display:none;"
                        @endif
                    @endif
                >
                    <div class="input-group-icon input-group-icon-right">
                        <input class="form-control"
                               value="{{isset($search['customer_number']) ? $search['customer_number'] : ""}}" type="text"
                               name="customer_number"
                               placeholder="{{__("Customer Number/TCKN")}}"/>
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="transactionState[]" class="selectpicker form-control custome-padding" data-title="{{__('Status')}}" data-actions-box="true" multiple>
                        {{--<option value="" disabled="disabled">{{__('Status')}}</option>--}}
                        @if(isset($transactionStateList[1]))
                            @foreach($transactionStateList as $key => $value)
                                <?php
                                $slctd = '';
                                if(is_array($search['transactionState']) && in_array($key, $search['transactionState'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$key}}" {{$slctd}}>{{__($value)}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                @if($search['send_type'] != \App\Models\Send::B2BPAYMENT)
                    <div class="col-md-3 {{$search['send_type']?'':'d-none'}}">
                        <select name="send_type" class="selectpicker form-control">
                            <option value=''>{{__('Type')}}</option>
                            @forelse(\App\Models\Send::getSendTypes() as $key=>$value)
                                <option value='{{$key}}' {{($search['send_type']==$key) ? 'selected' : ''}}>{{__($value)}}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                @endif
                @if(isset($is_allow_receiver_filter_in_B2B) && $is_allow_receiver_filter_in_B2B)
                    <div class="col-md-3">
                        <select class="form-control selectpicker"
                                data-actions-box="true"
                                data-live-search="true"
                                data-title="{{__("Receiver Merchant Name")}}"
                                name="to_user_id"
                                data-virtual-scroll="false"
                                data-style="''"
                                data-style-base="form-control">
                                <option value="">{{__("Please Select")}}</option>
                                @foreach($merchantList as $merchant)
                                     <option value="{{$merchant->user_id}}" {{ $search['to_user_id'] == $merchant->user_id ? 'selected' : '' }}>{{ $merchant->name . ' (' . $merchant->id . ')' }}</option>
                                @endforeach
                                </select>
                    </div>
                @endif
            </div>
            <div class="row mt-3 ml-0 mt-2 {{$hide_deposite_by_cc}}">
                <label class="checkbox checkbox-ebony">
                    <input name="is_cc_payment" value="1" {{isset($search['is_cc_payment']) ? 'checked':''}} id="is_cc_payment" type="checkbox" class="bulk-action">
                    <span class="input-span"></span>
                </label>
                <span>{{__("Deposit By CC")}}</span>
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="row mb-4 mt-4">
        <div class="col-12 text-md-right">
            <div class="d-flex flex-column flex-md-row justify-content-around justify-content-md-end align-items-sm-stretch align-items-md-center pr-3 pl-3 pl-md-0">

                {{-- Cear Filters --}}
                <a href="{{isset($selfUrls) ? $selfUrls : ""}}"
                   class="text-center">{{__("Clear Filter")}}</a>
                &nbsp;&nbsp;
                {{-- Cear Filters END --}}

                {{-- Submit Button --}}
                <button type="button" id="form-submit-btn"
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
                           href="{{ isset($selfUrls) ? $selfUrls."/exports" : ""}}"
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
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.daterangepicker')
    <style>
        .custome-padding div.bs-actionsbox{
            width: 100% !important;
        }
    </style>
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.validate')
    <script>

        (function ($) {
            $.fn.selectpicker.defaults = {
                noneSelectedText: "{{__('Nothing selected')}}",
                selectAllText: "{{__('Select All')}}",
                deselectAllText: "{{__('Deselect All')}}",
                noneResultsText: '{{__('No results matched')}} {0}',
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

        $("#form-submit-btn").on("click", function (event) {
            event.preventDefault();
            var fromurl = '{{isset($selfUrls) ? $selfUrls : ""}}';
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
        });

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
        });
    </script>
@endpush



