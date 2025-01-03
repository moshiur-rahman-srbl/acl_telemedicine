@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title d-flex justify-content-between">
                    <h3>{{ __($cmsInfo['subTitle']) }}</h3>
                    <a href="{{ route(Config::get('constants.defines.APP_MEDICAL_RECORDS_CREATE')) }}" class="ml-3 btn btn-sm btn-primary pull-right">
                        <i class="fa fa-plus-circle"></i>&nbsp;{{ __('Add') }}
                    </a>
                </div>
            </div>

            <div class="ibox-body">
                <div class="row justify-content-between">
                    <div class="form-group col-md-3">
                        <form action="{{ route(Config::get('constants.defines.APP_MEDICAL_RECORDS_INDEX')) }}" method="get">
                            <div class="input-group">
                                <input name="filter_key" id="filter_key" class="form-control form-control-sm"
                                       value="{{ request('filter_key') }}"
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
                                <th>{{ __('ID') }}</th>
                                <th>{{ __('Doctor ID') }}</th>
                                <th>{{ __('Patient ID') }}</th>
                                <th>{{ __('Record Date') }}</th>
                                <th>{{ __('Diagnosis') }}</th>
                                <th>{{ __('Treatments') }}</th>
                                <th>{{ __('Created At') }}</th>
                                <th>{{ __('Updated At') }}</th>
                                <th class="text-center">{{ __('Actions') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($medicalRecords as $record)
                                <tr>
                                    <td>{{ $record->id }}</td>
                                    <td>{{ $record->Doctor->name ?? '' }}</td>
                                    <td>{{ $record->Patient->name ?? '' }}</td>
                                    <td>{{ $record->record_date }}</td>
                                    <td>{{ Str::limit($record->diagnosis, 50) }}</td>
                                    <td>{{ Str::limit($record->treatments, 50) }}</td>

                                    <td>{{ $record->created_at }}</td>
                                    <td>{{ $record->updated_at }}</td>

                                    <td class="text-center">


                                        <a class="text-muted font-16 mr-1 ml-1" href="{{ route(Config::get('constants.defines.APP_MEDICAL_HISTORIES_VIEW'), $record->id) }}">
                                            <i class="ti-eye"></i>
                                        </a>
                                    </td>

                                    {{-- <td class="text-center">
                                        @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_MEDICAL_RECORDS_DELETE')))

                                            <a class="text-muted font-16 mr-1 ml-1" href="#"
                                               onclick="deleteAction('delete-form-{{$model->id}}')">
                                                <i class="ti-trash"></i>{{$model->id}}
                                            </a>
                                            <form id="delete-form-{{$model->id}}"
                                                  action="{{ route(Config::get('constants.defines.APP_MEDICAL_RECORDS_DELETE'), $model->id) }}"
                                                  method="post" style="display: none;">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                        @endif
                                        @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_MEDICAL_RECORDS_EDIT')))
                                            <a class="text-muted font-16 mr-1 ml-1"
                                               href="{{ route(Config::get('constants.defines.APP_MEDICAL_RECORDS_EDIT'), $model->id) }}">
                                                <i class="ti-pencil-alt"></i>
                                            </a>
                                        @endif

                                        @if(auth()->user()->hasPermissionOnAction(Config::get('constants.defines.APP_MEDICAL_RECORDS_VIEW')))
                                            <a class="text-muted font-16 mr-1 ml-1"
                                            href="{{ route(Config::get('constants.defines.APP_MEDICAL_RECORDS_VIEW'), $model->id) }}">
                                                <i class="ti-eye"></i>
                                            </a>
                                        @endif

                                    </td> --}}

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

                <div class="row justify-content-between">
                    <div class="col-md-6 text-left">
                        {{ $medicalRecords->total() }} {{ __('records found') }}
                    </div>
                    <div class="col-md-6 text-right">
                        {{ $medicalRecords->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
