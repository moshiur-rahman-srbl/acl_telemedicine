<!-- Modal -->
<div class="modal fade" id="block-cc-confirm-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
     aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-less">
                <h5 class="modal-title text-center w-100">{{__("Confirm")}}</h5>
                <button type="button" class="close cancel" data-bs-dismiss="modal" aria-label="Close">

                </button>
                <hr>
            </div>

            <div class="modal-body font13">
                <div class="row pl-4 pr-4">
                    <div class="col-12 text-muted pr-1 pl-2 text-center">
                        <label id="confirm_text"></label>
                    </div>
                </div>
                <div class="row px-4">
                    <div class="col-md-12">
                        <input type="text" class="col-12 mt-2 form-control" name="block_cc_reason" id="block_cc_reason" placeholder="Reason of Block CC">
                        <label id="block_cc_reason" style="margin: auto" class="label text-danger"></label>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label>
                            <input
                                type="checkbox"
                                class="ms-2 select_all_merchant"
                                name="select_all_merchant"
                                id="select_all_merchant"
                                checked
                            >
                            {{__('Block For All Merchant')}}
                        </label>
                        <input type="hidden" name="merchant_id" value="0" id="block_merchant_id">
                    </div>

                </div>
                <div class="row pl-4 pr-4">
                    <div class="col-6">
                        <button class="btn btn-block btn-primary rounded" id="block_cc_confirm">{{__("Confirm")}}</button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-block btn-primary rounded cancel" data-bs-dismiss="modal">{{__("Cancel")}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@section('js')
<script>
    $('#select_all_merchant').on('change', function () {
        let merchant_id = 0
        if (!$(this).is(':checked')) merchant_id = $('#block_cc').attr('data-merchant_id')
        $('#block_merchant_id').val(merchant_id)
    })
</script>
@endsection

