<form id="searchboxfrm"
       action="{{ route(Config::get('constants.defines.APP_AUTOMATIC_REPORTS_INDEX')) }}"
       method="get">
    <div class="row font13">

        <div class="col-md-3">
            <select class="selectpicker form-control show-tick"
                   name="date_period"
                   data-title="{{__("Choose Date Period")}}"
                   id="date_period">
                    @foreach($data['date_periods'] as $key => $value)
                    <option value="{{ $key }}"
                    >{{ $value }}</option>
                    @endforeach
           </select>
        </div>
        <div class="col-md-3">
           <select class="form-control show-tick selectpicker"
                    name="create_time"
                    data-title="{{__("Please select hour")}}"
                    id="date_hours">
                    <?php for($i = 0; $i < 24; $i++): ?>
                    <option value="<?= $i.":00" ?>"><?= $i.":00" ?></option>
                    <?php endfor ?>
           </select>
        </div>
        {{-- Eğer weekly seçiliyse bunu göster --}}
        <div class="col-md-3 hidden" id="date_periods_weekdays">
            <select class="show-tick form-control selectpicker"
                    data-title="{{__("Please select day")}}"
                    name="day_name"
                    id="week_names">
                @foreach($data['day_names'] as $key => $value)
                    <option value="{{ $key }}"
                    >{{ $value }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 hidden" id="date_periods_monthdays">
            <input class="form-control mer_datepicker" type="date" name="create_date" id="monthly_days">
        </div>
        <div class="col-md-3">
            <select class="selectpicker show-tick form-control"
                    multiple
                    data-actions-box="true"
                    data-title="{{__("Transaction State")}}"
                    id="status"
                    name="transaction_state_id[]" >
                @foreach($data['transaction_states'] as $key => $value)
                    <option value="{{ $key }}"
                        @isset($search['transaction_states']) {{ $value === $search['transaction_states'] ? 'selected':''}} @endisset
                    >{{ __($value) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row mt-3">

        <div class="col-md-3">
            <select class="form-control selectpicker"
                    multiple
                    data-actions-box="true"
                    data-title="{{__("Payment Type")}}"
                    name="payment_rec_option_id[]"
                    id="payment_methods">
                @foreach($data['payment_methods'] as $payment)
                    <option value="{{ $payment->id }}"
                        @isset($search['payment_methods']) {{ $value === $search['payment_methods'] ? 'selected':''}} @endisset
                    >{{ __($payment->name) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select class="selectpicker show-tick form-control"
                    multiple
                    data-actions-box="true"
                    data-title="{{__("Merchant")}}"
                    name="merchant_id[]">
                    @foreach ($data["merchant_list"] as $merchant)
                        <option value="{{ $merchant->id }}">{{ $merchant->name }}</option>
                    @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input class="form-control-rounded form-control"
                   type="email"
                   name="email"
                   placeholder="{{__('Email')}}"/>
        </div>
        <div class="col-md-3">
            <input class="form-control-rounded form-control"
                   type="search"
                   name="search"
                   placeholder="{{__('Search')}}"/>
    </div>
    </div>
    <div class="row mt-3">



    </div>
    <div class="row mb-4 mt-3">
            <div class="col-12 text-md-right">
                <div class="d-flex flex-column flex-md-row justify-content-around justify-content-md-end align-items-sm-stretch align-items-md-center pr-3 pl-3 pl-md-0">
                    {{-- Clear Filter --}}
                    <a href="{{route(Config::get('constants.defines.APP_AUTOMATIC_REPORTS_INDEX'))}}">{{__("Clear Filters")}}</a>
                    &nbsp;&nbsp;
                    {{-- Submit Button --}}
                    <button type="submit"
                            class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                        {{__("Submit")}}
                    </button>
                    {{-- Failed Reports --}}
                    <a href="{{route(config('constants.defines.APP_SUCCESS_AUTOMATIC_REPORTS_INDEX'))}}"
                        class="ml-3 btn btn-sm btn-success pull-right pt-2 pr-3 pb-2 pl-3">{{__('Successful Reports')}}</a>
                    {{-- Failed Reports End --}}
                    {{-- Submit Button END --}}
                    {{-- Export Button --}}
                    @if(isset($exportBtnRouteName))
                        @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                            <a href="{{ route($exportBtnRouteName) }}"
                               id="exportbtn"
                               class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                                <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i></span>
                            </a>
                        @endif
                    @endif
            </div>
        </div>
    </div>
</form>
<div class="row">
    <div class="col-md-12 text-end">
        <form action="{{ route(Config::get('constants.defines.APP_AUTOMATIC_REPORTS_EXPORT')) }}" method="get" class="pull-right">
            @csrf
            <?php $routeName = Request::route()->getName(); ?>
            {{-- {{ dd($successSearchArray) }} --}}
            @isset($searchArray)
                @foreach ($searchArray as $k => $reportSearch)
                    @if(is_array($reportSearch))

                        @foreach($reportSearch as $key => $report)
                            <input type="hidden" name="{{ $k."[".$report."]" }}" value="{{ $report }}">
                        @endforeach

                    @else
                        <input class="hidden" name="{{ $k }}" value="{{ $reportSearch }}">
                    @endif

                @endforeach
            @endisset
            @isset($successSearchArray)
                <input type="hidden" name="transaction_state_id[{{ $successSearchArray["transaction_state_id"]}}]" value="{{ $successSearchArray["transaction_state_id"]}}">
            @endisset
            <button type="submit"
                    class="btn btn-outline-secondary btn-fix btn-rounded ml-2 mb-2">
                <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i></span>
            </button>
        </form>

    </div>
</div>
@section('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')
@endsection
@section('js')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.moment')
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
    </script>
@endsection
