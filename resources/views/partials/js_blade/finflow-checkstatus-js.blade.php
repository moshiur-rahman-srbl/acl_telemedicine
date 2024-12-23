@push('scripts')
    <script>
        $('#check-status-button').click(function () {
            if ($('.bulk-action-items:checked').length > 0) {
                let confirmation_message = "<strong class='p-0 m-0 d-block'>" + "{{__('Are you sure?')}}" + "</strong>";
                confirmation_message += "<p class='p-0 m-0 pl-4 pr-4 text-left'>" + "{{__('This process will check the cashout status from finflow server and will complete the transaction accordingly')}}" + ".</p>";
                alertify.confirm(confirmation_message, function () {
                    let form_url = "{{ route(Config::get('constants.defines.APP_CASHOUTS_CHECK_STATUS')) }}";
                    let transaction_type = $('#check-status-button').data('transaction_type');
                    let bulk_action_form_selector = $('#bulk-action-form');

                    bulk_action_form_selector.append("<input type='hidden' name='transaction_type' value='" + transaction_type + "'/>");
                    bulk_action_form_selector.attr('action', form_url).submit();
                });

                $('button.ok').addClass('btn btn-danger rounded').text("{{ __('Okay') }}");
                $('button.cancel').addClass('btn btn-light rounded').text("{{ __('Cancel') }}");
            } else {
                alertify.alert("{{ __('Please, select transaction to check status.') }}");
                $('button.ok').addClass('btn btn-light rounded').text("{{ __('Okay') }}");
            }
        });
    </script>
@endpush
