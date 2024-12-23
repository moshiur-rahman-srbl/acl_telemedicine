@if( \common\integration\BrandConfiguration::call([common\integration\Brand\Configuration\Frontend\FrontendAdmin::class,'isAllowValidationForTcknAndVkn']))
    <script>
        function handleInputValidation(inputElement, errorElement, maxLength, errorMessage) {
            var characterLength = inputElement.value.length;
            if (characterLength > maxLength) {
                $(errorElement).css({
                    display: 'block',
                    color: 'red',
                }).html(errorMessage);
            } else {
                $(errorElement).css({
                    display: 'none',
                });
            }
        }

        // Event listener for TCKN input
        $('#tckn').keyup(function () {
            handleInputValidation(this, '#tckn_error', 11, "{{ __('You can’t add more than 11 characters in this field.') }}");
        });

        // Event listener for VKN input
        $('#vkn').keyup(function () {
            let vkn_limit = 10;
            let message = "{{ __("You can’t add more than 10 characters in this field") }}";

            @if(!empty($merchant->merchant_type) && $merchant->merchant_type == \common\integration\Models\Merchant::INDIVIDUAL_MERCHANT_TYPE)
                vkn_limit = 11;
                message = "{{ __("You can’t add more than 11 characters in this field.") }}";
            @endif

            handleInputValidation(this, '#vkn_error', vkn_limit, message);
        });
    </script>
@endif
