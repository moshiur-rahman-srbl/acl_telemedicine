<form method="get" action="{{route(Config::get('constants.defines.APP_ANNOUNCEMENT_LIST'))}}">
    <div class="row font13 align-items-center">
        <div class="col-md-2 content-center">
            <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        <div class="col-md-10">
            <input type="hidden" value="{{isset($search['date_range']) ? $search['date_range'] : ""}}" id="date_range"
                   name="date_range"/>
            <div class="row">
                <div class="col-md-4">
                    <select name="status" class="selectpicker form-control">
                        <option value="" disabled="disabled">{{__("Status")}}</option>
                        <option value="3" {{($search['status'] == 3) ? "selected" : ""}}>{{__("All")}}</option>
                        <option value="2" {{($search['status'] == 2) ? "selected" : ""}}>{{__("Active")}}</option>
                        <option value="1" {{($search['status'] == 1) ? "selected" : ""}}>{{__("Inactive")}}</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <select name="admin_id" class="selectpicker form-control">
                        <option value="">{{__("Admin Name")}}</option>
                        @if(!empty($admins))
                            @foreach($admins as $admin_id=>$admin)
                                <option {{$admin_id == $search['admin_id'] ? 'selected':''}} value="{{$admin_id}}">{{__($admin)}}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-md-4">
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
            </div>
        </div>
        <div class="col-md-12">
            <div class="row">
                <div class="col-md-8"></div>
                <div class="col-md-4 text-right">
                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded ml-2">
                        {{__("Submit")}}
                    </button>
                </div>
            </div>
        </div>
    </div>

</form>
@push('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.select2')
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
        .required:after{
            content:"*";
            font-weight:bold;
            color:red;
        }
    </style>
@endpush
@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.select2')
    <script>

        $('select[name=admin_id]').select2({ width: '100%' ,'border-radius':'5px',
            ajax: {
                url: '{{route(config('constants.defines.APP_ANNOUNCEMENT_LIST'))}}',
                data:function (params) {
                    var query = {
                        admin_name: params.term
                    };
                    return query;
                },
                processResults: function (data) {
                  console.log('data',data);
                  return {
                    results: data
                  };
                }
            },
            placeholder: "{{__('Admin Name')}}",
            allowClear: true
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

    </script>
@endpush



