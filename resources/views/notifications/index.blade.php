@extends('layouts.adminca')

@section('content')
    {{--@include('partials.page_heading')--}}

    <div class="page-content fade-in-up form-control-air" style="padding-top: 0px;margin-top: 25px !important">

        @include('partials.flash')

        @foreach (['danger', 'warning', 'success', 'info'] as $ft)
            @if(Session::has($ft))
                <div class="alert alert-{{$ft}} alert-dismissible fade show">
                    <button class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    {{ session()->get($ft) }}
                </div>
            @endif
        @endforeach

        {{--        @include('notifications.pertials.filter-form')--}}

        <div class="ibox">
            <div class="ibox-head">
                <div class="ibox-title text-center">
                    {{__($cmsInfo['subTitle'])}}
                </div>
            </div>
            <div class="ibox-body">
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="thead-default">
                                <tr>
                                    <th class="col_hide_column"
                                        data-value="Message" style="min-width: 500px">{{__('message.message')}}</th>
                                    <th class="col_hide_column"
                                        data-value="Status">{{__('Status')}}</th>
                                    <th class="col_hide_column"
                                        data-value="Created At">{{__('Created At')}}</th>
                                    <th class="col_hide_column"
                                        data-value="Action">{{__('Action')}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($notifications as $notification)
                                    <tr>
                                        @if(app()->getLocale() == 'en')
                                            <td class="col_hide_column notification-item" data-value="Message" href="{{ route(config('constants.defines.APP_NOTIFICATION_MARK_AS_READ'), $notification->id) }}" style="cursor: pointer">
                                                {!! $notification->data_en !!}
                                            </td>
                                        @else
                                            <td class="col_hide_column notification-item" data-value="Message" href="{{ route(config('constants.defines.APP_NOTIFICATION_MARK_AS_READ'), $notification->id) }}" style="cursor: pointer;">{!! $notification->data_tr !!}</td>
                                        @endif
                                        <td class="col_hide_column"
                                            data-value="Status">{{ $notification->is_read ? __('Read at') . $notification->read_at : __('New') }}</td>
                                        <td class="col_hide_column"
                                            data-value="Created At">
                                            <span>{{ $notification->created_at }}</span>
                                        </td>

                                        <td class="text-center col_hide_column"
                                            data-value="Action">
                                            <button onclick="confirm('Are you sure to delete?') ? $('#delete-notification-{{ $notification->id }}').submit() : null" type="button" class="details-deposit btn btn-outline-secondary btn-sm">{{ __('Delete') }}</button>
                                            @if(!$notification->is_read)
                                                <a class="details-deposit btn btn-outline-secondary btn-sm" href="{{ route(config('constants.defines.APP_NOTIFICATION_MARK_AS_READ'), $notification->id) }}">{{ __('Mark As Read') }}</a>
                                            @endif
                                            <form id="delete-notification-{{ $notification->id }}" action="{{ route(config('constants.defines.APP_NOTIFICATION_DELETE'), $notification->id) }}" method="post">@csrf @method('DELETE')</form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="text-center" colspan="14">{{__('No data found')}}</td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col col-md-6">
                        <Form method="get" action="{{ route(Config::get('constants.defines.APP_NOTIFICATION_INDEX')) }}">
                            <input type="hidden" name="date_range" value="{{$search['date_range']}}">
                            <input type="hidden" name="status" value="{{$search['status']}}">
                            <input type="hidden" name="search_key" value="{{$search['search_key']}}">

                            @if(app()->getLocale() == 'tr')
                                {{__('per page')}}
                                @include('partials.pagination_amount')
                                {{__('record')}}
                            @endif
                            @if(app()->getLocale() == 'en')
                                {{__('Show')}}
                                @include('partials.pagination_amount')
                                {{__('per page')}}
                            @endif
                        </Form>
                    </div>
                    <div class="col-md-6">
                        {{ $notifications->appends([
                            'date_range' => $search['date_range'],
                            'status' => $search['status'],
                            'page_limit' => $page_limit
                            ])->links('partials.pagination') }}
                    </div>
                </div>
            </div>
            <!-- /.box -->
        </div>
    </div>
@endsection
