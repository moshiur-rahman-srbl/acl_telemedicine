/**
 * Created by MD Eyasin on 8/25/2019.
 */

if (!String.prototype.trim) {
    String.prototype.trim = function () {
        return this.replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, '');
    };
}
(function ($) {
    $.fn.getRequest = function (options) {
        console.log(this[0]);
        $from = this[0];
        console.log($($from.id).serialize());
        options = $.extend({
            success: function (data) {
                console.log(data)
            },
            error: function (data) {
                console.log(data);
            }
        }, options);

        if ($.isFunction(options.success)) {
            options.success.call(this, null);
        }
        console.log("get work");
        return this.each(function () {
        })
    };

    $.fn.saleRequest = function (options) {
        options = $.extend({
            'contentType': 'application/json'
        }, options);





        // let id = $(this).data('id');
        // let url = $(this).data('url');

        // $.ajax({
        //     'url': url,
        //     'method': 'POST',
        //     'data': {'id': id},
        //     'content-type': options.contentType,
        //     'success': function (data) {
        //         if (typeof data !== 'undefined') {
        //             if (typeof options.success !== 'undefined') {
        //                 options.success(data);
        //             } else {
        //                 options.error(data)
        //             }
        //         } else {
        //             console.log("undefined");
        //             options.error(data);
        //         }
        //     },
        //     'error': function (data) {
        //         options.error(data);
        //     }
        // });

        console.log("post work");
        console.log($(this).parent('form#charge-back-from').serializeArray());

        return this;
    };

    $.fn.printTable = function () {
        console.log(this[0]);
    };

    $.fn.supportViewModal = function (options = '', check_permission = '') {

        let ticket = '';
        let currentToken = $('meta[name="csrf-token"]').attr('content');
        let encodeData = $(this).data('ticket');
        let user_id = $(this).data('user_id');
        let reassign_btn_name = $(this).data('reassign_btn_name');
        let encodeToken = encodeData.substr(0, encodeData.indexOf('$') + 1).replace('$', "");
        let encodeTicket = encodeData.substr(encodeData.indexOf('$') + 1);
        if (encodeToken === currentToken) {
            let $this = $('#' + $(this).data('modal'));
            if (typeof window.atob !== 'undefined')
                ticket = JSON.parse(window.atob(encodeTicket));
            else
                ticket = Base64.decode(encodeTicket);

            if(ticket.status == 'open'){
                console.log(options);
                if (options == 1 || options == true || options == 'true') {
                    $(".close-btn-div").show();
                } else {
                    if(ticket.assigned_user_id !== 0 && ticket.assigned_user_id !== user_id && (check_permission == 0 || check_permission == false || check_permission == 'false')){
                        $(".close-btn-div").hide();
                        $(".reassign-btn-div").hide();
                    }else if((ticket.assigned_user_id == 0 || ticket.assigned_user_id !== user_id) && (check_permission == 0 || check_permission == false || check_permission == 'false')){
                        $(".close-btn-div").hide();
                        $(".reassign-btn-div").show();
                        $(".reassign-btn-div").addClass("col-12");
                    }else{

                        $(".close-btn-div").show();
                        $(".reassign-btn-div").show();
                        $(".reassign-btn-div").addClass("col-6");
                        $("#reassign").text(reassign_btn_name);
                    }
                }

            }else if(ticket.status == 'closed'){

                $(".close-btn-div").hide();
                $(".reassign-btn-div").hide();
            }

            $this.find('#ticket_id').text(ticket.ticket_id);
            $this.find('#cust_num').text(ticket.customer_number);
            $this.find('#user_mer_id').text(ticket.user_id);
            $this.find('#gsm_number').text(ticket.gsm);
            $this.find('#user_mer_name').text(ticket.username);
            $this.find('#merchant_id').text(ticket.merchant_id);
            $this.find('#department').text(typeof ticket.department !== "undefined" ? ticket.department : 'NA');
            $this.find('#open_date').text(ticket.created_at);
            $this.find('#title').text(ticket.title);
            $this.find('#message').text(ticket.message);
            $this.find('#conversation').data('sendid',ticket.ticket_id);
            $this.find('#close-ticket').data('sendid',ticket.id);
            $this.find('#reassign').data('ticket_str_id',ticket.ticket_id);
            $this.find('#reassign').data('assigned_user_id',ticket.assigned_user_id);
            $this.find('#reassign').data('ticket_id',ticket.id);
            $this.modal('show');

        } else {
            if (typeof alertify !== "undefined") {
                alertify.alert("You are Wrong", function () {
                    window.location.reload();
                });
            }else {
                window.alert("You are Wrong");
                window.location.reload();
            }
        }

        return this;
    }



    $.fn.saleTransactionModal = function (options) {
        let transaction = '';
        let currentToken = $('meta[name="csrf-token"]').attr('content');
        let encodeData = $(this).data('transaction');
        let dplJsonData = $(this).data('dpl_json_data');
        let encodeToken = encodeData.substr(0, encodeData.indexOf('$') + 1).replace('$', "");
        let encodeTicket = encodeData.substr(encodeData.indexOf('$') + 1);
        if (encodeToken === currentToken) {
            let $this = $('#' + $(this).data('modal'));
            if (typeof window.atob !== 'undefined')
                transaction = JSON.parse(Base64.decode(JSON.stringify(encodeTicket)));
            else
                transaction = Base64.decode(JSON.stringify(encodeTicket));

            dataAssignment(transaction, $this, true, dplJsonData);
            if (typeof manipulateSettlementDate == 'function') {
                manipulateSettlementDate($(this), $("#sdm"), $(this).data('id'), $(this).data('url'));
            }

            $this.modal('show');

        } else {
            if (typeof alertify !== "undefined") {
                alertify.alert("You are Wrong", function () {
                    window.location.reload();
                });
            }else {
                window.alert("You are Wrong");
                window.location.reload();
            }
        }

        return this;
    }




}(jQuery));

function dataAssignment(transaction, $this, decode = true, dplJsonData) {
    if (transaction.payment_type_id === 2){
        $('#operator-div').show();
    }else {
        $('#operator-div').hide();
    }

    if (transaction.payment_type_id === transaction.pay_rec_opt){
        $('.ccno').show();
    }else {
        $('.ccno').hide();
    }

    if (transaction.wallet_refund){
        $('#refund-btn').show();
    } else {
        $('#refund-btn').hide();
    }

    $(".total_refund_fee_div").hide();
    $(".total_chargeback_fee_div").hide();

    var dynamicElement = $this.find("#dynamic_fields");
    dynamicElement.html("");
    if(dplJsonData != '')
    {
        $('#div_dynamic_fields').show();
        var locale = document.getElementsByTagName("html")[0].getAttribute("lang");
        const TURKISH = "tr"
        $.each(dplJsonData,function(key,value)
        {
            var title = value.title;
            if(locale == TURKISH)
            {
                var title = decode_utf8(value.title_tr, decode);
            }

        dynamicElement.append('<div><span class="m-0 p-0">'+title+' : </span>'+decode_utf8(value.input, decode)+'</div>');
        });
    }else {
        $('#div_dynamic_fields').hide();
    }
    formatted_customer_name = getNameSurnameByFullName(decode_utf8(transaction.customer_masked, decode));
    formatted_user_name = getNameSurnameByFullName(decode_utf8(transaction.user_masked, decode));
    $this.find("input[name=trxid]").val(transaction.id);
    $this.find("input[name=sale_settlement_sale_id]").val(transaction.id);
    $this.find("input[name=sale_settlement_currency_symbol]").val(transaction.currency_symbol);
    $this.find("#trans_id").html(transaction.payment_id);
    $this.find("#installment").html(transaction.installment);
    $this.find("#maturity_period").html(transaction.maturity_period);
    $this.find("#invoice_id").html(transaction.invoice_id);
    $this.find("#order_id").html(transaction.order_id);
    $this.find("#pos_id").html(transaction.pos_id);
    $this.find("#status_id").html(transaction.transaction_state_id);
    $this.find("#status").html(decode_utf8(transaction.state_label, decode));
    $this.find("#gsm").html(transaction.gsm_number);
    $this.find("#cust_name").html(formatted_customer_name.name);
    $this.find("#cust_surname").html(formatted_customer_name.surname);
    $this.find("#user_id").html(transaction.user_id);
    $this.find("#merc_name").html(decode_utf8(transaction.merchant_masked, decode));
    $this.find("#user_name").html(formatted_user_name.name);
    $this.find("#user_surname").html(formatted_user_name.surname);
    $this.find("#price").html(decode_utf8(transaction.product_price, decode));
    $this.find("#pre_auth_transaction_amount").html(decode_utf8(transaction.pre_auth_transaction_amount, decode));
    $this.find("#chargeback_reason").html(decode_utf8(transaction.refund_reason, decode));

    if (transaction.merchant_server_id === "") {
        $('#div_merchant_server_id').hide();
    } else {
        $('#div_merchant_server_id').show();
        $this.find("#merchant_server_id").html(decode_utf8(transaction.merchant_server_id, decode));
    }

    $this.find("#referer_url").html(decode_utf8(transaction.referer_url, decode));
    $this.find("#amount").html(decode_utf8(transaction.gross, decode) );
    $this.find("#share").html(decode_utf8(transaction.net, decode));
    $this.find("#net_share").html(decode_utf8(transaction.net_share, decode));
    $this.find("#cou").html(decode_utf8(transaction.user_commission, decode));
    $this.find("#com").html(decode_utf8(transaction.merchant_commission, decode));
    $this.find("#totcom").html(decode_utf8(transaction.total_commission , decode));

    if (parseFloat(transaction.pay_by_token_fee.split(" ")[0]) > 0 ) {
        $('#div_pay_by_token_fee').show();
        $this.find("#paybytokenfee").html(decode_utf8(transaction.pay_by_token_fee , decode));
    } else {
        $('#div_pay_by_token_fee').hide();
    }

    $this.find("#cost").html(decode_utf8(transaction.cost, decode) );
    $this.find("#rev").html(decode_utf8(transaction.rev, decode) );
    $this.find("#method").html(decode_utf8(transaction.method, decode) );
    $this.find("#operator").html(transaction.operator);
    $this.find("#pos").html(decode_utf8(transaction.pos, decode));
    $this.find("#sdb").html(transaction.settlement_date_bank2);
    $this.find("#issuer_name").html(decode_utf8(transaction.card_issuer_name, decode));
    $this.find("#bill_name").html(decode_utf8(transaction.bill_name, decode));
    $this.find("#bill_surname").html(decode_utf8(transaction.bill_surname, decode));
    $this.find("#billing_name").html(decode_utf8(transaction.bill_name, decode));
    $this.find("#billing_surname").html(decode_utf8(transaction.bill_surname, decode));
    $this.find("#description").html(decode_utf8(transaction.description, decode));
    $this.find("#billing_email").html(decode_utf8(transaction.bill_email, decode));
    $this.find("#bill_phone").html(transaction.bill_phone);

    if (parseFloat(transaction.sale_currency_conversion_from_currency) > 0) {
        $('#div_currency_converted').show();
        $this.find("#from_currency").html(decode_utf8(transaction.sale_currency_conversion_from_currency, decode));
        $this.find("#to_currency").html(decode_utf8(transaction.sale_currency_conversion_to_currency, decode));
        $this.find("#conversion_rate").html(decode_utf8(transaction.sale_currency_conversion_conversion_rate, decode));
        $this.find("#currency_converted_created_at").html(decode_utf8(transaction.sale_currency_conversion_created_at, decode));
    } else {
        $('#div_currency_converted').hide();
    }


    $this.find("#commission_percentage").html(decode_utf8(transaction.merchant_commission_percentage, decode));
    //$this.find("#commission_fixed").html(decode_utf8(transaction.merchant_commission_fixed, decode));
    $this.find("#cot_percentage").html(decode_utf8(transaction.cot_percentage, decode));
    //$this.find("#cot_fixed").html(decode_utf8(transaction.cot_fixed, decode));

    $this.find("#bill_email").html(decode_utf8(transaction.bill_email, decode));
    $this.find("#bill_postcode").html(transaction.bill_postcode);
    $this.find("#bill_address").html(decode_utf8(transaction.bill_address, decode));
    $this.find("#bill_city").html(decode_utf8(transaction.bill_city, decode));
    $this.find("#bill_country").html(decode_utf8(transaction.bill_country, decode));
    $this.find("#card_holder_bank").html(decode_utf8(transaction.card_holder_bank, decode));
    $this.find("#debit_credit_card").html(transaction.debit_credit_card);
    $this.find("#sdm").html(transaction.settlement_date_merchant2);
    $this.find("#card_program").html(decode_utf8(transaction.card_program, decode));
    $this.find("#card_network").html(decode_utf8(transaction.card_network, decode));
    $this.find("#ip").html(transaction.ip + ' (' +transaction.country_name + ')');
    $this.find("#result").html(decode_utf8(transaction.result, decode));
    $this.find("#authCode").html(transaction.auth_code ? decode_utf8(transaction.auth_code, decode) : '');
    $this.find("#transaction_pos_type").html(transaction.transaction_pos_type ? decode_utf8(transaction.transaction_pos_type, decode) : '');
    $this.find("#dt").html(transaction.created_at2);
    $this.find("#pdt").html(transaction.updated_at2);
    $this.find("#chargeback_reject_explanation").html(transaction.sale_chargeback_reject_explanation);
    $this.find("#admin_explanation").html(transaction.admin_force_chargeback_explanation);
    $this.find(".rdoc a").attr('href', transaction.uploadedDoc);
    $this.find("#credit_card").html(transaction.credit_card);
    $this.find("#chargeback_amount").html(decode_utf8(transaction.chargeback_amount, decode));
    $this.find("#real_card_holder_name").html(decode_utf8(transaction.real_card_holder_name, decode));
    $this.find("#details_fastpay_wallet_cashback_commission").html(decode_utf8(transaction.cashback_amount, decode));

    if (transaction.remote_transaction_datetime2 !== '') {
        $this.find("#remote_transaction_datetime").html(transaction.remote_transaction_datetime2);
        $this.find("#remote_transaction_datetime").parent().parent().show();
    } else {
        $this.find("#remote_transaction_datetime").parent().parent().hide();
        $this.find("#remote_transaction_datetime").html('');
    }

    if (transaction.remote_acquirer_reference !== '') {
        $this.find("#remote_acquirer_reference").html(transaction.remote_acquirer_reference);
        $this.find("#remote_acquirer_reference").parent().parent().show();
    } else {
        $this.find("#remote_acquirer_reference").parent().parent().hide();
        $this.find("#remote_acquirer_reference").html('');
    }

    if (transaction.remote_operation_name !== '') {
        $this.find("#remote_operation_name").html(transaction.remote_operation_name);
        $this.find("#remote_operation_name").parent().parent().show();
    } else {
        $this.find("#remote_operation_name").parent().parent().hide();
        $this.find("#remote_operation_name").html('');
    }

    if (transaction.remote_product_price !== '') {
        $this.find("#remote_product_price").html(transaction.remote_product_price + ' ' + transaction.currency_symbol);
        $this.find("#remote_product_price").parent().parent().show();
    } else {
        $this.find("#remote_product_price").parent().parent().hide();
        $this.find("#remote_product_price").html('');
    }

    if (transaction.is_installment_wise_settlement == 1) {
        $("#settlement_details_installment_wise").show();
    } else {
        $("#settlement_details_installment_wise").hide();
    }


    if (transaction.remote_sale_reference_id !== '') {
        $this.find("#remote_sale_reference_id").html(transaction.remote_sale_reference_id);
        $this.find("#remote_sale_reference_id").parent().parent().show();
    } else {
        $this.find("#remote_sale_reference_id").parent().parent().hide();
        $this.find("#remote_sale_reference_id").html('');
    }


    if (transaction.remote_payment_method !== '') {
        $this.find("#remote_payment_method").html(decode_utf8(transaction.remote_payment_method, decode));
        $this.find("#remote_payment_method").parent().parent().show();
    } else {
        $this.find("#remote_payment_method").parent().parent().hide();
        $this.find("#remote_payment_method").html('');
    }

    if (transaction.remote_internal_merchant_order_id !== '') {
        $this.find("#remote_internal_merchant_order_id").html(decode_utf8(transaction.remote_internal_merchant_order_id, decode));
        $this.find("#remote_internal_merchant_order_id").parent().parent().show();
    } else {
        $this.find("#remote_internal_merchant_order_id").parent().parent().hide();
        $this.find("#remote_internal_merchant_order_id").html('');
    }

    if (transaction.remote_order_id !== '') {
        $this.find("#remote_bank_reference_id").html(decode_utf8(transaction.remote_order_id, decode));
        $this.find("#remote_bank_reference_id").parent().parent().show();
    } else {
        $this.find("#remote_bank_reference_id").parent().parent().hide();
        $this.find("#remote_bank_reference_id").html('');
    }

    if (transaction.security_type !== '') {
        $this.find("#remote_security_type").html(decode_utf8(transaction.security_type, decode));
        $this.find("#remote_security_type").parent().parent().show();
    } else {
        $this.find("#remote_security_type").parent().parent().hide();
        $this.find("#remote_security_type").html('');
    }

    if (transaction.remote_bkm_serial_no != '') {
        $this.find("#remote_bkm_serial_no").html(transaction.remote_bkm_serial_no);
    }

    $this.find("#formated_total_refunded_amount").html(
        totalRefundedAmount(transaction.total_refunded_amount,
            transaction.actual_product_price, transaction.actual_gross
        )+' '+ decode_utf8(transaction.currency_symbol, decode)
    );

    // if(transaction.uploadedDoc === '#') {
    //     $this.find(".rdoc a").removeAttr('target').removeAttr('download');
    // } else {
    //     $this.find(".rdoc a").attr('target', '_blank').attr('download', transaction.id);
    // }

    if(transaction.card_holder_name != ''){
        $this.find("#card_holder_name").html(decode_utf8(transaction.card_holder_name, decode));
    }

    if(transaction.maturity_period == ''){
        $("#maturity_div").hide();
    }else{
        $("#maturity_div").show();
    }
    if(transaction.check_refund_button){
        if(transaction.payment_type_id == 1){
            $("#refund-btn").hide();
        }else if(transaction.payment_type_id == 3){
            $("#refund-btn-credit").hide();
        }
    }

    if (transaction.transaction_state_id === 5 || transaction.transaction_state_id === 13 ) {
        $(".total_refund_fee_div").show();
        $(".total_chargeback_fee_div").hide();
        $this.find("#refund_fee").html(decode_utf8(transaction.refunded_chargeback_fee, decode));
    }else if(transaction.transaction_state_id === 11) {
        $(".total_refund_fee_div").hide();
        $(".total_chargeback_fee_div").show();
        $this.find("#chargeback_fee").html(decode_utf8(transaction.refunded_chargeback_fee, decode));
    }else{
        $(".total_refund_fee_div").hide();
        $(".total_chargeback_fee_div").hide();
    }

    $this.find("#merchant_terminal_id").html(decode_utf8(transaction.merchant_terminal_id, decode));

    if(transaction.merchant_terminal_id != ''){
        $(".teminal_no_dev").show();
    } else {
        $(".teminal_no_dev").hide();
    }

    $this.find("#integrator_commission").html(transaction.sale_integrator_commission);

    var bill_name = decode_utf8(transaction.bill_name, decode),
        bill_surname = decode_utf8(transaction.bill_surname, decode),
        bill_phone = transaction.bill_phone,
        bill_email = transaction.bill_email,
        bill_postcode = transaction.bill_postcode,
        bill_address = decode_utf8(transaction.bill_address, decode),
        bill_city = decode_utf8(transaction.bill_city, decode),
        bill_state = decode_utf8(transaction.bill_state, decode),
        bill_country = decode_utf8(transaction.bill_country, decode),
        sale_billing_bill_tckn = decode_utf8(transaction.sale_billing_bill_tckn, decode),
        sale_billing_bill_tax_no = transaction.sale_billing_bill_tax_no,
        sale_billing_bill_tax_office = transaction.sale_billing_bill_tax_office,
        sale_billing_customer_type = transaction.sale_billing_customer_type;


    if (bill_name !== 'null') {
        $this.find("#billing_information_section").children("#bill_name_section").children("#bill_name").html(bill_name);
    }
    if (bill_surname !== 'null') {
        $this.find("#billing_information_section").children("#bill_name_section").children("#bill_surname").html(bill_surname);
    }
    if (bill_name !== 'null') {
        $this.find("#billing_information_section").children("#company-section").children("#company_billing_name").html(bill_name + ' ' + bill_surname);
    }


    $this.find("#billing_information_section").children("#bill_national_id_section").hide();

    if (sale_billing_bill_tckn && sale_billing_bill_tckn !== 'null') {
        $this.find("#billing_information_section").children("#bill_national_id_section").show();
        $this.find("#billing_information_section").children("#bill_national_id_section").children("#bill_national_id").html(sale_billing_bill_tckn);
    }

    $this.find("#billing_information_section").children("#bill_tax_no_section").hide();

    if (sale_billing_bill_tax_no && sale_billing_bill_tax_no !== 'null') {
        $this.find("#billing_information_section").children("#bill_tax_no_section").show();
        $this.find("#billing_information_section").children("#bill_tax_no_section").children("#bill_tax_no").html(sale_billing_bill_tax_no);
    }

    $this.find("#billing_information_section").children("#bill_tax_office_section").hide();

    if (sale_billing_bill_tax_office && sale_billing_bill_tax_office !== 'null') {
        $this.find("#billing_information_section").children("#bill_tax_office_section").show();
        $this.find("#billing_information_section").children("#bill_tax_office_section").children("#bill_tax_office").html(sale_billing_bill_tax_office);
    }


    if (sale_billing_customer_type === 2) {
        $this.find("#billing_information_section").children("#company-section").show();
        $this.find("#billing_information_section").children("#bill_name_section").hide();
        $this.find("#billing_information_section").children("#bill_surname_section").hide();
    } else {
        $this.find("#billing_information_section").children("#bill_name_section").show();
        $this.find("#billing_information_section").children("#bill_surname_section").show();
        $this.find("#billing_information_section").children("#company-section").hide();
    }



    $this.find("#billing_information_section").children("#bill_email_section").hide();
    if (bill_email && bill_email !== 'null') {
        $this.find("#billing_information_section").children("#bill_email_section").show();
        $this.find("#billing_information_section").children("#bill_email_section").children("#billing_email").html(bill_email);
    }

    $this.find("#billing_information_section").children("#bill_phone_section").hide();
    if (bill_phone && bill_phone !== 'null') {
        $this.find("#billing_information_section").children("#bill_phone_section").show();
        $this.find("#billing_information_section").children("#bill_phone_section").children("#bill_phone").html(bill_phone);
    }

    $this.find("#billing_information_section").children("#bill_address_section").hide();
    if (bill_address && bill_address !== 'null') {
        $this.find("#billing_information_section").children("#bill_address_section").show();
        $this.find("#billing_information_section").children("#bill_address_section").children("#bill_address").html(bill_address);
    }

    $this.find("#billing_information_section").children("#bill_postcode_section").hide();
    if (bill_postcode && bill_postcode !== 'null') {
        $this.find("#billing_information_section").children("#bill_postcode_section").show();
        $this.find("#billing_information_section").children("#bill_postcode_section").children("#bill_postcode").html(bill_postcode);
    }

    $this.find("#billing_information_section").children("#bill_city_section").hide();
    if (bill_city && bill_city !== 'null') {
        $this.find("#billing_information_section").children("#bill_city_section").show();
        $this.find("#billing_information_section").children("#bill_city_section").children("#bill_city").html(bill_city);
    }

        if(bill_state){
            $this.find("#bill_state_section").show();
        }else{
            $this.find("#bill_state_section").html('');
        }

    $this.find("#billing_information_section").children("#bill_country_section").hide();
    if (bill_country && bill_country !== 'null') {
        $this.find("#billing_information_section").children("#bill_country_section").show();
        $this.find("#billing_information_section").children("#bill_country_section").children("#bill_country").html(bill_country);
    }

    if(transaction.dpl_id>0){
        $(".dpl_class").show();
        $this.find("#dpl_id").html(transaction.dpl_id);
        $this.find("#bill_address").html(decode_utf8(transaction.bill_address, decode));
    }else{
        $(".dpl_class").hide();

    }


    if(transaction.payment_type_id == transaction.pay_rec_opt) {
        $(".ccno").show();
    } else {
        $(".ccno").hide();
    }

    $("#download-btn").attr('href', transaction.downloadURL);


    if(transaction.sale_type == 2 && transaction.transaction_state_id != 3 && transaction.transaction_state_id != 14 ){
        $(".pre_auth_transaction_amount").show();
    }else{
        $(".pre_auth_transaction_amount").hide();
    }

    if(transaction.transaction_state_id == 5 || transaction.transaction_state_id == 13){
        $(".refund_recipt_btn").show();
        $(".refund_recipt_btn a").attr("href",transaction.refundReceiptUrl).attr('target', '_blank');
    }else{
        $(".refund_recipt_btn").hide();
        $(".refund_recipt_btn a").attr("href", "#").attr('target', '_blank');
    }

    if(transaction.transaction_state_id === 3 && transaction.sale_type === 2){
        $(".preAuthClass").show();
        $("#preAuthApprove input[name=sale_id]").val(transaction.id);
        $("#preAuthReject input[name=sale_id]").val(transaction.id);
        $("#preAuthApprove input[name=merchant_id]").val(transaction.merchant_id);
        $("#preAuthReject input[name=merchant_id]").val(transaction.merchant_id);
        $("#preAuthApprove input[name=invoice_id]").val(transaction.invoice_id);
        $("#preAuthReject input[name=invoice_id]").val(transaction.invoice_id);
        $("#preAuthApprove input[name=total]").val(transaction.actual_product_price);
        $(".preAuthClass #product_price_text_box").val(transaction.actual_product_price);
    } else {
        $(".preAuthClass").hide();
        $("#preAuthApprove input[name=sale_id]").val('');
        $("#preAuthReject input[name=sale_id]").val('');
        $("#preAuthApprove input[name=merchant_id]").val('');
        $("#preAuthReject input[name=merchant_id]").val('');
        $("#preAuthApprove input[name=invoice_id]").val('');
        $("#preAuthReject input[name=invoice_id]").val('');
    }
    $(".preAuthClass #product_price_text_box").on('input',function () {
        var amount = $(this).val();
        //if (amount > 0 && amount <= transaction.actual_product_price){
        if (amount > 0) {
            $("#preAuthApprove input[name=total]").val($(this).val());
        }
        /*}else {
            if (amount > transaction.actual_product_price){
                $(this).val(transaction.actual_product_price);
            }

            $("#preAuthApprove input[name=total]").val(transaction.actual_product_price);
        }*/

    });

    $(".reStoreFromBackSuccess input[name=sale_id]").val(transaction.id);

    if(transaction.transaction_state_id == transaction.stateID) {
        $(".rndbtn").show();
    } else {
        $(".rndbtn").hide();
    }

    //rolling amount
    $this.find("#rolling_amount").html(decode_utf8(transaction.rolling_amount, decode));
    $this.find("#rolling_settlement").html(transaction.rolling_settlement);

    var refundableAmount = (transaction.actual_product_price - transaction.total_refunded_amount);


    $("#refund_amount").val(refundableAmount.toFixed(4));
    $("#hidded_product_price").val(transaction.actual_product_price);
    $("#hidded_refund_amount").val(transaction.actual_product_price);
    $("#hidded_total_refunded_amount").val(transaction.total_refunded_amount);
    $("#hidded_gross_amount").val(transaction.actual_gross);

    var refundable_amount = amountToBeRefunded(refundableAmount, transaction.actual_gross, transaction.actual_product_price, transaction.total_refunded_amount);

    $("#hidded_refundable_amount").val(refundableAmount.toFixed(4));

    $("#amount_to_be_added_to_wallet").html(refundable_amount.toFixed(4));
    $("#messageLabel").html('');

    $this.find("#restore-btn").attr("data-id", transaction.id);

    // Partial Chargeback
    $(".remaining_chargeback_amount").val(refundableAmount.toFixed(4));
    $(".hidden_total_chargeback_amount").val(transaction?.total_refunded_amount);
    $(".hidden_product_price").val(transaction?.actual_product_price);
    $(".hidden_gross_amount").val(transaction?.actual_gross);
    $(".hidden_chargeback_able_amount").val(refundableAmount.toFixed(4));
    $(".chargeback_amount_to_be_added").html(refundable_amount.toFixed(4));
    $('#transaction_amount').val(refundableAmount.toFixed(4))
    let total_chargebacked = parseFloat(totalRefundedAmount(transaction.total_refunded_amount,transaction.actual_product_price, transaction.actual_gross));
    $(".total_chargeback_amount").html(total_chargebacked.toFixed(2) +' '+ decode_utf8(transaction.currency_symbol, decode));
}

function decode_utf8(s, decode) {
    try {
        if (decode){
            return decodeURIComponent(encodeURIComponent(s));
        }
        return s;
    }
    catch(err) {
        return s;
    }
}

function amountToBeRefunded(request_amount, gross, product_price, total_refunded_amount){

    var amount_to_be_refunded = 0;
    if (product_price == (total_refunded_amount + request_amount)){
        amount_to_be_refunded = (gross - total_refunded_amount);
    }else{
        amount_to_be_refunded = request_amount;
    }

    return amount_to_be_refunded;
}

function totalRefundedAmount(total_refunded_amount, product_price, gross) {
    var res = total_refunded_amount;
    if (total_refunded_amount === product_price){
        res = gross;
    }
    return res;
}

function getNameSurnameByFullName(full_name)
{
    name = '';
    surname = '';
    if (full_name) {
        name_array = full_name.split(" ");
        surname = name_array.pop();
        name = name_array.join(" ");
    }

    return {
        'name' : name,
        'surname' : surname
    };
}
