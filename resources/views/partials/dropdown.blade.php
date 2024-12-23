@php
    $selectChecker = \common\integration\GlobalMerchant::selectedMailReceiverEmail($merchantAuthEmail, $checker)
@endphp
<div class="form-group mb-4">
    <label>{{$label}}</label>
    <div class="input-group-icon input-group-icon-right">
        <select name="{{$name}}" class="selectpicker form-control custome-padding validateClass"
                data-title="{{$label}}"
                data-actions-box="true"
                multiple>
            @foreach($merchantUsersEmailList as $key=>$value)
                <option value="{{$value}}" {{in_array($value, $selectChecker) ? 'selected' : ''}}>{{$value == $merchant_auth_email ? $auth_email_text : $value}}</option>
            @endforeach
        </select>
    </div>
</div>
