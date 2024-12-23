
<form class="pt-2 pb-4" action="" method="GET">
    <div class="row">
        <div class="col-sm-6 col-md-3 form-group">
            <input type="text"
                   value="{{$search['date_range'] ?? ''}}"
                   id="datepicker1"
                   class="form-control"
                   name="date_range"
                   autocomplete="off"
                   placeholder="{{__('Date Range')}}"
                   readonly/>
        </div>
        <div class="col-sm-6 col-md-3 form-group">
            <input class="form-control rounded" type="text"
                   name="application_id" value="{{$search['application_id']}}"
                   placeholder="{{__('Application ID')}}" />
        </div>
        <div class="col-sm-6 col-md-3 form-group">
            <input class="form-control rounded" type="text"
                   name="merchant_name" value="{{$search['merchant_name']}}"
                   placeholder="{{__('Merchant Name')}}" />
        </div>
        <div class="col-sm-6 col-md-3 form-group">
            <input class="form-control rounded" type="text"
                   name="auth_person_phone" value="{{$search['auth_person_phone']}}"
                   placeholder="{{__('Phone')}}" />
        </div>
        <div class="col-sm-6 col-md-3 form-group">
            <input class="form-control rounded" type="text"
                   name="auth_person_email" value="{{$search['auth_person_email']}}"
                   placeholder="{{__('Email')}}" />
        </div>
        <div class="col-sm-6 col-md-3 form-group">
            <input class="form-control rounded" type="text"
                   name="website" value="{{$search['website']}}"
                   placeholder="{{__('Website')}}" />
        </div>
        <div class="col-sm-6 col-md-3 form-group">
            <select class="form-control rounded" name="status">
                <option value="{{\App\Models\MerchantApplication::ALL_STATUS}}">{{__("Status")}}</option>
                @foreach($statuses as $key=>$value)
                    <option value="{{$key}}" {{$search['status'] == $key ? 'selected' : ''}}>{{__($value)}}</option>
                @endforeach
            </select>
        </div>
        @if(\common\integration\BrandConfiguration::call([common\integration\Brand\Configuration\All\Mix::class, 'isAllowFilterDocumentAndAssessmentStatus']))
        <div class="col-sm-6 col-md-3 form-group">
            <select class="form-control rounded" name="information_document_control_status">
                <option value="{{\App\Models\MerchantApplication::ALL_STATUS}}">{{__("Information Document Control Status")}}</option>
                @foreach($document_assessment_statuses as $key=>$value)
                    <option value="{{$key}}" {{$search['information_document_control_status'] == $key ? 'selected' : ''}}>{{__($value)}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-sm-6 col-md-3 form-group">
            <select class="form-control rounded" name="risk_compliance_assessment_status">
                <option value="{{\App\Models\MerchantApplication::ALL_STATUS}}">{{__("Risk Compliance Assessment Status")}}</option>
                @foreach($document_assessment_statuses as $key=>$value)
                    <option value="{{$key}}" {{$search['risk_compliance_assessment_status'] == $key ? 'selected' : ''}}>{{__($value)}}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-sm-6 col-md-3 form-group">
            <button class="p-2 btn btn-outline-primary btn-rounded btn-block"
                    type="submit"
                    name="search_submit"
                    value="search">
                {{__('Search')}}
            </button>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 col-md-3"></div>
        <div class="col-sm-6 col-md-3">
            @if(\common\integration\BrandConfiguration::call([common\integration\Brand\Configuration\Backend\BackendMix::class, 'isAllowIframeMerchantApplication']))
                <select class="form-control rounded" name="webform_application_status">
                    @foreach(\App\Models\MerchantApplication::WEB_FORM_STATUSES as $key=>$value)
                        <option value="{{$key}}" {{$search['webform_application_status'] == $key ? 'selected' : ''}}>{{__($value)}}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div class="col-sm-6 col-md-3">
            <button type="button" id="add-app" class="p-2 btn btn-outline-info btn-rounded btn-block">
                <i class="fa fa-plus pr-2"></i>{{__('Add New')}}
            </button>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="input-group">
                <select class="form-control rounded-start-pill w-50 shadow-none" name="file_type">
                    @forelse(\App\Models\MerchantReportHistory::FORMAT_LIST as $key => $value)
                        <option value="{{$key}}">{{\common\integration\Utility\Str::upperCase($value)}}</option>
                    @empty
                    @endforelse
                </select>
                <button class="p-2 btn btn-outline-secondary  w-50 rounded-end-pill" type="submit" name="export_submit" value="export">
                    {{__("Export")}}<i class="pl-2 fa fa-caret-right"></i>
                </button>
                <input type="hidden" name="page" value="{{request()->input('page')}}" />
            </div>
        </div>
    </div>
</form>

@push('styles')
    @include('partials.css_blade.daterangepicker')
@endpush
@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.daterangepicker')
    <script>
        $(document).ready(function(){
            $('#datepicker1').daterangepicker({
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
        });
    </script>
@endpush
