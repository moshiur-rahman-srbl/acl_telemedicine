@push('scripts')
    <script>
        $('#phone').on('keyup', function (e) {
            let value = $(this).val()
            if (e.which != 8){
                if(value.length == 1){
                    value = '('+value
                }else if(value.length == 4){
                    value = value+') '
                }else if(value.length == 9 || value.length == 12){
                    value = value+' '
                }
                $(this).val(value)
                let phoneWithCode = $('#phonecode').val()
                phoneWithCode = phoneWithCode.replaceAll(' ', '').replace('(', '').replace(')', '')
                $('#phonecode').val(phoneWithCode)
            }
        })
    </script>
@endpush