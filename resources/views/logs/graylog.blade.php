@extends('layouts.adminca')

@section('content')

    @include('partials.page_heading')

    <div class="page-content fade-in-up pl-2 pr-2">

        @include('partials.flash')

        <div class="ibox form-control-air">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}}
                </div>
            </div>

            <div class="ibox-body">

                <form action="{{ $self_url }}" method="get">

                    <input type="hidden" name="page_limit" value="{{ $page_limit ?? 100 }}">

                    <div class="row justify-content-end">

                        <label class="col-md-1 form-group px-1">
                                <span id="dateprangepicker"
                                      class="bg-light p-4 btn-rounded btn">
                                    <i class="fa fa-calendar fa-2x"></i>
                                </span>

                            <input type="hidden" value="{{ $search['daterange'] }}" id="daterange" class="form-control"
                                   name="daterange" autocomplete="off" placeholder="{{ __('Date Range') }}" readonly/>
                        </label>

                        <label class="col-md-5 form-group px-3 my-2">
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
                        </label>

                        <label class="col-md-5 input-group-icon input-group-icon-right my-2">
                            <input type="text" value="{{ $search['search'] }}" id="search" class="form-control-rounded form-control" name="search" placeholder="{{ __('Search') }}" autofocus/>
                        </label>


                        <div class="col-md-12 form-group px-3 text-right">
                            @if(request()->query())
                                <a class="btn btn-link text-danger text-decoration-none" href="{{ $self_url }}">Clear Filter</a>
                                <button type="button" class="btn btn-outline-secondary" onclick="CreateTextFile()">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="height: 14px" class="mx-1">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    {{ __('Export') }}
                                </button>
                            @endif

                            <button type="submit" class="btn btn-outline-primary">{{ __('Search') }}</button>
                        </div>
                    </div>
                </form>

                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-default">
                                <tr>
                                    <th>{{ __('Log Message') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($logs as $log)
                                    <tr>
                                        <td style="line-break: anywhere">{{ $log['message']['message'] }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="100%" class="text-center py-3">No Log Fund!</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-12 text-center col-md-3 text-md-left">
                                {{ (new \App\Utils\CommonFunction())->totalRecords($logs) }}
                            </div>
                            <div class="col-sm-12 text-center col-md-3">
                                <form method="get" action="{{ $self_url }}">
                                    <input type="hidden" name="daterange" value="{{ $search['daterange'] ?? '' }}">
                                    <input type="hidden" name="search" value="{{ $search['search'] ?? '' }}">
                                    @if(app()->getLocale() == 'tr')
                                        {{__('per page')}}
                                    @endif
                                    @include('partials.pagination_amount')
                                    @if(app()->getLocale() == 'en')
                                        {{__('per page')}}
                                    @endif
                                </form>
                            </div>
                            <div class="col-sm-12 text-center col-md-6 text-md-right">
                                <div class="dataTables_paginate paging_simple_numbers pull-right">
                                    <div class="px-3 px-md-0 py-2 py-md-auto">
                                        <div class="dataTables_paginate paging_simple_numbers pull-right">
                                            {{ $logs->links('partials.pagination') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.fle-save-js')
    @include('partials.js_blade.bootstrap-select')

    <script>
        $(document).ready(function () {
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
                    "{{ __('Today') }}": [moment(), moment()],
                    "{{ __('Yesterday') }}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                    "{{ __('Last 7 Days') }}": [moment().subtract(6, 'days'), moment()],
                    "{{ __('Last 30 Days') }}": [moment().subtract(29, 'days'), moment()],
                    "{{ __('This Month') }}": [moment().startOf('month'), moment().endOf('month')],
                    "{{ __('Last Month') }}": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                "alwaysShowCalendars": true,
            });

            $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
                var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
                $("#daterange").val(dateranges);
            });
        });
    </script>

    <script>
        function CreateTextFile() {
            const file_name = 'logs.txt'
            const log_data = '{!! $logs_string !!}';
            const blob = new Blob([log_data], {
                type: "text/plain;charset=utf-8",
            });
            saveAs(blob, file_name);
        }
    </script>
@endpush
