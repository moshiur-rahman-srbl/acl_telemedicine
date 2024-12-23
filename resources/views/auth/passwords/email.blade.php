@extends('layouts.app')
<style>
    .sa {
        font-size: 13px;
        color: #219351;
        background-color: #97e6b8;
    }
    .da {
        font-size: 13px;
        color: #a6372b;
        background-color: #f3a69e;
    }

    .security_image{
        cursor: pointer;
    }
    .security_image input[type="radio"]:checked+img{
        border: 5px solid #131313;
    }
</style>
@section('content')

<?php
    $isCreate = false;
    $formId = 'forgetPasswordfrm';
    $titleTxt = __('RESET PASSWORD');
    $btnTxt = __('Send Password Reset Link');
    $btnId = 'submit_btn';
    if(isset($is_create) && $is_create) {
        $isCreate = true;
        $formId = '';
        $titleTxt = __('Create New Password');
        $btnTxt = __('Send Password Create Link');
        $btnId = __('create_submit_btn');
    }
?>

    <form class="ibox-body" id="{{$formId}}" action="" method="POST">
        @csrf
        <input type="hidden" name="is_create" value="{{$isCreate}}" >
        <h4 class="font-strong text-center mb-5 text-uppercase">{{__($titleTxt)}}</h4>
        @include('partials.flash')

        <?php
            $verrors = session()->has('verrors') ? session()->get('verrors') : [];

            if(empty(old('email'))){
                session()->forget('verrors');
                session()->save();
            }

        ?>

        <div class="form-group mb-4">
            <input class="form-control form-control-line" type="email" autocomplete="off" name="email" value = "{{ old('email') }}" placeholder="{{__('Email')}}" required>
            <small class="text-danger font-italic">{{$verrors['email'] ?? ''}}</small>
            <small class="text-danger font-italic d-block">{{$verrors['decoded_email'] ?? ''}}</small>
        </div>

        @if(\common\integration\BrandConfiguration::secretQuestionResetPassword())
        @if (\common\integration\BrandConfiguration::securityImageForResetPasssword())

            @include('security_images.security', [
                'security_images' => (new \App\Models\SecurityImage())->getSecurityImagesWithBrandCodeAndStatus(\App\Models\SecurityImage::STATUS_ACTIVE)
            ])

        @else
        <div class="form-group mb-4">
            <select class="form-control form-control-line" name="question_one" required>
                <option value="" selected>{{__("Select Secret Question")}}</option>
                @foreach(\common\integration\BrandConfiguration::QUESTION_LIST() as $key=> $question)
                    <option value="{{$key}}">{{__($question)}}</option>
                @endforeach
            </select>
            <small class="text-danger font-italic">{{$verrors['question_one'] ?? ''}}</small>

        </div>
        <div class="form-group mb-4">
            <input class="form-control form-control-line" type="text" autocomplete="off" name="answer_one" placeholder="{{__('Answer')}}" required>
            <small class="text-danger font-italic">{{$verrors['answer_one'] ?? ''}}</small>
        </div>
        @endif
        @endif

        @if(\common\integration\BrandConfiguration::call([\common\integration\Brand\Configuration\All\Mix::class,
        'allowSecurityImageOnCreateUserAdminMerchant']))

            @include('security_images.security', [
			    'security_images' => (new \App\Models\SecurityImage())->getSecurityImagesWithBrandCodeAndStatus(\App\Models\SecurityImage::STATUS_ACTIVE)
            ])

        @endif

        <div class="text-center mb-4">
            <button type="submit" class="btn btn-primary btn-rounded btn-block selectLoader" id="{{$btnId}}">{{__($btnTxt)}}</button>
        </div>
    </form>

<div class="modal fade" id="otp_confirmation" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{ __('OTP Confirmation') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">

                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="otp" class="col-form-label">{{__("OTP")}}:</label>
                    <input type="text" name="otp" class="form-control" id="otp">
                </div>
                <span class="otp-invalid-feedback"></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__("Close")}}</button>
                <button type="button" class="btn btn-primary" id="confirmed">{{ __("Confirmed") }}</button>
            </div>
        </div>
    </div>
</div>

@endsection
