{{--how to use it--}}
{{--<select data-other_reason="sale_other_reason" .... >....</select>--}}
{{--<input id="sale_other_reason" class="d-none" disabled .... />--}}
{{--Note: "data-other_reason" attr of select = "id" of input--}}

<script>
    $("select.check_other_reason").bind("change paste keyup keydown", function() {
        if ('{{\common\integration\BrandConfiguration::isEnabledOtherReason()}}') {
            var _this = $(this),
                _id = _this.attr('data-other_reason'),
                _input = $('input#' + _id);

                if (_this.val() === -1) {
                _input.removeAttr('disabled').removeClass('d-none');
            } else {
                _input.val('').prop('disabled', true).addClass('d-none');
            }
        }
    });

    function checkOtherReason (reasonSelector) {
        return validateOtherReason(reasonSelector);
    }

    function resetReasonValue (reasonSelector) {
        return validateOtherReason(reasonSelector, false);
    }

    function validateOtherReason (reasonSelector, isBool=true) {
        var response = (isBool === true) ? isBool : reasonSelector.val();
        if ('{{\common\integration\BrandConfiguration::isEnabledOtherReason()}}') {
            var _id = reasonSelector.attr('data-other_reason'),
                _input = $('input#' + _id),
                _val = _input.val().trim(),
                _cond = (isBool === true) ? (_val.length === 0) : (_val.length > 0);

            if (reasonSelector.val() === -1 && _cond) {
                response = (isBool === true) ? !isBool : _val;
            }
        }
        return response;
    }
</script>
