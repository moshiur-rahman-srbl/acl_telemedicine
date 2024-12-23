<form method="get" action="{{route(Config::get('constants.defines.APP_BLOCK_CC_INDEX'))}}" id="search-form">
    <div class="row font13">
        <div class="col-md-12 col-lg-12">
            <div class="row pb-4">
                <div class="col-md-3 col-sm-6 pb-2">
                    <input value="{{ $search['date_range'] }}" placeholder="{{__('Date range')}}" type="text"
                           id="dateprangepicker" class="selectpicker show-tick rounded form-control"
                           name="date_range"
                           autocomplete="off" readonly/>
                </div>

                <div class="col-md-3 col-sm-6 pb-2">
                    <select name="status" class="form-control-rounded form-control">
                        <option value="" disabled="disabled" selected>{{__("Status")}}</option>
                        <option value="{{\App\Models\BlockCC::BLOCKED}}" {{ $search['status'] == \App\Models\BlockCC::BLOCKED ? 'selected':''}}>{{\App\Models\BlockCC::blockStatus(\App\Models\BlockCC::BLOCKED, true)}}</option>
                        @if(!\common\integration\BrandConfiguration::hideUnblockedStatusFromList())
                            <option value="{{\App\Models\BlockCC::UNBLOCKED}}" {{ $search['status'] == \App\Models\BlockCC::UNBLOCKED ? 'selected':''}}>{{\App\Models\BlockCC::blockStatus(\App\Models\BlockCC::UNBLOCKED, true)}}</option>
                        @endif
                        <option value="{{\App\Models\BlockCC::UNBLOCKED_TO_BLOCK}}" {{ $search['status'] == \App\Models\BlockCC::UNBLOCKED_TO_BLOCK ? 'selected':''}}>{{\App\Models\BlockCC::blockStatus(\App\Models\BlockCC::UNBLOCKED_TO_BLOCK, true)}}</option>
                        <option value="{{\App\Models\BlockCC::BLOCKED_TO_UNBLOCK}}" {{ $search['status'] == \App\Models\BlockCC::BLOCKED_TO_UNBLOCK ? 'selected':''}}>{{\App\Models\BlockCC::blockStatus(\App\Models\BlockCC::BLOCKED_TO_UNBLOCK, true)}}</option>
                        <option value="{{\App\Models\BlockCC::DELETED}}" {{ $search['status'] == \App\Models\BlockCC::DELETED ? 'selected':''}}>{{\App\Models\BlockCC::blockStatus(\App\Models\BlockCC::DELETED, true)}}</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 pb-2">
                    <select name="type" class="form-control-rounded form-control">
                        <option value="" disabled="disabled" selected>{{__("Type")}}</option>
                        <option value="{{\App\Models\BlockCC::TYPE_CARD}}" {{ $search['type'] == \App\Models\BlockCC::TYPE_CARD ? 'selected':''}} >{{\App\Models\BlockCC::blockType(\App\Models\BlockCC::TYPE_CARD)}}</option>
                        <option value="{{\App\Models\BlockCC::TYPE_FULL_CARD}}" {{ $search['type'] == \App\Models\BlockCC::TYPE_FULL_CARD ? 'selected':''}} >{{\App\Models\BlockCC::blockType(\App\Models\BlockCC::TYPE_FULL_CARD)}}</option>
                        <option value="{{\App\Models\BlockCC::TYPE_BIN}}" {{ $search['type'] == \App\Models\BlockCC::TYPE_BIN ? 'selected':''}}>{{\App\Models\BlockCC::blockType(\App\Models\BlockCC::TYPE_BIN)}}</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 pb-2">
                    <select name="merchant_id" class="form-control-rounded form-control selectpicker show-tick" data-live-search="true">
                        <option value="" disabled="disabled" selected>{{__("Select Merchant")}}</option>
                        @foreach($merchants as $merchant)
                            <option value="{{ $merchant->id  }}" {{ $search['merchant_id'] == $merchant->id ? 'selected':''}} >{{ $merchant->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 pb-2">
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

                <div class="col-md-12 col-sm-12 pb-2 text-right">
                    <label class="mt-2 mr-4">
                        <input type="checkbox"
                               class="mr-2"
                               id="is_block_temporarily_cb"
                               {{$search['is_block_temporarily'] ? 'checked' : ''}}
                        >{{__('Is Block Temporarily')}}
                    </label>
                    <input type="hidden" name="is_block_temporarily" value="{{$search['is_block_temporarily'] ? 1 : 0}}" />

                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded ml-4" name="submit" value="search-btn">
                        {{__("Search")}}
                    </button>
                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded ml-4" name="submit" value="export-btn">
                        {{__("Export")}}
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>
@section('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.select2')
    <link href="{{asset('adminca')}}/assets/vendors/smalot-bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet"/>
@endsection
@section('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.select2')
    <script>

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
@endsection



