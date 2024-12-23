
<div class="row mb-4 mt-4">
    <div class="col-md-12 text-md-right">
        <div class="d-flex flex-column flex-md-row justify-content-around justify-content-md-end align-items-sm-stretch align-items-md-center pr-3 pl-3 pl-md-0">
            @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowedFraudRuleStructureFeature']))
                <form id="searchboxfrm" action="{{route(Config::get('constants.defines.FRAUD_RULE_INDEX'))}}" class="col-md-12">
            @else
               <form id="searchboxfrm" method="get" action="" class="col-md-12">
            @endif
                @include('fraud.rule.combined.rule_select')
                <div class="row justify-content-end" >
                    <div class="col-md-3 col-sm-12">
                        <select id="risk_management_type" name="risk_management_type" class="form-control custome-padding" data-title="{{ __("Risk Management Type") }}">
                            <option value="">{{__("Select Risk Management Type")}}</option>
                            @foreach ($risk_management_types as $risk_management_type => $risk_management_type_label)
                                <option value="{{ $risk_management_type }}" @selected($search['risk_management_type'] && $search['risk_management_type'] == $risk_management_type)>{{ $risk_management_type_label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <select name="transaction_type" class="form-control custome-padding" data-title="{{__("Transaction Type")}}">
                            <option value="-1" disabled="disabled">{{__("Transaction Type")}}</option>
                            @foreach ($transactionTypes as $key => $type)
                                <option value="{{ $key }}" {{ isset($search['transactionType']) && $search['transactionType'] == $key ? 'selected' : '' }}>{{ __($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 col-sm-12">
                        <button id="searchBtn" type="submit" class="btn btn-outline-primary btn-fix btn-rounded" style="padding: 8px 40px">
                            {{__("Search")}}
                        </button>
                    </div>
                    @if( (isset($exportUrl) && \common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowedFraudRuleStructureFeature']) && Auth::user()->hasPermissionOnAction( Config::get('constants.defines.FRAUD_RULE_EXPORT')))
                        || (isset($exportUrl) && Auth::user()->hasPermissionOnAction( Config::get('constants.defines.APP_AML_RULE_EXPORT'))) )
                        <div class="col-md-4">

                                <div class="row">
                                    <div class="col-md-6 float-right">
                                        <select class="form-control show-tick rounded" name="file_type" id="file_type">
                                            @foreach($types as $key => $value)
                                                <option value="{{ $key }}">{{ __(strtoupper($value)) }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 float-right">
                                        <a id="exportBtn"
                                        href="{{route($exportUrl)}}" class="btn btn-outline-secondary btn-std-padding btn-block  btn-rounded">
                                        <span class="btn-icon">{{__("Export")}} </span>
                                        </a>
                                    </div>
                                </div>

                        </div>
                    @endif
                    @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowedFraudRuleStructureFeature']))
                        <div class="col-md-4">
                            <a href="{{route(Config::get('constants.defines.FRAUD_RULE_INDEX'))}}"
                               class="text-center btn btn-outline-danger btn-fix btn-rounded btn-sm">{{__("Clear Filters")}}
                            </a>
                        </div>
                    @endif
                </div>
            </form>
        </div>
    </div>
</div>
@section('css')
    @include('partials.css_blade.bootstrap-select')
@endsection
