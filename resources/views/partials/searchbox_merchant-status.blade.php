<form id="searchboxfrm" method="get" action="{{ Request::url() }}">
    @include('merchant_status.modals.col_hide')
    <input type="hidden" name="search" value="search"/>
    <div class="d-flex justify-content-around align-items-center flex-column flex-md-row font13 w-100 row mb-2 mx-0">
        {{-- Date Picker --}}
        {{-- <div class="col col-lg-1 col-md-2 mb-2 m-md-0 text-center">
            <span id="dateprangepicker"
                  class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div> --}}
        {{-- Date Picker END --}}
        <div class="col col-lg-11 col-md-12">
            <input type="hidden"
                   value="{{ isset($search['date_range']) ? $search['date_range'] : "" }}"
                   id="daterange"
                   name="from_date"/>
            <div class="w-100">
                <div class="row">
                    {{-- Search Field --}}
                    <div class="col-md-4 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-search"></i>
                            </span>
                            <input class="form-control-rounded form-control"
                                   value="{{ isset($search['searchkey']) ? $search['searchkey'] : "" }}"
                                   type="text"
                                   name="searchkey"
                                   placeholder="{{ __("Search") }} ..."/>
                        </div>
                    </div>
                    {{-- Search Field END --}}

                    {{-- VKN --}}
                    <div class="col-md-4 my-2">
                        <input class="form-control"
                               name="vkn"
                               value="{{ isset($search['vkn']) ? $search['vkn'] : "" }}"
                               type="text"
                               placeholder="{{ __("VKN") }}"/>
                    </div>
                    {{-- VKN END --}}

                    {{-- TCKN --}}
                    <div class="col-md-4 my-2">
                        <input class="form-control"
                               type="text"
                               value="{{ isset($search['tckn']) ? $search['tckn'] : "" }}"
                               name="tckn"
                               placeholder="{{ __("TCKN") }}"/>
                    </div>
                    {{-- TCKN END --}}
                </div>
                <div class="row">

                    {{-- daterangepicker start--}}
                    <div class="col-md-4 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <input type="text"
                                {{-- value="{{ isset($search['searchkey']) ? $search['searchkey'] : "" }}" --}}
                                value="{{ isset($search['date_range']) ? $search['date_range'] : "" }}"
                                id="dateprangepicker"
                                class="form-control"
                                readonly
                                placeholder=""
                                name="daterange"/>
                        </div>
                    </div>
                    {{-- daterangepicker end --}}




                    {{-- User --}}
                    <div class="col-md-4 my-2">
                        <input class="form-control"
                               type="text"
                               value="{{ isset($search['user']) ? $search['user'] : "" }}"
                               name="user"
                               placeholder="{{ __("User") }}"/>
                    </div>
                    {{-- User END --}}

                    {{-- Status --}}
                    <div class="col-md-4 my-2">
                        <select name="status[]"
                                multiple
                                class="selectpicker form-control custome-padding"
                                data-actions-box="true"
                                data-title="{{__('Status')}}">
                            <option value="1"
                                {{ (isset($search['status']) && in_array('1', $search['status'])) ? 'selected' : ''  }}>
                                {{__('Blacklisted')}}
                            </option>
                            <option value="0"
                                {{ (isset($search['status']) && in_array('0', $search['status'])) ? 'selected' : ''  }}>
                                {{__('Not blacklisted')}}
                            </option>
                        </select>
                    </div>
                    {{-- Status END --}}
                </div>
            </div>
        </div>
    </div>
    {{-- Buttons --}}
    <div class="row mb-4 mt-4">
        <div class="col-12 text-md-right">
            <div class="d-flex flex-column flex-md-row justify-content-around justify-content-md-end align-items-sm-stretch align-items-md-center pr-3 pl-3 pl-md-0">

                {{-- Cear Filters --}}
                <a href="{{ isset($selfUrls) ? $selfUrls : ""}}"
                   class="text-center">{{ __("Clear Filters") }}</a>
                &nbsp;&nbsp;
                {{-- Cear Filters END --}}

                {{-- Submit Button --}}
                <button type="submit"
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
                @if(isset($exportBtnRouteName))
                    @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                        <a id="exportbtn"
                           href="{{ route(config('constants.defines.APP_MERCHANT_STATUS_EXPORT')) }}"
                           class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                        <span class="btn-icon">{{ __("Export") }}&nbsp;&nbsp;&nbsp; <i
                                class="fa fa-caret-right"></i></span>
                        </a>
                    @endif
                @endif
                {{-- Export Button END --}}

            </div>
        </div>
    </div>
    {{-- Buttons END --}}
</form>

@section('scripts')
    <script>
        $('#dateprangepicker').daterangepicker({
            maxDate: new Date(),
            "autoApply": false,
            locale: {
                format: 'YYYY/MM/DD',
                customRangeLabel: "{{ __('Custom Range') }}",
                applyLabel: "{{ __('Apply') }}",
                cancelLabel: "{{ __('Cancel') }}"
            },
            ranges: {
                "{{ __('Today') }}": [moment(), moment()],
                "{{ __('Yesterday') }}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                "{{ __('Last 7 Days') }}": [moment().subtract(6, 'days'), moment()],
                "{{ __('Last 30 Days') }}": [moment().subtract(29, 'days'), moment()],
                "{{ __('This Month') }}": [moment().startOf('month'), moment().endOf('month')],
                "{{ __('Last Month') }}": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            "alwaysShowCalendars": true,
            "startDate": "{{isset($search['from_date']) ? $search['from_date'] : ""}}",
            "endDate": "{{isset($search['to_date']) ? $search['to_date'] : ""}}"
        });

        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
            //do something, like clearing an input
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#daterange").val(dateranges);
        });

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
        });
    </script>
@endsection
