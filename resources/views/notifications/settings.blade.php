@extends('layouts.adminca')
@section('content')
    @include('partials.page_heading')

    <div class="page-content fade-in-up">
        @include('partials.flash')
        <div class="ibox">
            <form action="{{route(Config::get('constants.defines.APP_NOTIFICATION_SETTINGS'))}}" method="post">
                @csrf
                <div class="ibox-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group mb-4">
                                <label>{{__('User Group')}}</label>
                                <select class="form-control" name="user_group" id="user-group">
                                    <option value="">{{__("Please select")}}</option>
                                    @if(!empty($userGroups))
                                        @foreach($userGroups as $userGroup)
                                            <option value="{{ $userGroup->id }}" {{ (old('user_group') == $userGroup->id) ? "selected" : "" }}>{{ $userGroup->group_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                <img id="ajax-loader" style="z-index:10000;display:none" class="pull-right mt-2" src="{{ asset('ajax-loader.gif') }}" alt="loader"/>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <div id="categories-subcategories-content"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="btn-area" style="display:none">
                        <div class="col-md-12">
                            <button class="btn btn-primary pull-right">{{__('Save Changes')}}</button>
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
        $(document).on('change', '#user-group', function () {
            var selectedValue = $(this).val();
            $('#btn-area').hide();
            $('#categories-subcategories-content').html('');
            if (selectedValue > 0) {
                $('#ajax-loader').show();

                $.ajax({
                    type: "GET",
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    url: '{{ route(Config::get('constants.defines.APP_NOTIFICATION_SETTINGS')) }}' + '?user_group=' + selectedValue,
                    dataType: "json",
                    success: function (response) {
                        $('#ajax-loader').hide();
                        $('#categories-subcategories-content').html(response.content);
                        $('#btn-area').show();
                    },
                    error: function (xhr, status, error) {
                        $('#ajax-loader').hide();
                    }
                });
            }

        });

        $(document).ready(function () {
            let userGroupSelect = $('#user-group');
            if (userGroupSelect.val() !== "") {
                userGroupSelect.change();
            }
        });

        function changedCategory(category_id) {
            var status = $('#category-' + category_id).is(':checked');
            $('.subcategory-' + category_id).each(function () {
                $(this).prop('checked', status);
            })
        }

        function changedSubcategory(category_id, subcategory_id) {
            var status = false;
            $('.subcategory-' + category_id).each(function () {
                if ($(this).is(':checked')) {
                    status = true;
                    return false;
                }
            });
            $('#category-' + category_id).prop('checked', status);
        }
    </script>
@endpush
