@extends('layouts.adminca')
@section('content')
@include('partials.page_heading')

<div class="page-content fade-in-up">
    @include('partials.flash')
    <div class="ibox">
            <div class="row pt-3">
                <div class="col-sm-12 text-right ">
                    @if(\common\integration\BrandConfiguration::allowRolePageAllRoleExport())

                            @if(Auth::user()->hasPermissionOnAction(Config::get('constants.defines.APP_ROLE_PAGE_ASSOCIATION_EXPORT')))
                                <form
                                    action="{{ route(Config::get('constants.defines.APP_ROLE_PAGE_ASSOCIATION_EXPORT')) }}"
                                    method="get">
                                    <button type="submit"
                                            class="btn btn-outline-primary btn-fix btn-rounded ml-md-2 mb-2 mb-md-auto mr-3">{{ __('Export All') }}</button>

                                </form>
                            @endif

                    @endif
                </div>
            </div>
        <form action="{{route(Config::get('constants.defines.APP_ROLE_PAGE_ASSOCIATION'))}}" method="post">
          @csrf
        <div class="ibox-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-4">
                    <label>{{__('Role Name')}}</label>
                    <select class="form-control" name="role_id" id="company-role">
                        <option value="">{{__("Please select")}}</option>
                        @if(!empty($roleList))
                            @foreach($roleList as $role_id => $role)
                            <option value="{{$role_id}}" {{(old('role_id') == $role_id) ? "selected":"" }}>{{$role}}</option>
                            @endforeach
                        @endif
                    </select>
                    <img id="ajax-loader" style="z-index:10000;display:none" class="pull-right mt-2" src="{{asset('ajax-loader.gif')}}" alt="loader"/>
                </div>
                </div>
                <div class="col-md-6" id="module-dropdown">

                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <div id="role-content">

                        </div>
                    </div>
                </div>
            </div>
            @if(\common\integration\BrandConfiguration::allowRolePageExport())
                <div class="form-group col-md-4" id="export_btn" style="display:none">
                    @if(Auth::user()->hasPermissionOnAction($exportBtnRouteName))
                        <form action="{{ route(Config::get('constants.defines.APP_ROLE_PAGE_EDIT_ASSOCIATION')) }}" method="get">
                            <div class="input-group">
                                <select class="form-control selectpicker show-tick rounded" name="file_type" id="file_type">
                                    @foreach($export_types as $key => $value)
                                        <option value="{{ $key }}">{{ __(strtoupper($value)) }}</option>
                                    @endforeach
                                </select>

                                <span class="input-group-btn">
                                    <button type="submit" name="export" class="btn btn-primary btn-std-padding">{{ __('Export') }}</button>
                                </span>
                            </div>
                        </form>
                    @endif
                </div>
            @endif
            <div class="row" id="btn-area" style="display:none">
                <div class="col-md-12">
                    @if(Auth::user()->hasPermissionOnAction( Config::get('constants.defines.APP_ROLE_PAGE_EDIT_ASSOCIATION') ) )
                        <button class="btn btn-primary pull-right">{{__('Save Changes')}}</button>
                    @endif


                </div>
            </div>
        </div>
         </form>
    </div>
</div>
@endsection
@push('scripts')

    @include('partials.js_blade.validate')
<script>
    $(document).on('change', '#company-role', function () {
           var selectedValue = $(this).val();
           $('#btn-area').hide();
           $('#export_btn').hide();
           $('#role-content').html('');
            $('#module-dropdown').html('');
            if (selectedValue > 0) {
                $('#ajax-loader').show();

                $.ajax({
                    type: "GET",
                    url: '{{url(config('constants.defines.ADMIN_URL_SLUG')."/role-pages-association/getassociation/")}}/'+selectedValue,
                    dataType: "json",
                    success: function (response) {
                        console.log(response);
                        $('#ajax-loader').hide();
                        $('#module-dropdown').html(response.moduledropdown);
                        $('#role-content').html(response.role_content);
                        $('#btn-area').show();
                        $('#export_btn').show();
                    },
                    error: function (xhr, status, error) {
                        $('#ajax-loader').hide();

                    }
                });
            }

        });

        $(document).ready(function(){
            if($('#company-role').val()!=""){
                $('#company-role').change();
            }
        });

</script>
<script>
function filterModule(elem){
  var module_id  = $(elem).val();
  var rows = $('#mainTable tr.section');
  rows.show();
  if(module_id > 0){
    var section = rows.filter('.section_'+module_id).show();
    rows.not(section).hide();
  }
}

function setAllChecked(elem){
    var id = $(elem).val();
    var type = $(elem).data('type');

    if(type == 'module'){
        $('.module_check_'+id).prop('checked',false);
    }else if(type == 'submodule'){
        var module_id = $(elem).data('module');
        $('#module_'+module_id).prop('checked',false);
        $('.submodule_check_'+id).prop('checked',false);
    }

    if($(elem).prop('checked')){
        if(type == 'module'){
            $('.module_check_'+id).prop('checked',true);
        }else if(type == 'submodule'){
            var module_id = $(elem).data('module');
            var allSubmodule = 0
            var selectedSubmodule = 0;
            $('.count_submodule_'+module_id).each(function(){
                allSubmodule++;
                if($(this).prop('checked')){
                    selectedSubmodule++;
                }
            })

            if(selectedSubmodule== allSubmodule){
                $('#module_'+module_id).prop('checked',true);
            }
            $('.submodule_check_'+id).prop('checked',true);
        }
    }

}

function pageChecked(elem){
    var submodule_id = $(elem).data('submodule');
    var module_id = $(elem).data('module');
    var allPages = 0;
    var selectedPages = 0;

    $('.count_pages_'+submodule_id).each(function(){
        allPages++;
        if($(this).prop('checked')){
            selectedPages++;
        }
    })
    $('#submodule_'+submodule_id).prop('checked',false);
    $('#module_'+module_id).prop('checked',false)
    if($(elem).prop('checked')){
        if(allPages == selectedPages){
            $('#submodule_'+submodule_id).prop('checked',true);
        }
        var allSubmodule = 0
        var selectedSubmodule = 0;
        $('.count_submodule_'+module_id).each(function(){
            allSubmodule++;
            if($(this).prop('checked')){
                selectedSubmodule++;
            }
        });
        if(selectedSubmodule == allSubmodule){
            $('#module_'+module_id).prop('checked',true);
        }
    }
}
</script>

@endpush
