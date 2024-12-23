@include('partials.css_blade.intlTelInput')
@include('partials.js_blade.intlTelInput')

<script>
    phoneCountryCodeInit();

    $("#phone").on("input",function (event) {
        event.preventDefault();
        var phoneNumber = $(this).val();
        var countryCode = $(this).prev("div").find(".iti__selected-dial-code").html();
        newPhoneNumber = countryCode+phoneNumber;
        $(this).parent("div").next("input").val(newPhoneNumber);
    });

    function phoneCountryCodeInit() {
        addCountryCode('phone');
    }

    function addCountryCode(elementId) {
        ///FOR country code
        var input = document.querySelector("#"+elementId);
        var country = intlTelInput(input, {
            initialCountry: "auto", geoIpLookup: function (callback) {
                $.get('https://ipinfo.io', function () {
                }, "jsonp").always(function (resp) {
                    var countryCode = (resp && resp.country) ? resp.country : "";
                    callback(countryCode);
                });
            },
            separateDialCode: true,
            preferredCountries:["us", "gb", "tr"]
        });

        window.country;
        input.addEventListener("countrychange", function (i, value) {
            var countrydata = country.getSelectedCountryData();

            // $("#"+elementId).val("+" + countrydata.dialCode);

            $("#"+elementId).focus();
        });

        // $(".intl-tel-input").addClass("d-block");
        $(".iti").addClass("d-block");
    }

    // var countryCode = "";
    // ///FOR country code
    // var input = document.querySelector("#phone");
    // var country = intlTelInput(input, {
    //     initialCountry: "auto", geoIpLookup: function (callback) {
    //         $.get('https://ipinfo.io', function () {
    //         }, "jsonp").always(function (resp) {
    //             countryCode = (resp && resp.country) ? resp.country : "";
    //             callback(countryCode);
    //         });
    //     },
    //     separateDialCode: true
    // });
    //
    //
    // window.country;
    //
    // input.addEventListener("countrychange", function (i, value) {
    //     var countrydata = country.getSelectedCountryData();
    //     $('input[name=country_code]').val(countrydata.dialCode);
    //
    //     var countryCode = $('#phone').intlTelInput("getDialCode");
    //
    //     $('#phone').val("+" + countrydata.dialCode);
    //
    //     $('#phone').focus();
    // });
    //
    // // $(".intl-tel-input").addClass("d-block");
    // $(".iti").addClass("d-block");
    //
    // $("#phone").on("change",function (event) {
    //     event.preventDefault();
    //     var phoneNumber = $("#phone").val();
    //     var countryCode = $(".iti__selected-dial-code").html();
    //     newPhoneNumber = countryCode+phoneNumber;
    //     $("#phonecode").val(newPhoneNumber);
    // });

</script>
