<script>
    $("input[required], select[required]").attr("oninvalid", "this.setCustomValidity('{{__('Please fill out this field.')}}')");
    $("input[required], select[required]").attr("oninput", "setCustomValidity('')");
</script>
