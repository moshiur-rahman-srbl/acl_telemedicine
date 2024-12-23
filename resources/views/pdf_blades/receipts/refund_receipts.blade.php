@include('pdf_blades.include.header')

<style>
    .td-label, .td-value {
        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
        font-size:9px;
        color:#555555;
        line-height:1.2;
        padding:15px 10px;
        border-top:1px solid #bbbbbb;

    }
    .td-label {
        text-transform: uppercase;
        text-align: left !important;
    }
    .td-title {
        font-family:Arial, 'Helvetica Neue', Helvetica, sans-serif;
        padding:20px 10px;
        font-size:16px;
        color:#555555;
        line-height:1.2;
        text-align: center !important;
        text-transform: uppercase;
    }
    .pre-auth-warning{
        font-size: 12px;
    }
    .pre-auth-warning_td{
        padding-top: 10px;
    }
    .td-value{
        text-align: left !important;
    }
</style>
<tr style="margin: 1px 20px;">
    <td style="padding:10px 10px; text-align: center; background: rgba(244,244,244,1); border-radius: 50px">
        <table style="margin:0 auto;padding:0;width:100%;border-collapse:collapse;">
            <tbody>
            <tr>
                <td colspan="4" class="td-title"><strong>{{strtoupper($data['header_name'])}}</strong></td>
            </tr>

            <tr>
                <td class="td-label"><strong>{{strtoupper(__('Transaction ID'))}}:</strong></td>
                <td class="td-value">{{ $data['payment_id'] ?? ""}}</td>
                <td class="td-label"><strong>{{strtoupper(__('TRANSACTION DATE'))}}: </strong></td>
                <td class="td-value">{{ $data['refund_created_date'] ?? ""}}</td>
            </tr>
            <tr>
                <td class="td-label"><strong>{{ __('Name Surname') }}: </strong></td>
                <td class="td-value">{{ $data['user_name_surname'] ?? ""}}</td>
                <td class="td-label"><strong>{{__('MERCHANT NAME')}}: </strong></td>
                <td class="td-value">{{ $data['merchant_name'] ?? ""}}</td>
            </tr>
            <tr>
                <td class="td-label"><strong>{{strtoupper(__('Product Price'))}}: </strong></td>
                <td class="td-value">{{ $data['product_price'] ?? ""}}</td>
                <td class="td-label"><strong>{{strtoupper(__('Payment Method '))}}: </strong></td>
                <td class="td-value">{{ __( $data['payment_method'] ?? "" ) }}</td>
            </tr>
            <tr>

                <td class="td-label"><strong>{{strtoupper(__('REFUND FEE'))}}: </strong></td>
                <td class="td-value">{{ $data['refund_fee'] ?? "" }}</td>
                <td class="td-label"><strong>{{strtoupper(__('REFUNDED AMOUNT'))}}:</strong></td>
                <td class="td-value">{{ $data['total_refunded_amount'] ?? "" }}</td>

            </tr>
            <tr>
                <td class="td-label"><strong>{{__('INSTALLMENT')}}: </strong></td>
                <td class="td-value">{{ $data['installment'] ?? "" }}</td>
                <td class="td-label"><strong>{{strtoupper(__('REFUND REASON'))}}:</strong></td>
                <td class="td-value">{{ __($data['refund_reason']) ?? "" }}</td>
            </tr>

            </tbody>
        </table>
    </td>

</tr>


@include('pdf_blades.include.footer')
