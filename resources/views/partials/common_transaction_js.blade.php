@include('partials.refundRequestValidation_js')
<script src="{{asset('js/Base64.js')}}"></script>
@if(request()->url() == route(config('constants.defines.APP_PAYMENT_TRANSACTION_INDEX')))
    <script src="{{asset('js/transaction.plugin.js')}}"></script>
@else
    <script src="{{asset('js/sipay.plugin.js')}}"></script>
@endif
<script>

    $(document).ready(function () {
        // $('#block_cc_reason').hide();
        $(".details-btn").on('click', function (e) {
            e.preventDefault();

            if($(this).data('is_disable_refund') == 1) {
                $('.disable_refundable').addClass('d-none');
            } else {
                if($('.disable_refundable').hasClass('d-none')) {
                    $('.disable_refundable').removeClass('d-none');
                }
            }
            $('#block_cc').attr('data-merchant_id', $(this).attr('data-merchant_id'))
            $('#sale_transaction_payment_id').val($(this).data('payment_id'));
           $(".settlement_list_show_hide").hide();
            $("#details_real_card_holder_name").html($(this).data('real_card_holder_name'));
            $('#block_ip').attr('data-merchant_id', $(this).attr('data-merchant_id'));
            /* let state_id = $(this).data('transaction_state_id');
            let payment_id = $(this).data('payment_id');
            let sale = $(this).data('id');

            let saleDetailsModal = $("#sale-trans-modal");
            reverseRefund(saleDetailsModal, state_id, "#sale_reverse_refund_btn")


            $('#sale_reverse_refund_btn').on('click', function () {
                $('#is_awaiting_refund').val('');
                $('#sale_id').val(sale);
                $('#paymentID').val(payment_id);
                $('#transaction_state_id').val(state_id);
                $('#refund_history_id').val('');
                $('#confirm-reverse-refund-modal').modal('show');
            })
            reverseRefundCommon() */


            if ($(this).data('original_bank_error_description') != '') {
                $('#div_original_bank_error_description').show();
                $("#original_bank_error_description").html($(this).data('original_bank_error_description'));

            } else {
                $('#div_original_bank_error_description').hide();
            }

            if($(this).data('bank_original_error_code') != '') {
                $('#div_original_bank_error_code').show();
                $("#original_bank_error_code").html($(this).data('bank_original_error_code'));
            } else {
                $('#div_original_bank_error_code').hide();
            }
            $('#div_physical_pos_bank_error_code').hide();
            @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowShowPaymentReasonCodeDetail']) || \common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Backend\BackendMix::class, 'shouldAllowToShowPhysicalPosBankErrorCode']))
                let sale_id = $(this).data('id');
                let transaction_state_id = $(this).data('transaction_state_id');
                let show_original_codes = 0;
                @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\Backend\BackendMix::class, 'shouldAllowToShowPhysicalPosBankErrorCode']))
                    show_original_codes = 1;
                @endif
                if(transaction_state_id == `{{ \App\Models\TransactionState::FAILED }}`){
                    getFailedPaymentReason(sale_id, show_original_codes);
                }
            @endif


            @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowRemoteReferenceForImportedTransaction']))
                let rrn = $(this).data('rrn');
                $("#rrn").html(rrn);
            @endif

            @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'shouldAllowPartialChargeback']))
                let trans_state_id = $(this).data('transaction_state_id');
                if( trans_state_id == `{{ \App\Models\TransactionState::CHARGE_BACK_REQUESTED }}` ||
                    trans_state_id == `{{ \App\Models\TransactionState::CHARGE_BACK_APPROVED }}` ||
                    trans_state_id == `{{ \App\Models\TransactionState::CHARGE_BACK_REJECTED }}` ||
                    trans_state_id == `{{ \App\Models\TransactionState::CHARGE_BACKED }}` ||
                    trans_state_id == `{{ \App\Models\TransactionState::CHARGE_CANCELED }}` ||
                    trans_state_id == `{{ \App\Models\TransactionState::PARTIAL_CHARGEBACK }}`)
                {
                    $("#total_refunded_label").text('Total Chargeback Amount');
                } else {
                    $("#total_refunded_label").text('Total Refunded Amount');
                }
            @endif

            $('#merchant_product_price').html($(this).data('merchant_product_price'))
            $('#sale_point_point_value').html($(this).data('sale_point_point_value'))

            if($(this).data('fastpay_wallet_user_fee') != undefined && $(this).data('fastpay_wallet_user_fee') != null && $(this).data('fastpay_wallet_user_fee') != ''){
                $("#details_fastpay_wallet_user_fee").html($(this).data('fastpay_wallet_user_fee').toFixed(2));
            }
            var plan_code = $(this).data("plan_code"),
                recurring_info = $(this).data("recurring_info");

                if(recurring_info){
                    $(document).find('#recurring_info').show().html(recurring_info).parent().siblings().show();
                }else{
                    $(document).find('#recurring_info').hide().parent().siblings().hide();
                }

            if (plan_code){
                $(document).find('#plan_code').html(plan_code);
                $(document).find('#plan_code_section').show();
            }else {
                $(document).find('#plan_code_section').hide();
            }
            var chn = $(this).data('chn');
            var three_d_source = $(this).data('three_d_source');

            $(this).saleTransactionModal();
            $("#card_holder_name").html(chn);
            $("#three_d_source").html(three_d_source);
            @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowPosProject']))
            $("#pos_project_name").html($(this).data('pos_project_name'));
            @endif
            @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class, 'isAllowPackageNameAndRemainingTurnover']))
            $("#package_name_code").html($(this).data('package_name_code'));
            @endif
        });


        $(".refund-btn").on('click',function () {
            $(this).prop('disabled',true);
            var btnContent = $(this).html();
            $(this).html('&nbsp;<i class="fa fa-refresh fa-spin"></i>&nbsp;');
            var currentUrl = "{{url()->current()}}";



            var refundAmount = parseFloat($("#refund_amount").val());
            var netAmount = $("#hidded_refund_amount").val();
            var productPrice = $("#hidded_product_price").val();
            var totalRefundedAmount = $("#hidded_total_refunded_amount").val();
            var refundableAmount = productPrice-totalRefundedAmount;
            var reason = $("#refund_reason").val();
            var messageLabelSelector = $("#messageLabel");
            console.log('a',refundAmount,refundableAmount.toFixed(4))
            if (refundAmount <= 0){
                messageLabelSelector.html("{{__('Refund amount should not less than or equal 0')}}");
                $(this).prop('disabled',false).html(btnContent);
                return;
            } else if (refundAmount > refundableAmount.toFixed(4)){
                messageLabelSelector.html("{{__('Refund amount should not exceed')}} "+ "("+refundableAmount.toFixed(4)+")")
                $(this).prop('disabled',false).html(btnContent);
                return;
            }

            if(reason === "") {
                messageLabelSelector.html("{{__('Please select a reason')}}");
                $(this).prop('disabled',false).html(btnContent);
                return;
            }


            // added for other reason input
            if(!checkOtherReason($("#refund_reason"))) {
                messageLabelSelector.html("{{__('Please input other reason')}}");
                $(this).prop('disabled',false).html(btnContent);
                return;
            } else {
                reason = resetReasonValue($("#refund_reason"));
            }


            var refundType = $(this).data('refund_type');
            var rstext = $(this).text();
            var trxid = $("input[name=trxid]").val(),
                backup_table_type = $("input[name=backup_table_type]").val();

            @php
                $url = url(config('constants.defines.ADMIN_URL_SLUG') . "/alltransaction/refund");

                if((request()->url() == route(config('constants.defines.APP_PAYMENT_TRANSACTION_INDEX'))) || Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_PAYMENT_TRANSACTION_REFUND'))){
                    $url = url(config('constants.defines.ADMIN_URL_SLUG') . "/payment-transactions/refund");
                }

            @endphp

            var url = '{{ $url }}';

            console.log(url)

            var csrf = '{{ csrf_token() }}';

            $.when(validateRefundRequest(trxid, refundAmount, csrf, url, $(this), backup_table_type)).done(function (status) {

            var refundValidationMessage = messageForRefundRequestValidation(status, $("#refund_confirm"), 'refund');

            $('#confirm_text').html(refundValidationMessage);
            $('#confirm-modal').modal('show');
            $("#refund_confirm").on('click',function () {
                $(this).prop('disabled', true);
                if (reason) {
                    $(this).html('&nbsp;<i class="fa fa-refresh fa-spin"></i>&nbsp;');
                    var data = JSON.stringify({id: trxid,
                        reason: reason,
                        refund_type: refundType,
                        backup_table_type: backup_table_type,
                        refund_amount : refundAmount,
                        _token: '{{ csrf_token() }}'});
                    $.ajax({
                        type: "post",
                        url: url,
                        contentType: "application/json",
                        dataType: "json",
                        data: data,
                        success: function (response) {
                            console.log(response);
                            $('#confirm-modal').modal('hide');
                            $("#refund-btn").html(rstext);
                            $('#sale-trans-modal').modal('hide');
                            alertify.alert(response, function () {
                                // location.reload();
                                if ($("#searchboxfrm").length) {
                                    $("#searchboxfrm").submit();
                                } else {
                                    window.location.href = currentUrl;
                                }
                            });
                        },
                        error: function (xhr, status, error) {
                            console.log(error);
                        }
                    });
                }
            });
            });

            // $(this).prop('disabled',false);

            $(".cancel").on('click',function () {
                $('#confirm-modal').modal('hide');
                $('#sale-trans-modal').modal('hide');

                if ($("#searchboxfrm").length) {
                    $("#searchboxfrm").submit();
                } else {
                    location.reload();
                }
            });
        });


        $(".refund-details-btn").on("click",function () {
            var btntxt = $(this).html();
            var id = $(this).attr("data-id");
            var backup_table_type = $(this).attr("data-backup_table_type");
            var order_id = $(this).attr("data-order_id");
            var payment_id = $(this).attr("data-payment_id");
            /* let transaction_state_id = $(this).data('transaction_state_id'); */
                    {{--var url = "{{url("alltransaction/get_partial_refund")}}";--}}
            var action = 'GET_PARTIAL_REFUND';

            var thisID = 'refund-details-btn-'+id;
            $("#"+thisID).html('&nbsp;<i class="fa fa-refresh fa-spin"></i>&nbsp;');
            $.ajax({
                url:`{{Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_PAYMENT_TRANSACTION_INDEX')) ? route(config('constants.defines.APP_PAYMENT_TRANSACTION_INDEX')) : route(config('constants.defines.APP_ALL_TRANSACTION_INDEX')) }}`,
                type: "get",
                dataType: "json",
                data: {id:id,order_id:order_id,payment_id:payment_id,action:action,backup_table_type:backup_table_type},
                success: function (response) {
                    // console.log(response);
                    $('#refund_data').html(response[0]);
                    $('#order_id1').html(response[1]);
                    $('#payment_id').html(response[2]);
                    $('#sale-partial-refund-modal').modal('show');

                    /* let salePartialRefundModal = $('#sale-partial-refund-modal');
                    reverseRefund(salePartialRefundModal, transaction_state_id, ".reverse-refund-btn-confirm")

                    $('.reverse-refund-btn-confirm').on('click', function () {
                        $('#is_awaiting_refund').val('');
                        $('#sale_id').val('');
                        $('#paymentID').val('');
                        $('#transaction_state_id').val('');
                        $('#refund_history_id').val('');
                        let refund_history_id = $(this).data('id');
                        response[3].forEach((item) => {
                            if (item.id == refund_history_id) {
                                $('#sale_id').val(item.sale_id);
                                $('#paymentID').val(response[2]);
                                $('#transaction_state_id').val(transaction_state_id);
                                $('#refund_history_id').val(item.id);
                            }
                        });
                        $('#awaiting_refund_confirmation_modal').modal('show');
                        $('#confirm-reverse-refund-modal').modal('show');
                    })
                    reverseRefundCommon() */
//                        alertify.alert(response, function () {
//                            location.reload();
//                        });
                    $("#"+thisID).html(btntxt);
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        });

        /* function reverseRefund(modal, transaction_state_id, el) {
            let reverseRefundButton = modal.find(el);
            let approveStatusDiv = modal.find('#approveStatusDiv');
            let rejectStatusDiv = modal.find('#rejectStatusDiv');
            reverseRefundButton.addClass('d-none');
            if(el == "#sale_reverse_refund_btn" && transaction_state_id == {{ \App\Models\TransactionState::PARTIAL_REFUND }}) {
                transaction_state_id = '';
            }
            switch (transaction_state_id) {
                case 5:
                case 13:
                    reverseRefundButton.removeClass('d-none');
                    break;
                default:
                    approveStatusDiv.css('display', 'none');
                    rejectStatusDiv.css('display', 'none');
                    break;
            }
        } */

        $("#refund_amount").on('input',function () {
            var deductFromWallet = 0;
            var thisAmount = parseFloat($(this).val());
            $("#messageLabel").html("");
            $("#amount_to_be_added_to_wallet").html("");

            var refundableAmount = parseFloat($("#hidded_refundable_amount").val()),
                grossAmount = parseFloat($("#hidded_gross_amount").val()),
                productAmount = parseFloat($("#hidded_product_price").val()),
                totRefundedAmount = parseFloat($("#hidded_total_refunded_amount").val());

          //  var remain_amount = productAmount - (totRefundedAmount+thisAmount);
          //  var valid_remain_amount_limit = "{{\common\integration\BankRefund::REFUND_VALID_REMAIN_AMOUNT_LIMIT}}";

            console.log('d',thisAmount,refundableAmount)
            if (thisAmount > refundableAmount){
                $("#messageLabel").html("{{__('Refund amount should not exceed')}} "+ "("+refundableAmount+")")
            }else {
                deductFromWallet = amountToBeRefunded(thisAmount, grossAmount, productAmount, totRefundedAmount);
            }

            if(!isNaN(deductFromWallet)){
                $("#amount_to_be_added_to_wallet").html(deductFromWallet.toFixed(4));
            }


        });

       $('.receipt-as-mail-btn').click(function () {
           $('#send_to_customer, #send_to_merchant').prop('checked', true);
           $('#customer_email, #merchant_email').prop('required', true);

           $('#customer_email').val(
               $(this).data('customer_email')
           );

           $('#merchant_email').val(
               $(this).data('merchant_email')
           );

           $('#receipt_saletransaction_id').val(
               $(this).data('saletransaction_id')
           );

           $('#state_label_for_receipt_mail').val(
               $(this).data('state_label')
           );

           $('#receipt-as-mail-confirm-modal').modal('show');
       });

       $('#send_to_merchant').change(function () {
           $('#merchant_email').prop('disabled', !$(this).prop('checked'));
       });

       $('#send_to_customer').change(function () {
           $('#customer_email').prop('disabled', !$(this).prop('checked'));
       });

       $('.send_mail_checkbox').on('change', function() {
           $('#send-transaction-receipt-as-mail-button').prop('disabled', $('.send_mail_checkbox:checked').length <= 0);
       });

        // Details installment wise settlement details

        $(".list_settlement_date_details").on("click", function (event) {
            $(".selectLoader").prop('disabled',true).append('<i class="fa fa-refresh fa-spin" style="font-size:15px;margin:10px;height:15px;"></i>');
            event.preventDefault();
            $(".settlement_list_show_hide").show();
            $(".settlement_list_show_hide").html('');

            let sale_settlement_sale_id = $("input[name=sale_settlement_sale_id]").val();
            let currency_symbol = $("input[name=sale_settlement_currency_symbol]").val();

            $.ajax({
                url:`{{Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_PAYMENT_TRANSACTION_INDEX')) ? route(config('constants.defines.APP_PAYMENT_TRANSACTION_INDEX')) : route(config('constants.defines.APP_ALL_TRANSACTION_INDEX')) }}`,
                type: "get",
                dataType: "json",
                data: {'sale_settlement_sale_id': sale_settlement_sale_id, 'action': 'get_installment_wise_settlement'},
                success: function (response, textStatus, jQxhr) {
                     if (response.status_code == '{{ \common\integration\ApiService::API_SERVICE_SUCCESS_CODE }}' && response.data.length > 0) {
                        var settlements = response.data;
                        $.each(settlements, function (key, settlement) {
                            let installment_suffix = getInstallmentSuffix(settlement);
                            var html = "<strong style='color:green;'>" + installment_suffix + "  : </strong> </br>"
                                + prepareMerchantSettlementDate(settlement, currency_symbol)
                                + prepareBankSettlementDate(settlement, currency_symbol);

                            $(".settlement_list_show_hide").append(html + '<br>');
                            $(".fa-spin").remove();
                            $(".selectLoader").prop('disabled', false);
                        });
                    }else{
                         var message = "<strong style='color:red;'>" + '{{__("Settlement data not found!")}}' + "  </strong> </br>"
                         $(".settlement_list_show_hide").append(message);
                         $(".fa-spin").remove();
                         $(".selectLoader").prop('disabled', false);
                    }
                },
                error: function (jqXhr, textStatus, errorThrown) {
                    var message = "<strong style='color:red;'>" + '{{__("Some Errors Occurs!")}}' + "  </strong> </br>"
                    $(".settlement_list_show_hide").html(message);
                    $(".fa-spin").remove();
                    $(".selectLoader").prop('disabled',false);
                }
            });
        });
        var isSubmitting = false;
        $('#block_cc').on('click', function () {
            var payment_id = $('#sale_transaction_payment_id').val();
            var currentUrl = "{{ url()->current() }}";
            var btnContent = $(this).html();
            var url = "{!! Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_PAYMENT_TRANSACTION_INDEX')) ? route(config('constants.defines.APP_PAYMENT_TRANSACTION_INDEX')) : route(Config::get('constants.defines.APP_ALL_TRANSACTION_INDEX')) !!}";
            var block_cc_content = $(this).html();
            $(this).html('&nbsp;<i class="fa fa-refresh fa-spin"></i>&nbsp;');

            $('#block-cc-confirm-modal').modal('show');

            $("#block_cc_confirm").on('click',function () {
                if(isSubmitting) {
                    return false;
                }
                isSubmitting = true;
                var block_cc_confirm_content = $(this).html();
                var reason = $("#block_cc_reason").val();

                if(reason === "") {
                    $("#block_cc_reason_error").html("{{__('Please select a reason')}}");
                    $(this).prop('disabled',false).html(btnContent);
                    return;
                }

                if (reason) {
                    $(this).html('&nbsp;<i class="fa fa-refresh fa-spin"></i>&nbsp;');
                    var data = JSON.stringify({
                        payment_id: payment_id,
                        block_reason: reason,
                        merchant_id: $('#block_merchant_id').val(),
                        type: 'block_cc',
                        _token: '{{ csrf_token() }}'
                    });

                    $.ajax({
                        type: "post",
                        url: url,
                        contentType: "application/json",
                        dataType: "json",
                        data: data,
                        success: function (response) {
                            console.log(response);
                            $('#block-cc-confirm-modal').modal('hide');
                            $('#sale-trans-modal').modal('hide');
                            alertify.alert(response.message);
                            $('#block_cc').html(block_cc_content);
                            $('#block_cc_confirm').html(block_cc_confirm_content);
                        },
                        error: function (xhr, status, error) {
                            console.log(error);
                        }
                    });
                }
            });
            isSubmitting = false;
        });

        $('#block_ip').on('click', function () {
            var currentUrl = "{{ url()->current() }}";
            var btnContent = $(this).html();
            var url = "{!! route(Config::get('constants.defines.APP_ALL_TRANSACTION_INDEX')) !!}";
            var block_ip_content = $(this).html();
            $(this).html('&nbsp;<i class="fa fa-refresh fa-spin"></i>&nbsp;');

            var merchant_id = $(this).attr('data-merchant_id');
            // var ip = $(this).attr('data-ip');
            // var ip = $("#ip").text().trim();
            var nameFormat = $("#ip").text().trim();
            var ipAddress = nameFormat.split(' ')[0];
            var data = JSON.stringify({
                merchant_id: merchant_id,
                assigned_ip: ipAddress,
                request_type: 'block_ip',
                _token: '{{ csrf_token() }}'
            });

            $.ajax({
                type: "post",
                url: url,
                contentType: "application/json",
                dataType: "json",
                data: data,
                success: function (response) {
                    console.log(response);
                    alertify.alert(response['message']);
                    $('#block_ip').html(block_ip_content);
                },
                error: function (xhr, status, error) {
                    console.log(error);
                }
            });
        });

        function getInstallmentSuffix(installment) {

            let installment_no = installment.installments_number;
            let j = installment_no % 10, k = installment_no % 100;
            let TYPE_ONE =  '{{ __("st") }}' , TYPE_TWO = '{{ __("nd") }}' , TYPE_THREE = '{{ __("nd") }}' , TYPE_FOUR =  '{{ __("th") }}'

            let text = '{{__("Installment")}}'
            if (j === 1 && k !== 11) {
                return installment_no + TYPE_ONE + ' ' + text;
            }

            if (j === 2 && k !== 12) {
                return installment_no + TYPE_TWO + ' ' + text;
            }

            if (j === 3 && k !== 13) {
                return installment_no + TYPE_THREE + ' ' + text;
            }
            if(installment_no == '0'){
                return '{{__("Single Payment")}}'
            }

            return installment_no + TYPE_FOUR + ' ' + text;
        }

        function prepareMerchantSettlementDate(saleSettlement, currency_symbol) {
            let amount = saleSettlement.net_settlement;
            return "<span class='pl-1'> {{__("Settlement Date Merchant")}}  :  </span>" + saleSettlement.formatted_settlement_date_merchant + " , " + amount + currency_symbol+ "</br>";
        }

        function prepareBankSettlementDate(saleSettlement, currency_symbol) {
            let amount = saleSettlement.net_settlement;
            return "<span class='pl-1'> {{ __("Settlement Date Bank") }} :  </span>" + saleSettlement.formatted_settlement_date_bank + " , " + amount + currency_symbol;
        }
    });


    const getFailedPaymentReason = (sale_id, show_original_code) => {
        $.ajax({
            type: 'get',
            data: {
                action: `{{ \App\Models\Sale::SALE_ERROR_DETAIL_MAPPING_ACTION }}`,
                show_original_code: show_original_code,
                sale_id: sale_id,
            },
            success: function(res) {
                if (res != [] && res.reason_code && res.reason_code_detail){
                    if(!show_original_code) {
                        $('#div_original_bank_error_code').show();
                        $('#div_original_bank_error_code').find('label').text(`{{ __('Payment Reason Code') }}`)
                        $('#div_original_bank_error_description').find('label').text(`{{ __('Payment Reason Code Detail') }}`)
                        $("#original_bank_error_description").html(res.reason_code_detail)
                        $("#original_bank_error_code").html(res.reason_code)
                    }else {
                        $('#div_physical_pos_bank_error_code').show();
                        $("#physical_pos_bank_error_code").html(res.reason_code)
                    }
                }
            },
            error: function(error) {
                console.log('error');
            }
        });
    }

    /* function reverseRefundCommon() {
        $('#reverse_refund_confirm').on('click', function () {
            $('#reverse-refund-form').submit();
            $('#confirm-reverse-refund-modal').find('button').prop('disabled', true);
        })
        $('#awaiting_refund_confirm').on('click', function () {
            $('#is_awaiting_refund').val(1);
            $('#awaiting_refund_confirmation_modal').modal('hide');
        })
        $('#awaiting_refund_cancel').on('click', function () {
            $('#is_awaiting_refund').val(0);
            $('#awaiting_refund_confirmation_modal').modal('hide');
        })
    } */
</script>
