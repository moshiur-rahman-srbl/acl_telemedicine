<form id="searchboxfrm" method="get" action="{{route(Config::get('constants.defines.APP_REVENUE_INDEX'))}}">
    <div class="row font13 align-items-center">
        <div class="col-md-2 content-center">
            <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        <div class="col-md-6">
            <input type="hidden" value="{{isset($search['date_range']) ? $search['date_range'] : ""}}" id="date_range"
                   name="date_range"/>
            <div class="row">
                <div class="col-md-5">
                    <select name="type" class="selectpicker form-control">
                        <option value="">{{__("Type")}}</option>
                        @if(!empty($type_list))
                            @foreach($type_list as $key=>$value)
                                <option
                                    {{$search['type'] == $key ? 'selected':''}} value="{{$key}}">{{__($value)}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-7">
                    <input type="hidden" value="" name="merchant_name" id="merchant_name"/>
                    <select data-live-search="true" name="merchantid" class="select2 form-control">
                        <option value="">{{__("Merchant Name")}}</option>
                        @if(!empty($merchants))
                            @foreach($merchants as $merchant_id=>$merchant)
                                <option
                                    {{$merchant_id == $search['selected_merchant_id'] ? 'selected':''}} value="{{$merchant_id}}">{{__($merchant)}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>

                {{--<div class="col-md-3">--}}
                {{--<select name="currency" class="selectpicker form-control">--}}
                {{--<option value="">{{__("Currency")}}</option>--}}
                {{--@if(!empty($currencies))--}}
                {{--@foreach($currencies as $currency)--}}
                {{--<option {{$currency->id == $search['currency'] ? 'selected':''}} value="{{$currency->id}}">{{$currency->code}}</option>--}}
                {{--@endforeach--}}
                {{--@endif--}}
                {{--</select>--}}
                {{--</div>--}}
            </div>
        </div>
        <div class="col-md-4 text-right">
            <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded ml-2">
                {{__("Submit")}}
            </button>
            <a id="exportbtn" href="{{ route(Config::get('constants.defines.APP_REVENUE_EXPORT'))}}"
               class="btn btn-outline-secondary btn-fix btn-rounded ml-2">
                <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i></span>
            </a>
        </div>
    </div>

</form>
@push('css')
    @include('partials.css_blade.daterangepicker')
    {{--    @include('partials.css_blade.select2')--}}
    @include('partials.css_blade.select2-v5')

    <style>
        select2-container--default .select2-selection--single {
            background-color: #fff;
            border: 1px solid #e3e2e2;
            border-radius: 0px;
        }

        .select2-container .select2-selection--single {
            box-sizing: border-box;
            cursor: pointer;
            display: block;
            height: 37px;
            user-select: none;
            -webkit-user-select: none;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #444;
            line-height: 20px;
        }

        .select2-container .select2-selection--single .select2-selection__rendered {
            display: block;
            padding-left: 16px;
            padding-right: 20px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 26px;
            position: absolute;
            top: 18px;
            right: 1px;
            width: 20px;
        }

        .select2-container--default .select2-selection--single .select2-selection__clear {
            position: absolute;
            top: 54%;
            right: 2rem;
            font-family: themify;
            speak: none;
            font-style: normal;
            display: inline-block;
            font-family: LineAwesome;
            text-decoration: inherit;
            text-rendering: optimizeLegibility;
            text-transform: none;
            font-weight: 400;
            line-height: 0;
            margin-right: 5px;
            font-size: 0;
        }

        .required:after {
            content: "*";
            font-weight: bold;
            color: red;
        }
    </style>
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.daterangepicker')
{{--        @include('partials.js_blade.select2')--}}
    @include('partials.js_blade.select2-v5')

    <script>

        $('select[name=merchantid]').select2({
            width: '100%', 'border-radius': '5px',
            ajax: {
                url: '{{route(config('constants.defines.APP_REVENUE_INDEX'))}}',
                data: function (params) {
                    var query = {
                        merchant_name: params.term,
                        action: 'GET_MERCHANTS'
                    }
                    return query;
                },
                processResults: function (data) {
                    console.log('data', data);
                    return {
                        results: data
                    };
                }
            },
            placeholder: "{{__('Merchant Name')}}",
            allowClear: true
        });

        {

            @if(is_array($search) && !empty($search['merchant_name']) && !empty($search['merchantid']) && !is_array($search['merchantid']))
            var data = {
                id: '{{$search['merchantid']}}',
                text: '{{$search['merchant_name']}}'
            };

            var newOption = new Option(data.text, data.id, true, true);
            $('select[name=merchantid]').append(newOption).trigger('change');
            $("#merchant_name").val(data.text);
            @endif

            $('select[name=merchantid]').on('select2:select', function (e) {
                var data = e.params.data;
                $("#merchant_name").val(data.text);
            });
        }

        // Large using Bootstrap 5 classes
        $("#form-select-lg").select2({
            theme: "bootstrap-5",
            dropdownParent: $("#form-select-lg").parent(), // Required for dropdown styling
        });
        $('#dateprangepicker').daterangepicker({
            maxDate: new Date(),
            "autoApply": false,
            "autoUpdateInput": false,
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

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');

            var search_form_url = $("#searchboxfrm").attr('action');
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
            $("#searchboxfrm").attr('action', search_form_url);
        });


    </script>
@endpush



