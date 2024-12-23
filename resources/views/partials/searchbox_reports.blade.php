<form id="searchboxfrm" method="get" action="{{route(Config::get('constants.defines.APP_REPORTS_INDEX'))}}">
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
                    <div class="input-group-icon input-group-icon-right">
                        <input class="form-control"
                               value="{{ isset($search['username'])? $search['username'] : ""}}" type="text"
                               name="username"
                               placeholder="{{__('User Name')}}"/>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="input-group-icon input-group-icon-right">
                        <input class="form-control"
                               value="{{ isset($search['merchantname'])? $search['merchantname'] : ""}}" type="text"
                               name="merchantname"
                               placeholder="{{__('Merchant Name')}}"/>
                    </div>
                </div>
                <div class="col-md-3">
                    @if(isset($transactionStateList))
                        <select name="transactionState[]"
                                class="selectpicker form-control custome-padding"
                                data-title="{{__("Status")}}"
                                data-actions-box="true"
                                multiple>
                            @foreach( $transactionStateList as $key => $value )
                                <?php
                                $slctd = '';
                                if(is_array($search['transactionState']) && in_array($key, $search['transactionState'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{ $key }}" {{ $slctd }}>{{ __($value) }}</option>
                            @endforeach
                                <option {{ is_array($search['transactionState']) && in_array(\App\Models\TransactionState::PROVISION , $search['transactionState']) ? 'selected': '' }} value="{{ \App\Models\TransactionState::PROVISION }}">{{__('Pre-Authorization')}}</option>
                        </select>
                    @endif
                </div>
            </div>
            <div class="row mt-3">
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
                {{-- Currency Selector END --}}
            </div>
        </div>
    </div>
    <div class="row mb-4 mt-3">
        <div class="col-md-12 text-right">
            <a href="{{route(Config::get('constants.defines.APP_REPORTS_INDEX'))}}">{{__("Clear Filter")}}</a>
            &nbsp;&nbsp;
            <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded ml-2">
                {{__("Submit")}}
            </button>

            <button type="button" class="btn btn-outline-secondary btn-fix btn-rounded ml-2" data-bs-toggle="modal" data-bs-target="#colHideModal">
                <span class="btn-icon">{{__("Col. Hide")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-eye"></i></span>
            </button>
            {{--@if(isset($exportBtnRouteName))--}}
            {{--@if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))--}}

            {{--@endif--}}
            {{--@endif--}}

        </div>
    </div>

</form>
<div class="row justify-content-end">
    <div class="col-md-3 text-end">
        <form action="{{ route(Config::get('constants.defines.APP_REPORTS_EXPORT')) }}" method="post" class="pull-right mb-2">
            @include('reports.colHideModal')
            @csrf
            <input type="hidden" id="hidden_daterange" name="daterange" value="{{ $search['daterange'] }}">
            <input type="hidden" id="hidden_customergsm" name="customergsm" value="{{ $search['customergsm'] }}">
            <input type="hidden" id="hidden_minamount" name="minamount" value="{{ $search['minamount'] }}">
            <input type="hidden" id="hidden_maxamount" name="maxamount" value="{{ $search['maxamount'] }}">
            <input type="hidden" id="hidden_searchkey" name="searchkey" value="{{ $search['searchkey'] }}">
            <input type="hidden" id="hidden_transid" name="transid" value="{{ $search['transid'] }}">
            <input type="hidden" id="hidden_merchantname" name="merchantname" value="{{ $search['merchantname'] }}">
            @if(!empty($search['currencies']))
                @foreach($search['currencies'] as $currency_key => $currency_id)
                    <input type="hidden" id="hidden_currencies_{{ $currency_key }}" name="currencies[]" value="{{ $currency_id }}">
                @endforeach
            @else
                <input type="hidden" id="hidden_currencies" name="currencies" value="">
            @endif
            @if(!empty($search['pos_id']))
                @foreach($search['pos_id'] as $pos_id_key => $pos_id)
                    <input type="hidden" id="hidden_pos_id_{{ $pos_id_key }}" name="pos_id[]" value="{{ $pos_id }}">
                @endforeach
            @else
                <input type="hidden" id="hidden_pos_id" name="pos_id" value="">
            @endif
            @if(!empty($search['transactionState']))
                @foreach($search['transactionState'] as $state_key => $state)
                    <input type="hidden" id="hidden_transstates_{{ $state_key }}" name="transactionState[]" value="{{ $state }}">
                @endforeach
            @else
                <input type="hidden" id="hidden_transstates" name="transactionState" value="">
            @endif
            <button type="submit"
                    class="btn btn-outline-secondary btn-fix btn-rounded ml-2">
                <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i></span>
            </button>
        </form>
    </div>
</div>

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
    @include('partials.js_blade.validate')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.daterangepicker')
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


        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');
            console.log(fromurl);
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
            $("#searchboxfrm").attr('action', "{{route(Config::get('constants.defines.APP_REPORTS_INDEX'))}}");
        });
    </script>
@endpush



