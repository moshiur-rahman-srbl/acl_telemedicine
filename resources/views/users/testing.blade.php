@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title">
                    {{__($cmsInfo['subTitle'])}} <a href="{{route(Config::get('constants.defines.APP_USERS_CREATE'))}}"
                                                    class="ml-3 btn btn-sm btn-primary pull-right"><i
                                class="fa fa-plus-circle"></i> {{__('Add')}}</a>
                </div>
            </div>
            <div class="ibox-body">
                <div class="row mb-4">
                    <div class="col-sm-3">
                        <div class="input-group">
                            <select id="bulkaction" name="bulkaction" class="selectpicker show-tick form-control mr-2">
                                <option value="">{{__("Bulk Action")}}</option>
                                <option value="movetotrash">{{__("Move to trash")}}</option>
                            </select>
                            <span class="input-group-btn">
                                            <button type="button" class="btn btn-primary">{{__('Apply')}}</button>
                                {{--<input class="btn btn-primary" value="{{__('Apply')}}" name="" type="submit" onclick="buckDeleteAction()">--}}
                                </span>
                        </div>
                    </div>
                    <div class="col-sm-5"></div>
                    <div class="col-sm-4">
                        {{--<input class="form-control form-control-rounded form-control-solid" id="key-search" type="text" placeholder="Search ...">--}}
                        <form action="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}" method="get">
                            <div class="input-group">
                                <input name="s" id="s" class="form-control" value="{{ $s  }}"
                                       type="text"
                                       placeholder="{{__('Search for...')}}">
                                <span class="input-group-btn">
                                    <input class="btn btn-primary" value="{{__('Search')}}" name="search_button"
                                           type="submit">
                                        </span>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="table-responsive row">
                    <div id="datatable_wrapper" class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer">
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
                                <th>{{__('Email')}}</th>
                                <th>{{__('Status')}}</th>
                                <th>user type</th>
                                <th>merchant_id</th>
                                <th>Company Id</th>
                                <th>{{__('Created At')}}</th>
                                <th>{{__('Updated At')}}</th>
                                <th class="text-center">{{__('Actions')}}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($users as $user)
                                <?php
                                        $merchant = \App\Models\Merchant::where('user_id', $user->merchant_parent_user_id)->first();
                                        $merchant_id = $merchant->id ?? 0

                                ?>
                                <tr>
                                    <td>
                                        <label class="checkbox checkbox-ebony">
                                            <input name="userCheckbox[]" value="{{$user->id}}" type="checkbox"
                                                   class="bulk-action">
                                            <span class="input-span"></span>
                                        </label>
                                    </td>
                                    <td>{{$user->id}}</td>
                                    <td>{{$user->name}}</td>
                                    <td>{{$user->email}}</td>
                                    <td>{{$user->status == 1 ? __("Active"):__("Inactive")}}</td>
                                    <td>{{ $user->user_type }}</td>
                                    <td>{{ $merchant_id }}</td>
                                    <td>{{ $user->company_id }}</td>
                                    <td>{{$user->created_at}}</td>
                                    <td>{{$user->updated_at}}</td>

                                    <td class="text-center">
                                        <a class="text-muted font-16 mr-1 ml-1" href="#"
                                           onclick="deleteAction('delete-form-{{$user->id}}')">
                                            <i class="ti-trash"></i>
                                        </a>
                                        <form id="delete-form-{{$user->id}}"
                                              action="{{route('testingdelete', $user->id)}}"
                                              method="post" style="display: none;">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7">{{__('No data found')}}</td>
                                </tr>
                            @endforelse
                        </table>
                        <div class="dataTables_info" id="datatable_info" role="status" aria-live="polite">
                            <Form method="get" action="{{route(Config::get('constants.defines.APP_USERS_INDEX'))}}">

                                <input type="hidden" name="s" value="{{ $s }}">
                                @if(app()->getLocale() == 'tr')
                                    {{__('per page')}}
                                @endif
                                {{--<strong>{{__('Show')}}</strong></div>--}}
                                <select name="page_limit" class="form-control form-control-sm d-inline"
                                        onchange="this.form.submit()" style="width: auto;">
                                    <option value="10" <?php echo $page_limit == "10" ? 'selected="selected"' : ''  ?>>
                                        10
                                    </option>
                                    <option value="25" <?php echo $page_limit == "25" ? 'selected="selected"' : ''  ?>>
                                        25
                                    </option>
                                    <option value="50" <?php echo $page_limit == "50" ? 'selected="selected"' : ''  ?>>
                                        50
                                    </option>
                                    <option value="100" <?php echo $page_limit == "100" ? 'selected="selected"' : ''  ?>>
                                        100
                                    </option>
                                </select>


                                @if(app()->getLocale() == 'en')
                                    {{__('per page')}}
                                @endif
                                {{--                                    {{__('per page')}}--}}


                            </Form>
                        </div>
                        <div class="dataTables_paginate paging_simple_numbers pull-right">
                            {{--{{$users->appends([--}}
                                {{--'s' => $s--}}
                            {{--])->links('partials.pagination')}}--}}
                        </div>
                    </div>

                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
    </div>

@endsection
@section('js')
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
                $('.bulk-action').click();
            })
        });
    </script>
@endsection
