<script>
    const resendWelcomeMail = (id, url) => {
        var isAlreadySent = $('#btn-resend-welcome-mail-' + id).attr("data-already-sent-mail");

        if(isAlreadySent == 1) {
            alertify.alert("{{__('Email already sent.')}}");
        } else {
            $.ajax({
                url: url,
                type: 'GET',
                data: {action: 'resend-welcome-mail', user_id: id},
                beforeSend: function () {
                    $('#btn-resend-welcome-mail-' + id).find('i').removeClass('ti-email').addClass('fa fa-spinner fa-spin')
                    $('#btn-resend-welcome-mail-' + id).attr('disabled', true);
                },
                success: function (response) {
                    if (response.data.status) {
                        alertify.alert(response.data.message);
                    } else {
                        alertify.alert(response.data.message);
                    }
                    location.reload();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alertify.alert("{{__('Something went wrong, try again')}}");
                }
            });
        }
    };
</script>