<!-- Modal -->
<div class="modal fade" id="confirm-reverse-chargeback-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
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
                        <label>Are you sure you want to reverse? </label>
                    </div>
                </div>
                <div class="row"><br></div>
                <div class="row pl-4 pr-4">
                    <div class="col-6">
                        <button class="btn btn-block btn-primary rounded" id="reverse_chargeback_confirm">{{__("Confirm")}}</button>
                    </div>
                    <div class="col-6">
                        <button class="btn btn-block btn-primary rounded cancel" data-bs-dismiss="modal">{{__("Cancel")}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


