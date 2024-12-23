<script>

	$(document).ready(function(){

        let duration = {{ $mins }};
        let checkIsOtpResent = "{{ $check_is_otp_resend }}";
        let html_otp_message_show = "{{ $html_otp_message_show }}";
        let html_timer_showing_sector_name = "{{ $html_timer_showing_sector_name }}";
        let html_resend_btn_sector_name = "{{ $html_resend_btn_sector_name }}";

        let resend_btn_name = "{{ $resend_btn_name }}";
        
        let html_resend_btn_code_content = $('{!! $html_resend_btn_code_content !!}').append(resend_btn_name);
		
		let is_disable_resend_btn_for_otp_timer = "{{ $is_disable_resend_btn_for_otp_timer }}";
		
        if(is_disable_resend_btn_for_otp_timer){
            $(html_resend_btn_sector_name)
	            .empty();
        }
		
        if(checkIsOtpResent){
            $(html_otp_message_show)
	            .removeClass('d-none');
        }else{
            $(html_otp_message_show)
	            .addClass('d-none');
        }

		duration = parseInt(duration);

        let countdown;
        let timer = duration, minutes, seconds;
        
        countdown = setInterval(function () {
            
            minutes = parseInt(timer / 60, 10);
            seconds = parseInt(timer % 60, 10);

            minutes = minutes < 10 ? "0" + minutes : minutes;
            seconds = seconds < 10 ? "0" + seconds : seconds;

            $(html_timer_showing_sector_name)
	            .html(minutes + ":" + seconds);

            if (--timer < 0) {
                
                clearInterval(countdown);
                $(html_resend_btn_sector_name).empty();
                $(html_resend_btn_sector_name).append(html_resend_btn_code_content);
	            
				{{--$("#submitbtn").html("{{__('Resend')}}");--}}
				{{--$("#otpfrm").attr('action','{{url('resend_otp')}}');--}}
            }
        }, 1000);
	});

</script>