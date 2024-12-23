<script>
    $(document).ready(function () {

        let binIds = [];

        function checkBinMappingContainerEmpty() {
            if ($.trim($('#bin-mapping-container').html()) === '') {
                $('#confirm-btn').attr('disabled', 'disabled');
            } else {
                $('#confirm-btn').removeAttr('disabled');
            }
        }

        // add new row
        $('#add-btn').click(function () {
            const newRow = $('#bin-mapping-template .bin-mapping-row').clone();
            $('#bin-mapping-container').append(newRow);
            checkBinMappingContainerEmpty();
        });

        // delete row
        $('#bin-mapping-container').on('click', '.delete-btn', function () {
            const button = $(this);
            const closestRow = button.closest('.bin-mapping-row');

            let binId = button.attr("data-bin_id");

            if (binId) {
                let token = '{{ csrf_token() }}';
                alertify.confirm("Are you sure you want to delete?", function (e) {
                    if (e) {
                        $.ajax({
                            type: "post",
                            url: `{{route(Config::get('constants.defines.APP_MANAGEMENT_PARAMETER_SETTINGS_INDEX'))}}`,
                            data: {id: binId, _token: token, action: "bin_mapping_delete"},
                            beforeSend: function () {
                                button.html('<i class="fa fa-refresh fa-spin"></i>');
                                button.prop('disabled', true);
                            },
                            success: function () {
                                window.location.href = "";
                            },
                            error: function (err) {
                                console.log($($(err.responseText)[1]).text())
                            }
                        });
                        return true;
                    } else {
                        return false;
                    }
                });
            } else {
                closestRow.remove();
            }
            checkBinMappingContainerEmpty();
        });

        checkBinMappingContainerEmpty();

        $('#bin-mapping-container').on('change', '.bin-mapping-row select', function() {
            let binId = $(this).data('bin_id');

            if (binIds.indexOf(binId) === -1) {
                binIds.push(binId);
            }
            $('#binIds').val(binIds)
        });

        function validateForm() {
            let isValid = true;
            $('#bin-mapping-container .bin-mapping-row select').each(function () {
                if ($(this).val() === "") {
                    $(this).addClass('input-error');
                    isValid = false;
                } else {
                    $(this).removeClass('input-error');
                }
            });
            return isValid;
        }

        $('#confirm-btn').on('click', function() {
            if (validateForm()) {
                $('#binRedirectionForm').submit();
            }
        });

        $(document).on('change', '.from_bank_code, .card_type, .to_bank_code', function(){
            let selectElement = $(this);
            let parentDiv = selectElement.closest('.bin-mapping-row');
            let fromBankCode = parentDiv.find('select.from_bank_code').val() ?? '';
            let cardType = parentDiv.find('select.card_type').val() ?? '';
            let toBankCode = parentDiv.find('select.to_bank_code').val() ?? '';

            if(fromBankCode !== '' && toBankCode !== ''){
                if(fromBankCode === toBankCode){
                    alertify.alert("Both bank could not be same");
                    selectElement.val('');
                }
            }

            if(fromBankCode !== '' && cardType !== ''){
                let [isDuplicate, alertMessage] = checkDuplicatePair(fromBankCode, cardType);
                if(isDuplicate){
                    alertify.alert(alertMessage);
                    selectElement.val('');
                }
            }
        })

        function checkDuplicatePair(fromBankCode, cardType) {
            let selectedPairs = [];
            let isDuplicate = false;
            let alertMessage = '';

            $('.bin-mapping-row').each(function() {
                let thisFromBankCode = $(this).find('.from_bank_code').val();
                let thisCardType = $(this).find('.card_type').val();
                let pair = thisFromBankCode + '-' + thisCardType;

                if (thisFromBankCode && thisCardType) {
                    if (selectedPairs.includes(pair)) {
                        isDuplicate = true;
                        alertMessage = "This bank and card type are already selected.";
                    } else if((thisFromBankCode === fromBankCode) && ((thisCardType !== '0' && cardType === '0') || (thisCardType === '0' && cardType !== '0'))){
                        isDuplicate = true;
                        alertMessage = "You already selected all card type or other card type for this bank";
                    } else {
                        selectedPairs.push(pair);
                    }
                }
            });

            return [isDuplicate, alertMessage];
        }

    });

</script>
