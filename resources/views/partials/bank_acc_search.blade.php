<form method="get" action="{{route(Config::get('constants.defines.APP_BANK_ACCOUNTS_INDEX'))}}">
    <div class="row font13">
        <div class="col-md-1 content-center">
            <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        <div class="col-md-11">
            <input type="hidden" value="{{isset($search['date_range']) ? $search['date_range'] : ""}}" id="date_range"
                   name="date_range"/>
            <div class="row">
                <div class="col-md-3">
                    <select name="status" class="selectpicker form-control custome-padding2">
                        @foreach($status_list as $key=>$value)
                            <option {{$search['status'] == $key ? 'selected' : ''}} value="{{$key}}">{{__($value)}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-control selectpicker"
                            name="merchant_id[]"
                            multiple
                            data-actions-box="true"
                            data-live-search="true"
                            data-title="{{ __('Merchant Name') }}"
                            data-virtual-scroll="true"
                            data-style="''"
                            data-style-base="form-control"
                    >
                        @foreach($merchant_list as $merchant)
                            <option value="{{ $merchant->id }}"
                                {{ !empty($search['merchant_id']) && in_array($merchant->id, $search['merchant_id']) ? 'selected':''}}
                            >{{ $merchant->name . ' (' . $merchant->id . ')' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    @if(isset($bank_list))
                        <select name="static_bank_id[]" class="selectpicker form-control custome-padding" data-title="{{__("Bank Name")}}" data-actions-box="true" multiple>
                            {{--                            <option value="" disabled="disabled">{{__('Please select')}}</option>--}}
                            @foreach($bank_list as $bank)
                                <?php
                                $slctd = '';
                                if(is_array($search['static_bank_id']) && in_array($bank->id, $search['static_bank_id'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$bank->id}}" {{$slctd}}>{{$bank->name}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-3">
                    <input class="form-control" name="account_no"
                           value="{{isset($search['account_no']) ? $search['account_no'] : ""}}" type="text"
                           placeholder="{{__("Account No")}}"/>
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-3">
                    <input class="form-control" name="iban"
                           value="{{isset($search['iban']) ? $search['iban'] : ""}}" type="text"
                           placeholder="{{__("IBAN")}}"/>
                </div>
                <div class="col-md-3">
                    @if(isset($currency_list))
                        <select name="currency_id[]" class="selectpicker form-control custome-padding" data-title="{{__("Currency")}}" data-actions-box="true" multiple>
                            {{--                            <option value="" disabled="disabled">{{__('Please select')}}</option>--}}
                            @foreach($currency_list as $currencyObj)
                                <?php
                                $slctd = '';
                                if(is_array($search['currency_id']) && in_array($currencyObj->id, $search['currency_id'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$currencyObj->id}}" {{$slctd}}>{{__($currencyObj->name)}}</option>
                            @endforeach
                        </select>
                    @endif
                </div>
                <div class="col-md-3">
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
                <div class="col-md-1 pt-3">
                    <a href="{{route(Config::get('constants.defines.APP_BANK_ACCOUNTS_INDEX'))}}"
                       class="float-right">{{__("Clear Filters")}}</a>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-outline-primary float-right btn-fix btn-rounded">
                        {{__("Submit")}}
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>
@push('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.select2')
    <style>
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
    @include('partials.js_blade.select2')
    <script>
        (function ($) {
            $.fn.selectpicker.defaults = {
                noneSelectedText: "{{__('Nothing selected')}}",
                selectAllText: "{{__('Select All')}}",
                deselectAllText: "{{__('Deselect All')}}"
            };
        })(jQuery);
        $('select[name=merchant_id]').select2({ width: '100%' ,'border-radius':'5px',
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
            placeholder: "{{__('Merchant Name')}}",
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
            $("#date_range").val(dateranges);
        });


    </script>
@endpush



