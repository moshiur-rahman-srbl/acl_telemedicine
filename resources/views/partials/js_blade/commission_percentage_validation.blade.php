    <script>
    function maxCommissionPercentageValidation(element, class_name) {

        $(element).on("click", function (e) {
            const maxCommissionPercentage = {{ \common\integration\GlobalCommission::MAX_COMMISSION_PERCENTAGE }};
            let shouldAlert = false;

            $(class_name).each(function () {
                let commission = parseFloat($(this).val());

                if (commission > maxCommissionPercentage) {
                    $(this).addClass("border-danger");
                    shouldAlert = true;
                } else {
                    $(this).removeClass("border-danger");
                }

            });

            if (shouldAlert) {
                e.preventDefault();
                $msg = "{{ __('commission percentage can not be greater than :number', [
                                'number' => \common\integration\GlobalCommission::MAX_COMMISSION_PERCENTAGE
                         ]) }}"
                alertify.alert($msg)
            } else {
                $("#edit_all_pos_form").submit();
                $(this).html('<i class="fa fa-refresh fa-spin"></i>');
            }

        });

    }
</script>
