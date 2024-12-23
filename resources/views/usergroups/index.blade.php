@extends('layouts.adminca')
{{--<style>--}}
{{--    @media (max-width: 568px) {--}}
{{--        .flexbox {--}}
{{--            all: unset!important;--}}
{{--        }--}}
{{--    }--}}
{{--</style>--}}
@section('content')
@include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">

                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_USERGROUPS_CREATE'))}}" class="ml-3 btn btn-sm btn-primary pull-right"> <i class="fa fa-plus-circle"></i> {{__('Add')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <div class="flexbox mb-4 row row-cols-auto">
                    <div class="flexbox mb-4 ">
                        <label class="mb-0 mr-2">{{__("Bulk Action")}}</label>
                        <select class="selectpicker show-tick form-control mr-2" title="{{__("Bulk Action")}}" data-style="btn-solid" data-width="150px">
                            <option>{{__("Move to trash")}}</option>
                        </select>
                         <button class="btn btn-primary">{{__('Apply')}}</button>
                    </div>

                    <div class="form-group col-md-4">
                        <form action="{{ route(Config::get('constants.defines.APP_USERGROUPS_INDEX')) }}" method="get">
                            <div class="input-group">
                                <input name="search_key" id="search_key" class="form-control form-control-sm" value="{{ $search['search_key'] ?? ''  }}" type="text" placeholder="{{ __('Search for...') }}">
                                <span class="input-group-btn">
                                    <input class="btn btn-primary" value="{{ __('Search') }}" name="search_button" type="submit">
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive row">
                    <table class="table table-bordered table-hover" style="width: 100%;">
                        <thead class="thead-default thead-lg">

                        <tr>
                            <th style="width: 5%;">
                                <label class="checkbox checkbox-ebony">
                                    <input type="checkbox" class="bulk-action" id="main-checkbox">
                                    <span class="input-span"></span>
                                </label>
                            </th>
                                <th >{{__('ID')}}</th>
                                <th >{{__('Group Name')}}</th>
                                <th >{{__('Dashboard URL')}}</th>
                                <th >{{__('Created At')}}</th>
                                <th class="no-sort text-center">{{__('Actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(!empty($usergroups))
                            {{--{{dd($usergroups)}}--}}
                            @foreach($usergroups as $usergroup)
                            <tr>
                                <td>
                                    <label class="checkbox checkbox-ebony">
                                        <input name="pageCheckbox[]" value="{{$usergroup->id}}" type="checkbox" class="bulk-action bulk-checkbox">
                                        <span class="input-span"></span>
                                    </label>
                                </td>
                                <td>{{$usergroup->id}}</td>
                                <td>{{__($usergroup->group_name)}}</td>
                                <td>{{__($usergroup->dashboard_url)}}</td>
                                <td>{{$usergroup->created_at}}</td>
                                <td class="text-center">
                                    <a class="text-muted font-16 mr-1 ml-1" data-id="{{$usergroup->id}}"  href="#" onclick="deleteAction('delete-form-{{$usergroup->id}}')">
                                        <i class="ti-trash"></i>
                                    </a>
                                    <form id="delete-form-{{$usergroup->id}}" action="{{route(Config::get('constants.defines.APP_USERGROUPS_DELETE'), $usergroup->id)}}" method="post" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <a class="text-muted font-16 mr-1 ml-1" href="{{route(Config::get('constants.defines.APP_USERGROUPS_EDIT'), $usergroup->id)}}">
                                        <i class="ti-pencil-alt"></i>
                                    </a>
                                    {{--<a class="text-muted font-16 mr-1 ml-1" href="{{route('usergroups.showViewForm', $usergroup->id)}}">--}}
                                        {{--<i class="ti-eye"></i>--}}
                                    {{--</a>--}}

                                </td>
                            </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
                <div class="row justify-content-md-between">
                    <div class="col-sm-12 text-center col-md pt-2 text-md-left">
                        @php
                            $commonFunction = new \App\Utils\CommonFunction();
                            $commonFunction->totalRecords($usergroups);
                        @endphp
                    </div>
                    <div class="col-sm-12 text-center col-md pt-2">
                        <Form method="get" action="{{ route(Config::get('constants.defines.APP_USERGROUPS_INDEX')) }}">

                            <input type="hidden" id="hidden_search_key" name="search_key" value="{{ $search['search_key'] }}">
                            <input type="hidden" id="hidden_page_limit" name="page_limit" value="{{ $search['page_limit'] }}">


                            @if (app()->getLocale() == 'tr')
                                {{ __('per page') }}
                            @endif
                            @include('partials.pagination_amount')
                            @if (app()->getLocale() == 'en')
                                {{ __('per page') }}
                            @endif

                        </Form>
                    </div>
                    <div class="col-sm-12 text-center col-md pt-2 text-md-right">
                        <div class="dataTables_paginate paging_simple_numbers" style="box-sizing: unset">
                            {{ $usergroups->appends([
                                    'page_limit' => $search['page_limit'],
                                    'search_key' => $search['search_key'],
                                ])->links('partials.pagination') }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection
@push('scripts')

    @include('partials.js_blade.validate')
    @include('partials.js_blade.alertify')
<script>
        $(function() {
            $('#datatable').DataTable({
                pageLength: 10,
                fixedHeader: true,
                responsive: true,
                "sDom": 'rtip',
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }]
            });

            var table = $('#datatable').DataTable();
            $('#key-search').on('keyup', function() {
                table.search(this.value).draw();
            });
            $('#type-filter').on('change', function() {
                table.column(4).search($(this).val()).draw();
            });

            $('#main-checkbox').change(function () {
                $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
            });

            $('.bulk-checkbox').change(function () {
                if ($('.bulk-checkbox:checked').length === $('.bulk-checkbox').length) {
                    $('#main-checkbox').prop('checked', true);
                } else {
                    $('#main-checkbox').prop('checked', false);
                }
            });

        });
    </script>
@endpush
