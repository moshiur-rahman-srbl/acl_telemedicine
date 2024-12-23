<form method="get" action="" id="search-form">
    <div class="row font13">
        <div class="col-md-12 col-lg-12">
            <div class="row pb-4">
               <div class="col-md-3 my-2">
                    <input type="text" class="form-control-rounded form-control"
                           value="{{ $search['date_range'] }}"
                           id="dateprangepicker"
                           class="form-control" name="date_range"
                           autocomplete="off"
                           placeholder="{{__('Date Range')}}" readonly/>
                </div>

                 <div class="col-md-3 my-2">
                    <div class="input-group-icon input-group-icon-right">
                        <input class="form-control-rounded form-control" value="{{ $search['merchant_name'] }}" type="text"
                               name="merchant_name" placeholder="{{ __('Merchant Name') }}">
                    </div>
                </div>

                <div class="col-md-3 my-2">
                    <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-search"></i>
                            </span>
                        <input class="form-control-rounded form-control" value="{{ $search['search_key'] }}" type="text"
                               name="search_key" placeholder="{{ __('Search ...') }}">
                    </div>
                </div>

                <div class="col-md-12 col-sm-12 pb-2 text-right">

                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded">
                        {{__("Search")}}
                    </button>

                    @if(Auth::user()->hasPermissionOnAction(Config('constants.defines.APP_WET_SIGNED_MERCHANT_REPORT_INDEX')))
                        <input type="submit" name="submitexport"
                               class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto"
                               value="{{__("Export")}}">
                    @endif

                </div>
            </div>
        </div>
    </div>
</form>
