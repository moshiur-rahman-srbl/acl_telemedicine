<script>
    // function for transaction reversal
    function setTargetUrlForReversal ($status_id, $is_reversed, $target_url) {
        var $target = $("#transactionReversalBtn");

        if ($status_id == '1' && $is_reversed != 1) {
            $target.attr('data-target', $target_url);
            $target.parent().show();
        } else {
            $target.removeAttr('data-target');
            $target.parent().hide();
            disableOrNotForReversal($is_reversed, true);
        }
    }
    function disableOrNotForReversal ($is_reversed, $auto_show = false) {
        if ($is_reversed == 1) {
            $(".disableForReversal").hide();
        } else if ($auto_show) {
            $(".disableForReversal").show();
        }
    }
    // function for transaction reversal
    function reverseThisTransaction ($this) {
        $('#reverseSubmitForm').data('action', $($this).attr('data-target'));
        $('.modal').modal('hide');
        $('.reverseReasonText').val('');
        $('.error').empty();
        $('#reverse_modal_id').modal('show');
    }
    $(function () {
        let reasonText = '';
        let reverseUrl = '';
        $('#reverseReasonText').on('input', function () {
            reasonText = $(this).val();
        });
        $('#reverseReasonModalClose').on('click', function () {
            $('#reverse_modal_id').modal('hide');
        });
        $('#reverseSubmitForm').on('submit', function(event) {
            event.preventDefault();
            reverseUrl = $(this).data('action');
            alertify.confirm("{{ __('Are you sure?')}}", function () {
                $('#revertReasonFormBtn').prop('disabled', true);
                $('#revertReasonFormBtn').html('<i class="fa fa-spin fa-spinner"></i>');
                $.ajax({
                    type: "POST",
                    url: reverseUrl,
                    data: {
                        '_token': '{{ csrf_token() }}',
                        'revert_reason': reasonText
                    },
                    dataType: "json",
                    success: function (res) {
                        let response = res.data;

                        if(response.status_code == '{{\common\integration\ApiService::API_SERVICE_SUCCESS_CODE}}') {
                            if($('#moneyTransferSubmitForm').length) {
                                $('#moneyTransferSubmitForm').submit(); //for reload page with post method search filter.
                            } else {
                                window.location.reload()
                            }
                        } else {
                            $('.error').html('<div class="alert alert-danger alert-dismissible fade show" id="alert"><p>'+response.message+'</p><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        }
                    },
                    complete: function () {
                        $('#revertReasonFormBtn').prop('disabled', false);
                        $('#revertReasonFormBtn').html('{{"Submit"}}');
                    }
                });
            }, function (e) {
                return false;
            });
            $('button.cancel').addClass('btn btn-light').text("{{ __('Cancel')}}");
            $('button.ok').addClass('btn btn-danger').text("{{ __('Yes')}}");
        });
    });
</script>
