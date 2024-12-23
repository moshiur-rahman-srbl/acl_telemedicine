@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_PAGES_CREATE'))}}" class="ml-3 btn btn-sm btn-primary pull-right"> <i class="fa fa-plus-circle"></i> {{__('Add')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <div class="flexbox mb-4">
                    <div class="flexbox">
                        <label class="mb-0 mr-2">{{__("Bulk Action")}}</label>
                        <select class="selectpicker show-tick form-control mr-2" title="{{__("Bulk Action")}}" data-style="btn-solid" data-width="150px">
                            <option>{{__("Move to trash")}}</option>
                        </select>
                         <button class="btn btn-primary">{{__('Apply')}}</button>
                    </div>

                    <div class="input-group-icon input-group-icon-left mr-3">
                        <span class="input-icon input-icon-right font-16"><i class="ti-search"></i></span>
                        <input class="form-control form-control-rounded form-control-solid" id="key-search" type="text" placeholder="Search">
                    </div>
                </div>
                <div class="table-responsive row">
                    <table class="table table-bordered table-hover not-datatables" id="datatable">
                        <thead class="thead-default thead-lg">
                            <tr>
                                <th class="no-sort">
                                    <label class="checkbox checkbox-ebony">
                                        <input type="checkbox" class="bulk-action" id="main-checkbox">
                                        <span class="input-span"></span>
                                    </label>
                                </th>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Module Name')}}</th>
                                <th>{{__('Sub Module Name')}}</th>
                                <th>{{__('Page Name')}}</th>
                                <th>{{__('Method Name')}}</th>
                                <th>{{__('Method Type')}}</th>
                                <th>{{__('Created On')}}</th>
                                <th class="no-sort text-center">{{__('Action')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(!empty($pages))
                            {{--{{dd($pages)}}--}}
                            @foreach($pages as $page)
                            <tr>
                                <td>
                                    <label class="checkbox checkbox-ebony">
                                        <input name="pageCheckbox[]" value="{{$page->id}}" type="checkbox" class="bulk-action">
                                        <span class="input-span"></span>
                                    </label>
                                </td>
                                <td>{{$page->id}}</td>
                                <td>{{__($page->modules['name'])}}</td>
                                <td>{{__($page->submodules['name'])}}</td>
                                <td>{{__($page->name)}}</td>
                                <td>{{__($page->method_name)}}</td>
                                <td>{{__($page->method_type)}}</td>
                                <td>{{$page->created_at}}</td>
                                <td class="text-center">
                                    <a class="text-muted font-16 mr-1 ml-1" data-id="{{$page->id}}"  href="#" onclick="deleteAction('delete-form-{{$page->id}}')">
                                        <i class="ti-trash"></i>
                                    </a>
                                    <form id="delete-form-{{$page->id}}" action="{{route(Config::get('constants.defines.APP_PAGES_DELETE'), $page->id)}}" method="post" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <a class="text-muted font-16 mr-1 ml-1" href="{{route(Config::get('constants.defines.APP_PAGES_EDIT'), $page->id)}}">
                                        <i class="ti-pencil-alt"></i>
                                    </a>
                                    {{--<a class="text-muted font-16 mr-1 ml-1" href="{{route('pages.view', $page->id)}}">--}}
                                        {{--<i class="ti-eye"></i>--}}
                                    {{--</a>--}}

                                </td>
                            </tr>
                            @endforeach
                        @endif
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
@endsection
@section('js')
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
            
            $('#main-checkbox').click(function(){
               $('.bulk-action').click();
            })
        });
    </script>
@endsection    