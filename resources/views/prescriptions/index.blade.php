@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')

<div class="page-content fade-in-up">
    @include('partials.flash')
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title d-flex justify-content-between">
                <h3>Prescription List</h3>
                <a href="{{ route(Config::get('constants.defines.APP_PRESCRIPTION_CREATE')) }}" class="ml-3 btn btn-sm btn-primary pull-right">
                    <i class="fa fa-plus-circle"></i>&nbsp;{{ __('Create') }}
                </a>
            </div>
        </div>
        <br>
        <br>
        <br>
        <div class="ibox-head">
            <div class="ibox-title">
            <h1 class="text-center">{{ $cmsInfo['moduleTitle'] }}</h1>
            </div>
        </div>
        <div class="ibox-body">

            <h3 class="text-center">{{ $cmsInfo['subTitle'] }}</h3>

            <div class="row justify-content-between">
                @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_PRESCRIPTION_DELETE')))
                    <div class="form-group col-md-3">
                        <form
                            action="{{ route(Config::get('constants.defines.APP_PRESCRIPTION_DELETE'), \App\User::MOVE_TO_TRASH) }}"
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
                    <form action="{{ route(Config::get('constants.defines.APP_PRESCRIPTION_INDEX')) }}" method="get">
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

{{--{{dd(session()->get('permittedRouteNames'))}}--}}
            <!-- Prescription Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-default thead-lg">
                        <tr>
                            <th>{{__('ID')}}</th>
                            <th>{{__('Patient Name')}}</th>
                            <th>{{__('Prescription Date')}}</th>
                            <th>{{__('Medications')}}</th>
                            <th>{{__('Instructions')}}</th>
                            <th>{{__('Created At')}}</th>
                            <th>{{__('Updated At')}}</th>
                            <th>{{__('Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($prescriptions as $prescription)
                            <tr>
                                <td>{{ $prescription->id }}</td>
                                <td>{{ $prescription->Appointment->Patient->name ?? '' }}</td>
                                <td>{{ $prescription->prescription_date }}</td>
                                <td>{{ $prescription->medications }}</td>
                                <td>{{ $prescription->instructions }}</td>
                                <td>{{ $prescription->created_at }}</td>
                                <td>{{ $prescription->updated_at }}</td>
                                <td>
                                    <a href="{{ route('prescription.view', $prescription->id) }}" class="text-muted font-16 mr-1 ml-1">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('prescription.edit', $prescription->id) }}" class="text-muted font-16 mr-1 ml-1">
                                        <i class="fa fa-edit"></i>
                                    </a>

                                    <a class="text-muted font-16 mr-1 ml-1" href="#"
                                       onclick="deleteAction('delete-form-{{$prescription->id}}')">
                                        <i class="ti-trash"></i>
                                    </a>


                                    <form id="delete-form-{{$prescription->id}}"
                                          action="{{ route(Config::get('constants.defines.APP_PRESCRIPTION_DELETE'), $prescription->id) }}"
                                          method="post" style="display: none;">
                                        @csrf
                                        @method('DELETE')
                                    </form>
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

            <div class="row justify-content-md-between">
                <div class="col-sm-12 text-center col-md pt-2 text-md-left">
                    @php
                        $commonFunction = new \App\Utils\CommonFunction();
                        $commonFunction->totalRecords($prescriptions)
                    @endphp
                </div>
                <div class="col-sm-12 text-center col-md pt-2">
                    <form method="get" action="{{ route(Config::get('constants.defines.APP_PRESCRIPTION_INDEX')) }}">
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
                        {{ $prescriptions->appends([
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
