<script>
    $('#input_field_type').change(function () {

        $('#divItem').empty();
        $('#divIsMultipleCheckbox').empty();

        if ($(this).val() == {{\common\integration\DplService::INPUT_FIELD_TYPE_DROPDOWN}}) {
            $('#divItem').html(
                `<label>{{ __('Input Items') }}</label>
<input type="text" name="items" class="form-control" data-role="tagsinput" />`
            );

            $('#divIsMultipleCheckbox').html(
                `<p>{{__('Is Option Multiple')}}</p>
<label class="ui-switch switch-icon switch-large switch-outline">
    <input type="checkbox" name="is_option_multiple" value="{{\common\integration\DplService::IS_OPTION_MULTIPLE_CHECKBOX_CHECKED}}">
    <span></span>
</label>`
            );
            $('input[name="items"]').tagsinput();
        }
    });

    function addItemsInTable(key) {
        $(`#${key}_item_container`).empty();

        if ($(`#${key}_input_field_type`).val() == {{\common\integration\DplService::INPUT_FIELD_TYPE_DROPDOWN}}) {
            $(`#${key}_item_container`).html(
                `<input type="text" name="${key}_items" class="form-control" data-role="tagsinput" value="" />
<label for="${key}_is_option_multiple"> <input type="checkbox" name="${key}_is_option_multiple" id="${key}_is_option_multiple" value="{{\common\integration\DplService::IS_OPTION_MULTIPLE_CHECKBOX_CHECKED}}"/>{{__('Is Option Multiple')}}</label>`
            );

            $(`input[name="${key}_items"]`).tagsinput();
        }
    }
</script>
