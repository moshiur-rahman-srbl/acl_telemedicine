<script>
    $('.settlementAmount').on('input', function () {
        var classIndex = $(this).index('.settlementAmount');
        var maxVal = parseFloat({{common\integration\Models\Wallet::getMaxValueForDoubleDataType()}});
        checkNumber(this.value, classIndex, maxVal);
    });


    function checkNumber(sNum, index, max) {
        var pattern = /^\d{0,9}(\.\d{0,2})?$/;
        var inputField = $('.settlementAmount').eq(index);
        var errorMessage = $('.error_amount_message').eq(index);

        if (pattern.test(sNum)) {
            if (parseFloat(sNum) > max) {
                inputField.val(max.toFixed(2));
                errorMessage.text("@lang('Automatic Withdrawal Limit Exceeded')").show();
            } else {
                errorMessage.hide();
            }
        } else {
            inputField.val(sNum.toString().substring(0, sNum.length - 1));
            errorMessage.text("@lang('Automatic Withdrawal Limit Exceeded')").show();
        }
    }


</script>