<script>
    $('document').ready(function () {
        let autoCompleteValue = 'off';

        @if(\common\integration\BrandConfiguration::isPasswordSaveRestrictedToBrowser())
            let browserName = '';
            let nAgt = navigator.userAgent;

            if ((nAgt.indexOf("OPR")) !== -1) {
                browserName = "Opera";
            } else if ((nAgt.indexOf("Edg")) !== -1) {
                browserName = "Microsoft Edge";
            } else if ((nAgt.indexOf("MSIE")) !== -1) {
                browserName = "Microsoft Internet Explorer";
            } else if ((nAgt.indexOf("Chrome")) !== -1) {
                browserName = "Chrome";
            } else if ((nAgt.indexOf("Safari")) !== -1) {
                browserName = "Safari";
            } else if ((nAgt.indexOf("Firefox")) !== -1) {
                browserName = "Firefox";
            }

            //chrome does not support autocomplete=off, it may change on chrome update
            if (browserName === 'Chrome') {
                autoCompleteValue = 'one-time-code';
            }
        @endif

        $(':input').attr('autocomplete', autoCompleteValue);

    });
</script>