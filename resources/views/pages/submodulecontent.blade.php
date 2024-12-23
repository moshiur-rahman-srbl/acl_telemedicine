@if(!empty($submodules))
    <option value="" selected disabled>{{__('Please select')}}</option>
    @foreach($submodules as $submodule)
        <option value="{{$submodule->id}}">{{__($submodule->name)}}</option>
    @endforeach
@endif