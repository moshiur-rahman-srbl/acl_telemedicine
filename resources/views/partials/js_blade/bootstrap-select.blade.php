<!-- <script src="{{asset('adminca')}}/assets/vendors/bootstrap-select/dist/js/bootstrap-select.min.js"></script> -->
<script src="{{asset('adminca')}}/assets/vendors/bootstrap-select/dist/js/bootstrap-select_v1.14.0.min.js"></script>
<script>
    (function ($) {
        $.fn.selectpicker.defaults = {
            noneSelectedText: "{{__('Nothing selected')}}",
            selectAllText: "{{__('Select All')}}",
            deselectAllText: "{{__('Deselect All')}}",
            noneResultsText: '{{__('No results matched')}} {0}',
        };
    })(jQuery);
</script>
