@foreach ($users as $user)
    <option value="{{$user->id}}">{{$user->name}} ({{$user->id}})</option>
@endforeach
