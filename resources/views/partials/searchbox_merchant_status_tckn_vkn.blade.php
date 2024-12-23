<form id="searchboxfrm" method="get" action="{{ Request::url() }}#tckVknBlacklistContainer">
    @include('merchant_status.modals.col_hide')
    <input type="hidden" name="type" value="tckn_vkn_blacklist_filter"/>
    <div class="d-flex justify-content-around align-items-center flex-column flex-md-row font13 w-100 row mb-2 mx-0">

        <div class="col col-lg-11 col-md-12">

                <div class="row">
                     {{-- Search Field --}}
                     <div class="col-md-4 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-search"></i>
                            </span>
                            <input class="form-control-rounded form-control"
                                   value="{{ isset($blacklist_filters['blacklist_searchkey']) ? $blacklist_filters['blacklist_searchkey'] : "" }}"
                                   type="text"
                                   name="blacklist_searchkey"
                                   placeholder="{{ __("Search") }} ..."/>
                        </div>
                    </div>
                    {{-- Search Field END --}}

                    {{-- VKN --}}
                    <div class="col-md-4 my-2">
                        <input class="form-control"
                               name="blacklist_vkn"
                               value="{{ isset($blacklist_filters['blacklist_vkn']) ? $blacklist_filters['blacklist_vkn'] : "" }}"
                               type="text"
                               placeholder="{{ __("VKN") }}"/>
                    </div>
                    {{-- VKN END --}}

                    {{-- TCKN --}}
                    <div class="col-md-4 my-2">
                        <input class="form-control"
                               type="text"
                               value="{{ isset($blacklist_filters['blacklist_tckn']) ? $blacklist_filters['blacklist_tckn'] : "" }}"
                               name="blacklist_tckn"
                               placeholder="{{ __("TCKN") }}"/>
                    </div>
                    {{-- TCKN END --}}

                    {{-- daterange picker strat --}}
                    <div class="col-md-4 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <input type="text"
                                value="{{ isset($blacklist_filters['blacklist_daterange']) ? $blacklist_filters['blacklist_daterange'] : "" }}"
                                id="dateprangepicker1"
                                class="form-control"
                                readonly
                                placeholder=""
                                name="blacklist_daterange"/>
                        </div>
                    </div>
                    {{-- daterange picker end --}}

                    {{-- User --}}
                    <div class="col-md-4 my-2">
                        <input class="form-control"
                               type="text"
                               value="{{ isset($blacklist_filters['blacklist_user']) ? $blacklist_filters['blacklist_user'] : "" }}"
                               name="blacklist_user"
                               placeholder="{{ __("User") }}"/>
                    </div>
                    {{-- User END --}}
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



            </div>
        </div>
    </div>
    {{-- Buttons END --}}
</form>

