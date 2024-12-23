@foreach($reasons as $key => $reason)
    <option class="reject_reason" value="{{($reason->id == \App\Models\Reason::REASON_OTHER_ID) ? $reason->id : $reason->title}}">{{__($reason->title_tr)}}</option>
@endforeach
