@extends('layouts.adminca')

@section('content')

@include('partials.page_heading')
    {{-- delete form --}}
    <form action="{{ route(Config::get('constants.defines.APP_ROLES_DELETE'), 'bulk_delete') }}" method="POST" id="bulk-delete-form">@csrf @method('DELETE')</form>
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_ROLES_CREATE'))}}" class="ml-3 btn btn-sm btn-primary pull-right"> <i class="fa fa-plus-circle"></i> {{__('Add')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <div class="flexbox mb-4">
                    <div class="flexbox">
                        <div class="form-group">
                            <label class="mb-0 mr-2">{{ __("Bulk Action") }}</label>
                            <div class="input-group">
                                <select class="selectpicker form-control form-control-sm mr-2" id="bulk-action-type" title="{{ __("Bulk Action") }}" data-style="btn-solid pt-2 pr-8 pl-3">
                                    <option value="move-to-trash">{{ __("Move to trash") }}</option>
                                </select>
                                <div class="input-group-btn">
                                    <button class="btn btn-primary btn-std-padding" id="bulk_action">{{ __("Apply") }}</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group-icon input-group-icon-left mr-3">
                        <span class="input-icon input-icon-right font-16"><i class="ti-search"></i></span>
                        <input class="form-control form-control-rounded form-control-solid" id="key-search" type="text" placeholder={{__("Search")}}>
                    </div>
                </div>
                <div class="table-responsive row">
                    <table class="table table-bordered table-hover not-datatables" id="datatable1" style="width: 100%;">
                        <thead class="thead-default thead-lg">

                        <tr>
                            <th style="width: 5%;">
                                <label class="checkbox checkbox-ebony">
                                    <input type="checkbox" id="main-checkbox">
                                    <span class="input-span"></span>
                                </label>
                            </th>
                                <th >{{__('ID')}}</th>
                                <th >{{__('Title')}}</th>
                                @if(\common\integration\BrandConfiguration::call
                                ([\common\integration\Brand\Configuration\Backend\BackendAdmin::class, 'allowAccessRoleTurkishVersion']))

                                    <th >{{__('Title TR')}}</th>

                                @endif
                                <th >{{__('Created At')}}</th>
                                <th >{{__('Updated At')}}</th>
                                <th class="no-sort text-center">{{__('Actions')}}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if(!empty($roles))
                            {{--{{dd($roles)}}--}}
                            @foreach($roles as $role)
                            <tr>
                                <td>
                                    <label class="checkbox checkbox-ebony">
                                        <input name="roles_id[]" value="{{ $role->id }}" type="checkbox" class="bulk-action" form="bulk-delete-form">
                                        <span class="input-span"></span>
                                    </label>
                                </td>
                                <td>{{$role->id}}</td>
                                <td>{{__($role->title)}}</td>

                                @if(\common\integration\BrandConfiguration::call
                                ([\common\integration\Brand\Configuration\Backend\BackendAdmin::class, 'allowAccessRoleTurkishVersion']))

                                    <td >{{__($role->title_tr)}}</td>

                                @endif


                                <td>{{$role->created_at}}</td>
                                <td>{{$role->updated_at}}</td>
                                <td class="text-center">
                                    <a class="text-muted font-16 mr-1 ml-1" data-id="{{$role->id}}"  href="#" onclick="deleteAction('delete-form-{{$role->id}}')">
                                        <i class="ti-trash"></i>
                                    </a>
                                    <form id="delete-form-{{$role->id}}" action="{{route(Config::get('constants.defines.APP_ROLES_DELETE'), $role->id)}}" method="post" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                    <a class="text-muted font-16 mr-1 ml-1" href="{{route(Config::get('constants.defines.APP_ROLES_EDIT'), $role->id)}}">
                                        <i class="ti-pencil-alt"></i>
                                    </a>
                                    {{--<a class="text-muted font-16 mr-1 ml-1" href="{{route(Config::get('constants.defines.APP_ROLES_EDIT'), $role->id)}}">--}}
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
@push('css')
    @include('partials.css_blade.alertify')
@endpush

@push('scripts')
    @include('partials.js_blade.validate')
    @include('partials.js_blade.alertify')
<script>
        $(function() {
            $('#datatable1').DataTable({
                pageLength: 10,
                fixedHeader: true,
                responsive: true,
                "sDom": 'rtip',
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false
                }],
                "language": {
                    "sInfo":          "{{__("Total _END_ Records Found")}}",
                    "oPaginate": {
                        "sFirst":    "{{__('First')}}",
                        "sLast":     "{{__('Last')}}",
                        "sNext":     "{{__('Next')}}",
                        "sPrevious": "{{__('Previous')}}"
                    },

                }
            });

            var table = $('#datatable1').DataTable();
            $('#key-search').on('keyup', function() {
                table.search(this.value).draw();
            });

            $('#type-filter').on('change', function() {
                table.column(4).search($(this).val()).draw();
            });

            // Toggle the bulk selection checkboxes start
            $('#main-checkbox').click(function() {
               $('.bulk-action').prop('checked', $(this).prop('checked'));
            });
            $('.bulk-action').click(function () {
                if ($('.bulk-action:checked').length == $('.bulk-action').length) {
                    $('#main-checkbox').prop('checked', true);
                } else {
                    $('#main-checkbox').prop('checked', false);
                }
            });
            // Toggle the bulk selection checkboxes end

            $('#bulk_action').click(function () {
                if ($('#bulk-action-type').val() == 'move-to-trash' && $('.bulk-action:checked').length > 0) {
                    deleteAction('bulk-delete-form');
                }
            });
        });
    </script>
@endpush
