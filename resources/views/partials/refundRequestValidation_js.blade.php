

<script>


    async  function validateRefundRequest(trxid,refundAmount, csrf, url, $this , backup_table_type = '{{ \common\integration\Replication\ReplicationRepository::TABLE_TYPE_ORIGINAL }}'){
        var result = '';
        var action = "CHECK_NEGATIVE";
        var confirm_data = JSON.stringify({id: trxid,
            trxid: trxid,
            action:action,
            type:'',
            backup_table_type: backup_table_type,
            refund_amount : refundAmount,
            _token: csrf});
        await $.ajax({
            type: "post",
            url: url,
            contentType: "application/json",
            dataType: "json",
            data: confirm_data,
            success: function (response) {
                $(this).prop('disabled', false);
                console.log(response);
                result = response;
            }
        });


        // $this.html('');

        return result;
    }

    function messageForRefundRequestValidation(statusCode, $confirmBtn, type) {
        var message = '';
        var headerMessage = '';

        if (type == 'chargeback'){
            headerMessage = '{{__("Are you sure to charge back?")}}';
        } else {
            headerMessage = '{{__("Are you sure to refund?")}}';
        }
        $confirmBtn.show();
        if (statusCode === 1) {
            message = '{{__('Total refund amount should not cross net amount')}}';
            $confirmBtn.hide();
        }else if(statusCode === 2){
            message = '{{__('Transaction Not Found')}}';
            $confirmBtn.hide();
        }else if (statusCode === 3){
            message = '{{__("By completing this action the current balance of the merchant will drop down to negative. Are you sure?")}}"';

        }else if(statusCode === 4){
            if (type == 'chargeback') {
                message = '{{__('Transaction can not be chargebacked')}}';
            }else {
                message = '{{__('Transaction can not be refunded')}}';
            }
            $confirmBtn.hide();

        }else if(statusCode === 30){
            message = '{{__('Double click attempts')}}';
            $confirmBtn.hide();
        } else if(statusCode == '{{\common\integration\ApiService::API_SERVICE_REFUND_CAN_NOT_PROGRESS_FOR_PHYSICAL_POS_TRANSACTION_OLD}}') {
            message = '{{__(\common\integration\ApiService::API_SERVICE_STATUS_MESSAGE[\common\integration\ApiService::API_SERVICE_REFUND_CAN_NOT_PROGRESS_FOR_PHYSICAL_POS_TRANSACTION_OLD])}}';
            $confirmBtn.hide();
        }
        message = headerMessage + "</br>" + message;

        return message;

    }
</script>
