
<form id="" method="get" action="">
    @if($is_enable_search_filter)
    <div class="row font13">
        <div class="row mt-3">
            <div class="col-md-3">
                <select name="global_merchant_ids[]" class="selectpicker form-control"
                        data-title="{{__('Global Merchant Id')}}" data-live-search="true"
                        multiple data-actions-box="true">
                    @foreach($global_merchant_ids as $global_merchant)
                        <option value="{{ $global_merchant }}" {{collect($search['global_merchant_ids'])->contains($global_merchant) ? 'selected' : ''}}>{{
                        $global_merchant }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="iks_merchant_ids[]" class="selectpicker form-control"
                        data-title="{{ __(':brand_name Merchant Id', ['brand_name' =>
                        \common\integration\Utility\Str::titleCase(config('brand.name'))]) }}"
                        data-live-search="true"
                        multiple data-actions-box="true">
                    @foreach($iks_merchant_ids as $iks_merchant_id)
                        <option value="{{ $iks_merchant_id }}" {{collect($search['iks_merchant_ids'])->contains
                        ($iks_merchant_id) ? 'selected' : ''}}>{{ $iks_merchant_id }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input class="form-control" name="tax_no" value="{{ $search['tax_no']  }}" type="text"
                       placeholder="{{__("Tax No")}}"/>
            </div>
            <div class="col-md-3">
                <select name="iks_status[]" class="selectpicker form-control"
                        data-title="{{__('Iks Status')}}" data-live-search="true"
                        multiple data-actions-box="true">

                    @foreach($iks_status as $key => $status)
                        <option value="{{ $key }}" {{collect($search['iks_status'])->contains($key)
                        ?'selected' : ''}}> {{ __($status) }} </option>
                    @endforeach

                </select>
            </div>
        </div>
        <div class="row mt-3">
            <div class="col-md-3">
                <input class="form-control" name="merchant_name" value="{{ $search['merchant_name']  }}" type="text"
                       placeholder="{{__("Merchant Name")}}"/>
            </div>

            <div class="col-md-3">
                <input class="form-control" name="trade_name" value="{{ $search['trade_name']  }}" type="text"
                       placeholder="{{__("Trade Name")}}"/>
            </div>

            <div class="col-md-3">
                <input class="form-control" name="registration_date" value="{{ $search['registration_date']  }}"
                       type="text" id="dateprangepicker" readonly
                       placeholder="{{__("IKS Registration Date")}}"/>
            </div>

        </div>
    </div>
    @endif

    <div class="row mb-4 mt-4">
        <div class="col-12 text-md-right">
            <div class="d-flex flex-column flex-md-row justify-content-around justify-content-md-end align-items-sm-stretch align-items-md-center pr-3 pl-3 pl-md-0">
                @if($is_enable_search_filter)
                    <button type="submit"
                            id="searchbtn"
                            class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                        {{__("Submit")}}
                    </button>
                @endif
                @if(isset($exportRouteName))
                    @if(Auth::user()->hasPermissionOnAction($exportRouteName))
                        <div class="row">
                            <div class="col-sm-12 ml-2">
                                <button type="button" id="export-btn" href="{{route($exportRouteName)}}" class="btn
                                btn-primary
                                pull-right
                                btn-std-padding"><span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-caret-right"></i></span></button>
                            </div>
                        </div>
                    @endif
                @endif

            </div>
        </div>
    </div>
    {{-- Buttons END --}}

</form>

@push('css')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.select2')
    @include('partials.css_blade.daterangepicker')
    <style>
        .custome-padding div.bs-actionsbox{
            width: 100% !important;
        }
    </style>
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.select2')
    @include('partials.js_blade.daterangepicker')
    <script>
        (function ($) {
            $.fn.selectpicker.defaults = {
                noneSelectedText: "{{__('Nothing selected')}}",
                selectAllText: "{{__('Select All')}}",
                deselectAllText: "{{__('Deselect All')}}"
            };
        })(jQuery);

        $('#dateprangepicker').daterangepicker({
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

        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#daterange").val(dateranges);
        });
    </script>
    <script>
        $(document).ready(function () {
            $('#export-btn').click(function () {
                $(this).closest('form').attr('action', $(this).attr('href')).submit();
            });
        });
    </script>
@endpush



