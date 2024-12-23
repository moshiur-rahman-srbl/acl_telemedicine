<a href="{{config('app.app_frontend_url')}}/deposits" class="noti_list checkedNotification">
    <span><img src="{{Storage::url("assets/images/icons/menu/6-MoneyTransfer.svg")}}" alt="icon"></span>
    Your information has been approved!You can now request money from other users, deposit money into your account and spend as much as the balance you have in your account. To transfer money and withdraw your balance to your bank account, you must make at least one successful deposit.
    <br/>
    <small data-created_at="{{$data['created_at']}}">Welcome to {{ config('brand.name') }}!</small>
</a>
