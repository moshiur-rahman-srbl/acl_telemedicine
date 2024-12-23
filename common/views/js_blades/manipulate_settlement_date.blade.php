<script>
    const manipulateSettlementDate = (thisObj, targetId, saleId, url, extraHtml='') => {
        const btnContent = thisObj.html();
        thisObj.prop('disabled',true).html('&nbsp;<i class="fa fa-refresh fa-spin"></i>&nbsp;');

        $.ajax({
            'url': url,
            'type': 'get',
            'dataType': 'json',
            'data': {'sale_id':  saleId , 'action': 'get_settlement_date'},
            'success': response => {
                if (response.settlement_date !== null && response.settlement_date !== '' && response.settlement_date !== 'undefined') {
                    targetId.html(`${extraHtml}${response.settlement_date}`);
                }
            },
            'error': (xhr, status, error) => {
                console.log(error)
            },
            'complete': function() {
                thisObj.prop('disabled',false).html(btnContent);
            }
        });
    }
</script>