<form id="binRangeFilter" method="get" action="{{route(Config::get('constants.defines.APP_BIN_RANGE_MANAGEMENT_INDEX'))}}">
    <div class="row font13">
        <div class="col-md-12 col-lg-12">
            <div class="row">

                <div class="col-md-3 col-sm-6">
                    <div class="input-group">
                        <input name="bin_from" id="bin_from" class="form-control" value="{{ $search['bin_from'] }}" type="text" placeholder="{{ __('Bin From') }}" style="height: 41px;">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="input-group">
                        <input name="bin_to" id="bin_to" class="form-control" value="{{ $search['bin_to'] }}" type="text" placeholder="{{ __('Bin To') }}" style="height: 41px;">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="input-group">
                        <input name="bin" id="bin" class="form-control" value="{{ $search['bin'] }}" type="text" placeholder="{{ __('Bin') }}" style="height: 41px;">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <select class="form-control" name="program" id="program">
                        <option value=""> {{__('Program')}}</option>
                        @if(isset($card_programs) && !empty($card_programs))
                        @foreach($card_programs as $key=>$program)

                        <?php

                        $cardBrand = $program->code;
                        if (\common\integration\BrandConfiguration::isAllowCardInfoFromBinRangeQp()) {
                            $cardBrand = $program->bin_range_qp_api_name_code;
                        }
                        ?>
                        @if(!empty($cardBrand))
                        <option value="{{$cardBrand}}" {{ $search['program'] == $cardBrand ? 'selected' : '' }}>
                            {{$program->code}} ( {{$program->bin_range_qp_api_name_code}} )
                        </option>
                        @endif
                        @endforeach
                        @endif
                    </select>
                </div>
            </div>
            <div class="row pt-3">
                <div class="col-md-3 col-sm-6">
                    <div class="input-group">
                        <select name="is_commercial" class="form-control">
                            <option value="">{{__('Is Commercial')}}</option>
                            <option value="true" {{ $search['is_commercial'] == 'true' ? 'selected' : '' }}>{{__('Commercial')}}
                            </option>
                            <option value="false" {{ $search['is_commercial'] == 'false' ? 'selected' : '' }}>{{__('Individual')}}
                            </option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="input-group-icon input-group-icon-right">
                        <span class="input-icon input-icon-right ">
                            <i class="fa fa-search"></i>
                        </span>
                        <input class="form-control" value="{{isset($search['search_key']) ? $search['search_key'] : ""}}" type="text" name="search_key" placeholder="{{__("Search")}} ..." />
                    </div>
                </div>
                @if(config('brand.name_code') == config('constants.BRAND_NAME_CODE_LIST.SP'))
                <div class="col-md-3 col-sm-6">
                    <div class="input-group">
                        <select name="bin_table" onchange="this.form.submit()" class="form-control" id="bin_table">
                            <option value="">{{__('Please select bin table')}}</option>
                            <option value="{{ \App\Models\BinResponse::BIN_RESPONSES_TABLE }}" {{ $search['bin_table'] == \App\Models\BinResponse::BIN_RESPONSES_TABLE ? 'selected' : '' }}>{{__('Bin Response')}}
                            </option>
                            <option value="{{\App\Models\BinRangeResponse::BIN_RANNGE_RESPONSES_TABLE}}" {{ $search['bin_table'] == \App\Models\BinRangeResponse::BIN_RANNGE_RESPONSES_TABLE ? 'selected' : '' }}>{{__('Bin Range Response')}}
                            </option>
                        </select>
                    </div>
                </div>
                @endif
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6 float-right pt-2">
                    <button type="submit" class="btn btn-outline-primary btn-fix btn-rounded float-right" id="searchBtn">
                        {{__("Search")}}
                    </button>
                </div>
                <div class="col-md-3 col-sm-6 float-right pt-2">
                    <select class="form-control show-tick rounded" name="file_type" id="file_type">
                    <option
                                    value="{{ \App\Models\BinRangeResponse::FORMAT_CSV }}">{{ __('CSV') }}</option>
                                <option
                                    value="{{ \App\Models\BinRangeResponse::FORMAT_XLS }}">{{ __('XLS') }}</option>
                    </select>
                </div>
                <div class="col-md-3 col-sm-6 float-right pt-2">
                    <input name="exportBin" id="exportBin" class="form-control" value="0" type="hidden" >
                    <button id="exportBtn" type="button" class="btn btn-outline-secondary btn-fix btn-rounded ml-2">
                        <span class="btn-icon">{{ __("Export") }}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i>
                    </button>
                    
                </div>
            </div>

        </div>
    </div>
</form>
@section('css')

@endsection
<!-- @section('scripts')

@include('partials.js_blade.validate')
@include('partials.js_blade.moment')
@endsection -->

@push('scripts')
@include('partials.js_blade.validate')
@include('partials.js_blade.moment')

<script>
    $(document).ready(function() {

        $("#exportBtn").click(function(e) {
            e.preventDefault();
            $("#exportBin").val(1);

            $('#binRangeFilter').submit();

        });

        $("#searchBtn").click(function(e) {
            e.preventDefault();
            $("#exportBin").val(0);

            $('#binRangeFilter').submit();

        });


    });
</script>

@endpush
