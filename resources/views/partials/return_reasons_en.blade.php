@foreach($returnReasons as $key => $reason)
    <option class="return_reason" value="{{($reason->id == \App\Models\Reason::REASON_OTHER_ID) ? $reason->id : $reason->title}}">{{__($reason->title)}}</option>
@endforeach
