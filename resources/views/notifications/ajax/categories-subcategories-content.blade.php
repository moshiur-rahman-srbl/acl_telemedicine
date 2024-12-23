<table class="table table-bordered" id="mainTable">
    <thead>
    <tr>
        <th rowspan="2">{{__('ID')}}</th>
        <th rowspan="2">{{__('Category')}}</th>
        <th rowspan="2">{{__('Sub Category')}}</th>
        <td colspan="3" class="text-center">{{__('Action')}}</td>
    </tr>
{{--    <tr>--}}
{{--        <th>{{__('E-Mail')}}</th>--}}
{{--        <th>{{__('SMS')}}</th>--}}
{{--        <th>{{__('Push')}}</th>--}}
{{--    </tr>--}}
    </thead>
    <tbody>
    @foreach($notificationCategories as $notificationCategory)
        @php($totalSubCategory = $notificationCategory->subcategories->count())
        @foreach($notificationCategory->subcategories as $subcategory)
            @php($userGroupsForCurrentSubcategories = $subcategory->userGroups->where('id', $userGroup->id))
            <tr>
                @if($totalSubCategory)
                    <td rowspan="{{ $totalSubCategory }}">{{ $notificationCategory->id }}</td>
                    <td rowspan="{{ $totalSubCategory }}">
                        <label class="checkbox checkbox-ebony">
                            <input type="checkbox" class="bulk-action" id="category-{{ $notificationCategory->id }}" onchange="changedCategory('{{ $notificationCategory->id }}')" {{ $userGroup->notificationSubcategories->whereIn('id', $notificationCategory->subcategories->pluck('id'))->count() ? 'checked' : null }}>
                            <span class="input-span"></span>{{ __($notificationCategory->name) }}
                        </label>
                    </td>
                    @php($totalSubCategory = 0)
                @endif
                <td>{{ __($subcategory->name) }}</td>
{{--                <td>--}}
{{--                    <label class="checkbox checkbox-ebony">--}}
{{--                        <input type="checkbox" name="sub_categories[{{ $subcategory->id }}][is_email]" value="1" class="bulk-action subcategory-{{ $notificationCategory->id }}" id="subcategory-{{ $notificationCategory->id }}-{{ $subcategory->id }}" onchange="changedSubcategory('{{ $notificationCategory->id }}', '{{ $subcategory->id }}')" {{ ($userGroupsForCurrentSubcategories->count() && $userGroupsForCurrentSubcategories->first()->pivot->is_email) ? 'checked' : null }}>--}}
{{--                        <span class="input-span"></span>--}}
{{--                    </label>--}}
{{--                </td>--}}
{{--                <td>--}}
{{--                    <label class="checkbox checkbox-ebony">--}}
{{--                        <input type="checkbox" name="sub_categories[{{ $subcategory->id }}][is_sms]" value="1" class="bulk-action subcategory-{{ $notificationCategory->id }}" id="subcategory-{{ $notificationCategory->id }}-{{ $subcategory->id }}" onchange="changedSubcategory('{{ $notificationCategory->id }}', '{{ $subcategory->id }}')" {{ ($userGroupsForCurrentSubcategories->count() && $userGroupsForCurrentSubcategories->first()->pivot->is_sms) ? 'checked' : null }}>--}}
{{--                        <span class="input-span"></span>--}}
{{--                    </label>--}}
{{--                </td>--}}
                <td class="text-center">
                    <label class="checkbox checkbox-ebony">
                        <input type="checkbox" name="sub_categories[{{ $subcategory->id }}][is_push]" value="1" class="bulk-action subcategory-{{ $notificationCategory->id }}" id="subcategory-{{ $notificationCategory->id }}-{{ $subcategory->id }}" onchange="changedSubcategory('{{ $notificationCategory->id }}', '{{ $subcategory->id }}')" {{ ($userGroupsForCurrentSubcategories->count() && $userGroupsForCurrentSubcategories->first()->pivot->is_push) ? 'checked' : null }}>
                        <span class="input-span"></span>
                    </label>
                </td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
