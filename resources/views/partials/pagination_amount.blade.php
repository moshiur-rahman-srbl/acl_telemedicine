<select name="page_limit" class="form-control form-control-sm d-inline"
        @if (empty($from))
            onchange="this.form.submit()"
        @endif
    style="width: auto;">
    <option value="10" <?php echo $page_limit == "10" ? 'selected="selected"' : ''  ?>>
        10
    </option>
    <option value="25" <?php echo $page_limit == "25" ? 'selected="selected"' : ''  ?>>
        25
    </option>
    <option value="50" <?php echo $page_limit == "50" ? 'selected="selected"' : ''  ?>>
        50
    </option>
    <option value="100" <?php echo $page_limit == "100" ? 'selected="selected"' : ''  ?>>
        100
    </option>
</select>
@if (!empty($from) && ($from == 'alltransaction' || $from == 'payment-transactions' || $from == 'accountstatement' || $from ==  \App\Models\MerchantReportHistory::FINANTIALIZATION_REPORT))
    @push('scripts')
        <script>
            $(document).ready(function(){
                $('select[name="page_limit"]').on('change', function(){
                    var page_limit = $(this).val();
                    $('input[name="page_limit"]').val(page_limit);
                    $('#search_form').submit();
                });
            });
        </script>
    @endpush
@endif
