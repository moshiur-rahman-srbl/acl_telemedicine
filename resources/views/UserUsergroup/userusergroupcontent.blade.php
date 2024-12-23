@php
    $checkeduser = array();
    if(!empty($userusergroups)) {
        foreach($userusergroups as $userusergroup) {
            $checkeduser[$userusergroup->user_id] = $userusergroup->user_id;
        }
    }
@endphp
@if(!empty($users))
    <div class="col p-0 rounded border border-top-0 border-right-0 border-left-0 border-primary" style="margin: 0 15px; height: inherit;">
        <ul class="list-group list-group-bordered" id="aulist">
            <li class="list-group-item active flexbox"><label class="m-0 p-0">{{__('Available Users')}}</label></li>
            @foreach($users as $user)
                @if(!(in_array($user->id, $checkeduser)))
                    <li class="list-group-item flexbox notsel" data="{{$user->id}}">{{$user->name}}</li>
                @endif
            @endforeach
        </ul>
    </div>
    <div class="col p-0 rounded border border-top-0 border-right-0 border-left-0 border-primary" style="margin: 0 15px;">
        <ul class="list-group list-group-bordered" id="sulist">
            <li class="list-group-item active flexbox"><label class="m-0 p-0">{{__('Selected Users')}}</label></li>
            @foreach($users as $user)
                @if(in_array($user->id, $checkeduser))
                    <li class="list-group-item flexbox seltd" data="{{$user->id}}"><input type="hidden" name="selected_users[]" value="{{$user->id}}" />{{$user->name}}</li>
                @endif
            @endforeach
        </ul>
    </div>
@endif