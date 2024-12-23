@php
    $current_route_name = Route::currentRouteName();
    $method_name = "GET";
    if ($current_route_name == Config::get('constants.defines.APP_DEPOSITS_INDEX') ) {
       $field_name = "username";
       $place_holder = "User Name";
    }elseif($current_route_name == Config::get('constants.defines.APP_ALL_TRANSACTION_INDEX') || $current_route_name == Config::get('constants.defines.APP_PAYMENT_TRANSACTION_INDEX')){
        $method_name = "POST";
        $field_name = "merchantname";
        $place_holder = "Merchant Name";

    }elseif($current_route_name == Config::get('constants.defines.APP_FAILED_TRANSACTION_INDEX')){
        $method_name = "POST";
        $field_name = "merchantname";
        $place_holder = "Merchant Name";

    }elseif($current_route_name == Config::get('constants.defines.APP_ACCOUNT_STATEMENT_INDEX') || $current_route_name == config('constants.defines.APP_ACCOUNT_STATEMENT_NEW_INDEX')){
        $method_name = "POST";
        $field_name = "merchantname";
        $place_holder = "Merchant Name";

    }elseif($current_route_name == Config::get('constants.defines.APP_BANK_SETTLEMENT_REPORT_INDEX')){
        $method_name = "POST";
        $field_name = "merchantname";
        $place_holder = "Merchant Name";

    }elseif($current_route_name == Config::get('constants.defines.APP_FINANCIALIZATION_REPORT_INDEX') || $current_route_name == config('constants.defines.APP_FINANCIALIZATION_REPORT_NEW_INDEX')){
        $method_name = "POST";
        $field_name = "merchantname";
        $place_holder = "Merchant Name";

    }else {
        $field_name = "merchantname";
        $place_holder = "Merchant Name";
    }
    $is_allow_to_select_future_date = false;
    if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Frontend\FrontendAdmin::class, 'isAllowToSelectFutureDate']) && request()->route()->getName() == Config::get('constants.defines.APP_BANK_SETTLEMENT_REPORT_INDEX')) {
        $is_allow_to_select_future_date = true;
    }

    $isAllowedFilterByRemoteTransactionAt = \common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowedFilterByRemoteTransactionAt']) && !empty($isEnabledDateRangeBy);
    if ($isAllowedFilterByRemoteTransactionAt) {
        $date_range_filter_by = \common\integration\SaleTransaction::DATERANGE_FILTER_BY;

        $divCol = '9';
    } else {
        $divCol = '12';
    }
@endphp

<form id="searchboxfrm" method="{{ $method_name }}" action="{{ isset($selfUrls) ? $selfUrls : Request::url() }}">
    @if($method_name == 'POST')
        @csrf
    @endif

    @include('sale_transaction.modals.col_hide')

    <input type="hidden" name="search" value="search"/>
    @if(isset($route_permission) && !empty($route_permission))
    <input type="hidden" name="route_permission" value="{{ $route_permission }}"/>
    <input type="hidden" name="page_limit" value="{{ $search['page_limit'] ?? 10 }}"/>
    @endif
        @if($current_route_name == config('constants.defines.APP_PAYMENT_TRANSACTION_INDEX'))
            <input type="hidden" name="export_from_payment_source" value="true">
        @endif
        @if( $current_route_name == Config::get('constants.defines.APP_ACCOUNT_STATEMENT_NEW_INDEX'))
            <input type="hidden" name="export_from_new_account_statement" value="true">
        @endif
        @if( $current_route_name == Config::get('constants.defines.APP_FINANCIALIZATION_REPORT_NEW_INDEX'))
            <input type="hidden" name="export_from_new_financialization_statement" value="true">
        @endif
    <div class="d-flex justify-content-around align-items-center flex-column flex-md-row font13 w-100 row mb-2 mx-0">
        {{-- Date Picker --}}
        <div class="col col-lg-1 col-md-2 mb-2 m-md-0 text-center">
            <span id="dateprangepicker"
                  class="bg-light p-4 btn-rounded btn">
                <i class="fa fa-calendar fa-2x"></i>
            </span>
        </div>
        {{-- Date Picker END --}}
        <div class="col col-lg-11 col-md-12">
            <div class="row">
                <div class="col-md-{{$divCol}}">
                    <input type="text"
                           value="{{ old('daterange', $search['daterange'] ?? '') }}"
                           id="daterange"
                           class="form-control"
                           readonly
                           placeholder="{{__('Between Two Date')}}"
                           name="daterange"/>
                </div>
                @if($isAllowedFilterByRemoteTransactionAt)
                    <div class="col-md-3">
                        <select class="form-control selectpicker" data-style="''" data-style-base="form-control" name="filter_daterange_as" id="filter_daterange_as">
                            @foreach($date_range_filter_by as $key => $val)
                                <option value="{{$key}}" {{ ($search['filter_daterange_as'] ?? '') == $key ? 'selected' : '' }}>{{__($val)}}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            </div>
            <div class="w-100">
                <div class="row">
                    {{-- Customer GSM --}}
                    <div class="col-md-3 my-2">
                        <input class="form-control"
                               name="customergsm"
                               value="{{ old('customergsm', $search['customergsm'] ?? "")}}"
                               type="text"
                               placeholder="{{__("Customer GSM")}}"/>
                    </div>
                    {{-- Customer GSM END --}}

                    {{-- Minamount --}}
                    <div class="col-md-3 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-long-arrow-down"></i>
                            </span>
                            <input class="form-control" type="text"
                                   value="{{ old('minamount',$search['minamount'] ?? '') }}"
                                   name="minamount"
                                   placeholder="{{__("Min Amount")}}"/>
                        </div>
                    </div>
                    {{-- Minamount END --}}

                    {{-- Maxamount --}}
                    <div class="col-md-3 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-long-arrow-up"></i>
                            </span>
                            <input class="form-control" type="text"
                                   value="{{old('maxamount', $search['maxamount'] ?? '')}}"
                                   name="maxamount"
                                   placeholder="{{__("Max Amount")}}"/>
                        </div>
                    </div>
                    {{-- Maxamount END --}}

                    {{-- Search Field --}}
                    {{-- <div class="col-md-3 my-2">
                        <div class="input-group-icon input-group-icon-right">
                            <span class="input-icon input-icon-right ">
                                <i class="fa fa-search"></i>
                            </span>
                            <input class="form-control-rounded form-control"
                                   value="{{old('searchkey', $search['searchkey'] ?? '') }}"
                                   type="text"
                                   name="searchkey"
                                   placeholder="{{__("Search")}} ..."/>
                        </div>
                    </div> --}}
                    <div class="col-md-3 my-2">
                        <input class="form-control"
                               name="orderid"
                               value="{{ old('orderid', $search['orderid'] ?? '') }}"
                               type="text"
                               placeholder="{{__("Order Id")}}"/>
                    </div>
                    {{-- Search Field END --}}
                </div>
                <div class="row">
                    {{-- Trans ID --}}
                    <div class="col-md-3 my-2">
                        <input class="form-control"
                               name="transid"
                               value="{{ old('transid', $search['transid'] ?? '') }}"
                               type="text"
                               placeholder="{{__("Trans. ID")}}"/>
                    </div>
                    {{-- Trans ID END --}}
                    <div class="col-md-3 my-2">
                    @if(isset($merchant_type))
                        <select class="form-control selectpicker"
                                multiple
                                data-actions-box="true"
                                data-live-search="true"
                                title="{{__("Merchant Type")}}"
                                name="merchant_type[]"
                                data-virtual-scroll="true"
                                id="merchant_type"
                                data-style="''"
                                data-style-base="form-control"
                        >
                            @foreach($merchant_type as $key => $value)
                                <option value="{{ $key }}" {{ !empty($search['type']) && in_array($key, $search['type']) ? 'selected':''}}>{{ $value }}</option>
                            @endforeach
                        </select>
                    @endif
                    </div>

                    <div class="col-md-3 my-2">
                        @if(isset($isAlltransaction) && $isAlltransaction)
                            {{-- Merchant --}}
                            <select class="form-control selectpicker"
                                    name="merchantid[]"
                                    id="merchant_id"
                                    multiple
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-title="{{ __($place_holder) }}"
                                    data-virtual-scroll="true"
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach($merchantList as $merchant)
                                    <option value="{{ $merchant->id }}"
                                        {{ (is_array($search['merchantid']) || is_array(old('merchantid'))) && in_array($merchant->id, old('merchantid') ?? $search['merchantid']) ? 'selected':''}}
                                    >{{ $merchant->name . ' (' . $merchant->id . ')' }}</option>
                                @endforeach
                            </select>
                            <input type="hidden" id="merchant_id_arr" name="merchant_id_arr[]" value="@json($merchantList->pluck('id'))">
                            {{-- Merchant END --}}
                        @else
                            {{-- User --}}
                            <div class="input-group-icon input-group-icon-right">
                                <input class="form-control"
                                       value="{{ old($field_name) ?? (isset($search[$field_name] )? $search[$field_name] : "")}}" type="text"
                                       name="{{ $field_name }}"
                                       placeholder="{{ __($place_holder) }}"/>
                            </div>
                            {{-- User END --}}
                        @endif

                    </div>
                    <div class="col-md-3 my-2">
                        @if(isset($transactionStateList))
                            {{-- Status --}}
                            <select name="transactionState[]"
                                    class="selectpicker form-control custome-padding"
                                    data-title="{{ __("Status") }}"
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-virtual-scroll="true"
                                    multiple
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach($transactionStateList as $key=>$value)
                                    <?php
                                    $slctd = '';
                                    if((is_array($search['transactionState']) || old('transactionState')) && in_array($key, old('transactionState') ?? $search['transactionState'])) {
                                        $slctd = 'selected';
                                    }
                                    ?>
                                    <option value="{{$key}}" {{$slctd}}>{{__($value)}}</option>
                                @endforeach
                                  <option {{ is_array($search['transactionState']) && in_array(\App\Models\TransactionState::PROVISION , $search['transactionState']) ? 'selected': '' }} value="{{ \App\Models\TransactionState::PROVISION }}">{{__('Pre-Authorization')}}</option>
                            </select>
                            {{-- Status END --}}
                        @endif
                    </div>
                </div>
                @if(isset($search['paymentmethodid']) && $search['paymentmethodid']==\App\Models\DepositeMethod::EFT)
                    <div class="row mt-3">
                        {{-- Automation Status --}}
                        <div class="col-md-3">
                            <select name="automationStatus"
                                    class="selectpicker form-control"
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                <option value=''
                                    {{ ( old('automationStatus') ?? $search['automationStatus'] == '') ? 'selected' : '' }}
                                >{{ __('Automation Status') }}</option>
                                <option value='{{\App\Models\Deposit::MANUAL}}'
                                    {{( old('automationStatus') ?? $search['automationStatus'] == \App\Models\Deposit::MANUAL) ? 'selected' : ''}}
                                >{{__('Manual')}}</option>
                                <option value='{{\App\Models\Deposit::AUTOMATION}}'
                                    {{( old('automationStatus') ?? $search['automationStatus'] == \App\Models\Deposit::AUTOMATION) ? 'selected' : ''}}
                                >{{__('Automation')}}</option>
                            </select>
                        </div>
                        {{-- Automation Status END --}}
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-3 my-2">
                        @if(isset($poses))
                            {{-- Pos --}}
                            <select name="pos_id[]"
                                    class="selectpicker form-control"
                                    data-title="{{__("POS")}}"
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-virtual-scroll="true"
                                    multiple
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach($poses as $pos)
                                    <?php
                                    $slctd = '';
                                    if( (is_array($search['pos_id']) || is_array(old('pos_id'))) && in_array($pos->pos_id, old('pos_id') ?? $search['pos_id'])) {
                                        $slctd = 'selected';
                                    }
                                    ?>
                                    <option value="{{$pos->pos_id}}" {{$slctd}}>{{$pos->name}}</option>
                                @endforeach
                            </select>
                            {{-- Pos END --}}
                        @endif
                    </div>
                    <div class="col-md-3 my-2">
                        @if(isset($currencies))
                            {{-- Currency --}}
                            <select name="currency_id[]"
                                    class="selectpicker form-control"
                                    data-title="{{ __("Currency") }}"
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-virtual-scroll="true"
                                    multiple
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach($currencies as $currencyObj)
                                    <?php
                                    $slctd = '';
                                    if((is_array($search['currency_id']) || is_array(old('currency_id'))) && in_array($currencyObj->id, old('currency_id') ?? $search['currency_id'])) {
                                        $slctd = 'selected';
                                    }
                                    ?>
                                    <option value="{{ $currencyObj->id }}"
                                            title="{{ $currencyObj->code }}"
                                        {{ $slctd }}>{{ __($currencyObj->name).' ('.$currencyObj->symbol.')' }}</option>
                                @endforeach
                            </select>
                            {{-- Currency END --}}
                        @endif
                    </div>
                    {{-- Billing Email --}}
                    <div class="col-md-3 my-2">
                        <input type="text"
                               name="bill_email"
                               class="form-control"
                               placeholder="{{ __("Billing Email") }}"
                               value="{{ old('bill_email') ?? ($search['bill_email'] ?? "" )}}">
                    </div>
                    {{-- Billing Email END --}}

                    {{-- Billing Phone --}}
                    <div class="col-md-3 my-2">
                        <input type="text"
                               name="bill_phone"
                               class="form-control"
                               placeholder="{{ __('Billing Phone') }}"
                               value="{{ old('bill_phone') ?? ($search['bill_phone'] ?? "-" )}}">
                    </div>
                    {{-- Billing Phone END --}}

                </div>
                <div class="row">
                    <div class="col-md-3 my-2">
                        <input type="text"
                               name="first_six_digit_card_number"
                               class="form-control"
                               placeholder="{{ __("First 6 Digits Card Number") }}"
                               value="{{ old('first_six_digit_card_number') ?? ($search['first_six_digit_card_number'] ?? "") }}">
                    </div>
                    <div class="col-md-3 my-2">
                        <input type="text"
                               name="last_four_digit_card_number"
                               class="form-control"
                               placeholder="{{ __("Last 4 Digits Card Number") }}"
                               value="{{ old('last_four_digit_card_number') ?? ($search['last_four_digit_card_number'] ?? "") }}">
                    </div>
                    <div class="col-md-3 my-2">
                        <input type="text"
                               name="card_holder"
                               class="form-control"
                               placeholder="{{ __("Card Holder") }}"
                               value="{{ old('card_holder') ??  ($search['card_holder'] ?? "") }}">
                    </div>
                    <div class="col-md-3 my-2">
                        <input type="text"
                               name="ip_address"
                               class="form-control"
                               placeholder="{{ __("IP Address") }}"
                               value="{{ old('ip_address') ?? ($search['ip_address'] ?? "") }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 my-2">
                        @if(isset($integrators))
                            <select name="integrator_id[]"
                                    class="selectpicker form-control"
                                    data-title="{{ __("Integrator") }}"
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-virtual-scroll="true"
                                    multiple
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach($integrators as $integrator)
                                    <?php
                                    $inslctd = '';
                                    if((is_array($search['integrator_id']) || old('integrator_id')) && in_array($integrator->id, old('integrator_id') ?? $search['integrator_id'])) {
                                        $inslctd = 'selected';
                                    }
                                    ?>
                                    <option value="{{ $integrator->id }}" {{ $inslctd }}>{{ $integrator->integrator_name }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                    @if(isset($installments))
                    <div class="col-md-3 my-2">
                            <select name="installment[]"
                                    class="selectpicker form-control"
                                    data-title="{{ __("Installment") }}"
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-virtual-scroll="true"
                                    multiple
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach($installments as $installment)
                                    <?php
                                    $installlctd = '';
                                    if((is_array($search['installment']) || is_array(old('installment'))) && in_array($installment, old('installment') ?? $search['installment'])) {
                                        $installlctd = 'selected';
                                    }
                                    ?>
                                    <option value="{{ $installment }}" {{ $installlctd }}>{{ __('Installment :value',
                                    ['value' =>$installment])}}</option>
                                @endforeach
                            </select>
                    </div>
                    <div class="col-md-3 my-2">
                        <input type="text"
                               name="dpl_field"
                               class="form-control"
                               placeholder="{{ __("Search by billing info...") }}"
                               value="{{ old('dpl_field') ?? ($search['dpl_field'] ?? "") }}">
                    </div>
                    @endif

                    @if(\common\integration\BrandConfiguration::allow2D3DCvvLessFiltter() && isset($filter_2d_3d_cvvless) && !empty($filter_2d_3d_cvvless))
                        <div class="col-md-3 my-2">
                            <select name="filter_2d_3d_cvvless[]"
                                    class="selectpicker form-control"
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-virtual-scroll="true"
                                    data-title="{{ __("Payment Type") }}"
                                    multiple
                                    data-style="''"
                                    data-style-base="form-control"

                            >
                                @foreach($filter_2d_3d_cvvless as $key => $val)
                                    <option value="{{ $key }}"
                                        {{ (is_array($search['filter_2d_3d_cvvless']) || is_array(old('filter_2d_3d_cvvless')) ) && in_array($key, old('filter_2d_3d_cvvless') ?? $search['filter_2d_3d_cvvless']) ? 'selected':''}}
                                    >{{ __($val) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(isset($table_types) && !empty($table_types))
                     <div class="col-md-3 my-2">
                            <select name="backup_table_type" class="form-control" data-title="{{ __("Backup Type") }}" data-style-base="form-control">
                                @foreach($table_types as $key => $table_type)
                                    <option value="{{ $key }}"{{ $key == $search["backup_table_type"] ? 'selected':''}}>{{ __($table_type) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(\common\integration\BrandConfiguration::allowPaymentSource() && isset($paymentSource) && !empty($paymentSource))
                        <div class="col-md-3 my-2">
                            <select name="payment_source[]"
                                    class="form-control selectpicker"
                                    multiple
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-title="{{ __('Payment Source') }}"
                                    data-virtual-scroll="true"
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach($paymentSource as $key => $val)
                                    <option value="{{ $key }}"
                                        {{ (is_array($search['payment_source']) || is_array(old('payment_source'))) && in_array($key, old('payment_source') ?? $search['payment_source']) ? 'selected':''}}
                                    >{{ __($val) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="col-md-3 my-2">
                        <input type="text"
                                name="invoiceid"
                                class="form-control"
                                placeholder="{{ __("Invoice Id") }}"
                                value="{{ old('invoiceid') ?? ($search['invoiceid'] ?? "") }}">
                    </div>
                    @if(isset($activeAdminUsers))
                    <div class="col-md-3 my-2">
                        <select class="form-control selectpicker"
                                multiple
                                data-actions-box="true"
                                data-live-search="true"
                                data-title="{{__("Account Manager")}}"
                                name="account_manager_ids[]"
                                data-virtual-scroll="true"
                                id="account_manager_ids"
                                data-style="''"
                                data-style-base="form-control">
                            @foreach($activeAdminUsers as $accountManager)
                                @if(isset($accountManager->users))
                                <option value="{{ $accountManager->user_id }}"
                                    {{ ((is_array($search['account_manager_ids']) || is_array(old('account_manager_ids'))) && in_array($accountManager->user_id, old('account_manager_ids') ?? $search['account_manager_ids'])) ? 'selected':''}}
                                >{{ $accountManager->users->name . ' (' . $accountManager->user_id . ')' }}</option>
                                @endif
                            @endforeach
                        </select>

                    </div>
                    @endif

                    @if(isset($merchantTerminalIds))
                        <div class="col-md-3 my-2">
                            <select class="form-control selectpicker"
                                    multiple
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-title="{{__("Search by terminal no")}}"
                                    name="merchant_terminal_ids[]"
                                    data-virtual-scroll="true"
                                    id="merchant_terminal_ids"
                                    data-style="''"
                                    data-style-base="form-control">
                                @foreach($merchantTerminalIds as $terminal)
                                    <option value="{{ $terminal->terminal_id }}"
                                        {{ (is_array($search['merchant_terminal_ids']) || is_array(old('merchant_terminal_ids') )) && in_array($terminal->terminal_id, old('merchant_terminal_ids') ?? $search['merchant_terminal_ids']) ? 'selected':''}}
                                    >{{ $terminal->terminal_id  }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div class="col-md-3 my-2">
                        @if(isset($type) && !empty($type))
                            {{-- Type --}}
                            <select name="typeid"
                                    class="selectpicker form-control"
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                <option value="">{{__("Type")}}</option>
                                <option {{ old('typeid') ?? ($search['typeid'] == 1 ? 'selected':'')}} value="1">{{__("Bank")}}</option>
                                <option {{ old('typeid') ?? ($search['typeid'] == 2 ? 'selected':'')}} value="2">{{__("Wallet")}}</option>
                            </select>
                            {{-- Type END --}}
                        @else
                            @if(isset($paymentMethods))
                                {{-- Payment Method --}}
                                <select name="paymentmethodid[]"
                                        class="selectpicker form-control"
                                        data-title="{{ __("Payment Method") }}"
                                        data-actions-box="true"
                                        data-live-search="true"
                                        data-virtual-scroll="true"
                                        multiple
                                        data-style="''"
                                        data-style-base="form-control"
                                >
                                        <?php
                                        $latest = \App\Models\PaymentRecOption::manualPaymentMethod();
                                        $paymentMethods = $paymentMethods->merge($latest);
                                        ?>
                                    @foreach($paymentMethods as $paymentMethod)
                                            <?php
                                            $slctd = '';
                                            if((is_array($search['paymentmethodid']) || is_array(old('paymentmethodid'))) && in_array($paymentMethod->id, old('paymentmethodid') ?? $search['paymentmethodid'])) {
                                                $slctd = 'selected';
                                            }
                                            ?>
                                        <option value="{{$paymentMethod->id}}"
                                            {{$slctd}}>{{__($paymentMethod->name)}}</option>
                                    @endforeach
                                </select>
                                {{-- Payment Method END --}}
                            @endif
                        @endif
                    </div>
                    @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'allowFilterByTransactionPosType'])  && isset($transactionPostTypes))
                        <div class="col-md-3 my-2">
                                <select name="pos_transaction_type[]"
                                        class="selectpicker form-control"
                                        data-title="{{ __("Transaction POS Type") }}"
                                        data-actions-box="true"
                                        data-live-search="true"
                                        data-virtual-scroll="true"
                                        multiple
                                        data-style="''"
                                        data-style-base="form-control"
                                >
                                @foreach($transactionPostTypes as $key => $value)
                                    <option value="{{$key}}" @selected(common\integration\Utility\Arr::isAMemberOf($key, $search['pos_transaction_type']))>{{__($value)}}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(\common\integration\BrandConfiguration::isEnableDeviceDetection())
                        <div class="col-md-3 my-2">
                            <select name="device_id[]"
                                    class="selectpicker form-control"
                                    data-title="{{ __("Device") }}"
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-virtual-scroll="true"
                                    multiple
                                    data-style="''"
                                    data-style-base="form-control"
                            >
                                @foreach(\common\integration\Utility\System::DEVICE_LIST as $key => $device)
                                    @php
                                        $slctd = '';
                                        if((isset($search['device_id']) && is_array($search['device_id']) || is_array(old('device_id'))) && in_array($key, old('device_id') ?? $search['device_id'])) {
                                            $slctd = 'selected';
                                        }
                                    @endphp
                                    <option value="{{ $key }}" {{ $slctd }}>{{ $device }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    @if(\common\integration\BrandConfiguration::allowAuthCodeColumnInAllTransaction())
                        <div class="col-md-3 my-2">
                            <input type="text"
                                   name="auth_code"
                                   class="form-control"
                                   placeholder="{{ __("Search by auth code") }}"
                                   value="{{ old('auth_code') ?? ($search['auth_code'] ?? "") }}">
                        </div>
                     @endif

                    @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'allowSettlementDateAndCardIssuerBankInAccountStatement'])
                        && isset($from) && $from == \App\Models\MerchantReportHistory::ACCOUNT_STATEMENT_REPORT)
                        <div class="col-md-3 my-2">
                            <input type="text"
                                   name="settlement_date_bank"
                                   id="settlement_date_bank"
                                   class="form-control mer_datepicker"
                                   placeholder="{{ __('Settlement Date Bank') }}"
                                   value="{{ old('ac_statement_settlement_date_bank', $search['ac_statement_settlement_date_bank'] ?? '') }}">
                        </div>

                        <div class="col-md-3 my-2">
                            <input type="text"
                                   name="settlement_date_merchant"
                                   id="settlement_date_merchant"
                                   class="form-control mer_datepicker"
                                   placeholder="{{ __("Settlement Date Merchant") }}"
                                   value="{{ old('settlement_date_merchant', $search['settlement_date_merchant'] ?? '') }}">
                        </div>

                        <div class="col-md-3 my-2">
                            <input type="text"
                                   name="card_issuer_name"
                                   class="form-control"
                                   placeholder="{{ __("Card Issuer Bank") }}"
                                   value="{{ old('card_issuer_name') ?? ($search['card_issuer_name'] ?? "") }}">
                        </div>
                    @endif

                    @if(isset($bandPaymentMethods) && !empty($bandPaymentMethods))
                        <div class="col-md-3 my-2">
                            <select class="form-control selectpicker"
                                    multiple
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-title="{{__("Remote Payment Method")}}"
                                    name="remote_payment_method[]"
                                    data-virtual-scroll="true"
                                    id="remote_payment_method"
                                    data-style="''"
                                    data-style-base="form-control">
                                @foreach($bandPaymentMethods as $key => $bandPaymentMethod)
                                    <option value="{{ $key }}"
                                        {{ (is_array($search['remote_payment_method']) || is_array(old('remote_payment_method') )) && in_array($key, old('remote_payment_method') ?? $search['remote_payment_method']) ? 'selected':''}}
                                    >{{ $bandPaymentMethod  }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowBrandImport']))
                        <div class="col-md-3 my-2">
                            <input type="text"
                                   name="remote_internal_merchant_order_id"
                                   class="form-control"
                                   placeholder="{{ __("Remote Internal Merchant Order Id") }}"
                                   value="{{ $search['remote_internal_merchant_order_id'] ?? ""}}">
                        </div>
                        <div class="col-md-3 my-2">
                            <input type="text"
                                   name="remote_order_id"
                                   class="form-control"
                                   placeholder="{{ __("Remote Bank Reference Id") }}"
                                   value="{{ $search['remote_order_id'] ?? ""}}">
                        </div>
                    @endif
                    @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'allowFilterTransactionsByOriginalBankErrorCodeDescription']))
                        <div class="col-md-3 my-2">
                            <input type="text" name="original_bank_error_code" class="form-control" placeholder="{{ __("Original Bank Error Code") }}"  value="{{ $search['original_bank_error_code'] ?? ""}}">
                        </div>
                        <div class="col-md-3 my-2">
                            <input type="text" name="original_bank_error_description" class="form-control" placeholder="{{ __("Original Bank Error Description") }}"  value="{{ $search['original_bank_error_description'] ?? ""}}">
                        </div>
                    @endif
                    @if(isset($allowTransactionChannel) && !empty($allowTransactionChannel))
                        <div class="col-md-3 my-2">
                            <select name="transaction_channel[]"
                                    class="selectpicker form-control"
                                    data-title="{{ __("Transaction Channel") }}"
                                    data-actions-box="true"
                                    data-live-search="true"
                                    data-virtual-scroll="true"
                                    multiple
                                    data-style=""
                                    data-style-base="form-control"
                            >
                                @foreach((new \common\integration\SaleTransaction())->getTransactionChannelList() as $key => $transaction_channel)
                                    @php
                                        $selected_key = '';
                                        if((isset($search['transaction_channel']) && \common\integration\Utility\Arr::isOfType($search['transaction_channel']) || \common\integration\Utility\Arr::isOfType(old('transaction_channel'))) && \common\integration\Utility\Arr::isAMemberOf($key, old('transaction_channel', $search['transaction_channel']))) {
                                            $selected_key = 'selected';
                                        }
                                    @endphp
                                    <option value="{{ $key }}" {{ $selected_key }}>{{ __(\common\integration\Utility\Str::titleCase($transaction_channel)) }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {{-- Buttons --}}
    <div class="row mb-4 mt-4">
        <div class="col-5">
            <div class="d-flex flex-column flex-md-row pull-end pr-2 pl-md-0">
                {{-- Cear Filters --}}
                <a href="{{isset($selfUrls) ? $selfUrls : ""}}"
                   class="text-center mt-3">{{__("Clear Filters")}}</a>
                &nbsp;&nbsp;
                {{-- Cear Filters END --}}
                <button type="button"
                        id="searchbtn"
                        class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto">
                    @if(\common\integration\BrandConfiguration::allowChangeSubmitButton())
                        {{__("Search")}}
                    @else
                        {{__("Submit")}}
                    @endif
                </button>
            </div>
        </div>
        <div class="col-md-7 d-flex flex-column flex-md-row text-md-right">
            @if($cmsInfo['subTitle'] === __('Sale Transaction List') || $cmsInfo['subTitle'] === __('Payment Transaction List'))
                <button type="button"
                        class="btn btn-outline-secondary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto"
                        data-bs-toggle="modal"
                        data-bs-target="#colHideModal">
                    <span class="btn-icon">{{__("Col. Hide")}}&nbsp;&nbsp;&nbsp; <i class="fa fa-eye"></i></span>
                </button>
            @endif
            @if(isset($exportBtnRouteName))
                @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                    <div class="form-group ml-1 mr-1">
                        <select class="form-control selectpicker show-tick rounded" name="file_type" id="file_type">
                            <option value="{{\App\Models\MerchantReportHistory::FORMAT_CSV}}">CSV</option>
                            <option value="{{\App\Models\MerchantReportHistory::FORMAT_XLS}}">XLS</option>
                            <option value="{{\App\Models\MerchantReportHistory::FORMAT_PDF}}">PDF</option>
                        </select>
                    </div>

                    <input type="hidden" name ="both_created_updated_at" value="{{ $search['both_created_updated_at'] ?? true }}">

                    @php
                    $export_url = "";
                    if (isset($selfUrls)){
                        $export_url = $selfUrls."/exports";
                    }
                    if (isset($exportBtnRouteName) && Route::has($exportBtnRouteName)){
                        $export_url = route($exportBtnRouteName);
                    }
                    @endphp
                    <a id="exportbtn"
                       href="{{ $export_url }}"
                       class="btn btn-outline-secondary btn-fix mb-2 mb-md-auto">
                                        <span class="btn-icon">{{__("Export")}}&nbsp;&nbsp;&nbsp; <i
                                                class="fa fa-caret-right"></i></span>
                    </a>

                    <div class="col- ml-2 mb-3">
                        <a class="btn btn-info rounded" href="{{route(Config::get('constants.defines.APP_EXPORT_REPORTS_HISTORY_INDEX'))}}">
                            {{__('Export Report History')}}</a>
                    </div>
                @endif
            @endif

        </div>
    </div>
    {{-- Buttons END --}}
</form>

@push('css')
    @include('partials.css_blade.daterangepicker')
    @include('partials.css_blade.bootstrap-select')
    @include('partials.css_blade.alertify')
    @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'allowSettlementDateAndCardIssuerBankInAccountStatement']))
        @include('partials.css_blade.datepicker')
    @endif

    <style>
        .custome-padding div.bs-actionsbox{
            width: 100% !important;
        }
        /* .form-control {
            padding-top: inherit; !important;
            padding-bottom: inherit; !important;
        } */

        .bootstrap-select.btn-group .dropdown-menu {
            width: 100%; !important;
        }
    </style>
@endpush

@push('scripts')
    @include('partials.js_blade.moment')
    @include('partials.js_blade.bootstrap-select')
    @include('partials.js_blade.daterangepicker')
    @include('partials.js_blade.alertify')
    @include('partials.js_blade.validate')
    @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'allowSettlementDateAndCardIssuerBankInAccountStatement']))
        @include('partials.js_blade.datepicker')
    @endif

    <script>
        $("#account_manager_ids").on("change", function () {
            $('#merchant_id').selectpicker('deselectAll');
        });
        $("#merchant_id").on("change", function () {
            $('#account_manager_ids').selectpicker('deselectAll');
        });

        (function ($) {
            $.fn.selectpicker.defaults = {
                noneSelectedText: "{{__('Nothing selected')}}",
                selectAllText: "{{__('Select All')}}",
                deselectAllText: "{{__('Deselect All')}}",
                noneResultsText: '{{__('No results matched')}} {0}'
            };
        })(jQuery);

        $('#merchant_type').on('hide.bs.select',function () {
            var data = {
                action: 'GET_MERCHANT_LIST_FOR_MERCHANT_TYPE',
                merchant_type: $('#merchant_type option:selected').toArray().map(item => item.value),
                merchant_id_arr: $('#merchant_id_arr').val()
            };
            $.ajax({
                type: "GET",
                url: "{{route(Config::get('constants.defines.APP_ALL_TRANSACTION_INDEX'))}}",
                data: data,
                success: function (response) {
                    $("#merchant_id").empty();
                    $.each(response, function (index, value) {
                        $("#merchant_id").append('<option value="' + value.id + '">' + value.name + '</option>');
                    });
                    $("#merchant_id").selectpicker('refresh');
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        });

        $('#daterange').show();
        $('#dateprangepicker').on('click' ,function () {
            $('#daterange').hide();
        });
        $('#dateprangepicker').daterangepicker({
            @if(!$is_allow_to_select_future_date)
                maxDate: new Date(),
            @endif
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
            "startDate": "{{ old('from_date') ?? (isset($search['from_date']) ? $search['from_date'] : "")}}",
            "endDate": "{{ old('to_date') ?? (isset($search['to_date']) ? $search['to_date'] : "")}}"
        });


        $('#dateprangepicker').on('hide.daterangepicker', function (ev, picker) {
            //do something, like clearing an input
            console.log(picker.startDate.format('YYYY/MM/DD'));
            var dateranges = picker.startDate.format('YYYY/MM/DD') + " - " + picker.endDate.format('YYYY/MM/DD');
            $("#daterange").show().val(dateranges);

        });

        $("#exportbtn").on("click", function (event) {
            event.preventDefault();
            var fromurl = $(this).attr('href');

            var msg = "<strong class='p-0 m-0 d-block'>"+"{{__('Are you sure?')}}"+"</strong>";
            msg += "<p class='p-0 m-0 pl-4 pr-4 text-left'>"+"{{__('The exported report file will be generated and link of it will be sent via email')}}"+".</p>";

            alertify.confirm(msg, function () {
                $('#exportbtn').html('<i class="fa fa-spin fa-refresh"></i>');
                // console.log(fromurl);
                $("#searchboxfrm").attr('action', fromurl);
                $("#searchboxfrm").submit();
            },function () {
                return false;
            });

            $('button.ok').addClass('btn btn-danger rounded').text("<?php echo e(__('Okay')); ?>");
            $('button.cancel').addClass('btn btn-light rounded').text("<?php echo e(__('Cancel')); ?>");
        });

        $("#searchbtn").on("click", function (event) {
            event.preventDefault();
            $("#searchboxfrm").attr('action', '{{isset($selfUrls) ? $selfUrls : Request::url()}}');
            $("#searchboxfrm").submit();
        });

        $('.selectpicker').selectpicker();
        $('.text').addClass('text-wrap');

        @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'allowSettlementDateAndCardIssuerBankInAccountStatement']))
            var localLang = "{{app()->getLocale()}}";
            $('.mer_datepicker').datepicker({
                todayBtn: "linked",
                keyboardNavigation: false,
                forceParse: false,
                calendarWeeks: true,
                autoclose: true,
                locale: 'tr',
                language: localLang,
                format: "yyyy/mm/dd",
            });
        @endif

    </script>
@endpush



