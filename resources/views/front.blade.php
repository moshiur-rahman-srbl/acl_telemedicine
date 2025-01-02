@extends('layouts.adminca')
@section('content')
    <?php
    $hasAdminPermission = \App\Utils\CommonFunction::hasAdminPermission();
    ?>
    <div class="page-content fade-in-up">

        @include('partials.flash')
        <div class="row mb-4">
            <div class="col-lg-6 col-md-6">
                <div class="card mb-4 myclass">
                    <div class="card-body flexbox-b">
                        <i class="fa fa-users fa-3x text-success fa_circle_border"></i>
                        <div>
                            <h3 class="font-strong text-success">15</h3>
                            <div class="text-muted">{{__('Doctors')}}</div>
                        </div>
                    </div>
                    <table class="table table-head-success">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('User')}}</th>
                            <th>{{$hasAdminPermission ? __('Site URL') : __('Date of Join')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($merchants->count()>0)
                            <?php $i = 1; ?>
                            @foreach($merchants as $merchant)
                                <tr>
                                    <td>{{$i++ }}</td>
                                    <td>{{ $merchant->user ? $merchant->user['name']: "" }}</td>
                                    <td>{{ $hasAdminPermission ? $merchant->site_url : \App\Utils\CommonFunction::dateFormat($merchant->created_at) }}</td>

                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" align="center" class="text-muted">{{ __('No data found') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    <button href=""
                            class="btn btn-success btn-sm btn-rounded"
                            style=" display: none;">{{__('View All')}}
                    </button>

                </div>


            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card mb-4 myclass">
                    <div class="card-body flexbox-b">
                        <i class="fa fa-globe fa-3x text-purple fa_circle_border"></i>
                        <div>
                            <h3 class="font-strong text-primary">10</h3>
                            <div class="text-muted">{{__('Patients')}}</div>
                        </div>
                    </div>
                    <table class="table table-head-purple">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('User')}}</th>
                            <th>{{__('Payment Method')}}</th>
                            <th>{{__('Doctors Section')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($deposit_requests->count()>0)
                            <?php $i = 1; ?>
                            @foreach($deposit_requests as $deposit)
                                <tr>
                                    <td>{{ $i++ }}</td>

                                    <td>
                                        {{ (!empty($deposit->userdata['name']))? $deposit->userdata['name']:
                                    $deposit->userdata['first_name'].' '.$deposit->userdata['last_name']  }}</td>
                                    <td>{{ $deposit->methoddata['name'] ?? '' }}</td>
                                    <td>{{ $deposit->currencydata['code'] ?? '' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" align="center"
                                    class="text-muted">{{ __('No Pending Request found') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    <button href="{{ $deposit_redirect_routes ?? ''  }}"
                            class="btn btn-primary btn-sm btn-rounded"
                            style="display: none;">{{__('View All')}}
                    </button>

                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card mb-4 myclass">
                    <div class="card-body flexbox-b">
                        <i class="fa fa-globe fa-3x fa_circle_border" style="color:#F39C12; "></i>
                        <div>
                            <h3 class="font-strong" style="color:#F39C12;">10</h3>
                            <div class="text-muted">{{__('Services')}}</div>
                        </div>
                    </div>
                    <table class="table table-head-yellow">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('User')}}</th>
                            <th>{{__('Payment Method')}}</th>
                            <th>{{__('Doctors Section')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($withdrawal_requests->count()>0)
                            <?php $i = 1; ?>
                            @foreach($withdrawal_requests as $withdrawal_request)
                                <tr>
                                    <td>{{ $i++ }}</td>

                                    <td>
                                        {{ (!empty($withdrawal_request->userdata['name']))? $withdrawal_request->userdata['name']:
                                    $withdrawal_request->userdata['first_name'].' '.$withdrawal_request->userdata['last_name']  }}</td>
                                    <td>{{ $withdrawal_request->method['name'] }}</td>
                                    <td>{{ $withdrawal_request->currencydata['code'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" align="center" class="text-muted">{{ __('No data found') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    <button href="{{ $withdrawal_redirect_routes ?? ''  }}"
                            class="btn btn-warning btn-sm btn-rounded"
                            style="display: none;">{{__('View All')}}
                    </button>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="card mb-4 myclass">
                    <div class="card-body flexbox-b">
                        <i class="fa fa-globe fa-3x fa_circle_border" style="color:#f75a5f; "></i>
                        <div>
                            <h3 class="font-strong" style="color:#f75a5f; ">----</h3>
                            <div class="text-muted">{{__('Appointments')}}</div>
                        </div>
                    </div>
                    <table class="table table-head-red">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('User')}}</th>
                            <th>{{__('Activity Title')}}</th>
                            <th>{{__('Doctors Section')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($transactions->count()>0)
                            <?php $i = 1; ?>
                            @foreach($transactions as $transaction)
                                <tr>
                                    <td>{{ $i++ }}</td>

                                    <td>{{ (isset($transaction->user['name'])) ? @$transaction->user['name'] : @$transaction->user['first_name'] . ' ' . @$transaction->user['last_name']  }}</td>
                                    <td>{{ __($transaction->activity_title) }}</td>
                                    <td>{{ $transaction->currency }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" align="center" class="text-muted">{{ __('No data found') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>

                    <button href=""
                            class="btn  btn-danger btn-sm btn-rounded"
                            style="display: none;">{{__('View All')}}
                    </button>
                </div>
            </div>
            @if(common\integration\BrandConfiguration::isWalletPaymentExist())
            <div class="col-lg-6 col-md-6">
                <div class="card mb-4 myclass">
                    <div class="card-body flexbox-b">
                        <i class="fa fa-globe fa-3x text-pink fa_circle_border"></i>
                        <div>
                            <h3 class="font-strong" style="color:#f75a5f; ">10</h3>
                            <div class="text-muted">{{__('Customers')}}</div>
                        </div>
                    </div>
                    <table class="table table-head-pink">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>{{__('Name')}}</th>
                            <th>{{__('Email')}}</th>
                            <th>{{__('Phone')}}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if($customers->count()>0)
                            <?php $i = 1; ?>
                            @foreach($customers as $customer)
                                <tr>
                                    <td>{{ $i++ }}</td>

                                    <td>
                                        {{ (!empty($customer->name))? $customer->name:
                                    $customer->first_name.' '.$customer->last_name }}</td>
                                    <td>{{ $customer->email }}</td>
                                    <td>{{ $customer->phone }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" align="center" class="text-muted">{{ __('No data found') }}</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                    <button href=""
                            class="btn btn-pink btn-sm btn-rounded"
                            style="display: none;">{{__('View All')}}
                    </button>
                </div>
            </div>
            @endif

        </div>


    </div>
@endsection
@push('js-before-app-js')
    @include('partials.js_blade.validate')
@endpush
@section('js')
    <script>
        $(document).ready(function () {
            $('.myclass').hover(function () {
                $(this).children('button').show(1).css('position', 'absolute').css('top', '55%').css('left', '45%');
                $(this).css('opacity', '.8').css('background', '#e0e0e0').css('cursor', 'pointer');

            }, function () {
                $(this).children('button').hide(500);
                $(this).css('opacity', '1').css('background', '');
            });

            $('.myclass').click(function () {
                location.href = $(this).children('button').attr("href");
            });


        });
    </script>


@endsection
@section('css')
    <style>
        .table-head-yellow thead th {
            background-color:rgb(72, 141, 214);
            border-color: #f39c12;
            color: #fff;
        }

        .table-head-red thead th {
            background-color: #f75a5f;
            border-color: #f75a5f;
            color: #fff;
        }

        .table-head-pink thead th {
            background-color: #FF4081;
            border-color: #FF4081;
            color: #fff;
        }

        i.fa_circle_border {
            display: inline-block;
            border: 7px solid;
            border-radius: 50px;
            box-shadow: 0 0 1px #888;
            padding: 11px 13px;
            margin-right: 10px;
        }
    </style>
@endsection
