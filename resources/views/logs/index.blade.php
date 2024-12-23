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

                {{-- Search Box --}}
                @include('partials.searchbox-logs')
                {{-- Search Box END --}}

                <div class="row mt-4 pt-4">
                    <div class="col-md-12">
                        <div class="table-responsive">

                            {{-- Table --}}
                            @empty($logs)
                                <p class="text-center">{{ isset($search['search']) ? __('No data found') : '' }}</p>
                            @else
                                <p><span class="font-weight-bold">Results for date: </span>{{ $search['daterange'] }}</p>
                                <table class="table table-bordered table-hover font13 not-datatables">
                                    <thead class="thead-default">
                                    <tr>
                                        <th>{{ __('Panel') }}</th>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Log') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td class="align-top font-weight-bold">{{ ucfirst($log['panel']) }}</td>
                                            <td class="align-top">{{ $log['date'] }}</td>
                                            <td style="word-break: break-all;">{!! nl2br(e($log['data'])) !!}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endempty

                            @if (is_object($logs))
                                <div class="d-flex justify-content-between flex-wrap">

                                    {{-- Hidden Search Form --}}
                                    <div>
                                        <form method="get"
                                              action="{{ $selfUrls }}">
                                            <input type="hidden"
                                                   id="hidden_from_date"
                                                   name="from_date"
                                                   value="{{ $search['daterange'] }}">

                                            <input type="hidden"
                                                   id="hidden_search_phrase"
                                                   name="search_phrase"
                                                   value="{{ $search['search_phrase'] }}">

                                            <input type="hidden"
                                                   id="hidden_search_phrase"
                                                   name="search"
                                                   value="{{ $search['search'] }}">

                                        @if(!empty($search['panel']))
                                                @foreach($search['panel'] as $key => $value)
                                                    <input type="hidden" id="hidden_panel_{{$key}}" name="panel[]"
                                                           value="{{ $value }}">
                                                @endforeach
                                            @else
                                                <input type="hidden" id="hidden_panel" name="panel" value="">
                                            @endif

                                            @if(app()->getLocale() == 'tr')
                                                {{__('per page')}}
                                            @endif
                                            @include('partials.pagination_amount')
                                            @if(app()->getLocale() == 'en')
                                                {{__('per page')}}
                                            @endif

                                        </form>
                                    </div>
                                    {{-- Hidden Search Form END --}}

                                    {{-- Pagination Links --}}
                                    <div>
                                        <div class="dataTables_paginate paging_simple_numbers pull-right">
                                            <div class="px-3 px-md-0 py-2 py-md-auto">
                                                <div class="dataTables_paginate paging_simple_numbers pull-right">
                                                    {{ $logs->appends([
                                                            'search_phrase' => $search['search_phrase'],
                                                            'panel' => $search['panel'],
                                                            'search' => $search['search'],
                                                            'from_date' => $search['daterange'],
                                                            'page_limit' => $page_limit
                                                            ])->links('partials.pagination') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Pagination Links END --}}

                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
