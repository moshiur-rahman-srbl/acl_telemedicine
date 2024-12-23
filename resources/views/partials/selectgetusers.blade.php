<script>

    let  get_only_wallet_type_users = false;
    if ($("#get_only_wallet_type_users").length > 0){
        get_only_wallet_type_users = $("#get_only_wallet_type_users").val();
    }

    $('.select2-get-user').select2({
        ajax: {
            url: routeurl,
            dataType: 'json',
            delay: 250,
            method: "GET",
            data: function (params) {
                var query = {
                    "search": params.term,
                    'get_only_wallet_type_users': get_only_wallet_type_users
                }

                // Query parameters will be ?search=[term]&type=public
                return query;
            },
            processResults: function (data) {
                var USER_TYPES = JSON.parse('<?php echo json_encode( \App\User::USER_TYPES, true) ?>');
                    return {
                    results: $.map(data, function (value, key) {
                        return {
                            id: value.id,
                            text: value.name + ' (' + value.email + ')'+ ' (' + USER_TYPES[value.user_type] + ')'
                        };
                    })
                };
            },
            cache: true
        }

    });
</script>
