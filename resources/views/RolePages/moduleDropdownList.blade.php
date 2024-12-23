<div class="form-group mb-4">
    <label>{{__('Module Name')}}</label>
    <select class="form-control" name="module_id" onchange="filterModule(this)" id="module-dropdown">
        <option value="0">{{__("Please select")}}</option>
        @if(!empty($modulesArray))
        @foreach($modulesArray as $module_id => $module_name)
        <option value="{{$module_id}}" {{(old('module_id') == $module_id) ? "selected":"" }}>{{__($module_name)}}</option>
        @endforeach
        @endif
    </select>
</div>
