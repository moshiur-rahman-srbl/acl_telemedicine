{{-- <div class="media-img">
        <img class="img-circle" src="./assets/img/merchant_imgs/users/u7.jpg" alt="image" />
</div> --}}
<div class="media-body">
    <div class="media-heading"></div>
    @if(!empty($notification->data['subject']))
    <div class="font-13 text-muted">{{$notification->data['subject'].(!empty($notification->data['data']['amount']) ? " for Amount ".$notification->data['data']['amount'] : "")}}</div>
    @endif
</div>