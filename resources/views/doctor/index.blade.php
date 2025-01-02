@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')
<div class="page-content fade-in-up">
    @include('partials.flash')
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title d-flex justify-content-between">
                <h3>{{ __($cmsInfo['subTitle']) }}</h3>
                <a href="{{ route(Config::get('constants.defines.APP_DOCTOR_CREATE')) }}" class="ml-3 btn btn-sm btn-primary pull-right"><i class="fa fa-plus-circle"></i>&nbsp;{{ __('Add') }}</a>

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
                @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_DOCTOR_DELETE')))
                <div class="form-group col-md-3">
                    <form
                        action="{{ route(Config::get('constants.defines.APP_DOCTOR_DELETE'), \App\User::MOVE_TO_TRASH) }}"
                        method="POST" id="bulk-action-form">
                        @csrf
                        @method('DELETE')
                        <div class="input-group">
                            <select id="bulk_action" name="action"
                                class="selectpicker show-tick form-control form-control-sm">
                                <option value="">{{ __("Bulk Action") }}</option>
                                
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
                    <form action="{{ route(Config::get('constants.defines.APP_DOCTOR_INDEX')) }}" method="get">
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
                                
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Email') }}</th>
                                <th>{{ __('License Number') }}</th>
                                <th>{{ __('Speciality') }}</th>
                                <th>{{ __('Experience Years') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Updated At') }}</th>
                                <th class="text-center">{{ __('Actionssss') }}</th>
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
                                <td>{{ $model->id}}</td>
                                
                                <td>{{ $model->name}}</td>
                                <td>{{ $model->email}}</td>
                                <td>{{ $model->license_number}}</td>
                                <td>{{ $model->speciality}}</td>
                                <td>{{ $model->experience_years}}</td>
                                <td>{{ $model->created_at }}</td>
                                <td>{{ $model->updated_at }}</td>
                                <td class="text-center">
                                    <a class="text-muted font-16 mr-1 ml-1" href="#"
                                        onclick="deleteAction('delete-form-{{$model->id}}')">
                                        <i class="ti-trash"></i>
                                    </a>
                                    <form id="delete-form-{{$model->id}}"
                                        action="{{ route(Config::get('constants.defines.APP_DOCTOR_DELETE'), $model->id) }}"
                                        method="post" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                    <a class="text-muted font-16 mr-1 ml-1"
                                        href="{{ route(Config::get('constants.defines.APP_DOCTOR_EDIT'), $model->id) }}">
                                        <i class="ti-pencil-alt"></i>
                                    </a>

                                    <a class="text-muted font-16 mr-1 ml-1"
                                        href="{{ route(Config::get('constants.defines.APP_DOCTOR_VIEW'), $model->id) }}">
                                        <i class="ti-eye"></i>
                                    </a>
                                </td>

                            </tr>
                            @empty
                            <tr>
                                <td colspan="10">{{ __('No data found') }}</td>
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
                    <form method="get" action="{{ route(Config::get('constants.defines.APP_DOCTOR_INDEX')) }}">
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
    $(function() {
        $('#main-checkbox').click(function() {
            $('.bulk-checkbox').prop('checked', $(this).prop('checked'));
        });

        $('.bulk-checkbox').change(function() {
            if ($('.bulk-checkbox:checked').length === $('.bulk-checkbox').length) {
                $('#main-checkbox').prop('checked', true);
            } else {
                $('#main-checkbox').prop('checked', false);
            }
        });

       
    });
</script>
@endpush