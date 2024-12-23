<form id="searchboxfrm" method="get" action="">
    <input type="hidden" name="search" value="search"/>
    <div class="row mb-4 font13">
        <div class="col-md-1 content-center">
            <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        <div class="col-md-11">
            <input name="date_range" type="hidden" value="{{$search['date_range']}}" id="date_range"/>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <select name="type" class="selectpicker form-control custome-padding2">
                        <option value="" {{$search['type'] == '' ? 'selected' : ''}} >{{__('Select Type')}}</option>
                        <option value="{{\common\integration\Models\CardTransaction::SEARCH_TYPE_SALE}}" {{$search['type'] == \common\integration\Models\CardTransaction::SEARCH_TYPE_SALE ? 'selected' : ''}}>{{__('Sale')}}</option>
                        <option value="{{\common\integration\Models\CardTransaction::SEARCH_TYPE_REVERSAL}}" {{$search['type'] == \common\integration\Models\CardTransaction::SEARCH_TYPE_REVERSAL ? 'selected' : ''}}>{{__('Reversal')}}</option>
                        <option value="{{\common\integration\Models\CardTransaction::SEARCH_TYPE_REFUND}}" {{$search['type'] == \common\integration\Models\CardTransaction::SEARCH_TYPE_REFUND ? 'selected' : ''}}>{{__('Refund')}}</option>
                        <option value="{{\common\integration\Models\CardTransaction::SEARCH_TYPE_LIMIT}}" {{$search['type'] == \common\integration\Models\CardTransaction::SEARCH_TYPE_LIMIT ? 'selected' : ''}}>{{__('Limit')}}</option>
                        <option value="{{\common\integration\Models\CardTransaction::SEARCH_TYPE_INQUIRY}}" {{$search['type'] == \common\integration\Models\CardTransaction::SEARCH_TYPE_INQUIRY ? 'selected' : ''}}>{{__('Inquiry')}}</option>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <input name="first_six_digits" type="text" value="{{$search['first_six_digits']}}" class="form-control rounded" placeholder="{{__('First Six Digits')}}" maxlength="6" onkeyup="limitInput(this, 6)">
                </div>
                <div class="col-md-2 mb-3">
                    <input name="last_four_digits" type="text" value="{{$search['last_four_digits']}}" class="form-control rounded" placeholder="{{__('Last Four Digits')}}" maxlength="4" onkeyup="limitInput(this, 4)">
                </div>
                <div class="col-md-2 mb-3">
                    <input name="tc_number" type="text" value="{{$search['tc_number']}}" class="form-control rounded" placeholder="{{__('TC Identify  Number')}}">
                </div>
                <div class="col-md-2 mb-3">
                    <input name="name" type="text" value="{{$search['name']}}" class="form-control rounded" placeholder="{{__('Name')}}">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-3">
                    <select name="card_type" class="selectpicker form-control custome-padding2">
                        <option value="" {{$search['card_type'] == '' ? 'selected' : ''}} >{{__('Select Card Type')}}</option>
                        <option value="{{\common\integration\Models\Card::CARD_VIRTUAL}}" {{$search['card_type'] == \common\integration\Models\Card::CARD_VIRTUAL ? 'selected' : ''}}>{{__('Virtual')}}</option>
                        <option value="{{\common\integration\Models\Card::CARD_PYHSICAL}}" {{$search['card_type'] == \common\integration\Models\Card::CARD_PYHSICAL ? 'selected' : ''}}>{{__('Physical')}} </option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <input name="transaction_id" type="text" value="{{$search['transaction_id']}}" class="form-control rounded" placeholder="{{__('Transaction Id')}}">
                </div>
                <div class="col-md-3 mb-3">
                    <input name="payment_id" type="text" value="{{$search['payment_id']}}" class="form-control rounded" placeholder="{{__('Payment ID')}}">
                </div>
                <div class="col-md-2 mb-3">
                    <input name="mcc" type="text" value="{{$search['mcc']}}" class="form-control rounded" placeholder="{{__('MCC')}}">
                </div>
            </div>

            <div class="row mt-4 text-md-right">
                <div class="col-md-2 col-sm-6 py-2">
                    <a>
                        <button type="submit" id="searchbtn"
                                class="btn btn-outline-primary btn-fix btn-rounded ml-2" id="pcm-search-btn">
                            {{__("Search")}} <i class="fa fa-caret-right"></i>
                        </button>
                    </a>
                </div>
                <div class="col-md-2 col-sm-6 py-2">
                    <a href="{{isset($selfUrls) ? $selfUrls : ""}}">
                        <button type="button" class="text-center btn btn-outline-danger btn-fix btn-rounded">
                            {{__("Clear Filters")}}
                        </button>
                    </a>
                </div>
                @if(isset($exportBtnRouteName))
                    @if(\Illuminate\Support\Facades\Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                        <div class="col-md-2 col-sm-6 py-2">
                            <a id="exportbtn" href="{{ isset($selfUrls) ? route(Config::get('constants.defines.APP_CARD_TRANSACTIONS_EXPORTS')) : ""}}">
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 rounded text-center">
                                    {{__("Export")}}
                                </button>
                            </a>
                        </div>
                    @endif
                @endif
            </div>

        </div>

    </div>

</form>

@push('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.bootstrap-select')

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
            "startDate": "{{isset($search['from_date']) ? $search['from_date'] : $search['from_date']}}",
            "endDate": "{{isset($search['to_date']) ? $search['to_date'] : $search['to_date']}}"
        });
        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#date_range").val(dateranges);
        });

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');
            var search_form_url = $("#searchboxfrm").attr('action');
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
            $("#searchboxfrm").attr('action', search_form_url);
        });
        function limitInput(element, maxLength) {
            let value = element.value;
            if (value.length > maxLength) {
                element.value = value.slice(0, maxLength);
            }
        }
    </script>
@endpush



