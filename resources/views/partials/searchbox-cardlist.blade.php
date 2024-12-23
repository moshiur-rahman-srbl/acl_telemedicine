<form id="searchboxfrm" method="get" action="">
    <input type="hidden" name="search" value="search"/>
    <div class="row mb-4 font13">
        <div class="col-md-1 content-center">
            <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        <input name="date_range" type="hidden" value="{{$search['date_range']}}" id="date_range"/>
        <div class="col-md-3">
            <select name="currency_id" class="selectpicker form-control custome-padding2">
                <option value="">{{__('Select Currency')}}</option>
                @foreach($currencies as $currency)
                    <option value="{{$currency->id}}" {{$search['currency_id'] == $currency->id ? 'selected' : ''}}>{{$currency->code}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="request_type" class="selectpicker form-control custome-padding2">
                <option value="" {{$search['request_type'] == '' ? 'selected' : ''}} >{{__('Select Type')}}</option>
                <option value="{{\common\integration\Models\Card::CARD_ACTIVE}}" {{$search['request_type'] == \common\integration\Models\Card::CARD_ACTIVE ? 'selected' : ''}}>{{__('Active')}}</option>
                <option value="{{\common\integration\Models\Card::CARD_PASSIVE}}" {{$search['request_type'] == \common\integration\Models\Card::CARD_PASSIVE ? 'selected' : ''}}>{{__('Passive')}}</option>
                <option value="{{\common\integration\Models\Card::CARD_CLOSED}}" {{$search['request_type'] === \common\integration\Models\Card::CARD_CLOSED ? 'selected' : ''}}>{{__('Closed')}}</option>
            </select>
        </div>
        <div class="col-md-2 mb-3">
            <input name="first_six_digits" type="text" value="{{$search['first_six_digits']}}" class="form-control rounded" placeholder="{{__('First Six Digits')}}" maxlength="6" onkeyup="limitInput(this, 6)">
        </div>
        <div class="col-md-2 mb-3">
            <input name="last_four_digits" type="text" value="{{$search['last_four_digits']}}" class="form-control rounded" placeholder="{{__('Last Four Digits')}}" maxlength="4" onkeyup="limitInput(this, 4)">
        </div>
        <div class="row mt-2 text-md-right">
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
        </div>
    </div>
    <div>
            @if(isset($exportBtnRouteName))
                @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                    <a id="exportbtn" href="{{ isset($selfUrls) ? $selfUrls."/export" : ""}}"
                       class="btn btn-outline-secondary btn-fix btn-rounded ml-2">
                        <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i></span>
                    </a>
                @endif
            @endif
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
    </script>
@endpush



