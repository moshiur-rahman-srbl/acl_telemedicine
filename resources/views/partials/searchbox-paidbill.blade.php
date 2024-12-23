
<div class="ibox-body">
    <form action="{{ route(config('constants.defines.APP_PAIDBILLS_INDEX')) }}" method="get">
        <div class="row justify-content-end">
            <div class="col-lg-12 col-md-12 mb-3 content-left"><span id="dateprangepicker" class="bg-light p-4 btn-rounded btn"><i class="fa fa-calendar fa-2x"></i></span></div>

            <div class="col-lg-4 col-md-4">
                <input name="date_range" class="form-control" value="{{$search['date_range']}}" id="date_range" readonly/>
            </div>

            <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <input name="transactionid" id="transactionid" class="form-control" value="{{$search['transactionid']}}"
                       type="text"
                       placeholder="{{__('Trans. ID')}}">
            </div>
            <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <select name="paymentmethodid" id="paymentmethodid" class="selectpicker form-control show-tick custome-padding">
                    <option value="">{{__("Payment Method")}}</option>
                    @foreach($paymentmethods as $key=>$value)
                        <option {{$key == $search['paymentmethodid'] ? 'selected' : ''}} value="{{$key}}">{{__($value)}}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <select name="bill_payment_provider" class="selectpicker form-control show-tick custome-padding">
                    <option value="">{{__('Payment Provider')}}</option>
                    @foreach($bill_payment_providers as $key=> $payment_provider)
                        <option
                            value="{{$key}}" {{ $key == $search['bill_payment_provider'] ? 'selected' : '' }}>{{__($payment_provider)}}</option>
                    @endforeach
                </select>
            </div>
            {{-- <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <select name="categoryid" id="categoryid" class="selectpicker form-control show-tick custome-padding">
                    <option value="">{{__("Category")}}</option>
                    @foreach($categories as $key=>$value)
                        <option {{$key == $search['categoryid'] ? 'selected' : ''}} value="{{$key}}">{{__($value)}}</option>
                    @endforeach
                </select>
            </div> --}}
            <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <input name="maxamount" id="maxamount" class="form-control float-number-only" value="{{$search['maxamount']}}"
                       type="text"
                       placeholder="{{__('Max Amount')}}">
            </div>
            <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <input name="minamount" id="minamount" class="form-control float-number-only" value="{{$search['minamount']}}"
                       type="text"
                       placeholder="{{__('Min Amount')}}">
            </div>
            <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <input name="tckn" id="tckn" class="form-control" value="{{$search['tckn']}}"
                       type="text"
                       placeholder="{{__('TCKN')}}">
            </div>
            <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <input name="gsmnumber" id="gsmnumber" class="form-control" value="{{$search['gsmnumber']}}"
                       type="text"
                       placeholder="{{__('Phone')}}">
            </div>
            <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <div class="input-group">
                    <input name="searchkey" id="search" class="form-control" value="{{$search['searchkey']}}"
                           type="text"
                           placeholder="{{__('Search for...')}}">
                </div>
            </div>
        </div>
        <div class="row justify-content-end pt-2">
            <div class="col-lg-4 col-md-6 mt-1 mb-1">
                <div class="row">
                    <div class="col-md-6">
                        <button  class="btn btn-primary btn-block" type="submit">{{__('Search')}}</button>
                    </div>

                    <div class="col-md-6">
                        <a href="{{ route(config('constants.defines.APP_PAIDBILLS_INDEX')) }}" class="clearUrlBtn btn btn-light bg-white text-secondary btn-block" type="button">{{__('Clear')}}</a>
                    </div>

                </div>
            </div>
        </div>
    </form>
</div>



</form>

@push('css')
    @include('partials.css_blade.daterangepicker')
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.daterangepicker')

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
            "startDate": "{{isset($search['from_date']) ? $search['from_date'] : $from_date}}",
            "endDate": "{{isset($search['to_date']) ? $search['to_date'] : $to_date}}"
        });

        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#date_range").val(dateranges);
        });

    </script>
@endpush



