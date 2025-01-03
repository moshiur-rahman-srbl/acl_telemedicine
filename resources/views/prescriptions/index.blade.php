@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')

<div class="page-content fade-in-up">
    @include('partials.flash')
    <div class="ibox">
        <div class="ibox-head">
            <div class="ibox-title d-flex justify-content-between">
                <h3>Create Prescription</h3>
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

            <!-- Prescription Table -->
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="thead-default thead-lg">
                        <tr>
                            <th>{{__('Appointment ID')}}</th>
                            <th>{{__('Doctor ID')}}</th>
                            <th>{{__('Patient ID')}}</th>
                            <th>{{__('Prescription Date')}}</th>
                            <th>{{__('Medications')}}</th>
                            <th>{{__('Instructions')}}</th>
                            <th>{{__('Created At')}}</th>
                            <th>{{__('Updated At')}}</th>
                            <th>{{__('Actions')}}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prescriptions as $prescription)
                            <tr>
                                <td>{{ $prescription->appointment_id }}</td>
                                <td>{{ $prescription->doctor_id }}</td>
                                <td>{{ $prescription->patient_id }}</td>
                                <td>{{ $prescription->prescription_date }}</td>
                                <td>{{ $prescription->medications }}</td>
                                <td>{{ $prescription->instructions }}</td>
                                <td>{{ $prescription->created_at }}</td>
                                <td>{{ $prescription->updated_at }}</td>
                                <td>
                                    <a href="{{ route('prescription.view', $prescription->id) }}" class="btn btn-sm btn-info">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <a href="{{ route('prescription.edit', $prescription->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    <form action="{{ route('prescription.delete', $prescription->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this prescription?')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row justify-content-md-between">
            <div class="col-sm-12 text-center col-md pt-2 text-md-left">
                @php
                    $commonFunction = new \App\Utils\CommonFunction();
                    $commonFunction->totalRecords($prescriptions);
                @endphp
            </div>
            <div class="col-sm-12 text-center col-md pt-2">
                <form method="get" action="{{ route(Config::get('constants.defines.APP_PRESCRIPTION_INDEX')) }}">
                    @include('partials.pagination_amount')
                </form>
            </div>
            <div class="col-sm-12 text-center col-md pt-2 text-md-right">
                <div class="dataTables_paginate paging_simple_numbers" style="box-sizing: unset">
                    {{ $prescriptions->links('partials.pagination') }}
                </div>
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
    });
</script>
@endsection
