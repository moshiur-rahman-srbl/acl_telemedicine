<script>
    $(document).on('input paste keyup keydown', '.customized-double-number-format', function() {
        let input_value = this.value,
            digit_before_decimal = this.getAttribute('data-digitsBeforeDecimal'),
            digit_after_decimal = this.getAttribute('data-digitsAfterDecimal');

        if (digit_before_decimal == 'undefined' || digit_before_decimal == '' || digit_before_decimal == null) {
            digit_before_decimal = 0;
        }
        digit_before_decimal = parseInt(digit_before_decimal);

        if (digit_after_decimal == 'undefined' || digit_after_decimal == '' || digit_after_decimal == null) {
            digit_after_decimal = 2;
        }
        digit_after_decimal = parseInt(digit_after_decimal) + 1;
        input_value = input_value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');

        if (digit_before_decimal > 0 && input_value.indexOf(".") < 0) {
            input_value = input_value.substr(0, digit_before_decimal);
        }
        input_value = (input_value.indexOf(".") >= 0)
            ? (input_value.substr(0, input_value.indexOf(".")) + input_value.substr(input_value.indexOf("."), digit_after_decimal))
            : input_value;

        if (digit_before_decimal > 0 && input_value.indexOf(".") > digit_before_decimal) {
            input_value = input_value.substr(0, digit_before_decimal) + input_value.substr(input_value.indexOf("."), digit_after_decimal);
        }
        this.value = input_value;
    });
</script>