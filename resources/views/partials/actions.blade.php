
<a class="text-muted font-16 mr-1 ml-1" data-id="{{$dataid}}" href="{{$dataid ? 'javascript:void(0);' : route($route, $id)}}" onclick="{{$dataid ? 'deleteAction("delete-form-'.$id.'")' : ''}}" @if($tooltip)data-bs-toggle="tooltip" data-placement="top" title data-original-title="{{$tooltip}}" @endif>
    <i class="{{$icon}}"></i>
</a>
@if($dataid)
<form id="delete-form-{{$id}}" action="{{route($route, $id)}}" method="post" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endif
