<script>
    function maskCreditCardNumber(credit_card_number, first_part_length = 6, last_part_length = 4, masking_symbol = '*', is_show_space = true) {
        credit_card_number = String(credit_card_number).replace(/\s/g, '');

        let masked_number = credit_card_number;
        const length = credit_card_number.length;

        if(length > (first_part_length + last_part_length)){
            const first_part = credit_card_number.slice(0, first_part_length);
            const last_part = credit_card_number.slice(-last_part_length);
            const num_of_masking_char = length - (first_part_length + last_part_length);
            const masked_middle = masking_symbol.repeat(num_of_masking_char);
            masked_number = first_part + masked_middle + last_part;
        }

        if(is_show_space){
            masked_number = masked_number.replace(/(.{4})/g, '$1 ');
        }

        return masked_number;
    }

    function maskCardHolderName(card_holder_name, masking_symbol = '*') {
        return card_holder_name.replace(/[^ ]/g, masking_symbol);
    }
</script>