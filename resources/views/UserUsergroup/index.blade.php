@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')
    <style>
        #aulist li:not(:first-child), #sulist li:not(:first-child) {
            cursor: pointer;
        }
        #aulist li:hover:not(:first-child), #sulist li:hover:not(:first-child) {
            background-color: #e1eaec;
        }
    </style>
    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <form action="{{route('user.usergroup.modify')}}" method="post">
                @csrf
                <div class="ibox-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="box-body">
                                <div class="form-group mb-4">
                                    <label>{{__('User Group')}}</label>
                                    <select class="form-control" name="usergroup_id" id="company-userusergroup" required>
                                        <option value="" selected disabled>{{__("Please select")}}</option>
                                        @if(!empty($usergroups))
                                            @foreach($usergroups as $usergroup)
                                                <option value="{{$usergroup->id}}" {{(old('usergroup_id') == $usergroup->id) ? "selected" : "" }}>{{$usergroup->group_name}}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                    {{--<p id="loader" class="text-center m-0 p-0 mt-4 d-none"><i class="fa fa-spinner fa-2x fa-spin"></i></p>--}}
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row mb-4" id="swap-roles">
                                    <div class="col p-0 rounded border border-top-0 border-right-0 border-left-0 border-primary" style="margin: 0 15px;">
                                        <ul class="list-group list-group-bordered" id="aulist">
                                            <li class="list-group-item active flexbox"><label class="m-0 p-0">{{__('Available Users')}}</label></li>
                                            <li class="list-group-item flexbox">&nbsp;</li>
                                        </ul>
                                    </div>
                                    <div class="col p-0 rounded border border-top-0 border-right-0 border-left-0 border-primary" style="margin: 0 15px;">
                                        <ul class="list-group list-group-bordered" id="sulist">
                                            <li class="list-group-item active flexbox"><label class="m-0 p-0">{{__('Selected Users')}}</label></li>
                                            <li class="list-group-item flexbox">&nbsp;</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <!-- /.box-body -->
                            <div class="box-footer">
                                <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('js')
    <script>

        $(document).on('click', '.notsel', function() {
            $('#sulist').append('<li class="list-group-item flexbox seltd" data="'+$(this).attr("data")+'"><input type="hidden" name="selected_users[]" value="'+$(this).attr("data")+'" /> '+$(this).text()+'</li>');
            $(this).remove();
        });

        $(document).on('click', '.seltd', function() {
            $('#aulist').append('<li class="list-group-item flexbox notsel" data="'+$(this).attr("data")+'">'+$(this).text()+'</li>');
            $(this).remove();
        });


        $(document).on('change', '#company-userusergroup', function () {
            var selectedValue = $(this).val();
            $('#swap-roles').html();
            $('#loader').removeClass('d-none');
            if(selectedValue) {
                $.ajax({
                    type: "GET",
                    url: '{{url(config('constants.defines.ADMIN_URL_SLUG')."/user-usergroup-association/getassociation/")}}/'+selectedValue,
                    dataType: "json",
                    success: function (response) {
                        $('#loader').addClass('d-none');
                        $('#swap-roles').html(response.user_content);
                    },
                    error: function (xhr, status, error) {
                        console.log('error');
                    }
                });
            }
        });

        $(document).ready(function(){
            if($('#company-role').val()!=""){
                $('#company-role').change();
            }
            $('#company-userusergroup').change();
        });

    </script>

@endsection