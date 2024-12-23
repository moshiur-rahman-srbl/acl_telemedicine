<style>
    .border-remove{
        border: none;
    }
    .border-right-only{
        border-right: 1px solid #e8e8e8;
    }
</style>
@if(!empty($modulesInfo))
<table class="table table-bordered" id="mainTable">
    <thead>
        <tr>
            <th>{{__('ID')}}</th>
            <th>{{__('Module')}}</th>
            <th>{{__('Sub Module')}}</th>
            <th>{{__('Pages')}}</th>
            <th>{{__('Actions')}}</th>
        </tr>
    </thead>
    <tbody>
        @foreach($modulesInfo as $moduleInfo)

            @if (!in_array($moduleInfo->id, $module_ids_array))
                @continue;
            @endif

        <tr class="section_{{$moduleInfo->id}} section">
            <td style="vertical-align:top">{{$moduleInfo->id}}</td>
            <td style="vertical-align:top">
                <label class="checkbox checkbox-ebony">
                    <input name="selectedModuleIds[]" id="module_{{$moduleInfo->id}}" value="{{$moduleInfo->id}}" data-type="module"
                           onclick="setAllChecked(this)" type="checkbox" class="bulk-action" {{isset($selectedModuleList[$moduleInfo->id]) && $selectedModuleList[$moduleInfo->id] == 1 ? "checked":"" }}>
                    <span class="input-span"></span>{{__($moduleInfo->name)}}
                </label>
            </td>
            <td colspan="3">
                @if(!empty($moduleInfo->submodules))
                <table class="table" style="width:100%;">
                    @foreach($moduleInfo->submodules as $submodules)

                        @if (!in_array($submodules->id, $usertype_submodule_ids_array))
                            @continue;
                        @endif

                    <tr>
                        <td style="width:40%;" class="border-remove">
                            <label class="checkbox checkbox-ebony">
                                <input name="selectedSubModuleIds[]" value="{{$submodules->id}}" data-type="submodule" data-module="{{$moduleInfo->id}}"
                                       type="checkbox" onclick="setAllChecked(this)"
                                       class="bulk-action module_check_{{$moduleInfo->id}} count_submodule_{{$moduleInfo->id}}" id="submodule_{{$submodules->id}}" {{$submoduleSelectedList[$submodules->id]==1 ? "checked":""}}>
                                <span class="input-span"></span>{{__($submodules['display_name'])}}
                            </label>
                        </td>
                        <td style="width:50%;" class="border-remove">
                            @if(!empty($submodules->pages))
                            <table class="table" style="width:100%;">
                                @foreach($submodules->pages as $pages)
                                @php
                                    $checked = "";
                                    if(in_array($pages->id, $selectedpages)){
                                        $checked = "checked";
                                    }
                                @endphp

                                <tr>
                                    <td style="width:50%;" class="border-remove">
                                        {{__($pages['name'])}}
                                    </td>
                                    <td style="width:50%;" class="border-remove">
                                        <label class="checkbox checkbox-ebony">
                                            <input name="selectedpageIds[]" value="{{$pages->id}}" type="checkbox" onclick="pageChecked(this)" data-module="{{$moduleInfo->id}}" data-submodule="{{$submodules->id}}"
                                           class="bulk-action module_check_{{$moduleInfo->id}} submodule_check_{{$submodules->id}} count_pages_{{$submodules->id}}" {{$checked}}>
                                        <span class="input-span" id="{{$pages->id}}"></span>
                                    </label>
                                    </td>
                                </tr>
                                @endforeach
                            </table>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </table>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

