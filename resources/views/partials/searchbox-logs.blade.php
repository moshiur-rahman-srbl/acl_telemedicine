<style>
    /* .form-control {
        padding-top: inherit;
    !important;
        padding-bottom: inherit;
    !important;
    } */

    .bootstrap-select.btn-group .dropdown-menu {
        width: 100%;
    !important;
    }
</style>
<form id="searchboxfrm"
      method="get"
      action="{{ isset($selfUrls) ? $selfUrls : Request::url() }}">
    <input type="hidden" name="search" value="search"/>
    <div class="d-flex justify-content-around align-items-center flex-column flex-md-row font13 w-100 row mb-2 mx-0">
        {{-- Date Picker --}}
        <div class="col col-lg-1 col-md-2 mb-2 m-md-0 text-center">
            <span id="dateprangepicker"
                  class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        {{-- Date Picker END --}}
        <div class="col col-lg-11 col-md-12">
            <input type="hidden"
                   value="{{ isset($search['daterange']) ? $search['daterange'] : "" }}"
                   id="daterange"
                   name="from_date"/>
            <div class="w-100">
                <div class="row">

                    {{-- Panel --}}
                    <div class="col-md-6 my-2">
                        <select class="form-control selectpicker"
                                name="panel[]"
                                multiple
                                data-title="{{ __('Select Panel Name') }}"
                                data-style="''"
                                data-style-base="form-control">
                            @foreach($available_panels as $panel)
                                <option {{ in_array($panel, $search['panel']) ? 'selected' : '' }}
                                        value="{{ $panel }}">{{ __(ucfirst($panel)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Panel END --}}

                    {{-- Search Field --}}
                    <div class="col-md-6 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-search"></i>
                            </span>
                            <input class="form-control-rounded form-control"
                                   value="{{ isset($search['search_phrase']) ? $search['search_phrase'] : "" }}"
                                   type="text"
                                   name="search_phrase"
                                   placeholder="{{ __("Search") }} ..."/>
                        </div>
                    </div>
                    {{-- Search Field END --}}
                </div>
            </div>
        </div>
    </div>
    {{-- Buttons --}}
    <div class="row mb-4 mt-4">
        <div class="col-12 text-md-right">
            <div
                class="d-flex flex-column flex-md-row justify-content-around justify-content-md-end align-items-sm-stretch align-items-md-center pr-3 pl-3 pl-md-0">

                {{-- Cear Filters --}}
                <a href="{{ isset($selfUrls) ? $selfUrls : "" }}"
                   class="text-center">{{ __("Clear Filters") }}</a>
                &nbsp;&nbsp;
                {{-- Cear Filters END --}}

                {{-- Submit Button --}}
                <button type="submit"
                        name="search"
                        id="searchbtn"
                        class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                    {{__("Search")}}
                </button>
                {{-- Submit Button END --}}

                {{-- Export Button --}}
                <button id="exportbtn"
                        name="export"
                        class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                    <span class="btn-icon">{{ __("Export") }}&nbsp;&nbsp;&nbsp; <i
                            class="fa fa-caret-right"></i></span>
                </button>
                {{-- Export Button END --}}
                {{-- download Button --}}
                <button id="downloadbtn"
                        name="downloadLogFile"
                        class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                    <span class="btn-icon">{{ __("Download") }}&nbsp;&nbsp;&nbsp; <i
                                class="fa fa-caret-right"></i></span>
                </button>
                {{-- download Button END --}}

            </div>
        </div>
    </div>
    {{-- Buttons END --}}
</form>
@section('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')
@endsection
@section('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.bootstrap-select')
    <script>
        const maxD = new Date();

        $('#dateprangepicker').daterangepicker({
            maxDate: maxD,
            "autoApply": false,
            locale: {
                format: 'YYYY/MM/DD',
                customRangeLabel: "{{ __('Custom Range') }}",
                applyLabel: "{{ __('Apply') }}",
                cancelLabel: "{{ __('Cancel') }}"
            },
            ranges: {
                "{{__('Today')}}": [moment(), moment()],
                "{{__('Yesterday')}}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                "{{__('Last 2 Days')}}": [moment().subtract(1, 'days'), moment()],
                {{--"{{__('Last 7 Days')}}": [moment().subtract(6, 'days'), moment()],--}}
                {{--"{{__('Last 30 Days')}}": [moment().subtract(29, 'days'), moment()],--}}
                {{--"{{__('This Month')}}": [moment().startOf('month'), moment().endOf('month')],--}}
                {{--"{{__('Last Month')}}": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]--}}
            },
            "alwaysShowCalendars": true,
            "startDate": "{{ isset($search['from_date']) ? $search['from_date'] : "" }}",
            "endDate": "{{ isset($search['to_date']) ? $search['to_date'] : "" }}"
        });

        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#daterange").val(dateranges);
        });

        $("#searchbtn").on("click", function (event) {
            event.preventDefault();
            $("#searchboxfrm").attr('action', '{{ isset($selfUrls) ? $selfUrls : Request::url() }}');
            $("#searchboxfrm").submit();
        });

        $('.selectpicker').selectpicker();
    </script>
@endsection
