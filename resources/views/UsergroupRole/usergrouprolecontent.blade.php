@php
    $checkedrole = array();
    if(!empty($usergrouproles)) {
        foreach($usergrouproles as $usergrouprole) {
            $checkedrole[$usergrouprole->role_id] = $usergrouprole->role_id;
        }
    }
@endphp
@if(!empty($roles))
    <div class="col p-0 rounded border border-top-0 border-right-0 border-left-0 border-primary" style="margin: 0 15px; height: inherit;">
        <ul class="list-group list-group-bordered" id="aulist">
            <li class="list-group-item active flexbox"><label class="m-0 p-0">{{__('Available Roles')}}</label></li>
            @foreach($roles as $role)
                @if(!(in_array($role->id, $checkedrole)))
                    <li class="list-group-item flexbox notsel" data="{{$role->id}}">{{$role->title}}</li>
                @endif
            @endforeach
        </ul>
    </div>
    <div class="col p-0 rounded border border-top-0 border-right-0 border-left-0 border-primary" style="margin: 0 15px;">
        <ul class="list-group list-group-bordered" id="sulist">
            <li class="list-group-item active flexbox"><label class="m-0 p-0">{{__('Selected Roles')}}</label></li>
            @foreach($roles as $role)
                @if(in_array($role->id, $checkedrole))
                    <li class="list-group-item flexbox seltd" data="{{$role->id}}"><input type="hidden" name="selected_roles[]" value="{{$role->id}}" />{{$role->title}}</li>
                @endif
            @endforeach
        </ul>
    </div>

    {{--<table class="table table-bordered">--}}
        {{--<tr>--}}
            {{--<td><strong>{{__($groupname)}}</strong></td>--}}
            {{--<td>--}}
                {{--<table class="table" style="width:100%;">--}}
                    {{--@foreach($roles as $role)--}}
                    {{--<tr>--}}
                        {{--<td>--}}
                            {{--<label class="checkbox checkbox-ebony">--}}
                                {{--<input type="checkbox" name="role_ids[]" id="" value="{{$role->id}}" {{in_array($role->id, $checkedrole) ? 'checked' : ''}}>--}}
                                {{--<span class="input-span"></span>{{$role->title}}</label>--}}
                        {{--</td>--}}
                    {{--</tr>--}}
                    {{--@endforeach--}}
                {{--</table>--}}
            {{--</td>--}}
        {{--</tr>--}}
    {{--</table>--}}
@endif