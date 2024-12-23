@push('scripts')
    <script>
        $('#{{$button_id}}').on('click', function (e) {
            e.preventDefault();
            $('#{{$button_id}}').attr('disabled', true);
            $('#{{$form_id}}').submit();
        })
    </script>
@endpush