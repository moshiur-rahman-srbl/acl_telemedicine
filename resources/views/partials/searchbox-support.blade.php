<form id="searchboxfrm" method="get" action="">
    @include('support.modals.col_hide')
    <input type="hidden" name="search" value="search"/>
    <div class="row font13">
        <div class="col-md-1 content-center mt-3">
                        <span id="dateprangepicker" class="bg-light p-4 btn-rounded btn">
                            <i class="fa fa-calendar fa-2x"></i>
                        </span>
        </div>
        <div class="col-md-11 mt-3">
            <input type="hidden" value="{{$search['daterange']}}"
                   id="daterange"
                   name="daterange"/>
            <div class="row">
                <div class="col-md-3 mt-3">
                    <input class="form-control" name="customer_gsm"
                           value="{{$search['customer_gsm']}}"
                           type="text"
                           placeholder="{{__("Customer GSM")}}"/>
                </div>
                <div class="col-md-3 mt-3">
                    <input class="form-control" name="ticket_id"
                           value="{{$search['ticket_id']}}" type="text"
                           placeholder="{{__("Ticket ID")}}"/>
                </div>
                <div class="col-md-3 mt-3">
                    <select name="user_type" class="selectpicker form-control custome-padding2">
                        <option value="">{{__('Select Sender Type')}}</option>

                        @if(\common\integration\BrandConfiguration::isWalletPaymentExist())
                        <option value="customer" {{$search['user_type'] == 'customer' ? 'selected' : ''}}>{{__('Users')}}</option>
                        @endif

                        <option value="merchant" {{$search['user_type'] == 'merchant' ? 'selected' : ''}}>{{__('Merchants')}}</option>
                    </select>
                </div>
                <div class="col-md-3 mt-3">
                    <div class="input-group-icon input-group-icon-right">
                                    <span class="input-icon input-icon-right ">
                                        <i class="fa fa-search"></i>
                                    </span>
                        <input class="form-control-rounded form-control"
                               value="{{$search['searchkey']}}"
                               type="text"
                               name="searchkey"
                               placeholder="{{__("Search")}} ..."/>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-3 mt-3">
                    <input class="form-control" name="user_id"
                           value="{{$search['user_id']}}" type="text"
                           placeholder="{{__("User ID")}}"/>
                </div>
                <div class="col-md-3 mt-3">
                    <select name="status" class="selectpicker form-control custome-padding2">
                            <option value="">{{__('Select Status')}}</option>
                            <option value="{{\App\Models\Ticket::OPEN}}" {{$search['status'] === \App\Models\Ticket::OPEN ? 'selected' : ''}}>{{__('Open')}}</option>
                            <option value="{{\App\Models\Ticket::CLOSED}}" {{$search['status'] === \App\Models\Ticket::CLOSED ? 'selected' : ''}}>{{__('Closed')}}</option>
                        @if(isset($search['is_allow_pending_status']) && $search['is_allow_pending_status'])
                            <option value="{{\App\Models\Ticket::PENDING}}" {{$search['status'] === \App\Models\Ticket::PENDING ? 'selected' : ''}}>{{__('Pending')}}</option>
                        @endif
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Buttons --}}
    <div class="row mb-4 mt-4">
        @if(isset($is_allow_ticket_summery) && $is_allow_ticket_summery)
        <div class="col-1">
        </div>
            <div class="col-5 text-md-right">
            <div class="d-flex flex-column justify-content-around justify-content-md-start align-items-sm-stretch align-items-md-start pr-3 pl-3 pl-md-0">
                @if( isset($open_ticket) && $open_ticket > 0)
                <p >{{__('Total Open Record')}} : {{$open_ticket ?? ''}}</p>
                @endif
                @if(isset($closed_ticket) && $closed_ticket > 0)
                <p >{{__('Total Closed Record')}} : {{$closed_ticket ?? ''}}</p>
                @endif
            </div>
        </div>
        @endif
            <div class="{{ isset($is_allow_ticket_summery) && $is_allow_ticket_summery ? 'col-6' : 'col-12' }} text-md-right">
            <div class="d-flex flex-column flex-md-row justify-content-around justify-content-md-end align-items-sm-stretch align-items-md-center pr-3 pl-3 pl-md-0">

                {{-- Cear Filters --}}
                <a href="{{isset($selfUrls) ? $selfUrls : ""}}"
                   class="text-center">{{__("Clear Filter")}}</a>
                &nbsp;&nbsp;
                {{-- Cear Filters END --}}

                {{-- Submit Button --}}
                <button type="submit"
                        id="searchbtn"
                        class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                    {{__("Search")}} <i class="fa fa-caret-right"></i>
                </button>
                {{-- Submit Button END --}}

                {{-- Col Hide Button --}}
                <button type="button"
                        class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto"
                        data-bs-toggle="modal"
                        data-bs-target="#colHideModal">
                    <span class="btn-icon">{{__("Col. Hide")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-eye"></i></span>
                </button>
                {{-- Col Hide Button END --}}

                {{-- Export Button --}}
                @if(isset($exportBtnRouteName))
                    @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                        <a id="exportbtn"
                           href="{{ isset($selfUrls) ? $selfUrls."/export" : ""}}"
                           class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                            <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i
                                    class="fa fa-caret-right"></i></span>
                        </a>
                    @endif
                @endif
                {{-- Export Button END --}}

            </div>
        </div>
    </div>

</form>
@section('scripts')
    <script>

        $('#dateprangepicker').daterangepicker({
            maxDate: new Date(),
            "autoApply": false,
            locale: {
                format: 'YYYY/MM/DD',
                customRangeLabel: "{{__('Custom Range')}}",
                applyLabel: "{{__('Apply')}}",
                cancelLabel: "{{__('Cancel')}}"
            },
            ranges: {
                "{{__('Today')}}": [moment(), moment()],
                "{{__('Yesterday')}}": [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                "{{__('Last 7 Days')}}": [moment().subtract(6, 'days'), moment()],
                "{{__('Last 30 Days')}}": [moment().subtract(29, 'days'), moment()],
                "{{__('This Month')}}": [moment().startOf('month'), moment().endOf('month')],
                "{{__('Last Month')}}": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            "alwaysShowCalendars": true,
            "startDate": "{{isset($search['from_date']) ? $search['from_date'] : $from_date}}",
            "endDate": "{{isset($search['to_date']) ? $search['to_date'] : $to_date}}"
        });

         $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
             //do something, like clearing an input
             // console.log(picker.startDate.format('YYYY/MM/DD'));
             var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
             $("#daterange").val(dateranges);
         });

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');
            // console.log(fromurl);
            $("#searchboxfrm").attr('action', fromurl);
            $("#searchboxfrm").submit();
        });

        $("#searchbtn").on("click", function (event) {
            event.preventDefault();
            $("#searchboxfrm").attr('action', '{{isset($selfUrls) ? $selfUrls : Request::url()}}');
            $("#searchboxfrm").submit();
        });


    </script>
@endsection



