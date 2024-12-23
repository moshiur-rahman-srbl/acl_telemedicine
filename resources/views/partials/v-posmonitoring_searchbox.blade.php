<form id="searchboxfrm" method="get" action="">
    @include('fraud.v-posmonitoring.modals.col_hide')
    <div class="row font13">
        <div class="col-md-1 content-center">
            <span id="datepicker1" class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        <div class="col-md-11">
            <input type="hidden" value="{{isset($search['date_range']) ? $search['date_range'] : ""}}" id="date_range"
                   name="date_range"/>
            <div class="row">

                @if(!isset($search['view_mode_id']) || ( isset($search['view_mode_id']) &&  $search['view_mode_id'] == \common\integration\Models\SaleActiveFraud::VIEW_PASSIVE_MODE))
                    <div class="col-md-3 mt-4">
                        <input class="form-control number-only" name="sale_id" maxlength="11"
                            value="{{isset($search['sale_id']) ? $search['sale_id'] : ""}}" type="text"
                            placeholder="{{__("Sale ID")}}"/>
                    </div>
                @endif

                <div class="col-md-3 mt-4">
                    <input class="form-control number-only" name="score" maxlength="4"
                           value="{{isset($search['score']) ? $search['score'] : ""}}" type="text"
                           placeholder="{{__("Score")}}"/>
                </div>

                @if(!isset($search['view_mode_id']) || ( isset($search['view_mode_id']) &&  $search['view_mode_id'] == \common\integration\Models\SaleActiveFraud::VIEW_PASSIVE_MODE))
                <div class="col-md-3 mt-4">
                    <select name="status[]" class="selectpicker form-control custome-padding" data-title="{{__('Status')}}" data-actions-box="true" multiple>
                        @if(isset($status[1]))
                            @foreach($status as $key => $value)
                                <?php
                                $slctd = '';
                                if(is_array($search['status']) && in_array($key, $search['status'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$key}}" {{$slctd}}>{{__($value)}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                @endif

                <div class="col-md-3 mt-4">
                    <select name="rule_name[]" class="selectpicker form-control custome-padding" data-title="{{__('Rule Name')}}" data-actions-box="true" multiple>
                        @if(count($rules) > 0)
                            @foreach($rules as $rule)
                                <?php
                                $slctd = '';
                                if(is_array($search['rule_name']) && in_array($rule->rule_name, $search['rule_name'])) {
                                    $slctd = 'selected';
                                }
                                ?>
                                <option value="{{$rule->rule_name}}" {{$slctd}}>{{$rule->rule_name}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                @if(!isset($search['view_mode_id']) || ( isset($search['view_mode_id']) &&  $search['view_mode_id'] == \common\integration\Models\SaleActiveFraud::VIEW_ACTIVE_MODE))
                    <div class="col-md-3 mt-4">
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
                @endif
                <div class="col-md-12"></div>


                @if(!isset($search['view_mode_id']) || ( isset($search['view_mode_id']) &&  $search['view_mode_id'] == \common\integration\Models\SaleActiveFraud::VIEW_PASSIVE_MODE))
                <div class="col-md-3 mt-4">
                    <select name="payment_type" class=" form-control custome-padding" data-title="{{__('Payment Type')}}"  >

                        @if(isset($payment_type[0]))
                            @foreach($payment_type as $key => $value)

                                <option value="{{$key}}" {{$search['payment_type'] == $key ? 'selected' : ''}}>{{__($value)}}</option>
                            @endforeach
                        @endif

                    </select>
                </div>
                @endif

                <div class="col-md-3 mt-4">
                    <input class="form-control number-only" name="merchant_id" maxlength="11"
                        value="{{isset($search['merchant_id']) ? $search['merchant_id'] : ""}}" type="text"
                        placeholder="{{__("Merchant ID")}}"/>
                </div>

                <div class="col-md-3 mt-4">
                    <input class="form-control number-only" name="card_number" maxlength="11"
                        value="{{isset($search['card_number']) ? $search['card_number'] : ""}}" type="text"
                        placeholder="{{__("Last 4 Digit Card Number")}}"/>
                </div>

                <div class="col-md-3 mt-4">
                    <input class="form-control number-only" name="severity"
                        value="{{isset($search['severity']) ? $search['severity'] : ""}}" type="text"
                        placeholder="{{__("Severity")}}"/>
                </div>

                @if(!isset($search['view_mode_id']) || ( isset($search['view_mode_id']) &&  $search['view_mode_id'] == \common\integration\Models\SaleActiveFraud::VIEW_PASSIVE_MODE))
                    <div class="col-md-3 mt-4">
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
                @endif
                <div class="col-md-3 mt-4">
                    <div class="input-group-icon input-group-icon-right">
                        <select name="view_mode_id" class="form-control" id="view_mode_id">
                            @foreach ($view_mode as $key => $mode)
                                <option value="{{ $key }}" {{ isset($search['view_mode_id']) && $search['view_mode_id'] == $key ? 'selected' : '' }}>{{ __($mode) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="row mb-4 mt-4">
        <div class="col-12 text-md-right">
            <div class="d-flex flex-column flex-md-row justify-content-around justify-content-md-end align-items-sm-stretch align-items-md-center pr-3 pl-3 pl-md-0">

                {{-- Cear Filters --}}
                <a href="{{isset($submitUrl) ? route($submitUrl) : ''}}"
                   class="text-center">{{__("Clear Filters")}}</a>
                &nbsp;&nbsp;
                {{-- Cear Filters END --}}

                {{-- Submit Button --}}
                <button type="submit"
                        id="submitBtn"
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
                @if(isset($exportUrl))
                    @if(\Illuminate\Support\Facades\Auth::user()->hasPermissionOnAction($exportUrl))

                        @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'fileChooseExportOptionForVPosMonitoring']))
                            <div class="form-group ml-1 mr-1">
                                <select class="form-control selectpicker show-tick rounded" name="file_type" id="file_type">
                                    <option value="{{\App\Models\MerchantReportHistory::FORMAT_CSV}}">CSV</option>
                                    <option value="{{\App\Models\MerchantReportHistory::FORMAT_XLS}}">XLS</option>
                                </select>
                            </div>
                        @endif

                        <a id="exportBtn"
                           href="{{ route($exportUrl) }}"
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
    @include('partials.js_blade.validate')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.bootstrap-select')
    <script>

        (function ($) {
            $.fn.selectpicker.defaults = {
                noneSelectedText: "{{__('Nothing selected')}}",
                selectAllText: "{{__('Select All')}}",
                deselectAllText: "{{__('Deselect All')}}",
                noneResultsText: '{{__('No results matched')}} {0}',
            };
        })(jQuery);

        $('#datepicker1').daterangepicker({
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

        $('#datepicker1').on('hide.daterangepicker', function (ev, picker) {
            //do something, like clearing an input
            //console.log(picker.startDate.format('YYYY/MM/DD'));
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#date_range").val(dateranges);
        });

        $("#exportBtn").on("click", function (event) {
            event.preventDefault();
            $("#searchboxfrm").attr('action', '{{isset($exportUrl) ? route($exportUrl) : ""}}');
            $("#searchboxfrm").submit();
        });

        $("#submitBtn").on("click", function (event) {
            event.preventDefault();
            $("#searchboxfrm").attr('action', '{{isset($submitUrl) ? route($submitUrl) : ""}}');
            $("#searchboxfrm").submit();
        });

        // Number only check
        $(".number-only").keypress(function(e){
            var keyCode = e.which;
            if (keyCode < 48 || keyCode > 57) {
                return false;
            }
        });

        $('#view_mode_id').on('change', function () {
            var previous_url = "{!! route(Config::get('constants.defines.FRAUD_TRANSACTION_LIST')) !!}";

            @if(!empty($dpl))
                previous_url = "{!! route(Config::get('constants.defines.FRAUD_TRANSACTION_LIST_DPL')) !!}";
            @elseif(!empty($physical_pos))
                previous_url = "{!! route(Config::get('constants.defines.FRAUD_TRANSACTION_LIST_PHYSICAL_POS')) !!}";
            @elseif(!empty($wallet))
                previous_url = "{!! route(Config::get('constants.defines.FRAUD_TRANSACTION_LIST_WALLET')) !!}";
            @endif
            var vpos_monitoring_url = previous_url+'?view_mode_id='+$(this).val();
            window.location.href = vpos_monitoring_url;
        });
    </script>
@endpush



