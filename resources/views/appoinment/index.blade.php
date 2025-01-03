@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title d-flex justify-content-between">
                <h3>{{ __($cmsInfo['subTitle']) }}</h3>
                <a href="{{ route(Config::get('constants.defines.APP_APPOINMENT_CREATE')) }}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-plus-circle"></i>&nbsp;{{ __('Add') }}</a>

                <!-- <h3>{{ __('Appointments') }}</h3>



                    @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_APPOINMENT_CREATE')))
                        <a href="{{ route(Config::get('constants.defines.APP_APPOINMENT_CREATE')) }}"
                           class="ml-3 btn btn-sm btn-primary pull-right"><i
                                class="fa fa-plus-circle"></i>&nbsp;{{ __('Add') }}</a>
                    @endif -->
                </div>
            </div>

            <div class="ibox-body">
                <div class="row justify-content-between">
                    @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_APPOINMENT_DELETE')))
                        <div class="form-group col-md-3">
                            <form
                                action="{{ route(Config::get('constants.defines.APP_APPOINMENT_DELETE'), \App\Models\Appoinment::MOVE_TO_TRASH) }}"
                                method="POST" id="bulk-action-form">
                                @csrf
                                @method('DELETE')
                                <div class="input-group">
                                    <select id="bulk_action" name="action"
                                            class="selectpicker show-tick form-control form-control-sm">
                                        <option value="">{{ __("Bulk Action") }}</option>
                                        <option
                                            value="{{ \App\Models\Appoinment::MOVE_TO_TRASH }}">{{ __("Move to trash") }}</option>
                                    </select>
                                    <span class="input-group-btn">
                                    <button id="bulk-action-apply" type="button"
                                            class="btn btn-primary btn-std-padding">{{ __('Apply') }}</button>
                                </span>
                                </div>
                            </form>
                        </div>
                    @endif
                    <div class="form-group col-md-3">
                        <form action="{{ route(Config::get('constants.defines.APP_APPOINMENT_INDEX')) }}" method="get">
                            <div class="input-group">
                                <input name="filter_key" id="filter_key" class="form-control form-control-sm"
                                       value="{{ $filters['filter_key'] }}"
                                       type="text" placeholder="{{ __('Search for...') }}">
                                <span class="input-group-btn">
                                    <input class="btn btn-primary btn-std-padding" value="{{ __('Filter') }}"
                                           name="search_button" type="submit">
                                </span>
                            </div>
                        </form>
                    </div>
                </div>
{{--                 {{dd(session()->get('permittedRouteNames'))}}--}}
                <div class="row">
                    <div id="datatable_wrapper"
                         class="dataTables_wrapper container-fluid dt-bootstrap4 no-footer table-responsive">
                        <table class="table table-bordered table-hover" id="datatable" style="width: 100%;">
                            <thead class="thead-default thead-lg">
                            <tr>
                                <th style="width: 5%;">
                                    <label class="checkbox checkbox-ebony">
                                        <input type="checkbox" class="bulk-action" id="main-checkbox">
                                        <span class="input-span"></span>
                                    </label>
                                </th>
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Doctor ID') }}</th>
                                <th>{{ __('Patient ID') }}</th>
                                <th>{{ __('Appointment Date & Time') }}</th>
                                <th>{{ __('Notes') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Updated At') }}</th>
                                <th class="text-center">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($models as $model)
                                <tr>
                                    <td>
                                        <label class="checkbox checkbox-ebony">
                                            <input name="ids[]" value="{{ $model->id }}" type="checkbox"
                                                   class="bulk-checkbox" form="bulk-action-form">
                                            <span class="input-span"></span>
                                        </label>
                                    </td>
                                    <td>{{ $model->id }}</td>
                                    <td>{{ $model->Doctor->name ?? '' }}</td>
                                    <td>{{ $model->Patient->name ?? '' }}</td>
                                    <td>{{ $model->appointment_date_time }}</td>
                                    <td>{{ $model->notes }}</td>
                                    <td>

                                        @switch($model->status)
                                            @case(1)
                                                {{ __('Scheduled') }}
                                                @break
                                            @case(2)
                                                {{ __('Completed') }}
                                                @break
                                            @case(3)
                                                {{ __('Cancelled') }}
                                                @break
                                            @default
                                                {{ __('Unknown') }}
                                        @endswitch
                                    </td>
                                    <td>{{ $model->created_at }}</td>
                                    <td>{{ $model->updated_at }}</td>
                                    <td class="text-center">
                                        @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_APPOINMENT_DELETE')))

                                            <a class="text-muted font-16 mr-1 ml-1" href="#"
                                               onclick="deleteAction('delete-form-{{$model->id}}')">
                                                <i class="ti-trash"></i>{{$model->id}}
                                            </a>
                                            <form id="delete-form-{{$model->id}}"
                                                  action="{{ route(Config::get('constants.defines.APP_APPOINMENT_DELETE'), $model->id) }}"
                                                  method="post" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                        @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_APPOINMENT_EDIT')))
                                            <a class="text-muted font-16 mr-1 ml-1"
                                               href="{{ route(Config::get('constants.defines.APP_APPOINMENT_EDIT'), $model->id) }}">
                                                <i class="ti-pencil-alt"></i>
                                            </a>
                                        @endif

                                        @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_APPOINMENT_VIEW')))
                                            <a class="text-muted font-16 mr-1 ml-1"
                                            href="{{ route(Config::get('constants.defines.APP_APPOINMENT_VIEW'), $model->id) }}">
                                                <i class="ti-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="100%">{{ __('No data found') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row justify-content-md-between">
                    <div class="col-sm-12 text-center col-md pt-2 text-md-left">
                        @php
                            $commonFunction = new \App\Utils\CommonFunction();
                            $commonFunction->totalRecords($models)
                        @endphp
                    </div>
                    <div class="col-sm-12 text-center col-md pt-2">
                        <form method="get" action="{{ route(Config::get('constants.defines.APP_APPOINMENT_INDEX')) }}">
                            <input type="hidden" name="filter_key" value="{{ $filters['filter_key'] }}">
                            @if(app()->getLocale() == 'tr')
                                {{ __('per page') }}
                            @endif
                            @include('partials.pagination_amount')
                            @if(app()->getLocale() == 'en')
                                {{ __('per page') }}
                            @endif
                        </form>
                    </div>
                    <div class="col-sm-12 text-center col-md pt-2 text-md-right">
                        <div class="dataTables_paginate paging_simple_numbers" style="box-sizing: unset">
                            {{ $models->appends([
                                'filter_key' => $filters['filter_key']
                            ])->links('partials.pagination') }}
                        </div>
                    </div>
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
        $(function () {
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
                if ($('#bulk_action').val() === '{{ \App\Models\Appoinment::MOVE_TO_TRASH }}' && $('.bulk-checkbox:checked').length > 0) {
                    alertify.confirm('Are you sure to delete selected appointments?<br/> It can\'t be undone.', function () {
                        $('#bulk-action-form').submit();
                    });
                }
            });
        });
    </script>
@endpush
