
<select name="wallet_type" class="selectpicker form-control custome-padding2">

    @foreach($wallet_type_list as $wallet_type => $wallet_name)
    <option value="{{ $wallet_type }}" {{$search['wallet_type'] == $wallet_type ? 'selected' :
    ''}}>{{__($wallet_name)}}</option>
    @endforeach

</select>
