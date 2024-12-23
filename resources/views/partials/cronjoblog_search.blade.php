<form method="get" action="{{route(Config::get('constants.defines.APP_CRON_JOB_LOG_INDEX'))}}">
    <div class="row font13">
        <div class="col-md-12 col-lg-12">
            <div class="row">

                <div class="col-md-3 col-sm-6">

                    <input value="{{ $search['date_range'] }}" placeholder="{{__('Date range')}}" type="text"
                           id="dateprangepicker" class="selectpicker show-tick form-control"
                           name="date_range"
                           autocomplete="off" readonly/>

                </div>

                <div class="col-md-3 col-sm-6">
                    <select name="name" class="form-control">
                        <option value="" >{{__("Select Cron job Name")}}</option>
                        <?php
                           $options = App\Models\CronjobLog::cronJobSelect();
                        ?>
                        @foreach($options as $option)
                        <option value="{{ $option }}" {{ $search['name'] == $option ? 'selected':''}}>{{__($option)}}</option>
                        @endforeach

                    </select>
                </div>


                <div class="col-md-3 col-sm-6">
                    <select name="panel_name" class="form-control">
                        <option value="">{{__("Select Panel Name")}}</option>
                        <option value="Admin" {{ $search['panel_name'] == 'Admin' ? 'selected':''}}>{{__('Admin')}}</option>
                        <option value="CCPayment" {{ $search['panel_name'] == 'CCPayment' ? 'selected':''}}>{{__('CCPayment')}}</option>
                        <option value="Merchant" {{ $search['panel_name'] == 'Merchant' ? 'selected':''}}>{{__('Merchant')}}</option>
                    </select>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="input-group-icon input-group-icon-right">
                    <span class="input-icon input-icon-right ">
                        <i class="fa fa-search"></i>
                    </span>
                        <input class="form-control"
                               value="{{isset($search['search_key']) ? $search['search_key'] : ""}}" type="text"
                               name="search_key"
                               placeholder="{{__("Search")}} ..."/>
                    </div>
                </div>
                <div class="col-md-12 col-sm-12 float-right pt-2">
                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded float-right">
                        {{__("Search")}}
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>
@section('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.select2')
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



