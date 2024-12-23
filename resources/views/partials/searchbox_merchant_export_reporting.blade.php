<form method="get" action="{{route(Config::get('constants.defines.APP_EXPORT_REPORTS_HISTORY_INDEX'))}}" id="search-form">
    <div class="row font13">
        <div class="col-md-12 col-lg-12">
            <div class="row pb-4">
                <div class="col-md-3 col-sm-6 pb-2">
                    <input type="text" class="form-control"
                           value="{{ $date_range }}"
                           id="dateprangepicker"
                           class="form-control" name="date_range"
                           autocomplete="off"
                           placeholder="{{__('Date Range')}}" readonly/>
                </div>



                <div class="col-md-3 col-sm-6 pb-2">
                    <select name="status" class="form-control-rounded form-control">
{{--                        <option value="" >{{__("Status")}}</option>--}}
                        @foreach($statusData as $key=>$value)
                            <option  value="{{$key}}" {{ $status == $key ? 'selected' : ''  }}>{{ __($value)}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 mt-1 mb-1">
                    <select name="report_type" id="report_type" class="form-control-rounded form-control">
                        <option value="" >{{__("All")}}</option>
                        @foreach($typeData as $key=>$rt_list)
                            <option value="{{$key}}" {{ $report_type ==$key ? 'selected' : '' }}>{{ __($rt_list)}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 pb-2">
                    <select name="format" class="form-control-rounded form-control">
                        <option value="" >{{__("All")}}</option>
                        @foreach($formatData as $key=>$value)
                            <option value="{{$key}}" {{ $format == $key ? 'selected' : ''  }}>{{$value}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-12 col-sm-12 pb-2 text-right">
                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded">
                        {{__("Search")}}
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
