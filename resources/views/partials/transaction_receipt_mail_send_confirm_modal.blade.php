
<div class="modal fade" id="receipt-as-mail-confirm-modal" tabindex="-1" role="dialog" aria-labelledby="receipt-as-mail-confirm-modalLabel"
     aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-less">
                <h5 class="modal-title pr-4 pl-4 w-100">{{ __("Transaction Receipt Send as Mail") }}</h5>
                <button type="button" class="close cancel p-1 m-0 pr-4 pl-4" data-bs-dismiss="modal" aria-label="Close">

                </button>
                <hr>
            </div>

            <div class="modal-body font13">
                @php
                    $payment_send_receipt_as_mail_permission = \Illuminate\Support\Facades\Auth::user()->hasPermissionOnAction( \Illuminate\Support\Facades\Config::get('constants.defines.APP_PAYMENT_TRANSACTION_INDEX'));
                @endphp
                <form action="{{ !empty($payment_send_receipt_as_mail_permission) ? route(Config::get('constants.defines.APP_PAYMENT_TRANSACTION_SEND_RECEIPT')) : route(Config::get('constants.defines.APP_ALL_TRANSACTION_SEND_RECEIPT')) }}" method="POST" id="send-transaction-receipt-as-mail-form">
                    @csrf
                    <input type="hidden" name="saletransaction_id" id="receipt_saletransaction_id">
                    <input type="hidden" name="state_label" id="state_label_for_receipt_mail">
                    <div class="row pl-4 pr-4 mb-4">
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input send_mail_checkbox" id="send_to_merchant" name="send_to_merchant">
                                    <label class="custom-control-label" for="send_to_merchant">{{__('Send Receipt To Merchant')}}</label>
                                </div>
                                <input type="email" class="form-control" name="merchant_email" id="merchant_email" placeholder="{{ __('Merchant email') }}">
                            </div>
                        </div>
                        <br>
                        <div class="col-md-12">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input send_mail_checkbox" id="send_to_customer" name="send_to_customer">
                                    <label class="custom-control-label" for="send_to_customer">{{__('Send Receipt To Customer')}}</label>
                                </div>
                                <input type="email" class="form-control" id="customer_email" name="customer_email" placeholder="{{ __('Customer email') }}">
                            </div>
                        </div>
                    </div>
                </form>
                <div class="row pl-4 pr-4">
                    <div class="col-6">
                        <button class="btn btn-block btn-primary rounded" type="submit" form="send-transaction-receipt-as-mail-form" id="send-transaction-receipt-as-mail-button">{{ __("Send") }}</button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-block btn-primary rounded cancel" data-bs-dismiss="modal">{{ __("Cancel") }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


