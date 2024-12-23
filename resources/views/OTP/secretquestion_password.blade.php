@php
    $show_modal = false;
    $user_details = request()->session()->get('login_info');
    if(!empty($user_details) && isset($user_details['id']) && \common\integration\GlobalFunction::checkOtpMaxAttempt($user_details['id'])){
        $show_modal = true;
    }
    $secrect_questions = \common\integration\BrandConfiguration::QUESTION_LIST();
@endphp

<div class="modal fade" id="secret_question_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
        <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">{{ __(\common\integration\BrandConfiguration::WRONG_OTP_ATTEMPT_HEADER) }}</h5>
            <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"> -->
            </button>
        </div>
        <div class="modal-body">
            <span class="text-danger" id="error_message"></span>
            <div class="form-group">
                <label class="text">{{__("Secret Question")}}</label>
                <div class="input-group-icon input-group-icon-right">
                    <select class="form-control" name="secrect_question_id" id="secrect_question_id">
                        <option value="">{{ __('Please select') }}</option>
                        @foreach ($secrect_questions as  $key => $val)
                            <option value="{{ $key }}">{{ __($val) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="text">{{__("Answer")}}</label>
                <div
                    class="input-group-icon input-group-icon-right">
                    <input required name="answer"
                            class="form-control rounded" type="text"
                            placeholder="{{__("Answer")}}"
                            value="" autocomplete="off" id="answer">
                </div>
            </div>

            <div class="form-group">
                <label class="text">{{__("Password")}}</label>
                <div
                    class="input-group-icon input-group-icon-right">
                    <input required name="password"
                            class="form-control rounded" type="password"
                            placeholder="{{__("Password")}}"
                            value="" autocomplete="off" id="password">
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button> -->
            <button type="button" id="submit" class="btn btn-primary">{{ __('Submit') }}</button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        $('doucment').ready(function () {

            var show_modal = "{{ $show_modal }}";
            if(show_modal){
                $('#secret_question_modal').modal({
                    backdrop: 'static',
                    keyboard: true,
                    show: true
                });
            }
            $('#submit').bind('click',function(){
                var $this = $(this);
                var btnTxt = $(this).text();
                var secrect_question_id = $('#secrect_question_id').val();
                var answer = $('#answer').val();
                var password = $('#password').val();

                if(!secrect_question_id || !answer || !password){
                    $('#error_message').text('{{ __(\common\integration\BrandConfiguration::REQUIRED_FIELD_ALL_FIELDS) }}');
                    return false;
                }else{
                    $('#error_message').text('');
                    $(this).html('<i class="fa fa-spinner fa-spin"></i>');
                }
                var data = {
                    'type' : 'secret_question_password_checker',
                    'secrect_question_id': secrect_question_id,
                    'answer': answer,
                    'password': password,
                    '_token': '{{ csrf_token() }}'
                };

                $.ajax({
                    url: '{{ route('verifyOTP') }}',
                    method: 'POST',
                    dataType: 'json',
                    data: data,
                    success:function(resp){
                        if(resp.status){
                            $('#secret_question_modal').modal('hide');
                           $('#resend_otp').click();
                        }else{

                            $this.html(btnTxt);
                            $('#error_message').text('{{ __(\common\integration\BrandConfiguration::INFO_NOT_MATCHED) }}');
                        }
                    },
                    error:function(resp){

                    },
                });
            });
        });
    </script>
@endpush

