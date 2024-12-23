
<script>
    $('doucment').ready(function(){

        var counter = 1;
        var last_page = -1;
        var panel_id = $('#notification_icon').attr('data-panel_id');

        $('#notification_scroller').scroll(function(){

            if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 10){
                counter = counter + 1;
                if(counter <= last_page || last_page == -1){
                    getNotificationData(counter);
                }

            }


        });

        // $('.notification_icon').click(function(){
        //     getNotificationData(1);
        // });

        function getNotificationData(counter){
            // Need loader
            var total_notification_number = `{{$unread_notifications->total ?? $unread_notifications}}` ;
            $.ajax({
                'url': "{{ route('home')  }}",
                'method' : 'GET',
                'cache': false,
                'data':{
                    'page':counter,
                    'per_page': 10,
                    'action': 'get-notifications-data',
                    'panel_id' : panel_id
                },
                beforeSend: function() {
                    $('#notification_number').html('<i class="fa fa-spin fa-spinner"></i>');
                },
                complete: function() {
                    $('#notification_number').html(total_notification_number);
                },
                success:function(response){

                    if(response){
                        last_page = response.last_page;
                        $('#notification_scroller span:last, .notification_admin:last').after(response.view_page);
                    }
                },
                error:function(response){
                    console.log(response);
                },
            });
        }

    });

</script>
