<script src="{{asset('intlTelInput/build/js/intlTelInput.js?1638200991544')}}"></script>
<!--
    #### important example (how to use this) ####

    <label>Phone Number</label>
    <input type="text" name="" value="" id="ID_HERE" class="number-only telNoSelector"/>
    <input type="hidden" name="" value="" id="OTHER_ID" class="telNoValue"/>

    **** must have specific id and these class ****
 -->
<script>
    $(".telNoSelector").each(function() {
        initPhoneNumber($(this));
    });

    $(".telNoSelector").on("input", function (event) {
        event.preventDefault();
        setPhoneNumber($(this));
    });

    $(".iti").addClass("d-block");

    function initPhoneNumber (thisElement) {
        var input = document.querySelector("#" + thisElement.attr("id")),
            options = {
                initialCountry: 'auto',
                placeholderNumberType: 'MOBILE',
                separateDialCode: true,
                preferredCountries:["us", "gb", "tr"],
                formatOnDisplay:false,
                geoIpLookup:function(callback) {
                    $.get('https://ipinfo.io', function() {}, "jsonp").always(function(resp) {
                        var countryCode = (resp && resp.country) ? resp.country : "tr";
                        callback(countryCode);
                    });
                },
                utilsScript: "{{ asset('intlTelInput/build/js/utils.js?1638200991544')}}",
                customPlaceholder:function(selectedCountryPlaceholder, selectedCountryData) {
                    if(input.value) {
                        thisElement.parent().next(".telNoValue").val("+" + selectedCountryData.dialCode + input.value);
                    }
                    return selectedCountryPlaceholder.replace(/[0-9]/g, '0');
                }
            }
        window.intlTelInput(input, options);
    }

    function setPhoneNumber (thisElement) {
        var phoneNumber = thisElement.val(),
            countryCode = thisElement.prev('div').find(".iti__selected-dial-code").html();

        if(thisElement.parent().next('.telNoValue').hasClass('telNoValue')){

            thisElement.parent().next(".telNoValue").val(countryCode + phoneNumber);

        }else{

            thisElement.parent().parent().next(".telNoValue").val(countryCode + phoneNumber);
            
        }            

    }
</script>
