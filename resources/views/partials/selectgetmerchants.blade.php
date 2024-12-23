@push('scripts')
    @include('partials.js_blade.select2')
    <script>
        $('.select2-get-merchant').select2({
            multiple: true,
            width: '100%',
            placeholder: "{{__('Select Merchants')}}",
            ajax: {
                url: "{{route(Config::get('constants.defines.APP_MERCHANTS_INDEX'))}}",
                dataType: 'json',
                delay: 00,
                method: "GET",
                data: function (params) {
                    var query = {
                        "search": params.term,
                        "action":"GET_MERCHANTS"
                    }

                    // Query parameters will be ?search=[term]&type=public
                    return query;
                },
                processResults: function (data) {
                    console.log(data)
                    return {
                        results: $.map(data, function (value, key) {
                            return {
                                id: value.id,
                                text: value.text + ' (' + value.id + ')'
                            };
                        })
                    };
                },
                cache: true
            }

        });
    </script>
@endpush
