<!-- Hidden form to manage bulk actions start -->
<form action="" method="POST" id="bulk-action-form" class="d-none">
    @csrf
</form>
<!-- Hidden form to manage bulk actions end -->

@push('scripts')
    <script>
        $(document).ready(function () {
            $('#check-all').click(function () {
                $('.bulk-action-items').prop('checked', $(this).prop('checked'));
            });

            $('.bulk-action-items').change(function () {
                if ($('.bulk-action-items:checked').length === $('.bulk-action-items').length) {
                    $('#check-all').prop('checked', true);
                } else {
                    $('#check-all').prop('checked', false);
                }
            });


            $('#check_all_pending').on('click', function () {
                $('.bulk-pending-items').prop('checked', $(this).prop('checked'))
                let transaction_id = []
                $.each($('input[name="withdrawal_id[]"]:checked'), function () {
                    transaction_id.push([ $(this).val()])
                })
                if (transaction_id.length > 0){
                    $('#total_record_display_area').text(` ${transaction_id.length} {{__('items selected')}}`)
                }else{
                    $('#total_record_display_area').text('')
                }

            })

            $('.bulk-pending-items').on('change', function () {
                if ($('.bulk-pending-items:checked').length === $('.bulk-pending-items').length) {
                    $('#check_all_pending').prop('checked', true);
                } else {
                    $('#check_all_pending').prop('checked', false);
                }
            })


        });
    </script>
@endpush
