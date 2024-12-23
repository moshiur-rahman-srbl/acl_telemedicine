@extends('layouts.adminca')

@section('content')

    @include('partials.page_heading')

    <div class="page-content fade-in-up">

        @include('partials.flash')

        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title d-flex justify-content-between">
                    <h3>{{ __($cmsInfo['subTitle']) }}</h3>
                    <a href="{{ route(Config::get('constants.defines.APP_USERS_CREATE')) }}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-plus-circle"></i>&nbsp;{{ __('Add') }}</a>
                </div>
            </div>

            <div class="ibox-body">
                <div class="row justify-content-between">
                    <div class="form-group col-md-4">
                        <form action="{{ route(Config::get('constants.defines.APP_USERS_DELETE'), 'move_to_trash') }}" method="POST" id="bulk-action-form">
                            @csrf
                            @method('DELETE')
                            <div class="input-group">
                                <select id="bulk_action" name="action" class="selectpicker show-tick form-control form-control-sm">
                                    <option value="">{{__("Bulk Action")}}</option>
                                    <option value="move_to_trash">{{__("Move to trash")}}</option>
                                </select>
                                <span class="input-group-btn">
                                    <button id="bulk-action-apply" type="button" class="btn btn-primary btn-std-padding">{{ __('Apply') }}</button>
                                </span>
                            </div>
                        </form>
                    </div>
                    @if(\common\integration\BrandConfiguration::allowAccessControlExport())
                        <div class="form-group col-md-4">
                            @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                                <form action="{{ route(Config::get('constants.defines.APP_USERS_INDEX')) }}" method="get">
                                    <div class="input-group">
                                        <select class="form-control selectpicker show-tick rounded" name="file_type" id="file_type">
                                            @foreach($export_types as $key => $value)
                                                <option value="{{ $key }}">{{ __(strtoupper($value)) }}</option>
                                            @endforeach
                                        </select>

                                        <span class="input-group-btn">
                                            <button type="submit" class="btn btn-primary btn-std-padding">{{ __('Export') }}</button>
                                        </span>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @endif
                    <div class="form-group col-md-4">
                        <form action="{{ route(Config::get('constants.defines.APP_USERS_INDEX')) }}" method="get">
                            <div class="input-group">
                                <input name="s" id="s" class="form-control form-control-sm" value="{{ $s  }}" type="text" placeholder="{{ __('Search for...') }}">
                                <span class="input-group-btn">
                                    <input class="btn btn-primary btn-std-padding" value="{{ __('Search') }}" name="search_button" type="submit">
                                </span>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div id="datatable_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer table-responsive">
                        <table class="table table-bordered table-hover" id="datatable" style="width: 100%;">
                            <thead class="thead-default thead-lg">
                            <tr>
                                <th style="width: 5%;">
                                    <label class="checkbox checkbox-ebony">
                                        <input type="checkbox" class="bulk-action" id="main-checkbox">
                                        <span class="input-span"></span>
                                    </label>
                                </th>
                                <th>{{__('ID')}}</th>
                                <th>{{__('Name')}}</th>
                                <th>{{__('Surname')}}</th>
                                <th>{{__('Email')}}</th>
                                <th>{{__('Status')}}</th>
                                @if(\common\integration\BrandConfiguration::allowAccessControlExport())
                                    <th>{{__('Roles')}}</th>
                                @endif
                                <th>{{__('Created At')}}</th>
                                <th>{{__('Updated At')}}</th>
                                <th>{{__('Last login')}}</th>
                                @if(\common\integration\BrandConfiguration::allowAccessControllUpdateAt())
                                    <th>{{__('Last Update Date')}}</th>
                                @endif
                                <th class="text-center">{{__('Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($users as $user)
                                <?php
                                $css = "color:rgb(32,90,224)";
                                $status = __('Pending');
                                if ($user->is_admin_verified == \App\Models\Profile::ADMIN_VERIFIED_APPROVED) {
                                    $css = "color:rgb(42,174,54)";
                                    $status = __('Active');
                                } elseif ($user->is_admin_verified == \App\Models\Profile::ADMIN_VERIFIED_NOT_APPROVED) {
                                    $css = "color:rgb(236,95,104)";
                                    $status = __('Inactive');
                                }
                                ?>
                                <tr>
                                    <td>
                                        <label class="checkbox checkbox-ebony">
                                            <input name="users_id[]" value="{{ $user->id }}" type="checkbox" class="bulk-checkbox" form="bulk-action-form">
                                            <span class="input-span"></span>
                                        </label>
                                    </td>
                                    <td>{{$user->id}}</td>
                                    @if (! empty($user->first_name))
                                        <td>{{ \common\integration\GlobalFunction::nameCaseConversion($user->first_name) }}</td>
                                        <td>{{ \common\integration\GlobalFunction::nameCaseConversion($user->last_name) }}</td>
                                    @else
                                        @php
                                            $formatted_name = \common\integration\GlobalUser::getNameSurnameByFullName($user->name);
                                        @endphp
                                        <td>{{ \common\integration\GlobalFunction::nameCaseConversion($formatted_name['name']) }}</td>
                                        <td>{{ \common\integration\GlobalFunction::nameCaseConversion($formatted_name['surname']) }}</td>
                                    @endif
                                    <td>{{$user->email}}</td>
                                    <td style="{{$css}}">{{$status}}</td>
                                    @if(\common\integration\BrandConfiguration::allowAccessControlExport())
                                        <td>{{ $user->useGroups->pluck('role.*.title')->flatten()->unique()->implode(', ') }}</td>
                                    @endif
                                    <td>{{$user->created_at}}</td>
                                    <td>{{$user->updated_at}}</td>
                                    <td>{{$user->login_at}}</td>
                                    @if(\common\integration\BrandConfiguration::allowAccessControllUpdateAt())

                                        <td>{{ @$user->userActionHistoriesForAccessControl->where('type',\App\Models\UserActionHistory::ACCESS_CONTROL_UPDATE_ACTION)->first()->created_at }}</td>
                                    @endif

                                    <td class="text-center">
                                        @if(isset($allowModifyHiddenMerchant) && $allowModifyHiddenMerchant)
                                            <a class="text-muted font-16 mr-1 ml-1"
                                               href="" title="{{__('Hide Merchant')}}">
                                                <i class="ti-check-box"></i>
                                            </a>
                                        @endif
                                        <a class="text-muted font-16 mr-1 ml-1" href="#"
                                           onclick="deleteAction('delete-form-{{$user->id}}')">
                                            <i class="ti-trash"></i>
                                        </a>
                                        <form id="delete-form-{{$user->id}}"
                                              action="{{route(Config::get('constants.defines.APP_USERS_DELETE'), $user->id)}}"
                                              method="post" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                        <a class="text-muted font-16 mr-1 ml-1"
                                           href="{{route(Config::get('constants.defines.APP_USERS_EDIT'), [$user->id,\App\User::USER_UPDATE_ACTION])}}">
                                            <i class="ti-pencil-alt"></i>
                                        </a>
                                        <a class="text-muted font-16 mr-1 ml-1"
                                           href="{{route(Config::get('constants.defines.APP_USERS_SHOW'), $user->id)}}">
                                            <i class="ti-eye"></i>
                                        </a>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">{{__('No data found')}}</td>
                                </tr>
                            @endforelse
                        </table>

                        {{----}}
                        <!--
                        {{--<div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">--}}
                            {{--<Form method="get" action="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}">--}}

                                {{--<input type="hidden" name="s" value="{{ $s }}">--}}
                                {{--@if(app()->getLocale() == 'tr')--}}
                                    {{--{{__('per page')}}--}}
                                {{--@endif--}}
                                {{--<strong>{{__('Show')}}</strong></div>--}}
                                {{--<select name="page_limit" class="form-control form-control-sm d-inline"--}}
                                        {{--onchange="this.form.submit()" style="width: auto;">--}}
                                    {{--<option value="10" <?php echo $page_limit == "10" ? 'selected="selected"' : ''  ?>>--}}
                                        {{--10--}}
                                    {{--</option>--}}
                                    {{--<option value="25" <?php echo $page_limit == "25" ? 'selected="selected"' : ''  ?>>--}}
                                        {{--25--}}
                                    {{--</option>--}}
                                    {{--<option value="50" <?php echo $page_limit == "50" ? 'selected="selected"' : ''  ?>>--}}
                                        {{--50--}}
                                    {{--</option>--}}
                                    {{--<option value="100" <?php echo $page_limit == "100" ? 'selected="selected"' : ''  ?>>--}}
                                        {{--100--}}
                                    {{--</option>--}}
                                {{--</select>--}}


                                {{--@if(app()->getLocale() == 'en')--}}
                                    {{--{{__('per page')}}--}}
                                {{--@endif--}}
                                {{--                                    {{__('per page')}}--}}


                            {{--</Form>--}}
                        {{--</div>--}}
                        {{--<div class="dataTables_paginate paging_simple_numbers pull-right">--}}
                            {{--{{$users->appends([--}}
                                {{--'s' => $s--}}
                            {{--])->links('partials.pagination')}}--}}
                        {{--</div>--}}

                        --!>
                    </div>

                </div>

                <div class="row justify-content-md-between">
                    <div class="col-sm-12 text-center col-md pt-2 text-md-left">
                        <?php $commonFunction = new \App\Utils\CommonFunction(); $commonFunction->totalRecords($users) ?>
                    </div>
                    <div class="col-sm-12 text-center col-md pt-2">
                        <Form method="get" action="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}">
                            <input type="hidden" name="s" value="{{ $s }}">
                            @if(app()->getLocale() == 'tr')
                                {{__('per page')}}
                            @endif
                            @include('partials.pagination_amount')
                            @if(app()->getLocale() == 'en')
                                {{__('per page')}}
                            @endif
                        </Form>
                    </div>
                    <div class="col-sm-12 text-center col-md pt-2 text-md-right">
                        <div class="dataTables_paginate paging_simple_numbers" style="box-sizing: unset">
                            {{ $users->appends(['s' => $s,'page_limit' => $page_limit])->links('partials.pagination')}}
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
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
        $(function () {
            // $('#datatable').DataTable({
            //     pageLength: 10,
            //     fixedHeader: true,
            //     responsive: true,
            //     "sDom": 'rtip',
            //     columnDefs: [{
            //         // targets: 'no-sort',
            //         // orderable: false,
            //         targets: [0 ],
            //         visible: true,
            //         orderable: false
            //     }]
            // });

            // var table = $('#datatable').DataTable();
            // $('#key-search').on('keyup', function () {
            //     table.search(this.value).draw();
            // });
            // $('#type-filter').on('change', function () {
            //     table.column(4).search($(this).val()).draw();
            // });

            $('#main-checkbox').click(function () {
                $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
            });

            $('.bulk-checkbox').change(function () {
                if ($('.bulk-checkbox:checked').length === $('.bulk-checkbox').length) {
                    $('#main-checkbox').prop('checked', true);
                } else {
                    $('#main-checkbox').prop('checked', false);
                }
            });

            $('#bulk-action-apply').click(function () {
                if ($('#bulk_action').val() === 'move_to_trash' && $('.bulk-checkbox:checked').length > 0) {
                    alertify.confirm('Are you sure to delete selected users?<br/> It\'s can\'t be undone.', function () {
                        $('#bulk-action-form').submit();
                    });
                }
            });
        });
    </script>
@endpush
