@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up pl-2 pr-2">
        @include('partials.flash')
        <div class="ibox form-control-air d-flex">

            <div class="col-md-12">
                <div class="ibox-head">
                    <div class="ibox-title">
                        {{__($cmsInfo['subTitle'])}}
                    </div>
                </div>
                <div class="ibox-body">
                    <div class="row">
                        <form action="" method="get">
                            <div class="col-md-12 d-flex">
                                <div class="col-md-8 mb-1">
                                    <input value="{{ $search['date_range'] }}" placeholder="{{__('Date range')}}" type="text"
                                           id="dateprangepicker" class="rounded form-control"
                                           name="date_range"
                                           autocomplete="off" readonly/>
                                </div>
                                {{-- <div class="col-md-4 mb-1">
                                    <input value="{{ $search['name'] }}" placeholder="{{__('Name')}}" type="text"
                                        class="show-tick rounded form-control"
                                           name="name"
                                           autocomplete="off"/>
                                </div> --}}
                                <div class="col-md-2 mb-1">
                                    <button type="submit"
                                            class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                                        {{__("Submit")}}
                                    </button>
                                </div>

                            </div>
                        </form>


                        <div class="col-md-12 mb-2">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover font13 not-datatables">
                                    <thead class="thead-default">
                                    <tr>

                                        <th>{{__('ID')}}</th>
                                        <th>{{__('Created Time')}}</th>

                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($details as $detail)
                                        <tr>
                                            <td>{{ $detail->id }}</td>
                                            <td>{{ \App\Utils\Date::format(2, $detail->failed_login_time) }}</td>

                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="text-center" colspan="14">{{__('No data found')}}</td>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col col-md-6">
                            <Form method="get"
                                  action="{{route('visitLastLoginHistory')}}">
                                <input type="hidden" name="date_range" value="{{$search['date_range']}}">

                                @if(app()->getLocale() == 'tr')
                                    {{__('per page')}}
                                    @include('partials.pagination_amount')
                                    {{__('record')}}
                                @endif
                                @if(app()->getLocale() == 'en')
                                    {{__('Show')}}
                                    @include('partials.pagination_amount')
                                    {{__('per page')}}
                                @endif
                            </Form>
                        </div>
                        <div class="col col-md-6">
                            <div class="dataTables_paginate paging_simple_numbers pull-right">
                                {{
                                    $details->appends([
                                        'date_range' => $search['date_range'],
                                        'page_limit' => $page_limit
                                    ])->links('partials.pagination')
                                }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- /.box -->
        </div>
    </div>


@endsection

@section('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.sweetalert')

@endsection
@section('js')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.sweetalert')

    <script>
        $('document').ready(function(){

            $('input[name="first_time_block"]').val(0);
            $('input[name="second_time_block"]').val(0);
            $('input[name="third_time_block"]').val(0);

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
        });

    </script>

@endsection
